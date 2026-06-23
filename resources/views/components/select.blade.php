@props(['title'=>'Titulo','name'=>'none','first'=>'Ninguno' ])
@vite(['resources/js/select.js'])
<div class="text-xs sm:text-sm h-20 w-full flex justify-center items-center ">
    {{$title}}
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
<input type="text" value="{{$first}}" name="{{$name}}-select-value" id="{{$name}}-select-value" class="w-0 h-0" >