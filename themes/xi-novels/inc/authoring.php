<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_dashboard_url( $args = array() ) {
	$page = get_page_by_path( 'dashboard' );
	$url  = $page ? get_permalink( $page ) : home_url( '/dashboard/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

function xin_can_author() {
	return is_user_logged_in() && current_user_can( 'edit_posts' );
}

function xin_owns( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}
	return current_user_can( 'edit_others_posts' ) || (int) $post->post_author === get_current_user_id();
}

function xin_user_novels( $user_id = 0, $limit = -1 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return get_posts( array(
		'post_type'      => 'novel',
		'author'         => $user_id,
		'posts_per_page' => $limit,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'orderby'        => 'modified',
		'order'          => 'DESC',
	) );
}

function xin_redirect_back( $args ) {
	wp_safe_redirect( xin_dashboard_url( $args ) );
	exit;
}

function xin_handle_save_novel() {
	check_admin_referer( 'xin_save_novel' );

	if ( ! xin_can_author() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novels' ) );
	}

	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;
	if ( $novel_id && ! xin_owns( $novel_id ) ) {
		wp_die( esc_html__( 'Это не ваш проект.', 'xi-novels' ) );
	}

	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	if ( ! $title ) {
		xin_redirect_back( array( 'view' => 'novels', 'msg' => 'no-title' ) );
	}

$status = current_user_can( 'publish_posts' )
		? ( isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'publish' )
		: 'pending';

	$data = array(
		'post_type'    => 'novel',
		'post_title'   => $title,
		'post_excerpt' => isset( $_POST['synopsis'] ) ? sanitize_textarea_field( wp_unslash( $_POST['synopsis'] ) ) : '',
		'post_content' => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
		'post_status'  => $status,
	);

	if ( $novel_id ) {
		$data['ID'] = $novel_id;
		wp_update_post( $data );
	} else {
		$data['post_author'] = get_current_user_id();
		$novel_id            = wp_insert_post( $data );
	}

	if ( is_wp_error( $novel_id ) || ! $novel_id ) {
		xin_redirect_back( array( 'view' => 'novels', 'msg' => 'error' ) );
	}

$genres = isset( $_POST['genres'] ) ? array_map( 'absint', (array) $_POST['genres'] ) : array();
	wp_set_object_terms( $novel_id, $genres, 'genre' );

	if ( isset( $_POST['status_term'] ) && $_POST['status_term'] ) {
		wp_set_object_terms( $novel_id, sanitize_title( wp_unslash( $_POST['status_term'] ) ), 'novel_status' );
	}

	if ( isset( $_POST['tags'] ) ) {
		$tags = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) ) ) );
		wp_set_object_terms( $novel_id, $tags, 'novel_tag' );
	}

$meta = array(
		'_xin_author_name'    => 'author_name',
		'_xin_original_title' => 'original_title',
		'_xin_translator'     => 'translator',
	);
	foreach ( $meta as $key => $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $novel_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	if ( isset( $_POST['year'] ) ) {
		update_post_meta( $novel_id, '_xin_year', absint( $_POST['year'] ) );
	}
	update_post_meta( $novel_id, '_xin_adult', isset( $_POST['adult'] ) ? 1 : 0 );

require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( ! empty( $_FILES['cover']['name'] ) ) {
		$cover = media_handle_upload( 'cover', $novel_id );
		if ( ! is_wp_error( $cover ) ) {
			set_post_thumbnail( $novel_id, $cover );
		}
	}
	if ( ! empty( $_FILES['artwork']['name'] ) ) {
		$art = media_handle_upload( 'artwork', $novel_id );
		if ( ! is_wp_error( $art ) ) {
			update_post_meta( $novel_id, '_xin_background', $art );
		}
	}

	delete_transient( 'xin_site_stats' );
	xin_redirect_back( array( 'view' => 'chapters', 'project' => $novel_id, 'msg' => 'novel-saved' ) );
}
add_action( 'admin_post_xin_save_novel', 'xin_handle_save_novel' );

function xin_handle_save_chapter() {
	check_admin_referer( 'xin_save_chapter' );

	if ( ! xin_can_author() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novels' ) );
	}

	$chapter_id = isset( $_POST['chapter_id'] ) ? absint( $_POST['chapter_id'] ) : 0;
	$novel_id   = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;

	if ( $chapter_id && ! xin_owns( $chapter_id ) ) {
		wp_die( esc_html__( 'Это не ваша глава.', 'xi-novels' ) );
	}
	if ( ! $novel_id || ! xin_owns( $novel_id ) ) {
		wp_die( esc_html__( 'Проект не найден.', 'xi-novels' ) );
	}

	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	if ( ! $title ) {
		xin_redirect_back( array( 'view' => 'chapters', 'project' => $novel_id, 'msg' => 'no-title' ) );
	}

	$status = current_user_can( 'publish_posts' )
		? ( isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'publish' )
		: 'pending';

$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
	if ( $content && false === strpos( $content, '<p' ) ) {
		$content = wpautop( $content );
	}

	$data = array(
		'post_type'    => 'chapter',
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => $status,
	);

	if ( $chapter_id ) {
		$data['ID'] = $chapter_id;
		wp_update_post( $data );
	} else {
		$data['post_author'] = get_current_user_id();
		$chapter_id          = wp_insert_post( $data );
	}

	if ( is_wp_error( $chapter_id ) || ! $chapter_id ) {
		xin_redirect_back( array( 'view' => 'chapters', 'project' => $novel_id, 'msg' => 'error' ) );
	}

	update_post_meta( $chapter_id, '_xin_novel', $novel_id );

if ( isset( $_POST['number'] ) && '' !== $_POST['number'] ) {
		update_post_meta( $chapter_id, '_xin_number', (float) $_POST['number'] );
	} elseif ( ! get_post_meta( $chapter_id, '_xin_number', true ) ) {
		$last = xin_last_chapter( $novel_id );
		update_post_meta( $chapter_id, '_xin_number', $last ? (float) xin_chapter_number( $last->ID ) + 1 : 1 );
	}

	update_post_meta( $chapter_id, '_xin_locked', isset( $_POST['locked'] ) ? 1 : 0 );

wp_update_post( array( 'ID' => $novel_id, 'post_modified' => current_time( 'mysql' ) ) );

	wp_cache_flush();
	delete_transient( 'xin_site_stats' );

	$next = isset( $_POST['and_new'] ) && $_POST['and_new']
		? array( 'view' => 'new-chapter', 'project' => $novel_id, 'msg' => 'chapter-saved' )
		: array( 'view' => 'chapters', 'project' => $novel_id, 'msg' => 'chapter-saved' );
	xin_redirect_back( $next );
}
add_action( 'admin_post_xin_save_chapter', 'xin_handle_save_chapter' );

function xin_handle_delete() {
	check_admin_referer( 'xin_delete' );

	$id = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
	if ( ! $id || ! xin_owns( $id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novels' ) );
	}

	$type     = get_post_type( $id );
	$novel_id = 'chapter' === $type ? xin_chapter_novel_id( $id ) : 0;

	wp_trash_post( $id );
	wp_cache_flush();

	xin_redirect_back(
		'chapter' === $type
			? array( 'view' => 'chapters', 'project' => $novel_id, 'msg' => 'deleted' )
			: array( 'view' => 'novels', 'msg' => 'deleted' )
	);
}
add_action( 'admin_post_xin_delete', 'xin_handle_delete' );

function xin_dashboard_notice() {
	$msg = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '';
	if ( ! $msg ) {
		return;
	}

	$map = array(
		'novel-saved'   => array( 'ok', __( 'Проект сохранён.', 'xi-novels' ) ),
		'chapter-saved' => array( 'ok', __( 'Глава сохранена.', 'xi-novels' ) ),
		'deleted'       => array( 'ok', __( 'Удалено — запись в корзине.', 'xi-novels' ) ),
		'no-title'      => array( 'err', __( 'Нужно название.', 'xi-novels' ) ),
		'error'         => array( 'err', __( 'Не удалось сохранить. Попробуйте ещё раз.', 'xi-novels' ) ),
	);
	if ( ! isset( $map[ $msg ] ) ) {
		return;
	}

	printf(
		'<div class="xin-notice xin-notice--%s">%s<span>%s</span></div>',
		esc_attr( $map[ $msg ][0] ),
		xin_icon( 'ok' === $map[ $msg ][0] ? 'check' : 'close' ), 
		esc_html( $map[ $msg ][1] )
	);
}

function xin_create_pages() {
	$pages = array(
		'dashboard' => array( __( 'Кабинет автора', 'xi-novels' ), 'template-dashboard.php' ),
		'library'   => array( __( 'Моя библиотека', 'xi-novels' ), 'template-library.php' ),
	);

	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $data[0],
			'post_name'    => $slug,
			'post_content' => '',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $data[1] );
		}
	}
}
add_action( 'after_switch_theme', 'xin_create_pages' );

function xin_library_url() {
	$page = get_page_by_path( 'library' );
	return $page ? get_permalink( $page ) : home_url( '/library/' );
}

function xin_author_stats( $user_id ) {
	$novels = get_posts( array(
		'post_type'      => 'novel',
		'author'         => $user_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	$views    = 0;
	$chapters = 0;
	foreach ( $novels as $id ) {
		$views    += xin_get_views( $id );
		$chapters += xin_chapter_count( $id );
	}

	return array(
		'novels'   => count( $novels ),
		'chapters' => $chapters,
		'views'    => $views,
		'posts'    => (int) count_user_posts( $user_id, 'post' ),
	);
}
