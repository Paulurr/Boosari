<x-layout title="Usuarios">
    <x-slot:head>
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/js/paneles.js',
            'resources/js/adminUsers.js',
        ])
    </x-slot:head>

    <x-nav/>

    <h1 class="col7 text-center pt-25 pb-15 text-4xl font-bold">
        Gestión de Usuarios
    </h1>

    <div class="lg:pl-30 lg:pr-30 md:pl-15 md:pr-15 pl-2 pr-2 pb-20 flex flex-col items-center gap-10 min-h-screen">

        @if (session('status'))
            <div class="w-full lg:w-3/5 bgcol4 col1 p-3 rounded-xs text-center text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Buscador + crear --}}
        <div class="w-full lg:w-4/5 flex flex-col gap-5">
            <div class="h-10 w-full flex justify-center relative gap-3">
                <form action="{{ route('usuarios.index') }}" method="GET" id="search-users-form" class="h-10 w-full flex justify-end">
                    @if($roleFilter !== '')
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                    @endif
                    <input
                        type="text"
                        id="search-users-input"
                        name="search"
                        value="{{ $search }}"
                        class="bgcol1 transition-all lg:text-lg xl:text-xl text-xs w-full h-10 col7 p-2"
                        placeholder="Busca por nombre o correo"
                    />
                    <button type="submit" class="ml-2 cursor-pointer overflow-hidden w-auto aspect-square perfil-div-nav flex items-center justify-center transition-colors rounded-full">
                        <img src="{{ asset('images/search.png') }}" alt="" class="h-full aspect-square scale-[0.75]">
                    </button>
                </form>
            </div>

            <div class="w-full flex flex-wrap items-center justify-between gap-3">
                <div id="role-filters" class="flex flex-wrap gap-3" data-current-role="{{ $roleFilter }}">
                    @php
                        $filtros = ['' => 'Todos', '1' => 'Usuario', '2' => 'Moderador', '3' => 'Admin'];
                    @endphp
                    @foreach ($filtros as $valor => $etiqueta)
                        <a href="{{ route('usuarios.index', array_filter(['role' => $valor, 'search' => $search])) }}" class="role-filter-link" data-role="{{ $valor }}">
                            <x-button
                                cont="div"
                                color1="var(--col4)"
                                color2="var(--col3)"
                                colortext="var(--col1)"
                                class="role-filter-btn p-2 flex items-center justify-center text-xs lg:text-sm {{ (string) $roleFilter === $valor ? 'focus-button' : '' }}"
                            >
                                {{ $etiqueta }}
                            </x-button>
                        </a>
                    @endforeach
                </div>

                @if ($esAdmin)
                    <a href="{{ route('usuarios.create') }}">
                        <x-button
                            color1="var(--col3)"
                            color2="var(--col4)"
                            colortext="var(--col7)"
                            class="p-4 w-auto text-xs lg:text-sm flex justify-center items-center"
                        >
                            + Crear usuario
                        </x-button>
                    </a>
                @endif
            </div>
        </div>

        {{-- Resultados: grid de usuarios + paginación (se reemplaza entero vía AJAX) --}}
        <div id="users-results" class="w-full flex flex-col items-center gap-10 transition-opacity duration-200">
            {{--
                Grid de usuarios + paginación.
                Se usa tanto en la carga normal de IndexAdmin.blade.php como en las
                respuestas AJAX del buscador/filtros/paginación (UserManagementController::index).
            --}}
            <div id="users-grid" class="w-full lg:w-4/5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($users as $user)
                    @php
                        $rolUsuario = match ((int) $user->roles_id) {
                            2 => 'Moderador',
                            3 => 'Admin',
                            default => 'Usuario',
                        };
                        // Si es mi propia cuenta, profile.show no lleva {id}, así que
                        // marcamos "from=admin" para que profile.blade.php sepa que
                        // venimos del panel y muestre el botón de "Volver a Usuarios"
                        // (si no, $esPropio=true ocultaría ese botón).
                        $profileParams = $user->id === auth()->id()
                            ? ['from' => 'admin']
                            : ['id' => $user->id];
                    @endphp
                    <a href="{{ route('profile.show', $profileParams) }}" class="w-full">
                        <div class="bgcol1 rounded-xs p-5 flex items-center gap-4 h-full transition-transform hover:scale-[1.02]">
                            <div class="h-14 w-14 rounded-full bgcol4 col1 flex items-center justify-center text-xl font-bold shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="col7 font-bold truncate">{{ $user->name }}</span>
                                <span class="col7 opacity-75 text-sm truncate">{{ $user->email }}</span>
                                <span class="col7 opacity-60 text-xs uppercase font-semibold mt-1">{{ $rolUsuario }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center col7 opacity-75 py-10">
                        No se encontraron usuarios con esos filtros.
                    </div>
                @endforelse
            </div>

            <div id="users-pagination" class="w-full lg:w-4/5">
                {{ $users->links() }}
            </div>
        </div>
    </div>
    <x-footer></x-footer>

</x-layout>