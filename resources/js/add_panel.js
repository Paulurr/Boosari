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

// NOTA: el envío real de cada formulario ya NO se dispara aquí con
// requestSubmit() (eso recargaba la página entera y perdíamos la
// oportunidad de pintar los errores de validación junto a cada campo).
// El botón "Aceptar" del panel "add" ya dispara Panel._trySubmit(), que
// llama a window.add_panel.onSubmit (definido más abajo), así que todo el
// envío pasa por fetch()/AJAX de forma genérica para los 8 formularios.

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
/**
 * Manejo genérico de errores por input (data-error-for)
 * ---------------------------------------------------------------------
 * Cada formulario del panel "add" (wallet, income, investment,
 * transaction, goal, paymentgoal, debt, paymentdebt) tiene, debajo de
 * cada campo, un <span class="error-msg" data-error-for="nombre-del-campo">.
 * El backend (RecordController) responde en JSON, con HTTP 422 y un
 * objeto "errors" con la forma { "nombre-del-campo": ["mensaje", ...] }
 * cuando la petición trae "Accept: application/json" (igual que hacía
 * antes investment-form). Esta función genérica:
 *
 *   1. Previene el submit nativo del formulario (evita recargar la
 *      página y perder el panel abierto).
 *   2. Envía el formulario por fetch/AJAX.
 *   3. Si el servidor responde con errores, los coloca en el span
 *      correspondiente según data-error-for; si el campo no tiene un
 *      span asociado (o es un error de negocio sin campo específico,
 *      ej. "Fondos insuficientes"), lo muestra en el bloque de error
 *      general del formulario.
 *   4. Si todo sale bien, resetea el formulario y recarga la página
 *      (igual que hacía antes investment-form).
 *
 * Devuelve la función "onSubmit" que Panel espera (debe resolver a
 * true/false), o null si el formulario no existe en el DOM.
 */
function crearManejadorAjax(form, generalErrorId, generalMsgId) {
    if (!form) return null;

    form.addEventListener("submit", (e) => e.preventDefault());

    return async function () {
        const formData = new FormData(form);

        // 1. Limpiar todos los mensajes de error inline previos
        form.querySelectorAll(".error-msg").forEach(span => span.textContent = "");

        const generalErrorCont = document.getElementById(generalErrorId);
        const generalErrorMsg = document.getElementById(generalMsgId);
        if (generalErrorCont) generalErrorCont.classList.add("hidden");

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                // Si proviene de fallos de validación (HTTP 422) u otro error controlado
                if (data.errors) {
                    Object.keys(data.errors).forEach(fieldKey => {
                        const valorError = data.errors[fieldKey];
                        const errorText = Array.isArray(valorError) ? valorError[0] : valorError;

                        // Buscar el span asignado al campo que falló
                        const targetSpan = form.querySelector(`[data-error-for="${fieldKey}"]`);
                        if (targetSpan) {
                            targetSpan.textContent = errorText;
                        } else if (generalErrorCont && generalErrorMsg) {
                            // Error general o sin campo específico asociado (ej. "Fondos insuficientes")
                            generalErrorMsg.textContent = errorText;
                            generalErrorCont.classList.remove("hidden");
                        }
                    });
                } else if (data.message && generalErrorCont && generalErrorMsg) {
                    generalErrorMsg.textContent = data.message;
                    generalErrorCont.classList.remove("hidden");
                } else if (generalErrorCont && generalErrorMsg) {
                    generalErrorMsg.textContent = "Ocurrió un error al procesar la solicitud.";
                    generalErrorCont.classList.remove("hidden");
                }

                // Previene que Panel.js cierre el modal
                return false;
            }

            // Guardado exitoso
            form.reset();
            window.location.reload();
            return true;

        } catch (error) {
            if (generalErrorCont && generalErrorMsg) {
                generalErrorMsg.textContent = "Error de conexión. Inténtelo de nuevo.";
                generalErrorCont.classList.remove("hidden");
            }
            return false;
        }
    };
}

document.addEventListener("DOMContentLoaded", () => {
    // Un manejador AJAX por cada formulario del panel "add". Si algún
    // formulario no existe en el DOM (ej. la sección @if no se renderizó),
    // crearManejadorAjax devuelve null y simplemente se ignora esa entrada.
    const manejadoresPorTipo = {
        "Billetera":     crearManejadorAjax(wallet_form, "wallet-general-error", "wallet-general-msg"),
        "Salario":       crearManejadorAjax(income_form, "income-general-error", "income-general-msg"),
        "Inversión":     crearManejadorAjax(investment_form, "investment-general-error", "investment-general-msg"),
        "Movimiento":    crearManejadorAjax(transaction_form, "transaction-general-error", "transaction-general-msg"),
        "Meta":          crearManejadorAjax(goal_form, "goal-general-error", "goal-general-msg"),
        "Pago de Meta":  crearManejadorAjax(paymentgoal_form, "paymentgoal-general-error", "paymentgoal-general-msg"),
        "Deuda":         crearManejadorAjax(debt_form, "debt-general-error", "debt-general-msg"),
        "Pago de Deuda": crearManejadorAjax(paymentdebt_form, "paymentdebt-general-error", "paymentdebt-general-msg"),
    };

    if (window.add_panel) {
        window.add_panel.onSubmit = async () => {
            if (!select) return false;

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

            // Validación local previa (campos x-select requeridos, ej. tipo de billetera)
            if (!formularioDestino || !validarFormularioActivo(formularioDestino)) {
                return false;
            }

            const manejador = manejadoresPorTipo[select_value];
            if (!manejador) return false;

            return await manejador();
        };
    }
});