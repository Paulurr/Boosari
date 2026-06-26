@props([
    'name' => '',
    'value' => ''
])

<li
    {{ $attributes->merge([
        'class' => "border-b p-3 {$name}-option x-option",
        'data-value' => $value
    ]) }}
>
    {{ $slot }}
</li>