<?php
/**
 * Plugin Name: XIN-Com — управление тайтлами
 * Plugin URI: https://github.com/rurumiru/wordpress-novel-themes
 * Description: Массовое редактирование и удаление тайтлов: поиск и фильтры, жанры и метки, автор и команда, обложки, PLUS и 18+, выгрузка в CSV. Работает с типами записей темы XIN-Com.
 * Version: 1.1.1
 * Requires PHP: 7.4
 * Author: XI Community
 * Author URI: https://xi.community/
 * License: GPL-2.0-or-later
 * Text Domain: xi-novel-manager
 * Domain Path: /languages
 *
 * @package XI_Novel_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XNM_VERSION', '1.1.1' );
define( 'XNM_DIR', plugin_dir_path( __FILE__ ) );
define( 'XNM_URL', plugin_dir_url( __FILE__ ) );

require_once XNM_DIR . 'includes/query.php';
require_once XNM_DIR . 'includes/actions.php';
require_once XNM_DIR . 'includes/screen.php';

/**
 * The capability that opens the screen. Editing other people's novels is the
 * lowest bar that makes a bulk editor meaningful.
 */
function xnm_capability() {
	return apply_filters( 'xnm_capability', 'edit_others_posts' );
}

function xnm_menu() {
	$hook = add_submenu_page(
		'edit.php?post_type=novel',
		__( 'Массовое управление', 'xi-novel-manager' ),
		__( 'Массовое управление', 'xi-novel-manager' ),
		xnm_capability(),
		'xi-novel-manager',
		'xnm_render_screen'
	);

	if ( $hook ) {
		add_action( 'load-' . $hook, 'xnm_handle_request' );
	}
}
add_action( 'admin_menu', 'xnm_menu' );

/**
 * The novel post type lives in the theme. Without it the screen has nothing to
 * manage, so say so plainly instead of showing an empty table.
 */
function xnm_theme_missing() {
	return ! post_type_exists( 'novel' );
}

function xnm_assets( $hook ) {
	if ( 'novel_page_xi-novel-manager' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'xnm', XNM_URL . 'assets/manager.css', array(), XNM_VERSION );
	wp_enqueue_script( 'xnm', XNM_URL . 'assets/manager.js', array(), XNM_VERSION, true );
	wp_enqueue_media();

	wp_localize_script( 'xnm', 'XNM', array(
		'confirmTrash'   => __( 'Переместить выбранные тайтлы в корзину?', 'xi-novel-manager' ),
		'confirmDelete'  => __( 'Удалить выбранные тайтлы навсегда? Вместе с ними удалятся их главы. Это действие необратимо.', 'xi-novel-manager' ),
		'confirmGeneric' => __( 'Применить действие к выбранным тайтлам?', 'xi-novel-manager' ),
		'pickCover'      => __( 'Выберите обложку', 'xi-novel-manager' ),
		'useCover'       => __( 'Поставить обложкой', 'xi-novel-manager' ),
		'nothingPicked'  => __( 'Сначала отметьте хотя бы один тайтл.', 'xi-novel-manager' ),
		'noAction'       => __( 'Выберите действие.', 'xi-novel-manager' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'xnm_assets' );

function xnm_load_textdomain() {
	load_plugin_textdomain( 'xi-novel-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'xnm_load_textdomain' );

/**
 * Link straight to the screen from the plugin list.
 */
function xnm_action_links( $links ) {
	$links[] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'edit.php?post_type=novel&page=xi-novel-manager' ) ),
		esc_html__( 'Открыть', 'xi-novel-manager' )
	);
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'xnm_action_links' );
