<?php
/**
 * То, что держит площадку под нагрузкой.
 *
 * Площадка для чтения устроена неудобно для базы: почти каждый заход — это
 * чтение главы, то есть запись счётчика. На сотне посетителей это незаметно,
 * на миллионе запросов в сутки из этого получается постоянный поток
 * одиночных UPDATE по одной и той же строке.
 *
 * Здесь собрано то, что снимает эту нагрузку: просмотры копятся в памяти и
 * ложатся в базу пачкой, тяжёлые подборки живут в кэше, а списки карточек
 * поднимают мету одним запросом вместо запроса на карточку.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Просмотры пачкой
 * ---------------------------------------------------------------------- */

const XV_VIEW_BUFFER = 'xv_view_buffer';

/**
 * Копит просмотры и сбрасывает их в базу разом.
 *
 * Один UPDATE на посетителя превращается в один UPDATE на пачку: при
 * постоянном кэше объектов пачка живёт в нём и до базы доходит раз в
 * несколько минут. Потерять при перезапуске можно только хвост пачки —
 * счётчик просмотров не бухгалтерия, и такая цена приемлема.
 */
function xv_queue_view( $post_id ) {
	$buffer = wp_cache_get( XV_VIEW_BUFFER, 'xin-v2' );
	$buffer = is_array( $buffer ) ? $buffer : array();

	$post_id            = (int) $post_id;
	$buffer[ $post_id ] = isset( $buffer[ $post_id ] ) ? $buffer[ $post_id ] + 1 : 1;

	wp_cache_set( XV_VIEW_BUFFER, $buffer, 'xin-v2', 0 );

	// Без постоянного кэша объектов пачка не переживёт запрос, поэтому
	// сбрасываем сразу: это ровно прежнее поведение, не хуже.
	if ( ! wp_using_ext_object_cache() || count( $buffer ) >= 50 ) {
		xv_flush_views();
	}
}

/**
 * Кладёт накопленное в базу одним запросом на запись.
 */
function xv_flush_views() {
	global $wpdb;

	$buffer = wp_cache_get( XV_VIEW_BUFFER, 'xin-v2' );

	if ( ! is_array( $buffer ) || ! $buffer ) {
		return;
	}

	wp_cache_delete( XV_VIEW_BUFFER, 'xin-v2' );

	foreach ( $buffer as $post_id => $add ) {
		$post_id = (int) $post_id;
		$add     = (int) $add;

		if ( ! $post_id || $add < 1 ) {
			continue;
		}

		// Прибавляем в самой базе, а не «прочитать — сложить — записать»:
		// два посетителя в одну секунду иначе засчитываются как один.
		$rows = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + %d WHERE post_id = %d AND meta_key = '_xin_views'",
				$add,
				$post_id
			)
		);

		if ( ! $rows ) {
			add_post_meta( $post_id, '_xin_views', $add, true );
		}

		wp_cache_delete( $post_id, 'post_meta' );
	}
}
add_action( 'shutdown', 'xv_flush_views', 99 );

/* -------------------------------------------------------------------------
 * Подборки витрины
 * ---------------------------------------------------------------------- */

/**
 * Кэш подборки тайтлов.
 *
 * Витрину открывают чаще всего, а её подборки меняются раз в день. Держим
 * идентификаторы, а не записи: записи поднимутся из кэша объектов, а список
 * идентификаторов занимает десятки байт и переживает правку любого тайтла.
 */
function xv_cached_novel_ids( $type, $limit, $build ) {
	$key = 'xv_list_' . $type . '_' . (int) $limit;
	$hit = wp_cache_get( $key, 'xin-v2' );

	if ( is_array( $hit ) ) {
		return $hit;
	}

	$ids = call_user_func( $build );

	wp_cache_set( $key, $ids, 'xin-v2', 10 * MINUTE_IN_SECONDS );

	return $ids;
}

/**
 * Сбрасываем подборки, когда меняется содержимое: иначе новый тайтл ждёт
 * своей очереди до истечения кэша.
 */
function xv_forget_lists( $post_id = 0, $post = null ) {
	$post = $post instanceof WP_Post ? $post : ( $post_id ? get_post( $post_id ) : null );

	if ( $post && ! in_array( $post->post_type, array( 'novel', 'chapter' ), true ) ) {
		return;
	}

	wp_cache_set( 'xv_lists_epoch', microtime( true ), 'xin-v2' );

	delete_transient( 'xv_site_stats' );
}
add_action( 'save_post', 'xv_forget_lists', 30, 2 );
add_action( 'deleted_post', 'xv_forget_lists', 30, 2 );

/* -------------------------------------------------------------------------
 * Запросы
 * ---------------------------------------------------------------------- */

/**
 * Греет мету и миниатюры для списка карточек.
 *
 * Карточка тайтла читает пять мета-полей и обложку. Без прогрева это по
 * запросу на поле на карточку: двадцать четыре карточки каталога — под
 * полторы сотни запросов на страницу.
 */
function xv_prime( $posts ) {
	$ids = array();

	foreach ( (array) $posts as $post ) {
		$ids[] = $post instanceof WP_Post ? (int) $post->ID : (int) $post;
	}

	$ids = array_values( array_filter( $ids ) );

	if ( ! $ids ) {
		return;
	}

	update_meta_cache( 'post', $ids );
	update_object_term_cache( $ids, 'novel' );

	$thumbs = array();

	foreach ( $ids as $id ) {
		$thumb = (int) get_post_meta( $id, '_thumbnail_id', true );

		if ( $thumb ) {
			$thumbs[] = $thumb;
		}
	}

	if ( $thumbs ) {
		_prime_post_caches( $thumbs, false, true );
	}
}

/**
 * Главный запрос архива тоже греем — WordPress этого сам не делает для
 * произвольных типов записей с большим числом мета-полей.
 */
function xv_prime_main_query( $posts, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $posts ) {
		return $posts;
	}

	if ( $query->is_post_type_archive( array( 'novel', 'chapter' ) ) || $query->is_tax( array( 'genre', 'novel_tag', 'novel_status' ) ) ) {
		xv_prime( $posts );
	}

	return $posts;
}
add_filter( 'the_posts', 'xv_prime_main_query', 10, 2 );

/**
 * Поиск по витрине без подсчёта всех совпадений там, где счёт не показывается.
 */
function xv_lighten_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Лента и поиск по сайту не показывают общее число страниц — считать его
	// значит делать второй проход по таблице.
	if ( $query->is_feed() ) {
		$query->set( 'no_found_rows', true );
	}
}
add_action( 'pre_get_posts', 'xv_lighten_queries', 30 );

/* -------------------------------------------------------------------------
 * Отдача страницы
 * ---------------------------------------------------------------------- */

/**
 * Предзагрузка шрифта заголовков и интерфейса.
 *
 * Только два файла и только те подмножества, которыми написана страница:
 * остальные начертания браузер возьмёт, когда они действительно понадобятся.
 */
function xv_preload_fonts() {
	$cyrillic = 0 === strpos( get_locale(), 'ru' ) || 0 === strpos( get_locale(), 'uk' ) || 0 === strpos( get_locale(), 'be' );
	$subset   = $cyrillic ? 'cyrillic' : 'latin';

	$fonts = array(
		'manrope-' . $subset . '-normal.woff2',
		'playfair-display-' . $subset . '-normal.woff2',
	);

	foreach ( $fonts as $font ) {
		$path = get_theme_file_path( 'assets/fonts/' . $font );

		if ( ! file_exists( $path ) ) {
			continue;
		}

		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) )
		);
	}
}
add_action( 'wp_head', 'xv_preload_fonts', 3 );

/**
 * Скрипты темы не участвуют в первой отрисовке — пусть не задерживают её.
 */
function xv_defer_scripts( $tag, $handle ) {
	if ( is_admin() || 0 !== strpos( $handle, 'xv-' ) ) {
		return $tag;
	}

	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'xv_defer_scripts', 10, 2 );

/**
 * Ленивая отрисовка того, что ниже сгиба.
 *
 * content-visibility снимает с браузера раскладку секций, до которых
 * читатель ещё не долистал. На витрине с шестью подборками это заметно на
 * слабом телефоне.
 */
function xv_lazy_sections() {
	echo '<style id="xv-lazy">.xv-section + .xv-section{content-visibility:auto;contain-intrinsic-size:auto 420px;}</style>' . "\n";
}
add_action( 'wp_head', 'xv_lazy_sections', 4 );

/**
 * Ненужное на витрине.
 *
 * oEmbed-обнаружение и REST-ссылка в шапке нужны редким сайтам, а разметку и
 * запросы добавляют всем.
 */
function xv_trim_head() {
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'xv_trim_head' );

/**
 * Ревизий у главы может набраться сотня — на тайтле в тысячи глав это
 * миллионы строк в таблице записей.
 */
function xv_limit_revisions( $limit, $post ) {
	if ( $post instanceof WP_Post && in_array( $post->post_type, array( 'chapter', 'novel' ), true ) ) {
		return 8;
	}

	return $limit;
}
add_filter( 'wp_revisions_to_keep', 'xv_limit_revisions', 10, 2 );

/**
 * Поисковый запрос по главам ищет и в тексте: на большой библиотеке это
 * полный просмотр таблицы. Ограничиваем поиск заголовками, если владелец
 * площадки не попросил иначе.
 */
function xv_search_titles_only( $search, $query ) {
	global $wpdb;

	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || '' === $query->get( 's' ) ) {
		return $search;
	}

	if ( ! apply_filters( 'xv_search_titles_only', true ) ) {
		return $search;
	}

	$term = $query->get( 's' );
	$like = '%' . $wpdb->esc_like( $term ) . '%';

	return $wpdb->prepare( " AND ({$wpdb->posts}.post_title LIKE %s) ", $like );
}
add_filter( 'posts_search', 'xv_search_titles_only', 10, 2 );
