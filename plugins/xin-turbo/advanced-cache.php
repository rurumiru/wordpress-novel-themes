<?php
/**
 * XIN Turbo page cache — перехватчик.
 *
 * Этот файл WordPress подключает раньше всего остального: до плагинов, до
 * темы и до единого запроса к базе. Поэтому здесь нет ни одной функции
 * WordPress — их ещё не существует.
 *
 * Задача одна: если для этого адреса уже лежит готовая страница и она не
 * протухла, отдать её и закончить работу. Всё остальное — не сюда.
 *
 * Файл ставится и убирается со страницы настроек плагина; править его руками
 * не нужно.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$xt_dir = '{{CACHE_DIR}}';

/* Только обычные GET-запросы без параметров. */
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
	return;
}

if ( ! empty( $_GET ) ) {
	return;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}

/* Вошедший читатель, покупатель и тот, кто только что написал комментарий,
   видят своё — им отдавать общую страницу нельзя. */
foreach ( array_keys( (array) $_COOKIE ) as $xt_name ) {
	if (
		0 === strpos( $xt_name, 'wordpress_logged_in' )
		|| 0 === strpos( $xt_name, 'comment_author' )
		|| 0 === strpos( $xt_name, 'woocommerce_' )
		|| 0 === strpos( $xt_name, 'wp_woocommerce_session' )
	) {
		return;
	}
}

$xt_uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
$xt_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';

/* Административные и служебные адреса мимо. */
if (
	false !== strpos( $xt_uri, '/wp-admin' )
	|| false !== strpos( $xt_uri, '/wp-login' )
	|| false !== strpos( $xt_uri, '/wp-json' )
	|| false !== strpos( $xt_uri, '/xmlrpc.php' )
	|| false !== strpos( $xt_uri, '/feed' )
) {
	return;
}

$xt_key  = md5( $xt_host . '|' . $xt_uri );
$xt_file = $xt_dir . '/' . substr( $xt_key, 0, 2 ) . '/' . substr( $xt_key, 2, 2 ) . '/' . $xt_key . '.html';

if ( ! is_readable( $xt_file ) ) {
	return;
}

$xt_age  = time() - (int) filemtime( $xt_file );
$xt_life = 3600;

if ( $xt_age > $xt_life ) {
	return;
}

/* Браузеру, у которого страница уже есть, отдаём пустой ответ: это дешевле
   и для сервера, и для мобильного канала. */
$xt_tag = '"' . $xt_key . '-' . filemtime( $xt_file ) . '"';

header( 'Content-Type: text/html; charset=UTF-8' );
header( 'X-XIN-Turbo: hit' );
header( 'ETag: ' . $xt_tag );
header( 'Cache-Control: public, max-age=0, must-revalidate' );

if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && trim( $_SERVER['HTTP_IF_NONE_MATCH'] ) === $xt_tag ) {
	http_response_code( 304 );
	exit;
}

readfile( $xt_file );
exit;
