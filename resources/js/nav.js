window.addEventListener("DOMContentLoaded",()=>{
    
    var nav_menu_button = document.getElementById("nav-menu-button");
    var nav_menu_button_div1 = document.getElementById("nav-menu-button-div1");
    var nav_menu_button_div2 = document.getElementById("nav-menu-button-div2");
    nav_menu_button_div1.style.backgroundColor ="var(--col3)";
    nav_menu_button_div2.style.backgroundColor ="var(--col7)";
    var nav_menu = document.getElementById("nav-menu");
    var nav_menu_back = document.getElementById("nav-menu-back");
    var path1= document.getElementById("path-nav1");
    var path2= document.getElementById("path-nav2");
    var path3= document.getElementById("path-nav3");
    var open_menu = false;
    var animation_end = true;

    nav_menu_button.addEventListener("click",open_close_menu);
    nav_menu_back.addEventListener("click",open_close_menu);
    function open_close_menu(){    
        if(!animation_end) return;
        animation_end = false;

        if ( open_menu == false ){
            open_menu=true;
            if (typeof open_perfil_menu !== "undefined" && open_perfil_menu) {
                open_perfil_menu = false;
                perfil_menu_nav.style.transform = "scale(0)";
            }
            nav_menu_button_div1.style.backgroundColor ="var(--col4)";
            nav_menu_button_div2.style.backgroundColor ="var(--col2)";
            path1.style.transform ="rotate(-45deg) scaleX(0.7)";
            path2.style.transform ="translateX(5px)";
            path3.style.transform ="rotate(45deg) scaleX(0.7)";    
            path1.style.stroke ="var(--col2)";
            path2.style.stroke ="var(--col2)";
            path3.style.stroke ="var(--col2)";    
            nav_menu.style.transform ="translate(100%)";        
            nav_menu_back.style.transform ="translate(100%)";        
        }else{
            open_menu=false;
            nav_menu_button_div1.style.backgroundColor ="var(--col3)";
            nav_menu_button_div2.style.backgroundColor ="var(--col7)";
            path1.style.transform ="";
            path2.style.transform ="";
            path3.style.transform ="";
            path1.style.stroke ="";
            path2.style.stroke ="";
            path3.style.stroke ="";  
            nav_menu.style.transform ="";
            nav_menu_back.style.transform ="";
        }
        

        setTimeout(() => {
            animation_end = true;
        }, 400);
    }
    
    if(document.getElementById("perfil-nav")){            
        var perfil_nav = document.getElementById("perfil-nav");
        var perfil_menu_nav = document.getElementById("perfil-menu-nav");
        var open_perfil_menu = false;
        var animation_end_perfil_menu = true;

        perfil_nav.addEventListener("click",open_close_perfil_menu);
        document.addEventListener("keydown",(e)=>{
            if(e.key == "Escape"){
                if(open_perfil_menu == true && animation_end_perfil_menu == true){
                    open_close_perfil_menu();
                }else{
                    return;
                }
            }
        })
        document.addEventListener("click", function(e) {
            if (open_perfil_menu) {
                if (
                    !perfil_menu_nav.contains(e.target) &&
                    !perfil_nav.contains(e.target)
                ) {
                    open_perfil_menu = false;
                    perfil_menu_nav.style.transform = "scale(0)";
                }
            }
        });
        function open_close_perfil_menu(){
            if(!animation_end_perfil_menu) return;
            animation_end_perfil_menu = false;

            if ( open_perfil_menu == false ){
                open_perfil_menu=true;
                perfil_menu_nav.style.transform="Scale(1)";
            }else{
                open_perfil_menu=false;
                perfil_menu_nav.style.transform="Scale(0)";

            }
            

            setTimeout(() => {
                animation_end_perfil_menu = true;
            }, 200);
        }
    }
})