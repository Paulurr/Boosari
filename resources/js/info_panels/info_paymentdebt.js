document.addEventListener('DOMContentLoaded', () => {

    // Instancia creada por paneles.js para "info-paymentDebt"
    const panel = window.info_paymentDebt_panel || window.panels?.['info-paymentDebt'];
    if (!panel) return;

    let currentPaymentDebtData = null;

    // Replica el formato de PHP number_format($n, 2): separador de miles ','
    // y siempre 2 decimales, para que el texto de la tarjeta del home
    // (record_card_sync.js) coincida con lo que renderiza Blade.
    function formatMoneyPhp(value) {
        const num = parseFloat(value) || 0;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const statusMsg = document.getElementById('paymentDebt-status');
    const detailsBox = document.getElementById('paymentDebt-details');

    const imgIcono = document.getElementById('paymentDebt-icono');

    const txtDebtTitulo = document.getElementById('paymentDebt-debt-titulo');
    const txtTitulo = document.getElementById('paymentDebt-titulo');
    const txtMonto = document.getElementById('paymentDebt-monto');
    const txtFecha = document.getElementById('paymentDebt-fecha');
    const txtWallet = document.getElementById('paymentDebt-wallet');
    const badgePagoMinimo = document.getElementById('paymentDebt-minimo-badge');

    const inputTitulo = document.getElementById('paymentDebt-titulo-input');
    const inputMonto = document.getElementById('paymentDebt-monto-input');
    const checkPagoMinimo = document.getElementById('paymentDebt-minimo-input');
    const selectWalletValue = document.getElementById('paymentDebtinfo-wallet-select-value');
    const selectWalletTit = document.getElementById('paymentDebtinfo-wallet-select-tit');

    function toggleBodySections(isEditing) {
        document.querySelectorAll('#info-paymentDebt-cont .modal-read-only').forEach(el => {
            el.classList.toggle('hidden', isEditing);
        });
        document.querySelectorAll('#info-paymentDebt-cont .modal-edit-only').forEach(el => {
            el.classList.toggle('hidden', !isEditing);
        });
    }

    function setWalletSelect(walletId, walletNombre) {
        if (!selectWalletValue || !selectWalletTit) return;
        selectWalletValue.value = walletId || '';
        selectWalletTit.textContent = walletId ? (walletNombre || 'Billetera') : 'Externa';
    }

    function renderMinimoBadge(esMinimo) {
        if (!badgePagoMinimo) return;
        if (esMinimo) {
            badgePagoMinimo.textContent = 'Pago Mínimo';
            badgePagoMinimo.classList.remove('hidden');
        } else {
            badgePagoMinimo.textContent = '';
            badgePagoMinimo.classList.add('hidden');
        }
    }

    // =====================================================================
    // CALLBACKS delegados a paneles.js
    // =====================================================================

    panel.onOpen = (trigger) => {
        const paymentId = trigger?.dataset?.id;
        if (!paymentId) return;

        if (statusMsg) {
            statusMsg.textContent = 'Cargando información del pago...';
            statusMsg.className = 'text-center col3 py-8 block';
            statusMsg.classList.remove('hidden');
        }
        detailsBox?.classList.add('hidden');

        fetch(`/payment-debts/${paymentId}/info`, {
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
            currentPaymentDebtData = res.data;

            if (imgIcono) imgIcono.src = currentPaymentDebtData.icono;
            if (txtDebtTitulo) txtDebtTitulo.textContent = currentPaymentDebtData.debt_titulo;
            if (txtTitulo) txtTitulo.textContent = currentPaymentDebtData.titulo;
            if (txtMonto) txtMonto.textContent = `$${currentPaymentDebtData.monto}`;
            if (txtFecha) txtFecha.textContent = currentPaymentDebtData.fecha;
            if (txtWallet) txtWallet.textContent = currentPaymentDebtData.wallet;

            renderMinimoBadge(currentPaymentDebtData.pago_minimo);

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
        if (!currentPaymentDebtData) return;
        if (inputTitulo) inputTitulo.value = currentPaymentDebtData.titulo;
        if (inputMonto) inputMonto.value = currentPaymentDebtData.monto;
        if (checkPagoMinimo) checkPagoMinimo.checked = Boolean(currentPaymentDebtData.pago_minimo);
        setWalletSelect(currentPaymentDebtData.wallet_id, currentPaymentDebtData.wallet);
        toggleBodySections(true);

        window.imagePreviews?.['paymentDebtinfo']?.setPreview(currentPaymentDebtData.icono);
        window.imagePreviews?.['paymentDebtinfo']?.clearFile();

        window.refreshFloatingLabels?.(panel.cont);
    };

    panel.onCancelEdit = () => {
        toggleBodySections(false);
        window.imagePreviews?.['paymentDebtinfo']?.reset();
    };

    panel.onSubmit = async () => {
        if (!currentPaymentDebtData) return false;

        const titulo = inputTitulo?.value.trim();
        const monto = parseFloat(inputMonto?.value);
        const walletId = selectWalletValue?.value || null;
        const pagoMinimo = checkPagoMinimo?.checked ? 1 : 0;

        if (!titulo) {
            alert('El concepto del pago no puede estar vacío.');
            return false;
        }
        if (isNaN(monto) || monto <= 0) {
            alert('Ingresa un monto válido mayor a 0.');
            return false;
        }

        const payload = { titulo, monto, wallet_id: walletId, pago_minimo: pagoMinimo };

        // Si el usuario eligió un nuevo icono, hay que mandarlo como
        // multipart/form-data (los archivos no viajan en JSON). Laravel no
        // interpreta bien un PUT con archivos, así que se envía como POST
        // con el campo "_method" para que el framework lo trate como PUT.
        const nuevoIcono = window.imagePreviews?.['paymentDebtinfo']?.getFile();

        let fetchOptions;
        if (nuevoIcono) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('titulo', payload.titulo);
            formData.append('monto', payload.monto);
            if (payload.wallet_id) formData.append('wallet_id', payload.wallet_id);
            formData.append('pago_minimo', payload.pago_minimo);
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
            const response = await fetch(`/payment-debts/${currentPaymentDebtData.id}`, fetchOptions);

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = resData.message || (resData.errors ? Object.values(resData.errors).flat().join('\n') : 'Error al actualizar el pago.');
                throw new Error(errorMsg);
            }

            currentPaymentDebtData = resData.data;

            if (imgIcono) imgIcono.src = currentPaymentDebtData.icono;
            if (txtTitulo) txtTitulo.textContent = currentPaymentDebtData.titulo;
            if (txtMonto) txtMonto.textContent = `$${currentPaymentDebtData.monto}`;
            if (txtWallet) txtWallet.textContent = currentPaymentDebtData.wallet;
            renderMinimoBadge(currentPaymentDebtData.pago_minimo);
            window.imagePreviews?.['paymentDebtinfo']?.clearFile();

            // Sincroniza la tarjeta del listado (home). El "Deuda:" (origen)
            // no cambia, ya que la deuda asociada no es editable aquí.
            window.dispatchEvent(new CustomEvent('record:updated', {
                detail: {
                    name: 'info-paymentDebt',
                    id: currentPaymentDebtData.id,
                    fields: {
                        titulo: currentPaymentDebtData.titulo,
                        icono: currentPaymentDebtData.icono,
                        monto: formatMoneyPhp(currentPaymentDebtData.monto)
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
        if (!currentPaymentDebtData) return false;

        if (!confirm(`¿Deseas eliminar el pago "${currentPaymentDebtData.titulo}"? Esto ajustará de vuelta el saldo de la deuda (y de la billetera de origen, si aplicó).`)) {
            return false;
        }

        try {
            const response = await fetch(`/payment-debts/${currentPaymentDebtData.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const resData = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(resData.message || 'No se pudo eliminar el pago.');
            }

            location.reload();
            return true;
        } catch (err) {
            alert(err.message);
            return false;
        }
    };
});