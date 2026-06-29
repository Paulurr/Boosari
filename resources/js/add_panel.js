let select = document.getElementById("add-select-value");
let default_ = document.getElementById("-default");
let wallet_form = document.getElementById("wallet-form");
let income_form = document.getElementById("income-form");
let investment_form = document.getElementById("investment-form");
let transaction_form = document.getElementById("transaction-form");
let goal_form = document.getElementById("goal-form");
let paymentgoal_form = document.getElementById("paymentgoal-form");
let debt_form = document.getElementById("debt-form");
let paymentdebt_form = document.getElementById("paymentdebt-form");

// Ocultar todos los formularios al inicio
ocultarFormularios();

document.addEventListener("DOMContentLoaded", () => {
    let tipoMovimientoSelect = document.getElementById("transaction-select-value"); // Tu input oculto del tipo
    let bloques = document.querySelectorAll(".grupo-movimiento");

    function alternarBloquesMovimiento() {
        if (!tipoMovimientoSelect) return;
        let valorActual = tipoMovimientoSelect.value.trim().toLowerCase();

        // 1. Ocultamos todos los bloques y desactivamos sus inputs para que no viajen al servidor
        bloques.forEach(bloque => {
            bloque.classList.add("hidden");
            bloque.style.display = "none";
            
            // Buscamos los inputs generados por tus x-select dentro de este bloque específico
            let inputsInternos = bloque.querySelectorAll("input");
            inputsInternos.forEach(input => input.setAttribute("disabled", "true"));
        });

        // 2. Activamos y mostramos únicamente el bloque seleccionado
        let bloqueActivo = document.getElementById(`bloque-${valorActual}`);
        if (bloqueActivo) {
            bloqueActivo.classList.remove("hidden");
            bloqueActivo.style.display = "flex";

            // Volvemos a habilitar los inputs de este bloque para que sí viajen en el request
            let inputsActivos = bloqueActivo.querySelectorAll("input");
            inputsActivos.forEach(input => input.removeAttribute("disabled"));
        }
    }

    // Registrar eventos para capturar el cambio inmediatamente
    if (tipoMovimientoSelect) {
        tipoMovimientoSelect.addEventListener("change", alternarBloquesMovimiento);
        tipoMovimientoSelect.addEventListener("input", alternarBloquesMovimiento);
    }

    // Inicialización por defecto al cargar la página
    alternarBloquesMovimiento();
});

function ocultarFormularios() {
    if(wallet_form) wallet_form.style.display = "none";
    if(income_form) income_form.style.display = "none";
    if(investment_form) investment_form.style.display = "none";
    if(transaction_form) transaction_form.style.display = "none";
    if(goal_form) goal_form.style.display = "none";
    if(paymentgoal_form) paymentgoal_form.style.display = "none";
    if(debt_form) debt_form.style.display = "none";
    if(paymentdebt_form) paymentdebt_form.style.display = "none";
}

function actualizarFormularioActivo() {
    let select_value = select.value;
    if(default_) default_.style.display = "none";
    ocultarFormularios();

    switch (select_value) {
        case "Billetera":     if(wallet_form) wallet_form.style.display = "flex"; break;
        case "Salario":       if(income_form) income_form.style.display = "flex"; break;
        case "Inversión":     if(investment_form) investment_form.style.display = "flex"; break;
        case "Movimiento":    if(transaction_form) transaction_form.style.display = "flex"; break;
        case "Meta":          if(goal_form) goal_form.style.display = "flex"; break;
        case "Pago de Meta":  if(paymentgoal_form) paymentgoal_form.style.display = "flex"; break;
        case "Deuda":         if(debt_form) debt_form.style.display = "flex"; break;
        case "Pago de Deuda": if(paymentdebt_form) paymentdebt_form.style.display = "flex"; break;
        default:              if(default_) default_.style.display = "flex"; break;
    }
}

if (select) {
    select.addEventListener("input", actualizarFormularioActivo);
    select.addEventListener("change", actualizarFormularioActivo);
}

// Lógica de validación genérica (Mantiene tu scroll y validación de bordes rojos)
function validarFormularioActivo(formulario) {
    if (!formulario) return true;
    let errorDetectado = false;
    let inputsRequeridos = formulario.querySelectorAll('.generic-x-select-input[data-required="true"]');

    inputsRequeridos.forEach(inputOculto => {
        let namePrefix = inputOculto.getAttribute('data-name-prefix');
        let contenedorError = document.getElementById(`${namePrefix}-error-msg`);
        let selectElement = document.getElementById(`${namePrefix}-select`);
        
        if (selectElement) {
            let cajaSelect = selectElement.parentElement;
            let valor = inputOculto.value.trim();

            if (!valor || valor === '' || valor === 'Ninguno') {
                errorDetectado = true;
                if (contenedorError) contenedorError.classList.remove('hidden');
                if (cajaSelect) cajaSelect.classList.add('border-red-500');
            } else {
                if (contenedorError) contenedorError.classList.add('hidden');
                if (cajaSelect) cajaSelect.classList.remove('border-red-500');
            }
        }
    });

    if (errorDetectado) {
        let primerError = formulario.querySelector('.border-red-500');
        if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return !errorDetectado;
}

let add_submit = document.getElementById("add-submit"); 
if (add_submit) {
    add_submit.addEventListener("click", () => {
        if (!select) return;
        let select_value = select.value;
        let formularioDestino = null;

        switch (select_value) {
            case "Billetera":     formularioDestino = wallet_form; break;
            case "Salario":       formularioDestino = income_form; break;
            case "Inversión":     formularioDestino = investment_form; break;
            case "Movimiento":    formularioDestino = transaction_form; break;
            case "Meta":          formularioDestino = goal_form; break;
            case "Pago de Meta":  formularioDestino = paymentgoal_form; break;
            case "Deuda":         formularioDestino = debt_form; break;
            case "Pago de Deuda": formularioDestino = paymentdebt_form; break;
        }

        if (formularioDestino && validarFormularioActivo(formularioDestino)) {
            formularioDestino.requestSubmit();
        }
    });
}

// Configuración inicial de fechas
document.addEventListener("DOMContentLoaded", () => {
    const hoy = new Date();
    const fechaFormateada = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
    const inputsFecha = document.querySelectorAll('input[type="date"]');

    inputsFecha.forEach(input => {
        input.min = "1970-01-01";
        input.max = `${hoy.getFullYear() + 10}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
        input.value = fechaFormateada;

        input.addEventListener("blur", () => {
            if (!input.value || input.value < input.min || input.value > input.max) {
                input.value = fechaFormateada;
            }
        });
    });
    
    if(select && select.value !== "") actualizarFormularioActivo();
});

document.addEventListener("DOMContentLoaded", function () {
    const inputRenta = document.getElementById("investment-renta-select-value");
    const wrapperTasa = document.getElementById("wrapper-tasa-interes");
    const formInversion = document.getElementById("investment-form");

    function evaluarTipoRenta() {
        if (!inputRenta || !wrapperTasa) return;
        
        const inputTasaReal = wrapperTasa.querySelector("input");
        const valor = inputRenta.value.trim().toLowerCase();

        if (valor === "fija") {
            // Quitamos hidden y aplicamos flex para mantener el comportamiento de alineación
            wrapperTasa.classList.remove("hidden");
            wrapperTasa.classList.add("flex");
            if (inputTasaReal) inputTasaReal.setAttribute("required", "true");
        } else {
            // Ocultamos el bloque por completo
            wrapperTasa.classList.add("hidden");
            wrapperTasa.classList.remove("flex");
            if (inputTasaReal) {
                inputTasaReal.removeAttribute("required");
                inputTasaReal.value = ""; // Reseteo preventivo
            }
        }
    }

    // 1. Monitorear cambios simulados o reales en el componente personalizado
    if (inputRenta) {
        // Tu componente altera el .value mediante JS, usamos un MutationObserver o intervals 
        // si el evento 'change' nativo no se dispara en elementos ocultos.
        inputRenta.addEventListener("change", evaluarTipoRenta);
        inputRenta.addEventListener("input", evaluarTipoRenta);
        
        // Detectar cambios directos en el valor si tu select.js los escribe mediante asignación directa
        const observer = new MutationObserver(() => evaluarTipoRenta());
        observer.observe(inputRenta, { attributes: true, attributeFilter: ['value'] });
    }

    // 2. Control total en el Submit para interceptar campos requeridos ocultos
    if (formInversion) {
        formInversion.addEventListener("submit", function (e) {
            const valorRenta = inputRenta ? inputRenta.value.trim().toLowerCase() : '';
            const inputTasaReal = wrapperTasa ? wrapperTasa.querySelector("input") : null;

            // Aseguramos que si no es fija, el required se destruya un milisegundo antes de enviar
            if (valorRenta !== "fija" && inputTasaReal) {
                inputTasaReal.removeAttribute("required");
            }
        });
    }

    // Ejecución inicial por si el formulario se renderiza con datos previos
    evaluarTipoRenta();
});
