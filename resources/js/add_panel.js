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

let transaction_select_transaccion = document.getElementById("transaction-select-transaccion");
let transaction_select_value = document.getElementById("transaction-select-value");

wallet_form.style.display = "none";
income_form.style.display = "none";
investment_form.style.display = "none";
transaction_form.style.display = "none";
goal_form.style.display = "none";
paymentgoal_form.style.display = "none";
debt_form.style.display = "none";
paymentdebt_form.style.display = "none";

select.addEventListener("input",()=>{
    let select_value = select.value;
    default_.style.display = "none";
    wallet_form.style.display = "none";
    income_form.style.display = "none";
    investment_form.style.display = "none";
    transaction_form.style.display = "none";
    goal_form.style.display = "none";
    paymentgoal_form.style.display = "none";
    debt_form.style.display = "none";
    paymentdebt_form.style.display = "none";

    
    switch (select_value) {
        case "Cuenta":
            wallet_form.style.display = "flex";    
            break;
        case "Salario":
            income_form.style.display = "flex";    
            
            break;
        case "Inversión":
            investment_form.style.display = "flex";    

            break;
        case "Movimiento":
            transaction_form.style.display = "flex";    
            transaction_select_value.addEventListener("input",()=>{
                let transaction_value = transaction_select_value.value;
                if(transaction_value == "Transferencia"){
                    transaction_select_transaccion.style.display = "flex"; 
                }else{
                    transaction_select_transaccion.style.display = "none"; 

                }
            })

            
            break;
        case "Meta":
            goal_form.style.display = "flex";    
            
            break;
        case "Pago de Meta":
            paymentgoal_form.style.display = "flex";    
            
            break;
        case "Deuda":
            debt_form.style.display = "flex";    
            break;
        case "Pago de Deuda":
            paymentdebt_form.style.display = "flex";    
            
            break;
    
        default:
            default_.style.display = "flex";
            break;
    }
})

let add_submit = document.getElementById("add-submit"); 
add_submit.addEventListener("click",()=>{
    let select_value = select.value;
    switch (select_value) {
        case "Cuenta":
            wallet_form.requestSubmit();    

            break;
        case "Salario":
            income_form.requestSubmit();    
            
            break;
        case "Inversión":
            investment_form.requestSubmit();    

            break;
        case "Movimiento":
            transaction_form.requestSubmit();    
            
            break;
        case "Meta":
            goal_form.requestSubmit();    
            
            break;
        case "Pago de Meta":
            paymentgoal_form.requestSubmit();    
            
            break;
        case "Deuda":
            debt_form.requestSubmit();    
            break;
        case "Pago de Deuda":
            paymentdebt_form.requestSubmit();    
            
            break;
    
        default:
            break;
    }
})

document.addEventListener("DOMContentLoaded", () => {
    // 1. Obtener la fecha actual en formato local YYYY-MM-DD
    const hoy = new Date();
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    const fechaFormateada = `${anio}-${mes}-${dia}`;

    // 2. Definir los límites de seguridad
    const fechaMinima = "1970-01-01";
    const fechaMaxima = `${anio + 10}-${mes}-${dia}`;

    // 3. Buscar TODOS los inputs de tipo date
    const inputsFecha = document.querySelectorAll('input[type="date"]');

    inputsFecha.forEach(input => {
        // Aplicar restricciones nativas para el calendario
        input.min = fechaMinima;
        input.max = fechaMaxima;
        input.value = fechaFormateada; // Valor por defecto hoy

        // 4. CORRECCIÓN PARA ESCRITURA MANUAL:
        // Escuchamos cuando el usuario termina de escribir o cambia el foco
        input.addEventListener("blur", () => {
            const fechaIntroducida = input.value;

            // Si el usuario escribió una fecha incompleta o un año como 0001
            if (!fechaIntroducida || fechaIntroducida < fechaMinima || fechaIntroducida > fechaMaxima) {
                input.value = fechaFormateada; // Forzamos a regresar al día de hoy
            }
        });
    });
});