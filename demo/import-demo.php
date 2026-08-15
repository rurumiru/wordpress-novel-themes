<?php
/**
 * Installs the demo content into a site running the XI Novels theme.
 *
 *   php demo/import-demo.php --wp=/var/www/site
 *   php demo/import-demo.php --wp=/var/www/site --author=3 --status=draft
 *   php demo/import-demo.php --wp=/var/www/site --covers=0
 *
 * Options:
 *   --wp=PATH        WordPress root (the folder with wp-load.php). Required.
 *   --author=ID      Author for the created posts. Default: first administrator.
 *   --status=STATUS  publish (default) or draft.
 *   --covers=0       Skip cover and banner artwork generation.
 *   --dry-run        Report what would be created, write nothing.
 *
 * Everything created here is tagged with the post meta `_xin_demo = 1`, so
 * `php demo/remove-demo.php --wp=...` takes the site back to where it was.
 * Re-running updates the same records instead of duplicating them.
 */

if ( 'cli' !== php_sapi_name() ) {
	die( "CLI only\n" );
}

$opts = getopt( '', array( 'wp:', 'author::', 'status::', 'covers::', 'dry-run' ) );

if ( empty( $opts['wp'] ) ) {
	fwrite( STDERR, "Usage: php demo/import-demo.php --wp=/path/to/wordpress\n" );
	exit( 1 );
}

$wp_root = rtrim( $opts['wp'], '\\/' );
$status  = isset( $opts['status'] ) ? $opts['status'] : 'publish';
$covers  = ! isset( $opts['covers'] ) || '0' !== (string) $opts['covers'];
$dry     = isset( $opts['dry-run'] );

if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
	fwrite( STDERR, "wp-load.php not found in {$wp_root}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ( ! post_type_exists( 'novel' ) ) {
	fwrite( STDERR, "The XI Novels theme is not active: post type `novel` is missing.\n" );
	exit( 1 );
}

$data = require __DIR__ . '/content.php';

$author_id = isset( $opts['author'] ) ? (int) $opts['author'] : 0;
if ( ! $author_id ) {
	$admins    = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	$author_id = $admins ? (int) $admins[0] : 1;
}

/* ------------------------------------------------------------------ covers */

/** Finds a bold TrueType font on the host, or returns '' when there is none. */
function xin_demo_font() {
	$candidates = array(
		'C:/Windows/Fonts/arialbd.ttf',
		'/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
		'/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
		'/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
		'/Library/Fonts/Arial Bold.ttf',
	);
	foreach ( $candidates as $path ) {
		if ( file_exists( $path ) ) {
			return $path;
		}
	}
	return '';
}

function xin_demo_hsl( $img, $h, $s, $l ) {
	$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x = $c * ( 1 - abs( fmod( $h / 60, 2 ) - 1 ) );
	$m = $l - $c / 2;
	$r = $g = $b = 0;

	if ( $h < 60 )      { $r = $c; $g = $x; }
	elseif ( $h < 120 ) { $r = $x; $g = $c; }
	elseif ( $h < 180 ) { $g = $c; $b = $x; }
	elseif ( $h < 240 ) { $g = $x; $b = $c; }
	elseif ( $h < 300 ) { $r = $x; $b = $c; }
	else                { $r = $c; $b = $x; }

	return imagecolorallocate( $img, (int) round( ( $r + $m ) * 255 ), (int) round( ( $g + $m ) * 255 ), (int) round( ( $b + $m ) * 255 ) );
}

/** Draws an abstract cover: vertical gradient, soft arcs, title at the bottom. */
function xin_demo_cover( $title, $hue, $w, $h, $file, $with_title = true ) {
	$img = imagecreatetruecolor( $w, $h );

	for ( $y = 0; $y < $h; $y++ ) {
		$t   = $y / $h;
		$col = xin_demo_hsl( $img, ( $hue + $t * 34 ) % 360, 0.42, 0.30 - $t * 0.20 );
		imagefilledrectangle( $img, 0, $y, $w, $y, $col );
	}

	imagesetthickness( $img, max( 2, (int) round( $w / 90 ) ) );
	for ( $i = 0; $i < 4; $i++ ) {
		$col = xin_demo_hsl( $img, ( $hue + 18 * $i ) % 360, 0.55, 0.52 );
		$r   = (int) ( $w * ( 0.85 + 0.42 * $i ) );
		imagearc( $img, (int) ( $w * 0.22 ), (int) ( $h * 0.30 ), $r, $r, 0, 360, $col );
	}

	$fade = imagecreatetruecolor( $w, $h );
	imagealphablending( $fade, false );
	imagesavealpha( $fade, true );
	for ( $y = 0; $y < $h; $y++ ) {
		$t     = max( 0, ( $y / $h - 0.35 ) / 0.65 );
		$alpha = (int) round( 127 - 118 * $t );
		imagefilledrectangle( $fade, 0, $y, $w, $y, imagecolorallocatealpha( $fade, 8, 9, 12, $alpha ) );
	}
	imagealphablending( $img, true );
	imagecopy( $img, $fade, 0, 0, 0, 0, $w, $h );
	imagedestroy( $fade );

	$font = xin_demo_font();
	if ( $with_title && $font ) {
		$size  = max( 15, (int) round( $w / 15 ) );
		$white = imagecolorallocate( $img, 255, 255, 255 );
		$words = preg_split( '/\s+/u', $title );
		$lines = array();
		$line  = '';

		foreach ( $words as $word ) {
			$try = $line ? $line . ' ' . $word : $word;
			$box = imagettfbbox( $size, 0, $font, $try );
			if ( $box[2] - $box[0] > $w - $size * 2 && $line ) {
				$lines[] = $line;
				$line    = $word;
			} else {
				$line = $try;
			}
		}
		if ( $line ) {
			$lines[] = $line;
		}

		$step = (int) round( $size * 1.35 );
		$y    = $h - (int) round( $size * 1.5 ) - ( count( $lines ) - 1 ) * $step;
		foreach ( $lines as $text ) {
			imagettftext( $img, $size, 0, $size, $y, $white, $font, $text );
			$y += $step;
		}
	}

	imagejpeg( $img, $file, 88 );
	imagedestroy( $img );
}

/** Puts a generated file into the media library and returns its attachment ID. */
function xin_demo_attach( $file, $title, $parent ) {
	$upload = wp_upload_dir();
	$name   = wp_unique_filename( $upload['path'], basename( $file ) );
	$dest   = $upload['path'] . '/' . $name;

	copy( $file, $dest );
	unlink( $file );

	$id = wp_insert_attachment( array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $dest, $parent );

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) );
	update_post_meta( $id, '_xin_demo', 1 );

	return (int) $id;
}

/* ------------------------------------------------------------------ import */

if ( ! function_exists( 'imagecreatetruecolor' ) && $covers ) {
	fwrite( STDERR, "GD is not available, running with --covers=0\n" );
	$covers = false;
}

printf( "Site: %s\nAuthor ID: %d\nStatus: %s\nCovers: %s\n%s\n\n",
	home_url(), $author_id, $status, $covers ? 'generated' : 'skipped', $dry ? "DRY RUN\n" : '' );

wp_defer_term_counting( true );

foreach ( $data['genres'] as $name ) {
	if ( ! term_exists( $name, 'genre' ) && ! $dry ) {
		wp_insert_term( $name, 'genre' );
	}
}
foreach ( $data['tags'] as $name ) {
	if ( ! term_exists( $name, 'novel_tag' ) && ! $dry ) {
		wp_insert_term( $name, 'novel_tag' );
	}
}
printf( "genres: %d, tags: %d\n", count( $data['genres'] ), count( $data['tags'] ) );

$tmp     = sys_get_temp_dir();
$novels  = 0;
$chapters = 0;
$day     = 0;

foreach ( $data['novels'] as $novel ) {
	printf( "+ %s\n", $novel['title'] );
	$novels++;

	if ( $dry ) {
		$chapters += count( $novel['chapters'] );
		continue;
	}

	$existing = get_page_by_path( $novel['slug'], OBJECT, 'novel' );

	$novel_id = wp_insert_post( array(
		'ID'           => $existing ? $existing->ID : 0,
		'post_type'    => 'novel',
		'post_status'  => $status,
		'post_author'  => $author_id,
		'post_title'   => $novel['title'],
		'post_name'    => $novel['slug'],
		'post_excerpt' => $novel['synopsis'],
		'post_content' => $novel['about'],
		'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 40 - $novels * 3 ) * DAY_IN_SECONDS ),
	), true );

	if ( is_wp_error( $novel_id ) ) {
		fwrite( STDERR, '  ! ' . $novel_id->get_error_message() . "\n" );
		continue;
	}

	update_post_meta( $novel_id, '_xin_demo', 1 );
	update_post_meta( $novel_id, '_xin_author_name', $novel['author'] );
	update_post_meta( $novel_id, '_xin_translator', $novel['transl'] );
	update_post_meta( $novel_id, '_xin_year', $novel['year'] );
	update_post_meta( $novel_id, '_xin_rating', $novel['rating'] );
	update_post_meta( $novel_id, '_xin_rating_count', $novel['votes'] );
	update_post_meta( $novel_id, '_xin_views', $novel['views'] );
	update_post_meta( $novel_id, '_xin_featured', empty( $novel['featured'] ) ? 0 : 1 );
	update_post_meta( $novel_id, '_xin_adult', 0 );

	wp_set_object_terms( $novel_id, $novel['genres'], 'genre' );
	wp_set_object_terms( $novel_id, $novel['tags'], 'novel_tag' );
	wp_set_object_terms( $novel_id, $novel['status'], 'novel_status' );

	if ( $covers && ! has_post_thumbnail( $novel_id ) ) {
		$file = $tmp . '/xin-cover-' . $novel_id . '.jpg';
		xin_demo_cover( $novel['title'], $novel['hue'], 800, 1200, $file );
		set_post_thumbnail( $novel_id, xin_demo_attach( $file, $novel['title'], $novel_id ) );
	}

	if ( $covers && ! empty( $novel['featured'] ) && ! get_post_meta( $novel_id, '_xin_background', true ) ) {
		$wide = $tmp . '/xin-art-' . $novel_id . '.jpg';
		xin_demo_cover( $novel['title'], ( $novel['hue'] + 20 ) % 360, 1920, 720, $wide, false );
		update_post_meta( $novel_id, '_xin_background', xin_demo_attach( $wide, $novel['title'] . ' — art', $novel_id ) );
	}

	$known = array();
	foreach ( get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_xin_novel', 'value' => $novel_id ) ),
	) ) as $id ) {
		$known[ (string) (float) get_post_meta( $id, '_xin_number', true ) ] = $id;
	}

	foreach ( $novel['chapters'] as $index => $chapter ) {
		$number = $index + 1;
		$body   = '';
		foreach ( $chapter[1] as $paragraph ) {
			$body .= '<p>' . $paragraph . "</p>\n";
		}

		$day++;
		$chapter_id = wp_insert_post( array(
			'ID'           => isset( $known[ (string) (float) $number ] ) ? $known[ (string) (float) $number ] : 0,
			'post_type'    => 'chapter',
			'post_status'  => $status,
			'post_author'  => $author_id,
			'post_title'   => $chapter[0],
			'post_content' => $body,
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 60 - $day ) * 8 * HOUR_IN_SECONDS ),
		), true );

		if ( is_wp_error( $chapter_id ) ) {
			continue;
		}

		update_post_meta( $chapter_id, '_xin_demo', 1 );
		update_post_meta( $chapter_id, '_xin_novel', $novel_id );
		update_post_meta( $chapter_id, '_xin_number', $number );
		update_post_meta( $chapter_id, '_xin_locked', $number === count( $novel['chapters'] ) ? 1 : 0 );
		update_post_meta( $chapter_id, '_xin_views', (int) round( $novel['views'] / ( $number + 3 ) ) );

		$chapters++;
	}
}

printf( "novels: %d, chapters: %d\n", $novels, $chapters );

$posts = 0;
foreach ( $data['posts'] as $index => $post ) {
	$posts++;
	if ( $dry ) {
		continue;
	}

	$slug     = sanitize_title( $post['title'] );
	$existing = get_page_by_path( $slug, OBJECT, 'post' );

	$post_id = wp_insert_post( array(
		'ID'           => $existing ? $existing->ID : 0,
		'post_type'    => 'post',
		'post_status'  => $status,
		'post_author'  => $author_id,
		'post_title'   => $post['title'],
		'post_name'    => $slug,
		'post_excerpt' => $post['excerpt'],
		'post_content' => $post['body'],
		'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $index + 1 ) * 2 * DAY_IN_SECONDS ),
	), true );

	if ( is_wp_error( $post_id ) ) {
		continue;
	}

	update_post_meta( $post_id, '_xin_demo', 1 );
	update_post_meta( $post_id, '_xin_views', 1200 + $index * 640 );

	if ( $covers && ! has_post_thumbnail( $post_id ) ) {
		$file = $tmp . '/xin-post-' . $post_id . '.jpg';
		xin_demo_cover( $post['title'], ( 200 + $index * 38 ) % 360, 1600, 900, $file, false );
		set_post_thumbnail( $post_id, xin_demo_attach( $file, $post['title'], $post_id ) );
	}
}
printf( "posts: %d\n", $posts );

$banners = 0;
foreach ( $data['banners'] as $index => $banner ) {
	$banners++;
	if ( $dry || ! post_type_exists( 'xin_banner' ) ) {
		continue;
	}

	$slug     = sanitize_title( 'demo-banner-' . ( $index + 1 ) );
	$existing = get_page_by_path( $slug, OBJECT, 'xin_banner' );

	$links = array(
		'updates'       => get_post_type_archive_link( 'chapter' ),
		'novels'        => get_post_type_archive_link( 'novel' ),
		'become-author' => function_exists( 'xin_page_url' ) ? xin_page_url( 'become-author' ) : home_url( '/become-author/' ),
	);

	$banner_id = wp_insert_post( array(
		'ID'          => $existing ? $existing->ID : 0,
		'post_type'   => 'xin_banner',
		'post_status' => 'publish',
		'post_author' => $author_id,
		'post_title'  => $banner['title'],
		'post_name'   => $slug,
		'menu_order'  => $index,
	), true );

	if ( is_wp_error( $banner_id ) ) {
		continue;
	}

	update_post_meta( $banner_id, '_xin_demo', 1 );
	update_post_meta( $banner_id, '_xin_b_subtitle', $banner['subtitle'] );
	update_post_meta( $banner_id, '_xin_b_text', $banner['text'] );
	update_post_meta( $banner_id, '_xin_b_cta', $banner['cta'] );
	update_post_meta( $banner_id, '_xin_b_badge', $banner['badge'] );
	update_post_meta( $banner_id, '_xin_b_align', $banner['align'] );
	update_post_meta( $banner_id, '_xin_b_link', isset( $links[ $banner['link'] ] ) ? $links[ $banner['link'] ] : home_url( '/' ) );

	if ( $covers && ! has_post_thumbnail( $banner_id ) ) {
		$file = $tmp . '/xin-banner-' . $banner_id . '.jpg';
		xin_demo_cover( $banner['title'], $banner['hue'], 1920, 720, $file, false );
		set_post_thumbnail( $banner_id, xin_demo_attach( $file, $banner['title'], $banner_id ) );
	}
}
printf( "banners: %d\n", $banners );

wp_defer_term_counting( false );

if ( ! $dry ) {
	delete_transient( 'xin_site_stats' );
	wp_cache_flush();
	flush_rewrite_rules( true );
}

echo "\nDone. Remove it later with: php demo/remove-demo.php --wp=" . $wp_root . "\n";
