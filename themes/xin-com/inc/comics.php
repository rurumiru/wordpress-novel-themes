<?php
/**
 * Данные для витрины и страницы комикса.
 *
 * Отдельно от `format.php`: там модель раздела — формат, адреса, шаблоны; здесь
 * выборки, которые нужны только двум страницам и больше нигде.
 *
 * @package XIN-Com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Расписание недели: по каким дням у тайтлов выходят главы.
 *
 * День берётся не из отдельного поля, а из того, что уже есть — дат последних
 * глав. Поле пришлось бы заполнять руками для каждого тайтла, оно устаревало бы
 * молча, и расписание расходилось бы с реальностью ровно в тот момент, когда на
 * него смотрят. Здесь расписание не может разойтись: это и есть выходы.
 *
 * @param int $depth Сколько последних глав раздела просмотреть.
 * @return array<int, int[]> Дни недели 1–7 и ID тайтлов.
 */
function xin_comics_schedule( $depth = 300 ) {
	$cached = get_transient( 'xin_comics_schedule' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => $depth,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => xin_format_meta_clause( 'comic' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	) );

	$days = array_fill_keys( range( 1, 7 ), array() );
	$seen = array();

	foreach ( $chapters as $chapter ) {
		$novel_id = xin_chapter_novel_id( $chapter->ID );

		if ( ! $novel_id || isset( $seen[ $novel_id ] ) ) {
			continue;
		}

		$seen[ $novel_id ] = true;
		$day               = (int) get_post_time( 'N', false, $chapter->ID );

		$days[ $day ][] = $novel_id;
	}

	set_transient( 'xin_comics_schedule', $days, 15 * MINUTE_IN_SECONDS );

	return $days;
}

/**
 * Свежие главы, сгруппированные по тайтлам.
 *
 * Лента раздела читается тайтлами, а не отдельными главами: у комикса за вечер
 * выходит три главы подряд, и плоский список превращается в один и тот же
 * постер, повторённый трижды.
 *
 * @param int $novels Сколько тайтлов вернуть.
 * @param int $each   Сколько последних глав показать у каждого.
 * @return array<int, array{novel: int, chapters: WP_Post[]}>
 */
function xin_comics_updates( $novels = 6, $each = 3 ) {
	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => max( 40, $novels * $each * 3 ),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => xin_format_meta_clause( 'comic' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	) );

	$groups = array();

	foreach ( $chapters as $chapter ) {
		$novel_id = xin_chapter_novel_id( $chapter->ID );

		if ( ! $novel_id ) {
			continue;
		}

		if ( ! isset( $groups[ $novel_id ] ) ) {
			if ( count( $groups ) >= $novels ) {
				continue;
			}

			$groups[ $novel_id ] = array(
				'novel'    => $novel_id,
				'chapters' => array(),
			);
		}

		if ( count( $groups[ $novel_id ]['chapters'] ) < $each ) {
			$groups[ $novel_id ]['chapters'][] = $chapter;
		}
	}

	return array_values( $groups );
}

/**
 * Похожие комиксы: те, что делят жанры с этим.
 *
 * @param int $novel_id Тайтл.
 * @param int $limit    Сколько вернуть.
 * @return int[]
 */
function xin_comics_related( $novel_id, $limit = 6 ) {
	$terms = get_the_terms( $novel_id, 'genre' );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$query = new WP_Query( array(
		'post_type'           => 'novel',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'post__not_in'        => array( $novel_id ),
		'orderby'             => 'rand',
		'meta_query'          => xin_format_meta_clause( 'comic' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'genre',
				'field'    => 'term_id',
				'terms'    => wp_list_pluck( $terms, 'term_id' ),
			),
		),
	) );

	return $query->posts;
}

/**
 * Жанры, у которых действительно есть комиксы.
 *
 * `get_terms()` считает записи всех типов и форматов сразу, поэтому в чипах
 * каталога оказывались жанры, где ни одного комикса нет, — и клик по такому
 * жанру приводил в пустоту.
 *
 * @return array<int, array{term: WP_Term, count: int}>
 */
function xin_comics_genres() {
	$cached = get_transient( 'xin_comics_genres' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$ids = get_posts( array(
		'post_type'      => 'novel',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => xin_format_meta_clause( 'comic' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	) );

	$counts = array();

	foreach ( $ids as $id ) {
		foreach ( (array) get_the_terms( $id, 'genre' ) as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			if ( ! isset( $counts[ $term->term_id ] ) ) {
				$counts[ $term->term_id ] = array(
					'term'  => $term,
					'count' => 0,
				);
			}

			++$counts[ $term->term_id ]['count'];
		}
	}

	uasort( $counts, static function ( $a, $b ) {
		return $b['count'] <=> $a['count'];
	} );

	$counts = array_values( $counts );

	set_transient( 'xin_comics_genres', $counts, 15 * MINUTE_IN_SECONDS );

	return $counts;
}

/**
 * Сбрасывает выборки раздела, когда что-то поменялось.
 *
 * @return void
 */
function xin_comics_flush_cache() {
	delete_transient( 'xin_comics_schedule' );
	delete_transient( 'xin_comics_genres' );
}
add_action( 'save_post_chapter', 'xin_comics_flush_cache' );
add_action( 'save_post_novel', 'xin_comics_flush_cache' );
