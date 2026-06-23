@props(['color1','color2' => 'var(--col2)','colortext' => 'var(--col1)','type' => 'submit','cont' => 'button' ,'id' => ''])
<{{$cont}} id="{{$id}}" type="{{$type}}" class="page-button rounded-md h-auto w-auto"
style="--page-button1:{{ $color1 }};--page-button2:{{ $color2 }};--color-page-text:{{ $colortext }};">

    <div
        {{ $attributes->merge([
            'class' => 'h-full page-button-div'
        ]) }}
    style="--page-button-div1:{{ $color1 }};--page-button-div2:{{ $color2 }};">
        {{$slot}}
    </div>
</{{$cont}}>