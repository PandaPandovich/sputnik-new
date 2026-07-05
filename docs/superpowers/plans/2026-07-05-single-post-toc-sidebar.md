# Содержание — TOC Sidebar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an auto-generated, sticky "Содержание" table-of-contents sidebar to single article pages, matching the Pencil mockup.

**Architecture:** Server-side PHP parses the rendered article content, injects anchor ids into `<h2>` headings, and builds a list of sections. `single-post.php` renders a two-column layout (article + sticky aside). A vanilla-JS module drives scroll-spy, a reading-progress bar, and a mobile accordion.

**Tech Stack:** PHP (DOMDocument), WordPress theme APIs, SCSS (`@wordpress/scripts` build), vanilla JS (IIFE module in `main.js`).

## Global Constraints

- Comments in code are in Russian (project convention).
- No test runner exists — verify with `php -l` (via `wp-env run cli`), `npm run build`, and browser checks. Run `wp-env` commands from `/Volumes/Webwork/sputnik-vet` when Docker is up.
- BEM class naming; follow existing `single-post.scss` / `main.js` patterns (IIFE + early-return guard).
- Mobile breakpoint: **1024px** (`$desktop-small`).
- TOC threshold: build only if **≥ 3** `<h2>` headings; otherwise render single-column unchanged.
- Colors (from mockup): accent `#1e4976`, muted `#6b7a8f`, item `#4a5568`, track `#e4e9f0`, card bg `#f5f8fb`.
- Existing article blocks are NOT modified. Sections = `<h2>` only.

---

### Task 1: PHP TOC builder + renderer

**Files:**
- Modify: `functions.php` (append two helper functions, near `sputnik_plus_breadcrumbs`)

**Interfaces:**
- Produces: `sputnik_plus_build_toc( string $html ): array` → `[ 'items' => array<{id:string,text:string}>, 'html' => string ]`
- Produces: `sputnik_plus_render_toc( array $items ): void` (echoes aside markup)
- Consumes: existing `sputnik_plural( $n, $one, $few, $many )`

- [ ] **Step 1: Add the builder function** to `functions.php`:

```php
/**
 * Строит оглавление из заголовков <h2> статьи.
 * Проставляет id-якоря там, где их нет.
 *
 * @param string $html Отрендеренный контент (результат the_content).
 * @return array{items: array<int, array{id: string, text: string}>, html: string}
 */
function sputnik_plus_build_toc( $html ) {
	$min = 3;
	if ( trim( (string) $html ) === '' ) {
		return [ 'items' => [], 'html' => $html ];
	}

	libxml_use_internal_errors( true );
	$dom = new DOMDocument();
	$dom->loadHTML(
		'<?xml encoding="UTF-8"?><div id="sputnik-toc-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();

	$headings = $dom->getElementsByTagName( 'h2' );
	if ( $headings->length < $min ) {
		return [ 'items' => [], 'html' => $html ];
	}

	$items = [];
	$used  = [];
	foreach ( $headings as $h ) {
		$text = trim( $h->textContent );
		if ( $text === '' ) {
			continue;
		}
		$id = $h->getAttribute( 'id' );
		if ( $id === '' ) {
			$base = sanitize_title( $text );
			if ( $base === '' ) {
				$base = 'section';
			}
			$id = $base;
			$n  = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $n;
				$n++;
			}
			$h->setAttribute( 'id', $id );
		}
		$used[ $id ] = true;
		$items[]     = [ 'id' => $id, 'text' => $text ];
	}

	if ( count( $items ) < $min ) {
		return [ 'items' => [], 'html' => $html ];
	}

	$root = $dom->getElementById( 'sputnik-toc-root' );
	$out  = '';
	foreach ( $root->childNodes as $child ) {
		$out .= $dom->saveHTML( $child );
	}

	return [ 'items' => $items, 'html' => $out ];
}
```

- [ ] **Step 2: Add the renderer function** to `functions.php`:

```php
/**
 * Выводит сайдбар «Содержание» на основе списка разделов.
 *
 * @param array<int, array{id: string, text: string}> $items
 */
function sputnik_plus_render_toc( array $items ) {
	if ( empty( $items ) ) {
		return;
	}
	$total = count( $items );
	$noun  = sputnik_plural( $total, 'раздела', 'разделов', 'разделов' );
	?>
	<aside class="toc" data-toc>
		<div class="toc__progress">
			<span class="toc__progress-label">Прогресс чтения</span>
			<span class="toc__progress-count" data-toc-count><span data-toc-current>1</span> из <?php echo esc_html( (string) $total ); ?> <?php echo esc_html( $noun ); ?></span>
			<div class="toc__progress-bar"><span class="toc__progress-fill" data-toc-fill></span></div>
		</div>
		<button type="button" class="toc__toggle" data-toc-toggle aria-expanded="false">
			<span>Содержание</span>
			<svg class="toc__chevron" width="14" height="14" viewBox="0 0 14 14" aria-hidden="true"><path d="M3.5 5.25 7 8.75l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
		<p class="toc__heading">В этой статье</p>
		<nav class="toc__list">
			<?php foreach ( $items as $item ) : ?>
				<a class="toc__link" href="#<?php echo esc_attr( $item['id'] ); ?>" data-toc-link><?php echo esc_html( $item['text'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</aside>
	<?php
}
```

- [ ] **Step 3: Lint the file**

Run: `cd /Volumes/Webwork/sputnik-vet && wp-env run cli php -l wp-content/themes/sputnik-new/functions.php`
Expected: `No syntax errors detected`
(If `wp-env`/Docker is down, run `php -l functions.php` directly.)

- [ ] **Step 4: Commit**

```bash
git add functions.php
git commit -m "feat(toc): PHP-хелперы построения и рендера оглавления статьи"
```

---

### Task 2: Two-column single-post layout

**Files:**
- Modify: `single-post.php:40-47` (the `single-post__body` block)

**Interfaces:**
- Consumes: `sputnik_plus_build_toc()`, `sputnik_plus_render_toc()` from Task 1
- Produces: `.single-post__layout` wrapper containing `.toc` (aside, first in DOM) + `.single-post__content`

- [ ] **Step 1: Replace the content section.** Change the current block:

```php
	<!-- Контент статьи -->
	<div class="single-post__body">
		<div class="container">
			<div class="single-post__content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
```

to:

```php
	<!-- Контент статьи -->
	<?php
	$sputnik_rendered = apply_filters( 'the_content', get_the_content() );
	$sputnik_toc      = sputnik_plus_build_toc( $sputnik_rendered );
	$sputnik_has_toc  = ! empty( $sputnik_toc['items'] );
	?>
	<div class="single-post__body">
		<div class="container">
			<div class="single-post__layout<?php echo $sputnik_has_toc ? '' : ' single-post__layout--full'; ?>">
				<?php if ( $sputnik_has_toc ) { sputnik_plus_render_toc( $sputnik_toc['items'] ); } ?>
				<div class="single-post__content">
					<?php echo $sputnik_toc['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
	</div>
```

- [ ] **Step 2: Lint the file**

Run: `cd /Volumes/Webwork/sputnik-vet && wp-env run cli php -l wp-content/themes/sputnik-new/single-post.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add single-post.php
git commit -m "feat(toc): двухколоночная разметка статьи с сайдбаром содержания"
```

---

### Task 3: TOC styles + grid

**Files:**
- Create: `src/scss/parts/toc.scss`
- Modify: `src/scss/main.scss` (add `@forward`)
- Modify: `src/scss/parts/single-post.scss` (add `&__layout`, `scroll-margin-top` on content `h2`)

**Interfaces:**
- Consumes: BEM classes / `data-*` from Task 1 markup and `.single-post__layout` from Task 2

- [ ] **Step 1: Create `src/scss/parts/toc.scss`:**

```scss
// Сайдбар «Содержание»
.toc {
  font-family: "Onest", sans-serif;

  &__progress {
    background: #f5f8fb;
    border-radius: 10px;
    padding: 0.875rem 1rem 1.125rem;
    margin-bottom: 1.75rem;
  }

  &__progress-label {
    display: block;
    font-size: 0.6875rem;
    letter-spacing: 0.03em;
    color: #6b7a8f;
    margin-bottom: 0.375rem;
  }

  &__progress-count {
    display: block;
    font-size: 0.875rem;
    font-weight: 700;
    color: #1e4976;
    margin-bottom: 0.75rem;
  }

  &__progress-bar {
    height: 4px;
    border-radius: 2px;
    background: #e4e9f0;
    overflow: hidden;
  }

  &__progress-fill {
    display: block;
    height: 100%;
    width: 0;
    border-radius: 2px;
    background: #1e4976;
    transition: width 0.15s linear;
  }

  &__heading {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #6b7a8f;
    margin: 0 0 0.75rem;
  }

  &__list {
    display: flex;
    flex-direction: column;
    border-left: 2px solid #e4e9f0;
  }

  &__link {
    display: block;
    padding: 0.5rem 0 0.5rem 1.125rem;
    margin-left: -2px;
    border-left: 2px solid transparent;
    font-size: 0.84375rem;
    line-height: 1.4;
    color: #4a5568;
    text-decoration: none;
    transition: color 0.15s ease, border-color 0.15s ease;

    &:hover {
      color: #1e4976;
    }

    &.is-active {
      color: #1e4976;
      font-weight: 700;
      border-left-color: #1e4976;
    }
  }

  &__toggle {
    display: none;
  }

  &__chevron {
    transition: transform 0.2s ease;
  }

  // Мобильная гармошка
  @media (max-width: 1024px) {
    &__progress {
      display: none;
    }

    &__heading {
      display: none;
    }

    &__toggle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 0.875rem 1rem;
      background: #f5f8fb;
      border: none;
      border-radius: 10px;
      font-family: inherit;
      font-size: 0.9375rem;
      font-weight: 700;
      color: #1e4976;
      cursor: pointer;
    }

    &__list {
      display: none;
      margin-top: 0.75rem;
    }
  }

  &.toc--open {
    @media (max-width: 1024px) {
      .toc__list {
        display: flex;
      }

      .toc__chevron {
        transform: rotate(180deg);
      }
    }
  }
}
```

- [ ] **Step 2: Forward the partial** — add to `src/scss/main.scss` after the `single-post.scss` forward:

```scss
@forward './parts/single-post.scss';
@forward './parts/toc.scss';
```

- [ ] **Step 3: Add grid to `src/scss/parts/single-post.scss`.** Inside `.single-post`, replace the `&__body` rule with:

```scss
  // Контент
  &__body {
    padding-bottom: var(--section-padding);
  }

  &__layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 3rem;
    align-items: start;
    max-width: 1160px;
    margin: 0 auto;

    .single-post__content {
      max-width: none;
      margin: 0;
    }

    .toc {
      grid-column: 2;
      grid-row: 1;
      position: sticky;
      top: 6.5rem;
    }

    &--full {
      display: block;
      max-width: none;
    }

    @media (max-width: 1024px) {
      grid-template-columns: 1fr;
      max-width: 880px;
      gap: 1.5rem;

      .toc {
        grid-column: 1;
        position: static;
      }
    }
  }
```

- [ ] **Step 4: Add `scroll-margin-top` to content headings.** In `src/scss/parts/single-post.scss`, inside `&__content { h2 { … } }`, add:

```scss
      scroll-margin-top: 7rem;
```

- [ ] **Step 5: Build**

Run: `cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new && npm run build`
Expected: build completes, no SCSS errors; `build/styles/main.css` regenerated.

- [ ] **Step 6: Commit**

```bash
git add src/scss/parts/toc.scss src/scss/main.scss src/scss/parts/single-post.scss build/
git commit -m "feat(toc): стили сайдбара и двухколоночная сетка статьи"
```

---

### Task 4: Scroll-spy, progress & mobile toggle JS

**Files:**
- Modify: `src/js/main.js` (append a new IIFE module at the end)

**Interfaces:**
- Consumes: `[data-toc]`, `[data-toc-link]`, `[data-toc-fill]`, `[data-toc-current]`, `[data-toc-toggle]`, `.single-post__content` from Tasks 1–2

- [ ] **Step 1: Append the module** to `src/js/main.js`:

```js
/**
 * Оглавление статьи: подсветка активного раздела,
 * прогресс чтения и мобильная гармошка.
 */
(function () {
	const toc = document.querySelector('[data-toc]');
	if (!toc) return;

	const links = Array.from(toc.querySelectorAll('[data-toc-link]'));
	const content = document.querySelector('.single-post__content');
	if (!links.length || !content) return;

	const fill = toc.querySelector('[data-toc-fill]');
	const current = toc.querySelector('[data-toc-current]');
	const toggle = toc.querySelector('[data-toc-toggle]');

	const headings = links
		.map((l) => {
			const id = decodeURIComponent(l.getAttribute('href').slice(1));
			return document.getElementById(id);
		})
		.filter(Boolean);

	let activeIndex = -1;
	function setActive(i) {
		if (i === activeIndex) return;
		activeIndex = i;
		links.forEach((l, idx) => l.classList.toggle('is-active', idx === i));
		if (current) current.textContent = String(i + 1);
	}

	function update() {
		const rect = content.getBoundingClientRect();
		const scrollable = rect.height - window.innerHeight;
		const passed = Math.min(Math.max(-rect.top, 0), Math.max(scrollable, 0));
		let pct;
		if (scrollable > 0) {
			pct = (passed / scrollable) * 100;
		} else {
			pct = rect.bottom <= window.innerHeight ? 100 : 0;
		}
		if (fill) fill.style.width = pct.toFixed(1) + '%';

		const offset = 120;
		let idx = 0;
		for (let i = 0; i < headings.length; i++) {
			if (headings[i].getBoundingClientRect().top - offset <= 0) {
				idx = i;
			} else {
				break;
			}
		}
		setActive(idx);
	}

	let ticking = false;
	function onScroll() {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(() => {
			update();
			ticking = false;
		});
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll, { passive: true });
	update();

	if (toggle) {
		toggle.addEventListener('click', function () {
			const open = toc.classList.toggle('toc--open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	links.forEach((l) =>
		l.addEventListener('click', function () {
			if (toc.classList.contains('toc--open')) {
				toc.classList.remove('toc--open');
				if (toggle) toggle.setAttribute('aria-expanded', 'false');
			}
		})
	);
})();
```

- [ ] **Step 2: Build**

Run: `cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new && npm run build`
Expected: build completes; `build/main.js` regenerated.

- [ ] **Step 3: Commit**

```bash
git add src/js/main.js build/
git commit -m "feat(toc): скролл-спай, прогресс чтения и мобильная гармошка"
```

---

### Task 5: Integration verification (browser)

**Files:** none (verification only)

- [ ] **Step 1: Open a real article** with ≥3 `<h2>` sections in the browser (Local by Flywheel site URL). Confirm:
  - Sidebar shows the progress card, "В этой статье", and one link per H2.
  - Ids are injected on the H2s (inspect DOM); clicking a link jumps with header offset (no overlap).
  - Scrolling highlights the current section (`.is-active`); progress bar and "N из M" update.

- [ ] **Step 2: Resize below 1024px.** Confirm the aside becomes a collapsible "Содержание" bar at the top, the progress card is hidden, the list toggles open/closed, and tapping a link collapses it.

- [ ] **Step 3: Open an article with fewer than 3 H2s.** Confirm it renders single-column, visually unchanged (no aside, no grid).

- [ ] **Step 4 (optional): Verify with Playwright** by navigating to an article, taking a screenshot at desktop and mobile widths, and checking `.toc__link.is-active` toggles on scroll.

---

## Notes

- Native smooth scrolling is already enabled globally (`html { scroll-behavior: smooth }`); the H2 `scroll-margin-top` supplies the sticky-header offset, so the JS does not manage scroll animation.
- `sputnik_plus_build_toc()` returns the original HTML untouched when there are fewer than 3 headings, so the content column always echoes `$sputnik_toc['html']` safely.
