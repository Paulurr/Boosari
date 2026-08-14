document.addEventListener('DOMContentLoaded', () => {

    // Se obtiene la instancia del panel creada por paneles.js
    const panel = window['info-transaction_panel'] || window.info_transaction_panel || window.transaction_panel;
    if (!panel) return;

    let currentTransactionData = null;

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
    const statusMsg = document.getElementById('transaction-status');
    const detailsBox = document.getElementById('transaction-details');

    const imgIcono = document.getElementById('transaction-icono');

    const txtTitulo = document.getElementById('transaction-titulo');
    const inputTitulo = document.getElementById('transaction-titulo-input');

    const txtMonto = document.getElementById('transaction-monto');
    const inputMonto = document.getElementById('transaction-monto-input');

    const txtCategoria = document.getElementById('transaction-categoria');
    const inputCategoria = document.getElementById('transaction-categoria-input');

    const txtTipo = document.getElementById('transaction-tipo');
    const txtOrigen = document.getElementById('transaction-origen');
    const txtDestino = document.getElementById('transaction-destino');
    const txtFecha = document.getElementById('transaction-fecha');

    // Alternar visibilidad entre lectura y edición
    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-transaction-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-transaction-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    // Estilos dinámicos para el badge según el tipo de movimiento
    function applyTipoBadgeStyle(tipoRaw) {
        if (!txtTipo) return;
        txtTipo.className = 'px-2 py-1 text-xs border rounded-full font-semibold uppercase ';
        if (tipoRaw === 'ingreso') {
            txtTipo.classList.add('text-green-600', 'border-green-600');
        } else if (tipoRaw === 'egreso' || tipoRaw === 'gasto') {
            txtTipo.classList.add('text-red-500', 'border-red-500');
        } else {
            txtTipo.classList.add('text-blue-500', 'border-blue-500');
        }
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    // Disparado al abrir el panel desde un gatillo (ej: clic en un registro)
    panel.onOpen = (trigger) => {
        const transactionId = trigger?.dataset?.id || trigger;
        if (!transactionId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/info/transaction/${transactionId}`, {
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
            currentTransactionData = res.data;

            // Renderizar datos de lectura
            if (imgIcono) imgIcono.src = currentTransactionData.icono;
            if (txtTitulo) txtTitulo.textContent = currentTransactionData.titulo;
            if (txtTipo) txtTipo.textContent = currentTransactionData.tipo;
            if (txtMonto) txtMonto.textContent = `$${parseFloat(currentTransactionData.monto).toFixed(2)}`;
            if (txtCategoria) txtCategoria.textContent = currentTransactionData.categoria;
            if (txtOrigen) txtOrigen.textContent = currentTransactionData.origen_nombre;
            if (txtDestino) txtDestino.textContent = currentTransactionData.destino_nombre;
            if (txtFecha) txtFecha.textContent = currentTransactionData.fecha;

            applyTipoBadgeStyle(currentTransactionData.tipo_raw);

            statusMsg?.classList.add('hidden');
            detailsBox?.classList.remove('hidden');

            toggleBodySections(false);
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

    // Disparado al hacer clic en "Editar"
    panel.onEdit = () => {
        if (!currentTransactionData) return;

        if (inputTitulo) inputTitulo.value = currentTransactionData.titulo;
        if (inputMonto) inputMonto.value = currentTransactionData.monto;
        if (inputCategoria) {
            inputCategoria.value = currentTransactionData.categoria === 'Sin categoría' ? '' : currentTransactionData.categoria;
        }

        toggleBodySections(true);

        // Precarga el icono actual en el componente de imagen del modo edición
        // y limpia cualquier archivo que hubiera quedado seleccionado de una
        // edición anterior.
        window.imagePreviews?.['transactioninfo']?.setPreview(currentTransactionData.icono);
        window.imagePreviews?.['transactioninfo']?.clearFile();
    };

    // Disparado al hacer clic en "Cancelar"
    panel.onCancelEdit = () => {
        toggleBodySections(false);
        window.imagePreviews?.['transactioninfo']?.reset();
    };

    // Disparado al hacer clic en "Aceptar" (guardar cambios)
    panel.onSubmit = async () => {
        if (!currentTransactionData) return false;

        const titulo = inputTitulo?.value.trim();
        const monto = parseFloat(inputMonto?.value);
        const categoria = inputCategoria?.value.trim();

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
            categoria: categoria
        };

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['transactioninfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto', payload.monto);
            if (payload.categoria) formData.append('categoria', payload.categoria);
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
            const response = await fetch(`/info/transaction/${currentTransactionData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar el movimiento.');
                throw new Error(errorMsg);
            }

            currentTransactionData = resData.data;

            // Actualizar textos de lectura con los nuevos datos devueltos
            if (imgIcono) imgIcono.src = currentTransactionData.icono;
            if (txtTitulo) txtTitulo.textContent = currentTransactionData.titulo;
            if (txtMonto) txtMonto.textContent = `$${parseFloat(currentTransactionData.monto).toFixed(2)}`;
            if (txtCategoria) txtCategoria.textContent = currentTransactionData.categoria;
            window.imagePreviews?.['transactioninfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). Origen/Destino
            // (billeteras) no son editables desde este panel.
            const categoriaCard = (currentTransactionData.categoria && currentTransactionData.categoria !== 'Sin categoría')
                ? `Categoria: ${capitalize(currentTransactionData.categoria)}`
                : '';

            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-transaction',
                    id: currentTransactionData.id,
                    fields: {
                        titulo: currentTransactionData.titulo,
                        icono: currentTransactionData.icono,
                        monto: formatMoneyPhp(currentTransactionData.monto),
                        categoria: categoriaCard
                    }
                }
            }));

            toggleBodySections(false);
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };

    // Disparado al hacer clic en "Eliminar"
    panel.onDelete = async () => {
        if (!currentTransactionData) return false;

        if (!confirm(`¿Deseas eliminar el movimiento "${currentTransactionData.titulo}"? Esta acción no se puede deshacer.`)) {
            return false;
        }

        try {
            const response = await fetch(`/info/transaction/${currentTransactionData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar el movimiento.');
            }

            // Opcional: Eliminar la fila o tarjeta del DOM si tiene data-id
            const elementInList = document.querySelector(`[data-id="${currentTransactionData.id}"]`);
            if (elementInList) elementInList.remove();

            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };
});