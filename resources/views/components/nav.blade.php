@vite(['resources/js/nav.js'])
<div class="h-15 w-auto flex flex-col mt-2 select-none">
    <div class="flex h-11/12 w-auto">
        <div class="h-full lg:w-1/4 w-full flex items-center justify-center pl-4 pr-4 lg:pl-0 lg:pr-0">

            <div class="nav-menu-cont flex items-center h-full w-1/3 lg:hidden">
                <button class="nav-menu cursor-pointer" id="nav-menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="black" stroke-width="2" stroke-linecap="round"/>
                        <path d="M3 12H21" stroke="black" stroke-width="2" stroke-linecap="round"/>
                        <path d="M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="flex justify-center h-full w-1/3">
                <img src="{{ asset('images/logotipo_boosari.webp') }}" alt="Logo" srcset="" class="h-10/12 object-contain">
            </div>
            <div class="flex items-center justify-end h-full w-1/3">
                    
            </div>
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
                <a href="" class="a-nav">
                    Iniciar Sesión
                </a>
                
            </li>
            <li class="li-nav">
                <a href="" class="a-nav">
                    Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
    <div class="line-nav w-auto h-1/12 flex"></div>
</div>