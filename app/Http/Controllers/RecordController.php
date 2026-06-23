<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Income;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function index()
    {
        // 1. Cargamos las billeteras para los listados de la vista
        $wallets = Wallet::where('user_id', Auth::id())->get();

        // 2. Traemos las transacciones con doble ordenamiento: por fecha unificada y desempatando por ID descendente
        $transactions = Transaction::with(['walletOrigen', 'walletDestino', 'category'])
            ->where('user_id', Auth::id())
            ->orderBy(DB::raw('COALESCE(fecha_ejecucion, created_at)'), 'desc')
            ->orderBy('id', 'desc')
            ->paginate(9);

        return view('home', compact('wallets', 'transactions'));
    }

    public function create_wallet(Request $request)
    {
        $request->validate([
            'wallet-titulo' => 'required|max:25',
            'wallet-monto' => 'required|numeric|min:0',
            'wallet-image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'wallet-select-value' => 'required'
        ]);

        $ruta = null;
        if($request->hasFile('wallet-image')){
            $ruta = $request->file('wallet-image')->store('wallet_icons', 'public');
        }

        Wallet::create([
            'user_id' => Auth::id(),
            'titulo' => $request->input('wallet-titulo'),
            'icono' => $ruta,
            'tipo' => strtolower($request->input('wallet-select-value')),
            'monto_actual' => $request->input('wallet-monto'),
            'monto_inicial' => $request->input('wallet-monto')
        ]);

        return back()->with('success', 'Billetera creada correctamente');
    }

    public function create_transaction(Request $request)
    {
        $request->validate([
            'transaction-titulo' => 'required|max:25',
            'transaction-category' => 'required|max:25',
            'transaction-monto' => 'required|numeric|min:0.01',
            'transaction-select-value' => 'required',
            'transaction-origen-select-value' => 'nullable',
            'transaction-destino-select-value' => 'required',
            'transaction-image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096'
        ]);

        DB::beginTransaction();

        try {
            $ruta = null;
            if ($request->hasFile('transaction-image')) {
                $ruta = $request->file('transaction-image')->store('transactions', 'public');
            }

            $tipo = strtolower($request->input('transaction-select-value'));
            $monto = $request->input('transaction-monto');
            $walletOrigenNombre = $request->input('transaction-origen-select-value');
            $walletDestinoNombre = $request->input('transaction-destino-select-value');
            
            $nombreCategoria = trim($request->input('transaction-category'));

            // Buscar o crear categoría
            $category = Category::firstOrCreate([
                'user_id' => Auth::id(),
                'categoria' => $nombreCategoria
            ]);

            // SOLUCIÓN AL ERROR: Buscamos las billeteras usando su título string de forma segura
            $origen = null;
            $destino = null;

            if ($tipo === 'ingreso') {
                $destino = Wallet::where('user_id', Auth::id())->where('titulo', $walletDestinoNombre)->firstOrFail();
                $destino->increment('monto_actual', $monto);

            } elseif ($tipo === 'gasto') {
                $destino = Wallet::where('user_id', Auth::id())->where('titulo', $walletDestinoNombre)->firstOrFail();

                if ($destino->monto_actual < $monto) {
                    throw new \Exception('Fondos insuficientes');
                }
                $destino->decrement('monto_actual', $monto);

            } elseif ($tipo === 'transferencia') {
                $origen = Wallet::where('user_id', Auth::id())->where('titulo', $walletOrigenNombre)->firstOrFail();
                $destino = Wallet::where('user_id', Auth::id())->where('titulo', $walletDestinoNombre)->firstOrFail();

                if ($origen->monto_actual < $monto) {
                    throw new \Exception('Fondos insuficientes');
                }

                $origen->decrement('monto_actual', $monto);
                $destino->increment('monto_actual', $monto);
            }

            // Registrar la transacción guardando los IDs correctos que encontramos
            Transaction::create([
                'user_id' => Auth::id(),
                'wallet_origen_id' => $origen ? $origen->id : null,
                'wallet_destino_id' => $destino ? $destino->id : null,
                'category_id' => $category->id,
                'titulo' => $request->input('transaction-titulo'),
                'icono' => $ruta,
                'monto' => $monto,
                'tipo' => $tipo,
                'descripcion' => null
            ]);

            DB::commit();
            return back()->with('success', 'Movimiento creado');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }
public function create_income(Request $request)
    {
        $request->validate([
            'income-titulo' => 'required|max:25',
            'income-category' => 'required|max:25',
            'income-monto' => 'required|numeric|min:0.01',
            'income-wallet-select-value' => 'nullable',
            'income-select-value' => 'required',
            'income-image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $frecuencia = strtolower(trim($request->input('income-select-value')));
        
        if ($frecuencia != 'ninguno') {
            $request->validate([
                'income-ejecucion' => [
                    'required',
                    'date',
                    'after_or_equal:1970-01-01',
                    'before_or_equal:' . now()->addYears(10)->format('Y-m-d')
                ]
            ]);
        }

        $ruta = null;
        if ($request->hasFile('income-image')) {
            $ruta = $request->file('income-image')->store('income_icons', 'public');
        }

        $category = Category::firstOrCreate([
            'user_id' => Auth::id(),
            'categoria' => trim($request->input('income-category'))
        ]);

        // Buscar billetera por su nombre
        $walletId = null;
        $rawValue = $request->input('income-wallet-select-value');

        // Validamos que venga un ID numérico válido y no la palabra 'Ninguno'
        if ($request->filled('income-wallet-select-value') && strtolower($rawValue) !== 'ninguno') {
            $walletId = intval($rawValue);
        }

        $fechaInicio = $request->filled('income-ejecucion') 
            ? \Carbon\Carbon::parse($request->input('income-ejecucion'))->startOfDay() 
            : now()->startOfDay();

        DB::beginTransaction();

        try {
            $income = Income::create([
                'user_id' => Auth::id(),
                'wallet_id' => $walletId,
                'category_id' => $category->id,
                'titulo' => $request->input('income-titulo'),
                'icono' => $ruta,
                'monto' => $request->input('income-monto'),
                'frecuencia' => $frecuencia,
                'fecha_inicio' => $fechaInicio->format('Y-m-d H:i:s'),
                'activo' => $frecuencia != 'ninguno', 
            ]);

            $hoy = now()->startOfDay();
            $fechaIteracion = $fechaInicio->copy();

            if ($fechaIteracion->lte($hoy)) {
                do {
                    Transaction::create([
                        'user_id' => Auth::id(),
                        'wallet_origen_id' => null,
                        'wallet_destino_id' => $walletId,
                        'category_id' => $category->id,
                        'income_id' => $income->id,
                        'titulo' => $income->titulo,
                        'icono' => $ruta,
                        'monto' => $income->monto,
                        'tipo' => 'ingreso',
                        'fecha_ejecucion' => $fechaIteracion->format('Y-m-d H:i:s')
                    ]);

                    if ($walletId) {
                        Wallet::where('id', $walletId)
                            ->where('user_id', Auth::id())
                            ->increment('monto_actual', $income->monto);
                    }

                    if ($frecuencia === 'ninguno') {
                        break;
                    }

                    $fechaIteracion = $this->avanzarFecha($fechaIteracion, $frecuencia)->startOfDay();

                } while ($fechaIteracion->lte($hoy));
            }

            if ($frecuencia !== 'ninguno') {
                $income->fecha_inicio = $fechaIteracion->format('Y-m-d H:i:s'); 
                $income->save();
            }

            DB::commit();
            
            // Ya funciona todo bien, regresamos el redireccionamiento normal quitando el dd()
            return back()->with('success', 'Ingreso creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function avanzarFecha($fecha, $frecuencia) {
        switch ($frecuencia) {
            case 'diario': return $fecha->addDay();
            case 'semanal': return $fecha->addWeek();
            case 'quincenal': return $fecha->addDays(15);
            case 'mensual': return $fecha->addMonth();
            case 'anual': return $fecha->addYear();
            default: return $fecha;
        }
    }
}