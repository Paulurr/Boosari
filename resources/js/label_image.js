class ImagePreview {
    constructor(name) {
        this.name = name;
        this.input = document.getElementById(name + '-image');
        this.preview = document.getElementById(name + '-preview');
        this.defaultSrc = this.preview ? this.preview.getAttribute('src') : null;

        if (!this.input || !this.preview) {
            return;
        }

        this.input.addEventListener('change', () => {
            const archivo = this.input.files[0];
            if (archivo) {
                this.preview.src = URL.createObjectURL(archivo);
            }
        });

        // Se registra la instancia para que otros scripts (ej. info_wallet.js)
        // puedan reutilizar el mismo componente: precargar el icono actual al
        // editar y leer el archivo elegido al enviar el formulario.
        window.imagePreviews = window.imagePreviews || {};
        window.imagePreviews[name] = this;
    }

    /** Devuelve el archivo nuevo seleccionado por el usuario, o null si no cambió. */
    getFile() {
        return this.input?.files?.[0] || null;
    }

    /** Muestra en el <img> una URL existente (ej. el icono actual del registro). */
    setPreview(url) {
        if (!this.preview) return;
        this.preview.src = url || this.defaultSrc || '';
    }

    /** Limpia el archivo seleccionado (no toca la vista previa mostrada). */
    clearFile() {
        if (this.input) this.input.value = '';
    }

    /** Vuelve todo a su estado inicial: sin archivo y con la imagen por defecto. */
    reset() {
        this.clearFile();
        if (this.preview) this.preview.src = this.defaultSrc || '';
    }
}

window.ImagePreview = ImagePreview;

// Formularios de creación (comportamiento original, sin cambios)
let wallet = new ImagePreview("wallet");
let income = new ImagePreview("income");
let investment = new ImagePreview("investment");
let transaction = new ImagePreview("transaction");
let goal = new ImagePreview("goal");
let paygoal = new ImagePreview("paygoal");
let debt = new ImagePreview("debt");
let paymentdebt = new ImagePreview("paymentdebt");

let walletinfo = new ImagePreview("walletinfo");
let goalinfo = new ImagePreview("goalinfo");
let debtinfo = new ImagePreview("debtinfo");
let incomeinfo = new ImagePreview("incomeinfo");
let transactioninfo = new ImagePreview("transactioninfo");
let paymentGoalinfo = new ImagePreview("paymentGoalinfo");
let paymentDebtinfo = new ImagePreview("paymentDebtinfo");
let investmentinfo = new ImagePreview("investmentinfo");