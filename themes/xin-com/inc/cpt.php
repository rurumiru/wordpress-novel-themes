<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_post_supports( $supports ) {
	if ( function_exists( 'xin_discussions_on' ) && xin_discussions_on() ) {
		$supports[] = 'comments';
	}

	return $supports;
}

function xin_register_post_types() {
	register_post_type( 'novel', array(
		'labels'              => array(
			'name'               => __( 'Новеллы', 'xin-com' ),
			'singular_name'      => __( 'Новелла', 'xin-com' ),
			'add_new'            => __( 'Добавить', 'xin-com' ),
			'add_new_item'       => __( 'Добавить новеллу', 'xin-com' ),
			'edit_item'          => __( 'Редактировать новеллу', 'xin-com' ),
			'new_item'           => __( 'Новая новелла', 'xin-com' ),
			'view_item'          => __( 'Смотреть новеллу', 'xin-com' ),
			'search_items'       => __( 'Искать новеллы', 'xin-com' ),
			'not_found'          => __( 'Новелл не найдено', 'xin-com' ),
			'all_items'          => __( 'Все новеллы', 'xin-com' ),
			'menu_name'          => __( 'Новеллы', 'xin-com' ),
			'featured_image'     => __( 'Обложка', 'xin-com' ),
			'set_featured_image' => __( 'Задать обложку', 'xin-com' ),
		),
		'public'              => true,
		'has_archive'         => 'novels',
		'menu_icon'           => 'dashicons-book-alt',
		'menu_position'       => 5,
		'show_in_rest'        => true,
		'supports'            => xin_post_supports( array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'revisions' ) ),
		'taxonomies'          => array( 'genre', 'novel_tag', 'novel_status' ),
		'rewrite'             => array( 'slug' => 'novels', 'with_front' => false ),
		'exclude_from_search' => false,
	) );

	register_post_type( 'chapter', array(
		'labels'          => array(
			'name'          => __( 'Главы', 'xin-com' ),
			'singular_name' => __( 'Глава', 'xin-com' ),
			'add_new_item'  => __( 'Добавить главу', 'xin-com' ),
			'edit_item'     => __( 'Редактировать главу', 'xin-com' ),
			'all_items'     => __( 'Все главы', 'xin-com' ),
			'search_items'  => __( 'Искать главы', 'xin-com' ),
			'not_found'     => __( 'Глав не найдено', 'xin-com' ),
			'menu_name'     => __( 'Главы', 'xin-com' ),
		),
		'public'          => true,
		'has_archive'     => 'updates',
		'menu_icon'       => 'dashicons-media-text',
		'menu_position'   => 6,
		'show_in_rest'    => true,
		'supports'        => xin_post_supports( array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ) ),
		'rewrite'         => array( 'slug' => 'read', 'with_front' => false ),
	) );
}
add_action( 'init', 'xin_register_post_types' );

function xin_register_taxonomies() {
	register_taxonomy( 'genre', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Жанры', 'xin-com' ),
			'singular_name' => __( 'Жанр', 'xin-com' ),
			'add_new_item'  => __( 'Добавить жанр', 'xin-com' ),
			'menu_name'     => __( 'Жанры', 'xin-com' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'genre', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_tag', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Теги тайтлов', 'xin-com' ),
			'singular_name' => __( 'Тег', 'xin-com' ),
			'menu_name'     => __( 'Теги тайтлов', 'xin-com' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'novel-tag', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_status', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Статусы', 'xin-com' ),
			'singular_name' => __( 'Статус', 'xin-com' ),
			'menu_name'     => __( 'Статусы', 'xin-com' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'status', 'with_front' => false ),
	) );
}
add_action( 'init', 'xin_register_taxonomies' );

function xin_seed_terms() {
	if ( get_option( 'xin_terms_seeded' ) ) {
		return;
	}
	$statuses = array(
		'ongoing'   => __( 'Выходит', 'xin-com' ),
		'completed' => __( 'Завершён', 'xin-com' ),
		'hiatus'    => __( 'Заморожен', 'xin-com' ),
		'announced' => __( 'Анонс', 'xin-com' ),
	);
	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'novel_status' ) ) {
			wp_insert_term( $name, 'novel_status', array( 'slug' => $slug ) );
		}
	}
	update_option( 'xin_terms_seeded', 1 );
}
add_action( 'init', 'xin_seed_terms', 20 );

function xin_flush_rewrites() {
	xin_register_post_types();
	xin_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'xin_flush_rewrites' );

function xin_chapter_novel_id( $chapter_id = 0 ) {
	$chapter_id = $chapter_id ? $chapter_id : get_the_ID();
	return (int) get_post_meta( $chapter_id, '_xin_novel', true );
}

function xin_chapter_number( $chapter_id = 0 ) {
	$chapter_id = $chapter_id ? $chapter_id : get_the_ID();
	$num        = get_post_meta( $chapter_id, '_xin_number', true );
	return '' === $num ? null : (float) $num;
}

function xin_get_chapters( $novel_id, $order = 'ASC', $limit = -1 ) {
	if ( ! $novel_id ) {
		return array();
	}

	$key = 'xin_chapters_' . $novel_id . '_' . $order . '_' . $limit;
	$hit = wp_cache_get( $key, 'xin-com' );
	if ( false !== $hit ) {
		return $hit;
	}

	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_key'       => '_xin_number',
		'orderby'        => array( 'meta_value_num' => $order, 'date' => $order ),
		'meta_query'     => array(
			array(
				'key'   => '_xin_novel',
				'value' => (int) $novel_id,
			),
		),
		// Пагинации у списка глав нет, а SQL_CALC_FOUND_ROWS стоит второго
		// прохода по таблице. Термины главам не назначаются вовсе.
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );

	wp_cache_set( $key, $chapters, 'xin-com', 5 * MINUTE_IN_SECONDS );
	return $chapters;
}

/**
 * Порядковый список ID глав тайтла.
 *
 * Отдельно от xin_get_chapters(), потому что тому нужны целые записи — вместе
 * с текстом главы. Карточке в каталоге нужно ЧИСЛО глав, соседней главе — ID
 * соседа; поднимать ради этого весь текст тайтла (сотни записей на каждую
 * карточку страницы) — самое дорогое, что тема делала на обычном каталоге.
 *
 * @param int    $novel_id ID тайтла.
 * @param string $order    ASC или DESC.
 * @return int[]
 */
function xin_chapter_ids( $novel_id, $order = 'ASC' ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return array();
	}

	$order = 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
	$key   = 'xin_chapter_ids_' . $novel_id . '_' . $order;
	$hit   = wp_cache_get( $key, 'xin-com' );
	if ( false !== $hit ) {
		return $hit;
	}

	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_xin_number',
		'orderby'        => array( 'meta_value_num' => $order, 'date' => $order ),
		'meta_query'     => array(
			array(
				'key'   => '_xin_novel',
				'value' => $novel_id,
			),
		),
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	$ids = array_map( 'intval', (array) $ids );
	wp_cache_set( $key, $ids, 'xin-com', 5 * MINUTE_IN_SECONDS );

	return $ids;
}

function xin_chapter_count( $novel_id ) {
	return count( xin_chapter_ids( $novel_id ) );
}

function xin_first_chapter( $novel_id ) {
	$chapters = xin_get_chapters( $novel_id, 'ASC', 1 );
	return $chapters ? $chapters[0] : null;
}

function xin_last_chapter( $novel_id ) {
	$chapters = xin_get_chapters( $novel_id, 'DESC', 1 );
	return $chapters ? $chapters[0] : null;
}

function xin_adjacent_chapter( $chapter_id, $dir = 1 ) {
	$novel_id = xin_chapter_novel_id( $chapter_id );
	if ( ! $novel_id ) {
		return null;
	}
	// По списку ID, а не по записям целиком: соседняя глава нужна одна, и
	// поднимать ради неё текст всего тайтла на каждый показ главы незачем.
	$ids = xin_chapter_ids( $novel_id, 'ASC' );
	$at  = array_search( (int) $chapter_id, $ids, true );
	if ( false === $at ) {
		return null;
	}

	$target = $at + ( $dir > 0 ? 1 : -1 );

	return isset( $ids[ $target ] ) ? get_post( $ids[ $target ] ) : null;
}

function xin_clear_chapter_cache( $post_id ) {
	if ( 'chapter' === get_post_type( $post_id ) ) {
		wp_cache_flush_group( 'xin-com' );
	}
}
add_action( 'save_post', 'xin_clear_chapter_cache' );
add_action( 'deleted_post', 'xin_clear_chapter_cache' );

if ( ! function_exists( 'wp_cache_flush_group' ) ) {
	function wp_cache_flush_group( $group ) { 
		wp_cache_flush();
	}
}
