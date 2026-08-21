<x-layout title="Perfil">
    <x-slot:head>
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/js/paneles.js',
            'resources/js/profile.js',
        ])
    </x-slot:head>

    <x-nav/>

    <h1 class="col7 text-center pt-25 pb-15 text-4xl font-bold">
        Perfil
    </h1>

    <div class="lg:pl-30 lg:pr-30 md:pl-15 md:pr-15 pl-2 pr-2 pb-20 flex flex-col items-center gap-10">

        @if (!$esPropio || request('from') === 'admin')
            {{-- Se ve cuando un Admin/Moderador gestiona la cuenta de otra persona,
                 O cuando entraste a TU PROPIO perfil desde el panel de usuarios
                 (marcado con ?from=admin en el link, ver partials/users-grid.blade.php) --}}
            <div class="w-full lg:w-3/5 flex justify-start">
                <a href="{{ route('usuarios.index') }}" class="inline-block">
                    <x-button color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" class="p-2 col7 w-auto text-sm" type="button">
                        ← Volver a Usuarios
                    </x-button>
                </a>
            </div>
        @endif

        @if (!$esPropio)
            {{-- Solo se ve cuando un Admin/Moderador está gestionando la cuenta de otra persona --}}
            <div class="w-full lg:w-3/5 bgcol4 col1 p-3 rounded-xs text-center text-sm">
                Estás editando el perfil de <strong>{{ $target->name }}</strong> ({{ $rolTarget }}) como {{ auth()->user()->name }}.
            </div>
        @endif

        {{-- Cabecera --}}
        <div class="w-full lg:w-3/5 flex items-center gap-5">
            <div class="h-20 w-20 rounded-full bgcol4 col1 flex items-center justify-center text-3xl font-bold shrink-0">
                <span class="profile-avatar-initial">{{ strtoupper(substr($target->name, 0, 1)) }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl font-bold col7 profile-name-display">{{ $target->name }}</span>
                <span class="col7 opacity-75 profile-email-display">{{ $target->email }}</span>
                <span class="col7 opacity-75 text-sm">
                    <span class="profile-role-display">{{ $rolTarget }}</span> · Miembro desde {{ $target->created_at->format('d/m/Y') }}
                </span>
            </div>
        </div>

        {{-- Información de la cuenta: lectura <-> edición, inline en la misma tarjeta --}}
        <div class="w-full lg:w-3/5 bgcol1 rounded-xs p-6 flex flex-col items-center">
            <h2 class="text-xl font-bold col4 w-full">Información de la cuenta</h2>
            <div class="bgcol4 h-1 w-full mt-3 mb-6"></div>

            <div id="info-profile-error" class="w-full mb-4 hidden p-2 bg-red-100 border border-red-400 rounded">
                <span class="text-red-700 font-medium text-sm block text-center" data-error></span>
            </div>

            {{-- Modo lectura --}}
            <div id="info-profile-read" class="w-full flex flex-col gap-3">
                <div class="flex items-center justify-between border-b col7 border-opacity-10 pb-3">
                    <span class="col7 opacity-60 text-sm">Nombre</span>
                    <span id="info-profile-name-read" class="col7 font-medium">{{ $target->name }}</span>
                </div>
                <div class="flex items-center justify-between {{ $puedeCambiarRol ? 'border-b col7 border-opacity-10 pb-3' : 'pb-1' }}">
                    <span class="col7 opacity-60 text-sm">Correo electrónico</span>
                    <span id="info-profile-email-read" class="col7 font-medium">{{ $target->email }}</span>
                </div>
                @if ($puedeCambiarRol)
                    <div class="flex items-center justify-between pb-1">
                        <span class="col7 opacity-60 text-sm">Rol</span>
                        <span id="info-profile-role-read" class="col7 font-medium">{{ $rolTarget }}</span>
                    </div>
                @endif
                <div class="mt-4 flex justify-end">
                    <x-button color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" class="p-2 col7 w-auto text-sm" type="button" id="info-profile-edit-btn">
                        Editar
                    </x-button>
                </div>
            </div>

            {{-- Modo edición (oculto por defecto) --}}
            <form id="info-profile-form" data-action="{{ route('profile.update', $esPropio ? [] : ['id' => $target->id]) }}" class="w-full flex-col items-center" style="display:none;">
                <div class="w-full flex flex-wrap gap-6 justify-center">
                    <x-label
                        name="info-profile-name"
                        title="Nombre"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        maxlength="25"
                        required="true"
                        value="{{ $target->name }}"
                    />
                    <x-label
                        name="info-profile-email"
                        type="email"
                        title="Correo electrónico"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        maxlength="100"
                        required="true"
                        value="{{ $target->email }}"
                    />
                </div>

                @if ($puedeCambiarRol)
                    {{-- Solo un Admin editando la cuenta de OTRA persona ve esto.
                         No hay un x-select nativo en el proyecto (el select.js del
                         home maneja un dropdown propio con <li> y clases *-option),
                         así que usamos un <select> normal con los mismos tokens de
                         color (--col3/--col4) para no desentonar visualmente. --}}
                    <div class="w-3/5 flex flex-col mt-6">
                        <label for="info-profile-roles_id" class="col7 opacity-75 text-sm mb-2">Rol</label>
                        <select
                            id="info-profile-roles_id"
                            name="info-profile-roles_id"
                            required
                            class="p-2 rounded-xs bgcol2 col7 border"
                            style="border-color:var(--col4);"
                        >
                            <option value="1" @selected($target->roles_id == 1)>Usuario</option>
                            <option value="2" @selected($target->roles_id == 2)>Moderador</option>
                            <option value="3" @selected($target->roles_id == 3)>Admin</option>
                        </select>
                    </div>
                @endif

                <div class="mt-6 flex gap-3">
                    <x-button color1="red" color2="white" colortext="white" class="p-2 col7 w-auto" type="button" id="info-profile-cancel-btn">
                        Cancelar
                    </x-button>
                    <x-button color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" class="p-2 col7 w-auto" type="submit">
                        Enviar
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Resumen de datos --}}
        <div class="w-full lg:w-3/5 bgcol1 rounded-xs p-6 flex flex-col items-center">
            <h2 class="text-xl font-bold col4 w-full">Resumen de datos</h2>
            <div class="bgcol4 h-1 w-full mt-3 mb-6"></div>

            <div class="w-full grid grid-cols-2 md:grid-cols-3 gap-6 text-center">
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['billeteras'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Billeteras</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['transacciones'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Transacciones</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['ingresos'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Ingresos</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['metas'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Metas</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['deudas'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Deudas</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-bold col7">{{ $resumen['inversiones'] }}</span>
                    <span class="col7 opacity-75 text-xs uppercase font-semibold">Inversiones</span>
                </div>
            </div>
        </div>

        {{-- Sesiones (solo lectura): el admin ve las de cualquier usuario, cada quien ve las suyas --}}
        @if ($puedeVerSesiones)
            <div class="w-full lg:w-3/5 bgcol1 rounded-xs p-6 flex flex-col items-center">
                <h2 class="text-xl font-bold col4 w-full">Sesiones</h2>
                <div class="bgcol4 h-1 w-full mt-3 mb-6"></div>

                @if ($sesiones->isEmpty())
                    <p class="col7 opacity-75 text-sm w-full text-center">No hay sesiones registradas.</p>
                @else
                    <div class="w-full flex flex-col gap-3">
                        @foreach ($sesiones as $sesion)
                            <div class="flex items-center justify-between gap-3 flex-wrap {{ !$loop->last ? 'border-b col7 border-opacity-10 pb-3' : '' }}">
                                <div class="flex flex-col">
                                    <span class="col7 font-medium">
                                        {{ $sesion['dispositivo'] }}
                                        @if ($sesion['actual'])
                                            <span class="ml-2 text-xs bgcol4 col1 px-2 py-0.5 rounded-xs align-middle">Esta sesión</span>
                                        @endif
                                    </span>
                                    <span class="col7 opacity-60 text-xs">IP: {{ $sesion['ip'] }}</span>
                                </div>
                                <span class="col7 opacity-75 text-sm whitespace-nowrap" title="{{ $sesion['ultima_actividad']->format('d/m/Y H:i') }}">
                                    {{ $sesion['ultima_actividad']->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Zona de peligro --}}
        @if ($puedeEliminar)
            <div class="w-full lg:w-3/5 rounded-xs p-6 flex flex-col gap-5 border-2 border-red-400">
                <h2 class="text-xl font-bold text-red-500">Zona de peligro</h2>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex flex-col">
                        <span class="col7 font-bold">Eliminar todos los registros</span>
                        <span class="col7 opacity-75 text-sm">
                            Borra billeteras, transacciones, ingresos, metas, deudas, inversiones y el historial del asistente.
                            {{ $esPropio ? 'Tu cuenta seguirá activa.' : 'La cuenta seguirá activa.' }}
                        </span>
                    </div>
                    <x-button color1="red" color2="white" colortext="white" class="p-2 col7 w-auto confirm-reset-btn" type="button">
                        Eliminar registros
                    </x-button>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex flex-col">
                        <span class="col7 font-bold">
                            {{ $esPropio ? 'Eliminar mi cuenta' : 'Eliminar la cuenta de ' . $target->name }}
                        </span>
                        <span class="col7 opacity-75 text-sm">Esta acción no se puede deshacer.</span>
                    </div>
                    <x-button color1="red" color2="white" colortext="white" class="p-2 col7 w-auto confirm-delete-account-btn" type="button">
                        Eliminar cuenta
                    </x-button>
                </div>
            </div>
        @endif
    </div>

    @if ($puedeEliminar)
        {{-- Modal: confirmar eliminación de registros --}}
        <x-panel newH="h-2/4" name="confirm-reset" title="Eliminar todos los registros" data-action="{{ route('profile.records.delete', $esPropio ? [] : ['id' => $target->id]) }}">
            <div class="p-6 h-full flex flex-col items-center justify-around gap-4">
                <p class="col7 text-center">
                    Estás a punto de eliminar {{ $esPropio ? 'todos tus registros financieros' : 'todos los registros financieros de ' . $target->name }}.
                    Esta acción no se puede deshacer.
                </p>

                <div id="confirm-reset-error" class="w-full hidden p-2 bg-red-100 border border-red-400 rounded">
                    <span class="text-red-700 font-medium text-sm block text-center" data-error></span>
                </div>

                @if ($esPropio)
                    <div class="w-100">
                        <x-label
                            name="reset_confirm_password"
                            type="password"
                            title="Tu contraseña"
                            color1="var(--col3)"
                            color2="var(--col4)"
                            w="w-full"
                            required="true"
                        />
                    </div>
                @else
                    {{-- Admin gestionando a otra persona: sin contraseña, solo advertencia --}}
                    <p class="col7 opacity-75 text-sm text-center font-semibold">
                        Esta acción es permanente e inmediata. Al confirmar, se eliminarán todos los registros de {{ $target->name }}.
                    </p>
                @endif
            </div>
        </x-panel>

        {{-- Modal: confirmar eliminación de cuenta --}}
        <x-panel newH="h-2/4" name="confirm-delete-account" title="Eliminar cuenta" data-action="{{ route('profile.destroy', $esPropio ? [] : ['id' => $target->id]) }}">
            <div class="p-6 h-full flex flex-col justify-around items-center gap-4">
                <p class="col7 text-center">
                    Estás a punto de eliminar {{ $esPropio ? 'tu cuenta' : 'la cuenta de ' . $target->name }} de forma permanente,
                    junto con todos sus registros. Esta acción no se puede deshacer.
                </p>

                <div id="confirm-delete-account-error" class="w-full hidden p-2 bg-red-100 border border-red-400 rounded">
                    <span class="text-red-700 font-medium text-sm block text-center" data-error></span>
                </div>

                @if ($esPropio)
                    <div class="w-100">
                        <x-label
                            name="delete_confirm_password"
                            type="password"
                            title="Tu contraseña"
                            color1="var(--col3)"
                            color2="var(--col4)"
                            w="w-full"
                            required="true"
                        />
                    </div>
                @else
                    {{-- Admin gestionando a otra persona: sin contraseña, solo advertencia --}}
                    <p class="col7 opacity-75 text-sm text-center font-semibold">
                        Esta acción es permanente e inmediata. Al confirmar, se eliminará la cuenta de {{ $target->name }} junto con todos sus registros.
                    </p>
                @endif
            </div>
        </x-panel>
    @endif
        <x-footer></x-footer>

</x-layout>