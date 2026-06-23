@props([
    'name',
    'type' => 'text',
    'title',
    'color1',
    'color2',
    'w' => 'w-3/5',
    'maxlength' => '',
])
@vite(['resources/js/label.js'])
<label for="" class="flex flex-col {{$w}} label-cont">
    <input 
        name="{{ $name }}" 
        type="{{ $type }}" 
        placeholder=" "
        required
        maxlength="{{$maxlength}}"
        class="input-label mt-5"
        style="--input-color1:{{ $color1 }};--input-color2:{{ $color2 }};"
    >
    <span class="name-label absolute "
    style="--color-deco:{{ $color1 }};">
        {{ $title }}
    </span>
        <div class="input-deco" style="--color-deco:{{ $color1 }};"></div>
</label>
