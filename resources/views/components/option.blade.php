@props([
    'name' => '',
    'value' => ''
])

<li
    class="border-b p-3 {{$name}}-option x-option"
    data-value="{{$value}}"
>
    {{$slot}}
</li>