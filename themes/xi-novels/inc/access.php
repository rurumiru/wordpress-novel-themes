<?php
/**
 * Кто и что может читать: PLUS, покупка главы, команда проекта.
 *
 * Все проверки платного доступа проходят через xin_can_read_chapter(),
 * чтобы читалка, оглавление и экспорт отвечали одинаково.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_chapter_is_locked( $chapter_id ) {
	return (bool) get_post_meta( $chapter_id, '_xin_locked', true );
}

function xin_chapter_product( $chapter_id ) {
	$product_id = (int) get_post_meta( $chapter_id, '_xin_product', true );

	if ( ! $product_id || ! xin_woo_active() || 'product' !== get_post_type( $product_id ) ) {
		return 0;
	}

	return $product_id;
}

function xin_woo_active() {
	return class_exists( 'WooCommerce' );
}

function xin_user_bought( $product_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $product_id || ! $user_id || ! function_exists( 'wc_customer_bought_product' ) ) {
		return false;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return false;
	}

	return (bool) wc_customer_bought_product( $user->user_email, $user_id, $product_id );
}

function xin_can_read_chapter( $chapter_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! xin_chapter_is_locked( $chapter_id ) ) {
		return true;
	}
	if ( $user_id && user_can( $user_id, 'edit_others_posts' ) ) {
		return true;
	}
	if ( $user_id && (int) get_post_field( 'post_author', $chapter_id ) === (int) $user_id ) {
		return true;
	}
	if ( xin_user_is_plus( $user_id ) ) {
		return true;
	}

	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );
	if ( $novel_id && $user_id && in_array( (int) $user_id, xin_novel_team( $novel_id ), true ) ) {
		return true;
	}

	$product_id = xin_chapter_product( $chapter_id );

	return $product_id ? xin_user_bought( $product_id, $user_id ) : false;
}

function xin_chapter_price( $chapter_id ) {
	$product_id = xin_chapter_product( $chapter_id );

	if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	return $product ? wp_strip_all_tags( wc_price( $product->get_price() ) ) : '';
}

function xin_chapter_buy_url( $chapter_id ) {
	$product_id = xin_chapter_product( $chapter_id );

	if ( ! $product_id ) {
		return '';
	}

	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;

	return $product ? add_query_arg( 'add-to-cart', $product_id, wc_get_cart_url() ) : '';
}

function xin_novel_team( $novel_id ) {
	$team = get_post_meta( $novel_id, '_xin_team', true );
	$team = is_array( $team ) ? array_map( 'absint', $team ) : array();

	return array_values( array_filter( array_unique( $team ) ) );
}

function xin_novel_team_users( $novel_id ) {
	$ids = xin_novel_team( $novel_id );

	return $ids ? get_users( array( 'include' => $ids, 'orderby' => 'display_name' ) ) : array();
}

function xin_set_novel_team( $novel_id, $names ) {
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

function xin_team_names( $novel_id ) {
	$names = array();

	foreach ( xin_novel_team_users( $novel_id ) as $user ) {
		$names[] = $user->user_login;
	}

	return implode( ', ', $names );
}

function xin_in_team( $novel_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id && in_array( (int) $user_id, xin_novel_team( $novel_id ), true );
}

function xin_team_can_edit( $can, $post_id ) {
	if ( $can || ! is_user_logged_in() ) {
		return $can;
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return $can;
	}

	if ( 'novel' === $post->post_type ) {
		return xin_in_team( $post_id );
	}
	if ( 'chapter' === $post->post_type ) {
		$novel_id = (int) get_post_meta( $post_id, '_xin_novel', true );
		return $novel_id ? xin_in_team( $novel_id ) : $can;
	}

	return $can;
}

function xin_user_projects( $user_id, $limit = -1 ) {
	$own = get_posts( array(
		'post_type'      => 'novel',
		'author'         => $user_id,
		'posts_per_page' => $limit,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'fields'         => 'ids',
	) );

	$shared = get_posts( array(
		'post_type'      => 'novel',
		'posts_per_page' => $limit,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'fields'         => 'ids',
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
