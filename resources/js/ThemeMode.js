const temaClaro = {
    '--col1': '#ffffff',
    '--col2': '#fff9e8',
    '--col3': '#ffc400',
    '--col4': '#22a443',
    '--col5': '#f3e38b',
    '--col6': '#252525',
    '--col7': '#3e3e3e'
};

const temaOscuro = {
    '--col1': '#252525',
    '--col2': '#3e3e3e',
    '--col3': '#22a443',
    '--col4': '#ffc400',
    '--col5': '#23803B',
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

/*
|--------------------------------------------------------------------------
| OBTENER IDIOMA
|--------------------------------------------------------------------------
*/

function obtenerIdioma() {

    return document.documentElement.lang || 'es';

}

/*
|--------------------------------------------------------------------------
| CAMBIAR SVG SEGÚN TEMA + IDIOMA
|--------------------------------------------------------------------------
*/

function cambiarAnimacion(tema) {

    const idioma = obtenerIdioma();

    const animacion = document.getElementById('IndexAnimate');

    if (!animacion) return;

    let archivo = 'IndexAnimate';

    // Tema oscuro
    if (tema === 'oscuro') {
        archivo += 'Dark';
    }

    // Inglés
    if (idioma === 'en') {
        archivo += 'En';
    }

    archivo += '.svg';

    animacion.data = `/images/${archivo}`;

}

/*
|--------------------------------------------------------------------------
| CAMBIAR TEMA
|--------------------------------------------------------------------------
*/

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

    cambiarAnimacion(temaActual);

}

/*
|--------------------------------------------------------------------------
| CARGA INICIAL
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    const temaGuardado = localStorage.getItem('tema') || 'claro';

    if (temaGuardado === 'oscuro') {
        aplicarTema(temaOscuro);
    } else {
        aplicarTema(temaClaro);
    }

    moverSwitch(temaGuardado);

    cambiarLogo(temaGuardado);

    cambiarAnimacion(temaGuardado);

    document.querySelectorAll('.switch-theme').forEach(function (contenedor) {

        contenedor.addEventListener('click', cambiarTema);

    });

});