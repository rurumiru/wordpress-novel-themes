<?php
/**
 * Раздел комиксов.
 *
 * Манга, манхва и веб-тун — те же тайтлы и главы, только вместо текста
 * страницы-картинки. Заводить ради них второй тип записи значило бы
 * раздваивать каталог, поиск, рейтинг и кабинет автора; поэтому формат —
 * это поле тайтла `_xin_format`, а отличается только показ.
 *
 * Ключи те же, что у XIN-Com: комикс, заведённый там, открывается здесь.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_FORMAT    = '_xin_format';
const XV_PAGES     = '_xin_pages';
const XV_DIRECTION = '_xin_direction';

function xv_comics_on() {
	return (bool) xv_opt( 'comics', false );
}

function xv_formats() {
	return array(
		'novel' => __( 'Текст', 'xin-v2' ),
		'comic' => __( 'Комикс', 'xin-v2' ),
	);
}

function xv_novel_format( $novel_id ) {
	$format = get_post_meta( $novel_id, XV_FORMAT, true );

	return 'comic' === $format ? 'comic' : 'novel';
}

function xv_is_comic( $novel_id ) {
	return xv_comics_on() && 'comic' === xv_novel_format( $novel_id );
}

/**
 * Комикс ли глава — решает её тайтл, а не она сама: смешанных тайтлов не
 * бывает, и хранить формат дважды значит однажды их рассогласовать.
 */
function xv_chapter_is_comic( $chapter_id ) {
	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );

	return $novel_id && xv_is_comic( $novel_id );
}

/**
 * Направление чтения: сверху вниз (веб-тун), справа налево (манга) или
 * слева направо.
 */
function xv_comic_direction( $novel_id ) {
	$dir = get_post_meta( $novel_id, XV_DIRECTION, true );

	return in_array( $dir, array( 'vertical', 'rtl', 'ltr' ), true ) ? $dir : 'vertical';
}

function xv_comic_directions() {
	return array(
		'vertical' => __( 'Сверху вниз (веб-тун)', 'xin-v2' ),
		'rtl'      => __( 'Справа налево (манга)', 'xin-v2' ),
		'ltr'      => __( 'Слева направо', 'xin-v2' ),
	);
}

/**
 * Страницы главы — вложения в порядке, заданном автором.
 */
function xv_comic_pages( $chapter_id ) {
	$ids = get_post_meta( $chapter_id, XV_PAGES, true );
	$ids = is_array( $ids ) ? array_values( array_filter( array_map( 'absint', $ids ) ) ) : array();

	return $ids;
}

function xv_comic_page_count( $chapter_id ) {
	return count( xv_comic_pages( $chapter_id ) );
}

/**
 * Адреса страниц в нужном размере.
 *
 * Отдаём и полный, и уменьшенный: страницу комикса открывают и на телефоне,
 * и на большом экране, а весит разворот заметно больше текста главы.
 */
function xv_comic_page_sources( $chapter_id ) {
	$out = array();

	foreach ( xv_comic_pages( $chapter_id ) as $id ) {
		$full = wp_get_attachment_image_src( $id, 'full' );

		if ( ! $full ) {
			continue;
		}

		$small = wp_get_attachment_image_src( $id, 'large' );

		$out[] = array(
			'id'     => $id,
			'src'    => $full[0],
			'width'  => (int) $full[1],
			'height' => (int) $full[2],
			'small'  => $small ? $small[0] : $full[0],
			'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		);
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * Выбор шаблона
 * ---------------------------------------------------------------------- */

/**
 * Комикс показывается своими шаблонами. Подменяем на этапе выбора, а не
 * ветвлением внутри общего файла: две разметки в одном шаблоне читаются
 * хуже, чем два коротких.
 */
function xv_comic_template( $template ) {
	if ( ! xv_comics_on() ) {
		return $template;
	}

	if ( is_singular( 'novel' ) && xv_is_comic( get_queried_object_id() ) ) {
		$own = get_theme_file_path( 'single-comic.php' );

		return file_exists( $own ) ? $own : $template;
	}

	if ( is_singular( 'chapter' ) && xv_chapter_is_comic( get_queried_object_id() ) ) {
		$own = get_theme_file_path( 'single-comic-chapter.php' );

		return file_exists( $own ) ? $own : $template;
	}

	return $template;
}
add_filter( 'template_include', 'xv_comic_template', 20 );

/* -------------------------------------------------------------------------
 * Каталог
 * ---------------------------------------------------------------------- */

/**
 * Какой формат просит адрес: `?format=comic`, страница комиксов или ничего.
 */
function xv_requested_format() {
	if ( ! xv_comics_on() ) {
		return '';
	}

	if ( ! empty( $_GET['format'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$asked = sanitize_key( wp_unslash( $_GET['format'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return isset( xv_formats()[ $asked ] ) ? $asked : '';
	}

	return '';
}

/**
 * Ограничение каталога по формату.
 *
 * Тайтлы без поля `_xin_format` — текстовые: поле появилось позже самих
 * тайтлов, и требовать его наличия значило бы спрятать всю старую
 * библиотеку.
 */
function xv_format_clause( $format ) {
	if ( 'comic' === $format ) {
		return array(
			array( 'key' => XV_FORMAT, 'value' => 'comic' ),
		);
	}

	return array(
		'relation' => 'OR',
		array( 'key' => XV_FORMAT, 'value' => 'comic', 'compare' => '!=' ),
		array( 'key' => XV_FORMAT, 'compare' => 'NOT EXISTS' ),
	);
}

function xv_comics_filter_catalog( $query ) {
	if ( ! xv_comics_on() || is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'novel' ) && ! $query->is_tax( array( 'genre', 'novel_tag', 'novel_status' ) ) ) {
		return;
	}

	$format = xv_requested_format();

	if ( ! $format ) {
		return;
	}

	$meta   = (array) $query->get( 'meta_query' );
	$meta[] = xv_format_clause( $format );

	$query->set( 'meta_query', $meta );
}
add_action( 'pre_get_posts', 'xv_comics_filter_catalog', 20 );

/* -------------------------------------------------------------------------
 * Правка из кабинета и админки
 * ---------------------------------------------------------------------- */

/**
 * Страницы главы. Принимаем список идентификаторов вложений — загрузку
 * делает медиатека, а порядок задаёт автор.
 */
function xv_save_comic_pages( $chapter_id, $raw ) {
	$ids = array();

	foreach ( preg_split( '/[\s,]+/', (string) $raw ) as $piece ) {
		$id = (int) $piece;

		if ( $id && 'attachment' === get_post_type( $id ) ) {
			$ids[] = $id;
		}
	}

	if ( $ids ) {
		update_post_meta( $chapter_id, XV_PAGES, $ids );
	} else {
		delete_post_meta( $chapter_id, XV_PAGES );
	}

	return $ids;
}

/**
 * Смена формата тайтла перестраивает ссылки: у комиксов свой раздел.
 */
function xv_comics_flush_on_toggle( $old, $new ) {
	if ( $old !== $new ) {
		flush_rewrite_rules();
	}
}

/**
 * Ссылка на каталог нужного формата.
 */
function xv_format_url( $format ) {
	$base = get_post_type_archive_link( 'novel' );

	return 'comic' === $format ? add_query_arg( 'format', 'comic', $base ) : $base;
}
