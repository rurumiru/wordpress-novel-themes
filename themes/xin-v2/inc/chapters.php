<?php
/**
 * Порядок глав внутри тайтла.
 *
 * Тайтл на четыре тысячи глав — обычное дело для перевода, который идёт
 * годами. Поднимать ради оглавления четыре тысячи объектов записи нельзя:
 * это десятки мегабайт памяти и секунды ожидания на каждой странице.
 *
 * Поэтому порядок живёт отдельно от содержимого — одним массивом
 * идентификаторов в мете тайтла. Страница читает одну строку меты, берёт из
 * неё нужный кусок и поднимает только его. Индекс пересобирается сам, когда
 * глава появляется, меняет номер, статус или исчезает.
 *
 * Глава без номера из индекса не выпадает: она встаёт в конец по дате.
 * Раньше сортировка по `meta_key` добавляла INNER JOIN, и такая глава
 * пропадала и из оглавления, и из перехода «предыдущая/следующая».
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_INDEX_META = '_xin_chapter_index';
const XV_INDEX_ALL  = '_xin_chapter_index_all';

// Forms/importers set relationship and number after wp_insert_post fires save_post.
// Invalidate both public and studio indexes when those metadata values arrive.
function xv_index_on_metadata($meta_id,$post_id,$key,$value) {
	if(!in_array($key,array('_xin_novel','_xin_number'),true) || 'chapter'!==get_post_type($post_id))return;
	$post=get_post($post_id);
	xv_index_on_save($post_id,$post);
	if(function_exists('xv_sync_novel_counters'))xv_sync_novel_counters($post_id,$post);
}
add_action('added_post_meta','xv_index_on_metadata',30,4);
add_action('updated_post_meta','xv_index_on_metadata',30,4);
add_action('deleted_post_meta','xv_index_on_metadata',30,4);

/**
 * Видит ли текущий читатель черновики и отложенные главы тайтла.
 */
function xv_sees_drafts( $novel_id ) {
	return is_user_logged_in() && current_user_can( 'edit_post', $novel_id );
}

/**
 * Порядок глав тайтла.
 *
 * @param int  $novel_id Тайтл.
 * @param bool $all      Включать черновики, отложенные и ожидающие.
 * @return int[] Идентификаторы глав в порядке чтения.
 */
function xv_chapter_index( $novel_id, $all = false ) {
	$novel_id = (int) $novel_id;

	if ( ! $novel_id ) {
		return array();
	}

	$key   = $all ? XV_INDEX_ALL : XV_INDEX_META;
	$group = 'xin-v2-index';
	$hit   = wp_cache_get( $key . ':' . $novel_id, $group );

	if ( is_array( $hit ) ) {
		return $hit;
	}

	$index = get_post_meta( $novel_id, $key, true );

	if ( ! is_array( $index ) ) {
		$index = xv_chapter_index_build( $novel_id, $all );
	}

	$index = array_map( 'intval', $index );
	wp_cache_set( $key . ':' . $novel_id, $index, $group, HOUR_IN_SECONDS );

	return $index;
}

/**
 * Собирает порядок заново и кладёт в мету.
 *
 * Двумя запросами вместо одного: сначала пронумерованные главы по номеру,
 * затем безномерные по дате. Одним запросом это не делается — сортировка по
 * мете выбрасывает записи, у которых меты нет.
 */
function xv_chapter_index_build( $novel_id, $all = false ) {
	$novel_id = (int) $novel_id;
	$statuses = $all ? array( 'publish', 'draft', 'pending', 'future', 'private' ) : array( 'publish' );

	$base = array(
		'post_type'              => 'chapter',
		'post_status'            => $statuses,
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'suppress_filters'       => false,
	);

	$numbered = get_posts( array_merge( $base, array(
		'orderby'    => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
		'meta_key'   => '_xin_number',
		'meta_query' => array(
			array( 'key' => '_xin_novel', 'value' => $novel_id ),
		),
	) ) );

	$loose = get_posts( array_merge( $base, array(
		'orderby'    => 'date',
		'order'      => 'ASC',
		'meta_query' => array(
			'relation' => 'AND',
			array( 'key' => '_xin_novel', 'value' => $novel_id ),
			array( 'key' => '_xin_number', 'compare' => 'NOT EXISTS' ),
		),
	) ) );

	$index = array_values( array_unique( array_merge( array_map( 'intval', $numbered ), array_map( 'intval', $loose ) ) ) );

	update_post_meta( $novel_id, $all ? XV_INDEX_ALL : XV_INDEX_META, $index );

	if ( ! $all ) {
		update_post_meta( $novel_id, '_xin_chapter_count', count( $index ) );
	}

	return $index;
}

/**
 * Порядок для текущего читателя: редактор тайтла видит и неопубликованное.
 */
function xv_chapter_ids( $novel_id ) {
	return xv_chapter_index( $novel_id, xv_sees_drafts( $novel_id ) );
}

function xv_chapter_count( $novel_id ) {
	return count( xv_chapter_ids( $novel_id ) );
}

/**
 * Сбрасывает индекс. Пересоберётся при первом же обращении.
 */
function xv_forget_chapter_index( $novel_id ) {
	$novel_id = (int) $novel_id;

	if ( ! $novel_id ) {
		return;
	}

	delete_post_meta( $novel_id, XV_INDEX_META );
	delete_post_meta( $novel_id, XV_INDEX_ALL );

	wp_cache_delete( XV_INDEX_META . ':' . $novel_id, 'xin-v2-index' );
	wp_cache_delete( XV_INDEX_ALL . ':' . $novel_id, 'xin-v2-index' );
}

/**
 * Кусок оглавления. Поднимает ровно столько записей, сколько показывает.
 *
 * @param int $novel_id Тайтл.
 * @param int $offset   С какой позиции.
 * @param int $length   Сколько глав; -1 — до конца.
 * @return WP_Post[]
 */
function xv_chapters_slice( $novel_id, $offset = 0, $length = -1 ) {
	$ids = xv_chapter_ids( $novel_id );

	if ( ! $ids ) {
		return array();
	}

	$ids = $length < 0 ? array_slice( $ids, $offset ) : array_slice( $ids, $offset, $length );

	return $ids ? xv_posts_in_order( $ids ) : array();
}

/**
 * Записи в заданном порядке, одним запросом и с прогретым кэшем меты.
 */
function xv_posts_in_order( $ids ) {
	$ids = array_values( array_filter( array_map( 'intval', (array) $ids ) ) );

	if ( ! $ids ) {
		return array();
	}

	$posts = get_posts( array(
		'post_type'           => 'chapter',
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $ids ),
		'post_status'         => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );

	return $posts;
}

/**
 * Совместимость со старым вызовом: limit, order и смещение.
 */
function xv_novel_chapters( $novel_id, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'limit'  => -1,
		'order'  => 'ASC',
		'offset' => 0,
	) );

	$ids = xv_chapter_ids( $novel_id );

	if ( 'DESC' === strtoupper( $args['order'] ) ) {
		$ids = array_reverse( $ids );
	}

	$ids = (int) $args['limit'] < 0
		? array_slice( $ids, (int) $args['offset'] )
		: array_slice( $ids, (int) $args['offset'], (int) $args['limit'] );

	return xv_posts_in_order( $ids );
}

/**
 * Место главы в тайтле, считая с нуля. -1 — главы в индексе нет.
 */
function xv_chapter_position( $chapter_id, $novel_id = 0 ) {
	$novel_id = $novel_id ? (int) $novel_id : (int) get_post_meta( $chapter_id, '_xin_novel', true );

	if ( ! $novel_id ) {
		return -1;
	}

	$pos = array_search( (int) $chapter_id, xv_chapter_ids( $novel_id ), true );

	return false === $pos ? -1 : (int) $pos;
}

/**
 * Соседние главы. Поднимает две записи, а не весь тайтл.
 */
function xv_adjacent_chapters( $chapter_id ) {
	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );
	$empty    = array( 'prev' => null, 'next' => null, 'novel' => $novel_id, 'index' => -1, 'total' => 0 );

	if ( ! $novel_id ) {
		return $empty;
	}

	$ids   = xv_chapter_ids( $novel_id );
	$index = array_search( (int) $chapter_id, $ids, true );

	if ( false === $index ) {
		return array_merge( $empty, array( 'total' => count( $ids ) ) );
	}

	$want = array_values( array_filter( array(
		$index > 0 ? $ids[ $index - 1 ] : 0,
		isset( $ids[ $index + 1 ] ) ? $ids[ $index + 1 ] : 0,
	) ) );

	$near = array();

	foreach ( xv_posts_in_order( $want ) as $post ) {
		$near[ (int) $post->ID ] = $post;
	}

	return array(
		'prev'  => $index > 0 && isset( $near[ $ids[ $index - 1 ] ] ) ? $near[ $ids[ $index - 1 ] ] : null,
		'next'  => isset( $ids[ $index + 1 ], $near[ $ids[ $index + 1 ] ] ) ? $near[ $ids[ $index + 1 ] ] : null,
		'novel' => $novel_id,
		'index' => (int) $index,
		'total' => count( $ids ),
	);
}

/**
 * Окно оглавления вокруг текущей главы.
 *
 * Читалке нужно показать, где читатель находится, а не весь тайтл: на
 * четырёх тысячах глав полный список — это мегабайт разметки, который никто
 * не пролистает. Остальное дозагружается по мере прокрутки списка.
 *
 * @return array{posts: WP_Post[], offset: int, total: int, index: int}
 */
function xv_chapter_window( $chapter_id, $before = 25, $after = 75 ) {
	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );

	if ( ! $novel_id ) {
		return array( 'posts' => array(), 'offset' => 0, 'total' => 0, 'index' => -1 );
	}

	$ids   = xv_chapter_ids( $novel_id );
	$total = count( $ids );
	$index = array_search( (int) $chapter_id, $ids, true );
	$index = false === $index ? 0 : (int) $index;

	$offset = max( 0, $index - (int) $before );
	$length = (int) $before + (int) $after + 1;

	return array(
		'posts'  => xv_posts_in_order( array_slice( $ids, $offset, $length ) ),
		'offset' => $offset,
		'total'  => $total,
		'index'  => $index,
	);
}

/* -------------------------------------------------------------------------
 * Поддержание индекса
 * ---------------------------------------------------------------------- */

/**
 * Индекс сбрасывается при любом изменении главы: номер, статус, привязка к
 * тайтлу. Пересборка одна и та же, повод разный.
 */
function xv_index_on_save( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || 'chapter' !== $post->post_type || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$novel_id = (int) get_post_meta( $post_id, '_xin_novel', true );

	if ( $novel_id ) {
		xv_forget_chapter_index( $novel_id );
		xv_chapter_index_build( $novel_id, false );
	}

	// Главу могли перенести в другой тайтл — старый тоже надо пересобрать.
	$was = (int) get_post_meta( $post_id, '_xin_novel_was', true );

	if ( $was && $was !== $novel_id ) {
		xv_forget_chapter_index( $was );
		xv_chapter_index_build( $was, false );
	}

	if ( $novel_id ) {
		update_post_meta( $post_id, '_xin_novel_was', $novel_id );
	}
}
add_action( 'save_post', 'xv_index_on_save', 20, 2 );

function xv_index_on_delete( $post_id, $post = null ) {
	$post = $post instanceof WP_Post ? $post : get_post( $post_id );

	if ( ! $post || 'chapter' !== $post->post_type ) {
		return;
	}

	$novel_id = (int) get_post_meta( $post_id, '_xin_novel', true );

	if ( $novel_id ) {
		xv_forget_chapter_index( $novel_id );
	}
}
add_action( 'deleted_post', 'xv_index_on_delete', 20, 2 );
add_action( 'trashed_post', 'xv_index_on_delete', 20 );
add_action( 'untrashed_post', 'xv_index_on_delete', 20 );

/**
 * Отложенная глава выходит по расписанию — без сброса индекса она появится
 * в оглавлении только после следующего сохранения.
 */
function xv_index_on_status( $new_status, $old_status, $post ) {
	if ( ! $post instanceof WP_Post || 'chapter' !== $post->post_type || $new_status === $old_status ) {
		return;
	}

	$novel_id = (int) get_post_meta( $post->ID, '_xin_novel', true );

	if ( $novel_id ) {
		xv_forget_chapter_index( $novel_id );
	}
}
add_action( 'transition_post_status', 'xv_index_on_status', 20, 3 );

/* -------------------------------------------------------------------------
 * Оглавление в читалке
 * ---------------------------------------------------------------------- */

/**
 * Одна строка бокового оглавления.
 *
 * @param WP_Post $chapter  Глава.
 * @param int     $current  Открытая сейчас глава.
 * @param int     $position Место в тайтле, считая с нуля.
 */
function xv_toc_link( $chapter, $current, $position ) {
	$open   = xv_chapter_unlocked( $chapter->ID );
	$is_now = (int) $chapter->ID === (int) $current;
	$number = xv_chapter_number( $chapter->ID );

	printf(
		'<a href="%1$s" class="%2$s" data-xv-toc-pos="%3$d"%4$s><span class="xv-reader-toc-num">%5$s</span><span class="xv-reader-toc-title">%6$s</span></a>',
		esc_url( get_permalink( $chapter ) ),
		esc_attr( trim( ( $is_now ? 'is-current ' : '' ) . ( $open ? '' : 'is-locked' ) ) ),
		(int) $position,
		$is_now ? ' aria-current="page"' : '',
		esc_html( '' !== $number ? $number : (string) ( $position + 1 ) ),
		esc_html( get_the_title( $chapter ) )
	);
}

/**
 * Догрузка оглавления. Отдаёт готовую разметку, а не данные: разметку строки
 * всё равно знает только тема, и собирать её второй раз в JS незачем.
 */
function xv_register_toc_route() {
	register_rest_route( 'xin/v2', '/toc', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => array(
			'novel'   => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'offset'  => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
			'length'  => array( 'default' => 100, 'sanitize_callback' => 'absint' ),
			'current' => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xv_rest_toc',
	) );
}
add_action( 'rest_api_init', 'xv_register_toc_route' );

function xv_rest_toc( WP_REST_Request $request ) {
	$novel_id = (int) $request->get_param( 'novel' );

	if ( 'novel' !== get_post_type( $novel_id ) ) {
		return new WP_Error( 'xv_not_novel', __( 'Это не тайтл.', 'xin-v2' ), array( 'status' => 400 ) );
	}

	$offset  = (int) $request->get_param( 'offset' );
	$length  = min( 200, max( 1, (int) $request->get_param( 'length' ) ) );
	$current = (int) $request->get_param( 'current' );
	$total   = xv_chapter_count( $novel_id );

	ob_start();

	foreach ( xv_chapters_slice( $novel_id, $offset, $length ) as $i => $chapter ) {
		xv_toc_link( $chapter, $current, $offset + $i );
	}

	return array(
		'html'   => ob_get_clean(),
		'offset' => $offset,
		'length' => $length,
		'total'  => $total,
	);
}

/**
 * Записи по списку идентификаторов, в порядке списка. Пропавшие молча
 * выпадают — список избранного переживает удалённый тайтл.
 */
function xv_posts_by_ids( $ids, $type = 'any' ) {
	$ids = array_values( array_filter( array_map( 'intval', (array) $ids ) ) );

	if ( ! $ids ) {
		return array();
	}

	return get_posts( array(
		'post_type'           => $type,
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $ids ),
		'post_status'         => 'publish',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );
}
