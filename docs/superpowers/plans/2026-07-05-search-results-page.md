# Страница результатов поиска — план реализации

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Реализовать шаблон `search.php`, который ищет по статьям, отделениям, врачам (опции `team_members`) и услугам (опции `price_categories`), с клиентской фильтрацией по категориям и кнопкой записи `tel:` на карточках врачей и услуг.

**Architecture:** Чистая PHP-функция `sputnik_collect_search_results()` в `functions.php` собирает и нормализует результаты из четырёх источников в единый массив. Шаблон `search.php` рендерит заголовок, сайдбар-фильтр и карточки. Стили — SCSS-партиал, подключённый в `main.scss`. JS в `main.js` даёт мгновенную клиентскую фильтрацию и чинит submit формы хедера, который сейчас блокируется.

**Tech Stack:** WordPress (тема Sputnik Plus), PHP, ACF (опции темы), SCSS через `@wordpress/scripts` (`npm run build`), ванильный JS.

## Global Constraints

- Комментарии в коде — на русском (соглашение проекта).
- Весь пользовательский вывод экранируется: `esc_html`, `esc_url`, `esc_attr`.
- Все сравнения строк — через `mb_stripos` (кириллица, регистронезависимо).
- Телефон для `tel:` — из `get_field('footer_phone', 'option')`, нормализация `preg_replace('/[^\d+]/', '', $phone)` (как в `footer.php:28`).
- Тип `page` в поиск НЕ включается (в макете нет такого фильтра).
- Цвета — через CSS custom properties из `src/scss/root.scss` (`--color-text-primary`, `--color-text-secondary`, `--color-brand-blue`, `--color-red`, `--color-border`, `--color-surface-50`, `--color-white`).
- Категории и их метки фиксированы: `service`→«Услуги», `article`→«Статьи», `branch`→«Клиники», `doctor`→«Врачи», плюс `all`→«Все».
- Автотестов в проекте нет. Проверка = `npm run build` без ошибок + ручная проверка в браузере (Local by Flywheel). Где доступен WP-CLI — можно валидировать данные через `wp eval`.

---

### Task 1: Слой данных — сбор и нормализация результатов

**Files:**
- Modify: `functions.php` (добавить функции в конец файла, перед закрывающим кодом enqueue-блоков — любое место на верхнем уровне подходит)

**Interfaces:**
- Produces:
  - `sputnik_plural( int $n, string $one, string $few, string $many ): string` — русская плюрализация.
  - `sputnik_search_phone_link(): string` — нормализованный телефон (цифры и `+`).
  - `sputnik_collect_search_results( string $query ): array` — массив элементов. Каждый элемент:
    `[ 'type' => string, 'type_label' => string, 'title' => string, 'breadcrumb' => ?string, 'excerpt' => ?string, 'url' => ?string, 'price' => ?string, 'bookable' => bool ]`.
    `type` ∈ `service|article|branch|doctor`. Для `doctor`/`service` `url === null` и `bookable === true`.

- [ ] **Step 1: Добавить хелпер плюрализации и телефон**

В `functions.php` (на верхнем уровне) добавить:

```php
/**
 * Русская плюрализация числительных.
 */
function sputnik_plural( $n, $one, $few, $many ) {
	$n  = abs( (int) $n ) % 100;
	$n1 = $n % 10;
	if ( $n > 10 && $n < 20 ) {
		return $many;
	}
	if ( $n1 > 1 && $n1 < 5 ) {
		return $few;
	}
	if ( 1 === $n1 ) {
		return $one;
	}
	return $many;
}

/**
 * Нормализованный телефон для tel:-ссылки (из настроек темы).
 */
function sputnik_search_phone_link() {
	$phone = get_field( 'footer_phone', 'option' );
	return preg_replace( '/[^\d+]/', '', (string) $phone );
}
```

- [ ] **Step 2: Добавить коллектор результатов**

В `functions.php` добавить:

```php
/**
 * Сбор и нормализация результатов поиска из всех источников:
 * статьи (post), отделения (branch), врачи (опции team_members),
 * услуги (опции price_categories → prices).
 */
function sputnik_collect_search_results( $query ) {
	$query = trim( (string) $query );
	$items = array();
	if ( '' === $query ) {
		return $items;
	}

	$match = function ( $haystack ) use ( $query ) {
		return '' !== (string) $haystack && false !== mb_stripos( (string) $haystack, $query );
	};

	// Статьи и отделения — штатный поиск WordPress.
	$post_map = array(
		'post'   => array( 'type' => 'article', 'label' => 'Статья' ),
		'branch' => array( 'type' => 'branch', 'label' => 'Отделение' ),
	);
	foreach ( $post_map as $pt => $meta ) {
		if ( ! post_type_exists( $pt ) ) {
			continue;
		}
		$q = new WP_Query(
			array(
				'post_type'      => $pt,
				'post_status'    => 'publish',
				's'              => $query,
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);
		while ( $q->have_posts() ) {
			$q->the_post();
			$items[] = array(
				'type'       => $meta['type'],
				'type_label' => $meta['label'],
				'title'      => get_the_title(),
				'breadcrumb' => 'article' === $meta['type'] ? get_the_date( 'j F Y' ) : null,
				'excerpt'    => wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 24 ),
				'url'        => get_permalink(),
				'price'      => null,
				'bookable'   => false,
			);
		}
		wp_reset_postdata();
	}

	// Врачи — опции team_members.
	$team = get_field( 'team_members', 'option' );
	if ( is_array( $team ) ) {
		foreach ( $team as $m ) {
			$name   = (string) ( $m['name'] ?? '' );
			$spec   = (string) ( $m['specialization'] ?? '' );
			$tag    = (string) ( $m['tag'] ?? '' );
			$branch = (string) ( $m['branch'] ?? '' );
			$desc   = (string) ( $m['description'] ?? '' );
			if ( $match( $name ) || $match( $spec ) || $match( $tag ) || $match( $branch ) || $match( $desc ) ) {
				$crumb   = array_filter( array( $spec ?: $tag, $branch ) );
				$items[] = array(
					'type'       => 'doctor',
					'type_label' => 'Врач',
					'title'      => $name,
					'breadcrumb' => $crumb ? implode( ' · ', $crumb ) : null,
					'excerpt'    => $desc ? wp_trim_words( $desc, 24 ) : null,
					'url'        => null,
					'price'      => null,
					'bookable'   => true,
				);
			}
		}
	}

	// Услуги — опции price_categories → prices.
	$categories = get_field( 'price_categories', 'option' );
	if ( is_array( $categories ) ) {
		foreach ( $categories as $cat ) {
			$cat_name   = (string) ( $cat['name'] ?? '' );
			$dept       = $cat['department'] ?? null;
			$dept_title = '';
			if ( is_object( $dept ) && isset( $dept->ID ) ) {
				$dept_title = get_the_title( $dept->ID );
			} elseif ( is_numeric( $dept ) ) {
				$dept_title = get_the_title( (int) $dept );
			}
			$prices = $cat['prices'] ?? array();
			if ( ! is_array( $prices ) ) {
				continue;
			}
			foreach ( $prices as $p ) {
				$service = (string) ( $p['price_category'] ?? '' );
				$pdesc   = (string) ( $p['desc'] ?? '' );
				$price   = (string) ( $p['price'] ?? '' );
				if ( $match( $service ) || $match( $pdesc ) || $match( $cat_name ) || $match( $dept_title ) ) {
					$crumb   = array_filter( array( $dept_title, $cat_name ) );
					$items[] = array(
						'type'       => 'service',
						'type_label' => 'Услуга',
						'title'      => $service,
						'breadcrumb' => $crumb ? implode( ' · ', $crumb ) : null,
						'excerpt'    => $pdesc ?: null,
						'url'        => null,
						'price'      => $price ?: null,
						'bookable'   => true,
					);
				}
			}
		}
	}

	return $items;
}
```

- [ ] **Step 3: Проверить, что PHP валиден**

Run: `php -l functions.php`
Expected: `No syntax errors detected in functions.php`

- [ ] **Step 4: (Опционально) Проверить данные через WP-CLI**

Если доступен WP-CLI в окружении Local:
Run: `wp eval 'var_dump( array_map( fn($r)=>$r["type"]."|".$r["title"], sputnik_collect_search_results("а") ) );'`
Expected: непустой массив со смесью типов (article/branch/doctor/service) для распространённой буквы. Если WP-CLI недоступен — пропустить, проверка будет в Task 5 в браузере.

- [ ] **Step 5: Commit**

```bash
git add functions.php
git commit -m "feat(search): добавить сбор результатов поиска по статьям, отделениям, врачам и услугам"
```

---

### Task 2: Шаблон `search.php`

**Files:**
- Create: `search.php`

**Interfaces:**
- Consumes: `sputnik_collect_search_results()`, `sputnik_plural()`, `sputnik_search_phone_link()` из Task 1.
- Produces: HTML-разметку с корневым `.search`, чипами `[data-filter]`, карточками `.search__result[data-type]`, элементом `.search__filter-empty[hidden]` (для Task 4).

- [ ] **Step 1: Создать шаблон**

Создать `search.php`:

```php
<?php
/**
 * Шаблон результатов поиска.
 * Ищет по статьям, отделениям, врачам (опции) и услугам (опции).
 */
get_header();

$query   = get_search_query();
$results = function_exists( 'sputnik_collect_search_results' ) ? sputnik_collect_search_results( $query ) : array();
$total   = count( $results );

$counts = array( 'all' => $total, 'service' => 0, 'article' => 0, 'branch' => 0, 'doctor' => 0 );
foreach ( $results as $r ) {
	if ( isset( $counts[ $r['type'] ] ) ) {
		$counts[ $r['type'] ]++;
	}
}

$filters = array(
	'all'     => 'Все',
	'service' => 'Услуги',
	'article' => 'Статьи',
	'branch'  => 'Клиники',
	'doctor'  => 'Врачи',
);

$phone       = sputnik_search_phone_link();
$results_word = sputnik_plural( $total, 'результат', 'результата', 'результатов' );
?>

<section class="search">
	<div class="container">
		<header class="search__head">
			<h1 class="search__title">Результаты по запросу «<?php echo esc_html( $query ); ?>»</h1>
			<p class="search__count">Найдено <?php echo esc_html( $total . ' ' . $results_word ); ?></p>
		</header>

		<?php if ( $total > 0 ) : ?>
			<div class="search__layout">
				<aside class="search__filters">
					<span class="search__filters-label">Категория</span>
					<?php foreach ( $filters as $key => $label ) : ?>
						<?php if ( 'all' === $key || $counts[ $key ] > 0 ) : ?>
							<button
								type="button"
								class="search__filter<?php echo 'all' === $key ? ' is-active' : ''; ?>"
								data-filter="<?php echo esc_attr( $key ); ?>">
								<span class="search__filter-name"><?php echo esc_html( $label ); ?></span>
								<span class="search__filter-count"><?php echo esc_html( $counts[ $key ] ); ?></span>
							</button>
						<?php endif; ?>
					<?php endforeach; ?>
				</aside>

				<div class="search__results">
					<?php foreach ( $results as $r ) : ?>
						<?php
						$is_link = ! empty( $r['url'] );
						$tag     = $is_link ? 'a' : 'div';
						?>
						<<?php echo $tag; ?> class="search__result" data-type="<?php echo esc_attr( $r['type'] ); ?>"<?php echo $is_link ? ' href="' . esc_url( $r['url'] ) . '"' : ''; ?>>
							<span class="search__result-icon search__result-icon--<?php echo esc_attr( $r['type'] ); ?>" aria-hidden="true"></span>
							<div class="search__result-body">
								<div class="search__result-meta">
									<span class="search__result-type"><?php echo esc_html( $r['type_label'] ); ?></span>
									<?php if ( ! empty( $r['breadcrumb'] ) ) : ?>
										<span class="search__result-crumb"><?php echo esc_html( $r['breadcrumb'] ); ?></span>
									<?php endif; ?>
								</div>
								<span class="search__result-title"><?php echo esc_html( $r['title'] ); ?></span>
								<?php if ( ! empty( $r['excerpt'] ) ) : ?>
									<p class="search__result-excerpt"><?php echo esc_html( $r['excerpt'] ); ?></p>
								<?php endif; ?>
							</div>
							<div class="search__result-aside">
								<?php if ( ! empty( $r['price'] ) ) : ?>
									<span class="search__result-price"><?php echo esc_html( $r['price'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $r['bookable'] ) && $phone ) : ?>
									<a class="search__result-book" href="tel:<?php echo esc_attr( $phone ); ?>">Записаться</a>
								<?php elseif ( $is_link ) : ?>
									<span class="search__result-arrow" aria-hidden="true">→</span>
								<?php endif; ?>
							</div>
						</<?php echo $tag; ?>>
					<?php endforeach; ?>

					<div class="search__filter-empty" hidden>В этой категории ничего не найдено.</div>
				</div>
			</div>
		<?php else : ?>
			<div class="search__empty">
				<div class="search__empty-title">Ничего не нашлось</div>
				<p class="search__empty-text">По запросу «<?php echo esc_html( $query ); ?>» ничего не найдено. Попробуйте переформулировать или свяжитесь с нами — подберём врача.</p>
				<?php if ( $phone ) : ?>
					<a class="search__empty-btn" href="tel:<?php echo esc_attr( $phone ); ?>">Связаться с клиникой</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
```

- [ ] **Step 2: Проверить синтаксис**

Run: `php -l search.php`
Expected: `No syntax errors detected in search.php`

- [ ] **Step 3: Проверить в браузере**

Открыть `http://<local-site>/?s=терап` (или другое распространённое слово).
Expected: заголовок «Результаты по запросу «терап»», счётчик, сайдбар с чипами и счётчиками, список карточек. Разметка не сломана (стили появятся в Task 3).

- [ ] **Step 4: Commit**

```bash
git add search.php
git commit -m "feat(search): добавить шаблон страницы результатов поиска"
```

---

### Task 3: Стили `search.scss`

**Files:**
- Create: `src/scss/parts/search.scss`
- Modify: `src/scss/main.scss` (добавить `@forward`)

**Interfaces:**
- Consumes: классы из Task 2 (`.search`, `.search__filter[data-filter]`, `.search__result[data-type]`, `.search__filter-empty`).
- Produces: скомпилированные стили в `build/styles/main.css`.

- [ ] **Step 1: Создать партиал стилей**

Создать `src/scss/parts/search.scss`:

```scss
.search {
	padding: 3rem 0 var(--section-padding);

	&__head {
		margin-bottom: 2rem;
	}

	&__title {
		font-size: 2rem;
		font-weight: 700;
		color: var(--color-text-primary);
		margin: 0 0 .5rem;
	}

	&__count {
		color: var(--color-text-secondary);
		font-size: 1rem;
		margin: 0;
	}

	&__layout {
		display: grid;
		grid-template-columns: 220px 1fr;
		gap: 2.5rem;
		align-items: start;
	}

	&__filters {
		display: flex;
		flex-direction: column;
		gap: .25rem;
	}

	&__filters-label {
		font-size: .8125rem;
		text-transform: uppercase;
		letter-spacing: .04em;
		color: var(--color-text-tertiary);
		margin-bottom: .5rem;
	}

	&__filter {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		padding: .625rem .875rem;
		border: 0;
		background: transparent;
		border-radius: .5rem;
		cursor: pointer;
		color: var(--color-text-secondary);
		font-size: .9375rem;
		text-align: left;
		transition: background .15s, color .15s;

		&:hover {
			background: var(--color-surface-50);
		}

		&.is-active {
			background: var(--color-brand-tint);
			color: var(--color-brand-blue);
			font-weight: 600;
		}
	}

	&__filter-count {
		font-size: .8125rem;
		color: var(--color-text-tertiary);
	}

	&__results {
		display: flex;
		flex-direction: column;
	}

	&__result {
		display: flex;
		align-items: flex-start;
		gap: 1rem;
		padding: 1.25rem 0;
		border-bottom: 1px solid var(--color-line);
		text-decoration: none;
		color: inherit;
	}

	a.search__result:hover .search__result-title {
		color: var(--color-brand-blue);
	}

	&__result-icon {
		flex: 0 0 40px;
		width: 40px;
		height: 40px;
		border-radius: 10px;
		background: var(--color-brand-tint);

		&--article { background: var(--color-brand-tint); }
		&--branch { background: var(--color-brand-tint-2); }
		&--doctor { background: #E9F7EF; }
		&--service { background: #FDECEC; }
	}

	&__result-body {
		flex: 1 1 auto;
		min-width: 0;
	}

	&__result-meta {
		display: flex;
		flex-wrap: wrap;
		gap: .5rem;
		font-size: .8125rem;
		color: var(--color-text-tertiary);
		margin-bottom: .25rem;
	}

	&__result-type {
		color: var(--color-brand-blue);
		font-weight: 600;
	}

	&__result-title {
		display: block;
		font-size: 1.0625rem;
		font-weight: 600;
		color: var(--color-text-primary);
		transition: color .15s;
	}

	&__result-excerpt {
		margin: .375rem 0 0;
		font-size: .9375rem;
		color: var(--color-text-secondary);
		line-height: 1.4;
	}

	&__result-aside {
		flex: 0 0 auto;
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: .5rem;
	}

	&__result-price {
		font-weight: 700;
		color: var(--color-text-primary);
		white-space: nowrap;
	}

	&__result-book {
		font-size: .875rem;
		font-weight: 600;
		color: var(--color-brand-blue);
		text-decoration: none;
		white-space: nowrap;

		&:hover {
			text-decoration: underline;
		}
	}

	&__result-arrow {
		color: var(--color-text-tertiary);
		font-size: 1.125rem;
	}

	&__filter-empty {
		padding: 2rem 0;
		color: var(--color-text-secondary);
	}

	&__empty {
		max-width: 480px;
		padding: 2rem 0;
	}

	&__empty-title {
		font-size: 1.25rem;
		font-weight: 700;
		color: var(--color-text-primary);
		margin-bottom: .5rem;
	}

	&__empty-text {
		color: var(--color-text-secondary);
		margin: 0 0 1.25rem;
		line-height: 1.5;
	}

	&__empty-btn {
		display: inline-block;
		padding: .75rem 1.5rem;
		background: var(--color-brand-blue);
		color: var(--color-white);
		border-radius: .5rem;
		text-decoration: none;
		font-weight: 600;
	}

	@media (max-width: 800px) {
		&__layout {
			grid-template-columns: 1fr;
			gap: 1.5rem;
		}

		&__filters {
			flex-direction: row;
			flex-wrap: wrap;
		}

		&__result {
			flex-wrap: wrap;
		}

		&__result-aside {
			flex-direction: row;
			width: 100%;
			justify-content: space-between;
			align-items: center;
		}
	}
}
```

- [ ] **Step 2: Подключить партиал в `main.scss`**

В `src/scss/main.scss` после строки `@forward './parts/page-content.scss';` добавить:

```scss
@forward './parts/search.scss';
```

- [ ] **Step 3: Собрать ассеты**

Run: `npm run build`
Expected: сборка без ошибок; `build/styles/main.css` содержит класс `.search` (`grep -c 'search__result' build/styles/main.css` → ≥ 1).

- [ ] **Step 4: Проверить в браузере**

Обновить `?s=терап`: страница оформлена по макету — сайдбар слева, список карточек, активный чип «Все» подсвечен, у услуг видна цена, у врачей/услуг — кнопка «Записаться».

- [ ] **Step 5: Commit**

```bash
git add src/scss/parts/search.scss src/scss/main.scss build/
git commit -m "feat(search): добавить стили страницы результатов поиска"
```

---

### Task 4: JS — клиентская фильтрация и починка submit хедера

**Files:**
- Modify: `src/js/main.js`

**Interfaces:**
- Consumes: DOM из Task 2 (`.search`, `[data-filter]`, `.search__result[data-type]`, `.search__filter-empty`); форму `.header__search` из `header.php`.
- Produces: поведение фильтрации и навигацию формы на `?s=`.

- [ ] **Step 1: Разрешить submit формы хедера**

В `src/js/main.js` найти блок в обработчике AJAX-поиска хедера:

```js
	const form = input.closest('form');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
		});
	}
```

Удалить его целиком (форма должна нативно уходить на `?s=` по Enter и кнопке). Живой AJAX-дропдаун (обработчик `input.addEventListener('input', …)`) остаётся без изменений.

- [ ] **Step 2: Добавить модуль клиентской фильтрации**

В конец `src/js/main.js` добавить:

```js
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
```

- [ ] **Step 3: Собрать ассеты**

Run: `npm run build`
Expected: сборка без ошибок; `grep -c 'search__filter' build/js/main.js` → ≥ 1.

- [ ] **Step 4: Проверить в браузере**

На `?s=терап`:
1. Клик по «Врачи» — показаны только карточки врачей, чип активен, счётчик совпадает.
2. Клик по «Все» — снова все карточки.
3. Клик по категории без результатов недоступен (чип скрыт в Task 2), а если выбрать пустую — виден текст «В этой категории ничего не найдено».
4. Enter в поиске хедера открывает `/?s=…` (страницу результатов), а не остаётся на месте.

- [ ] **Step 5: Commit**

```bash
git add src/js/main.js build/
git commit -m "feat(search): клиентская фильтрация результатов и навигация формы поиска"
```

---

### Task 5: Финальная сквозная проверка

**Files:** нет (только проверка)

- [ ] **Step 1: Полная сборка**

Run: `npm run build`
Expected: без ошибок.

- [ ] **Step 2: Сквозные сценарии в браузере**

Проверить на Local by Flywheel:

1. Поиск «вакцин» из хедера по Enter → открывается `search.php`.
2. Есть карточки как минимум двух типов; счётчик «Найдено N результатов» склоняется верно (1 результат / 2 результата / 5 результатов).
3. Запрос с ФИО реального врача → карточка в «Врачи» с кнопкой «Записаться» (`tel:` = телефон из настроек), карточка НЕ является ссылкой.
4. Запрос по услуге → карточка «Услуги» с ценой и кнопкой «Записаться».
5. Карточки статей и отделений кликабельны и ведут на свои страницы.
6. Каждый фильтр сайдбара показывает только свою категорию; «Все» — всё.
7. Запрос-бессмыслица (`?s=zzzzz`) → блок «Ничего не нашлось» с кнопкой связи.

- [ ] **Step 3: Финальный commit (если остались несобранные ассеты)**

```bash
git add -A
git commit -m "chore(search): пересборка ассетов страницы поиска" || echo "нечего коммитить"
```

---

## Заметки по реализации

- Шаблон `search.php` WordPress подхватывает автоматически для `?s=` — регистрировать ничего не нужно.
- Ассеты уже энкью'ятся из `build/styles/main.css` и `build/js/main.js` (см. `functions.php`), дополнительного enqueue не требуется.
- Иконки категорий заданы цветными плашками (`.search__result-icon--<type>`). Если нужны конкретные SVG из макета — заменить фон на inline-SVG в Task 2/3 отдельной итерацией (вне рамок первичного плана).
