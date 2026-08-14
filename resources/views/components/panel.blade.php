@props(['name' => '',
        'title' => ''
        ,'info' => false
        ,'showDelete' => true
        ,'newH' => 'h-10/12'
])
<div {{ $attributes->merge(['id' => "{$name}-cont", 'class' => 'top-12 left-0 w-full h-[95vh] z-10 fixed hidden items-center justify-center']) }}>
    <div class="bgcol1 {{ $newH }} rounded-xs overflow-hidden lg:w-220 xl:w-280 md:w-180 w-11/12 flex flex-col relative z-1">
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
            <div class="h-full w-auto flex justify-end items-center gap-2 px-4">
                @if($info == false)
                    <x-button
                        color1="red"
                        color2="white"
                        colortext="white"
                        class="modal-close p-2 col7 {{ $name }}-close"
                        type="button"
                        >
                        Cancelar
                    </x-button>
                    <x-button
                        color1="var(--col4)"
                        color2="var(--col3)"
                        colortext="var(--col1)"
                        class="modal-btn-submit {{$name}}-submit p-2 col7"
                        id="{{$name}}-submit"
                        >
                        Enviar
                    </x-button>
                @else
                    {{-- Modo Lectura: Salir, Eliminar, Editar --}}
                    <x-button
                        color1="red"
                        color2="white"
                        colortext="white"
                        class="modal-close {{ $name }}-close p-2 col7"
                        type="button"
                        >
                        Salir
                    </x-button>
                    @if($showDelete)
                        <x-button
                            color1="red"
                            color2="white"
                            colortext="white"
                            class="modal-btn-delete {{$name}}-delete p-2 col7"
                            type="button"
                            >
                            Eliminar
                        </x-button>
                    @endif
                    <x-button
                        color1="var(--col4)"
                        color2="var(--col3)"
                        colortext="var(--col1)"
                        class="modal-btn-edit {{$name}}-edit p-2 col7"
                        type="button"
                        >
                        Editar
                    </x-button>

                    {{-- Modo Edición: Cancelar, Aceptar (Ocultos por defecto) --}}
                    <x-button
                        color1="red"
                        color2="white"
                        colortext="white"
                        class="modal-btn-cancel {{$name}}-cancelEdit p-2 col7 hidden"
                        type="button"
                        >
                        Cancelar
                    </x-button>
                    <x-button
                        color1="var(--col4)"
                        color2="var(--col3)"
                        colortext="var(--col1)"
                        class="modal-btn-submit {{$name}}-submit p-2 col7 hidden"
                        type="button"
                        >
                        Aceptar
                    </x-button>
                @endif
            </div>
        </div>
    </div>
    <div class="{{ $name }}-close absolute top-0 left-0 w-full h-full panel-deco cursor-pointer"></div>
</div>