window.addEventListener("DOMContentLoaded",()=>{
    var nav_menu_button = document.getElementById("nav-menu-button");
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
})