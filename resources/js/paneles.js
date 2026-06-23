class Panel{
    constructor(name){
        if(!document.getElementById(name+"-cont")){
            return;
        }        
        let cont = document.getElementById(name+"-cont");
        let close = document.querySelectorAll("."+name+"-close");
        let btn = document.querySelectorAll("."+name+"-btn");
        let state = false ;
        let animate = false;
        cont.style.opacity = "0";
        cont.style.transition = "opacity 0.2s ease";

        btn.forEach(e => {
            e.addEventListener("click",open_edit_panel);
        });
        close.forEach(e => {
            e.addEventListener("click",close_edit_panel);
        });
        document.addEventListener("keydown",(e)=>{
            if(e.key == "Escape"){
                if(animate == false && state == true){
                    close_edit_panel();
                }else{
                    return;
                }
            }
        })
                
        function open_edit_panel(){
            if(state ==  false){
                if (animate == true){
                    return;
                }
                animate = true;
                cont.style.display ="flex";
                setTimeout(() => {
                    cont.style.opacity = "1";
                }, 100);
                setTimeout(() => {
                    state = true;
                    animate = false;
                }, 200);
            }else{
                return;
            }
        };
        function close_edit_panel(){
            if(state ==  false){
                return;
            }else{
                if (animate == true){
                    return;
                }
                animate =true;
                cont.style.opacity = "0";
                setTimeout(() => {
                    cont.style.display ="none";
                    state = false;
                    animate = false;
                }, 200);
                
            }
        };        
    }
}
let edit_panel = new Panel("editar");
let add_panel = new Panel("add");
let filter_panel = new Panel("filter");