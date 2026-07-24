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
use Carbon\Carbon;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Capturar los filtros (sort_by se queda como string; los demás se fuerzan a arrays si traen datos)
        $sortBy = $request->input('sort_by', 'date_desc');
        $rangeAmount = $request->input('range_amount') ? (array) $request->input('range_amount') : [];
        $timeFrame = $request->input('time_frame') ? (array) $request->input('time_frame') : [];
        $recordType = $request->input('record_type') ? (array) $request->input('record_type') : [];
        $currentCategory = $request->input('category_id') ? (array) $request->input('category_id') : [];

        // Variables requeridas por los modales/formularios de tu vista home
        $categories = Category::all();
        $wallets = DB::table('wallets')->where('user_id', $userId)->get();
        $goals = DB::table('goals')->where('user_id', $userId)->get();
        $debts = DB::table('debts')->where('user_id', $userId)->get();

        // 2. Mapeos de subconsultas estandarizadas
        $queries = [];

        $subqueriesConfig = [
            'wallet' => [
                'table' => 'wallets',
                'monto_col' => 'wallets.monto_actual',
                'monto_inicial' => 'wallets.monto_inicial',
                'extra_info' => 'wallets.tipo',
                'fecha' => 'wallets.created_at',
                'join_category' => false,
                'join_wallet' => false,
                'join_wallet_origen' => false
            ],
            'debt' => [
                'table' => 'debts',
                'monto_col' => 'debts.monto_actual',
                'monto_inicial' => 'debts.monto_inicial',
                'extra_info' => 'debts.prioridad',
                'fecha' => 'debts.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'debts.fecha_vencimiento'
            ],
            'goal' => [
                'table' => 'goals',
                'monto_col' => 'goals.monto_objetivo',
                'monto_inicial' => 'goals.monto_inicial',
                'extra_info' => 'goals.estado',
                'fecha' => 'goals.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'goals.fecha_limite' 
            ],
            'income' => [
                'table' => 'incomes',
                'monto_col' => 'incomes.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => 'incomes.frecuencia',
                'fecha' => 'COALESCE(incomes.fecha_inicio, incomes.created_at)',
                'join_category' => true,
                'join_wallet' => true,
                'join_wallet_origen' => false,
                'fk_wallet_destino' => 'incomes.wallet_id'
            ],
            'investment' => [
                'table' => 'investments',
                'monto_col' => 'investments.valor_actual',
                'monto_inicial' => 'investments.monto_inicial',
                'extra_info' => 'investments.tipo_renta',
                'fecha' => 'investments.fecha_adquisicion',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'investments.fecha_vencimiento', 
                'tasa_interes_raw' => 'investments.tasa_interes'
            ],
            'transaction' => [
                'table' => 'transactions',
                'monto_col' => 'transactions.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => 'transactions.tipo',
                'fecha' => 'COALESCE(transactions.fecha_ejecucion, transactions.created_at)',
                'join_category' => true,
                'join_wallet' => true,
                'join_wallet_origen' => true,
                'fk_wallet_destino' => 'transactions.wallet_destino_id', 
                'fk_wallet_origen'  => 'transactions.wallet_origen_id'
            ],
            'paymentGoal' => [
                'table' => 'payment_goals',
                'monto_col' => 'payment_goals.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => "''",
                'fecha' => 'payment_goals.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'join_parent' => ['table' => 'goals', 'foreign_key' => 'payment_goals.goal_id', 'owner_col' => 'goals.user_id'],
                'join_parent_title' => true
            ],
            'paymentDebt' => [
                'table' => 'payment_debts',
                'monto_col' => 'payment_debts.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => "''",
                'fecha' => 'payment_debts.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'join_parent' => ['table' => 'debts', 'foreign_key' => 'payment_debts.debt_id', 'owner_col' => 'debts.user_id'],
                'join_parent_title' => true
            ],
        ];

        // Procesamiento dinámico para el UNION
        foreach ($subqueriesConfig as $tipo => $config) {
            // CORRECCIÓN: Si el array de tipos no está vacío y el tipo actual NO está seleccionado, lo omitimos
            if (!empty($recordType) && !in_array($tipo, $recordType)) {
                continue;
            }

            $query = DB::table($config['table']);

            if (isset($config['join_parent'])) {
                $query->join($config['join_parent']['table'], $config['join_parent']['foreign_key'], '=', $config['join_parent']['table'] . '.id');
                $userColumn = $config['join_parent']['owner_col'];
            } else {
                $userColumn = $config['table'] . '.user_id';
            }

            if ($config['join_category']) {
                $query->leftJoin('categories', $config['table'] . '.category_id', '=', 'categories.id');
            }

            if ($config['join_wallet']) {
                $fkDestino = $config['fk_wallet_destino'] ?? ($config['table'] . '.wallet_id');
                $query->leftJoin('wallets AS w_destino', $fkDestino, '=', 'w_destino.id');
            }

            if ($config['join_wallet_origen']) {
                $fkOrigen = $config['fk_wallet_origen'] ?? ($config['table'] . '.wallet_origen_id');
                $query->leftJoin('wallets AS w_origen', $fkOrigen, '=', 'w_origen.id');
            }
            $query->select([
                $config['table'] . '.id AS id', // Asegura el ID del registro
                $config['table'] . '.titulo AS titulo',
                $config['table'] . '.icono AS icono',
                DB::raw("$userColumn AS user_id"),
                
                // SOLUCIÓN CLAVE: Fuerza el alias exacto para mapear en el UNION
                $config['table'] === 'wallets' 
                    ? DB::raw("NULL AS category_id") 
                    : $config['table'] . '.category_id AS category_id',
                
                DB::raw($config['monto_col'] . " AS monto"),
                DB::raw($config['monto_inicial'] . " AS monto_inicial"),
                DB::raw($config['extra_info'] . " AS extra_info"),
                DB::raw("'$tipo' AS tipo_registro"),
                DB::raw($config['fecha'] . " AS fecha"),
                
                isset($config['fecha_vencimiento_raw']) ? DB::raw($config['fecha_vencimiento_raw'] . ' AS vencimiento_registro') : DB::raw("NULL AS vencimiento_registro"),
                isset($config['tasa_interes_raw']) ? DB::raw($config['tasa_interes_raw'] . ' AS tasa_interes_registro') : DB::raw("NULL AS tasa_interes_registro"),
                
                (isset($config['join_parent_title']) && $config['join_parent_title']) 
                    ? $config['join_parent']['table'] . '.titulo AS nombre_padre' 
                    : DB::raw("NULL AS nombre_padre"),
                
                $config['join_category'] ? 'categories.categoria AS categoria' : DB::raw("NULL AS categoria"),
                $config['join_wallet'] ? 'w_destino.titulo AS billetera_destino' : DB::raw("NULL AS billetera_destino"),
                $config['join_wallet_origen'] ? 'w_origen.titulo AS billetera_origen' : DB::raw("NULL AS billetera_origen"),
            ])->where($userColumn, $userId);

            $queries[] = $query;
        } 
        // =========================================================================
        // 3 y 4. Unificar mediante UNION todas las subconsultas de forma Nativa
        // =========================================================================
        $firstQuery = array_shift($queries);
        
        if (!$firstQuery) {
            // Consulta vacía de respaldo si no hay tipos seleccionados
            $firstQuery = DB::table('transactions')
                ->select([
                    'id', 'titulo', 'icono', 'user_id', 'category_id', 
                    'monto', 'monto_inicial', 'extra_info', 'tipo_registro', 'fecha',
                    'vencimiento_registro', 'tasa_interes_registro', 'nombre_padre',
                    'categoria', 'billetera_destino', 'billetera_origen'
                ])
                ->whereRaw('1 = 0');
        }

        // Unificar el resto de consultas de manera limpia
        foreach ($queries as $subQuery) {
            $firstQuery->unionAll($subQuery);
        }

        // CREAR LA SUBCONSULTA PROTEGIENDO LOS BINDINGS NATIVAMENTE
        // Al pasar directamente la instancia de la query al FROM, Laravel gestiona los bindings en orden perfecto.
        $mainQuery = DB::table($firstQuery, 'registros');

        // =========================================================================
        // 5. Aplicación de Filtros Globales Múltiples (Corregido con Aislamiento Estricto)
        // =========================================================================

        // CATEGORÍAS MÚLTIPLES: Forzado en un subgrupo condicional aislado
        if (!empty($currentCategory)) {
            $mainQuery->where(function($q) use ($currentCategory) {
                foreach ($currentCategory as $index => $catId) {
                    if ($index === 0) {
                        $q->where('category_id', '=', $catId);
                    } else {
                        $q->orWhere('category_id', '=', $catId);
                    }
                }
            });
        }

        // RANGOS DE DINERO MÚLTIPLES
        if (!empty($rangeAmount)) {
            $mainQuery->where(function($q) use ($rangeAmount) {
                if (in_array('low', $rangeAmount)) {
                    $q->orWhere('monto', '<', 50);
                }
                if (in_array('medium', $rangeAmount)) {
                    $q->orWhereBetween('monto', [50, 500]);
                }
                if (in_array('high', $rangeAmount)) {
                    $q->orWhere('monto', '>', 500);
                }
            });
        }
        // 6. Aplicación de Ordenamiento
        if ($sortBy === 'amount_desc') {
            $mainQuery->orderBy('monto', 'desc');
        } elseif ($sortBy === 'alpha_asc') {
            $mainQuery->orderBy('titulo', 'asc');
        } else {
            $mainQuery->orderBy('fecha', 'desc');
        }

        // 7. Paginación adaptada
        $records = $mainQuery->paginate(15)->appends($request->all());

        // 8. Retornar vista incluyendo todas las variables necesarias
        return view('home', compact(
            'records',
            'categories',
            'sortBy',
            'rangeAmount',
            'timeFrame',
            'recordType',
            'currentCategory',
            'wallets',
            'goals',
            'debts'
        ));
    }


    public function create_wallet(Request $request)
    {
        $request->validate([
            'wallet-titulo' => 'required|max:25',
            'wallet-monto' => 'required|numeric|min:0|max:999999',
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
        $request->validate([
            'goal-titulo'         => 'required|string|max:25',
            'goal-category'       => 'nullable|string|max:25',
            'goal-monto-objetivo' => 'required|numeric|min:0.01',
            'goal-monto-inicial'  => 'nullable|numeric|min:0',
            'goal-fecha-limite'   => 'required|date', 
            'goal-descripcion'    => 'nullable|string|max:500',
            'goal-image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
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

            // 3. Manejo de la subida de la imagen
            $imagePath = null;
            if ($request->hasFile('goal-image') && $request->file('goal-image')->isValid()) {
                // Guarda el archivo en storage/app/public/goals con un nombre único aleatorio
                $imagePath = $request->file('goal-image')->store('goals', 'public');
            }

            // 4. Crear el registro mapeando tus campos fillable
            $goal = Goal::create([
                'user_id'        => Auth::id(),
                'category_id'    => $categoryId,
                'titulo'         => trim($request->input('goal-titulo')),
                // Guardamos la ruta de la imagen en 'icono'. Si no se subió nada, queda null.
                'icono'          => $imagePath, 
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
        $request->validate([
            'paygoal-titulo'              => 'required|string|max:25',
            'paygoal-category'            => 'nullable|string|max:50',
            'paygoal-monto'               => 'required|numeric|min:0.01',
            'paygoal-wallet-select-value' => 'nullable', 
            'paygoal-target-select-value' => 'required|exists:goals,id', 
            'paygoal-image'               => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:2048',
        ]);
        
        DB::beginTransaction();
        $pathIcono = null; 

        try {
            $monto = $request->input('paygoal-monto');
            $goalId = $request->input('paygoal-target-select-value');
            
            $walletId = $request->input('paygoal-wallet-select-value');
            if ($walletId === 'Externa' || empty($walletId)) {
                $walletId = null;
            }

            $categoryId = null;
            if ($request->filled('paygoal-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('paygoal-category'))
                ]);
                $categoryId = $category->id;
            }

            // Subida forzada usando el objeto directo del request
            if ($request->hasFile('paygoal-image') && $request->file('paygoal-image')->isValid()) {
                $pathIcono = $request->file('paygoal-image')->store('comprobantes_metas', 'public');
            }

            $goal = Goal::where('user_id', Auth::id())->findOrFail($goalId);

            if ($walletId !== null) {
                $wallet = Wallet::where('user_id', Auth::id())->findOrFail($walletId);
                if ($wallet->monto_actual < $monto) {
                    DB::rollBack();
                    if ($pathIcono) { Storage::disk('public')->delete($pathIcono); }
                    return redirect()->back()->withInput()->withErrors(['error' => 'Fondos insuficientes.']);
                }
                $wallet->decrement('monto_actual', $monto);
            }

            // Inserción explícita en la base de datos
            $nuevoAbono = new PaymentGoal();
            $nuevoAbono->goal_id = $goal->id;
            $nuevoAbono->wallet_id = $walletId;
            $nuevoAbono->category_id = $categoryId;
            $nuevoAbono->titulo = trim($request->input('paygoal-titulo'));
            $nuevoAbono->icono = $pathIcono;
            $nuevoAbono->monto = $monto;
            $nuevoAbono->save();

            $goal->increment('monto_actual', $monto);

            $goal->refresh(); 
            if ($goal->monto_actual >= $goal->monto_objetivo) {
                $goal->update(['estado' => 'completada']);
            }

            DB::commit();
            return redirect()->back()->with('success', '¡Aporte realizado correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
    public function create_debt(Request $request)
    {
        // 1. Validamos usando la clave exacta del formulario: 'debt-image'
        $request->validate([
            'debt-titulo'                 => 'required|string|max:25',
            'debt-category'               => 'nullable|string|max:50', 
            'debt-monto'                  => 'required|numeric|min:0.01',
            'debt-vencimiento'            => 'required|date',
            'debt-tasa'                   => 'nullable|numeric|min:0',
            'debt-prioridad-select-value' => 'required|string|in:Ninguno,media,alta,baja',
            'debt-image'                  => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048', // <-- CORREGIDO
        ]);

        try {
            $prioridad = $request->input('debt-prioridad-select-value');
            if ($prioridad === 'Ninguno' || empty($prioridad)) {
                $prioridad = 'media';
            }

            // Recuperamos el archivo usando la clave correcta
            $pathIcono = null;
            if ($request->hasFile('debt-image') && $request->file('debt-image')->isValid()) { // <-- CORREGIDO
                $pathIcono = $request->file('debt-image')->store('debts', 'public');
            }

            // 2. Manejo dinámico de categorías
            $categoryId = null;
            if ($request->filled('debt-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('debt-category'))
                ]);
                $categoryId = $category->id;
            }

            // 3. Creación del registro
            Debt::create([
                'user_id'           => Auth::id(),
                'category_id'       => $categoryId,
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

        // 1. Modificamos la validación para que admita texto en billetera (por si viene "Externa") y categoría
        $request->validate([
            'payment-titulo'              => 'required|string|max:25',
            'payment-monto'               => 'required|numeric|min:0.01',
            'payment-target-select-value' => 'required|exists:debts,id', 
            'payment-wallet-select-value' => 'nullable|string', // Cambiado a string para aceptar "Externa"
            'payment-category'            => 'nullable|string|max:50', // Para la creación dinámica
            'paymentdebt-image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction(); // Cambiado a control manual para mejor gestión de excepciones

        try {
            $debt = Debt::findOrFail($request->input('payment-target-select-value'));
            $montoPago = $request->input('payment-monto');

            // Manejo del select de billetera ("Externa" o vacío -> null)
            $walletId = $request->input('payment-wallet-select-value');
            if ($walletId === 'Externa' || empty($walletId)) {
                $walletId = null;
            }

            // Manejo dinámico de la categoría (Igual que en tus otros componentes)
            $categoryId = null;
            if ($request->filled('payment-category')) {
                $category = \App\Models\Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('payment-category'))
                ]);
                $categoryId = $category->id;
            }

            // Procesar la imagen si se subió
            $pathIcono = null;
            if ($request->hasFile('paymentdebt-image')) {
                $pathIcono = $request->file('paymentdebt-image')->store('comprobantes_deudas', 'public');
            }

            // Si se seleccionó una billetera interna, verificar saldo y descontar
            if ($walletId !== null) {
                $wallet = Wallet::findOrFail($walletId);
                
                if ($wallet->monto_actual < $montoPago) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->withErrors(['error' => 'Fondos insuficientes en la billetera seleccionada.']);
                }

                $wallet->decrement('monto_actual', $montoPago); 
            }

            // Registrar la transacción con el ID dinámico de categoría
            PaymentDebt::create([
                'debt_id'     => $debt->id,
                'wallet_id'   => $walletId,
                'category_id' => $categoryId, // <-- Ahora guarda el ID de la categoría creada o encontrada
                'titulo'      => trim($request->input('payment-titulo')),
                'icono'       => $pathIcono,
                'monto'       => $montoPago,
                'pago_minimo' => $request->has('payment-minimo'), 
            ]);

            // Amortizar la deuda principal
            $debt->decrement('monto_actual', $montoPago);

            // Verificar si la deuda se liquidó por completo
            if ($debt->refresh()->monto_actual <= 0) {
                $debt->update([
                    'monto_actual' => 0,
                    'estado'       => 'pagada'
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', '¡Abono a la deuda registrado correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Si falló la BD y se había subido un archivo, lo borramos
            if (isset($pathIcono) && $pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al procesar el pago: ' . $e->getMessage()]);
        }
    }
   


}