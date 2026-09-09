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
	if ( ! strpos( $src, 'ver=' ) ) {
		return $src;
	}

	if ( false !== strpos( $src, XIN_URI . '/' ) ) {
		return $src;
	}

	return remove_query_arg( 'ver', $src );
}
add_filter( 'style_loader_src', 'xin_strip_version', 20 );
add_filter( 'script_loader_src', 'xin_strip_version', 20 );

function xin_clean_body_class( $classes ) {
	$drop = array( 'wp-embed-responsive', 'wp-singular', 'wp-theme-xin-com', 'wp-custom-logo' );
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
	if ( xin_discussions_on() ) {
		return;
	}

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
	if ( xin_discussions_on() ) {
		return;
	}

	global $pagenow;
	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'xin_block_comments_screen' );

function xin_login_styles() {
	$primary = get_theme_mod( 'xin_primary', '' );
	$accent  = $primary ? $primary : '#b63d25';
	?>
	<style>
		/*
		 * Экран входа — первое, что видит автор, и он обязан выглядеть частью
		 * сайта. Токены темы сюда не доезжают: страница логина грузится без
		 * фронтовых стилей, поэтому значения продублированы из style.css.
		 */
		body.login {
			background: #faf8f5;
			color: #262119;
			font-family: Candara, "Avenir Next", "Segoe UI Variable Text", "Helvetica Neue", system-ui, -apple-system, sans-serif;
		}
		.login h1 a {
			background: none; width: auto; height: auto; text-indent: 0;
			font-family: "Sitka Banner", "Hoefler Text", "Iowan Old Style", Charter, Constantia, "Palatino Linotype", Georgia, serif;
			font-size: 26px; font-weight: 600; letter-spacing: -.02em; color: #262119;
			line-height: 1.2; margin-bottom: 10px;
		}
		.login form {
			background: #fefdfb; border: 1px solid #e2ddd7; border-radius: 8px;
			box-shadow: none; padding: 28px 26px;
		}
		.login form label { color: #746b63; font-size: 13px; }
		.login input[type="text"], .login input[type="password"] {
			background: #fefdfb; border: 1px solid #d2cbc1; color: #262119;
			border-radius: 5px; padding: 10px 12px; box-shadow: none;
		}
		.login input[type="text"]:focus, .login input[type="password"]:focus {
			border-color: <?php echo esc_html( $accent ); ?>; box-shadow: 0 0 0 3px <?php echo esc_html( $accent ); ?>26;
		}
		.wp-core-ui .button-primary {
			background: <?php echo esc_html( $accent ); ?>; border: 0; border-radius: 5px;
			padding: 7px 18px; height: auto; font-weight: 600; text-shadow: none; box-shadow: none;
		}
		.wp-core-ui .button-primary:hover { background: <?php echo esc_html( $accent ); ?>e0; }
		.login .button.wp-hide-pw { color: #9b9187; }
		.login #nav, .login #backtoblog { padding: 10px 26px; }
		.login #nav a, .login #backtoblog a { color: #746b63; font-size: 13px; }
		.login #nav a:hover, .login #backtoblog a:hover { color: <?php echo esc_html( $accent ); ?>; }
		.login .language-switcher, .login .privacy-policy-page-link { display: none; }
		.login .message, .login .notice {
			background: #fefdfb; border: 1px solid #e2ddd7; border-left: 2px solid <?php echo esc_html( $accent ); ?>;
			border-radius: 5px; color: #262119; box-shadow: none;
		}
	</style>
	<?php
}
add_action( 'login_head', 'xin_login_styles' );

function xin_login_title( $login_title ) {
	return str_replace( ' &#8212; WordPress', '', $login_title );
}
add_filter( 'login_title', 'xin_login_title' );

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
		esc_html( sprintf( __( 'Вернуться на %s', 'xin-com' ), get_bloginfo( 'name' ) ) )
	);
}
add_filter( 'login_site_html_link', 'xin_login_back_text' );

function xin_login_errors( $errors ) {
	if ( ! is_wp_error( $errors ) ) {
		return $errors;
	}

	$vague = array( 'invalid_username', 'invalid_email', 'incorrect_password', 'incorrect_username' );
	if ( ! array_intersect( $vague, $errors->get_error_codes() ) ) {
		return $errors;
	}

	$clean = new WP_Error();
	foreach ( $errors->get_error_codes() as $code ) {
		if ( in_array( $code, $vague, true ) ) {
			continue;
		}
		foreach ( $errors->get_error_messages( $code ) as $message ) {
			$clean->add( $code, $message );
		}
	}
	$clean->add( 'xin_credentials', __( 'Не подходит: проверьте имя пользователя и пароль.', 'xin-com' ) );

	return $clean;
}
add_filter( 'wp_login_errors', 'xin_login_errors' );

/**
 * Empties whatever cache the site runs in front of WordPress.
 *
 * Kept separate from the once-per-version check below so anything that changes
 * what every page shows — the navigation gaining a section, for instance — can
 * ask for a purge on its own terms.
 */
function xin_purge_caches() {
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	do_action( 'rocket_clean_domain' );
	do_action( 'w3tc_flush_all' );
	do_action( 'wpsc_delete_cache' );
	do_action( 'autoptimize_action_cachepurged' );
}

function xin_flush_caches() {
	if ( XIN_VERSION === get_option( 'xin_asset_stamp' ) ) {
		return;
	}

	update_option( 'xin_asset_stamp', XIN_VERSION, false );

	xin_purge_caches();
}
add_action( 'init', 'xin_flush_caches', 30 );
add_action( 'after_switch_theme', 'xin_flush_caches' );
