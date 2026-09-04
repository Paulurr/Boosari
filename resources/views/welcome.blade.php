<x-layout title="Welcome">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/welcome.js'])

    </x-slot:head>

    <x-nav/>
    <x-nav></x-nav>
        <div class="h-200 lg:h-230 w-full relative">
            <div class="h-2/3 w-full flex  items-center justify-center">
             
                <div class="flex flex-col w-full h-11/12 pt-15">
                    <div class=" flex items-center xl:justify-between justify-center h-full w-full relative z-2">
                        <div class="xl:flex h-10/12 w-full items-center justify-center hidden">
                             <object data="{{ asset('images/IndexXlAnimate.svg') }}" type="image/svg+xml" class="h-full aspect-square"></object>
                        </div>
                        <object type="image/svg+xml" class="md:h-full md:w-auto w-full h-auto object-fill" id="IndexAnimate"></object>
                    </div>
                    
                </div>
            </div>
            <div class="h-auto w-full flex justify-center">
                <div class="h-35  {{app()->getLocale() == 'en' ? 'w-90' : 'w-150'}} flex justify-evenly items-center flex-col lg:flex-row ">
                    <x-button
                        color1="var(--col3)"
                        color2="var(--col4)"
                        colortext="var(--col7)"
                        class="p-4 lg:text-3xl text-sx lg:mb-0"
                    >
                        <a id="log_in" href="/log_in">                    
                            {{ __('welcome.log_in') }}
                        </a>
                    </x-button>
                    <x-button 
                        color1="var(--col4)"
                        color2="var(--col3)"
                        class="p-4 lg:text-3xl text-sx "
                    >
                        <a href="/sign_up">                    
                            {{ __('welcome.sign_up') }}
                        </a>
                    </x-button>
                </div>

            </div>
            
            <div class="h-1/6 w-full flex justify-center items-center">
                <a href="#seccion-destino" class="inline-block h-1/2">
                    <object data="{{ asset('images/masAbajo.svg') }}" type="image/svg+xml" class="h-full object-fill pointer-events-none"></object>
                </a>
            </div>
            <div class="parallax z-10">
                <div class="w-full h-15 mt-10 overflow-hidden bgcol1">
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol3 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol3 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                </div>
                <div class="h-110 w-full bgcol4 flex flex-col lg:flex-row items-center justify-center p-5 pt-0 lg:pl-50 lg:pr-50">
                    <div class="lg:h-160 h-1/3 flex justify-center items-center m-5 lg:m-0 lg:p-30 lg:text-3xl col1 text-xl text-center font-bold">
                        {{ __('welcome.txt_1') }}
                    </div>
                    <div class="h-2/3 aspect-square bgcol1 rounded-md lg:mb-0 mb-20">
                        <object data="{{ asset('images/GoalsAnimate.svg') }}" type="image/svg+xml" class="w-full h-full object-contain"></object>
                    </div>
                </div>
                <div class="w-full h-15 mb-10 overflow-hidden rotate-180 bgcol1">
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol3 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol3 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                    <div class="bgcol4 w-400 h-10 rotate-45 m-15 welcome-animation-div-scale"></div>
                </div>
            </div>

            <div id="welcome-scroll-div-cont" class="lg:h-[180vh] h-[150vh] bgcol3 flex relative ">
                <div class="h-full w-full overflow-hidden absolute top-0 left-0">
                    <div id="welcome-scroll-div-animate" 
                        class="bgcol4 origin-left h-full w-full absolute top-0 left-0 z-1 overflow-hidden">

                        <div id="welcome-textoScroll" class="lg:text-[200px] md:text-9xl text-5xl font-bold absolute col1 h-250 w-full flex justify-center items-center flex-col">
                            {{ __('welcome.txt_3') }}
                            <div class="h-150 flex lg:flex-row lg:p-40 p-10 flex-col justify-center items-center">
                                <div class="text-lg hidden lg:flex flex-col justify-evenly items-center font-normal mt-20 p-30 pb-0 pt-0">
                                    <div class="hidden xl:block w-auto  mb-10 text-center">
                                        {{ __('welcome.txt_4') }}
                                    </div>
                                        <x-button 
                                        color1="var(--col1)"
                                        color2="var(--col4)"
                                        colortext="var(--col7)"
                                        class="p-4 text-xl"
                                    >
                                        <a href="/log_in">                    
                                            {{ __('welcome.log_in') }}
                                        </a>
                                    </x-button>
                                </div>
                                <div class="text-lg font-normal mb-20 mt-10 lg:hidden md:pr-50 md:pl-50 sm:pr-30 pr-20 sm:pl-30 pl-20">{{ __('welcome.txt_4') }}
                                 
                                </div>
                                <div id="seccion-destino" class="lg:h-120 h-2/3 aspect-square bgcol1 rounded-md">
                                    
                                    <object data="{{ asset('images/Organizate.svg') }}" type="image/svg+xml" class="w-full h-full object-contain"></object>
                                </div>
                            </div>
                            
                        </div>

                    </div>    
                </div>
                    <div class="h-screen transition-transform w-full p-10 flex flex-col lg:flex-row sticky top-0 left-0 justify-center items-center">

                        <div class=" flex-col  justify-evenly p-10 mt-10 h-full lg:flex hidden overflow-hidden items-center">
                            <div class="font-bold text-center text-2xl">{{ __('welcome.txt_5') }}</div>
                            <div class="w-80 rounded-md bgcol1 relative aspect-square overflow-hidden">
                                 <object data="{{ asset('images/PDF_excel_wlcome_donwload_icone.svg') }}" type="image/svg+xml" class="h-full w-full object-contain"></object>
                            </div>
                        </div>
                        <div class=" flex-col  justify-evenly p-10 mt-10 h-full lg:flex hidden overflow-hidden items-center">
                            <div class="w-80 rounded-md bgcol1 relative aspect-square overflow-hidden">
                                <object data="{{ asset('images/RegisterAnimateWelcome.svg') }}" type="image/svg+xml" class="h-full w-full object-contain"></object>
                            </div>
                            <div class="font-bold text-center text-2xl">{{ __('welcome.txt_6') }}</div>
                        </div>
                        <div class=" flex-col  justify-evenly p-10 mt-10 h-full lg:flex hidden overflow-hidden items-center">
                            <div class="font-bold text-center text-2xl">{{ __('welcome.txt_7') }}</div>
                            <div class="w-80 rounded-md bgcol1 relative flex items-center justify-center aspect-square overflow-hidden">
                                <object data="{{ asset('images/GraficsWelcomeAnimate.svg') }}" type="image/svg+xml" class="h-full w-full object-contain"></object>
                            </div>
                        </div>
      
                        <div class=" flex-col  justify-evenly p-10 mt-10 h-full flex lg:hidden overflow-hidden items-center">
                            <div class="font-bold text-center text-2xl">{{ __('welcome.txt_5') }}</div>
                            <div class="w-80  rounded-md bgcol1 relative aspect-square overflow-hidden">
                                 <object data="{{ asset('images/PDF_excel_wlcome_donwload_icone.svg') }}" type="image/svg+xml" class="h-full w-full object-contain"></object>
                            </div>
                        </div>
                    </div>
                </div>

              
                <object data="{{ asset('images/Comienza.svg') }}" type="image/svg+xml" class="w-full h-auto"></object>
              
                <x-footer/>

            </div>
            <div class="bgcol1 h-180 flex justify-center text-center items-end p-30 lg:text-5xl text-4xl font-bold">
                {{ __('welcome.txt_2') }}
            </div>
            
        </div>
</x-layout>
