/**
 * Panel: motor GENÉRICO y escalable para modales.
 *
 * Responsabilidad única de esta clase:
 *   1. Abrir/cerrar el contenedor del modal (animación de opacidad).
 *   2. En modales con modo Edición/Lectura (prefix 'info-'), decidir
 *      qué botones del FOOTER se muestran u ocultan:
 *        - Modo lectura  -> Salir, Eliminar, Editar
 *        - Modo edición  -> Cancelar, Aceptar (Aceptar = botón submit)
 *
 * Esta clase NO sabe nada de datos, fetch, validaciones ni de qué hay
 * dentro del cuerpo del panel. Toda esa lógica individual vive en el
 * script propio de cada panel (ej. info_wallet.js), que se conecta
 * mediante los callbacks: onOpen, onClose, onEdit, onCancelEdit,
 * onSubmit, onDelete.
 */
class Panel {
    constructor(name, options = {}) {
        this.name = name;
        this.entity = name.replace(/^info-/, '');
        this.cont = document.getElementById(`${name}-cont`);
        if (!this.cont) return;

        this.hasEditMode = options.hasEditMode !== undefined
            ? options.hasEditMode
            : name.startsWith('info-');

        // Estados
        this.isOpen = false;
        this.isAnimating = false;
        this.isEditing = false;
        this.trigger = null; // elemento que disparó la apertura (ej. botón "Detalles")

        // Callbacks: el panel específico decide QUÉ hacer,
        // Panel solo decide CUÁNDO llamarlos. Por defecto no hacen nada
        // "real" (sin funcionalidad de negocio), tal como se pidió.
        this.onOpen = options.onOpen || (() => {});
        this.onClose = options.onClose || (() => {});
        this.onEdit = options.onEdit || (() => {});
        this.onCancelEdit = options.onCancelEdit || (() => {});
        this.onSubmit = options.onSubmit || (async () => true);
        this.onDelete = options.onDelete || (async () => confirm(`¿Estás seguro de eliminar este registro de ${this.entity}?`));

        // Estilos iniciales para animación
        this.cont.style.opacity = "0";
        this.cont.style.transition = "opacity 0.2s ease";

        this._initEvents();
    }

    open(trigger = null) {
        if (this.isOpen || this.isAnimating) return;

        this.trigger = trigger;
        this.isAnimating = true;
        this.cont.style.display = "flex";

        setTimeout(() => {
            this.cont.style.opacity = "1";
        }, 50);

        setTimeout(() => {
            this.isOpen = true;
            this.isAnimating = false;
            this.onOpen(this.trigger);
        }, 200);
    }

    close() {
        if (!this.isOpen || this.isAnimating) return;

        this.isAnimating = true;
        this.cont.style.opacity = "0";

        setTimeout(() => {
            this.cont.style.display = "none";
            this.isOpen = false;
            this.isAnimating = false;

            // Restablece a modo lectura al cerrar
            if (this.hasEditMode && this.isEditing) {
                this.setEditMode(false);
            }

            this.onClose();
        }, 200);
    }

    handleCloseOrCancel() {
        if (this.hasEditMode && this.isEditing) {
            this.setEditMode(false);
        } else {
            this.close();
        }
    }

    /**
     * ÚNICO trabajo "real" de esta clase: decidir qué botones del
     * footer se muestran. No toca el cuerpo del panel.
     */
    setEditMode(isEditing) {
        if (!this.hasEditMode) return;

        this.isEditing = isEditing;

        // Botones de Modo Lectura: (Salir, Eliminar, Editar)
        // Se OCULTAN cuando isEditing === true
        const readModeBtns = this.cont.querySelectorAll(
            `.modal-close, .modal-btn-delete, .modal-btn-edit, .${this.name}-delete, .${this.name}-edit`
        );
        readModeBtns.forEach(btn => btn.classList.toggle('hidden', isEditing));

        // Botones de Modo Edición: (Cancelar, Aceptar)
        // Se MUESTRAN cuando isEditing === true. "Aceptar" es el botón submit.
        const editModeBtns = this.cont.querySelectorAll(
            `.modal-btn-cancel, .modal-btn-submit, .${this.name}-cancelEdit, .${this.name}-submit`
        );
        editModeBtns.forEach(btn => btn.classList.toggle('hidden', !isEditing));

        // Delega al panel específico qué hacer con SU contenido
        if (isEditing) {
            this.onEdit();
        } else {
            this.onCancelEdit();
        }
    }

    /**
     * Ejecuta el onSubmit del panel específico y, si tiene éxito,
     * regresa a modo lectura (paneles con edición) o cierra (modales
     * simples). Centralizado aquí para que tanto el clic en "Aceptar"
     * como la tecla Enter usen exactamente el mismo flujo.
     */
    async _trySubmit() {
        const success = await this.onSubmit();
        if (success) {
            if (this.hasEditMode) {
                this.setEditMode(false);
            } else {
                this.close();
            }
        }
        return success;
    }

    _initEvents() {
        // Botones de Apertura externos
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest(`.${this.name}-btn`);
            if (trigger) {
                e.preventDefault();
                this.open(trigger);
            }
        });

        // Botones externos de Cierre (ej: 'X' superior o overlay)
        const closeBtns = document.querySelectorAll(`.${this.name}-close`);
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Si es un botón del footer, se procesa en el evento click interno del contenedor
                if (btn.classList.contains('modal-close') && this.cont.contains(btn)) return;

                e.preventDefault();
                this.handleCloseOrCancel();
            });
        });

        // Tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen && !this.isAnimating) {
                this.handleCloseOrCancel();
            }
        });

        // Tecla ENTER -> dispara el mismo flujo que el botón "Aceptar"/submit.
        // - En modales con modo edición: solo cuenta si isEditing === true
        //   (en modo lectura, Enter no debe hacer nada).
        // - En modales simples (sin modo edición): siempre intenta enviar.
        // Se ignora dentro de <textarea> (para permitir saltos de línea) y
        // mientras se está componiendo texto con IME.
        this.cont.addEventListener('keydown', async (e) => {
            if (e.key !== 'Enter' || e.shiftKey || e.isComposing) return;
            if (!this.isOpen || this.isAnimating) return;
            if (e.target.tagName === 'TEXTAREA') return;
            if (this.hasEditMode && !this.isEditing) return;

            e.preventDefault();
            await this._trySubmit();
        });

        // Delegación de eventos dentro del contenedor del Modal
        this.cont.addEventListener('click', async (e) => {
            if (!this.hasEditMode) {
                // Modal simple
                if (e.target.closest('.modal-close, .' + this.name + '-close')) {
                    e.preventDefault();
                    this.close();
                    return;
                }
                if (e.target.closest('.modal-btn-submit, .' + this.name + '-submit')) {
                    e.preventDefault();
                    await this._trySubmit();
                    return;
                }
                return;
            }

            // --- MODO LECTURA / EDICIÓN ---

            // 1. Clic en Editar -> Activa Modo Edición (isEditing = true)
            if (e.target.closest(`.modal-btn-edit, .${this.name}-edit`)) {
                e.preventDefault();
                this.setEditMode(true);
                return;
            }

            // 2. Clic en Cancelar -> Regresa a Modo Lectura (isEditing = false)
            if (e.target.closest(`.modal-btn-cancel, .${this.name}-cancelEdit`)) {
                e.preventDefault();
                this.setEditMode(false);
                return;
            }

            // 3. Clic en Aceptar (submit) -> Envía y si tiene éxito regresa a Modo Lectura
            if (e.target.closest(`.modal-btn-submit, .${this.name}-submit`)) {
                e.preventDefault();
                await this._trySubmit();
                return;
            }

            // 4. Clic en Eliminar -> Confirma y elimina
            if (e.target.closest(`.modal-btn-delete, .${this.name}-delete`)) {
                e.preventDefault();
                const success = await this.onDelete();
                if (success) {
                    this.close();
                }
                return;
            }

            // 5. Clic en Salir (botón .modal-close)
            if (e.target.closest('.modal-close')) {
                e.preventDefault();
                if (this.isEditing) {
                    this.setEditMode(false);
                } else {
                    this.close();
                }
                return;
            }
        });
    }
}


window.Panel = Panel;
window.panels = window.panels || {};

function registerPanel(name, options = {}) {
    const instance = new Panel(name, options);
    window.panels[name] = instance;
    return instance;
}
window.registerPanel = registerPanel;

window.wallet_panel = registerPanel("info-wallet");
window.info_income_panel = registerPanel("info-income");
window.transaction_panel = registerPanel("info-transaction");
window.info_goal_panel = registerPanel("info-goal");
window.info_debt_panel = registerPanel("info-debt");
window.info_paymentGoal_panel = registerPanel("info-paymentGoal");
window.info_paymentDebt_panel = registerPanel("info-paymentDebt");
window.info_investment_panel = registerPanel("info-investment");
window.add_panel = registerPanel("add");
window.filter_panel = registerPanel("filter");
