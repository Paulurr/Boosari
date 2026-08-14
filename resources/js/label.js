// Lógica compartida de los inputs con floating label (componente x-label)

function checkValue(input) {
    if (input.value.trim() !== "") {
        input.classList.add("has-value");
    } else {
        input.classList.remove("has-value");
    }
}

function initLabelInput(input) {
    input.addEventListener("input", () => checkValue(input));
    input.addEventListener("blur", () => checkValue(input));

    checkValue(input);
    // Respaldo por si el navegador rellena el valor (autofill) después del render inicial
    setTimeout(() => checkValue(input), 1000);
}

document.querySelectorAll(".input-label").forEach(initLabelInput);

document.addEventListener("animationstart", (e) => {
    if (e.animationName === "autofill") {
        e.target.classList.add("has-value");
    }
});

/**
 * FIX GENERAL: cuando cualquier script hace `input.value = "algo"` por
 * código, el navegador NO dispara el evento "input" por sí solo, así que
 * la etiqueta flotante nunca se enteraba de que ya había texto (esto es lo
 * que causaba el texto superpuesto en cualquier panel: wallet, income,
 * transaction, o el que sea, presente o futuro).
 *
 * En vez de tener que acordarnos de llamar una función en cada panel,
 * interceptamos aquí mismo el setter nativo de `.value` en los inputs:
 * así, TODO input con la clase "input-label" se re-sincroniza solo, sin
 * importar desde qué archivo .js se le asigne el valor.
 */
(function interceptValueSetter() {
    const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    if (!descriptor || !descriptor.set) return; // seguridad por si el navegador no lo expone

    Object.defineProperty(HTMLInputElement.prototype, 'value', {
        get: descriptor.get,
        set: function (val) {
            descriptor.set.call(this, val);
            if (this.classList.contains('input-label')) {
                checkValue(this);
            }
        },
        configurable: true
    });
})();

// Se mantiene disponible por si algún panel quiere forzar una revisión manual
// (ej. tras mostrar/ocultar una sección completa), aunque ya no es obligatorio usarlo.
window.refreshFloatingLabels = function (scope) {
    const root = scope || document;
    root.querySelectorAll(".input-label").forEach(checkValue);
};