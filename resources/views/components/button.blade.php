@props(['color1','color2' => 'var(--col2)','colortext' => 'var(--col1)'])
<button type="submit" class="page-button rounded-md h-auto w-auto "
style="--page-button1:{{ $color1 }};--page-button2:{{ $color2 }};--color-page-text:{{ $colortext }};">

    <div
        {{ $attributes->merge([
            'class' => 'h-full page-button-div'
        ]) }}
    style="--page-button-div1:{{ $color1 }};--page-button-div2:{{ $color2 }};">
        {{$slot}}
    </div>
</button>