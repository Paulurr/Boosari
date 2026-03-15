@vite(['resources/js/nav.js'])
<div class="h-15 w-auto flex flex-col mt-2 select-none">
    <div class="flex h-11/12 w-auto">
        <div class="h-full lg:w-1/4 w-full flex items-center justify-center pl-4 pr-4 lg:pl-0 sm:pr-0">

            <div class="nav-menu-cont flex items-center h-full w-1/3 lg:hidden">
                <div class="h-full w-full absolute top-0 flex nav-menu-back right-full flex-col" id="nav-menu-back"></div>
                <div class="h-full w-1/3 absolute top-0 right-3/3 flex nav-menu flex-col" id="nav-menu">
                    <div class="h-15 w-full"></div>
                    <ul class="ul-menu-nav flex flex-col h-full p-3">
                        <li class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                Inicio
                            </a>
                        </li>
                        <li class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                Sobre nosotros
                            </a>
                            
                        </li>
                        </li>
                        <li class="li-menu-nav rounded-br-md rounded-tr-md overflow-hidden">
                            <a href="" class="a-menu-nav text-sm p-2">
                                Acerca de
                            </a>
                            
                        </li>
                    </ul>
                </div>
                <button class="nav-menu-button p-2 cursor-pointer relative rounded-full" id="nav-menu-button">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav1"/>
                        <path d="M3 12H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav2"/>
                        <path d="M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" id="path-nav3"/>
                    </svg>
                </button>
            </div>
            <a href="" class="flex justify-center h-full w-1/3">
                <img src="{{ asset('images/logotipo_boosari.webp') }}" alt="Logo" srcset="" class="h-10/12 object-contain">
            </a>
            <ul class="ul-nav-menu flex items-center  justify-end sm:justify-evenly h-full w-1/3">
                <li class="li-nav">
                    <a href="" class="a-nav a-nav-log p-1 rounded-md overflow-hidden text-sm lg:text-lg" >
                        Iniciar Sesión
                    </a>
                    
                </li>
                <li class="li-nav">
                    <a href="" class="a-nav a-nav-up p-1 hidden sm:flex rounded-md overflow-hidden text-sm lg:text-lg" >
                        Registrarse
                    </a>
                    
                </li>
            </ul>
        </div>
        <ul class="ul-nav h-full w-[55%] flex items-center justify-end 100">
            <li class="li-nav">
                <a href="" class="a-nav">
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
        <ul class="ul-nav h-full w-1/4 flex items-center justify-end">
            <li class="li-nav">
                <a href="" class="a-nav a-nav-log p-1 rounded-md overflow-hidden" >
                    Iniciar Sesión
                </a>
                
            </li>
            <li class="li-nav">
                <a href="" class="a-nav a-nav-up p-1 rounded-md overflow-hidden">
                    Registrarse
                </a>
            </li>
        </ul>
    </div>
    <div class="line-nav w-auto h-1/12 flex"></div>
</div>