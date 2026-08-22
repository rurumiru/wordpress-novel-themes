<?php
/**
 * Пакетная обработка: ZIP распаковывается один раз, а главы идут порциями.
 *
 * Загрузить архив на пятьсот глав и разобрать его в одном запросе — верный
 * способ упереться в лимит времени и положить сайт на минуту-другую. Поэтому
 * запрос делает ровно одно: кладёт архив в отдельную папку и записывает список
 * файлов. Дальше браузер сам просит следующую порцию, по десять штук за раз, и
 * между порциями сервер свободен.
 *
 * Состояние задания живёт в опции: его переживает и перезагрузка страницы, и
 * оборванное соединение — задание можно продолжить.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XNI_BATCH = 10;

function xni_job_key( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return 'xni_job_' . $user_id;
}

function xni_job_get( $user_id = 0 ) {
	$job = get_option( xni_job_key( $user_id ) );
	return is_array( $job ) ? $job : null;
}

function xni_job_put( $job ) {
	update_option( xni_job_key(), $job, false );
}

function xni_job_clear() {
	$job = xni_job_get();
	if ( $job && ! empty( $job['dir'] ) ) {
		xni_rmdir( $job['dir'] );
	}
	delete_option( xni_job_key() );
}

/**
 * Убирает за собой распакованный архив.
 */
function xni_rmdir( $dir ) {
	$base = trailingslashit( wp_upload_dir()['basedir'] ) . 'xni-jobs';

	// Никогда не удаляем ничего за пределами своей песочницы.
	if ( 0 !== strpos( wp_normalize_path( $dir ), wp_normalize_path( $base ) ) ) {
		return;
	}
	if ( ! is_dir( $dir ) ) {
		return;
	}

	foreach ( (array) glob( trailingslashit( $dir ) . '*' ) as $file ) {
		if ( is_dir( $file ) ) {
			xni_rmdir( $file );
			continue;
		}
		wp_delete_file( $file );
	}

	@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- уже пусто или уже нет.
}

/**
 * Распаковывает архив в свою папку и возвращает отсортированный список файлов.
 *
 * @param string $zip_path Путь к загруженному архиву.
 * @return array|WP_Error dir и files.
 */
function xni_unpack( $zip_path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'xni_zip', __( 'На сервере нет расширения ZipArchive — архив не распаковать.', 'xi-novel-import' ) );
	}

	$uploads = wp_upload_dir();
	$base    = trailingslashit( $uploads['basedir'] ) . 'xni-jobs';
	$dir     = trailingslashit( $base ) . 'job-' . get_current_user_id() . '-' . time();

	if ( ! wp_mkdir_p( $dir ) ) {
		return new WP_Error( 'xni_dir', __( 'Не удалось создать папку для распаковки в uploads.', 'xi-novel-import' ) );
	}

	// Папка служебная: ни листинга, ни отдачи наружу.
	if ( ! file_exists( trailingslashit( $base ) . 'index.php' ) ) {
		file_put_contents( trailingslashit( $base ) . 'index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path ) ) {
		xni_rmdir( $dir );
		return new WP_Error( 'xni_zip_open', __( 'Архив не открывается — возможно, он повреждён.', 'xi-novel-import' ) );
	}

	$files = array();

	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$entry = $zip->getNameIndex( $i );

		if ( ! $entry || '/' === substr( $entry, -1 ) ) {
			continue;
		}

		$name = basename( str_replace( '\\', '/', $entry ) );

		// Пропускаем служебное от macOS и скрытые файлы.
		if ( '' === $name || '.' === $name[0] || 0 === strpos( $entry, '__MACOSX/' ) ) {
			continue;
		}

		$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'txt', 'md', 'html', 'htm', 'docx' ), true ) ) {
			continue;
		}

		$safe = sanitize_file_name( $name );
		$dest = trailingslashit( $dir ) . $safe;

		// Одинаковые имена в разных папках архива не должны затирать друг друга.
		$n = 1;
		while ( file_exists( $dest ) ) {
			$dest = trailingslashit( $dir ) . pathinfo( $safe, PATHINFO_FILENAME ) . '-' . $n . '.' . $ext;
			$n++;
		}

		$stream = $zip->getStream( $entry );
		if ( ! $stream ) {
			continue;
		}
		file_put_contents( $dest, stream_get_contents( $stream ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fclose( $stream );

		$files[] = array( 'path' => $dest, 'name' => $name );
	}

	$zip->close();

	if ( ! $files ) {
		xni_rmdir( $dir );
		return new WP_Error( 'xni_empty', __( 'В архиве нет ни одного файла главы (.txt, .md, .html, .docx).', 'xi-novel-import' ) );
	}

	// Порядок имён должен быть человеческим: 2 раньше 10.
	usort( $files, static function ( $a, $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	} );

	return array( 'dir' => $dir, 'files' => $files );
}

/**
 * Создаёт задание из загруженного архива.
 *
 * @param string $zip_path Путь к архиву.
 * @param string $type     import | fix.
 * @param array  $args     Настройки формы.
 * @return array|WP_Error Задание.
 */
function xni_job_start( $zip_path, $type, $args ) {
	$unpacked = xni_unpack( $zip_path );

	if ( is_wp_error( $unpacked ) ) {
		return $unpacked;
	}

	xni_job_clear();

	$job = array(
		'type'    => 'fix' === $type ? 'fix' : 'import',
		'dir'     => $unpacked['dir'],
		'files'   => $unpacked['files'],
		'total'   => count( $unpacked['files'] ),
		'cursor'  => 0,
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'failed'  => 0,
		'log'     => array(),
		'args'    => $args,
		'started' => time(),
	);

	xni_job_put( $job );

	return $job;
}

/**
 * Обрабатывает следующую порцию задания.
 *
 * @return array|WP_Error Состояние задания после порции.
 */
function xni_job_step() {
	$job = xni_job_get();

	if ( ! $job ) {
		return new WP_Error( 'xni_nojob', __( 'Задание не найдено — возможно, оно уже завершилось.', 'xi-novel-import' ) );
	}

	$end = min( $job['total'], $job['cursor'] + XNI_BATCH );

	for ( $i = $job['cursor']; $i < $end; $i++ ) {
		$file = $job['files'][ $i ];
		$job  = 'fix' === $job['type']
			? xni_step_fix( $job, $file )
			: xni_step_import( $job, $file, $i );
	}

	$job['cursor'] = $end;

	// Журнал живёт в опции вместе с заданием: держим последние двести строк,
	// чтобы архив на тысячу файлов не раздул её до мегабайтов.
	if ( count( $job['log'] ) > 200 ) {
		$job['log'] = array_slice( $job['log'], -200 );
	}

	if ( $job['cursor'] >= $job['total'] ) {
		$job['done'] = true;

		if ( 'import' === $job['type'] && ! empty( $job['args']['autosort'] ) ) {
			xni_resort( (int) $job['args']['novel_id'] );
		}

		delete_transient( 'xin_site_stats' );
		if ( function_exists( 'xin_purge_caches' ) ) {
			xin_purge_caches();
		}

		xni_rmdir( $job['dir'] );
		$job['dir'] = '';
		update_option( xni_job_key(), $job, false );
	} else {
		xni_job_put( $job );
	}

	return $job;
}

/**
 * Одна глава на импорте.
 *
 * @param array $job   Задание.
 * @param array $file  Файл из архива.
 * @param int   $index Порядковый номер в архиве.
 */
function xni_step_import( $job, $file, $index ) {
	$args   = $job['args'];
	$parsed = xni_parse_file( $file['path'], $file['name'], $args['encoding'] );

	if ( is_wp_error( $parsed ) ) {
		$job['failed']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'failed', 'note' => $parsed->get_error_message() );
		return $job;
	}

	$novel_id = (int) $args['novel_id'];
	$number   = null !== $parsed['number'] ? (float) $parsed['number'] : (float) ( $args['start'] + $index );
	$existing = xni_find_chapter( $novel_id, $number );

	if ( $existing && ! empty( $args['skip_dupes'] ) ) {
		$job['skipped']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'skipped', 'note' => __( 'Такая глава уже есть.', 'xi-novel-import' ) );
		return $job;
	}

	// Естественный разбег в пару секунд: главы не встают одной меткой времени и
	// сохраняют порядок в ленте, но и не растягиваются на часы, как было бы с
	// минутным шагом.
	$stamp = $job['started'] + $index * 2;

	$data = array(
		'post_type'     => 'chapter',
		'post_title'    => wp_strip_all_tags( $parsed['title'] ),
		'post_content'  => wp_kses_post( $parsed['content'] ),
		// В очередь глава заводится черновиком сразу: иначе она успела бы мелькнуть
		// опубликованной между вставкой и постановкой в слот.
		'post_status'   => ( ! empty( $args['queue'] ) || 'draft' === $args['status'] ) ? 'draft' : 'publish',
		'post_author'   => (int) $args['author_id'],
		'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $stamp ) ),
		'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $stamp ),
	);

	if ( $existing ) {
		$data['ID'] = $existing;
		unset( $data['post_date'], $data['post_date_gmt'] );
		wp_update_post( $data );
		$chapter_id = $existing;
		$job['updated']++;
	} else {
		$chapter_id = wp_insert_post( $data );
		$job['created']++;
	}

	if ( ! $chapter_id || is_wp_error( $chapter_id ) ) {
		$job['failed']++;
		$job['log'][] = array( 'name' => $file['name'], 'state' => 'failed', 'note' => __( 'WordPress не сохранил запись.', 'xi-novel-import' ) );
		return $job;
	}

	update_post_meta( $chapter_id, '_xin_novel', $novel_id );
	update_post_meta( $chapter_id, '_xin_number', $number );

	xni_apply_money( $chapter_id, $args );

	if ( ! empty( $args['queue'] ) ) {
		$schedule = xni_schedule( $novel_id );
		$slot     = xni_next_slot( $schedule, xni_queue_tail( $novel_id ) );
		$free = ( (float) $args['price'] <= 0 ) && ! empty( $schedule['free'] );
		xni_enqueue( $chapter_id, $slot, $free, (float) $args['price'] );
		$job['log'][] = array(
			'name'  => $file['name'],
			'state' => 'queued',
			'note'  => sprintf( __( 'Выйдет %s', 'xi-novel-import' ), wp_date( 'd.m.Y H:i', $slot ) ),
		);
		return $job;
	}

	$job['log'][] = array(
		'name'  => $file['name'],
		'state' => $existing ? 'updated' : 'created',
		'note'  => sprintf( __( 'Глава %s', 'xi-novel-import' ), rtrim( rtrim( number_format( $number, 1, '.', '' ), '0' ), '.' ) ),
	);

	return $job;
}

/**
 * Цена, замок и дата разблокировки — общие для всей пачки.
 *
 * В теме нет монет: цена больше нуля означает ранний доступ, а сама сумма
 * хранится рядом и, если работает WooCommerce, превращается в товар, который
 * читатель может купить отдельно от подписки.
 */
function xni_apply_money( $chapter_id, $args ) {
	$price  = isset( $args['price'] ) ? (float) $args['price'] : 0;
	$unlock = isset( $args['unlock_at'] ) ? (int) $args['unlock_at'] : 0;

	if ( $price > 0 ) {
		update_post_meta( $chapter_id, XNI_PRICE, $price );
		update_post_meta( $chapter_id, '_xin_locked', 1 );

		$product = xni_ensure_product( $chapter_id, $price );
		if ( $product ) {
			update_post_meta( $chapter_id, '_xin_product', $product );
		}
	} else {
		delete_post_meta( $chapter_id, XNI_PRICE );
		delete_post_meta( $chapter_id, '_xin_locked' );
	}

	if ( $unlock > 0 && $price > 0 ) {
		update_post_meta( $chapter_id, XNI_UNLOCK, $unlock );
	} else {
		delete_post_meta( $chapter_id, XNI_UNLOCK );
	}
}

/**
 * Товар WooCommerce под платную главу — если магазин вообще включён.
 *
 * @return int Идентификатор товара или 0.
 */
function xni_ensure_product( $chapter_id, $price ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return 0;
	}

	$existing = (int) get_post_meta( $chapter_id, '_xin_product', true );

	if ( $existing && 'product' === get_post_type( $existing ) ) {
		update_post_meta( $existing, '_price', $price );
		update_post_meta( $existing, '_regular_price', $price );
		return $existing;
	}

	$product_id = wp_insert_post( array(
		'post_type'   => 'product',
		'post_status' => 'publish',
		'post_title'  => get_the_title( $chapter_id ),
	) );

	if ( ! $product_id || is_wp_error( $product_id ) ) {
		return 0;
	}

	update_post_meta( $product_id, '_price', $price );
	update_post_meta( $product_id, '_regular_price', $price );
	update_post_meta( $product_id, '_virtual', 'yes' );
	update_post_meta( $product_id, '_downloadable', 'no' );
	wp_set_object_terms( $product_id, 'simple', 'product_type' );

	return $product_id;
}

/**
 * Пересобирает нумерацию проекта по возрастанию, не трогая содержимое.
 */
function xni_resort( $novel_id ) {
	if ( ! $novel_id ) {
		return;
	}

	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_xin_number', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) absint( $novel_id ) ),
		),
	) );

	$order = 1;
	foreach ( $ids as $id ) {
		wp_update_post( array( 'ID' => $id, 'menu_order' => $order ) );
		$order++;
	}
}
