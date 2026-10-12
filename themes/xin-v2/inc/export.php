<?php
/**
 * Выгрузка тайтла книгой: EPUB и FB2.
 *
 * Читают не только в браузере. EPUB открывают ридеры и телефоны, FB2 —
 * русскоязычные читалки, где он до сих пор родной формат.
 *
 * Собираем на лету и отдаём потоком: держать готовые файлы значит хранить
 * копию всей библиотеки и следить, чтобы она не устаревала после каждой
 * правки главы.
 *
 * В книгу попадает только то, что читателю и так открыто: закрытые главы
 * проходят ту же проверку, что и на странице.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_EXPORT_VAR = 'xv_book';

/* -------------------------------------------------------------------------
 * Кому можно
 * ---------------------------------------------------------------------- */

/**
 * Кому открыта выгрузка: всем, только вошедшим или только своим.
 */
function xv_download_audiences() {
	return array(
		'all'    => __( 'Всем', 'xin-v2' ),
		'users'  => __( 'Вошедшим', 'xin-v2' ),
		'team'   => __( 'Авторам и редакторам', 'xin-v2' ),
		'nobody' => __( 'Никому', 'xin-v2' ),
	);
}

function xv_can_download( $novel_id = 0 ) {
	$who = xv_opt( 'downloads', 'all' );

	if ( 'nobody' === $who ) {
		return false;
	}

	if ( 'all' === $who ) {
		return true;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( 'users' === $who ) {
		return true;
	}

	return current_user_can( 'edit_posts' ) || ( $novel_id && xv_in_team( $novel_id ) );
}

/**
 * Формат — в адресе, а не в форме: ссылку на книгу присылают друг другу.
 */
function xv_export_url( $novel_id, $format ) {
	return add_query_arg(
		array( XV_EXPORT_VAR => $format ),
		get_permalink( $novel_id )
	);
}

/* -------------------------------------------------------------------------
 * Сбор книги
 * ---------------------------------------------------------------------- */

/**
 * Главы, которые попадут в книгу: только открытые этому читателю.
 */
function xv_export_chapters( $novel_id ) {
	$out = array();

	foreach ( xv_chapter_index( $novel_id, false ) as $id ) {
		if ( ! xv_chapter_unlocked( $id ) ) {
			continue;
		}

		$out[] = $id;
	}

	return $out;
}

/**
 * Текст главы, пригодный для книги.
 *
 * Ридеры разбирают строгий XHTML: незакрытый <br> или <img> валит всю книгу,
 * причём молча — файл просто не открывается. Поэтому прогоняем через
 * загрузчик HTML и отдаём то, что он собрал обратно.
 */
function xv_export_clean( $html ) {
	$html = strip_shortcodes( $html );

	// Подсказки глоссария и служебные обёртки в книге не нужны.
	$html = preg_replace( '~<span class="xv-term"[^>]*>(.*?)</span>~is', '$1', $html );

	$html = wp_kses(
		$html,
		array(
			'p'          => array(),
			'br'         => array(),
			'em'         => array(),
			'i'          => array(),
			'strong'     => array(),
			'b'          => array(),
			'blockquote' => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'hr'         => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
			'a'          => array( 'href' => array() ),
		)
	);

	if ( ! class_exists( 'DOMDocument' ) ) {
		// Без DOM закрываем одиночные теги вручную — лучше, чем битая книга.
		return preg_replace( '~<(br|hr)\s*/?>~i', '<$1 />', $html );
	}

	$doc = new DOMDocument( '1.0', 'UTF-8' );
	$old = libxml_use_internal_errors( true );

	$doc->loadHTML(
		'<?xml encoding="UTF-8"><div id="xv-root">' . $html . '</div>',
		LIBXML_NOWARNING | LIBXML_NOERROR
	);

	libxml_clear_errors();
	libxml_use_internal_errors( $old );

	$root = $doc->getElementById( 'xv-root' );

	if ( ! $root ) {
		return $html;
	}

	$out = '';

	foreach ( $root->childNodes as $node ) {
		$out .= $doc->saveXML( $node );
	}

	return $out;
}

function xv_export_filename( $novel_id, $extension ) {
	$slug = sanitize_title( get_the_title( $novel_id ) );

	if ( '' === $slug ) {
		$slug = 'book-' . (int) $novel_id;
	}

	return $slug . '.' . $extension;
}

function xv_export_headers( $name, $type, $length = 0 ) {
	nocache_headers();

	header( 'Content-Type: ' . $type );
	header( 'Content-Disposition: attachment; filename="' . $name . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
	header( 'X-Content-Type-Options: nosniff' );

	if ( $length ) {
		header( 'Content-Length: ' . (int) $length );
	}
}

/* -------------------------------------------------------------------------
 * EPUB
 * ---------------------------------------------------------------------- */

/**
 * EPUB — это zip со строгим порядком: первым и без сжатия должен лежать
 * mimetype, иначе часть ридеров отказывается открывать файл.
 */
function xv_send_epub( $novel_id ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( esc_html__( 'На сервере нет расширения Zip — EPUB собрать нечем. FB2 работает без него.', 'xin-v2' ) );
	}

	$chapters = xv_export_chapters( $novel_id );
	$title    = get_the_title( $novel_id );
	$author   = xv_novel_author( $novel_id );
	$uuid     = 'urn:uuid:' . wp_generate_uuid4();
	// tempnam() ядра живёт в админской части и на витрине не подключён,
	// поэтому берём каталог через get_temp_dir() и создаём файл сами.
	$path     = tempnam( get_temp_dir(), 'xv-epub' );

	$zip = new ZipArchive();

	if ( ! $path || true !== $zip->open( $path, ZipArchive::OVERWRITE ) ) {
		wp_die( esc_html__( 'Не удалось собрать книгу.', 'xin-v2' ) );
	}

	$zip->addFromString( 'mimetype', 'application/epub+zip' );
	$zip->setCompressionName( 'mimetype', ZipArchive::CM_STORE );

	$zip->addFromString(
		'META-INF/container.xml',
		'<?xml version="1.0" encoding="UTF-8"?>'
		. '<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">'
		. '<rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles>'
		. '</container>'
	);

	$zip->addFromString(
		'OEBPS/style.css',
		"body{font-family:serif;line-height:1.55;margin:0 6%;}\n"
		. "h1,h2{font-weight:600;line-height:1.25;}\n"
		. "p{margin:0 0 .9em;text-indent:1.2em;}\n"
		. "p:first-of-type{text-indent:0;}\n"
		. ".title{margin:18% 0 0;text-align:center;}\n"
		. ".by{text-align:center;color:#555;}\n"
	);

	// Обложка кладётся как есть: пережимать чужую картинку ради пары
	// килобайт — не наша задача.
	$cover_id = get_post_thumbnail_id( $novel_id );
	$cover    = '';

	if ( $cover_id ) {
		$file = get_attached_file( $cover_id );

		if ( $file && file_exists( $file ) && filesize( $file ) < 4 * MB_IN_BYTES ) {
			$ext   = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
			$ext   = in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ? $ext : '';
			$cover = $ext ? 'cover.' . $ext : '';

			if ( $cover ) {
				$zip->addFile( $file, 'OEBPS/' . $cover );
			}
		}
	}

	$manifest = array();
	$spine    = array();
	$nav      = array();

	$about = '<?xml version="1.0" encoding="UTF-8"?>'
		. '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . esc_html( $title ) . '</title>'
		. '<link rel="stylesheet" type="text/css" href="style.css"/></head><body>'
		. ( $cover ? '<div style="text-align:center"><img src="' . esc_attr( $cover ) . '" alt="" style="max-width:100%"/></div>' : '' )
		. '<h1 class="title">' . esc_html( $title ) . '</h1>'
		. ( $author ? '<p class="by">' . esc_html( $author ) . '</p>' : '' )
		. '</body></html>';

	$zip->addFromString( 'OEBPS/title.xhtml', $about );
	$manifest[] = '<item id="title" href="title.xhtml" media-type="application/xhtml+xml"/>';
	$spine[]    = '<itemref idref="title"/>';

	foreach ( $chapters as $i => $id ) {
		$name    = sprintf( 'ch%04d', $i + 1 );
		$heading = get_the_title( $id );
		$number  = xv_chapter_number( $id );

		$page = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . esc_html( $heading ) . '</title>'
			. '<link rel="stylesheet" type="text/css" href="style.css"/></head><body>'
			. '<h2>' . esc_html( '' !== $number ? $number . '. ' . $heading : $heading ) . '</h2>'
			. xv_export_clean( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) )
			. '</body></html>';

		$zip->addFromString( 'OEBPS/' . $name . '.xhtml', $page );

		$manifest[] = '<item id="' . $name . '" href="' . $name . '.xhtml" media-type="application/xhtml+xml"/>';
		$spine[]    = '<itemref idref="' . $name . '"/>';
		$nav[]      = '<li><a href="' . $name . '.xhtml">' . esc_html( $heading ) . '</a></li>';
	}

	if ( $cover ) {
		$type       = 'png' === pathinfo( $cover, PATHINFO_EXTENSION ) ? 'image/png' : 'image/jpeg';
		$manifest[] = '<item id="cover-image" href="' . esc_attr( $cover ) . '" media-type="' . $type . '" properties="cover-image"/>';
	}

	$zip->addFromString(
		'OEBPS/nav.xhtml',
		'<?xml version="1.0" encoding="UTF-8"?>'
		. '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops"><head>'
		. '<title>' . esc_html__( 'Оглавление', 'xin-v2' ) . '</title></head><body>'
		. '<nav epub:type="toc" id="toc"><h1>' . esc_html__( 'Оглавление', 'xin-v2' ) . '</h1><ol>'
		. implode( '', $nav )
		. '</ol></nav></body></html>'
	);

	$manifest[] = '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>';
	$manifest[] = '<item id="css" href="style.css" media-type="text/css"/>';

	$zip->addFromString(
		'OEBPS/content.opf',
		'<?xml version="1.0" encoding="UTF-8"?>'
		. '<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="book-id">'
		. '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/">'
		. '<dc:identifier id="book-id">' . esc_html( $uuid ) . '</dc:identifier>'
		. '<dc:title>' . esc_html( $title ) . '</dc:title>'
		. '<dc:language>' . esc_html( str_replace( '_', '-', get_locale() ) ) . '</dc:language>'
		. ( $author ? '<dc:creator>' . esc_html( $author ) . '</dc:creator>' : '' )
		. '<dc:source>' . esc_html( get_permalink( $novel_id ) ) . '</dc:source>'
		. '<meta property="dcterms:modified">' . gmdate( 'Y-m-d\TH:i:s\Z' ) . '</meta>'
		. ( $cover ? '<meta name="cover" content="cover-image"/>' : '' )
		. '</metadata>'
		. '<manifest>' . implode( '', $manifest ) . '</manifest>'
		. '<spine>' . implode( '', $spine ) . '</spine>'
		. '</package>'
	);

	$zip->close();

	xv_export_headers( xv_export_filename( $novel_id, 'epub' ), 'application/epub+zip', filesize( $path ) );

	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	wp_delete_file( $path );

	exit;
}

/* -------------------------------------------------------------------------
 * FB2
 * ---------------------------------------------------------------------- */

/**
 * FB2 — один XML-файл. Разметки в нём немного, и чужие теги он не прощает,
 * поэтому текст переводим в его собственные: <p>, <emphasis>, <strong>.
 */
function xv_send_fb2( $novel_id ) {
	$chapters = xv_export_chapters( $novel_id );
	$title    = get_the_title( $novel_id );
	$author   = xv_novel_author( $novel_id );

	$body = '';

	foreach ( $chapters as $id ) {
		$heading = get_the_title( $id );
		$number  = xv_chapter_number( $id );
		$text    = xv_export_clean( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) );

		$text = preg_replace( '~</?(em|i)\b[^>]*>~i', '<emphasis>', $text );
		$text = preg_replace( '~</?(b)\b[^>]*>~i', '<strong>', $text );
		$text = preg_replace( '~<(br|hr)\s*/?>~i', '', $text );
		$text = preg_replace( '~</?(ul|ol|blockquote|h[2-4])[^>]*>~i', '', $text );
		$text = preg_replace( '~<li[^>]*>~i', '<p>', $text );
		$text = preg_replace( '~</li>~i', '</p>', $text );
		$text = preg_replace( '~<a\b[^>]*>(.*?)</a>~is', '$1', $text );

		// Открывающий и закрывающий <emphasis> после замены выглядят
		// одинаково — чинить пару проще, чем разбирать теги по отдельности.
		$text = xv_fb2_pair( $text, 'emphasis' );
		$text = xv_fb2_pair( $text, 'strong' );

		$body .= '<section><title><p>' . esc_html( '' !== $number ? $number . '. ' . $heading : $heading ) . '</p></title>'
			. $text
			. '</section>';
	}

	$xml = '<?xml version="1.0" encoding="UTF-8"?>'
		. '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">'
		. '<description><title-info>'
		. '<book-title>' . esc_html( $title ) . '</book-title>'
		. ( $author ? '<author><nickname>' . esc_html( $author ) . '</nickname></author>' : '' )
		. '<lang>' . esc_html( substr( get_locale(), 0, 2 ) ) . '</lang>'
		. '</title-info><document-info>'
		. '<id>' . esc_html( wp_generate_uuid4() ) . '</id>'
		. '<date value="' . gmdate( 'Y-m-d' ) . '">' . gmdate( 'Y-m-d' ) . '</date>'
		. '<src-url>' . esc_html( get_permalink( $novel_id ) ) . '</src-url>'
		. '</document-info></description>'
		. '<body>' . $body . '</body>'
		. '</FictionBook>';

	xv_export_headers( xv_export_filename( $novel_id, 'fb2' ), 'application/x-fictionbook+xml', strlen( $xml ) );

	echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	exit;
}

/**
 * Расставляет закрывающие теги в паре: первый вход открывает, второй
 * закрывает.
 */
function xv_fb2_pair( $text, $tag ) {
	$open = 0;

	return preg_replace_callback(
		'~<' . $tag . '>~',
		function () use ( &$open, $tag ) {
			$open++;

			return 1 === $open % 2 ? '<' . $tag . '>' : '</' . $tag . '>';
		},
		$text
	);
}

/* -------------------------------------------------------------------------
 * Приём запроса
 * ---------------------------------------------------------------------- */

/**
 * Сборка книги стоит заметно дороже обычной страницы, поэтому один адрес
 * может просить её раз в полминуты.
 */
function xv_export_throttled() {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'xv_book_' . md5( $ip );

	if ( get_transient( $key ) ) {
		return true;
	}

	set_transient( $key, 1, 30 );

	return false;
}

function xv_export_router() {
	if ( ! is_singular( 'novel' ) || empty( $_GET[ XV_EXPORT_VAR ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$format   = sanitize_key( wp_unslash( $_GET[ XV_EXPORT_VAR ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$novel_id = get_queried_object_id();

	if ( ! in_array( $format, array( 'epub', 'fb2' ), true ) ) {
		return;
	}

	if ( ! xv_can_download( $novel_id ) ) {
		wp_die(
			esc_html__( 'Выгрузка книги на этой площадке закрыта.', 'xin-v2' ),
			esc_html__( 'Нельзя скачать', 'xin-v2' ),
			array( 'response' => 403 )
		);
	}

	if ( xv_export_throttled() ) {
		wp_die(
			esc_html__( 'Слишком часто. Подождите полминуты.', 'xin-v2' ),
			esc_html__( 'Подождите', 'xin-v2' ),
			array( 'response' => 429 )
		);
	}

	if ( ! xv_export_chapters( $novel_id ) ) {
		wp_die(
			esc_html__( 'Собирать пока нечего: открытых глав нет.', 'xin-v2' ),
			esc_html__( 'Пустая книга', 'xin-v2' ),
			array( 'response' => 404 )
		);
	}

	// Большой тайтл собирается не мгновенно — даём себе время и память.
	wp_raise_memory_limit( 'admin' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	while ( ob_get_level() ) {
		ob_end_clean();
	}

	if ( 'epub' === $format ) {
		xv_send_epub( $novel_id );
	}

	xv_send_fb2( $novel_id );
}
add_action( 'template_redirect', 'xv_export_router', 1 );
