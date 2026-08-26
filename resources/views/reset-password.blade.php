<x-layout title="Restablecer contraseña">
    <x-slot:head>
        @vite(['resources/css/app.css'])
    </x-slot:head>

    <x-nav />

    <!-- Contenedor principal centrado -->
    <main class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center p-4">
        
        <!-- Formulario de restablecimiento de contraseña -->
        <form class="form-login w-full max-w-md rounded-2xl shadow-xl border overflow-hidden" 
              style="background-color: var(--col1); border-color: var(--col2);" 
              id="reset_password_form" 
              method="POST" 
              action="{{ route('password.update') }}">
            @csrf

            <!-- Token de recuperación obligatorio -->
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-login-cont w-full p-8 flex flex-col items-center">
                
                <!-- Decoración superior (Degradado entre verde y amarillo) -->
                <div class="form-login-cont-deco w-16 h-1 rounded-full mb-6" 
                     style="background: linear-gradient(to right, var(--col3), var(--col4));"></div>
                
                <!-- Icono de Candado / Seguridad -->
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" 
                     style="background-color: var(--col2);">
                    <svg class="w-6 h-6" style="color: var(--col4);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>

                <!-- Título -->
                <h1 class="font-bold text-2xl text-center mb-2" style="color: var(--col6);">
                    Restablecer contraseña
                </h1>
                
                <!-- Subtítulo -->
                <p class="text-sm text-center mb-6 px-2" style="color: var(--col7);">
                    Ingresa tu correo electrónico registrado y define tu nueva clave de acceso.
                </p>

                <!-- Mensaje de estado de sesión (si existe) -->
                @if (session('status'))
                    <div class="w-full mb-4 p-3 rounded-lg text-xs font-semibold text-center border" 
                         style="background-color: var(--col2); color: var(--col4); border-color: var(--col3);">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Campo: Correo Electrónico -->
                <div class="w-full flex flex-col items-center mb-4">
                    <x-label 
                        name="email"
                        type="email"
                        title='{{ __("forms.box1") }}'
                        color1="var(--col3)"
                        color2="var(--col4)"
                        value="{{ old('email', request('email')) }}"
                        required
                    />
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo: Nueva Contraseña -->
                <div class="w-full flex flex-col items-center mb-4">
                    <x-label 
                        name="password"
                        type="password"
                        title="Nueva contraseña"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        required
                    />
                    @error('password')
                        <span class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo: Confirmar Contraseña -->
                <div class="w-full flex flex-col items-center mb-6">
                    <x-label 
                        name="password_confirmation"
                        type="password"
                        title="Confirmar contraseña"
                        color1="var(--col3)"
                        color2="var(--col4)"
                        required
                    />
                </div>

                <!-- Botón de envío -->
                <div class="w-full flex items-center justify-center mb-6">
                    <x-button 
                        color1="var(--col3)"
                        color2="var(--col4)"
                        colortext="var(--col1)"
                        class="w-full p-3 rounded-lg font-semibold shadow-md transition-transform hover:scale-[1.01]"
                    >
                        Restablecer Contraseña
                    </x-button>
                </div>

                <!-- Regresar a Login -->
                <div class="w-full flex items-center justify-center text-sm mb-4" style="color: var(--col7);">
                    <a href="/log_in" class="hover:underline font-bold flex items-center gap-1" style="color: var(--col4);">
                        ← {{ __('nav.log_in') }}
                    </a>
                </div>

                <!-- Footer del form -->
                <div class="w-full text-center text-xs border-t pt-4 mt-2" 
                     style="color: var(--col7); border-color: var(--col2);">
                    {{ __('forms.msg2') }}
                </div>

            </div>
        </form>
    </main>
</x-layout>