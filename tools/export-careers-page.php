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
 * Заменяет ID вложения плейсхолдером и копит запись в манифест.
 *
 * @param int   $id    ID вложения.
 * @param array $media Манифест: слаг => описание. Передаётся по ссылке.
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
 * @param int   $id    ID формы.
 * @param array $forms Манифест форм: слаг => слаг. Передаётся по ссылке.
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

$media  = [];
$forms  = [];
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

		/*
		 * Тип определяем через ACF по ключу-спутнику, а не по имени поля:
		 * так находятся и картинки внутри репитеров (items_0_icon),
		 * и ничего не сломается при переименовании поля.
		 */
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
