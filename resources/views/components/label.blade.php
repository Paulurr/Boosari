@props([
    'name',
    'type' => 'text',
    'title',
    'color1',
    'color2',
    'w' => 'w-3/5',
    'maxlength' => '',
    'required' => false,
    'value' => ''
])
@vite(['resources/js/label.js'])
<label for="" class="flex flex-col {{$w}} label-cont">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder=" "
        {{$required ? "required" : ""}}
        maxlength="{{$maxlength}}"
        {{-- Si el tipo es password, el valor SIEMPRE será vacío. Si es texto/email, usará old() --}}
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        class="input-label mt-5"
        style="--input-color1:{{ $color1 }};--input-color2:{{ $color2 }};"
    >
    <span class="name-label absolute" style="--color-deco:{{ $color1 }};">
        {{ $title }}
    </span>
    <div class="input-deco" style="--color-deco:{{ $color1 }};"></div>
</label>