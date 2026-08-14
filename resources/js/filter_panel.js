document.addEventListener('DOMContentLoaded', () => {

    // Instancia del panel "filter" creada por paneles.js (registerPanel("filter"))
    const panel = window.filter_panel || window.panels?.['filter'];

    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const filterForm = document.getElementById('filter-form');
    const recordsContainer = document.getElementById('records-container');

    if (!searchForm || !filterForm || !recordsContainer) return;

    const BASE_URL = searchForm.getAttribute('action') || window.location.pathname;
    const STORAGE_KEY = 'selected_filters';

    // =====================================================================
    // Persistencia en localStorage (tomado del filter_panel.js original,
    // para que los filtros sigan "recordándose" entre visitas)
    // =====================================================================

    function getStorageFilters() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : { sort_by: 'date_desc', multiples: [], search: '' };
    }

    function saveStorageFilters(filters) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
    }

    // Guarda en localStorage exactamente lo que está pintado en pantalla
    // (los botones .focus-button realmente activos), nunca un estado
    // "adivinado". Así localStorage nunca puede quedar desincronizado de
    // lo que en verdad se ve.
    function syncStorageFromDOM() {
        const filters = { sort_by: 'date_desc', multiples: [], search: (searchInput?.value || '').trim() };

        filterForm.querySelectorAll('.filter-btn-form.focus-button').forEach(btn => {
            const name = btn.getAttribute('name');
            const value = btn.getAttribute('value');
            if (!name || value === null) return;

            if (name === 'sort_by') {
                filters.sort_by = value;
            } else {
                filters.multiples.push({ name, value });
            }
        });

        saveStorageFilters(filters);
    }

    // Pinta los botones de filtro según un objeto de filtros guardado
    // (sin tocar el servidor todavía).
    function paintFiltersOnDOM(filters) {
        filterForm.querySelectorAll('.filter-btn-form').forEach(btn => {
            const name = btn.getAttribute('name');
            const value = btn.getAttribute('value');
            if (!name || value === null) return;

            const selected = name === 'sort_by'
                ? filters.sort_by === value
                : filters.multiples.some(f => f.name === name && f.value === value);

            btn.classList.toggle('focus-button', selected);
        });

        if (searchInput && filters.search) {
            searchInput.value = filters.search;
        }
    }

    // =====================================================================
    // Construcción del query a partir del estado actual en pantalla
    // =====================================================================

    // Lee los botones de filtro (son <div> con atributos name/value, no inputs
    // reales) que estén marcados como activos (clase .focus-button) y arma
    // los parámetros que espera RecordController@index.
    function buildQueryParams() {
        const params = new URLSearchParams();

        const searchValue = (searchInput?.value || '').trim();
        if (searchValue) {
            params.set('search', searchValue);
        }

        const multiValues = {};

        filterForm.querySelectorAll('.filter-btn-form.focus-button').forEach(btn => {
            const name = btn.getAttribute('name');
            const value = btn.getAttribute('value');
            if (!name || value === null) return;

            if (name === 'sort_by') {
                // sort_by es selección única
                params.set('sort_by', value);
            } else {
                if (!multiValues[name]) multiValues[name] = [];
                multiValues[name].push(value);
            }
        });

        Object.keys(multiValues).forEach(name => {
            multiValues[name].forEach(value => {
                params.append(`${name}[]`, value);
            });
        });

        return params;
    }

    // =====================================================================
    // Petición AJAX + reemplazo del fragmento de resultados/filtros
    // =====================================================================

    let currentRequestId = 0;

    function fetchResults(params, { pushState = true } = {}) {
        const requestId = ++currentRequestId;
        const url = `${BASE_URL}?${params.toString()}`;

        recordsContainer.classList.add('opacity-50', 'pointer-events-none');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const resData = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(resData.message || `Error ${response.status} al filtrar los registros.`);
            }
            return resData;
        })
        .then(res => {
            // Si el usuario disparó otra búsqueda/filtro mientras esta seguía
            // en vuelo, ignoramos esta respuesta obsoleta.
            if (requestId !== currentRequestId) return;

            const parser = new DOMParser();
            const doc = parser.parseFromString(res.html, 'text/html');

            const newRecords = doc.getElementById('records-container');
            if (newRecords) {
                recordsContainer.innerHTML = newRecords.innerHTML;
            }

            // Volvemos a pintar el cuerpo del panel de filtros con lo que el
            // servidor realmente aplicó, así los botones activos SIEMPRE
            // reflejan el filtro que de verdad se está usando (y no un
            // estado visual que se desincroniza del backend, que era el bug
            // original: localStorage pintaba botones sin haber pedido esos
            // resultados todavía).
            const newFilterForm = doc.getElementById('filter-form');
            if (newFilterForm) {
                filterForm.innerHTML = newFilterForm.innerHTML;
            }

            if (searchInput) {
                searchInput.value = params.get('search') || '';
            }

            // El localStorage siempre se actualiza a partir de lo que el
            // servidor confirmó, nunca antes.
            syncStorageFromDOM();

            if (pushState) {
                history.pushState({ boosariFilters: true }, '', url);
            }
        })
        .catch(error => {
            console.error('Error al filtrar registros:', error);
        })
        .finally(() => {
            if (requestId === currentRequestId) {
                recordsContainer.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    }

    // =====================================================================
    // Estado inicial al cargar la página
    // =====================================================================
    // Si la URL ya trae filtros (?search=, ?sort_by=, ?category_id[]=...),
    // el servidor ya renderizó el estado correcto: lo respetamos tal cual y
    // solo dejamos el localStorage al día con eso.
    //
    // Si la URL llega "limpia" (primera visita, o entraste directo a Inicio)
    // y hay algo guardado en localStorage de una sesión anterior, restauramos
    // esos filtros Y pedimos los resultados correspondientes en el momento,
    // para que lo que se ve marcado sea siempre lo que en verdad se está
    // filtrando (antes solo se pintaban los botones sin refrescar el listado,
    // y por eso parecía que "a veces no filtraba hasta reenviar").
    const initialParams = new URLSearchParams(window.location.search);
    const urlHasFilters = [...initialParams.keys()].length > 0;

    if (urlHasFilters) {
        syncStorageFromDOM();
    } else {
        const stored = getStorageFilters();
        const hasStoredFilters = stored.sort_by !== 'date_desc' || stored.multiples.length > 0 || stored.search;
        if (hasStoredFilters) {
            paintFiltersOnDOM(stored);
            fetchResults(buildQueryParams(), { pushState: false });
        }
    }

    // =====================================================================
    // Eventos: barra de búsqueda, botones de filtro y paginación
    // =====================================================================

    // Barra de búsqueda superior
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchResults(buildQueryParams());
    });

    // Toggle de los botones de filtro (delegado: sigue funcionando aunque el
    // contenido de #filter-form se reemplace después de cada búsqueda)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.filter-btn-form');
        if (!btn || !filterForm.contains(btn)) return;

        e.preventDefault();

        const name = btn.getAttribute('name');

        if (name === 'sort_by') {
            // Selección única: desmarca a los demás botones del mismo grupo
            filterForm.querySelectorAll('.filter-btn-form[name="sort_by"]').forEach(el => {
                el.classList.remove('focus-button');
            });
            btn.classList.add('focus-button');
        } else {
            btn.classList.toggle('focus-button');
        }

        // Guardamos el estado visual apenas cambia, para que si el usuario
        // cierra el panel sin "Enviar" y vuelve más tarde, lo encuentre igual.
        syncStorageFromDOM();
    });

    // Filtro en vivo del listado de categorías dentro del panel (no dispara
    // fetch, solo oculta/muestra botones ya renderizados)
    document.addEventListener('input', (e) => {
        if (e.target.id !== 'category-search') return;

        const query = e.target.value.trim().toLowerCase();
        document.querySelectorAll('.category-item-wrapper').forEach(el => {
            const name = el.dataset.name || '';
            el.classList.toggle('hidden', query !== '' && !name.includes(query));
        });
    });

    // Botón "Enviar" del panel de filtros (Panel.onSubmit, ver paneles.js).
    // Al devolver true, el panel se cierra automáticamente tras aplicar.
    if (panel) {
        panel.onSubmit = async () => {
            fetchResults(buildQueryParams());
            return true;
        };
    }

    // Paginación: los links de "siguiente/anterior página" viven dentro del
    // contenedor de resultados, así que se interceptan por delegación para
    // que también se resuelvan por AJAX en vez de recargar la página.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('#records-container a[href]');
        if (!link) return;

        e.preventDefault();
        const linkUrl = new URL(link.href, window.location.origin);
        fetchResults(linkUrl.searchParams);

        // Al paginar, llevamos la vista de vuelta al inicio del listado
        recordsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // =====================================================================
    // Descargar Excel / PDF respetando exactamente los filtros y la
    // búsqueda que están activos en pantalla en este momento.
    // Reutiliza buildQueryParams() (la misma función que usa el AJAX de
    // filtros) para no duplicar esa lógica ni desincronizarla.
    //
    // Se usa delegación de eventos sobre `document` (igual que el resto
    // de los listeners de este archivo) a propósito: #filter-form se
    // reemplaza por completo (innerHTML) cada vez que se aplica un
    // filtro, así que un addEventListener normal atado a los botones se
    // perdería después del primer filtrado. Con delegación, los botones
    // siguen funcionando sin importar cuántas veces se regenere el HTML.
    // =====================================================================

    function triggerDownload(url) {
        const link = document.createElement('a');
        link.href = url;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    document.addEventListener('click', (e) => {
        const exportBtn = e.target.closest('#export-excel-btn, #export-pdf-btn');
        if (!exportBtn) return;

        e.preventDefault();

        // data-export-url puede caer en el botón raíz o en el <div>
        // interno de <x-button>, según cómo reparte Blade los atributos
        // no declarados como @props.
        const baseUrl = exportBtn.getAttribute('data-export-url')
            || exportBtn.querySelector('[data-export-url]')?.getAttribute('data-export-url');

        if (!baseUrl) {
            console.error('Botón de exportación sin data-export-url:', exportBtn);
            return;
        }

        const params = buildQueryParams();
        const query = params.toString();
        triggerDownload(query ? `${baseUrl}?${query}` : baseUrl);
    });

    // Navegación con los botones atrás/adelante del navegador
    window.addEventListener('popstate', () => {
        fetchResults(new URLSearchParams(window.location.search), { pushState: false });
    });
});