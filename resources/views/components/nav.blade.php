@vite(['resources/js/nav.js'])
@guest
    
<nav class="h-15 w-full flex flex-col fixed top-0 pt-2 select-none z-50 bgcol2  overflow-hidden">
    <div class="flex h-11/12 w-auto">
        <div class="h-full lg:w-1/4 w-full flex items-center justify-center pl-4 pr-4 lg:pl-0 sm:pr-0">

            <div class="nav-menu-cont flex items-center h-screen w-1/3 lg:hidden">
                <div class="h-full w-full fixed top-0 flex nav-menu-back right-full flex-col" id="nav-menu-back"></div>
                <div class="h-screen w-64 fixed top-0 right-3/3 flex nav-menu flex-col overflow-y-auto" id="nav-menu">
                    <div class="ul-menu-nav p-3 min-h-150">
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/" class="a-menu-nav text-sm p-2">
                                Inicio
                            </a>
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                Sobre nosotros
                            </a>
                            
                        </div>
                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                Acerca de
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
            </div>
            <a href="/" class="flex justify-center h-full w-1/3">
                <img src="{{ asset('images/logotipo_boosari.webp') }}" alt="Logo" srcset="" class="h-10/12 object-contain">
            </a>
            <ul class="ul-nav-menu flex items-center  justify-end sm:justify-evenly h-full w-1/3">
                <li class="li-nav mr-1">
                    <a href="">
                        <img src="{{ asset('images/bandera.png') }}" alt="" class="h-10 object-contain">
                    </a>
                    
                </li>
                <li class="li-nav">
                    <a href="/log_in" class="a-nav a-nav-log p-1 rounded-md overflow-hidden text-xs lg:text-lg" >
                        Iniciar Sesión
                    </a>
                    
                </li>
                <li class="li-nav">
                    <a href="/sign_up" class="a-nav a-nav-up p-1 hidden md:flex rounded-md overflow-hidden text-xs lg:text-lg" >
                        Crear una cuenta
                    </a>
                    
                </li>
            </ul>
        </div>
        <ul class="ul-nav h-full w-[65%] flex items-center justify-end 100 ">
            <li class="li-nav">
                <a href="/" class="a-nav">
                    Inicio
                </a>
            </li>
            <li class="li-nav">
                <a href="" class="a-nav">
                    Sobre nosotros
                </a>
                
            </li>
            </li>
            <li class="li-nav">
                <a href="" class="a-nav">
                    Acerca de
                </a>
                
            </li>
            
    
        </ul>
        <ul class="ul-nav h-full w-1/2 flex items-center justify-end">
            <li class="li-nav">
                <a href="">
                    <img src="{{ asset('images/bandera.png') }}" alt="" class="h-10">
                </a>
                
            </li>
            <li class="li-nav">
                <a href="/log_in" class="a-nav a-nav-log p-1 rounded-md overflow-hidden" >
                    Iniciar Sesión
                </a>
                
            </li>
            <li class="li-nav">
                <a href="/sign_up" class="a-nav a-nav-up p-1 rounded-md overflow-hidden">
                    Crear una cuenta
                </a>
            </li>
        </ul>
    </div>
    <div class="line-nav w-auto h-1/12 flex"></div>
</nav>
@endguest
@auth
<nav class="h-15 w-full flex flex-col  sticky top-0 pt-2 select-none z-10 bgcol2">

    <div class="flex h-11/12 w-full items-center">

        <div class="h-full lg:w-full w-full flex items-center justify-between pl-4 pr-4">

            <!-- Contenedor hamburguesa -->
            <div class="nav-menu-cont w-1/3 lg:w-auto flex items-center">

                <div class="h-full w-full fixed top-0 flex nav-menu-back right-full flex-col" id="nav-menu-back"></div>


                <div class="h-screen w-64 lg:w-120 fixed top-0 left-0 flex nav-menu flex-col overflow-y-auto -translate-x-full" id="nav-menu">

                    <div class="ul-menu-nav p-3 min-h-150">

                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="/" class="a-menu-nav text-sm lg:text-xl p-2">
                                Hogar
                            </a>
                        </div>

                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm lg:text-xl p-2">
                                Tarjetas
                            </a>
                        </div>

                        <div class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm lg:text-xl p-2">
                                Deudas
                            </a>
                        </div>

                    </div>
                </div>

                <button class="nav-menu-button p-2 cursor-pointer relative rounded-full" id="nav-menu-button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 6H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav1"/>
                        <path d="M3 12H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav2"/>
                        <path d="M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav3"/>
                    </svg>

                    <div class="nav-menu-button-div2" id="nav-menu-button-div2"></div>
                    <div class="nav-menu-button-div1" id="nav-menu-button-div1"></div>
                </button>

            </div>

            <!-- Logo -->
            <a href="/" class="flex lg:justify-start justify-center items-center h-full w-1/3 lg:w-auto lg:ml-10 lg:p-1">
                <img src="{{ asset('images/logotipo_boosari.webp') }}" alt="Logo" srcset="" class="h-10/12 object-contain">
            </a>
            <ul class="ul-nav h-full w-[75%] pr-15 flex items-center justify-end 100">
                <li class="li-nav">
                    <a href="/" class="a-nav">
                        Hogar
                    </a>
                </li>
                <li class="li-nav">
                    <a href="" class="a-nav">
                        Tarjetas
                    </a>
                    
                </li>
                </li>
                <li class="li-nav">
                    <a href="/deudas" class="a-nav">
                        Deudas
                    </a>
                    
                </li>
        
            </ul>
            <div class="h-full lg:w-auto w-1/3 flex items-center justify-end pb-1">
                    
                <div class="li-nav m-2">
                    <a href="">
                        <img src="{{ asset('images/bandera.png') }}" alt="" class="h-10 object-contain">
                    </a>
                    
                </div>
                <div class="perfil-div-nav border w-11 lg:w-auto h-full cursor-pointer overflow-hidden flex items-center justify-center rounded-full transition-colors" id="perfil-nav">
                        <img src="{{ asset('images/perfil.png') }}" alt="perfil" class="w-11 object-contain">
                </div>
                
            </div>
            

        </div>

    </div>

    <div class="line-nav w-full h-1"></div>
    <div id="perfil-menu-nav" class="perfil-menu-nav flex flex-col p-2">
        <a href= "/" class="w-full h-1/3 flex items-center perfil-menu-div-nav">
                <div class="pl-3 relative z-1">
                    Administrar perfil
                </div>
        </a>
        <a href="/" class="w-full h-1/3 flex items-center perfil-menu-div-nav">
                <div class="pl-3 relative z-1">
                    Configuración
                </div>

        </a>
        <form method="POST" action="/log_out" class="w-full h-1/3 flex items-center perfil-menu-div-nav">
            @csrf
                <button type="submit" class="pl-3 relative z-1 w-full h-full flex items-center justify-start cursor-pointer">
                    Cerrar Sesión                
                </button>
        </form>
    </div>
</nav>
@endauth