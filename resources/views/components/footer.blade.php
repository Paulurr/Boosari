<footer class="w-full bg-[var(--col4)]">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 pt-14 pb-8">

        <div class="flex flex-col lg:flex-row lg:items-start gap-10 lg:gap-6">

            {{-- Logo + descripción --}}
            <div class="lg:w-1/3 flex flex-col gap-4">
                <a href="/" class="flex h-12 w-1/3 lg:w-auto">
                    <img src="{{ asset('images/LogoTypeLight.svg') }}" alt="Logo" srcset="" class="h-full lg:w-auto logo-theme w-full object-contain">
                </a>
                <p class="text-sm leading-relaxed max-w-xs text-[var(--col1)]/85">
                    Ahorra con propósito, alcanza tus metas y toma el control de tus finanzas, todo desde un solo lugar.
                </p>

                {{-- Redes sociales --}}
                <div class="flex items-center gap-3 mt-2">
                    <a href="#" aria-label="Facebook"
                       class="flex items-center justify-center w-9 h-9 rounded-full bg-[var(--col4)] text-[var(--col1)] transition-all duration-150 hover:bg-[var(--col3)] hover:text-[var(--col6)] hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                            <path d="M22 12a10 10 0 1 0-11.5 9.9v-7H7.9V12h2.6V9.8c0-2.6 1.5-4 3.9-4 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.3 0-1.7.8-1.7 1.6V12h2.9l-.5 2.9h-2.4v7A10 10 0 0 0 22 12Z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram"
                       class="flex items-center justify-center w-9 h-9 rounded-full bg-[var(--col4)] text-[var(--col1)] transition-all duration-150 hover:bg-[var(--col3)] hover:text-[var(--col6)] hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                            <path d="M12 2c2.7 0 3.1 0 4.1.06 1.1.05 1.9.24 2.5.5.7.28 1.2.66 1.8 1.2.6.6.9 1.1 1.2 1.8.26.6.45 1.4.5 2.5.05 1 .06 1.4.06 4.1s0 3.1-.06 4.1c-.05 1.1-.24 1.9-.5 2.5-.28.7-.66 1.2-1.2 1.8-.6.6-1.1.9-1.8 1.2-.6.26-1.4.45-2.5.5-1 .05-1.4.06-4.1.06s-3.1 0-4.1-.06c-1.1-.05-1.9-.24-2.5-.5-.7-.28-1.2-.66-1.8-1.2-.6-.6-.9-1.1-1.2-1.8-.26-.6-.45-1.4-.5-2.5C2 15.1 2 14.7 2 12s0-3.1.06-4.1c.05-1.1.24-1.9.5-2.5.28-.7.66-1.2 1.2-1.8.6-.6 1.1-.9 1.8-1.2.6-.26 1.4-.45 2.5-.5C8.9 2 9.3 2 12 2Zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4Zm5.2-8.4a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0Z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="X"
                       class="flex items-center justify-center w-9 h-9 rounded-full bg-[var(--col4)] text-[var(--col1)] transition-all duration-150 hover:bg-[var(--col3)] hover:text-[var(--col6)] hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor">
                            <path d="M18.9 3H22l-7.6 8.6L23 21h-6.9l-5.4-6.6L4.5 21H1.4l8.1-9.2L1 3h7l4.9 6 6-6Zm-1.2 16h1.9L7.4 5H5.4l12.3 14Z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Enlaces rápidos --}}
            <div class="lg:w-1/3 grid grid-cols-2 gap-6">
                <div class="flex flex-col gap-3">
                    <h4 class="text-sm font-bold tracking-wide uppercase text-[var(--col7)]">Producto</h4>
                    <a href="/" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">Inicio</a>
                    <a href="#" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">Agente</a>
                </div>
                <div class="flex flex-col gap-3">
                    <h4 class="text-sm font-bold tracking-wide uppercase text-[var(--col7)]">Legal</h4>
                    <a href="/terms" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">Términos y condiciones</a>
                    <a href="#" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">Política de privacidad</a>
                    <a href="#" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">Soporte</a>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="lg:w-1/3 flex flex-col gap-3">
                <h4 class="text-sm font-bold tracking-wide uppercase text-[var(--col7)]">Contacto</h4>
                <a href="mailto:hola@boosari.com" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">hola@boosari.com</a>
                <a href="tel:+50322222222" class="w-fit text-sm text-[var(--col2)]/85 transition-all duration-150 hover:text-[var(--col7)] hover:opacity-100 hover:underline">+503 2222 2222</a>
                <p class="text-sm text-[var(--col2)]/85">San Salvador, El Salvador</p>
            </div>

        </div>

        {{-- Línea divisoria --}}
        <div class="w-full h-px my-8 bg-[var(--col4)]/50"></div>

        {{-- Barra inferior --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[var(--col2)]/75">
            <p>&copy; {{ date('Y') }} Boosari. Todos los derechos reservados.</p>
            <div class="flex items-center gap-4">
                <a href="/terms" class="hover:underline hover:text-[var(--col7)]">Términos</a>
                <a href="#" class="hover:underline hover:text-[var(--col7)]">Privacidad</a>
                <a href="#" class="hover:underline hover:text-[var(--col7)]">Cookies</a>
            </div>
        </div>

    </div>
</footer>