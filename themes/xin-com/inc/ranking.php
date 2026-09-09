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
 * Условия отбора, общие для всех показателей доски.
 *
 * @param string $period Период.
 * @param string $genre  Слаг жанра или пустая строка.
 * @return array sql-куски: join и where.
 */
function xin_ranking_scope( $period, $genre ) {
	global $wpdb;

	$periods = xin_ranking_periods();
	$days    = isset( $periods[ $period ] ) ? (int) $periods[ $period ][1] : 0;

	$join  = '';
	$where = '';

	if ( $days ) {
		$where .= $wpdb->prepare(
			' AND p.post_modified_gmt > %s',
			gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS )
		);
	}

	if ( $genre ) {
		$term = get_term_by( 'slug', $genre, 'genre' );

		if ( $term && ! is_wp_error( $term ) ) {
			$join .= $wpdb->prepare(
				" INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
				  INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				       AND tt.taxonomy = 'genre' AND tt.term_id = %d",
				$term->term_id
			);
		} else {
			// Жанра нет — доска обязана выйти пустой, а не «как будто без фильтра».
			$where .= ' AND 1 = 0';
		}
	}

	return array( 'join' => $join, 'where' => $where );
}

/**
 * Среднее по всем оценённым тайтлам — та самая C во взвешенной оценке.
 *
 * @param array $scope Куски запроса из xin_ranking_scope().
 * @return float
 */
function xin_ranking_mean( $scope ) {
	global $wpdb;

	$sql = "SELECT AVG( CAST( val.meta_value AS DECIMAL(10,4) ) )
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} val ON val.post_id = p.ID AND val.meta_key = '_xin_rating'
		INNER JOIN {$wpdb->postmeta} cnt ON cnt.post_id = p.ID AND cnt.meta_key = '_xin_rating_count'
		{$scope['join']}
		WHERE p.post_type = 'novel' AND p.post_status = 'publish'
		  AND CAST( cnt.meta_value AS UNSIGNED ) > 0
		  {$scope['where']}";

	// Куски собраны здесь же через $wpdb->prepare(), пользовательских строк в них нет.
	return (float) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
}

/**
 * Строит доску.
 *
 * Прежде отбирались ID всех опубликованных тайтлов без предела, а очки
 * считались обходом этого списка в PHP с get_post_meta() на каждый шаг. На
 * каталоге в десятки тысяч тайтлов это десятки тысяч запросов на один показ
 * страницы — то место, где тема на большом сайте и падала по таймауту.
 * Теперь сортировка и предел выполняются базой, а в PHP приезжает ровно
 * столько строк, сколько показывает доска.
 *
 * @param string $metric Показатель.
 * @param string $period Период.
 * @param string $genre  Жанр.
 * @param int    $limit  Сколько строк вернуть.
 * @return array Список array( id, score, display, votes ), лучшие первыми.
 */
function xin_ranking_board( $metric, $period, $genre = '', $limit = 50 ) {
	global $wpdb;

	$limit  = max( 1, min( 200, (int) $limit ) );
	$key    = 'xin_rank_' . xin_ranking_version() . '_' . md5( $metric . '|' . $period . '|' . $genre . '|' . $limit );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$scope = xin_ranking_scope( $period, $genre );
	$board = array();

	if ( 'chapters' === $metric ) {
		$sql = "SELECT p.ID AS id, COUNT( ch.ID ) AS score
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} link ON link.meta_key = '_xin_novel' AND CAST( link.meta_value AS UNSIGNED ) = p.ID
			INNER JOIN {$wpdb->posts} ch ON ch.ID = link.post_id AND ch.post_type = 'chapter' AND ch.post_status = 'publish'
			{$scope['join']}
			WHERE p.post_type = 'novel' AND p.post_status = 'publish'
			  {$scope['where']}
			GROUP BY p.ID
			HAVING score > 0
			ORDER BY score DESC, p.post_date DESC
			LIMIT {$limit}";

		$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery

		foreach ( (array) $rows as $row ) {
			$n       = (int) $row->score;
			$board[] = array( 'id' => (int) $row->id, 'score' => (float) $n, 'display' => number_format_i18n( $n ), 'votes' => 0 );
		}
	} elseif ( 'views' === $metric ) {
		$sql = "SELECT p.ID AS id, CAST( v.meta_value AS UNSIGNED ) AS score
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} v ON v.post_id = p.ID AND v.meta_key = '_xin_views'
			{$scope['join']}
			WHERE p.post_type = 'novel' AND p.post_status = 'publish'
			  AND CAST( v.meta_value AS UNSIGNED ) > 0
			  {$scope['where']}
			ORDER BY score DESC, p.post_date DESC
			LIMIT {$limit}";

		$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery

		foreach ( (array) $rows as $row ) {
			$n       = (int) $row->score;
			$board[] = array( 'id' => (int) $row->id, 'score' => (float) $n, 'display' => xin_num( $n ), 'votes' => 0 );
		}
	} else {
		/*
		 * Взвешенная оценка: тайтл с тремя пятёрками не должен обгонять тайтл
		 * с четырьмя сотнями голосов и оценкой 4,8. Формула та же, что была в
		 * PHP, — просто считает её база.
		 */
		$m    = (float) xin_ranking_weight();
		$mean = xin_ranking_mean( $scope );

		if ( $mean > 0 ) {
			$sql = $wpdb->prepare(
				"SELECT p.ID AS id,
					CAST( val.meta_value AS DECIMAL(10,4) ) AS value,
					CAST( cnt.meta_value AS UNSIGNED ) AS votes,
					( CAST( cnt.meta_value AS UNSIGNED ) / ( CAST( cnt.meta_value AS UNSIGNED ) + %f ) ) * CAST( val.meta_value AS DECIMAL(10,4) )
					+ ( %f / ( CAST( cnt.meta_value AS UNSIGNED ) + %f ) ) * %f AS score
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} val ON val.post_id = p.ID AND val.meta_key = '_xin_rating'
				INNER JOIN {$wpdb->postmeta} cnt ON cnt.post_id = p.ID AND cnt.meta_key = '_xin_rating_count'
				{$scope['join']}
				WHERE p.post_type = 'novel' AND p.post_status = 'publish'
				  AND CAST( cnt.meta_value AS UNSIGNED ) > 0
				  {$scope['where']}
				ORDER BY score DESC, votes DESC
				LIMIT {$limit}",
				$m,
				$m,
				$m,
				$mean
			);

			$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery

			foreach ( (array) $rows as $row ) {
				$board[] = array(
					'id'      => (int) $row->id,
					'score'   => (float) $row->score,
					'display' => number_format_i18n( round( (float) $row->value, 1 ), 1 ),
					'votes'   => (int) $row->votes,
				);
			}
		}
	}

	/*
	 * Записи доски всё равно понадобятся шаблону: поднимаем их разом, иначе
	 * каждая строка сама сходит в базу за заголовком и обложкой.
	 */
	$ids = wp_list_pluck( $board, 'id' );
	if ( $ids ) {
		_prime_post_caches( $ids, false, true );
	}

	set_transient( $key, $board, 10 * MINUTE_IN_SECONDS );

	return $board;
}

/**
 * Версия досок. Входит в имя транзиента, поэтому сброс — это +1 к числу.
 *
 * @return int
 */
function xin_ranking_version() {
	return (int) get_option( 'xin_rank_version', 1 );
}

/**
 * Оценка, просмотры или новая глава делают доски устаревшими.
 *
 * Прежде здесь выполнялся `DELETE ... LIKE '_transient_xin_rank_%'` — обход
 * всей таблицы настроек. Он висел на сохранении каждой главы, поэтому импорт
 * на пять тысяч глав означал пять тысяч таких обходов. Теперь меняется одно
 * число, а прежние доски просто дотлевают по своему сроку в десять минут.
 * Статический флаг не даёт делать это дважды за запрос.
 *
 * @return void
 */
function xin_ranking_forget() {
	static $done = false;

	if ( $done ) {
		return;
	}

	$done = true;
	update_option( 'xin_rank_version', xin_ranking_version() + 1, false );
}
add_action( 'xin_rating_saved', 'xin_ranking_forget' );
add_action( 'save_post_novel', 'xin_ranking_forget' );
add_action( 'save_post_chapter', 'xin_ranking_forget' );
