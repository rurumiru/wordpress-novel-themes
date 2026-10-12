<?php
/**
 * Что убрать со страницы и как отдать остальное.
 *
 * Свежая установка WordPress тащит в каждую страницу вещи, которыми почти
 * никто не пользуется: скрипт эмодзи, слой совместимости jQuery, ссылки на
 * oEmbed и RSD. Каждая из них — лишний файл, лишний запрос и лишние
 * миллисекунды у читателя на телефоне.
 *
 * Ничего не выключается молча: каждый пункт — переключатель на странице
 * настроек, и у каждого написано, что именно перестанет работать.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Шапка документа
 * ---------------------------------------------------------------------- */

function xt_trim_head() {
	if ( ! xt_opt( 'trim_head' ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'xt_trim_head' );

/**
 * Эмодзи.
 *
 * Скрипт подменял эмодзи картинками в браузерах, которые их не рисовали. Те
 * браузеры давно ушли, а скрипт в каждой странице остался.
 */
function xt_drop_emoji() {
	if ( ! xt_opt( 'drop_emoji' ) ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return array_diff( (array) $plugins, array( 'wpemoji' ) );
	} );

	// Иначе браузер всё равно пойдёт разогревать соединение с s.w.org.
	add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
		if ( 'dns-prefetch' !== $relation ) {
			return $urls;
		}

		return array_filter( (array) $urls, function ( $url ) {
			return false === strpos( (string) $url, 's.w.org' );
		} );
	}, 10, 2 );
}
add_action( 'init', 'xt_drop_emoji' );

/**
 * jQuery Migrate нужен темам и плагинам, написанным под jQuery 1.x. На
 * современной сборке это мёртвый груз, но выключать его вслепую нельзя —
 * поэтому по умолчанию включено, а сломаться может только старый плагин.
 */
function xt_drop_migrate( $scripts ) {
	if ( ! xt_opt( 'drop_migrate' ) || is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		array( 'jquery-migrate' )
	);
}
add_action( 'wp_default_scripts', 'xt_drop_migrate' );

/**
 * Стили блочного редактора на витрине.
 *
 * Сайту, который не пользуется блоками, они дают лишние 60–100 КБ CSS. Тем,
 * кто пользуется, — наоборот, ломают вид, поэтому по умолчанию выключатель
 * стоит в «оставить».
 */
function xt_drop_block_css() {
	if ( ! xt_opt( 'drop_blocks' ) || is_admin() ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'xt_drop_block_css', 100 );

/**
 * Скрипты, не участвующие в первой отрисовке, не должны её задерживать.
 *
 * Только те, что стоят в подвале: скрипт из шапки там оказался намеренно —
 * скорее всего, от него зависит разметка.
 */
function xt_defer_scripts( $tag, $handle ) {
	if ( ! xt_opt( 'defer_js' ) || is_admin() ) {
		return $tag;
	}

	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}

	// jQuery часто нужен встроенным скриптам, которые идут сразу за ним.
	$keep = apply_filters( 'xt_never_defer', array( 'jquery-core', 'jquery-migrate', 'jquery' ) );

	if ( in_array( $handle, (array) $keep, true ) ) {
		return $tag;
	}

	if ( ! wp_script_is( $handle, 'done' ) && ! wp_scripts()->get_data( $handle, 'group' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'xt_defer_scripts', 20, 2 );

/* -------------------------------------------------------------------------
 * Пингбэки и XML-RPC
 * ---------------------------------------------------------------------- */

/**
 * XML-RPC сегодня используют в основном для перебора паролей: один запрос
 * позволяет проверить сотни пар логин-пароль. Приложениям давно хватает
 * REST.
 */
function xt_close_xmlrpc() {
	if ( xt_opt( 'no_xmlrpc' ) ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );

		add_filter( 'wp_headers', function ( $headers ) {
			unset( $headers['X-Pingback'] );

			return $headers;
		} );
	}

	if ( xt_opt( 'no_pingback' ) ) {
		add_filter( 'pings_open', '__return_false', 20 );
		add_filter( 'pre_option_default_ping_status', function () { return 'closed'; } );
		add_filter( 'pre_option_default_pingback_flag', '__return_zero' );

		// Самопингование — запрос сайта к самому себе на каждую публикацию.
		add_action( 'pre_ping', function ( &$links ) {
			$home = home_url();

			foreach ( $links as $i => $link ) {
				if ( 0 === strpos( (string) $link, $home ) ) {
					unset( $links[ $i ] );
				}
			}
		} );
	}
}
add_action( 'init', 'xt_close_xmlrpc' );

/* -------------------------------------------------------------------------
 * Заголовки кэширования
 * ---------------------------------------------------------------------- */

/**
 * Подсказка браузеру и промежуточным кэшам.
 *
 * Только для гостей и только на чтение: у вошедшего страница своя, и
 * закэшировать её на стороне провайдера было бы утечкой.
 */
function xt_cache_headers( $headers ) {
	if ( is_admin() || is_user_logged_in() || ! xt_opt( 'page_cache' ) ) {
		return $headers;
	}

	if ( is_singular() || is_archive() || is_front_page() || is_home() ) {
		$life = max( 60, (int) xt_opt( 'cache_life', 3600 ) );

		$headers['Cache-Control'] = 'public, max-age=0, s-maxage=' . $life . ', stale-while-revalidate=60';
	}

	return $headers;
}
add_filter( 'wp_headers', 'xt_cache_headers' );
