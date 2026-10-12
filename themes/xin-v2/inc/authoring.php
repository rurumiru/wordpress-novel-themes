<?php
/**
 * Кабинет автора: работа с тайтлами и главами без входа в админку.
 *
 * Все действия проходят через один обработчик на `admin_post_*`: так проверка
 * прав и nonce живёт в одном месте, а шаблоны остаются разметкой.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Может ли пользователь вести работы. Роль «автор» в WordPress уже это умеет —
 * отдельной роли не заводим.
 */
function xv_can_author( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id && user_can( $user_id, 'edit_posts' );
}

function xv_author_novels( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$args = array(
		'post_type'      => 'novel',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	// Редактор видит всё — иначе он не может помочь с чужим тайтлом.
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$args['author'] = $user_id;
	}

	return get_posts( $args );
}

function xv_author_stats( $user_id = 0 ) {
	$novels   = xv_author_novels( $user_id );
	$chapters = 0;
	$views    = 0;

	foreach ( $novels as $novel ) {
		$chapters += (int) get_post_meta( $novel->ID, '_xin_chapter_count', true );
		$views    += xv_get_views( $novel->ID );
	}

	return array(
		'novels'   => count( $novels ),
		'chapters' => $chapters,
		'views'    => $views,
	);
}

/** Studio lists drafts and scheduled chapters; public contents remain publish-only. */
function xv_author_chapters( $novel_id ) {
	if(!current_user_can('edit_post',$novel_id))return array();
	return xv_posts_in_order(xv_chapter_index($novel_id,true));
}

/**
 * Адрес кабинета с параметрами: ?work=12 открывает список глав тайтла.
 *
 * Параметры намеренно не `novel`/`chapter`: это query-переменные самих типов
 * записей, и WordPress на них искал тайтл с именем «12» и отдавал 404.
 */
function xv_author_url( $args = array() ) {
	$base = xv_page_url( 'template-author.php' );

	if ( ! $base ) {
		return '';
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/* -------------------------------------------------------------------------
 * Обработка форм
 * ---------------------------------------------------------------------- */

/**
 * Состояние и дата выхода из формы кабинета.
 *
 * «По расписанию» без даты — это черновик, который автор считает
 * запланированным: WordPress опубликовал бы его сразу. Прошедшая дата —
 * тоже не расписание, а обычная публикация задним числом.
 */
function xv_requested_schedule() {
	$asked = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'draft'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( ! in_array( $asked, array( 'publish', 'future' ), true ) || ! current_user_can( 'publish_posts' ) ) {
		return array( 'post_status' => 'draft' );
	}

	if ( 'publish' === $asked ) {
		return array( 'post_status' => 'publish' );
	}

	$when = isset( $_POST['publish_at'] ) ? sanitize_text_field( wp_unslash( $_POST['publish_at'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$time = $when ? strtotime( $when ) : 0;

	if ( ! $time ) {
		return array( 'post_status' => 'draft' );
	}

	if ( $time <= time() ) {
		return array(
			'post_status'   => 'publish',
			'post_date'     => wp_date( 'Y-m-d H:i:s', $time ),
			'post_date_gmt' => get_gmt_from_date( wp_date( 'Y-m-d H:i:s', $time ) ),
		);
	}

	return array(
		'post_status'   => 'future',
		'post_date'     => wp_date( 'Y-m-d H:i:s', $time ),
		'post_date_gmt' => get_gmt_from_date( wp_date( 'Y-m-d H:i:s', $time ) ),
	);
}
function xv_handle_novel_save() {
	xv_guard( 'xv_save_novel' );

	$id    = isset( $_POST['novel_id'] ) ? (int) $_POST['novel_id'] : 0;
	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

	if ( '' === $title ) {
		xv_back( array( 'work' => $id ), 'empty-title' );
	}

	$data = array(
		'post_type'    => 'novel',
		'post_title'   => $title,
		'post_content' => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
	);

	$data = array_merge( $data, xv_requested_schedule() );

	if ( $id ) {
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
		}

		$data['ID'] = $id;
		wp_update_post( $data );
	} else {
		$data['post_author'] = get_current_user_id();
		$id                  = wp_insert_post( $data );

		if ( is_wp_error( $id ) || ! $id ) {
			xv_back( array(), 'save-failed' );
		}
	}

	foreach ( array( '_xin_author_name', '_xin_translator', '_xin_year', '_xin_country' ) as $key ) {
		$field = ltrim( str_replace( '_xin_', '', $key ), '_' );

		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	// Команду правит только владелец тайтла и редакторы: иначе соавтор
	// мог бы переписать состав и выставить остальных.
	if ( isset( $_POST['team'] ) && ( current_user_can( 'edit_others_posts' ) || (int) get_post_field( 'post_author', $id ) === get_current_user_id() ) ) {
		xv_set_novel_team( $id, sanitize_text_field( wp_unslash( $_POST['team'] ) ) );
	}

	if ( isset( $_POST['genres'] ) ) {
		wp_set_object_terms( $id, array_map( 'intval', (array) $_POST['genres'] ), 'genre' );
	}

	if ( isset( $_POST['novel_status'] ) ) {
		wp_set_object_terms( $id, array( (int) $_POST['novel_status'] ), 'novel_status' );
	}

	if ( isset( $_POST['tags'] ) ) {
		$tags = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) ) ) );
		wp_set_object_terms( $id, $tags, 'novel_tag' );
	}

	if ( ! empty( $_FILES['cover']['name'] ) ) {
		xv_attach_cover( $id, 'cover' );
	}

	xv_back( array( 'work' => $id ), '', 'saved' );
}
add_action( 'admin_post_xv_save_novel', 'xv_handle_novel_save' );

function xv_handle_chapter_save() {
	xv_guard( 'xv_save_chapter' );

	$id    = isset( $_POST['chapter_id'] ) ? (int) $_POST['chapter_id'] : 0;
	$novel = isset( $_POST['novel_id'] ) ? (int) $_POST['novel_id'] : 0;
	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

	if ( ! $novel || ! current_user_can( 'edit_post', $novel ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	if ( '' === $title ) {
		xv_back( array( 'work' => $novel, 'part' => $id ), 'empty-title' );
	}

	$data = array(
		'post_type'    => 'chapter',
		'post_title'   => $title,
		'post_content' => isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '',
	);

	$data = array_merge( $data, xv_requested_schedule() );

	if ( $id ) {
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
		}

		$data['ID'] = $id;
		wp_update_post( $data );
	} else {
		$data['post_author'] = get_current_user_id();
		$id                  = wp_insert_post( $data );

		if ( is_wp_error( $id ) || ! $id ) {
			xv_back( array( 'work' => $novel ), 'save-failed' );
		}
	}

	update_post_meta( $id, '_xin_novel', $novel );

	// Номер по умолчанию — следующий свободный: автору не приходится
	// помнить, на чём он остановился.
	$number = isset( $_POST['number'] ) ? sanitize_text_field( wp_unslash( $_POST['number'] ) ) : '';

	if ( '' === $number ) {
		$number = (string) max(1,count( xv_author_chapters( $novel ) ));
	}

	update_post_meta( $id, '_xin_number', $number );

	if ( ! empty( $_POST['locked'] ) ) {
		update_post_meta( $id, '_xin_locked', '1' );
		update_post_meta( $id, '_xin_price', isset( $_POST['price'] ) ? (int) $_POST['price'] : 0 );

		$unlock = isset( $_POST['unlock_at'] ) ? sanitize_text_field( wp_unslash( $_POST['unlock_at'] ) ) : '';

		if ( $unlock ) {
			update_post_meta( $id, '_xin_unlock_at', (int) get_gmt_from_date( $unlock, 'U' ) );
		} else {
			delete_post_meta( $id, '_xin_unlock_at' );
		}
	} else {
		delete_post_meta( $id, '_xin_locked' );
		delete_post_meta( $id, '_xin_unlock_at' );
	}

	xv_back( array( 'work' => $novel ), '', 'saved' );
}
add_action( 'admin_post_xv_save_chapter', 'xv_handle_chapter_save' );

function xv_handle_delete() {
	xv_guard( 'xv_delete' );

	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	if ( ! $id || ! current_user_can( 'delete_post', $id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	$novel = 'chapter' === get_post_type( $id ) ? (int) get_post_meta( $id, '_xin_novel', true ) : 0;

	wp_trash_post( $id );

	xv_back( $novel ? array( 'work' => $novel ) : array(), '', 'deleted' );
}
add_action( 'admin_post_xv_delete', 'xv_handle_delete' );

/**
 * Общая проверка: вход, право писать и nonce.
 */
function xv_guard( $action ) {
	if ( ! is_user_logged_in() ) {
		auth_redirect();
	}

	if ( ! xv_can_author() ) {
		wp_die( esc_html__( 'Кабинет доступен авторам.', 'xin-v2' ), 403 );
	}

	check_admin_referer( $action );
}

function xv_back( $args = array(), $error = '', $done = '' ) {
	$url = xv_author_url( $args );

	if ( $error ) {
		$url = add_query_arg( 'xv_error', $error, $url );
	}

	if ( $done ) {
		$url = add_query_arg( 'xv_done', $done, $url );
	}

	wp_safe_redirect( $url ? $url : home_url( '/' ) );
	exit;
}

/**
 * Загрузка обложки. Требует media-функций, которых нет на фронте.
 */
function xv_attach_cover( $post_id, $field ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment = media_handle_upload( $field, $post_id );

	if ( is_wp_error( $attachment ) ) {
		return false;
	}

	set_post_thumbnail( $post_id, $attachment );

	return true;
}

/**
 * Сообщения кабинета.
 */
function xv_author_notice() {
	$errors = array(
		'empty-title'  => __( 'Нужно название.', 'xin-v2' ),
		'save-failed'  => __( 'Не удалось сохранить. Попробуйте ещё раз.', 'xin-v2' ),
	);

	$done = array(
		'saved'   => __( 'Сохранено.', 'xin-v2' ),
		'deleted' => __( 'Удалено в корзину.', 'xin-v2' ),
	);

	if ( ! empty( $_GET['xv_error'] ) ) {
		$key = sanitize_key( wp_unslash( $_GET['xv_error'] ) );

		if ( isset( $errors[ $key ] ) ) {
			printf( '<p class="xv-notice is-error">%s</p>', esc_html( $errors[ $key ] ) );
		}
	}

	if ( ! empty( $_GET['xv_done'] ) ) {
		$key = sanitize_key( wp_unslash( $_GET['xv_done'] ) );

		if ( isset( $done[ $key ] ) ) {
			printf( '<p class="xv-notice is-ok">%s</p>', esc_html( $done[ $key ] ) );
		}
	}
}
