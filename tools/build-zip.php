<?php
/**
 * Packs a folder into a distributable zip.
 *
 *   php tools/build-zip.php --src=themes/xi-novels --out=dist/xi-novels.zip
 *   php tools/build-zip.php --src=demo --out=dist/demo.zip --root=demo
 *
 * Options:
 *   --src=PATH   Folder to pack. Required.
 *   --out=PATH   Zip file to write. Required.
 *   --root=NAME  Folder name inside the archive. Default: basename of --src.
 *
 * Why this exists: PowerShell's Compress-Archive writes entry paths with
 * backslashes, and unzip implementations on other platforms — including the
 * one WordPress uses when installing a theme — then treat `theme\style.css` as
 * a single file name. The upload fails with "the theme is missing style.css".
 * ZipArchive writes forward slashes, which every unpacker understands.
 */

if ( 'cli' !== php_sapi_name() ) {
	die( "CLI only\n" );
}

$opts = getopt( '', array( 'src:', 'out:', 'root::' ) );

if ( empty( $opts['src'] ) || empty( $opts['out'] ) ) {
	fwrite( STDERR, "Usage: php tools/build-zip.php --src=themes/xi-novels --out=dist/theme.zip\n" );
	exit( 1 );
}

$src = rtrim( str_replace( '\\', '/', realpath( $opts['src'] ) ), '/' );
$out = str_replace( '\\', '/', $opts['out'] );
$root = ! empty( $opts['root'] ) ? trim( $opts['root'], '/' ) : basename( $src );

if ( ! $src || ! is_dir( $src ) ) {
	fwrite( STDERR, "Source folder not found: {$opts['src']}\n" );
	exit( 1 );
}

if ( ! class_exists( 'ZipArchive' ) ) {
	fwrite( STDERR, "The zip extension is not available in this PHP build.\n" );
	exit( 1 );
}

/*
 * bootstrap в списке не по ошибке: папка assets/vendor/bootstrap лежит под
 * vendor/ в .gitignore, то есть в репозитории её нет, а на диске она остаётся
 * с тех пор, когда тема грузила фреймворк. Без этой строки давно не нужные
 * 313 КБ продолжали бы уезжать в каждую сборку темы.
 */
$skip = array( '.git', '.github', 'node_modules', 'bootstrap', '.DS_Store', 'Thumbs.db', 'desktop.ini' );

$dir = dirname( $out );
if ( $dir && ! is_dir( $dir ) ) {
	mkdir( $dir, 0777, true );
}
if ( file_exists( $out ) ) {
	unlink( $out );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $out, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "Cannot create {$out}\n" );
	exit( 1 );
}

$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ),
	RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;

foreach ( $files as $file ) {
	$path     = str_replace( '\\', '/', $file->getPathname() );
	$relative = ltrim( substr( $path, strlen( $src ) ), '/' );

	if ( '' === $relative ) {
		continue;
	}

	foreach ( $skip as $unwanted ) {
		// Совпадение искали только в начале пути и по имени самого файла,
		// поэтому вложенная папка отбрасывалась лишь как пустая запись, а её
		// содержимое всё равно уезжало в архив: assets/vendor/bootstrap/*.min.js
		// не начинается с 'bootstrap' и не называется так. Проверяем ещё и
		// вхождение папки в середину пути.
		if ( $relative === $unwanted
			|| 0 === strpos( $relative, $unwanted . '/' )
			|| false !== strpos( $relative, '/' . $unwanted . '/' )
			|| basename( $relative ) === $unwanted
		) {
			continue 2;
		}
	}

	$entry = $root . '/' . $relative;

	if ( $file->isDir() ) {
		$zip->addEmptyDir( $entry );
		continue;
	}

	$zip->addFile( $path, $entry );
	$count++;
}

$zip->close();

printf( "%s\n  root: %s/\n  files: %d\n  size: %d KB\n", $out, $root, $count, (int) round( filesize( $out ) / 1024 ) );

$check = new ZipArchive();
if ( true === $check->open( $out ) ) {
	$bad = 0;
	for ( $i = 0; $i < $check->numFiles; $i++ ) {
		if ( false !== strpos( $check->getNameIndex( $i ), '\\' ) ) {
			$bad++;
		}
	}
	$check->close();
	printf( "  backslash entries: %d%s\n", $bad, $bad ? '  <-- broken' : '' );
}
