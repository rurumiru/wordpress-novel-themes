<?php
/**
 * Уголок читателя: витрина того, что уже происходит на площадке.
 *
 * Ничего нового про людей здесь не собирается. Всё считается из того, что тема
 * и так хранит: комментарии и отметки под ними, просмотры глав, серии чтения.
 * Единственное добавление — короткий журнал последних прочтений, и тот живёт
 * ограниченным кольцом и только для вошедших.
 *
 * Каждая выборка кэшируется: страница собирается из десятка запросов, а
 * открывают её часто.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_HUB_TTL      = 300;
const XIN_HUB_ACTIVITY = 'xin_hub_activity';
const XIN_HUB_LOG_MAX  = 40;

/**
 * Значение из кэша либо посчитанное заново.
 *
 * @param string   $key   Ключ.
 * @param callable $build Что считать при промахе.
 * @return mixed
 */
function xin_hub_cached( $key, $build ) {
	$name   = 'xin_hub_' . $key;
	$cached = get_transient( $name );

	if ( false !== $cached ) {
		return $cached;
	}

	$value = call_user_func( $build );
	set_transient( $name, $value, XIN_HUB_TTL );

	return $value;
}

/**
 * Сбрасывает витрину: её цифры меняются от каждого комментария и отметки.
 */
function xin_hub_forget() {
	global $wpdb;

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '_transient_xin_hub_%'
		    OR option_name LIKE '_transient_timeout_xin_hub_%'"
	);
}
add_action( 'wp_insert_comment', 'xin_hub_forget' );
add_action( 'transition_comment_status', 'xin_hub_forget' );
add_action( 'save_post_novel', 'xin_hub_forget' );

/* -------------------------------------------------------------------------
 * Уровень читателя
 * ---------------------------------------------------------------------- */

/**
 * Уровень — это не отдельная сущность, а короткая запись того, сколько человек
 * прочитал и сколько написал. Растёт всё медленнее, чтобы сотый уровень не
 * брался за неделю.
 *
 * @param int $user_id Читатель.
 * @return int
 */
function xin_hub_level( $user_id ) {
	$read     = (int) get_user_meta( $user_id, XIN_READ_COUNT, true );
	$comments = (int) get_comments( array( 'user_id' => $user_id, 'count' => true, 'status' => 'approve' ) );
	$points   = $read + $comments * 2;

	if ( $points < 1 ) {
		return 1;
	}

	return (int) min( 100, floor( sqrt( $points ) ) + 1 );
}

/* -------------------------------------------------------------------------
 * Таблица лидеров
 * ---------------------------------------------------------------------- */

/**
 * Кто больше пишет и кого больше отмечают.
 *
 * @param string $by    comments | reactions.
 * @param int    $limit Сколько строк.
 * @return array Список: user_id, name, avatar, level, value.
 */
function xin_hub_leaderboard( $by = 'comments', $limit = 7 ) {
	$by = 'reactions' === $by ? 'reactions' : 'comments';

	return xin_hub_cached( 'board_' . $by . '_' . $limit, static function () use ( $by, $limit ) {
		global $wpdb;

		if ( 'reactions' === $by ) {
			// Сумма отметок под всеми комментариями человека.
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT c.user_id, SUM( CAST( m.meta_value AS UNSIGNED ) ) AS total
				 FROM {$wpdb->comments} c
				 INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = %s
				 WHERE c.user_id > 0 AND c.comment_approved = '1'
				 GROUP BY c.user_id
				 HAVING total > 0
				 ORDER BY total DESC
				 LIMIT %d",
				XIN_TALK_LIKES,
				$limit
			) );
		} else {
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT user_id, COUNT(*) AS total
				 FROM {$wpdb->comments}
				 WHERE user_id > 0 AND comment_approved = '1'
				 GROUP BY user_id
				 ORDER BY total DESC
				 LIMIT %d",
				$limit
			) );
		}

		$out = array();

		foreach ( (array) $rows as $row ) {
			$user = get_userdata( (int) $row->user_id );
			if ( ! $user ) {
				continue;
			}
			$out[] = array(
				'user_id'   => (int) $user->ID,
				'name'      => $user->display_name,
				'url'       => get_author_posts_url( $user->ID ),
				'level'     => xin_hub_level( $user->ID ),
				'value'     => (int) $row->total,
				// Обе метрики сразу: в строке видно и сколько человек написал, и
				// сколько собрал, а не только то, по чему сейчас сортируем.
				'comments'  => (int) get_comments( array( 'user_id' => $user->ID, 'count' => true, 'status' => 'approve' ) ),
				'reactions' => xin_hub_user_reactions( $user->ID ),
				'read'      => (int) get_user_meta( $user->ID, XIN_READ_COUNT, true ),
			);
		}

		return $out;
	} );
}

/* -------------------------------------------------------------------------
 * Главы в обсуждении
 * ---------------------------------------------------------------------- */

/**
 * Главы, вокруг которых сейчас больше всего разговора.
 *
 * @param int $limit Сколько.
 * @param int $days  За сколько последних дней считать комментарии.
 * @return array
 */
function xin_hub_trending( $limit = 10, $days = 14 ) {
	return xin_hub_cached( 'trending_' . $limit . '_' . $days, static function () use ( $limit, $days ) {
		global $wpdb;

		$since = gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS );

		$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT c.comment_post_ID AS post_id, COUNT(*) AS talk
			 FROM {$wpdb->comments} c
			 INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
			 WHERE c.comment_approved = '1' AND c.comment_date_gmt >= %s
			   AND p.post_type = 'chapter' AND p.post_status = 'publish'
			 GROUP BY c.comment_post_ID
			 ORDER BY talk DESC
			 LIMIT %d",
			$since,
			$limit
		) );

		$out = array();

		foreach ( (array) $rows as $row ) {
			$id       = (int) $row->post_id;
			$novel_id = xin_chapter_novel_id( $id );

			$out[] = array(
				'chapter_id' => $id,
				'title'      => get_the_title( $id ),
				'label'      => xin_chapter_label( $id ),
				'url'        => get_permalink( $id ),
				'novel'      => $novel_id ? get_the_title( $novel_id ) : '',
				'cover'      => $novel_id ? xin_cover_url( $novel_id, 'xin-cover-sm' ) : '',
				'views'      => (int) xin_get_views( $id ),
				'comments'   => (int) $row->talk,
				'reactions'  => xin_hub_post_reactions( $id ),
			);
		}

		return $out;
	} );
}

/**
 * Сумма отметок под всеми комментариями записи.
 */
function xin_hub_post_reactions( $post_id ) {
	global $wpdb;

	return (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT COALESCE( SUM( CAST( m.meta_value AS UNSIGNED ) ), 0 )
		 FROM {$wpdb->comments} c
		 INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = %s
		 WHERE c.comment_post_ID = %d AND c.comment_approved = '1'",
		XIN_TALK_LIKES,
		$post_id
	) );
}

/* -------------------------------------------------------------------------
 * Лучшие комментарии
 * ---------------------------------------------------------------------- */

/**
 * @param string $by    reacted | replied.
 * @param int    $limit Сколько.
 */
function xin_hub_top_comments( $by = 'reacted', $limit = 6 ) {
	$by = 'replied' === $by ? 'replied' : 'reacted';

	return xin_hub_cached( 'talk_' . $by . '_' . $limit, static function () use ( $by, $limit ) {
		global $wpdb;

		if ( 'replied' === $by ) {
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT c.comment_ID AS id, COUNT( r.comment_ID ) AS total
				 FROM {$wpdb->comments} c
				 INNER JOIN {$wpdb->comments} r ON r.comment_parent = c.comment_ID AND r.comment_approved = '1'
				 WHERE c.comment_approved = '1'
				 GROUP BY c.comment_ID
				 ORDER BY total DESC
				 LIMIT %d",
				$limit
			) );
		} else {
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT c.comment_ID AS id, CAST( m.meta_value AS UNSIGNED ) AS total
				 FROM {$wpdb->comments} c
				 INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = %s
				 WHERE c.comment_approved = '1' AND CAST( m.meta_value AS UNSIGNED ) > 0
				 ORDER BY total DESC
				 LIMIT %d",
				XIN_TALK_LIKES,
				$limit
			) );
		}

		$out = array();

		foreach ( (array) $rows as $row ) {
			$comment = get_comment( (int) $row->id );
			if ( ! $comment ) {
				continue;
			}

			$post_id  = (int) $comment->comment_post_ID;
			$novel_id = 'chapter' === get_post_type( $post_id ) ? xin_chapter_novel_id( $post_id ) : 0;

			$out[] = array(
				'id'      => (int) $comment->comment_ID,
				'author'  => $comment->comment_author,
				'user_id' => (int) $comment->user_id,
				'date'    => (int) strtotime( $comment->comment_date_gmt . ' UTC' ),
				'text'    => wp_trim_words( wp_strip_all_tags( $comment->comment_content ), 44 ),
				'where'   => trim( ( $novel_id ? get_the_title( $novel_id ) . ' — ' : '' ) . get_the_title( $post_id ) ),
				'url'     => get_comment_link( $comment ),
				'value'   => (int) $row->total,
			);
		}

		return $out;
	} );
}

/* -------------------------------------------------------------------------
 * Цифры площадки
 * ---------------------------------------------------------------------- */

function xin_hub_stats() {
	return xin_hub_cached( 'stats', static function () {
		global $wpdb;

		$month = gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS );

		return array(
			'readers'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'new_series' => (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'novel' AND post_status = 'publish' AND post_date_gmt >= %s",
				$month
			) ),
			'comments'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'novels'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'novel' AND post_status = 'publish'" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'chapters'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'chapter' AND post_status = 'publish'" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'reactions' => (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT COALESCE( SUM( CAST( meta_value AS UNSIGNED ) ), 0 ) FROM {$wpdb->commentmeta} WHERE meta_key = %s",
				XIN_TALK_LIKES
			) ),
		);
	} );
}

/**
 * Три достижения недели: тайтл, автор и человек.
 */
function xin_hub_highlights() {
	return xin_hub_cached( 'highlights', static function () {
		$out = array( 'series' => null, 'author' => null, 'contributor' => null );

		$top = xin_ranking_board( 'views', 'all', '', 1 );
		if ( $top ) {
			$out['series'] = array( 'title' => get_the_title( $top[0]['id'] ), 'url' => get_permalink( $top[0]['id'] ) );
		}

		$authors = get_users( array(
			'number'              => 1,
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'has_published_posts' => array( 'novel' ),
			'fields'              => array( 'ID', 'display_name' ),
		) );
		if ( $authors ) {
			$out['author'] = array( 'title' => $authors[0]->display_name, 'url' => get_author_posts_url( $authors[0]->ID ) );
		}

		$board = xin_hub_leaderboard( 'comments', 1 );
		if ( $board ) {
			$out['contributor'] = array( 'title' => $board[0]['name'], 'url' => $board[0]['url'] );
		}

		return $out;
	} );
}

/* -------------------------------------------------------------------------
 * Живая лента
 * ---------------------------------------------------------------------- */

/**
 * Записывает прочтение в кольцо последних событий.
 *
 * Кольцо короткое и живёт в одной опции: это витрина «сейчас на площадке», а не
 * история наблюдений. Гости сюда не попадают — их прочтения не отслеживаются.
 *
 * @param int $user_id    Читатель.
 * @param int $chapter_id Глава.
 */
function xin_hub_log_read( $user_id, $chapter_id ) {
	$log = get_option( XIN_HUB_ACTIVITY );
	$log = is_array( $log ) ? $log : array();

	array_unshift( $log, array(
		'user'    => (int) $user_id,
		'chapter' => (int) $chapter_id,
		'time'    => time(),
	) );

	update_option( XIN_HUB_ACTIVITY, array_slice( $log, 0, XIN_HUB_LOG_MAX ), false );
}

/**
 * Последние прочтения, уже развёрнутые в имена и названия.
 *
 * @param int $limit Сколько.
 */
function xin_hub_activity( $limit = 6 ) {
	$log = get_option( XIN_HUB_ACTIVITY );
	$log = is_array( $log ) ? $log : array();
	$out = array();

	foreach ( $log as $row ) {
		if ( count( $out ) >= $limit ) {
			break;
		}

		$user    = get_userdata( (int) $row['user'] );
		$chapter = get_post( (int) $row['chapter'] );

		if ( ! $user || ! $chapter || 'publish' !== $chapter->post_status ) {
			continue;
		}

		$novel_id = xin_chapter_novel_id( $chapter->ID );

		$out[] = array(
			'name'    => $user->display_name,
			'url'     => get_permalink( $chapter->ID ),
			'novel'   => $novel_id ? get_the_title( $novel_id ) : '',
			'label'   => xin_chapter_label( $chapter->ID ),
			'time'    => (int) $row['time'],
			'clock'   => wp_date( 'H:i:s', (int) $row['time'] ),
			'level'   => xin_hub_level( (int) $user->ID ),
		);
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * Личные цифры
 * ---------------------------------------------------------------------- */

function xin_hub_me( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return null;
	}

	$stats = xin_reading_stats( $user_id );

	return array(
		'level'     => xin_hub_level( $user_id ),
		'read'      => (int) $stats['read'],
		'streak'    => (int) $stats['streak'],
		'best'      => (int) $stats['best'],
		'comments'  => (int) get_comments( array( 'user_id' => $user_id, 'count' => true, 'status' => 'approve' ) ),
		'reactions' => xin_hub_user_reactions( $user_id ),
	);
}

function xin_hub_user_reactions( $user_id ) {
	global $wpdb;

	return (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT COALESCE( SUM( CAST( m.meta_value AS UNSIGNED ) ), 0 )
		 FROM {$wpdb->comments} c
		 INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = %s
		 WHERE c.user_id = %d AND c.comment_approved = '1'",
		XIN_TALK_LIKES,
		$user_id
	) );
}

/**
 * «5 секунд назад», «3 минуты назад» — короче, чем human_time_diff, и без «ago».
 */
function xin_hub_ago( $stamp ) {
	$diff = max( 0, time() - (int) $stamp );

	/*
	 * Интервал считается здесь, а не через human_time_diff(): ядро переводит его
	 * своими файлами, а язык на фронте переключает фильтр темы — и в
	 * португальском получалось «há 8 hours». Свои строки идут вместе с темой и
	 * следуют за ней всегда.
	 */
	if ( $diff < MINUTE_IN_SECONDS ) {
		/* translators: %s: number of seconds. */
		$span = sprintf( __( '%s сек.', 'xin-com' ), number_format_i18n( max( 1, $diff ) ) );
	} elseif ( $diff < HOUR_IN_SECONDS ) {
		/* translators: %s: number of minutes. */
		$span = sprintf( __( '%s мин.', 'xin-com' ), number_format_i18n( (int) floor( $diff / MINUTE_IN_SECONDS ) ) );
	} elseif ( $diff < DAY_IN_SECONDS ) {
		/* translators: %s: number of hours. */
		$span = sprintf( __( '%s ч.', 'xin-com' ), number_format_i18n( (int) floor( $diff / HOUR_IN_SECONDS ) ) );
	} else {
		/* translators: %s: number of days. */
		$span = sprintf( __( '%s дн.', 'xin-com' ), number_format_i18n( (int) floor( $diff / DAY_IN_SECONDS ) ) );
	}

	/* translators: %s: time span, already formatted. */
	return sprintf( __( '%s назад', 'xin-com' ), $span );
}

/**
 * Доля значения от лидера в процентах — общая мера для всех шкал уголка.
 *
 * Пять процентов снизу нужны, чтобы у последней строки шкала не исчезала
 * совсем: пустая полоса читается как «данных нет», а не как «мало».
 *
 * @param int $value Значение строки.
 * @param int $top   Значение лидера.
 * @return int
 */
function xin_hub_share( $value, $top ) {
	$top = (int) $top;

	if ( $top < 1 ) {
		return 0;
	}

	return (int) max( 5, min( 100, round( (int) $value / $top * 100 ) ) );
}
