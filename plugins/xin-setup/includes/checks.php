<?php
/**
 * Сводка: что настроено, а что нет.
 *
 * Экран должен отвечать на один вопрос — «всё ли готово». Поэтому проверки
 * собраны в одном месте и каждая умеет сказать три вещи: как сейчас, как
 * надо и зачем.
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Полная картина по сайту.
 */
function xs_survey() {
	$out = array(
		'settings' => array(),
		'pages'    => array(),
		'content'  => array(),
		'left'     => 0,
	);

	foreach ( xs_wanted_settings() as $key => $item ) {
		$ok = (bool) call_user_func( $item['ok'] );

		$out['settings'][ $key ] = array(
			'label' => $item['label'],
			'why'   => $item['why'],
			'now'   => (string) call_user_func( $item['now'] ),
			'ok'    => $ok,
		);

		if ( ! $ok ) {
			$out['left']++;
		}
	}

	foreach ( xs_wanted_pages() as $template => $page ) {
		$id = xs_page_for( $template );

		$out['pages'][ $template ] = array(
			'label' => $page['title'],
			'id'    => $id,
			'ok'    => (bool) $id,
			'url'   => $id ? get_permalink( $id ) : '',
		);

		if ( ! $id ) {
			$out['left']++;
		}
	}

	foreach ( xs_content_jobs() as $key => $job ) {
		$count = (int) call_user_func( $job['count'] );

		$out['content'][ $key ] = array(
			'label' => $job['label'],
			'why'   => $job['why'],
			'count' => $count,
			'ok'    => 0 === $count,
		);

		if ( $count ) {
			$out['left']++;
		}
	}

	return $out;
}

/**
 * Сколько всего в библиотеке — чтобы владелец понимал масштаб правок.
 */
function xs_library_size() {
	$novels   = wp_count_posts( 'novel' );
	$chapters = wp_count_posts( 'chapter' );

	return array(
		'novels'   => $novels ? (int) $novels->publish : 0,
		'chapters' => $chapters ? (int) $chapters->publish : 0,
	);
}
