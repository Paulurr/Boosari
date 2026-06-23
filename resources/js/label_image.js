class ImagePreview{
    constructor(name){
        if(!document.getElementById(name+'-image')){
            return;
        }
        const input = document.getElementById(name+'-image');
        const preview = document.getElementById(name+'-preview');

        input.addEventListener('change', () => {
            const archivo = input.files[0];

            if (archivo) {
                preview.src = URL.createObjectURL(archivo);
            }
        });
    }
}
let wallet = new ImagePreview("wallet");
let income = new ImagePreview("income");
let transaction = new ImagePreview("transaction");

