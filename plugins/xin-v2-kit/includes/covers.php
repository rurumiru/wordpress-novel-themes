<?php
/**
 * Обложки-заглушки.
 *
 * Пока у тайтла нет настоящей обложки, витрине нужна хотя бы аккуратная
 * подложка с названием. Генератор следит за главным: текст переносится по
 * словам, уменьшается, пока не влезет, и никогда не уходит за край — именно
 * этим страдали заглушки из демо-данных.
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xvk_covers_supported() {
	return extension_loaded( 'gd' ) && function_exists( 'imagettftext' ) && '' !== xvk_font_path();
}

/**
 * Шрифт с кириллицей. Берём первый найденный — свой в плагин не кладём,
 * чтобы не тащить лицензию.
 */
function xvk_font_path() {
	static $found = null;

	if ( null !== $found ) {
		return $found;
	}

	$candidates = apply_filters( 'xvk_font_candidates', array(
		'/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
		'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
		'/Library/Fonts/Arial Bold.ttf',
		'C:/Windows/Fonts/segoeuib.ttf',
		'C:/Windows/Fonts/arialbd.ttf',
		'C:/Windows/Fonts/segoeui.ttf',
		'C:/Windows/Fonts/arial.ttf',
	) );

	foreach ( $candidates as $path ) {
		if ( is_readable( $path ) ) {
			$found = $path;

			return $found;
		}
	}

	$found = '';

	return $found;
}

/**
 * Цвет подложки по названию: у одного тайтла он всегда один и тот же.
 */
function xvk_cover_hue( $title ) {
	return abs( crc32( $title ) ) % 360;
}

/**
 * hsl → rgb, значения 0..1 для s и l.
 */
function xvk_hsl( $h, $s, $l ) {
	$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x = $c * ( 1 - abs( fmod( $h / 60, 2 ) - 1 ) );
	$m = $l - $c / 2;

	$sets = array(
		array( $c, $x, 0 ),
		array( $x, $c, 0 ),
		array( 0, $c, $x ),
		array( 0, $x, $c ),
		array( $x, 0, $c ),
		array( $c, 0, $x ),
	);

	list( $r, $g, $b ) = $sets[ (int) floor( $h / 60 ) % 6 ];

	return array(
		(int) round( ( $r + $m ) * 255 ),
		(int) round( ( $g + $m ) * 255 ),
		(int) round( ( $b + $m ) * 255 ),
	);
}

/**
 * Разбить строку по словам под заданную ширину.
 *
 * Длинное слово, которое не влезает целиком, не режем: вместо этого
 * вызывающий уменьшает кегль.
 */
function xvk_wrap( $text, $font, $size, $max_width ) {
	$words = preg_split( '/\s+/u', trim( $text ) );
	$lines = array();
	$line  = '';

	foreach ( $words as $word ) {
		$try = '' === $line ? $word : $line . ' ' . $word;
		$box = imagettfbbox( $size, 0, $font, $try );

		if ( abs( $box[2] - $box[0] ) <= $max_width || '' === $line ) {
			$line = $try;
			continue;
		}

		$lines[] = $line;
		$line    = $word;
	}

	if ( '' !== $line ) {
		$lines[] = $line;
	}

	return $lines;
}

function xvk_text_width( $font, $size, $text ) {
	$box = imagettfbbox( $size, 0, $font, $text );

	return abs( $box[2] - $box[0] );
}

/**
 * Нарисовать обложку и вернуть путь к файлу.
 *
 * @return string|WP_Error
 */
function xvk_draw_cover( $title, $dir ) {
	$font = xvk_font_path();

	if ( ! $font ) {
		return new WP_Error( 'xvk_no_font', __( 'Не нашёлся шрифт с кириллицей.', 'xin-v2-kit' ) );
	}

	$w = 600;
	$h = 900;

	$image = imagecreatetruecolor( $w, $h );
	imageantialias( $image, true );

	$hue = xvk_cover_hue( $title );

	// Вертикальный градиент: сверху насыщеннее, снизу почти чёрный —
	// на тёмном низе белый текст читается при любом цвете.
	for ( $y = 0; $y < $h; $y++ ) {
		$k = $y / $h;
		list( $r, $g, $b ) = xvk_hsl( $hue, 0.45 - 0.2 * $k, 0.30 - 0.22 * $k );
		$color = imagecolorallocate( $image, $r, $g, $b );
		imageline( $image, 0, $y, $w, $y, $color );
	}

	// Пара дуг для фактуры — чуть светлее фона, без свечения.
	list( $ar, $ag, $ab ) = xvk_hsl( $hue, 0.55, 0.62 );

	for ( $i = 0; $i < 3; $i++ ) {
		// Прозрачность в GD — 0..127, за границей функция бросает исключение.
		$arc = imagecolorallocatealpha( $image, $ar, $ag, $ab, min( 127, 96 + $i * 14 ) );
		$size = 520 + $i * 260;
		imagesetthickness( $image, 2 );
		imagearc( $image, (int) ( $w * 0.32 ), (int) ( $h * 0.22 ), $size, $size, 0, 360, $arc );
	}

	// Текст. Кегль уменьшаем, пока блок не уложится в отведённое поле —
	// именно этого не хватало прежним заглушкам: текст уезжал за край.
	$pad        = 54;
	$max_width  = $w - $pad * 2;
	$max_height = (int) ( $h * 0.42 );
	$size       = 54;
	$lines      = array();
	$line_step  = 0;

	while ( $size >= 20 ) {
		$lines     = xvk_wrap( $title, $font, $size, $max_width );
		$line_step = (int) round( $size * 1.32 );
		$fits      = ( count( $lines ) * $line_step ) <= $max_height;

		foreach ( $lines as $line ) {
			if ( xvk_text_width( $font, $size, $line ) > $max_width ) {
				$fits = false;
				break;
			}
		}

		if ( $fits ) {
			break;
		}

		$size -= 3;
	}

	// Блок прижат к низу, но с полем: буквы с нижними выносными элементами
	// должны оставаться внутри картинки.
	$bottom = $h - 72;
	$top    = $bottom - ( count( $lines ) - 1 ) * $line_step;

	$white  = imagecolorallocate( $image, 255, 255, 255 );
	$shadow = imagecolorallocatealpha( $image, 0, 0, 0, 80 );

	foreach ( $lines as $i => $line ) {
		$x = (int) round( ( $w - xvk_text_width( $font, $size, $line ) ) / 2 );
		$y = (int) ( $top + $i * $line_step );

		imagettftext( $image, $size, 0, $x + 2, $y + 2, $shadow, $font, $line );
		imagettftext( $image, $size, 0, $x, $y, $white, $font, $line );
	}

	$file = trailingslashit( $dir ) . 'xv-cover-' . wp_generate_password( 8, false ) . '.jpg';

	if ( ! imagejpeg( $image, $file, 88 ) ) {
		imagedestroy( $image );

		return new WP_Error( 'xvk_write_failed', __( 'Не удалось записать файл.', 'xin-v2-kit' ) );
	}

	imagedestroy( $image );

	return $file;
}

/**
 * Сгенерировать и поставить обложку тайтлу.
 *
 * @return int|WP_Error Идентификатор вложения.
 */
function xvk_make_cover( $novel_id ) {
	$novel_id = (int) $novel_id;
	$title    = get_the_title( $novel_id );

	if ( '' === $title ) {
		return new WP_Error( 'xvk_no_title', __( 'У тайтла нет названия.', 'xin-v2-kit' ) );
	}

	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'xvk_uploads', $uploads['error'] );
	}

	$file = xvk_draw_cover( $title, $uploads['path'] );

	if ( is_wp_error( $file ) ) {
		return $file;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment = wp_insert_attachment( array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $file, $novel_id );

	if ( is_wp_error( $attachment ) || ! $attachment ) {
		return new WP_Error( 'xvk_attach_failed', __( 'Не удалось добавить файл в медиатеку.', 'xin-v2-kit' ) );
	}

	wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $file ) );

	$old = get_post_thumbnail_id( $novel_id );

	set_post_thumbnail( $novel_id, $attachment );

	// Прежнюю заглушку убираем, чужие картинки не трогаем.
	if ( $old && xvk_is_placeholder( $old ) ) {
		wp_delete_attachment( $old, true );
	}

	return (int) $attachment;
}

/**
 * Похоже ли вложение на сгенерированную заглушку.
 *
 * Опознаём по имени файла: настоящие обложки заливают руками и называют
 * иначе, а удалять чужое нельзя.
 */
function xvk_is_placeholder( $attachment_id ) {
	$file = get_attached_file( $attachment_id );

	if ( ! $file ) {
		return false;
	}

	$name = strtolower( basename( $file ) );

	foreach ( array( 'xv-cover-', 'xin-cover-', 'cover-' ) as $prefix ) {
		if ( str_starts_with( $name, $prefix ) ) {
			return true;
		}
	}

	return false;
}

/* -------------------------------------------------------------------------
 * Проверки
 * ---------------------------------------------------------------------- */

/**
 * Тайтлы, которым можно нарисовать обложку: её нет вовсе.
 */
function xvk_find_no_cover() {
	$bad = array();

	foreach ( xvk_all_novels() as $id ) {
		if ( ! get_post_thumbnail_id( $id ) ) {
			$bad[] = $id;
		}
	}

	return $bad;
}

/**
 * Тайтлы на сгенерированной заглушке — их можно перерисовать.
 */
function xvk_find_placeholders() {
	$found = array();

	foreach ( xvk_all_novels() as $id ) {
		$thumb = get_post_thumbnail_id( $id );

		if ( $thumb && xvk_is_placeholder( $thumb ) ) {
			$found[] = $id;
		}
	}

	return $found;
}

function xvk_fix_covers( $ids ) {
	if ( ! xvk_covers_supported() ) {
		return 0;
	}

	$done = 0;

	// Рисование тяжелее прочих правок, поэтому порция меньше.
	foreach ( array_slice( $ids, 0, 40 ) as $id ) {
		$result = xvk_make_cover( $id );

		if ( ! is_wp_error( $result ) ) {
			$done++;
		}
	}

	return $done;
}

/**
 * Добавляем проверки в общий список.
 */
function xvk_cover_checks( $checks ) {
	$checks['covers'] = array(
		'label' => __( 'Тайтлы без обложки', 'xin-v2-kit' ),
		'hint'  => __( 'В каталоге и на главной вместо обложки останется пустая плашка.', 'xin-v2-kit' ),
		'find'  => 'xvk_find_no_cover',
		'note'  => xvk_covers_supported()
			? __( 'Починка рисует заглушку с названием — её потом можно заменить настоящей.', 'xin-v2-kit' )
			: __( 'Нужны расширение GD и шрифт с кириллицей — на этом сервере их нет.', 'xin-v2-kit' ),
	);

	if ( xvk_covers_supported() ) {
		$checks['covers']['fix'] = 'xvk_fix_covers';

		$checks['placeholders'] = array(
			'label'    => __( 'Обложки-заглушки', 'xin-v2-kit' ),
			'hint'     => __( 'Нарисованы автоматически. Перерисовка чинит съехавший или обрезанный текст.', 'xin-v2-kit' ),
			'find'     => 'xvk_find_placeholders',
			'fix'      => 'xvk_fix_covers',
			'note'     => __( 'Это не поломка: перерисовывайте, только если текст на обложках выглядит криво.', 'xin-v2-kit' ),
			'optional' => true,
		);
	}

	return $checks;
}
add_filter( 'xvk_checks', 'xvk_cover_checks' );
