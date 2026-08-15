<?php
/**
 * Router for PHP's built-in server: serve real files, hand everything else to
 * WordPress. Usage:
 *
 *   php -S localhost:8080 -t /path/to/wordpress tools/dev-router.php
 */

$root = rtrim( $_SERVER['DOCUMENT_ROOT'], '\\/' );
$uri  = urldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$path = $root . str_replace( '/', DIRECTORY_SEPARATOR, $uri );

if ( is_dir( $path ) && file_exists( rtrim( $path, '\\/' ) . '/index.php' ) ) {
	$_SERVER['SCRIPT_NAME'] = rtrim( $uri, '/' ) . '/index.php';
	require rtrim( $path, '\\/' ) . '/index.php';
	return true;
}

if ( '/' !== $uri && file_exists( $path ) && ! is_dir( $path ) ) {
	return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
