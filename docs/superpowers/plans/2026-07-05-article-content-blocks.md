# Контент-блоки статьи — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Добавить три элемента тела статьи — ACF-блоки «Важно знать» и «Миф/Реальность» и стиль-вариацию «Спутник» для ядрового `core/quote`.

**Architecture:** Каждый ACF-блок — папка `src/blocks/block-<name>/` (`block.json` c секцией `acf`, `render.php`, `index.js`, `style.scss`, `editor.scss`) + группа полей в `acf-json/group_*.json` (location `block == sputnik/<name>`). Регистрация блоков автоматическая через манифест сборки. Цитата — не новый блок, а `register_block_style('core/quote', …)` в `functions.php` + SCSS в общей теме.

**Tech Stack:** WordPress, ACF (blockVersion 3, autoInlineEditing), @wordpress/scripts (webpack), SCSS.

## Global Constraints

- Комментарии в коде — на русском (конвенция репозитория).
- ACF-блоки следуют паттерну `sputnik/faq`: `"acf": { "blockVersion": 3, "autoInlineEditing": true, "mode": "preview", "renderTemplate": "render.php" }`.
- Текстовые поля — типы `text`/`textarea` (правятся и инлайн в превью, и во вкладке ACF). WYSIWYG не используем.
- Имя блока в namespace: `sputnik/<name>`. Категория: `layout`.
- Блоки не ограничены типами записей (доступны везде).
- `render.php` при полностью пустых полях делает ранний `return` (ничего не выводит).
- Точные hex берём из макета `chdJU`; rem считаем от 16px.
- Сборка: `npm run build`. Линт PHP: `wp-env run cli` из `/Volumes/Webwork/sputnik-vet` (см. память проекта) — при недоступности Docker линт пропускаем и отмечаем это.
- Нет автотест-фреймворка: гейт задачи = успешная сборка + `php -l`; финальная визуальная проверка — вручную в редакторе.

---

### Task 1: Блок `sputnik/important` («Важно знать»)

**Files:**
- Create: `src/blocks/block-important/block.json`
- Create: `src/blocks/block-important/index.js`
- Create: `src/blocks/block-important/render.php`
- Create: `src/blocks/block-important/style.scss`
- Create: `src/blocks/block-important/editor.scss`
- Create: `acf-json/group_68a1c0de10001.json`

**Interfaces:**
- Produces: блок `sputnik/important` с ACF-полями `title` (text), `content` (textarea).

- [ ] **Step 1: Создать `block.json`**

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "sputnik/important",
    "title": "Важно знать",
    "category": "layout",
    "icon": "info-outline",
    "description": "Информационная плашка (callout) для тела статьи",
    "keywords": ["важно", "callout", "инфо", "заметка"],
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

- [ ] **Step 2: Создать `index.js`** (импорт стилей для сборки)

```js
import './editor.scss';
import './style.scss';
```

- [ ] **Step 3: Создать `render.php`**

```php
<?php
/**
 * Блок «Важно знать» — информационная плашка (callout).
 */

$title   = get_field( 'title' );
$content = get_field( 'content' );

if ( ! $title && ! $content ) {
    return;
}
?>

<div class="imp">
    <span class="imp__icon" aria-hidden="true">i</span>
    <div class="imp__body">
        <?php if ( $title ) : ?>
            <p class="imp__title"><?php echo esc_html( $title ); ?></p>
        <?php endif; ?>
        <?php if ( $content ) : ?>
            <div class="imp__text"><?php echo wp_kses_post( wpautop( $content ) ); ?></div>
        <?php endif; ?>
    </div>
</div>
```

- [ ] **Step 4: Создать `editor.scss`**

```scss
/* Стили редактора */
```

- [ ] **Step 5: Создать `style.scss`** (цвета из макета `F3LEz`)

```scss
.imp {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  margin: 2rem 0;
  padding: 1.5rem 1.75rem;
  background: #fbf9f5;
  border-radius: 0.875rem;

  &__icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 50%;
    font-family: 'Georgia', serif;
    font-style: italic;
    font-size: 1.125rem;
    font-weight: 700;
    color: #143358;
  }

  &__body {
    flex: 1;
    min-width: 0;
  }

  &__title {
    margin: 0 0 0.5rem;
    font-family: 'Onest', sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: #143358;
  }

  &__text {
    font-family: 'Onest', sans-serif;
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #2e3e55;

    p {
      margin: 0 0 0.75rem;

      &:last-child {
        margin-bottom: 0;
      }
    }
  }
}
```

- [ ] **Step 6: Создать ACF-группу `acf-json/group_68a1c0de10001.json`**

```json
{
    "key": "group_68a1c0de10001",
    "title": "Блок «Важно знать»",
    "fields": [
        {
            "key": "field_68a1c0de10002",
            "label": "Заголовок",
            "name": "title",
            "type": "text",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "default_value": "Важно знать",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "",
            "append": ""
        },
        {
            "key": "field_68a1c0de10003",
            "label": "Текст",
            "name": "content",
            "type": "textarea",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "default_value": "",
            "maxlength": "",
            "allow_in_bindings": 0,
            "rows": 4,
            "placeholder": "",
            "new_lines": "wpautop"
        }
    ],
    "location": [
        [
            { "param": "block", "operator": "==", "value": "sputnik\/important" }
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
    "show_in_rest": 0
}
```

Примечание: поле `content` имеет `new_lines: "wpautop"`, поэтому `get_field('content')` возвращает уже с `<p>`; в `render.php` дополнительный `wpautop` безопасен (не задваивает `<p>` на уже обёрнутом тексте, но для простого текста добавит). Если в превью появится двойное оборачивание — убрать `wpautop(...)` из `render.php`, оставив `wp_kses_post( $content )`.

- [ ] **Step 7: Собрать проект**

Run: `npm run build`
Expected: сборка завершается без ошибок; появляется каталог `build/blocks/sputnik-important/` с `block.json`, `render.php`, `index.css`, `style-index.css`.

Проверить: `ls build/blocks/sputnik-important/`

- [ ] **Step 8: Линт PHP**

Run: `cd /Volumes/Webwork/sputnik-vet && wp-env run cli php -l wp-content/themes/sputnik-new/build/blocks/sputnik-important/render.php`
Expected: `No syntax errors detected`.
Если Docker/wp-env недоступен — пропустить и отметить в отчёте.

- [ ] **Step 9: Commit**

```bash
git add src/blocks/block-important acf-json/group_68a1c0de10001.json
git commit -m "feat(blocks): блок «Важно знать» (sputnik/important)"
```

---

### Task 2: Блок `sputnik/myth` («Миф / Реальность»)

**Files:**
- Create: `src/blocks/block-myth/block.json`
- Create: `src/blocks/block-myth/index.js`
- Create: `src/blocks/block-myth/render.php`
- Create: `src/blocks/block-myth/style.scss`
- Create: `src/blocks/block-myth/editor.scss`
- Create: `acf-json/group_68a1c0de20001.json`

**Interfaces:**
- Produces: блок `sputnik/myth` с полями `heading`, `myth_label`, `myth_text`, `reality_label`, `reality_text`.

- [ ] **Step 1: Создать `block.json`**

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "sputnik/myth",
    "title": "Миф и реальность",
    "category": "layout",
    "icon": "editor-contract",
    "description": "Заголовок мифа и две карточки: миф и реальность",
    "keywords": ["миф", "реальность", "сравнение"],
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

- [ ] **Step 3: Создать `render.php`**

```php
<?php
/**
 * Блок «Миф и реальность» — заголовок мифа + две карточки-сравнения.
 */

$heading       = get_field( 'heading' );
$myth_label    = get_field( 'myth_label' );
$myth_text     = get_field( 'myth_text' );
$reality_label = get_field( 'reality_label' );
$reality_text  = get_field( 'reality_text' );

if ( ! $heading && ! $myth_text && ! $reality_text ) {
    return;
}
?>

<div class="myth">
    <?php if ( $heading ) : ?>
        <h3 class="myth__heading"><?php echo esc_html( $heading ); ?></h3>
    <?php endif; ?>

    <div class="myth__cards">
        <div class="myth__card myth__card--myth">
            <span class="myth__badge myth__badge--myth" aria-hidden="true">✕</span>
            <?php if ( $myth_label ) : ?>
                <span class="myth__label myth__label--myth"><?php echo esc_html( $myth_label ); ?></span>
            <?php endif; ?>
            <?php if ( $myth_text ) : ?>
                <div class="myth__text"><?php echo wp_kses_post( wpautop( $myth_text ) ); ?></div>
            <?php endif; ?>
        </div>

        <div class="myth__card myth__card--reality">
            <span class="myth__badge myth__badge--reality" aria-hidden="true">✓</span>
            <?php if ( $reality_label ) : ?>
                <span class="myth__label myth__label--reality"><?php echo esc_html( $reality_label ); ?></span>
            <?php endif; ?>
            <?php if ( $reality_text ) : ?>
                <div class="myth__text"><?php echo wp_kses_post( wpautop( $reality_text ) ); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Создать `editor.scss`**

```scss
/* Стили редактора */
```

- [ ] **Step 5: Создать `style.scss`** (цвета из макета `OO7jV` / `UDJTE` / `g76wc`)

```scss
.myth {
  margin: 2rem 0;

  &__heading {
    margin: 0 0 1rem;
    font-family: 'Onest', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.3;
    color: #143358;
  }

  &__cards {
    display: flex;
    gap: 1rem;

    @media (max-width: 800px) {
      flex-direction: column;
    }
  }

  &__card {
    flex: 1;
    min-width: 0;
    padding: 1.5rem;
    border-radius: 0.875rem;

    &--myth {
      background: #fbf2f2;
    }

    &--reality {
      background: #eef6f2;
    }
  }

  &__badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;

    &--myth {
      background: #e6bbc0;
      color: #b93a46;
    }

    &--reality {
      background: #bfdbc9;
      color: #2f7d5b;
    }
  }

  &__label {
    display: block;
    margin: 0.75rem 0 0.5rem;
    font-family: 'Onest', sans-serif;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;

    &--myth {
      color: #b93a46;
    }

    &--reality {
      color: #2f7d5b;
    }
  }

  &__text {
    font-family: 'Onest', sans-serif;
    font-size: 0.9375rem;
    line-height: 1.55;
    color: #2e3e55;

    p {
      margin: 0 0 0.75rem;

      &:last-child {
        margin-bottom: 0;
      }
    }
  }
}
```

- [ ] **Step 6: Создать ACF-группу `acf-json/group_68a1c0de20001.json`**

```json
{
    "key": "group_68a1c0de20001",
    "title": "Блок «Миф и реальность»",
    "fields": [
        {
            "key": "field_68a1c0de20002",
            "label": "Заголовок мифа",
            "name": "heading",
            "type": "text",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "default_value": "Миф 1. ",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "",
            "append": ""
        },
        {
            "key": "field_68a1c0de20003",
            "label": "Подпись карточки «Миф»",
            "name": "myth_label",
            "type": "text",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "50", "class": "", "id": "" },
            "default_value": "Миф",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "",
            "append": ""
        },
        {
            "key": "field_68a1c0de20005",
            "label": "Подпись карточки «Реальность»",
            "name": "reality_label",
            "type": "text",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "50", "class": "", "id": "" },
            "default_value": "Реальность",
            "maxlength": "",
            "allow_in_bindings": 0,
            "placeholder": "",
            "prepend": "",
            "append": ""
        },
        {
            "key": "field_68a1c0de20004",
            "label": "Текст «Миф»",
            "name": "myth_text",
            "type": "textarea",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "50", "class": "", "id": "" },
            "default_value": "",
            "maxlength": "",
            "allow_in_bindings": 0,
            "rows": 4,
            "placeholder": "",
            "new_lines": "wpautop"
        },
        {
            "key": "field_68a1c0de20006",
            "label": "Текст «Реальность»",
            "name": "reality_text",
            "type": "textarea",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "50", "class": "", "id": "" },
            "default_value": "",
            "maxlength": "",
            "allow_in_bindings": 0,
            "rows": 4,
            "placeholder": "",
            "new_lines": "wpautop"
        }
    ],
    "location": [
        [
            { "param": "block", "operator": "==", "value": "sputnik\/myth" }
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
    "show_in_rest": 0
}
```

- [ ] **Step 7: Собрать проект**

Run: `npm run build`
Expected: без ошибок; появляется `build/blocks/sputnik-myth/`.
Проверить: `ls build/blocks/sputnik-myth/`

- [ ] **Step 8: Линт PHP**

Run: `cd /Volumes/Webwork/sputnik-vet && wp-env run cli php -l wp-content/themes/sputnik-new/build/blocks/sputnik-myth/render.php`
Expected: `No syntax errors detected`. Недоступен Docker — пропустить, отметить.

- [ ] **Step 9: Commit**

```bash
git add src/blocks/block-myth acf-json/group_68a1c0de20001.json
git commit -m "feat(blocks): блок «Миф и реальность» (sputnik/myth)"
```

---

### Task 3: Стиль-вариация «Спутник» для `core/quote`

**Files:**
- Modify: `functions.php` (добавить регистрацию стиля перед строкой `add_action( 'init', 'sputnik_plus_register_blocks', 9 );` или рядом с блочной регистрацией)
- Create: `src/scss/parts/quote.scss`
- Modify: `src/scss/main.scss` (добавить `@forward './parts/quote.scss';`)
- Modify: `src/scss/editor.scss` (добавить те же правила для превью в редакторе)

**Interfaces:**
- Consumes: ничего из прошлых задач.
- Produces: стиль `is-style-sputnik` для `core/quote`.

- [ ] **Step 1: Зарегистрировать стиль блока в `functions.php`**

Добавить функцию и хук (рядом с прочими регистрациями блоков):

```php
/**
 * Регистрация стиля-вариации «Спутник» для ядрового блока «Цитата».
 */
function sputnik_plus_register_block_styles() {
	register_block_style(
		'core/quote',
		[
			'name'  => 'sputnik',
			'label' => __( 'Спутник', 'sputnik-plus' ),
		]
	);
}
add_action( 'init', 'sputnik_plus_register_block_styles' );
```

- [ ] **Step 2: Создать `src/scss/parts/quote.scss`** (цвета из макета `MjNiL`)

```scss
.wp-block-quote.is-style-sputnik {
  margin: 2rem 0;
  padding: 0 0 0 2rem;
  border-left: 4px solid #e63946;
  background: none;
  font-style: normal;

  p {
    font-family: 'Georgia', serif;
    font-style: italic;
    font-size: 1.3125rem;
    line-height: 1.64;
    color: #143358;
  }

  cite {
    display: block;
    margin-top: 1.25rem;
    font-family: 'Onest', sans-serif;
    font-style: normal;
    font-size: 0.8125rem;
    font-weight: 500;
    letter-spacing: 0.3px;
    color: #6b7a8f;
  }
}
```

- [ ] **Step 3: Подключить партиал во фронт-сборке — `src/scss/main.scss`**

Добавить строку в блок `@forward` (после `@forward './parts/single-post.scss';`):

```scss
@forward './parts/quote.scss';
```

- [ ] **Step 4: Добавить те же правила в редактор — `src/scss/editor.scss`**

Дописать в конец файла:

```scss
.wp-block-quote.is-style-sputnik {
  margin: 2rem 0;
  padding: 0 0 0 2rem;
  border-left: 4px solid #e63946;
  background: none;
  font-style: normal;

  p {
    font-family: 'Georgia', serif;
    font-style: italic;
    font-size: 1.3125rem;
    line-height: 1.64;
    color: #143358;
  }

  cite {
    display: block;
    margin-top: 1.25rem;
    font-family: 'Onest', sans-serif;
    font-style: normal;
    font-size: 0.8125rem;
    font-weight: 500;
    letter-spacing: 0.3px;
    color: #6b7a8f;
  }
}
```

- [ ] **Step 5: Собрать проект**

Run: `npm run build`
Expected: без ошибок; `build/styles/main.css` и `build/styles/editor.css` содержат `.wp-block-quote.is-style-sputnik`.
Проверить: `grep -c "is-style-sputnik" build/styles/main.css build/styles/editor.css`

- [ ] **Step 6: Линт PHP**

Run: `cd /Volumes/Webwork/sputnik-vet && wp-env run cli php -l wp-content/themes/sputnik-new/functions.php`
Expected: `No syntax errors detected`. Недоступен Docker — пропустить, отметить.

- [ ] **Step 7: Commit**

```bash
git add functions.php src/scss/parts/quote.scss src/scss/main.scss src/scss/editor.scss
git commit -m "feat(blocks): стиль-вариация «Спутник» для core/quote"
```

---

### Task 4: Визуальная проверка в редакторе (ручная)

**Files:** нет изменений (проверка).

- [ ] **Step 1: Открыть редактор записи и вставить блоки**

В админке WordPress создать/открыть запись, через инсёртер добавить:
- «Важно знать» (`sputnik/important`),
- «Миф и реальность» (`sputnik/myth`),
- «Цитата» (`core/quote`) и в панели стилей выбрать вариацию «Спутник».

- [ ] **Step 2: Проверить инлайн-редактирование**

Ожидается: в превью блоков «Важно знать» и «Миф» тексты (`title`, `content`, `heading`, подписи, тексты карточек) правятся кликом прямо на канвасе; те же поля доступны во вкладке ACF. Цитата и подпись правятся как обычный ядровый блок.

- [ ] **Step 3: Сверить с макетом**

Ожидается визуальное совпадение с `chdJU`:
- «Важно знать»: бежевая плашка, круглая иконка «i», заголовок #143358, текст.
- «Миф»: заголовок + две карточки (красноватая ✕ / зелёная ✓), на узкой ширине — в столбец.
- «Цитата»: левая красная граница, курсив, подпись автора.

- [ ] **Step 4: Проверить пустое состояние**

Ожидается: пустой блок «Важно знать»/«Миф» на фронте не выводит разметку (ранний `return`).

- [ ] **Step 5: Зафиксировать результат проверки**

Записать итог (что совпало, где инлайн сработал/не сработал). Если для textarea инлайн не активировался — это допустимо по спеке (правится во вкладке ACF); при двойном `<p>` в тексте — убрать `wpautop(...)` из соответствующего `render.php`, оставив `wp_kses_post( $field )`, и пересобрать.

---

## Self-Review

**Покрытие спеки:**
- «Важно знать» → Task 1. ✓
- «Миф/Реальность», один блок = один миф → Task 2. ✓
- Кастомизация core/quote → Task 3. ✓
- Конвенция ACF-блоков, автоинлайн, textarea → все задачи, Global Constraints. ✓
- Проверка (build, php -l, инлайн, пустое состояние, визуал) → Task 1–4. ✓
- Вне объёма (новость, ограничения по типам) → не включено. ✓

**Плейсхолдеры:** отсутствуют — весь код приведён целиком.

**Согласованность типов/имён:** имена полей (`title`, `content`, `heading`, `myth_label`, `myth_text`, `reality_label`, `reality_text`) совпадают между `render.php` и ACF-группами; классы CSS совпадают между `render.php` и `style.scss`; namespace `sputnik/<name>` совпадает между `block.json` и location ACF-групп.
