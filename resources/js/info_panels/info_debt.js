document.addEventListener('DOMContentLoaded', () => {

    // Instancia creada por paneles.js para "info-debt"
    const panel = window.info_debt_panel || window.panels?.['info-debt'];
    if (!panel) return;

    let debtChartInstance = null;
    let currentDebtHistorial = null;
    let currentDebtData = null;

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales, para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function capitalize(text) {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    const statusMsg = document.getElementById('debt-status');
    const detailsBox = document.getElementById('debt-details');

    const imgIcono = document.getElementById('debt-icono');
    const txtTitulo = document.getElementById('debt-titulo');
    const badgeEstado = document.getElementById('debt-estado-badge');

    const txtTasa = document.getElementById('debt-tasa');
    const txtFechaVencimiento = document.getElementById('debt-fecha-vencimiento');
    const txtPrioridad = document.getElementById('debt-prioridad-txt');
    const txtCategory = document.getElementById('debt-category');
    const txtMontoInicial = document.getElementById('debt-monto-inicial');
    const txtMontoActual = document.getElementById('debt-monto-actual');
    const txtMontoInicialTxt = document.getElementById('debt-monto-inicial-txt');

    const progresoBar = document.getElementById('debt-progreso-bar');
    const progresoPct = document.getElementById('debt-progreso-pct');

    const inputTitulo = document.getElementById('debt-titulo-input');
    const inputTasa = document.getElementById('debt-tasa-input');
    const inputFechaVencimiento = document.getElementById('debt-fecha-vencimiento-input');
    const selectPrioridadValue = document.getElementById('debtinfo-prioridad-select-value');
    const selectPrioridadTit = document.getElementById('debtinfo-prioridad-select-tit');

    const PRIORIDAD_LABELS = {
        media: 'Media (Normal)',
        alta: 'Alta (Urgente)',
        baja: 'Baja (Flexible)',
    };

    function setPrioridadSelect(valor) {
        if (!selectPrioridadValue || !selectPrioridadTit) return;
        const v = valor || 'media';
        selectPrioridadValue.value = v;
        selectPrioridadTit.textContent = PRIORIDAD_LABELS[v] || 'Media (Normal)';
    }

    const paymentSection = document.getElementById('debt-payment-section');
    const checkAddPayment = document.getElementById('debt-add-payment-check');
    const paymentFields = document.getElementById('debt-payment-fields');
    const inputPagoTitulo = document.getElementById('debt-payment-titulo');
    const inputPagoMonto = document.getElementById('debt-payment-monto');
    const selectPagoWalletValue = document.getElementById('debtinfo-payment-wallet-select-value');
    const checkPagoMinimo = document.getElementById('debt-payment-minimo');

    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-debt-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-debt-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function resetPaymentFields() {
        if (checkAddPayment) checkAddPayment.checked = false;
        if (paymentFields) paymentFields.classList.add('hidden');
        if (inputPagoTitulo) inputPagoTitulo.value = '';
        if (inputPagoMonto) inputPagoMonto.value = '';
        if (checkPagoMinimo) checkPagoMinimo.checked = false;
    }

    checkAddPayment?.addEventListener('change', (e) => {
        paymentFields?.classList.toggle('hidden', !e.target.checked);
    });

    function renderProgreso(montoActual, montoInicial) {
        const inicial = parseFloat(montoInicial) || 0;
        const actual = parseFloat(montoActual) || 0;
        const pct = inicial > 0 ? Math.min(100, Math.round(((inicial - actual) / inicial) * 1000) / 10) : 0;

        if (progresoBar) progresoBar.style.width = `${pct}%`;
        if (progresoPct) progresoPct.textContent = `${pct}%`;
        if (txtMontoActual) txtMontoActual.textContent = `Resta: $${actual.toFixed(2)}`;
        if (txtMontoInicialTxt) txtMontoInicialTxt.textContent = `Original: $${inicial.toFixed(2)}`;
    }

    function renderEstadoBadge(estado) {
        if (!badgeEstado) return;
        if (estado === 'pagada') {
            badgeEstado.textContent = '¡Deuda Pagada!';
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-green-100 text-green-700 border-green-400';
        } else {
            badgeEstado.textContent = 'Pendiente';
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-red-100 text-red-600 border-red-400';
        }
    }

    panel.onOpen = (trigger) => {
        const debtId = trigger?.dataset?.id;
        if (!debtId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información de la deuda...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/debts/${debtId}/info`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(async response => {
            const resData = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(resData.message || `Error ${response.status}: No se pudo obtener la información.`);
            }
            return resData;
        })
        .then(res => {
            currentDebtData = res.data;

            if (imgIcono) imgIcono.src = currentDebtData.icono;
            if (txtTitulo) txtTitulo.textContent = currentDebtData.titulo;
            if (txtTasa) txtTasa.textContent = `${currentDebtData.tasa_interes}%`;
            if (txtFechaVencimiento) txtFechaVencimiento.textContent = currentDebtData.fecha_vencimiento_f;
            if (txtPrioridad) txtPrioridad.textContent = currentDebtData.prioridad;
            if (txtCategory) txtCategory.textContent = currentDebtData.category;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${currentDebtData.monto_inicial}`;

            renderEstadoBadge(currentDebtData.estado);
            renderProgreso(currentDebtData.monto_actual, currentDebtData.monto_inicial);
            renderDebtChart(currentDebtData.historial || []);

            statusMsg?.classList.add('hidden');
            detailsBox?.classList.remove('hidden');

            toggleBodySections(false);
            resetPaymentFields();
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            if (statusMsg) {
                statusMsg.textContent = error.message;
                statusMsg.className = 'text-center col3 py-4 block';
                statusMsg.classList.remove('hidden');
            }
            detailsBox?.classList.add('hidden');
        });
    };

    panel.onEdit = () => {
        if (!currentDebtData) return;
        if (inputTitulo) inputTitulo.value = currentDebtData.titulo;
        if (inputTasa) inputTasa.value = currentDebtData.tasa_interes;
        if (inputFechaVencimiento) inputFechaVencimiento.value = currentDebtData.fecha_vencimiento || '';
        setPrioridadSelect(currentDebtData.prioridad);
        toggleBodySections(true);

        window.imagePreviews?.['debtinfo']?.setPreview(currentDebtData.icono);
        window.imagePreviews?.['debtinfo']?.clearFile();
    };

    panel.onCancelEdit = () => {
        toggleBodySections(false);
        resetPaymentFields();
        window.imagePreviews?.['debtinfo']?.reset();
    };

    panel.onSubmit = async () => {
        if (!currentDebtData) return false;

        // La deuda ya está saldada: no tiene sentido seguir editándola con pagos.
        if (currentDebtData.estado === 'pagada' && checkAddPayment?.checked) {
            alert('Esta deuda ya está pagada por completo.');
            return false;
        }

        const titulo = inputTitulo?.value.trim();
        const tasa = inputTasa?.value !== '' ? parseFloat(inputTasa.value) : null;
        const fechaVencimiento = inputFechaVencimiento?.value;
        const prioridad = selectPrioridadValue?.value || 'media';

        if (!titulo) {
            alert('El nombre de la deuda no puede estar vacío.');
            return false;
        }
        if (!fechaVencimiento) {
            alert('Selecciona una fecha de vencimiento.');
            return false;
        }

        const payload = {
            titulo,
            tasa_interes: tasa,
            fecha_vencimiento: fechaVencimiento,
            prioridad,
        };

        if (checkAddPayment?.checked) {
            const pagoTitulo = inputPagoTitulo?.value.trim();
            const pagoMonto = parseFloat(inputPagoMonto?.value);
            const restante = parseFloat(currentDebtData.monto_actual) || 0;

            if (!pagoTitulo) {
                alert('Ingresa un concepto para el pago.');
                return false;
            }
            if (isNaN(pagoMonto) || pagoMonto <= 0) {
                alert('Ingresa un monto de pago válido mayor a 0.');
                return false;
            }
            if (pagoMonto > restante) {
                alert(`El pago no puede ser mayor al saldo restante ($${restante.toFixed(2)}). Ingresa como máximo esa cantidad.`);
                return false;
            }

            payload.pago = {
                titulo: pagoTitulo,
                monto: pagoMonto,
                wallet_id: selectPagoWalletValue?.value || null,
                pago_minimo: checkPagoMinimo?.checked ? 1 : 0,
            };
        }

        try {
            const nuevoIcono = window.imagePreviews?.['debtinfo']?.getFile();

            let fetchOptions;
            if (nuevoIcono) {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('titulo', payload.titulo);
                if (payload.tasa_interes !== null && payload.tasa_interes !== undefined) {
                    formData.append('tasa_interes', payload.tasa_interes);
                }
                formData.append('fecha_vencimiento', payload.fecha_vencimiento);
                formData.append('prioridad', payload.prioridad);
                if (payload.pago) {
                    formData.append('pago[titulo]', payload.pago.titulo);
                    formData.append('pago[monto]', payload.pago.monto);
                    if (payload.pago.wallet_id) formData.append('pago[wallet_id]', payload.pago.wallet_id);
                    formData.append('pago[pago_minimo]', payload.pago.pago_minimo);
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

            const response = await fetch(`/debts/${currentDebtData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar la deuda.');
                throw new Error(errorMsg);
            }

            currentDebtData = resData.data;

            if (imgIcono) imgIcono.src = currentDebtData.icono;
            if (txtTitulo) txtTitulo.textContent = currentDebtData.titulo;
            if (txtTasa) txtTasa.textContent = `${currentDebtData.tasa_interes}%`;
            if (txtFechaVencimiento) txtFechaVencimiento.textContent = currentDebtData.fecha_vencimiento_f;
            if (txtPrioridad) txtPrioridad.textContent = currentDebtData.prioridad;

            renderEstadoBadge(currentDebtData.estado);
            renderProgreso(currentDebtData.monto_actual, currentDebtData.monto_inicial);
            renderDebtChart(currentDebtData.historial || []);
            resetPaymentFields();
            window.imagePreviews?.['debtinfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). Reproduce el mismo
            // formato que arma el switch de 'debt' en home_blade.php.
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-debt',
                    id: currentDebtData.id,
                    fields: {
                        titulo: currentDebtData.titulo,
                        icono: currentDebtData.icono,
                        monto: formatMoneyPhp(currentDebtData.monto_actual),
                        destino: `Prioridad: ${capitalize(currentDebtData.prioridad)} | Vence: ${currentDebtData.fecha_vencimiento_f}`
                    }
                }
            }));

            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    panel.onDelete = async () => {
        if (!currentDebtData) return false;

        if (!confirm(`¿Deseas eliminar la deuda "${currentDebtData.titulo}"? Se eliminará también todo su historial de pagos.`)) {
            return false;
        }

        try {
            const response = await fetch(`/debts/${currentDebtData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar la deuda.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // =====================================================================
    // Gráfica de pagos (saldo restante bajando hacia 0)
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

    function renderDebtChart(historial) {
        if (historial !== undefined) currentDebtHistorial = historial;

        const canvas = document.getElementById('debt-chart');
        const emptyMsg = document.getElementById('debt-chart-empty');
        if (!canvas || typeof Chart === 'undefined') return;

        if (debtChartInstance) {
            debtChartInstance.destroy();
            debtChartInstance = null;
        }

        if (!currentDebtHistorial || currentDebtHistorial.length === 0) {
            canvas.classList.add('hidden');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
            return;
        }

        canvas.classList.remove('hidden');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const ctx = canvas.getContext('2d');
        const colorGasto = '#ef4444';
        const colorTexto = getCssVar('--col7');
        const BgcolorTexto = getCssVar('--col2');
        const bgRelleno = hexToRgba(colorGasto, 0.15);

        const labels = currentDebtHistorial.map(item => item.fecha);
        const dataBalances = currentDebtHistorial.map(item => item.saldo);

        debtChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Deuda restante ($)',
                    data: dataBalances,
                    backgroundColor: bgRelleno,
                    borderColor: colorGasto,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: colorGasto,
                    pointBorderColor: colorGasto,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                                const item = currentDebtHistorial[context.dataIndex];
                                return [
                                    `Pago: ${item.titulo} (-$${item.monto})`,
                                    `Resta: $${item.saldo}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colorTexto, font: { size: 10 } } },
                    y: { grid: { color: hexToRgba(colorTexto, 0.1) }, ticks: { color: colorTexto, font: { size: 10 } } }
                }
            }
        });
    }

    window.addEventListener('themeChanged', () => {
        if (debtChartInstance) renderDebtChart();
    });
});