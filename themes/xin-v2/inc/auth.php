<?php
/**
 * Вход, регистрация и восстановление пароля на самом сайте.
 *
 * `wp-login.php` выглядит как чужая страница и ломает впечатление ровно там,
 * где читатель впервые что-то доверяет площадке. Поэтому форма живёт на
 * обычной странице с шаблоном «Вход и регистрация», а ссылки ядра
 * подменяются на неё.
 *
 * Проверку имени и пароля делает само ядро — `wp_signon()` и
 * `register_new_user()`. Своей криптографии здесь нет и быть не должно.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Страница со входом. Пусто — страницы нет, и тогда все ссылки ведут туда
 * же, куда и раньше: в `wp-login.php`.
 */
function xv_auth_url( $args = array() ) {
	$base = xv_page_url( 'template-auth.php' );

	if ( ! $base ) {
		return '';
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

function xv_login_url( $redirect = '' ) {
	$page = xv_auth_url();

	if ( ! $page ) {
		return wp_login_url( $redirect );
	}

	return $redirect ? add_query_arg( 'to', rawurlencode( $redirect ), $page ) : $page;
}

function xv_register_url( $redirect = '' ) {
	$page = xv_auth_url( array( 'do' => 'register' ) );

	if ( ! $page ) {
		return wp_registration_url();
	}

	return $redirect ? add_query_arg( 'to', rawurlencode( $redirect ), $page ) : $page;
}

function xv_lost_url() {
	$page = xv_auth_url( array( 'do' => 'lost' ) );

	return $page ? $page : wp_lostpassword_url();
}

function xv_registration_open() {
	return (bool) get_option( 'users_can_register' );
}

/**
 * Ссылки ядра ведут на свою страницу.
 *
 * Кроме выхода: `wp_logout_url` подписан одноразовым кодом, и подменять его
 * нельзя — иначе выход перестанет работать.
 */
function xv_filter_login_url( $url, $redirect = '' ) {
	$page = xv_auth_url();

	return $page ? xv_login_url( $redirect ) : $url;
}
add_filter( 'login_url', 'xv_filter_login_url', 10, 2 );

function xv_filter_register_url( $url ) {
	$page = xv_auth_url();

	return $page ? xv_register_url() : $url;
}
add_filter( 'register_url', 'xv_filter_register_url' );

function xv_filter_lost_url( $url ) {
	$page = xv_auth_url();

	return $page ? xv_lost_url() : $url;
}
add_filter( 'lostpassword_url', 'xv_filter_lost_url' );

/**
 * Куда возвращать после входа.
 *
 * Только на свой сайт: параметр приходит из адресной строки, и без проверки
 * им можно было бы увести читателя на чужую страницу.
 */
function xv_auth_target() {
	$to = isset( $_REQUEST['to'] ) ? rawurldecode( sanitize_text_field( wp_unslash( $_REQUEST['to'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $to ) {
		return home_url( '/' );
	}

	$safe = wp_validate_redirect( $to, '' );

	return $safe ? $safe : home_url( '/' );
}

/* -------------------------------------------------------------------------
 * Защита от перебора
 * ---------------------------------------------------------------------- */

/**
 * Ключ счётчика попыток. Считаем по адресу, а не по логину: иначе перебор
 * по списку логинов не ограничен ничем.
 */
function xv_auth_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';

	return 'xv_auth_' . md5( $ip );
}

function xv_auth_attempts() {
	return (int) get_transient( xv_auth_key() );
}

function xv_auth_blocked() {
	return xv_auth_attempts() >= 8;
}

function xv_auth_failed() {
	set_transient( xv_auth_key(), xv_auth_attempts() + 1, 15 * MINUTE_IN_SECONDS );
}

function xv_auth_passed() {
	delete_transient( xv_auth_key() );
}

/* -------------------------------------------------------------------------
 * Обработка форм
 * ---------------------------------------------------------------------- */

function xv_auth_back( $do, $error = '', $done = '' ) {
	$args = array();

	if ( $do && 'login' !== $do ) {
		$args['do'] = $do;
	}

	if ( isset( $_REQUEST['to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$args['to'] = rawurlencode( xv_auth_target() );
	}

	if ( $error ) {
		$args['xv_error'] = $error;
	}

	if ( $done ) {
		$args['xv_done'] = $done;
	}

	$url = xv_auth_url( $args );

	wp_safe_redirect( $url ? $url : home_url( '/' ) );
	exit;
}

function xv_handle_login() {
	check_admin_referer( 'xv_login' );

	if ( xv_auth_blocked() ) {
		xv_auth_back( 'login', 'blocked' );
	}

	$user = wp_signon( array(
		'user_login'    => isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '',
		'user_password' => isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		'remember'      => ! empty( $_POST['remember'] ),
	), is_ssl() );

	if ( is_wp_error( $user ) ) {
		xv_auth_failed();
		xv_auth_back( 'login', 'bad-login' );
	}

	xv_auth_passed();
	wp_set_current_user( $user->ID );

	wp_safe_redirect( xv_auth_target() );
	exit;
}
add_action( 'admin_post_nopriv_xv_login', 'xv_handle_login' );
add_action( 'admin_post_xv_login', 'xv_handle_login' );

function xv_handle_register() {
	check_admin_referer( 'xv_register' );

	if ( ! xv_registration_open() ) {
		xv_auth_back( 'register', 'closed' );
	}

	if ( xv_auth_blocked() ) {
		xv_auth_back( 'register', 'blocked' );
	}

	$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
	$email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';

	$id = register_new_user( $login, $email );

	if ( is_wp_error( $id ) ) {
		xv_auth_failed();
		xv_auth_back( 'register', $id->get_error_code() );
	}

	xv_auth_passed();

	// Пароль читатель задаёт по письму: придумывать его в форме — значит
	// хранить ещё одно поле и ещё один способ ошибиться.
	xv_auth_back( 'login', '', 'registered' );
}
add_action( 'admin_post_nopriv_xv_register', 'xv_handle_register' );
add_action( 'admin_post_xv_register', 'xv_handle_register' );

function xv_handle_lost() {
	check_admin_referer( 'xv_lost' );

	$sent = retrieve_password( isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '' );

	// Про неизвестный адрес не сообщаем: иначе форму можно использовать как
	// проверку, зарегистрирован ли человек на площадке.
	if ( is_wp_error( $sent ) && 'invalidcombo' !== $sent->get_error_code() ) {
		xv_auth_back( 'lost', 'lost-failed' );
	}

	xv_auth_back( 'lost', '', 'lost-sent' );
}
add_action( 'admin_post_nopriv_xv_lost', 'xv_handle_lost' );
add_action( 'admin_post_xv_lost', 'xv_handle_lost' );

/**
 * Выход возвращает на главную, а не на серый экран `wp-login.php`.
 */
function xv_logout_redirect( $url, $requested, $user ) {
	return $requested ? $url : home_url( '/' );
}
add_filter( 'logout_redirect', 'xv_logout_redirect', 10, 3 );

/**
 * Вошедшему на странице входа делать нечего.
 */
function xv_auth_guard() {
	if ( ! is_page_template( 'template-auth.php' ) || ! is_user_logged_in() ) {
		return;
	}

	// Кроме случая, когда он сам пришёл сменить учётную запись.
	if ( isset( $_GET['do'] ) && 'switch' === $_GET['do'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect( xv_auth_target() );
	exit;
}
add_action( 'template_redirect', 'xv_auth_guard' );

/**
 * Сообщения формы.
 */
function xv_auth_notice() {
	$errors = array(
		'bad-login'         => __( 'Не подошёл логин или пароль.', 'xin-v2' ),
		'blocked'           => __( 'Слишком много попыток. Попробуйте через четверть часа.', 'xin-v2' ),
		'closed'            => __( 'Регистрация на площадке закрыта.', 'xin-v2' ),
		'empty_username'    => __( 'Нужен логин.', 'xin-v2' ),
		'invalid_username'  => __( 'В логине есть недопустимые знаки.', 'xin-v2' ),
		'username_exists'   => __( 'Такой логин уже занят.', 'xin-v2' ),
		'empty_email'       => __( 'Нужна почта — на неё придёт пароль.', 'xin-v2' ),
		'invalid_email'     => __( 'Проверьте адрес почты.', 'xin-v2' ),
		'email_exists'      => __( 'На этот адрес уже заведена учётная запись.', 'xin-v2' ),
		'lost-failed'       => __( 'Письмо не ушло. Попробуйте ещё раз.', 'xin-v2' ),
	);

	$done = array(
		'registered' => __( 'Готово. Пароль отправлен на почту — войдите с ним.', 'xin-v2' ),
		'lost-sent'  => __( 'Если такая учётная запись есть, письмо уже в пути.', 'xin-v2' ),
	);

	if ( ! empty( $_GET['xv_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = sanitize_key( wp_unslash( $_GET['xv_error'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		printf(
			'<p class="xv-notice is-error">%s</p>',
			esc_html( isset( $errors[ $key ] ) ? $errors[ $key ] : __( 'Что-то пошло не так.', 'xin-v2' ) )
		);
	}

	if ( ! empty( $_GET['xv_done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = sanitize_key( wp_unslash( $_GET['xv_done'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( isset( $done[ $key ] ) ) {
			printf( '<p class="xv-notice is-ok">%s</p>', esc_html( $done[ $key ] ) );
		}
	}
}
