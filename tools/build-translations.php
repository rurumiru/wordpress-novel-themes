<?php
/**
 * Builds the .po and .mo files for the theme and for every bundled plugin.
 *
 *   php tools/build-translations.php
 *
 * For each target it extracts every translatable string from the source,
 * checks it against the RU -> * maps in tools/i18n/, reports what is missing
 * or no longer used, then writes both files for every locale it finds.
 * No msgfmt required; a missing string exits non-zero, so it doubles as a check.
 */

$root  = dirname( __DIR__ );
$theme = $root . '/themes/xin-com';

/* Что переводим: тема и плагины, у каждого свой текстовый домен. */
$targets = array(
	array(
		'domain' => 'xin-com',
		'name'   => 'XIN-Com',
		'src'    => $theme,
		'maps'   => __DIR__ . '/i18n',
		'out'    => $theme . '/languages',
		'prefix' => '',
	),
	array(
		'domain' => 'xi-studio',
		'name'   => 'XI Studio',
		'src'    => $root . '/plugins/xi-studio',
		'maps'   => __DIR__ . '/i18n/xi-studio',
		'out'    => $root . '/plugins/xi-studio/languages',
		'prefix' => 'xi-studio-',
	),
	array(
		'domain' => 'xi-novel-import',
		'name'   => 'XIN-Com — chapter import',
		'src'    => $root . '/plugins/xi-novel-import',
		'maps'   => __DIR__ . '/i18n/xi-novel-import',
		'out'    => $root . '/plugins/xi-novel-import/languages',
		'prefix' => 'xi-novel-import-',
	),
	array(
		'domain' => 'xi-novel-manager',
		'name'   => 'XIN-Com — bulk management',
		'src'    => $root . '/plugins/xi-novel-manager',
		'maps'   => __DIR__ . '/i18n/xi-novel-manager',
		'out'    => $root . '/plugins/xi-novel-manager/languages',
		'prefix' => 'xi-novel-manager-',
	),
);

$version = '1.0.0';
if ( preg_match( "/define\(\s*'XIN_VERSION',\s*'([^']+)'/", file_get_contents( $theme . '/functions.php' ), $m ) ) {
	$version = $m[1];
}

$plurals = array(
	'en_US' => 'nplurals=2; plural=(n != 1);',
	'pt_BR' => 'nplurals=2; plural=(n > 1);',
);

$names = array(
	'en_US' => 'English (US)',
	'pt_BR' => 'Brazilian Portuguese',
);

/* ----------------------------------------------------- Строки из исходников */

$collect = static function ( $dir, $domain ) {
	$all = array();

	if ( ! is_dir( $dir ) ) {
		return $all;
	}

	$quoted = preg_quote( $domain, '/' );
	$re     = "/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'{$quoted}'/";

	$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );

	foreach ( $rii as $file ) {
		if ( $file->isDir() || 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		$code = file_get_contents( $file->getPathname() );

		if ( preg_match_all( $re, $code, $found ) ) {
			foreach ( $found[1] as $string ) {
				$all[ stripslashes( $string ) ] = true;
			}
		}
	}

	$all = array_keys( $all );
	sort( $all );

	return $all;
};

/* ------------------------------------------------------------------ Запись */

$esc = static function ( $s ) {
	return str_replace( array( '\\', '"', "\n" ), array( '\\\\', '\"', '\n' ), $s );
};

$write_po = static function ( $file, $name, $header, $map ) use ( $esc ) {
	$po = '# ' . $name . " translation for the XIN-Com project.\n"
		. "msgid \"\"\nmsgstr \"\"\n";

	foreach ( explode( '\\n', $header ) as $line ) {
		if ( '' === $line ) {
			continue;
		}
		$po .= '"' . $line . '\n"' . "\n";
	}
	$po .= "\n";

	foreach ( $map as $src => $dst ) {
		$po .= 'msgid "' . $esc( $src ) . "\"\n";
		$po .= 'msgstr "' . $esc( $dst ) . "\"\n\n";
	}

	file_put_contents( $file, $po );
};

$write_mo = static function ( $file, $header, $map ) {
	$entries = array( '' => str_replace( '\\n', "\n", $header ) );
	foreach ( $map as $src => $dst ) {
		$entries[ $src ] = $dst;
	}
	ksort( $entries, SORT_STRING );

	$count       = count( $entries );
	$offsets_o   = array();
	$offsets_t   = array();
	$data        = '';
	$origin_size = 28 + 16 * $count;

	foreach ( $entries as $src => $dst ) {
		$offsets_o[] = array( strlen( $src ), $origin_size + strlen( $data ) );
		$data       .= $src . "\0";
	}
	foreach ( $entries as $src => $dst ) {
		$offsets_t[] = array( strlen( $dst ), $origin_size + strlen( $data ) );
		$data       .= $dst . "\0";
	}

	$mo = pack( 'V*', 0x950412de, 0, $count, 28, 28 + 8 * $count, 0, 28 + 16 * $count );
	foreach ( $offsets_o as $o ) {
		$mo .= pack( 'VV', $o[0], $o[1] );
	}
	foreach ( $offsets_t as $o ) {
		$mo .= pack( 'VV', $o[0], $o[1] );
	}
	$mo .= $data;

	file_put_contents( $file, $mo );

	return $count;
};

/* ----------------------------------------------------------------- Сборка */

$fail = 0;

foreach ( $targets as $target ) {
	$all = $collect( $target['src'], $target['domain'] );

	if ( ! $all ) {
		echo $target['domain'] . ": nothing to translate, skipped\n";
		continue;
	}

	$maps = glob( $target['maps'] . '/*.php' );
	sort( $maps );

	if ( ! $maps ) {
		echo $target['domain'] . ': no maps in ' . $target['maps'] . "\n";
		$fail++;
		continue;
	}

	if ( ! is_dir( $target['out'] ) ) {
		mkdir( $target['out'], 0777, true );
	}

	echo $target['domain'] . ': ' . count( $all ) . " source strings\n";

	foreach ( $maps as $map_file ) {
		$locale = basename( $map_file, '.php' );
		$map    = require $map_file;
		$name   = isset( $names[ $locale ] ) ? $names[ $locale ] : $locale;

		$missing = array_values( array_diff( $all, array_keys( $map ) ) );
		$unused  = array_values( array_diff( array_keys( $map ), $all ) );

		if ( $missing ) {
			$fail++;
			echo '  ' . $locale . ' MISSING (' . count( $missing ) . "):\n    " . implode( "\n    ", $missing ) . "\n";
		}
		if ( $unused ) {
			echo '  ' . $locale . ' UNUSED (' . count( $unused ) . ")\n";
		}

		$header = 'Project-Id-Version: ' . $target['name'] . ' ' . $version . "\\n"
			. 'Language: ' . $locale . "\\n"
			. "MIME-Version: 1.0\\n"
			. "Content-Type: text/plain; charset=UTF-8\\n"
			. "Content-Transfer-Encoding: 8bit\\n"
			. 'Plural-Forms: ' . ( isset( $plurals[ $locale ] ) ? $plurals[ $locale ] : 'nplurals=2; plural=(n != 1);' ) . "\\n";

		$base = $target['out'] . '/' . $target['prefix'] . $locale;

		$write_po( $base . '.po', $name, $header, $map );
		$count = $write_mo( $base . '.mo', $header, $map );

		echo '  ' . $locale . ': ' . $count . " entries written\n";
	}
}

exit( $fail ? 1 : 0 );
