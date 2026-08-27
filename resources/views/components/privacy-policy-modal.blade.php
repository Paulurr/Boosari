{{--
    Ventana flotante de "Política de Privacidad" (centrada, arrastrable, con cierre).
    Incluye este componente UNA sola vez en tu layout (por ejemplo justo antes de </body>
    o dentro de x-footer, ya está integrado ahí). Cualquier botón/enlace en la página puede
    abrirla llamando a la función global: togglePrivacyModal()
--}}
<div id="pv-backdrop" class="hidden fixed inset-0 z-40 bg-transparent"></div>

<div id="pv-window"
     class="hidden fixed z-50 w-[92vw] max-w-xl rounded-xl shadow-2xl overflow-hidden border border-[var(--col5)]"
     style="top: 50%; left: 50%; transform: translate(-50%, -50%);">

    {{-- Barra de título (arrastrable) --}}
    <div id="pv-titlebar"
         class="flex items-center justify-between gap-3 px-4 py-3 bg-[var(--col4)] text-[var(--col1)] cursor-move select-none">
        <div class="flex items-center gap-2 min-w-0">
            <span class="font-semibold text-sm truncate">Política de Privacidad</span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="pv-close-btn" title="Cerrar"
                    class="w-6 h-6 flex items-center justify-center rounded hover:bg-red-500 transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Contenido --}}
    <div id="pv-body" class="bg-[var(--col2)] text-[var(--col7)] px-6 py-5 overflow-y-auto" style="max-height: 65vh;">

        <div class="space-y-2">
            <h4 class="font-bold text-base text-[var(--col5)]">1. Introducción y Nuestro Compromiso</h4>
            <p class="text-sm leading-relaxed">
                En Boosari, valoramos profundamente la confianza que depositas en nuestra plataforma al compartir tu información personal y operativa. La protección de tus datos no es solo una prioridad legal o de cumplimiento, sino el pilar fundamental sobre el cual construimos nuestra relación contigo. Garantizamos que toda la información compartida por los usuarios se encuentra completamente segura gracias a estrictos protocolos tecnológicos, organizativos y legales.
            </p>
        </div>

        <div class="space-y-2 mt-5">
            <h4 class="font-bold text-base text-[var(--col5)]">2. Estándares Avanzados de Encriptación</h4>
            <p class="text-sm leading-relaxed">
                Para asegurar que tus datos sean inaccesibles para terceros no autorizados, Boosari implementa tecnologías de cifrado líderes en la industria en cada etapa del ciclo de vida de la información:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                <li><span class="font-semibold">Cifrado en tránsito:</span> Todas las comunicaciones entre tu dispositivo y nuestros servidores están protegidas mediante protocolos de seguridad robustos (SSL/TLS con los estándares criptográficos más modernos), impidiendo la interceptación de datos.</li>
                <li><span class="font-semibold">Cifrado en reposo:</span> La información almacenada en nuestras bases de datos se encuentra cifrada utilizando algoritmos avanzados de encriptación (como AES-256), lo que garantiza que, ante cualquier eventualidad física en los servidores, los datos sigan siendo ilegibles y seguros.</li>
            </ul>
        </div>

        <div class="space-y-2 mt-5">
            <h4 class="font-bold text-base text-[var(--col5)]">3. Arquitectura de Seguridad y Control de Acceso</h4>
            <p class="text-sm leading-relaxed">
                Nuestra infraestructura tecnológica está diseñada bajo el principio de seguridad por diseño:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                <li><span class="font-semibold">Control de acceso basado en roles (RBAC):</span> El acceso a los datos de los usuarios está estrictamente limitado al personal autorizado que lo requiera para el soporte o mantenimiento, bajo acuerdos de confidencialidad vinculantes y estrictas auditorías internas.</li>
                <li><span class="font-semibold">Autenticación multifactor (MFA):</span> Implementamos capas adicionales de verificación de identidad para prevenir accesos no autorizados a las cuentas de usuario y consolas administrativas.</li>
                <li><span class="font-semibold">Monitoreo continuo:</span> Contamos con sistemas automatizados de detección de anomalías y auditorías de seguridad periódicas para identificar y mitigar cualquier vulnerabilidad de manera proactiva.</li>
            </ul>
        </div>

        <div class="space-y-2 mt-5">
            <h4 class="font-bold text-base text-[var(--col5)]">4. Privacidad y Uso Responsable de la Información</h4>
            <p class="text-sm leading-relaxed">
                En estricto cumplimiento con las normativas internacionales de protección de datos personales:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                <li><span class="font-semibold">Cero comercialización:</span> Boosari nunca vende, alquila ni comercializa los datos personales o la información compartida por los usuarios con terceros con fines publicitarios o lucrativos.</li>
                <li><span class="font-semibold">Minimización de datos:</span> Solo recopilamos y procesamos aquella información que es estrictamente necesaria para el correcto funcionamiento y mejora de la experiencia en la plataforma.</li>
                <li><span class="font-semibold">Control total del usuario:</span> Como usuario, mantienes el derecho absoluto a acceder, rectificar, actualizar o solicitar la eliminación completa de tus datos personales de nuestros sistemas en cualquier momento.</li>
            </ul>
        </div>

        <div class="space-y-2 mt-5 mb-1">
            <h4 class="font-bold text-base text-[var(--col5)]">5. Actualizaciones de la Política</h4>
            <p class="text-sm leading-relaxed">
                Nos reservamos el derecho de actualizar esta política de privacidad para reflejar mejoras tecnológicas o cambios normativos. Cualquier modificación relevante será debidamente notificada a través de nuestros canales oficiales, garantizando siempre la máxima transparencia con nuestra comunidad.
            </p>
        </div>

    </div>
</div>

<script>
(function () {
    if (window.__pvModalInit) return; // evita doble inicialización si el partial se incluye más de una vez
    window.__pvModalInit = true;

    const backdrop   = document.getElementById('pv-backdrop');
    const win        = document.getElementById('pv-window');
    const titlebar   = document.getElementById('pv-titlebar');
    const body       = document.getElementById('pv-body');
    const closeBtn   = document.getElementById('pv-close-btn');

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
    window.togglePrivacyModal = function () {
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
        if (e.target.closest('button')) return; // no arrastrar si se hace clic en minimizar/cerrar
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