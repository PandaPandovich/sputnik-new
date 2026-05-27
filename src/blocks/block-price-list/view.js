/**
 * Интерактивность блока «Прайс-лист»:
 * — переключение отделений
 * — поиск по услугам
 * — «Показать все»
 */
const block = document.querySelector('.price-list');

if (block) {
    const departments = block.querySelectorAll('.price-list__department');
    const deptGroups = block.querySelectorAll('.price-list__dept-group');
    const searchInput = block.querySelector('.price-list__search-input');
    const noResults = block.querySelector('.price-list__no-results');

    // Переключение отделения
    departments.forEach((dept) => {
        dept.addEventListener('click', () => {
            const deptId = dept.dataset.department;

            // Обновляем активное отделение
            departments.forEach((d) => {
                d.classList.remove('is-active');
                const arrow = d.querySelector('.price-list__category-arrow');
                if (arrow) arrow.remove();
            });
            dept.classList.add('is-active');

            // Добавляем стрелку
            const arrow = document.createElement('span');
            arrow.className = 'price-list__category-arrow';
            arrow.textContent = '→ ';
            dept.prepend(arrow);

            // Показываем нужную группу категорий
            deptGroups.forEach((g) => {
                g.classList.toggle('is-active', g.dataset.department === deptId);
            });

            // Сбрасываем поиск
            if (searchInput) {
                searchInput.value = '';
                resetSearch();
            }

            if (noResults) noResults.style.display = 'none';
        });
    });

    // Поиск
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();

            if (!query) {
                resetSearch();
                return;
            }

            let hasAnyResult = false;

            // Ищем по всем группам и таблицам
            deptGroups.forEach((group) => {
                let groupHasMatch = false;
                const tables = group.querySelectorAll('.price-list__table');

                tables.forEach((table) => {
                    const rows = table.querySelectorAll('.price-list__row');
                    let tableHasMatch = false;

                    rows.forEach((row) => {
                        const name = row.querySelector('.price-list__row-name');
                        const text = name ? name.textContent.toLowerCase() : '';
                        const matches = text.includes(query);
                        row.style.display = matches ? '' : 'none';
                        row.classList.remove('is-hidden');
                        if (matches) tableHasMatch = true;
                    });

                    table.style.display = tableHasMatch ? '' : 'none';
                    const showAllBtn = table.querySelector('.price-list__show-all');
                    if (showAllBtn) showAllBtn.style.display = 'none';

                    if (tableHasMatch) groupHasMatch = true;
                });

                group.classList.toggle('is-active', groupHasMatch);
                if (groupHasMatch) hasAnyResult = true;
            });

            // Подсвечиваем отделения с результатами
            departments.forEach((dept) => {
                const deptId = dept.dataset.department;
                const group = block.querySelector(`.price-list__dept-group[data-department="${deptId}"]`);
                const hasMatch = group && group.classList.contains('is-active');
                dept.classList.toggle('has-results', hasMatch);
            });

            if (noResults) {
                noResults.style.display = hasAnyResult ? 'none' : '';
            }
        });
    }

    // «Показать все»
    block.querySelectorAll('.price-list__show-all').forEach((btn) => {
        btn.addEventListener('click', () => {
            const table = btn.closest('.price-list__table');
            const hiddenRows = table.querySelectorAll('.price-list__row.is-hidden');
            hiddenRows.forEach((row) => row.classList.remove('is-hidden'));
            btn.classList.add('is-expanded');
        });
    });

    // Сброс поиска
    function resetSearch() {
        deptGroups.forEach((group) => {
            const tables = group.querySelectorAll('.price-list__table');
            tables.forEach((table) => {
                table.style.display = '';
                const rows = table.querySelectorAll('.price-list__row');
                rows.forEach((row, i) => {
                    row.style.display = '';
                    if (i >= 6) {
                        const showAllBtn = table.querySelector('.price-list__show-all');
                        if (showAllBtn && !showAllBtn.classList.contains('is-expanded')) {
                            row.classList.add('is-hidden');
                        }
                    }
                });
                const showAllBtn = table.querySelector('.price-list__show-all');
                if (showAllBtn) showAllBtn.style.display = '';
            });
        });

        // Показываем только активную группу
        const activeDept = block.querySelector('.price-list__department.is-active');
        if (activeDept) {
            const activeId = activeDept.dataset.department;
            deptGroups.forEach((g) => {
                g.classList.toggle('is-active', g.dataset.department === activeId);
            });
        }

        departments.forEach((d) => d.classList.remove('has-results'));
        if (noResults) noResults.style.display = 'none';
    }
}
