<?php
/**
 * Модель данных: типы записей, таксономии и мета.
 *
 * Живёт в плагине, а не в теме, чтобы тайтлы и главы не исчезали при смене
 * оформления. Контракт мета-полей — `_xin_*`, тот же, что у XIN-Com: обе темы
 * открывают одну базу без миграции.
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Поддерживает ли тип записей комментарии. Тема может отключить обсуждения —
 * тогда и поддержки быть не должно.
 */
function xvk_supports_comments() {
	return (bool) apply_filters( 'xvk_discussions', true );
}

function xvk_register_content() {
	xvk_register_post_types();
	xvk_register_taxonomies();
}

function xvk_register_post_types() {
	$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'revisions' );

	if ( xvk_supports_comments() ) {
		$supports[] = 'comments';
	}

	register_post_type( 'novel', array(
		'labels'              => array(
			'name'               => __( 'Новеллы', 'xin-v2-kit' ),
			'singular_name'      => __( 'Новелла', 'xin-v2-kit' ),
			'add_new'            => __( 'Добавить', 'xin-v2-kit' ),
			'add_new_item'       => __( 'Добавить новеллу', 'xin-v2-kit' ),
			'edit_item'          => __( 'Редактировать новеллу', 'xin-v2-kit' ),
			'new_item'           => __( 'Новая новелла', 'xin-v2-kit' ),
			'view_item'          => __( 'Смотреть новеллу', 'xin-v2-kit' ),
			'search_items'       => __( 'Искать новеллы', 'xin-v2-kit' ),
			'not_found'          => __( 'Новелл не найдено', 'xin-v2-kit' ),
			'all_items'          => __( 'Все новеллы', 'xin-v2-kit' ),
			'menu_name'          => __( 'Новеллы', 'xin-v2-kit' ),
			'featured_image'     => __( 'Обложка', 'xin-v2-kit' ),
			'set_featured_image' => __( 'Задать обложку', 'xin-v2-kit' ),
		),
		'public'              => true,
		'has_archive'         => 'novels',
		'menu_icon'           => 'dashicons-book-alt',
		'menu_position'       => 5,
		'show_in_rest'        => true,
		'supports'            => $supports,
		'taxonomies'          => array( 'genre', 'novel_tag', 'novel_status' ),
		'rewrite'             => array( 'slug' => 'novels', 'with_front' => false ),
		'exclude_from_search' => false,
	) );

	$chapter_supports = array( 'title', 'editor', 'author', 'revisions', 'custom-fields' );

	if ( xvk_supports_comments() ) {
		$chapter_supports[] = 'comments';
	}

	register_post_type( 'chapter', array(
		'labels'        => array(
			'name'          => __( 'Главы', 'xin-v2-kit' ),
			'singular_name' => __( 'Глава', 'xin-v2-kit' ),
			'add_new_item'  => __( 'Добавить главу', 'xin-v2-kit' ),
			'edit_item'     => __( 'Редактировать главу', 'xin-v2-kit' ),
			'all_items'     => __( 'Все главы', 'xin-v2-kit' ),
			'search_items'  => __( 'Искать главы', 'xin-v2-kit' ),
			'not_found'     => __( 'Глав не найдено', 'xin-v2-kit' ),
			'menu_name'     => __( 'Главы', 'xin-v2-kit' ),
		),
		'public'        => true,
		'has_archive'   => 'updates',
		'menu_icon'     => 'dashicons-media-text',
		'menu_position' => 6,
		'show_in_rest'  => true,
		'supports'      => $chapter_supports,
		'rewrite'       => array( 'slug' => 'read', 'with_front' => false ),
	) );
}

function xvk_register_taxonomies() {
	register_taxonomy( 'genre', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Жанры', 'xin-v2-kit' ),
			'singular_name' => __( 'Жанр', 'xin-v2-kit' ),
			'add_new_item'  => __( 'Добавить жанр', 'xin-v2-kit' ),
			'menu_name'     => __( 'Жанры', 'xin-v2-kit' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'genre', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_tag', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Теги тайтлов', 'xin-v2-kit' ),
			'singular_name' => __( 'Тег', 'xin-v2-kit' ),
			'menu_name'     => __( 'Теги тайтлов', 'xin-v2-kit' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'novel-tag', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_status', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Статусы', 'xin-v2-kit' ),
			'singular_name' => __( 'Статус', 'xin-v2-kit' ),
			'menu_name'     => __( 'Статусы', 'xin-v2-kit' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'status', 'with_front' => false ),
	) );
}
add_action( 'init', 'xvk_register_content' );

/**
 * Мета-поля тайтла и главы.
 */
function xvk_novel_meta() {
	return array(
		'_xin_author_name'     => 'string',
		'_xin_translator'      => 'string',
		'_xin_year'            => 'string',
		'_xin_country'         => 'string',
		'_xin_rating'          => 'number',
		'_xin_rating_count'    => 'integer',
		'_xin_views'           => 'integer',
		'_xin_chapter_count'   => 'integer',
		'_xin_last_chapter_at' => 'integer',
		'_xin_featured'        => 'integer',
		'_xin_adult'           => 'integer',
		'_xin_background'      => 'integer',
		// Соавторы и порядок глав — массивы, поэтому тип строкой не описать.
		'_xin_team'            => 'array',
		'_xin_chapter_index'   => 'array',
	);
}

function xvk_chapter_meta() {
	return array(
		'_xin_novel'     => 'integer',
		'_xin_number'    => 'number',
		'_xin_locked'    => 'integer',
		'_xin_price'     => 'integer',
		'_xin_unlock_at' => 'integer',
		// Товар, которым глава открывается в магазине. Ключ общий с XIN-Com.
		'_xin_product'   => 'integer',
		'_xin_views'     => 'integer',
	);
}

function xvk_register_meta() {
	$auth = function () {
		return current_user_can( 'edit_posts' );
	};

	foreach ( xvk_novel_meta() as $key => $type ) {
		register_post_meta( 'novel', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => $auth,
		) );
	}

	foreach ( xvk_chapter_meta() as $key => $type ) {
		register_post_meta( 'chapter', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => $auth,
		) );
	}
}
add_action( 'init', 'xvk_register_meta' );

/**
 * Базовые статусы выхода — чтобы фильтр каталога не был пустым на чистой
 * установке.
 */
function xvk_seed_terms() {
	if ( get_option( 'xvk_terms_seeded' ) ) {
		return;
	}

	$statuses = array(
		'ongoing'   => __( 'Выходит', 'xin-v2-kit' ),
		'completed' => __( 'Завершён', 'xin-v2-kit' ),
		'hiatus'    => __( 'Заморожен', 'xin-v2-kit' ),
		'announced' => __( 'Анонс', 'xin-v2-kit' ),
	);

	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'novel_status' ) ) {
			wp_insert_term( $name, 'novel_status', array( 'slug' => $slug ) );
		}
	}

	update_option( 'xvk_terms_seeded', 1 );
}
add_action( 'admin_init', 'xvk_seed_terms' );
