<x-layout title="Sesión expirada">
    <x-slot:head>
        @vite(['resources/css/app.css'])
    </x-slot:head>
    <x-nav/>

    <div class="w-full min-h-screen flex flex-col items-center justify-center px-4">

        <div class="w-full lg:w-2/5 md:w-3/5 rounded-xs p-8 flex flex-col items-center text-center gap-5">

            <div class=" aspect-square p-5 rounded-full bgcol4 col1 flex items-center justify-center text-4xl font-bold ">
                ⏱
            </div>

            <div>
                <h1 class="text-2xl font-bold col7 mb-2">Tu sesión expiró</h1>
                <p class="col7 opacity-75 text-sm leading-relaxed">
                    Por seguridad, tu sesión se cierra sola después de un tiempo de inactividad
                    (o si la pestaña quedó abierta desde hace rato sin usarse).
                    Esto no significa que haya pasado algo malo con tu cuenta, solo necesitas
                    volver a iniciar sesión para continuar.
                </p>
            </div>


            <div class="w-full flex flex-col gap-3">
                <a href="{{ url('/log_in') }}" class="w-full">
                    <x-button
                        color1="var(--col3)"
                        color2="var(--col4)"
                        colortext="var(--col7)"
                        class="p-3 w-full text-sm flex items-center justify-center">
                        Iniciar sesión de nuevo
                    </x-button>
                </a>

                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="w-full">
                    <x-button
                        color1="var(--col4)"
                        color2="var(--col3)"
                        colortext="var(--col1)"
                        class="p-3 w-full text-sm flex items-center justify-center">
                        Volver atrás
                    </x-button>
                </a>
            </div>

            <p class="col7 opacity-50 text-xs mt-2">
                Código de error: 419 · Página expirada
            </p>
        </div>

    </div>
    <x-footer></x-footer>

</x-layout>