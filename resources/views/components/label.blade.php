@props([
    'name',
    'type' => 'text',
    'title',
    'color1',
    'color2'
])
<label for="" class="flex flex-col w-3/5 label-cont">
    <input name="${{ $name }}" type="{{ $type }}" class="input-label mt-5"
    style="--input-color1:{{ $color1 }};--input-color2:{{ $color2 }};">
    <span class="name-label absolute "
    style="--color-deco:{{ $color1 }};">
        {{ $title }}
    </span>
        <div class="input-deco" style="--color-deco:{{ $color1 }};"></div>
</label>
<script>
    document.querySelectorAll(".input-label").forEach(input => {
        
        function checkValue() {
            if (input.value.trim() !== "") {
                input.classList.add("has-value");
            } else {
                input.classList.remove("has-value");
            }
        }

        // cuando escribe
        input.addEventListener("input", checkValue);

        // cuando pierde focus
        input.addEventListener("blur", checkValue);

        // por si ya tiene valor al cargar
        checkValue();
    });
</script>