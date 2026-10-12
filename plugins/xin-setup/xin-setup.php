<?php
/**
 * Plugin Name:       XIN Setup — настройка сайта под тему
 * Plugin URI:        https://github.com/rurumiru/wordpress-novel-themes
 * Description:       Готовит WordPress к работе с темой: заводит страницы на нужных шаблонах, меню, ссылки, жанры и статусы, приводит настройки чтения и обсуждений в порядок и подгоняет уже накопленные записи — главы без тайтла, пропавшие номера, обложки и счётчики. Каждое действие показывает, что именно изменит, до того как это сделать.
 * Version:           3.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            XI Community
 * Author URI:        https://xi.community/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xin-setup
 * Domain Path:       /languages
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XS_VERSION', '3.0.0' );
define( 'XS_DIR', plugin_dir_path( __FILE__ ) );
define( 'XS_URL', plugin_dir_url( __FILE__ ) );

require_once XS_DIR . 'includes/checks.php';
require_once XS_DIR . 'includes/pages.php';
require_once XS_DIR . 'includes/settings.php';
require_once XS_DIR . 'includes/content.php';
require_once XS_DIR . 'includes/screen.php';
require_once XS_DIR . 'includes/automatic.php';

function xs_textdomain() {
	load_plugin_textdomain( 'xin-setup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'xs_textdomain' );

/**
 * Право на настройку. Действия меняют страницы, меню и ссылки всего сайта —
 * это тот же уровень, что и «Настройки».
 */
function xs_cap() {
	return apply_filters( 'xs_capability', 'manage_options' );
}

/**
 * Тема, под которую настраиваем.
 *
 * Плагин не привязан к одной теме: он смотрит, какие шаблоны страниц она
 * объявляет, и заводит страницы под них. С чужой темой он просто ничего не
 * найдёт и честно об этом скажет.
 */
function xs_theme() {
	$theme = wp_get_theme();

	return array(
		'name'      => $theme->get( 'Name' ),
		'slug'      => $theme->get_stylesheet(),
		'templates' => (array) ($theme->get_post_templates()['page'] ?? array()),
		'ours'      => in_array( $theme->get_stylesheet(), array( 'xin-v2', 'xin-com' ), true ),
	);
}

/**
 * После включения ведём владельца на экран настройки: плагин бесполезен,
 * пока его не открыли.
 */
function xs_activate() {
	add_option( 'xs_seen', 0 );
	xs_schedule_setup();
}
register_activation_hook( __FILE__, 'xs_activate' );

function xs_first_run_notice() {
	if ( get_option( 'xs_seen' ) || ! current_user_can( xs_cap() ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( $screen && 'tools_page_xin-setup' === $screen->id ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'XIN Setup готов проверить, что на сайте настроено под тему, а что нет.', 'xin-setup' ),
		esc_url( admin_url( 'tools.php?page=xin-setup' ) ),
		esc_html__( 'Открыть проверку', 'xin-setup' )
	);
}
add_action( 'admin_notices', 'xs_first_run_notice' );
