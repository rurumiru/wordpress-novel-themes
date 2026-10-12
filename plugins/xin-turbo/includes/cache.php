<?php
/**
 * Кэш готовых страниц.
 *
 * Самая большая экономия на площадке для чтения — не запускать WordPress
 * вообще. Гость, открывший главу, получает один и тот же HTML, что и
 * предыдущий гость: смысла собирать его заново нет.
 *
 * Перехватчик кладётся в `wp-content/advanced-cache.php` — файл, который
 * WordPress подключает до подключения плагинов и даже до темы. Отдача
 * готовой страницы оттуда стоит примерно столько же, сколько отдача обычного
 * файла.
 *
 * Кэшируются только гости и только GET без параметров. Вошедший читатель
 * видит своё избранное и свою серию чтения, и отдать ему чужую страницу
 * нельзя.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xt_cache_dir() {
	$uploads = wp_get_upload_dir();

	return trailingslashit( $uploads['basedir'] ) . 'xin-turbo-cache';
}

function xt_dropin_path() {
	return WP_CONTENT_DIR . '/advanced-cache.php';
}

/**
 * Наш ли это перехватчик. Чужой не трогаем: там может быть другой кэш.
 */
function xt_dropin_is_ours() {
	$file = xt_dropin_path();

	if ( ! file_exists( $file ) ) {
		return false;
	}

	return false !== strpos( (string) file_get_contents( $file ), 'XIN Turbo page cache' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

function xt_dropin_installed() {
	return xt_dropin_is_ours() && defined( 'WP_CACHE' ) && WP_CACHE;
}

/**
 * Ставит перехватчик и включает WP_CACHE в wp-config.php.
 *
 * @return true|WP_Error
 */
function xt_install_dropin() {
	global $wp_filesystem;

	$file = xt_dropin_path();

	if ( file_exists( $file ) && ! xt_dropin_is_ours() ) {
		return new WP_Error(
			'xt_foreign_dropin',
			__( 'В wp-content уже лежит чужой advanced-cache.php — видимо, от другого плагина кэширования. Уберите его или выключите тот плагин.', 'xin-turbo' )
		);
	}

	$source = XT_DIR . 'advanced-cache.php';

	if ( ! file_exists( $source ) ) {
		return new WP_Error( 'xt_no_source', __( 'Файл перехватчика не найден в плагине.', 'xin-turbo' ) );
	}

	if ( ! wp_mkdir_p( xt_cache_dir() ) ) {
		return new WP_Error( 'xt_no_dir', __( 'Не удалось создать папку кэша в uploads.', 'xin-turbo' ) );
	}

	$body = (string) file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$body = str_replace( '{{CACHE_DIR}}', addslashes( xt_cache_dir() ), $body );

	if ( false === file_put_contents( $file, $body ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return new WP_Error( 'xt_no_write', __( 'Нет прав на запись в wp-content.', 'xin-turbo' ) );
	}

	return xt_set_wp_cache( true );
}

function xt_remove_dropin() {
	if ( xt_dropin_is_ours() ) {
		wp_delete_file( xt_dropin_path() );
	}

	xt_set_wp_cache( false );
}

/**
 * Переключает константу WP_CACHE в wp-config.php.
 *
 * Без неё WordPress перехватчик не подключит, а дописывать её руками — самое
 * частое место, где установка кэша ломается.
 */
function xt_set_wp_cache( $on ) {
	$config = ABSPATH . 'wp-config.php';

	if ( ! file_exists( $config ) ) {
		$config = dirname( ABSPATH ) . '/wp-config.php';
	}

	if ( ! file_exists( $config ) || ! is_writable( $config ) ) {
		return new WP_Error(
			'xt_config_locked',
			__( 'wp-config.php закрыт на запись. Допишите в него строку define( \'WP_CACHE\', true ); вручную — выше строки «That\'s all, stop editing».', 'xin-turbo' )
		);
	}

	$body = (string) file_get_contents( $config ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$line = "define( 'WP_CACHE', " . ( $on ? 'true' : 'false' ) . " ); // XIN Turbo";

	if ( preg_match( "~^.*define\(\s*'WP_CACHE'.*$~m", $body ) ) {
		$body = preg_replace( "~^.*define\(\s*'WP_CACHE'.*$~m", $line, $body, 1 );
	} elseif ( $on ) {
		$body = preg_replace( '~^<\?php~', "<?php\n" . $line, $body, 1 );
	}

	if ( false === file_put_contents( $config, $body ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return new WP_Error( 'xt_config_write', __( 'Не удалось записать wp-config.php.', 'xin-turbo' ) );
	}

	return true;
}

/* -------------------------------------------------------------------------
 * Запись страниц
 * ---------------------------------------------------------------------- */

/**
 * Можно ли положить эту страницу в кэш.
 *
 * Список намеренно строгий. Отданная не тому человеку страница — самая
 * дорогая ошибка кэша, и лучше не закэшировать лишнее, чем показать
 * чужой кабинет.
 */
function xt_cacheable() {
	if ( ! xt_opt( 'page_cache' ) ) {
		return false;
	}

	if ( is_user_logged_in() || is_admin() || is_feed() || is_preview() || is_404() || is_search() ) {
		return false;
	}

	if ( defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) || defined( 'REST_REQUEST' ) || defined( 'XMLRPC_REQUEST' ) ) {
		return false;
	}

	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
		return false;
	}

	if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	if ( is_post_type_archive() && get_query_var( 'paged' ) > 20 ) {
		return false;
	}

	// Комментарий только что оставлен — читатель должен увидеть его сразу.
	foreach ( array_keys( (array) $_COOKIE ) as $name ) {
		if ( 0 === strpos( $name, 'comment_author' ) || 0 === strpos( $name, 'wordpress_logged_in' ) || 0 === strpos( $name, 'woocommerce_' ) ) {
			return false;
		}
	}

	return (bool) apply_filters( 'xt_cacheable', true );
}

function xt_cache_key( $url = '' ) {
	$url = $url ? $url : ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	$host = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	return md5( $host . '|' . $url );
}

function xt_cache_file( $key ) {
	// Раскладываем по двум уровням: сто тысяч файлов в одной папке заметно
	// медленнее читаются на обычной файловой системе.
	return xt_cache_dir() . '/' . substr( $key, 0, 2 ) . '/' . substr( $key, 2, 2 ) . '/' . $key . '.html';
}

function xt_start_buffer() {
	if ( ! xt_cacheable() ) {
		return;
	}

	ob_start( 'xt_store_page' );
}
add_action( 'template_redirect', 'xt_start_buffer', 0 );

/**
 * Кладёт готовую страницу на диск.
 */
function xt_store_page( $html ) {
	if ( strlen( $html ) < 255 || ! xt_cacheable() ) {
		return $html;
	}

	// Страницу с ошибкой запоминать нельзя — она застрянет надолго.
	if ( false !== stripos( $html, '<title>Ошибка базы данных' ) || 200 !== http_response_code() ) {
		return $html;
	}

	$file = xt_cache_file( xt_cache_key() );
	$dir  = dirname( $file );

	if ( ! wp_mkdir_p( $dir ) ) {
		return $html;
	}

	$stamp = "\n<!-- XIN Turbo: " . gmdate( 'c' ) . " -->";

	// Пишем через временный файл: иначе посетитель может получить половину
	// страницы, пока она ещё записывается.
	$tmp = $file . '.' . wp_generate_password( 6, false ) . '.tmp';

	if ( false !== file_put_contents( $tmp, $html . $stamp ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		rename( $tmp, $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
	}

	return $html;
}

/* -------------------------------------------------------------------------
 * Сброс
 * ---------------------------------------------------------------------- */

/**
 * Убирает весь кэш. Точечный сброс здесь был бы обманом: главная, каталог,
 * лента обновлений и страница тайтла меняются от одной новой главы, и
 * угадывать этот список — верный способ показать читателю вчерашнее.
 */
function xt_cache_clear() {
	$dir = xt_cache_dir();

	if ( ! is_dir( $dir ) ) {
		return 0;
	}

	$count = 0;
	$it    = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			wp_delete_file( $file->getPathname() );
			$count++;
		} elseif ( $file->isDir() ) {
			@rmdir( $file->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}

	return $count;
}

function xt_cache_size() {
	$dir = xt_cache_dir();

	if ( ! is_dir( $dir ) ) {
		return array( 'files' => 0, 'bytes' => 0 );
	}

	$files = 0;
	$bytes = 0;

	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			$files++;
			$bytes += $file->getSize();
		}
	}

	return array( 'files' => $files, 'bytes' => $bytes );
}

/**
 * Всё, что меняет вид сайта, сбрасывает кэш.
 */
function xt_purge_on_change() {
	if ( ! xt_opt( 'page_cache' ) ) {
		return;
	}

	xt_cache_clear();
}
add_action( 'save_post', 'xt_purge_on_change', 99 );
add_action( 'deleted_post', 'xt_purge_on_change', 99 );
add_action( 'comment_post', 'xt_purge_on_change', 99 );
add_action( 'wp_set_comment_status', 'xt_purge_on_change', 99 );
add_action( 'switch_theme', 'xt_purge_on_change', 99 );
add_action( 'customize_save_after', 'xt_purge_on_change', 99 );
add_action( 'updated_option', 'xt_purge_on_change', 99 );

/**
 * Старые файлы убираем по ночам: без этого папка кэша растёт вместе с
 * каталогом и однажды упирается в место на диске.
 */
function xt_cache_expire() {
	$dir = xt_cache_dir();

	if ( ! is_dir( $dir ) ) {
		return;
	}

	$life = max( 300, (int) xt_opt( 'cache_life', 3600 ) );
	$edge = time() - $life;

	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $it as $file ) {
		if ( $file->isFile() && $file->getMTime() < $edge ) {
			wp_delete_file( $file->getPathname() );
		}
	}
}
add_action( 'xt_nightly', 'xt_cache_expire' );
