<?php
/**
 * Счётчики тайтла: число глав и время последнего обновления.
 *
 * Каталог сортируется по этим полям, поэтому они должны меняться вместе с
 * главами, а не пересчитываться на каждый показ страницы.
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Опубликованные главы тайтла.
 *
 * @return int[] Идентификаторы.
 */
function xvk_novel_chapter_ids( $novel_id, $statuses = array( 'publish' ) ) {
	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => $statuses,
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array( 'key' => '_xin_novel', 'value' => (int) $novel_id ),
		),
	) );
}

/**
 * Пересчёт счётчиков одного тайтла.
 *
 * @return array Что изменилось.
 */
function xvk_sync_novel( $novel_id ) {
	$novel_id = (int) $novel_id;
	$changed  = array();

	if ( 'novel' !== get_post_type( $novel_id ) ) {
		return $changed;
	}

	$ids   = xvk_novel_chapter_ids( $novel_id );
	$count = count( $ids );

	if ( (int) get_post_meta( $novel_id, '_xin_chapter_count', true ) !== $count ) {
		update_post_meta( $novel_id, '_xin_chapter_count', $count );
		$changed[] = 'chapter_count';
	}

	$last = 0;

	foreach ( $ids as $id ) {
		$time = (int) get_post_timestamp( $id );

		if ( $time > $last ) {
			$last = $time;
		}
	}

	if ( $last && (int) get_post_meta( $novel_id, '_xin_last_chapter_at', true ) !== $last ) {
		update_post_meta( $novel_id, '_xin_last_chapter_at', $last );
		$changed[] = 'last_chapter_at';
	}

	if ( ! $last && get_post_meta( $novel_id, '_xin_last_chapter_at', true ) ) {
		delete_post_meta( $novel_id, '_xin_last_chapter_at' );
		$changed[] = 'last_chapter_at';
	}

	return $changed;
}

function xvk_on_save_chapter( $post_id, $post ) {
	if ( 'chapter' !== $post->post_type || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$novel = (int) get_post_meta( $post_id, '_xin_novel', true );

	if ( $novel ) {
		xvk_sync_novel( $novel );
	}

	delete_transient( 'xv_site_stats' );
}
add_action( 'save_post', 'xvk_on_save_chapter', 20, 2 );

function xvk_on_trash_chapter( $post_id ) {
	if ( 'chapter' !== get_post_type( $post_id ) ) {
		return;
	}

	$novel = (int) get_post_meta( $post_id, '_xin_novel', true );

	if ( $novel ) {
		xvk_sync_novel( $novel );
	}

	delete_transient( 'xv_site_stats' );
}
add_action( 'trashed_post', 'xvk_on_trash_chapter' );
add_action( 'untrashed_post', 'xvk_on_trash_chapter' );
add_action( 'before_delete_post', 'xvk_on_trash_chapter' );

/**
 * Следующий свободный номер главы в тайтле.
 */
function xvk_next_chapter_number( $novel_id ) {
	$ids = xvk_novel_chapter_ids( $novel_id, array( 'publish', 'draft', 'pending', 'future' ) );
	$max = 0;

	foreach ( $ids as $id ) {
		$number = (float) get_post_meta( $id, '_xin_number', true );

		if ( $number > $max ) {
			$max = $number;
		}
	}

	return (int) floor( $max ) + 1;
}
