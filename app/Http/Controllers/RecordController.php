<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Income;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\Goal;
use App\Models\PaymentGoal;
use App\Models\Debt;
use App\Models\PaymentDebt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $investments = \App\Models\Investment::with(['category', 'wallet'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(9, ['*'], 'investments_page');


        // 1. Billeteras (Paginadas para que no rompan con ->links() y manejables por JS)
        $wallets = Wallet::where('user_id', $userId)
            ->latest()
            ->paginate(6, ['*'], 'wallets_page');

        // 2. Ingresos programados
        $incomes = Income::with(['wallet', 'category'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(6, ['*'], 'incomes_page');

        // 3. Transacciones principales
        $transactions = Transaction::with(['walletOrigen', 'walletDestino', 'category'])
            ->where('user_id', $userId)
            ->orderBy(DB::raw('COALESCE(fecha_ejecucion, created_at)'), 'desc')
            ->orderBy('id', 'desc')
            ->paginate(9, ['*'], 'transactions_page');

        $goals = \App\Models\Goal::with(['category'])
            ->where('user_id', $userId)
            ->orderByRaw("FIELD(estado, 'activa', 'completada', 'expirada')") // Prioriza las activas arriba
            ->latest()
            ->paginate(9, ['*'], 'goals_page');

        $paymentGoals = \App\Models\PaymentGoal::with(['goal', 'wallet', 'category'])
            ->whereHas('goal', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
        ->latest()
        ->paginate(9, ['*'], 'payment_goals_page');
        $debts = \App\Models\Debt::with(['category']) // <-- Agregamos la relación aquí
        ->where('user_id', $userId)
        ->orderByRaw("FIELD(estado, 'pendiente', 'pagada')")
        ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
        ->orderBy('fecha_vencimiento', 'asc') 
        ->paginate(6, ['*'], 'debts_page');
        $paymentDebts = \App\Models\PaymentDebt::with(['debt', 'wallet', 'category'])
            ->whereHas('debt', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->paginate(9, ['*'], 'payment_debts_page');

        // Y no olvides agregar 'paymentDebts' al compact final:
        return view('home', compact('wallets', 'transactions', 'incomes', 'goals', 'debts', 'investments', 'paymentGoals', 'paymentDebts'));
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
        // 1. Validaciones adaptadas a los nombres reales que viajan desde tus componentes Blade
        $request->validate([
            'transaction-titulo'                       => 'required|string|max:25',
            'transaction-category'                     => 'required|string|max:25',
            'transaction-monto'                        => 'required|numeric|min:0.01',
            'transaction-select-value'                 => 'required|string|in:Ingreso,Gasto,Transferencia',
            'transaction-destino-ingreso-select-value' => 'nullable|string', // Se usa para Ingreso
            'transaction-destino-gasto-select-value'   => 'nullable|string', // Se usa para Gasto
            'transaction-origen-select-value'          => 'nullable|string', // Se usa para Transferencia (Origen)
            'transaction-destino-select-value'         => 'nullable|string', // Se usa para Transferencia (Destino)
            'transaction-image'                        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096'
        ]);

        DB::beginTransaction();

        try {
            $ruta = null;
            if ($request->hasFile('transaction-image')) {
                $ruta = $request->file('transaction-image')->store('transactions', 'public');
            }

            $monto = $request->input('transaction-monto');
            $tipo = strtolower($request->input('transaction-select-value'));

            // Inicializar variables de billeteras
            $walletOrigenId = null;
            $walletDestinoId = null;

            // 2. Mapeo inteligente según la naturaleza dinámica de los bloques del Front-end
            if ($tipo === 'ingreso') {
                $walletDestinoId = $request->input('transaction-destino-ingreso-select-value');
            } elseif ($tipo === 'gasto') {
                // En los gastos, la cuenta de donde sale el dinero actúa técnicamente como destino/pago
                $walletDestinoId = $request->input('transaction-destino-gasto-select-value');
            } elseif ($tipo === 'transferencia') {
                $walletOrigenId = $request->input('transaction-origen-select-value');
                $walletDestinoId = $request->input('transaction-destino-select-value');
                
                if ($walletOrigenId === 'Externa') {
                    $walletOrigenId = null;
                }
            }

            // 3. Crear u obtener la categoría del movimiento
            $category = Category::firstOrCreate([
                'user_id'   => Auth::id(),
                'categoria' => trim($request->input('transaction-category'))
            ]);

            // 4. Cargar y verificar las instancias de billetera vinculadas al usuario
            $origen = $walletOrigenId ? Wallet::where('user_id', Auth::id())->findOrFail($walletOrigenId) : null;
            $destino = $walletDestinoId ? Wallet::where('user_id', Auth::id())->findOrFail($walletDestinoId) : null;

            // Validaciones de reglas de negocio financieras
            if (!$destino) {
                throw new \Exception('Debe seleccionar una billetera válida para completar la operación.');
            }

            if ($tipo === 'ingreso' && $destino->tipo === 'credito') {
                return back()->withErrors(['error' => 'Operación inválida: No se pueden registrar ingresos directos a una tarjeta de crédito.']);
            }

            if ($tipo === 'transferencia' && $origen && $origen->tipo === 'credito') {
                return back()->withErrors(['error' => 'Operación inválida: No puedes transferir fondos usando una tarjeta de crédito como origen.']);
            }

            // 5. Ejecutar operaciones matemáticas de balances
            if ($tipo === 'ingreso') {
                $destino->increment('monto_actual', $monto);

            } elseif ($tipo === 'gasto') {
                if ($destino->tipo === 'credito') {
                    // En tarjetas de crédito los gastos incrementan la deuda global
                    $destino->increment('monto_actual', $monto);
                } else {
                    if ($destino->monto_actual < $monto) {
                        throw new \Exception('Fondos insuficientes en la cuenta elegida.');
                    }
                    $destino->decrement('monto_actual', $monto);
                }

            } elseif ($tipo === 'transferencia') {
                if ($origen) {
                    if ($origen->monto_actual < $monto) {
                        throw new \Exception('Fondos insuficientes en la cuenta origen.');
                    }
                    $origen->decrement('monto_actual', $monto);
                }

                if ($destino->tipo === 'credito') {
                    // Si el destino es crédito, la transferencia mitiga/amortiza la deuda (resta)
                    $destino->decrement('monto_actual', $monto);
                } else {
                    $destino->increment('monto_actual', $monto);
                }
            }

            // 6. Registrar la auditoría del Movimiento
            Transaction::create([
                'user_id'           => Auth::id(),
                'wallet_origen_id'  => $origen ? $origen->id : null,
                'wallet_destino_id' => $destino ? $destino->id : null,
                'category_id'       => $category->id,
                'titulo'            => $request->input('transaction-titulo'),
                'icono'             => $ruta,
                'monto'             => $monto,
                'tipo'              => $tipo,
                'descripcion'       => null
            ]);

            DB::commit();
            return back()->with('success', 'Movimiento procesado de manera correcta.');

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

    public function investment_create(Request $request)
    {
        // 1. Validación estricta usando las claves de tus inputs
        $request->validate([
            'investment-titulo'              => 'required|max:25',
            'investment-category'            => 'nullable|max:50',
            'investment-monto'               => 'required|numeric|min:0.01',
            'investment-wallet-select-value' => 'required', 
            'investment-renta-select-value'  => 'required|in:fija,variable', 
            'investment-tasa'                => 'required_if:investment-renta-select-value,fija|nullable|numeric|min:0',
            'investment-vencimiento'         => 'required|date',
            'investment'                     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);


        DB::beginTransaction();

        try {
            // Procesar subida de archivo
            $ruta = null;
            if ($request->hasFile('investment')) {
                $ruta = $request->file('investment')->store('investments', 'public');
            }

            $monto = $request->input('investment-monto');
            $walletId = $request->input('investment-wallet-select-value');
            $tipoRenta = strtolower($request->input('investment-renta-select-value'));

            // Obtener la billetera origen
            $wallet = Wallet::where('user_id', Auth::id())->findOrFail($walletId);

            // Comprobación de fondos
            if ($wallet->monto_actual < $monto) {
                throw new \Exception('Fondos insuficientes en la billetera origen elegida.');
            }

            Investment::create([
                'user_id'           => Auth::id(),
                'wallet_id'         => $wallet->id,
                'titulo'            => $request->input('investment-titulo'),
                // 'categoria' no existe en tu migración directa (tienes category_id), 
                // si manejas la string en 'icono' o usas un string temporal, lo asignamos a icono:
                'icono'             => $ruta, 
                'monto_inicial'     => $monto,
                'valor_actual'      => $monto, // 
                'ganancia'          => 0.00,
                'tipo_renta'        => $tipoRenta,
                'tasa_interes'      => ($tipoRenta === 'fija') ? $request->input('investment-tasa') : null,
                'fecha_vencimiento' => $request->input('investment-vencimiento'),
                'estado'            => 'activa',
            ]);

            // 4. Afectar el saldo de la billetera origen
            $wallet->decrement('monto_actual', $monto);

            DB::commit();
            return back()->with('success', 'Inversión registrada y procesada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar la inversión: ' . $e->getMessage()]);
        }
        
        
    }
   public function create_goal(Request $request)
    {
        // 1. Validar usando las claves exactas de tu dd()
        $request->validate([
            'goal-titulo'         => 'required|string|max:25',
            'goal-category'       => 'nullable|string|max:25',
            'goal-monto-objetivo' => 'required|numeric|min:0.01',
            'goal-monto-inicial'  => 'nullable|numeric|min:0',
            'goal-fecha-limite'   => 'required|date', // Simplificado para evitar bloqueos por zona horaria
            'goal-descripcion'    => 'nullable|string|max:500',
        ]);

        // Forzar que si es null, empiece en 0
        $montoInicial = $request->input('goal-monto-inicial') ?? 0;
        $montoObjetivo = $request->input('goal-monto-objetivo');

        if ((float)$montoInicial > (float)$montoObjetivo) {
            return redirect()->back()->withErrors(['error' => 'El monto inicial no puede ser mayor que el monto objetivo.']);
        }

        DB::beginTransaction();

        try {
            // 2. Manejo dinámico de la categoría
            $categoryId = null;
            if ($request->filled('goal-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('goal-category'))
                ]);
                $categoryId = $category->id;
            }

            // 3. Crear el registro mapeando tus campos fillable
            Goal::create([
                'user_id'        => Auth::id(),
                'category_id'    => $categoryId,
                'titulo'         => trim($request->input('goal-titulo')),
                'icono'          => null,
                'monto_inicial'  => $montoInicial,
                'monto_actual'   => $montoInicial, 
                'monto_objetivo' => $montoObjetivo,
                'descripcion'    => $request->input('goal-descripcion'),
                'fecha_limite'   => $request->input('goal-fecha-limite'),
                'estado'         => 'activa',
            ]);

            DB::commit();
            return redirect()->back()->with('success', '¡Meta creada exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al crear la meta: ' . $e->getMessage()]);
        }
    }
    public function create_payment_goal(Request $request)
    {
        // 1. Validar incluyendo la categoría opcional del abono
        $request->validate([
            'paygoal-titulo'              => 'required|string|max:25',
            'paygoal-category'            => 'nullable|string|max:50', // Agregada al validador
            'paygoal-monto'               => 'required|numeric|min:0.01',
            'paygoal-wallet-select-value' => 'nullable', 
            'paygoal-target-select-value' => 'required|exists:goals,id', 
        ]);
        
        DB::beginTransaction();

        try {
            $monto = $request->input('paygoal-monto');
            $goalId = $request->input('paygoal-target-select-value');
            
            // Manejo del select de billetera ("Externa" -> null)
            $walletId = $request->input('paygoal-wallet-select-value');
            if ($walletId === 'Externa' || empty($walletId)) {
                $walletId = null;
            }

            // 2. Manejo dinámico de la categoría del ABONO
            $categoryId = null;
            if ($request->filled('paygoal-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('paygoal-category'))
                ]);
                $categoryId = $category->id;
            }

            $goal = Goal::where('user_id', Auth::id())->findOrFail($goalId);

            // Si el dinero proviene de una billetera interna, verificar fondos y restar
            if ($walletId !== null) {
                $wallet = Wallet::where('user_id', Auth::id())->findOrFail($walletId);

                if ($wallet->monto_actual < $monto) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->withErrors(['error' => 'Fondos insuficientes en la billetera seleccionada.']);
                }

                $wallet->decrement('monto_actual', $monto);
            }

            // 3. Crear registro en payment_goals con todas tus llaves foráneas
            PaymentGoal::create([
                'goal_id'     => $goal->id,
                'wallet_id'   => $walletId, 
                'category_id' => $categoryId, // ¡Guardado correctamente aquí!
                'titulo'      => trim($request->input('paygoal-titulo')),
                'icono'       => null,
                'monto'       => $monto,
            ]);

            // Aumentar balance actual de la meta
            $goal->increment('monto_actual', $monto);

            // Verificar si la meta fue alcanzada
            $goal->refresh(); 
            if ($goal->monto_actual >= $goal->monto_objetivo) {
                $goal->update(['estado' => 'completada']);
            }

            DB::commit();
            return redirect()->back()->with('success', '¡Aporte realizado correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al procesar el pago: ' . $e->getMessage()]);
        }
    }
    public function create_debt(Request $request)
    {
        $request->validate([
            'debt-titulo'                 => 'required|string|max:25',
            // Cambió de string a un ID numérico que debe existir en la tabla categories
            'debt-category'               => 'nullable|integer|exists:categories,id', 
            'debt-monto'                  => 'required|numeric|min:0.01',
            'debt-vencimiento'            => 'required|date',
            'debt-tasa'                   => 'nullable|numeric|min:0',
            'debt-prioridad-select-value' => 'required|string|in:Ninguno,media,alta,baja',
            'debt'                        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        try {
            $prioridad = $request->input('debt-prioridad-select-value');
            if ($prioridad === 'Ninguno' || empty($prioridad)) {
                $prioridad = 'media';
            }

            $pathIcono = null;
            if ($request->hasFile('debt')) {
                $pathIcono = $request->file('debt')->store('debts', 'public');
            }

            Debt::create([
                'user_id'           => Auth::id(),
                'category_id'       => $request->input('debt-category'), // <-- Guardamos el ID directamente
                'titulo'            => trim($request->input('debt-titulo')),
                'monto_inicial'     => $request->input('debt-monto'),
                'monto_actual'      => $request->input('debt-monto'), 
                'fecha_vencimiento' => $request->input('debt-vencimiento'),
                'tasa_interes'      => $request->input('debt-tasa', 0.00) ?? 0.00,
                'prioridad'         => $prioridad,
                'estado'            => 'pendiente',
                'icono'             => $pathIcono,
            ]);

            return redirect()->back()->with('success', '¡Deuda registrada exitosamente!');

        } catch (\Exception $e) {
            if (isset($pathIcono) && $pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }
    public function create_paymentdebt(Request $request)
    {
        // 1. Validar los datos del formulario respetando la estructura de tus componentes customizados

        $request->validate([
            'payment-titulo'              => 'required|string|max:25',
            'payment-monto'               => 'required|numeric|min:0.01',
            'payment-target-select-value' => 'required|exists:debts,id', // ID de la deuda destino
            'payment-wallet-select-value' => 'nullable|exists:wallets,id', // ID de la billetera origen (puede ser externa/null)
            'payment-category'            => 'nullable|string', // Si usas ID de categoría ajustarlo a select-value o dejar nullable
            'payment-image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Usamos una transacción de Base de Datos para asegurar la consistencia del dinero
        DB::transaction(function () use ($request) {
            
            // Obtener la deuda seleccionada
            $debt = Debt::findOrFail($request->input('payment-target-select-value'));
            $montoPago = $request->input('payment-monto');

            // 2. Procesar el icono o comprobante de pago si se subió
            $pathIcono = null;
            if ($request->hasFile('payment-image')) {
                // Modifica la ruta de guardado según tus preferencias de almacenamiento
                $pathIcono = $request->file('payment-image')->store('comprobantes_deudas', 'public');
            }

            // 3. Descontar saldo de la billetera emisora (siempre que no se defina como "Externa")
            $walletId = $request->input('payment-wallet-select-value');
            if (!empty($walletId)) {
                $wallet = Wallet::findOrFail($walletId);
                $wallet->decrement('monto_actual', $montoPago); // Resta el dinero gastado en pagar la deuda
            }

            // 4. Registrar la transacción en la tabla payment_debts
            PaymentDebt::create([
                'debt_id'     => $debt->id,
                'wallet_id'   => $walletId ?: null,
                'category_id' => null, // Puedes vincularlo con tu tabla real si manejas un select de categorías
                'titulo'      => $request->input('payment-titulo'),
                'icono'       => $pathIcono,
                'monto'       => $montoPago,
                'pago_minimo' => $request->has('payment-minimo'), // Evalúa true si el checkbox está marcado
            ]);

            // 5. Mitigar y actualizar el balance de la Deuda Principal amortizada
            $debt->decrement('monto_actual', $montoPago);

            // Opcional: Si el monto_actual llega a 0 o menos, podrías marcar automáticamente el estado como pagada.
            if ($debt->refresh()->monto_actual <= 0) {
                $debt->update([
                    'monto_actual' => 0,
                    'estado'       => 'pagada'
                ]);
            }
        });

        return redirect()->back()->with('success', '¡Abono a la deuda registrado correctamente!');
    }
   


}