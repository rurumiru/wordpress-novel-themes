<?php
/**
 * Выгрузка тайтла в EPUB и FB2.
 *
 * Собирается из опубликованных глав; закрытые попадают в файл только
 * если читатель имеет к ним доступ.
 *
 * Кому вообще позволено скачивать, решает настройка «Скачивание книг» в панели
 * управления: всем, вошедшим, по PLUS или по выбранным ролям. Проверка одна на
 * ссылку и на сам файл — xin_can_download().
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_export_url( $novel_id, $format ) {
	return add_query_arg( 'xin_export', $format, get_permalink( $novel_id ) );
}

/**
 * Режимы доступа к выгрузке. Ключ хранится в теме, подпись видит админ.
 *
 * @return array
 */
function xin_download_audiences() {
	return array(
		'all'       => __( 'Всем, включая гостей', 'xin-com' ),
		'members'   => __( 'Любому вошедшему', 'xin-com' ),
		'plus'      => __( 'Только с доступом PLUS', 'xin-com' ),
		'plus_role' => __( 'PLUS или выбранные роли', 'xin-com' ),
		'roles'     => __( 'Только выбранным ролям', 'xin-com' ),
	);
}

/**
 * Текущий режим. Значение вне списка = «всем»: так тема ведёт себя как до
 * появления настройки, а не запирает скачивание из-за мусора в theme_mod.
 *
 * @return string
 */
function xin_download_audience() {
	$mode = get_theme_mod( 'xin_download_audience', 'all' );

	return isset( xin_download_audiences()[ $mode ] ) ? $mode : 'all';
}

/**
 * Роли, которым разрешено скачивание.
 *
 * Хранится строкой слагов через запятую: ролей на площадке немного, а один
 * theme_mod проще переносить между сайтами, чем набор ключей на каждую роль.
 *
 * @return array
 */
function xin_download_roles() {
	$raw = get_theme_mod( 'xin_download_roles', '' );
	$raw = is_array( $raw ) ? $raw : preg_split( '/[,\s]+/', (string) $raw );
	$out = array();

	foreach ( (array) $raw as $role ) {
		$role = sanitize_key( $role );
		if ( $role && ! in_array( $role, $out, true ) ) {
			$out[] = $role;
		}
	}

	return $out;
}

/**
 * Роли, из которых админ выбирает в панели.
 *
 * Администратора в списке нет: он скачивает при любом режиме, и галочка
 * напротив него обещала бы настройку, которой на самом деле не существует.
 *
 * @return array
 */
function xin_download_role_choices() {
	$out = array();

	foreach ( wp_roles()->get_names() as $slug => $name ) {
		if ( 'administrator' === $slug ) {
			continue;
		}
		$out[ $slug ] = translate_user_role( $name );
	}

	return $out;
}

/**
 * Есть ли у пользователя хоть одна из разрешённых ролей.
 *
 * @param int $user_id ID пользователя.
 * @return bool
 */
function xin_download_role_match( $user_id ) {
	$roles = xin_download_roles();
	$user  = $user_id ? get_userdata( $user_id ) : false;

	if ( ! $roles || ! $user ) {
		return false;
	}

	return (bool) array_intersect( $roles, (array) $user->roles );
}

/**
 * Может ли этот человек скачать книгу.
 *
 * Администратор может всегда: настройкой управляет он сам, и запереть себя
 * собственным переключателем — единственный способ остаться без выгрузки на
 * площадке, где её больше некому проверить.
 *
 * @param int $user_id ID пользователя, по умолчанию текущий.
 * @return bool
 */
function xin_can_download( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$mode    = xin_download_audience();

	if ( 'all' === $mode ) {
		$can = true;
	} elseif ( $user_id && user_can( $user_id, 'manage_options' ) ) {
		$can = true;
	} elseif ( ! $user_id ) {
		$can = false;
	} elseif ( 'members' === $mode ) {
		$can = true;
	} elseif ( 'roles' === $mode ) {
		$can = xin_download_role_match( $user_id );
	} elseif ( 'plus_role' === $mode ) {
		$can = xin_download_role_match( $user_id ) || xin_user_is_plus( $user_id );
	} else {
		$can = xin_user_is_plus( $user_id );
	}

	/**
	 * Последнее слово о доступе к выгрузке.
	 *
	 * Плагину членства этого достаточно, чтобы не трогать тему: свой признак
	 * подписки он подставляет здесь.
	 *
	 * @param bool $can     Решение темы.
	 * @param int  $user_id Кого проверяем.
	 * @param string $mode  Выбранный режим.
	 */
	return (bool) apply_filters( 'xin_can_download', $can, $user_id, $mode );
}

/**
 * Чем объяснить отказ: текст и куда идти дальше.
 *
 * @return array
 */
function xin_download_denied() {
	$mode = xin_download_audience();

	if ( ! is_user_logged_in() ) {
		return array(
			'text' => __( 'Скачивание книг доступно не всем посетителям. Войдите в аккаунт.', 'xin-com' ),
			'url'  => xin_login_url( get_permalink() ),
			'link' => __( 'Войти', 'xin-com' ),
		);
	}

	if ( 'roles' === $mode ) {
		$text = __( 'Скачивание книг открыто только отдельным ролям площадки.', 'xin-com' );
	} elseif ( 'plus_role' === $mode ) {
		$text = __( 'Скачивание книг открыто по доступу PLUS и отдельным ролям площадки.', 'xin-com' );
	} else {
		$text = __( 'Скачивание книг входит в доступ PLUS.', 'xin-com' );
	}

	return array( 'text' => $text, 'url' => '', 'link' => '' );
}

function xin_export_router() {
	if ( ! is_singular( 'novel' ) || empty( $_GET['xin_export'] ) ) {
		return;
	}

	$format = sanitize_key( wp_unslash( $_GET['xin_export'] ) );
	if ( ! in_array( $format, array( 'epub', 'fb2' ), true ) ) {
		return;
	}

	$novel_id = get_queried_object_id();

	if ( ! xin_can_download() ) {
		$denied = xin_download_denied();
		$args   = array( 'response' => 403 );

		if ( $denied['url'] ) {
			$args['link_url']  = $denied['url'];
			$args['link_text'] = $denied['link'];
		}

		wp_die( esc_html( $denied['text'] ), '', $args );
	}

	if ( xin_export_throttled() ) {
		wp_die( esc_html__( 'Слишком много выгрузок подряд. Попробуйте через несколько минут.', 'xin-com' ), '', array( 'response' => 429 ) );
	}

	$chapters = xin_export_chapters( $novel_id );
	if ( ! $chapters ) {
		wp_die( esc_html__( 'В этом тайтле пока нет доступных глав.', 'xin-com' ), '', array( 'response' => 404 ) );
	}

	if ( 'epub' === $format ) {
		xin_send_epub( $novel_id, $chapters );
	} else {
		xin_send_fb2( $novel_id, $chapters );
	}
}
add_action( 'template_redirect', 'xin_export_router', 3 );

function xin_export_throttled() {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'none';
	$key = 'xin_export_' . md5( $ip );

	$hits = (int) get_transient( $key );
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	return $hits >= 10;
}

function xin_export_chapters( $novel_id ) {
	$out = array();

	foreach ( xin_get_chapters( $novel_id, 'ASC' ) as $chapter ) {
		if ( ! xin_can_read_chapter( $chapter->ID ) ) {
			continue;
		}

		$out[] = array(
			'id'      => $chapter->ID,
			'title'   => get_the_title( $chapter->ID ),
			'label'   => xin_chapter_label( $chapter->ID ),
			'content' => xin_export_clean( apply_filters( 'the_content', $chapter->post_content ) ),
		);
	}

	return $out;
}

function xin_export_clean( $html ) {
	$html = wp_kses( $html, array(
		'p'      => array(),
		'br'     => array(),
		'em'     => array(),
		'i'      => array(),
		'strong' => array(),
		'b'      => array(),
		'h2'     => array(),
		'h3'     => array(),
		'blockquote' => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
		'hr'     => array(),
	) );

	$html = preg_replace( '/<(br|hr)\s*\/?>/i', '<$1/>', $html );
	$html = str_replace( array( '&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;', '&hellip;' ), array( ' ', '—', '–', '«', '»', '…' ), $html );
	$html = preg_replace( '/&(?!(amp|lt|gt|quot|apos);)/', '&amp;', $html );

	return trim( $html );
}

function xin_export_filename( $novel_id, $ext ) {
	$slug = sanitize_title( get_the_title( $novel_id ) );
	$slug = $slug ? $slug : 'novel';

	return $slug . '.' . $ext;
}

function xin_temp_file( $prefix ) {
	$file = tempnam( get_temp_dir(), $prefix );

	return $file ? $file : get_temp_dir() . $prefix . wp_generate_password( 8, false );
}

function xin_export_headers( $file, $name, $type ) {
	while ( ob_get_level() ) {
		ob_end_clean();
	}

	nocache_headers();
	header( 'Content-Type: ' . $type );
	header( 'Content-Disposition: attachment; filename="' . $name . '"' );
	header( 'Content-Length: ' . filesize( $file ) );
	header( 'X-Content-Type-Options: nosniff' );

	readfile( $file );
	wp_delete_file( $file );
	exit;
}

function xin_export_cover( $novel_id ) {
	$id = get_post_thumbnail_id( $novel_id );
	if ( ! $id ) {
		return array();
	}

	$path = get_attached_file( $id );
	if ( ! $path || ! file_exists( $path ) ) {
		return array();
	}

	$type = get_post_mime_type( $id );
	$ext  = 'image/png' === $type ? 'png' : 'jpg';

	return array( 'path' => $path, 'type' => $type ? $type : 'image/jpeg', 'ext' => $ext );
}

function xin_send_epub( $novel_id, $chapters ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( esc_html__( 'На сервере нет расширения ZipArchive — EPUB собрать нечем. Попробуйте FB2.', 'xin-com' ) );
	}

	$file = xin_temp_file( 'xin-epub' );
	$zip  = new ZipArchive();

	if ( true !== $zip->open( $file, ZipArchive::OVERWRITE ) ) {
		wp_die( esc_html__( 'Не удалось собрать файл.', 'xin-com' ) );
	}

	$title  = get_the_title( $novel_id );
	$author = xin_novel_author( $novel_id );
	$uid    = 'urn:uuid:' . md5( home_url( '/' ) . '-' . $novel_id );
	$lang   = 0 === strpos( get_bloginfo( 'language' ), 'en' ) ? 'en' : 'ru';
	$cover  = xin_export_cover( $novel_id );

	$zip->addFromString( 'mimetype', 'application/epub+zip' );
	if ( method_exists( $zip, 'setCompressionName' ) ) {
		$zip->setCompressionName( 'mimetype', ZipArchive::CM_STORE );
	}

	$zip->addFromString( 'META-INF/container.xml', '<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
	<rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles>
</container>' );

	$zip->addFromString( 'OEBPS/style.css', 'body{font-family:Georgia,serif;line-height:1.6;margin:1em}h1,h2{font-family:sans-serif;line-height:1.25}p{margin:0 0 .9em;text-indent:1.2em}p.first{text-indent:0}hr{border:0;border-top:1px solid #ccc;margin:2em auto;width:40%}' );

	$manifest = array();
	$spine    = array();
	$nav      = array();

	foreach ( $chapters as $i => $chapter ) {
		$name  = sprintf( 'ch%03d.xhtml', $i + 1 );
		$head  = $chapter['label'] ? sprintf( '%s. %s', $chapter['label'], $chapter['title'] ) : $chapter['title'];
		$body  = $chapter['content'] ? $chapter['content'] : '<p></p>';

		$zip->addFromString( 'OEBPS/' . $name, '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . esc_attr( $lang ) . '">
<head><title>' . esc_html( $head ) . '</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
<body><h2>' . esc_html( $head ) . '</h2>' . $body . '</body></html>' );

		$manifest[] = sprintf( '<item id="c%1$d" href="%2$s" media-type="application/xhtml+xml"/>', $i + 1, $name );
		$spine[]    = sprintf( '<itemref idref="c%d"/>', $i + 1 );
		$nav[]      = sprintf( '<li><a href="%s">%s</a></li>', $name, esc_html( $head ) );
	}

	$zip->addFromString( 'OEBPS/nav.xhtml', '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="' . esc_attr( $lang ) . '">
<head><title>' . esc_html( $title ) . '</title></head>
<body><nav epub:type="toc" id="toc"><h1>' . esc_html( $title ) . '</h1><ol>' . implode( '', $nav ) . '</ol></nav></body></html>' );

	$cover_item = '';
	$cover_meta = '';
	if ( $cover ) {
		$zip->addFile( $cover['path'], 'OEBPS/cover.' . $cover['ext'] );
		$cover_item = sprintf( '<item id="cover-image" href="cover.%s" media-type="%s" properties="cover-image"/>', $cover['ext'], $cover['type'] );
		$cover_meta = '<meta name="cover" content="cover-image"/>';
	}

	$zip->addFromString( 'OEBPS/content.opf', '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="bookid">
	<metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
		<dc:identifier id="bookid">' . esc_html( $uid ) . '</dc:identifier>
		<dc:title>' . esc_html( $title ) . '</dc:title>
		<dc:creator>' . esc_html( $author ) . '</dc:creator>
		<dc:language>' . esc_html( $lang ) . '</dc:language>
		<dc:publisher>' . esc_html( get_bloginfo( 'name' ) ) . '</dc:publisher>
		<dc:source>' . esc_url( get_permalink( $novel_id ) ) . '</dc:source>
		<meta property="dcterms:modified">' . esc_html( gmdate( 'Y-m-d\TH:i:s\Z' ) ) . '</meta>
		' . $cover_meta . '
	</metadata>
	<manifest>
		<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>
		<item id="css" href="style.css" media-type="text/css"/>
		' . $cover_item . '
		' . implode( "\n\t\t", $manifest ) . '
	</manifest>
	<spine>' . implode( '', $spine ) . '</spine>
</package>' );

	$zip->close();

	xin_export_headers( $file, xin_export_filename( $novel_id, 'epub' ), 'application/epub+zip' );
}

function xin_send_fb2( $novel_id, $chapters ) {
	$title    = get_the_title( $novel_id );
	$author   = xin_novel_author( $novel_id );
	$parts    = explode( ' ', $author, 2 );
	$first    = $parts[0];
	$last     = isset( $parts[1] ) ? $parts[1] : '';
	$lang     = 0 === strpos( get_bloginfo( 'language' ), 'en' ) ? 'en' : 'ru';
	$synopsis = wp_strip_all_tags( get_the_excerpt( $novel_id ) );
	$cover    = xin_export_cover( $novel_id );
	$genres   = get_the_terms( $novel_id, 'genre' );
	$genre    = ( ! is_wp_error( $genres ) && $genres ) ? $genres[0]->name : __( 'Проза', 'xin-com' );

	$body = '';
	foreach ( $chapters as $chapter ) {
		$head = $chapter['label'] ? sprintf( '%s. %s', $chapter['label'], $chapter['title'] ) : $chapter['title'];
		$text = $chapter['content'];

		$text = preg_replace( '#<h[23][^>]*>(.*?)</h[23]>#is', '<subtitle>$1</subtitle>', $text );
		$text = preg_replace( '#</?(ul|ol)[^>]*>#i', '', $text );
		$text = preg_replace( '#<li[^>]*>(.*?)</li>#is', '<p>— $1</p>', $text );
		$text = str_replace( array( '<blockquote>', '</blockquote>' ), array( '<cite>', '</cite>' ), $text );
		$text = str_replace( array( '<b>', '</b>' ), array( '<strong>', '</strong>' ), $text );
		$text = str_replace( array( '<i>', '</i>' ), array( '<emphasis>', '</emphasis>' ), $text );
		$text = str_replace( array( '<em>', '</em>' ), array( '<emphasis>', '</emphasis>' ), $text );
		$text = str_replace( '<hr/>', '<empty-line/>', $text );
		$text = str_replace( '<br/>', ' ', $text );

		$body .= '<section><title><p>' . esc_html( $head ) . '</p></title>' . $text . '</section>';
	}

	$binary = '';
	$coverpage = '';
	if ( $cover ) {
		$data = file_get_contents( $cover['path'] );
		if ( false !== $data ) {
			$binary    = '<binary id="cover.' . esc_attr( $cover['ext'] ) . '" content-type="' . esc_attr( $cover['type'] ) . '">' . base64_encode( $data ) . '</binary>';
			$coverpage = '<coverpage><image l:href="#cover.' . esc_attr( $cover['ext'] ) . '"/></coverpage>';
		}
	}

	$xml = '<?xml version="1.0" encoding="UTF-8"?>
<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">
<description>
	<title-info>
		<genre>prose_contemporary</genre>
		<author><first-name>' . esc_html( $first ) . '</first-name><last-name>' . esc_html( $last ) . '</last-name></author>
		<book-title>' . esc_html( $title ) . '</book-title>
		<annotation><p>' . esc_html( $synopsis ) . '</p></annotation>
		' . $coverpage . '
		<lang>' . esc_html( $lang ) . '</lang>
		<keywords>' . esc_html( $genre ) . '</keywords>
	</title-info>
	<document-info>
		<author><nickname>' . esc_html( get_bloginfo( 'name' ) ) . '</nickname></author>
		<date value="' . esc_attr( gmdate( 'Y-m-d' ) ) . '">' . esc_html( gmdate( 'Y-m-d' ) ) . '</date>
		<id>' . esc_html( md5( home_url( '/' ) . '-' . $novel_id ) ) . '</id>
		<version>1.0</version>
		<src-url>' . esc_url( get_permalink( $novel_id ) ) . '</src-url>
	</document-info>
</description>
<body><title><p>' . esc_html( $title ) . '</p></title>' . $body . '</body>
' . $binary . '</FictionBook>';

	$file = xin_temp_file( 'xin-fb2' );
	file_put_contents( $file, $xml );

	xin_export_headers( $file, xin_export_filename( $novel_id, 'fb2' ), 'application/x-fictionbook+xml' );
}
