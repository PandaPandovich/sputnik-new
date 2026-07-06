# Блок «Документы» (`sputnik/documents`) — спецификация

**Дата:** 2026-07-06
**Тип:** Новый ACF-блок Gutenberg для темы Sputnik Plus

## Цель

Блок для вывода списка загружаемых документов в виде плиток. Каждая плитка
показывает иконку (по типу файла), название, тип и размер, и является ссылкой
на скачивание файла. Оформление — в стилистике сайта, адаптивное.

## Пользовательские решения

- **Источник данных:** ACF-поле типа **File** (return format — массив). Имя,
  размер и ссылка берутся автоматически. Плюс необязательное поле для красивого
  названия.
- **Иконка:** различается по типу файла (PDF, DOC, XLS, PPT, ZIP, изображение,
  прочее) на основе расширения. Реализуется inline (буквенный/SVG-чип), без
  внешних ассетов.

## Поля ACF (field group)

Location: `block == sputnik/documents`. Файл: `acf-json/group_<id>.json`
(следовать формату существующих групп, напр. `group_687c4d5e6f7a8.json`).

- `title` — text, необязательный. Заголовок секции. `default_value`: «Документы».
- `items` — repeater «Документы», `button_label`: «Добавить документ»,
  `layout: block`, `collapsed` на под-поле `file`:
  - `file` — type `file`, return format `array`, `required: 1`.
    Инструкция: «Загрузите файл документа».
  - `title_override` — text, необязательный. Инструкция: «Необязательно.
    Красивое название вместо имени файла».

## Рендер (`render.php`)

```
$title = get_field('title');
$items = get_field('items');
if ( ! $title && ! $items ) return;
```

Разметка:

- `<section class="documents">` → `<div class="container">`
  - `<h2 class="documents__title">` если задан `$title`.
  - `<div class="documents__grid">` если есть `$items`.
    - Для каждой строки: пропустить, если нет `file` или `file['url']`.
    - `<a class="documents__item" href="{url}" download>`:
      - `.documents__icon documents__icon--{type}` — тип из хелпера.
        Внутри — буква/метка типа (напр. «PDF»).
      - `.documents__body`:
        - `.documents__name` — `title_override` иначе `file['filename']`
          (или `file['title']`).
        - `.documents__meta` — «{ТИП} · {размер}». Тип в верхнем регистре из
          расширения; размер через `size_format( (int) $file['filesize'] )`
          (WordPress → «1,2 МБ»). Если размер отсутствует — не выводить.
      - `.documents__download` — inline-SVG стрелки скачивания (декоративная,
        `aria-hidden`).

Экранирование: `esc_url`, `esc_html`, `esc_attr` как в существующих блоках.

### Хелпер определения типа

Локальная функция в `render.php` (с `function_exists` guard), напр.
`sputnik_document_ext( $filename )`:

- Берёт расширение через `pathinfo( $filename, PATHINFO_EXTENSION )`,
  приводит к нижнему регистру.
- Маппинг в класс-модификатор:
  - `pdf` → `pdf`
  - `doc`, `docx`, `rtf`, `odt` → `doc`
  - `xls`, `xlsx`, `csv`, `ods` → `xls`
  - `ppt`, `pptx`, `odp` → `ppt`
  - `zip`, `rar`, `7z` → `zip`
  - `jpg`, `jpeg`, `png`, `gif`, `webp`, `svg` → `img`
  - иначе → `file`
- Возвращает `[ 'type' => <класс>, 'label' => strtoupper(<расширение или 'FILE'>) ]`.

## Стили (`style.scss`)

Шрифт `Onest`. Цвета из `src/scss/root.scss`:
`--color-text-primary #1D3658`, `--color-text-secondary #627384`,
`--color-accent #1074BC`, `--color-border #DDE4ED`, `--color-surface-50 #F6F6F6`,
`--color-red #FF6060`.

- `.documents` — вертикальные отступы секции (в духе других блоков, напр.
  `margin` / использование `--section-padding` при необходимости).
- `.documents__title` — `Onest`, 700, ~1.5rem, цвет `--color-text-primary`,
  нижний отступ ~1.5rem.
- `.documents__grid` — `display: grid`,
  `grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))`, `gap: 1rem`.
  На `@media (max-width: 800px)` — одна колонка (`1fr`).
- `.documents__item` — `display: flex; align-items: center; gap: 1rem`,
  белый фон, `border: 1px solid var(--color-border)`,
  `border-radius: 0.875rem`, паддинг ~1.25rem, `text-decoration: none`,
  `transition` для границы/тени/трансформа.
  - Hover/focus-visible: `border-color: var(--color-accent)`, лёгкая тень,
    сдвиг стрелки вправо.
- `.documents__icon` — flex-центрирование, ~48×48px, `border-radius: 0.625rem`,
  `flex-shrink: 0`, мелкий жирный текст-метка. Цвет фона/текста по модификатору:
  - `--pdf` — красноватый (семейство `--color-red`).
  - `--doc` — синий (`--color-accent`).
  - `--xls` — зелёный.
  - `--ppt` — оранжевый.
  - `--zip` — фиолетовый/серый.
  - `--img` — бирюзовый.
  - `--file` — нейтральный серый (`--color-surface-50` / `--color-text-secondary`).
- `.documents__body` — `min-width: 0` (для переноса длинных имён),
  `flex: 1`.
- `.documents__name` — `Onest`, 600, ~0.9375rem, цвет `--color-text-primary`,
  перенос длинных слов (`overflow-wrap: anywhere`).
- `.documents__meta` — ~0.8125rem, цвет `--color-text-secondary`,
  верхний отступ ~0.25rem.
- `.documents__download` — ~20px, цвет `--color-text-tertiary`, при hover
  плитки — `--color-accent`, `flex-shrink: 0`.

## Стили редактора (`editor.scss`)

Минимальные, по образцу других блоков (`import` в `index.js`). Достаточно
импортировать те же стили; при необходимости — небольшие правки превью.

## Файлы

- `src/blocks/block-documents/block.json` — apiVersion 3, `name: sputnik/documents`,
  `category: layout`, ACF-настройки (`renderTemplate: render.php`, `mode: preview`,
  `autoInlineEditing: true`), ссылки на стили/скрипт как у `block-myth`/`block-faq`.
- `src/blocks/block-documents/index.js` — импорт `editor.scss` и `style.scss`.
- `src/blocks/block-documents/render.php` — разметка + хелпер типа.
- `src/blocks/block-documents/style.scss` — фронтенд-стили.
- `src/blocks/block-documents/editor.scss` — стили редактора.
- `acf-json/group_<id>.json` — группа полей.

Блок авто-обнаруживается билдом (`src/blocks/*/block.json`). Сборка: `npm run build`.

## Тестирование / приёмка

Автотестов в проекте нет. Проверка:

1. `php -l render.php` — синтаксис (через `wp-env run cli` при поднятом Docker).
2. `npm run build` — сборка без ошибок, появляется `build/blocks/block-documents/`.
3. В редакторе Gutenberg: блок «Документы» доступен, поля ACF отображаются,
   загрузка файла и превью работают.
4. На фронте: плитки в сетке, корректные иконки по типу, размер в человекочитаемом
   формате, ссылка скачивает файл, адаптив на ≤800px — одна колонка.

## Вне области (YAGNI)

- Категории/фильтрация документов.
- Пагинация.
- Внешние иконочные ассеты (используем inline).
- Ручной ввод размера/ссылки (используем данные ACF File).
