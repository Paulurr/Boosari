class Select{
    constructor(name){
        if(!document.getElementById(name+"-select")){
            return;
        }        
        let select_btn = document.getElementById(name+"-select");
        let select_tit = document.getElementById(name+"-select-tit");
        let select_value = document.getElementById(name+"-select-value");
        let select_list = document.getElementById(name+"-select-list");
        let select_option = document.querySelectorAll("."+name+"-option");
        let state = false;
        let animate = false;
        select_list.style.transition = "opacity 0.2s ease";
        select_list.style.opacity = "0";
        select_btn.addEventListener("click",open_close_list);
        select_option.forEach((e) => {
            e.addEventListener("click", () => {

                open_close_list();

                select_tit.textContent = e.textContent.trim();

                select_value.value = e.dataset.value || e.textContent.trim();

                select_value.dispatchEvent(new Event("input"));
            });
        });
        document.addEventListener("keydown",(e)=>{
            if(e.key == "Escape"){
                if(animate == false && state == true){
                    open_close_list();
                }else{
                    return;
                }
            }
        })
        document.addEventListener("click",(e)=>{
            if(animate == false && state == true){
                open_close_list();
            }else{
                return;
            }
        
        })
        function open_close_list(){
            if(animate == true){
                return;
            }
            if(!state){
                animate =true;
                select_list.style.display = "block";
                setTimeout(() => {
                    select_list.style.opacity = "1";
                }, 100);
                setTimeout(() => {
                    state = true;
                    animate =false;
                }, 300);
            }else{
                animate =true;
                select_list.style.opacity = "0";
                setTimeout(() => {                        
                    select_list.style.display = "";
                    state = false;
                    animate =false;
                }, 200);

            }
        }
    }
}
let add = new Select("add");
let income = new Select("income");
let income_wallet = new Select("income-wallet");
let wallet = new Select("wallet");
let transaction = new Select("transaction");
let transaction_origen = new Select("transaction-origen");
let transaction_destino = new Select("transaction-destino");
