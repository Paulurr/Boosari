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
let investment = new ImagePreview("investment");
let transaction = new ImagePreview("transaction");
let goal = new ImagePreview("goal");
let paygoal = new ImagePreview("paygoal");
let debt = new ImagePreview("debt");
let paymentdebt = new ImagePreview("paymentdebt");

