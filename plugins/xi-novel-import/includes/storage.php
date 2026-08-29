<?php
/**
 * Выгрузка страниц комиксов в S3-совместимое хранилище.
 *
 * Зачем: глава комикса — это десятки картинок по мегабайту, и на площадке из
 * тысячи глав это десятки гигабайт на диске сервера, которые к тому же надо
 * раздавать самому. В хранилище они лежат дешевле и раздаются через его домен
 * или CDN.
 *
 * Подписывается запрос вручную, по AWS Signature V4: тянуть SDK ради двух
 * запросов значило бы завести composer в проекте, где его нет и не должно быть.
 * Работает с любым S3-совместимым хранилищем — Amazon S3, Yandex Object
 * Storage, MinIO, Cloudflare R2, Selectel.
 *
 * Границы, которые стоит знать заранее:
 *
 * 1. Выгружаются только страницы комиксов, а не вся медиатека. Подменять адреса
 *    всем вложениям подряд — это отдельное решение с другими последствиями, и
 *    принимать его молча за пользователя неправильно.
 * 2. Файл уходит целиком в памяти: WP HTTP API не умеет потоковую отправку.
 *    Поэтому есть предел размера, и он проверяется до отправки, а не после.
 * 3. Ключ доступа лежит в опциях. Кто может читать базу — тот прочтёт и ключ.
 *    Поэтому поддержаны константы в wp-config.php, и это правильный путь.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Опция с настройками хранилища. */
const XNI_S3_OPTION = 'xni_s3';

/** Предел размера одного файла: всё уходит через память. */
const XNI_S3_MAX_BYTES = 33554432; // 32 МБ.

/**
 * Настройки хранилища.
 *
 * Ключ и секрет можно задать константами в wp-config.php — тогда они не лежат
 * в базе, и утечка дампа не означает утечку доступа к бакету. Константа всегда
 * сильнее поля в форме.
 *
 * @return array
 */
function xni_s3_settings() {
	$saved = get_option( XNI_S3_OPTION, array() );

	$settings = wp_parse_args( is_array( $saved ) ? $saved : array(), array(
		'enabled'    => 0,
		'endpoint'   => '',
		'region'     => 'us-east-1',
		'bucket'     => '',
		'key'        => '',
		'secret'     => '',
		'prefix'     => 'comics',
		'base_url'   => '',
		'path_style' => 1,
		'keep_local' => 1,
		'public_acl' => 1,
		'mirrors'    => '',
	) );

	if ( defined( 'XNI_S3_KEY' ) ) {
		$settings['key'] = XNI_S3_KEY;
	}

	if ( defined( 'XNI_S3_SECRET' ) ) {
		$settings['secret'] = XNI_S3_SECRET;
	}

	return $settings;
}

/**
 * Настроено ли хранилище настолько, чтобы им можно было пользоваться.
 *
 * @return bool
 */
function xni_s3_ready() {
	$s = xni_s3_settings();

	return (bool) ( $s['endpoint'] && $s['bucket'] && $s['key'] && $s['secret'] );
}

/**
 * @return bool
 */
function xni_s3_enabled() {
	$s = xni_s3_settings();

	return ! empty( $s['enabled'] ) && xni_s3_ready();
}

/* -------------------------------------------------------------------------
 * Подпись и запрос
 * ---------------------------------------------------------------------- */

/**
 * Адрес объекта в хранилище.
 *
 * @param string $key Ключ объекта.
 * @return string
 */
function xni_s3_object_url( $key ) {
	$s    = xni_s3_settings();
	$host = wp_parse_url( $s['endpoint'], PHP_URL_HOST );
	$base = rtrim( $s['endpoint'], '/' );

	if ( empty( $s['path_style'] ) ) {
		$scheme = wp_parse_url( $s['endpoint'], PHP_URL_SCHEME );
		$base   = $scheme . '://' . $s['bucket'] . '.' . $host;

		return $base . '/' . $key;
	}

	return $base . '/' . $s['bucket'] . '/' . $key;
}

/**
 * Публичный адрес объекта: домен CDN, если задан, иначе адрес хранилища.
 *
 * @param string $key Ключ объекта.
 * @return string
 */
function xni_s3_public_url( $key ) {
	$s = xni_s3_settings();

	if ( $s['base_url'] ) {
		return rtrim( $s['base_url'], '/' ) . '/' . $key;
	}

	return xni_s3_object_url( $key );
}

/**
 * Кодирует путь так, как того требует подпись: каждый сегмент отдельно.
 *
 * @param string $path Путь.
 * @return string
 */
function xni_s3_encode_path( $path ) {
	$parts = explode( '/', ltrim( $path, '/' ) );
	$parts = array_map( 'rawurlencode', $parts );

	return '/' . implode( '/', $parts );
}

/**
 * Собирает заголовок Authorization по AWS Signature V4.
 *
 * Вынесено из отправки, чтобы подпись можно было проверить отдельно от сети:
 * алгоритм здесь ровно тот, что описан в документации AWS, и прогоняется на её
 * же контрольном примере.
 *
 * @param string $method  HTTP-метод.
 * @param string $path    Путь, уже закодированный посегментно.
 * @param array  $headers Заголовки запроса, включая host, в нижнем регистре.
 * @param string $hash    Хеш тела.
 * @param string $stamp   Отметка вида 20130524T000000Z.
 * @param string $region  Регион.
 * @param string $access  Ключ доступа.
 * @param string $secret  Секрет.
 * @return string
 */
function xni_s3_authorization( $method, $path, $headers, $hash, $stamp, $region, $access, $secret ) {
	$date  = substr( $stamp, 0, 8 );
	$scope = $date . '/' . $region . '/s3/aws4_request';

	ksort( $headers );

	$canonical_headers = '';
	foreach ( $headers as $name => $value ) {
		$canonical_headers .= $name . ':' . trim( $value ) . "\n";
	}

	$signed_headers = implode( ';', array_keys( $headers ) );

	$canonical_request = implode( "\n", array(
		$method,
		$path,
		'',
		$canonical_headers,
		$signed_headers,
		$hash,
	) );

	$string_to_sign = implode( "\n", array(
		'AWS4-HMAC-SHA256',
		$stamp,
		$scope,
		hash( 'sha256', $canonical_request ),
	) );

	$signing_key = hash_hmac( 'sha256', $date, 'AWS4' . $secret, true );
	$signing_key = hash_hmac( 'sha256', $region, $signing_key, true );
	$signing_key = hash_hmac( 'sha256', 's3', $signing_key, true );
	$signing_key = hash_hmac( 'sha256', 'aws4_request', $signing_key, true );

	return sprintf(
		'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
		$access,
		$scope,
		$signed_headers,
		hash_hmac( 'sha256', $string_to_sign, $signing_key )
	);
}

/**
 * Подписывает и выполняет запрос к хранилищу.
 *
 * @param string $method HTTP-метод.
 * @param string $key    Ключ объекта.
 * @param string $body   Тело запроса.
 * @param string $mime   Тип содержимого.
 * @return array|WP_Error Ответ wp_remote_request или ошибка.
 */
function xni_s3_request( $method, $key, $body = '', $mime = '', $extra = array() ) {
	$s = xni_s3_settings();

	if ( ! xni_s3_ready() ) {
		return new WP_Error( 'xni_s3_config', __( 'Хранилище не настроено: нужны адрес, бакет, ключ и секрет.', 'xi-novel-import' ) );
	}

	$url   = xni_s3_object_url( $key );
	$path  = xni_s3_encode_path( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	$stamp = gmdate( 'Ymd\THis\Z' );
	$hash  = hash( 'sha256', $body );

	$headers = array(
		'host'                 => (string) wp_parse_url( $url, PHP_URL_HOST ),
		'x-amz-content-sha256' => $hash,
		'x-amz-date'           => $stamp,
	);

	if ( $mime ) {
		$headers['content-type'] = $mime;
	}

	foreach ( $extra as $name => $value ) {
		$headers[ strtolower( $name ) ] = $value;
	}

	$send = $headers;
	unset( $send['host'] );

	$send['Authorization'] = xni_s3_authorization( $method, $path, $headers, $hash, $stamp, $s['region'], $s['key'], $s['secret'] );

	return wp_remote_request( $url, array(
		'method'  => $method,
		'headers' => $send,
		'body'    => $body,
		'timeout' => 30,
	) );
}

/**
 * Кладёт файл в хранилище.
 *
 * @param string $key  Ключ объекта.
 * @param string $file Путь к файлу на диске.
 * @return true|WP_Error
 */
function xni_s3_put_file( $key, $file ) {
	if ( ! is_readable( $file ) ) {
		return new WP_Error( 'xni_s3_file', __( 'Файл не читается.', 'xi-novel-import' ) );
	}

	$size = (int) filesize( $file );

	if ( $size > XNI_S3_MAX_BYTES ) {
		return new WP_Error(
			'xni_s3_size',
			sprintf(
				/* translators: 1 — размер файла, 2 — предел. */
				__( 'Файл на %1$s не влезает в предел %2$s: отправка идёт через память.', 'xi-novel-import' ),
				size_format( $size ),
				size_format( XNI_S3_MAX_BYTES )
			)
		);
	}

	$body = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $body ) {
		return new WP_Error( 'xni_s3_read', __( 'Не удалось прочитать файл.', 'xi-novel-import' ) );
	}

	$s     = xni_s3_settings();
	$type  = wp_check_filetype( $file );
	$extra = array();

	/*
	 * Без этого объект остаётся закрытым, и браузер получает от хранилища 403:
	 * запрос картинки со страницы не подписан и подписан быть не может. У части
	 * провайдеров ACL отключены на уровне бакета и заголовок вызывает отказ —
	 * там доступ открывают политикой бакета, а флаг снимают.
	 */
	if ( ! empty( $s['public_acl'] ) ) {
		$extra['x-amz-acl'] = 'public-read';
	}

	$response = xni_s3_request( 'PUT', $key, $body, $type['type'] ? $type['type'] : 'application/octet-stream', $extra );

	return xni_s3_check( $response, __( 'Загрузка', 'xi-novel-import' ) );
}

/**
 * Разбирает ответ хранилища.
 *
 * S3 отвечает ошибкой в XML, и человеку нужен её текст, а не «код 403»:
 * SignatureDoesNotMatch и NoSuchBucket требуют совершенно разных действий.
 *
 * @param array|WP_Error $response Ответ.
 * @param string         $what     Что делали.
 * @return true|WP_Error
 */
function xni_s3_check( $response, $what ) {
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( $code >= 200 && $code < 300 ) {
		return true;
	}

	$body    = wp_remote_retrieve_body( $response );
	$message = '';

	if ( $body && preg_match( '~<Code>([^<]+)</Code>~', $body, $m ) ) {
		$message = $m[1];

		if ( preg_match( '~<Message>([^<]+)</Message>~', $body, $m2 ) ) {
			$message .= ': ' . $m2[1];
		}
	}

	return new WP_Error(
		'xni_s3_http',
		sprintf(
			/* translators: 1 — действие, 2 — код ответа, 3 — сообщение хранилища. */
			__( '%1$s не удалась. Код %2$d. %3$s', 'xi-novel-import' ),
			$what,
			$code,
			$message ? $message : __( 'Хранилище не объяснило причину.', 'xi-novel-import' )
		)
	);
}

/**
 * Удаляет объект.
 *
 * @param string $key Ключ объекта.
 * @return true|WP_Error
 */
function xni_s3_delete( $key ) {
	return xni_s3_check( xni_s3_request( 'DELETE', $key ), __( 'Удаление', 'xi-novel-import' ) );
}

/**
 * Зеркала раздачи: те же объекты, другой домен.
 *
 * Хранится строкой — по одному зеркалу в строке, «Название|адрес». Репитер в
 * настройках дал бы то же самое ценой отдельного контрола и его состояния, а
 * зеркал у площадки бывает два-три, и правят их раз в год.
 *
 * @return array<int, array{label: string, base: string}>
 */
function xni_s3_mirrors() {
	$s   = xni_s3_settings();
	$out = array();

	foreach ( preg_split( '~\r\n|\r|\n~', (string) $s['mirrors'] ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$base  = isset( $parts[1] ) ? $parts[1] : $parts[0];
		$label = isset( $parts[1] ) ? $parts[0] : wp_parse_url( $base, PHP_URL_HOST );

		/*
		 * Проверяем схему и хост, а не `wp_http_validate_url()`: та резолвит имя
		 * и отбраковывает всё, что не разрешается с сервера, — защита от запросов
		 * во внутреннюю сеть. Но по этому адресу ходит браузер читателя, а не
		 * сервер, и домен CDN с площадки может не резолвиться вовсе. Такая
		 * проверка молча выбрасывала бы рабочие зеркала.
		 */
		$scheme = wp_parse_url( $base, PHP_URL_SCHEME );
		$host   = wp_parse_url( $base, PHP_URL_HOST );

		if ( ! $host || ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			continue;
		}

		$out[] = array(
			'label' => (string) $label,
			'base'  => rtrim( $base, '/' ),
		);
	}

	return $out;
}

/**
 * Добавляет зеркала в список источников читалки.
 *
 * Зеркало предлагается только тогда, когда все страницы главы действительно
 * лежат в хранилище: у главы, выгруженной наполовину, переключение показало бы
 * дыру вместо кадра.
 *
 * @param array $sources    Источники от темы.
 * @param int   $chapter_id Глава.
 * @return array
 */
function xni_s3_page_sources( $sources, $chapter_id ) {
	$mirrors = xni_s3_mirrors();

	if ( ! $mirrors || ! function_exists( 'xin_comic_pages' ) ) {
		return $sources;
	}

	$keys = array();

	foreach ( xin_comic_pages( $chapter_id ) as $page_id ) {
		$key = get_post_meta( $page_id, '_xni_s3_key', true );

		if ( ! $key ) {
			return $sources;
		}

		$keys[] = $key;
	}

	if ( ! $keys ) {
		return $sources;
	}

	foreach ( $mirrors as $i => $mirror ) {
		$urls = array();

		foreach ( $keys as $key ) {
			$urls[] = $mirror['base'] . '/' . $key;
		}

		$sources[] = array(
			'id'    => 'mirror-' . $i,
			'label' => $mirror['label'],
			'urls'  => $urls,
		);
	}

	return $sources;
}
add_filter( 'xin_comic_page_sources', 'xni_s3_page_sources', 10, 2 );

/* -------------------------------------------------------------------------
 * Выгрузка вложений
 * ---------------------------------------------------------------------- */

/**
 * Ключ объекта для вложения.
 *
 * В ключе есть год, месяц и ID: без ID два файла с одинаковым именем из разных
 * глав затёрли бы друг друга.
 *
 * @param int $attachment_id Вложение.
 * @return string
 */
function xni_s3_key_for( $attachment_id ) {
	$s    = xni_s3_settings();
	$file = get_post_meta( $attachment_id, '_wp_attached_file', true );
	$name = $file ? basename( $file ) : ( 'file-' . $attachment_id );
	$date = get_post_time( 'Y/m', true, $attachment_id );

	$parts = array_filter( array(
		trim( (string) $s['prefix'], '/' ),
		$date ? $date : gmdate( 'Y/m' ),
		$attachment_id,
		sanitize_file_name( $name ),
	) );

	return implode( '/', $parts );
}

/**
 * Выгружает вложение в хранилище.
 *
 * @param int  $attachment_id Вложение.
 * @param bool $force         Выгрузить, даже если уже выгружено.
 * @return true|WP_Error
 */
function xni_s3_offload( $attachment_id, $force = false ) {
	if ( ! xni_s3_enabled() ) {
		return new WP_Error( 'xni_s3_off', __( 'Выгрузка в хранилище выключена.', 'xi-novel-import' ) );
	}

	if ( ! $force && get_post_meta( $attachment_id, '_xni_s3_key', true ) ) {
		return true;
	}

	$file = get_attached_file( $attachment_id );

	if ( ! $file || ! file_exists( $file ) ) {
		return new WP_Error( 'xni_s3_missing', __( 'Файла вложения нет на диске.', 'xi-novel-import' ) );
	}

	$key    = xni_s3_key_for( $attachment_id );
	$result = xni_s3_put_file( $key, $file );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	update_post_meta( $attachment_id, '_xni_s3_key', $key );
	update_post_meta( $attachment_id, '_xni_s3_url', xni_s3_public_url( $key ) );

	$s = xni_s3_settings();

	/*
	 * Локальную копию удаляем только по явной просьбе и только после успешной
	 * выгрузки: удалить раньше — значит потерять страницу, если хранилище
	 * ответит ошибкой.
	 */
	if ( empty( $s['keep_local'] ) ) {
		wp_delete_file( $file );
	}

	return true;
}

/**
 * Выгружает все страницы главы.
 *
 * @param int $chapter_id Глава.
 * @return array{ok: int, failed: int, errors: string[]}
 */
function xni_s3_offload_chapter( $chapter_id ) {
	$pages  = get_post_meta( $chapter_id, '_xin_pages', true );
	$report = array(
		'ok'     => 0,
		'failed' => 0,
		'errors' => array(),
	);

	if ( ! is_array( $pages ) ) {
		return $report;
	}

	foreach ( $pages as $page_id ) {
		$result = xni_s3_offload( (int) $page_id );

		if ( is_wp_error( $result ) ) {
			++$report['failed'];
			$report['errors'][] = $result->get_error_message();
			continue;
		}

		++$report['ok'];
	}

	return $report;
}

/**
 * Страницы уходят в хранилище сразу после того, как их привязали к главе.
 *
 * @param int    $meta_id    Идентификатор строки меты.
 * @param int    $post_id    Запись.
 * @param string $meta_key   Ключ меты.
 * @return void
 */
function xni_s3_on_pages_saved( $meta_id, $post_id, $meta_key ) {
	if ( '_xin_pages' !== $meta_key || ! xni_s3_enabled() ) {
		return;
	}

	xni_s3_offload_chapter( (int) $post_id );
}
add_action( 'updated_post_meta', 'xni_s3_on_pages_saved', 10, 3 );
add_action( 'added_post_meta', 'xni_s3_on_pages_saved', 10, 3 );

/**
 * Выгруженное вложение отдаётся с адреса хранилища.
 *
 * @param string $url           Адрес.
 * @param int    $attachment_id Вложение.
 * @return string
 */
function xni_s3_attachment_url( $url, $attachment_id ) {
	$remote = get_post_meta( $attachment_id, '_xni_s3_url', true );

	return $remote ? $remote : $url;
}
add_filter( 'wp_get_attachment_url', 'xni_s3_attachment_url', 10, 2 );

/**
 * Вложение удалили — убираем и объект, иначе в бакете копятся сироты.
 *
 * @param int $attachment_id Вложение.
 * @return void
 */
function xni_s3_on_delete( $attachment_id ) {
	$key = get_post_meta( $attachment_id, '_xni_s3_key', true );

	if ( $key && xni_s3_ready() ) {
		xni_s3_delete( $key );
	}
}
add_action( 'delete_attachment', 'xni_s3_on_delete' );

/* -------------------------------------------------------------------------
 * Проверка связи
 * ---------------------------------------------------------------------- */

/**
 * Кладёт, читает и убирает пробный объект.
 *
 * Проверка именно записью, а не запросом списка: право на список и право на
 * запись — разные права, и «подключение есть» без права на запись означало бы
 * обещание, которого хранилище не выполнит.
 *
 * @return true|WP_Error
 */
function xni_s3_probe() {
	$s   = xni_s3_settings();
	$key = trim( (string) $s['prefix'], '/' ) . '/.xni-probe-' . wp_generate_password( 8, false );

	$put = xni_s3_check( xni_s3_request( 'PUT', $key, 'xni', 'text/plain' ), __( 'Пробная запись', 'xi-novel-import' ) );

	if ( is_wp_error( $put ) ) {
		return $put;
	}

	$get = xni_s3_check( xni_s3_request( 'GET', $key ), __( 'Пробное чтение', 'xi-novel-import' ) );

	xni_s3_delete( $key );

	return $get;
}
