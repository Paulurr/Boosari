document.addEventListener('DOMContentLoaded', () => {

    // Instancia creada por paneles.js para "info-investment"
    const panel = window.info_investment_panel || window.panels?.['info-investment'];
    if (!panel) return;

    let investmentChartInstance = null;
    let currentInvestmentData = null;

    // Elementos de la UI
    const statusMsg = document.getElementById('investment-status');
    const detailsBox = document.getElementById('investment-details');

    const imgIcono = document.getElementById('investment-icono');
    const txtTitulo = document.getElementById('investment-titulo');
    const badgeRenta = document.getElementById('investment-renta-badge');
    const badgeEstado = document.getElementById('investment-estado-badge');

    const txtMontoInicial = document.getElementById('investment-monto-inicial');
    const txtValorActual = document.getElementById('investment-valor-actual');
    const txtGanancia = document.getElementById('investment-ganancia');
    const txtRentabilidad = document.getElementById('investment-rentabilidad');

    const txtTasa = document.getElementById('investment-tasa');
    const txtFechaAdquisicion = document.getElementById('investment-fecha-adquisicion');
    const txtFechaVencimiento = document.getElementById('investment-fecha-vencimiento');
    const txtWallet = document.getElementById('investment-wallet');
    const txtCategory = document.getElementById('investment-category');

    // Edición
    const inputTitulo = document.getElementById('investment-titulo-input');
    const inputMontoInicial = document.getElementById('investment-monto-inicial-input');
    const inputValorActual = document.getElementById('investment-valor-actual-input');
    const inputTasa = document.getElementById('investment-tasa-input');
    const tasaFieldWrap = document.getElementById('investment-tasa-field');
    const inputFechaVencimiento = document.getElementById('investment-fecha-vencimiento-input');
    const selectEstadoValue = document.getElementById('investmentinfo-estado-select-value');
    const selectEstadoTit = document.getElementById('investmentinfo-estado-select-tit');

    const ESTADO_LABELS = {
        activa: 'Activa',
        finalizada: 'Finalizada',
        cancelada: 'Cancelada'
    };

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales. Se usa para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida exactamente con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setEstadoSelect(valor) {
        if (!selectEstadoValue || !selectEstadoTit) return;
        const v = valor || 'activa';
        selectEstadoValue.value = v;
        selectEstadoTit.textContent = ESTADO_LABELS[v] || 'Activa';
    }

    function getEstadoSelect() {
        return selectEstadoValue?.value || 'activa';
    }

    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-investment-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-investment-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function renderRentaBadge(tipoRenta) {
        if (!badgeRenta) return;
        badgeRenta.textContent = tipoRenta === 'fija' ? 'Renta Fija' : 'Renta Variable';
        badgeRenta.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase col7';
    }

    function renderEstadoBadge(estado) {
        if (!badgeEstado) return;
        badgeEstado.textContent = ESTADO_LABELS[estado] || estado;
        if (estado === 'activa') {
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-green-100 text-green-700 border-green-400';
        } else if (estado === 'finalizada') {
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-gray-100 text-gray-600 border-gray-400';
        } else {
            badgeEstado.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase bg-red-100 text-red-600 border-red-400';
        }
    }

    function renderGanancia(data) {
        const ganancia = parseFloat(data.ganancia) || 0;
        const signo = ganancia > 0 ? '+' : '';
        if (txtGanancia) {
            txtGanancia.textContent = `${signo}$${ganancia.toFixed(2)}`;
            txtGanancia.className = 'modal-read-only text-lg font-bold ' + (ganancia > 0 ? 'text-green-500' : (ganancia < 0 ? 'text-red-500' : 'col7'));
        }
        if (txtRentabilidad) {
            const inicial = parseFloat(data.monto_inicial) || 0;
            const pct = inicial > 0 ? (ganancia / inicial) * 100 : 0;
            txtRentabilidad.textContent = `${signo}${pct.toFixed(2)}%`;
            txtRentabilidad.className = 'text-xs font-semibold ' + (ganancia > 0 ? 'text-green-500' : (ganancia < 0 ? 'text-red-500' : 'col7'));
        }
    }

    function toggleTasaField() {
        if (!tasaFieldWrap) return;
        // Solo aplica a inversiones de renta fija.
        tasaFieldWrap.classList.toggle('hidden', currentInvestmentData?.tipo_renta !== 'fija');
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    panel.onOpen = (trigger) => {
        const investmentId = trigger?.dataset?.id;
        if (!investmentId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información de la inversión...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/investments/${investmentId}/info`, {
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
            currentInvestmentData = res.data;

            if (imgIcono) imgIcono.src = currentInvestmentData.icono;
            if (txtTitulo) txtTitulo.textContent = currentInvestmentData.titulo;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${currentInvestmentData.monto_inicial}`;
            if (txtValorActual) txtValorActual.textContent = `$${currentInvestmentData.valor_actual}`;
            if (txtTasa) txtTasa.textContent = currentInvestmentData.tasa_interes ? `${currentInvestmentData.tasa_interes}%` : 'N/A';
            if (txtFechaAdquisicion) txtFechaAdquisicion.textContent = currentInvestmentData.fecha_adquisicion_f;
            if (txtFechaVencimiento) txtFechaVencimiento.textContent = currentInvestmentData.fecha_vencimiento_f;
            if (txtWallet) txtWallet.textContent = currentInvestmentData.wallet;
            if (txtCategory) txtCategory.textContent = currentInvestmentData.category;

            renderRentaBadge(currentInvestmentData.tipo_renta);
            renderEstadoBadge(currentInvestmentData.estado);
            renderGanancia(currentInvestmentData);
            renderInvestmentChart(currentInvestmentData);
            toggleTasaField();

            statusMsg?.classList.add('hidden');
            detailsBox?.classList.remove('hidden');

            toggleBodySections(false);
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
        if (!currentInvestmentData) return;

        if (inputTitulo) inputTitulo.value = currentInvestmentData.titulo;
        if (inputMontoInicial) inputMontoInicial.value = currentInvestmentData.monto_inicial;
        if (inputValorActual) inputValorActual.value = currentInvestmentData.valor_actual;
        if (inputTasa) inputTasa.value = currentInvestmentData.tasa_interes || '';
        if (inputFechaVencimiento) inputFechaVencimiento.value = currentInvestmentData.fecha_vencimiento || '';
        setEstadoSelect(currentInvestmentData.estado);

        toggleBodySections(true);
        toggleTasaField();

        // Precarga el icono actual en el componente de imagen del modo edición
        // y limpia cualquier archivo que hubiera quedado seleccionado de una
        // edición anterior.
        window.imagePreviews?.['investmentinfo']?.setPreview(currentInvestmentData.icono);
        window.imagePreviews?.['investmentinfo']?.clearFile();

        window.refreshFloatingLabels?.(panel.cont);
    };

    panel.onCancelEdit = () => {
        toggleBodySections(false);
        window.imagePreviews?.['investmentinfo']?.reset();
    };

    panel.onSubmit = async () => {
        if (!currentInvestmentData) return false;

        const titulo = inputTitulo?.value.trim();
        const montoInicial = parseFloat(inputMontoInicial?.value);
        const valorActual = parseFloat(inputValorActual?.value);
        const tasaInteres = inputTasa?.value ? parseFloat(inputTasa.value) : null;
        const fechaVencimiento = inputFechaVencimiento?.value || null;
        const estado = getEstadoSelect();

        if (!titulo) {
            alert('El nombre de la inversión no puede estar vacío.');
            return false;
        }
        if (isNaN(montoInicial) || montoInicial <= 0) {
            alert('Ingresa un monto invertido válido.');
            return false;
        }
        if (isNaN(valorActual) || valorActual < 0) {
            alert('Ingresa un valor actual válido.');
            return false;
        }

        const payload = {
            titulo: titulo,
            monto_inicial: montoInicial,
            valor_actual: valorActual,
            tasa_interes: tasaInteres,
            fecha_vencimiento: fechaVencimiento,
            estado: estado
        };

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['investmentinfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto_inicial', payload.monto_inicial);
            formData.append('valor_actual', payload.valor_actual);
            if (payload.tasa_interes !== null) formData.append('tasa_interes', payload.tasa_interes);
            if (payload.fecha_vencimiento) formData.append('fecha_vencimiento', payload.fecha_vencimiento);
            formData.append('estado', payload.estado);
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
            const response = await fetch(`/investments/${currentInvestmentData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar la inversión.');
                throw new Error(errorMsg);
            }

            currentInvestmentData = resData.data;

            if (imgIcono) imgIcono.src = currentInvestmentData.icono;
            if (txtTitulo) txtTitulo.textContent = currentInvestmentData.titulo;
            if (txtMontoInicial) txtMontoInicial.textContent = `$${currentInvestmentData.monto_inicial}`;
            if (txtValorActual) txtValorActual.textContent = `$${currentInvestmentData.valor_actual}`;
            if (txtTasa) txtTasa.textContent = currentInvestmentData.tasa_interes ? `${currentInvestmentData.tasa_interes}%` : 'N/A';
            if (txtFechaVencimiento) txtFechaVencimiento.textContent = currentInvestmentData.fecha_vencimiento_f;

            renderEstadoBadge(currentInvestmentData.estado);
            renderGanancia(currentInvestmentData);
            renderInvestmentChart(currentInvestmentData);
            toggleTasaField();
            window.imagePreviews?.['investmentinfo']?.clearFile();

            // ---- Sincroniza la tarjeta del listado (home) ------------------
            // Reproduce exactamente el mismo formato que arma el switch de
            // 'investment' en home_blade.php para que la tarjeta quede
            // idéntica a como quedaría tras un recargue de página.
            const gananciaNum = parseFloat(currentInvestmentData.ganancia) || 0;
            const signoGanancia = gananciaNum >= 0 ? '+' : '';
            const gananciaFormateada = `${signoGanancia}$${gananciaNum.toFixed(2)}`;

            let tasaTexto = '';
            if (currentInvestmentData.tipo_renta === 'fija' && currentInvestmentData.tasa_interes) {
                tasaTexto = ` | Tasa: ${parseFloat(currentInvestmentData.tasa_interes)}%`;
            }

            const vencimientoTexto = currentInvestmentData.fecha_vencimiento_f || 'Sin fecha';

            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-investment',
                    id: currentInvestmentData.id,
                    fields: {
                        titulo: currentInvestmentData.titulo,
                        icono: currentInvestmentData.icono,
                        monto: formatMoneyPhp(currentInvestmentData.valor_actual),
                        origen: `Invertido: $${formatMoneyPhp(currentInvestmentData.monto_inicial)}`,
                        destino: `Ganancia: ${gananciaFormateada}${tasaTexto} | Vence: ${vencimientoTexto}`
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
        if (!currentInvestmentData) return false;

        if (!confirm(`¿Deseas eliminar la inversión "${currentInvestmentData.titulo}"? Esta acción es irreversible.`)) {
            return false;
        }

        try {
            const response = await fetch(`/investments/${currentInvestmentData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar la inversión.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // =====================================================================
    // Gráfica: comparación Invertido vs Valor Actual (no hay historial de
    // movimientos para inversiones, así que se muestra una foto del estado
    // actual en vez de una serie temporal como en wallet/goal/debt).
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

    function renderInvestmentChart(data) {
        const canvas = document.getElementById('investment-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        if (investmentChartInstance) {
            investmentChartInstance.destroy();
            investmentChartInstance = null;
        }

        const inicial = parseFloat(data.monto_inicial) || 0;
        const actual = parseFloat(data.valor_actual) || 0;
        const ganancia = actual - inicial;

        const colorIngreso = getCssVar('--col4');
        const colorGasto = '#ef4444';
        const colorTexto = getCssVar('--col7');
        const BgcolorTexto = getCssVar('--col2');

        const ctx = canvas.getContext('2d');

        investmentChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Invertido', 'Valor Actual'],
                datasets: [{
                    label: 'Monto ($)',
                    data: [inicial, actual],
                    backgroundColor: [
                        hexToRgba(colorTexto, 0.35),
                        ganancia >= 0 ? hexToRgba(colorIngreso, 0.7) : hexToRgba(colorGasto, 0.7)
                    ],
                    borderColor: [
                        colorTexto,
                        ganancia >= 0 ? colorIngreso : colorGasto
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                    maxBarThickness: 70
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
                            label: (context) => `$${context.parsed.y.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: colorTexto, font: { size: 11 } }
                    },
                    y: {
                        grid: { color: hexToRgba(colorTexto, 0.1) },
                        ticks: { color: colorTexto, font: { size: 10 } },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    window.addEventListener('themeChanged', () => {
        if (investmentChartInstance && currentInvestmentData) renderInvestmentChart(currentInvestmentData);
    });
});