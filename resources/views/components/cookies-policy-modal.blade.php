{{--
    Ventana flotante de "Política de Cookies" (centrada, arrastrable, con cierre).
    Incluye este componente UNA sola vez en tu layout (por ejemplo justo antes de </body>
    o dentro de x-footer, ya está integrado ahí). Cualquier botón/enlace en la página puede
    abrirla llamando a la función global: toggleCookiesModal()
--}}
<div id="ck-backdrop" class="hidden fixed inset-0 z-40 bg-transparent"></div>

<div id="ck-window"
     class="hidden fixed z-50 w-[92vw] max-w-xl rounded-xl shadow-2xl overflow-hidden border border-[var(--col5)]"
     style="top: 50%; left: 50%; transform: translate(-50%, -50%);">

    {{-- Barra de título (arrastrable) --}}
    <div id="ck-titlebar"
         class="flex items-center justify-between gap-3 px-4 py-3 bg-[var(--col4)] text-[var(--col1)] cursor-move select-none">
        <div class="flex items-center gap-2 min-w-0">
            <span class="font-semibold text-sm truncate">Política de Cookies</span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="ck-close-btn" title="Cerrar"
                    class="w-6 h-6 flex items-center justify-center rounded hover:bg-red-500 transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Contenido --}}
    <div id="ck-body" class="bg-[var(--col2)] text-[var(--col7)] px-6 py-5 overflow-y-auto" style="max-height: 65vh;">

        <div class="space-y-2">
            <h4 class="font-bold text-base text-[var(--col5)]">Uso de Cookies</h4>
            <p class="text-sm leading-relaxed">
                Todas nuestras cookies tienen un propósito exclusivamente funcional y de seguridad. Boosari no utiliza cookies de seguimiento comercial ni comparte datos de navegación con terceros para fines publicitarios.
            </p>
        </div>

    </div>
</div>

<script>
(function () {
    if (window.__ckModalInit) return; // evita doble inicialización si el partial se incluye más de una vez
    window.__ckModalInit = true;

    const backdrop   = document.getElementById('ck-backdrop');
    const win        = document.getElementById('ck-window');
    const titlebar   = document.getElementById('ck-titlebar');
    const body       = document.getElementById('ck-body');
    const closeBtn   = document.getElementById('ck-close-btn');

    let hasBeenDragged = false;
    let isDragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;

    function openModal() {
        backdrop.classList.remove('hidden');
        win.classList.remove('hidden');
        body.classList.remove('hidden');
    }

    function closeModal() {
        backdrop.classList.add('hidden');
        win.classList.add('hidden');
    }

    // Botón/enlace externo llama a esta función global para abrir la ventana
    window.toggleCookiesModal = function () {
        if (win.classList.contains('hidden')) {
            openModal();
        } else {
            closeModal();
        }
    };

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    // --- Arrastrar la ventana como si fuera de escritorio ---
    function startDrag(clientX, clientY) {
        isDragging = true;
        const rect = win.getBoundingClientRect();

        // La primera vez que se arrastra, se pasa de "centrado con transform"
        // a coordenadas absolutas para que el arrastre sea preciso.
        if (!hasBeenDragged) {
            win.style.top = rect.top + 'px';
            win.style.left = rect.left + 'px';
            win.style.transform = 'none';
            hasBeenDragged = true;
        }

        dragOffsetX = clientX - rect.left;
        dragOffsetY = clientY - rect.top;
        document.body.style.userSelect = 'none';
    }

    function moveDrag(clientX, clientY) {
        if (!isDragging) return;
        const winRect = win.getBoundingClientRect();
        const maxLeft = window.innerWidth - winRect.width;
        const maxTop = window.innerHeight - winRect.height;

        let newLeft = clientX - dragOffsetX;
        let newTop = clientY - dragOffsetY;

        newLeft = Math.min(Math.max(newLeft, 0), Math.max(maxLeft, 0));
        newTop = Math.min(Math.max(newTop, 0), Math.max(maxTop, 0));

        win.style.left = newLeft + 'px';
        win.style.top = newTop + 'px';
    }

    function endDrag() {
        isDragging = false;
        document.body.style.userSelect = '';
    }

    // Mouse
    titlebar.addEventListener('mousedown', (e) => {
        if (e.target.closest('button')) return; // no arrastrar si se hace clic en cerrar
        startDrag(e.clientX, e.clientY);
    });
    document.addEventListener('mousemove', (e) => moveDrag(e.clientX, e.clientY));
    document.addEventListener('mouseup', endDrag);

    // Touch (móvil / tablet)
    titlebar.addEventListener('touchstart', (e) => {
        if (e.target.closest('button')) return;
        const t = e.touches[0];
        startDrag(t.clientX, t.clientY);
    }, { passive: true });
    document.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const t = e.touches[0];
        moveDrag(t.clientX, t.clientY);
    }, { passive: true });
    document.addEventListener('touchend', endDrag);
})();
</script>