<?php
/**
 * Основные функции темы Sputnik Plus.
 *
 * @package sputnik-plus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_theme_support( 'title-tag' );

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
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();

	// Версия по времени изменения файла — сбрасывает кеш браузера при каждой пересборке.
	$style_path = $theme_dir . '/build/styles/main.css';
	$style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : SPUTNIK_PLUS_VERSION;

	wp_enqueue_style(
		'sputnik-plus-style',
		$theme_uri . '/build/styles/main.css',
		array(),
		$style_ver
	);

	$script_path = $theme_dir . '/build/main.js';
	$script_ver  = file_exists( $script_path ) ? filemtime( $script_path ) : SPUTNIK_PLUS_VERSION;

	wp_enqueue_script(
		'sputnik-plus-script',
		$theme_uri . '/build/main.js',
		array(),
		$script_ver,
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

	// Общий источник с той же страницей результатов: статьи, отделения, врачи, услуги, страницы.
	$results     = array_slice( sputnik_collect_search_results( $query ), 0, 10 );
	$results_url = home_url( '/?s=' . rawurlencode( $query ) );

	$items = [];
	foreach ( $results as $r ) {
		$items[] = [
			'title'    => $r['title'],
			// Врачи и услуги не имеют своей страницы — ведём на страницу результатов.
			'url'      => ! empty( $r['url'] ) ? $r['url'] : $results_url,
			'type'     => $r['type_label'],
			'type_key' => $r['type'],
			'meta'     => $r['breadcrumb'] ?? '',
			'icon'     => sputnik_search_icon( $r['type'] ),
		];
	}

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

/**
 * Сотрудники из ACF options (Настройки темы → Команда) в виде «ФИО — специализация».
 *
 * Ключ — ФИО, а не порядковый номер, как в блоке врача отделения выше:
 * перетасовка списка в настройках темы не должна переназначать уже выбранных
 * сотрудников в блоках.
 *
 * @return array<string,string>
 */
function sputnik_plus_team_member_choices() {
	static $choices = null;

	if ( null !== $choices ) {
		return $choices;
	}

	$choices = [];
	$team    = get_field( 'team_members', 'option' );

	if ( $team ) {
		foreach ( $team as $member ) {
			$name = isset( $member['name'] ) ? trim( $member['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}

			$choices[ $name ] = ! empty( $member['specialization'] )
				? $name . ' — ' . $member['specialization']
				: $name;
		}
	}

	return $choices;
}

/**
 * Данные сотрудника из «Настройки темы → Команда» по ФИО.
 *
 * @param string $name ФИО, сохранённое в поле блока.
 * @return array|null Строка репитера team_members либо null, если сотрудника
 *                    переименовали или удалили.
 */
function sputnik_plus_get_team_member( $name ) {
	static $index = null;

	if ( null === $index ) {
		$index = [];
		$team  = get_field( 'team_members', 'option' );

		if ( $team ) {
			foreach ( $team as $member ) {
				$key = isset( $member['name'] ) ? trim( $member['name'] ) : '';
				if ( '' !== $key && ! isset( $index[ $key ] ) ) {
					$index[ $key ] = $member;
				}
			}
		}
	}

	return $index[ $name ] ?? null;
}

/**
 * Динамическое заполнение выпадайки сотрудников
 * в блоке «Наша команда» на странице вакансий.
 */
add_filter( 'acf/load_field/key=field_68b4d5e6f7006', function ( $field ) {
	$field['choices'] = sputnik_plus_team_member_choices();

	return $field;
} );

/**
 * Динамическое заполнение выпадайки «Форма отклика» в блоке вакансий
 * списком форм Contact Form 7. Значение — ID формы: заголовок могут переименовать.
 */
add_filter( 'acf/load_field/key=field_68b3c4d5e6016', function ( $field ) {
	$field['choices'] = [];

	if ( ! post_type_exists( 'wpcf7_contact_form' ) ) {
		return $field;
	}

	$forms = get_posts(
		[
			'post_type'   => 'wpcf7_contact_form',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		]
	);

	foreach ( $forms as $form ) {
		$field['choices'][ $form->ID ] = $form->post_title;
	}

	return $field;
} );

/**
 * Форма отклика свёрстана вручную на классах темы, поэтому wpautop
 * ей только мешает — он насыпал бы <p> внутрь разметки.
 * Остальные формы продолжают форматироваться штатно.
 */
add_filter( 'wpcf7_autop_or_not', function ( $autop ) {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return $autop;
	}

	$form = WPCF7_ContactForm::get_current();

	return ( $form && 'vacancy-application' === $form->name() ) ? false : $autop;
} );

/**
 * URL страницы блога (любая страница, использующая шаблон home.php).
 * Если такой страницы нет, возвращает главную.
 */
function sputnik_plus_get_blog_url() {
	$cached = wp_cache_get( 'sputnik_blog_url' );
	if ( false !== $cached ) {
		return $cached;
	}

	$pages = get_pages( [
		'meta_key'    => '_wp_page_template',
		'meta_value'  => 'home.php',
		'number'      => 1,
		'post_status' => 'publish',
	] );

	$url = ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' );
	wp_cache_set( 'sputnik_blog_url', $url );

	return $url;
}

/**
 * Хлебные крошки. Принимает массив пар [url, title] и финальный текущий пункт.
 * Последний пункт без ссылки. Поддерживает schema.org BreadcrumbList.
 */
function sputnik_plus_breadcrumbs( array $items ) {
	if ( empty( $items ) ) {
		return;
	}

	$last = count( $items ) - 1;
	?>
	<nav class="breadcrumbs" aria-label="Хлебные крошки">
		<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
			<?php foreach ( $items as $i => $item ):
				$title = $item['title'] ?? '';
				$url   = $item['url']   ?? '';
				$pos   = $i + 1;
				?>
				<li class="breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php if ( $i === $last || empty( $url ) ): ?>
						<span class="breadcrumbs__current" itemprop="name"><?php echo esc_html( $title ); ?></span>
					<?php else: ?>
						<a class="breadcrumbs__link" href="<?php echo esc_url( $url ); ?>" itemprop="item">
							<span itemprop="name"><?php echo esc_html( $title ); ?></span>
						</a>
					<?php endif; ?>
					<meta itemprop="position" content="<?php echo (int) $pos; ?>">
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

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
			<span class="toc__list-line" data-toc-line aria-hidden="true"></span>
			<?php foreach ( $items as $item ) : ?>
				<a class="toc__link" href="#<?php echo esc_attr( $item['id'] ); ?>" data-toc-link><?php echo esc_html( $item['text'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</aside>
	<?php
}

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

/**
 * Inline SVG-иконка для карточки результата поиска по её типу.
 */
function sputnik_search_icon( $type ) {
	$paths = array(
		'article' => '<path d="M5.5 3h6l3 3v11h-9z"/><path d="M11.5 3v3h3"/><path d="M7.5 10h5"/><path d="M7.5 13h5"/>',
		'branch'  => '<path d="M4.5 16.5v-9l5.5-3 5.5 3v9"/><path d="M8.5 16.5v-3.5h3v3.5"/><path d="M10 6.5v2.5"/><path d="M8.75 7.75h2.5"/>',
		'doctor'  => '<circle cx="10" cy="6.75" r="2.75"/><path d="M4.75 16.25a5.25 5.25 0 0 1 10.5 0"/>',
		'service' => '<path d="M4 10.5 10.5 4H16v5.5L9.5 16z"/><circle cx="12.75" cy="7.25" r="1.1"/>',
		'page'    => '<path d="M6 3h5l3 3v11H6z"/><path d="M11 3v3h3"/><path d="M8 10.5h4"/><path d="M8 13h4"/>',
	);
	$path = $paths[ $type ] ?? $paths['page'];

	return '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/**
 * Форматирование цены услуги: разряды пробелом + символ ₽.
 * Сохраняет текстовый префикс («от 1500» → «от 1 500 ₽») и нечисловые значения как есть.
 */
function sputnik_format_price( $price ) {
	$price = trim( (string) $price );
	if ( '' === $price ) {
		return '';
	}

	// Числовая часть (цифры с возможными пробелами внутри).
	if ( ! preg_match( '/\d[\d\s]*/u', $price, $m ) ) {
		return $price; // Нечисловая цена («договорная») — возвращаем как есть.
	}

	$number    = (int) preg_replace( '/\D/', '', $m[0] );
	$formatted = number_format( $number, 0, '', ' ' ) . ' ₽';

	// Текст до числа (например «от»), без валютных символов.
	$prefix = trim( mb_substr( $price, 0, mb_strpos( $price, $m[0] ) ) );

	return trim( $prefix . ' ' . $formatted );
}

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
		'page'   => array( 'type' => 'page', 'label' => 'Страница' ),
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
				// Матчим услугу по её собственному названию и описанию.
				// Категория/отделение — только в хлебных крошках: иначе запрос по названию
				// отделения («терапия») вытянул бы весь его прайс.
				if ( $match( $service ) || $match( $pdesc ) ) {
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

	// Порядок выдачи: страницы → отделения → услуги → врачи → статьи.
	// usort стабилен (PHP 8.0+), поэтому порядок внутри каждого типа сохраняется.
	$type_order = array( 'page' => 0, 'branch' => 1, 'service' => 2, 'doctor' => 3, 'article' => 4 );
	usort(
		$items,
		function ( $a, $b ) use ( $type_order ) {
			$pa = $type_order[ $a['type'] ] ?? 99;
			$pb = $type_order[ $b['type'] ] ?? 99;
			return $pa <=> $pb;
		}
	);

	return $items;
}

/**
 * Онлайн-запись: параметры CRM-формы Битрикс24.
 *
 * По умолчанию — локальное окружение. Для прода переопределяются константами
 * в wp-config.php, код темы править не нужно.
 */
if ( ! defined( 'SPUTNIK_CRM_URL' ) ) {
	define( 'SPUTNIK_CRM_URL', 'http://sputnik.crm' );
}
if ( ! defined( 'SPUTNIK_CRM_FORM_ID' ) ) {
	define( 'SPUTNIK_CRM_FORM_ID', '15' );
}
if ( ! defined( 'SPUTNIK_CRM_FORM_SEC' ) ) {
	define( 'SPUTNIK_CRM_FORM_SEC', '5asnhp' );
}
/**
 * Имя поля «Услуга» в форме №15 — в него подставляется услуга, с кнопки которой
 * открыли запись. Берётся из конфига формы (loader_<id>_<sec>.js, data.fields[].name)
 * и меняется при пересборке формы, поэтому вынесено в константу.
 * Пустое значение отключает подстановку: кнопки продолжают открывать форму как есть.
 */
if ( ! defined( 'SPUTNIK_CRM_FORM_SERVICE_FIELD' ) ) {
	define( 'SPUTNIK_CRM_FORM_SERVICE_FIELD', 'DEAL_UF_CRM_SITE_SERVICE' );
}

/**
 * Соответствие «категория прайса сайта → id пункта списка в форме».
 *
 * Поле «Услуга» в форме — список, и предзаполняется он именно id пункта:
 * передача названия не срабатывает (проверено на живой форме). Пункты списка —
 * рубрики раздела «Прайс с сайта» в CRM, то есть те же категории, что в ACF
 * options price_categories, поэтому ключ здесь — название категории с сайта.
 *
 * Id выдаются при создании перечисления и на разных порталах разные: локальные
 * значения ниже, для прода переопределяются фильтром sputnik_crm_service_map
 * (например из wp-config.php или mu-plugin), код темы править не нужно.
 *
 * @return array<string,string>
 */
function sputnik_crm_service_map() {
	static $map = null;

	if ( null !== $map ) {
		return $map;
	}

	$map = apply_filters(
		'sputnik_crm_service_map',
		[
			'Вакцинация кошек и собак'                => '358',
			'Ветеринарные профилактические услуги'    => '359',
			'Ветеринарные услуги для собак и кошек'   => '360',
			'Дерматолог'                              => '361',
			'Кардиолог'                               => '362',
			'Кастрация и стерилизация'                => '363',
			'Кремация животных'                       => '364',
			'Лаборатория'                             => '365',
			'Лечение переломов у животных'            => '366',
			'МРТ'                                     => '367',
			'Ортопед'                                 => '368',
			'Офтальмолог'                             => '369',
			'Рентгенография'                          => '370',
			'Стоматолог'                              => '371',
			'Стрижка когтей/колтунов у кошек и собак' => '372',
			'Удаление опухоли у животных'             => '373',
			'УЗИ брюшной полости'                     => '374',
			'Услуги ветеринарной диагностики'         => '375',
			'Услуги ветеринарной терапии'             => '376',
			'Услуги ветеринарной хирургии'            => '377',
			'Услуги неотложной помощи'                => '378',
			'Услуги узкопрофильных специалистов'      => '379',
			'Усыпление животных'                      => '380',
			'Чипирование'                             => '381',
		]
	);

	return $map;
}

/**
 * Включена ли онлайн-запись (Настройки темы → Онлайн-запись).
 *
 * Выключенный флаг превращает все кнопки записи в звонок: якоря формы не
 * печатаются, а с ними не подключается и загрузчик CRM.
 * Пока настройки ни разу не сохраняли, ACF отдаёт default_value поля — запись включена.
 *
 * Администраторы видят онлайн-запись всегда, даже в режиме «Телефон», —
 * чтобы проверять форму на живом сайте, пока посетителям показан звонок.
 *
 * @return bool
 */
function sputnik_crm_booking_enabled() {
	if ( ! function_exists( 'get_field' ) ) {
		return true;
	}

	$enabled = (bool) get_field( 'online_booking_enabled', 'option' ) || current_user_can( 'manage_options' );

	return (bool) apply_filters( 'sputnik_crm_booking_enabled', $enabled );
}

/**
 * Ссылка-звонок вместо кнопки записи — когда онлайн-запись выключена.
 *
 * @param string $label Подпись кнопки.
 * @param string $class CSS-класс кнопки.
 */
function sputnik_crm_booking_phone_link( $label, $class = '' ) {
	$phone = sputnik_search_phone_link();
	if ( '' === $phone ) {
		$phone = '+74954450120';
	}
	?>
	<a href="<?php echo esc_attr( 'tel:' . $phone ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></a>
	<?php
}

/**
 * Печатает якорь CRM-формы онлайн-записи.
 *
 * В режиме click загрузчик Битрикса вешает обработчик на элемент, идущий сразу
 * за этим <script> (crm/site/form/src/app.js: node.nextElementSibling), поэтому
 * тег обязан стоять непосредственно перед кнопкой-триггером.
 *
 * Сам загрузчик один на страницу и подключается в подвале — см.
 * sputnik_crm_booking_loader().
 *
 * @param string $action Режим встраивания: click | inline | auto.
 */
function sputnik_crm_booking_script( $action = 'click' ) {
	sputnik_crm_booking_used( true );
	?>
	<script
		data-b24-form="<?php echo esc_attr( $action . '/' . SPUTNIK_CRM_FORM_ID . '/' . SPUTNIK_CRM_FORM_SEC ); ?>"
		data-skip-moving="true"
	></script>
	<?php
}

/**
 * Флаг «на странице есть хотя бы один якорь формы».
 *
 * @param bool $set Передать true, чтобы поднять флаг.
 * @return bool
 */
function sputnik_crm_booking_used( $set = false ) {
	static $used = false;

	if ( $set ) {
		$used = true;
	}

	return $used;
}

/**
 * Реестр услуг, для которых на странице нужна кнопка записи.
 *
 * Ключ — стабильный хеш названия, поэтому одна и та же услуга, выведенная
 * дважды, получает один триггер.
 *
 * @param string $service Название услуги; пустая строка — только чтение реестра.
 * @return array|string Реестр целиком либо ключ добавленной услуги.
 */
function sputnik_crm_booking_services( $service = '' ) {
	static $services = [];

	if ( '' === $service ) {
		return $services;
	}

	$key = substr( md5( $service ), 0, 10 );

	$services[ $key ] = [
		'name' => $service,
		// Пусто, если категории нет в карте: кнопка тогда просто открывает форму.
		'id'   => sputnik_crm_service_map()[ $service ] ?? '',
	];

	return $key;
}

/**
 * Кнопка «Записаться» с привязкой к конкретной услуге.
 *
 * Кнопка — только видимая обёртка: сам якорь Битрикса живёт в подвале, потому
 * что якорь обязан стоять вплотную к своему триггеру, а разметку блоков это
 * ограничение ломает (в слайдере услуг Swiper ещё и клонирует слайды, из-за
 * чего клон-кнопка остаётся без обработчика). Клик проксируется на скрытый
 * триггер — см. sputnik_crm_booking_triggers() и обработчик в main.js.
 *
 * @param string $label   Подпись кнопки.
 * @param string $service Название услуги.
 * @param string $class   CSS-класс кнопки.
 */
function sputnik_crm_booking_button( $label, $service, $class = '' ) {
	if ( '' === trim( (string) $service ) ) {
		return;
	}

	if ( ! sputnik_crm_booking_enabled() ) {
		sputnik_crm_booking_phone_link( $label, $class );
		return;
	}

	$key = sputnik_crm_booking_services( $service );
	?>
	<button
		type="button"
		class="<?php echo esc_attr( $class ); ?>"
		data-b24-key="<?php echo esc_attr( $key ); ?>"
	><?php echo esc_html( $label ); ?></button>
	<?php
}

/**
 * Кнопка, просто открывающая форму записи — без предвыбранной услуги.
 *
 * Якорь печатается прямо перед кнопкой, поэтому вызывать там, где разметку
 * не клонируют (не внутри слайдов Swiper) — иначе нужна
 * sputnik_crm_booking_button().
 *
 * @param string $label Подпись кнопки.
 * @param string $class CSS-класс кнопки.
 */
function sputnik_crm_booking_open_button( $label, $class = '' ) {
	if ( ! sputnik_crm_booking_enabled() ) {
		sputnik_crm_booking_phone_link( $label, $class );
		return;
	}

	sputnik_crm_booking_script();
	?>
	<button type="button" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></button>
	<?php
}

/**
 * Выводит в подвале скрытые триггеры — по одному на услугу.
 *
 * Каждый триггер это пара «якорь + кнопка»: загрузчик обходит все теги
 * script[data-b24-form] и для каждого создаёт отдельный экземпляр формы, так что
 * услуги не перетекают между кнопками.
 */
function sputnik_crm_booking_triggers() {
	$services = sputnik_crm_booking_services();

	if ( ! $services ) {
		return;
	}
	?>
	<div class="crm-booking-triggers" hidden>
		<?php foreach ( $services as $key => $service ) : ?>
			<?php sputnik_crm_booking_script(); ?>
			<button
				type="button"
				class="crm-booking-triggers__item"
				data-b24-key="<?php echo esc_attr( $key ); ?>"
				data-b24-service="<?php echo esc_attr( $service['id'] ); ?>"
				data-b24-name="<?php echo esc_attr( $service['name'] ); ?>"
				data-b24-field="<?php echo esc_attr( SPUTNIK_CRM_FORM_SERVICE_FIELD ); ?>"
				tabindex="-1"
				aria-hidden="true"
			></button>
		<?php endforeach; ?>
	</div>
	<?php
}
add_action( 'wp_footer', 'sputnik_crm_booking_triggers', 5 );

/**
 * Подключает загрузчик CRM-формы — один на страницу, в самом конце подвала.
 *
 * Порядок важен: загрузчик обходит теги script[data-b24-form] один раз, сразу
 * после загрузки, поэтому он обязан идти после всех якорей — включая те, что
 * печатает sputnik_crm_booking_triggers().
 *
 * Подключается не штатный loader_<id>_<sec>.js, а обёртка портала
 * /local/assets/booking-form/loader.php. Она тянет тот же штатный загрузчик, а
 * сверху даёт бренд-слой (form.css) и две правки виджета: фото врача в списке
 * выбора (штатный виджет его не рисует) и абсолютные пути к аватарам — иначе
 * браузер ищет /upload/booking/... на домене сайта и получает 404.
 */
function sputnik_crm_booking_loader() {
	if ( ! sputnik_crm_booking_used() ) {
		return;
	}

	$origin = rtrim( SPUTNIK_CRM_URL, '/' );
	?>
	<div class="crm-booking-loader" hidden>
		<script
			src="<?php echo esc_url( $origin . '/local/assets/booking-form/loader.php' ); ?>" async
			data-b24-origin="<?php echo esc_attr( $origin ); ?>"
			data-b24-form="<?php echo esc_attr( 'click/' . SPUTNIK_CRM_FORM_ID . '/' . SPUTNIK_CRM_FORM_SEC ); ?>"
			data-skip-moving="true"
		></script>
		<?php // Тег загрузчика заодно остаётся якорем — так было до разделения, и обёртка
		      // портала может читать data-b24-form со своего же тега. Раз якорь есть,
		      // Битрикс повесит обработчик на следующий элемент, поэтому следом идёт
		      // кнопка-пустышка: иначе обработчик достанется случайному узлу страницы. ?>
		<button type="button" tabindex="-1" aria-hidden="true"></button>
	</div>
	<?php
}
add_action( 'wp_footer', 'sputnik_crm_booking_loader', 99 );
