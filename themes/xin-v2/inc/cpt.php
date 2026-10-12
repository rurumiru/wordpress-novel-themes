<?php
/**
 * Типы записей и таксономии.
 *
 * Контракт мета-полей намеренно совпадает с xin-com: одна и та же база
 * открывается любой из тем без миграции.
 *
 * Когда рядом работает XIN-V2 Kit, модель данных держит он — тайтлы и главы
 * тогда переживают смену темы. Тема в этом случае только сообщает плагину,
 * включены ли обсуждения, и ничего не регистрирует сама.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'xvk_discussions', function () {
	return (bool) xv_opt( 'discussions', true );
} );

if ( defined( 'XIN_V2_KIT' ) ) {
	return;
}

function xv_post_supports( $supports ) {
	if ( ! xv_opt( 'discussions', true ) ) {
		return $supports;
	}

	$supports[] = 'comments';

	return $supports;
}

function xv_register_post_types() {
	register_post_type( 'novel', array(
		'labels'              => array(
			'name'               => __( 'Новеллы', 'xin-v2' ),
			'singular_name'      => __( 'Новелла', 'xin-v2' ),
			'add_new'            => __( 'Добавить', 'xin-v2' ),
			'add_new_item'       => __( 'Добавить новеллу', 'xin-v2' ),
			'edit_item'          => __( 'Редактировать новеллу', 'xin-v2' ),
			'new_item'           => __( 'Новая новелла', 'xin-v2' ),
			'view_item'          => __( 'Смотреть новеллу', 'xin-v2' ),
			'search_items'       => __( 'Искать новеллы', 'xin-v2' ),
			'not_found'          => __( 'Новелл не найдено', 'xin-v2' ),
			'all_items'          => __( 'Все новеллы', 'xin-v2' ),
			'menu_name'          => __( 'Новеллы', 'xin-v2' ),
			'featured_image'     => __( 'Обложка', 'xin-v2' ),
			'set_featured_image' => __( 'Задать обложку', 'xin-v2' ),
		),
		'public'              => true,
		'has_archive'         => 'novels',
		'menu_icon'           => 'dashicons-book-alt',
		'menu_position'       => 5,
		'show_in_rest'        => true,
		'supports'            => xv_post_supports( array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'revisions' ) ),
		'taxonomies'          => array( 'genre', 'novel_tag', 'novel_status' ),
		'rewrite'             => array( 'slug' => 'novels', 'with_front' => false ),
		'exclude_from_search' => false,
	) );

	register_post_type( 'chapter', array(
		'labels'        => array(
			'name'          => __( 'Главы', 'xin-v2' ),
			'singular_name' => __( 'Глава', 'xin-v2' ),
			'add_new_item'  => __( 'Добавить главу', 'xin-v2' ),
			'edit_item'     => __( 'Редактировать главу', 'xin-v2' ),
			'all_items'     => __( 'Все главы', 'xin-v2' ),
			'search_items'  => __( 'Искать главы', 'xin-v2' ),
			'not_found'     => __( 'Глав не найдено', 'xin-v2' ),
			'menu_name'     => __( 'Главы', 'xin-v2' ),
		),
		'public'        => true,
		'has_archive'   => 'updates',
		'menu_icon'     => 'dashicons-media-text',
		'menu_position' => 6,
		'show_in_rest'  => true,
		'supports'      => xv_post_supports( array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ) ),
		'rewrite'       => array( 'slug' => 'read', 'with_front' => false ),
	) );
}
add_action( 'init', 'xv_register_post_types' );

function xv_register_taxonomies() {
	register_taxonomy( 'genre', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Жанры', 'xin-v2' ),
			'singular_name' => __( 'Жанр', 'xin-v2' ),
			'add_new_item'  => __( 'Добавить жанр', 'xin-v2' ),
			'menu_name'     => __( 'Жанры', 'xin-v2' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'genre', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_tag', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Теги тайтлов', 'xin-v2' ),
			'singular_name' => __( 'Тег', 'xin-v2' ),
			'menu_name'     => __( 'Теги тайтлов', 'xin-v2' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'novel-tag', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_status', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Статусы', 'xin-v2' ),
			'singular_name' => __( 'Статус', 'xin-v2' ),
			'menu_name'     => __( 'Статусы', 'xin-v2' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'status', 'with_front' => false ),
	) );
}
add_action( 'init', 'xv_register_taxonomies' );

/**
 * Мета-поля. Регистрируем явно — чтобы REST и ревизии их видели.
 */
function xv_register_meta() {
	$auth = function () {
		return current_user_can( 'edit_posts' );
	};

	$novel = array(
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

	foreach ( $novel as $key => $type ) {
		register_post_meta( 'novel', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => $auth,
		) );
	}

	$chapter = array(
		'_xin_novel'     => 'integer',
		'_xin_number'    => 'number',
		'_xin_locked'    => 'integer',
		'_xin_price'     => 'integer',
		'_xin_unlock_at' => 'integer',
		// Товар, которым глава открывается в магазине. Ключ общий с XIN-Com.
		'_xin_product'   => 'integer',
		'_xin_views'     => 'integer',
	);

	foreach ( $chapter as $key => $type ) {
		register_post_meta( 'chapter', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => $auth,
		) );
	}
}
add_action( 'init', 'xv_register_meta' );

/**
 * Базовые статусы — чтобы фильтр каталога не был пустым на чистой установке.
 */
function xv_seed_terms() {
	if ( get_option( 'xv_terms_seeded' ) ) {
		return;
	}

	$statuses = array(
		'ongoing'   => __( 'Выходит', 'xin-v2' ),
		'completed' => __( 'Завершён', 'xin-v2' ),
		'hiatus'    => __( 'Заморожен', 'xin-v2' ),
		'announced' => __( 'Анонс', 'xin-v2' ),
	);

	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'novel_status' ) ) {
			wp_insert_term( $name, 'novel_status', array( 'slug' => $slug ) );
		}
	}

	update_option( 'xv_terms_seeded', 1 );
}

function xv_activate() {
	xv_register_post_types();
	xv_register_taxonomies();
	xv_seed_terms();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'xv_activate' );
