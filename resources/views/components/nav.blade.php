@vite(['resources/js/nav.js','resources/js/ThemeMode.js'])
@guest
    
<nav class="h-15 w-full flex flex-col fixed top-0 pt-2 select-none z-50 bgcol2 ">
    <div class="flex h-11/12 w-auto">
        <div class="h-full lg:w-1/4 w-full flex items-center justify-center pl-4 pr-4 lg:pl-0 sm:pr-0">

            <div class="nav-menu-cont flex items-center h-auto w-1/3 lg:hidden">
                <div class="h-full w-full fixed top-0 flex nav-menu-back right-full flex-col" id="nav-menu-back"></div>
                <div class="h-screen w-64 fixed top-0 right-3/3 flex nav-menu flex-col overflow-y-auto" id="nav-menu">
                    <div class="ul-menu-nav p-3 min-h-150">
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/" class="a-menu-nav text-sm p-2">
                                {{ __('nav.nav_home') }}
                            </a>
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                {{ __('nav.about') }}
                            </a>
                            
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/terms" class="a-menu-nav text-sm p-2">
                                {{ __('nav.services') }}
                            </a>
                            
                        </div>
                        
                    </div>
                </div>
                <button class="nav-menu-button p-2 cursor-pointer relative rounded-full" id="nav-menu-button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav1"/>
                        <path d="M3 12H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav2"/>
                        <path d="M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav3"/>
                    </svg>
                    <div class="nav-menu-button-div2" id="nav-menu-button-div2"></div>
                    <div class="nav-menu-button-div1" id="nav-menu-button-div1"></div>
                </button>
                <div class="lg:hidden ml-3 bgcol3 h-5 w-10 relative cursor-pointer switch-theme overflow-hidden">
                    <div
                        class="absolute top-0 h-full w-1/2 bgcol1 switch-theme-div transition-transform duration-300">
                    </div>
                </div>
                                
            </div>
          
            <a href="/" class="flex justify-center h-full w-1/3">
                <img src="{{ asset('images/LogoTypeLight.svg') }}" alt="Logo" srcset="" class="h-full lg:w-auto logo-theme w-full object-contain">
            </a>
             <div class="lg:flex hidden ml-3 bgcol3 h-5 w-10 relative cursor-pointer switch-theme overflow-hidden">
                    <div
                        class="absolute top-0 h-full w-1/2 bgcol1 switch-theme-div transition-transform duration-300">
                    </div>
                </div>
            <ul class="ul-nav-menu flex items-center  justify-end sm:justify-evenly h-full w-1/3">
                <li class="li-nav mr-1">
                    <a href="{{ route('lang.switch', app()->getLocale() == 'en' ? 'es' : 'en') }}">
                        <img 
                            src="{{ asset(app()->getLocale() == 'en' 
                                ? 'images/bandera_en.png' 
                                : 'images/bandera_es.png') }}" 
                            alt="Switch Language" 
                            class="h-10"
                        >
                    </a>
                </li>
                <li class="li-nav">
                    <a href="/log_in" class="a-nav a-nav-log p-1 rounded-md overflow-hidden text-xs lg:text-lg" >
                        {{ __('nav.log_in') }}
                    </a>
                    
                </li>
                <li class="li-nav">
                    <a href="/sign_up" class="a-nav a-nav-up p-1 hidden md:flex rounded-md overflow-hidden text-xs lg:text-lg" >
                        {{ __('nav.sign_up') }}
                    </a>
                    
                </li>
            </ul>
        </div>
        <ul class="ul-nav h-full w-[65%] flex items-center justify-end 100 ">
            <li class="li-nav">
                <a href="/" class="a-nav">
                    {{ __('nav.nav_home') }}
                </a>
            </li>
            <li class="li-nav">
                <a href="/about_us" class="a-nav">
                    {{ __('nav.about') }}
                </a>
                
            </li>
            </li>
            <li class="li-nav">
                <a href="/terms" class="a-nav">
                    {{ __('nav.services') }}
                </a>
                
            </li>
            
    
        </ul>
        <ul class="ul-nav h-full w-1/2 flex items-center justify-end">
            <li class="li-nav">
                <a href="{{ route('lang.switch', app()->getLocale() == 'en' ? 'es' : 'en') }}">
                        <img 
                            src="{{ asset(app()->getLocale() == 'en' 
                                ? 'images/bandera_en.png' 
                                : 'images/bandera_es.png') }}" 
                            alt="Switch Language" 
                            class="h-10"
                        >
                    </a>
            </li>
            <li class="li-nav">
                <a href="/log_in" class="a-nav a-nav-log p-1 rounded-md overflow-hidden" >
                    {{ __('nav.log_in') }}
                </a>
                
            </li>
            <li class="li-nav">
                <a href="/sign_up" class="a-nav a-nav-up p-1 rounded-md overflow-hidden">
                    {{ __('nav.sign_up') }}
                </a>
            </li>
        </ul>
    </div>
    <div class="line-nav w-auto h-1/12 flex"></div>
</nav>
@endguest
@auth
    
<nav class="h-15 w-full flex flex-col fixed top-0 pt-2 select-none z-50 bgcol2">
    <div class="flex h-11/12 w-auto">
        <div class="h-full lg:w-1/4  w-full flex items-center justify-between lg:justify-center pl-4 pr-4 lg:pl-0 sm:pr-0">

            <div class="nav-menu-cont flex items-center h-auto w-1/3 lg:hidden">
                <div class="h-full w-full fixed top-0 flex nav-menu-back right-full flex-col" id="nav-menu-back"></div>
                <div class="h-screen w-64 fixed top-0 right-3/3 flex nav-menu flex-col overflow-y-auto" id="nav-menu">
                    <div class="ul-menu-nav p-3 min-h-150">
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/" class="a-menu-nav text-sm p-2">
                                {{ __('nav.nav_home') }}
                            </a>
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="agent" class="a-menu-nav text-sm p-2">
                                {{ __('nav.agent') }}
                            </a>
                            
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/terms" class="a-menu-nav text-sm p-2">
                                {{ __('nav.services') }}
                                
                            </a>
                            
                        </div>
                        
                    </div>
                </div>
                <button class="nav-menu-button p-2 cursor-pointer relative rounded-full" id="nav-menu-button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav1"/>
                        <path d="M3 12H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav2"/>
                        <path d="M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav3"/>
                    </svg>
                    <div class="nav-menu-button-div2" id="nav-menu-button-div2"></div>
                    <div class="nav-menu-button-div1" id="nav-menu-button-div1"></div>
                </button>
                <div class="lg:hidden ml-3 bgcol3 h-5 w-10 relative cursor-pointer switch-theme overflow-hidden">
                    <div
                        class="absolute top-0 h-full w-1/2 bgcol1 switch-theme-div transition-transform duration-300">
                    </div>
                </div>
                                
            </div>
          
            <a href="/" class="flex justify-center h-full w-1/3">
                <img src="{{ asset('images/LogoTypeLight.svg') }}" alt="Logo" srcset="" class="h-full lg:w-auto logo-theme w-full object-contain">
            </a>
             <div class="lg:flex hidden ml-3 bgcol3 h-5 w-10 relative cursor-pointer switch-theme overflow-hidden">
                    <div
                        class="absolute top-0 h-full w-1/2 bgcol1 switch-theme-div transition-transform duration-300">
                    </div>
                </div>
            <ul class="ul-nav-menu flex items-center  justify-end sm:justify-evenly h-full w-auto">
                <li class="li-nav mr-1">
                    <a href="{{ route('lang.switch', app()->getLocale() == 'en' ? 'es' : 'en') }}">
                        <img 
                            src="{{ asset(app()->getLocale() == 'en' 
                                ? 'images/bandera_en.png' 
                                : 'images/bandera_es.png') }}" 
                            alt="Switch Language" 
                            class="h-10"
                        >
                    </a>
                </li>
            </ul>
        </div>
        <ul class="ul-nav h-full w-[65%] flex items-center justify-end 100 ">
            <li class="li-nav">
                <a href="/" class="a-nav">
                    {{ __('nav.nav_home') }}
                </a>
            </li>
            <li class="li-nav">
                <a href="/agent" class="a-nav">
                    {{ __('nav.agent') }}
                </a>
                
            </li>
            </li>
            <li class="li-nav">
                <a href="/terms" class="a-nav">
                    {{ __('nav.services') }}
                </a>
                
            </li>
            
    
        </ul>
        <ul class="ul-nav h-full w-1/2 flex items-center justify-end">
            <li class="li-nav">
                <a href="{{ route('lang.switch', app()->getLocale() == 'en' ? 'es' : 'en') }}">
                        <img 
                            src="{{ asset(app()->getLocale() == 'en' 
                                ? 'images/bandera_en.png' 
                                : 'images/bandera_es.png') }}" 
                            alt="Switch Language" 
                            class="h-10"
                        >
                    </a>
            </li>
            
        </ul>
        <div class="h-full lg:w-auto w-1/6 flex items-center justify-end pb-1 mr-5">
               
                <div class="perfil-div-nav border bg-transparent aspect-square h-11/12 rounded-full cursor-pointer overflow-hidden flex items-center justify-center  transition-colors" id="perfil-nav">
                    
                    <img src="{{ asset("images/account.png") }}" alt="" >                    
                </div>
                
            </div>
            

        </div>

    </div>

    <div class="line-nav w-full h-1"></div>
    <div id="perfil-menu-nav" class="perfil-menu-nav flex flex-col p-2">
        <a href="/profile" class="w-full h-full">
             <x-button
                    color1="var(--col4)"
                    color2="var(--col3)"
                    colortext="var(--col1)"
                    class="p-2 w-63 h-full text-start"
                    >
                    {{ __('nav.profile_config') }}

            </x-button>

        </a>
        <a href="/config" class="w-full h-full">
                
            <x-button
                        color1="var(--col4)"
                        color2="var(--col3)"
                        colortext="var(--col1)"
                        class=" p-2 w-63 h-full text-start"
                        >
                        {{ __('nav.settings') }}

            </x-button>
        </a>

        @if (in_array((int) auth()->user()->roles_id, [2, 3]))
            <a href="{{ route('usuarios.index') }}" class="w-full h-full">
                <x-button
                    color1="var(--col4)"
                    color2="var(--col3)"
                    colortext="var(--col1)"
                    class="p-2 w-63 h-full text-start"
                    >
                    {{ __('nav.user_management') }}
                </x-button>
            </a>
        @endif

        <form method="POST" action="/log_out" class="w-full h-full">
            @csrf
                <x-button
                    color1="red"
                    color2="white"
                    colortext="var(--col1)"
                    class=" p-2 w-63 h-full text-start "
                    type="submit"
                    >
                    {{ __('nav.log_out') }}
                </x-button>

        </form>
    </div>
</nav>
@endauth