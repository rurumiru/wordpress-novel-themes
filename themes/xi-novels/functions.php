<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XIN_VERSION', '0.3.1-beta' );
define( 'XIN_DIR', get_template_directory() );
define( 'XIN_URI', get_template_directory_uri() );

require_once XIN_DIR . '/inc/cpt.php';
require_once XIN_DIR . '/inc/meta-boxes.php';
require_once XIN_DIR . '/inc/icons.php';
require_once XIN_DIR . '/inc/template-tags.php';
require_once XIN_DIR . '/inc/skin.php';
require_once XIN_DIR . '/inc/glossary.php';
require_once XIN_DIR . '/inc/customizer.php';
require_once XIN_DIR . '/inc/widgets.php';
require_once XIN_DIR . '/inc/authoring.php';
require_once XIN_DIR . '/inc/auth.php';
require_once XIN_DIR . '/inc/manage.php';
require_once XIN_DIR . '/inc/access.php';
require_once XIN_DIR . '/inc/reading.php';
require_once XIN_DIR . '/inc/export.php';
require_once XIN_DIR . '/inc/discussions.php';
require_once XIN_DIR . '/inc/banners.php';
require_once XIN_DIR . '/inc/user-fields.php';
require_once XIN_DIR . '/inc/i18n.php';
require_once XIN_DIR . '/inc/nav-walker.php';
require_once XIN_DIR . '/inc/cleanup.php';

function xin_setup() {
	load_theme_textdomain( 'xi-novels', XIN_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array( 'default-color' => '' ) );
	add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'video' ) );

add_image_size( 'xin-cover', 320, 480, true );
	add_image_size( 'xin-cover-lg', 520, 780, true );
	add_image_size( 'xin-cover-sm', 120, 180, true );
	add_image_size( 'xin-banner', 1920, 640, true );
	add_image_size( 'xin-wide', 720, 405, true );

	register_nav_menus( array(
		'primary' => __( 'Главное меню', 'xi-novels' ),
		'footer'  => __( 'Меню подвала', 'xi-novels' ),
		'legal'   => __( 'Правовые ссылки (низ подвала)', 'xi-novels' ),
		'quick'   => __( 'Быстрые переходы (плитки под баннером)', 'xi-novels' ),
	) );

if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 820;
	}
}
add_action( 'after_setup_theme', 'xin_setup' );

function xin_asset_ver( $file ) {
	$path = XIN_DIR . $file;
	$time = file_exists( $path ) ? filemtime( $path ) : 0;

	return $time ? XIN_VERSION . '.' . $time : XIN_VERSION;
}

function xin_assets() {
	wp_enqueue_style( 'bootstrap', XIN_URI . '/assets/vendor/bootstrap/bootstrap.min.css', array(), '5.3.3' );
	wp_enqueue_style( 'xi-novels', get_stylesheet_uri(), array( 'bootstrap' ), xin_asset_ver( '/style.css' ) );
	wp_enqueue_style( 'xi-novels-skin', XIN_URI . '/assets/css/skin.css', array( 'xi-novels' ), xin_asset_ver( '/assets/css/skin.css' ) );
	wp_enqueue_style( 'xi-novels-pages', XIN_URI . '/assets/css/pages.css', array( 'xi-novels-skin' ), xin_asset_ver( '/assets/css/pages.css' ) );
	wp_enqueue_style( 'xi-novels-parts', XIN_URI . '/assets/css/parts.css', array( 'xi-novels-pages' ), xin_asset_ver( '/assets/css/parts.css' ) );
	wp_enqueue_style( 'xi-novels-widgets', XIN_URI . '/assets/css/widgets.css', array( 'xi-novels-parts' ), xin_asset_ver( '/assets/css/widgets.css' ) );
	wp_enqueue_style( 'xi-novels-landing', XIN_URI . '/assets/css/landing.css', array( 'xi-novels-widgets' ), xin_asset_ver( '/assets/css/landing.css' ) );

	wp_enqueue_script( 'bootstrap', XIN_URI . '/assets/vendor/bootstrap/bootstrap.bundle.min.js', array(), '5.3.3', true );

$custom = xin_customizer_css();
	if ( $custom ) {
		wp_add_inline_style( 'xi-novels-pages', $custom );
	}

	wp_enqueue_script( 'xi-novels', XIN_URI . '/assets/js/theme.js', array(), xin_asset_ver( '/assets/js/theme.js' ), true );
	wp_localize_script( 'xi-novels', 'XIN', array(
		'restUrl'      => esc_url_raw( rest_url( 'xin/v1/' ) ),
		'nonce'        => wp_create_nonce( 'wp_rest' ),
		'homeUrl'      => home_url( '/' ),
		'defaultTheme' => get_theme_mod( 'xin_default_scheme', 'light' ),
		'read'         => xin_skin_reader_defaults(),
		'i18n'         => array(
			'added'   => __( 'В библиотеке', 'xi-novels' ),
			'add'     => __( 'В библиотеку', 'xi-novels' ),
			'empty'   => __( 'Здесь пока пусто', 'xi-novels' ),
			'nothing' => __( 'Ничего не найдено', 'xi-novels' ),
			'library' => __( 'Моя библиотека', 'xi-novels' ),
			'hint'    => __( 'Нажмите на закладку у любой обложки — тайтл появится здесь.', 'xi-novels' ),
			'saved'   => __( 'черновик сохранён', 'xi-novels' ),
		),
	) );

	wp_register_script( 'xi-novels-replace', XIN_URI . '/assets/js/replace.js', array(), xin_asset_ver( '/assets/js/replace.js' ), true );

	if ( is_singular( 'chapter' ) ) {
		wp_enqueue_script( 'xi-novels-reader', XIN_URI . '/assets/js/reader.js', array( 'xi-novels' ), xin_asset_ver( '/assets/js/reader.js' ), true );

		wp_enqueue_script( 'xi-novels-glossary', XIN_URI . '/assets/js/glossary.js', array( 'xi-novels-reader', 'xi-novels-replace' ), xin_asset_ver( '/assets/js/glossary.js' ), true );
		wp_localize_script( 'xi-novels-glossary', 'XIN_GL', array(
			'project' => xin_glossary_for_js( xin_chapter_novel_id( get_queried_object_id() ) ),
			'icons' => array(
				'check' => xin_icon( 'check' ),
				'trash' => xin_icon( 'trash' ),
			),
			'i18n'  => array(
				'add'        => __( 'Добавить', 'xi-novels' ),
				'save'       => __( 'Сохранить', 'xi-novels' ),
				'empty'      => __( 'Правил пока нет. Выделите слово в тексте — или впишите его сами.', 'xi-novels' ),
				'hint'       => __( 'Термин слева заменяется на термин справа прямо при чтении.', 'xi-novels' ),
				'stat'       => __( 'Правил: %1$s · замен в главе: %2$s', 'xi-novels' ),
				'scopeNovel' => __( 'Этот тайтл', 'xi-novels' ),
				'scopeAll'   => __( 'Все тайтлы', 'xi-novels' ),
				'scopeProject'   => __( 'От переводчика', 'xi-novels' ),
				'fromTranslator' => __( 'Правило переводчика — его можно только выключить целиком', 'xi-novels' ),
				'ruleOn'     => __( 'Включить правило', 'xi-novels' ),
				'ruleOff'    => __( 'Выключить правило', 'xi-novels' ),
				'ruleEdit'   => __( 'Изменить правило', 'xi-novels' ),
				'ruleDelete' => __( 'Удалить правило', 'xi-novels' ),
				'ruleCut'    => __( '— убрать —', 'xi-novels' ),
				'flagCase'   => __( 'регистр', 'xi-novels' ),
				'flagWhole'  => __( 'целиком', 'xi-novels' ),
				'was'        => __( 'В оригинале: %s', 'xi-novels' ),
				'imported'   => __( 'Добавлено правил: %s', 'xi-novels' ),
				'badFile'    => __( 'Не получилось прочитать файл словаря.', 'xi-novels' ),
			),
		) );
	}

	if ( is_page_template( 'template-dashboard.php' ) ) {
		$xin_project = isset( $_GET['project'] ) ? absint( $_GET['project'] ) : 0;
		if ( ! $xin_project && isset( $_GET['id'] ) ) {
			$xin_project = xin_chapter_novel_id( absint( $_GET['id'] ) );
		}

		if ( current_user_can( 'upload_files' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_style( 'xi-novels-writer', XIN_URI . '/assets/css/writer.css', array( 'xi-novels-parts' ), xin_asset_ver( '/assets/css/writer.css' ) );
		wp_enqueue_script( 'xi-novels-writer', XIN_URI . '/assets/js/writer.js', array( 'xi-novels', 'xi-novels-replace' ), xin_asset_ver( '/assets/js/writer.js' ), true );
		wp_localize_script( 'xi-novels-writer', 'XIN_WRITER', array(
			'glossary' => $xin_project ? xin_glossary_for_js( $xin_project ) : array(),
			'i18n'     => array(
				'stats'           => __( 'Слов: %1$s · знаков: %2$s · ~%3$s мин чтения', 'xi-novels' ),
				'saved'           => __( 'черновик сохранён', 'xi-novels' ),
				'tidied'          => __( 'текст причёсан', 'xi-novels' ),
				'replaced'        => __( 'Заменено: %s', 'xi-novels' ),
				'glossaryApplied' => __( 'Словарь проекта применён, замен: %s', 'xi-novels' ),
				'draftFound'      => __( 'В браузере остался черновик от %s', 'xi-novels' ),
				'pickImage'       => __( 'Выбрать картинку', 'xi-novels' ),
			),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'xin_assets' );

function xin_no_flash_script() {
	$default = get_theme_mod( 'xin_default_scheme', 'light' );
	?>
	<script>
	(function () {
		try {
			var saved = localStorage.getItem('xin-theme');
			var mode = saved || <?php echo wp_json_encode( $default ); ?>;
			if (mode === 'auto') {
				mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
			}
			document.documentElement.setAttribute('data-theme', mode);
		} catch (e) {
			document.documentElement.setAttribute('data-theme', <?php echo wp_json_encode( $default ); ?>);
		}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'xin_no_flash_script', 1 );

function xin_widgets_init() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar( array_merge( $common, array(
		'name'        => __( 'Боковая колонка блога', 'xi-novels' ),
		'id'          => 'sidebar-blog',
		'description' => __( 'Показывается в блоге и на записях.', 'xi-novels' ),
	) ) );

	register_sidebar( array_merge( $common, array(
		'name'        => __( 'Боковая колонка тайтла', 'xi-novels' ),
		'id'          => 'sidebar-novel',
		'description' => __( 'Показывается на странице новеллы под блоком информации.', 'xi-novels' ),
	) ) );

	register_sidebar( array_merge( $common, array(
		'name'        => __( 'Подвал', 'xi-novels' ),
		'id'          => 'footer-widgets',
		'description' => __( 'Колонки виджетов в подвале (вместо меню).', 'xi-novels' ),
	) ) );
}
add_action( 'widgets_init', 'xin_widgets_init' );

function xin_body_class( $classes ) {
	if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
		$classes[] = 'xin-no-sidebar';
	}
	if ( is_singular( 'chapter' ) ) {
		$classes[] = 'xin-is-reader';
	}
	return $classes;
}
add_filter( 'body_class', 'xin_body_class' );

function xin_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'xin_excerpt_length' );

function xin_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'xin_excerpt_more' );

add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

function xin_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'novel' ) || $query->is_tax( array( 'genre', 'novel_tag', 'novel_status' ) ) ) {
		$query->set( 'posts_per_page', 24 );

		$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : '';
		switch ( $sort ) {
			case 'popular':
				$query->set( 'meta_key', '_xin_views' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
				break;
			case 'rating':
				$query->set( 'meta_key', '_xin_rating' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
				break;
			case 'updated':
				$query->set( 'orderby', 'modified' );
				$query->set( 'order', 'DESC' );
				break;
			case 'title':
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
				break;
			default:
				$query->set( 'orderby', 'date' );
				$query->set( 'order', 'DESC' );
		}

		$status = isset( $_GET['status'] ) ? sanitize_title( wp_unslash( $_GET['status'] ) ) : '';
		if ( $status && ! $query->is_tax( 'novel_status' ) ) {
			$tax_query   = (array) $query->get( 'tax_query' );
			$tax_query[] = array(
				'taxonomy' => 'novel_status',
				'field'    => 'slug',
				'terms'    => $status,
			);
			$query->set( 'tax_query', $tax_query );
		}
	}

if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array( 'post', 'page', 'novel', 'chapter' ) );
	}
}
add_action( 'pre_get_posts', 'xin_pre_get_posts' );

function xin_get_views( $post_id ) {
	return (int) get_post_meta( $post_id, '_xin_views', true );
}

function xin_count_view() {
	if ( ! is_singular( array( 'novel', 'chapter', 'post' ) ) || is_preview() ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$cookie = 'xin_seen_' . $post_id;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return;
	}

	update_post_meta( $post_id, '_xin_views', xin_get_views( $post_id ) + 1 );

if ( 'chapter' === get_post_type( $post_id ) ) {
		$novel_id = xin_chapter_novel_id( $post_id );
		if ( $novel_id ) {
			update_post_meta( $novel_id, '_xin_views', xin_get_views( $novel_id ) + 1 );
		}
	}

	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
}
add_action( 'template_redirect', 'xin_count_view' );

function xin_register_rest() {
	register_rest_route( 'xin/v1', '/rate', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id'    => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'value' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xin_rest_rate',
	) );
}
add_action( 'rest_api_init', 'xin_register_rest' );

function xin_rest_rate( WP_REST_Request $request ) {
	$id    = (int) $request['id'];
	$value = min( 5, max( 1, (int) $request['value'] ) );

	if ( 'novel' !== get_post_type( $id ) ) {
		return new WP_Error( 'xin_bad_target', __( 'Оценивать можно только новеллы.', 'xi-novels' ), array( 'status' => 400 ) );
	}

	$count = (int) get_post_meta( $id, '_xin_rating_count', true );
	$avg   = (float) get_post_meta( $id, '_xin_rating', true );
	$sum   = $avg * $count + $value;
	$count++;

	$new = round( $sum / $count, 2 );
	update_post_meta( $id, '_xin_rating', $new );
	update_post_meta( $id, '_xin_rating_count', $count );

	return array(
		'rating' => $new,
		'count'  => $count,
	);
}

function xin_dequeue_bloat() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'xin_dequeue_bloat' );

function xin_avatar( $id_or_email, $size = 40, $name = '' ) {
	$avatar = get_avatar( $id_or_email, $size, '', $name, array( 'class' => 'xin-avatar-img' ) );
	if ( $avatar ) {
		return $avatar;
	}
	$letter = mb_strtoupper( mb_substr( $name ? $name : 'A', 0, 1 ) );
	return sprintf(
		'<span class="xin-avatar" style="width:%1$dpx;height:%1$dpx;font-size:%2$dpx">%3$s</span>',
		(int) $size,
		max( 11, (int) round( $size / 2.6 ) ),
		esc_html( $letter )
	);
}

function xin_admin_notice_empty() {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}
	$count = (int) wp_count_posts( 'novel' )->publish;
	if ( $count > 0 ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>XI Novels:</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Каталог пуст. Добавьте первый тайтл:', 'xi-novels' ),
		esc_url( admin_url( 'post-new.php?post_type=novel' ) ),
		esc_html__( 'Новеллы → Добавить', 'xi-novels' )
	);
}
add_action( 'admin_notices', 'xin_admin_notice_empty' );
