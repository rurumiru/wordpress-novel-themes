<?php
/**
 * Управление площадкой без входа в wp-admin.
 *
 * Модератору нужны три вещи: разобрать очередь комментариев, выдать роль и
 * ответить на заявки в авторы. Ради этого гонять человека в админку с её
 * таблицами не стоит — там он ещё и увидит всё остальное, чего ему видеть
 * не нужно.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_manage_url( $args = array() ) {
	$base = xv_page_url( 'template-manage.php' );

	if ( ! $base ) {
		return '';
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Полный доступ к странице — у того, кто и так управляет сайтом.
 */
function xv_can_manage() {
	return current_user_can( 'promote_users' ) || current_user_can( 'manage_options' );
}

/**
 * Разбирать комментарии может и редактор — это его работа.
 */
function xv_can_moderate() {
	return current_user_can( 'moderate_comments' );
}

/**
 * Роли, которые здесь выдают. Администратора в списке нет намеренно: это
 * решение принимают не через витрину.
 */
function xv_manage_roles() {
	return array(
		'subscriber'  => __( 'Читатель', 'xin-v2' ),
		'author'      => __( 'Автор', 'xin-v2' ),
		'editor'      => __( 'Редактор', 'xin-v2' ),
	);
}

function xv_role_label( $role ) {
	$roles = xv_manage_roles();

	if ( isset( $roles[ $role ] ) ) {
		return $roles[ $role ];
	}

	if ( 'administrator' === $role ) {
		return __( 'Администратор', 'xin-v2' );
	}

	return $role ? $role : __( 'Без роли', 'xin-v2' );
}

/**
 * Очередь комментариев на проверке.
 */
function xv_manage_queue( $limit = 30 ) {
	return get_comments( array(
		'status'  => 'hold',
		'number'  => (int) $limit,
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
	) );
}

/**
 * Сводка: что ждёт решения.
 */
function xv_manage_stats() {
	$comments = wp_count_comments();

	return array(
		'hold'     => (int) $comments->moderated,
		'spam'     => (int) $comments->spam,
		'asks'     => count( xv_author_requests() ),
		'pending'  => (int) wp_count_posts( 'chapter' )->pending + (int) wp_count_posts( 'novel' )->pending,
		'users'    => (int) count_users()['total_users'],
	);
}

/* -------------------------------------------------------------------------
 * Заявки в авторы
 * ---------------------------------------------------------------------- */

const XV_ASK_META = '_xin_author_ask';

function xv_author_requests() {
	$users = get_users( array(
		'meta_key'     => XV_ASK_META,
		'meta_compare' => 'EXISTS',
		'number'       => 100,
		'fields'       => array( 'ID', 'display_name', 'user_email' ),
	) );

	$rows = array();

	foreach ( $users as $user ) {
		$ask = get_user_meta( $user->ID, XV_ASK_META, true );

		if ( ! is_array( $ask ) || empty( $ask['at'] ) ) {
			continue;
		}

		$rows[] = array(
			'id'    => (int) $user->ID,
			'name'  => $user->display_name,
			'email' => $user->user_email,
			'about' => isset( $ask['about'] ) ? $ask['about'] : '',
			'at'    => (int) $ask['at'],
		);
	}

	usort( $rows, function ( $a, $b ) {
		return $a['at'] <=> $b['at'];
	} );

	return $rows;
}

function xv_has_author_request( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id && is_array( get_user_meta( $user_id, XV_ASK_META, true ) );
}

/* -------------------------------------------------------------------------
 * Действия
 * ---------------------------------------------------------------------- */

function xv_manage_back( $error = '', $done = '' ) {
	$args = array();

	if ( $error ) {
		$args['xv_error'] = $error;
	}

	if ( $done ) {
		$args['xv_done'] = $done;
	}

	$url = xv_manage_url( $args );

	wp_safe_redirect( $url ? $url : home_url( '/' ) );
	exit;
}

function xv_handle_moderate() {
	check_admin_referer( 'xv_moderate' );

	if ( ! xv_can_moderate() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	$id = isset( $_POST['comment'] ) ? (int) $_POST['comment'] : 0;
	$do = isset( $_POST['do'] ) ? sanitize_key( wp_unslash( $_POST['do'] ) ) : '';

	if ( ! $id || ! get_comment( $id ) ) {
		xv_manage_back( 'no-comment' );
	}

	switch ( $do ) {
		case 'approve':
			wp_set_comment_status( $id, 'approve' );
			break;

		case 'spam':
			wp_spam_comment( $id );
			break;

		case 'trash':
			wp_trash_comment( $id );
			break;

		default:
			xv_manage_back( 'unknown' );
	}

	xv_manage_back( '', 'moderated' );
}
add_action( 'admin_post_xv_moderate', 'xv_handle_moderate' );

function xv_handle_role() {
	check_admin_referer( 'xv_role' );

	if ( ! xv_can_manage() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	$id   = isset( $_POST['user'] ) ? (int) $_POST['user'] : 0;
	$role = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : '';
	$user = $id ? get_userdata( $id ) : null;

	if ( ! $user || ! isset( xv_manage_roles()[ $role ] ) ) {
		xv_manage_back( 'bad-role' );
	}

	// Администратора отсюда не трогаем: понизить его через витрину нельзя.
	if ( user_can( $user, 'manage_options' ) ) {
		xv_manage_back( 'is-admin' );
	}

	if ( get_current_user_id() === $id ) {
		xv_manage_back( 'self' );
	}

	$user->set_role( $role );
	delete_user_meta( $id, XV_ASK_META );

	xv_manage_back( '', 'role-set' );
}
add_action( 'admin_post_xv_role', 'xv_handle_role' );

function xv_handle_ask() {
	check_admin_referer( 'xv_ask' );

	if ( ! is_user_logged_in() ) {
		auth_redirect();
	}

	$user_id = get_current_user_id();

	if ( user_can( $user_id, 'edit_posts' ) ) {
		wp_safe_redirect( xv_author_url() ? xv_author_url() : home_url( '/' ) );
		exit;
	}

	update_user_meta( $user_id, XV_ASK_META, array(
		'about' => isset( $_POST['about'] ) ? sanitize_textarea_field( wp_unslash( $_POST['about'] ) ) : '',
		'at'    => time(),
	) );

	$page = xv_page_url( 'template-become-author.php' );

	wp_safe_redirect( add_query_arg( 'xv_done', 'asked', $page ? $page : home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_xv_ask', 'xv_handle_ask' );

function xv_handle_ask_drop() {
	check_admin_referer( 'xv_ask_drop' );

	if ( ! xv_can_manage() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	$id = isset( $_POST['user'] ) ? (int) $_POST['user'] : 0;

	if ( $id ) {
		delete_user_meta( $id, XV_ASK_META );
	}

	xv_manage_back( '', 'ask-closed' );
}
add_action( 'admin_post_xv_ask_drop', 'xv_handle_ask_drop' );

/**
 * Сообщения страницы управления.
 */
function xv_manage_notice() {
	$errors = array(
		'no-comment' => __( 'Комментарий не найден.', 'xin-v2' ),
		'unknown'    => __( 'Непонятное действие.', 'xin-v2' ),
		'bad-role'   => __( 'Такую роль здесь не выдают.', 'xin-v2' ),
		'is-admin'   => __( 'Роль администратора меняют не отсюда.', 'xin-v2' ),
		'self'       => __( 'Свою роль менять нельзя.', 'xin-v2' ),
	);

	$done = array(
		'moderated'  => __( 'Готово.', 'xin-v2' ),
		'role-set'   => __( 'Роль изменена.', 'xin-v2' ),
		'ask-closed' => __( 'Заявка закрыта.', 'xin-v2' ),
	);

	foreach ( array( 'xv_error' => $errors, 'xv_done' => $done ) as $param => $list ) {
		if ( empty( $_GET[ $param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			continue;
		}

		$key = sanitize_key( wp_unslash( $_GET[ $param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( isset( $list[ $key ] ) ) {
			printf(
				'<p class="xv-notice %1$s">%2$s</p>',
				'xv_error' === $param ? 'is-error' : 'is-ok',
				esc_html( $list[ $key ] )
			);
		}
	}
}
