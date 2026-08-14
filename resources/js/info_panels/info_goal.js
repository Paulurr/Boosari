document.addEventListener('DOMContentLoaded', () => {

    // Instancia creada por paneles.js para "info-goal"
    const panel = window.info_goal_panel || window.panels?.['info-goal'];
    if (!panel) return;

    let goalChartInstance = null;
    let currentGoalHistorial = null;
    let currentGoalData = null;

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

    // Elementos de la UI
    const statusMsg = document.getElementById('goal-status');
    const detailsBox = document.getElementById('goal-details');

    const imgIcono = document.getElementById('goal-icono');
    const txtTitulo = document.getElementById('goal-titulo');
    const badgeEstado = document.getElementById('goal-estado-badge');

    const progresoBar = document.getElementById('goal-progreso-bar');
    const progresoPct = document.getElementById('goal-progreso-pct');
    const txtMontoActual = document.getElementById('goal-monto-actual');
    const txtMontoObjetivoTxt = document.getElementById('goal-monto-objetivo-txt');

    const txtMontoObjetivo = document.getElementById('goal-monto-objetivo');
    const txtFechaLimite = document.getElementById('goal-fecha-limite');
    const txtDescripcion = document.getElementById('goal-descripcion');
    const txtCategory = document.getElementById('goal-category');
    const txtMontoInicial = document.getElementById('goal-monto-inicial');

    // Edición
    const inputTitulo = document.getElementById('goal-titulo-input');
    const inputMontoObjetivo = document.getElementById('goal-monto-objetivo-input');
    const inputFechaLimite = document.getElementById('goal-fecha-limite-input');
    const inputDescripcion = document.getElementById('goal-descripcion-input');

    // Sección opcional de abono
    const paymentSection = document.getElementById('goal-payment-section');
    const checkAddPayment = document.getElementById('goal-add-payment-check');
    const paymentFields = document.getElementById('goal-payment-fields');
    const inputPagoTitulo = document.getElementById('goal-payment-titulo');
    const inputPagoMonto = document.getElementById('goal-payment-monto');
    const selectPagoWalletValue = document.getElementById('goalinfo-payment-wallet-select-value');

    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-goal-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-goal-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function resetPaymentFields() {
        if (checkAddPayment) checkAddPayment.checked = false;
        if (paymentFields) paymentFields.classList.add('hidden');
        if (inputPagoTitulo) inputPagoTitulo.value = '';
        if (inputPagoMonto) inputPagoMonto.value = '';
    }

    checkAddPayment?.addEventListener('change', (e) => {
        paymentFields?.classList.toggle('hidden', !e.target.checked);
    });

    function renderProgreso(data) {
        const pct = data.progreso ?? 0;
        const actual = parseFloat(data.monto_actual) || 0;
        const objetivo = parseFloat(data.monto_objetivo) || 0;

        if (progresoBar) progresoBar.style.width = `${pct}%`;
        if (progresoPct) progresoPct.textContent = `${pct}%`;
        if (txtMontoActual) txtMontoActual.textContent = `$${actual.toFixed(2)}`;
        if (txtMontoObjetivoTxt) txtMontoObjetivoTxt.textContent = `$${objetivo.toFixed(2)}`;
    }

    function renderEstadoBadge(estado) {
        if (!badgeEstado) return;
        if (estado === 'completada') {
            badgeEstado.textContent = '¡Meta Completada!';
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-green-100 text-green-700 border-green-400';
        } else {
            badgeEstado.textContent = 'En progreso';
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-gray-100 text-gray-600 border-gray-400';
        }
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    panel.onOpen = (trigger) => {
        const goalId = trigger?.dataset?.id;
        if (!goalId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información de la meta...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/goals/${goalId}/info`, {
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
            currentGoalData = res.data;

            if (imgIcono) imgIcono.src = currentGoalData.icono;
            if (txtTitulo) txtTitulo.textContent = currentGoalData.titulo;
            if (txtMontoObjetivo) txtMontoObjetivo.textContent = `$${currentGoalData.monto_objetivo}`;
            if (txtFechaLimite) txtFechaLimite.textContent = currentGoalData.fecha_limite_f;
            if (txtDescripcion) txtDescripcion.textContent = currentGoalData.descripcion || 'Sin descripción.';
            if (txtCategory) txtCategory.textContent = currentGoalData.category;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${currentGoalData.monto_inicial}`;

            renderEstadoBadge(currentGoalData.estado);
            renderProgreso(currentGoalData);
            renderGoalChart(currentGoalData.historial || []);

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
        if (!currentGoalData) return;
        if (inputTitulo) inputTitulo.value = currentGoalData.titulo;
        if (inputMontoObjetivo) inputMontoObjetivo.value = currentGoalData.monto_objetivo;
        if (inputFechaLimite) inputFechaLimite.value = currentGoalData.fecha_limite || '';
        if (inputDescripcion) inputDescripcion.value = currentGoalData.descripcion || '';
        toggleBodySections(true);

        window.imagePreviews?.['goalinfo']?.setPreview(currentGoalData.icono);
        window.imagePreviews?.['goalinfo']?.clearFile();
    };

    panel.onCancelEdit = () => {
        toggleBodySections(false);
        resetPaymentFields();
        window.imagePreviews?.['goalinfo']?.reset();
    };

    panel.onSubmit = async () => {
        if (!currentGoalData) return false;

        // La meta ya está completada: no tiene sentido seguir registrando abonos.
        if (currentGoalData.estado === 'completada' && checkAddPayment?.checked) {
            alert('Esta meta ya fue completada.');
            return false;
        }

        const titulo = inputTitulo?.value.trim();
        const montoObjetivo = parseFloat(inputMontoObjetivo?.value);
        const fechaLimite = inputFechaLimite?.value;
        const descripcion = inputDescripcion?.value.trim();

        if (!titulo) {
            alert('El nombre de la meta no puede estar vacío.');
            return false;
        }
        if (isNaN(montoObjetivo) || montoObjetivo <= 0) {
            alert('Ingresa un monto objetivo válido mayor a 0.');
            return false;
        }
        if (!fechaLimite) {
            alert('Selecciona una fecha límite.');
            return false;
        }

        const payload = {
            titulo,
            monto_objetivo: montoObjetivo,
            fecha_limite: fechaLimite,
            descripcion: descripcion || null,
        };

        if (checkAddPayment?.checked) {
            const pagoTitulo = inputPagoTitulo?.value.trim();
            const pagoMonto = parseFloat(inputPagoMonto?.value);

            if (!pagoTitulo) {
                alert('Ingresa un concepto para el abono.');
                return false;
            }
            if (isNaN(pagoMonto) || pagoMonto <= 0) {
                alert('Ingresa un monto de abono válido mayor a 0.');
                return false;
            }

            payload.abono = {
                titulo: pagoTitulo,
                monto: pagoMonto,
                wallet_id: selectPagoWalletValue?.value || null,
            };
        }

        try {
            const nuevoIcono = window.imagePreviews?.['goalinfo']?.getFile();

            let fetchOptions;
            if (nuevoIcono) {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('titulo', payload.titulo);
                formData.append('monto_objetivo', payload.monto_objetivo);
                formData.append('fecha_limite', payload.fecha_limite);
                if (payload.descripcion) formData.append('descripcion', payload.descripcion);
                if (payload.abono) {
                    formData.append('abono[titulo]', payload.abono.titulo);
                    formData.append('abono[monto]', payload.abono.monto);
                    if (payload.abono.wallet_id) formData.append('abono[wallet_id]', payload.abono.wallet_id);
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

            const response = await fetch(`/goals/${currentGoalData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar la meta.');
                throw new Error(errorMsg);
            }

            currentGoalData = resData.data;

            if (imgIcono) imgIcono.src = currentGoalData.icono;
            if (txtTitulo) txtTitulo.textContent = currentGoalData.titulo;
            if (txtMontoObjetivo) txtMontoObjetivo.textContent = `$${currentGoalData.monto_objetivo}`;
            if (txtFechaLimite) txtFechaLimite.textContent = currentGoalData.fecha_limite_f;
            if (txtDescripcion) txtDescripcion.textContent = currentGoalData.descripcion || 'Sin descripción.';

            renderEstadoBadge(currentGoalData.estado);
            renderProgreso(currentGoalData);
            renderGoalChart(currentGoalData.historial || []);
            resetPaymentFields();
            window.imagePreviews?.['goalinfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). Reproduce el mismo
            // formato que arma el switch de 'goal' en home_blade.php.
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-goal',
                    id: currentGoalData.id,
                    fields: {
                        titulo: currentGoalData.titulo,
                        icono: currentGoalData.icono,
                        monto: formatMoneyPhp(currentGoalData.monto_objetivo),
                        origen: `Meta: $${formatMoneyPhp(currentGoalData.monto_objetivo)} | Inicial: $${formatMoneyPhp(currentGoalData.monto_inicial)}`,
                        destino: `Fecha Límite: ${currentGoalData.fecha_limite_f} | Estado: ${capitalize(currentGoalData.estado)}`
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
        if (!currentGoalData) return false;

        if (!confirm(`¿Deseas eliminar la meta "${currentGoalData.titulo}"? Se eliminará también todo su historial de abonos.`)) {
            return false;
        }

        try {
            const response = await fetch(`/goals/${currentGoalData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar la meta.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // =====================================================================
    // Gráfica de abonos (saldo acumulado subiendo hacia el objetivo)
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

    function renderGoalChart(historial) {
        if (historial !== undefined) currentGoalHistorial = historial;

        const canvas = document.getElementById('goal-chart');
        const emptyMsg = document.getElementById('goal-chart-empty');
        if (!canvas || typeof Chart === 'undefined') return;

        if (goalChartInstance) {
            goalChartInstance.destroy();
            goalChartInstance = null;
        }

        if (!currentGoalHistorial || currentGoalHistorial.length === 0) {
            canvas.classList.add('hidden');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
            return;
        }

        canvas.classList.remove('hidden');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const ctx = canvas.getContext('2d');
        const colorIngreso = getCssVar('--col4');
        const colorTexto = getCssVar('--col7');
        const BgcolorTexto = getCssVar('--col2');
        const bgRelleno = colorIngreso.startsWith('#')
            ? hexToRgba(colorIngreso, 0.15)
            : colorIngreso.replace('rgb', 'rgba').replace(')', ', 0.15)');

        const labels = currentGoalHistorial.map(item => item.fecha);
        const dataBalances = currentGoalHistorial.map(item => item.saldo);

        goalChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Progreso de la meta ($)',
                    data: dataBalances,
                    backgroundColor: bgRelleno,
                    borderColor: colorIngreso,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: colorIngreso,
                    pointBorderColor: colorIngreso,
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
                                const item = currentGoalHistorial[context.dataIndex];
                                return [
                                    `Abono: ${item.titulo} (+$${item.monto})`,
                                    `Acumulado: $${item.saldo}`
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
        if (goalChartInstance) renderGoalChart();
    });
});