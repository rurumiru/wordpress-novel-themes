<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XIN_VERSION', '0.6.0-beta' );
define( 'XIN_DIR', get_template_directory() );
define( 'XIN_URI', get_template_directory_uri() );

require_once XIN_DIR . '/inc/cpt.php';
require_once XIN_DIR . '/inc/format.php';
require_once XIN_DIR . '/inc/comics.php';
require_once XIN_DIR . '/inc/permalinks.php';
require_once XIN_DIR . '/inc/meta-boxes.php';
require_once XIN_DIR . '/inc/icons.php';
require_once XIN_DIR . '/inc/template-tags.php';
require_once XIN_DIR . '/inc/ranking.php';
require_once XIN_DIR . '/inc/skin.php';
require_once XIN_DIR . '/inc/glossary.php';
require_once XIN_DIR . '/inc/customizer.php';
require_once XIN_DIR . '/inc/widgets.php';
require_once XIN_DIR . '/inc/authoring.php';
require_once XIN_DIR . '/inc/auth.php';
require_once XIN_DIR . '/inc/manage.php';
require_once XIN_DIR . '/inc/access.php';
require_once XIN_DIR . '/inc/reading.php';
require_once XIN_DIR . '/inc/hub.php';
require_once XIN_DIR . '/inc/export.php';
require_once XIN_DIR . '/inc/discussions.php';
require_once XIN_DIR . '/inc/banners.php';
require_once XIN_DIR . '/inc/user-fields.php';
require_once XIN_DIR . '/inc/i18n.php';
require_once XIN_DIR . '/inc/nav-walker.php';
require_once XIN_DIR . '/inc/cleanup.php';
require_once XIN_DIR . '/inc/push.php';

function xin_setup() {
	load_theme_textdomain( 'xin-com', XIN_DIR . '/languages' );

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
		'primary' => __( 'Главное меню', 'xin-com' ),
		'footer'  => __( 'Меню подвала', 'xin-com' ),
		'legal'   => __( 'Правовые ссылки (низ подвала)', 'xin-com' ),
		'quick'   => __( 'Быстрые переходы (плитки под баннером)', 'xin-com' ),
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
	wp_enqueue_style( 'xin-com', get_stylesheet_uri(), array( 'bootstrap' ), xin_asset_ver( '/style.css' ) );
	wp_enqueue_style( 'xin-com-skin', XIN_URI . '/assets/css/skin.css', array( 'xin-com' ), xin_asset_ver( '/assets/css/skin.css' ) );
	wp_enqueue_style( 'xin-com-pages', XIN_URI . '/assets/css/pages.css', array( 'xin-com-skin' ), xin_asset_ver( '/assets/css/pages.css' ) );
	wp_enqueue_style( 'xin-com-parts', XIN_URI . '/assets/css/parts.css', array( 'xin-com-pages' ), xin_asset_ver( '/assets/css/parts.css' ) );
	wp_enqueue_style( 'xin-com-widgets', XIN_URI . '/assets/css/widgets.css', array( 'xin-com-parts' ), xin_asset_ver( '/assets/css/widgets.css' ) );
	wp_enqueue_style( 'xin-com-landing', XIN_URI . '/assets/css/landing.css', array( 'xin-com-widgets' ), xin_asset_ver( '/assets/css/landing.css' ) );

	if ( xin_comics_enabled() ) {
		wp_enqueue_style( 'xin-com-comics', XIN_URI . '/assets/css/comics.css', array( 'xin-com-landing' ), xin_asset_ver( '/assets/css/comics.css' ) );
	}


	wp_enqueue_script( 'bootstrap', XIN_URI . '/assets/vendor/bootstrap/bootstrap.bundle.min.js', array(), '5.3.3', true );

$custom = xin_customizer_css();
	if ( $custom ) {
		wp_add_inline_style( 'xin-com-pages', $custom );
	}

	wp_enqueue_script( 'xin-com', XIN_URI . '/assets/js/theme.js', array(), xin_asset_ver( '/assets/js/theme.js' ), true );
	wp_localize_script( 'xin-com', 'XIN', array(
		'restUrl'      => esc_url_raw( rest_url( 'xin/v1/' ) ),
		'nonce'        => wp_create_nonce( 'wp_rest' ),
		'homeUrl'      => home_url( '/' ),
		'defaultTheme' => get_theme_mod( 'xin_default_scheme', 'light' ),
		'loggedIn'     => is_user_logged_in(),
		'loginUrl'     => wp_login_url(),
		'read'         => xin_skin_reader_defaults(),
		'i18n'         => array(
			'added'   => __( 'В библиотеке', 'xin-com' ),
			'add'     => __( 'В библиотеку', 'xin-com' ),
			'empty'   => __( 'Здесь пока пусто', 'xin-com' ),
			'nothing' => __( 'Ничего не найдено', 'xin-com' ),
			'library' => __( 'Моя библиотека', 'xin-com' ),
			'hint'    => __( 'Нажмите на закладку у любой обложки — тайтл появится здесь.', 'xin-com' ),
			'saved'   => __( 'черновик сохранён', 'xin-com' ),
			'quoted'  => __( 'Цитата добавлена в обсуждение.', 'xin-com' ),
			'copied'  => __( 'Скопировано в буфер обмена.', 'xin-com' ),
			'linkCopied' => __( 'Ссылка на абзац скопирована.', 'xin-com' ),
			'bookmarked' => __( 'Абзац отмечен закладкой.', 'xin-com' ),
			'bookmarkOff' => __( 'Закладка снята.', 'xin-com' ),
			'suggested' => __( 'Правка добавлена в обсуждение. Пролистайте вниз и отправьте.', 'xin-com' ),
			'ttsOff'  => __( 'Синтез речи в этом браузере недоступен.', 'xin-com' ),
			'loginTalk' => __( 'Войдите, чтобы цитировать и предлагать правки.', 'xin-com' ),
			'jumpBookmark' => __( 'К закладке', 'xin-com' ),
			'quoteEllipsis' => '…',
			'voiceLocal' => __( 'На устройстве', 'xin-com' ),
			'voiceNet' => __( 'Браузерный', 'xin-com' ),
			'voiceEmpty' => __( 'Голосов не нашлось. Windows: Параметры → Время и язык → Речь. В Chrome появятся ещё и голоса Google, если снять «Только на устройстве».', 'xin-com' ),
			'voiceCount' => __( 'Голосов на этом устройстве: %d', 'xin-com' ),
			'previewSample' => __( 'Печать сломали тысячу лет назад, чтобы никто не удержал её целиком.', 'xin-com' ),
			'chooseVoice' => __( 'Выберите голос', 'xin-com' ),
		),
	) );

	wp_register_script( 'xin-com-replace', XIN_URI . '/assets/js/replace.js', array(), xin_asset_ver( '/assets/js/replace.js' ), true );

	/*
	 * Глава комикса получает свою читалку и не получает текстовую: словарь,
	 * озвучка и абзацные инструменты работают по тексту, которого здесь нет, а
	 * reader.js на пустом месте только считал бы прокрутку впустую.
	 */
	if ( is_singular( 'chapter' ) && 'comic' === xin_chapter_format( get_queried_object_id() ) ) {
		wp_enqueue_script( 'xin-com-comic-reader', XIN_URI . '/assets/js/comic-reader.js', array(), xin_asset_ver( '/assets/js/comic-reader.js' ), true );
	} elseif ( is_singular( 'chapter' ) ) {
		wp_enqueue_script( 'xin-com-reader', XIN_URI . '/assets/js/reader.js', array( 'xin-com' ), xin_asset_ver( '/assets/js/reader.js' ), true );

		wp_enqueue_script( 'xin-com-glossary', XIN_URI . '/assets/js/glossary.js', array( 'xin-com-reader', 'xin-com-replace' ), xin_asset_ver( '/assets/js/glossary.js' ), true );
		wp_localize_script( 'xin-com-glossary', 'XIN_GL', array(
			'project' => xin_glossary_for_js( xin_chapter_novel_id( get_queried_object_id() ) ),
			'icons' => array(
				'check' => xin_icon( 'check' ),
				'trash' => xin_icon( 'trash' ),
			),
			'i18n'  => array(
				'add'        => __( 'Добавить', 'xin-com' ),
				'save'       => __( 'Сохранить', 'xin-com' ),
				'empty'      => __( 'Правил пока нет. Выделите слово в тексте — или впишите его сами.', 'xin-com' ),
				'hint'       => __( 'Термин слева заменяется на термин справа прямо при чтении.', 'xin-com' ),
				'stat'       => __( 'Правил: %1$s · замен в главе: %2$s', 'xin-com' ),
				'scopeNovel' => __( 'Этот тайтл', 'xin-com' ),
				'scopeAll'   => __( 'Все тайтлы', 'xin-com' ),
				'scopeProject'   => __( 'От переводчика', 'xin-com' ),
				'fromTranslator' => __( 'Правило переводчика — его можно только выключить целиком', 'xin-com' ),
				'ruleOn'     => __( 'Включить правило', 'xin-com' ),
				'ruleOff'    => __( 'Выключить правило', 'xin-com' ),
				'ruleEdit'   => __( 'Изменить правило', 'xin-com' ),
				'ruleDelete' => __( 'Удалить правило', 'xin-com' ),
				'ruleCut'    => __( '— убрать —', 'xin-com' ),
				'flagCase'   => __( 'регистр', 'xin-com' ),
				'flagWhole'  => __( 'целиком', 'xin-com' ),
				'was'        => __( 'В оригинале: %s', 'xin-com' ),
				'imported'   => __( 'Добавлено правил: %s', 'xin-com' ),
				'badFile'    => __( 'Не получилось прочитать файл словаря.', 'xin-com' ),
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

		wp_enqueue_style( 'xin-com-writer', XIN_URI . '/assets/css/writer.css', array( 'xin-com-parts' ), xin_asset_ver( '/assets/css/writer.css' ) );
		wp_enqueue_script( 'xin-com-writer', XIN_URI . '/assets/js/writer.js', array( 'xin-com', 'xin-com-replace' ), xin_asset_ver( '/assets/js/writer.js' ), true );
		wp_localize_script( 'xin-com-writer', 'XIN_WRITER', array(
			'glossary' => $xin_project ? xin_glossary_for_js( $xin_project ) : array(),
			'i18n'     => array(
				'stats'           => __( 'Слов: %1$s · знаков: %2$s · ~%3$s мин чтения', 'xin-com' ),
				'saved'           => __( 'черновик сохранён', 'xin-com' ),
				'tidied'          => __( 'текст причёсан', 'xin-com' ),
				'replaced'        => __( 'Заменено: %s', 'xin-com' ),
				'glossaryApplied' => __( 'Словарь проекта применён, замен: %s', 'xin-com' ),
				'draftFound'      => __( 'В браузере остался черновик от %s', 'xin-com' ),
				'pickImage'       => __( 'Выбрать картинку', 'xin-com' ),
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
		'name'        => __( 'Боковая колонка блога', 'xin-com' ),
		'id'          => 'sidebar-blog',
		'description' => __( 'Показывается в блоге и на записях.', 'xin-com' ),
	) ) );

	register_sidebar( array_merge( $common, array(
		'name'        => __( 'Боковая колонка тайтла', 'xin-com' ),
		'id'          => 'sidebar-novel',
		'description' => __( 'Показывается на странице новеллы под блоком информации.', 'xin-com' ),
	) ) );

	register_sidebar( array_merge( $common, array(
		'name'        => __( 'Подвал', 'xin-com' ),
		'id'          => 'footer-widgets',
		'description' => __( 'Колонки виджетов в подвале (вместо меню).', 'xin-com' ),
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
		return new WP_Error( 'xin_bad_target', __( 'Оценивать можно только новеллы.', 'xin-com' ), array( 'status' => 400 ) );
	}

	$count = (int) get_post_meta( $id, '_xin_rating_count', true );
	$avg   = (float) get_post_meta( $id, '_xin_rating', true );
	$sum   = $avg * $count + $value;
	$count++;

	$new = round( $sum / $count, 2 );
	update_post_meta( $id, '_xin_rating', $new );
	update_post_meta( $id, '_xin_rating_count', $count );

	/**
	 * A new vote reorders the rating board.
	 *
	 * @param int $id Novel that was rated.
	 */
	do_action( 'xin_rating_saved', $id );

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
		'<div class="notice notice-info"><p><strong>XIN-Com:</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Каталог пуст. Добавьте первый тайтл:', 'xin-com' ),
		esc_url( admin_url( 'post-new.php?post_type=novel' ) ),
		esc_html__( 'Новеллы → Добавить', 'xin-com' )
	);
}
add_action( 'admin_notices', 'xin_admin_notice_empty' );
