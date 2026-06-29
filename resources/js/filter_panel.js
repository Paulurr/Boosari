document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filter-form');
    const filterSubmit = document.getElementById('filter-submit');
    const searchInput = document.getElementById('category-search');
    const categoryItems = document.querySelectorAll('.category-item-wrapper');

    // 1. Envío del formulario
    if (filterSubmit && form) {
        filterSubmit.addEventListener("click", (e) => {
            e.preventDefault();
            form.requestSubmit();
        });
    }

    // 2. Buscador de categorías en tiempo real
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            categoryItems.forEach(item => {
                const categoryName = item.getAttribute('data-name') || '';
                item.style.display = categoryName.includes(query) ? 'block' : 'none';
            });
        });
    }

    // 3. Control de Estado Múltiple respetando solo .focus-button
    const filter_btn_form = document.querySelectorAll(".filter-btn-form");
    
    filter_btn_form.forEach((e) => {
        e.addEventListener("click", () => {
            const name = e.getAttribute('name');   
            const value = e.getAttribute('value'); 

            if (!name || !value) return;

            // CASO "Ordenar por": Cambia uno por otro (No múltiple por lógica de SQL)
            if (name === 'sort_by') {
                const orderButtons = form.querySelectorAll('.filter-btn-form[name="sort_by"]');
                orderButtons.forEach(btn => btn.classList.remove("focus-button"));
                
                e.classList.add("focus-button");
                
                const staticInput = form.querySelector('input[name="sort_by"]');
                if (staticInput) staticInput.value = value;
                return; 
            }

            // CASO GENERAL: Selección Múltiple (Categorías, Tipos de registro, Rangos)
            if (!e.classList.contains("focus-button")) {
                // Agregar visualmente y crear input array
                e.classList.add("focus-button");

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `${name}[]`; 
                hiddenInput.value = value;
                hiddenInput.id = `hidden-${name}-${value}`; 
                
                form.appendChild(hiddenInput);
            } else {
                // Quitar visualmente y remover input
                e.classList.remove("focus-button");

                const inputToRemove = document.getElementById(`hidden-${name}-${value}`);
                if (inputToRemove) {
                    inputToRemove.remove();
                }
            }
        });
    });
});