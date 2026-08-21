class Select {
    constructor(name) {
        if (!document.getElementById(name + "-select")) {
            return;
        }        
        let select_btn = document.getElementById(name + "-select");
        let select_tit = document.getElementById(name + "-select-tit");
        let select_value = document.getElementById(name + "-select-value");
        let select_list = document.getElementById(name + "-select-list");
        let select_option = document.querySelectorAll("." + name + "-option");
        
        let error_msg = document.getElementById(name + "-error-msg");
        let select_container = select_btn.parentElement; // El contenedor con borde

        let state = false;
        let animate = false;

        select_list.style.transition = "opacity 0.2s ease";
        select_list.style.opacity = "0";

        select_btn.addEventListener("click", (e) => {
            e.stopPropagation();
            open_close_list();
        });

        select_option.forEach((e) => {
            e.addEventListener("click", (evt) => {
                evt.stopPropagation(); // Evitamos efectos colaterales en clicks anidados
                open_close_list();

                let texto = e.textContent.trim();
                let valor = e.getAttribute('value') || e.dataset.value || texto;

                select_tit.textContent = texto;

                if (valor === "Ninguno" || valor === "") {
                    select_value.value = "";
                } else {
                    select_value.value = valor;
                }

                if (select_value.value !== "" && select_value.value !== "Ninguno") {
                    if (error_msg) error_msg.classList.add("hidden");
                    if (select_container) select_container.classList.remove("border-red-500");
                }

                select_value.dispatchEvent(new Event("input"));
            });
        });

        document.addEventListener("keydown", (e) => {
            if (e.key == "Escape") {
                if (animate == false && state == true) {
                    open_close_list();
                }
            }
        });

        // Click afuera cierra la lista de forma segura
        document.addEventListener("click", () => {
            if (animate == false && state == true) {
                open_close_list();
            }
        });

        function open_close_list() {
            if (animate == true) {
                return;
            }
            if (!state) {
                animate = true;
                select_list.style.display = "block";
                setTimeout(() => {
                    select_list.style.opacity = "1";
                }, 100);
                setTimeout(() => {
                    state = true;
                    animate = false;
                }, 300);
            } else {
                animate = true;
                select_list.style.opacity = "0";
                setTimeout(() => {                        
                    select_list.style.display = "";
                    state = false;
                    animate = false;
                }, 200);
            }
        }
    }
}

// Tus instanciaciones se quedan exactamente igual:
let add = new Select("add");
let income = new Select("income");
let income_wallet = new Select("income-wallet");
let income_frecuencia = new Select("income-frecuencia"); // <-- panel info-income (modo edición)
let wallet = new Select("wallet");
let investment_wallet = new Select("investment-wallet");
let investment_renta = new Select("investment-renta");
let transaction = new Select("transaction");
let transaction_origen = new Select("transaction-origen");
let transaction_destino_ingreso = new Select("transaction-destino-ingreso");
let transaction_destino_gasto = new Select("transaction-destino-gasto");
let transaction_destino = new Select("transaction-destino");
let paygoal_wallet = new Select("paygoal-wallet");
let paygoal_target = new Select("paygoal-target");
let debt_prioridad = new Select("debt-prioridad");
let payment_wallet = new Select("payment-wallet");
let payment_target = new Select("payment-target");
// Selects propios de los paneles info-goal / info-debt (modo edición).
// Usan nombres distintos a los del panel "add" para no duplicar IDs en el DOM.
let goalinfo_payment_wallet = new Select("goalinfo-payment-wallet");
let debtinfo_prioridad = new Select("debtinfo-prioridad");
let debtinfo_payment_wallet = new Select("debtinfo-payment-wallet");
// Selects propios de los paneles info-paymentGoal / info-paymentDebt (edición de un abono/pago puntual).
let paymentGoalinfo_wallet = new Select("paymentGoalinfo-wallet");
let paymentDebtinfo_wallet = new Select("paymentDebtinfo-wallet");
let investmentinfo_estado = new Select("investmentinfo-estado");

