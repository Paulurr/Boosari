<x-layout title="Home">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite([
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/paneles.js',
                'resources/js/select.js',
                'resources/js/add_panel.js',
                'resources/js/filter_panel.js',
                'resources/js/col_home.js',
            ])
    </x-slot:head>

    <x-nav/>
     <h1 class="col7 text-center pt-25 pb-15 text-4xl font-bold">
            {{ (__('main.title')) }}
        </h1>
        <object data="{{ asset('images/BarAppere (3).svg') }}" type="image/svg+xml" class="w-full pointer-events-none h-full object-contain rotate-180 mb-15"></object>
        <div class="lg:pl-30  lg:pr-30 md:pl-15 md:pr-15 pl-2 pr-2 h-auto flex flex-col items-center justify-start">
            <div class="h-10 w-full flex justify-center mb-5 relative">
                <x-button
                    color1="var(--col4)"
                    color2="var(--col3)"
                    colortext="var(--col1)"
                    class="filter-btn p-2 flex items-center justify-center"
                    >
                    <div class="filtro-but-icon">
                        <div class="filtro-but-icon-p2"></div>
                        <div class="filtro-but-icon-p1"></div>
                    </div>
                    {{ __('main.filter') }}
                </x-button>
                <div class="w-10">

                </div>

                <form action="{{ route('records.index') }}" method="GET" id="search-form" class="h-10 w-full flex justify-end mb-15">
                    <input type="text" name="search" id="search-input" value="{{ $search ?? '' }}" class="bgcol1 transition-all  lg:text-lg  xl:text-xl text-xs w-full h-10 mb-15 col7 p-2" placeholder="{{ __('main.search') }}"/>
                    <button type="submit" class="ml-2 cursor-pointer overflow-hidden w-auto aspect-square perfil-div-nav flex items-center justify-center transition-colors rounded-full">
                        <img src="{{ asset("images/search.png") }}" alt="" class="h-full aspect-square scale-[0.75]">
                    </button>
                </form>
            </div>
            <div class="w-full flex items-center justify-end mb-15">
                
                <div class="h-full flex items-center justify-center">
                    <x-button
                        color1="var(--col3)"
                        color2="var(--col4)"
                        colortext="var(--col7)"
                        class="p-4 w-autoW text-xs lg:text-sm flex justify-center items-center add-btn"
                        >
                        + {{ __('main.add') }}
                    </x-button>
                </div>
                
            </div>
          <div id="records-container" class="w-full h-auto show-record">
                @if($records->count())
                    {{-- Generamos exactamente las 3 filas fijas de la malla --}}
                    @for ($i = 0; $i < 3; $i++)
                        {{-- Solo abrimos la fila si existen elementos para ella --}}
                        @if(isset($records[$i * 3]))
                            <x-row-home>
                                {{-- Cada fila dibuja exactamente 3 columnas internas --}}
                                @for ($j = 0; $j < 3; $j++)
                                    @php
                                        $index = ($i * 3) + $j;
                                        $record = $records[$index] ?? null;
                                    @endphp

                                    @if($record)
                                        {{-- Procesamiento dinámico de datos según el tipo de registro --}}
                                        @php
                                            // Inicializamos las variables por defecto
                                            $origen = '';
                                            $destino = '';
                                            $name = ''; // <-- 1. Inicializamos la variable name

                                            // Si el campo 'categoria' del query no está vacío, lo usa. Si no, ""
                                            $categoria = (!empty($record->categoria)) ? "Categoria: ".ucfirst($record->categoria) : '';

                                            $tipoCard = ucfirst($record->tipo_registro);
                                            $fechaFormateada = \Carbon\Carbon::parse($record->fecha)->format('d/m/Y');

                                            switch($record->tipo_registro) {
                                                case 'wallet': //traducido
                                                    $name = 'info-wallet'; // <-- 2. Asignamos el nombre para la billetera
                                                    // 1. Clasificación del tipo de billetera para el título de la tarjeta
                                                    switch($record->extra_info) {
                                                        case 'efectivo':
                                                            $tipoCard = __('main.cash');
                                                            break;
                                                        case 'debito':
                                                            $tipoCard = __('main.debit');
                                                            break;
                                                        case 'ahorro':
                                                            $tipoCard = __('main.savings');
                                                            break;
                                                        case 'credito':
                                                            $tipoCard = __('main.credit_card');
                                                            break;
                                                        default:
                                                            $tipoCard = __('main.financial');
                                                            break;
                                                    }
                                                    $origen = "";

                                                    // 3. Lógica para el Monto Inicial y Destinos Dinámicos
                                                    if ($record->extra_info === __('main.credit')) {
                                                        $destino = '';
                                                    } else {
                                                        $montoInicialFormateado = isset($record->monto_inicial)
                                                            ? '$' . number_format($record->monto_inicial, 2)
                                                            : __('main.unregistered');

                                                        $destino = __('main.initial_amount') . ': ' . $montoInicialFormateado;
                                                    }
                                                    break;

                                                case 'income': //traducido
                                                    $name = 'info-income';
                                                    $tipoCard = __('main.income');
                                                    $origen = "";
                                                    $destino = !empty($record->billetera_destino)
                                                        ? __('main.destination') . ': ' . $record->billetera_destino
                                                        : __('main.destination') . ': ' . __('main.external_account');
                                                    break;

                                                case 'transaction': //traducido
                                                    $name = 'info-transaction';
                                                    if ($record->extra_info === 'ingreso') {
                                                        $tipoCard = __('main.income');
                                                        $origen = '';
                                                        $destino = !empty($record->billetera_destino) ? __('main.destination') . ': ' . $record->billetera_destino : __('main.destination') . ': ' . __('main.not_assigned');
                                                    } elseif ($record->extra_info === 'gasto') {
                                                        $tipoCard = __('main.expense');
                                                        $origen = '';
                                                        $destino = !empty($record->billetera_destino) ? __('main.account') . ': ' . $record->billetera_destino : __('main.account') . ': ' . __('main.not_assigned');
                                                    } else {
                                                        $tipoCard = __('main.wire_transfer');
                                                        $origen = !empty($record->billetera_origen) ? __('main.from') . ': ' . $record->billetera_origen : __('main.from') . ': ' . __('main.issuing_account');
                                                        $destino = !empty($record->billetera_destino) ? __('main.to') . ': ' . $record->billetera_destino : __('main.to') . ': ' . __('main.receiving_account');
                                                    }
                                                    break;

                                                case 'investment': //traducido
                                                    $name = 'info-investment';
                                                    $tipoCard = __('main.investment') . ' (' . ucfirst($record->extra_info) . ')';
                                                    $montoInvertido = '$' . number_format($record->monto_inicial, 2);
                                                    $origen = __('main.invested') . ': ' . $montoInvertido;

                                                    $gananciaNeto = $record->monto - $record->monto_inicial;
                                                    $signo = $gananciaNeto >= 0 ? '+' : '';
                                                    $gananciaFormateada = $signo . '$' . number_format($gananciaNeto, 2);

                                                    $vencimiento = !empty($record->vencimiento_registro)
                                                        ? date('d/m/Y', strtotime($record->vencimiento_registro))
                                                        : __('main.no_date');

                                                    $tasaTexto = '';
                                                    if ($record->extra_info === __('main.fixed') && !empty($record->tasa_interes_registro)) {
                                                        $tasaTexto = ' | ' . __('main.rate') . ': ' . ($record->tasa_interes_registro + 0) . '%';
                                                    }

                                                    $destino = " " . __('main.profit') . ": {$gananciaFormateada}{$tasaTexto} | " . __('main.expires') . ": {$vencimiento}";
                                                    break;

                                                case 'goal': //traducido
                                                    $name = 'info-goal';
                                                    $tipoCard = __('main.goal');
                                                    $montoObjetivo = '$' . number_format($record->monto, 2);
                                                    $montoInicial = '$' . number_format($record->monto_inicial, 2);
                                                    $origen = " " . __('main.goal') . ": {$montoObjetivo} | " . __('main.initial_amount') . ": {$montoInicial}";

                                                    $fechaLimite = !empty($record->vencimiento_registro)
                                                        ? date('d/m/Y', strtotime($record->vencimiento_registro))
                                                        : __('main.no_limit');

                                                    $destino = " " . __('main.deadline') . ": {$fechaLimite} | " . __('main.status') . ": " . ucfirst($record->extra_info);
                                                    break;

                                                case 'debt': //traducido
                                                    $name = 'info-debt';
                                                    $tipoCard = __('main.debt');
                                                    $montoOriginal = '$' . number_format($record->monto_inicial, 2);
                                                    $origen = " " . __('main.initial_amount') . ": {$montoOriginal}";

                                                    $vencimiento = !empty($record->vencimiento_registro)
                                                        ? date('d/m/Y', strtotime($record->vencimiento_registro))
                                                        : __('main.no_date');

                                                    $prioridad = !empty($record->extra_info) ? ucfirst($record->extra_info) : 'No definida';
                                                    $destino = " " . __('main.priority') . ": {$prioridad} | " . __('main.expires') . ": {$vencimiento}";
                                                    break;

                                                case 'paymentGoal': //traducido
                                                    $name = 'info-paymentGoal';
                                                    $tipoCard = __('main.payment_goal');
                                                    $metaAsociada = !empty($record->nombre_padre) ? $record->nombre_padre : __('main.goal_not_found');
                                                    $origen = " " . __('main.goal') . ": {$metaAsociada}";
                                                    $destino = '';
                                                    break;

                                                case 'paymentDebt': //traducido 
                                                    $name = 'info-paymentDebt';
                                                    $tipoCard = __('main.debt_payment');
                                                    $deudaAsociada = !empty($record->nombre_padre) ? $record->nombre_padre : __('main.debt_not_found');
                                                    $origen = " " . __('main.debt') . ": {$deudaAsociada}";
                                                    $destino = '';
                                                    break;
                                            }
                                        @endphp

                                        {{-- 3. Pasamos :name="$name" dinámicamente al componente --}}
                                        <x-col-home
                                            :name="$name"
                                            :id="$record->id"
                                            :tipo="$tipoCard"
                                            :titulo="$record->titulo"
                                            :icono="$record->icono"
                                            :monto="number_format($record->monto ?? $record->monto_actual, 2)"
                                            :origen="$origen"
                                            :destino="$destino"
                                            :categoria="$categoria"
                                            :fecha="$fechaFormateada"
                                        />
                                    @endif
                                @endfor
                            </x-row-home>
                        @endif
                    @endfor
                @else
                    <div class="h-100 text-center flex items-center justify-center">
                        {{ __('main.missing') }}
                    </div>
                @endif

                {{-- Paginación única global --}}
                <div class="h-30 flex items-center justify-center mt-5">
                    {{ $records->appends(request()->query())->links() }}
                </div>
            </div>
        </div> 

            
       
        <object data="{{ asset('images/BarAppere (2).svg') }}" type="image/svg+xml" class="w-full h-full pointer-events-none object-contain border-b-3-3 col3 mt-15"></object>
        <x-panel 
            name="info-wallet" 
            title="{{ __('main.record_info') }}"
            :info="true">
            <div id="info-wallet-container" class="p-6">
                
                <!-- Mensaje de estado -->
                <p id="wallet-status" class="text-center col3 py-4">
                    {{ __('main.select') }}
                </p>

                <!-- Estructura de detalles -->
                <div id="wallet-details" class="space-y-8 hidden">
                    <div class="flex items-center space-x-4">
                        <img id="wallet-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="walletinfo" />
                        </div>
                        <div>
                            <h3 id="wallet-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                            <div class="modal-edit-only hidden mb-5">
                                <x-label
                                    name="wallet-titulo-input"
                                    :title="__('main.wallet_name')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                            <span id="wallet-tipo" class="px-2 py-1 text-xs col7 border rounded-full font-semibold uppercase"></span>
                        </div>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Monto Actual -->
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.current_amount') }}</p>
                            
                            <!-- MODO LECTURA: clase modal-read-only y data-field -->
                            <p id="wallet-monto-actual" data-field="monto_actual" class="modal-read-only text-lg font-bold col4"></p>
                            
                            <!-- MODO EDICIÓN: mismo componente x-label del panel "Añadir registro" -->
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="wallet-monto-actual-input"
                                    :title="__('main.current_amount')"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <!-- Monto Inicial -->
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.initial_amount') }}</p>
                            
                            <!-- MODO LECTURA: clase modal-read-only y data-field -->
                            <p id="wallet-monto-inicial" data-field="monto_inicial" class="modal-read-only text-lg font-bold col7"></p>
                            
                            <!-- MODO EDICIÓN: mismo componente x-label del panel "Añadir registro" -->
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="wallet-monto-inicial-input"
                                    :title="__('main.initial_amount')"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- MODO EDICIÓN: Sección opcional para registrar un movimiento -->
                    <div id="wallet-movement-section" class="modal-edit-only pt-6 border-t-3 col4 hidden">
                        <label class="flex items-center space-x-2 cursor-pointer font-semibold text-xs uppercase">
                            <input type="checkbox" id="wallet-add-movement-check" class="rounded">
                            <span>{{ __('main.new_transaction') }}</span>
                        </label>

                        <div id="wallet-movement-fields" class="space-y-4 hidden mt-4">
                            <div class="col7 w-70">
                                <x-label
                                    name="wallet-mov-titulo"
                                    :title="__('main.concept_title')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1">{{ __('main.transaction_amount') }}</label>
                                <p id="wallet-mov-tipo-indicator" class="text-xs mt-1 font-semibold"></p>
                            </div>
                        </div>
                    </div>

                    <!-- MODO LECTURA: Gráfica de últimos 8 movimientos -->
                    <div class="modal-read-only pt-6 border-t-3 col4">
                        <p class="text-xs font-semibold uppercase col7 mb-3">{{ __('main.last_8_movements') }}</p>
                        <div class="relative w-full h-48">
                            <canvas id="wallet-chart"></canvas>
                            <p id="wallet-chart-empty" class="hidden text-center col7 text-sm opacity-75">
                                {{ __('main.transaction_move') }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right text-xs">
                        {{ __('main.recorded_on') }}: <span id="wallet-fecha"></span>
                    </div>
                </div>

            </div>
        </x-panel>
        <x-panel 
            name="info-transaction" 
            title="{{ __('main.movement_info') }}"
            :info="true">
            <div id="info-transaction-container" class="p-6">
                
                <!-- Mensaje de estado al cargar -->
                <p id="transaction-status" class="text-center col3 py-4">
                    {{ __('main.select') }}
                </p>

                <!-- Contenedor de detalles -->
                <div id="transaction-details" class="space-y-8 hidden">
                    
                    <!-- Cabecera / Icono / Título -->
                    <div class="flex items-center space-x-4">
                        <img id="transaction-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="transactioninfo" />
                        </div>
                        <div class="flex-1">
                            <!-- MODO LECTURA: Título -->
                            <h3 id="transaction-titulo" data-field="titulo" class="modal-read-only text-xl font-bold col4"></h3>
                            
                            <!-- MODO EDICIÓN: mismo componente x-label del panel "Añadir registro" -->
                            <div class="modal-edit-only w-50 hidden mb-2">
                                <x-label
                                    name="transaction-titulo-input"
                                    :title="__('main.concept_title')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>

                            <span id="transaction-tipo" class="px-2 py-1 text-xs col7 border rounded-full font-semibold uppercase"></span>
                        </div>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <!-- Campos Principales (Monto y Categoría) -->
                    <div class="grid grid-cols-2 gap-6">
                        
                        <!-- Monto -->
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.amount') }}</p>
                            
                            <p id="transaction-monto" data-field="monto" class="modal-read-only text-lg font-bold col7"></p>
                            
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="transaction-monto-input"
                                    :title="__('main.amount') . ' ($)'"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.category') }}</p>
                            
                            <p id="transaction-categoria" data-field="categoria" class="modal-read-only text-lg font-bold col7"></p>
                            
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="transaction-categoria-input"
                                    :title="__('main.category')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Cuentas Origen / Destino (Solo Lectura Informativo) -->
                    <div class="grid grid-cols-2 gap-6 pt-6 col7">
                        <div>
                            <p class="text-xs font-semibold uppercase opacity-75">{{ __('main.issuing_account') }}</p>
                            <p id="transaction-origen" class="text-sm font-semibold mt-1">N/A</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase opacity-75">{{ __('main.receiving_account') }}</p>
                            <p id="transaction-destino" class="text-sm font-semibold mt-1">N/A</p>
                        </div>
                    </div>

                    <div class="text-right text-xs col7">
                        {{ __('main.recorded_on') }}: <span id="transaction-fecha"></span>
                    </div>
                </div>

            </div>
        </x-panel>
        <x-panel 
            name="info-income" 
            :title="__('main.scheduled_income')"
            :info="true">
            <div id="income-container" class="p-6">
                
                <p id="income-status" class="text-center col3 py-8 block">
                    {{ __('main.loading') }}
                </p>

                <div id="income-details" class="space-y-8 hidden">
                    
                    <!-- Estado Recurrencia Activa/Inactiva (Badges & Switch) -->
                    <div class="flex items-center justify-between border-b-3 col4 pb-4">
                        <div>
                            <!-- MODO LECTURA: Badge -->
                            <span id="income-activo-badge" class="modal-read-only px-3 py-1 text-xs rounded-full font-bold uppercase"></span>
                            
                            <!-- MODO EDICIÓN: Switch Activar/Desactivar -->
                            <div class="modal-edit-only hidden flex items-center space-x-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="income-activo-input" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                </label>
                                <span class="text-xs font-semibold col7">{{ __('main.programed_transaction') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Icono y Título -->
                    <div class="flex items-center space-x-4">
                        <img id="income-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="incomeinfo" />
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.concept_title') }}</p>
                            <h3 id="income-titulo" class="modal-read-only text-xl font-bold col4"></h3>
                            <div class="modal-edit-only hidden w-50" >
                                <x-label
                                    name="income-titulo-input"
                                    :title="__('main.concept_title')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Monto y Frecuencia -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.amount') }}</p>
                            <p id="income-monto" class="modal-read-only text-lg font-bold text-green-500"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="income-monto-input"
                                    :title="__('main.amount') . ' ($)'"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.frequency') }}</p>
                            <p id="income-frecuencia" class="modal-read-only text-sm font-semibold capitalize mt-1"></p>

                            {{-- MODO EDICIÓN: mismo componente x-select usado en el panel "Añadir registro" --}}
                            <div class="modal-edit-only hidden">
                                <x-select
                                    title=""
                                    name="income-frecuencia"
                                    first="{{ __('main.none') }}">
                                    <x-option name="income-frecuencia" value="ninguno">{{ __('main.none') }}</x-option>
                                    <x-option name="income-frecuencia" value="diario">{{ __('main.daily') }}</x-option>
                                    <x-option name="income-frecuencia" value="semanal">{{ __('main.weekly') }}</x-option>
                                    <x-option name="income-frecuencia" value="quincenal">{{ __('main.biweekly') }}</x-option>
                                    <x-option name="income-frecuencia" value="mensual">{{ __('main.monthly') }}</x-option>
                                    <x-option name="income-frecuencia" value="anual">{{ __('main.yearly') }}</x-option>
                                </x-select>
                            </div>
                        </div>
                    </div>

                    <!-- Próxima Ejecución -->
                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.next_run') }}</p>
                        <p id="income-fecha" class="modal-read-only text-sm font-semibold"></p>
                        <input type="datetime-local" id="income-fecha-input" class="modal-edit-only hidden w-full border p-1.5 rounded bg-transparent col7 text-sm">
                    </div>

                    <!-- Info Relacional (Lectura) -->
                    <div class="grid grid-cols-2 gap-6 text-xs pt-6 border-t-3 col4">
                        <div>
                            <span class="opacity-75">{{ __('main.wallet') }}:</span>
                            <p id="income-wallet" class="font-bold col7"></p>
                        </div>
                        <div>
                            <span class="opacity-75">{{ __('main.category') }}:</span>
                            <p id="income-category" class="font-bold col7"></p>
                        </div>
                    </div>

                </div>
            </div>
        </x-panel>
        <x-panel
            name="info-goal"
            :title="__('main.goal_info')"
            :info="true">
            <div id="info-goal-container" class="p-6">

                <p id="goal-status" class="text-center col3 py-8 block">
                    {{ __('main.goal_select') }}
                </p>

                <div id="goal-details" class="space-y-8 hidden">

                    <div class="flex items-center space-x-4">
                        <img id="goal-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="goalinfo" />
                        </div>
                        <div>
                            <h3 id="goal-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="goal-titulo-input"
                                    :title="__('main.goal_title')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                            <span id="goal-estado-badge" class="px-2 py-1 text-xs col7 border rounded-full font-semibold uppercase"></span>
                        </div>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <!-- Progreso -->
                    <div class="col7">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-xs font-semibold uppercase col7">{{ __('main.progress') }}</p>
                            <p id="goal-progreso-pct" class="text-xs font-bold col7"></p>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-300 overflow-hidden">
                            <div id="goal-progreso-bar" class="h-full bgcol4" style="width:0%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-2 opacity-75">
                            <span id="goal-monto-actual">$0.00</span>
                            <span id="goal-monto-objetivo-txt">$0.00</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.goal_target_amount') }}</p>
                            <p id="goal-monto-objetivo" data-field="monto_objetivo" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="goal-monto-objetivo-input"
                                    :title="__('main.goal_target_amount') . ' ($)'"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.deadline') }}</p>
                            <p id="goal-fecha-limite" class="modal-read-only text-sm font-semibold mt-1"></p>
                            <input type="date" id="goal-fecha-limite-input" class="modal-edit-only hidden w-full border p-1.5 rounded bg-transparent col7 text-sm">
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.description') }}</p>
                        <p id="goal-descripcion" class="modal-read-only text-sm"></p>
                        <textarea id="goal-descripcion-input" rows="3" maxlength="255" class="modal-edit-only hidden w-full border p-2 rounded bg-transparent col7 text-sm resize-none"></textarea>
                    </div>

                    <!-- MODO EDICIÓN: Sección opcional para registrar un abono -->
                    <div id="goal-payment-section" class="modal-edit-only pt-6 border-t-3 col4 hidden">
                        <label class="flex items-center space-x-2 cursor-pointer font-semibold text-xs uppercase">
                            <input type="checkbox" id="goal-add-payment-check" class="rounded">
                            <span>{{ __('main.ask_goal_payment') }}</span>
                        </label>

                        <div id="goal-payment-fields" class="space-y-4 hidden  mt-4">
                            <div class="col7 w-70">
                                <x-label
                                    name="goal-payment-titulo"
                                    :title="__('main.payment_goal_concept')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                            <div class="col7 w-70">
                                <x-label
                                    name="goal-payment-monto"
                                    :title="__('main.contribution_amount') . ' ($)'"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                            <x-select :title="__('main.issuing_account')" name="goalinfo-payment-wallet" first="{{ __('main.external') }}">
                                <x-option name="goalinfo-payment-wallet" value="">{{ __('main.external') }}</x-option>
                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                    <x-option name="goalinfo-payment-wallet" value="{{ $wallet->id }}">
                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual, 2) }})
                                    </x-option>
                                @endforeach
                            </x-select>
                            <span class="text-xs -mt-4 block">{{ __('main.wallet_selection') }}</span>
                        </div>
                    </div>

                    <!-- MODO LECTURA: Gráfica de abonos -->
                    <div class="modal-read-only pt-6 border-t-3 col4">
                        <p class="text-xs font-semibold uppercase col7 mb-3">{{ __('main.contribution_history') }}</p>
                        <div class="relative w-full h-48">
                            <canvas id="goal-chart"></canvas>
                            <p id="goal-chart-empty" class="hidden text-center col7 text-sm opacity-75">
                                {{ __('main.payment_advice') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 text-xs">
                        <div>
                            <span class="opacity-75">{{ __('main.category') }}:</span>
                            <p id="goal-category" class="font-bold col7"></p>
                        </div>
                        <div>
                            <span class="opacity-75">{{ __('main.initial_amount') }}:</span>
                            <p id="goal-monto-inicial" class="font-bold col7"></p>
                        </div>
                    </div>
                </div>
            </div>
        </x-panel>
        <x-panel
            name="info-debt"
            title="{{ __('main.debt_info') }}"
            :info="true">
            <div id="info-debt-container" class="p-6">

                <p id="debt-status" class="text-center col3 py-8 block">
                    {{ __('main.debt_select') }}
                </p>

                <div id="debt-details" class="space-y-8 hidden">

                    <div class="flex items-center space-x-4">
                        <img id="debt-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="debtinfo" />
                        </div>
                        <div>
                            <h3 id="debt-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                            <div class="modal-edit-only mb-3 hidden">
                                <x-label
                                    name="debt-titulo-input"
                                    :title="__('main.debt_name')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                            <span id="debt-estado-badge" class="px-2 py-1 text-xs col7 border rounded-full font-semibold uppercase"></span>
                        </div>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <!-- Restante -->
                    <div class="col7">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-xs font-semibold uppercase col7">{{ __('main.paid') }}</p>
                            <p id="debt-progreso-pct" class="text-xs font-bold col7"></p>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-300 overflow-hidden">
                            <div id="debt-progreso-bar" class="h-full bg-red-500" style="width:0%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-2 opacity-75">
                            <span id="debt-monto-actual">{{ __('main.minus') }}: $0.00</span>
                            <span id="debt-monto-inicial-txt">{{ __('main.original') }}: $0.00</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.interest_rate') }}</p>
                            <p id="debt-tasa" data-field="tasa_interes" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="debt-tasa-input"
                                    :title="__('main.interest_rate')"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.expiration_date') }}</p>
                            <p id="debt-fecha-vencimiento" class="modal-read-only text-sm font-semibold mt-1"></p>
                            <input type="date" id="debt-fecha-vencimiento-input" class="modal-edit-only hidden w-full border p-1.5 rounded bg-transparent col7 text-sm">
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.alert_priority') }}</p>
                        <p id="debt-prioridad-txt" class="modal-read-only text-sm font-semibold capitalize mt-1"></p>
                        <div class="modal-edit-only hidden">
                            <x-select title="" name="debtinfo-prioridad" first="{{ __('main.mid') }}">
                                <x-option name="debtinfo-prioridad" value="media">{{ __('main.mid') }}</x-option>
                                <x-option name="debtinfo-prioridad" value="alta">{{ __('main.tall') }}</x-option>
                                <x-option name="debtinfo-prioridad" value="baja">{{ __('main.low') }}</x-option>
                            </x-select>
                        </div>
                    </div>

                    <!-- MODO EDICIÓN: Sección opcional para registrar un pago -->
                    <div id="debt-payment-section" class="modal-edit-only pt-6 border-t-3 col4 hidden">
                        <label class="flex items-center space-x-2 cursor-pointer font-semibold text-xs uppercase">
                            <input type="checkbox" id="debt-add-payment-check" class="rounded">
                            <span>{{ __('main.debt_pay') }}</span>
                        </label>

                        <div id="debt-payment-fields" class="space-y-4 hidden mt-4">
                            <div class=" w-50">
                                <x-label
                                    name="debt-payment-titulo"
                                    :title="__('main.payment_goal_concept')"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                            <div>
                                <x-label
                                    name="debt-payment-monto"
                                    :title="__('main.amount_to_pay')"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                            <x-select title="{{ __('main.source_wallet') }} :" name="debtinfo-payment-wallet" first="{{ __('main.external') }}">
                                <x-option name="debtinfo-payment-wallet" value="">{{ __('main.external') }}</x-option>
                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                    <x-option name="debtinfo-payment-wallet" value="{{ $wallet->id }}">
                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual, 2) }})
                                    </x-option>
                                @endforeach
                            </x-select>
                            <span class="text-xs -mt-4 block">{{ __('main.debt_payment_wallet_note') }}</span>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-medium">
                                <input type="checkbox" id="debt-payment-minimo" class="w-4 h-4 rounded cursor-pointer">
                                {{ __('main.minimum_payment_question') }}
                            </label>
                        </div>
                    </div>

                    <!-- MODO LECTURA: Gráfica de pagos -->
                    <div class="modal-read-only pt-6 border-t-3 col4">
                        <p class="text-xs font-semibold uppercase col7 mb-3">{{ __('main.payment_history') }}</p>
                        <div class="relative w-full h-48">
                            <canvas id="debt-chart"></canvas>
                            <p id="debt-chart-empty" class="hidden text-center col7 text-sm opacity-75">
                                {{ __('main.record_payment_note') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 text-xs">
                        <div>
                            <span class="opacity-75">{{ __('main.category') }}:</span>
                            <p id="debt-category" class="font-bold col7"></p>
                        </div>
                        <div>
                            <span class="opacity-75">{{ __('main.initial_amount') }}:</span>
                            <p id="debt-monto-inicial" class="font-bold col7"></p>
                        </div>
                    </div>
                </div>
            </div>
        </x-panel>
        <x-panel
            name="info-paymentGoal"
            title="{{ __('main.goal_contribution') }}"
            :info="true">
            <div id="info-paymentGoal-container" class="p-6">

                <p id="paymentGoal-status" class="text-center col3 py-8 block">
                    {{ __('main.select_contribution_prompt') }}
                </p>

                <div id="paymentGoal-details" class="space-y-8 hidden">

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.goal') }}</p>
                        <p id="paymentGoal-goal-titulo" class="font-bold col4"></p>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <div class="flex items-center space-x-4">
                        <img id="paymentGoal-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="paymentGoalinfo" />
                        </div>
                        <div class="flex-1">
                            <h3 id="paymentGoal-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="paymentGoal-titulo-input"
                                    title="{{ __('main.contribution_concept') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.amount') }}</p>
                            <p id="paymentGoal-monto" data-field="monto" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="paymentGoal-monto-input"
                                    title="{{ __('main.amount_dollar') }}"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.date') }}</p>
                            <p id="paymentGoal-fecha" class="text-sm font-semibold mt-1"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.source_wallet') }}</p>
                        <p id="paymentGoal-wallet" class="modal-read-only text-sm font-semibold"></p>
                        <div class="modal-edit-only hidden">
                            <x-select title="{{ __('main.source_wallet') }} :" name="paymentGoalinfo-wallet" first="{{ __('main.external') }}">
                                <x-option name="paymentGoalinfo-wallet" value="">{{ __('main.external') }}</x-option>
                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                    <x-option name="paymentGoalinfo-wallet" value="{{ $wallet->id }}">
                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual, 2) }})
                                    </x-option>
                                @endforeach
                            </x-select>
                            <span class="text-xs -mt-4 block">{{ __('main.wallet_change_balance_note') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-panel>
        <x-panel
            name="info-paymentDebt"
            title="{{ __('main.debt_payment') }}"
            :info="true">
            <div id="info-paymentDebt-container" class="p-6">

                <p id="paymentDebt-status" class="text-center col3 py-8 block">
                    {{ __('main.select_payment_prompt') }}
                </p>

                <div id="paymentDebt-details" class="space-y-8 hidden">

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.debt') }}</p>
                        <p id="paymentDebt-debt-titulo" class="font-bold col4"></p>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <div class="flex items-center space-x-4">
                        <img id="paymentDebt-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="paymentDebtinfo" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 id="paymentDebt-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                                <span id="paymentDebt-minimo-badge" class="modal-read-only hidden px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-yellow-100 text-yellow-700 border-yellow-400"></span>
                            </div>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="paymentDebt-titulo-input"
                                    title="{{ __('main.payment_concept') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.amount') }}</p>
                            <p id="paymentDebt-monto" data-field="monto" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="paymentDebt-monto-input"
                                    title="{{ __('main.amount_dollar') }}"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.date') }}</p>
                            <p id="paymentDebt-fecha" class="text-sm font-semibold mt-1"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.source_wallet') }}</p>
                        <p id="paymentDebt-wallet" class="modal-read-only text-sm font-semibold"></p>
                        <div class="modal-edit-only hidden">
                            <x-select title="{{ __('main.source_wallet') }} :" name="paymentDebtinfo-wallet" first="{{ __('main.external') }}">
                                <x-option name="paymentDebtinfo-wallet" value="">{{ __('main.external') }}</x-option>
                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                    <x-option name="paymentDebtinfo-wallet" value="{{ $wallet->id }}">
                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual, 2) }})
                                    </x-option>
                                @endforeach
                            </x-select>
                            <span class="text-xs -mt-4 block">{{ __('main.wallet_change_balance_note') }}</span>
                        </div>
                    </div>

                    <div class="modal-edit-only hidden">
                        <label class="flex items-center space-x-2 cursor-pointer font-semibold text-xs uppercase">
                            <input type="checkbox" id="paymentDebt-minimo-input" class="rounded">
                            <span>{{ __('main.was_minimum_payment') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-panel>
        <x-panel
            name="info-investment"
            title="{{ __('main.investment_info') }}"
            :info="true">
            <div id="info-investment-container" class="p-6">

                <p id="investment-status" class="text-center col3 py-8 block">
                    {{ __('main.select_investment_prompt') }}
                </p>

                <div id="investment-details" class="space-y-8 hidden">

                    <div class="flex items-center space-x-4">
                        <img id="investment-icono" src="" alt="{{ __('main.icon_alt') }}" class="modal-read-only w-16 h-16 object-contain rounded border p-1">
                        <div class="modal-edit-only hidden">
                            <x-label-image name="investmentinfo" />
                        </div>
                        <div class="flex-1">
                            <h3 id="investment-titulo" data-field="titulo" class="modal-read-only text-lg font-bold col4"></h3>
                            <div class="modal-edit-only hidden w-50 mb-3">
                                <x-label
                                    name="investment-titulo-input"
                                    title="{{ __('main.investment_name') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <span id="investment-renta-badge" class="px-2 py-1 text-xs col7 border rounded-full font-semibold uppercase"></span>
                                <span id="investment-estado-badge" class="px-2 py-1 text-xs border rounded-full font-semibold uppercase"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bgcol4 h-1 w-full"></div>

                    <!-- Ganancia / Rentabilidad -->
                    <div class="col7">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-xs font-semibold uppercase col7">{{ __('main.profit') }}</p>
                            <p id="investment-rentabilidad" class="text-xs font-bold"></p>
                        </div>
                        <p id="investment-ganancia" class="modal-read-only text-lg font-bold"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.invested_amount') }}</p>
                            <p id="investment-monto-inicial" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="investment-monto-inicial-input"
                                    title="{{ __('main.invested_amount_dollar') }}"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.current_value') }}</p>
                            <p id="investment-valor-actual" data-field="valor_actual" class="modal-read-only text-lg font-bold col7"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="investment-valor-actual-input"
                                    title="{{ __('main.current_value_dollar') }}"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div id="investment-tasa-field">
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.interest_rate_percent') }}</p>
                            <p id="investment-tasa" class="modal-read-only text-sm font-semibold mt-1"></p>
                            <div class="modal-edit-only hidden">
                                <x-label
                                    name="investment-tasa-input"
                                    title="{{ __('main.interest_rate_percent') }}"
                                    type="number"
                                    color1="var(--col3)"
                                    color2="var(--col7)"
                                    w="w-full"
                                    :required="false"
                                />
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.expiration_date') }}</p>
                            <p id="investment-fecha-vencimiento" class="modal-read-only text-sm font-semibold mt-1"></p>
                            <input type="date" id="investment-fecha-vencimiento-input" class="modal-edit-only hidden w-full border p-1.5 rounded bg-transparent col7 text-sm">
                        </div>
                    </div>

                    <!-- MODO EDICIÓN: Estado de la inversión -->
                    <div class="modal-edit-only hidden">
                        <p class="text-xs font-semibold uppercase col7 mb-1">{{ __('main.status') }}</p>
                        <x-select title="" name="investmentinfo-estado" first="{{ __('main.active') }}">
                            <x-option name="investmentinfo-estado" value="activa">{{ __('main.active') }}</x-option>
                            <x-option name="investmentinfo-estado" value="finalizada">{{ __('main.finished') }}</x-option>
                            <x-option name="investmentinfo-estado" value="cancelada">{{ __('main.cancelled') }}</x-option>
                        </x-select>
                    </div>

                    <!-- MODO LECTURA: Gráfica Invertido vs Valor Actual -->
                    <div class="modal-read-only pt-6 border-t-3 col4">
                        <p class="text-xs font-semibold uppercase col7 mb-3">{{ __('main.invested') }} vs. {{ __('main.current_value') }}</p>
                        <div class="relative w-full h-48">
                            <canvas id="investment-chart"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 text-xs pt-6 border-t-3 col4">
                        <div>
                            <span class="opacity-75">{{ __('main.source_wallet') }}:</span>
                            <p id="investment-wallet" class="font-bold col7"></p>
                        </div>
                        <div>
                            <span class="opacity-75">{{ __('main.category') }}:</span>
                            <p id="investment-category" class="font-bold col7"></p>
                        </div>
                    </div>

                    <div class="text-right text-xs col7">
                        {{ __('main.acquisition_date') }}: <span id="investment-fecha-adquisicion"></span>
                    </div>
                </div>
            </div>
        </x-panel>
        <x-panel
            name="add"
            title="{{ __('main.add_record') }}"
        >
            <div class="w-full h-full overflow-y-auto barpag">
                <div class="w-full h-auto">
                    
                    <x-select
                        title="{{ __('main.select_type') }}"
                        name="add">
                        <x-option
                            name="add"
                            value="Ninguno"
                        >
                            {{ __('main.none') }}
                        </x-option>  
                        <x-option
                            name="add"
                            value="Billetera"
                        >
                            {{ __('main.wallet') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Salario"
                        >
                            {{ __('main.salary') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Inversión"
                        >
                            {{ __('main.investment') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Movimiento"
                        >
                            {{ __('main.movement') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Meta"
                        >
                            {{ __('main.goal') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Pago de Meta"
                        >
                            {{ __('main.payment_goal') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Deuda"
                        >
                            {{ __('main.debt') }}
                        </x-option>    
                        <x-option
                            name="add"
                            value="Pago de Deuda"
                        >
                            {{ __('main.debt_payment') }}
                        </x-option>    
                    </x-select>
                    <div id="-default" class="flex items-center justify-center text-xl h-100 w-full ">
                        {{ __('main.no_record_type_selected') }}
                    </div>
                    <form action="/wallet/create" method="POST" enctype="multipart/form-data" id="wallet-form" class="w-full flex items-center justify-center flex-col ">
                        @csrf
                        <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                            {{ __('main.new_wallet') }}
                        </h2>

                        <!-- Contenedor para errores generales -->
                        <div id="wallet-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                            <span class="text-red-700 font-medium text-sm block text-center" id="wallet-general-msg"></span>
                        </div>

                        <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center ">
                            <x-label 
                            name="wallet-titulo"
                            title="{{ __('main.wallet_name') }}"
                            maxlength="25"
                                color1="var(--col3)"
                                color2="var(--col4)"
                                w="w-full"
                                />
                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="wallet-titulo"></span>
                            </div>
                            <div class="mb-50 h-70 w-3/5 flex items-center justify-evenly">
                                <div class="flex flex-col">
                                    <x-label-image
                                    name="wallet"
                                    />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="wallet-image"></span>
                                </div>
                                <div class="w-full h-full  flex items-center justify-evenly flex-col p-5">
                                    
                                    <x-label 
                                    name="wallet-monto"
                                    title="{{ __('main.amount') }}"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                    type="number"
                                    />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="wallet-monto"></span>
                                    
                                    <x-select
                                    title="{{ __('main.select_type') }}"
                                    name="wallet"
                                    :required="true">
                                    <x-option
                                            name="wallet"
                                            value="ninguno"
                                        >
                                        {{ __('main.none') }}
                                        </x-option>
                                        <x-option
                                            name="wallet"
                                            value="efectivo"

                                        >
                                        {{ __('main.cash') }}
                                    </x-option>
                                        <x-option
                                            name="wallet"
                                                value="debito"

                                            >
                                            {{ __('main.debit') }}
                                        </x-option>
                                        <x-option
                                        name="wallet"
                                            value="credito"
                                        
                                        >
                                            {{ __('main.credit_card') }}   
                                        </x-option>
                                        <x-option
                                        name="wallet"
                                            value="ahorro"

                                        >
                                            {{ __('main.savings') }} 
                                        </x-option>
                                    </x-select>
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="wallet-select-value"></span>
                                    
                                </div>
                            </div>
                            
                        </form>
                        
                        <form action="/income/create" method="POST" enctype="multipart/form-data" id="income-form" class="w-full flex items-center justify-center flex-col ">
                            @csrf
                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.new_salary') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="income-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="income-general-msg"></span>
                            </div>

                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center ">
                                <x-label 
                                    name="income-titulo"
                                    title="{{ __('main.salary') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                    />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-titulo"></span>
                            </div>
                            <div class="mb-10 h-120 w-3/5 flex items-center justify-evenly">
                                <div class="flex flex-col">
                                    <x-label-image
                                        name="income"
                                    />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="income-image"></span>
                                </div>
                                <div class="w-full h-full  flex items-center justify-evenly flex-col p-5">
                                    <x-label 
                                        name="income-category"
                                        title="{{ __('main.category') }}"
                                        color1="var(--col3)"
                                        color2="var(--col4)"
                                        w="w-full"
                                        />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-category"></span>
                                    <x-label 
                                        name="income-monto"
                                        title="{{ __('main.amount') }}"
                                        color1="var(--col3)"
                                        color2="var(--col4)"
                                        type="number"
                                        w="w-full"
                                        />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-monto"></span>
                                            
                                        <x-select
                                            title="{{ __('main.select_type') }}"
                                            name="income"
                                            :required="true">
                                            <x-option
                                                name="income"
                                            value="ninguno"

                                                
                                            >
                                                {{ __('main.none') }}
                                            </x-option>
                                            <x-option
                                                name="income"
                                            value="diario"

                                            >
                                                {{ __('main.daily') }}
                                            </x-option>
                                            <x-option
                                                name="income"
                                            value="semanal"

                                            >
                                                {{ __('main.weekly') }}
                                            </x-option>
                                            <x-option
                                                name="income"
                                            value="quincenal"

                                            >
                                                {{ __('main.biweekly') }}   
                                            </x-option>
                                            <x-option
                                                name="income"
                                            value="anual"

                                            >
                                                {{ __('main.yearly') }}   
                                            </x-option>
                                        </x-select>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-select-value"></span>
                                        <div class="text-center">
                                            {{ __('main.income_frequency_note') }}
                                        </div>
                                        <input type="date" class="border cursor-pointer p-2 col7 bgcol3 mt-2" required name="income-ejecucion">
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-ejecucion"></span>
                                        
                                </div>
                                
                            </div>

                            <div class="mb-60 h-auto w-3/5 flex flex-col items-center justify-center">
                                <x-select
                                    title="{{ __('main.select_wallet') }}"
                                    name="income-wallet"
                                    :required="true">

                                    <x-option name="income-wallet">
                                    {{ __('main.none') }}
                                    </x-option>

                                    @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)

                                    <x-option
                                    name="income-wallet"
                                    value="{{ $wallet->id }}"
                                    >
                                    {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})

                                    </x-option>

                                    @endforeach

                                </x-select>
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="income-wallet-select-value"></span>
                            
                            </div>
                        </form>
                        
                        
                       <form action="/investment/create" method="POST" enctype="multipart/form-data" id="investment-form" class="w-full flex items-center justify-center flex-col">
                            @csrf
                            @if($wallets->whereIn('tipo', ['ahorro', 'debito', 'efectivo'])->isNotEmpty())
                                <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                    {{ __('main.new_investment') }}
                                </h2>

                                <!-- Contenedor para errores generales (ej: fondos insuficientes) -->
                                <div id="investment-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                    <span class="text-red-700 font-medium text-sm block text-center" id="investment-general-msg"></span>
                                </div>

                                <!-- Campo: Título -->
                                <div class="mb-6 h-auto w-3/5 flex flex-col items-center justify-center">
                                    <x-label 
                                        name="investment-titulo"
                                        title="{{ __('main.investment_name') }}"
                                        maxlength="25"
                                        color1="var(--col3)"
                                        color2="var(--col4)"
                                        w="w-full"
                                    />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="investment-titulo"></span>
                                </div>

                                <div class="mb-50 h-auto w-full sm:w-3/5 flex flex-col lg:flex-row items-center lg:items-start justify-evenly">
                                    
                                    <!-- Imagen -->
                                    <div class="mb-6 lg:mb-0 flex flex-col">
                                        <x-label-image name="investment" />
                                        <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-image"></span>
                                    </div>
                                    
                                    <div class="w-full h-auto flex flex-col p-5">
                                        
                                        <div class="w-full flex flex-col lg:flex-row items-end mb-6">
                                            <!-- Categoría -->
                                            <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                                <x-label 
                                                    name="investment-category"
                                                    title="{{ __('main.category') }}"
                                                    color1="var(--col3)"
                                                    color2="var(--col4)"
                                                    w="w-full"
                                                />
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-category"></span>
                                            </div>

                                            <!-- Monto -->
                                            <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                                <x-label 
                                                    name="investment-monto"
                                                    title="{{ __('main.initial_amount') }}"
                                                    color1="var(--col3)"
                                                    color2="var(--col4)"
                                                    type="number"
                                                    w="w-full"
                                                />
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-monto"></span>
                                            </div>
                                        </div>

                                        <div class="w-full flex flex-col lg:flex-row items-end mb-6">
                                            <!-- Billetera Origen -->
                                            <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                                <x-select :required="true" title="{{ __('main.source_wallet') }}:" name="investment-wallet">
                                                    <x-option name="investment-wallet" value="Ninguno">{{ __('main.none') }}</x-option>
                                                    @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                                        <x-option name="investment-wallet" value="{{ $wallet->id }}">
                                                            {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})
                                                        </x-option>
                                                    @endforeach
                                                </x-select>
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-wallet-select-value"></span>
                                            </div>

                                            <!-- Tipo de Renta -->
                                            <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                                <x-select :required="true" title="{{ __('main.investment_type') }}" name="investment-renta" id="investment-renta-select">
                                                    <x-option name="investment-renta" value="Ninguno">{{ __('main.none') }}</x-option>
                                                    <x-option name="investment-renta" value="variable">{{ __('main.variable_stocks') }}</x-option>
                                                    <x-option name="investment-renta" value="fija">{{ __('main.fixed_term') }}</x-option>
                                                </x-select>
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-renta-select-value"></span>
                                            </div>
                                        </div>

                                        <div class="w-full flex flex-col lg:flex-row items-start mb-6 gap-4 lg:gap-0">
                                            <!-- Tasa de Interés -->
                                            <div id="wrapper-tasa-interes" class="w-full lg:w-1/2 lg:pr-2 hidden flex-col">
                                                <x-label 
                                                    name="investment-tasa"
                                                    title="{{ __('main.annual_interest_rate') }}"
                                                    color1="var(--col3)"
                                                    color2="var(--col4)"
                                                    type="number"
                                                    w="w-full"
                                                />
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-tasa"></span>
                                            </div>

                                            <!-- Fecha Vencimiento -->
                                            <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                                <span class="text-xs font-semibold mb-1" style="color: var(--col3)">{{ __('main.expiration_date') }}</span>
                                                <input type="date" class="border cursor-pointer p-2 w-full lg:w-40 col7 bgcol3 rounded-md" name="investment-vencimiento" required>
                                                <span class="error-msg text-red-500 text-xs mt-1" data-error-for="investment-vencimiento"></span>
                                            </div>
                                        </div>

                                    </div>                  
                                </div>
                            @else
                                {{ __('main.need_wallet_for_investment') }}
                            @endif
                        </form>
                        
                        <form action="/transaction/create" method="POST" enctype="multipart/form-data" id="transaction-form" class="w-full flex items-center justify-center flex-col mb-50">
                            @csrf
                            @if($wallets->whereIn('tipo', ['ahorro', 'debito', 'efectivo'])->isNotEmpty())

                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.new_movement') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="transaction-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="transaction-general-msg"></span>
                            </div>

                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center ">
                                <x-label 
                                    name="transaction-titulo"
                                    title="{{ __('main.movement') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                    />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-titulo"></span>
                            </div>
                            <div class="mb-10 h-auto w-3/5 flex items-center justify-evenly">
                                <div class="flex flex-col">
                                    <x-label-image
                                        name="transaction"
                                    />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="transaction-image"></span>
                                </div>
                                <div class="w-full h-full  flex items-center justify-evenly flex-col p-5">
                                    <x-label 
                                        name="transaction-category"
                                        title="{{ __('main.category') }}"
                                        color1="var(--col3)"
                                        color2="var(--col4)"
                                        w="w-full"
                                        maxlength="25"
                                        />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-category"></span>
                                    <x-label 
                                        name="transaction-monto"
                                        title="{{ __('main.amount') }}"
                                        color1="var(--col3)"
                                        color2="var(--col4)"
                                        type="number"
                                        w="w-full"
                                        />
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-monto"></span>
                                            
                                        <x-select title="{{ __('main.select_type') }}" name="transaction" id="transaction-tipo-select" first="{{ __('main.income') }}">
                                            <x-option name="transaction" value="Ingreso">{{ __('main.income') }}</x-option>
                                            <x-option name="transaction" value="Gasto">{{ __('main.expense') }}</x-option>
                                            <x-option name="transaction" value="Transferencia">{{ __('main.transfer') }}</x-option>
                                        </x-select>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-select-value"></span>
                                    
                                </div>

                            </div>
                            {{-- 2. BLOQUE INGRESO: Solo destino (Sin Crédito) --}}
                                <div id="bloque-ingreso" class="grupo-movimiento w-full flex flex-col items-center">
                                    <x-select title="{{ __('main.destination_wallet_income') }}" name="transaction-destino-ingreso" first="{{ __('main.select_ellipsis') }}">
                                        @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                            <x-option name="transaction-destino-ingreso" value="{{ $wallet->id }}">{{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})</x-option>
                                        @endforeach
                                    </x-select>
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-destino-ingreso-select-value"></span>
                                </div>

                                {{-- 3. BLOQUE GASTO: Solo origen/destino de pago (Todas las cuentas) --}}
                                <div id="bloque-gasto" class="grupo-movimiento w-full  flex-col items-center hidden">
                                    <x-select title="{{ __('main.payment_wallet_expense') }}" name="transaction-destino-gasto" first="{{ __('main.select_ellipsis') }}">
                                        @foreach($wallets as $wallet)
                                            <x-option name="transaction-destino-gasto" value="{{ $wallet->id }}">{{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})</x-option>
                                        @endforeach
                                    </x-select>
                                    <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-destino-gasto-select-value"></span>
                                </div>

                                {{-- 4. BLOQUE TRANSFERENCIA: Origen (Sin crédito) y Destino (Todas) --}}
                                <div id="bloque-transferencia" class="grupo-movimiento w-full  flex-col items-center hidden">
                                    <div class="mb-5 w-full flex flex-col items-center">
                                        <x-select title="{{ __('main.source_wallet') }}:" name="transaction-origen" first="{{ __('main.external') }}">
                                            <x-option name="transaction-origen" value="Externa">{{ __('main.external') }}</x-option>
                                            @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                                <x-option name="transaction-origen" value="{{ $wallet->id }}">{{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})</x-option>
                                            @endforeach
                                        </x-select>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-origen-select-value"></span>
                                    </div>
                                    <div class="w-full flex flex-col items-center">
                                        <x-select title="{{ __('main.destination_wallet') }}" name="transaction-destino" first="{{ __('main.select_ellipsis') }}">
                                            @foreach($wallets as $wallet)
                                                <x-option name="transaction-destino" value="{{ $wallet->id }}">{{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})</x-option>
                                            @endforeach
                                        </x-select>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="transaction-destino-select-value"></span>
                                    </div>
                                </div>
                            @else
                                {{ __('main.need_wallet_for_transaction') }}
                            @endif
                        </form>

                        <form action="/goal/create" method="POST" enctype="multipart/form-data" id="goal-form" class="w-full flex items-center justify-center flex-col">
                            @csrf
                            
                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.new_savings_goal') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="goal-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="goal-general-msg"></span>
                            </div>

                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center">
                                <x-label 
                                    name="goal-titulo"
                                    title="{{ __('main.goal_name') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-titulo"></span>
                            </div>

                            <div class="mb-10 h-auto w-full sm:w-3/5 flex flex-col lg:flex-row items-center lg:items-start justify-evenly gap-6">
                                
                                <div class="mb-6 lg:mb-0 flex flex-col">
                                    <x-label-image name="goal" />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="goal-image"></span>
                                </div>
                                
                                <div class="w-full h-auto flex flex-col p-5 gap-6">
                                    
                                    <div class="w-full flex flex-col lg:flex-row items-end">
                                        <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <x-label 
                                                name="goal-category"
                                                title="{{ __('main.category') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                w="w-full"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-category"></span>
                                        </div>
                                        <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                            <x-label 
                                                name="goal-monto-objetivo"
                                                title="{{ __('main.goal_target_amount_dollar') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                type="number"
                                                w="w-full"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-monto-objetivo"></span>
                                        </div>
                                    </div>

                                    <div class="w-full flex flex-col lg:flex-row items-end">
                                        <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <x-label 
                                                name="goal-monto-inicial"
                                                title="{{ __('main.goal_initial_amount_optional') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                type="number"
                                                w="w-full"
                                                :required="false"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-monto-inicial"></span>
                                        </div>
                                        <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                            <span class="text-xs mb-1" style="color: var(--col3)">{{ __('main.deadline') }}</span>
                                            <input type="date" class="border cursor-pointer p-2 w-full col7 bgcol3 rounded-md h-10" name="goal-fecha-limite" required>
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-fecha-limite"></span>
                                        </div>
                                    </div>

                                    <div class="w-full flex flex-col">
                                        <span class="text-xs mb-1">{{ __('main.description_notes') }}</span>
                                        <textarea 
                                            name="goal-descripcion" 
                                            rows="3" 
                                            maxlength="255"
                                            class="border p-2 w-full col7 bgcol3 rounded-md resize-none outline-none transition-colors text-sm"
                                            placeholder="{{ __('main.goal_savings_placeholder') }}"
                                        ></textarea>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="goal-descripcion"></span>
                                    </div>

                                </div>                                    
                            </div>

                          

                        </form>         

                        <form action="/payment-goal/create" method="POST" enctype="multipart/form-data" id="paymentgoal-form" class="w-full flex items-center justify-center flex-col">
                            @csrf
                            @if($goals->isNotEmpty())
                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.new_goal_payment') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="paymentgoal-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="paymentgoal-general-msg"></span>
                            </div>

                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center">
                                <x-label 
                                    name="paygoal-titulo"
                                    title="{{ __('main.payment_contribution_concept') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="paygoal-titulo"></span>
                            </div>

                            <div class="mb-10 h-auto w-full sm:w-3/5 flex flex-col lg:flex-row items-center lg:items-start justify-evenly gap-6">
                                
                                <div class="mb-6 lg:mb-0 flex flex-col">
                                    <x-label-image name="paygoal" />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="paygoal-image"></span>
                                </div>
                                
                                <div class="w-full h-auto flex flex-col p-5 gap-6 mb-30">
                                    
                                    <div class="w-full flex flex-col lg:flex-row items-end">
                                        <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <x-label 
                                                name="paygoal-category"
                                                title="{{ __('main.category') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                w="w-full"
                                                :required="false"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="paygoal-category"></span>
                                        </div>
                                        <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                            <x-label 
                                                name="paygoal-monto"
                                                title="{{ __('main.contribution_amount_dollar') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                type="number"
                                                w="w-full"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="paygoal-monto"></span>
                                        </div>
                                    </div>

                                    <div class="w-full flex flex-col xl:flex-row gap-4">
                                        
                                        
                                        <div class="w-full lg:w-1/2 flex items-center flex-col">
                                            <x-select title="{{ __('main.source_wallet') }} :" name="paygoal-wallet" first="{{ __('main.external') }}">
                                                <x-option name="paygoal-wallet" value="">{{ __('main.external') }}</x-option>
                                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                                    <x-option name="paygoal-wallet" value="{{ $wallet->id }}">
                                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})
                                                    </x-option>
                                                @endforeach
                                            </x-select>
                                            <span class="text-xs">(No se muestran billeteras credito)</span>
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="paygoal-wallet-select-value"></span>

                                        </div>

                                        <div class="w-full lg:w-1/2">
                                            <x-select title="{{ __('main.target_goal') }}" name="paygoal-target" :required="true" first="{{ __('main.select_goal_placeholder') }}">
                                                <x-option name="paygoal-target" value="">{{ __('main.select_goal_placeholder') }}</x-option>
                                                @foreach($goals as $goal)
                                                    <x-option name="paygoal-target" value="{{ $goal->id }}">
                                                        {{ $goal->titulo }} (Faltan: ${{ number_format($goal->monto_objetivo - $goal->monto_actual, 2) }})
                                                    </x-option>
                                                @endforeach
                                            </x-select>
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="paygoal-target-select-value"></span>
                                        </div>

                                    </div>

                                </div>                                    
                            </div>
                            @else
                                <div class="w-full text-center">
                                        {{ __('main.need_goal_before_saving') }}
                                </div>
                            @endif


                        </form>
                        
                        <form action="/debt/create" method="POST" enctype="multipart/form-data" id="debt-form" class="w-full p-4 flex items-center justify-center flex-col">
                            @csrf
                            
                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.new_main_debt') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="debt-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="debt-general-msg"></span>
                            </div>

                            {{-- Concepto o Nombre de la Deuda --}}
                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center">
                                <x-label 
                                    name="debt-titulo"
                                    title="{{ __('main.debt_to_whom_placeholder') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-titulo"></span>
                            </div>

                            <div class="mb-10 h-auto w-full sm:w-3/5 flex flex-col items-center justify-evenly gap-6">
                                
                                {{-- Imagen vinculada exactamente a: let debt = new ImagePreview("debt"); --}}
                                <div class="mb-6 lg:mb-0 flex flex-col">
                                    <x-label-image name="debt" />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="debt-image"></span>
                                </div>
                                
                                <div class="w-full h-auto flex flex-col p-5 gap-6">
                                    
                                    {{-- Fila 1: Categoría y Monto Inicial --}}
                                    <div class="w-full flex flex-col lg:flex-row items-end">
                                        <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <x-label 
                                                name="debt-category"
                                                title="{{ __('main.category_example_placeholder') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                w="w-full"
                                                :required="false"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-category"></span>
                                        </div>
                                        <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                            <x-label 
                                                name="debt-monto"
                                                title="{{ __('main.debt_initial_amount') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                type="number"
                                                w="w-full"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-monto"></span>
                                        </div>
                                    </div>



                                </div>              
                                <div class="w-full flex flex-col lg:flex-row items-center">
                                    <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <span class="text-xs">{{ __('main.expiration_date') }}: </span>
                                        <input type="date" class="border cursor-pointer p-2 w-full col7 bgcol3 rounded-md h-10" name="debt-vencimiento" required>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-vencimiento"></span>

                                    </div>
                                    <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                        <x-label 
                                            name="debt-tasa"
                                            title="{{ __('main.interest_rate_optional') }}"
                                            color1="var(--col3)"
                                            color2="var(--col4)"
                                            type="number"
                                            w="w-full"
                                            value="0"
                                        />
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-tasa"></span>
                                    </div>
                                </div>

                                {{-- Fila 3: Prioridad de Alerta --}}
                                <div class="w-full flex flex-col xl:flex-row gap-4">
                                    <div class="w-full flex flex-col">
                                        <x-select title="{{ __('main.alert_priority') }} :" :required="true" name="debt-prioridad" first="{{ __('main.none') }}">
                                            <x-option name="debt-prioridad" value="Ninguno">{{ __('main.none') }}</x-option>
                                            <x-option name="debt-prioridad" value="media">{{ __('main.mid') }}</x-option>
                                            <x-option name="debt-prioridad" value="alta">{{ __('main.tall') }}</x-option>
                                            <x-option name="debt-prioridad" value="baja">{{ __('main.low') }}</x-option>
                                        </x-select>
                                        <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="debt-prioridad-select-value"></span>
                                    </div>
                                </div>      
                            </div>                
                        </form>
                        
                        
                        <form action="/payment-debt/create" method="POST" enctype="multipart/form-data" id="paymentdebt-form" class="w-full flex items-center justify-center flex-col">
                            @csrf
                            
                            @if($debts->isNotEmpty())
                            <h2 class="mt-15 mb-15 h-auto text-xl sm:text-4xl font-bold w-4/5 text-center">
                                {{ __('main.register_debt_payment') }}
                            </h2>

                            <!-- Contenedor para errores generales -->
                            <div id="paymentdebt-general-error" class="w-4/5 mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                                <span class="text-red-700 font-medium text-sm block text-center" id="paymentdebt-general-msg"></span>
                            </div>

                            {{-- Concepto del Pago --}}
                            <div class="mb-10 h-auto w-3/5 flex flex-col items-center justify-center">
                                <x-label 
                                    name="payment-titulo"
                                    title="{{ __('main.debt_payment_concept_placeholder') }}"
                                    maxlength="25"
                                    color1="var(--col3)"
                                    color2="var(--col4)"
                                    w="w-full"
                                />
                                <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="payment-titulo"></span>
                            </div>

                            <div class="mb-10 h-auto w-full sm:w-3/5 flex flex-col lg:flex-row items-center lg:items-start justify-evenly gap-6">
                                
                                {{-- Imagen vinculada exactamente a: let payment = new ImagePreview("payment"); --}}
                                <div class="mb-6 lg:mb-0 flex flex-col">
                                    <x-label-image name="paymentdebt" />
                                    <span class="error-msg text-red-500 text-xs mt-1" data-error-for="paymentdebt-image"></span>
                                </div>
                                
                                <div class="w-full h-auto flex flex-col p-5 gap-6 mb-30">
                                    
                                    {{-- Fila 1: Categoría (Opcional) y Monto a Abonar --}}
                                    <div class="w-full flex flex-col lg:flex-row items-end">
                                        <div class="w-full lg:w-1/2 lg:pr-2 mb-6 lg:mb-0 flex flex-col">
                                            <x-label 
                                                name="payment-category"
                                                title="{{ __('main.category_optional') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                w="w-full"
                                                :required="false"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="payment-category"></span>
                                        </div>
                                        <div class="w-full lg:w-1/2 lg:pl-2 flex flex-col">
                                            <x-label 
                                                name="payment-monto"
                                                title="{{ __('main.amount_to_pay') }}"
                                                color1="var(--col3)"
                                                color2="var(--col4)"
                                                type="number"
                                                w="w-full"
                                            />
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="payment-monto"></span>
                                        </div>
                                    </div>

                                    {{-- Fila 2: Billetera Origen (wallet_id) y Deuda Destino (debt_id) --}}
                                    <div class="w-full flex flex-col lg:flex-row gap-4">
                                        {{-- Billetera Origen --}}
                                        <div class="w-full lg:w-1/2 flex items-center flex-col">
                                            <x-select title="{{ __('main.source_wallet') }} :" name="payment-wallet" first="{{ __('main.external') }}">
                                                <x-option name="payment-wallet" value="">{{ __('main.external') }}</x-option>
                                                @foreach($wallets->where('tipo', '!=', 'credito')->values() as $wallet)
                                                    <x-option name="payment-wallet" value="{{ $wallet->id }}">
                                                        {{ $wallet->titulo }} (${{ number_format($wallet->monto_actual) }})
                                                    </x-option>
                                                @endforeach
                                            </x-select>
                                            <span class="text-xs mt-1">({{ __('main.credit_wallets_hidden_note') }})</span>
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="payment-wallet-select-value"></span>
                                        </div>

                                        {{-- Deuda Destino --}}
                                        <div class="w-full lg:w-1/2">
                                            <x-select title="{{ __('main.debt_to_apply_payment') }}" name="payment-target" :required="true" first="{{ __('main.select_debt_placeholder') }}">
                                                <x-option name="payment-target" value="">{{ __('main.select_debt_placeholder') }}</x-option>
                                                @foreach($debts as $debt)
                                                    <x-option name="payment-target" value="{{ $debt->id }}">
                                                        {{ $debt->titulo }} (Resta: ${{ number_format($debt->monto_actual, 2) }})
                                                    </x-option>
                                                @endforeach
                                            </x-select>
                                            <span class="error-msg text-red-500 text-xs mt-1 self-start" data-error-for="payment-target-select-value"></span>
                                        </div>
                                    </div>

                                    {{-- Fila 3: Checkbox estratégico para Pago Mínimo (pago_minimo) --}}
                                    <div class="w-full flex items-center gap-3 pl-1 mt-2">
                                        <input 
                                            type="checkbox" 
                                            name="payment-minimo" 
                                            id="payment-minimo" 
                                            value="1" 
                                            class="w-4 h-4 rounded cursor-pointer"
                                        >
                                        <label for="payment-minimo" class="text-sm font-medium  select-none cursor-pointer">
                                            {{ __('main.minimum_payment_question') }}
                                        </label>
                                    </div>

                                </div>                                    
                            </div>
                            @else
                                <div class="w-full text-center py-8 ">
                                    {{ __('main.no_pending_debts') }}
                                </div>
                            @endif
                        </form>
                        
                    </div>
            </div>
        </x-panel>
        <x-panel name="filter" title="{{ __('main.filter') }}">
            <form action="{{ route('records.index') }}" method="POST" id="filter-form" class="w-full h-full overflow-y-auto barpag p-10">
                @csrf

                <input type="hidden" name="sort_by" value="{{ $sortBy ?? 'date_desc' }}">
                <input type="hidden" name="search" id="filter-search-input" value="{{ $search ?? '' }}">

                <div class="w-full h-auto">
                    
                    <div class="w-full h-auto pt-5">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.sort_by') }}</h1>
                        <div class="w-full h-auto flex flex-wrap gap-3">
                            <x-button cont="div" name="sort_by" value="date_desc" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex font-normal items-center justify-center text-xs lg:text-sm filter-btn-form {{ ($sortBy ?? 'date_desc') === 'date_desc' ? 'focus-button' : '' }}">
                                {{ __('main.most_recent_first') }}
                            </x-button>
                            <x-button cont="div" name="sort_by" value="amount_desc" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ ($sortBy ?? '') === 'amount_desc' ? 'focus-button' : '' }}">
                                {{ __('main.highest_amount') }}
                            </x-button>
                            <x-button cont="div" name="sort_by" value="alpha_asc" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ ($sortBy ?? '') === 'alpha_asc' ? 'focus-button' : '' }}">
                                {{ __('main.alphabetical_az') }}
                            </x-button>
                        </div>
                    </div>

                    <div class="w-full h-auto pt-5">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.money_range') }}</h1>
                        <div class="w-full h-auto flex flex-wrap gap-3">
                            <x-button cont="div" name="range_amount" value="low" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($rangeAmount) && in_array('low', $rangeAmount) ? 'focus-button' : '' }}">
                                {{ __('main.low_amounts') }}
                            </x-button>
                            <x-button cont="div" name="range_amount" value="medium" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($rangeAmount) && in_array('medium', $rangeAmount) ? 'focus-button' : '' }}">
                                {{ __('main.medium_amounts') }}
                            </x-button>
                            <x-button cont="div" name="range_amount" value="high" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($rangeAmount) && in_array('high', $rangeAmount) ? 'focus-button' : '' }}">
                                {{ __('main.high_amounts') }}
                            </x-button>
                        </div>
                    </div>

                    <div class="w-full h-auto pt-5">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.time_period') }}</h1>
                        <div class="w-full h-auto flex flex-wrap gap-3">
                            <x-button cont="div" name="time_frame" value="today" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($timeFrame ?? []) && in_array('today', $timeFrame ?? []) ? 'focus-button' : '' }}">
                                {{ __('main.today') }}
                            </x-button>
                            <x-button cont="div" name="time_frame" value="week" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($timeFrame ?? []) && in_array('week', $timeFrame ?? []) ? 'focus-button' : '' }}">
                                {{ __('main.this_week') }}
                            </x-button>
                            <x-button cont="div" name="time_frame" value="month" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($timeFrame ?? []) && in_array('month', $timeFrame ?? []) ? 'focus-button' : '' }}">
                                {{ __('main.this_month') }}
                            </x-button>
                            <x-button cont="div" name="time_frame" value="year" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($timeFrame ?? []) && in_array('year', $timeFrame ?? []) ? 'focus-button' : '' }}">
                                {{ __('main.this_year') }}
                            </x-button>
                        </div>
                    </div>

                    <div class="w-full h-auto pt-5">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.record_type') }}</h1>
                        <div class="w-full h-auto flex flex-wrap gap-3">
                            @foreach(['wallet' => __('main.wallet'), 'income' => __('main.salary'), 'transaction' => __('main.transaction'), 'investment' => __('main.investment'), 'goal' => __('main.goal'), 'paymentGoal' => __('main.payment_goal'), 'debt' => __('main.debt'), 'paymentDebt' => __('main.debt_payment')] as $key => $label)
                                <x-button cont="div" name="record_type" value="{{ $key }}" color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" 
                                    class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($recordType) && in_array($key, $recordType) ? 'focus-button' : '' }}">
                                    {{ $label }}
                                </x-button>
                            @endforeach
                        </div>
                    </div>
                    <div class="w-full h-auto pt-8 pb-4">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.download_filtered_results') }}</h1>
                        <div class="w-full h-auto flex flex-wrap gap-3">
                            <x-button type="button" id="export-excel-btn" data-export-url="{{ route('records.export.excel') }}" color1="green" color2="white" colortext="var(--col1)"
                                class="p-2 flex items-center font-bold justify-center text-xs lg:text-sm gap-2">
                                {{ __('main.download_excel') }}
                            </x-button>
                            <x-button type="button" id="export-pdf-btn" data-export-url="{{ route('records.export.pdf') }}" color1="red" color2="white" colortext="var(--col1)"
                                class="p-2 flex items-center font-bold justify-center text-xs lg:text-sm gap-2">
                                {{ __('main.download_pdf') }}
                            </x-button>
                        </div>
                    </div>
                    <div class="h-15 w-full flex justify-center mt-5">
                        <input id="category-search" class="bgcol2 transition-all lg:text-lg xl:text-xl m-3 text-xs w-full h-10 mb-15 col7 p-2" placeholder="{{ __('main.search_category_placeholder') }}"/>
                        <button type="button" class="h-10 cursor-pointer transition-colors aspect-square perfil-div-nav rounded-full flex items-center justify-center m-3">
                            <img src="{{ asset('images/search.png') }}" alt="" class="scale-[0.75]">
                        </button>
                    </div>
                    

                    <div class="w-full h-auto pt-5">
                        <h1 class="text-lg font-bold mb-3 col7">{{ __('main.categories') }}</h1>
                        <div id="categories-container" class="w-full h-auto flex flex-wrap gap-3">
                            @foreach($categories as $cat)
                                <div class="category-item-wrapper" data-name="{{ strtolower($cat->categoria) }}">
                                    <x-button 
                                        cont="div"
                                        name="category_id" 
                                        value="{{ $cat->id }}" 
                                        color1="var(--col4)" 
                                        color2="var(--col3)" 
                                        colortext="var(--col1)" 
                                        class="p-2 flex items-center justify-center text-xs lg:text-sm filter-btn-form {{ is_array($currentCategory ?? []) && in_array($cat->id, $currentCategory ?? []) ? 'focus-button' : '' }}">
                                        {{ $cat->categoria }}
                                    </x-button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </form>
        </x-panel>
        <x-footer/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
</x-layout>