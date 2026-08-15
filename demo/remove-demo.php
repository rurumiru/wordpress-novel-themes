<?php
/**
 * Removes everything the demo import created.
 *
 *   php demo/remove-demo.php --wp=/var/www/site
 *   php demo/remove-demo.php --wp=/var/www/site --dry-run
 *   php demo/remove-demo.php --wp=/var/www/site --trash
 *
 * Only records carrying the post meta `_xin_demo = 1` are touched, so anything
 * you wrote yourself stays where it is. By default records are deleted for
 * good; `--trash` moves them to the trash instead.
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
$dry     = isset( $opts['dry-run'] );
$trash   = isset( $opts['trash'] );

if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
	fwrite( STDERR, "wp-load.php not found in {$wp_root}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_root . '/wp-load.php';

$ids = get_posts( array(
	'post_type'      => 'any',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_query'     => array( array( 'key' => '_xin_demo', 'value' => '1' ) ),
) );

$attachments = get_posts( array(
	'post_type'      => 'attachment',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_query'     => array( array( 'key' => '_xin_demo', 'value' => '1' ) ),
) );

$all = array_unique( array_merge( $ids, $attachments ) );

if ( ! $all ) {
	echo "Nothing to remove: no records tagged as demo.\n";
	exit( 0 );
}

$by_type = array();
foreach ( $all as $id ) {
	$type              = get_post_type( $id );
	$by_type[ $type ]  = isset( $by_type[ $type ] ) ? $by_type[ $type ] + 1 : 1;
}

foreach ( $by_type as $type => $count ) {
	printf( "%-12s %d\n", $type, $count );
}

if ( $dry ) {
	echo "\nDRY RUN — nothing was removed.\n";
	exit( 0 );
}

$done = 0;
foreach ( $all as $id ) {
	if ( 'attachment' === get_post_type( $id ) ) {
		wp_delete_attachment( $id, ! $trash );
	} elseif ( $trash ) {
		wp_trash_post( $id );
	} else {
		wp_delete_post( $id, true );
	}
	$done++;
}

delete_transient( 'xin_site_stats' );
wp_cache_flush();

printf( "\nRemoved: %d records%s.\n", $done, $trash ? ' (moved to trash)' : '' );
