@props(['name'=>''])
@vite(['resources/js/label_image.js'])
<div class="w-auto h-auto">
    <label for="{{$name}}-image" class="cursor-pointer">
        <img
            id="{{$name}}-preview"
            src="{{ asset('images/logo_boosari.webp') }}"
            alt="Vista previa"
            class="w-15 sm:w-20 md:w-30 lg:w-40 mb-3 aspect-square p-2 rounded-lg border-2 border-dashed object-cover hover:opacity-80 transition"
        >
    </label>
    <label
        for="{{$name}}-image"
        class="mt-5"
    >
        <x-button
            color1="var(--col3)"
            color2="var(--col4)"
            colortext="var(--col7)"
            type="button"
            cont="div"
            class="p-4 w-15 sm:w-20 md:w-30 text-xs lg:w-40 text-center flex items-center justify-center"
            >
            {{ __('main.add_image') }}
        </x-button>
    </label>

    <input
        type="file"
        id="{{$name}}-image"
        name="{{$name}}-image"
        accept="image/*"
        class="hidden"
    />
</div>
