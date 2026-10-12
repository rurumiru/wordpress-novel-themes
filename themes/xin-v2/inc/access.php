<?php
/**
 * Кто что может читать и кто что может править.
 *
 * Все проверки платного доступа проходят здесь, чтобы читалка, оглавление,
 * выгрузка книги и лента отвечали одинаково. Замки бывают двух видов, и оба
 * надо понимать: ранний доступ до даты `_xin_unlock_at` и покупка товара
 * WooCommerce `_xin_product`. Первый ставит сама тема, второй достаётся от
 * XIN-Com — база у тем общая, и глава, купленная там, должна открываться и
 * здесь.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Замок главы
 * ---------------------------------------------------------------------- */

function xv_chapter_is_locked( $chapter_id ) {
	return (bool) get_post_meta( $chapter_id, '_xin_locked', true );
}

/**
 * Цена в монетах площадки. Ноль — цены нет, замок держит только дата.
 */
function xv_chapter_price( $chapter_id ) {
	return (int) get_post_meta( $chapter_id, '_xin_price', true );
}

function xv_woo_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Товар, которым открывается глава. Пустой результат значит, что платного
 * товара нет — либо его не назначили, либо WooCommerce выключен.
 */
function xv_chapter_product( $chapter_id ) {
	$product_id = (int) get_post_meta( $chapter_id, '_xin_product', true );

	if ( ! $product_id || ! xv_woo_active() || 'product' !== get_post_type( $product_id ) ) {
		return 0;
	}

	return $product_id;
}

function xv_user_bought( $product_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $product_id || ! $user_id || ! function_exists( 'wc_customer_bought_product' ) ) {
		return false;
	}

	$user = get_userdata( $user_id );

	return $user ? (bool) wc_customer_bought_product( $user->user_email, $user_id, $product_id ) : false;
}

/**
 * Когда глава откроется всем. Ноль — дата не задана.
 */
function xv_chapter_unlock_time( $chapter_id ) {
	return (int) get_post_meta( $chapter_id, '_xin_unlock_at', true );
}

/**
 * Открыта ли глава этому читателю.
 *
 * Порядок проверок важен: сначала бесплатные и свои, потом дата, потом
 * покупка. Глава без даты и без товара остаётся закрытой — но и показать её
 * как «купите» нельзя, поэтому такие замки помечает «Диагностика» плагина.
 */
function xv_chapter_unlocked( $chapter_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! xv_chapter_is_locked( $chapter_id ) ) {
		return true;
	}

	if ( $user_id && user_can( $user_id, 'edit_others_posts' ) ) {
		return true;
	}

	if ( $user_id && (int) get_post_field( 'post_author', $chapter_id ) === (int) $user_id ) {
		return true;
	}

	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );

	if ( $novel_id && $user_id && xv_in_team( $novel_id, $user_id ) ) {
		return true;
	}

	$at = xv_chapter_unlock_time( $chapter_id );

	if ( $at && $at <= time() ) {
		return true;
	}

	$product_id = xv_chapter_product( $chapter_id );

	return $product_id ? xv_user_bought( $product_id, $user_id ) : false;
}

/**
 * Чем глава открывается: датой, покупкой или ничем.
 *
 * Читалке нужно не «закрыто», а «закрыто до вторника» либо «закрыто, пока не
 * купите»: без этого экран замка не может ничего предложить.
 */
function xv_chapter_lock( $chapter_id ) {
	$lock = array(
		'locked'  => ! xv_chapter_unlocked( $chapter_id ),
		'kind'    => '',
		'at'      => xv_chapter_unlock_time( $chapter_id ),
		'price'   => xv_chapter_price( $chapter_id ),
		'buy_url' => '',
		'money'   => '',
	);

	if ( ! $lock['locked'] ) {
		return $lock;
	}

	$product_id = xv_chapter_product( $chapter_id );

	if ( $product_id ) {
		$lock['kind']    = 'purchase';
		$lock['buy_url'] = xv_chapter_buy_url( $chapter_id );
		$lock['money']   = xv_chapter_money( $chapter_id );

		return $lock;
	}

	$lock['kind'] = $lock['at'] ? 'schedule' : 'closed';

	return $lock;
}

function xv_chapter_money( $chapter_id ) {
	$product_id = xv_chapter_product( $chapter_id );

	if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	return $product ? wp_strip_all_tags( wc_price( $product->get_price() ) ) : '';
}

function xv_chapter_buy_url( $chapter_id ) {
	$product_id = xv_chapter_product( $chapter_id );

	if ( ! $product_id || ! function_exists( 'wc_get_cart_url' ) ) {
		return '';
	}

	return add_query_arg( 'add-to-cart', $product_id, wc_get_cart_url() );
}

/* -------------------------------------------------------------------------
 * Команда проекта
 * ---------------------------------------------------------------------- */

/**
 * Соавторы тайтла. Тот же ключ, что у XIN-Com: команда, собранная там,
 * продолжает работать здесь.
 */
function xv_novel_team( $novel_id ) {
	$team = get_post_meta( $novel_id, '_xin_team', true );
	$team = is_array( $team ) ? array_map( 'absint', $team ) : array();

	return array_values( array_filter( array_unique( $team ) ) );
}

function xv_novel_team_users( $novel_id ) {
	$ids = xv_novel_team( $novel_id );

	return $ids ? get_users( array( 'include' => $ids, 'orderby' => 'display_name' ) ) : array();
}

function xv_in_team( $novel_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id && in_array( (int) $user_id, xv_novel_team( $novel_id ), true );
}

/**
 * Принимает логины, ник-адреса и почту через запятую: автор набирает так,
 * как помнит соавтора, а не так, как его хранит база.
 */
function xv_set_novel_team( $novel_id, $names ) {
	$ids = array();

	foreach ( preg_split( '/[,;\n]+/', (string) $names ) as $name ) {
		$name = trim( $name );

		if ( '' === $name ) {
			continue;
		}

		$user = get_user_by( 'login', $name );

		if ( ! $user ) {
			$user = get_user_by( 'slug', sanitize_title( $name ) );
		}

		if ( ! $user ) {
			$user = get_user_by( 'email', $name );
		}

		if ( $user ) {
			$ids[] = (int) $user->ID;
		}
	}

	$ids = array_values( array_unique( array_diff( $ids, array( (int) get_post_field( 'post_author', $novel_id ) ) ) ) );

	if ( $ids ) {
		update_post_meta( $novel_id, '_xin_team', $ids );
	} else {
		delete_post_meta( $novel_id, '_xin_team' );
	}

	return $ids;
}

function xv_team_names( $novel_id ) {
	$names = array();

	foreach ( xv_novel_team_users( $novel_id ) as $user ) {
		$names[] = $user->user_login;
	}

	return implode( ', ', $names );
}

/**
 * Право править тайтл и его главы для соавтора.
 *
 * Через user_has_cap, а не через роли: команда меняется у каждого тайтла
 * отдельно, а роль одна на весь сайт.
 */
function xv_team_can_edit( $allcaps, $caps, $args ) {
	if ( empty( $args[0] ) || ! in_array( $args[0], array( 'edit_post', 'delete_post', 'publish_post' ), true ) ) {
		return $allcaps;
	}

	$user_id = isset( $args[1] ) ? (int) $args[1] : 0;
	$post_id = isset( $args[2] ) ? (int) $args[2] : 0;

	if ( ! $user_id || ! $post_id ) {
		return $allcaps;
	}

	$post = get_post( $post_id );

	if ( ! $post ) {
		return $allcaps;
	}

	if ( 'novel' === $post->post_type ) {
		$novel_id = $post_id;
	} elseif ( 'chapter' === $post->post_type ) {
		$novel_id = (int) get_post_meta( $post_id, '_xin_novel', true );
	} else {
		return $allcaps;
	}

	if ( ! $novel_id || ! xv_in_team( $novel_id, $user_id ) ) {
		return $allcaps;
	}

	foreach ( (array) $caps as $cap ) {
		$allcaps[ $cap ] = true;
	}

	return $allcaps;
}
add_filter( 'user_has_cap', 'xv_team_can_edit', 10, 3 );

/**
 * Тайтлы, которые пользователь ведёт: свои и те, куда его позвали.
 */
function xv_user_projects( $user_id, $limit = -1 ) {
	$statuses = array( 'publish', 'draft', 'pending', 'future', 'private' );

	$own = get_posts( array(
		'post_type'      => 'novel',
		'author'         => $user_id,
		'posts_per_page' => $limit,
		'post_status'    => $statuses,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$shared = get_posts( array(
		'post_type'      => 'novel',
		'posts_per_page' => $limit,
		'post_status'    => $statuses,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_xin_team',
				'value'   => ':' . (int) $user_id . ';',
				'compare' => 'LIKE',
			),
		),
	) );

	return array_values( array_unique( array_merge( $own, $shared ) ) );
}

/**
 * Что сейчас закрыто ранним доступом. Для витрины поддержки: показать, за
 * что именно её просят.
 */
function xv_locked_chapters( $limit = 8 ) {
	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array( 'key' => '_xin_locked', 'value' => '1' ),
		),
	) );
}
