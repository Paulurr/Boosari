/**
 * record_card_sync.js
 *
 * Sincroniza en vivo las tarjetas del listado "Registros" (home) cuando un
 * panel de edición (info-wallet, info-investment, etc.) guarda cambios con
 * éxito, sin necesidad de recargar la página.
 *
 * Este script NO sabe nada de wallets, inversiones, etc. Es genérico:
 * solo escucha un evento y mueve texto/atributos a los elementos que
 * coincidan por id. Cada panel específico (info_investment.js, info_wallet.js...)
 * es responsable de calcular los textos ya formateados y disparar el evento.
 *
 * ---------------------------------------------------------------------
 * CONTRATO DEL EVENTO
 * ---------------------------------------------------------------------
 * window.dispatchEvent(new CustomEvent('record:updated', {
 *     detail: {
 *         name: 'info-investment',   // el mismo "name" que usa Panel / x-col-home
 *         id: 42,                    // id del registro editado
 *         fields: {                  // todos los campos son OPCIONALES;
 *             titulo:    'Nuevo nombre',
 *             icono:     'https://.../storage/investments/xxx.png', // URL completa
 *             monto:     '1,234.56',                 // ya formateado, SIN '$'
 *             origen:    'Invertido: $1,234.56',      // texto completo de la línea
 *             destino:   'Ganancia: +$120.00 | Vence: 12/03/2027',
 *             categoria: 'Categoria: Tecnología',
 *             fecha:     '09/08/2026'
 *         }
 *     }
 * }));
 *
 * Cualquier campo omitido (undefined) simplemente no se toca en la tarjeta.
 * Si la tarjeta no está en el DOM actual (quedó fuera por paginación o
 * filtros), el evento no hace nada — no hay que comprobarlo antes de
 * dispararlo.
 */
(function () {
    function flash(card) {
        const prevTransition = card.style.transition;
        const prevBg = card.style.backgroundColor;

        card.style.transition = 'background-color 0.3s ease';
        card.style.backgroundColor = 'rgba(34, 197, 94, 0.15)';

        setTimeout(() => {
            card.style.backgroundColor = prevBg;
            setTimeout(() => {
                card.style.transition = prevTransition;
            }, 300);
        }, 600);
    }

    function setTextIfPresent(prefix, key, value) {
        if (value === undefined) return;
        const el = document.getElementById(`${prefix}-${key}`);
        if (!el) return;
        el.textContent = value;
    }

    function setOptionalLine(prefix, key, value) {
        if (value === undefined) return;
        const el = document.getElementById(`${prefix}-${key}`);
        if (!el) return;
        el.textContent = value;
        el.classList.toggle('hidden', !value);
    }

    window.addEventListener('record:updated', (e) => {
        const { name, id, fields } = e.detail || {};
        if (!name || !id || !fields) return;

        const prefix = `record-card-${name}-${id}`;
        const card = document.getElementById(prefix);
        if (!card) return; // la tarjeta no existe en la página actual

        setTextIfPresent(prefix, 'titulo', fields.titulo);
        setTextIfPresent(prefix, 'fecha', fields.fecha);

        if (fields.icono !== undefined) {
            const img = document.getElementById(`${prefix}-icono`);
            if (img) img.src = fields.icono;
        }

        if (fields.monto !== undefined) {
            const el = document.getElementById(`${prefix}-monto`);
            if (el) el.textContent = `Monto: $${fields.monto}`;
        }

        setOptionalLine(prefix, 'origen', fields.origen);
        setOptionalLine(prefix, 'destino', fields.destino);
        setOptionalLine(prefix, 'categoria', fields.categoria);

        flash(card);
    });
})();