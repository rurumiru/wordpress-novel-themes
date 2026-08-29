<?php
/**
 * Browser push for free chapter releases.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once XIN_DIR . '/inc/web-push-sender.php';

function xin_push_is_sw_request() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['xin_push_sw'] ) && (string) wp_unslash( $_GET['xin_push_sw'] ) !== '' ) {
		return true;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	$path = is_string( $path ) ? untrailingslashit( $path ) : '';
	return (bool) preg_match( '#/xin-push-sw\.js$#', $path );
}

function xin_push_sw_file() {
	return XIN_DIR . '/assets/js/push-sw.js';
}

function xin_push_serve_service_worker() {
	if ( ! xin_push_is_sw_request() ) {
		return;
	}

	$sw_file = xin_push_sw_file();
	if ( ! file_exists( $sw_file ) ) {
		status_header( 404 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'Service worker file missing.';
		exit;
	}

	status_header( 200 );
	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	@ini_set( 'zlib.output_compression', 'Off' );
	if ( function_exists( 'nocache_headers' ) ) {
		nocache_headers();
	}
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Service-Worker-Allowed: /' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Robots-Tag: noindex, nofollow' );

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	readfile( $sw_file );
	exit;
}

add_action( 'init', 'xin_push_serve_service_worker', 0 );
add_action( 'template_redirect', 'xin_push_serve_service_worker', 0 );

add_filter( 'redirect_canonical', 'xin_push_disable_canonical_for_sw', 0, 2 );
function xin_push_disable_canonical_for_sw( $redirect_url, $requested_url ) {
	if ( xin_push_is_sw_request() ) {
		return false;
	}
	if ( is_string( $requested_url ) && strpos( $requested_url, 'xin-push-sw.js' ) !== false ) {
		return false;
	}
	return $redirect_url;
}

function xin_push_get_sw_url() {
	$sw   = xin_push_sw_file();
	$ver  = file_exists( $sw ) ? (string) filemtime( $sw ) : (string) time();

	return add_query_arg(
		array(
			'xin_push_sw' => '1',
			'ver'         => $ver,
		),
		home_url( '/index.php' )
	);
}

add_action( 'init', 'xin_push_maybe_create_table', 5 );
function xin_push_maybe_create_table() {
	if ( get_option( 'xin_push_table_version' ) === '1' ) {
		return;
	}

	global $wpdb;
	$table = xin_push_get_table_name();

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists === $table ) {
		update_option( 'xin_push_table_version', '1', false );
		return;
	}

	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		novel_id bigint(20) unsigned NOT NULL,
		endpoint_hash char(64) NOT NULL,
		endpoint text NOT NULL,
		p256dh varchar(255) NOT NULL,
		auth varchar(255) NOT NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT 0,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY endpoint_novel (endpoint_hash, novel_id),
		KEY novel_id (novel_id)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'xin_push_table_version', '1', false );
}

function xin_push_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'xin_push_subscriptions';
}

function xin_push_openssl_config_path() {
	static $resolved = false;
	static $path     = '';

	if ( $resolved ) {
		return $path;
	}
	$resolved = true;

	$env = getenv( 'OPENSSL_CONF' );
	if ( is_string( $env ) && $env !== '' && is_readable( $env ) ) {
		$path = $env;
		return $path;
	}

	$candidates = array();
	$bins       = array();
	if ( defined( 'PHP_BINARY' ) && PHP_BINARY ) {
		$bins[] = dirname( PHP_BINARY );
	}
	if ( defined( 'PHP_EXTENSION_DIR' ) && PHP_EXTENSION_DIR ) {
		$bins[] = dirname( PHP_EXTENSION_DIR );
	}

	foreach ( array_unique( $bins ) as $bin ) {
		$dir            = str_replace( '\\', '/', $bin );
		$candidates[]   = $dir . '/extras/ssl/openssl.cnf';
		$candidates[]   = $dir . '/ssl/openssl.cnf';
	}

	foreach ( $candidates as $candidate ) {
		if ( is_readable( $candidate ) ) {
			$path = $candidate;
			return $path;
		}
	}

	$path = '';
	return $path;
}

function xin_push_openssl_args() {
	$args = array(
		'curve_name'       => 'prime256v1',
		'private_key_type' => OPENSSL_KEYTYPE_EC,
	);
	$cnf = xin_push_openssl_config_path();
	if ( $cnf ) {
		$args['config'] = $cnf;
	}
	return $args;
}

function xin_push_get_vapid_keys() {
	$keys = get_option( 'xin_push_vapid_keys' );
	if ( is_array( $keys ) && ! empty( $keys['publicKey'] ) && ! empty( $keys['privateKey'] ) ) {
		return $keys;
	}

	$resource = openssl_pkey_new( xin_push_openssl_args() );

	if ( ! $resource ) {
		return array();
	}

	$details = openssl_pkey_get_details( $resource );
	if ( empty( $details['ec']['d'] ) || empty( $details['ec']['x'] ) || empty( $details['ec']['y'] ) ) {
		return array();
	}

	$x = str_pad( $details['ec']['x'], 32, "\0", STR_PAD_LEFT );
	$y = str_pad( $details['ec']['y'], 32, "\0", STR_PAD_LEFT );
	$d = str_pad( $details['ec']['d'], 32, "\0", STR_PAD_LEFT );

	$keys = array(
		'publicKey'  => xin_web_push_base64url_encode( "\x04" . $x . $y ),
		'privateKey' => xin_web_push_base64url_encode( $d ),
		'subject'    => 'mailto:' . xin_push_get_contact_email(),
	);

	update_option( 'xin_push_vapid_keys', $keys, false );
	return $keys;
}

function xin_push_get_contact_email() {
	$admin_email = get_option( 'admin_email' );
	if ( is_email( $admin_email ) ) {
		return $admin_email;
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	return 'admin@' . ( is_string( $host ) ? $host : 'localhost' );
}

function xin_push_is_supported_environment() {
	if ( is_ssl() ) {
		return true;
	}

	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = is_string( $host ) ? strtolower( $host ) : '';

	return in_array( $host, array( 'localhost', '127.0.0.1' ), true )
		|| substr( $host, -6 ) === '.local';
}

add_action( 'wp_enqueue_scripts', 'xin_push_enqueue_assets', 40 );
function xin_push_enqueue_assets() {
	if ( ! is_singular( 'novel' ) ) {
		return;
	}

	$novel_id = get_the_ID();
	$vapid    = xin_push_get_vapid_keys();

	wp_enqueue_script(
		'xin-com-push',
		XIN_URI . '/assets/js/push.js',
		array(),
		xin_asset_ver( '/assets/js/push.js' ),
		true
	);

	wp_localize_script(
		'xin-com-push',
		'xinPush',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'novelId'        => (int) $novel_id,
			'swUrl'          => xin_push_get_sw_url(),
			'vapidPublicKey' => $vapid['publicKey'] ?? '',
			'nonce'          => wp_create_nonce( 'xin_push_' . $novel_id ),
			'supported'      => ( xin_push_is_supported_environment() && ! empty( $vapid['publicKey'] ) ) ? 1 : 0,
			'icons'          => array(
				'bell'    => xin_icon( 'bell' ),
				'bellOff' => xin_icon( 'bell-off' ),
			),
			'i18n'           => array(
				'enabled'       => __( 'Уведомления вкл.', 'xin-com' ),
				'disabled'      => __( 'Уведомлять', 'xin-com' ),
				'unsupported'   => __( 'Нужны HTTPS и современный браузер', 'xin-com' ),
				'denied'        => __( 'Заблокировано в браузере', 'xin-com' ),
				'error'         => __( 'Не получилось обновить уведомления. Попробуйте ещё раз.', 'xin-com' ),
				'subscribing'   => __( 'Включаю…', 'xin-com' ),
				'unsubscribing' => __( 'Выключаю…', 'xin-com' ),
			),
		)
	);
}

function xin_push_rate_limit_ok( $scope, $limit, $window ) {
	$scope  = sanitize_key( (string) $scope );
	$limit  = max( 1, (int) $limit );
	$window = max( MINUTE_IN_SECONDS, (int) $window );
	$user   = get_current_user_id();
	$client = $user > 0
		? 'u' . $user
		: 'g' . hash( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown' );
	$key    = 'xin_push_rl_' . md5( $scope . '|' . $client );
	$count  = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, $window );
	return true;
}

function xin_push_enforce_rate_limit( $scope, $limit, $window ) {
	if ( ! xin_push_rate_limit_ok( $scope, $limit, $window ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Слишком много запросов. Подождите и попробуйте снова.', 'xin-com' ),
				'code'    => 'rate_limited',
			),
			429
		);
	}
}

function xin_push_valid_base64url_key( $value, $min, $max ) {
	$length = strlen( (string) $value );
	return $length >= $min
		&& $length <= $max
		&& (bool) preg_match( '/^[A-Za-z0-9_-]+$/', (string) $value );
}

add_action( 'wp_ajax_xin_push_nonce', 'xin_ajax_push_nonce' );
add_action( 'wp_ajax_nopriv_xin_push_nonce', 'xin_ajax_push_nonce' );
function xin_ajax_push_nonce() {
	if ( function_exists( 'nocache_headers' ) ) {
		nocache_headers();
	}
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	xin_push_enforce_rate_limit( 'nonce', 60, MINUTE_IN_SECONDS );

	$novel_id = isset( $_REQUEST['novel_id'] ) ? absint( $_REQUEST['novel_id'] ) : 0;
	if ( ! $novel_id || get_post_type( $novel_id ) !== 'novel' || get_post_status( $novel_id ) !== 'publish' ) {
		wp_send_json_error( array( 'message' => 'invalid_novel' ), 400 );
	}

	wp_send_json_success(
		array(
			'nonce'    => wp_create_nonce( 'xin_push_' . $novel_id ),
			'novel_id' => $novel_id,
		)
	);
}

add_action( 'wp_ajax_xin_push_subscribe', 'xin_ajax_push_subscribe' );
add_action( 'wp_ajax_nopriv_xin_push_subscribe', 'xin_ajax_push_subscribe' );
function xin_ajax_push_subscribe() {
	if ( function_exists( 'nocache_headers' ) ) {
		nocache_headers();
	}
	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;
	xin_push_verify_ajax_request( $novel_id, 'subscribe', 20, HOUR_IN_SECONDS );

	xin_push_maybe_create_table();

	$endpoint = isset( $_POST['endpoint'] ) ? xin_push_sanitize_endpoint( wp_unslash( $_POST['endpoint'] ) ) : '';
	$p256dh   = isset( $_POST['p256dh'] ) ? sanitize_text_field( wp_unslash( $_POST['p256dh'] ) ) : '';
	$auth     = isset( $_POST['auth'] ) ? sanitize_text_field( wp_unslash( $_POST['auth'] ) ) : '';

	if (
		! $endpoint
		|| ! xin_push_valid_base64url_key( $p256dh, 64, 128 )
		|| ! xin_push_valid_base64url_key( $auth, 16, 64 )
	) {
		wp_send_json_error( array( 'message' => __( 'Неверные данные подписки.', 'xin-com' ) ), 400 );
	}

	global $wpdb;
	$table = xin_push_get_table_name();
	$hash  = hash( 'sha256', $endpoint );
	$user  = get_current_user_id();

	$existing_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE endpoint_hash = %s AND novel_id = %d LIMIT 1",
			$hash,
			$novel_id
		)
	);

	$row = array(
		'novel_id'      => $novel_id,
		'endpoint_hash' => $hash,
		'endpoint'      => $endpoint,
		'p256dh'        => $p256dh,
		'auth'          => $auth,
		'user_id'       => $user,
	);

	if ( $existing_id > 0 ) {
		$ok = $wpdb->update(
			$table,
			$row,
			array( 'id' => $existing_id ),
			array( '%d', '%s', '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);
		if ( false === $ok ) {
			wp_send_json_error( array( 'message' => __( 'Не получилось обновить подписку.', 'xin-com' ) ), 500 );
		}
	} else {
		$ok = $wpdb->insert(
			$table,
			$row,
			array( '%d', '%s', '%s', '%s', '%s', '%d' )
		);
		if ( ! $ok ) {
			$ok2 = $wpdb->replace(
				$table,
				$row,
				array( '%d', '%s', '%s', '%s', '%s', '%d' )
			);
			if ( ! $ok2 ) {
				wp_send_json_error(
					array( 'message' => __( 'Не получилось сохранить подписку.', 'xin-com' ) ),
					500
				);
			}
		}
	}

	wp_send_json_success( array( 'subscribed' => true, 'novel_id' => $novel_id ) );
}

add_action( 'wp_ajax_xin_push_unsubscribe', 'xin_ajax_push_unsubscribe' );
add_action( 'wp_ajax_nopriv_xin_push_unsubscribe', 'xin_ajax_push_unsubscribe' );
function xin_ajax_push_unsubscribe() {
	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;
	xin_push_verify_ajax_request( $novel_id, 'unsubscribe', 60, HOUR_IN_SECONDS );

	$endpoint = isset( $_POST['endpoint'] ) ? xin_push_sanitize_endpoint( wp_unslash( $_POST['endpoint'] ) ) : '';
	if ( ! $endpoint ) {
		wp_send_json_error( array( 'message' => __( 'Неверный endpoint.', 'xin-com' ) ), 400 );
	}

	global $wpdb;
	$table = xin_push_get_table_name();
	$hash  = hash( 'sha256', $endpoint );

	$wpdb->delete(
		$table,
		array(
			'novel_id'      => $novel_id,
			'endpoint_hash' => $hash,
		),
		array( '%d', '%s' )
	);

	wp_send_json_success( array( 'subscribed' => false ) );
}

add_action( 'wp_ajax_xin_push_status', 'xin_ajax_push_status' );
add_action( 'wp_ajax_nopriv_xin_push_status', 'xin_ajax_push_status' );
function xin_ajax_push_status() {
	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;
	xin_push_verify_ajax_request( $novel_id, 'status', 120, HOUR_IN_SECONDS );

	$endpoint = isset( $_POST['endpoint'] ) ? xin_push_sanitize_endpoint( wp_unslash( $_POST['endpoint'] ) ) : '';
	if ( ! $endpoint ) {
		wp_send_json_success( array( 'subscribed' => false ) );
	}

	global $wpdb;
	$table = xin_push_get_table_name();
	$hash  = hash( 'sha256', $endpoint );

	$exists = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE novel_id = %d AND endpoint_hash = %s",
			$novel_id,
			$hash
		)
	);

	wp_send_json_success( array( 'subscribed' => $exists > 0 ) );
}

function xin_push_sanitize_endpoint( $endpoint ) {
	$endpoint = trim( (string) $endpoint );
	if ( $endpoint === '' || stripos( $endpoint, 'https://' ) !== 0 ) {
		return '';
	}
	if ( strlen( $endpoint ) > 2048 ) {
		return '';
	}
	if ( preg_match( '/[\x00-\x20\x7f]/', $endpoint ) ) {
		return '';
	}
	$parts = wp_parse_url( $endpoint );
	if (
		! is_array( $parts )
		|| ( $parts['scheme'] ?? '' ) !== 'https'
		|| empty( $parts['host'] )
		|| isset( $parts['user'] )
		|| isset( $parts['pass'] )
		|| isset( $parts['fragment'] )
	) {
		return '';
	}
	return $endpoint;
}

function xin_push_verify_ajax_request( $novel_id, $scope = 'request', $limit = 60, $window = HOUR_IN_SECONDS ) {
	if ( ! $novel_id || get_post_type( $novel_id ) !== 'novel' || get_post_status( $novel_id ) !== 'publish' ) {
		wp_send_json_error( array( 'message' => __( 'Неверный тайтл.', 'xin-com' ), 'code' => 'invalid_novel' ), 400 );
	}

	$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'xin_push_' . $novel_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Сессия истекла — повторяю…', 'xin-com' ),
				'code'    => 'bad_nonce',
			),
			403
		);
	}

	xin_push_enforce_rate_limit( $scope, $limit, $window );
}

$GLOBALS['xin_push_pending'] = array();

function xin_push_chapter_is_free( $chapter_id ) {
	return $chapter_id && ! xin_chapter_is_locked( $chapter_id );
}

function xin_push_get_novel_id_for_chapter( $chapter_id ) {
	return xin_chapter_novel_id( absint( $chapter_id ) );
}

function xin_push_digest_option_key( $novel_id ) {
	return 'xin_push_digest_' . absint( $novel_id );
}

add_action( 'transition_post_status', 'xin_push_on_chapter_publish', 20, 3 );
function xin_push_on_chapter_publish( $new_status, $old_status, $post ) {
	if ( $new_status !== 'publish' || $old_status === 'publish' ) {
		return;
	}
	if ( ! $post instanceof WP_Post || $post->post_type !== 'chapter' ) {
		return;
	}
	$GLOBALS['xin_push_pending'][ (int) $post->ID ] = (int) $post->ID;
}

add_action( 'save_post_chapter', 'xin_push_on_save_chapter', 99 );
function xin_push_on_save_chapter( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( empty( $GLOBALS['xin_push_pending'][ (int) $post_id ] ) ) {
		return;
	}
	xin_push_consider_chapter( (int) $post_id, false );
}

add_action( 'shutdown', 'xin_push_on_shutdown_pending', 40 );
function xin_push_on_shutdown_pending() {
	if ( empty( $GLOBALS['xin_push_pending'] ) || ! is_array( $GLOBALS['xin_push_pending'] ) ) {
		return;
	}
	foreach ( $GLOBALS['xin_push_pending'] as $chapter_id ) {
		xin_push_consider_chapter( (int) $chapter_id, false );
	}
}

add_action( 'updated_post_meta', 'xin_push_on_chapter_unlock', 20, 4 );
add_action( 'deleted_post_meta', 'xin_push_on_chapter_unlock', 20, 4 );
function xin_push_on_chapter_unlock( $meta_id, $post_id, $meta_key, $meta_value = '' ) {
	if ( $meta_key !== '_xin_locked' ) {
		return;
	}
	if ( current_filter() === 'updated_post_meta' && (string) $meta_value !== '0' && $meta_value !== 0 && $meta_value !== '' ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'chapter' ) {
		return;
	}
	xin_push_consider_chapter( (int) $post_id, true );
}

function xin_push_consider_chapter( $chapter_id, $force_requeue = false ) {
	$chapter_id = absint( $chapter_id );
	if ( ! $chapter_id ) {
		return;
	}

	$novel_id = xin_push_get_novel_id_for_chapter( $chapter_id );
	if ( ! $novel_id ) {
		return;
	}

	unset( $GLOBALS['xin_push_pending'][ $chapter_id ] );
	xin_push_queue_free_chapter( $novel_id, $chapter_id, $force_requeue );
}

function xin_push_queue_free_chapter( $novel_id, $chapter_id, $force_requeue = false ) {
	$chapter_id = absint( $chapter_id );
	$novel_id   = absint( $novel_id );
	if ( ! $chapter_id ) {
		return;
	}
	if ( ! $novel_id ) {
		$novel_id = xin_push_get_novel_id_for_chapter( $chapter_id );
	}
	if ( ! $novel_id || get_post_type( $chapter_id ) !== 'chapter' ) {
		return;
	}
	if ( get_post_status( $chapter_id ) !== 'publish' || ! xin_push_chapter_is_free( $chapter_id ) ) {
		return;
	}

	$lock = 'xin_push_sent_' . $chapter_id;
	if ( get_transient( $lock ) && ! $force_requeue ) {
		return;
	}
	if ( $force_requeue ) {
		delete_transient( $lock );
	}

	xin_push_digest_add( $novel_id, $chapter_id );
	xin_push_schedule_digest( $novel_id );
}

function xin_push_digest_add( $novel_id, $chapter_id ) {
	$key  = xin_push_digest_option_key( $novel_id );
	$data = get_option( $key, array() );
	if ( ! is_array( $data ) ) {
		$data = array();
	}

	$ids   = isset( $data['ids'] ) && is_array( $data['ids'] ) ? $data['ids'] : array();
	$ids[ $chapter_id ] = $chapter_id;
	$now   = time();
	$first = ! empty( $data['first'] ) ? (int) $data['first'] : $now;

	update_option(
		$key,
		array(
			'ids'   => $ids,
			'first' => $first,
		),
		false
	);
}

function xin_push_schedule_digest( $novel_id ) {
	$novel_id = absint( $novel_id );
	if ( ! $novel_id ) {
		return;
	}

	$key  = xin_push_digest_option_key( $novel_id );
	$data = get_option( $key, array() );
	if ( empty( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
		return;
	}

	$now   = time();
	$first = ! empty( $data['first'] ) ? (int) $data['first'] : $now;
	$when  = min( $now + 30, $first + 5 * MINUTE_IN_SECONDS );
	if ( $when <= $now ) {
		$when = $now + 5;
	}

	$pending              = get_option( 'xin_push_digest_pending', array() );
	$pending              = is_array( $pending ) ? $pending : array();
	$pending[ $novel_id ] = $when;
	update_option( 'xin_push_digest_pending', $pending, false );

	$hook = 'xin_push_flush_digest';
	$args = array( $novel_id );
	$next = wp_next_scheduled( $hook, $args );
	if ( $next ) {
		wp_unschedule_event( $next, $hook, $args );
	}
	wp_schedule_single_event( $when, $hook, $args );
}

add_action( 'xin_push_flush_digest', 'xin_push_run_digest' );
add_action( 'init', 'xin_push_maybe_flush_due_digests', 25 );

function xin_push_maybe_flush_due_digests() {
	if ( ! wp_doing_cron() ) {
		return;
	}

	$pending = get_option( 'xin_push_digest_pending', array() );
	if ( empty( $pending ) || ! is_array( $pending ) ) {
		return;
	}

	$now = time();
	foreach ( $pending as $novel_id => $when ) {
		if ( $now >= (int) $when ) {
			xin_push_run_digest( (int) $novel_id );
		}
	}
}

function xin_push_run_digest( $novel_id ) {
	$novel_id = absint( $novel_id );
	if ( ! $novel_id ) {
		return;
	}

	$lock = 'xin_push_digest_lock_' . $novel_id;
	if ( ! add_option( $lock, time(), '', false ) ) {
		$started = (int) get_option( $lock );
		if ( $started && ( time() - $started ) < 120 ) {
			return;
		}
	}

	$key  = xin_push_digest_option_key( $novel_id );
	$data = get_option( $key, array() );
	delete_option( $key );

	$pending = get_option( 'xin_push_digest_pending', array() );
	if ( is_array( $pending ) ) {
		unset( $pending[ $novel_id ] );
		update_option( 'xin_push_digest_pending', $pending, false );
	}

	$chapter_ids = array();
	if ( is_array( $data ) && ! empty( $data['ids'] ) && is_array( $data['ids'] ) ) {
		$chapter_ids = array_values( array_filter( array_map( 'absint', $data['ids'] ) ) );
	}

	if ( $chapter_ids ) {
		xin_push_dispatch_novel_notification( $novel_id, $chapter_ids );
	}

	delete_option( $lock );
}

function xin_push_dispatch_novel_notification( $novel_id, $chapter_ids ) {
	global $wpdb;

	$novel_id    = absint( $novel_id );
	$chapter_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $chapter_ids ) ) ) );
	if ( ! $novel_id || empty( $chapter_ids ) ) {
		return;
	}

	$eligible = array();
	foreach ( $chapter_ids as $chapter_id ) {
		if ( get_post_status( $chapter_id ) !== 'publish' ) {
			continue;
		}
		if ( ! xin_push_chapter_is_free( $chapter_id ) ) {
			continue;
		}
		$eligible[] = $chapter_id;
	}
	$eligible = array_values( array_unique( $eligible ) );
	if ( empty( $eligible ) ) {
		return;
	}

	foreach ( $eligible as $chapter_id ) {
		set_transient( 'xin_push_sent_' . $chapter_id, 1, 10 * MINUTE_IN_SECONDS );
	}

	$table = xin_push_get_table_name();
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT endpoint, p256dh, auth FROM {$table} WHERE novel_id = %d",
			$novel_id
		),
		ARRAY_A
	);

	if ( empty( $rows ) ) {
		return;
	}

	$vapid = xin_push_get_vapid_keys();
	if ( empty( $vapid['publicKey'] ) || empty( $vapid['privateKey'] ) ) {
		return;
	}

	$payload = xin_push_build_payload( $novel_id, $eligible );
	if ( empty( $payload ) ) {
		return;
	}

	foreach ( $rows as $row ) {
		$result = xin_web_push_send( $row, $payload, $vapid );
		if ( is_wp_error( $result ) && $result->get_error_code() === 'xin_push_expired' ) {
			$wpdb->delete(
				$table,
				array(
					'novel_id'      => $novel_id,
					'endpoint_hash' => hash( 'sha256', $row['endpoint'] ),
				),
				array( '%d', '%s' )
			);
		}
	}
}

function xin_push_build_payload( $novel_id, $chapter_ids ) {
	$novel_id    = absint( $novel_id );
	$chapter_ids = array_values( array_filter( array_map( 'absint', (array) $chapter_ids ) ) );
	if ( ! $novel_id || empty( $chapter_ids ) ) {
		return array();
	}

	$novel_title = get_the_title( $novel_id );
	$icon        = get_site_icon_url( 192 );
	$icon        = $icon ? $icon : '';
	$count       = count( $chapter_ids );

	$sorted = $chapter_ids;
	usort(
		$sorted,
		static function ( $a, $b ) {
			$ta = (int) get_post_time( 'U', true, $a );
			$tb = (int) get_post_time( 'U', true, $b );
			if ( $ta === $tb ) {
				return $a - $b;
			}
			return $ta - $tb;
		}
	);
	$latest_id = (int) end( $sorted );
	$url       = $latest_id ? get_permalink( $latest_id ) : get_permalink( $novel_id );
	if ( ! $url ) {
		$url = get_permalink( $novel_id );
	}

	$title = ( $count === 1 )
		? __( 'Доступна 1 новая глава', 'xin-com' )
		: sprintf(
			/* translators: %d: number of new free chapters posted in this batch */
			__( 'Опубликовано %d новых глав', 'xin-com' ),
			$count
		);

	if ( $count === 1 ) {
		$chapter_title = get_the_title( $sorted[0] );
		$body          = $novel_title
			? sprintf(
				/* translators: 1: novel title, 2: chapter title */
				__( '%1$s — %2$s', 'xin-com' ),
				$novel_title,
				$chapter_title
			)
			: $chapter_title;
		return array(
			'title' => $title,
			'body'  => $body,
			'url'   => $url ? $url : home_url( '/' ),
			'icon'  => $icon,
			'tag'   => 'xin-novel-' . $novel_id . '-ch-' . (int) $sorted[0],
		);
	}

	return array(
		'title' => $title,
		/* translators: %s: novel title */
		'body'  => $novel_title
			? sprintf( __( '%s — вышли бесплатные главы', 'xin-com' ), $novel_title )
			: __( 'Вышли бесплатные главы', 'xin-com' ),
		'url'   => $url ? $url : home_url( '/' ),
		'icon'  => $icon,
		'tag'   => 'xin-novel-' . $novel_id . '-free-batch',
	);
}

function xin_push_render_bell_button( $novel_id ) {
	$supported = xin_push_is_supported_environment() && ! empty( xin_push_get_vapid_keys()['publicKey'] );
	$classes   = 'btn btn-outline xin-push-bell';
	if ( ! $supported ) {
		$classes .= ' is-unsupported';
	}
	?>
	<button
		type="button"
		class="<?php echo esc_attr( $classes ); ?>"
		data-novel-id="<?php echo esc_attr( $novel_id ); ?>"
		aria-pressed="false"
		aria-label="<?php echo esc_attr__( 'Уведомления о главах', 'xin-com' ); ?>"
		title="<?php echo esc_attr__( 'Браузерные уведомления, когда выходят бесплатные главы', 'xin-com' ); ?>"
	>
		<span class="xin-push-bell-icon"><?php xin_the_icon( 'bell' ); ?></span>
		<span class="xin-push-bell-label"><?php esc_html_e( 'Уведомлять', 'xin-com' ); ?></span>
	</button>
	<?php
}
