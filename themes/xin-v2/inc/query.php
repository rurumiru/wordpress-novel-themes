<?php
/**
 * Навигация, поиск и настройка основных запросов.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Адрес страницы по её шаблону. Кабинет автора и управление сайтом — это
 * обычные страницы WordPress, поэтому их адрес задаёт владелец сайта, а не
 * тема.
 */
function xv_page_url( $template ) {
	$cache = wp_cache_get( 'xv_tpl_' . $template, 'xin-v2' );

	if ( false !== $cache ) {
		return $cache ? get_permalink( $cache ) : '';
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$id = $pages ? (int) $pages[0] : 0;
	wp_cache_set( 'xv_tpl_' . $template, $id, 'xin-v2' );

	return $id ? get_permalink( $id ) : '';
}

/**
 * Пункты верхнего меню.
 *
 * Если владелец сайта назначил меню `primary` — берём его. Иначе строим
 * разделы темы сами: пустая шапка на свежей установке выглядит поломкой.
 */
function xv_nav_items() {
	$items = xv_nav_from_menu();

	if ( $items ) {
		return $items;
	}

	$novels   = get_post_type_archive_link( 'novel' );
	$updates  = get_post_type_archive_link( 'chapter' );
	$rankings = xv_page_url( 'template-ranking.php' );
	$blog     = xv_blog_url();

	$read = array(
		array(
			'label'  => __( 'Каталог', 'xin-v2' ),
			'url'    => $novels,
			'icon'   => 'compass',
			'desc'   => __( 'Все тайтлы платформы', 'xin-v2' ),
			'active' => is_post_type_archive( 'novel' ) || is_tax( array( 'genre', 'novel_tag', 'novel_status' ) ),
		),
		array(
			'label'  => __( 'Обновления', 'xin-v2' ),
			'url'    => $updates,
			'icon'   => 'clock',
			'desc'   => __( 'Свежие главы', 'xin-v2' ),
			'active' => is_post_type_archive( 'chapter' ),
		),
	);

	if ( $rankings ) {
		$read[] = array(
			'label'  => __( 'Рейтинг', 'xin-v2' ),
			'url'    => $rankings,
			'icon'   => 'trophy',
			'desc'   => __( 'Что читают прямо сейчас', 'xin-v2' ),
			'active' => is_page_template( 'template-ranking.php' ),
		);
	}

	$items = array(
		array(
			'label'    => __( 'Главная', 'xin-v2' ),
			'url'      => home_url( '/' ),
			'icon'     => 'home',
			'active'   => is_front_page(),
			'children' => array(),
		),
		array(
			'label'    => __( 'Читать', 'xin-v2' ),
			'url'      => $novels,
			'icon'     => 'book-open',
			'active'   => wp_list_filter( $read, array( 'active' => true ) ) ? true : false,
			'children' => $read,
		),
	);

	if ( $blog ) {
		$items[] = array(
			'label'    => __( 'Блог', 'xin-v2' ),
			'url'      => $blog,
			'icon'     => 'newspaper',
			'active'   => is_home() || is_singular( 'post' ) || is_category(),
			'children' => array(),
		);
	}

	return $items;
}

function xv_nav_from_menu() {
	if ( ! has_nav_menu( 'primary' ) ) {
		return array();
	}

	$locations = get_nav_menu_locations();
	$menu      = wp_get_nav_menu_object( $locations['primary'] );

	if ( ! $menu ) {
		return array();
	}

	$objects = wp_get_nav_menu_items( $menu->term_id );

	if ( ! $objects ) {
		return array();
	}

	$current = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$tree    = array();
	$index   = array();

	foreach ( $objects as $object ) {
		$node = array(
			'label'    => $object->title,
			'url'      => $object->url,
			'icon'     => xv_menu_icon( $object ),
			'desc'     => $object->description,
			'active'   => untrailingslashit( $object->url ) === untrailingslashit( $current ),
			'children' => array(),
		);

		$index[ $object->ID ] = $node;
	}

	foreach ( $objects as $object ) {
		if ( $object->menu_item_parent && isset( $index[ (int) $object->menu_item_parent ] ) ) {
			continue;
		}

		$node = $index[ $object->ID ];

		foreach ( $objects as $child ) {
			if ( (int) $child->menu_item_parent === (int) $object->ID ) {
				$node['children'][] = $index[ $child->ID ];

				if ( $index[ $child->ID ]['active'] ) {
					$node['active'] = true;
				}
			}
		}

		$tree[] = $node;
	}

	return $tree;
}

/**
 * Иконка пункта меню: из CSS-класса `xv-icon-<имя>`, иначе по типу объекта.
 */
function xv_menu_icon( $object ) {
	foreach ( (array) $object->classes as $class ) {
		if ( str_starts_with( $class, 'xv-icon-' ) ) {
			return substr( $class, 9 );
		}
	}

	if ( 'post_type_archive' === $object->type ) {
		return 'novel' === $object->object ? 'compass' : 'clock';
	}

	if ( 'taxonomy' === $object->type ) {
		return 'tag';
	}

	return 'chevron-right';
}

function xv_blog_url() {
	$page = (int) get_option( 'page_for_posts' );

	if ( $page ) {
		return get_permalink( $page );
	}

	// Записей нет — ссылка в шапке только мешает.
	return wp_count_posts( 'post' )->publish ? home_url( '/' ) : '';
}

/**
 * Основные запросы архивов.
 */
function xv_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Каталог: сортировка и фильтры приходят из строки запроса.
	if ( $query->is_post_type_archive( 'novel' ) || $query->is_tax( array( 'genre', 'novel_tag', 'novel_status' ) ) ) {
		$query->set( 'posts_per_page', max( 6, (int) xv_opt( 'catalog_per_page', 24 ) ) );
		xv_apply_catalog_args( $query );

		return;
	}

	if ( $query->is_post_type_archive( 'chapter' ) ) {
		$query->set( 'posts_per_page', 40 );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );

		return;
	}

	// Поиск по умолчанию ищет тайтлы — их ищут чаще, чем страницы и главы.
	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array( 'novel', 'chapter', 'post', 'page' ) );
	}
}
add_action( 'pre_get_posts', 'xv_pre_get_posts' );

/**
 * Фильтры каталога. Держим в одном месте: их читают и архив, и таксономии.
 */
function xv_apply_catalog_args( $query ) {
	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'new';

	switch ( $sort ) {
		case 'popular':
			$query->set( 'meta_key', '_xin_views' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		case 'rating':
			$query->set( 'meta_key', '_xin_rating' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		case 'updated':
			$query->set( 'meta_key', '_xin_last_chapter_at' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;

		case 'title':
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
			break;

		default:
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
	}

	$tax = array();

	if ( ! empty( $_GET['status'] ) ) {
		$tax[] = array(
			'taxonomy' => 'novel_status',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['status'] ) ),
		);
	}

	if ( ! empty( $_GET['genre'] ) && ! $query->is_tax( 'genre' ) ) {
		$tax[] = array(
			'taxonomy' => 'genre',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['genre'] ) ),
		);
	}

	if ( ! empty( $_GET['tag'] ) && ! $query->is_tax( 'novel_tag' ) ) {
		$tax[] = array(
			'taxonomy' => 'novel_tag',
			'field'    => 'slug',
			'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['tag'] ) ),
		);
	}

	if ( count( $tax ) > 1 ) {
		$tax['relation'] = 'AND';
	}

	if ( $tax ) {
		$existing = (array) $query->get( 'tax_query' );
		$query->set( 'tax_query', array_merge( $existing, $tax ) );
	}

	if ( ! empty( $_GET['q'] ) ) {
		$query->set( 's', sanitize_text_field( wp_unslash( $_GET['q'] ) ) );
	}

	if ( ! empty( $_GET['year'] ) ) {
		$meta   = (array) $query->get( 'meta_query' );
		$meta[] = array(
			'key'   => '_xin_year',
			'value' => sanitize_text_field( wp_unslash( $_GET['year'] ) ),
		);
		$query->set( 'meta_query', $meta );
	}
}

/**
 * Главы открываются по адресу тайтла: /read/<тайтл>/<глава>/ читается
 * лучше, чем плоский /read/<глава>/.
 */
function xv_chapter_permalink( $url, $post ) {
	if ( 'chapter' !== $post->post_type ) {
		return $url;
	}

	$novel = (int) get_post_meta( $post->ID, '_xin_novel', true );

	if ( ! $novel ) {
		return $url;
	}

	$slug = get_post_field( 'post_name', $novel );

	if ( ! $slug ) {
		return $url;
	}

	return home_url( user_trailingslashit( 'read/' . $slug . '/' . $post->post_name ) );
}
add_filter( 'post_type_link', 'xv_chapter_permalink', 10, 2 );

function xv_chapter_rewrite() {
	add_rewrite_rule(
		'^read/([^/]+)/([^/]+)/?$',
		'index.php?post_type=chapter&name=$matches[2]',
		'top'
	);
}
add_action( 'init', 'xv_chapter_rewrite' );

/**
 * Один адрес на главу.
 *
 * Главы отдаются и по вложенному `/read/<тайтл>/<глава>/`, и по плоскому
 * `/read/<глава>/`, который остаётся от собственного правила типа записи, и
 * по вложенному с чужим тайтлом. Три живых адреса на один текст — это
 * расползание по выдаче и разные адреса в закладках у читателей, поэтому
 * лишние варианты уводим постоянным редиректом на канонический.
 */
function xv_chapter_canonical() {
	if ( ! is_singular( 'chapter' ) || is_preview() || is_customize_preview() ) {
		return;
	}

	$target = get_permalink( get_queried_object_id() );

	if ( ! $target ) {
		return;
	}

	$want = rawurldecode( (string) wp_parse_url( $target, PHP_URL_PATH ) );
	$have = rawurldecode( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( '' === $want || '' === $have ) {
		return;
	}

	if ( untrailingslashit( $want ) === untrailingslashit( $have ) ) {
		return;
	}

	// Строку запроса сохраняем: по ней приходят метки источника и порядок
	// комментариев, и терять их на редиректе незачем.
	$query = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	wp_safe_redirect( $query ? $target . '?' . $query : $target, 301 );
	exit;
}
add_action( 'template_redirect', 'xv_chapter_canonical', 5 );
