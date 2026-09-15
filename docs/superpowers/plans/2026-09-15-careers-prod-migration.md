# Миграция страницы «Работа в клинике» на прод — план выполнения

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Перенести на прод шесть блоков `careers-*`/`vacancies`, страницу «Работа в клинике» и форму отклика Contact Form 7 так, чтобы контент совпал с локальным, а не был пересобран вручную.

**Architecture:** Код едет через git (`push` в `main` → `git pull` на сервере). Контент едет через пару WP-CLI-скриптов, обменивающихся коммитимым JSON: экспортёр на локали заменяет числовые ID вложений и формы на плейсхолдеры `{{att:слаг}}` / `{{cf7:слаг}}`, импортёр на проде резолвит их обратно в локальные ID и останавливается с ошибкой, если что-то не нашлось. Медиа-иконки едут файлами в репозитории, фотографии ожидаются уже в медиатеке прода.

**Tech Stack:** WordPress 7.1, ACF PRO 6.8.1 (ACF-блоки, `parse_blocks`/`serialize_block`), Contact Form 7 5.8, WP-CLI (`wp eval-file`), wp-env для локали, cyr2lat и SVG Support на обоих окружениях.

**Spec:** `docs/superpowers/specs/2026-09-15-careers-prod-migration-design.md`

## Статус

**Task 1–5 выполнены и отправлены в `main` 2026-09-15** (коммиты `3e2880f`, `60cdc0b`, `ad7d6cd`, `61fd2f9`). Проверено на локали: негативный тест импортёра останавливает запись, round-trip даёт байт-в-байт тот же манифест, дубликатов вложений не создаётся, страница рендерит все шесть блоков и форму.

**Осталось: Task 6–8 на проде.**

Поправка к шагам с `php -l`: на хосте PHP не установлен, линтить нужно через контейнер — `wp-env run cli php -l wp-content/themes/sputnik-new/<файл>`.

## Global Constraints

- Комментарии в коде — на русском (правило проекта, `CLAUDE.md`).
- Тест-раннера в проекте нет. Проверка каждой задачи — `php -l`, прогон через WP-CLI и `git diff`.
- Скрипты `tools/*.php` запускаются **только** через `wp eval-file` и обязаны начинаться с гварда `if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit(...); }` — как существующий `tools/create-vacancy-form.php`.
- Слаг страницы: `vacancies`. Заголовок: `Работа в клинике`.
- Слаг формы CF7: `vacancy-application`. Получатель писем остаётся `[_site_admin_email]` — не менять.
- Страница на проде создаётся в статусе `draft`; публикация — вручную после осмотра.
- При записи `post_content` обязателен `wp_slash()` — иначе экранирование `\n` внутри JSON-атрибутов блоков потеряет обратный слэш.
- Требования к проду: WordPress ≥ 6.8, ACF PRO ≥ 6.0, Contact Form 7 активен, SVG Support активен, `upload_max_filesize` и `post_max_size` ≥ 10 МБ.
- Локальный WP-CLI вызывается из `/Volumes/Webwork/sputnik-vet` как `wp-env run cli wp …` и требует запущенного Docker (`wp-env start`).
- Путь темы внутри контейнера и на проде: `wp-content/themes/sputnik-new`.

---

### Task 1: Гигиена репозитория

Работаем прямо в `main` — прод тянет именно его. Закрываем мусор, который иначе уедет в коммит: в статусе висят 20 файлов `.playwright-mcp/` и `.DS_Store`.

**Files:**
- Modify: `.gitignore`

**Interfaces:**
- Consumes: —
- Produces: чистый `git status`, в котором видны только файлы миграции.

- [ ] **Step 1: Убедиться, что мусор действительно не игнорируется**

```bash
git status --porcelain | grep -c '^?? \.playwright-mcp/'
```

Ожидается: число больше нуля (сейчас 20). Если ноль — шаг 3 всё равно выполнить, правило на будущее.

- [ ] **Step 2: Дописать `.gitignore`**

Текущее содержимое — три строки (`node_modules`, `./maket`, `.npm-cache`). Привести к виду:

```gitignore
node_modules
./maket
.npm-cache
.playwright-mcp
.DS_Store
```

- [ ] **Step 3: Проверить, что мусор исчез из статуса**

```bash
git status --porcelain | grep -E '^\?\? (\.playwright-mcp/|\.DS_Store)' | wc -l
```

Ожидается: `0`.

- [ ] **Step 4: Коммит**

```bash
git add .gitignore
git commit -m "chore: игнорировать артефакты playwright-mcp и .DS_Store"
```

---

### Task 2: Экспортёр страницы в переносимый JSON

Экспортёр обходит блоки страницы и подменяет числовые ID на плейсхолдеры. Тип поля определяется не по имени, а через `acf_get_field()` по ключу-спутнику (`_image`, `_items_0_icon` и т. д.) — так экспортёр не сломается при переименовании полей и сам найдёт картинки внутри репитеров.

**Files:**
- Create: `tools/export-careers-page.php`
- Create: `tools/data/careers-page.json` (генерируется скриптом, коммитится)

**Interfaces:**
- Consumes: —
- Produces: `tools/data/careers-page.json` со структурой `{ title: string, slug: string, blocks: string, media: array<{slug, title, file, bundled}>, forms: array<{slug}> }`. Плейсхолдеры внутри `blocks` — строго в кавычках: `"{{att:слаг}}"`, `"{{cf7:слаг}}"`. Импортёр (Task 3) заменяет их вместе с кавычками на голое число, чтобы восстановить исходный числовой тип.

- [ ] **Step 1: Убедиться, что локаль поднята**

```bash
cd /Volumes/Webwork/sputnik-vet && wp-env start && wp-env run cli wp option get siteurl
```

Ожидается: URL сайта. Если Docker не запущен — сначала запустить Docker Desktop.

- [ ] **Step 2: Написать экспортёр**

Создать `tools/export-careers-page.php`:

```php
<?php
/**
 * Экспорт страницы «Работа в клинике» в переносимый JSON.
 *
 * Числовые ID вложений и формы CF7 заменяются плейсхолдерами по слагам:
 * между локалью и продом ID не совпадают, а слаги совпадают.
 *
 * Запуск:
 *   wp eval-file wp-content/themes/sputnik-new/tools/export-careers-page.php
 *
 * Результат коммитится в репозиторий и разбирается импортёром на проде.
 *
 * @package sputnik-plus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Скрипт рассчитан на запуск через wp eval-file.\n" );
}

const SPUTNIK_CAREERS_SLUG     = 'vacancies';
const SPUTNIK_CAREERS_DATA_DIR = __DIR__ . '/data';
const SPUTNIK_CAREERS_ICONS    = __DIR__ . '/icons';

/**
 * Собранные по ходу обхода вложения: слаг => описание для манифеста.
 *
 * @var array<string,array>
 */
$media = [];

/**
 * Собранные формы CF7: слаг => слаг.
 *
 * @var array<string,string>
 */
$forms = [];

/**
 * Заменяет ID вложения плейсхолдером и копит запись в манифест.
 *
 * @param int $id ID вложения.
 * @return string|null Плейсхолдер либо null, если вложение не найдено.
 */
function sputnik_export_attachment( $id, array &$media ) {
	$post = get_post( $id );

	if ( ! $post || 'attachment' !== $post->post_type ) {
		WP_CLI::warning( "Вложение {$id} не найдено — оставляю значение как есть." );

		return null;
	}

	$file = (string) get_post_meta( $post->ID, '_wp_attached_file', true );

	if ( ! isset( $media[ $post->post_name ] ) ) {
		// Иконки лежат в репозитории и могут быть залиты импортёром,
		// фотографии ожидаются уже в медиатеке прода.
		$bundled = file_exists( SPUTNIK_CAREERS_ICONS . '/' . basename( $file ) );

		$media[ $post->post_name ] = [
			'slug'    => $post->post_name,
			'title'   => $post->post_title,
			'file'    => $bundled ? basename( $file ) : $file,
			'bundled' => $bundled,
		];
	}

	return '{{att:' . $post->post_name . '}}';
}

/**
 * Заменяет ID формы CF7 плейсхолдером и копит слаг в манифест.
 *
 * @param int $id ID формы.
 * @return string|null Плейсхолдер либо null, если форма не найдена.
 */
function sputnik_export_form( $id, array &$forms ) {
	$post = get_post( $id );

	if ( ! $post || 'wpcf7_contact_form' !== $post->post_type ) {
		WP_CLI::warning( "Форма {$id} не найдена — оставляю значение как есть." );

		return null;
	}

	$forms[ $post->post_name ] = $post->post_name;

	return '{{cf7:' . $post->post_name . '}}';
}

/* -------------------------------------------------------------------------
 * Обход страницы
 * ---------------------------------------------------------------------- */

$page = get_page_by_path( SPUTNIK_CAREERS_SLUG, OBJECT, 'page' );

if ( ! $page ) {
	WP_CLI::error( 'Страница со слагом «' . SPUTNIK_CAREERS_SLUG . '» не найдена.' );
}

$blocks = parse_blocks( $page->post_content );

foreach ( $blocks as &$block ) {
	if ( empty( $block['blockName'] ) || empty( $block['attrs']['data'] ) ) {
		continue;
	}

	$data = $block['attrs']['data'];

	foreach ( $data as $name => $value ) {
		// Ключи-спутники (_image, _items_0_icon) обрабатываются вместе с полем.
		if ( str_starts_with( (string) $name, '_' ) ) {
			continue;
		}

		if ( ! is_scalar( $value ) || ! preg_match( '/^\d+$/', (string) $value ) || 0 === (int) $value ) {
			continue;
		}

		$field_key = $data[ '_' . $name ] ?? '';
		$field     = $field_key ? acf_get_field( $field_key ) : null;

		// Поле могли удалить из группы — значение остаётся нетронутым.
		if ( ! $field ) {
			continue;
		}

		if ( 'image' === $field['type'] ) {
			$placeholder = sputnik_export_attachment( (int) $value, $media );
		} elseif ( 'form' === $field['name'] ) {
			$placeholder = sputnik_export_form( (int) $value, $forms );
		} else {
			continue;
		}

		if ( null !== $placeholder ) {
			$data[ $name ] = $placeholder;
		}
	}

	$block['attrs']['data'] = $data;
}
unset( $block );

$content = '';
foreach ( $blocks as $block ) {
	$content .= serialize_block( $block );
}

/* -------------------------------------------------------------------------
 * Запись манифеста
 * ---------------------------------------------------------------------- */

$export = [
	'title'  => $page->post_title,
	'slug'   => $page->post_name,
	'blocks' => $content,
	'media'  => array_values( $media ),
	'forms'  => array_values(
		array_map(
			static fn( $slug ) => [ 'slug' => $slug ],
			$forms
		)
	),
];

if ( ! wp_mkdir_p( SPUTNIK_CAREERS_DATA_DIR ) ) {
	WP_CLI::error( 'Не удалось создать каталог ' . SPUTNIK_CAREERS_DATA_DIR );
}

$json = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

if ( false === $json ) {
	WP_CLI::error( 'Не удалось закодировать JSON: ' . json_last_error_msg() );
}

// Перевод строки в конце — чтобы git не ругался на файл без него.
if ( false === file_put_contents( SPUTNIK_CAREERS_DATA_DIR . '/careers-page.json', $json . "\n" ) ) {
	WP_CLI::error( 'Не удалось записать careers-page.json' );
}

WP_CLI::success(
	sprintf(
		'Экспортировано: вложений %d, форм %d, разметки %d байт.',
		count( $media ),
		count( $forms ),
		strlen( $content )
	)
);

foreach ( $media as $item ) {
	WP_CLI::log(
		sprintf(
			'  · %-34s %s',
			$item['slug'],
			$item['bundled'] ? 'файл в репозитории' : 'ожидается в медиатеке прода'
		)
	);
}
```

- [ ] **Step 3: Проверить синтаксис**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
php -l tools/export-careers-page.php
```

Ожидается: `No syntax errors detected`.

- [ ] **Step 4: Запустить экспорт**

```bash
cd /Volumes/Webwork/sputnik-vet
wp-env run cli wp eval-file wp-content/themes/sputnik-new/tools/export-careers-page.php 2>&1 | grep -v textdomain
```

Ожидается: `Success: Экспортировано: вложений 6, форм 1, разметки ~13000 байт.` и шесть строк манифеста, где четыре иконки помечены «файл в репозитории», а `close-up-veterinarian-taking-care-dog-2` и `dog_10` — «ожидается в медиатеке прода».

- [ ] **Step 5: Проверить, что все семь ссылок стали плейсхолдерами**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
grep -o '{{att:[^}]*}}' tools/data/careers-page.json | sort -u
grep -o '{{cf7:[^}]*}}' tools/data/careers-page.json | sort -u
grep -cE '"(image|icon|form)":[0-9]+' tools/data/careers-page.json
```

Ожидается: шесть уникальных `att`-плейсхолдеров, один `cf7`-плейсхолдер, и `0` в третьей команде — ни одного числового ID картинки или формы не осталось.

- [ ] **Step 6: Коммит**

```bash
git add tools/export-careers-page.php tools/data/careers-page.json
git commit -m "feat(careers): экспорт страницы вакансий в переносимый JSON"
```

---

### Task 3: Импортёр страницы на целевом окружении

Импортёр — зеркало экспортёра. Главное требование: он обязан **остановиться**, а не создать страницу с битыми картинками. Явная простановка `post_name` после сайдлоада обязательна: плагин `cyr2lat` активен, и слаг, выведенный из русского заголовка, получился бы транслитерированным (`zabota-o-komande`) вместо ожидаемого.

**Files:**
- Create: `tools/import-careers-page.php`

**Interfaces:**
- Consumes: `tools/data/careers-page.json` из Task 2; файлы иконок в `tools/icons/`; форму CF7 со слагом `vacancy-application`, созданную `tools/create-vacancy-form.php`.
- Produces: страницу `vacancies` на целевом окружении.

- [ ] **Step 1: Написать импортёр**

Создать `tools/import-careers-page.php`:

```php
<?php
/**
 * Импорт страницы «Работа в клинике» из tools/data/careers-page.json.
 *
 * Плейсхолдеры по слагам разворачиваются в локальные ID окружения.
 * Если хоть одно вложение или форма не найдены — страница не трогается:
 * лучше остановиться, чем опубликовать страницу с битыми картинками.
 *
 * Запуск:
 *   wp eval-file wp-content/themes/sputnik-new/tools/import-careers-page.php
 *
 * Повторный запуск безопасен: обновляет содержимое, но не меняет статус
 * страницы, так что опубликованную страницу не вернёт в черновики.
 *
 * @package sputnik-plus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Скрипт рассчитан на запуск через wp eval-file.\n" );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/*
 * SVG-загрузку плагин svg-support разрешает по правам пользователя,
 * а в WP-CLI текущего пользователя нет — подставляем администратора.
 * Фильтр mime-типов страхует на случай выключенной настройки плагина.
 */
$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
if ( $admins ) {
	wp_set_current_user( (int) $admins[0] );
}

add_filter( 'upload_mimes', static function ( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
} );

const SPUTNIK_CAREERS_DATA  = __DIR__ . '/data/careers-page.json';
const SPUTNIK_CAREERS_ICONS = __DIR__ . '/icons';

/**
 * ID вложения по слагу.
 *
 * get_page_by_path() не годится: у прикреплённых к записи медиа
 * путь включает слаг родителя, и точного совпадения не будет.
 */
function sputnik_import_attachment_id( $slug ) {
	$found = get_posts(
		[
			'name'                   => $slug,
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'numberposts'            => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Заливает файл из tools/icons/ и принудительно выставляет слаг.
 *
 * Без явного post_name слаг вывелся бы из русского заголовка, а cyr2lat
 * превратил бы его в транслитерацию — плейсхолдер перестал бы совпадать.
 *
 * @return int ID вложения либо 0.
 */
function sputnik_import_sideload( array $item ) {
	$source = SPUTNIK_CAREERS_ICONS . '/' . $item['file'];

	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( "Файл не найден: {$source}" );

		return 0;
	}

	// media_handle_sideload() удаляет исходник, поэтому отдаём ему копию.
	$tmp = wp_tempnam( $item['file'] );
	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		WP_CLI::warning( "Не удалось подготовить временный файл для {$item['slug']}" );

		return 0;
	}

	$id = media_handle_sideload(
		[
			'name'     => $item['file'],
			'tmp_name' => $tmp,
		],
		0,
		$item['title'],
		[ 'test_form' => false ]
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( $item['slug'] . ': ' . $id->get_error_message() );

		return 0;
	}

	wp_update_post(
		[
			'ID'        => $id,
			'post_name' => $item['slug'],
		]
	);

	WP_CLI::log( "· {$item['slug']}: загружен (ID {$id})" );

	return (int) $id;
}

/* -------------------------------------------------------------------------
 * Чтение манифеста
 * ---------------------------------------------------------------------- */

if ( ! file_exists( SPUTNIK_CAREERS_DATA ) ) {
	WP_CLI::error( 'Не найден ' . SPUTNIK_CAREERS_DATA );
}

$export = json_decode( (string) file_get_contents( SPUTNIK_CAREERS_DATA ), true );

if ( ! is_array( $export ) || empty( $export['blocks'] ) ) {
	WP_CLI::error( 'careers-page.json повреждён: ' . json_last_error_msg() );
}

/* -------------------------------------------------------------------------
 * Резолв ссылок
 * ---------------------------------------------------------------------- */

$map     = [];
$missing = [];

foreach ( (array) ( $export['media'] ?? [] ) as $item ) {
	$id = sputnik_import_attachment_id( $item['slug'] );

	if ( ! $id && ! empty( $item['bundled'] ) ) {
		$id = sputnik_import_sideload( $item );
	}

	if ( ! $id ) {
		$missing[] = sprintf( '%s (файл %s)', $item['slug'], $item['file'] );
		continue;
	}

	$map[ 'att:' . $item['slug'] ] = $id;
	WP_CLI::log( sprintf( '· %-34s → ID %d', $item['slug'], $id ) );
}

foreach ( (array) ( $export['forms'] ?? [] ) as $item ) {
	$found = get_posts(
		[
			'name'          => $item['slug'],
			'post_type'     => 'wpcf7_contact_form',
			'post_status'   => 'any',
			'numberposts'   => 1,
			'fields'        => 'ids',
			'no_found_rows' => true,
		]
	);

	if ( ! $found ) {
		WP_CLI::error(
			sprintf(
				'Форма «%s» не найдена. Сначала выполните: wp eval-file %s',
				$item['slug'],
				'wp-content/themes/sputnik-new/tools/create-vacancy-form.php'
			)
		);
	}

	$map[ 'cf7:' . $item['slug'] ] = (int) $found[0];
	WP_CLI::log( sprintf( '· %-34s → форма ID %d', $item['slug'], $found[0] ) );
}

if ( $missing ) {
	WP_CLI::error(
		"Не найдены вложения — страница не изменена:\n  · " . implode( "\n  · ", $missing )
			. "\nЗагрузите их в медиатеку с указанными слагами и повторите запуск."
	);
}

/* -------------------------------------------------------------------------
 * Подстановка
 * ---------------------------------------------------------------------- */

// Кавычки съедаются вместе с плейсхолдером: в исходной разметке значение
// было числом, а не строкой, и подстановка должна вернуть тот же тип.
$content = preg_replace_callback(
	'/"\{\{(att|cf7):([^"}]+)\}\}"/',
	static function ( $m ) use ( $map ) {
		$key = $m[1] . ':' . $m[2];

		if ( ! isset( $map[ $key ] ) ) {
			WP_CLI::error( "Плейсхолдер {$key} не разрешён." );
		}

		return (string) $map[ $key ];
	},
	$export['blocks']
);

if ( preg_match( '/\{\{(att|cf7):/', $content ) ) {
	WP_CLI::error( 'В разметке остались неразрешённые плейсхолдеры.' );
}

/* -------------------------------------------------------------------------
 * Запись страницы
 * ---------------------------------------------------------------------- */

$existing = get_page_by_path( $export['slug'], OBJECT, 'page' );

$postarr = [
	'post_title' => $export['title'],
	'post_name'  => $export['slug'],
	'post_type'  => 'page',
	// wp_insert_post() снимает слэши со всего массива, иначе экранирование
	// переносов строк в JSON-атрибутах блоков (\n) потеряло бы слэш.
	'post_content' => wp_slash( $content ),
];

if ( $existing ) {
	// Статус не трогаем: опубликованную страницу нельзя ронять в черновики.
	$postarr['ID'] = $existing->ID;
	$page_id       = wp_update_post( $postarr, true );
	$action        = 'обновлена';
} else {
	$postarr['post_status'] = 'draft';
	$page_id                = wp_insert_post( $postarr, true );
	$action                 = 'создана как черновик';
}

if ( is_wp_error( $page_id ) ) {
	WP_CLI::error( 'Не удалось сохранить страницу: ' . $page_id->get_error_message() );
}

WP_CLI::success(
	sprintf(
		'Страница «%s» %s (ID %d): %s',
		$export['title'],
		$action,
		$page_id,
		get_permalink( $page_id )
	)
);
```

- [ ] **Step 2: Проверить синтаксис**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
php -l tools/import-careers-page.php
```

Ожидается: `No syntax errors detected`.

- [ ] **Step 3: Проверить, что импортёр останавливается на недостающем вложении**

Это проверка главного требования — «падать громко». Временно ломаем слаг в манифесте:

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
cp tools/data/careers-page.json /tmp/careers-page.json.bak
sed -i '' 's/close-up-veterinarian-taking-care-dog-2/nonexistent-photo-slug/g' tools/data/careers-page.json
cd /Volumes/Webwork/sputnik-vet
wp-env run cli wp eval-file wp-content/themes/sputnik-new/tools/import-careers-page.php 2>&1 | grep -v textdomain | tail -5
```

Ожидается: `Error: Не найдены вложения — страница не изменена:` со строкой `nonexistent-photo-slug`.

- [ ] **Step 4: Вернуть манифест**

```bash
cp /tmp/careers-page.json.bak /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new/tools/data/careers-page.json
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
git diff --stat tools/data/careers-page.json
```

Ожидается: пустой вывод — файл вернулся к закоммиченному состоянию.

- [ ] **Step 5: Коммит**

```bash
git add tools/import-careers-page.php
git commit -m "feat(careers): импорт страницы вакансий с резолвом ссылок по слагам"
```

---

### Task 4: Round-trip на локали и вывод бутстрап-скриптов из обращения

Round-trip — единственная честная проверка того, что экспорт и импорт зеркальны: экспорт → импорт → повторный экспорт обязан дать байт-в-байт тот же JSON. Если совпало, значит ID разворачиваются обратно в те же значения и числовой тип не поплыл. После этого одноразовые бутстрап-скрипты можно удалять: их работу берёт на себя импортёр, а оба содержат дефекты (устаревшая карта полей в сидере; поиск иконок по слагу, который скрипт сам не создаёт).

**Files:**
- Delete: `tools/seed-vacancies-page.php`
- Delete: `tools/import-careers-icons.php`

**Interfaces:**
- Consumes: `tools/export-careers-page.php` и `tools/import-careers-page.php` из Task 2 и 3.
- Produces: подтверждение, что пара скриптов обратима.

- [ ] **Step 1: Убедиться, что манифест соответствует закоммиченному**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
git diff --stat tools/data/careers-page.json
```

Ожидается: пустой вывод. Прогон начинается с чистого манифеста, иначе сравнение на шаге 3 ничего не докажет.

- [ ] **Step 2: Прогнать импорт на локали**

```bash
cd /Volumes/Webwork/sputnik-vet
wp-env run cli wp eval-file wp-content/themes/sputnik-new/tools/import-careers-page.php 2>&1 | grep -v textdomain | tail -12
```

Ожидается: шесть строк `слаг → ID` (иконки резолвятся по уже существующим слагам, не заливаются заново), строка `vacancy-application → форма ID 3153` и `Success: Страница «Работа в клинике» обновлена (ID 3141)`.

- [ ] **Step 3: Повторно экспортировать и сравнить**

```bash
cd /Volumes/Webwork/sputnik-vet
wp-env run cli wp eval-file wp-content/themes/sputnik-new/tools/export-careers-page.php 2>&1 | grep -v textdomain | tail -2
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
git diff --stat tools/data/careers-page.json
```

Ожидается: **пустой вывод** `git diff`. Непустой означает, что импорт исказил контент — разбираться, не двигаться дальше.

- [ ] **Step 4: Убедиться, что дубликатов иконок не появилось**

```bash
cd /Volumes/Webwork/sputnik-vet
wp-env run cli wp post list --post_type=attachment --s=careers- --field=post_name 2>/dev/null | sort
```

Ожидается: четыре слага (`zabota-o-komande`, `razvitie-i-obuchenie`, `sovremennoe-oborudovanie`, `interesnye-klinicheskie-sluchai`) и ни одного `careers-care`-подобного дубликата.

- [ ] **Step 5: Удалить бутстрап-скрипты**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
git rm --cached tools/seed-vacancies-page.php tools/import-careers-icons.php 2>/dev/null
rm -f tools/seed-vacancies-page.php tools/import-careers-icons.php
ls tools/
```

Ожидается: в `tools/` остались `create-vacancy-form.php`, `export-careers-page.php`, `import-careers-page.php`, `data/`, `icons/`. Скрипты не были закоммичены ранее, поэтому `git rm --cached` может вернуть ошибку — это нормально, файлы всё равно удаляются командой `rm`.

- [ ] **Step 6: Коммит**

```bash
git add -A tools/
git commit -m "chore(careers): убрать одноразовые бутстрап-скрипты в пользу export/import"
```

---

### Task 5: Коммит кода темы и слияние в `main`

Здесь в репозиторий впервые попадают сами блоки, ACF-группы и правки `functions.php`. Коммит разбит на два: код блоков отдельно от вспомогательных правок — чтобы откат `git revert` можно было сделать прицельно.

**Files:**
- Add: `src/blocks/block-careers-{hero,why,steps,team,cta}/`, `src/blocks/block-vacancies/`
- Add: `build/blocks/block-careers-*/`, `build/blocks/block-vacancies/`
- Add: `acf-json/group_68b1a2c3d4e01.json` … `acf-json/group_68b6f7a8b9c06.json`
- Modify: `functions.php`, `build/blocks-manifest.php`, прочие `build/blocks/*/index.*`

**Interfaces:**
- Consumes: содержимое веток из Task 1–4.
- Produces: коммит в `main`, который прод получит через `git pull`.

- [ ] **Step 1: Пересобрать фронтенд, чтобы `build/` соответствовал `src/`**

```bash
cd /Volumes/Webwork/sputnik-vet/wp-content/themes/sputnik-new
npm run build
```

Ожидается: сборка без ошибок.

- [ ] **Step 2: Убедиться, что глобальные бандлы не задеты**

```bash
git status --porcelain build/styles/ build/js/
```

Ожидается: пустой вывод. Изменения в `build/styles/main.css` или `build/js/main.js` означали бы, что выкатка заденет весь сайт, а не только новую страницу — это повод остановиться и разобраться.

- [ ] **Step 3: Проверить синтаксис PHP во всех новых файлах**

```bash
for f in functions.php src/blocks/block-careers-*/render.php src/blocks/block-vacancies/render.php tools/*.php; do php -l "$f" || echo "ОШИБКА: $f"; done
```

Ожидается: `No syntax errors detected` для каждого файла, ни одной строки `ОШИБКА:`.

- [ ] **Step 4: Убедиться, что все шесть блоков попали в манифест**

```bash
grep -c "sputnik/careers-\|sputnik/vacancies" build/blocks-manifest.php
```

Ожидается: `6`.

- [ ] **Step 5: Закоммитить блоки и ACF-группы**

```bash
git add src/blocks/block-careers-cta src/blocks/block-careers-hero src/blocks/block-careers-steps \
        src/blocks/block-careers-team src/blocks/block-careers-why src/blocks/block-vacancies \
        build/blocks acf-json/group_68b1a2c3d4e01.json acf-json/group_68b2b3c4d5e02.json \
        acf-json/group_68b3c4d5e6f03.json acf-json/group_68b4d5e6f7a04.json \
        acf-json/group_68b5e6f7a8b05.json acf-json/group_68b6f7a8b9c06.json \
        build/blocks-manifest.php
git commit -m "feat(careers): шесть блоков страницы вакансий и ACF-группы"
```

- [ ] **Step 6: Закоммитить правки `functions.php`**

```bash
git add functions.php
git commit -m "feat(careers): выпадайки сотрудников и форм CF7, отключение wpautop для формы отклика"
```

- [ ] **Step 7: Убедиться, что посторонние файлы не уехали**

```bash
git status --porcelain
git log --oneline origin/main..HEAD
```

Ожидается: в первой команде остались только untracked `docs/superpowers/*search-results-page*` — они относятся к ветке `feature/search-results-page` и в этой миграции не участвуют. Во второй — коммиты задач 1–5.

- [ ] **Step 8: Отправить в `main`**

```bash
git push origin main
```

---

### Task 6: Пред-проверки прода

Выполняется на сервере **до** выкатки. Цель — убедиться, что окружение потянет блоки и форму, и узнать о расхождениях заранее, а не в момент, когда страница уже наполовину создана.

**Files:** —

**Interfaces:**
- Consumes: —
- Produces: подтверждение, что можно тянуть код; список расхождений, если они есть.

- [ ] **Step 1: Версии ядра и плагинов**

```bash
wp core version
wp eval 'echo PHP_VERSION."\n";'
wp plugin get advanced-custom-fields-pro --field=version
wp plugin list --status=active --field=name | grep -E 'contact-form-7|svg-support|cyr2lat'
```

Ожидается: ядро ≥ 6.8, PHP ≥ 7.4, ACF PRO ≥ 6.0, и три плагина в списке активных. Если ядро < 6.8 — регистрация блоков уйдёт в ветку отката `functions.php:412`; она рабочая, но об этом надо знать заранее.

- [ ] **Step 2: Лимиты загрузки под резюме**

```bash
wp eval 'echo "upload_max_filesize=".ini_get("upload_max_filesize")." post_max_size=".ini_get("post_max_size")."\n";'
```

Ожидается: оба значения ≥ 10 МБ. Форма принимает резюме до 10 МБ (`limit:10mb`); при меньшем лимите крупные файлы будут молча отваливаться.

- [ ] **Step 3: Страница политики конфиденциальности**

```bash
wp post list --post_type=page --name=politika-konfidencialnosti --fields=ID,post_status
```

Ожидается: одна опубликованная страница. На неё ссылается чекбокс согласия в форме — `create-vacancy-form.php` подставляет её URL при создании формы.

- [ ] **Step 4: Наличие фотографий в медиатеке**

```bash
wp post list --post_type=attachment --post_status=inherit --name=close-up-veterinarian-taking-care-dog-2 --fields=ID,post_name
wp post list --post_type=attachment --post_status=inherit --name=dog_10 --fields=ID,post_name
```

Ожидается: по одной строке на каждый слаг. Пусто — значит фото придётся загрузить вручную, проследив за слагом; импортёр в этом случае остановится и назовёт недостающее.

- [ ] **Step 5: Сверить ФИО сотрудников**

```bash
wp eval '$t=get_field("team_members","option"); foreach((array)$t as $m){ if(in_array(trim($m["name"]??""),["Илья Владимирович Середа","Клавдия Николаевна Налётова","Евгения Александровна Васильева"],true)) echo "есть: ".$m["name"]."\n"; }'
```

Ожидается: три строки. Блок «Наша команда» выбирает сотрудников по ФИО; расхождение хотя бы в одном символе — и карточка не отрисуется (блок не упадёт, просто покажет на одного человека меньше).

- [ ] **Step 6: Снять бэкап БД перед изменениями**

```bash
wp db export ~/backup-before-careers-$(date +%Y%m%d-%H%M).sql
```

Ожидается: файл создан. Это страховка на случай, если что-то пойдёт не так на шагах Task 7.

---

### Task 7: Выкатка на прод

**Files:** —

**Interfaces:**
- Consumes: коммиты из Task 5; результаты проверок Task 6.
- Produces: страницу-черновик `vacancies` и форму `vacancy-application` на проде.

- [ ] **Step 1: Забрать код**

```bash
cd <путь к теме на сервере>
git pull origin main
git log --oneline -3
```

Ожидается: свежие коммиты миграции в истории.

- [ ] **Step 2: Убедиться, что блоки зарегистрировались**

```bash
wp eval 'foreach(["careers-hero","careers-why","vacancies","careers-team","careers-steps","careers-cta"] as $b){ echo $b.": ".(WP_Block_Type_Registry::get_instance()->is_registered("sputnik/".$b)?"да":"НЕТ")."\n"; }'
```

Ожидается: шесть строк `да`. Хотя бы одно `НЕТ` — остановиться: без регистрации импорт создаст страницу, которую нечем отрендерить.

- [ ] **Step 3: Создать форму отклика**

```bash
wp eval-file wp-content/themes/sputnik-new/tools/create-vacancy-form.php
```

Ожидается: `Success: Форма «Отклик на вакансию» создана: ID <номер>, слаг vacancy-application`.

- [ ] **Step 4: Импортировать страницу**

```bash
wp eval-file wp-content/themes/sputnik-new/tools/import-careers-page.php
```

Ожидается: шесть строк `слаг → ID` (четыре иконки со словом «загружен», две фотографии — с найденными ID), строка про форму и `Success: Страница «Работа в клинике» создана как черновик (ID <номер>)`.

Если вместо этого пришла ошибка про недостающие вложения — загрузить названные файлы в медиатеку, проследив за слагом, и повторить шаг. Страница при этом не пострадала: импортёр не дошёл до записи.

- [ ] **Step 5: Сбросить кэш**

```bash
wp cache flush
```

Плюс сбросить страничный кэш и OPcache средствами хостинга, если они используются.

---

### Task 8: Приёмка, публикация и меню

**Files:** —

**Interfaces:**
- Consumes: страницу-черновик из Task 7.
- Produces: опубликованную страницу в меню.

- [ ] **Step 1: Открыть предпросмотр черновика**

```bash
wp post list --post_type=page --name=vacancies --fields=ID,post_status
wp eval 'echo get_preview_post_link( get_page_by_path("vacancies") )."\n";'
```

Открыть полученную ссылку в браузере под администратором.

- [ ] **Step 2: Пройти чек-лист приёмки**

- [ ] Все шесть блоков на месте, вёрстка не разъехалась
- [ ] В hero видно фото, а не пустой прямоугольник
- [ ] В блоке «Почему „Спутник“» четыре карточки с иконками
- [ ] Фильтр по направлениям переключает карточки; «Все» показывает все четыре вакансии
- [ ] Клик по карточке открывает модалку с описанием
- [ ] «Откликнуться» переключает модалку на форму, в заголовке — название вакансии
- [ ] Кнопка «Отправить резюме» в CTA-баннере открывает ту же форму с заголовком «Отправить резюме» и без привязки к вакансии
- [ ] Форма не обёрнута лишними `<p>` (проверка фильтра `wpcf7_autop_or_not`)
- [ ] Блок «Наша команда» показывает трёх сотрудников с фото
- [ ] В CTA-блоке видно фото

- [ ] **Step 3: Проверить отправку формы**

Отправить тестовый отклик с приложенным PDF около 1 МБ.

Ожидается: появляется экран «Спасибо за отклик!», на админский адрес приходит письмо с темой вида `Врач-терапевт — отклик с сайта`, приложенным резюме и `Reply-To` на адрес кандидата.

- [ ] **Step 4: Опубликовать страницу**

```bash
wp post update $(wp post list --post_type=page --name=vacancies --field=ID) --post_status=publish
```

- [ ] **Step 5: Добавить пункт в меню**

Подставить ID нужного меню из списка:

```bash
wp menu list
wp menu item add-post <menu-id> $(wp post list --post_type=page --name=vacancies --field=ID) --title="Работа в клинике"
```

- [ ] **Step 6: Проверить опубликованную страницу и сбросить кэш**

```bash
wp cache flush
curl -so /dev/null -w '%{http_code}\n' "$(wp eval 'echo get_permalink( get_page_by_path("vacancies") );')"
```

Ожидается: `200`. Затем открыть страницу в браузере инкогнито и убедиться, что пункт меню виден и ведёт куда надо.

---

## Известные хвосты

Приняты осознанно при согласовании спеки, в объём этого плана не входят:

- Кнопка «Познакомиться с командой» в блоке «Наша команда» ведёт на `href="#"` — на опубликованной странице это неработающая ссылка. Правится в редакторе в любой момент. (У кнопки CTA `href="#"` намеренный: клик перехватывает `view.js` блока вакансий.)
- Yoast-мета страницы не заполнена — title и description будут автоматическими.
- Получатель писем остаётся админским адресом сайта, отдельный HR-ящик не заводится.

## Откат

- **Код:** `git revert` коммитов задач 1–5 в `main`, затем `git pull` на проде. Ключевой — коммит `feat(careers): шесть блоков страницы вакансий и ACF-группы`: без него страница перестанет рендериться, даже если остальное останется.
- **Контент:** вернуть страницу в черновик (`wp post update <ID> --post_status=draft`) либо в корзину. Форма CF7 и четыре иконки безвредны сами по себе; удаляются вручную при необходимости.
- **Полный откат:** восстановить дамп из Task 6, шаг 6.
