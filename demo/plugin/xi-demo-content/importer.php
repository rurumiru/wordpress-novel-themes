<?php
/**
 * Shared demo import logic: used both by the admin plugin and by the CLI
 * scripts in demo/. Nothing here talks to the request directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The dataset. */
function xin_demo_data() {
	return require __DIR__ . '/content.php';
}

/** True when the XIN-Com theme (or anything providing its types) is active. */
function xin_demo_ready() {
	return post_type_exists( 'novel' ) && post_type_exists( 'chapter' );
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

/** A bold TrueType font on this host, or '' when there is none. */
function xin_demo_font() {
	$candidates = array(
		'/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
		'/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
		'/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
		'/usr/share/fonts/liberation/LiberationSans-Bold.ttf',
		'C:/Windows/Fonts/arialbd.ttf',
		'/Library/Fonts/Arial Bold.ttf',
	);
	foreach ( $candidates as $path ) {
		if ( file_exists( $path ) ) {
			return $path;
		}
	}
	return '';
}

/** Draws an abstract cover: vertical gradient, soft arcs, title at the bottom. */
function xin_demo_draw( $title, $hue, $w, $h, $file, $with_title = true ) {
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

/** Puts a generated file into the media library, returns the attachment ID. */
function xin_demo_attach( $file, $title, $parent ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';

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

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) );
	update_post_meta( $id, '_xin_demo', 1 );

	return (int) $id;
}

/**
 * Creates the demo content.
 *
 * @param array $args author_id, status, covers (bool).
 * @return array|WP_Error counts per type.
 */
function xin_demo_install( $args = array() ) {
	if ( ! xin_demo_ready() ) {
		return new WP_Error( 'xin_demo_theme', __( 'Тема XIN-Com не активна: типы записей «Новеллы» и «Главы» не зарегистрированы.', 'xi-demo' ) );
	}

	$args = wp_parse_args( $args, array(
		'author_id' => 0,
		'status'    => 'publish',
		'covers'    => true,
	) );

	if ( ! $args['author_id'] ) {
		$admins            = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
		$args['author_id'] = $admins ? (int) $admins[0] : get_current_user_id();
	}

	if ( $args['covers'] && ! function_exists( 'imagecreatetruecolor' ) ) {
		$args['covers'] = false;
	}

	@set_time_limit( 0 );
	wp_raise_memory_limit( 'image' );

	$data  = xin_demo_data();
	$tmp   = get_temp_dir();
	$count = array( 'novels' => 0, 'chapters' => 0, 'posts' => 0, 'banners' => 0, 'images' => 0 );

	wp_defer_term_counting( true );

	foreach ( $data['genres'] as $name ) {
		if ( ! term_exists( $name, 'genre' ) ) {
			wp_insert_term( $name, 'genre' );
		}
	}
	foreach ( $data['tags'] as $name ) {
		if ( ! term_exists( $name, 'novel_tag' ) ) {
			wp_insert_term( $name, 'novel_tag' );
		}
	}

	$day = 0;

	foreach ( $data['novels'] as $index => $novel ) {
		$existing = get_page_by_path( $novel['slug'], OBJECT, 'novel' );

		$novel_id = wp_insert_post( array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'novel',
			'post_status'  => $args['status'],
			'post_author'  => $args['author_id'],
			'post_title'   => $novel['title'],
			'post_name'    => $novel['slug'],
			'post_excerpt' => $novel['synopsis'],
			'post_content' => $novel['about'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 40 - $index * 3 ) * DAY_IN_SECONDS ),
		), true );

		if ( is_wp_error( $novel_id ) ) {
			continue;
		}

		$count['novels']++;

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

		if ( $args['covers'] && ! has_post_thumbnail( $novel_id ) ) {
			$file = $tmp . 'xin-cover-' . $novel_id . '.jpg';
			xin_demo_draw( $novel['title'], $novel['hue'], 800, 1200, $file );
			$cover = xin_demo_attach( $file, $novel['title'], $novel_id );
			if ( $cover ) {
				set_post_thumbnail( $novel_id, $cover );
				$count['images']++;
			}
		}

		if ( $args['covers'] && ! empty( $novel['featured'] ) && ! get_post_meta( $novel_id, '_xin_background', true ) ) {
			$wide = $tmp . 'xin-art-' . $novel_id . '.jpg';
			xin_demo_draw( $novel['title'], ( $novel['hue'] + 20 ) % 360, 1920, 720, $wide, false );
			$art = xin_demo_attach( $wide, $novel['title'] . ' — art', $novel_id );
			if ( $art ) {
				update_post_meta( $novel_id, '_xin_background', $art );
				$count['images']++;
			}
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

		foreach ( $novel['chapters'] as $position => $chapter ) {
			$number = $position + 1;
			$body   = '';
			foreach ( $chapter[1] as $paragraph ) {
				$body .= '<p>' . $paragraph . "</p>\n";
			}

			$day++;
			$chapter_id = wp_insert_post( array(
				'ID'           => isset( $known[ (string) (float) $number ] ) ? $known[ (string) (float) $number ] : 0,
				'post_type'    => 'chapter',
				'post_status'  => $args['status'],
				'post_author'  => $args['author_id'],
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

			$count['chapters']++;
		}
	}

	foreach ( $data['posts'] as $index => $post ) {
		$slug     = sanitize_title( $post['title'] );
		$existing = get_page_by_path( $slug, OBJECT, 'post' );

		$post_id = wp_insert_post( array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'post',
			'post_status'  => $args['status'],
			'post_author'  => $args['author_id'],
			'post_title'   => $post['title'],
			'post_name'    => $slug,
			'post_excerpt' => $post['excerpt'],
			'post_content' => $post['body'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $index + 1 ) * 2 * DAY_IN_SECONDS ),
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		$count['posts']++;
		update_post_meta( $post_id, '_xin_demo', 1 );
		update_post_meta( $post_id, '_xin_views', 1200 + $index * 640 );

		if ( $args['covers'] && ! has_post_thumbnail( $post_id ) ) {
			$file = $tmp . 'xin-post-' . $post_id . '.jpg';
			xin_demo_draw( $post['title'], ( 200 + $index * 38 ) % 360, 1600, 900, $file, false );
			$image = xin_demo_attach( $file, $post['title'], $post_id );
			if ( $image ) {
				set_post_thumbnail( $post_id, $image );
				$count['images']++;
			}
		}
	}

	if ( post_type_exists( 'xin_banner' ) ) {
		$links = array(
			'updates'       => get_post_type_archive_link( 'chapter' ),
			'novels'        => get_post_type_archive_link( 'novel' ),
			'become-author' => function_exists( 'xin_page_url' ) ? xin_page_url( 'become-author' ) : home_url( '/become-author/' ),
		);

		foreach ( $data['banners'] as $index => $banner ) {
			$slug     = 'demo-banner-' . ( $index + 1 );
			$existing = get_page_by_path( $slug, OBJECT, 'xin_banner' );

			$banner_id = wp_insert_post( array(
				'ID'          => $existing ? $existing->ID : 0,
				'post_type'   => 'xin_banner',
				'post_status' => 'publish',
				'post_author' => $args['author_id'],
				'post_title'  => $banner['title'],
				'post_name'   => $slug,
				'menu_order'  => $index,
			), true );

			if ( is_wp_error( $banner_id ) ) {
				continue;
			}

			$count['banners']++;
			update_post_meta( $banner_id, '_xin_demo', 1 );
			update_post_meta( $banner_id, '_xin_b_subtitle', $banner['subtitle'] );
			update_post_meta( $banner_id, '_xin_b_text', $banner['text'] );
			update_post_meta( $banner_id, '_xin_b_cta', $banner['cta'] );
			update_post_meta( $banner_id, '_xin_b_badge', $banner['badge'] );
			update_post_meta( $banner_id, '_xin_b_align', $banner['align'] );
			update_post_meta( $banner_id, '_xin_b_link', isset( $links[ $banner['link'] ] ) ? $links[ $banner['link'] ] : home_url( '/' ) );

			if ( $args['covers'] && ! has_post_thumbnail( $banner_id ) ) {
				$file = $tmp . 'xin-banner-' . $banner_id . '.jpg';
				xin_demo_draw( $banner['title'], $banner['hue'], 1920, 720, $file, false );
				$image = xin_demo_attach( $file, $banner['title'], $banner_id );
				if ( $image ) {
					set_post_thumbnail( $banner_id, $image );
					$count['images']++;
				}
			}
		}
	}

	wp_defer_term_counting( false );

	delete_transient( 'xin_site_stats' );
	wp_cache_flush();

	return $count;
}

/** IDs of everything the demo created. */
function xin_demo_ids() {
	$posts = get_posts( array(
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

	return array_unique( array_merge( $posts, $attachments ) );
}

/**
 * Removes the demo content.
 *
 * @param bool $trash Move to trash instead of deleting.
 * @return int How many records were removed.
 */
function xin_demo_remove( $trash = false ) {
	@set_time_limit( 0 );

	$ids  = xin_demo_ids();
	$done = 0;

	foreach ( $ids as $id ) {
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

	return $done;
}
