@props(['title'=>'Titulo','name'=>'none','first'=>'Ninguno', 'required' => false])
@vite(['resources/js/select.js'])

<div class="text-xs sm:text-sm h-auto p-2 min-h-20 w-full flex justify-center items-center">
    {{$title}} 
    @if($required)
        <div class="text-red-500 w-auto h-full flex items-center justify-center ml-2 font-bold">*</div>
    @endif
    
    <div class="ml-5 relative cursor-pointer w-50 sm:w-80 bgcol2 col7 border flex">
        <div id="{{$name}}-select" class="w-full p-3 flex items-center justify-between x-select h-10">
            <span id="{{$name}}-select-tit" class="x-select-tit">{{$first}}</span>
            <span class="scale-x-[1.5] x-select-v">V</span>
        </div>
        <ul id="{{$name}}-select-list" class="hidden absolute top-full left-0 bgcol1 w-full max-h-80 overflow-y-auto barpag z-5">
            {{$slot}}
        </ul>
    </div>
</div>

<input 
    type="text" 
    value="{{ $first === 'Ninguno' ? '' : $first }}" 
    name="{{$name}}-select-value" 
    id="{{$name}}-select-value" 
    class="absolute opacity-0 w-0 h-0 pointer-events-none generic-x-select-input"
    data-required="{{ $required ? 'true' : 'false' }}"
    data-name-prefix="{{$name}}"
>

<div id="{{$name}}-error-msg" class="text-red-500 text-xs mt-1 hidden text-center w-full font-medium generic-error-msg">
    Este campo es obligatorio.
</div>