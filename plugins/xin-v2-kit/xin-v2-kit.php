<?php
/**
 * Plugin Name:       XIN-V2 Kit — данные и починка
 * Plugin URI:        https://github.com/rurumiru/wordpress-novel-themes
 * Description:       Держит типы записей, таксономии и мета тайтлов и глав вне темы, следит за счётчиками и чинит расхождения: главы без тайтла, неверное число глав, пропавшие номера, отсутствующие страницы шаблонов и устаревшие правила ссылок.
 * Version:           2.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            XI Community
 * Author URI:        https://xi.community/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xin-v2-kit
 * Domain Path:       /languages
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XIN_V2_KIT', '2.0.0' );
define( 'XVK_DIR', plugin_dir_path( __FILE__ ) );
define( 'XVK_URL', plugin_dir_url( __FILE__ ) );

require_once XVK_DIR . 'includes/content.php';
require_once XVK_DIR . 'includes/counters.php';
require_once XVK_DIR . 'includes/doctor.php';
require_once XVK_DIR . 'includes/covers.php';
require_once XVK_DIR . 'includes/screen.php';

/**
 * Право на починку. Правки идут по всему сайту, поэтому это тот же уровень,
 * что и настройки.
 */
function xvk_cap() {
	return apply_filters( 'xvk_capability', 'manage_options' );
}

function xvk_activate() {
	xvk_register_content();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'xvk_activate' );

function xvk_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'xvk_deactivate' );

function xvk_textdomain() {
	load_plugin_textdomain( 'xin-v2-kit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'xvk_textdomain' );
