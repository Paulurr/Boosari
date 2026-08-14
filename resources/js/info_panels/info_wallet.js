document.addEventListener('DOMContentLoaded', () => {

    // Este script asume que paneles.js ya corrió y creó window.wallet_panel
    const panel = window.wallet_panel || window.panels?.['info-wallet'];
    if (!panel) return;

    let walletChartInstance = null;
    let currentWalletHistorial = null;
    let currentWalletData = null;

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales, para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Elementos de la UI (todo lo individual del panel de wallet)
    const statusMsg = document.getElementById('wallet-status');
    const detailsBox = document.getElementById('wallet-details');

    const imgIcono = document.getElementById('wallet-icono');
    const txtTitulo = document.getElementById('wallet-titulo');
    const inputTitulo = document.getElementById('wallet-titulo-input');
    const txtTipo = document.getElementById('wallet-tipo');
    const txtFecha = document.getElementById('wallet-fecha');

    const txtMontoActual = document.getElementById('wallet-monto-actual');
    const txtMontoInicial = document.getElementById('wallet-monto-inicial');
    const inputMontoActual = document.getElementById('wallet-monto-actual-input');
    const inputMontoInicial = document.getElementById('wallet-monto-inicial-input');

    const movementSection = document.getElementById('wallet-movement-section');
    const checkAddMovement = document.getElementById('wallet-add-movement-check');
    const movementFields = document.getElementById('wallet-movement-fields');
    const inputMovTitulo = document.getElementById('wallet-mov-titulo');
    const indicatorMovTipo = document.getElementById('wallet-mov-tipo-indicator');

    // --- Alternar el CUERPO del panel entre lectura/edición ---
    // (paneles.js ya no toca esto; es responsabilidad de este panel)
    function toggleBodySections(isEditing) {        // Genérico por clase: funciona sin importar si "modal-edit-only" está
        // en el propio input o en un <div> que lo envuelve (como el de x-label).
        // Apuntar a IDs específicos del input rompía esto, porque la clase
        // "hidden" quedaba en el <div> padre y nadie se la quitaba.
        panel.cont.querySelectorAll('.modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        panel.cont.querySelectorAll('.modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function resetMovementFields() {
        if (checkAddMovement) checkAddMovement.checked = false;
        if (movementFields) movementFields.classList.add('hidden');
        if (inputMovTitulo) inputMovTitulo.value = '';
        if (indicatorMovTipo) indicatorMovTipo.textContent = '';

        // inputMovTitulo también usa floating label: re-sincronizar tras vaciarlo por JS
        window.refreshFloatingLabels?.(panel.cont);
    }

    // Actualizar indicador del tipo de movimiento en tiempo real
    function actualizarIndicadorTipo() {
        if (!indicatorMovTipo || !currentWalletData) return;

        const nuevoMonto = parseFloat(inputMontoActual.value);
        const montoAnterior = parseFloat(currentWalletData.monto_actual);

        if (isNaN(nuevoMonto)) {
            indicatorMovTipo.textContent = '';
            return;
        }

        const diferencia = nuevoMonto - montoAnterior;

        if (diferencia === 0) {
            indicatorMovTipo.textContent = 'El monto no ha cambiado.';
            indicatorMovTipo.className = 'text-xs mt-1 font-semibold text-gray-400';
        } else if (diferencia < 0) {
            indicatorMovTipo.textContent = `Tipo: Gasto (-$${Math.abs(diferencia).toFixed(2)})`;
            indicatorMovTipo.className = 'text-xs mt-1 font-semibold text-red-500';
        } else {
            indicatorMovTipo.textContent = `Tipo: Ingreso (+$${diferencia.toFixed(2)})`;
            indicatorMovTipo.className = 'text-xs mt-1 font-semibold text-green-500';
        }
    }

    inputMontoActual?.addEventListener('input', actualizarIndicadorTipo);

    checkAddMovement?.addEventListener('change', (e) => {
        if (e.target.checked) {
            movementFields?.classList.remove('hidden');
            actualizarIndicadorTipo();
        } else {
            movementFields?.classList.add('hidden');
        }
    });

    // =====================================================================
    // CALLBACKS que wallet_panel (paneles.js) invocará en cada momento.
    // Aquí vive TODA la lógica individual: fetch, edición, guardado, borrado.
    // =====================================================================

    // Se dispara cuando el panel termina de abrirse (clic en "Detalles")
    panel.onOpen = (trigger) => {
        const walletId = trigger?.dataset?.id;
        if (!walletId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/wallets/${walletId}/info`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const resData = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(resData.message || `Error ${response.status}: No se pudo encontrar la información.`);
            }
            return resData;
        })
        .then(res => {
            currentWalletData = res.data;

            if (imgIcono) imgIcono.src = currentWalletData.icono;
            if (txtTitulo) txtTitulo.textContent = currentWalletData.titulo;
            if (txtTipo) txtTipo.textContent = currentWalletData.tipo;
            if (txtMontoActual) txtMontoActual.textContent = `$${currentWalletData.monto_actual}`;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${currentWalletData.monto_inicial}`;
            if (txtFecha) txtFecha.textContent = currentWalletData.fecha;

            renderWalletChart(currentWalletData.historial || []);

            statusMsg?.classList.add('hidden');
            detailsBox?.classList.remove('hidden');

            toggleBodySections(false);
            resetMovementFields();
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            if (statusMsg) {
                statusMsg.textContent = error.message;
                statusMsg.className = 'text-center col3 py-4 block';
            }
            detailsBox?.classList.add('hidden');
        });
    };

    // Se dispara cuando el usuario da clic en "Editar"
    panel.onEdit = () => {
        if (!currentWalletData) return;
        if (inputTitulo) inputTitulo.value = currentWalletData.titulo;
        inputMontoActual.value = currentWalletData.monto_actual;
        inputMontoInicial.value = currentWalletData.monto_inicial;
        toggleBodySections(true);
        actualizarIndicadorTipo();

        // Precarga el icono actual en el componente de imagen del modo edición
        // y limpia cualquier archivo que hubiera quedado seleccionado de una
        // edición anterior.
        window.imagePreviews?.['walletinfo']?.setPreview(currentWalletData.icono);
        window.imagePreviews?.['walletinfo']?.clearFile();

        // Los valores se asignaron por JS: la etiqueta flotante (label.js)
        // no se entera sola, hay que re-sincronizarla manualmente.
        window.refreshFloatingLabels?.(panel.cont);
    };

    // Se dispara cuando el usuario da clic en "Cancelar" (o al cerrar en modo edición)
    panel.onCancelEdit = () => {
        toggleBodySections(false);
        resetMovementFields();
        window.imagePreviews?.['walletinfo']?.reset();
    };

    // Se dispara cuando el usuario da clic en "Aceptar" (submit)
    // Debe devolver true/false para que paneles.js sepa si regresar a modo lectura
    panel.onSubmit = async () => {
        if (!currentWalletData) return false;

        const nuevoTitulo = inputTitulo?.value.trim();
        const nuevoMontoActual = parseFloat(inputMontoActual.value);
        const nuevoMontoInicial = parseFloat(inputMontoInicial.value);
        const montoAnterior = parseFloat(currentWalletData.monto_actual);

        if (!nuevoTitulo) {
            alert('El nombre de la billetera no puede estar vacío.');
            return false;
        }

        if (isNaN(nuevoMontoActual) || isNaN(nuevoMontoInicial)) {
            alert('Los montos no pueden estar vacíos.');
            return false;
        }

        const registrarMovimiento = checkAddMovement?.checked;
        let movimientoPayload = null;

        if (registrarMovimiento) {
            const tituloMov = inputMovTitulo.value.trim();
            const diferencia = nuevoMontoActual - montoAnterior;

            if (diferencia === 0) {
                alert('El monto actual no ha cambiado. Modifica el saldo o desmarca la opción de registrar movimiento.');
                return false;
            }

            if (!tituloMov) {
                alert('Ingresa un título para el movimiento.');
                return false;
            }

            movimientoPayload = {
                titulo: tituloMov,
                monto: Math.abs(diferencia),
                tipo: diferencia < 0 ? 'gasto' : 'ingreso'
            };
        }

        const payload = {
            titulo: nuevoTitulo,
            monto_actual: nuevoMontoActual,
            monto_inicial: nuevoMontoInicial
        };

        if (registrarMovimiento && movimientoPayload) {
            payload.movimiento = movimientoPayload;
        }

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['walletinfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto_actual', payload.monto_actual);
            formData.append('monto_inicial', payload.monto_inicial);
            if (payload.movimiento) {
                formData.append('movimiento[titulo]', payload.movimiento.titulo);
                formData.append('movimiento[monto]', payload.movimiento.monto);
                formData.append('movimiento[tipo]', payload.movimiento.tipo);
            }
            formData.append('icono', nuevoIcono);

            fetchOptions = {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            };
        } else {
            fetchOptions = {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            };
        }

        try {
            const response = await fetch(`/wallets/${currentWalletData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar los datos.');
                throw new Error(errorMsg);
            }

            currentWalletData = resData.data;
            if (txtTitulo) txtTitulo.textContent = resData.data.titulo;
            if (txtMontoActual) txtMontoActual.textContent = `$${resData.data.monto_actual}`;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${resData.data.monto_inicial}`;
            if (imgIcono) imgIcono.src = resData.data.icono;
            window.imagePreviews?.['walletinfo']?.clearFile();
            renderWalletChart(resData.data.historial || []);

            // Sincroniza la tarjeta del listado (home). Las tarjetas de
            // crédito no muestran "Monto Inicial" (destino queda oculto).
            const esCredito = (currentWalletData.tipo || '').toLowerCase() === 'credito';
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-wallet',
                    id: currentWalletData.id,
                    fields: {
                        titulo: currentWalletData.titulo,
                        icono: currentWalletData.icono,
                        monto: formatMoneyPhp(currentWalletData.monto_actual),
                        destino: esCredito ? '' : `Monto Inicial: $${formatMoneyPhp(currentWalletData.monto_inicial)}`
                    }
                }
            }));

            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // Se dispara cuando el usuario da clic en "Eliminar"
    // Debe devolver true/false para que paneles.js sepa si cerrar el panel
    panel.onDelete = async () => {
        if (!currentWalletData) return false;

        if (!confirm(`¿Deseas eliminar la billetera "${currentWalletData.titulo}"? Esta acción es irreversible.`)) {
            return false;
        }

        try {
            const response = await fetch(`/wallets/${currentWalletData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar la billetera.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // =====================================================================
    // Helpers y renderizado de Chart.js (lógica individual, sin cambios)
    // =====================================================================

    function getCssVar(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    function hexToRgba(hex, alpha) {
        if (!hex.startsWith('#')) return hex;
        let c = hex.substring(1);
        if (c.length === 3) c = c.split('').map(x => x + x).join('');
        const num = parseInt(c, 16);
        return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
    }

    function renderWalletChart(historial) {
        if (historial !== undefined) {
            currentWalletHistorial = historial;
        }

        const canvas = document.getElementById('wallet-chart');
        const emptyMsg = document.getElementById('wallet-chart-empty');

        if (!canvas || typeof Chart === 'undefined') return;

        if (walletChartInstance) {
            walletChartInstance.destroy();
            walletChartInstance = null;
        }

        if (!currentWalletHistorial || currentWalletHistorial.length === 0) {
            canvas.classList.add('hidden');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
            return;
        }

        canvas.classList.remove('hidden');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const ctx = canvas.getContext('2d');

        const colorGasto = '#ef4444';
        const colorIngreso = getCssVar('--col4');
        const colorTexto = getCssVar('--col7');
        const BgcolorTexto = getCssVar('--col2');

        const bgRelleno = colorIngreso.startsWith('#')
            ? hexToRgba(colorIngreso, 0.15)
            : colorIngreso.replace('rgb', 'rgba').replace(')', ', 0.15)');

        const labels = currentWalletHistorial.map(item => item.fecha);
        const dataBalances = currentWalletHistorial.map(item => item.saldo);

        const pointColors = currentWalletHistorial.map(item => {
            const tipo = (item.tipo || '').toLowerCase();
            if (tipo === 'gasto') return colorGasto;
            if (tipo === 'transaccion' || tipo === 'transferencia') return colorTexto;
            return colorIngreso;
        });

        walletChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Saldo acumulado ($)',
                    data: dataBalances,
                    backgroundColor: bgRelleno,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: pointColors,
                    pointHoverBackgroundColor: pointColors,
                    pointHoverBorderColor: pointColors,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    segment: {
                        borderColor: ctx => pointColors[ctx.p1DataIndex]
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                limits: {
                    x: { min: 'original', max: 'original', minRange: 4 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: BgcolorTexto,
                        titleColor: colorTexto,
                        bodyColor: colorTexto,
                        borderColor: hexToRgba(colorTexto, 0.2),
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) {
                                const item = currentWalletHistorial[context.dataIndex];
                                const tipo = (item.tipo || '').toLowerCase();

                                if (tipo === 'transaccion' || tipo === 'transferencia') {
                                    return [
                                        `Movimiento: ${item.titulo} ($${item.monto})`,
                                        `Origen: ${item.origen || 'N/A'}`,
                                        `Destino: ${item.destino || 'N/A'}`,
                                        `Saldo: $${item.saldo}`
                                    ];
                                }

                                const signo = tipo === 'gasto' ? '-' : '+';
                                return [
                                    `Movimiento: ${item.titulo} (${signo}$${item.monto})`,
                                    `Saldo: $${item.saldo}`
                                ];
                            }
                        }
                    },
                    zoom: {
                        pan: { enabled: true, mode: 'x', modifierKey: null, threshold: 0 },
                        zoom: { wheel: { enabled: true, speed: 0.25 }, pinch: { enabled: true }, mode: 'x' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: colorTexto, font: { size: 10 } }
                    },
                    y: {
                        grid: { color: hexToRgba(colorTexto, 0.1) },
                        ticks: { color: colorTexto, font: { size: 10 } }
                    }
                }
            }
        });
    }

    window.addEventListener('themeChanged', () => {
        if (walletChartInstance) renderWalletChart();
    });

    document.getElementById('btn-reset-zoom')?.addEventListener('click', () => {
        if (walletChartInstance) walletChartInstance.resetZoom();
    });
});