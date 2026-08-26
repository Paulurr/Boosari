<x-layout title="Configuración">
    <x-slot:head>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/configuracion.js'])
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </x-slot:head>

    <x-nav/>

    <div class="pt-25 pb-20 bgcol2 min-h-screen px-4">
        <h1 class="col7 text-center text-3xl font-bold mb-10">Configuración</h1>

        {{-- ================= Switch: activar/desactivar el agente ================= --}}
        <section id="agente" class="max-w-2xl mx-auto mb-8 bgcol1 rounded-2xl p-6 md:p-8 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 class="col7 font-bold text-lg mb-1">Asistente Peche IA</h2>
                <p class="col7 opacity-60 text-sm">Actívalo o desactívalo. Si lo apagas, la página del agente le pedirá al usuario venir aquí.</p>
            </div>

            <button type="button"
                    id="switch-agente"
                    role="switch"
                    aria-checked="{{ $configuracion->agente_activo ? 'true' : 'false' }}"
                    class="relative w-14 h-7 rounded-full shrink-0 transition-colors duration-200 {{ $configuracion->agente_activo ? 'bgcol4' : 'bgcol1' }}"
                    style="box-shadow: inset 0 0 0 1px var(--col6);">
                <span class="switch-agente-div absolute top-0.5 left-0.5 w-6 h-6 rounded-full bgcol6 shadow transition-transform duration-200 {{ $configuracion->agente_activo ? 'translate-x-7' : 'translate-x-0' }}"></span>
            </button>
        </section>
        <p id="agente-msg" class="max-w-2xl mx-auto text-xs col7 opacity-70 mb-10 min-h-[1em]"></p>

        {{-- ================= Panel: paleta de colores (claro / oscuro) ================= --}}
        <section id="colores" class="max-w-2xl mx-auto bgcol1 rounded-2xl p-6 md:p-8">
            <h2 class="col7 font-bold text-lg mb-1">Paleta de colores</h2>
            <p class="col7 opacity-60 text-sm mb-6">Personaliza los 7 colores de cada modo. Si dejas uno sin cambiar, se usa el color base.</p>

            {{-- Tabs --}}
            <div class="flex gap-2 mb-6">
                <button type="button" data-tab-colores="claro"
                        class="tab-colores-btn text-sm font-semibold px-4 py-2 rounded-lg transition bgcol4 col1">
                    Modo claro
                </button>
                <button type="button" data-tab-colores="oscuro"
                        class="tab-colores-btn text-sm font-semibold px-4 py-2 rounded-lg transition bgcol1 col7">
                    Modo oscuro
                </button>
            </div>

            @php
                $etiquetas = [
                    1 => 'Fondo principal',
                    2 => 'Fondo secundario',
                    3 => 'Acento (amarillo)',
                    4 => 'Acento (verde)',
                    5 => 'Verde oscuro',
                    6 => 'Oscuro',
                    7 => 'Texto',
                ];
            @endphp

            {{-- ---- Formulario: modo claro ---- --}}
            <form id="form-colores-claro" data-modo="claro" class="form-colores grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="hidden" name="modo" value="claro">
                @foreach($etiquetas as $indice => $etiqueta)
                    <div class="flex items-center gap-3 bgcol1 rounded-lg p-3">
                        <input type="color"
                               name="color_{{ $indice }}"
                               id="color_{{ $indice }}_claro"
                               value="{{ $coloresClaro[$indice] }}"
                               class="w-10 h-10 rounded-md border-0 cursor-pointer shrink-0 bg-transparent">
                        <div class="flex-1 min-w-0">
                            <label for="color_{{ $indice }}_claro" class="block text-sm col7 font-medium truncate">{{ $etiqueta }}</label>
                            <span class="text-[11px] col7 opacity-60 uppercase" data-hex-for="color_{{ $indice }}_claro">{{ $coloresClaro[$indice] }}</span>
                        </div>
                    </div>
                @endforeach

                <div class="sm:col-span-2 flex flex-wrap items-center gap-3 mt-3">
                    <button type="submit" class="page-button rounded-xs"
                            style="--page-button1:var(--col4);--page-button2:var(--col3);--color-page-text:var(--col1);">
                        <div class="h-full page-button-div px-6 py-2.5 font-semibold text-sm"
                             style="--page-button-div1:var(--col4);--page-button-div2:var(--col3);">
                            Guardar paleta
                        </div>
                    </button>
                    <button type="button" class="btn-restaurar-colores editar-btn text-sm">
                        Restaurar colores base
                    </button>
                </div>
                <p class="colores-msg sm:col-span-2 text-xs col7 opacity-70 min-h-[1em]"></p>
            </form>

            {{-- ---- Formulario: modo oscuro ---- --}}
            <form id="form-colores-oscuro" data-modo="oscuro" class="form-colores hidden grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="hidden" name="modo" value="oscuro">
                @foreach($etiquetas as $indice => $etiqueta)
                    <div class="flex items-center gap-3 bgcol1 rounded-lg p-3">
                        <input type="color"
                               name="color_{{ $indice }}_oscuro"
                               id="color_{{ $indice }}_oscuro"
                               value="{{ $coloresOscuro[$indice] }}"
                               class="w-10 h-10 rounded-md border-0 cursor-pointer shrink-0 bg-transparent">
                        <div class="flex-1 min-w-0">
                            <label for="color_{{ $indice }}_oscuro" class="block text-sm col7 font-medium truncate">{{ $etiqueta }}</label>
                            <span class="text-[11px] col7 opacity-60 uppercase" data-hex-for="color_{{ $indice }}_oscuro">{{ $coloresOscuro[$indice] }}</span>
                        </div>
                    </div>
                @endforeach

                <div class="sm:col-span-2 flex flex-wrap items-center gap-3 mt-3">
                    <button type="submit" class="page-button rounded-xs"
                            style="--page-button1:var(--col4);--page-button2:var(--col3);--color-page-text:var(--col1);">
                        <div class="h-full page-button-div px-6 py-2.5 font-semibold text-sm"
                             style="--page-button-div1:var(--col4);--page-button-div2:var(--col3);">
                            Guardar paleta
                        </div>
                    </button>
                    <button type="button" class="btn-restaurar-colores editar-btn text-sm">
                        Restaurar colores base
                    </button>
                </div>
                <p class="colores-msg sm:col-span-2 text-xs col7 opacity-70 min-h-[1em]"></p>
            </form>
        </section>
    </div>
    <x-footer></x-footer>
    
</x-layout>