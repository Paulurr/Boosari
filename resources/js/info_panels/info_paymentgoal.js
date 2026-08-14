document.addEventListener('DOMContentLoaded', () => {

    // Instancia creada por paneles.js para "info-paymentGoal"
    const panel = window.info_paymentGoal_panel || window.panels?.['info-paymentGoal'];
    if (!panel) return;

    let currentPaymentGoalData = null;

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales, para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const statusMsg = document.getElementById('paymentGoal-status');
    const detailsBox = document.getElementById('paymentGoal-details');

    const imgIcono = document.getElementById('paymentGoal-icono');

    const txtGoalTitulo = document.getElementById('paymentGoal-goal-titulo');
    const txtTitulo = document.getElementById('paymentGoal-titulo');
    const txtMonto = document.getElementById('paymentGoal-monto');
    const txtFecha = document.getElementById('paymentGoal-fecha');
    const txtWallet = document.getElementById('paymentGoal-wallet');

    const inputTitulo = document.getElementById('paymentGoal-titulo-input');
    const inputMonto = document.getElementById('paymentGoal-monto-input');
    const selectWalletValue = document.getElementById('paymentGoalinfo-wallet-select-value');
    const selectWalletTit = document.getElementById('paymentGoalinfo-wallet-select-tit');

    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-paymentGoal-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-paymentGoal-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function setWalletSelect(walletId, walletNombre) {
        if (!selectWalletValue || !selectWalletTit) return;
        selectWalletValue.value = walletId || '';
        selectWalletTit.textContent = walletId ? (walletNombre || 'Billetera') : 'Externa';
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    panel.onOpen = (trigger) => {
        const paymentId = trigger?.dataset?.id;
        if (!paymentId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información del abono...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/payment-goals/${paymentId}/info`, {
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
            currentPaymentGoalData = res.data;

            if (imgIcono) imgIcono.src = currentPaymentGoalData.icono;
            if (txtGoalTitulo) txtGoalTitulo.textContent = currentPaymentGoalData.goal_titulo;
            if (txtTitulo) txtTitulo.textContent = currentPaymentGoalData.titulo;
            if (txtMonto) txtMonto.textContent = `$${currentPaymentGoalData.monto}`;
            if (txtFecha) txtFecha.textContent = currentPaymentGoalData.fecha;
            if (txtWallet) txtWallet.textContent = currentPaymentGoalData.wallet;

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
        if (!currentPaymentGoalData) return;
        if (inputTitulo) inputTitulo.value = currentPaymentGoalData.titulo;
        if (inputMonto) inputMonto.value = currentPaymentGoalData.monto;
        setWalletSelect(currentPaymentGoalData.wallet_id, currentPaymentGoalData.wallet);
        toggleBodySections(true);

        window.imagePreviews?.['paymentGoalinfo']?.setPreview(currentPaymentGoalData.icono);
        window.imagePreviews?.['paymentGoalinfo']?.clearFile();

        window.refreshFloatingLabels?.(panel.cont);
    };

    panel.onCancelEdit = () => {
        toggleBodySections(false);
        window.imagePreviews?.['paymentGoalinfo']?.reset();
    };

    panel.onSubmit = async () => {
        if (!currentPaymentGoalData) return false;

        const titulo = inputTitulo?.value.trim();
        const monto = parseFloat(inputMonto?.value);
        const walletId = selectWalletValue?.value || null;

        if (!titulo) {
            alert('El concepto del abono no puede estar vacío.');
            return false;
        }
        if (isNaN(monto) || monto <= 0) {
            alert('Ingresa un monto válido mayor a 0.');
            return false;
        }

        const payload = { titulo, monto, wallet_id: walletId };

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['paymentGoalinfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto', payload.monto);
            if (payload.wallet_id) formData.append('wallet_id', payload.wallet_id);
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
            const response = await fetch(`/payment-goals/${currentPaymentGoalData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar el abono.');
                throw new Error(errorMsg);
            }

            currentPaymentGoalData = resData.data;

            if (imgIcono) imgIcono.src = currentPaymentGoalData.icono;
            if (txtTitulo) txtTitulo.textContent = currentPaymentGoalData.titulo;
            if (txtMonto) txtMonto.textContent = `$${currentPaymentGoalData.monto}`;
            if (txtWallet) txtWallet.textContent = currentPaymentGoalData.wallet;
            window.imagePreviews?.['paymentGoalinfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). El "Meta:" (origen)
            // no cambia, ya que la meta asociada no es editable aquí.
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-paymentGoal',
                    id: currentPaymentGoalData.id,
                    fields: {
                        titulo: currentPaymentGoalData.titulo,
                        icono: currentPaymentGoalData.icono,
                        monto: formatMoneyPhp(currentPaymentGoalData.monto)
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
        if (!currentPaymentGoalData) return false;

        if (!confirm(`¿Deseas eliminar el abono "${currentPaymentGoalData.titulo}"? Esto ajustará de vuelta el saldo de la meta (y de la billetera de origen, si aplicó).`)) {
            return false;
        }

        try {
            const response = await fetch(`/payment-goals/${currentPaymentGoalData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar el abono.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };
});