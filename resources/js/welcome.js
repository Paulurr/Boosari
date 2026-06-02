document.addEventListener('DOMContentLoaded', function() {
window.addEventListener("scroll", () => {
    const scroll = window.scrollY;
    const elemento = document.querySelector(".parallax");

    elemento.style.transform = `translateY(${scroll * -0.6}px)`;



    });
    const WelcomeScrollDivCont = document.getElementById("welcome-scroll-div-cont");
        const WelcomeScrollDivAnimate = document.getElementById("welcome-scroll-div-animate");

        window.addEventListener("scroll", () => {
            const rect = WelcomeScrollDivCont.getBoundingClientRect();

            const windowHeight = window.innerHeight;
            const totalScroll = rect.height - windowHeight;
            const container = WelcomeScrollDivCont;
            const texto = document.getElementById("welcome-textoScroll");

            window.addEventListener("scroll", () => {
                const rect = container.getBoundingClientRect();

                const offset = 210; 
                
                const move = Math.min(0, rect.top - offset);

                texto.style.transform = `translate3d(0, ${move}px, 0)`;
            });

            // cuánto se ha recorrido dentro del div
            let progress = -rect.top / totalScroll;

            // limitar entre 0 y 1
            progress = Math.max(0, Math.min(1, progress));

            // invertir (1 → 0)
            const translateX = progress * 100;

            WelcomeScrollDivAnimate.style.transform = `translateX(${translateX}%)`;
        }
        
    );
});