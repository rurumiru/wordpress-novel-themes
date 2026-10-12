<?php
/**
 * Настройки.
 *
 * Один массив в одной опции: каждый переключатель здесь — это отдельное
 * поведение, и разбрасывать их по отдельным строкам таблицы означает лишний
 * запрос на каждое.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xt_defaults() {
	return array(
		// Кэш готовых страниц для тех, кто не вошёл.
		'page_cache'    => false,
		'cache_life'    => 3600,
		'cache_mobile'  => true,

		// Чистка шапки и очереди файлов.
		'trim_head'     => true,
		'drop_emoji'    => true,
		'drop_migrate'  => true,
		'drop_blocks'   => false,
		'defer_js'      => true,

		// Картинки и встраивания.
		'lazy_media'    => true,
		'size_images'   => true,
		'lazy_iframes'  => true,

		// Административная часть.
		'slow_heartbeat' => true,
		'autosave'       => 120,
		'revisions'      => 10,
		'no_xmlrpc'      => true,
		'no_pingback'    => true,

		// Уборка базы по ночам.
		'clean_db'       => true,
		'keep_trash'     => 14,
	);
}

function xt_opt( $key, $fallback = null ) {
	static $opts = null;

	if ( null === $opts ) {
		$opts = wp_parse_args( (array) get_option( 'xt_settings', array() ), xt_defaults() );
	}

	if ( ! array_key_exists( $key, $opts ) ) {
		return $fallback;
	}

	return $opts[ $key ];
}

function xt_set( $values ) {
	$opts = wp_parse_args( (array) get_option( 'xt_settings', array() ), xt_defaults() );

	update_option( 'xt_settings', array_merge( $opts, $values ) );
}

/**
 * Право менять настройки скорости. Это уровень настроек всего сайта:
 * неверный кэш виден всем сразу.
 */
function xt_cap() {
	return apply_filters( 'xt_capability', 'manage_options' );
}
