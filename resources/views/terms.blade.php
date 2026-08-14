<x-layout title="Terminos y condiciones">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css'])
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
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-10">    
                <aside class="w-full md:w-1/4 h-fit md:sticky md:top-24 bgcol5 p-5 rounded-lg shadow-md">
                    <h2 class="col1 font-bold text-lg mb-4 border-b border-gray-600 pb-2">Índice</h2>
                    <nav class="flex flex-col gap-2 max-h-[60vh] overflow-y-auto pr-2 scrollbar-thin">
                        <a href="#aceptacion" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt1') }}</a>
                        <a href="#descripcion" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt2') }}</a>
                        <a href="#registro" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt3') }}</a>
                        <a href="#uso-adecuado" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt4') }}</a>
                        <a href="#info-financiera" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt5') }}</a>
                        <a href="#metas-ahorro" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt6') }}</a>
                        <a href="#proteccion-datos" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt7') }}</a>
                        <a href="#disponibilidad" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt8') }}</a>
                        <a href="#propiedad-intelectual" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt9') }}</a>
                        <a href="#responsabilidad" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt10') }}</a>
                        <a href="#suspension" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt11') }}</a>
                        <a href="#modificaciones" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt12') }}</a>
                        <a href="#contacto" class="col7 opacity-80 hover:opacity-100 transition-all text-sm font-medium hover:underline">{{ __('terms.index_txt13') }}</a>
                    </nav>
                </aside>

                <main class="w-full md:w-3/4 space-y-12 bgcol4 rounded-lg pr-4 pl-4 shadow-lg">

                    <section id="aceptacion" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-7 font-bold mb-2">{{ __('terms.index_txt1') }}</h3>
                            <p class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                {{ __('terms.txt2') }}
                            </p>
                        </section>

                        <section id="descripcion" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt2') }}</h3>
                            <p class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                {{ __('terms.txt3') }}
                            </p>
                        </section>

                        <section id="registro" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt3') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt4_li1') }}</li>
                                    <li>{{ __('terms.txt4_li2') }}</li>
                                    <li>{{ __('terms.txt4_li3') }}</li>
                                    <li>{{ __('terms.txt4_li4') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="uso-adecuado" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt4') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <p class="mb-3 font-semibold">{{ __('terms.txt5') }}</p>
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt5_li1') }}</li>
                                    <li>{{ __('terms.txt5_li2') }}</li>
                                    <li>{{ __('terms.txt5_li3') }}</li>
                                    <li>{{ __('terms.txt5_li4') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="info-financiera" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt5') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt6_li1') }}</li>
                                    <li>{{ __('terms.txt6_li2') }}</li>
                                    <li>{{ __('terms.txt6_li3') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="metas-ahorro" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt6') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt7_li1') }}</li>
                                    <li>{{ __('terms.txt7_li2') }}</li>
                                    <li>{{ __('terms.txt7_li3') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="proteccion-datos" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt7') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt8_li1') }}</li>
                                    <li>{{ __('terms.txt8_li2') }}</li>
                                    <li>{{ __('terms.txt8_li3') }}</li>
                                    <li>{{ __('terms.txt8_li4') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="disponibilidad" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt8') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt9_li1') }}</li>
                                    <li>{{ __('terms.txt9_li2') }}</li>
                                    <li>{{ __('terms.txt9_li3') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="propiedad-intelectual" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt9') }}</h3>
                            <p class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                {{ __('terms.txt10') }}
                            </p>
                        </section>

                        <section id="responsabilidad" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt10') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <p class="mb-3 font-semibold">{{ __('terms.txt11') }}</p>
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt11_li1') }}</li>
                                    <li>{{ __('terms.txt11_li2') }}</li>
                                    <li>{{ __('terms.txt11_li3') }}</li>
                                    <li>{{ __('terms.txt11_li4') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="suspension" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt11') }}</h3>
                            <div class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                <p class="mb-3 font-semibold">{{ __('terms.txt12') }}</p>
                                <ul class="list-disc pl-5 space-y-2">
                                    <li>{{ __('terms.txt12_li1') }}</li>
                                    <li>{{ __('terms.txt12_li2') }}</li>
                                    <li>{{ __('terms.txt12_li3') }}</li>
                                </ul>
                            </div>
                        </section>

                        <section id="modificaciones" class="scroll-mt-28">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt12') }}</h3>
                            <p class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                {{ __('terms.txt13') }}
                            </p>
                        </section>

                        <section id="contacto" class="scroll-mt-28 mb-8">
                            <h3 class="col1 text-xl pl-6 pr-6 pt-2 font-bold mb-2">{{ __('terms.index_txt13') }}</h3>
                            <p class="col7 rounded-md text-sm p-6 lg:text-base leading-relaxed bgcol3 block">
                                {{ __('terms.txt14') }}
                            </p>
                        </section>

                </main>
            </div>
        </div>

        <div class="h-auto w-full rotate-180 mb-6">
            <object data="{{ asset('images/BarAppere.svg') }}" type="image/svg+xml" class="w-full object-contain"></object>
        </div>

        <x-footer/>
    </div>
</x-layout>


