<?php

namespace App\Http\Controllers;

use App\Models\AgentConversation;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Goal;
use App\Models\Income;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\CozeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Cuántas transacciones recientes se mandan como contexto.
     * Súbelo si quieres que el agente "recuerde" más historial,
     * pero ten en cuenta que sube el tamaño (y costo) del prompt.
     */
    private const LIMITE_TRANSACCIONES = 300;

    /**
     * true si el usuario autenticado tiene el agente activado
     * (por defecto true si aún no tiene fila en "configuraciones").
     */
    private function agenteActivo(): bool
    {
        return auth()->user()->configuracion->agente_activo ?? true;
    }

    public function index()
    {
        $agenteActivo = $this->agenteActivo();

        $conversaciones = $agenteActivo
            ? AgentConversation::where('user_id', auth()->id())
                ->latest()
                ->get(['id', 'titulo', 'created_at'])
            : collect();

        return view('agent', compact('conversaciones', 'agenteActivo'));
    }

    public function messages(AgentConversation $conversation)
    {
        abort_unless($this->agenteActivo(), 403, 'El asistente está desactivado.');
        abort_unless($conversation->user_id === auth()->id(), 403);

        return response()->json([
            'status' => 'success',
            'data'   => $conversation->messages()->get(['rol', 'contenido', 'created_at']),
        ]);
    }

    public function destroy(AgentConversation $conversation)
    {
        abort_unless($conversation->user_id === auth()->id(), 403);

        // Los mensajes se borran solos: agent_messages tiene
        // onDelete('cascade') sobre agent_conversation_id.
        $conversation->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Conversación eliminada.',
        ]);
    }

    public function chat(Request $request)
    {
        abort_unless($this->agenteActivo(), 403, 'El asistente está desactivado. Actívalo desde Configuración.');

        set_time_limit(120);

        $request->validate([
            'mensaje'         => 'required|string|max:1000',
            'conversation_id' => 'nullable|integer|exists:agent_conversations,id',
        ]);

        $conversation = null;
        $esNuevaConversacion = false;

        if ($request->filled('conversation_id')) {
            $conversation = AgentConversation::where('user_id', auth()->id())
                ->find($request->conversation_id);
        }

        if (!$conversation) {
            $conversation = AgentConversation::create([
                'user_id' => auth()->id(),
                'titulo'  => Str::limit($request->mensaje, 40),
            ]);
            $esNuevaConversacion = true;
        }

        // Guarda el mensaje del usuario tal cual en la BD local
        $conversation->messages()->create([
            'rol'       => 'usuario',
            'contenido' => $request->mensaje,
        ]);

        // Si es el primer mensaje del chat, inyecta el contexto de la BD; 
        // si la conversación ya existe, solo envía la pregunta del usuario para no saturar a Coze
        $promptCoze = $esNuevaConversacion 
            ? $this->buildPrompt(auth()->user(), $request->mensaje) 
            : $request->mensaje;

        try {
            $coze = app(CozeService::class);
            $resultado = $coze->sendMessage($conversation->coze_conversation_id, (string) auth()->id(), $promptCoze);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 502);
        }

        if (!$conversation->coze_conversation_id) {
            $conversation->update(['coze_conversation_id' => $resultado['conversation_id']]);
        }

        // Guarda la respuesta limpia del asistente
        $conversation->messages()->create([
            'rol'       => 'asistente',
            'contenido' => $resultado['reply'],
        ]);

        return response()->json([
            'status'          => 'success',
            'conversation_id' => $conversation->id,
            'reply'           => $resultado['reply'],
        ]);
    }

    /**
     * Arma el contexto financiero completo del usuario para el primer
     * mensaje de cada conversación. Incluye TODO (no solo lo activo/del
     * mes) para que el agente pueda responder sobre cualquier billetera,
     * transacción, deuda, meta o inversión por nombre, sin importar si
     * está activa, pagada, vencida o del mes actual.
     */
    private function buildPrompt($user, string $mensaje): string
    {
        $uid = $user->id;

        // ---- Catálogos para resolver nombres (evita mandar solo ids) ----
        $wallets      = Wallet::where('user_id', $uid)->get();
        $walletsPorId = $wallets->keyBy('id');
        $categorias   = Category::where('user_id', $uid)->pluck('categoria', 'id');

        $c = "=== PERFIL FINANCIERO COMPLETO DE {$user->name} ===\n";
        $c .= "Toda la información de esta sección viene directo de la base de datos del usuario y está completa (no solo el mes actual, no solo lo activo). ";
        $c .= "Úsala para responder con precisión: si preguntan por el saldo de una billetera específica, dilo exacto; si mencionan el nombre de una transacción, deuda, meta o inversión, búscala en las listas de abajo y describe todos sus datos; si preguntan por algo pasado, pagado o vencido, también está incluido aquí.\n\n";

        // ---- Billeteras ----
        $c .= "--- BILLETERAS (" . $wallets->count() . ") ---\n";
        foreach ($wallets as $w) {
            $c .= "- \"{$w->titulo}\" (tipo: {$w->tipo}): saldo actual \${$w->monto_actual} (saldo inicial \${$w->monto_inicial})\n";
        }
        $c .= "Saldo total sumando todas las billeteras: \$" . number_format((float) $wallets->sum('monto_actual'), 2) . "\n";

        // ---- Ingresos (todos, activos e inactivos) ----
        $incomes = Income::where('user_id', $uid)->get();
        $c .= "\n--- INGRESOS REGISTRADOS (" . $incomes->count() . ") ---\n";
        foreach ($incomes as $i) {
            $wallet = $walletsPorId->get($i->wallet_id)?->titulo ?? 'sin billetera';
            $cat    = $categorias[$i->category_id] ?? 'sin categoría';
            $estado = $i->activo ? 'activo' : 'inactivo';
            $c .= "- \"{$i->titulo}\": \${$i->monto} cada {$i->frecuencia}, billetera \"{$wallet}\", categoría {$cat}, desde {$i->fecha_inicio?->format('Y-m-d')}, estado: {$estado}\n";
        }

        // ---- Deudas (todas, con su historial de pagos) ----
        $debts = Debt::where('user_id', $uid)->with('payments')->get();
        $c .= "\n--- DEUDAS (" . $debts->count() . ") ---\n";
        foreach ($debts as $d) {
            $cat = $categorias[$d->category_id] ?? 'sin categoría';
            $c .= "- \"{$d->titulo}\" [estado: {$d->estado}]: debe \${$d->monto_actual} de \${$d->monto_inicial} original, tasa {$d->tasa_interes}%, vence {$d->fecha_vencimiento?->format('Y-m-d')}, prioridad {$d->prioridad}, categoría {$cat}\n";
            foreach ($d->payments as $p) {
                $walletPago = $walletsPorId->get($p->wallet_id)?->titulo ?? 'sin billetera';
                $c .= "    · pago de \${$p->monto} el {$p->created_at->format('Y-m-d')} desde \"{$walletPago}\"" . ($p->pago_minimo ? ' (pago mínimo)' : '') . "\n";
            }
        }

        // ---- Metas de ahorro (todas, con su historial de abonos) ----
        $goals = Goal::where('user_id', $uid)->with('payments')->get();
        $c .= "\n--- METAS DE AHORRO (" . $goals->count() . ") ---\n";
        foreach ($goals as $g) {
            $cat = $categorias[$g->category_id] ?? 'sin categoría';
            $c .= "- \"{$g->titulo}\" [estado: {$g->estado}]: \${$g->monto_actual} de \${$g->monto_objetivo} (inicial \${$g->monto_inicial}), límite {$g->fecha_limite?->format('Y-m-d')}, categoría {$cat}";
            $c .= $g->descripcion ? ", nota: {$g->descripcion}\n" : "\n";
            foreach ($g->payments as $p) {
                $walletAbono = $walletsPorId->get($p->wallet_id)?->titulo ?? 'sin billetera';
                $c .= "    · abono de \${$p->monto} el {$p->created_at->format('Y-m-d')} desde \"{$walletAbono}\"\n";
            }
        }

        // ---- Inversiones (todas) ----
        $investments = Investment::where('user_id', $uid)->get();
        $c .= "\n--- INVERSIONES (" . $investments->count() . ") ---\n";
        foreach ($investments as $inv) {
            $wallet = $walletsPorId->get($inv->wallet_id)?->titulo ?? 'sin billetera';
            $cat    = $categorias[$inv->category_id] ?? 'sin categoría';
            $c .= "- \"{$inv->titulo}\" [estado: {$inv->estado}] ({$inv->tipo_renta}): invertido \${$inv->monto_inicial}, valor actual \${$inv->valor_actual}, ganancia \${$inv->ganancia}, tasa {$inv->tasa_interes}%, adquirida {$inv->fecha_adquisicion?->format('Y-m-d')}, vence {$inv->fecha_vencimiento?->format('Y-m-d')}, billetera \"{$wallet}\", categoría {$cat}\n";
        }

        // ---- Transacciones recientes (para buscar cualquiera por nombre/monto/fecha) ----
        $transactions = Transaction::where('user_id', $uid)
            ->orderByDesc('fecha_ejecucion')
            ->limit(self::LIMITE_TRANSACCIONES)
            ->get();

        $totalTransacciones = Transaction::where('user_id', $uid)->count();

        $c .= "\n--- TRANSACCIONES (mostrando las " . $transactions->count() . " más recientes de un total de {$totalTransacciones}; están de la más nueva a la más vieja) ---\n";
        foreach ($transactions as $t) {
            $origen  = $walletsPorId->get($t->wallet_origen_id)?->titulo;
            $destino = $walletsPorId->get($t->wallet_destino_id)?->titulo;
            $cat     = $categorias[$t->category_id] ?? 'sin categoría';
            $fecha   = $t->fecha_ejecucion?->format('Y-m-d H:i');

            $c .= "- \"{$t->titulo}\" [{$t->tipo}]: \${$t->monto}, categoría {$cat}, fecha {$fecha}";
            if ($origen)  $c .= ", desde \"{$origen}\"";
            if ($destino) $c .= ", hacia \"{$destino}\"";
            $c .= "\n";
        }

        // ---- Resumen del mes actual ----
        $gastoMes = Transaction::where('user_id', $uid)->where('tipo', 'gasto')
            ->whereMonth('fecha_ejecucion', now()->month)->whereYear('fecha_ejecucion', now()->year)->sum('monto');
        $ingresoMes = Transaction::where('user_id', $uid)->where('tipo', 'ingreso')
            ->whereMonth('fecha_ejecucion', now()->month)->whereYear('fecha_ejecucion', now()->year)->sum('monto');

        // ---- Totales históricos (todo el tiempo, no solo el mes) ----
        $gastoTotal   = Transaction::where('user_id', $uid)->where('tipo', 'gasto')->sum('monto');
        $ingresoTotal = Transaction::where('user_id', $uid)->where('tipo', 'ingreso')->sum('monto');

        $c .= "\n--- RESUMEN ---\n";
        $c .= "Mes actual (" . now()->translatedFormat('F Y') . "): ingresos \${$ingresoMes}, gastos \${$gastoMes}\n";
        $c .= "Histórico total (desde que el usuario tiene cuenta): ingresos \${$ingresoTotal}, gastos \${$gastoTotal}, {$totalTransacciones} transacciones registradas en total\n";

        $c .= "\n---\nPREGUNTA DEL USUARIO:\n{$mensaje}";

        return $c;
    }
}