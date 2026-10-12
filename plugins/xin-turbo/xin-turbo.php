<?php
/**
 * Plugin Name:       XIN Turbo — скорость WordPress
 * Plugin URI:        https://github.com/rurumiru/wordpress-novel-themes
 * Description:       Кэш готовых страниц для гостей, чистка шапки и очереди файлов, ленивые картинки, придержанный heartbeat и уборка базы по расписанию. Работает с любой темой, знает про большие библиотеки текстов и ничего не ломает на слабом хостинге.
 * Version:           2.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            XI Community
 * Author URI:        https://xi.community/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xin-turbo
 * Domain Path:       /languages
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XT_VERSION', '2.0.0' );
define( 'XT_DIR', plugin_dir_path( __FILE__ ) );
define( 'XT_URL', plugin_dir_url( __FILE__ ) );
define( 'XT_FILE', __FILE__ );

require_once XT_DIR . 'includes/settings.php';
require_once XT_DIR . 'includes/cache.php';
require_once XT_DIR . 'includes/front.php';
require_once XT_DIR . 'includes/media.php';
require_once XT_DIR . 'includes/admin-weight.php';
require_once XT_DIR . 'includes/database.php';
require_once XT_DIR . 'includes/screen.php';

function xt_textdomain() {
	load_plugin_textdomain( 'xin-turbo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'xt_textdomain' );

/**
 * При включении ничего не трогаем молча: кэш страниц ставит файл-перехватчик
 * в wp-content и меняет wp-config, а такие вещи владелец сайта должен
 * включить сам, на своей странице настроек.
 */
function xt_activate() {
	add_option( 'xt_settings', xt_defaults() );

	if ( ! wp_next_scheduled( 'xt_nightly' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'xt_nightly' );
	}
}
register_activation_hook( __FILE__, 'xt_activate' );

function xt_deactivate() {
	wp_clear_scheduled_hook( 'xt_nightly' );

	// Перехватчик оставлять нельзя: без плагина он станет мёртвым файлом,
	// который WordPress всё равно подключает на каждом запросе.
	xt_remove_dropin();
	xt_cache_clear();
}
register_deactivation_hook( __FILE__, 'xt_deactivate' );
