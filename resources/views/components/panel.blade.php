@props(['name' => '','title' => ''])
<div id="{{ $name }}-cont" class=" top-12 left-0 w-full h-[95vh] fixed hidden items-center justify-center">
    <div class="bgcol1 h-10/12 rounded-xs overflow-hidden lg:w-220 xl:w-280 md:w-180 w-11/12 flex flex-col relative z-1">
        <div class="bgcol4 col1 h-10 w-full p-2 pl-5 pr-5 flex justify-between">
            <span>
                {{ $title }}
            </span>
            <span class="{{ $name }}-close font-bold transition-colors hover:text-black cursor-pointer">
                ✕
            </span>
        </div>
        <div class="w-full h-full barpage overflow-y-auto">
            {{ $slot }}
        </div>
        <div class="h-20 border-t-2 col4 flex justify-end">
            <div class="h-full w-1/2 md:w-1/4 flex justify-evenly items-center">
                <x-button
                    color1="red"
                    color2="white"
                    colortext="white"
                    class="p-2 text-red-500 {{ $name }}-close"
                    type="button"
                    >
                    Cancelar
                </x-button>
                <x-button
                    color1="var(--col4)"
                    color2="var(--col3)"
                    colortext="var(--col1)"
                    class="p-2 col7"
                    id="{{$name}}-submit"
                    >
                        Enviar
                </x-button>
            </div>
        </div>
    </div>
    <div class="{{ $name }}-close absolute top-0 left-0 w-full h-full panel-deco cursor-pointer">

    </div>
</div>