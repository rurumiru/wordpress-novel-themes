<?php
/**
 * Создание глав из разобранных файлов.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xni_theme_ready() {
	return post_type_exists( 'novel' ) && post_type_exists( 'chapter' );
}

function xni_novels() {
	return get_posts( array(
		'post_type'      => 'novel',
		'posts_per_page' => 200,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	) );
}

function xni_next_number( $novel_id ) {
	$last = get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => 1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'meta_key'       => '_xin_number',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'meta_query'     => array(
			array( 'key' => '_xin_novel', 'value' => (int) $novel_id ),
		),
	) );

	return $last ? (float) get_post_meta( $last[0]->ID, '_xin_number', true ) + 1 : 1;
}

function xni_find_chapter( $novel_id, $number ) {
	$found = get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => 1,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'meta_query'     => array(
			array( 'key' => '_xin_novel', 'value' => (int) $novel_id ),
			array( 'key' => '_xin_number', 'value' => (string) $number ),
		),
	) );

	return $found ? $found[0]->ID : 0;
}

function xni_import( $chapters, $args ) {
	$args = wp_parse_args( $args, array(
		'novel_id'    => 0,
		'novel_title' => '',
		'status'      => 'publish',
		'start'       => 0,
		'locked_from' => 0,
		'author_id'   => get_current_user_id(),
	) );

	if ( ! xni_theme_ready() ) {
		return new WP_Error( 'xni_theme', __( 'Тема XI Novels не активна: типов записей «Новеллы» и «Главы» не существует.', 'xi-novel-import' ) );
	}

	$novel_id = (int) $args['novel_id'];

	if ( ! $novel_id && $args['novel_title'] ) {
		$novel_id = wp_insert_post( array(
			'post_type'   => 'novel',
			'post_title'  => sanitize_text_field( $args['novel_title'] ),
			'post_status' => 'publish',
			'post_author' => (int) $args['author_id'],
		) );
	}

	if ( ! $novel_id || is_wp_error( $novel_id ) || 'novel' !== get_post_type( $novel_id ) ) {
		return new WP_Error( 'xni_novel', __( 'Не выбран проект и не задано название нового.', 'xi-novel-import' ) );
	}

	$number  = $args['start'] > 0 ? (float) $args['start'] : xni_next_number( $novel_id );
	$created = 0;
	$updated = 0;
	$report  = array();

	foreach ( $chapters as $chapter ) {
		$num = null !== $chapter['number'] ? (float) $chapter['number'] : $number;

		$data = array(
			'post_type'    => 'chapter',
			'post_title'   => wp_strip_all_tags( $chapter['title'] ),
			'post_content' => wp_kses_post( $chapter['content'] ),
			'post_status'  => 'draft' === $args['status'] ? 'draft' : 'publish',
			'post_author'  => (int) $args['author_id'],
		);

		$existing = xni_find_chapter( $novel_id, $num );

		if ( $existing ) {
			$data['ID'] = $existing;
			wp_update_post( $data );
			$chapter_id = $existing;
			$updated++;
		} else {
			$chapter_id = wp_insert_post( $data );
			$created++;
		}

		if ( ! $chapter_id || is_wp_error( $chapter_id ) ) {
			continue;
		}

		update_post_meta( $chapter_id, '_xin_novel', $novel_id );
		update_post_meta( $chapter_id, '_xin_number', $num );

		if ( $args['locked_from'] > 0 && $num >= (float) $args['locked_from'] ) {
			update_post_meta( $chapter_id, '_xin_locked', 1 );
		}

		$report[] = array(
			'number' => $num,
			'title'  => $data['post_title'],
			'source' => $chapter['source'],
			'new'    => ! $existing,
		);

		$number = $num + 1;
	}

	delete_transient( 'xin_site_stats' );

	return array(
		'novel_id' => $novel_id,
		'created'  => $created,
		'updated'  => $updated,
		'report'   => $report,
	);
}
