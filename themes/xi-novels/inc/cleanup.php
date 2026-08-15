<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'show_admin_bar', '__return_false' );

function xin_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'xin_clean_head' );

add_filter( 'xmlrpc_enabled', '__return_false' );
function xin_clean_headers( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'xin_clean_headers' );

function xin_strip_version( $src ) {
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'xin_strip_version', 20 );
add_filter( 'script_loader_src', 'xin_strip_version', 20 );

function xin_clean_body_class( $classes ) {
	$drop = array( 'wp-embed-responsive', 'wp-singular', 'wp-theme-xi-novels', 'wp-custom-logo' );
	return array_values( array_diff( $classes, $drop ) );
}
add_filter( 'body_class', 'xin_clean_body_class', 20 );

function xin_clean_post_class( $classes ) {
	return array_values( array_filter( $classes, static function ( $class ) {
		return ! preg_match( '/^(post-\d+|type-|status-|hentry|format-)/', $class );
	} ) );
}
add_filter( 'post_class', 'xin_clean_post_class', 20 );

add_filter( 'the_generator', '__return_empty_string' );

function xin_drop_global_styles() {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'xin_drop_global_styles', 20 );

add_filter( 'rest_url_prefix', function () {
	return 'api';
} );

function xin_hide_comments_admin() {
	remove_menu_page( 'edit-comments.php' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

	foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
}
add_action( 'admin_menu', 'xin_hide_comments_admin' );

function xin_block_comments_screen() {
	global $pagenow;
	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'xin_block_comments_screen' );

function xin_login_styles() {
	$primary = get_theme_mod( 'xin_primary', '' );
	$accent  = $primary ? $primary : '#e1173f';
	?>
	<style>
		body.login {
			background: #0d0e11;
			color: #e3e5ec;
			font-family: "Inter", "Segoe UI", system-ui, sans-serif;
		}
		body.login::before {
			content: ""; position: fixed; inset: -20% 40% 40% -20%;
			background: radial-gradient(circle, <?php echo esc_html( $accent ); ?>55, transparent 65%);
			filter: blur(90px); pointer-events: none;
		}
		.login h1 a {
			background: none; width: auto; height: auto; text-indent: 0;
			font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: #fff;
			line-height: 1.2; margin-bottom: 8px;
		}
		.login h1 a::first-letter { color: <?php echo esc_html( $accent ); ?>; }
		.login form {
			background: #14161b; border: 1px solid #262a33; border-radius: 16px;
			box-shadow: 0 18px 44px rgba(0, 0, 0, .55); padding: 26px 24px;
		}
		.login form label { color: #aeb3c0; font-size: 13px; }
		.login input[type="text"], .login input[type="password"] {
			background: #0d0e11; border: 1px solid #333846; color: #e3e5ec;
			border-radius: 10px; padding: 10px 12px; box-shadow: none;
		}
		.login input[type="text"]:focus, .login input[type="password"]:focus {
			border-color: <?php echo esc_html( $accent ); ?>; box-shadow: 0 0 0 3px <?php echo esc_html( $accent ); ?>33;
		}
		.wp-core-ui .button-primary {
			background: <?php echo esc_html( $accent ); ?>; border: 0; border-radius: 999px;
			padding: 6px 22px; height: auto; font-weight: 700; text-shadow: none; box-shadow: none;
		}
		.wp-core-ui .button-primary:hover { background: <?php echo esc_html( $accent ); ?>d9; }
		.login .button.wp-hide-pw { color: #8b90a0; }
		.login #nav, .login #backtoblog { padding: 8px 24px; }
		.login #nav a, .login #backtoblog a { color: #aeb3c0; }
		.login #nav a:hover, .login #backtoblog a:hover { color: <?php echo esc_html( $accent ); ?>; }
		.login .language-switcher, .login .privacy-policy-page-link { display: none; }
		.login .message, .login .notice { background: #14161b; border-left-color: <?php echo esc_html( $accent ); ?>; color: #e3e5ec; }
	</style>
	<?php
}
add_action( 'login_head', 'xin_login_styles' );

function xin_login_logo_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'xin_login_logo_url' );

function xin_login_logo_text() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'xin_login_logo_text' );

function xin_login_back_text( $link ) {
	return sprintf(
		'<a href="%s">%s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( sprintf( __( 'Вернуться на %s', 'xi-novels' ), get_bloginfo( 'name' ) ) )
	);
}
add_filter( 'login_site_html_link', 'xin_login_back_text' );

function xin_login_errors() {
	return __( 'Неверная пара логин / пароль.', 'xi-novels' );
}
add_filter( 'login_errors', 'xin_login_errors' );
