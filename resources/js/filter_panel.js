document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filter-form');
    const filterSubmit = document.getElementById('filter-submit');
    const searchInput = document.getElementById('category-search');
    const categoryItems = document.querySelectorAll('.category-item-wrapper');
    const filter_btn_form = document.querySelectorAll(".filter-btn-form");

    const getStorageFilters = () => {
        const stored = localStorage.getItem('selected_filters');
        return stored ? JSON.parse(stored) : { sort_by: 'date_desc', multiples: [] };
    };

    const saveStorageFilters = (filters) => {
        localStorage.setItem('selected_filters', JSON.stringify(filters));
    };

    const restoreFilters = () => {
        const filters = getStorageFilters();

        // Limpiar inputs ocultos viejos generados previamente para evitar duplicación con Blade
        form.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (input.name !== 'sort_by' && input.name !== '_token') {
                input.remove();
            }
        });

        filter_btn_form.forEach((btn) => {
            const name = btn.getAttribute('name');
            const value = btn.getAttribute('value');
            if (!name || !value) return;

            if (name === 'sort_by' && filters.sort_by === value) {
                btn.classList.add("focus-button");
                const staticInput = form.querySelector('input[name="sort_by"]');
                if (staticInput) staticInput.value = value;
            }

            if (name !== 'sort_by') {
                const isSelected = filters.multiples.some(f => f.name === name && f.value === value);
                
                if (isSelected) {
                    btn.classList.add("focus-button");

                    // Re-inyectar de forma segura el array []
                    const inputId = `hidden-${name}-${value}`;
                    if (!document.getElementById(inputId)) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `${name}[]`;
                        hiddenInput.value = value;
                        hiddenInput.id = inputId;
                        form.appendChild(hiddenInput);
                    }
                } else {
                    // Asegurar remover la clase si el LocalStorage no lo tiene seleccionado
                    btn.classList.remove("focus-button");
                }
            }
        });
    };

    if (form) restoreFilters();

    if (filterSubmit && form) {
        filterSubmit.addEventListener("click", (e) => {
            e.preventDefault();
            form.requestSubmit();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            categoryItems.forEach(item => {
                const categoryName = item.getAttribute('data-name') || '';
                item.style.display = categoryName.includes(query) ? 'block' : 'none';
            });
        });
    }

    filter_btn_form.forEach((e) => {
        e.addEventListener("click", () => {
            const name = e.getAttribute('name');   
            const value = e.getAttribute('value'); 

            if (!name || !value) return;

            let filters = getStorageFilters();

            if (name === 'sort_by') {
                const orderButtons = form.querySelectorAll('.filter-btn-form[name="sort_by"]');
                orderButtons.forEach(btn => btn.classList.remove("focus-button"));
                
                e.classList.add("focus-button");
                
                const staticInput = form.querySelector('input[name="sort_by"]');
                if (staticInput) staticInput.value = value;

                filters.sort_by = value;
                saveStorageFilters(filters);
                return; 
            }

            const inputId = `hidden-${name}-${value}`;

            if (!e.classList.contains("focus-button")) {
                e.classList.add("focus-button");

                if (!document.getElementById(inputId)) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `${name}[]`; 
                    hiddenInput.value = value;
                    hiddenInput.id = inputId; 
                    form.appendChild(hiddenInput);
                }

                const alreadySaved = filters.multiples.some(f => f.name === name && f.value === value);
                if (!alreadySaved) {
                    filters.multiples.push({ name, value });
                    saveStorageFilters(filters);
                }
            } else {
                e.classList.remove("focus-button");

                const inputToRemove = document.getElementById(inputId);
                if (inputToRemove) inputToRemove.remove();

                filters.multiples = filters.multiples.filter(f => !(f.name === name && f.value === value));
                saveStorageFilters(filters);
            }
        });
    });
});