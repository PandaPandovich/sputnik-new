<?php
/**
 * Основные функции темы Sputnik Plus.
 *
 * @package sputnik-plus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'acf/settings/enable_acf_ai', '__return_true' );

/**
 * Abilities API: доступ к страницам и записям через MCP.
 * Требуется WordPress 6.9+.
 */
if ( function_exists( 'wp_register_ability' ) ) {
	// Категория для контент-abilities
	add_action( 'wp_abilities_api_categories_init', function() {
		wp_register_ability_category( 'content-management', [
			'label'       => 'Управление контентом',
			'description' => 'Чтение и управление страницами и записями',
		] );
	} );

	add_action( 'wp_abilities_api_init', function() {

		// Список страниц
		wp_register_ability( 'sputnik/pages', [
			'label'       => 'Список страниц',
			'description' => 'Получить список опубликованных страниц сайта с заголовком, URL и ID.',
			'category'    => 'content-management',
			'input_schema' => [
				'type'       => 'object',
				'properties' => [
					'search' => [
						'type'        => 'string',
						'description' => 'Поиск по заголовку страницы',
					],
					'per_page' => [
						'type'        => 'integer',
						'description' => 'Количество результатов (по умолчанию 20)',
						'default'     => 20,
					],
				],
			],
			'output_schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'id'    => [ 'type' => 'integer' ],
						'title' => [ 'type' => 'string' ],
						'url'   => [ 'type' => 'string' ],
						'slug'  => [ 'type' => 'string' ],
					],
				],
			],
			'execute_callback'   => function( $input ) {
				$args = [
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => $input['per_page'] ?? 20,
					'orderby'        => 'title',
					'order'          => 'ASC',
				];
				if ( ! empty( $input['search'] ) ) {
					$args['s'] = sanitize_text_field( $input['search'] );
				}
				$query = new WP_Query( $args );
				$pages = [];
				while ( $query->have_posts() ) {
					$query->the_post();
					$pages[] = [
						'id'    => get_the_ID(),
						'title' => get_the_title(),
						'url'   => get_permalink(),
						'slug'  => get_post_field( 'post_name' ),
					];
				}
				wp_reset_postdata();
				return $pages;
			},
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
			'meta' => [ 'show_in_rest' => true ],
		] );

		// Просмотр страницы по ID
		wp_register_ability( 'sputnik/view-page', [
			'label'       => 'Просмотр страницы',
			'description' => 'Получить содержимое страницы по ID: заголовок, контент, отрывок, URL.',
			'category'    => 'content-management',
			'input_schema' => [
				'type'       => 'object',
				'properties' => [
					'id' => [
						'type'        => 'integer',
						'description' => 'ID страницы',
					],
				],
				'required' => [ 'id' ],
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'id'      => [ 'type' => 'integer' ],
					'title'   => [ 'type' => 'string' ],
					'content' => [ 'type' => 'string' ],
					'excerpt' => [ 'type' => 'string' ],
					'url'     => [ 'type' => 'string' ],
					'slug'    => [ 'type' => 'string' ],
				],
			],
			'execute_callback' => function( $input ) {
				$post = get_post( $input['id'] );
				if ( ! $post || $post->post_type !== 'page' ) {
					return new WP_Error( 'not_found', 'Страница не найдена' );
				}
				return [
					'id'      => $post->ID,
					'title'   => $post->post_title,
					'content' => apply_filters( 'the_content', $post->post_content ),
					'excerpt' => $post->post_excerpt,
					'url'     => get_permalink( $post ),
					'slug'    => $post->post_name,
				];
			},
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
			'meta' => [ 'show_in_rest' => true ],
		] );

		// Список записей
		wp_register_ability( 'sputnik/posts', [
			'label'       => 'Список записей',
			'description' => 'Получить список опубликованных записей блога с заголовком, отрывком, изображением и URL.',
			'category'    => 'content-management',
			'input_schema' => [
				'type'       => 'object',
				'properties' => [
					'search' => [
						'type'        => 'string',
						'description' => 'Поиск по заголовку',
					],
					'category' => [
						'type'        => 'string',
						'description' => 'Slug категории для фильтрации',
					],
					'per_page' => [
						'type'        => 'integer',
						'description' => 'Количество результатов (по умолчанию 20)',
						'default'     => 20,
					],
				],
			],
			'output_schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'id'        => [ 'type' => 'integer' ],
						'title'     => [ 'type' => 'string' ],
						'excerpt'   => [ 'type' => 'string' ],
						'url'       => [ 'type' => 'string' ],
						'date'      => [ 'type' => 'string' ],
						'thumbnail' => [ 'type' => 'string' ],
					],
				],
			],
			'execute_callback' => function( $input ) {
				$args = [
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => $input['per_page'] ?? 20,
					'orderby'        => 'date',
					'order'          => 'DESC',
				];
				if ( ! empty( $input['search'] ) ) {
					$args['s'] = sanitize_text_field( $input['search'] );
				}
				if ( ! empty( $input['category'] ) ) {
					$args['category_name'] = sanitize_text_field( $input['category'] );
				}
				$query = new WP_Query( $args );
				$posts = [];
				while ( $query->have_posts() ) {
					$query->the_post();
					$posts[] = [
						'id'        => get_the_ID(),
						'title'     => get_the_title(),
						'excerpt'   => get_the_excerpt(),
						'url'       => get_permalink(),
						'date'      => get_the_date( 'Y-m-d' ),
						'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ?: '',
					];
				}
				wp_reset_postdata();
				return $posts;
			},
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
			'meta' => [ 'show_in_rest' => true ],
		] );

		// Просмотр записи по ID
		wp_register_ability( 'sputnik/view-post', [
			'label'       => 'Просмотр записи',
			'description' => 'Получить содержимое записи по ID: заголовок, контент, отрывок, дату, изображение, URL.',
			'category'    => 'content-management',
			'input_schema' => [
				'type'       => 'object',
				'properties' => [
					'id' => [
						'type'        => 'integer',
						'description' => 'ID записи',
					],
				],
				'required' => [ 'id' ],
			],
			'output_schema' => [
				'type'       => 'object',
				'properties' => [
					'id'        => [ 'type' => 'integer' ],
					'title'     => [ 'type' => 'string' ],
					'content'   => [ 'type' => 'string' ],
					'excerpt'   => [ 'type' => 'string' ],
					'url'       => [ 'type' => 'string' ],
					'date'      => [ 'type' => 'string' ],
					'thumbnail' => [ 'type' => 'string' ],
				],
			],
			'execute_callback' => function( $input ) {
				$post = get_post( $input['id'] );
				if ( ! $post || $post->post_type !== 'post' ) {
					return new WP_Error( 'not_found', 'Запись не найдена' );
				}
				return [
					'id'        => $post->ID,
					'title'     => $post->post_title,
					'content'   => apply_filters( 'the_content', $post->post_content ),
					'excerpt'   => $post->post_excerpt ?: wp_trim_words( $post->post_content, 30 ),
					'url'       => get_permalink( $post ),
					'date'      => get_the_date( 'Y-m-d', $post ),
					'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
				];
			},
			'permission_callback' => function() {
				return current_user_can( 'read' );
			},
			'meta' => [ 'show_in_rest' => true ],
		] );

	} );
}

if ( ! defined( 'SPUTNIK_PLUS_VERSION' ) ) {
	define( 'SPUTNIK_PLUS_VERSION', wp_get_theme()->get( 'Version' ) );
}

add_theme_support('menus');
add_theme_support('post-thumbnails');

/**
 * Поддержка миниатюр для кастомных типов записей.
 */
add_action( 'init', function() {
	$post_types = [ 'branch', 'services' ];
	foreach ( $post_types as $pt ) {
		if ( post_type_exists( $pt ) ) {
			add_post_type_support( $pt, 'thumbnail' );
		}
	}
}, 99 );

/**
 * Подключение меню в футере.
 */

add_action( 'after_setup_theme', function(){
	register_nav_menus( [
		'header_menu' => 'Header menu',
		'footer_menu_about' => 'Футер: О клинике',
		'footer_menu_services' => 'Футер: Услуги и цены'
	] );
} );

/**
 * Подключение стилей редактора Gutenberg.
 */
function sputnik_plus_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'build/styles/editor.css' );
}
add_action( 'after_setup_theme', 'sputnik_plus_editor_styles' );

/**
 * Подключение скрипта редактора (инициализация Swiper в ACF-превью).
 */
function sputnik_plus_enqueue_editor_script() {
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$script    = '/build/editor.js';

	if ( file_exists( $theme_dir . $script ) ) {
		wp_enqueue_script(
			'sputnik-plus-editor-script',
			$theme_uri . $script,
			array(),
			SPUTNIK_PLUS_VERSION,
			true
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'sputnik_plus_enqueue_editor_script' );

/**
 * Подключение стилей и скриптов темы.
 */
function sputnik_plus_enqueue_assets() {
	$theme_uri = get_template_directory_uri();

	wp_enqueue_style(
		'sputnik-plus-style',
		$theme_uri . '/build/styles/main.css',
		array(),
		SPUTNIK_PLUS_VERSION
	);

	wp_enqueue_script(
		'sputnik-plus-script',
		$theme_uri . '/build/main.js',
		array(),
		SPUTNIK_PLUS_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'sputnik_plus_enqueue_assets' );

/**
 * Передаём URL для AJAX-запросов в JS.
 */
function sputnik_plus_localize_scripts() {
	wp_localize_script( 'sputnik-plus-script', 'sputnikAjax', [
		'url' => admin_url( 'admin-ajax.php' ),
	] );
}
add_action( 'wp_enqueue_scripts', 'sputnik_plus_localize_scripts' );

/**
 * AJAX-поиск для хедера.
 */
function sputnik_plus_ajax_search() {
	$query = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );

	if ( mb_strlen( $query ) < 2 ) {
		wp_send_json( [] );
	}

	$results = new WP_Query( [
		'post_type'      => [ 'post', 'page', 'branch', 'services' ],
		'post_status'    => 'publish',
		's'              => $query,
		'posts_per_page' => 8,
	] );

	$items = [];
	while ( $results->have_posts() ) {
		$results->the_post();
		$pt        = get_post_type();
		$pt_obj    = get_post_type_object( $pt );
		$type_name = $pt_obj->labels->name;

		// Мета-информация: тип · дата для статей
		$meta = '';
		if ( $pt === 'post' ) {
			$meta = $pt_obj->labels->singular_name . ' · ' . get_the_date( 'j F Y' );
		} elseif ( $pt === 'page' ) {
			$meta = $pt_obj->labels->singular_name;
		}

		$items[] = [
			'title'     => get_the_title(),
			'url'       => get_permalink(),
			'type'      => $type_name,
			'post_type' => $pt,
			'meta'      => $meta,
		];
	}
	wp_reset_postdata();

	wp_send_json( $items );
}
add_action( 'wp_ajax_sputnik_search', 'sputnik_plus_ajax_search' );
add_action( 'wp_ajax_nopriv_sputnik_search', 'sputnik_plus_ajax_search' );

/**
 * Регистрация блоков через манифест (генерируется wp-scripts build-blocks-manifest).
 * Требуется WordPress 6.8+ для wp_register_block_types_from_metadata_collection.
 * Для WP 6.7 используйте wp_register_block_metadata_collection + ручная регистрация.
 */
function sputnik_plus_register_blocks() {
	$blocks_dir      = get_template_directory() . '/build/blocks';
	$blocks_manifest = $blocks_dir . '-manifest.php';

	// WordPress 6.8+: автоматическая регистрация всех блоков из манифеста
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && file_exists( $blocks_manifest ) ) {
		wp_register_block_types_from_metadata_collection( $blocks_dir, $blocks_manifest );
		return;
	}

	// WordPress 6.7: регистрация коллекции + ручная регистрация блоков
	if ( function_exists( 'wp_register_block_metadata_collection' ) && file_exists( $blocks_manifest ) ) {
		wp_register_block_metadata_collection( $blocks_dir, $blocks_manifest );

		// Сканируем build/blocks для регистрации каждого блока
		if ( is_dir( $blocks_dir ) ) {
			$block_folders = glob( $blocks_dir . '/*/block.json' );
			foreach ( $block_folders as $block_json ) {
				register_block_type( dirname( $block_json ) );
			}
		}
		return;
	}

	// Fallback: ручная регистрация без манифеста (если WP < 6.7 или манифест не создан)
	if ( is_dir( $blocks_dir ) ) {
		$block_folders = glob( $blocks_dir . '/*/block.json' );
		foreach ( $block_folders as $block_json ) {
			register_block_type( dirname( $block_json ) );
		}
	}
}
add_action( 'init', 'sputnik_plus_register_blocks', 9 );

/**
 * Регистрация страницы настроек ACF.
 */
function sputnik_plus_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( [
		'page_title' => 'Настройки темы',
		'menu_title' => 'Настройки темы',
		'menu_slug'  => 'theme-settings',
		'capability' => 'edit_posts',
		'redirect'   => false,
		'icon_url'   => 'dashicons-admin-generic',
		'position'   => 59,
	] );

	acf_add_options_sub_page( [
		'page_title'  => 'Цены на услуги',
		'menu_title'  => 'Цены на услуги',
		'menu_slug'   => 'theme-settings-prices',
		'parent_slug' => 'theme-settings',
		'capability'  => 'edit_posts',
	] );

	acf_add_options_sub_page( [
		'page_title'  => 'Команда',
		'menu_title'  => 'Команда',
		'menu_slug'   => 'theme-settings-team',
		'parent_slug' => 'theme-settings',
		'capability'  => 'edit_posts',
	] );
}
add_action( 'acf/init', 'sputnik_plus_acf_options_page' );

/**
 * Шаблон блоков для типа записи «Отделение» (branch).
 * При создании нового отделения блоки автоматически вставляются в нужном порядке.
 */
function sputnik_plus_branch_block_template() {
	$post_type_object = get_post_type_object( 'branch' );
	if ( ! $post_type_object ) {
		return;
	}

	$post_type_object->template = [
		[ 'sputnik/hero-inner', [] ],
		[ 'sputnik/department-services', [] ],
		[ 'sputnik/department-doctor', [] ],
		[ 'sputnik/faq', [] ],
		[ 'sputnik/clinics', [] ],
		[ 'sputnik/banner', [] ],
	];
}
add_action( 'init', 'sputnik_plus_branch_block_template', 100 );

/**
 * Динамическое заполнение поля «Категория прайса» в блоке услуг отделения.
 * Берёт названия категорий из ACF options (Настройки темы → Цены на услуги).
 */
add_filter( 'acf/load_field/key=field_685a1b2c3d007', function( $field ) {
	$categories = get_field( 'price_categories', 'option' );
	$field['choices'] = [];

	if ( $categories ) {
		foreach ( $categories as $index => $cat ) {
			$field['choices'][ $index ] = $cat['name'];
		}
	}

	return $field;
} );

/**
 * Динамическое заполнение поля «Врачи» в блоке врача отделения.
 * Берёт список сотрудников из ACF options (Настройки темы → Команда).
 */
add_filter( 'acf/load_field/key=field_685b2c3d4e009', function( $field ) {
	$team = get_field( 'team_members', 'option' );
	$field['choices'] = [];

	if ( $team ) {
		foreach ( $team as $index => $member ) {
			$label = $member['name'];
			if ( ! empty( $member['specialization'] ) ) {
				$label .= ' — ' . $member['specialization'];
			}
			$field['choices'][ $index ] = $label;
		}
	}

	return $field;
} );
