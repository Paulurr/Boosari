<x-layout title="Terminos y condiciones">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css'])

        {{-- Estilos del acordeón. Todo el efecto abrir/cerrar lo maneja
             el navegador de forma nativa con <details>/<summary>, sin JS. --}}
        <style>
            .accordion-item summary {
                list-style: none;
                cursor: pointer;
            }
            /* Oculta el triángulo por defecto que ponen los navegadores */
            .accordion-item summary::-webkit-details-marker {
                display: none;
            }
            .accordion-item summary::marker {
                content: "";
            }
            .accordion-item .chevron {
                transition: transform 0.2s ease-in-out;
            }
            .accordion-item[open] .chevron {
                transform: rotate(180deg);
            }
            .accordion-item[open] summary {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }

            /* Paleta de marca */
            .term-acc {
                border: 1px solid var(--col3);
            }
            .term-acc-header {
                transition:background-color 0.2s ease; 
                background-color: var(--col4);
                color: var(--col1);
            }
            .term-acc-header:hover {
                background-color: var(--col3);
            }
            .term-acc-body {
                background-color: var(--col1);
                color: var(--col7);
            }
            .term-acc-body a {
                color: var(--col3);
                text-decoration: underline;
            }
        </style>
    </x-slot:head>

    <x-nav/>
    <div class="bgcol2 w-full pt-15 min-h-screen flex flex-col justify-between">

        <div class="h-auto pt-15 pb-10 w-full ">
            <h1 class="col7 text-4xl lg:text-6xl font-bold text-center">
                {{ __('terms.terms') }}
            </h1>
            <p class="text-center col6 mt-4 text-sm lg:text-base max-w-2xl mx-auto px-4">
                {{ __('terms.txt1') }}
            </p>
        </div>

        <div class="h-auto w-full ">
            <object data="{{ asset('images/BarAppere.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
        </div>

        <div class="px-4 py-10 md:px-10 lg:px-20 grow">
            <div class="max-w-4xl mx-auto">

                <main class="w-full flex flex-col gap-3">

                    <details id="aceptacion" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt1') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            {{ __('terms.txt2') }}
                        </div>
                    </details>

                    <details id="descripcion" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt2') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            {{ __('terms.txt3') }}
                        </div>
                    </details>

                    <details id="registro" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt3') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt4_li1') }}</li>
                                <li>{{ __('terms.txt4_li2') }}</li>
                                <li>{{ __('terms.txt4_li3') }}</li>
                                <li>{{ __('terms.txt4_li4') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="uso-adecuado" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt4') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <p class="mb-3 font-semibold">{{ __('terms.txt5') }}</p>
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt5_li1') }}</li>
                                <li>{{ __('terms.txt5_li2') }}</li>
                                <li>{{ __('terms.txt5_li3') }}</li>
                                <li>{{ __('terms.txt5_li4') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="info-financiera" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt5') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt6_li1') }}</li>
                                <li>{{ __('terms.txt6_li2') }}</li>
                                <li>{{ __('terms.txt6_li3') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="metas-ahorro" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt6') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt7_li1') }}</li>
                                <li>{{ __('terms.txt7_li2') }}</li>
                                <li>{{ __('terms.txt7_li3') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="proteccion-datos" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt7') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt8_li1') }}</li>
                                <li>{{ __('terms.txt8_li2') }}</li>
                                <li>{{ __('terms.txt8_li3') }}</li>
                                <li>{{ __('terms.txt8_li4') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="disponibilidad" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt8') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt9_li1') }}</li>
                                <li>{{ __('terms.txt9_li2') }}</li>
                                <li>{{ __('terms.txt9_li3') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="propiedad-intelectual" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt9') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            {{ __('terms.txt10') }}
                        </div>
                    </details>

                    <details id="responsabilidad" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt10') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <p class="mb-3 font-semibold">{{ __('terms.txt11') }}</p>
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt11_li1') }}</li>
                                <li>{{ __('terms.txt11_li2') }}</li>
                                <li>{{ __('terms.txt11_li3') }}</li>
                                <li>{{ __('terms.txt11_li4') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="suspension" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt11') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            <p class="mb-3 font-semibold">{{ __('terms.txt12') }}</p>
                            <ul class="list-disc pl-5 space-y-2">
                                <li>{{ __('terms.txt12_li1') }}</li>
                                <li>{{ __('terms.txt12_li2') }}</li>
                                <li>{{ __('terms.txt12_li3') }}</li>
                            </ul>
                        </div>
                    </details>

                    <details id="modificaciones" class="accordion-item term-acc scroll-mt-28 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt12') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            {{ __('terms.txt13') }}
                        </div>
                    </details>

                    <details id="contacto" class="accordion-item term-acc scroll-mt-28 mb-8 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 px-6 py-4 term-acc-header font-semibold text-sm lg:text-base">
                            <span>{{ __('terms.index_txt13') }}</span>
                            <svg class="chevron w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="term-acc-body text-sm lg:text-base leading-relaxed px-6 py-5">
                            {{ __('terms.txt14') }}
                        </div>
                    </details>

                </main>
            </div>
        </div>

        <div class="h-auto w-full rotate-180 mb-6">
            <object data="{{ asset('images/BarAppere.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
        </div>

        <x-footer/>
    </div>
</x-layout>