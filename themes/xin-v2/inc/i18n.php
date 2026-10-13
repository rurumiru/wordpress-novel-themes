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

/** Translate standard navigation and taxonomy labels stored in WordPress. */
function xv_ui_label($label) {
    $labels = [
        'Главная'=>__('Главная','xin-v2'), 'Каталог'=>__('Каталог','xin-v2'),
        'Обновления'=>__('Обновления','xin-v2'), 'Рейтинг'=>__('Рейтинг','xin-v2'),
        'Закладки'=>__('Закладки','xin-v2'), 'Блог'=>__('Блог','xin-v2'),
        'Сообщество'=>__('Сообщество','xin-v2'), 'Ресурсы'=>__('Ресурсы','xin-v2'),
        'Студия'=>__('Студия','xin-v2'), 'Кабинет автора'=>__('Творческая студия','xin-v2'),
        'Помощь'=>__('Помощь','xin-v2'), 'Правила'=>__('Правила','xin-v2'),
        'Контакты'=>__('Контакты','xin-v2'), 'Поддержать'=>__('Поддержать','xin-v2'),
        'Стать автором'=>__('Стать автором','xin-v2'), 'Вход'=>__('Вход','xin-v2'),
        'Выходит'=>__('Выходит','xin-v2'), 'Завершён'=>__('Завершён','xin-v2'),
        'Заморожен'=>__('Заморожен','xin-v2'), 'Фэнтези'=>__('Фэнтези','xin-v2'),
        'Драма'=>__('Драма','xin-v2'), 'Романтика'=>__('Романтика','xin-v2'),
        'Боевик'=>__('Боевик','xin-v2'), 'Приключения'=>__('Приключения','xin-v2'),
        'Фантастика'=>__('Фантастика','xin-v2'), 'Детектив'=>__('Детектив','xin-v2'),
        'Триллер'=>__('Триллер','xin-v2'), 'Ужасы'=>__('Ужасы','xin-v2'),
        'Повседневность'=>__('Повседневность','xin-v2'), 'Боевые искусства'=>__('Боевые искусства','xin-v2'),
        'Сянься'=>__('Сянься','xin-v2'), 'Сюаньхуань'=>__('Сюаньхуань','xin-v2'),
        'Академия'=>__('Академия','xin-v2'), 'Исекай'=>__('Исекай','xin-v2'),
        'Мистика'=>__('Мистика','xin-v2'), 'Научная фантастика'=>__('Научная фантастика','xin-v2'),
        'Психология'=>__('Психология','xin-v2'),
    ];
    return $labels[$label] ?? $label;
}
add_filter('nav_menu_item_title',function($title){return is_admin() ? $title : xv_ui_label($title);});
add_filter('document_title_parts',function($parts){if(!is_admin() && isset($parts['title'])) $parts['title']=xv_ui_label($parts['title']);return $parts;});
add_filter('the_title',function($title,$id){return !is_admin() && get_post_type($id)==='page' ? xv_ui_label($title) : $title;},20,2);
function xv_localize_ui_term($term) {
    if(is_admin() || !($term instanceof WP_Term) || !in_array($term->taxonomy,['genre','novel_status'],true)) return $term;
    $translated=clone $term; $translated->name=xv_ui_label($term->name); return $translated;
}
add_filter('get_term','xv_localize_ui_term');
add_filter('get_terms',function($terms){return is_array($terms)?array_map('xv_localize_ui_term',$terms):$terms;});
add_filter('get_the_terms',function($terms){return is_array($terms)?array_map('xv_localize_ui_term',$terms):$terms;});

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
