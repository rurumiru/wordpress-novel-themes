<?php
/**
 * Картинки и встраивания.
 *
 * На витрине тайтлов картинок больше, чем текста: каталог на 24 карточки —
 * это 24 обложки. Всё, что тут делается, сводится к одному: не грузить то,
 * чего читатель ещё не видит, и не давать странице прыгать, когда картинка
 * наконец пришла.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ленивая загрузка у WordPress есть, но первая картинка экрана от неё
 * проигрывает: браузер узнаёт о ней позже, чем мог бы. Поэтому первую в
 * документе, наоборот, помечаем как важную.
 */
function xt_smart_loading( $attr, $attachment, $size ) {
	if ( is_admin() || is_feed() || ! xt_opt( 'lazy_media' ) ) {
		return $attr;
	}

	static $seen = 0;

	$seen++;

	if ( 1 === $seen && ( is_singular() || is_front_page() ) ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';

		unset( $attr['decoding'] );

		return $attr;
	}

	$attr['loading']  = 'lazy';
	$attr['decoding'] = 'async';

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'xt_smart_loading', 10, 3 );

/**
 * Размеры у картинок из текста.
 *
 * Картинка без width и height заставляет страницу прыгать, когда она
 * загрузится: читатель теряет строку, на которой был. Проставляем размеры
 * там, где их не проставил редактор.
 */
function xt_size_images( $html ) {
	if ( is_admin() || is_feed() || ! xt_opt( 'size_images' ) ) {
		return $html;
	}

	if ( false === strpos( $html, '<img' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'~<img\s([^>]+)>~i',
		function ( $m ) {
			$tag = $m[0];

			if ( preg_match( '~\swidth=~i', $tag ) ) {
				return $tag;
			}

			if ( ! preg_match( '~\ssrc=["\']([^"\']+)["\']~i', $tag, $src ) ) {
				return $tag;
			}

			$id = attachment_url_to_postid( $src[1] );

			if ( ! $id ) {
				return $tag;
			}

			$meta = wp_get_attachment_metadata( $id );

			if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
				return $tag;
			}

			return str_replace( '<img ', '<img width="' . (int) $meta['width'] . '" height="' . (int) $meta['height'] . '" ', $tag );
		},
		$html
	);
}
add_filter( 'the_content', 'xt_size_images', 30 );

/**
 * Встраивания.
 *
 * Плеер с чужого сайта тянет за собой чужие скрипты и чужие счётчики.
 * Ленивая загрузка не убирает их, но откладывает до того момента, когда
 * читатель до них долистает.
 */
function xt_lazy_iframes( $html ) {
	if ( is_admin() || is_feed() || ! xt_opt( 'lazy_iframes' ) ) {
		return $html;
	}

	if ( false === strpos( $html, '<iframe' ) ) {
		return $html;
	}

	return preg_replace_callback(
		'~<iframe\s([^>]+)>~i',
		function ( $m ) {
			if ( preg_match( '~\sloading=~i', $m[0] ) ) {
				return $m[0];
			}

			return str_replace( '<iframe ', '<iframe loading="lazy" ', $m[0] );
		},
		$html
	);
}
add_filter( 'the_content', 'xt_lazy_iframes', 31 );
add_filter( 'widget_text_content', 'xt_lazy_iframes', 31 );

/**
 * Размеры промежуточных копий.
 *
 * WordPress режет каждую загруженную картинку на полдюжины размеров, часть
 * которых на площадке для чтения никто не покажет. Лишние копии — это место
 * на диске и время при загрузке обложки.
 */
function xt_trim_sizes( $sizes ) {
	if ( ! xt_opt( 'size_images' ) ) {
		return $sizes;
	}

	foreach ( array( '1536x1536', '2048x2048' ) as $name ) {
		unset( $sizes[ $name ] );
	}

	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'xt_trim_sizes' );
