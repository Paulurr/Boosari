<?php /* Ubicación sugerida: resources/views/agent.blade.php */ ?>
@php
    // El controlador del agente debería pasar $agenteActivo explícitamente
    // (ver nota de integración). Este fallback evita un error si no lo hace.
    $agenteActivo = $agenteActivo ?? (auth()->user()?->configuracion?->agente_activo ?? true);
@endphp

<x-layout title="Peche IA">
    <x-slot:head>
        @if($agenteActivo)
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/agent.js'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </x-slot:head>

    @if($agenteActivo)
        <style>
            html{
                overflow: hidden;
            }
        </style>
        <x-nav></x-nav>
        <div class="w-auto overflow-hidden pt-15 h-auto bgcol1">
            <div id="agent-shell" class="w-full h-[calc(100vh-3rem)] flex relative overflow-hidden">

                <!-- Overlay móvil: cierra el historial al tocar fuera -->
                <div id="agent-sidebar-overlay" class="fixed inset-0 bg-black/40 z-20 hidden md:hidden"></div>

                {{-- Historial de conversaciones --}}
                <aside id="agent-sidebar"
                       class="w-72 shrink-0 bgcol2  col7 flex flex-col
                              fixed md:static inset-y-0 left-0 z-30 -translate-x-full md:translate-x-0
                              transition-transform duration-200 pt-15 md:pt-0">

                    <div class="p-3 h-20 flex items-center justify-center border-b col7">
                        <button id="agent-new-chat"
                                class="w-full flex items-center justify-center gap-2 p-2.5 col1 bgcol4 rounded-lg font-semibold text-sm shadow-sm hover:opacity-90 active:scale-[0.98] transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Nueva conversación
                        </button>
                    </div>

                    <div id="agent-history" class="flex-1 overflow-y-auto barpag px-3 py-3 space-y-1">
                        @forelse($conversaciones as $conv)
                            <div data-id="{{ $conv->id }}"
                                 class="agent-history-item group relative w-full rounded-lg text-sm col7 hover:bgcol2 transition">
                                <button type="button"
                                        class="agent-history-open w-full text-left p-3 pr-9 flex flex-col gap-0.5 rounded-lg">
                                    <span class="truncate font-medium">{{ $conv->titulo ?? 'Conversación' }}</span>
                                    <span class="text-[11px] col7 opacity-60">{{ $conv->created_at?->diffForHumans() }}</span>
                                </button>
                                <button type="button"
                                        class="agent-delete-btn absolute right-1.5 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center rounded-md text-red-500 hover:bg-red-500/10"
                                        title="Eliminar conversación">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                                </button>
                            </div>
                        @empty
                            <div id="agent-history-empty" class="text-center col7 opacity-60 text-xs px-4 py-10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Aún no tienes conversaciones.
                            </div>
                        @endforelse
                    </div>
                </aside>

                {{-- Chat --}}
                <div class="flex-1 flex flex-col min-w-0">
                    <div class="p-3 h-20 border-b col7 bgcol2 flex items-center gap-3">
                        <button id="agent-sidebar-toggle" class="md:hidden p-1.5 rounded-md hover:bgcol1 col7">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="w-9 h-9 rounded-full bgcol4 col1 flex items-center justify-center shrink-0 text-sm font-bold">IA</div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold col7 truncate">Peche IA</p>
                            <p class="text-[11px] col7 opacity-60 truncate">Usa tus billeteras, ingresos, deudas, metas e inversiones · datos enviados a Coze</p>
                        </div>
                    </div>

                    <div id="agent-messages" class="flex-1 overflow-y-auto barpag p-4 md:p-6 space-y-4 bgcol1">
                        <div id="agent-empty-state" class="max-w-md mx-auto text-center pt-10">
                            <div class="w-14 h-14 rounded-full bgcol4 col1 flex items-center justify-center mx-auto mb-4 text-xl font-bold">IA</div>
                            <p class="col7 font-semibold mb-1">¿En qué te ayudo?</p>
                            <p class="col7 opacity-60 text-sm mb-5">Pregúntame sobre tus finanzas, con base en tus datos reales.</p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿En qué debería enfocarme este mes?</button>
                                <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿Cómo van mis metas de ahorro?</button>
                                <button class="agent-suggestion text-xs col7 border rounded-full px-3 py-1.5 hover:bgcol2 transition">¿Qué deuda debería priorizar?</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 md:p-4 border-t col7 bgcol2">
                        <div class="flex items-end gap-2 bgcol1 border col7 rounded-xl p-2 focus-within:ring-2 focus-within:ring-[var(--col4)] transition-shadow">
                            <textarea id="agent-input" rows="1" maxlength="1000"
                                      class="flex-1 resize-none bg-transparent col7 text-sm p-1.5 outline-none max-h-32"
                                      placeholder="Escribe tu pregunta..."></textarea>
                            <button id="agent-send"
                                    class="shrink-0 w-9 h-9 flex items-center justify-center col1 bgcol4 rounded-lg hover:opacity-90 active:scale-[0.94] transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
                            </button>
                        </div>
                        <p class="text-[11px] col7 opacity-60 mt-1.5 text-right"><span id="agent-char-count">0</span>/1000</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Agente desactivado desde Configuración --}}
        <x-nav></x-nav>
        <div class="w-full min-h-[calc(100vh-3rem)] pt-15 flex items-center justify-center px-4">
            <div class="max-w-md w-full text-center bgcol1 rounded-2xl p-8">
                <div class="w-14 h-14 rounded-full bgcol4 col1 flex items-center justify-center mx-auto mb-4 text-xl font-bold">IA</div>
                <p class="col7 font-semibold mb-2 text-lg">Peche IA está desactivado</p>
                <p class="col7 opacity-70 text-sm mb-6">Actívalo desde Configuración para empezar a usarlo con tus datos financieros.</p>

                <a href="{{ route('config.index') }}#agente"
                   class="page-button rounded-xs inline-block"
                   style="--page-button1:var(--col4);--page-button2:var(--col3);--color-page-text:var(--col1);">
                    <div class="h-full page-button-div px-6 py-3 font-semibold text-sm"
                         style="--page-button-div1:var(--col4);--page-button-div2:var(--col3);">
                        Ir a Configuración
                    </div>
                </a>
            </div>
        </div>
    @endif
</x-layout>