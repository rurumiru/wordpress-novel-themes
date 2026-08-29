<?php
/**
 * Очередь публикации и снятие замков по времени.
 *
 * У проекта есть расписание: дни недели и время выхода. Импортированные главы
 * ложатся в очередь черновиками, каждой достаётся свой слот, и раз в минуту тик
 * публикует те, чьё время подошло. Отдельно тот же тик снимает ранний доступ с
 * глав, у которых наступила дата разблокировки.
 *
 * Минуту даёт не WP-Cron сам по себе — он просыпается только когда на сайт
 * кто-то зашёл. Расписание на минуту здесь регистрируется, а будит его системный
 * cron, дёргающий wp-cron.php: именно так это и задумано.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XNI_SLOT     = '_xni_slot';
const XNI_ON_FREE  = '_xni_release_free';
const XNI_SCHED    = '_xni_schedule';
const XNI_PRICE    = '_xin_price';
const XNI_UNLOCK   = '_xin_unlock_at';

/**
 * Расписание проекта.
 *
 * @param int $novel_id Проект.
 * @return array days (1–7, понедельник первый), times (H:i), enabled, free.
 */
function xni_schedule( $novel_id ) {
	$saved = get_post_meta( $novel_id, XNI_SCHED, true );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), array(
		'enabled' => false,
		'days'    => array( 1, 4 ),
		'times'   => array( '18:00' ),
		'free'    => true,
		'price'   => 0,
		'unlock'  => 0,
	) );
}

function xni_save_schedule( $novel_id, $data ) {
	$days = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $data['days'] ?? array() ) ), static function ( $d ) {
		return $d >= 1 && $d <= 7;
	} ) ) );
	sort( $days );

	$times = array();
	foreach ( (array) ( $data['times'] ?? array() ) as $time ) {
		$time = trim( (string) $time );
		if ( preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $time ) ) {
			$times[] = $time;
		}
	}
	$times = array_values( array_unique( $times ) );
	sort( $times );

	update_post_meta( $novel_id, XNI_SCHED, array(
		'enabled' => ! empty( $data['enabled'] ),
		'days'    => $days ? $days : array( 1 ),
		'times'   => $times ? $times : array( '18:00' ),
		'free'    => ! empty( $data['free'] ),
		'price'   => max( 0, (float) ( $data['price'] ?? 0 ) ),
		'unlock'  => max( 0, absint( $data['unlock'] ?? 0 ) ),
	) );
}

/**
 * Слот, начиная с которого можно ставить следующую главу.
 *
 * Считаем от последнего занятого слота проекта, а если очередь пуста — от
 * текущего момента, чтобы новая пачка не встала в прошлое.
 *
 * @param int $novel_id Проект.
 * @return int Метка времени UTC, от которой ищем свободный слот.
 */
function xni_queue_tail( $novel_id ) {
	$last = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'draft', 'future', 'pending' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => XNI_SLOT, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) absint( $novel_id ) ),
			array( 'key' => XNI_SLOT, 'compare' => 'EXISTS' ),
		),
	) );

	$tail = $last ? (int) get_post_meta( $last[0], XNI_SLOT, true ) : 0;

	return max( $tail, time() );
}

/**
 * Первый слот расписания строго позже указанного момента.
 *
 * @param array $schedule Расписание проекта.
 * @param int   $after    Метка времени UTC.
 * @return int Метка времени UTC следующего слота.
 */
function xni_next_slot( $schedule, $after ) {
	$offset = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	$local  = $after + $offset;

	// Ищем на две недели вперёд: этого хватает при любом наборе дней.
	for ( $day = 0; $day <= 14; $day++ ) {
		$stamp = $local + $day * DAY_IN_SECONDS;
		$dow   = (int) gmdate( 'N', $stamp );

		if ( ! in_array( $dow, $schedule['days'], true ) ) {
			continue;
		}

		foreach ( $schedule['times'] as $time ) {
			list( $h, $m ) = array_map( 'absint', explode( ':', $time ) );
			$slot_local = strtotime( gmdate( 'Y-m-d', $stamp ) . ' ' . sprintf( '%02d:%02d:00', $h, $m ) . ' UTC' );

			if ( $slot_local > $local ) {
				return $slot_local - $offset;
			}
		}
	}

	// Расписание пустое или бессмысленное — не роняем импорт, ставим через сутки.
	return $after + DAY_IN_SECONDS;
}

/**
 * Ставит главу в очередь: черновик со слотом.
 *
 * @param int   $chapter_id Глава.
 * @param int   $slot       Метка времени UTC.
 * @param bool  $free       Выйдет бесплатной или под ранним доступом.
 * @param float $price      Цена, если платная.
 */
function xni_enqueue( $chapter_id, $slot, $free, $price = 0 ) {
	wp_update_post( array( 'ID' => $chapter_id, 'post_status' => 'draft' ) );
	update_post_meta( $chapter_id, XNI_SLOT, (int) $slot );
	update_post_meta( $chapter_id, XNI_ON_FREE, $free ? 1 : 0 );

	if ( ! $free && $price > 0 ) {
		update_post_meta( $chapter_id, XNI_PRICE, (float) $price );
	}
}

/**
 * Ближайшая публикация проекта — то, что показывает таймер на странице тайтла.
 *
 * @param int $novel_id Проект.
 * @return array|null slot, chapter_id, title.
 */
function xni_next_release( $novel_id ) {
	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'draft', 'future', 'pending' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => XNI_SLOT, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) absint( $novel_id ) ),
			array( 'key' => XNI_SLOT, 'value' => time(), 'compare' => '>', 'type' => 'NUMERIC' ),
		),
	) );

	if ( ! $ids ) {
		return null;
	}

	return array(
		'slot'       => (int) get_post_meta( $ids[0], XNI_SLOT, true ),
		'chapter_id' => (int) $ids[0],
		'title'      => get_the_title( $ids[0] ),
	);
}

/* -------------------------------------------------------------------------
 * Тик
 * ---------------------------------------------------------------------- */

function xni_cron_schedules( $schedules ) {
	$schedules['xni_minute'] = array(
		'interval' => MINUTE_IN_SECONDS,
		'display'  => __( 'Каждую минуту (XIN-Com)', 'xi-novel-import' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'xni_cron_schedules' ); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected

function xni_schedule_tick() {
	if ( ! wp_next_scheduled( 'xni_tick' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'xni_minute', 'xni_tick' );
	}
}
add_action( 'init', 'xni_schedule_tick' );

/**
 * Публикует всё, чей слот наступил, и снимает замки, у которых вышел срок.
 *
 * Обе очереди ограничены сверху: тик выполняется раз в минуту и не должен
 * превращаться в долгий запрос, даже если накопилась сотня просроченных глав.
 *
 * @return array Сколько опубликовано и сколько разблокировано.
 */
function xni_tick() {
	$now       = time();
	$published = 0;
	$unlocked  = 0;

	$due = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'draft', 'pending', 'future' ),
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'meta_key'       => XNI_SLOT, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => XNI_SLOT, 'value' => $now, 'compare' => '<=', 'type' => 'NUMERIC' ),
		),
	) );

	foreach ( $due as $id ) {
		$free = (bool) get_post_meta( $id, XNI_ON_FREE, true );

		wp_update_post( array(
			'ID'            => $id,
			'post_status'   => 'publish',
			'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $now ) ),
			'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $now ),
		) );

		if ( $free ) {
			delete_post_meta( $id, '_xin_locked' );
		} else {
			update_post_meta( $id, '_xin_locked', 1 );
		}

		delete_post_meta( $id, XNI_SLOT );
		$published++;
	}

	$locked = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_locked', 'value' => '1' ),
			array( 'key' => XNI_UNLOCK, 'value' => $now, 'compare' => '<=', 'type' => 'NUMERIC' ),
		),
	) );

	foreach ( $locked as $id ) {
		delete_post_meta( $id, '_xin_locked' );
		delete_post_meta( $id, XNI_UNLOCK );

		// Глава становится бесплатной сейчас — в ленте обновлений она должна
		// встать сегодняшним числом, а не тем, когда её выложили под замок.
		wp_update_post( array(
			'ID'            => $id,
			'post_date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $now ) ),
			'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $now ),
		) );

		$unlocked++;
	}

	if ( $published || $unlocked ) {
		delete_transient( 'xin_site_stats' );
		if ( function_exists( 'xin_purge_caches' ) ) {
			xin_purge_caches();
		}
		if ( function_exists( 'xin_ranking_forget' ) ) {
			xin_ranking_forget();
		}
	}

	return array( 'published' => $published, 'unlocked' => $unlocked );
}
add_action( 'xni_tick', 'xni_tick' );

function xni_deactivate_cron() {
	$next = wp_next_scheduled( 'xni_tick' );
	if ( $next ) {
		wp_unschedule_event( $next, 'xni_tick' );
	}
}
