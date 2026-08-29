<?php
/**
 * Bulk importer for titles and chapters.
 *
 *   php tools/import-novels.php --wp=/path/to/wordpress --file=data.json
 *   php tools/import-novels.php --wp=/var/www/site --file=novels.json --author=2 --dry-run
 *
 * Options:
 *   --wp=PATH        WordPress root (the folder with wp-load.php). Required.
 *   --file=PATH      JSON manifest, or CSV when --format=csv. Required.
 *   --format=json    json (default) or csv.
 *   --author=ID      Author for imported posts. Default: first administrator.
 *   --status=STATUS  publish (default) or draft.
 *   --media=1        Download covers and artwork. Set to 0 to skip images.
 *   --dry-run        Parse and report, write nothing.
 *
 * Chapters from files instead of a manifest:
 *
 *   php tools/import-novels.php --wp=/var/www/site --from-zip=chapters.zip --novel="Title"
 *   php tools/import-novels.php --wp=/var/www/site --from-dir=./chapters --novel-slug=my-title
 *
 *   --from-dir=PATH  Folder with .txt / .html / .md chapter files.
 *   --from-zip=PATH  Archive with the same. A single wrapper folder inside is
 *                    unwrapped; sub-folders mean one title per folder.
 *   --novel=NAME     Target title. Created when it does not exist yet.
 *   --novel-slug=S   Target title by slug.
 *   --novel-id=ID    Target title by post ID.
 *   --start=N        First number when file names carry none. Default 1.
 *   --locked-from=N  Mark chapters from this number on as PLUS.
 *   --encoding=ENC   Force the source encoding, e.g. windows-1251.
 *
 * File names give the number and the title: `001. The shard.txt`,
 * `001 - The shard.txt`, `12.5_Side story.html`, `Chapter 3 - Name.md`,
 * `Глава 3. Название.txt`, or a bare `The shard.txt` numbered by file order.
 *
 * JSON shape — an array of titles:
 *
 * [
 *   {
 *     "title": "Seal of the Ninth Heaven",
 *     "slug": "seal-of-the-ninth-heaven",
 *     "synopsis": "One line for catalog cards.",
 *     "description": "<p>Full description, HTML allowed.</p>",
 *     "author_name": "Liu Chenxing",
 *     "original_title": "第九天印",
 *     "translator": "East Wind team",
 *     "year": 2021,
 *     "status": "ongoing",
 *     "genres": ["Fantasy", "Xianxia"],
 *     "tags": ["cultivation", "rebirth"],
 *     "adult": false,
 *     "featured": true,
 *     "views": 128400,
 *     "rating": 4.7,
 *     "rating_count": 214,
 *     "cover": "https://example.com/cover.jpg",
 *     "artwork": "/home/user/art/wide.jpg",
 *     "chapters": [
 *       { "number": 1, "title": "The shard", "content": "<p>...</p>", "date": "2026-01-05 10:00:00" },
 *       { "number": 2, "title": "First snow", "content_file": "chapters/002.html", "locked": true }
 *     ]
 *   }
 * ]
 *
 * CSV shape — one row per chapter, the title columns repeat:
 *
 *   novel_title,novel_slug,synopsis,genres,status,cover,chapter_number,chapter_title,chapter_file,locked
 *
 * `genres` and `tags` are separated by `|`. Paths in `content_file`, `cover`
 * and `artwork` may be absolute or relative to the manifest file.
 *
 * The script is idempotent: a title is matched by slug (or by exact title when
 * no slug is given), a chapter by its number inside that title. Re-running the
 * same manifest updates instead of duplicating.
 */

if ( 'cli' !== php_sapi_name() ) {
	die( "CLI only\n" );
}

$opts = getopt( '', array(
	'wp:', 'file:', 'format::', 'author::', 'status::', 'media::', 'dry-run',
	'from-dir:', 'from-zip:', 'novel::', 'novel-slug::', 'novel-id::', 'start::', 'locked-from::', 'encoding::',
) );

$files_mode = isset( $opts['from-dir'] ) || isset( $opts['from-zip'] );

if ( empty( $opts['wp'] ) || ( empty( $opts['file'] ) && ! $files_mode ) ) {
	fwrite( STDERR, "Usage:\n" );
	fwrite( STDERR, "  php tools/import-novels.php --wp=/path/to/wordpress --file=data.json\n" );
	fwrite( STDERR, "  php tools/import-novels.php --wp=/path/to/wordpress --from-zip=chapters.zip --novel=\"Title\"\n" );
	fwrite( STDERR, "  php tools/import-novels.php --wp=/path/to/wordpress --from-dir=./chapters --novel-slug=my-title\n" );
	exit( 1 );
}

$wp_root  = rtrim( $opts['wp'], '\\/' );
$manifest = isset( $opts['file'] ) ? $opts['file'] : '';
$format   = isset( $opts['format'] ) ? strtolower( $opts['format'] ) : 'json';
$status   = isset( $opts['status'] ) ? $opts['status'] : 'publish';
$with_med = ! isset( $opts['media'] ) || '0' !== (string) $opts['media'];
$dry      = isset( $opts['dry-run'] );

if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
	fwrite( STDERR, "wp-load.php not found in {$wp_root}\n" );
	exit( 1 );
}
if ( ! $files_mode && ! file_exists( $manifest ) ) {
	fwrite( STDERR, "Manifest not found: {$manifest}\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
require $wp_root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if ( ! post_type_exists( 'novel' ) ) {
	fwrite( STDERR, "The XIN-Com theme is not active: post type `novel` is missing.\n" );
	exit( 1 );
}

$base_dir = ( $manifest && file_exists( $manifest ) ) ? rtrim( dirname( realpath( $manifest ) ), '\\/' ) : getcwd();

$author_id = isset( $opts['author'] ) ? (int) $opts['author'] : 0;
if ( ! $author_id ) {
	$admins    = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	$author_id = $admins ? (int) $admins[0] : 1;
}

/** Reads the manifest into a uniform array of titles. */
function xin_import_read( $file, $format ) {
	if ( 'csv' === $format ) {
		return xin_import_read_csv( $file );
	}

	$data = json_decode( file_get_contents( $file ), true );
	if ( ! is_array( $data ) ) {
		fwrite( STDERR, "Cannot parse JSON: " . json_last_error_msg() . "\n" );
		exit( 1 );
	}
	return isset( $data['novels'] ) ? $data['novels'] : $data;
}

/** Groups a flat chapter-per-row CSV into titles. */
function xin_import_read_csv( $file ) {
	$fh = fopen( $file, 'r' );
	if ( ! $fh ) {
		fwrite( STDERR, "Cannot open {$file}\n" );
		exit( 1 );
	}

	$head   = fgetcsv( $fh );
	$novels = array();

	while ( ( $row = fgetcsv( $fh ) ) !== false ) {
		$r = array_combine( $head, $row );
		if ( ! $r ) {
			continue;
		}

		$key = ! empty( $r['novel_slug'] ) ? $r['novel_slug'] : $r['novel_title'];
		if ( ! isset( $novels[ $key ] ) ) {
			$novels[ $key ] = array(
				'title'       => $r['novel_title'],
				'slug'        => isset( $r['novel_slug'] ) ? $r['novel_slug'] : '',
				'synopsis'    => isset( $r['synopsis'] ) ? $r['synopsis'] : '',
				'description' => isset( $r['description'] ) ? $r['description'] : '',
				'author_name' => isset( $r['author_name'] ) ? $r['author_name'] : '',
				'status'      => isset( $r['status'] ) ? $r['status'] : '',
				'genres'      => isset( $r['genres'] ) ? array_filter( array_map( 'trim', explode( '|', $r['genres'] ) ) ) : array(),
				'tags'        => isset( $r['tags'] ) ? array_filter( array_map( 'trim', explode( '|', $r['tags'] ) ) ) : array(),
				'cover'       => isset( $r['cover'] ) ? $r['cover'] : '',
				'artwork'     => isset( $r['artwork'] ) ? $r['artwork'] : '',
				'chapters'    => array(),
			);
		}

		if ( ! empty( $r['chapter_title'] ) || ! empty( $r['chapter_file'] ) ) {
			$novels[ $key ]['chapters'][] = array(
				'number'       => isset( $r['chapter_number'] ) ? (float) $r['chapter_number'] : 0,
				'title'        => isset( $r['chapter_title'] ) ? $r['chapter_title'] : '',
				'content_file' => isset( $r['chapter_file'] ) ? $r['chapter_file'] : '',
				'content'      => isset( $r['chapter_content'] ) ? $r['chapter_content'] : '',
				'locked'       => ! empty( $r['locked'] ),
				'date'         => isset( $r['date'] ) ? $r['date'] : '',
			);
		}
	}

	fclose( $fh );
	return array_values( $novels );
}

/** Resolves a path relative to the manifest, leaves URLs untouched. */
function xin_import_path( $value, $base ) {
	if ( ! $value ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $value ) ) {
		return $value;
	}
	if ( file_exists( $value ) ) {
		return $value;
	}
	$candidate = $base . '/' . ltrim( $value, '\\/' );
	return file_exists( $candidate ) ? $candidate : '';
}

/** Attaches an image from a URL or a local file, returns the attachment ID. */
function xin_import_media( $source, $post_id, $title ) {
	if ( ! $source ) {
		return 0;
	}

	if ( preg_match( '#^https?://#i', $source ) ) {
		$tmp = download_url( $source, 60 );
		if ( is_wp_error( $tmp ) ) {
			fwrite( STDERR, "  ! cannot download {$source}: " . $tmp->get_error_message() . "\n" );
			return 0;
		}
		$name = basename( parse_url( $source, PHP_URL_PATH ) );
		$file = array( 'name' => $name ? $name : 'cover.jpg', 'tmp_name' => $tmp );
	} else {
		// media_handle_sideload moves the file, so hand it a copy.
		$tmp = wp_tempnam( basename( $source ) );
		if ( ! copy( $source, $tmp ) ) {
			fwrite( STDERR, "  ! cannot read {$source}\n" );
			return 0;
		}
		$file = array( 'name' => basename( $source ), 'tmp_name' => $tmp );
	}

	$id = media_handle_sideload( $file, $post_id, $title );

	if ( is_wp_error( $id ) ) {
		if ( file_exists( $tmp ) ) {
			@unlink( $tmp );
		}
		fwrite( STDERR, '  ! media error: ' . $id->get_error_message() . "\n" );
		return 0;
	}

	return (int) $id;
}

/* ------------------------------------------------------- files and archives */

/** Text or markup file into chapter HTML. */
function xin_import_text_to_html( $raw, $ext, $encoding = '' ) {
	// Strip a UTF-8 byte order mark, then bring the text to UTF-8 if it is not
	// there already. Exports from Word and older editors are usually cp1251.
	$raw = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );

	if ( $encoding && 'utf-8' !== strtolower( $encoding ) ) {
		$raw = @iconv( $encoding, 'UTF-8//TRANSLIT', $raw );
	} elseif ( ! mb_check_encoding( $raw, 'UTF-8' ) ) {
		$converted = @iconv( 'windows-1251', 'UTF-8//TRANSLIT', $raw );
		$raw       = false === $converted ? $raw : $converted;
	}

	$raw = str_replace( array( "\r\n", "\r" ), "\n", trim( $raw ) );

	if ( in_array( $ext, array( 'html', 'htm' ), true ) ) {
		// Keep only what lives inside <body> when a full document is given.
		if ( preg_match( '#<body[^>]*>(.*?)</body>#is', $raw, $m ) ) {
			$raw = $m[1];
		}
		return trim( $raw );
	}

	if ( 'md' === $ext ) {
		// Escape first, convert second: doing it the other way round turns the
		// tags produced here back into visible &lt;strong&gt; text.
		$raw = esc_html( $raw );
		$raw = preg_replace( '/^###\s+(.+)$/m', '<h3>$1</h3>', $raw );
		$raw = preg_replace( '/^##\s+(.+)$/m', '<h2>$1</h2>', $raw );
		$raw = preg_replace( '/^#\s+(.+)$/m', '<h2>$1</h2>', $raw );
		$raw = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $raw );
		$raw = preg_replace( '/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $raw );
	}

	// A blank line starts a new paragraph; a single newline inside a paragraph
	// becomes a line break, which is how dialogue is usually typed.
	$parts = preg_split( '/\n{2,}/', $raw );
	$out   = array();

	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( '' === $part ) {
			continue;
		}
		if ( preg_match( '/^<(h[1-6]|p|div|blockquote|ul|ol|figure|hr)\b/i', $part ) ) {
			$out[] = $part;
			continue;
		}
		// Markdown was escaped above; plain text still needs it here.
		$text  = 'md' === $ext ? $part : esc_html( $part );
		$out[] = '<p>' . str_replace( "\n", "<br>\n", $text ) . '</p>';
	}

	return implode( "\n", $out );
}

/**
 * Chapter number and title from a file name.
 *
 * Understands `001. Title`, `001 - Title`, `12.5_Title`, `Chapter 3 - Title`,
 * `Глава 3. Название` and a bare `Title`. Returns null for the number when the
 * name carries none, and the caller falls back to file order.
 */
function xin_import_parse_name( $name ) {
	$name = preg_replace( '/\.[a-z0-9]+$/i', '', $name );
	$name = str_replace( '_', ' ', $name );
	$name = trim( $name );

	$number = null;
	$title  = $name;

	if ( preg_match( '/^(?:chapter|глава|гл\.?)?\s*[#№]?\s*(\d+(?:[.,]\d+)?)\s*(?:[.)\-–—:]\s*|\s+)(.*)$/iu', $name, $m ) ) {
		$number = (float) str_replace( ',', '.', $m[1] );
		$title  = trim( $m[2] );
	}

	if ( '' === $title ) {
		$title = null === $number
			? $name
			: sprintf( 'Chapter %s', rtrim( rtrim( number_format( $number, 1, '.', '' ), '0' ), '.' ) );
	}

	return array( $number, $title );
}

/** Sorts file names the way a person would: 2 before 10. */
function xin_import_sort_files( $files ) {
	usort( $files, static function ( $a, $b ) {
		return strnatcasecmp( basename( $a ), basename( $b ) );
	} );
	return $files;
}

/** Chapter files inside a folder, non-recursive. */
function xin_import_scan_dir( $dir ) {
	$files = array();
	foreach ( (array) glob( rtrim( $dir, '\\/' ) . '/*' ) as $path ) {
		if ( is_dir( $path ) ) {
			continue;
		}
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( in_array( $ext, array( 'txt', 'html', 'htm', 'md' ), true ) ) {
			$files[] = $path;
		}
	}
	return xin_import_sort_files( $files );
}

/** Sub-folders that contain chapter files — one title per folder. */
function xin_import_scan_subdirs( $dir ) {
	$dirs = array();
	foreach ( (array) glob( rtrim( $dir, '\\/' ) . '/*', GLOB_ONLYDIR ) as $path ) {
		if ( xin_import_scan_dir( $path ) ) {
			$dirs[] = $path;
		}
	}
	sort( $dirs, SORT_NATURAL | SORT_FLAG_CASE );
	return $dirs;
}

/** Unpacks an archive into a temporary folder and returns its path. */
function xin_import_unzip( $zip_path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		fwrite( STDERR, "The zip extension is not available in this PHP build.\n" );
		exit( 1 );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path ) ) {
		fwrite( STDERR, "Cannot open archive: {$zip_path}\n" );
		exit( 1 );
	}

	$target = rtrim( sys_get_temp_dir(), '\\/' ) . '/xin-import-' . wp_generate_password( 8, false );
	wp_mkdir_p( $target );

	// Extracting entry by entry keeps names out of parent folders and drops
	// system files that archivers add on their own.
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$entry = $zip->getNameIndex( $i );
		if ( ! $entry || '/' === substr( $entry, -1 ) ) {
			continue;
		}
		if ( false !== strpos( $entry, '..' ) || 0 === strpos( $entry, '__MACOSX' ) || '.DS_Store' === basename( $entry ) ) {
			continue;
		}

		$dest = $target . '/' . $entry;
		wp_mkdir_p( dirname( $dest ) );

		$stream = $zip->getStream( $entry );
		if ( ! $stream ) {
			continue;
		}
		file_put_contents( $dest, stream_get_contents( $stream ) );
		fclose( $stream );
	}

	$zip->close();

	// Archives are often packed with a single wrapper folder inside.
	$inner = glob( $target . '/*', GLOB_ONLYDIR );
	if ( 1 === count( (array) $inner ) && ! xin_import_scan_dir( $target ) ) {
		return $inner[0];
	}

	return $target;
}

/** Removes a temporary folder tree. */
function xin_import_rmdir( $dir ) {
	foreach ( (array) glob( rtrim( $dir, '\\/' ) . '/*' ) as $path ) {
		is_dir( $path ) ? xin_import_rmdir( $path ) : @unlink( $path );
	}
	@rmdir( $dir );
}

/** Finds a title by id, slug or exact name; creates one when missing. */
function xin_import_resolve_novel( $opts, $author_id, $status, $fallback_title ) {
	if ( ! empty( $opts['novel-id'] ) ) {
		$post = get_post( (int) $opts['novel-id'] );
		if ( $post && 'novel' === $post->post_type ) {
			return (int) $post->ID;
		}
		fwrite( STDERR, "Title with ID {$opts['novel-id']} not found.\n" );
		exit( 1 );
	}

	$name = ! empty( $opts['novel'] ) ? $opts['novel'] : $fallback_title;
	$slug = ! empty( $opts['novel-slug'] ) ? sanitize_title( $opts['novel-slug'] ) : sanitize_title( $name );

	$existing = get_page_by_path( $slug, OBJECT, 'novel' );
	if ( $existing ) {
		return (int) $existing->ID;
	}

	$found = get_posts( array(
		'post_type'      => 'novel',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'title'          => $name,
		'fields'         => 'ids',
	) );
	if ( $found ) {
		return (int) $found[0];
	}

	$novel_id = wp_insert_post( array(
		'post_type'   => 'novel',
		'post_status' => $status,
		'post_author' => $author_id,
		'post_title'  => $name,
		'post_name'   => $slug,
	), true );

	if ( is_wp_error( $novel_id ) ) {
		fwrite( STDERR, '! ' . $novel_id->get_error_message() . "\n" );
		exit( 1 );
	}

	printf( "+ title created: %s\n", $name );
	return (int) $novel_id;
}

/** Imports one folder of chapter files into one title. */
function xin_import_files_into( $dir, $novel_id, $opts, $author_id, $status, $dry ) {
	$files = xin_import_scan_dir( $dir );
	if ( ! $files ) {
		printf( "  no chapter files in %s\n", $dir );
		return 0;
	}

	$existing = array();
	foreach ( get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_xin_novel', 'value' => $novel_id ) ),
	) ) as $chapter_id ) {
		$existing[ (string) (float) get_post_meta( $chapter_id, '_xin_number', true ) ] = $chapter_id;
	}

	$start     = isset( $opts['start'] ) ? (float) $opts['start'] : 1;
	$lock_from = isset( $opts['locked-from'] ) ? (float) $opts['locked-from'] : 0;
	$encoding  = isset( $opts['encoding'] ) ? $opts['encoding'] : '';
	$auto      = 0;
	$done      = 0;

	foreach ( $files as $path ) {
		$auto++;
		list( $number, $title ) = xin_import_parse_name( basename( $path ) );

		if ( null === $number ) {
			$number = $start + $auto - 1;
		}

		printf( "  #%s %s\n", rtrim( rtrim( number_format( $number, 1, '.', '' ), '0' ), '.' ), $title );

		if ( $dry ) {
			$done++;
			continue;
		}

		$ext     = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$content = xin_import_text_to_html( file_get_contents( $path ), $ext, $encoding );

		if ( '' === trim( $content ) ) {
			fwrite( STDERR, "  ! empty file skipped: " . basename( $path ) . "\n" );
			continue;
		}

		$key        = (string) (float) $number;
		$chapter_id = isset( $existing[ $key ] ) ? $existing[ $key ] : 0;

		$chapter_id = wp_insert_post( array(
			'ID'           => $chapter_id,
			'post_type'    => 'chapter',
			'post_status'  => $status,
			'post_author'  => $author_id,
			'post_title'   => $title,
			'post_content' => $content,
		), true );

		if ( is_wp_error( $chapter_id ) ) {
			fwrite( STDERR, '  ! ' . $chapter_id->get_error_message() . "\n" );
			continue;
		}

		update_post_meta( $chapter_id, '_xin_novel', $novel_id );
		update_post_meta( $chapter_id, '_xin_number', (float) $number );
		update_post_meta( $chapter_id, '_xin_locked', ( $lock_from && $number >= $lock_from ) ? 1 : 0 );

		$done++;
	}

	return $done;
}

/* ------------------------------------------------------------ files mode run */

if ( $files_mode ) {
	$source  = isset( $opts['from-zip'] ) ? $opts['from-zip'] : $opts['from-dir'];
	$cleanup = '';

	if ( isset( $opts['from-zip'] ) ) {
		if ( ! file_exists( $source ) ) {
			fwrite( STDERR, "Archive not found: {$source}\n" );
			exit( 1 );
		}
		$source  = xin_import_unzip( $source );
		$cleanup = $source;
	}

	if ( ! is_dir( $source ) ) {
		fwrite( STDERR, "Folder not found: {$source}\n" );
		exit( 1 );
	}

	wp_defer_term_counting( true );
	wp_suspend_cache_invalidation( true );

	$total   = 0;
	$subdirs = xin_import_scan_subdirs( $source );

	if ( $subdirs && ! xin_import_scan_dir( $source ) && empty( $opts['novel'] ) && empty( $opts['novel-id'] ) && empty( $opts['novel-slug'] ) ) {
		// Folder per title: the folder name becomes the title name.
		foreach ( $subdirs as $subdir ) {
			$name     = basename( $subdir );
			$novel_id = xin_import_resolve_novel( array(), $author_id, $status, $name );
			printf( "%s\n", $name );
			$total += xin_import_files_into( $subdir, $novel_id, $opts, $author_id, $status, $dry );
		}
	} else {
		$fallback = basename( rtrim( $source, '\\/' ) );
		$novel_id = xin_import_resolve_novel( $opts, $author_id, $status, $fallback );
		printf( "%s\n", get_the_title( $novel_id ) );
		$total   += xin_import_files_into( $source, $novel_id, $opts, $author_id, $status, $dry );
	}

	wp_defer_term_counting( false );
	wp_suspend_cache_invalidation( false );

	if ( ! $dry ) {
		delete_transient( 'xin_site_stats' );
		wp_cache_flush();
	}

	if ( $cleanup ) {
		xin_import_rmdir( dirname( $cleanup ) === rtrim( sys_get_temp_dir(), '\\/' ) ? $cleanup : dirname( $cleanup ) );
	}

	printf( "\nDone. Chapters: %d.\n", $total );
	exit( 0 );
}
/* ------------------------------------------------------------------ import */

$novels = xin_import_read( $manifest, $format );

printf( "Manifest: %s\nTitles: %d\nAuthor ID: %d\nStatus: %s\nMedia: %s\n%s\n\n",
	$manifest,
	count( $novels ),
	$author_id,
	$status,
	$with_med ? 'yes' : 'skipped',
	$dry ? 'DRY RUN — nothing is written' : ''
);

// Term counting and cache invalidation are the two things that make bulk
// inserts crawl. Both are restored at the end.
wp_defer_term_counting( true );
wp_suspend_cache_invalidation( true );

$made_novels   = 0;
$made_chapters = 0;

foreach ( $novels as $data ) {
	$title = isset( $data['title'] ) ? trim( $data['title'] ) : '';
	if ( ! $title ) {
		continue;
	}

	$slug     = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $title );
	$existing = get_page_by_path( $slug, OBJECT, 'novel' );

	printf( "%s %s\n", $existing ? '~' : '+', $title );

	if ( $dry ) {
		$made_novels++;
		$made_chapters += isset( $data['chapters'] ) ? count( $data['chapters'] ) : 0;
		continue;
	}

	$novel_id = wp_insert_post( array(
		'ID'           => $existing ? $existing->ID : 0,
		'post_type'    => 'novel',
		'post_status'  => $status,
		'post_author'  => $author_id,
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_excerpt' => isset( $data['synopsis'] ) ? $data['synopsis'] : '',
		'post_content' => isset( $data['description'] ) ? $data['description'] : '',
	), true );

	if ( is_wp_error( $novel_id ) ) {
		fwrite( STDERR, '  ! ' . $novel_id->get_error_message() . "\n" );
		continue;
	}

	$made_novels++;

	$meta = array(
		'_xin_author_name'    => isset( $data['author_name'] ) ? $data['author_name'] : null,
		'_xin_original_title' => isset( $data['original_title'] ) ? $data['original_title'] : null,
		'_xin_translator'     => isset( $data['translator'] ) ? $data['translator'] : null,
		'_xin_year'           => isset( $data['year'] ) ? (int) $data['year'] : null,
		'_xin_source'         => isset( $data['source'] ) ? $data['source'] : null,
		'_xin_views'          => isset( $data['views'] ) ? (int) $data['views'] : null,
		'_xin_rating'         => isset( $data['rating'] ) ? (float) $data['rating'] : null,
		'_xin_rating_count'   => isset( $data['rating_count'] ) ? (int) $data['rating_count'] : null,
		'_xin_adult'          => isset( $data['adult'] ) ? (int) (bool) $data['adult'] : null,
		'_xin_featured'       => isset( $data['featured'] ) ? (int) (bool) $data['featured'] : null,
	);
	foreach ( $meta as $key => $value ) {
		if ( null !== $value ) {
			update_post_meta( $novel_id, $key, $value );
		}
	}

	if ( ! empty( $data['genres'] ) ) {
		wp_set_object_terms( $novel_id, (array) $data['genres'], 'genre' );
	}
	if ( ! empty( $data['tags'] ) ) {
		wp_set_object_terms( $novel_id, (array) $data['tags'], 'novel_tag' );
	}
	if ( ! empty( $data['status'] ) ) {
		wp_set_object_terms( $novel_id, sanitize_title( $data['status'] ), 'novel_status' );
	}

	if ( $with_med && ! empty( $data['cover'] ) && ! has_post_thumbnail( $novel_id ) ) {
		$cover = xin_import_media( xin_import_path( $data['cover'], $base_dir ), $novel_id, $title );
		if ( $cover ) {
			set_post_thumbnail( $novel_id, $cover );
		}
	}
	if ( $with_med && ! empty( $data['artwork'] ) && ! get_post_meta( $novel_id, '_xin_background', true ) ) {
		$art = xin_import_media( xin_import_path( $data['artwork'], $base_dir ), $novel_id, $title . ' art' );
		if ( $art ) {
			update_post_meta( $novel_id, '_xin_background', $art );
		}
	}

	$existing_chapters = array();
	foreach ( get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_xin_novel', 'value' => $novel_id ) ),
	) ) as $chapter_id ) {
		$existing_chapters[ (string) (float) get_post_meta( $chapter_id, '_xin_number', true ) ] = $chapter_id;
	}

	$chapters = isset( $data['chapters'] ) ? $data['chapters'] : array();
	$auto     = 0;

	foreach ( $chapters as $chapter ) {
		$auto++;
		$number  = isset( $chapter['number'] ) && '' !== $chapter['number'] ? (float) $chapter['number'] : (float) $auto;
		$content = isset( $chapter['content'] ) ? $chapter['content'] : '';

		if ( ! $content && ! empty( $chapter['content_file'] ) ) {
			$path = xin_import_path( $chapter['content_file'], $base_dir );
			if ( $path ) {
				$content = file_get_contents( $path );
			}
		}

		if ( $content && false === strpos( $content, '<p' ) ) {
			$content = wpautop( $content );
		}

		$key        = (string) $number;
		$chapter_id = isset( $existing_chapters[ $key ] ) ? $existing_chapters[ $key ] : 0;

		$post = array(
			'ID'           => $chapter_id,
			'post_type'    => 'chapter',
			'post_status'  => $status,
			'post_author'  => $author_id,
			'post_title'   => isset( $chapter['title'] ) && $chapter['title'] ? $chapter['title'] : sprintf( 'Chapter %s', rtrim( rtrim( number_format( $number, 1, '.', '' ), '0' ), '.' ) ),
			'post_content' => $content,
		);
		if ( ! empty( $chapter['date'] ) ) {
			$post['post_date'] = $chapter['date'];
		}

		$chapter_id = wp_insert_post( $post, true );
		if ( is_wp_error( $chapter_id ) ) {
			fwrite( STDERR, '  ! ' . $chapter_id->get_error_message() . "\n" );
			continue;
		}

		update_post_meta( $chapter_id, '_xin_novel', $novel_id );
		update_post_meta( $chapter_id, '_xin_number', $number );
		update_post_meta( $chapter_id, '_xin_locked', empty( $chapter['locked'] ) ? 0 : 1 );

		$made_chapters++;
	}

	printf( "  chapters: %d\n", count( $chapters ) );
}

wp_defer_term_counting( false );
wp_suspend_cache_invalidation( false );

if ( ! $dry ) {
	delete_transient( 'xin_site_stats' );
	wp_cache_flush();
}

printf( "\nDone. Titles: %d, chapters: %d.\n", $made_novels, $made_chapters );
