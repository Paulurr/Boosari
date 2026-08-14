// Ubicación sugerida: resources/js/configuracion.js
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // =====================================================================
    // Switch: activar/desactivar el agente
    // =====================================================================
    const switchAgente = document.getElementById('switch-agente');
    const agenteMsg = document.getElementById('agente-msg');

    function pintarSwitchAgente(activo) {
        switchAgente.setAttribute('aria-checked', String(activo));
        switchAgente.classList.toggle('bgcol4', activo);
        switchAgente.classList.toggle('bgcol1', !activo);

        const bolita = switchAgente.querySelector('.switch-agente-div');
        bolita.classList.toggle('translate-x-7', activo);
        bolita.classList.toggle('translate-x-0', !activo);
    }

    switchAgente?.addEventListener('click', async () => {
        const activoActual = switchAgente.getAttribute('aria-checked') === 'true';
        const nuevoEstado = !activoActual;

        pintarSwitchAgente(nuevoEstado); // optimista

        try {
            const res = await fetch('/config/agente', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ agente_activo: nuevoEstado }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'No se pudo actualizar la preferencia.');

            agenteMsg.textContent = nuevoEstado ? 'Asistente activado.' : 'Asistente desactivado.';
        } catch (err) {
            pintarSwitchAgente(activoActual); // revertir
            agenteMsg.textContent = ` ${err.message}`;
        }
    });

    // =====================================================================
    // Tabs: modo claro / modo oscuro
    // =====================================================================
    const tabBtns = document.querySelectorAll('.tab-colores-btn');
    const formClaro = document.getElementById('form-colores-claro');
    const formOscuro = document.getElementById('form-colores-oscuro');

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const modo = btn.dataset.tabColores;

            tabBtns.forEach((b) => {
                const activo = b === btn;
                b.classList.toggle('bgcol4', activo);
                b.classList.toggle('col1', activo);
                b.classList.toggle('bgcol1', !activo);
                b.classList.toggle('col7', !activo);
            });

            formClaro.classList.toggle('hidden', modo !== 'claro');
            formOscuro.classList.toggle('hidden', modo !== 'oscuro');
        });
    });

    // =====================================================================
    // Paleta de colores (funciona igual para el form de claro y el de oscuro)
    // =====================================================================
    function temaActivo() {
        return localStorage.getItem('tema') || 'claro';
    }

    function actualizarEtiquetaHex(form, input) {
        const etiqueta = form.querySelector(`[data-hex-for="${input.id}"]`);
        if (etiqueta) etiqueta.textContent = input.value.toUpperCase();
    }

    function previsualizarColor(modoForm, nombreCampo, valorHex) {
        // Solo previsualiza en vivo si el modo del formulario coincide
        // con el tema que se está viendo ahora mismo (claro/oscuro).
        if (modoForm !== temaActivo()) return;
        const indice = nombreCampo.replace('color_', '').replace('_oscuro', '');
        document.documentElement.style.setProperty(`--col${indice}`, valorHex);
    }

    document.querySelectorAll('.form-colores').forEach((form) => {
        const modo = form.dataset.modo;
        const msg = form.querySelector('.colores-msg');

        form.querySelectorAll('input[type="color"]').forEach((input) => {
            input.addEventListener('input', () => {
                actualizarEtiquetaHex(form, input);
                previsualizarColor(modo, input.name, input.value);
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const datos = Object.fromEntries(new FormData(form).entries());

            try {
                const res = await fetch('/config/colores', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(datos),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(json.message || 'No se pudo guardar la paleta.');

                msg.textContent = ' Paleta guardada.';
            } catch (err) {
                msg.textContent = ` ${err.message}`;
            }
        });

        form.querySelector('.btn-restaurar-colores')?.addEventListener('click', async () => {
            if (!confirm('¿Restaurar los colores base de este modo? Se perderá tu personalización.')) return;

            try {
                const res = await fetch(`/config/colores?modo=${modo}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(json.message || 'No se pudo restaurar la paleta.');

                Object.entries(json.colores).forEach(([indice, hex]) => {
                    const input = document.getElementById(`color_${indice}_${modo}`);
                    if (input) {
                        input.value = hex;
                        actualizarEtiquetaHex(form, input);
                        previsualizarColor(modo, input.name, hex);
                    }
                });

                msg.textContent = ' Colores restaurados.';
            } catch (err) {
                msg.textContent = ` ${err.message}`;
            }
        });
    });
});