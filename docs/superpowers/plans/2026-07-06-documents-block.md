# Блок «Документы» (`sputnik/documents`) — план реализации

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Добавить ACF-блок Gutenberg «Документы» — сетка плиток с иконкой по типу файла, названием, типом, размером и ссылкой на скачивание; в стилистике сайта, адаптивно.

**Architecture:** Серверный рендер через ACF (`render.php`), поля из синхронизируемой группы `acf-json/`. Данные документа берутся из ACF-поля File (url/filename/filesize). Стили — SCSS, компилируется `@wordpress/scripts`. Блок авто-обнаруживается билдом по `src/blocks/*/block.json`.

**Tech Stack:** WordPress 6.8+, ACF (ACF Blocks v3, apiVersion 3), PHP, SCSS, `@wordpress/scripts` (webpack).

## Global Constraints

- Комментарии в коде — на русском.
- Имя блока: `sputnik/documents`; namespace плиток BEM: `documents__*`.
- apiVersion 3, ACF `blockVersion 3`, `mode: preview`, `renderTemplate: render.php`.
- Цвета/шрифты — из `src/scss/root.scss` (CSS-переменные) и `Onest`.
- Никаких внешних иконочных ассетов — иконки типов inline.
- Автотестов в проекте нет; верификация — `php -l`, `npm run build`, ручная проверка в редакторе/на фронте.
- Экранирование вывода: `esc_url`, `esc_html`, `esc_attr`, `wp_kses_post`.
- Брейкпоинт адаптива — `max-width: 800px` (как в других блоках).

---

### Task 1: Каркас блока (регистрация, пустой рендер, стили)

Создаём структуру файлов блока, чтобы он появился в редакторе и собирался билдом. Рендер на этом шаге минимальный — заголовок из ACF, чтобы подтвердить регистрацию.

**Files:**
- Create: `src/blocks/block-documents/block.json`
- Create: `src/blocks/block-documents/index.js`
- Create: `src/blocks/block-documents/render.php`
- Create: `src/blocks/block-documents/style.scss`
- Create: `src/blocks/block-documents/editor.scss`
- Create: `acf-json/group_68a2d0c1a0001.json`

**Interfaces:**
- Produces: блок `sputnik/documents` с ACF-полями `title` (text) и `items` (repeater: `file` type file/array, `title_override` text). Классы разметки: `.documents`, `.documents__title`, `.documents__grid`.

- [ ] **Step 1: Создать `block.json`**

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "sputnik/documents",
    "title": "Документы",
    "category": "layout",
    "icon": "media-document",
    "description": "Плитки документов с иконкой, названием, размером и ссылкой на скачивание",
    "keywords": ["документы", "файлы", "скачать", "download"],
    "acf": {
        "blockVersion": 3,
        "autoInlineEditing": true,
        "mode": "preview",
        "renderTemplate": "render.php"
    },
    "style": "file:./style-index.css",
    "editorStyle": "file:./index.css",
    "editorScript": "file:./index.js"
}
```

- [ ] **Step 2: Создать `index.js`**

```js
import './editor.scss';
import './style.scss';
```

- [ ] **Step 3: Создать `editor.scss`**

```scss
/* Стили редактора */
```

- [ ] **Step 4: Создать минимальный `render.php`**

```php
<?php
/**
 * Блок «Документы» — сетка плиток с иконкой, названием, размером и ссылкой.
 */

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="documents">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="documents__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>
    </div>
</section>
```

- [ ] **Step 5: Создать минимальный `style.scss`**

```scss
.documents {
  margin: 3rem 0;

  &__title {
    margin: 0 0 1.5rem;
    font-family: 'Onest', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--color-text-primary);
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;

    @media (max-width: 800px) {
      grid-template-columns: 1fr;
    }
  }
}
```

- [ ] **Step 6: Создать группу полей ACF `acf-json/group_68a2d0c1a0001.json`**

```json
{
    "key": "group_68a2d0c1a0001",
    "title": "Блок «Документы»",
    "fields": [
        {
            "key": "field_68a2d0c1a0002",
            "label": "Заголовок",
            "name": "title",
            "aria-label": "",
            "type": "text",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "default_value": "Документы",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "",
            "append": ""
        },
        {
            "key": "field_68a2d0c1a0003",
            "label": "Документы",
            "name": "items",
            "aria-label": "",
            "type": "repeater",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "layout": "block",
            "pagination": 0,
            "min": 0,
            "max": 0,
            "collapsed": "field_68a2d0c1a0004",
            "button_label": "Добавить документ",
            "rows_per_page": 20,
            "sub_fields": [
                {
                    "key": "field_68a2d0c1a0004",
                    "label": "Файл",
                    "name": "file",
                    "aria-label": "",
                    "type": "file",
                    "instructions": "Загрузите файл документа",
                    "required": 1,
                    "conditional_logic": 0,
                    "wrapper": { "width": "60", "class": "", "id": "" },
                    "return_format": "array",
                    "library": "all",
                    "min_size": "",
                    "max_size": "",
                    "mime_types": "",
                    "allow_in_bindings": 0,
                    "parent_repeater": "field_68a2d0c1a0003"
                },
                {
                    "key": "field_68a2d0c1a0005",
                    "label": "Название (необязательно)",
                    "name": "title_override",
                    "aria-label": "",
                    "type": "text",
                    "instructions": "Необязательно. Красивое название вместо имени файла",
                    "required": 0,
                    "conditional_logic": 0,
                    "wrapper": { "width": "40", "class": "", "id": "" },
                    "default_value": "",
                    "maxlength": "",
                    "allow_in_bindings": 0,
                    "placeholder": "",
                    "prepend": "",
                    "append": "",
                    "parent_repeater": "field_68a2d0c1a0003"
                }
            ]
        }
    ],
    "location": [
        [
            { "param": "block", "operator": "==", "value": "sputnik\/documents" }
        ]
    ],
    "menu_order": 0,
    "position": "normal",
    "style": "default",
    "label_placement": "top",
    "instruction_placement": "label",
    "hide_on_screen": "",
    "active": true,
    "description": "",
    "show_in_rest": 0,
    "display_title": "",
    "allow_ai_access": 1,
    "ai_description": "",
    "modified": 1783000000
}
```

- [ ] **Step 7: Проверить синтаксис PHP**

Run: `php -l src/blocks/block-documents/render.php`
(Если PHP нет в PATH: `wp-env run cli php -l wp-content/themes/sputnik-new/src/blocks/block-documents/render.php` из `/Volumes/Webwork/sputnik-vet`.)
Expected: `No syntax errors detected`

- [ ] **Step 8: Проверить сборку**

Run: `npm run build`
Expected: сборка без ошибок; создаётся каталог `build/blocks/block-documents/` с `block.json`, `render.php`, `style-index.css`, `index.css`, `index.js`.

- [ ] **Step 9: Проверить JSON группы полей**

Run: `node -e "require('./acf-json/group_68a2d0c1a0001.json'); console.log('json ok')"`
Expected: `json ok`

- [ ] **Step 10: Commit**

```bash
git add src/blocks/block-documents acf-json/group_68a2d0c1a0001.json
git commit -m "feat(documents): каркас блока «Документы» и группа полей ACF"
```

---

### Task 2: Хелпер типа файла + полный рендер плиток

Добавляем определение типа файла по расширению и полную разметку плиток: иконка, название, тип+размер, стрелка скачивания.

**Files:**
- Modify: `src/blocks/block-documents/render.php`

**Interfaces:**
- Consumes: ACF-поля `title`, `items` (`file` = массив с `url`, `filename`, `title`, `filesize`; `title_override` = строка).
- Produces: функция `sputnik_document_ext( string $filename ): array` → `[ 'type' => <класс>, 'label' => <МЕТКА> ]`. Разметка `.documents__item`, `.documents__icon documents__icon--{type}`, `.documents__body`, `.documents__name`, `.documents__meta`, `.documents__download`.

- [ ] **Step 1: Заменить содержимое `render.php` на полный рендер**

```php
<?php
/**
 * Блок «Документы» — сетка плиток с иконкой, названием, размером и ссылкой.
 */

/**
 * Определяет тип документа по расширению имени файла.
 *
 * @param string $filename Имя файла.
 * @return array{type:string,label:string} Класс-модификатор и текстовая метка.
 */
if ( ! function_exists( 'sputnik_document_ext' ) ) {
    function sputnik_document_ext( $filename ) {
        $ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );

        $map = array(
            'pdf'  => 'pdf',
            'doc'  => 'doc',
            'docx' => 'doc',
            'rtf'  => 'doc',
            'odt'  => 'doc',
            'xls'  => 'xls',
            'xlsx' => 'xls',
            'csv'  => 'xls',
            'ods'  => 'xls',
            'ppt'  => 'ppt',
            'pptx' => 'ppt',
            'odp'  => 'ppt',
            'zip'  => 'zip',
            'rar'  => 'zip',
            '7z'   => 'zip',
            'jpg'  => 'img',
            'jpeg' => 'img',
            'png'  => 'img',
            'gif'  => 'img',
            'webp' => 'img',
            'svg'  => 'img',
        );

        $type  = isset( $map[ $ext ] ) ? $map[ $ext ] : 'file';
        $label = $ext ? strtoupper( $ext ) : 'FILE';

        return array(
            'type'  => $type,
            'label' => $label,
        );
    }
}

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="documents">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="documents__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $items ) : ?>
            <div class="documents__grid">
                <?php
                foreach ( $items as $item ) :
                    $file = isset( $item['file'] ) ? $item['file'] : null;

                    if ( ! $file || empty( $file['url'] ) ) {
                        continue;
                    }

                    $filename = ! empty( $file['filename'] ) ? $file['filename'] : ( ! empty( $file['title'] ) ? $file['title'] : $file['url'] );
                    $meta     = sputnik_document_ext( $filename );
                    $name     = ! empty( $item['title_override'] ) ? $item['title_override'] : $filename;
                    $size     = ( isset( $file['filesize'] ) && $file['filesize'] ) ? size_format( (int) $file['filesize'] ) : '';
                    ?>
                    <a class="documents__item" href="<?php echo esc_url( $file['url'] ); ?>" download>
                        <span class="documents__icon documents__icon--<?php echo esc_attr( $meta['type'] ); ?>">
                            <?php echo esc_html( $meta['label'] ); ?>
                        </span>
                        <span class="documents__body">
                            <span class="documents__name"><?php echo esc_html( $name ); ?></span>
                            <?php if ( $size ) : ?>
                                <span class="documents__meta"><?php echo esc_html( $meta['label'] . ' · ' . $size ); ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="documents__download" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 3v9m0 0 3.5-3.5M10 12 6.5 8.5M4 15h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
```

- [ ] **Step 2: Проверить синтаксис PHP**

Run: `php -l src/blocks/block-documents/render.php`
(Fallback: `wp-env run cli php -l wp-content/themes/sputnik-new/src/blocks/block-documents/render.php` из `/Volumes/Webwork/sputnik-vet`.)
Expected: `No syntax errors detected`

- [ ] **Step 3: Собрать и убедиться, что render.php попал в build**

Run: `npm run build`
Expected: сборка без ошибок; `build/blocks/block-documents/render.php` обновлён.

- [ ] **Step 4: Commit**

```bash
git add src/blocks/block-documents/render.php
git commit -m "feat(documents): рендер плиток и хелпер типа файла"
```

---

### Task 3: Полные стили плиток (иконки по типу, hover, адаптив)

Дописываем `style.scss`: карточка-ссылка, цветные иконки типов, состояния hover/focus, адаптив.

**Files:**
- Modify: `src/blocks/block-documents/style.scss`

**Interfaces:**
- Consumes: классы из Task 2 (`.documents__item`, `.documents__icon--{pdf,doc,xls,ppt,zip,img,file}`, `.documents__body`, `.documents__name`, `.documents__meta`, `.documents__download`).

- [ ] **Step 1: Заменить содержимое `style.scss` на полную версию**

```scss
.documents {
  margin: 3rem 0;

  &__title {
    margin: 0 0 1.5rem;
    font-family: 'Onest', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--color-text-primary);
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;

    @media (max-width: 800px) {
      grid-template-columns: 1fr;
    }
  }

  &__item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 0.875rem;
    text-decoration: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;

    &:hover,
    &:focus-visible {
      border-color: var(--color-accent);
      box-shadow: 0 8px 24px rgba(16, 116, 188, 0.12);

      .documents__download {
        color: var(--color-accent);
        transform: translateY(2px);
      }
    }
  }

  &__icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 0.625rem;
    font-family: 'Onest', sans-serif;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #fff;
    background: var(--color-text-secondary);

    &--pdf { background: #FF6060; }
    &--doc { background: #1074BC; }
    &--xls { background: #2f9e6b; }
    &--ppt { background: #e2803a; }
    &--zip { background: #7b6ea8; }
    &--img { background: #12a6a6; }
    &--file { background: #9BA8B5; }
  }

  &__body {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
    flex: 1;
  }

  &__name {
    font-family: 'Onest', sans-serif;
    font-size: 0.9375rem;
    font-weight: 600;
    line-height: 1.35;
    color: var(--color-text-primary);
    overflow-wrap: anywhere;
  }

  &__meta {
    font-family: 'Onest', sans-serif;
    font-size: 0.8125rem;
    line-height: 1.3;
    color: var(--color-text-secondary);
  }

  &__download {
    flex-shrink: 0;
    color: var(--color-text-tertiary);
    transition: color 0.2s ease, transform 0.2s ease;
  }
}
```

- [ ] **Step 2: Собрать стили**

Run: `npm run build`
Expected: сборка без ошибок; `build/blocks/block-documents/style-index.css` содержит `.documents__item`.

- [ ] **Step 3: Проверить наличие правил в собранном CSS**

Run: `grep -c "documents__icon--pdf" build/blocks/block-documents/style-index.css`
Expected: `1` (или больше).

- [ ] **Step 4: Commit**

```bash
git add src/blocks/block-documents/style.scss
git commit -m "style(documents): плитки, цветные иконки типов, hover и адаптив"
```

---

### Task 4: Ручная проверка в редакторе и на фронте

Финальная приёмка вживую (Local by Flywheel / браузер). Автотестов нет — проверяем поведение вручную.

**Files:** — (изменений кода нет; при находках — точечные правки в соответствующих файлах Task 1–3.)

- [ ] **Step 1: Убедиться, что билд свежий**

Run: `npm run build`
Expected: без ошибок.

- [ ] **Step 2: Проверка в редакторе Gutenberg**

Открыть страницу в админке, добавить блок «Документы». Ожидается:
- Блок находится в категории «layout» по названию «Документы».
- Отображаются поля: «Заголовок», repeater «Документы» с «Файл» и «Название (необязательно)».
- Загрузка файла через медиабиблиотеку работает; превью блока рендерится без ошибок.

- [ ] **Step 3: Проверка на фронте**

Открыть страницу с блоком. Ожидается:
- Плитки в сетке; на ширине ≤800px — одна колонка.
- Иконка соответствует типу (напр. загруженный PDF → красная иконка «PDF»).
- В подписи — тип и человекочитаемый размер (напр. «PDF · 1,2 МБ»).
- Клик по плитке скачивает/открывает файл (атрибут `download`, корректный `href`).
- Hover: подсветка границы акцентом, тень, сдвиг стрелки.
- Название из «Название (необязательно)» переопределяет имя файла, если задано.

- [ ] **Step 4: Зафиксировать возможные правки**

Если по итогам проверки нужны правки — внести в соответствующие файлы и закоммитить:

```bash
git add -A
git commit -m "fix(documents): правки по итогам ручной проверки"
```

Если правок нет — задача завершена без коммита.

---

## Self-Review

- **Покрытие спецификации:** поля ACF (title, file, title_override) — Task 1; хелпер типа + рендер (иконка, название, тип+размер, стрелка, экранирование, пустые проверки) — Task 2; стили (сетка, плитка, цветные иконки, hover, адаптив ≤800px) — Task 3; приёмка (редактор, фронт, `php -l`, `npm run build`) — Task 4. Вне области (категории, пагинация, внешние иконки, ручной ввод размера) не реализуется — соответствует спецификации.
- **Плейсхолдеры:** отсутствуют — весь код приведён целиком.
- **Согласованность типов:** `sputnik_document_ext()` возвращает `['type','label']`, потребляется в Task 2; классы `documents__icon--{type}` из Task 2 совпадают с модификаторами в Task 3 (`pdf/doc/xls/ppt/zip/img/file`). Имя файла берётся из `file['filename']` с фолбэком на `title`/`url`.
