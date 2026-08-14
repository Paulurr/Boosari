<?php

namespace App\Http\Controllers;

use App\Models\AgentConversation;
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

 
    private function buildPrompt($user, string $mensaje): string
    {
        $uid = $user->id;

        $wallets     = Wallet::where('user_id', $uid)->get(['titulo', 'tipo', 'monto_actual']);
        $incomes     = Income::where('user_id', $uid)->where('activo', true)->get(['titulo', 'monto', 'frecuencia']);
        $debts       = Debt::where('user_id', $uid)->where('estado', 'pendiente')->get(['titulo', 'monto_actual', 'tasa_interes', 'fecha_vencimiento']);
        $goals       = Goal::where('user_id', $uid)->where('estado', 'activa')->get(['titulo', 'monto_actual', 'monto_objetivo', 'fecha_limite']);
        $investments = Investment::where('user_id', $uid)->where('estado', 'activa')->get(['titulo', 'monto_inicial', 'valor_actual', 'ganancia', 'tipo_renta']);

        $gastoMes = Transaction::where('user_id', $uid)->where('tipo', 'gasto')
            ->whereMonth('fecha_ejecucion', now()->month)->whereYear('fecha_ejecucion', now()->year)->sum('monto');
        $ingresoMes = Transaction::where('user_id', $uid)->where('tipo', 'ingreso')
            ->whereMonth('fecha_ejecucion', now()->month)->whereYear('fecha_ejecucion', now()->year)->sum('monto');
        
        $c = "CONTEXTO FINANCIERO DEL USUARIO:\n\n";

        $c .= "BILLETERAS:\n";
        foreach ($wallets as $w) {
            $c .= "- {$w->titulo} ({$w->tipo}): \${$w->monto_actual}\n";
        }

        $c .= "\nINGRESOS ACTIVOS:\n";
        foreach ($incomes as $i) {
            $c .= "- {$i->titulo}: \${$i->monto} ({$i->frecuencia})\n";
        }

        $c .= "\nDEUDAS PENDIENTES:\n";
        foreach ($debts as $d) {
            $c .= "- {$d->titulo}: \${$d->monto_actual} restante, tasa {$d->tasa_interes}%, vence {$d->fecha_vencimiento}\n";
        }

        $c .= "\nMETAS ACTIVAS:\n";
        foreach ($goals as $g) {
            $c .= "- {$g->titulo}: \${$g->monto_actual} de \${$g->monto_objetivo}, límite {$g->fecha_limite}\n";
        }

        $c .= "\nINVERSIONES ACTIVAS:\n";
        foreach ($investments as $inv) {
            $c .= "- {$inv->titulo} ({$inv->tipo_renta}): invertido \${$inv->monto_inicial}, valor actual \${$inv->valor_actual}, ganancia \${$inv->ganancia}\n";
        }

        $c .= "\nRESUMEN MES ACTUAL: ingresos \${$ingresoMes}, gastos \${$gastoMes}\n";
        $c .= "\n---\nPREGUNTA DEL USUARIO:\n{$mensaje}";

        return $c;
    }
}