document.addEventListener('DOMContentLoaded', () => {

    // Instancia del panel creada por paneles.js para "info-income"
    const panel = window.info_income_panel || window.panels?.['info-income'];
    if (!panel) return;

    let currentIncomeData = null;

    // Elementos de la UI
    const statusMsg = document.getElementById('income-status');
    const detailsBox = document.getElementById('income-details');

    // Icono
    const imgIcono = document.getElementById('income-icono');

    // Lectura
    const txtTitulo = document.getElementById('income-titulo');
    const txtMonto = document.getElementById('income-monto');
    const txtFrecuencia = document.getElementById('income-frecuencia');
    const txtFecha = document.getElementById('income-fecha');
    const txtWallet = document.getElementById('income-wallet');
    const txtCategory = document.getElementById('income-category');
    const badgeActivo = document.getElementById('income-activo-badge');

    // Edición
    const inputTitulo = document.getElementById('income-titulo-input');
    const inputMonto = document.getElementById('income-monto-input');
    const inputFecha = document.getElementById('income-fecha-input');
    const checkActivo = document.getElementById('income-activo-input'); // <-- coincide con el blade

    // El campo Frecuencia ahora usa el componente x-select (name="income-frecuencia"):
    // el valor real vive en el input oculto "income-frecuencia-select-value"
    // y el texto visible en "income-frecuencia-select-tit".
    const selectFrecuenciaValue = document.getElementById('income-frecuencia-select-value');
    const selectFrecuenciaTit = document.getElementById('income-frecuencia-select-tit');

    const FRECUENCIA_LABELS = {
        ninguno: 'Ninguno',
        diario: 'Diario',
        semanal: 'Semanal',
        quincenal: 'Quincenal',
        mensual: 'Mensual',
        anual: 'Anual'
    };

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales, para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setFrecuenciaSelect(valor) {
        if (!selectFrecuenciaValue || !selectFrecuenciaTit) return;
        const v = valor || 'ninguno';
        selectFrecuenciaValue.value = v === 'ninguno' ? '' : v;
        selectFrecuenciaTit.textContent = FRECUENCIA_LABELS[v] || 'Ninguno';
    }

    function getFrecuenciaSelect() {
        const v = selectFrecuenciaValue?.value || '';
        return v === '' ? 'ninguno' : v;
    }

    // Alternar visibilidad del cuerpo entre Modo Lectura y Modo Edición
    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-income-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-income-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    // Actualizar visualmente el badge de estado activo/inactivo
    function renderActivoBadge(isActivo) {
        if (!badgeActivo) return;
        if (isActivo) {
            badgeActivo.textContent = 'Generación Activa';
            badgeActivo.className = 'modal-read-only px-3 py-1 text-xs rounded-full font-bold uppercase bg-green-100 text-green-700 border border-green-400';
        } else {
            badgeActivo.textContent = 'Pausado / Inactivo';
            badgeActivo.className = 'modal-read-only px-3 py-1 text-xs rounded-full font-bold uppercase bg-gray-100 text-gray-600 border border-gray-400';
        }
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    // Disparado al abrir el panel desde un registro (trigger = botón "Detalles" con data-id)
    panel.onOpen = (trigger) => {
        const incomeId = trigger?.dataset?.id;

        if (!incomeId) {
            console.error('No se encontró un data-id válido en el disparador:', trigger);
            if (statusMsg) {
                statusMsg.textContent = 'Error: No se proporcionó el ID del ingreso.';
                statusMsg.classList.remove('hidden');
            }
            detailsBox?.classList.add('hidden');
            return;
        }

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información del ingreso...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/incomes/${incomeId}/info`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const resData = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(resData.message || `Error ${response.status}: No se pudo obtener la información.`);
            }
            return resData;
        })
        .then(res => {
            currentIncomeData = res.data;

            // Renderizado en vista de lectura
            if (imgIcono) imgIcono.src = currentIncomeData.icono;
            if (txtTitulo) txtTitulo.textContent = currentIncomeData.titulo;
            if (txtMonto) txtMonto.textContent = `$${parseFloat(currentIncomeData.monto).toFixed(2)}`;
            if (txtFrecuencia) txtFrecuencia.textContent = currentIncomeData.frecuencia;
            if (txtFecha) txtFecha.textContent = currentIncomeData.fecha_f;
            if (txtWallet) txtWallet.textContent = currentIncomeData.wallet;
            if (txtCategory) txtCategory.textContent = currentIncomeData.category;

            renderActivoBadge(currentIncomeData.activo);

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

    // Disparado al hacer clic en "Editar"
    panel.onEdit = () => {
        if (!currentIncomeData) return;

        if (inputTitulo) inputTitulo.value = currentIncomeData.titulo;
        if (inputMonto) inputMonto.value = currentIncomeData.monto;
        setFrecuenciaSelect(currentIncomeData.frecuencia);
        if (inputFecha) inputFecha.value = currentIncomeData.fecha_inicio || '';
        if (checkActivo) checkActivo.checked = Boolean(currentIncomeData.activo);

        toggleBodySections(true);

        // Precarga el icono actual en el componente de imagen del modo edición
        // y limpia cualquier archivo que hubiera quedado seleccionado de una
        // edición anterior.
        window.imagePreviews?.['incomeinfo']?.setPreview(currentIncomeData.icono);
        window.imagePreviews?.['incomeinfo']?.clearFile();

        // inputTitulo/inputMonto usan floating label (x-label): los valores se
        // asignaron por JS, así que hay que re-sincronizar manualmente (label.js).
        window.refreshFloatingLabels?.(panel.cont);
    };

    // Disparado al hacer clic en "Cancelar"
    panel.onCancelEdit = () => {
        toggleBodySections(false);
        window.imagePreviews?.['incomeinfo']?.reset();
    };

    // Disparado al guardar cambios ("Aceptar")
    panel.onSubmit = async () => {
        if (!currentIncomeData) return false;

        const titulo = inputTitulo?.value.trim();
        const monto = parseFloat(inputMonto?.value);
        const frecuencia = getFrecuenciaSelect();
        const fechaInicio = inputFecha?.value;
        const activo = checkActivo?.checked ? 1 : 0;

        if (!titulo) {
            alert('El título o concepto no puede estar vacío.');
            return false;
        }

        if (isNaN(monto) || monto <= 0) {
            alert('Ingresa un monto válido mayor a 0.');
            return false;
        }

        const payload = {
            titulo: titulo,
            monto: monto,
            frecuencia: frecuencia,
            fecha_inicio: fechaInicio || null,
            activo: activo
        };

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['incomeinfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto', payload.monto);
            formData.append('frecuencia', payload.frecuencia);
            if (payload.fecha_inicio) formData.append('fecha_inicio', payload.fecha_inicio);
            formData.append('activo', payload.activo);
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
            const response = await fetch(`/incomes/${currentIncomeData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar el ingreso.');
                throw new Error(errorMsg);
            }

            currentIncomeData = resData.data;

            // Actualizar vista de lectura con la respuesta del backend
            if (imgIcono) imgIcono.src = currentIncomeData.icono;
            if (txtTitulo) txtTitulo.textContent = currentIncomeData.titulo;
            if (txtMonto) txtMonto.textContent = `$${parseFloat(currentIncomeData.monto).toFixed(2)}`;
            if (txtFrecuencia) txtFrecuencia.textContent = currentIncomeData.frecuencia;
            if (txtFecha) txtFecha.textContent = currentIncomeData.fecha_f;

            renderActivoBadge(currentIncomeData.activo);
            window.imagePreviews?.['incomeinfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). El "Destino" del
            // ingreso (billetera de depósito) no es editable desde este
            // panel, así que no se toca.
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-income',
                    id: currentIncomeData.id,
                    fields: {
                        titulo: currentIncomeData.titulo,
                        icono: currentIncomeData.icono,
                        monto: formatMoneyPhp(currentIncomeData.monto)
                    }
                }
            }));

            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // Disparado al hacer clic en "Eliminar"
    panel.onDelete = async () => {
        if (!currentIncomeData) return false;

        if (!confirm(`¿Deseas eliminar el ingreso programado "${currentIncomeData.titulo}"? Ya no se generarán movimientos automáticos asociados.`)) {
            return false;
        }

        try {
            const response = await fetch(`/incomes/${currentIncomeData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar el ingreso.');
            }

            // Remover el elemento del listado en el DOM si existe
            const elementInList = document.querySelector(`[data-income-id="${currentIncomeData.id}"]`);
            if (elementInList) elementInList.remove();

            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };
});