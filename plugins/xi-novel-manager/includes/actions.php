<?php
/**
 * Applying a bulk action to a set of novels.
 *
 * Every action goes through xnm_handle_request(): one nonce, one capability
 * check, one place that decides which ids are in play. The individual handlers
 * below only do the work.
 *
 * @package XI_Novel_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The list of actions the screen offers, grouped for the dropdown.
 */
function xnm_actions() {
	return array(
		__( 'Публикация', 'xi-novel-manager' ) => array(
			'publish' => __( 'Опубликовать', 'xi-novel-manager' ),
			'draft'   => __( 'В черновики', 'xi-novel-manager' ),
			'private' => __( 'Сделать личными', 'xi-novel-manager' ),
		),
		__( 'Состояние тайтла', 'xi-novel-manager' ) => array(
			'novel_status' => __( 'Поставить статус…', 'xi-novel-manager' ),
			'adult_on'     => __( 'Пометить 18+', 'xi-novel-manager' ),
			'adult_off'    => __( 'Снять 18+', 'xi-novel-manager' ),
		),
		__( 'Жанры и метки', 'xi-novel-manager' ) => array(
			'genre_add'     => __( 'Добавить жанры…', 'xi-novel-manager' ),
			'genre_remove'  => __( 'Убрать жанры…', 'xi-novel-manager' ),
			'genre_replace' => __( 'Заменить жанры на…', 'xi-novel-manager' ),
			'tag_add'       => __( 'Добавить метки…', 'xi-novel-manager' ),
			'tag_remove'    => __( 'Убрать метки…', 'xi-novel-manager' ),
			'tag_replace'   => __( 'Заменить метки на…', 'xi-novel-manager' ),
		),
		__( 'Авторство', 'xi-novel-manager' ) => array(
			'owner'      => __( 'Сменить владельца…', 'xi-novel-manager' ),
			'translator' => __( 'Поставить команду перевода…', 'xi-novel-manager' ),
		),
		__( 'Обложка и PLUS', 'xi-novel-manager' ) => array(
			'cover_set'    => __( 'Поставить обложку…', 'xi-novel-manager' ),
			'cover_remove' => __( 'Убрать обложку', 'xi-novel-manager' ),
			'plus_on'      => __( 'Все главы — ранний доступ PLUS', 'xi-novel-manager' ),
			'plus_off'     => __( 'Снять PLUS со всех глав', 'xi-novel-manager' ),
		),
		__( 'Прочее', 'xi-novel-manager' ) => array(
			'export'  => __( 'Выгрузить в CSV', 'xi-novel-manager' ),
			'trash'   => __( 'В корзину', 'xi-novel-manager' ),
			'restore' => __( 'Восстановить из корзины', 'xi-novel-manager' ),
			'delete'  => __( 'Удалить навсегда', 'xi-novel-manager' ),
		),
	);
}

/**
 * Actions that destroy something and therefore need the delete capability.
 */
function xnm_destructive() {
	return array( 'trash', 'delete' );
}

/**
 * Entry point, wired to the screen's load- hook so a redirect is still possible.
 */
function xnm_handle_request() {
	if ( empty( $_POST['xnm_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked below.
		return;
	}

	check_admin_referer( 'xnm_bulk' );

	if ( ! current_user_can( xnm_capability() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-manager' ) );
	}

	$action = sanitize_key( wp_unslash( $_POST['xnm_action'] ) );

	if ( in_array( $action, xnm_destructive(), true ) && ! current_user_can( 'delete_others_posts' ) ) {
		wp_die( esc_html__( 'Недостаточно прав на удаление.', 'xi-novel-manager' ) );
	}

	$ids = xnm_requested_ids();

	if ( ! $ids ) {
		wp_safe_redirect( xnm_url( array( 'xnm_msg' => 'none' ) ) );
		exit;
	}

	// The export writes a file and ends the request; it never redirects.
	if ( 'export' === $action ) {
		xnm_export_csv( $ids );
	}

	$done = xnm_apply( $action, $ids );

	wp_safe_redirect( xnm_url( array(
		'xnm_msg'  => 'done',
		'xnm_did'  => $action,
		'xnm_n'    => $done,
		'paged'    => '',
	) ) );
	exit;
}

/**
 * Which novels the action applies to: the ticked rows, or everything the current
 * filter matches when "выбрать все найденные" is on.
 */
function xnm_requested_ids() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- the caller verified the nonce.
	if ( ! empty( $_POST['xnm_all_matching'] ) ) {
		$ids = xnm_all_matching_ids( xnm_filters() );
	} else {
		$ids = isset( $_POST['xnm_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['xnm_ids'] ) ) : array();
	}
	// phpcs:enable

	$ids = array_filter( array_unique( $ids ) );

	// Never touch a post the current user may not edit, and never anything that
	// is not a novel — the ids arrive from the browser.
	return array_values( array_filter( $ids, static function ( $id ) {
		return 'novel' === get_post_type( $id ) && current_user_can( 'edit_post', $id );
	} ) );
}

/**
 * Runs one action over a set of novels and returns how many were changed.
 *
 * @param string $action Action key.
 * @param array  $ids    Novel ids, already filtered by capability.
 */
function xnm_apply( $action, $ids ) {
	$payload = xnm_payload();
	$done    = 0;

	foreach ( $ids as $id ) {
		if ( xnm_apply_one( $action, $id, $payload ) ) {
			$done++;
		}
	}

	if ( $done ) {
		xnm_forget_caches();
	}

	return $done;
}

/**
 * The extra field the chosen action needs — a term list, a user, an attachment.
 */
function xnm_payload() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- the caller verified the nonce.
	return array(
		'terms'      => isset( $_POST['xnm_terms'] ) ? sanitize_text_field( wp_unslash( $_POST['xnm_terms'] ) ) : '',
		'status'     => isset( $_POST['xnm_novel_status'] ) ? sanitize_title( wp_unslash( $_POST['xnm_novel_status'] ) ) : '',
		'owner'      => isset( $_POST['xnm_owner'] ) ? absint( $_POST['xnm_owner'] ) : 0,
		'translator' => isset( $_POST['xnm_translator'] ) ? sanitize_text_field( wp_unslash( $_POST['xnm_translator'] ) ) : '',
		'cover'      => isset( $_POST['xnm_cover_id'] ) ? absint( $_POST['xnm_cover_id'] ) : 0,
	);
	// phpcs:enable
}

/**
 * @param string $action  Action key.
 * @param int    $id      Novel id.
 * @param array  $payload Extra field for the action.
 * @return bool Whether anything changed.
 */
function xnm_apply_one( $action, $id, $payload ) {
	switch ( $action ) {
		case 'publish':
		case 'draft':
		case 'private':
			return (bool) wp_update_post( array( 'ID' => $id, 'post_status' => $action ) );

		case 'trash':
			return (bool) wp_trash_post( $id );

		case 'restore':
			return (bool) wp_untrash_post( $id );

		case 'delete':
			// Chapters are separate posts pointing back at the novel; leaving them
			// behind would strand them with no parent to reach them through.
			foreach ( xnm_chapter_ids( $id ) as $chapter ) {
				wp_delete_post( $chapter, true );
			}
			return (bool) wp_delete_post( $id, true );

		case 'novel_status':
			if ( ! $payload['status'] ) {
				return false;
			}
			return ! is_wp_error( wp_set_object_terms( $id, array( $payload['status'] ), 'novel_status', false ) );

		case 'adult_on':
			return (bool) update_post_meta( $id, '_xin_adult', '1' );

		case 'adult_off':
			return (bool) delete_post_meta( $id, '_xin_adult' );

		case 'genre_add':
			return xnm_terms( $id, 'genre', $payload['terms'], 'add' );
		case 'genre_remove':
			return xnm_terms( $id, 'genre', $payload['terms'], 'remove' );
		case 'genre_replace':
			return xnm_terms( $id, 'genre', $payload['terms'], 'replace' );
		case 'tag_add':
			return xnm_terms( $id, 'novel_tag', $payload['terms'], 'add' );
		case 'tag_remove':
			return xnm_terms( $id, 'novel_tag', $payload['terms'], 'remove' );
		case 'tag_replace':
			return xnm_terms( $id, 'novel_tag', $payload['terms'], 'replace' );

		case 'owner':
			if ( ! $payload['owner'] ) {
				return false;
			}
			return (bool) wp_update_post( array( 'ID' => $id, 'post_author' => $payload['owner'] ) );

		case 'translator':
			if ( '' === $payload['translator'] ) {
				return (bool) delete_post_meta( $id, '_xin_translator' );
			}
			return (bool) update_post_meta( $id, '_xin_translator', $payload['translator'] );

		case 'cover_set':
			if ( ! $payload['cover'] ) {
				return false;
			}
			return (bool) set_post_thumbnail( $id, $payload['cover'] );

		case 'cover_remove':
			return (bool) delete_post_thumbnail( $id );

		case 'plus_on':
		case 'plus_off':
			$on      = 'plus_on' === $action;
			$touched = false;
			foreach ( xnm_chapter_ids( $id ) as $chapter ) {
				if ( $on ) {
					update_post_meta( $chapter, '_xin_locked', '1' );
				} else {
					delete_post_meta( $chapter, '_xin_locked' );
				}
				$touched = true;
			}
			return $touched;
	}

	return false;
}

/**
 * Adds, removes or replaces terms on one novel.
 *
 * The list arrives as free text — comma separated names or slugs, the way a
 * person pastes them out of a spreadsheet. Names that do not exist yet are
 * created for hierarchical-free taxonomies, which is what genres and tags are.
 *
 * @param int    $id       Novel id.
 * @param string $taxonomy Taxonomy.
 * @param string $raw      Comma separated names or slugs.
 * @param string $mode     add | remove | replace
 */
function xnm_terms( $id, $taxonomy, $raw, $mode ) {
	$names = array_filter( array_map( 'trim', explode( ',', (string) $raw ) ) );

	if ( ! $names && 'replace' !== $mode ) {
		return false;
	}

	if ( 'remove' === $mode ) {
		$ids = array();
		foreach ( $names as $name ) {
			$term = xnm_find_term( $name, $taxonomy );
			if ( $term ) {
				$ids[] = (int) $term->term_id;
			}
		}
		if ( ! $ids ) {
			return false;
		}
		return ! is_wp_error( wp_remove_object_terms( $id, $ids, $taxonomy ) );
	}

	$ids = array();
	foreach ( $names as $name ) {
		$term = xnm_find_term( $name, $taxonomy );
		if ( ! $term ) {
			$made = wp_insert_term( $name, $taxonomy );
			if ( is_wp_error( $made ) ) {
				continue;
			}
			$ids[] = (int) $made['term_id'];
			continue;
		}
		$ids[] = (int) $term->term_id;
	}

	// append for add, overwrite for replace — an empty list with replace clears.
	return ! is_wp_error( wp_set_object_terms( $id, $ids, $taxonomy, 'add' === $mode ) );
}

/**
 * Finds a term by slug first, then by name, so both forms work in the field.
 */
function xnm_find_term( $value, $taxonomy ) {
	$term = get_term_by( 'slug', sanitize_title( $value ), $taxonomy );
	if ( $term ) {
		return $term;
	}
	return get_term_by( 'name', $value, $taxonomy );
}

/**
 * The theme keeps a few counters in transients; a bulk change makes them stale.
 */
function xnm_forget_caches() {
	delete_transient( 'xin_site_stats' );
	if ( function_exists( 'xin_bump_cache' ) ) {
		xin_bump_cache();
	}
}

/**
 * Writes the selection out as CSV and ends the request.
 *
 * @param array $ids Novel ids.
 */
function xnm_export_csv( $ids ) {
	$name = 'novels-' . gmdate( 'Y-m-d-Hi' ) . '.csv';

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $name . '"' );

	$out      = fopen( 'php://output', 'w' );
	$chapters = xnm_chapter_counts( $ids );

	// Excel opens a UTF-8 CSV as the system codepage unless the file says otherwise.
	fwrite( $out, "\xEF\xBB\xBF" );

	fputcsv( $out, array(
		'ID', 'Название', 'Оригинальное название', 'Ссылка', 'Владелец', 'Автор оригинала',
		'Команда перевода', 'Статус тайтла', 'Публикация', 'Жанры', 'Метки', 'Глав',
		'Просмотры', 'Оценка', 'Голосов', '18+', 'Обложка', 'Создан', 'Изменён',
	) );

	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}
		$rating = (float) get_post_meta( $id, '_xin_rating', true );
		fputcsv( $out, array(
			$id,
			get_the_title( $id ),
			(string) get_post_meta( $id, '_xin_original_title', true ),
			get_permalink( $id ),
			get_the_author_meta( 'display_name', $post->post_author ),
			(string) get_post_meta( $id, '_xin_author_name', true ),
			(string) get_post_meta( $id, '_xin_translator', true ),
			xnm_term_list( $id, 'novel_status' ),
			$post->post_status,
			xnm_term_list( $id, 'genre' ),
			xnm_term_list( $id, 'novel_tag' ),
			isset( $chapters[ $id ] ) ? $chapters[ $id ] : 0,
			(int) get_post_meta( $id, '_xin_views', true ),
			$rating ? number_format( $rating, 2, '.', '' ) : '',
			(int) get_post_meta( $id, '_xin_rating_count', true ),
			get_post_meta( $id, '_xin_adult', true ) ? 'да' : '',
			has_post_thumbnail( $id ) ? 'есть' : '',
			get_the_date( 'Y-m-d H:i', $id ),
			get_the_modified_date( 'Y-m-d H:i', $id ),
		) );
	}

	fclose( $out );
	exit;
}

/**
 * Term names of one taxonomy as a comma separated string.
 */
function xnm_term_list( $id, $taxonomy ) {
	$terms = get_the_terms( $id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	return implode( ', ', wp_list_pluck( $terms, 'name' ) );
}
