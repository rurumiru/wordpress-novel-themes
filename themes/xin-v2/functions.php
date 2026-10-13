<?php
/**
 * XIN-V2 — витрина для чтения веб-новелл.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XV_VERSION', '3.0.1' );

/**
 * Настройка темы. Все опции живут в одном массиве, чтобы кастомайзер и
 * шаблоны читали их одинаково.
 */
function xv_defaults() {
	return array(
		'tagline'        => __( 'Библиотека переводов — читайте лучшее', 'xin-v2' ),
		'footer_note'    => '',
		'discussions'    => true,
		'reader_toolbar' => true,
		// Бесконечная прокрутка и озвучка — по умолчанию включены, но их
		// выключают: на слабых устройствах дочитывать по главам спокойнее.
		'reader_endless' => false,
		'reader_speech'  => true,
		'glossary'       => true,
		// Кому открыта выгрузка книг: all, users, team, nobody.
		'downloads'      => 'all',
		// Раздел комиксов выключен по умолчанию: площадке с одним текстом
		// лишний переключатель форматов в каталоге только мешает.
		'comics'         => false,
		'coin_label'     => '₽',
		'hero_limit'     => 5,
		'site_width'     => 1200,
		'catalog_per_page' => 24,
		'reader_size'    => 18,
		'reader_width'   => 720,
		'home_sections'  => array( 'hero', 'blog', 'genres', 'popular', 'rated', 'fresh', 'updates' ),
		// Куда ведёт кнопка поддержки. Приёма денег в теме нет — только ссылка.
		'plus_url'       => '',
		'plus_note'      => '',
	);
}

function xv_opt( $key, $fallback = null ) {
	static $opts = null;

	if ( null === $opts ) {
		$opts = wp_parse_args( (array) get_option( 'xv_settings', array() ), xv_defaults() );
	}

	if ( ! array_key_exists( $key, $opts ) ) {
		return $fallback;
	}

	return $opts[ $key ];
}

function xv_set_opt( $key, $value ) {
	$opts         = wp_parse_args( (array) get_option( 'xv_settings', array() ), xv_defaults() );
	$opts[ $key ] = $value;
	update_option( 'xv_settings', $opts );
}

require_once get_theme_file_path( 'inc/i18n.php' );
require_once get_theme_file_path( 'inc/performance.php' );
require_once get_theme_file_path( 'inc/icons.php' );
require_once get_theme_file_path( 'inc/cpt.php' );
require_once get_theme_file_path( 'inc/access.php' );
require_once get_theme_file_path( 'inc/chapters.php' );
require_once get_theme_file_path( 'inc/reader.php' );
require_once get_theme_file_path( 'inc/glossary.php' );
require_once get_theme_file_path( 'inc/export.php' );
require_once get_theme_file_path( 'inc/web-push-sender.php' );
require_once get_theme_file_path( 'inc/push.php' );
require_once get_theme_file_path( 'inc/banners.php' );
require_once get_theme_file_path( 'inc/widgets.php' );
require_once get_theme_file_path( 'inc/comics.php' );
require_once get_theme_file_path( 'inc/auth.php' );
require_once get_theme_file_path( 'inc/hub.php' );
require_once get_theme_file_path( 'inc/manage.php' );
require_once get_theme_file_path( 'inc/template-tags.php' );
require_once get_theme_file_path( 'inc/meta-boxes.php' );
require_once get_theme_file_path( 'inc/query.php' );
require_once get_theme_file_path( 'inc/wuxia-navigation.php' );
require_once get_theme_file_path( 'inc/reading.php' );
require_once get_theme_file_path( 'inc/discussions.php' );
require_once get_theme_file_path( 'inc/authoring.php' );
require_once get_theme_file_path( 'inc/profile.php' );
require_once get_theme_file_path( 'inc/admin.php' );
require_once get_theme_file_path( 'inc/customizer.php' );

function xv_setup() {
	load_theme_textdomain( 'xin-v2', get_theme_file_path( 'languages' ) );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	// Обложка — 2:3, вертикальный книжный формат.
	add_image_size( 'xv-cover', 300, 450, true );
	add_image_size( 'xv-cover-lg', 480, 720, true );
	add_image_size( 'xv-banner', 1600, 640, true );

	register_nav_menus( array(
		'primary'   => __( 'Основное меню', 'xin-v2' ),
		'footer'    => __( 'Разделы в подвале', 'xin-v2' ),
		'footer_info' => __( 'Информация в подвале', 'xin-v2' ),
	) );

	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'xv_setup' );

function xv_asset_ver( $file ) {
	$path = get_theme_file_path( $file );

	return file_exists( $path ) ? (string) filemtime( $path ) : XV_VERSION;
}

function xv_assets() {
	// Source Serif используется в чтении и заголовках, а интерфейс берёт системный шрифт.
	// JetBrains Mono — служебные подписи. Все четыре лежат в теме: сайт не
	// ходит на чужой домен и не ждёт чужой DNS.
	wp_enqueue_style( 'xv-fonts', get_theme_file_uri( 'assets/css/fonts.css' ), array(), xv_asset_ver( 'assets/css/fonts.css' ) );

	wp_enqueue_style( 'xv-style', get_stylesheet_uri(), array( 'xv-fonts' ), xv_asset_ver( 'style.css' ) );
	wp_enqueue_style( 'xv-pages', get_theme_file_uri( 'assets/css/pages.css' ), array( 'xv-style' ), xv_asset_ver( 'assets/css/pages.css' ) );
	wp_enqueue_style( 'xv-community', get_theme_file_uri( 'assets/css/community.css' ), array( 'xv-pages' ), xv_asset_ver( 'assets/css/community.css' ) );
	wp_enqueue_style( 'xv-wp', get_theme_file_uri( 'assets/css/wp.css' ), array( 'xv-community' ), xv_asset_ver( 'assets/css/wp.css' ) );
	wp_enqueue_style( 'xv-redesign', get_theme_file_uri( 'assets/css/redesign.css' ), array( 'xv-wp' ), xv_asset_ver( 'assets/css/redesign.css' ) );
	wp_enqueue_style( 'xv-dragons', get_theme_file_uri( 'assets/css/dragons.css' ), array( 'xv-redesign' ), xv_asset_ver( 'assets/css/dragons.css' ) );
	wp_enqueue_style( 'xv-wuxia', get_theme_file_uri( 'assets/css/wuxia.css' ), array( 'xv-dragons' ), xv_asset_ver( 'assets/css/wuxia.css' ) );
	wp_enqueue_style( 'xv-wuxiaworld', get_theme_file_uri( 'assets/css/wuxiaworld.css' ), array( 'xv-wuxia' ), xv_asset_ver( 'assets/css/wuxiaworld.css' ) );
	wp_enqueue_style( 'xv-header', get_theme_file_uri( 'assets/css/site-header.css' ), array( 'xv-wuxiaworld' ), xv_asset_ver( 'assets/css/site-header.css' ) );
	wp_enqueue_style( 'xv-polish', get_theme_file_uri( 'assets/css/interface.css' ), array( 'xv-header' ), xv_asset_ver( 'assets/css/interface.css' ) );
	wp_enqueue_script('xv-interface', get_theme_file_uri('assets/js/interface.js'), array('xv-theme'), xv_asset_ver('assets/js/interface.js'), true);

	wp_enqueue_script( 'xv-theme', get_theme_file_uri( 'assets/js/theme.js' ), array(), xv_asset_ver( 'assets/js/theme.js' ), true );
	wp_enqueue_script( 'xv-dragons-ui', get_theme_file_uri( 'assets/js/dragons-ui.js' ), array( 'xv-theme' ), xv_asset_ver( 'assets/js/dragons-ui.js' ), true );

	wp_localize_script( 'xv-theme', 'xvData', array(
		'root'    => esc_url_raw( rest_url( 'xin/v2/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'home'    => home_url( '/' ),
		'loggedIn' => is_user_logged_in(),
		'login'    => esc_url_raw( xv_login_url() ),
		'i18n'    => array(
			'searchHint' => __( 'Название, автор, тег…', 'xin-v2' ),
			'nothing'    => __( 'Ничего не нашлось', 'xin-v2' ),
			'saved'      => __( 'Сохранено', 'xin-v2' ),
			'error'      => __( 'Не получилось. Попробуйте ещё раз.', 'xin-v2' ),
			'more'       => __( 'Читать полностью ↓', 'xin-v2' ),
			'less'       => __( 'Свернуть ↑', 'xin-v2' ),
			'faved'      => __( 'В избранном', 'xin-v2' ),
			'unfaved'    => __( 'В избранное', 'xin-v2' ),
			'chapter'    => __( 'Глава %s', 'xin-v2' ),
			'sayMore'    => __( 'Напишите пару слов.', 'xin-v2' ),
			'sending'    => __( 'Отправляем…', 'xin-v2' ),
			'held'       => __( 'Отправлено на проверку — появится после одобрения.', 'xin-v2' ),
			'posted'     => __( 'Опубликовано.', 'xin-v2' ),
			'dropAsk'    => __( 'Удалить комментарий?', 'xin-v2' ),
			'quiet'      => __( 'Тихо. Скажите первое слово.', 'xin-v2' ),
			'dirDown'    => __( 'Сверху вниз', 'xin-v2' ),
			'dirRtl'     => __( 'Справа налево', 'xin-v2' ),
			'dirLtr'     => __( 'Слева направо', 'xin-v2' ),
		),
	) );

	if ( is_singular( 'chapter' ) && xv_chapter_is_comic( get_queried_object_id() ) ) {
		wp_enqueue_script( 'xv-comic', get_theme_file_uri( 'assets/js/comic-reader.js' ), array(), xv_asset_ver( 'assets/js/comic-reader.js' ), true );
	} elseif ( is_singular( 'chapter' ) ) {
		wp_enqueue_script( 'xv-reader', get_theme_file_uri( 'assets/js/reader.js' ), array( 'xv-theme' ), xv_asset_ver( 'assets/js/reader.js' ), true );
		wp_enqueue_script( 'xv-pia-reader', get_theme_file_uri( 'assets/js/pia-reader.js' ), array( 'xv-reader' ), xv_asset_ver( 'assets/js/pia-reader.js' ), true );

		if ( xv_opt( 'glossary', true ) ) {
			wp_enqueue_script( 'xv-glossary', get_theme_file_uri( 'assets/js/glossary.js' ), array(), xv_asset_ver( 'assets/js/glossary.js' ), true );
		}
		// Через inline-скрипт, а не wp_localize_script: тот приводит все
		// значения к строкам, и «следующей главы нет» приезжает как '0' —
		// строка, которая в JS истинна.
		wp_add_inline_script(
			'xv-reader',
			'window.xvReader = ' . wp_json_encode( xv_reader_data() ) . ';',
			'before'
		);
	}

	if ( xv_is_author_area() ) {
		wp_enqueue_script( 'xv-author', get_theme_file_uri( 'assets/js/author.js' ), array( 'xv-theme' ), xv_asset_ver( 'assets/js/author.js' ), true );
	}

	if ( is_singular() && ( xv_discussion_open( get_queried_object_id() ) || get_comments_number() ) ) {
		wp_enqueue_script( 'xv-comments', get_theme_file_uri( 'assets/js/comments.js' ), array( 'xv-theme' ), xv_asset_ver( 'assets/js/comments.js' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'xv_assets' );

/**
 * Тема (светлая/тёмная) применяется до первой отрисовки — иначе на тёмной
 * теме мигает белым.
 */
/**
 * Ширина контентной колонки — из настроек. Одна переменная, от неё зависят
 * шапка, секции, читалка и подвал: края всегда совпадают.
 */
function xv_width_var() {
	$width = (int) xv_opt( 'site_width', 1200 );
	$value = $width > 0 ? $width . 'px' : 'none';

	printf( '<style id="xv-width">:root{--site-max:%s;}</style>', esc_html( $value ) );
}
add_action( 'wp_head', 'xv_width_var', 2 );

function xv_no_flash() {
	?>
	<script>
	(function () {
		try {
			var t = localStorage.getItem('xv-theme');
			if (t !== 'dark' && t !== 'light') {
				t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
			}
			document.documentElement.setAttribute('data-theme', t);
			var p = localStorage.getItem('xv-paper');
			if (p) { document.documentElement.setAttribute('data-paper', p); }
			var f = localStorage.getItem('xv-font-size');
			if (f) { document.documentElement.style.setProperty('--reader-size', f + 'px'); }
			var w = localStorage.getItem('xv-reader-width');
			if (w) { document.documentElement.setAttribute('data-reader-width', w); }
		} catch (e) {}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'xv_no_flash', 1 );

function xv_body_class( $classes ) {
	if ( is_singular( 'chapter' ) ) {
		$classes[] = xv_chapter_is_comic( get_queried_object_id() ) ? 'xv-comic-reading' : 'xv-reading';
	}

	if ( xv_is_author_area() ) {
		$classes[] = 'xv-authoring';
	}

	$classes[] = 'xv-page';

	return $classes;
}
add_filter( 'body_class', 'xv_body_class' );

function xv_excerpt_length() {
	return 32;
}
add_filter( 'excerpt_length', 'xv_excerpt_length' );

function xv_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'xv_excerpt_more' );

add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/**
 * Убираем то, что тема не использует: эмодзи-скрипт и блочные стили ядра
 * на фронте (свои компоненты полностью перекрывают их).
 */
function xv_dequeue_bloat() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'xv_dequeue_bloat' );

/**
 * Аватар.
 *
 * Инициал лежит в основе всегда, картинка — слоем поверх и убирает себя,
 * если не загрузилась: иначе на сайте без доступа к граватару висят рамки
 * битых изображений.
 */
function xv_avatar( $user_id, $size = 40, $class = '' ) {
	$user = get_userdata( $user_id );
	$name = $user ? $user->display_name : '?';
	// default=blank — прозрачная картинка вместо чужого «силуэта»: у кого
	// аватар есть, тот его и увидит, у остальных остаётся инициал.
	$url = $user && get_option( 'show_avatars' )
		? get_avatar_url( $user_id, array( 'size' => $size * 2, 'default' => 'blank' ) )
		: '';

	ob_start();
	?>
	<span class="xv-avatar <?php echo esc_attr( $class ); ?>" style="width:<?php echo (int) $size; ?>px;height:<?php echo (int) $size; ?>px;font-size:<?php echo (int) round( $size * 0.42 ); ?>px;">
		<?php echo esc_html( mb_strtoupper( mb_substr( $name, 0, 1 ) ) ); ?>
		<?php if ( $url ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy" width="<?php echo (int) $size; ?>" height="<?php echo (int) $size; ?>" onerror="this.remove()">
		<?php endif; ?>
	</span>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Счётчик просмотров. Считаем один раз на сессию посетителя, иначе
 * перезагрузка страницы накручивает статистику.
 */
function xv_get_views( $post_id ) {
	return (int) get_post_meta( $post_id, '_xin_views', true );
}

function xv_bump_view( $post_id ) {
	if ( is_user_logged_in() && current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$cookie = 'xin_seen_' . $post_id;

	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return;
	}

	// Не «прочитать, сложить, записать»: под нагрузкой два посетителя в одну
	// секунду так засчитываются как один. Копим и прибавляем в базе.
	xv_queue_view( $post_id );

	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	}

	if ( 'chapter' === get_post_type( $post_id ) ) {
		$novel = (int) get_post_meta( $post_id, '_xin_novel', true );

		if ( $novel ) {
			xv_queue_view( $novel );
		}
	}
}

function xv_count_view() {
	if ( is_singular( array( 'novel', 'chapter' ) ) ) {
		xv_bump_view( get_queried_object_id() );
	}
}
add_action( 'template_redirect', 'xv_count_view' );
