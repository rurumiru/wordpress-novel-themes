<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_LANG_COOKIE = 'xin_lang';

function xin_languages() {
	return array(
		'ru' => array( 'label' => 'RU', 'locale' => 'ru_RU', 'name' => 'Русский' ),
		'en' => array( 'label' => 'EN', 'locale' => 'en_US', 'name' => 'English' ),
	);
}

function xin_current_lang() {
	$langs = xin_languages();

	if ( isset( $_GET['lang'] ) ) {
		$key = sanitize_key( wp_unslash( $_GET['lang'] ) );
		if ( isset( $langs[ $key ] ) ) {
			return $key;
		}
	}

	if ( isset( $_COOKIE[ XIN_LANG_COOKIE ] ) ) {
		$key = sanitize_key( wp_unslash( $_COOKIE[ XIN_LANG_COOKIE ] ) );
		if ( isset( $langs[ $key ] ) ) {
			return $key;
		}
	}

	$default = get_theme_mod( 'xin_default_lang', '' );
	if ( isset( $langs[ $default ] ) ) {
		return $default;
	}

	return 0 === strpos( (string) get_option( 'WPLANG', 'ru_RU' ), 'en' ) ? 'en' : 'ru';
}

function xin_lang_cookie() {
	if ( ! isset( $_GET['lang'] ) || headers_sent() ) {
		return;
	}
	$key = sanitize_key( wp_unslash( $_GET['lang'] ) );
	if ( ! isset( xin_languages()[ $key ] ) ) {
		return;
	}
	setcookie( XIN_LANG_COOKIE, $key, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
}
add_action( 'init', 'xin_lang_cookie', 1 );

function xin_filter_locale( $locale ) {
	if ( is_admin() || wp_doing_ajax() ) {
		return $locale;
	}
	$langs = xin_languages();
	$key   = xin_current_lang();
	return isset( $langs[ $key ] ) ? $langs[ $key ]['locale'] : $locale;
}
add_filter( 'locale', 'xin_filter_locale' );

function xin_lang_switcher() {
	$langs   = xin_languages();
	$current = xin_current_lang();

	echo '<div class="xin-lang" role="group" aria-label="' . esc_attr__( 'Язык интерфейса', 'xi-novels' ) . '">';
	foreach ( $langs as $key => $lang ) {
		printf(
			'<a class="xin-lang__btn%s" href="%s" hreflang="%s" title="%s">%s</a>',
			$current === $key ? ' is-active' : '',
			esc_url( add_query_arg( 'lang', $key ) ),
			esc_attr( str_replace( '_', '-', $lang['locale'] ) ),
			esc_attr( $lang['name'] ),
			esc_html( $lang['label'] )
		);
	}
	echo '</div>';
}
