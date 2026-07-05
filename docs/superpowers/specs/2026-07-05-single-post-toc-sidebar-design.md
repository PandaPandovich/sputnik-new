# Содержание — sticky TOC sidebar for articles

**Date:** 2026-07-05
**Status:** Approved design
**Scope:** Single-post article layout — auto-generated table-of-contents sidebar.

## Goal

Add a "Содержание" (table of contents) sidebar to single article pages, matching
the Pencil mockup (`Aside - Sidebar`): a reading-progress card, an "В этой статье"
list of section links with the current section highlighted, and a left-accent on
the active item. The list auto-builds from the article's `<h2>` headings and tracks
scroll position (scroll-spy + progress).

## Decisions

- **Item source:** auto-generated from `<h2>` headings in the rendered article
  content. The editor writes normal H2 section headings; the TOC stays in sync.
- **Placement:** template-level sidebar in `single-post.php` (two-column layout),
  not a Gutenberg block inserted into content.
- **Mobile:** collapsible accordion at the top of the article below 1024px.
- **Existing article blocks are not modified.** Sections = H2 only. `faq` (emits H2)
  appears; `myth`'s H3 sub-heading does not. Promoting H3s is explicitly out of scope.

## Architecture & data flow

Rendered in `single-post.php` via two new PHP helpers (mirroring the existing
`sputnik_plus_breadcrumbs` helper style in `functions.php`):

### `sputnik_plus_build_toc( string $html ): array`

- Parse `$html` (the output of `the_content()`) with `DOMDocument`
  (`libxml_use_internal_errors`, UTF-8 safe — wrap with the standard
  `mb_convert_encoding` / meta-charset guard so Cyrillic is preserved).
- Collect every `<h2>` in document order.
- For each `<h2>` without an `id`, inject a slugified `id` derived from its text.
  Transliterate/slugify Cyrillic via `sanitize_title()`; on empty or duplicate
  slug, fall back to `section-N` / append `-2`, `-3`… to guarantee uniqueness.
- Return `[ 'items' => [ ['id' => …, 'text' => …], … ], 'html' => <modified html> ]`.
- **Threshold:** if fewer than **3** headings, return `items => []` and the caller
  renders the article single-column (unchanged behavior). Threshold as a constant.

### `sputnik_plus_render_toc( array $items ): void`

Echoes the aside markup (below). Called only when `items` is non-empty.

### `single-post.php` changes

- Replace the `single-post__content` block with a two-column wrapper:
  `single-post__layout` grid = `minmax(0, 1fr)` article + `~260px` aside.
- Compute `$toc = sputnik_plus_build_toc( apply the_content )`. Because `the_content()`
  echoes, capture via output buffering (or `apply_filters( 'the_content', get_the_content() )`)
  so ids can be injected before output. Render `$toc['html']` into the content column
  and `sputnik_plus_render_toc( $toc['items'] )` into the aside.
- If `$toc['items']` is empty → render the content single-column (no aside, no grid).

## Markup (BEM)

```html
<aside class="toc" data-toc>
  <div class="toc__progress">
    <span class="toc__progress-label">Прогресс чтения</span>
    <span class="toc__progress-count" data-toc-count>1 из 6 разделов</span>
    <div class="toc__progress-bar"><span class="toc__progress-fill" data-toc-fill></span></div>
  </div>

  <button type="button" class="toc__toggle" data-toc-toggle aria-expanded="false">
    Содержание <svg class="toc__chevron">…</svg>
  </button>

  <p class="toc__heading">В этой статье</p>
  <nav class="toc__list">
    <a class="toc__link" href="#section-id">Заголовок раздела</a>
    …
  </nav>
</aside>
```

- Active link gets `.is-active` (bold `#1e4976`, 2px left accent border).
- `data-*` hooks: `data-toc` (root), `data-toc-count`, `data-toc-fill`,
  `data-toc-toggle`. Links use real `href="#id"` anchors.

## Styles (`src/scss/parts/toc.scss`, forwarded from `main.scss`)

Colors from the mockup: accent `#1e4976`, muted `#6b7a8f`, item text `#4a5568`,
track `#e4e9f0`, card bg `#f5f8fb`, radius 10. Reuse existing scss color variables
where equivalents exist; otherwise define locally.

- Desktop (≥1024px): aside `position: sticky; top: <header height + gap>`; progress
  card visible; `.toc__toggle` hidden; list always open.
- Mobile (<1024px): single column, aside sits at top of article body; progress card
  hidden; `.toc__toggle` visible; list collapsed until toggled (`.toc--open`).
- Grid additions live in `single-post.scss` (`.single-post__layout`).

## Behavior (`src/js/main.js`, new IIFE module, guarded by `[data-toc]`)

Follows the existing main.js pattern: self-invoking function, early return if the
root element is absent.

- **Scroll-spy:** `IntersectionObserver` over the H2 anchors; the current section is
  the last heading scrolled past its threshold → toggle `.is-active` on the matching
  link.
- **Reading progress:** `data-toc-fill` width = continuous scroll % through the
  article body (article top → bottom, clamped 0–100). `data-toc-count` text =
  "N из M разделов" where N = current active section index (1-based), M = total.
- **Smooth scroll:** intercept link clicks → `scrollIntoView`/`scrollTo` with an
  offset for the sticky header height; update `location.hash` via
  `history.replaceState` (no jump). Respect `prefers-reduced-motion`.
- **Mobile toggle:** `data-toc-toggle` toggles `.toc--open` and `aria-expanded`;
  selecting a link collapses the list.

## Files touched

- `functions.php` — add `sputnik_plus_build_toc()` + `sputnik_plus_render_toc()`.
- `single-post.php` — two-column layout + helper calls.
- `src/scss/parts/toc.scss` — new partial (forward from `main.scss`).
- `src/scss/parts/single-post.scss` — `.single-post__layout` grid.
- `src/js/main.js` — TOC module.
- No new block registration, no new enqueue (ships in existing main bundle).

## Testing / acceptance

Verify on a real article (via `wp-env` / browser):

- TOC builds from H2s; ids injected on headings; anchors resolve.
- Scroll-spy highlights the current section; progress bar + "N из M" update.
- Smooth scroll lands with correct sticky-header offset; hash updates without jump.
- Mobile (<1024px): accordion toggles; progress card hidden.
- Article with <3 H2s renders single-column, visually unchanged.
- Cyrillic headings slugify correctly; duplicate headings get unique ids.
```
