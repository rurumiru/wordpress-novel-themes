<?php
/**
 * Разбор входящих файлов в главы.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xni_supported_ext() {
	return array( 'txt', 'md', 'html', 'htm', 'docx', 'zip' );
}

function xni_read_file( $path, $encoding = '' ) {
	$raw = file_get_contents( $path );

	if ( false === $raw ) {
		return '';
	}
	if ( $encoding && 'utf-8' !== strtolower( $encoding ) ) {
		return mb_convert_encoding( $raw, 'UTF-8', $encoding );
	}
	if ( ! mb_check_encoding( $raw, 'UTF-8' ) ) {
		return mb_convert_encoding( $raw, 'UTF-8', 'windows-1251' );
	}

	return $raw;
}

function xni_text_to_html( $text ) {
	$text  = str_replace( array( "\r\n", "\r" ), "\n", $text );
	$text  = preg_replace( '/\n{3,}/', "\n\n", trim( $text ) );
	$out   = '';

	foreach ( explode( "\n\n", $text ) as $block ) {
		$block = trim( $block );
		if ( '' === $block ) {
			continue;
		}
		if ( preg_match( '/^#{2,3}\s+(.+)$/u', $block, $m ) ) {
			$out .= '<h3>' . esc_html( trim( $m[1] ) ) . '</h3>' . "\n";
			continue;
		}
		if ( preg_match( '/^[-*_]{3,}$/', $block ) ) {
			$out .= "<hr>\n";
			continue;
		}

		$block = esc_html( $block );
		$block = preg_replace( '/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $block );
		$block = preg_replace( '/(?<!\*)\*([^*]+)\*(?!\*)/su', '<em>$1</em>', $block );
		$block = str_replace( "\n", "<br>\n", $block );

		$out .= '<p>' . $block . "</p>\n";
	}

	return trim( $out );
}

function xni_html_body( $html ) {
	if ( preg_match( '#<body[^>]*>(.*?)</body>#is', $html, $m ) ) {
		$html = $m[1];
	}

	$html = preg_replace( '#<(script|style)[^>]*>.*?</\1>#is', '', $html );
	$html = preg_replace( '#\sstyle="[^"]*"#i', '', $html );
	$html = preg_replace( '#\sclass="[^"]*"#i', '', $html );
	$html = preg_replace( '#<span[^>]*>|</span>#i', '', $html );

	return trim( wp_kses_post( $html ) );
}

function xni_docx_to_html( $path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'xni_no_zip', __( 'На сервере нет расширения ZipArchive — .docx разобрать нечем.', 'xi-novel-import' ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		return new WP_Error( 'xni_bad_docx', __( 'Файл .docx не открывается.', 'xi-novel-import' ) );
	}

	$xml = $zip->getFromName( 'word/document.xml' );
	$zip->close();

	if ( ! $xml ) {
		return new WP_Error( 'xni_bad_docx', __( 'Внутри .docx нет документа.', 'xi-novel-import' ) );
	}

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadXML( $xml );
	libxml_clear_errors();

	$out = '';
	foreach ( $dom->getElementsByTagName( 'p' ) as $p ) {
		$style = '';
		foreach ( $p->getElementsByTagName( 'pStyle' ) as $node ) {
			$style = strtolower( (string) $node->getAttribute( 'w:val' ) );
		}

		$line = '';
		foreach ( $p->getElementsByTagName( 'r' ) as $run ) {
			$piece = '';
			foreach ( $run->getElementsByTagName( 't' ) as $t ) {
				$piece .= $t->nodeValue;
			}
			if ( '' === $piece ) {
				if ( $run->getElementsByTagName( 'br' )->length ) {
					$line .= '<br>';
				}
				continue;
			}

			$piece = esc_html( $piece );
			$props = $run->getElementsByTagName( 'rPr' );
			if ( $props->length ) {
				$rpr = $props->item( 0 );
				if ( $rpr->getElementsByTagName( 'b' )->length ) {
					$piece = '<strong>' . $piece . '</strong>';
				}
				if ( $rpr->getElementsByTagName( 'i' )->length ) {
					$piece = '<em>' . $piece . '</em>';
				}
			}

			$line .= $piece;
		}

		$line = trim( $line );
		if ( '' === $line || '<br>' === $line ) {
			continue;
		}

		if ( false !== strpos( $style, 'heading1' ) || false !== strpos( $style, 'title' ) ) {
			$out .= '<h2>' . $line . "</h2>\n";
		} elseif ( false !== strpos( $style, 'heading' ) ) {
			$out .= '<h3>' . $line . "</h3>\n";
		} else {
			$out .= '<p>' . $line . "</p>\n";
		}
	}

	return trim( $out );
}

function xni_title_from_name( $name ) {
	$name = preg_replace( '/\.[a-z0-9]+$/i', '', $name );
	$name = str_replace( array( '_', '  ' ), ' ', $name );
	$name = trim( $name );

	$number = null;
	if ( preg_match( '/^(\d+(?:[.,]\d+)?)\s*[.\-–—)]?\s*(.*)$/u', $name, $m ) ) {
		$number = (float) str_replace( ',', '.', $m[1] );
		$name   = trim( $m[2] );
	} elseif ( preg_match( '/^(?:глава|chapter|ch|гл)\.?\s*(\d+(?:[.,]\d+)?)\s*[.\-–—:)]?\s*(.*)$/iu', $name, $m ) ) {
		$number = (float) str_replace( ',', '.', $m[1] );
		$name   = trim( $m[2] );
	}

	return array( 'number' => $number, 'title' => $name );
}

function xni_title_from_text( $html ) {
	if ( preg_match( '#<h[23][^>]*>(.*?)</h[23]>#is', $html, $m ) ) {
		return trim( wp_strip_all_tags( $m[1] ) );
	}
	if ( preg_match( '#<p[^>]*>(.*?)</p>#is', $html, $m ) ) {
		$first = trim( wp_strip_all_tags( $m[1] ) );
		if ( mb_strlen( $first ) <= 70 ) {
			return $first;
		}
	}

	return '';
}

function xni_parse_file( $path, $name, $encoding = '' ) {
	$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	if ( 'docx' === $ext ) {
		$html = xni_docx_to_html( $path );
		if ( is_wp_error( $html ) ) {
			return $html;
		}
	} elseif ( in_array( $ext, array( 'html', 'htm' ), true ) ) {
		$html = xni_html_body( xni_read_file( $path, $encoding ) );
	} elseif ( in_array( $ext, array( 'txt', 'md' ), true ) ) {
		$html = xni_text_to_html( xni_read_file( $path, $encoding ) );
	} else {
		return new WP_Error( 'xni_ext', sprintf( __( 'Формат .%s не поддерживается.', 'xi-novel-import' ), $ext ) );
	}

	if ( ! $html ) {
		return new WP_Error( 'xni_empty', sprintf( __( 'Файл «%s» пустой.', 'xi-novel-import' ), $name ) );
	}

	$parsed = xni_title_from_name( $name );
	$title  = $parsed['title'] ? $parsed['title'] : xni_title_from_text( $html );

	return array(
		'title'   => $title ? $title : pathinfo( $name, PATHINFO_FILENAME ),
		'number'  => $parsed['number'],
		'content' => $html,
		'source'  => $name,
	);
}

function xni_parse_zip( $path, $encoding = '' ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'xni_no_zip', __( 'На сервере нет расширения ZipArchive.', 'xi-novel-import' ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		return new WP_Error( 'xni_bad_zip', __( 'Архив не открывается.', 'xi-novel-import' ) );
	}

	$dir = get_temp_dir() . 'xni-' . wp_generate_password( 8, false );
	wp_mkdir_p( $dir );

	$files = array();
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$entry = $zip->getNameIndex( $i );

		if ( '/' === substr( $entry, -1 ) || false !== strpos( $entry, '..' ) || 0 === strpos( basename( $entry ), '.' ) || false !== strpos( $entry, '__MACOSX' ) ) {
			continue;
		}
		$ext = strtolower( pathinfo( $entry, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'txt', 'md', 'html', 'htm', 'docx' ), true ) ) {
			continue;
		}

		$target = $dir . '/' . md5( $entry ) . '.' . $ext;
		$data   = $zip->getFromIndex( $i );
		if ( false === $data ) {
			continue;
		}
		file_put_contents( $target, $data );
		$files[] = array( 'path' => $target, 'name' => basename( $entry ) );
	}
	$zip->close();

	if ( ! $files ) {
		return new WP_Error( 'xni_zip_empty', __( 'В архиве нет файлов поддерживаемых форматов.', 'xi-novel-import' ) );
	}

	usort( $files, static function ( $a, $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	} );

	$out = array();
	foreach ( $files as $file ) {
		$chapter = xni_parse_file( $file['path'], $file['name'], $encoding );
		if ( ! is_wp_error( $chapter ) ) {
			$out[] = $chapter;
		}
		wp_delete_file( $file['path'] );
	}

	@rmdir( $dir );

	return $out;
}

function xni_google_doc( $url ) {
	if ( ! preg_match( '#/document/d/(?:e/)?([a-zA-Z0-9_-]{12,})#', $url, $m ) ) {
		return new WP_Error( 'xni_gdoc_url', __( 'Это не похоже на ссылку Google Docs. Нужен адрес вида docs.google.com/document/d/…', 'xi-novel-import' ) );
	}

	$id     = $m[1];
	$export = false !== strpos( $url, '/document/d/e/' )
		? 'https://docs.google.com/document/d/e/' . $id . '/pub'
		: 'https://docs.google.com/document/d/' . $id . '/export?format=html';

	$response = wp_remote_get( $export, array( 'timeout' => 25, 'redirection' => 5 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}
	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'xni_gdoc_access', __( 'Google не отдал документ. Откройте доступ по ссылке или опубликуйте документ в интернете: Файл → Поделиться → Опубликовать в интернете.', 'xi-novel-import' ) );
	}

	$html = wp_remote_retrieve_body( $response );
	if ( false !== strpos( $html, 'accounts.google.com' ) || false !== strpos( $html, 'ServiceLogin' ) ) {
		return new WP_Error( 'xni_gdoc_access', __( 'Документ закрыт: Google просит вход. Откройте доступ по ссылке и повторите.', 'xi-novel-import' ) );
	}

	$body = xni_html_body( $html );
	if ( ! $body ) {
		return new WP_Error( 'xni_gdoc_empty', __( 'Документ пустой.', 'xi-novel-import' ) );
	}

	$title = '';
	if ( preg_match( '#<title[^>]*>(.*?)</title>#is', $html, $t ) ) {
		$title = trim( wp_strip_all_tags( $t[1] ) );
	}
	if ( ! $title ) {
		$title = xni_title_from_text( $body );
	}

	$parsed = xni_title_from_name( $title );

	return array( array(
		'title'   => $parsed['title'] ? $parsed['title'] : $title,
		'number'  => $parsed['number'],
		'content' => $body,
		'source'  => 'Google Docs',
	) );
}
