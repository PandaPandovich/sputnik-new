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
 *
 * @param string $slug Слаг вложения.
 * @return int ID либо 0.
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
 * @param array $item Запись манифеста: slug, title, file.
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

/*
 * Кавычки съедаются вместе с плейсхолдером: в исходной разметке значение
 * было числом, а не строкой, и подстановка должна вернуть тот же тип.
 */
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
	'post_title'   => $export['title'],
	'post_name'    => $export['slug'],
	'post_type'    => 'page',
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
