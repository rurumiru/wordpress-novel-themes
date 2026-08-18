<?php
/**
 * Панель управления площадкой прямо на сайте.
 *
 * Роли, доступ PLUS, модерация и настройки темы — без захода в /wp-admin.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_MANAGE_SLUG = 'manage';
const XIN_PLUS_META   = '_xin_plus_until';

function xin_manage_page() {
	static $page = null;
	if ( null === $page ) {
		$found = get_page_by_path( XIN_MANAGE_SLUG );
		$page  = $found ? $found : false;
	}
	return $page;
}

function xin_manage_url( $args = array() ) {
	$page = xin_manage_page();
	$url  = $page ? get_permalink( $page ) : home_url( '/' . XIN_MANAGE_SLUG . '/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

function xin_can_manage() {
	return current_user_can( 'manage_options' );
}

function xin_can_moderate() {
	return current_user_can( 'edit_others_posts' );
}

function xin_manage_roles() {
	return array(
		'subscriber'  => __( 'Читатель', 'xi-novels' ),
		'contributor' => __( 'Участник', 'xi-novels' ),
		'author'      => __( 'Автор', 'xi-novels' ),
		'editor'      => __( 'Модератор', 'xi-novels' ),
	);
}

function xin_role_label( $user ) {
	$roles = xin_manage_roles();
	$role  = $user->roles ? reset( $user->roles ) : '';

	if ( 'administrator' === $role ) {
		return __( 'Администратор', 'xi-novels' );
	}

	return isset( $roles[ $role ] ) ? $roles[ $role ] : $role;
}

function xin_plus_until( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return $user_id ? (int) get_user_meta( $user_id, XIN_PLUS_META, true ) : 0;
}

function xin_user_is_plus( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'edit_others_posts' ) ) {
		return true;
	}

	$until = xin_plus_until( $user_id );

	return -1 === $until || ( $until > 0 && $until > time() );
}

function xin_plus_label( $user_id ) {
	$until = xin_plus_until( $user_id );

	if ( -1 === $until ) {
		return __( 'Бессрочно', 'xi-novels' );
	}
	if ( $until > time() ) {
		/* translators: %s: date */
		return sprintf( __( 'до %s', 'xi-novels' ), date_i18n( get_option( 'date_format' ), $until ) );
	}
	if ( $until > 0 ) {
		return __( 'истёк', 'xi-novels' );
	}

	return '';
}

function xin_set_plus( $user_id, $days ) {
	$days = (int) $days;

	if ( 0 === $days ) {
		delete_user_meta( $user_id, XIN_PLUS_META );
		return 0;
	}
	if ( $days < 0 ) {
		update_user_meta( $user_id, XIN_PLUS_META, -1 );
		return -1;
	}

	$from  = xin_plus_until( $user_id );
	$start = $from > time() ? $from : time();
	$until = $start + $days * DAY_IN_SECONDS;
	update_user_meta( $user_id, XIN_PLUS_META, $until );

	return $until;
}

function xin_manage_stats() {
	$counts = count_users();

	return array(
		'users'    => (int) $counts['total_users'],
		'novels'   => (int) wp_count_posts( 'novel' )->publish,
		'chapters' => (int) wp_count_posts( 'chapter' )->publish,
		'pending'  => (int) wp_count_posts( 'novel' )->pending + (int) wp_count_posts( 'chapter' )->pending,
		'plus'     => count( get_users( array(
			'meta_key'     => XIN_PLUS_META,
			'meta_compare' => 'EXISTS',
			'fields'       => 'ID',
			'number'       => 500,
		) ) ),
	);
}

function xin_manage_back( $args ) {
	wp_safe_redirect( xin_manage_url( $args ) );
	exit;
}

function xin_manage_router() {
	$action = isset( $_POST['xin_manage'] ) ? sanitize_key( wp_unslash( $_POST['xin_manage'] ) ) : '';
	if ( ! $action ) {
		return;
	}

	$tab   = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'overview';
	$back  = array( 'tab' => $tab );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

	if ( ! is_user_logged_in() || ! xin_can_moderate() ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'denied' ) ) );
	}
	if ( ! wp_verify_nonce( $nonce, 'xin_manage_' . $action ) ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'expired' ) ) );
	}

	switch ( $action ) {
		case 'role':
			xin_manage_do_role( $back );
			break;
		case 'plus':
			xin_manage_do_plus( $back );
			break;
		case 'moderate':
			xin_manage_do_moderate( $back );
			break;
		case 'settings':
			xin_manage_do_settings( $back );
			break;
	}
}
add_action( 'template_redirect', 'xin_manage_router', 4 );

function xin_manage_do_role( $back ) {
	if ( ! xin_can_manage() ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'denied' ) ) );
	}

	$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
	$role    = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : '';
	$user    = $user_id ? get_userdata( $user_id ) : false;

	if ( ! $user || ! isset( xin_manage_roles()[ $role ] ) ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'nope' ) ) );
	}
	if ( $user_id === get_current_user_id() || user_can( $user_id, 'manage_options' ) ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'protected' ) ) );
	}

	$user->set_role( $role );
	xin_manage_back( array_merge( $back, array( 'msg' => 'role-set' ) ) );
}

function xin_manage_do_plus( $back ) {
	$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
	$days    = isset( $_POST['days'] ) ? (int) $_POST['days'] : 0;

	if ( ! $user_id || ! get_userdata( $user_id ) ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'nope' ) ) );
	}

	xin_set_plus( $user_id, $days );
	xin_manage_back( array_merge( $back, array( 'msg' => 0 === $days ? 'plus-off' : 'plus-on' ) ) );
}

function xin_manage_do_moderate( $back ) {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$what    = isset( $_POST['what'] ) ? sanitize_key( wp_unslash( $_POST['what'] ) ) : '';
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post || ! in_array( $post->post_type, array( 'novel', 'chapter' ), true ) || ! current_user_can( 'edit_post', $post_id ) ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'nope' ) ) );
	}

	switch ( $what ) {
		case 'publish':
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
			$msg = 'published';
			break;
		case 'draft':
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
			$msg = 'drafted';
			break;
		case 'trash':
			wp_trash_post( $post_id );
			$msg = 'trashed';
			break;
		default:
			$msg = 'nope';
	}

	delete_transient( 'xin_site_stats' );
	xin_manage_back( array_merge( $back, array( 'msg' => $msg ) ) );
}

function xin_manage_do_settings( $back ) {
	if ( ! xin_can_manage() ) {
		xin_manage_back( array_merge( $back, array( 'msg' => 'denied' ) ) );
	}

	set_theme_mod( 'xin_open_registration', ! empty( $_POST['open_registration'] ) );
	set_theme_mod( 'xin_discussions', ! empty( $_POST['discussions'] ) );

	$role = isset( $_POST['new_user_role'] ) ? sanitize_key( wp_unslash( $_POST['new_user_role'] ) ) : 'author';
	if ( in_array( $role, array( 'subscriber', 'contributor', 'author' ), true ) ) {
		set_theme_mod( 'xin_new_user_role', $role );
	}

	$lang = isset( $_POST['default_lang'] ) ? sanitize_key( wp_unslash( $_POST['default_lang'] ) ) : 'ru';
	if ( isset( xin_languages()[ $lang ] ) ) {
		set_theme_mod( 'xin_default_lang', $lang );
	}

	$scheme = isset( $_POST['default_scheme'] ) ? sanitize_key( wp_unslash( $_POST['default_scheme'] ) ) : 'light';
	if ( in_array( $scheme, array( 'light', 'dark', 'auto' ), true ) ) {
		set_theme_mod( 'xin_default_scheme', $scheme );
	}

	xin_manage_back( array_merge( $back, array( 'msg' => 'saved' ) ) );
}

function xin_manage_notice() {
	$msg = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '';
	if ( ! $msg ) {
		return;
	}

	$map = array(
		'role-set'  => array( 'ok', __( 'Роль изменена.', 'xi-novels' ) ),
		'plus-on'   => array( 'ok', __( 'Доступ PLUS выдан.', 'xi-novels' ) ),
		'plus-off'  => array( 'ok', __( 'Доступ PLUS снят.', 'xi-novels' ) ),
		'published' => array( 'ok', __( 'Опубликовано.', 'xi-novels' ) ),
		'drafted'   => array( 'ok', __( 'Убрано в черновики.', 'xi-novels' ) ),
		'trashed'   => array( 'ok', __( 'Перенесено в корзину.', 'xi-novels' ) ),
		'saved'     => array( 'ok', __( 'Настройки сохранены.', 'xi-novels' ) ),
		'denied'    => array( 'err', __( 'Недостаточно прав для этого действия.', 'xi-novels' ) ),
		'expired'   => array( 'err', __( 'Форма была открыта слишком долго. Обновите страницу и повторите.', 'xi-novels' ) ),
		'protected' => array( 'err', __( 'Эту учётную запись менять нельзя: администраторы и собственная роль защищены.', 'xi-novels' ) ),
		'nope'      => array( 'err', __( 'Не получилось: запись или пользователь не найдены.', 'xi-novels' ) ),
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

function xin_manage_queue( $limit = 30 ) {
	return get_posts( array(
		'post_type'        => array( 'novel', 'chapter' ),
		'post_status'      => 'pending',
		'posts_per_page'   => $limit,
		'orderby'          => 'date',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
}
