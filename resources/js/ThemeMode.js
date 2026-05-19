const temaClaro = {
    '--col1': '#ffffff',
    '--col2': '#fff9e8',
    '--col3': '#ffc400',
    '--col4': '#22a443',
    '--col5': '#3d9c55',
    '--col6': '#252525',
    '--col7': '#3e3e3e'
};

const temaOscuro = {
    '--col1': '#252525',
    '--col2': '#3e3e3e',
    '--col3': '#22a443',
    '--col4': '#ffc400',
    '--col5': '#3d9c55',
    '--col6': '#fff9e8',
    '--col7': '#ffffff'
};

function aplicarTema(tema) {
    const root = document.documentElement;

    Object.keys(tema).forEach(function(variable) {
        root.style.setProperty(variable, tema[variable]);
    });
}


function moverSwitch(estado) {
    document.querySelectorAll('.switch-theme').forEach(function(contenedor) {
        const boton = contenedor.querySelector('.switch-theme-div');

        if (estado === 'oscuro') {
            boton.style.transform = 'translateX(0%)';
        } else {
            boton.style.transform = 'translateX(100%)';
        }
    });
}

function cambiarLogo(estado) {
    document.querySelectorAll('.logo-theme').forEach(function(logo) {
        if (estado === 'oscuro') {
            logo.src = '/images/LogoTypeDark.svg';
        } else {
            logo.src = '/images/LogoTypeLight.svg';
        }
    });
}
function cambiarTema() {

    let temaActual = localStorage.getItem('tema') || 'claro';

    if (temaActual === 'claro') {

        temaActual = 'oscuro';
        aplicarTema(temaOscuro);

    } else {

        temaActual = 'claro';
        aplicarTema(temaClaro);

    }

    localStorage.setItem('tema', temaActual);

    moverSwitch(temaActual);
    cambiarLogo(temaActual);

    const animacion = document.getElementById('IndexAnimate');

    if (animacion) {

        if (temaActual === 'oscuro') {
            animacion.data = '/images/IndexAnimateDark.svg';
        } else {
            animacion.data = '/images/IndexAnimate.svg';
        }

    }

    
    

}

document.addEventListener('DOMContentLoaded', function () {

    const temaGuardado = localStorage.getItem('tema') || 'claro';

    // Aplicar colores
    if (temaGuardado === 'oscuro') {
        aplicarTema(temaOscuro);
    } else {
        aplicarTema(temaClaro);
    }

    // Mover switch
    moverSwitch(temaGuardado);

    // Cambiar logos
    cambiarLogo(temaGuardado);

    // Cambiar animación SVG
    const animacion = document.getElementById('IndexAnimate');

    if (animacion) {

        if (temaGuardado === 'oscuro') {
            animacion.data = '/images/IndexAnimateDark.svg';
        } else {
            animacion.data = '/images/IndexAnimate.svg';
        }

    }

    // Eventos click
    document.querySelectorAll('.switch-theme').forEach(function (contenedor) {
        contenedor.addEventListener('click', cambiarTema);
    });

});