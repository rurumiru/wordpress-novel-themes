<?php
/**
 * Язык интерфейса.
 *
 * Читатель выбирает язык сам и остаётся на нём: выбор живёт в куке, а не в
 * адресе, поэтому ссылками делятся обычными, без хвоста `?lang=`.
 *
 * Переключается только интерфейс. Тексты глав остаются такими, какими их
 * написали, — переводить произведение на лету тема не берётся.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_LANG_COOKIE = 'xv_lang';

/**
 * Языки, на которые собраны файлы перевода темы.
 */
function xv_languages() {
	$langs = array(
		'ru' => array( 'label' => 'RU', 'locale' => 'ru_RU', 'name' => 'Русский' ),
		'en' => array( 'label' => 'EN', 'locale' => 'en_US', 'name' => 'English' ),
		'pt' => array( 'label' => 'PT', 'locale' => 'pt_BR', 'name' => 'Português (BR)' ),
	);

	return apply_filters( 'xv_languages', $langs );
}

function xv_current_lang() {
	static $lang = null;

	if ( null !== $lang ) {
		return $lang;
	}

	$langs = xv_languages();

	// Явный выбор из адреса важнее всего: по нему приходят со ссылки.
	if ( isset( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( isset( $langs[ $key ] ) ) {
			return $lang = $key;
		}
	}

	if ( isset( $_COOKIE[ XV_LANG_COOKIE ] ) ) {
		$key = sanitize_key( wp_unslash( $_COOKIE[ XV_LANG_COOKIE ] ) );

		if ( isset( $langs[ $key ] ) ) {
			return $lang = $key;
		}
	}

	// Иначе — язык самого сайта.
	$site = (string) get_option( 'WPLANG', 'ru_RU' );

	foreach ( $langs as $key => $item ) {
		if ( 0 === strpos( $site, substr( $item['locale'], 0, 2 ) ) ) {
			return $lang = $key;
		}
	}

	return $lang = 'ru';
}

function xv_lang_cookie() {
	if ( ! isset( $_GET['lang'] ) || headers_sent() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$key = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! isset( xv_languages()[ $key ] ) ) {
		return;
	}

	setcookie( XV_LANG_COOKIE, $key, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
}
add_action( 'init', 'xv_lang_cookie', 1 );

/**
 * Админку не трогаем: там язык выбирает сам WordPress по учётной записи.
 */
function xv_filter_locale( $locale ) {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return $locale;
	}

	$langs = xv_languages();
	$key   = xv_current_lang();

	return isset( $langs[ $key ] ) ? $langs[ $key ]['locale'] : $locale;
}
add_filter( 'locale', 'xv_filter_locale' );

/**
 * Переключатель. Ссылки ведут на ту же страницу — читатель не теряет место.
 */
function xv_lang_switcher() {
	$langs = xv_languages();

	if ( count( $langs ) < 2 ) {
		return;
	}

	$current = xv_current_lang();

	echo '<div class="xv-lang" role="group" aria-label="' . esc_attr__( 'Язык интерфейса', 'xin-v2' ) . '">';

	foreach ( $langs as $key => $lang ) {
		printf(
			'<a class="xv-lang-btn%1$s" href="%2$s" hreflang="%3$s" title="%4$s"%5$s>%6$s</a>',
			$current === $key ? ' is-on' : '',
			esc_url( add_query_arg( 'lang', $key ) ),
			esc_attr( str_replace( '_', '-', $lang['locale'] ) ),
			esc_attr( $lang['name'] ),
			$current === $key ? ' aria-current="true"' : '',
			esc_html( $lang['label'] )
		);
	}

	echo '</div>';
}
