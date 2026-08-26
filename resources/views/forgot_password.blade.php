<!-- Navegación reutilizable usando el componente Blade x-nav -->
<x-nav />

<!-- Contenido Principal: Formulario de Restablecimiento -->
<main class="flex-grow flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-md border border-gray-200">
        
        <!-- Encabezado del Formulario -->
        <div>
            <div class="flex justify-center mb-4">
                <div class="h-12 w-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-center text-2xl font-extrabold text-gray-900">
                Restablecer Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu correo y define tu nueva contraseña para acceder.
            </p>
        </div>

        <!-- Alertas de estado de Laravel -->
        @if (session('status'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <!-- Formulario POST hacia password.update -->
        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- Token enviado desde el correo de recuperación -->
            <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

            <div class="space-y-4">
                <!-- Campo: Correo Electrónico -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email', request()->email) }}"
                           class="w-full px-3 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="usuario@ejemplo.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo: Nueva Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva Contraseña
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="w-full px-3 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo: Confirmar Nueva Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Contraseña
                    </label>
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="••••••••">
                </div>
            </div>

            <!-- Botón de envío -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Restablecer Contraseña
                </button>
            </div>

            <!-- Regreso al Login -->
            <div class="text-center pt-2">
                <a href="{{ route('log_in') }}" class="text-xs text-indigo-600 hover:text-indigo-500 font-medium">
                    &larr; Volver al inicio de sesión
                </a>
            </div>
        </form>

    </div>
</main>

<!-- Footer sencillo -->
<footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500">
    &copy; {{ date('Y') }} MiAplicacion. Todos los derechos reservados.
</footer>