<?php
/**
 * Plugin Name:       XI Studio — студия темы
 * Plugin URI:        https://github.com/rurumiru/wordpress-novel-themes
 * Description:       Экран настройки облика темы XIN-Com: цвет, форма, шрифты и читалка — с живым предпросмотром сайта, готовыми наборами и переносом настроек файлом.
 * Version:           0.4.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            XI Community
 * Author URI:        https://xi.community/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xi-studio
 * Domain Path:       /languages
 *
 * @package XI_Studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XIS_VERSION', '0.4.0' );
define( 'XIS_FILE', __FILE__ );
define( 'XIS_DIR', plugin_dir_path( __FILE__ ) );
define( 'XIS_URI', plugin_dir_url( __FILE__ ) );

require_once XIS_DIR . 'includes/setup.php';
require_once XIS_DIR . 'includes/setup-screen.php';

/**
 * Тема на месте? Без неё крутить нечего.
 *
 * @return bool
 */
function xis_theme_ready() {
	return function_exists( 'xin_skin_fields' ) && function_exists( 'xin_skin_css' );
}

/**
 * Метка версии файла, чтобы браузер не держал старое.
 *
 * @param string $file Путь внутри плагина.
 * @return string
 */
function xis_ver( $file ) {
	$path = XIS_DIR . ltrim( $file, '/' );
	$time = file_exists( $path ) ? filemtime( $path ) : 0;

	return $time ? XIS_VERSION . '.' . $time : XIS_VERSION;
}

/**
 * Пункт меню.
 */
function xis_menu() {
	add_menu_page(
		__( 'Студия темы', 'xi-studio' ),
		__( 'Студия темы', 'xi-studio' ),
		'edit_theme_options',
		'xi-studio',
		'xis_render',
		'dashicons-art',
		59
	);

	add_submenu_page(
		'xi-studio',
		__( 'Настройка сайта', 'xi-studio' ),
		__( 'Настройка сайта', 'xi-studio' ),
		'manage_options',
		'xi-studio-setup',
		'xis_setup_render'
	);
}
add_action( 'admin_menu', 'xis_menu' );

/**
 * Страницы, которые можно открыть в предпросмотре.
 *
 * @return array
 */
function xis_preview_pages() {
	$pages = array(
		array(
			'key'   => 'home',
			'label' => __( 'Главная', 'xi-studio' ),
			'url'   => home_url( '/' ),
		),
	);

	$catalog = get_post_type_archive_link( 'novel' );
	if ( $catalog ) {
		$pages[] = array(
			'key'   => 'catalog',
			'label' => __( 'Каталог', 'xi-studio' ),
			'url'   => $catalog,
		);
	}

	$novels = get_posts( array(
		'post_type'      => 'novel',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	) );

	if ( $novels ) {
		$pages[] = array(
			'key'   => 'novel',
			'label' => __( 'Тайтл', 'xi-studio' ),
			'url'   => get_permalink( $novels[0] ),
		);
	}

	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	) );

	if ( $chapters ) {
		$pages[] = array(
			'key'   => 'chapter',
			'label' => __( 'Глава', 'xi-studio' ),
			'url'   => get_permalink( $chapters[0] ),
		);
	}

	return $pages;
}

/**
 * Стили и скрипты только на своём экране.
 *
 * @param string $hook Текущий экран.
 */
function xis_assets( $hook ) {
	if ( 'toplevel_page_xi-studio' !== $hook || ! xis_theme_ready() ) {
		return;
	}

	wp_enqueue_style( 'xi-studio', XIS_URI . 'assets/studio.css', array(), xis_ver( 'assets/studio.css' ) );
	wp_enqueue_script( 'xi-studio', XIS_URI . 'assets/studio.js', array(), xis_ver( 'assets/studio.js' ), true );

	wp_localize_script( 'xi-studio', 'XIS', array(
		'restUrl' => esc_url_raw( rest_url( 'xin/v1/skin' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'fields'  => xin_skin_fields(),
		'groups'  => xin_skin_groups(),
		'values'  => xin_skin_values(),
		'presets' => xin_skin_presets(),
		'pages'   => xis_preview_pages(),
		'i18n'    => array(
			'saved'     => __( 'Сохранено', 'xi-studio' ),
			'failed'    => __( 'Не удалось сохранить', 'xi-studio' ),
			'dirty'     => __( 'Есть несохранённые изменения', 'xi-studio' ),
			'clean'     => __( 'Всё сохранено', 'xi-studio' ),
			'leave'     => __( 'Настройки не сохранены. Уйти со страницы?', 'xi-studio' ),
			'reset'     => __( 'Вернуть все значения по умолчанию?', 'xi-studio' ),
			'badFile'   => __( 'Файл не похож на настройки студии', 'xi-studio' ),
			'imported'  => __( 'Настройки из файла подставлены — проверьте и сохраните', 'xi-studio' ),
			'preset'    => __( 'Набор применён — проверьте и сохраните', 'xi-studio' ),
			'default'   => __( 'по умолчанию', 'xi-studio' ),
		),
	) );
}
add_action( 'admin_enqueue_scripts', 'xis_assets' );

/**
 * Экран студии.
 */
function xis_render() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-studio' ) );
	}

	if ( ! xis_theme_ready() ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Студия темы', 'xi-studio' ) . '</h1>';
		echo '<div class="notice notice-warning"><p>'
			. esc_html__( 'Студия настраивает тему XIN-Com — включите её в «Внешний вид → Темы», и экран заработает.', 'xi-studio' )
			. '</p></div></div>';
		return;
	}

	require XIS_DIR . 'includes/screen.php';
}

/**
 * Ссылка на студию в списке плагинов.
 *
 * @param array $links Ссылки.
 * @return array
 */
function xis_action_links( $links ) {
	array_unshift(
		$links,
		'<a href="' . esc_url( admin_url( 'admin.php?page=xi-studio' ) ) . '">' . esc_html__( 'Открыть студию', 'xi-studio' ) . '</a>'
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'xis_action_links' );

/**
 * Кнопка в панели администратора рядом с «Настроить».
 *
 * @param WP_Admin_Bar $bar Панель.
 */
function xis_admin_bar( $bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! xis_theme_ready() ) {
		return;
	}

	$bar->add_node( array(
		'id'    => 'xi-studio',
		'title' => __( 'Студия темы', 'xi-studio' ),
		'href'  => admin_url( 'admin.php?page=xi-studio' ),
	) );
}
add_action( 'admin_bar_menu', 'xis_admin_bar', 81 );

/**
 * Свой текстовый домен.
 */
function xis_load_textdomain() {
	load_plugin_textdomain( 'xi-studio', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'xis_load_textdomain' );
