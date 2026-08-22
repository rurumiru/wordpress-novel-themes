<?php
/**
 * Серии чтения и достижения.
 *
 * Считается только то, что видно читателю: дни подряд, прочитанные главы
 * и опубликованное автором. Никаких профилей поведения.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_READ_DAY    = '_xin_last_read_day';
const XIN_READ_STREAK = '_xin_streak';
const XIN_READ_BEST   = '_xin_streak_best';
const XIN_READ_COUNT  = '_xin_read_count';

function xin_today() {
	return (int) floor( ( time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) / DAY_IN_SECONDS );
}

function xin_track_reading( $chapter_id ) {
	$user_id = get_current_user_id();
	if ( ! $user_id || ! $chapter_id ) {
		return;
	}

	$seen = 'xin_seen_' . $user_id . '_' . $chapter_id;
	if ( get_transient( $seen ) ) {
		return;
	}
	set_transient( $seen, 1, DAY_IN_SECONDS );

	update_user_meta( $user_id, XIN_READ_COUNT, (int) get_user_meta( $user_id, XIN_READ_COUNT, true ) + 1 );

	$today = xin_today();
	$last  = (int) get_user_meta( $user_id, XIN_READ_DAY, true );

	if ( $last === $today ) {
		return;
	}

	$streak = 1;
	if ( $last && $today - $last === 1 ) {
		$streak = (int) get_user_meta( $user_id, XIN_READ_STREAK, true ) + 1;
	}

	update_user_meta( $user_id, XIN_READ_DAY, $today );
	update_user_meta( $user_id, XIN_READ_STREAK, $streak );

	if ( $streak > (int) get_user_meta( $user_id, XIN_READ_BEST, true ) ) {
		update_user_meta( $user_id, XIN_READ_BEST, $streak );
	}
}

function xin_streak( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return 0;
	}

	$last   = (int) get_user_meta( $user_id, XIN_READ_DAY, true );
	$streak = (int) get_user_meta( $user_id, XIN_READ_STREAK, true );
	$today  = xin_today();

	if ( ! $last || $today - $last > 1 ) {
		return 0;
	}

	return $streak;
}

function xin_reading_stats( $user_id ) {
	return array(
		'read'   => (int) get_user_meta( $user_id, XIN_READ_COUNT, true ),
		'streak' => xin_streak( $user_id ),
		'best'   => (int) get_user_meta( $user_id, XIN_READ_BEST, true ),
	);
}

function xin_achievement_list() {
	return array(
		'first-chapter'  => array( 'read', 1, 'book-open', __( 'Первая глава', 'xi-novels' ), __( 'Прочитана первая глава на площадке', 'xi-novels' ) ),
		'ten-chapters'   => array( 'read', 10, 'book', __( 'Десять глав', 'xi-novels' ), __( 'Прочитано десять глав', 'xi-novels' ) ),
		'fifty-chapters' => array( 'read', 50, 'layers', __( 'Полсотни', 'xi-novels' ), __( 'Прочитано пятьдесят глав', 'xi-novels' ) ),
		'hundred'        => array( 'read', 100, 'trophy', __( 'Сотня', 'xi-novels' ), __( 'Прочитано сто глав', 'xi-novels' ) ),
		'streak-3'       => array( 'best', 3, 'flame', __( 'Три дня подряд', 'xi-novels' ), __( 'Чтение три дня подряд', 'xi-novels' ) ),
		'streak-7'       => array( 'best', 7, 'flame', __( 'Неделя подряд', 'xi-novels' ), __( 'Чтение семь дней подряд', 'xi-novels' ) ),
		'streak-30'      => array( 'best', 30, 'crown', __( 'Месяц подряд', 'xi-novels' ), __( 'Чтение тридцать дней подряд', 'xi-novels' ) ),
		'first-project'  => array( 'projects', 1, 'pen', __( 'Свой проект', 'xi-novels' ), __( 'Создан первый проект', 'xi-novels' ) ),
		'first-release'  => array( 'published', 1, 'sparkles', __( 'Первая публикация', 'xi-novels' ), __( 'Опубликована первая глава', 'xi-novels' ) ),
		'ten-releases'   => array( 'published', 10, 'award', __( 'Десять выпусков', 'xi-novels' ), __( 'Опубликовано десять глав', 'xi-novels' ) ),
	);
}

function xin_user_metrics( $user_id ) {
	$stats = xin_reading_stats( $user_id );

	$stats['projects']  = count( xin_user_projects( $user_id ) );
	$stats['published'] = (int) count_user_posts( $user_id, 'chapter', true );

	return $stats;
}

function xin_achievements( $user_id ) {
	$metrics = xin_user_metrics( $user_id );
	$out     = array();

	foreach ( xin_achievement_list() as $key => $data ) {
		list( $field, $need, $icon, $title, $note ) = $data;
		$have = isset( $metrics[ $field ] ) ? (int) $metrics[ $field ] : 0;

		$out[ $key ] = array(
			'icon'     => $icon,
			'title'    => $title,
			'note'     => $note,
			'need'     => $need,
			'have'     => min( $have, $need ),
			'unlocked' => $have >= $need,
		);
	}

	return $out;
}

/**
 * Number chapter paragraphs so the reader toolbar can bookmark / quote / TTS them.
 *
 * @param string $content Post content.
 * @return string
 */
function xin_add_chapter_paragraph_ids( $content ) {
	if ( ! is_singular( 'chapter' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( post_password_required() ) {
		return $content;
	}

	static $paragraph_id = 0;
	if ( did_action( 'the_post' ) === 1 ) {
		$paragraph_id = 0;
	}

	return preg_replace_callback(
		'/<p(?=\s|>)/i',
		static function () use ( &$paragraph_id ) {
			$id = $paragraph_id++;
			return '<p id="paragraph-' . $id . '" data-paragraph-id="' . $id . '" ';
		},
		$content
	);
}
add_filter( 'the_content', 'xin_add_chapter_paragraph_ids', 12 );

function xin_streak_note( $user_id ) {
	$streak = xin_streak( $user_id );

	if ( ! $streak ) {
		return __( 'Серия начнётся с первой главы за сегодня.', 'xi-novels' );
	}

	return sprintf(
		/* translators: %s: number of days */
		_n( 'Серия: %s день подряд', 'Серия: %s дней подряд', $streak, 'xi-novels' ),
		number_format_i18n( $streak )
	);
}
