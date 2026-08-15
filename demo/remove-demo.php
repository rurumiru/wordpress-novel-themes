<?php
/**
 * Removes the demo content from the command line.
 *
 * Prefer the plugin if you work through the admin:
 * Tools → Demo content → Remove.
 *
 *   php demo/remove-demo.php --wp=/var/www/site
 *   php demo/remove-demo.php --wp=/var/www/site --dry-run
 *   php demo/remove-demo.php --wp=/var/www/site --trash
 *
 * Only records carrying the post meta `_xin_demo = 1` are touched, so anything
 * you wrote yourself stays where it is.
 */

if ( 'cli' !== php_sapi_name() ) {
	die( "CLI only\n" );
}

$opts = getopt( '', array( 'wp:', 'dry-run', 'trash' ) );

if ( empty( $opts['wp'] ) ) {
	fwrite( STDERR, "Usage: php demo/remove-demo.php --wp=/path/to/wordpress\n" );
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

$ids = xin_demo_ids();

if ( ! $ids ) {
	echo "Nothing to remove: no records tagged as demo.\n";
	exit( 0 );
}

$by_type = array();
foreach ( $ids as $id ) {
	$type             = get_post_type( $id );
	$by_type[ $type ] = isset( $by_type[ $type ] ) ? $by_type[ $type ] + 1 : 1;
}

foreach ( $by_type as $type => $count ) {
	printf( "%-12s %d\n", $type, $count );
}

if ( isset( $opts['dry-run'] ) ) {
	echo "\nDRY RUN — nothing was removed.\n";
	exit( 0 );
}

$done = xin_demo_remove( isset( $opts['trash'] ) );

printf( "\nRemoved: %d records%s.\n", $done, isset( $opts['trash'] ) ? ' (moved to trash)' : '' );
