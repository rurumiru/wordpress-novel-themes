<?php
/**
 * Уже накопленные записи.
 *
 * Сайт редко переезжает на тему пустым. Чаще там уже лежат главы,
 * перенесённые откуда-то ещё, и в них не хватает ровно того, на чём держится
 * витрина: привязки к тайтлу, номера, обложки, счётчиков.
 *
 * Ничего не делается молча. Каждая правка сначала считается и показывается
 * числом, и только потом применяется — и всегда порциями.
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Что можно поправить в записях.
 */
function xs_content_jobs() {
	return array(
		'orphans' => array(
			'label' => __( 'Главы без тайтла', 'xin-setup' ),
			'why'   => __( 'Такая глава не попадает ни в оглавление, ни в переход «следующая»: читатель до неё просто не доходит. Привязываем по названию — «Название тайтла. Глава 5» и подобным.', 'xin-setup' ),
			'count' => 'xs_count_orphans',
			'fix'   => 'xs_fix_orphans',
		),
		'numbers' => array(
			'label' => __( 'Главы без номера', 'xin-setup' ),
			'why'   => __( 'Номер задаёт порядок чтения. Без него глава встаёт в конец оглавления по дате — и сто тридцатая оказывается после второй.', 'xin-setup' ),
			'count' => 'xs_count_numbers',
			'fix'   => 'xs_fix_numbers',
		),
		'counters' => array(
			'label' => __( 'Счётчики тайтлов', 'xin-setup' ),
			'why'   => __( 'Число глав и время последнего обновления лежат в мете тайтла: по ним сортируется каталог. После переноса они почти всегда пустые.', 'xin-setup' ),
			'count' => 'xs_count_counters',
			'fix'   => 'xs_fix_counters',
		),
		'covers' => array(
			'label' => __( 'Тайтлы без обложки', 'xin-setup' ),
			'why'   => __( 'Каталог без обложек читать невозможно. Рисуем временную — с названием на спокойном фоне, пока не появится настоящая.', 'xin-setup' ),
			'count' => 'xs_count_covers',
			'fix'   => 'xs_fix_covers',
		),
		'excerpts' => array(
			'label' => __( 'Тайтлы без краткого описания', 'xin-setup' ),
			'why'   => __( 'Карточка каталога показывает цитату. Пустая карточка — это название и ничего больше. Берём первые строки описания.', 'xin-setup' ),
			'count' => 'xs_count_excerpts',
			'fix'   => 'xs_fix_excerpts',
		),
	);
}

/* -------------------------------------------------------------------------
 * Главы без тайтла
 * ---------------------------------------------------------------------- */

function xs_count_orphans() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_novel'
		  WHERE p.post_type = 'chapter'
		    AND p.post_status IN ('publish','draft','future','pending')
		    AND ( m.meta_value IS NULL OR m.meta_value = '' OR m.meta_value = '0' )"
	);
}

/**
 * Привязывает главу к тайтлу по названию.
 *
 * Берём то, что стоит до разделителя: «Станция „Тишина“. Глава 5» и
 * «Станция „Тишина“ — 5» дают одно и то же имя. Если такого тайтла нет —
 * оставляем как есть: выдумывать привязку хуже, чем не делать ничего.
 */
function xs_fix_orphans( $limit = 200 ) {
	global $wpdb;

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_novel'
			  WHERE p.post_type = 'chapter'
			    AND p.post_status IN ('publish','draft','future','pending')
			    AND ( m.meta_value IS NULL OR m.meta_value = '' OR m.meta_value = '0' )
			  LIMIT %d",
			$limit
		)
	);

	$done = 0;

	foreach ( (array) $ids as $id ) {
		$title = get_the_title( $id );
		$parts = preg_split( '~\s*(?:\.|—|–|-|:|\|)\s*~u', $title, 2 );
		$name  = isset( $parts[0] ) ? trim( $parts[0] ) : '';

		if ( '' === $name ) {
			continue;
		}

		$novel = get_page_by_title( $name, OBJECT, 'novel' );

		if ( ! $novel ) {
			// Вторая попытка: по адресу-слагу.
			$found = get_posts( array(
				'post_type'      => 'novel',
				'name'           => sanitize_title( $name ),
				'posts_per_page' => 1,
				'no_found_rows'  => true,
			) );

			$novel = $found ? $found[0] : null;
		}

		if ( ! $novel ) {
			continue;
		}

		update_post_meta( $id, '_xin_novel', (int) $novel->ID );
		$done++;
	}

	return $done;
}

/* -------------------------------------------------------------------------
 * Номера
 * ---------------------------------------------------------------------- */

function xs_count_numbers() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_number'
		  WHERE p.post_type = 'chapter'
		    AND p.post_status IN ('publish','draft','future','pending')
		    AND ( m.meta_value IS NULL OR m.meta_value = '' )"
	);
}

/**
 * Номер берём из названия, а если его там нет — по месту в тайтле.
 */
function xs_fix_numbers( $limit = 200 ) {
	global $wpdb;

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_number'
			  WHERE p.post_type = 'chapter'
			    AND p.post_status IN ('publish','draft','future','pending')
			    AND ( m.meta_value IS NULL OR m.meta_value = '' )
			  ORDER BY p.post_date ASC
			  LIMIT %d",
			$limit
		)
	);

	$done  = 0;
	$seats = array();

	foreach ( (array) $ids as $id ) {
		$title = get_the_title( $id );

		if ( preg_match( '~(?:глава|гл\.?|chapter|ch\.?|#)\s*([0-9]+(?:[.,][0-9]+)?)~ui', $title, $m ) ) {
			update_post_meta( $id, '_xin_number', str_replace( ',', '.', $m[1] ) );
			$done++;
			continue;
		}

		if ( preg_match( '~^\s*([0-9]+(?:[.,][0-9]+)?)\b~u', $title, $m ) ) {
			update_post_meta( $id, '_xin_number', str_replace( ',', '.', $m[1] ) );
			$done++;
			continue;
		}

		// Ни в названии, ни рядом — ставим следующий свободный в тайтле.
		$novel = (int) get_post_meta( $id, '_xin_novel', true );

		if ( ! $novel ) {
			continue;
		}

		if ( ! isset( $seats[ $novel ] ) ) {
			$seats[ $novel ] = 1 + (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COALESCE( MAX( CAST( m2.meta_value AS DECIMAL(10,2) ) ), 0 )
					   FROM {$wpdb->postmeta} m1
					   JOIN {$wpdb->postmeta} m2 ON m2.post_id = m1.post_id AND m2.meta_key = '_xin_number'
					  WHERE m1.meta_key = '_xin_novel' AND m1.meta_value = %d",
					$novel
				)
			);
		}

		update_post_meta( $id, '_xin_number', (string) $seats[ $novel ] );
		$seats[ $novel ]++;
		$done++;
	}

	return $done;
}

/* -------------------------------------------------------------------------
 * Счётчики тайтлов
 * ---------------------------------------------------------------------- */

function xs_count_counters() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_chapter_count'
		  WHERE p.post_type = 'novel' AND p.post_status = 'publish'
		    AND ( m.meta_value IS NULL OR m.meta_value = '' )"
	);
}

function xs_fix_counters( $limit = 100 ) {
	global $wpdb;

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			  WHERE p.post_type = 'novel' AND p.post_status = 'publish'
			  ORDER BY p.ID ASC
			  LIMIT %d",
			$limit
		)
	);

	$done = 0;

	foreach ( (array) $ids as $novel ) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_date_gmt FROM {$wpdb->posts} p
				   JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_novel'
				  WHERE p.post_type = 'chapter' AND p.post_status = 'publish' AND m.meta_value = %d",
				$novel
			)
		);

		update_post_meta( $novel, '_xin_chapter_count', count( $rows ) );

		$last = 0;

		foreach ( $rows as $row ) {
			$time = strtotime( $row->post_date_gmt . ' GMT' );
			$last = $time > $last ? $time : $last;
		}

		if ( $last ) {
			update_post_meta( $novel, '_xin_last_chapter_at', $last );
		}

		// Индекс оглавления, если тема его ведёт, пересоберётся сам.
		if ( function_exists( 'xv_forget_chapter_index' ) ) {
			xv_forget_chapter_index( $novel );
		}

		$done++;
	}

	return $done;
}

/* -------------------------------------------------------------------------
 * Обложки
 * ---------------------------------------------------------------------- */

function xs_count_covers() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_thumbnail_id'
		  WHERE p.post_type = 'novel' AND p.post_status = 'publish'
		    AND ( m.meta_value IS NULL OR m.meta_value = '' )"
	);
}

/**
 * Рисует временную обложку: название на спокойном фоне.
 *
 * Цвет берём из названия, а не случайно: одна и та же книга должна
 * выглядеть одинаково после любой пересборки.
 */
function xs_fix_covers( $limit = 25 ) {
	global $wpdb;

	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			  LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_thumbnail_id'
			  WHERE p.post_type = 'novel' AND p.post_status = 'publish'
			    AND ( m.meta_value IS NULL OR m.meta_value = '' )
			  LIMIT %d",
			$limit
		)
	);

	$done = 0;

	foreach ( (array) $ids as $novel ) {
		$id = xs_make_cover( $novel );

		if ( $id ) {
			set_post_thumbnail( $novel, $id );
			$done++;
		}
	}

	return $done;
}

function xs_make_cover( $novel_id ) {
	$title = get_the_title( $novel_id );
	$w     = 600;
	$h     = 900;

	$image = imagecreatetruecolor( $w, $h );

	// Тон из названия: одинаковая книга — одинаковая заглушка.
	$hash = crc32( $title );
	$hue  = $hash % 360;

	list( $r1, $g1, $b1 ) = xs_hsl_rgb( $hue, 0.34, 0.24 );
	list( $r2, $g2, $b2 ) = xs_hsl_rgb( ( $hue + 28 ) % 360, 0.30, 0.10 );

	for ( $y = 0; $y < $h; $y++ ) {
		$k     = $y / $h;
		$color = imagecolorallocate(
			$image,
			(int) round( $r1 + ( $r2 - $r1 ) * $k ),
			(int) round( $g1 + ( $g2 - $g1 ) * $k ),
			(int) round( $b1 + ( $b2 - $b1 ) * $k )
		);

		imageline( $image, 0, $y, $w, $y, $color );
	}

	$ink = imagecolorallocatealpha( $image, 255, 255, 255, 40 );

	// Название разбиваем по словам — встроенный шрифт GD узкий, но кириллицу
	// он не знает, поэтому пишем только то, что он умеет, а для остального
	// оставляем полосы-заглушки.
	$safe = preg_replace( '~[^\x20-\x7E]~', '', $title );

	if ( strlen( trim( (string) $safe ) ) > 1 ) {
		$lines = str_split( wordwrap( trim( $safe ), 22, "\n", true ) === '' ? '' : wordwrap( trim( $safe ), 22, "\n", true ), 10000 );
		$y     = $h - 220;

		foreach ( explode( "\n", $lines[0] ) as $line ) {
			imagestring( $image, 5, 48, $y, $line, $ink );
			$y += 26;
		}
	} else {
		imagefilledrectangle( $image, 48, $h - 200, 48 + 220, $h - 194, $ink );
		imagefilledrectangle( $image, 48, $h - 170, 48 + 140, $h - 164, $ink );
	}

	$uploads = wp_upload_dir();
	$name    = 'cover-' . $novel_id . '-' . substr( md5( $title ), 0, 6 ) . '.jpg';
	$path    = trailingslashit( $uploads['path'] ) . $name;

	imagejpeg( $image, $path, 82 );
	imagedestroy( $image );

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$path,
		$novel_id
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );

	return (int) $id;
}

/**
 * HSL в RGB. Нужен ровно для заглушек обложек.
 */
function xs_hsl_rgb( $h, $s, $l ) {
	$h /= 360;
	$r  = $l;
	$g  = $l;
	$b  = $l;

	if ( $s > 0 ) {
		$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
		$p = 2 * $l - $q;

		$r = xs_hue_channel( $p, $q, $h + 1 / 3 );
		$g = xs_hue_channel( $p, $q, $h );
		$b = xs_hue_channel( $p, $q, $h - 1 / 3 );
	}

	return array( (int) round( $r * 255 ), (int) round( $g * 255 ), (int) round( $b * 255 ) );
}

function xs_hue_channel( $p, $q, $t ) {
	if ( $t < 0 ) {
		$t += 1;
	}

	if ( $t > 1 ) {
		$t -= 1;
	}

	if ( $t < 1 / 6 ) {
		return $p + ( $q - $p ) * 6 * $t;
	}

	if ( $t < 1 / 2 ) {
		return $q;
	}

	if ( $t < 2 / 3 ) {
		return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
	}

	return $p;
}

/* -------------------------------------------------------------------------
 * Краткие описания
 * ---------------------------------------------------------------------- */

function xs_count_excerpts() {
	global $wpdb;

	return (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		  WHERE post_type = 'novel' AND post_status = 'publish'
		    AND ( post_excerpt IS NULL OR post_excerpt = '' )
		    AND post_content <> ''"
	);
}

function xs_fix_excerpts( $limit = 200 ) {
	global $wpdb;

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_content FROM {$wpdb->posts}
			  WHERE post_type = 'novel' AND post_status = 'publish'
			    AND ( post_excerpt IS NULL OR post_excerpt = '' )
			    AND post_content <> ''
			  LIMIT %d",
			$limit
		)
	);

	$done = 0;

	foreach ( $rows as $row ) {
		$text = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $row->post_content ) ), 38, '…' );

		if ( '' === trim( $text ) ) {
			continue;
		}

		$wpdb->update( $wpdb->posts, array( 'post_excerpt' => $text ), array( 'ID' => (int) $row->ID ) );
		clean_post_cache( (int) $row->ID );

		$done++;
	}

	return $done;
}
