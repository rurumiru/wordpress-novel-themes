<?php
/**
 * Страницы на шаблонах темы.
 *
 * Тема объявляет шаблоны — «Моя библиотека», «Уголок читателя», «Вход и
 * регистрация». Пока под шаблон не заведена страница, раздела на сайте
 * попросту нет, и найти это можно только наткнувшись.
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Что предлагаем завести: шаблон, адрес и вступительный текст.
 *
 * Тексты короткие и по делу: страница без единого слова выглядит недоделкой,
 * а длинную заглушку владелец всё равно сотрёт.
 */
function xs_wanted_pages() {
	$pages = array(
		'template-library.php' => array(
			'title' => __( 'Моя библиотека', 'xin-setup' ),
			'slug'  => 'library',
			'text'  => '',
		),
		'template-hub.php' => array(
			'title' => __( 'Уголок читателя', 'xin-setup' ),
			'slug'  => 'hub',
			'text'  => '',
		),
		'template-auth.php' => array(
			'title' => __( 'Вход и регистрация', 'xin-setup' ),
			'slug'  => 'account',
			'text'  => '',
		),
		'template-author.php' => array(
			'title' => __( 'Кабинет автора', 'xin-setup' ),
			'slug'  => 'cabinet',
			'text'  => '',
		),
		'template-ranking.php' => array(
			'title' => __( 'Рейтинг', 'xin-setup' ),
			'slug'  => 'ranking',
			'text'  => '',
		),
		'template-manage.php' => array(
			'title' => __( 'Панель управления', 'xin-setup' ),
			'slug'  => 'manage',
			'text'  => '',
		),
		'template-become-author.php' => array(
			'title' => __( 'Стать автором', 'xin-setup' ),
			'slug'  => 'become-author',
			'text'  => '',
		),
		'template-plus.php' => array(
			'title' => __( 'Поддержать площадку', 'xin-setup' ),
			'slug'  => 'support',
			'text'  => '',
		),
		'template-info.php' => array(
			'title' => __( 'Правила площадки', 'xin-setup' ),
			'slug'  => 'rules',
			'text'  => "<h2>" . __( 'Что здесь можно', 'xin-setup' ) . "</h2>\n<p>"
				. __( 'Опишите, что вы публикуете и на каких условиях. Этот текст читают перед тем, как зарегистрироваться.', 'xin-setup' )
				. "</p>\n<h2>" . __( 'Чего здесь нельзя', 'xin-setup' ) . "</h2>\n<p>"
				. __( 'Короткий и понятный список. Длинные правила не читают.', 'xin-setup' ) . '</p>',
		),
	);

	$have = xs_theme()['templates'];

	// Предлагаем только то, что тема действительно умеет показать.
	return array_intersect_key( $pages, $have );
}

/**
 * Есть ли страница на этом шаблоне.
 */
function xs_page_for( $template ) {
	$found = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	return $found ? (int) $found[0] : 0;
}

/**
 * Каких страниц не хватает.
 */
function xs_missing_pages() {
	$out = array();

	foreach ( xs_wanted_pages() as $template => $page ) {
		if ( ! xs_page_for( $template ) ) {
			$out[ $template ] = $page;
		}
	}

	return $out;
}

/**
 * Заводит недостающие страницы.
 *
 * Существующие не трогаем даже если они в черновике: владелец мог убрать
 * раздел намеренно, и возвращать его молча нельзя.
 */
function xs_create_pages( $templates = array() ) {
	$wanted = xs_wanted_pages();
	$made   = array();

	foreach ( $wanted as $template => $page ) {
		if ( $templates && ! in_array( $template, (array) $templates, true ) ) {
			continue;
		}

		if ( xs_page_for( $template ) ) {
			continue;
		}
		$existing=get_page_by_path($page['slug']);
		if($existing) {
			$assigned=get_post_meta($existing->ID,'_wp_page_template',true);
			if(!$assigned || $assigned==='default') {
				update_post_meta($existing->ID,'_wp_page_template',$template);
				$made[$template]=$existing->ID;
			}
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['text'],
		) );

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		update_post_meta( $id, '_wp_page_template', $template );

		$made[ $template ] = $id;
	}

	return $made;
}

/**
 * Меню сайта.
 *
 * Тема умеет обойтись без меню — она строит разделы сама. Но как только
 * владелец добавит свою страницу, ему понадобится настоящее меню, и лучше
 * собрать его сразу из того, что уже есть.
 */
function xs_build_menus() {
	$made = array();

	$plan = array(
		'primary' => array(
			'name'  => __( 'Основное меню', 'xin-setup' ),
			'items' => array(
				array( 'type' => 'archive', 'object' => 'novel', 'label' => __( 'Каталог', 'xin-setup' ) ),
				array( 'type' => 'archive', 'object' => 'chapter', 'label' => __( 'Обновления', 'xin-setup' ) ),
				array( 'type' => 'template', 'object' => 'template-ranking.php', 'label' => __( 'Рейтинг', 'xin-setup' ) ),
				array( 'type' => 'template', 'object' => 'template-hub.php', 'label' => __( 'Уголок читателя', 'xin-setup' ) ),
			),
		),
		'footer_info' => array(
			'name'  => __( 'Информация в подвале', 'xin-setup' ),
			'items' => array(
				array( 'type' => 'template', 'object' => 'template-info.php', 'label' => __( 'Правила', 'xin-setup' ) ),
				array( 'type' => 'template', 'object' => 'template-plus.php', 'label' => __( 'Поддержать', 'xin-setup' ) ),
				array( 'type' => 'template', 'object' => 'template-become-author.php', 'label' => __( 'Стать автором', 'xin-setup' ) ),
			),
		),
	);

	$locations = get_nav_menu_locations();

	foreach ( $plan as $location => $menu ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			continue;
		}

		$existing = wp_get_nav_menu_object( $menu['name'] );
		$menu_id  = $existing ? (int) $existing->term_id : (int) wp_create_nav_menu( $menu['name'] );

		if ( ! $menu_id || is_wp_error( $menu_id ) ) {
			continue;
		}

		if ( ! $existing ) {
			foreach ( $menu['items'] as $item ) {
				xs_add_menu_item( $menu_id, $item );
			}
		}

		$locations[ $location ] = $menu_id;
		$made[]                 = $menu['name'];
	}

	set_theme_mod( 'nav_menu_locations', $locations );

	return $made;
}

function xs_add_menu_item( $menu_id, $item ) {
	if ( 'archive' === $item['type'] ) {
		$url = get_post_type_archive_link( $item['object'] );

		if ( ! $url ) {
			return;
		}

		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $item['label'],
			'menu-item-url'    => $url,
			'menu-item-status' => 'publish',
		) );

		return;
	}

	$page_id = xs_page_for( $item['object'] );

	if ( ! $page_id ) {
		return;
	}

	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => $item['label'],
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	) );
}
