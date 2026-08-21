
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('search-users-form');
    const searchInput = document.getElementById('search-users-input');
    const resultsEl = document.getElementById('users-results');
    const filtersEl = document.getElementById('role-filters');
 
    if (!resultsEl) return;
 
    const baseUrl = searchForm ? searchForm.action : window.location.pathname;
    let lastRoleFilter = filtersEl ? (filtersEl.dataset.currentRole || '') : '';
 
    function currentRole() {
        return filtersEl ? (filtersEl.dataset.currentRole || '') : '';
    }
 
    function buildUrl(role, search) {
        const params = new URLSearchParams();
        if (role !== '') params.set('role', role);
        if (search !== '') params.set('search', search);
        const qs = params.toString();
        return qs ? `${baseUrl}?${qs}` : baseUrl;
    }
 
    function aplicarFiltroActivo(roleFilter) {
        if (!filtersEl) return;
        filtersEl.querySelectorAll('.role-filter-link').forEach((link) => {
            const activo = (link.dataset.role || '') === (roleFilter || '');
            if (!activo) {
                link.querySelectorAll('.focus-button').forEach((el) => el.classList.remove('focus-button'));
                return;
            }
            // Si el server no puso .focus-button en ningún hijo (o algún
            // script externo se lo quitó), lo forzamos en el primer hijo
            // real del <a>, que es el nodo raíz de <x-button>.
            const target = link.querySelector('.focus-button') || link.firstElementChild;
            target?.classList.add('focus-button');
        });
    }
 
    function actualizarFiltroActivo(roleFilter) {
        if (!filtersEl) return;
        filtersEl.dataset.currentRole = roleFilter ?? '';
        lastRoleFilter = roleFilter ?? '';
        aplicarFiltroActivo(lastRoleFilter);
        // Blindaje extra: reaplicamos en el siguiente frame por si algún otro
        // script (p.ej. un efecto de click del propio x-button) pisa la clase
        // justo después de que termina este handler.
        requestAnimationFrame(() => aplicarFiltroActivo(lastRoleFilter));
    }
 
    async function loadUsers(url, { pushState = true } = {}) {
        resultsEl.classList.add('opacity-50', 'pointer-events-none');
 
        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
 
            if (!res.ok) {
                throw new Error('Respuesta no válida del servidor');
            }
 
            const data = await res.json();
 
            resultsEl.innerHTML = data.html;
            actualizarFiltroActivo(data.roleFilter ?? '');
 
            if (pushState) {
                window.history.pushState({}, '', url);
            }
        } catch (e) {
            // Fallback: si el AJAX falla, navegamos normal para no dejar la UI rota.
            window.location.href = url;
        } finally {
            resultsEl.classList.remove('opacity-50', 'pointer-events-none');
        }
    }
 
    // --- Buscador ---
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const search = (searchInput?.value || '').trim();
            loadUsers(buildUrl(currentRole(), search));
        });
    }
 
    // --- Filtros por rol (fase de captura, ver nota arriba) ---
    if (filtersEl) {
        filtersEl.querySelectorAll('.role-filter-link').forEach((link) => {
            link.addEventListener(
                'click',
                (e) => {
                    e.preventDefault();
                    e.stopPropagation(); // corta la captura antes de llegar al botón interno
                    const role = link.dataset.role || '';
                    const search = (searchInput?.value || '').trim();
                    loadUsers(buildUrl(role, search));
                },
                true // <- fase de captura
            );
        });
    }
 
    // --- Paginación (delegado: el contenido de #users-results se reemplaza) ---
    resultsEl.addEventListener(
        'click',
        (e) => {
            const link = e.target.closest('#users-pagination a');
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            loadUsers(link.href);
        },
        true
    );
 
    // --- Botones atrás/adelante del navegador ---
    window.addEventListener('popstate', () => {
        loadUsers(window.location.href, { pushState: false });
    });
 
    // Estado inicial (por si el server no lo dejó consistente al cargar)
    aplicarFiltroActivo(lastRoleFilter);
});
 