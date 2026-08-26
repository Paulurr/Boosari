<x-layout title="Crear Usuario">
    <x-slot:head>
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])
    </x-slot:head>

    <x-nav/>
    <h1 class="col7 text-center pt-25 pb-15 text-4xl font-bold">
        Crear Usuario
    </h1>
    <div class="w-full flex justify-center mb-10">
        <div class="lg:w-3/5 m-5 w-full flex justify-start">

            <a href="{{ route('usuarios.index') }}" class="inline-block">
                <x-button color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" class="p-2 col7 w-auto text-sm" type="button">
                    ← Volver a Usuarios
                </x-button>
            </a>
        </div>
    </div>

    <div class="lg:pl-30 lg:pr-30 md:pl-15 md:pr-15 pl-2 pr-2 pb-20 flex flex-col items-center gap-10">

        <div class="w-full lg:w-3/5 bgcol1 rounded-xs p-6 flex flex-col items-center">
            <h2 class="text-xl font-bold col4 w-full">Datos de la cuenta</h2>
            <div class="bgcol4 h-1 w-full mt-3 mb-6"></div>

            @if ($errors->any())
                <div class="w-full mb-4 p-2 bg-red-100 border border-red-400 rounded">
                    <ul class="text-red-700 text-sm text-center list-none">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('usuarios.store') }}" class="w-full flex flex-col items-center">
                @csrf

                <div class="w-full flex flex-wrap gap-6 justify-center">
                    <x-label
                        name="name"
                        title="Nombre"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        maxlength="25"
                        required="true"
                        value="{{ old('name') }}"
                    />
                    <x-label
                        name="email"
                        type="email"
                        title="Correo electrónico"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        maxlength="100"
                        required="true"
                        value="{{ old('email') }}"
                    />
                    <x-label
                        name="password"
                        type="password"
                        title="Contraseña"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        required="true"
                    />
                    <x-label
                        name="repeat_password"
                        type="password"
                        title="Repetir contraseña"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        required="true"
                    />
                </div>

                {{-- Selector de rol: no hay un x-select en el proyecto, así que se
                     estiliza a mano con los mismos tokens de color (--col3/--col4)
                     que usa x-label, para que no desentone visualmente. --}}
                <div class="w-3/5 flex flex-col mt-6">
                    <label for="roles_id" class="col7 opacity-75 text-sm mb-2">Rol</label>
                    <select
                        id="roles_id"
                        name="roles_id"
                        required
                        class="p-2 rounded-xs bgcol2 col7 border"
                        style="border-color:var(--col4);"
                    >
                        <option value="1" @selected(old('roles_id') == 1)>Usuario</option>
                        <option value="2" @selected(old('roles_id') == 2)>Moderador</option>
                        <option value="3" @selected(old('roles_id') == 3)>Admin</option>
                    </select>
                </div>

                <div class="mt-6 h-10 flex">
                    <a class="mr-2" href="{{ route('usuarios.index') }}">
                        <x-button color1="red" color2="white" colortext="white" class="p-2 col7 w-auto" type="button">
                            Cancelar
                        </x-button>
                    </a>
                    <x-button color1="var(--col4)" color2="var(--col3)" colortext="var(--col1)" class="p-2 col7 w-auto" type="submit">
                        Crear usuario
                    </x-button>
                </div>
            </form>
        </div>
    </div>
    <x-footer></x-footer>

</x-layout>
