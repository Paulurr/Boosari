<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Income;
use App\Models\Category;
use App\Models\Goal;
use App\Models\PaymentGoal;
use App\Models\Debt;
use App\Models\PaymentDebt;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InfoController extends Controller
{
    public function getWalletInfo($id)
    {
        // 1. Obtener la billetera del usuario
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($id);

        // 2. Obtener los últimos 8 movimientos e incluir las relaciones de origen y destino
        $transactions = Transaction::with(['walletOrigen', 'walletDestino'])
            ->where(function ($query) use ($id) {
                $query->where('wallet_origen_id', $id)
                      ->orWhere('wallet_destino_id', $id);
            })
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 3. Reconstruir el saldo histórico hacia atrás partiendo del saldo actual
        $runningBalance = (float) $wallet->monto_actual;
        $historial = [];

        foreach ($transactions as $tx) {
            $historial[] = [
                'titulo'  => $tx->titulo,
                'monto'   => (float) $tx->monto,
                'tipo'    => $tx->tipo,
                'saldo'   => round($runningBalance, 2),
                'fecha'   => $tx->created_at ? $tx->created_at->format('d/m H:i') : 'N/A',
                'origen'  => $tx->walletOrigen->titulo ?? 'N/A',
                'destino' => $tx->walletDestino->titulo ?? 'N/A'
            ];

            // Revertir el movimiento para calcular el saldo anterior
            $isDestino = ($tx->wallet_destino_id == $id);
            $isOrigen  = ($tx->wallet_origen_id == $id);

            if ($wallet->tipo === 'credito') {
                if ($tx->tipo === 'gasto' && $isDestino) {
                    $runningBalance -= $tx->monto;
                } elseif ($tx->tipo === 'transferencia' && $isDestino) {
                    $runningBalance += $tx->monto;
                }
            } else {
                if ($isDestino && ($tx->tipo === 'ingreso' || $tx->tipo === 'transferencia')) {
                    $runningBalance -= $tx->monto;
                } elseif ($isOrigen || ($tx->tipo === 'gasto' && $isDestino)) {
                    $runningBalance += $tx->monto;
                }
            }
        }

        // Reordenar de más antiguo a más reciente para el eje X de la gráfica
        $historial = array_reverse($historial);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'            => $wallet->id,
                'titulo'        => $wallet->titulo,
                'tipo'          => ucfirst($wallet->tipo),
                'icono'         => $wallet->icono ? asset('storage/' . $wallet->icono) : asset('images/logo_boosari.webp'),
                'monto_actual'  => number_format($wallet->monto_actual, 2, '.', ''),
                'monto_inicial' => number_format($wallet->monto_inicial, 2, '.', ''),
                'fecha'         => $wallet->created_at->format('d/m/Y H:i'),
                'historial'     => $historial
            ]
        ]);
    }

   public function updateWallet(Request $request, $id)
    {
        $request->validate([
            'titulo'             => 'required|string|max:25',
            'monto_actual'       => 'required|numeric',
            'monto_inicial'      => 'required|numeric',
            'movimiento'         => 'nullable|array',
            'movimiento.titulo'  => 'required_with:movimiento.monto|string|max:25',
            'movimiento.monto'   => 'required_with:movimiento.titulo|numeric|min:0.01',
            'movimiento.tipo'    => 'required_with:movimiento.monto|in:ingreso,gasto',
            'icono'              => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                // lockForUpdate() dentro de la transacción (antes estaba fuera y el
                // bloqueo no protegía nada: en MySQL, sin transacción activa, el
                // lock se libera apenas termina esa consulta).
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->findOrFail($id);
                $esCredito = $wallet->tipo === 'credito';

                // Si se subió un icono nuevo, se guarda y se borra el anterior
                // (misma carpeta que usa create_wallet en RecordController).
                $nuevoIcono = $wallet->icono;
                if ($request->hasFile('icono')) {
                    $nuevoIcono = $request->file('icono')->store('wallet_icons', 'public');
                    if ($wallet->icono) {
                        Storage::disk('public')->delete($wallet->icono);
                    }
                }

            // El saldo final se calcula SIEMPRE en el servidor, nunca se confía
            // ciegamente en "monto_actual" que manda el cliente: si viene un
            // movimiento explícito, ese movimiento es la única fuente de verdad
            // para el nuevo saldo (antes se podía enviar un monto_actual que no
            // coincidía con el monto del movimiento, dejando el historial de
            // transacciones desincronizado del saldo real de la billetera).
            if ($request->filled('movimiento.monto')) {
                $mov = $request->movimiento;

                // Mismas reglas de negocio que create_transaction: en una tarjeta
                // de crédito no se permiten "ingresos" directos, y un "gasto"
                // SUMA al saldo (es deuda) en vez de restar.
                if ($esCredito && $mov['tipo'] === 'ingreso') {
                    throw new \Exception('No se pueden registrar ingresos directos a una tarjeta de crédito.');
                }

                if ($esCredito) {
                    // gasto en crédito: siempre suma, y la billetera actúa como "destino"
                    $signo = 1;
                    $nuevoMontoActual = round($wallet->monto_actual + $mov['monto'], 2);
                } else {
                    $signo = $mov['tipo'] === 'gasto' ? -1 : 1;
                    $nuevoMontoActual = round($wallet->monto_actual + ($signo * $mov['monto']), 2);

                    if ($mov['tipo'] === 'gasto' && $nuevoMontoActual < 0) {
                        throw new \Exception('Fondos insuficientes para registrar este movimiento.');
                    }
                }

                Transaction::create([
                    'user_id'           => auth()->id(),
                    'titulo'            => $mov['titulo'],
                    'monto'             => $mov['monto'],
                    'tipo'              => $mov['tipo'],
                    // En crédito, el gasto siempre entra como "destino" (nunca origen);
                    // en el resto, el gasto sale de la billetera como "origen".
                    'wallet_origen_id'  => (!$esCredito && $mov['tipo'] === 'gasto') ? $id : null,
                    'wallet_destino_id' => ($esCredito || $mov['tipo'] === 'ingreso') ? $id : null,
                ]);
            } else {
                // Sin movimiento explícito: el monto_actual enviado se trata como
                // ajuste manual de saldo y sí se respeta el valor del cliente.
                $nuevoMontoActual = (float) $request->monto_actual;
                $diferencia = $nuevoMontoActual - $wallet->monto_actual;

                if ($diferencia != 0) {
                    // Mismas reglas de polaridad que create_transaction:
                    // en crédito, cualquier aumento de saldo es "gasto" (deuda) y
                    // cualquier disminución es "transferencia" (pago/abono a la
                    // tarjeta); en el resto, aumento = ingreso, disminución = gasto.
                    if ($esCredito) {
                        Transaction::create([
                            'user_id'           => auth()->id(),
                            'titulo'            => 'Ajuste manual de saldo',
                            'monto'             => abs($diferencia),
                            'tipo'              => $diferencia > 0 ? 'gasto' : 'transferencia',
                            'wallet_origen_id'  => null,
                            'wallet_destino_id' => $id,
                        ]);
                    } else {
                        Transaction::create([
                            'user_id'           => auth()->id(),
                            'titulo'            => 'Ajuste manual de saldo',
                            'monto'             => abs($diferencia),
                            'tipo'              => $diferencia > 0 ? 'ingreso' : 'gasto',
                            'wallet_origen_id'  => $diferencia < 0 ? $id : null,
                            'wallet_destino_id' => $diferencia > 0 ? $id : null,
                        ]);
                    }
                }
            }

            // Actualizar la billetera con los nuevos valores
            $wallet->update([
                'titulo'        => $request->titulo,
                'monto_actual'  => $nuevoMontoActual,
                'monto_inicial' => $request->monto_inicial,
                'icono'         => $nuevoIcono,
            ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getWalletInfo($id);
    }

    public function deleteWallet($id)
    {
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($id) {
            // Desvincular transacciones dependientes antes de borrar la billetera
            Transaction::where('wallet_origen_id', $id)->update(['wallet_origen_id' => null]);
            Transaction::where('wallet_destino_id', $id)->update(['wallet_destino_id' => null]);

            // IMPORTANTE: incomes.wallet_id no tiene onDelete('set null') en la
            // migración (a diferencia de investments/payment_goals/payment_debts),
            // así que si no se desvincula aquí, borrar la billetera revienta con
            // un error de restricción de llave foránea sin manejar (500) en
            // cuanto exista un ingreso programado apuntando a ella.
            Income::where('wallet_id', $id)->update(['wallet_id' => null]);

            Wallet::where('id', $id)->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Billetera eliminada con éxito.'
        ]);
}
// Obtener información detallada del movimiento
public function getTransactionInfo($id)
{
    $transaction = Transaction::with(['walletOrigen', 'walletDestino', 'category'])
        ->where('user_id', auth()->id())
        ->findOrFail($id);

    return response()->json([
        'status' => 'success',
        'data' => [
            'id'             => $transaction->id,
            'titulo'         => $transaction->titulo,
            'icono'          => $transaction->icono ? asset('storage/' . $transaction->icono) : asset('images/logo_boosari.webp'),
            'tipo'           => ucfirst($transaction->tipo),
            'tipo_raw'       => $transaction->tipo,
            'monto'          => number_format($transaction->monto, 2, '.', ''),
            'categoria'      => $transaction->category->categoria ?? 'Sin categoría',
            'origen_id'      => $transaction->wallet_origen_id,
            'origen_nombre'  => $transaction->walletOrigen->titulo ?? 'N/A',
            'destino_id'     => $transaction->wallet_destino_id,
            'destino_nombre' => $transaction->walletDestino->titulo ?? 'N/A',
            'fecha'          => $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : 'N/A',
        ]
    ]);
}

// Aplica el efecto en saldo (positivo o negativo) que corresponde a una
// transacción según su tipo y el tipo de billetera (crédito o no), usando
// las MISMAS reglas de polaridad que create_transaction en RecordController.
// $signo = 1 para aplicar el movimiento tal cual, -1 para revertirlo.
private function applyTransactionEffect(Transaction $tx, int $signo): void
{
    $monto = (float) $tx->monto;

    if ($tx->tipo === 'ingreso') {
        if ($tx->wallet_destino_id) {
            $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($tx->wallet_destino_id);
            if ($wallet) {
                $wallet->increment('monto_actual', $signo * $monto);
            }
        }
    } elseif ($tx->tipo === 'gasto') {
        if ($tx->wallet_destino_id) {
            $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($tx->wallet_destino_id);
            if ($wallet) {
                // En crédito, un gasto SUMA al saldo (deuda); en el resto, RESTA.
                $delta = $wallet->tipo === 'credito' ? ($signo * $monto) : (-$signo * $monto);
                $wallet->increment('monto_actual', $delta);
            }
        }
    } elseif ($tx->tipo === 'transferencia') {
        if ($tx->wallet_origen_id) {
            $origen = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($tx->wallet_origen_id);
            if ($origen) {
                $origen->increment('monto_actual', -$signo * $monto);
            }
        }
        if ($tx->wallet_destino_id) {
            $destino = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($tx->wallet_destino_id);
            if ($destino) {
                // Transferir a una tarjeta de crédito abona/amortiza (RESTA); al resto, SUMA.
                $delta = $destino->tipo === 'credito' ? (-$signo * $monto) : ($signo * $monto);
                $destino->increment('monto_actual', $delta);
            }
        }
    }
}

// Actualizar un movimiento
public function updateTransaction(Request $request, $id)
{
    $request->validate([
        'titulo'    => 'required|string|max:25',
        'monto'     => 'required|numeric|min:0.01',
        'categoria' => 'nullable|string|max:25',
        'icono'     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
    ]);

    $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);

    // La categoría vive en la tabla categories (category_id), no en una
    // columna "categoria" directa de transactions.
    $categoryId = $transaction->category_id;
    if ($request->filled('categoria')) {
        $category = Category::firstOrCreate([
            'user_id'   => auth()->id(),
            'categoria' => trim($request->categoria),
        ]);
        $categoryId = $category->id;
    } elseif ($request->has('categoria')) {
        // Se envió vacío explícitamente: se quita la categoría.
        $categoryId = null;
    }

    // Si se subió un icono nuevo, se guarda y se borra el anterior
    // (misma carpeta que usa create_transaction en RecordController).
    $nuevoIcono = $transaction->icono;

    try {
        DB::transaction(function () use ($request, $transaction, $categoryId, &$nuevoIcono) {
            if ($request->hasFile('icono')) {
                $nuevoIcono = $request->file('icono')->store('transactions', 'public');
                if ($transaction->icono) {
                    Storage::disk('public')->delete($transaction->icono);
                }
            }

            // Validar fondos suficientes ANTES de tocar nada, si el nuevo monto es
            // mayor al anterior y afecta a una billetera no-crédito que se resta.
            $nuevoMonto = (float) $request->monto;
            $delta = $nuevoMonto - (float) $transaction->monto;

            if ($delta > 0) {
                if ($transaction->tipo === 'gasto' && $transaction->wallet_destino_id) {
                    $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($transaction->wallet_destino_id);
                    if ($wallet && $wallet->tipo !== 'credito' && $wallet->monto_actual < $delta) {
                        throw new \Exception('Fondos insuficientes para aumentar el monto de este gasto.');
                    }
                } elseif ($transaction->tipo === 'transferencia' && $transaction->wallet_origen_id) {
                    $origen = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($transaction->wallet_origen_id);
                    if ($origen && $origen->monto_actual < $delta) {
                        throw new \Exception('Fondos insuficientes en la cuenta origen para aumentar el monto.');
                    }
                }
            }

            // 1. Revertir el efecto del monto ANTERIOR sobre la(s) billetera(s) ligada(s).
            $this->applyTransactionEffect($transaction, -1);

            // 2. Actualizar la transacción con el nuevo monto/título/categoría/icono.
            $transaction->update([
                'titulo'      => $request->titulo,
                'monto'       => $nuevoMonto,
                'category_id' => $categoryId,
                'icono'       => $nuevoIcono,
            ]);

            // 3. Aplicar el efecto del monto NUEVO.
            $this->applyTransactionEffect($transaction->fresh(), 1);
        });
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
    }

    return $this->getTransactionInfo($id);
}

// Eliminar un movimiento
public function deleteTransaction($id)
{
    $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);

    DB::transaction(function () use ($transaction) {
        // Revertir el efecto de la transacción sobre la(s) billetera(s) antes de borrarla,
        // para que monto_actual siga reflejando exactamente el historial restante.
        $this->applyTransactionEffect($transaction, -1);
        $transaction->delete();
    });

    return response()->json([
        'status'  => 'success',
        'message' => 'Movimiento eliminado con éxito.'
    ]);
}
public function getInfo($id)
    {
        $income = Income::with(['wallet', 'category'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'           => $income->id,
                'titulo'       => $income->titulo,
                'icono'        => $income->icono ? asset('storage/' . $income->icono) : asset('images/logo_boosari.webp'),
                'monto'        => number_format($income->monto, 2, '.', ''),
                'frecuencia'   => $income->frecuencia,
                'fecha_inicio' => $income->fecha_inicio ? Carbon::parse($income->fecha_inicio)->format('Y-m-d\TH:i') : null,
                'fecha_f'      => $income->fecha_inicio ? Carbon::parse($income->fecha_inicio)->format('d/m/Y H:i') : 'Sin fecha',
                'activo'       => (bool) $income->activo,
                'wallet'       => $income->wallet->titulo ?? 'Sin billetera',
                'category'     => $income->category->nombre ?? 'Sin categoría',
            ]
        ]);
    }

    // Actualizar el ingreso programado (incluyendo activar/desactivar la ejecución recurrente)
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'       => 'required|string|max:25',
            'monto'        => 'required|numeric|min:0.01',
            'frecuencia'   => 'required|in:ninguno,diario,semanal,quincenal,mensual,anual',
            'fecha_inicio' => 'nullable|date',
            'activo'       => 'required|boolean',
            'icono'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $income = Income::where('user_id', auth()->id())->findOrFail($id);

        // Si se subió un icono nuevo, se guarda y se borra el anterior
        // (misma carpeta que usa create_income en RecordController).
        $nuevoIcono = $income->icono;
        if ($request->hasFile('icono')) {
            $nuevoIcono = $request->file('icono')->store('income_icons', 'public');
            if ($income->icono) {
                Storage::disk('public')->delete($income->icono);
            }
        }

        $income->update([
            'titulo'       => $request->titulo,
            'monto'        => $request->monto,
            'frecuencia'   => $request->frecuencia,
            'fecha_inicio' => $request->fecha_inicio,
            'activo'       => $request->activo,
            'icono'        => $nuevoIcono,
        ]);

        return $this->getInfo($income->id);
    }

    // Eliminar la programación del ingreso
    public function destroy($id)
    {
        $income = Income::where('user_id', auth()->id())->findOrFail($id);
        $income->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Programación de ingreso eliminada correctamente.'
        ]);
    }

    // =========================================================================
    //  METAS (goals) + abonos (payment_goals)
    // =========================================================================

    public function getGoalInfo($id)
    {
        $goal = Goal::with('category')->where('user_id', auth()->id())->findOrFail($id);

        $payments = PaymentGoal::where('goal_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Igual que en getWalletInfo: reconstruimos el saldo hacia atrás
        // partiendo del monto_actual real, para que la gráfica sea consistente
        // aunque se hayan editado montos manualmente entre abonos.
        $runningBalance = (float) $goal->monto_actual;
        $historial = [];
        foreach ($payments as $p) {
            $historial[] = [
                'titulo' => $p->titulo,
                'monto'  => (float) $p->monto,
                'saldo'  => round($runningBalance, 2),
                'fecha'  => $p->created_at ? $p->created_at->format('d/m H:i') : 'N/A',
            ];
            $runningBalance -= $p->monto;
        }
        $historial = array_reverse($historial);

        $objetivo = (float) $goal->monto_objetivo;
        $progreso = $objetivo > 0 ? min(100, round(((float) $goal->monto_actual / $objetivo) * 100, 1)) : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $goal->id,
                'titulo'         => $goal->titulo,
                'icono'          => $goal->icono ? asset('storage/' . $goal->icono) : asset('images/logo_boosari.webp'),
                'descripcion'    => $goal->descripcion,
                'monto_inicial'  => number_format($goal->monto_inicial, 2, '.', ''),
                'monto_actual'   => number_format($goal->monto_actual, 2, '.', ''),
                'monto_objetivo' => number_format($goal->monto_objetivo, 2, '.', ''),
                'progreso'       => $progreso,
                'estado'         => $goal->estado,
                'fecha_limite'   => optional($goal->fecha_limite)->format('Y-m-d'),
                'fecha_limite_f' => $goal->fecha_limite ? Carbon::parse($goal->fecha_limite)->format('d/m/Y') : 'Sin fecha',
                'category'       => $goal->category->nombre ?? 'Sin categoría',
                'historial'      => $historial,
            ]
        ]);
    }

    public function updateGoal(Request $request, $id)
    {
        // Igual que en updatePaymentGoal: si el select manda string vacío
        // para "Externa" en vez de null, se normaliza antes de validar,
        // para que la regla `nullable` de abono.wallet_id funcione bien
        // y no choque contra `exists` con un id vacío.
        if ($request->has('abono')) {
            $abono = $request->input('abono', []);
            // El <x-select> manda el texto de la etiqueta ("Externa") como
            // valor cuando no se elige una billetera real, no un string
            // vacío ni null. Cualquier wallet_id que no sea numérico se
            // trata como "sin billetera" (cuenta externa).
            $abono['wallet_id'] = is_numeric($abono['wallet_id'] ?? null) ? $abono['wallet_id'] : null;
            $request->merge(['abono' => $abono]);
        }

        $request->validate([
            'titulo'          => 'required|string|max:25',
            'monto_objetivo'  => 'required|numeric|min:0.01',
            'fecha_limite'    => 'required|date',
            'descripcion'     => 'nullable|string|max:255',
            'abono'           => 'nullable|array',
            'abono.titulo'    => 'required_with:abono.monto|string|max:25',
            'abono.monto'     => 'required_with:abono.titulo|numeric|min:0.01',
            'abono.wallet_id' => 'nullable|exists:wallets,id,user_id,' . auth()->id(),
            'icono'           => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $goal) {
            $nuevoMontoActual = (float) $goal->monto_actual;

            // Si se subió un icono nuevo, se guarda y se borra el anterior
            // (misma carpeta que usa create_goal en RecordController).
            $nuevoIcono = $goal->icono;
            if ($request->hasFile('icono')) {
                $nuevoIcono = $request->file('icono')->store('goals', 'public');
                if ($goal->icono) {
                    Storage::disk('public')->delete($goal->icono);
                }
            }

            if ($request->filled('abono.monto')) {
                $abono = $request->abono;
                $nuevoMontoActual = round($nuevoMontoActual + $abono['monto'], 2);

                PaymentGoal::create([
                    'goal_id'     => $goal->id,
                    'wallet_id'   => $abono['wallet_id'] ?? null,
                    'category_id' => $goal->category_id,
                    'titulo'      => $abono['titulo'],
                    'monto'       => $abono['monto'],
                ]);

                // Si el abono sale de una billetera propia, se le descuenta el saldo
                // y queda registrado como transacción (antes esto no ocurría en
                // ningún lado: el abono a la meta y el saldo de la billetera podían
                // quedar totalmente desincronizados entre sí).
                if (!empty($abono['wallet_id'])) {
                    $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($abono['wallet_id']);
                    if ($wallet) {
                        // Una tarjeta de crédito no tiene "fondos disponibles" (su
                        // monto_actual es deuda), así que no puede usarse como
                        // origen de un abono.
                        if ($wallet->tipo === 'credito') {
                            throw new \Exception('No puedes usar una tarjeta de crédito como billetera origen para este abono.');
                        }
                        if ($wallet->monto_actual < $abono['monto']) {
                            throw new \Exception('Fondos insuficientes en la billetera seleccionada para este abono.');
                        }
                        $wallet->decrement('monto_actual', $abono['monto']);
                        Transaction::create([
                            'user_id'           => auth()->id(),
                            'titulo'            => 'Abono a meta: ' . $goal->titulo,
                            'monto'             => $abono['monto'],
                            'tipo'              => 'gasto',
                            'wallet_origen_id'  => $wallet->id,
                            'wallet_destino_id' => null,
                        ]);
                    }
                }
            }

            $estado = $goal->estado;
            if ($nuevoMontoActual >= (float) $request->monto_objetivo) {
                $estado = 'completada';
            } elseif ($estado === 'completada' && $nuevoMontoActual < (float) $request->monto_objetivo) {
                $estado = 'activa';
            }

            $goal->update([
                'titulo'         => $request->titulo,
                'monto_objetivo' => $request->monto_objetivo,
                'monto_actual'   => $nuevoMontoActual,
                'fecha_limite'   => $request->fecha_limite,
                'descripcion'    => $request->descripcion,
                'estado'         => $estado,
                'icono'          => $nuevoIcono,
            ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getGoalInfo($id);
    }

    public function deleteGoal($id)
    {
        $goal = Goal::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($goal) {
            // Antes de que la cascada de la BD borre los payment_goals, hay que
            // devolverle a cada billetera de origen el dinero que financió esos
            // abonos; si no, el saldo de esa billetera queda descontado para
            // siempre aunque la meta (y su historial) ya no exista.
            $pagosConWallet = PaymentGoal::where('goal_id', $goal->id)
                ->whereNotNull('wallet_id')
                ->get();

            foreach ($pagosConWallet as $pago) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($pago->wallet_id);
                if ($wallet) {
                    $wallet->increment('monto_actual', $pago->monto);
                }
            }

            $goal->delete(); // payment_goals se eliminan en cascada (ver migración)
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Meta eliminada con éxito.'
        ]);
    }

    // =========================================================================
    //  DEUDAS (debts) + pagos (payment_debts)
    // =========================================================================

    public function getDebtInfo($id)
    {
        $debt = Debt::with('category')->where('user_id', auth()->id())->findOrFail($id);

        $payments = PaymentDebt::where('debt_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Reconstrucción hacia atrás: cada pago REDUCE la deuda hacia adelante,
        // así que para reconstruir el saldo anterior hay que sumarlo de vuelta.
        $runningBalance = (float) $debt->monto_actual;
        $historial = [];
        foreach ($payments as $p) {
            $historial[] = [
                'titulo' => $p->titulo,
                'monto'  => (float) $p->monto,
                'saldo'  => round($runningBalance, 2),
                'fecha'  => $p->created_at ? $p->created_at->format('d/m H:i') : 'N/A',
            ];
            $runningBalance += $p->monto;
        }
        $historial = array_reverse($historial);

        $inicial = (float) $debt->monto_inicial;
        $pagadoPct = $inicial > 0
            ? min(100, round((($inicial - (float) $debt->monto_actual) / $inicial) * 100, 1))
            : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                  => $debt->id,
                'titulo'              => $debt->titulo,
                'icono'               => $debt->icono ? asset('storage/' . $debt->icono) : asset('images/logo_boosari.webp'),
                'monto_inicial'       => number_format($debt->monto_inicial, 2, '.', ''),
                'monto_actual'        => number_format($debt->monto_actual, 2, '.', ''),
                'pagado_pct'          => $pagadoPct,
                'tasa_interes'        => number_format($debt->tasa_interes, 2, '.', ''),
                'prioridad'           => $debt->prioridad,
                'estado'              => $debt->estado,
                'fecha_vencimiento'   => optional($debt->fecha_vencimiento)->format('Y-m-d'),
                'fecha_vencimiento_f' => $debt->fecha_vencimiento ? Carbon::parse($debt->fecha_vencimiento)->format('d/m/Y') : 'Sin fecha',
                'category'            => $debt->category->nombre ?? 'Sin categoría',
                'historial'           => $historial,
            ]
        ]);
    }

    public function updateDebt(Request $request, $id)
    {
        // Mismo fix que en updateGoal: normalizar pago.wallet_id vacío a
        // null antes de validar, para que "Externa" no choque contra
        // `exists`.
        if ($request->has('pago')) {
            $pago = $request->input('pago', []);
            // Mismo problema que en updateGoal: el <x-select> manda
            // "Externa" literal como valor, no vacío ni null.
            $pago['wallet_id'] = is_numeric($pago['wallet_id'] ?? null) ? $pago['wallet_id'] : null;
            $request->merge(['pago' => $pago]);
        }

        $request->validate([
            'titulo'            => 'required|string|max:25',
            'tasa_interes'      => 'nullable|numeric|min:0',
            'prioridad'         => 'required|in:media,alta,baja',
            'fecha_vencimiento' => 'required|date',
            'pago'              => 'nullable|array',
            'pago.titulo'       => 'required_with:pago.monto|string|max:25',
            'pago.monto'        => 'required_with:pago.titulo|numeric|min:0.01',
            'pago.wallet_id'    => 'nullable|exists:wallets,id,user_id,' . auth()->id(),
            'pago.pago_minimo'  => 'nullable|boolean',
            'icono'             => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $debt) {
            $nuevoMontoActual = (float) $debt->monto_actual;

            // Si se subió un icono nuevo, se guarda y se borra el anterior
            // (misma carpeta que usa create_debt en RecordController).
            $nuevoIcono = $debt->icono;
            if ($request->hasFile('icono')) {
                $nuevoIcono = $request->file('icono')->store('debts', 'public');
                if ($debt->icono) {
                    Storage::disk('public')->delete($debt->icono);
                }
            }

            if ($request->filled('pago.monto')) {
                $pago = $request->pago;

                // No permitir abonar más de lo que realmente resta de deuda.
                $montoPago = min((float) $pago['monto'], $nuevoMontoActual);
                $nuevoMontoActual = round($nuevoMontoActual - $montoPago, 2);

                PaymentDebt::create([
                    'debt_id'     => $debt->id,
                    'wallet_id'   => $pago['wallet_id'] ?? null,
                    'category_id' => $debt->category_id,
                    'titulo'      => $pago['titulo'],
                    'monto'       => $montoPago,
                    'pago_minimo' => (bool) ($pago['pago_minimo'] ?? false),
                ]);

                // Igual que con las metas: si el pago sale de una billetera propia,
                // se le descuenta el saldo y queda como transacción real.
                if (!empty($pago['wallet_id'])) {
                    $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($pago['wallet_id']);
                    if ($wallet) {
                        if ($wallet->tipo === 'credito') {
                            throw new \Exception('No puedes usar una tarjeta de crédito como billetera origen para pagar una deuda.');
                        }
                        if ($wallet->monto_actual < $montoPago) {
                            throw new \Exception('Fondos insuficientes en la billetera seleccionada para este pago.');
                        }
                        $wallet->decrement('monto_actual', $montoPago);
                        Transaction::create([
                            'user_id'           => auth()->id(),
                            'titulo'            => 'Pago de deuda: ' . $debt->titulo,
                            'monto'             => $montoPago,
                            'tipo'              => 'gasto',
                            'wallet_origen_id'  => $wallet->id,
                            'wallet_destino_id' => null,
                        ]);
                    }
                }
            }

            $debt->update([
                'titulo'            => $request->titulo,
                'tasa_interes'      => $request->tasa_interes,
                'prioridad'         => $request->prioridad,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'monto_actual'      => $nuevoMontoActual,
                'estado'            => $nuevoMontoActual <= 0 ? 'pagada' : 'pendiente',
                'icono'             => $nuevoIcono,
            ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getDebtInfo($id);
    }

    public function deleteDebt($id)
    {
        $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($debt) {
            // Igual que en deleteGoal: antes de que la cascada borre los
            // payment_debts, se le regresa a cada billetera de origen el
            // dinero que se usó para pagar, para no dejar el saldo descontado
            // permanentemente.
            $pagosConWallet = PaymentDebt::where('debt_id', $debt->id)
                ->whereNotNull('wallet_id')
                ->get();

            foreach ($pagosConWallet as $pago) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($pago->wallet_id);
                if ($wallet) {
                    $wallet->increment('monto_actual', $pago->monto);
                }
            }

            $debt->delete(); // payment_debts se eliminan en cascada (ver migración)
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Deuda eliminada con éxito.'
        ]);
    }

    // =========================================================================
    //  ABONOS A METAS (payment_goals) — edición/borrado individual
    // =========================================================================

    public function getPaymentGoalInfo($id)
    {
        $payment = PaymentGoal::with(['goal', 'wallet'])
            ->whereHas('goal', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'          => $payment->id,
                'titulo'      => $payment->titulo,
                'icono'       => $payment->icono ? asset('storage/' . $payment->icono) : asset('images/logo_boosari.webp'),
                'monto'       => number_format($payment->monto, 2, '.', ''),
                'goal_id'     => $payment->goal_id,
                'goal_titulo' => $payment->goal->titulo ?? 'Meta no encontrada',
                'wallet_id'   => $payment->wallet_id,
                'wallet'      => $payment->wallet->titulo ?? 'Externa',
                'fecha'       => $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A',
            ]
        ]);
    }

    public function updatePaymentGoal(Request $request, $id)
    {
        $request->merge([
        // El <x-select> manda "Externa" literal, no vacío ni null, cuando
        // no se elige una billetera real.
        'wallet_id' => is_numeric($request->wallet_id) ? $request->wallet_id : null,
        ]);

        $request->validate([
            'titulo'      => 'required|string|max:25',
            'monto'       => 'required|numeric|min:0.01',
            'wallet_id'   => 'nullable|exists:wallets,id,user_id,' . auth()->id(),
            'pago_minimo' => 'nullable|boolean',
            'icono'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $payment = PaymentGoal::whereHas('goal', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $payment) {
            $goal = Goal::where('user_id', auth()->id())->lockForUpdate()->findOrFail($payment->goal_id);

            // Si se subió un icono nuevo, se guarda y se borra el anterior
            // (misma carpeta que usa create_payment_goal en RecordController).
            $nuevoIcono = $payment->icono;
            if ($request->hasFile('icono')) {
                $nuevoIcono = $request->file('icono')->store('comprobantes_metas', 'public');
                if ($payment->icono) {
                    Storage::disk('public')->delete($payment->icono);
                }
            }

            $oldMonto    = (float) $payment->monto;
            $oldWalletId = $payment->wallet_id;
            $newMonto    = (float) $request->monto;
            $newWalletId = $request->wallet_id ?: null;

            // Se revierte el abono anterior sobre el saldo de la meta y se aplica el nuevo.
            $nuevoMontoActual = max(0, round((float) $goal->monto_actual - $oldMonto + $newMonto, 2));

            // Se revierte el efecto en la billetera anterior (si abonó desde una).
            if ($oldWalletId) {
                $walletAnterior = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($oldWalletId);
                if ($walletAnterior) {
                    $walletAnterior->increment('monto_actual', $oldMonto);
                }
            }

            // Se aplica el efecto en la billetera nueva (si aplica).
            if ($newWalletId) {
                $walletNueva = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($newWalletId);
                if ($walletNueva) {
                    // Una tarjeta de crédito no tiene "fondos disponibles" (su
                    // monto_actual es deuda): antes solo se saltaba la validación
                    // de fondos pero igual se le descontaba el monto.
                    if ($walletNueva->tipo === 'credito') {
                        throw new \Exception('No puedes usar una tarjeta de crédito como billetera origen para este abono.');
                    }
                    if ($walletNueva->monto_actual < $newMonto) {
                        throw new \Exception('Fondos insuficientes en la billetera de origen elegida.');
                    }
                    $walletNueva->decrement('monto_actual', $newMonto);
                }
            }

            $estado = $goal->estado;
            if ($nuevoMontoActual >= (float) $goal->monto_objetivo) {
                $estado = 'completada';
            } elseif ($estado === 'completada' && $nuevoMontoActual < (float) $goal->monto_objetivo) {
                $estado = 'activa';
            }

            $goal->update([
                'monto_actual' => $nuevoMontoActual,
                'estado'       => $estado,
            ]);

            $payment->update([
                'titulo'    => $request->titulo,
                'monto'     => $newMonto,
                'wallet_id' => $newWalletId,
                'icono'     => $nuevoIcono,
            ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getPaymentGoalInfo($id);
    }

    public function deletePaymentGoal($id)
    {
        $payment = PaymentGoal::whereHas('goal', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        DB::transaction(function () use ($payment) {
            $goal = Goal::where('user_id', auth()->id())->lockForUpdate()->findOrFail($payment->goal_id);

            $nuevoMontoActual = max(0, round((float) $goal->monto_actual - (float) $payment->monto, 2));

            // Se revierte el efecto en la billetera de origen, si tuvo una.
            if ($payment->wallet_id) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($payment->wallet_id);
                if ($wallet) {
                    $wallet->increment('monto_actual', $payment->monto);
                }
            }

            $estado = $goal->estado;
            if ($estado === 'completada' && $nuevoMontoActual < (float) $goal->monto_objetivo) {
                $estado = 'activa';
            }

            $goal->update([
                'monto_actual' => $nuevoMontoActual,
                'estado'       => $estado,
            ]);

            $payment->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Abono eliminado con éxito.'
        ]);
    }

    // =========================================================================
    //  PAGOS A DEUDAS (payment_debts) — edición/borrado individual
    // =========================================================================

    public function getPaymentDebtInfo($id)
    {
        $payment = PaymentDebt::with(['debt', 'wallet'])
            ->whereHas('debt', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'          => $payment->id,
                'titulo'      => $payment->titulo,
                'icono'       => $payment->icono ? asset('storage/' . $payment->icono) : asset('images/logo_boosari.webp'),
                'monto'       => number_format($payment->monto, 2, '.', ''),
                'pago_minimo' => (bool) $payment->pago_minimo,
                'debt_id'     => $payment->debt_id,
                'debt_titulo' => $payment->debt->titulo ?? 'Deuda no encontrada',
                'wallet_id'   => $payment->wallet_id,
                'wallet'      => $payment->wallet->titulo ?? 'Externa',
                'fecha'       => $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A',
            ]
        ]);
    }

    public function updatePaymentDebt(Request $request, $id)
    {
        $request->merge([
        // El <x-select> manda "Externa" literal, no vacío ni null, cuando
        // no se elige una billetera real.
        'wallet_id' => is_numeric($request->wallet_id) ? $request->wallet_id : null,
        ]);

        $request->validate([
            'titulo'      => 'required|string|max:25',
            'monto'       => 'required|numeric|min:0.01',
            'wallet_id'   => 'nullable|exists:wallets,id,user_id,' . auth()->id(),
            'pago_minimo' => 'nullable|boolean',
            'icono'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $payment = PaymentDebt::whereHas('debt', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $payment) {
            $debt = Debt::where('user_id', auth()->id())->lockForUpdate()->findOrFail($payment->debt_id);

            // Si se subió un icono nuevo, se guarda y se borra el anterior
            // (misma carpeta que usa create_paymentdebt en RecordController).
            $nuevoIcono = $payment->icono;
            if ($request->hasFile('icono')) {
                $nuevoIcono = $request->file('icono')->store('comprobantes_deudas', 'public');
                if ($payment->icono) {
                    Storage::disk('public')->delete($payment->icono);
                }
            }

            $oldMonto    = (float) $payment->monto;
            $oldWalletId = $payment->wallet_id;
            $newMonto    = (float) $request->monto;
            $newWalletId = $request->wallet_id ?: null;

            // Se revierte el pago anterior sobre la deuda (se le regresa el monto) y se aplica el nuevo.
            $nuevoMontoActual = round((float) $debt->monto_actual + $oldMonto - $newMonto, 2);
            $nuevoMontoActual = max(0, min($nuevoMontoActual, (float) $debt->monto_inicial));

            if ($oldWalletId) {
                $walletAnterior = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($oldWalletId);
                if ($walletAnterior) {
                    $walletAnterior->increment('monto_actual', $oldMonto);
                }
            }

            if ($newWalletId) {
                $walletNueva = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($newWalletId);
                if ($walletNueva) {
                    // Una tarjeta de crédito no tiene "fondos disponibles" (su
                    // monto_actual es deuda): antes solo se saltaba la validación
                    // de fondos pero igual se le descontaba el monto.
                    if ($walletNueva->tipo === 'credito') {
                        throw new \Exception('No puedes usar una tarjeta de crédito como billetera origen para este pago.');
                    }
                    if ($walletNueva->monto_actual < $newMonto) {
                        throw new \Exception('Fondos insuficientes en la billetera de origen elegida.');
                    }
                    $walletNueva->decrement('monto_actual', $newMonto);
                }
            }

            $debt->update([
                'monto_actual' => $nuevoMontoActual,
                'estado'       => $nuevoMontoActual <= 0 ? 'pagada' : 'pendiente',
            ]);

            $payment->update([
                'titulo'      => $request->titulo,
                'monto'       => $newMonto,
                'wallet_id'   => $newWalletId,
                'pago_minimo' => (bool) ($request->pago_minimo ?? false),
                'icono'       => $nuevoIcono,
            ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getPaymentDebtInfo($id);
    }

    public function deletePaymentDebt($id)
    {
        $payment = PaymentDebt::whereHas('debt', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        DB::transaction(function () use ($payment) {
            $debt = Debt::where('user_id', auth()->id())->lockForUpdate()->findOrFail($payment->debt_id);

            $nuevoMontoActual = round((float) $debt->monto_actual + (float) $payment->monto, 2);
            $nuevoMontoActual = min($nuevoMontoActual, (float) $debt->monto_inicial);

            if ($payment->wallet_id) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($payment->wallet_id);
                if ($wallet) {
                    $wallet->increment('monto_actual', $payment->monto);
                }
            }

            $debt->update([
                'monto_actual' => $nuevoMontoActual,
                'estado'       => $nuevoMontoActual <= 0 ? 'pagada' : 'pendiente',
            ]);

            $payment->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Pago eliminado con éxito.'
        ]);
    }

    // =========================================================================
    //  INVERSIONES (investments)
    // =========================================================================

    public function getInvestmentInfo($id)
    {
        $investment = Investment::with(['category', 'wallet'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $inicial = (float) $investment->monto_inicial;
        $actual  = (float) $investment->valor_actual;
        $ganancia = round($actual - $inicial, 2);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                   => $investment->id,
                'titulo'               => $investment->titulo,
                'icono'                => $investment->icono ? asset('storage/' . $investment->icono) : asset('images/logo_boosari.webp'),
                'monto_inicial'        => number_format($inicial, 2, '.', ''),
                'valor_actual'         => number_format($actual, 2, '.', ''),
                'ganancia'             => number_format($ganancia, 2, '.', ''),
                'tipo_renta'           => $investment->tipo_renta,
                'tasa_interes'         => $investment->tasa_interes,
                'estado'               => $investment->estado,
                'fecha_adquisicion'    => optional($investment->fecha_adquisicion)->format('Y-m-d'),
                'fecha_adquisicion_f'  => $investment->fecha_adquisicion ? Carbon::parse($investment->fecha_adquisicion)->format('d/m/Y') : 'Sin fecha',
                'fecha_vencimiento'    => optional($investment->fecha_vencimiento)->format('Y-m-d'),
                'fecha_vencimiento_f'  => $investment->fecha_vencimiento ? Carbon::parse($investment->fecha_vencimiento)->format('d/m/Y') : 'Sin fecha',
                'wallet'               => $investment->wallet->titulo ?? 'Sin billetera',
                'category'             => $investment->category->nombre ?? 'Sin categoría',
            ]
        ]);
    }

    public function updateInvestment(Request $request, $id)
    {
        $request->validate([
            'titulo'            => 'required|string|max:25',
            'monto_inicial'     => 'required|numeric|min:0.01',
            'valor_actual'      => 'required|numeric|min:0',
            'tasa_interes'      => 'nullable|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'estado'            => 'required|in:activa,finalizada,cancelada',
            'icono'             => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $investment = Investment::where('user_id', auth()->id())->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $investment) {
                // Si se subió un icono nuevo, se guarda y se borra el anterior
                // (misma carpeta que usa investment_create en RecordController).
                $nuevoIcono = $investment->icono;
                if ($request->hasFile('icono')) {
                    $nuevoIcono = $request->file('icono')->store('investments', 'public');
                    if ($investment->icono) {
                        Storage::disk('public')->delete($investment->icono);
                    }
                }

                $nuevoMontoInicial = (float) $request->monto_inicial;
                $montoInicialAnterior = (float) $investment->monto_inicial;
                $diferencia = round($nuevoMontoInicial - $montoInicialAnterior, 2);

                // Si el monto invertido cambia, hay que reflejarlo en la billetera
                // de origen: si sube, se le descuenta la diferencia (como al crear);
                // si baja, se le devuelve la diferencia (como al eliminar).
                if ($diferencia !== 0.0 && $investment->wallet_id) {
                    $wallet = Wallet::where('user_id', auth()->id())
                        ->lockForUpdate()
                        ->find($investment->wallet_id);

                    if ($wallet) {
                        if ($diferencia > 0) {
                            if ($wallet->monto_actual < $diferencia) {
                                throw new \Exception('Fondos insuficientes en la billetera origen para aumentar el monto invertido.');
                            }
                            $wallet->decrement('monto_actual', $diferencia);
                        } else {
                            $wallet->increment('monto_actual', abs($diferencia));
                        }
                    }
                }

                $nuevoValorActual = (float) $request->valor_actual;
                $ganancia = round($nuevoValorActual - $nuevoMontoInicial, 2);

                $investment->update([
                    'titulo'            => $request->titulo,
                    'monto_inicial'     => $nuevoMontoInicial,
                    'valor_actual'      => $nuevoValorActual,
                    'ganancia'          => $ganancia,
                    'tasa_interes'      => $investment->tipo_renta === 'fija' ? $request->tasa_interes : null,
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'estado'            => $request->estado,
                    'icono'             => $nuevoIcono,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return $this->getInvestmentInfo($id);
    }

    public function deleteInvestment($id)
    {
        $investment = Investment::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($investment) {
            // Se le regresa a la billetera de origen el monto originalmente
            // invertido, igual que se le descontó en investment_create.
            if ($investment->wallet_id) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->find($investment->wallet_id);
                if ($wallet) {
                    $wallet->increment('monto_actual', $investment->monto_inicial);
                }
            }

            if ($investment->icono) {
                Storage::disk('public')->delete($investment->icono);
            }

            $investment->delete();
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Inversión eliminada con éxito.'
        ]);
    }
}