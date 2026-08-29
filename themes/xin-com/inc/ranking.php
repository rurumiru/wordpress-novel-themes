<?php
/**
 * The ranking: which titles come out on top, and why.
 *
 * Sorting straight by average score puts a title with one five-star vote above
 * one with four hundred votes averaging 4.8, which is not a ranking anybody
 * trusts. The rating board therefore uses a weighted score: a title is pulled
 * towards the site-wide average until it has collected enough votes to speak
 * for itself. Views and chapter counts are plain numbers and are used as they
 * are.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Votes a title needs before its own average outweighs the site average.
 */
function xin_ranking_weight() {
	return (int) apply_filters( 'xin_ranking_weight', 5 );
}

/**
 * The boards on offer: query key => visible name.
 */
function xin_ranking_metrics() {
	return array(
		'rating'   => __( 'По оценке', 'xin-com' ),
		'views'    => __( 'По просмотрам', 'xin-com' ),
		'chapters' => __( 'По числу глав', 'xin-com' ),
	);
}

/**
 * Time windows. A title qualifies when it was updated inside the window, which
 * is the only honest reading: the theme keeps a running view counter and a
 * running average, not a history of either.
 */
function xin_ranking_periods() {
	return array(
		'all'   => array( __( 'За всё время', 'xin-com' ), 0 ),
		'month' => array( __( 'За месяц', 'xin-com' ), 30 ),
		'week'  => array( __( 'За неделю', 'xin-com' ), 7 ),
	);
}

/**
 * Reads the board settings out of the request.
 */
function xin_ranking_state() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only.
	$metric = isset( $_GET['metric'] ) ? sanitize_key( wp_unslash( $_GET['metric'] ) ) : 'rating';
	$period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'all';
	$genre  = isset( $_GET['genre'] ) ? sanitize_title( wp_unslash( $_GET['genre'] ) ) : '';
	// phpcs:enable

	$metrics = xin_ranking_metrics();
	$periods = xin_ranking_periods();

	return array(
		'metric' => isset( $metrics[ $metric ] ) ? $metric : 'rating',
		'period' => isset( $periods[ $period ] ) ? $period : 'all',
		'genre'  => $genre,
	);
}

/**
 * A link to the board with one setting changed.
 *
 * @param array $overrides metric | period | genre.
 */
function xin_ranking_url( $overrides = array() ) {
	$state = array_merge( xin_ranking_state(), $overrides );
	$base  = xin_page_url( 'ranking' );
	$args  = array();

	if ( 'rating' !== $state['metric'] ) {
		$args['metric'] = $state['metric'];
	}
	if ( 'all' !== $state['period'] ) {
		$args['period'] = $state['period'];
	}
	if ( $state['genre'] ) {
		$args['genre'] = $state['genre'];
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Where "Рейтинг" points. The board has a page of its own; a site that predates
 * it — or one where the page was deleted — falls back to the sorted catalog so
 * the link is never dead.
 */
function xin_ranking_link() {
	$page = get_page_by_path( 'ranking' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return add_query_arg( 'sort', 'rating', get_post_type_archive_link( 'novel' ) );
}

/**
 * The ids in the running for a board, before they are scored.
 *
 * @param string $period Period key.
 * @param string $genre  Genre slug, or an empty string for every genre.
 */
function xin_ranking_candidates( $period, $genre ) {
	$periods = xin_ranking_periods();
	$days    = isset( $periods[ $period ] ) ? (int) $periods[ $period ][1] : 0;

	$args = array(
		'post_type'      => 'novel',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);

	if ( $days ) {
		$args['date_query'] = array(
			array(
				'column' => 'post_modified_gmt',
				'after'  => $days . ' days ago',
			),
		);
	}

	if ( $genre ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array( 'taxonomy' => 'genre', 'field' => 'slug', 'terms' => $genre ),
		);
	}

	$query = new WP_Query( $args );
	return array_map( 'absint', $query->posts );
}

/**
 * Chapter counts for many novels in one query, so a board of fifty rows does
 * not cost fifty round trips.
 *
 * @param array $novel_ids Novel ids.
 * @return array id => count.
 */
function xin_ranking_chapter_counts( $novel_ids ) {
	global $wpdb;

	$novel_ids = array_filter( array_map( 'absint', (array) $novel_ids ) );
	if ( ! $novel_ids ) {
		return array();
	}

	$counts = array_fill_keys( $novel_ids, 0 );
	$in     = implode( ',', $novel_ids );

	// $in is built from absint output and holds nothing but integers.
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		"SELECT pm.meta_value AS novel_id, COUNT(*) AS total
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_xin_novel'
		   AND pm.meta_value IN ({$in})
		   AND p.post_type = 'chapter'
		   AND p.post_status = 'publish'
		 GROUP BY pm.meta_value"
	);

	foreach ( (array) $rows as $row ) {
		$counts[ (int) $row->novel_id ] = (int) $row->total;
	}

	return $counts;
}

/**
 * Scores and orders a board.
 *
 * @param string $metric Metric key.
 * @param string $period Period key.
 * @param string $genre  Genre slug.
 * @param int    $limit  How many rows to return.
 * @return array List of array( id, score, display, votes ), best first.
 */
function xin_ranking_board( $metric, $period, $genre = '', $limit = 50 ) {
	$key   = 'xin_rank_' . md5( $metric . '|' . $period . '|' . $genre . '|' . $limit );
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$ids   = xin_ranking_candidates( $period, $genre );
	$board = array();

	if ( ! $ids ) {
		set_transient( $key, $board, 10 * MINUTE_IN_SECONDS );
		return $board;
	}

	if ( 'chapters' === $metric ) {
		$counts = xin_ranking_chapter_counts( $ids );
		foreach ( $ids as $id ) {
			$n = isset( $counts[ $id ] ) ? $counts[ $id ] : 0;
			if ( ! $n ) {
				continue;
			}
			$board[] = array( 'id' => $id, 'score' => (float) $n, 'display' => number_format_i18n( $n ), 'votes' => 0 );
		}
	} elseif ( 'views' === $metric ) {
		foreach ( $ids as $id ) {
			$n = (int) get_post_meta( $id, '_xin_views', true );
			if ( ! $n ) {
				continue;
			}
			$board[] = array( 'id' => $id, 'score' => (float) $n, 'display' => xin_num( $n ), 'votes' => 0 );
		}
	} else {
		// Weighted rating. C is the mean score across everything that has been
		// rated at all; m is how many votes it takes to pull free of it.
		$m     = xin_ranking_weight();
		$sum   = 0.0;
		$rated = 0;
		$raw   = array();

		foreach ( $ids as $id ) {
			$votes = (int) get_post_meta( $id, '_xin_rating_count', true );
			if ( $votes < 1 ) {
				continue;
			}
			$value = (float) get_post_meta( $id, '_xin_rating', true );
			$raw[] = array( 'id' => $id, 'value' => $value, 'votes' => $votes );
			$sum  += $value;
			$rated++;
		}

		if ( ! $rated ) {
			set_transient( $key, $board, 10 * MINUTE_IN_SECONDS );
			return $board;
		}

		$c = $sum / $rated;

		foreach ( $raw as $item ) {
			$weighted = ( $item['votes'] / ( $item['votes'] + $m ) ) * $item['value']
				+ ( $m / ( $item['votes'] + $m ) ) * $c;

			$board[] = array(
				'id'      => $item['id'],
				'score'   => $weighted,
				'display' => number_format_i18n( round( $item['value'], 1 ), 1 ),
				'votes'   => $item['votes'],
			);
		}
	}

	usort( $board, static function ( $a, $b ) {
		if ( $a['score'] === $b['score'] ) {
			return 0;
		}
		return ( $a['score'] < $b['score'] ) ? 1 : -1;
	} );

	$board = array_slice( $board, 0, $limit );

	set_transient( $key, $board, 10 * MINUTE_IN_SECONDS );
	return $board;
}

/**
 * A rating, a view count or a chapter tally changing makes the boards stale.
 */
function xin_ranking_forget() {
	global $wpdb;

	// Transient names are hashed, so there is no way to target them one by one.
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '_transient_xin_rank_%'
		    OR option_name LIKE '_transient_timeout_xin_rank_%'"
	);
}
add_action( 'xin_rating_saved', 'xin_ranking_forget' );
add_action( 'save_post_novel', 'xin_ranking_forget' );
add_action( 'save_post_chapter', 'xin_ranking_forget' );
