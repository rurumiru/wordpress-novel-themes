<?php
/**
 * Вход, регистрация и восстановление пароля на самой площадке.
 *
 * Форма wp-login.php остаётся рабочей, но читателя туда больше не отправляют:
 * она выглядит как чужой сайт и подписана «WordPress».
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_AUTH_SLUG = 'account';

function xin_auth_page() {
	static $page = null;
	if ( null === $page ) {
		$found = get_page_by_path( XIN_AUTH_SLUG );
		$page  = $found ? $found : false;
	}
	return $page;
}

function xin_auth_url( $args = array() ) {
	$page = xin_auth_page();
	$url  = $page ? get_permalink( $page ) : wp_login_url();
	return $args ? add_query_arg( $args, $url ) : $url;
}

function xin_login_url( $redirect = '' ) {
	if ( ! xin_auth_page() ) {
		return $redirect ? wp_login_url( $redirect ) : wp_login_url();
	}
	return xin_auth_url( $redirect ? array( 'redirect_to' => $redirect ) : array() );
}

function xin_register_url( $redirect = '' ) {
	if ( ! xin_auth_page() ) {
		return wp_registration_url();
	}
	$args = array( 'view' => 'register' );
	if ( $redirect ) {
		$args['redirect_to'] = $redirect;
	}
	return xin_auth_url( $args );
}

function xin_lost_url() {
	return xin_auth_page() ? xin_auth_url( array( 'view' => 'lost' ) ) : wp_lostpassword_url();
}

function xin_registration_open() {
	if ( get_option( 'users_can_register' ) ) {
		return true;
	}
	return (bool) get_theme_mod( 'xin_open_registration', true );
}

function xin_new_user_role() {
	$role  = get_theme_mod( 'xin_new_user_role', 'author' );
	$roles = array( 'subscriber', 'contributor', 'author' );
	return in_array( $role, $roles, true ) ? $role : 'author';
}

function xin_current_url() {
	global $wp;
	$path = isset( $wp->request ) ? $wp->request : '';
	return $path ? home_url( user_trailingslashit( $path ) ) : home_url( '/' );
}

function xin_auth_context() {
	if ( is_admin() || wp_doing_ajax() ) {
		return false;
	}
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return false;
	}
	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return false;
	}
	return (bool) xin_auth_page();
}

function xin_filter_login_url( $url, $redirect = '' ) {
	return xin_auth_context() ? xin_login_url( $redirect ) : $url;
}
add_filter( 'login_url', 'xin_filter_login_url', 10, 2 );

function xin_filter_register_url( $url ) {
	return xin_auth_context() ? xin_register_url() : $url;
}
add_filter( 'register_url', 'xin_filter_register_url' );

function xin_filter_lostpassword_url( $url ) {
	return xin_auth_context() ? xin_lost_url() : $url;
}
add_filter( 'lostpassword_url', 'xin_filter_lostpassword_url' );

function xin_auth_target() {
	$to = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';
	if ( ! $to ) {
		return '';
	}

	$to   = wp_validate_redirect( $to, '' );
	$page = xin_auth_page();
	if ( $to && $page && untrailingslashit( wp_parse_url( $to, PHP_URL_PATH ) ) === untrailingslashit( wp_parse_url( get_permalink( $page ), PHP_URL_PATH ) ) ) {
		return '';
	}

	return $to;
}

function xin_after_login_url( $user ) {
	if ( $user instanceof WP_User && user_can( $user, 'edit_posts' ) ) {
		return xin_dashboard_url();
	}
	return xin_library_url();
}

function xin_auth_back( $args ) {
	wp_safe_redirect( xin_auth_url( $args ) );
	exit;
}

function xin_auth_field( $key ) {
	return isset( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : '';
}

function xin_auth_secret( $key ) {
	return isset( $_POST[ $key ] ) ? (string) wp_unslash( $_POST[ $key ] ) : '';
}

function xin_auth_attempts( $step = 0 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'none';
	$key = 'xin_auth_' . md5( $ip );

	if ( $step < 0 ) {
		delete_transient( $key );
		return 0;
	}

	$count = (int) get_transient( $key );
	if ( $step > 0 ) {
		$count += $step;
		set_transient( $key, $count, 15 * MINUTE_IN_SECONDS );
	}

	return $count;
}

function xin_auth_blocked() {
	return xin_auth_attempts() >= 12;
}

function xin_auth_nonce( $action, $back ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'expired' ) ) );
	}
}

function xin_auth_router() {
	$action = isset( $_POST['xin_auth'] ) ? sanitize_key( wp_unslash( $_POST['xin_auth'] ) ) : '';

	switch ( $action ) {
		case 'login':
			xin_handle_login();
			break;
		case 'register':
			xin_handle_register();
			break;
		case 'lost':
			xin_handle_lost();
			break;
	}
}
add_action( 'template_redirect', 'xin_auth_router', 4 );

function xin_handle_login() {
	xin_auth_nonce( 'xin_auth_login', array() );

	$target = xin_auth_target();
	$keep   = $target ? array( 'redirect_to' => $target ) : array();

	if ( xin_auth_blocked() ) {
		xin_auth_back( array_merge( $keep, array( 'msg' => 'throttled' ) ) );
	}

	$login = xin_auth_field( 'xin_user' );
	$pass  = xin_auth_secret( 'xin_pass' );

	if ( '' === $login || '' === $pass ) {
		xin_auth_back( array_merge( $keep, array( 'msg' => 'empty' ) ) );
	}

	$user = wp_signon( array(
		'user_login'    => $login,
		'user_password' => $pass,
		'remember'      => ! empty( $_POST['xin_remember'] ),
	), is_ssl() );

	if ( is_wp_error( $user ) ) {
		xin_auth_attempts( 1 );
		xin_auth_back( array_merge( $keep, array( 'msg' => xin_auth_error_slug( $user ) ) ) );
	}

	xin_auth_attempts( -1 );
	wp_set_current_user( $user->ID );

	wp_safe_redirect( $target ? $target : xin_after_login_url( $user ) );
	exit;
}

function xin_auth_error_slug( $error ) {
	$codes = $error->get_error_codes();

	if ( array_intersect( array( 'invalid_username', 'invalid_email', 'incorrect_password' ), $codes ) ) {
		return 'credentials';
	}
	if ( array_intersect( array( 'empty_username', 'empty_password' ), $codes ) ) {
		return 'empty';
	}

	return 'failed';
}

function xin_handle_register() {
	$back = array( 'view' => 'register' );
	xin_auth_nonce( 'xin_auth_register', $back );

	$target = xin_auth_target();
	if ( $target ) {
		$back['redirect_to'] = $target;
	}

	if ( ! xin_registration_open() ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'closed' ) ) );
	}
	if ( '' !== xin_auth_field( 'xin_site' ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'failed' ) ) );
	}
	if ( xin_auth_blocked() ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'throttled' ) ) );
	}

	$login = sanitize_user( xin_auth_field( 'xin_user' ), true );
	$email = sanitize_email( xin_auth_field( 'xin_email' ) );
	$pass  = xin_auth_secret( 'xin_pass' );
	$again = xin_auth_secret( 'xin_pass2' );

	$back['xin_name']  = $login;
	$back['xin_email'] = $email;

	if ( '' === $login || ! validate_username( $login ) || strlen( $login ) < 3 ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'bad-name' ) ) );
	}
	if ( username_exists( $login ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'name-taken' ) ) );
	}
	if ( ! is_email( $email ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'bad-email' ) ) );
	}
	if ( email_exists( $email ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'email-taken' ) ) );
	}
	if ( strlen( $pass ) < 8 ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'short-pass' ) ) );
	}
	if ( $pass !== $again ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'mismatch' ) ) );
	}

	$user_id = wp_insert_user( array(
		'user_login'   => $login,
		'user_email'   => $email,
		'user_pass'    => $pass,
		'display_name' => $login,
		'nickname'     => $login,
		'role'         => xin_new_user_role(),
	) );

	if ( is_wp_error( $user_id ) ) {
		xin_auth_attempts( 1 );
		xin_auth_back( array_merge( $back, array( 'msg' => 'failed' ) ) );
	}

	xin_auth_attempts( -1 );
	wp_new_user_notification( $user_id, null, 'admin' );

	$user = get_userdata( $user_id );
	wp_set_auth_cookie( $user_id, true, is_ssl() );
	wp_set_current_user( $user_id );
	do_action( 'wp_login', $user->user_login, $user );

	wp_safe_redirect( add_query_arg( 'msg', 'welcome', $target ? $target : xin_after_login_url( $user ) ) );
	exit;
}

function xin_handle_lost() {
	$back = array( 'view' => 'lost' );
	xin_auth_nonce( 'xin_auth_lost', $back );

	if ( xin_auth_blocked() ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'throttled' ) ) );
	}
	if ( '' === xin_auth_field( 'user_login' ) ) {
		xin_auth_back( array_merge( $back, array( 'msg' => 'empty' ) ) );
	}

	$sent = function_exists( 'retrieve_password' ) ? retrieve_password( xin_auth_field( 'user_login' ) ) : new WP_Error( 'xin_no_reset' );

	if ( is_wp_error( $sent ) ) {
		xin_auth_attempts( 1 );
		xin_auth_back( array_merge( $back, array( 'msg' => 'lost-failed' ) ) );
	}

	xin_auth_back( array_merge( $back, array( 'msg' => 'lost-sent' ) ) );
}

function xin_auth_page_guard() {
	if ( ! is_page_template( 'template-auth.php' ) || ! is_user_logged_in() || isset( $_POST['xin_auth'] ) ) {
		return;
	}

	$target = xin_auth_target();
	wp_safe_redirect( $target ? $target : xin_after_login_url( wp_get_current_user() ) );
	exit;
}
add_action( 'template_redirect', 'xin_auth_page_guard', 6 );

function xin_auth_notice() {
	$msg = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '';
	if ( ! $msg ) {
		return;
	}

	$map = array(
		'credentials' => array( 'err', __( 'Не подходит: проверьте имя пользователя и пароль.', 'xin-com' ) ),
		'empty'       => array( 'err', __( 'Заполните оба поля.', 'xin-com' ) ),
		'expired'     => array( 'err', __( 'Форма была открыта слишком долго. Обновите страницу и отправьте снова.', 'xin-com' ) ),
		'throttled'   => array( 'err', __( 'Слишком много попыток подряд. Подождите четверть часа.', 'xin-com' ) ),
		'failed'      => array( 'err', __( 'Не получилось. Попробуйте ещё раз.', 'xin-com' ) ),
		'closed'      => array( 'err', __( 'Регистрация сейчас закрыта.', 'xin-com' ) ),
		'bad-name'    => array( 'err', __( 'Имя пользователя: от трёх символов, латиница, цифры, дефис и подчёркивание.', 'xin-com' ) ),
		'name-taken'  => array( 'err', __( 'Такое имя уже занято.', 'xin-com' ) ),
		'bad-email'   => array( 'err', __( 'Проверьте адрес почты.', 'xin-com' ) ),
		'email-taken' => array( 'err', __( 'На эту почту аккаунт уже заведён. Войдите или восстановите пароль.', 'xin-com' ) ),
		'short-pass'  => array( 'err', __( 'Пароль короче восьми символов.', 'xin-com' ) ),
		'mismatch'    => array( 'err', __( 'Пароли не совпали.', 'xin-com' ) ),
		'lost-sent'   => array( 'ok', __( 'Письмо со ссылкой на смену пароля отправлено.', 'xin-com' ) ),
		'lost-failed' => array( 'err', __( 'Такой пользователь не найден.', 'xin-com' ) ),
		'signed-out'  => array( 'ok', __( 'Вы вышли из аккаунта.', 'xin-com' ) ),
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

function xin_auth_logout_redirect( $url, $requested ) {
	if ( $requested ) {
		return $url;
	}
	return xin_auth_page() ? xin_auth_url( array( 'msg' => 'signed-out' ) ) : $url;
}
add_filter( 'logout_redirect', 'xin_auth_logout_redirect', 10, 2 );
