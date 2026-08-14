/**
 * Lógica propia de la página de Perfil.
 *
 * "Información de la cuenta" y "Contraseña" son secciones inline en la
 * misma página (lectura <-> edición con Editar/Cancelar/Enviar), NO
 * modales — se manejan con un toggle simple de mostrar/ocultar.
 *
 * La Zona de peligro SÍ son modales (x-panel), así que se registran
 * con window.registerPanel, igual que cualquier otro panel de la app.
 */
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    async function apiRequest(url, method, body = null) {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: body ? JSON.stringify(body) : null,
        });

        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    }

    function primerError(data, fallback) {
        if (data?.message) return data.message;
        if (data?.errors) {
            const primero = Object.values(data.errors)[0];
            if (Array.isArray(primero)) return primero[0];
        }
        return fallback;
    }

    function mostrarError(scope, mensaje) {
        const span = scope.querySelector('[data-error]');
        if (!span) {
            alert(mensaje);
            return;
        }
        span.textContent = mensaje;
        span.parentElement.classList.remove('hidden');
    }

    function limpiarError(scope) {
        const span = scope.querySelector('[data-error]');
        if (!span) return;
        span.textContent = '';
        span.parentElement.classList.add('hidden');
    }

    function sincronizarNombreCorreo(name, email) {
        document.querySelectorAll('.profile-name-display').forEach(el => { el.textContent = name; });
        document.querySelectorAll('.profile-email-display').forEach(el => { el.textContent = email; });
        document.querySelectorAll('.profile-avatar-initial').forEach(el => { el.textContent = name.charAt(0).toUpperCase(); });

        const nameRead = document.getElementById('info-profile-name-read');
        const emailRead = document.getElementById('info-profile-email-read');
        if (nameRead) nameRead.textContent = name;
        if (emailRead) emailRead.textContent = email;
    }

    // Toggle genérico lectura <-> edición para una sección inline
    // (no modal): recibe los ids de los bloques y botones involucrados.
    function initInlineToggle({ readBlockId, formId, editBtnId, cancelBtnId, errorScopeId, onSubmit }) {
        const readBlock = document.getElementById(readBlockId);
        const form = document.getElementById(formId);
        const editBtn = document.getElementById(editBtnId);
        const cancelBtn = document.getElementById(cancelBtnId);
        const errorScope = document.getElementById(errorScopeId);
        if (!readBlock || !form || !editBtn || !cancelBtn) return;

        function mostrarLectura() {
            limpiarError(errorScope);
            form.reset();
            form.style.display = 'none';
            readBlock.style.display = 'flex';
        }

        function mostrarEdicion() {
            limpiarError(errorScope);
            readBlock.style.display = 'none';
            form.style.display = 'flex';
            form.querySelector('input')?.focus();
        }

        editBtn.addEventListener('click', mostrarEdicion);
        cancelBtn.addEventListener('click', mostrarLectura);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            limpiarError(errorScope);

            const exito = await onSubmit(form);
            if (exito) {
                mostrarLectura();
            }
        });
    }

    // --- Información de la cuenta ---
    initInlineToggle({
        readBlockId: 'info-profile-read',
        formId: 'info-profile-form',
        editBtnId: 'info-profile-edit-btn',
        cancelBtnId: 'info-profile-cancel-btn',
        errorScopeId: 'info-profile-error',
        onSubmit: async (form) => {
            const { ok, data } = await apiRequest(form.dataset.action, 'PUT', {
                name: form.querySelector('[name="info-profile-name"]').value,
                email: form.querySelector('[name="info-profile-email"]').value,
            });

            if (!ok) {
                mostrarError(document.getElementById('info-profile-error'), primerError(data, 'No se pudo actualizar la información.'));
                return false;
            }

            sincronizarNombreCorreo(data.data.name, data.data.email);
            return true;
        },
    });

    // --- Modal: eliminar todos los registros ---
    const resetPanelEl = document.getElementById('confirm-reset-cont');
    if (resetPanelEl && window.registerPanel) {
        window.registerPanel('confirm-reset', {
            onSubmit: async () => {
                limpiarError(resetPanelEl);
                const input = resetPanelEl.querySelector('[name="reset_confirm_password"]');

                const { ok, data } = await apiRequest(resetPanelEl.dataset.action, 'DELETE', {
                    confirm_password: input.value,
                });

                if (!ok) {
                    mostrarError(resetPanelEl, primerError(data, 'No se pudo completar la eliminación.'));
                    return false;
                }

                window.location.reload();
                return true;
            },
        });
    }

    // --- Modal: eliminar cuenta ---
    const deletePanelEl = document.getElementById('confirm-delete-account-cont');
    if (deletePanelEl && window.registerPanel) {
        window.registerPanel('confirm-delete-account', {
            onSubmit: async () => {
                limpiarError(deletePanelEl);
                const input = deletePanelEl.querySelector('[name="delete_confirm_password"]');

                const { ok, data } = await apiRequest(deletePanelEl.dataset.action, 'DELETE', {
                    confirm_password: input.value,
                });

                if (!ok) {
                    mostrarError(deletePanelEl, primerError(data, 'No se pudo eliminar la cuenta.'));
                    return false;
                }

                window.location.href = data.redirect || '/';
                return true;
            },
        });
    }
});