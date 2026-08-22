<?php
/**
 * Turning the filter bar into a query, and back into links.
 *
 * @package XI_Novel_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads the filter bar out of the request. Everything here is a GET argument, so
 * a filtered view is a link somebody can bookmark or hand to a colleague.
 */
function xnm_filters() {
	$get = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filtering.

	return array(
		's'        => isset( $get['s'] ) ? sanitize_text_field( $get['s'] ) : '',
		'author'   => isset( $get['xnm_author'] ) ? absint( $get['xnm_author'] ) : 0,
		'genre'    => isset( $get['xnm_genre'] ) ? sanitize_title( $get['xnm_genre'] ) : '',
		'status'   => isset( $get['xnm_status'] ) ? sanitize_title( $get['xnm_status'] ) : '',
		'state'    => isset( $get['xnm_state'] ) ? sanitize_key( $get['xnm_state'] ) : 'any',
		'cover'    => isset( $get['xnm_cover'] ) ? sanitize_key( $get['xnm_cover'] ) : '',
		'adult'    => isset( $get['xnm_adult'] ) ? sanitize_key( $get['xnm_adult'] ) : '',
		'orderby'  => isset( $get['xnm_orderby'] ) ? sanitize_key( $get['xnm_orderby'] ) : 'date',
		'order'    => isset( $get['xnm_order'] ) && 'asc' === strtolower( $get['xnm_order'] ) ? 'ASC' : 'DESC',
		'paged'    => isset( $get['paged'] ) ? max( 1, absint( $get['paged'] ) ) : 1,
		'per_page' => isset( $get['xnm_per'] ) ? min( 200, max( 10, absint( $get['xnm_per'] ) ) ) : 40,
	);
}

/**
 * Builds WP_Query arguments from the filter bar.
 *
 * @param array $filters Output of xnm_filters().
 * @param bool  $ids_only Return every matching id instead of one page of posts.
 */
function xnm_query_args( $filters, $ids_only = false ) {
	$args = array(
		'post_type'      => 'novel',
		'post_status'    => 'any' === $filters['state']
			? array( 'publish', 'draft', 'pending', 'private', 'future' )
			: array( $filters['state'] ),
		'posts_per_page' => $ids_only ? -1 : $filters['per_page'],
		'paged'          => $ids_only ? 1 : $filters['paged'],
		'orderby'        => 'date',
		'order'          => $filters['order'],
		'no_found_rows'  => $ids_only,
	);

	if ( $ids_only ) {
		$args['fields'] = 'ids';
	}

	if ( $filters['s'] ) {
		$args['s'] = $filters['s'];
	}

	if ( $filters['author'] ) {
		$args['author'] = $filters['author'];
	}

	$tax = array();
	if ( $filters['genre'] ) {
		$tax[] = array( 'taxonomy' => 'genre', 'field' => 'slug', 'terms' => $filters['genre'] );
	}
	if ( $filters['status'] ) {
		$tax[] = array( 'taxonomy' => 'novel_status', 'field' => 'slug', 'terms' => $filters['status'] );
	}
	if ( $tax ) {
		$tax['relation'] = 'AND';
		$args['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	$meta = array();
	if ( 'yes' === $filters['adult'] ) {
		$meta[] = array( 'key' => '_xin_adult', 'value' => '1' );
	} elseif ( 'no' === $filters['adult'] ) {
		$meta[] = array( 'key' => '_xin_adult', 'compare' => 'NOT EXISTS' );
	}
	if ( 'no' === $filters['cover'] ) {
		$meta[] = array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' );
	} elseif ( 'yes' === $filters['cover'] ) {
		$meta[] = array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' );
	}
	if ( $meta ) {
		$meta['relation'] = 'AND';
		$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	switch ( $filters['orderby'] ) {
		case 'title':
			$args['orderby'] = 'title';
			break;
		case 'modified':
			$args['orderby'] = 'modified';
			break;
		case 'views':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_xin_views'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			break;
		case 'rating':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_xin_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			break;
	}

	return $args;
}

/**
 * Every id the current filter matches, not just the page on screen. This is what
 * "выбрать все найденные" acts on.
 */
function xnm_all_matching_ids( $filters ) {
	$query = new WP_Query( xnm_query_args( $filters, true ) );
	return array_map( 'absint', $query->posts );
}

/**
 * A URL back to this screen with the filter bar preserved.
 *
 * @param array $overrides Arguments to change or, when null, drop.
 */
function xnm_url( $overrides = array() ) {
	$filters = xnm_filters();
	$args    = array(
		'post_type'   => 'novel',
		'page'        => 'xi-novel-manager',
		's'           => $filters['s'],
		'xnm_author'  => $filters['author'] ?: '',
		'xnm_genre'   => $filters['genre'],
		'xnm_status'  => $filters['status'],
		'xnm_state'   => $filters['state'],
		'xnm_cover'   => $filters['cover'],
		'xnm_adult'   => $filters['adult'],
		'xnm_orderby' => $filters['orderby'],
		'xnm_order'   => strtolower( $filters['order'] ),
		'xnm_per'     => $filters['per_page'],
		'paged'       => $filters['paged'] > 1 ? $filters['paged'] : '',
	);

	$args = array_merge( $args, $overrides );
	$args = array_filter( $args, static function ( $value ) {
		return '' !== $value && null !== $value;
	} );

	return admin_url( 'edit.php?' . http_build_query( $args ) );
}

/**
 * The chapters that belong to a novel, whatever their post status. Used for the
 * count in the table and for the PLUS switch.
 *
 * @param int $novel_id Novel.
 */
function xnm_chapter_ids( $novel_id ) {
	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_xin_novel', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => (string) absint( $novel_id ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	) );
}

/**
 * Chapter counts for a whole page of novels in one query.
 *
 * Asking per row cost one query per novel, which on a page of forty is forty
 * round trips for a number in a narrow column.
 *
 * @param array $novel_ids Novel ids.
 * @return array novel id => number of chapters.
 */
function xnm_chapter_counts( $novel_ids ) {
	global $wpdb;

	$novel_ids = array_filter( array_map( 'absint', (array) $novel_ids ) );
	if ( ! $novel_ids ) {
		return array();
	}

	$counts = array_fill_keys( $novel_ids, 0 );
	$in     = implode( ',', $novel_ids );

	// $in holds nothing but integers produced by absint just above.
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		"SELECT pm.meta_value AS novel_id, COUNT(*) AS total
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_xin_novel'
		   AND pm.meta_value IN ({$in})
		   AND p.post_type = 'chapter'
		   AND p.post_status IN ('publish','draft','pending','private','future')
		 GROUP BY pm.meta_value"
	);

	foreach ( (array) $rows as $row ) {
		$counts[ (int) $row->novel_id ] = (int) $row->total;
	}

	return $counts;
}
