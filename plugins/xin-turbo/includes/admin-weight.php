<?php
/**
 * Вес административной части.
 *
 * Автор, который пишет главу час, всё это время шлёт на сервер запрос
 * каждые пятнадцать секунд — так работает heartbeat. Десять таких авторов
 * дают больше запросов, чем сотня читателей.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Придерживаем heartbeat.
 *
 * В редакторе он нужен — на нём держится блокировка записи и автосохранение,
 * поэтому там ставим минуту, а не выключаем. На остальных экранах и на
 * витрине он не нужен вовсе.
 */
function xt_slow_heartbeat( $settings ) {
	if ( ! xt_opt( 'slow_heartbeat' ) ) {
		return $settings;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( $screen && 'post' === $screen->base ) {
		$settings['interval'] = 60;

		return $settings;
	}

	$settings['interval'] = 120;

	return $settings;
}
add_filter( 'heartbeat_settings', 'xt_slow_heartbeat' );

function xt_heartbeat_off_front() {
	if ( ! xt_opt( 'slow_heartbeat' ) || is_admin() ) {
		return;
	}

	wp_deregister_script( 'heartbeat' );
}
add_action( 'init', 'xt_heartbeat_off_front', 1 );

/**
 * Автосохранение. Минута по умолчанию — это минута записей в базу на
 * каждого пишущего автора.
 */
function xt_autosave_interval() {
	$seconds = (int) xt_opt( 'autosave', 120 );

	if ( $seconds > 0 && ! defined( 'AUTOSAVE_INTERVAL' ) ) {
		define( 'AUTOSAVE_INTERVAL', max( 30, $seconds ) );
	}
}
add_action( 'init', 'xt_autosave_interval', 1 );

/**
 * Ревизии.
 *
 * У главы, которую правили полгода, их набирается сотня. На библиотеке в
 * тысячи глав это миллионы строк в таблице записей — и каждый запрос по ней
 * становится медленнее.
 */
function xt_limit_revisions( $limit, $post ) {
	$keep = (int) xt_opt( 'revisions', 10 );

	if ( $keep < 1 ) {
		return $limit;
	}

	return $keep;
}
add_filter( 'wp_revisions_to_keep', 'xt_limit_revisions', 20, 2 );

/**
 * Виджеты чужих новостей в консоли ходят в сеть при каждом заходе.
 */
function xt_trim_dashboard() {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
}
add_action( 'wp_dashboard_setup', 'xt_trim_dashboard', 20 );
