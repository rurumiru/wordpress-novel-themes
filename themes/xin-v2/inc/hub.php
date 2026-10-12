<?php
/**
 * Уголок читателя: что на площадке происходит прямо сейчас.
 *
 * Все подборки кэшируются: это витрина, а не отчётность, и минута
 * запаздывания здесь никому не мешает. Без кэша каждая из них — обход
 * таблицы комментариев или меты на каждый заход.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Считает один раз и держит в переходном хранилище.
 *
 * @param string   $key     Имя подборки.
 * @param callable $build   Чем считать.
 * @param int      $minutes Сколько держать.
 */
function xv_hub_cached( $key, $build, $minutes = 10 ) {
	$name = 'xv_hub_' . $key;
	$hit  = get_transient( $name );

	if ( false !== $hit ) {
		return $hit;
	}

	$value = call_user_func( $build );
	set_transient( $name, $value, $minutes * MINUTE_IN_SECONDS );

	return $value;
}

/**
 * Сбрасывает все подборки уголка. Зовётся, когда появилась глава или
 * комментарий: показывать вчерашнее «сейчас читают» бессмысленно.
 */
function xv_hub_forget() {
	global $wpdb;

	$names = $wpdb->get_col(
		"SELECT option_name FROM {$wpdb->options}
		  WHERE option_name LIKE '_transient_xv_hub_%'
		     OR option_name LIKE '_transient_timeout_xv_hub_%'"
	);

	foreach ( (array) $names as $name ) {
		delete_option( $name );
	}
}

function xv_hub_forget_on_content( $post_id, $post = null ) {
	$post = $post instanceof WP_Post ? $post : get_post( $post_id );

	if ( $post && in_array( $post->post_type, array( 'novel', 'chapter' ), true ) ) {
		xv_hub_forget();
	}
}
add_action( 'save_post', 'xv_hub_forget_on_content', 30, 2 );
add_action( 'comment_post', 'xv_hub_forget', 30 );

/**
 * Что читают: главы последней недели, самые просматриваемые.
 */
function xv_hub_trending( $limit = 8 ) {
	return xv_hub_cached( 'trend_' . (int) $limit, function () use ( $limit ) {
		$ids = get_posts( array(
			'post_type'      => 'novel',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_xin_last_chapter_at',
			'orderby'        => array( 'meta_value_num' => 'DESC' ),
			'meta_query'     => array(
				array(
					'key'     => '_xin_last_chapter_at',
					'value'   => time() - 14 * DAY_IN_SECONDS,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		) );

		// Свежих обновлений может не быть вовсе — тогда показываем просто
		// самое читаемое: пустой блок хуже неточного.
		if ( ! $ids ) {
			$ids = wp_list_pluck( xv_get_novels( 'popular', $limit ), 'ID' );
		}

		return array_map( 'intval', $ids );
	} );
}

/**
 * Разговоры недели — ветки, которые собрали больше всего откликов.
 */
function xv_hub_talk( $limit = 6 ) {
	return xv_hub_cached( 'talk_' . (int) $limit, function () use ( $limit ) {
		$comments = get_comments( array(
			'status'  => 'approve',
			'number'  => 200,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
			'type'    => 'comment',
		) );

		$rows = array();

		foreach ( $comments as $comment ) {
			$type = get_post_type( $comment->comment_post_ID );

			if ( ! in_array( $type, array( 'novel', 'chapter', 'post' ), true ) ) {
				continue;
			}

			$rows[] = array(
				'id'    => (int) $comment->comment_ID,
				'post'  => (int) $comment->comment_post_ID,
				'likes' => xv_comment_likes( $comment->comment_ID ),
				'at'    => strtotime( $comment->comment_date_gmt . ' GMT' ),
			);
		}

		usort( $rows, function ( $a, $b ) {
			return $b['likes'] <=> $a['likes'] ?: $b['at'] <=> $a['at'];
		} );

		return array_slice( $rows, 0, (int) $limit );
	} );
}

/**
 * Кто больше читает. Никаких профилей поведения — только счётчик глав,
 * который читатель и сам видит у себя.
 */
function xv_hub_readers( $limit = 10 ) {
	return xv_hub_cached( 'readers_' . (int) $limit, function () use ( $limit ) {
		$users = get_users( array(
			'meta_key' => XV_READ_COUNT,
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
			'number'   => (int) $limit,
			'fields'   => array( 'ID', 'display_name' ),
		) );

		$rows = array();

		foreach ( $users as $user ) {
			$count = xv_read_count( $user->ID );

			if ( $count < 1 ) {
				continue;
			}

			$rows[] = array(
				'id'     => (int) $user->ID,
				'name'   => $user->display_name,
				'read'   => $count,
				'streak' => xv_streak( $user->ID ),
			);
		}

		return $rows;
	}, 30 );
}

/**
 * Новое в избранном читателя. Считается для каждого своё, поэтому не
 * кэшируется на весь сайт.
 */
function xv_hub_shelf_updates( $user_id = 0, $limit = 8 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$shelf   = xv_favourites( $user_id );

	if ( ! $shelf ) {
		return array();
	}

	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_xin_novel',
				'value'   => $shelf,
				'compare' => 'IN',
			),
		),
	) );
}

/**
 * Сводка площадки для шапки уголка.
 */
function xv_hub_pulse() {
	return xv_hub_cached( 'pulse', function () {
		$week = get_posts( array(
			'post_type'      => 'chapter',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'date_query'     => array( array( 'after' => '7 days ago' ) ),
		) );

		$stats = xv_site_stats();

		return array(
			'novels'   => $stats['novels'],
			'chapters' => $stats['chapters'],
			'readers'  => $stats['readers'],
			'week'     => count( $week ),
		);
	} );
}
