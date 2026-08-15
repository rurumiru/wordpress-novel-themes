<?php
/**
 * Installs the demo content from the command line.
 *
 * Prefer the plugin if you work through the admin: upload
 * `xi-demo-content.zip` under Plugins → Add New → Upload, then
 * Tools → Demo content → Install.
 *
 *   php demo/import-demo.php --wp=/var/www/site
 *   php demo/import-demo.php --wp=/var/www/site --status=draft --covers=0
 *
 * Options:
 *   --wp=PATH        WordPress root (the folder with wp-load.php). Required.
 *   --author=ID      Author for the created posts. Default: first administrator.
 *   --status=STATUS  publish (default) or draft.
 *   --covers=0       Skip cover and artwork generation.
 *
 * Everything created carries the post meta `_xin_demo = 1`, so
 * `php demo/remove-demo.php --wp=...` takes the site back to where it was.
 * Re-running updates the same records instead of duplicating them.
 */

if ( 'cli' !== php_sapi_name() ) {
	die( "CLI only\n" );
}

$opts = getopt( '', array( 'wp:', 'author::', 'status::', 'covers::' ) );

if ( empty( $opts['wp'] ) ) {
	fwrite( STDERR, "Usage: php demo/import-demo.php --wp=/path/to/wordpress\n" );
	exit( 1 );
}

$wp_root = rtrim( $opts['wp'], '\\/' );

if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
	fwrite( STDERR, "wp-load.php not found in {$wp_root}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_root . '/wp-load.php';
require_once __DIR__ . '/plugin/xi-demo-content/importer.php';

$result = xin_demo_install( array(
	'author_id' => isset( $opts['author'] ) ? (int) $opts['author'] : 0,
	'status'    => isset( $opts['status'] ) ? $opts['status'] : 'publish',
	'covers'    => ! isset( $opts['covers'] ) || '0' !== (string) $opts['covers'],
) );

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}

printf(
	"Site: %s\n\nnovels: %d\nchapters: %d\nposts: %d\nbanners: %d\nimages: %d\n\nRemove it later with: php demo/remove-demo.php --wp=%s\n",
	home_url(),
	$result['novels'],
	$result['chapters'],
	$result['posts'],
	$result['banners'],
	$result['images'],
	$wp_root
);
