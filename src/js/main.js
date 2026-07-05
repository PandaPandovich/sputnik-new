import '../scss/main.scss';

console.log('Sputnik Plus theme loaded');

/**
 * Cookie-баннер ФЗ-152.
 */
(function () {
	const banner = document.getElementById('cookie-banner');
	if (!banner) return;

	const STORAGE_KEY = 'sputnik_cookie_consent';

	if (localStorage.getItem(STORAGE_KEY)) return;

	banner.hidden = false;
	requestAnimationFrame(() => banner.classList.add('is-visible'));

	banner.addEventListener('click', function (e) {
		const btn = e.target.closest('[data-cookie-action]');
		if (!btn) return;

		localStorage.setItem(STORAGE_KEY, btn.dataset.cookieAction);
		banner.classList.remove('is-visible');
		setTimeout(() => { banner.hidden = true; }, 400);
	});
})();

/**
 * AJAX-поиск в хедере.
 */
(function () {
	const input = document.querySelector('.header__search-input');
	const results = document.querySelector('.header__search-results');
	if (!input || !results) return;

	let timer = null;

	input.addEventListener('input', function () {
		clearTimeout(timer);
		const query = this.value.trim();

		if (query.length < 2) {
			results.innerHTML = '';
			results.classList.remove('active');
			return;
		}

		timer = setTimeout(() => {
			fetch(
				`${sputnikAjax.url}?action=sputnik_search&s=${encodeURIComponent(query)}`
			)
				.then((r) => r.json())
				.then((items) => {
					if (!items.length) {
						results.innerHTML = `
						<div class="header__search-empty">
							<div class="header__search-empty-icon">
								<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="9.17" cy="9.17" r="6.42" stroke="#9BA8B5" stroke-width="1.47"/>
									<line x1="14.67" y1="14.67" x2="18.33" y2="18.33" stroke="#9BA8B5" stroke-width="1.47" stroke-linecap="round"/>
									<line x1="7.33" y1="10.08" x2="12.83" y2="10.08" stroke="#9BA8B5" stroke-width="1.47" stroke-linecap="round"/>
								</svg>
							</div>
							<div class="header__search-empty-title">Ничего не нашлось</div>
							<div class="header__search-empty-text">По запросу «${query}» у нас нет страниц. Попробуйте переформулировать или свяжитесь с нами</div>
							<a href="#" class="header__search-empty-btn">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2.43 6.86L5.14 9.57L9.57 3.43" stroke="#fff" stroke-width="1.2" stroke-linejoin="round"/>
								</svg>
								Связаться с клиникой
							</a>
						</div>`;
						results.classList.add('active');
						return;
					}

					results.innerHTML = items
						.map(
							(item) => `
						<a href="${item.url}" class="header__search-item">
							<span class="header__search-item-title">${item.title}</span>
							<span class="header__search-item-type">${item.type}</span>
						</a>`
						)
						.join('');
					results.classList.add('active');
				});
		}, 300);
	});

	// Закрытие дропдауна при клике вне
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.header__search')) {
			results.innerHTML = '';
			results.classList.remove('active');
		}
	});
})();

/**
 * Мобильное меню: бургер и подменю.
 */
(function () {
	const burger = document.querySelector('.header__burger');
	const mobileMenu = document.querySelector('.header__mobile-menu');
	if (!burger || !mobileMenu) return;

	// Открытие/закрытие меню
	burger.addEventListener('click', function () {
		this.classList.toggle('active');
		mobileMenu.classList.toggle('active');
		document.body.classList.toggle('menu-open');
	});

	// Раскрытие подменю по клику на пункт с дочерними элементами
	const parentItems = mobileMenu.querySelectorAll('.menu-item-has-children');
	parentItems.forEach(function (item) {
		const link = item.querySelector(':scope > a');
		link.addEventListener('click', function (e) {
			e.preventDefault();
			item.classList.toggle('submenu-open');
		});
	});
})();

/**
 * Мобильный поиск.
 */
(function () {
	const trigger = document.querySelector('.header__mobile-search');
	const overlay = document.querySelector('.msearch');
	if (!trigger || !overlay) return;

	const input = overlay.querySelector('.msearch__input');
	const clearBtn = overlay.querySelector('.msearch__clear');
	const backBtn = overlay.querySelector('.msearch__back');
	const resultsEl = overlay.querySelector('.msearch__results');

	let timer = null;

	// Открытие
	trigger.addEventListener('click', function () {
		overlay.classList.add('active');
		document.body.classList.add('menu-open');
		input.focus();
	});

	// Закрытие
	backBtn.addEventListener('click', function () {
		overlay.classList.remove('active');
		document.body.classList.remove('menu-open');
		input.value = '';
		clearBtn.classList.remove('visible');
		resultsEl.innerHTML = '';
	});

	// Кнопка очистки
	clearBtn.addEventListener('click', function () {
		input.value = '';
		this.classList.remove('visible');
		resultsEl.innerHTML = '';
		input.focus();
	});

	// Группировка по типу
	function groupByType(items) {
		const groups = {};
		items.forEach(function (item) {
			if (!groups[item.type]) groups[item.type] = [];
			groups[item.type].push(item);
		});
		return groups;
	}

	// Рендер результатов
	function renderResults(items, query) {
		if (!items.length) {
			resultsEl.innerHTML = `
				<div class="msearch__empty">
					<div class="msearch__empty-icon">
						<svg width="22" height="22" viewBox="0 0 22 22" fill="none">
							<circle cx="9.17" cy="9.17" r="6.42" stroke="#9BA8B5" stroke-width="1.47"/>
							<line x1="14.67" y1="14.67" x2="18.33" y2="18.33" stroke="#9BA8B5" stroke-width="1.47" stroke-linecap="round"/>
							<line x1="7.33" y1="10.08" x2="12.83" y2="10.08" stroke="#9BA8B5" stroke-width="1.47" stroke-linecap="round"/>
						</svg>
					</div>
					<div class="msearch__empty-title">Ничего не нашлось</div>
					<div class="msearch__empty-text">По запросу «${query}» ничего не найдено. Попробуйте переформулировать.</div>
				</div>`;
			return;
		}

		const groups = groupByType(items);
		let html = '';

		Object.entries(groups).forEach(function ([type, groupItems], idx) {
			if (idx > 0) html += '<hr class="msearch__divider">';

			html += `<div class="msearch__group">`;
			html += `<div class="msearch__group-title">${type} · ${groupItems.length}</div>`;

			groupItems.forEach(function (item) {
				html += `
					<a href="${item.url}" class="msearch__result">
						<div class="msearch__result-icon msearch__result-icon--${item.type_key}">
							${item.icon}
						</div>
						<div class="msearch__result-content">
							<div class="msearch__result-title">${item.title}</div>
							${item.meta ? `<div class="msearch__result-meta">${item.meta}</div>` : ''}
						</div>
					</a>`;
			});

			html += '</div>';
		});

		resultsEl.innerHTML = html;
	}

	// Поиск
	input.addEventListener('input', function () {
		clearTimeout(timer);
		const query = this.value.trim();

		clearBtn.classList.toggle('visible', query.length > 0);

		if (query.length < 2) {
			resultsEl.innerHTML = '';
			return;
		}

		timer = setTimeout(function () {
			fetch(
				`${sputnikAjax.url}?action=sputnik_search&s=${encodeURIComponent(query)}`
			)
				.then(function (r) { return r.json(); })
				.then(function (items) {
					renderResults(items, query);
				});
		}, 300);
	});
})();

/**
 * Клиентская фильтрация результатов поиска по категориям.
 */
(function () {
	const root = document.querySelector('.search');
	if (!root) return;

	const chips = root.querySelectorAll('[data-filter]');
	const results = root.querySelectorAll('.search__result');
	const empty = root.querySelector('.search__filter-empty');
	if (!chips.length) return;

	chips.forEach((chip) => {
		chip.addEventListener('click', () => {
			const type = chip.dataset.filter;
			chips.forEach((c) => c.classList.toggle('is-active', c === chip));

			let visible = 0;
			results.forEach((r) => {
				const show = type === 'all' || r.dataset.type === type;
				r.hidden = !show;
				if (show) visible += 1;
			});

			if (empty) empty.hidden = visible !== 0;
		});
	});
})();
