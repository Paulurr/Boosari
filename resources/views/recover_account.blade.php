<x-layout title="Recuperar cuenta">
    <x-slot:head>
        @vite(['resources/css/app.css'])
    </x-slot:head>

    <x-nav />

    <!-- Contenedor principal centrado -->
    <main class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center p-4">
        
        <!-- Formulario de recuperación de contraseña -->
        <form class="form-login w-full max-w-md rounded-2xl shadow-xl border overflow-hidden" 
              style="background-color: var(--col1); border-color: var(--col2);" 
              id="forgot_password_form" 
              method="POST" 
              action="{{ route('password.email') }}">
            @csrf
            
            <div class="form-login-cont w-full p-8 flex flex-col items-center">
                
                <!-- Decoración superior -->
                <div class="form-login-cont-deco w-16 h-1 rounded-full mb-6" 
                     style="background: linear-gradient(to right, var(--col3), var(--col4));"></div>
                
                <!-- Icono de seguridad -->
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" 
                     style="background-color: var(--col2);">
                    <svg class="w-6 h-6" style="color: var(--col4);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"></path>
                    </svg>
                </div>

                <!-- Título -->
                <h1 class="font-bold text-2xl text-center mb-2" style="color: var(--col6);">
                    ¿Olvidaste tu contraseña?
                </h1>
                
                <!-- Subtítulo -->
                <p class="text-sm text-center mb-6 px-2" style="color: var(--col7);">
                    Ingresa tu correo electrónico registrado y te enviaremos las instrucciones para restablecerla.
                </p>

                <!-- Mensaje de éxito al enviar enlace -->
                @if (session('status'))
                    <div class="w-full p-3 mb-4 rounded-lg text-xs text-center font-semibold text-green-400 bg-green-950/40 border border-green-800">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Campo de Email -->
                <div class="w-full flex flex-col items-center mb-6">
                    <x-label 
                        name="email"
                        type="email"
                        title='{{ __("forms.box1") }}'
                        color1="var(--col3)"
                        color2="var(--col4)"
                        value="{{ old('email') }}"
                    />
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botón de envío -->
                <div class="w-full flex items-center justify-center mb-6">
                    <x-button 
                        color1="var(--col3)"
                        color2="var(--col4)"
                        colortext="var(--col1)"
                        class="w-full p-3 rounded-lg font-semibold shadow-md transition-transform hover:scale-[1.01]"
                    >
                        Enviar instrucciones
                    </x-button>
                </div>

                <!-- Regresar a Login -->
                <div class="w-full flex items-center justify-center text-sm mb-4" style="color: var(--col7);">
                    ¿Recordaste tu contraseña?
                    <a href="{{ route('log_in') }}" class="hover:underline ml-1 font-bold" style="color: var(--col4);">
                        {{ __('nav.log_in') }}
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