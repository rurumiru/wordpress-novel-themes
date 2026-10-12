<?php
/**
 * Чтение: закладки, избранное, прогресс и подбор похожего.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Избранное и прогресс — в мете пользователя
 * ---------------------------------------------------------------------- */

function xv_favourites( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	return array_map( 'intval', (array) get_user_meta( $user_id, '_xin_favourites', true ) );
}

function xv_is_favourite( $novel_id, $user_id = 0 ) {
	return in_array( (int) $novel_id, xv_favourites( $user_id ), true );
}

function xv_toggle_favourite( $novel_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	$list  = xv_favourites( $user_id );
	$novel = (int) $novel_id;
	$key   = array_search( $novel, $list, true );

	if ( false !== $key ) {
		unset( $list[ $key ] );
		$on = false;
	} else {
		$list[] = $novel;
		$on     = true;
	}

	update_user_meta( $user_id, '_xin_favourites', array_values( $list ) );

	return $on;
}

function xv_fav_button( $novel_id ) {
	$on = xv_is_favourite( $novel_id );

	ob_start();
	?>
	<button type="button"
		class="xv-btn block xv-fav<?php echo $on ? ' is-on' : ''; ?>"
		data-xv-fav="<?php echo (int) $novel_id; ?>"
		aria-pressed="<?php echo $on ? 'true' : 'false'; ?>">
		<?php xv_the_icon( 'heart', '', $on ); ?>
		<span data-xv-fav-label><?php echo esc_html( $on ? __( 'В избранном', 'xin-v2' ) : __( 'В избранное', 'xin-v2' ) ); ?></span>
	</button>
	<?php
	return trim( ob_get_clean() );
}

/**
 * На чём читатель остановился.
 */
function xv_progress( $novel_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return 0;
	}

	$all = (array) get_user_meta( $user_id, '_xin_progress', true );

	return isset( $all[ $novel_id ] ) ? (int) $all[ $novel_id ] : 0;
}

function xv_set_progress( $novel_id, $chapter_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	$all                 = (array) get_user_meta( $user_id, '_xin_progress', true );
	$all[ (int) $novel_id ] = (int) $chapter_id;

	update_user_meta( $user_id, '_xin_progress', $all );
}

/**
 * Кнопка чтения: «Продолжить» там, где читатель остановился, иначе
 * «Начать читать» с первой главы.
 */
function xv_resume_chapter( $novel_id, $chapters = null ) {
	$chapters = null === $chapters ? xv_novel_chapters( $novel_id ) : $chapters;

	if ( ! $chapters ) {
		return array( 'chapter' => null, 'label' => '' );
	}

	$saved = xv_progress( $novel_id );

	if ( $saved ) {
		foreach ( $chapters as $chapter ) {
			if ( (int) $chapter->ID === $saved ) {
				return array(
					'chapter' => $chapter,
					/* translators: %s: номер главы. */
					'label'   => sprintf( __( 'Продолжить — гл. %s', 'xin-v2' ), xv_chapter_number( $chapter->ID ) ),
				);
			}
		}
	}

	return array(
		'chapter' => $chapters[0],
		'label'   => __( 'Начать читать', 'xin-v2' ),
	);
}

/**
 * Соседние главы по порядку внутри тайтла.
 */
// Соседние главы считает inc/chapters.php по индексу.

/**
 * Похожее — по общим жанрам. Совпадений нет — показываем то, что рядом по
 * оценке: пустой блок хуже неточного.
 */
/**
 * Случайное окно вместо случайного порядка.
 *
 * ORDER BY RAND() заставляет базу отсортировать всю выборку ради шести
 * строк. Считаем, сколько подходит, и берём оттуда случайный кусок: один
 * дешёвый COUNT вместо сортировки библиотеки.
 */
function xv_random_offset( $args, $limit ) {
	$count = new WP_Query( array_merge( $args, array(
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) ) );

	$total = (int) $count->found_posts;

	return $total > $limit ? wp_rand( 0, $total - $limit ) : 0;
}
function xv_similar_novels( $novel_id, $limit = 6 ) {
	$terms = wp_get_post_terms( $novel_id, 'genre', array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'      => 'novel',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'post__not_in'   => array( (int) $novel_id ),
		// Не ORDER BY RAND(): на большой библиотеке это полный просмотр
		// таблицы на каждой странице тайтла. Берём случайное окно ниже.
		'orderby'        => 'date',
		'no_found_rows'  => true,
	);

	if ( $terms && ! is_wp_error( $terms ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'genre',
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		);
	}

	$args['offset'] = xv_random_offset( $args, (int) $limit );
	$posts          = get_posts( $args );

	if ( ! $posts ) {
		unset( $args['offset'] );
	}

	if ( ! $posts ) {
		unset( $args['tax_query'] );
		$args['meta_key'] = '_xin_rating';
		$args['orderby']  = 'meta_value_num';
		$posts            = get_posts( $args );
	}

	return $posts;
}

/* -------------------------------------------------------------------------
 * REST
 * ---------------------------------------------------------------------- */

function xv_register_rest() {
	register_rest_route( 'xin/v2', '/favourite', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'novel' => array(
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		),
		'callback'            => function ( WP_REST_Request $request ) {
			$novel = (int) $request->get_param( 'novel' );

			if ( 'novel' !== get_post_type( $novel ) ) {
				return new WP_Error( 'xv_not_novel', __( 'Это не тайтл.', 'xin-v2' ), array( 'status' => 400 ) );
			}

			return array( 'on' => xv_toggle_favourite( $novel ) );
		},
	) );

	register_rest_route( 'xin/v2', '/progress', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'chapter' => array(
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		),
		'callback'            => function ( WP_REST_Request $request ) {
			$chapter = (int) $request->get_param( 'chapter' );
			$novel   = (int) get_post_meta( $chapter, '_xin_novel', true );

			if ( ! $novel ) {
				return new WP_Error( 'xv_no_novel', __( 'У главы нет тайтла.', 'xin-v2' ), array( 'status' => 400 ) );
			}

			xv_set_progress( $novel, $chapter );

			return array( 'saved' => true );
		},
	) );

	register_rest_route( 'xin/v2', '/rate', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'novel' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'value' => array( 'required' => true, 'type'=>'integer', 'minimum'=>1, 'maximum'=>5 ),
		),
		'callback'            => 'xv_rest_rate',
	) );
}
add_action( 'rest_api_init', 'xv_register_rest' );

/**
 * Оценка тайтла. Храним оценку каждого читателя отдельно, а в мете тайтла —
 * пересчитанное среднее: иначе повторный голос накручивает рейтинг.
 */
function xv_rest_rate( WP_REST_Request $request ) {
	$novel = (int) $request->get_param( 'novel' );
	$raw_value = $request->get_param( 'value' );
	$value = (int) $raw_value;
	if(!is_numeric($raw_value) || (float)$raw_value !== (float)$value || $value<1 || $value>5) return new WP_Error('xv_invalid_rating', __('Оценка должна быть от 1 до 5.','xin-v2'), ['status'=>400]);

	if ( 'novel' !== get_post_type( $novel ) ) {
		return new WP_Error( 'xv_not_novel', __( 'Это не тайтл.', 'xin-v2' ), array( 'status' => 400 ) );
	}
	if('publish'!==get_post_status($novel) && !current_user_can('edit_post',$novel)) return new WP_Error('xv_not_public','This title is unavailable.',array('status'=>403));

	$stored_votes = get_post_meta( $novel, '_xin_votes', true );
	$votes = array();
	if(is_array($stored_votes))foreach($stored_votes as $user_id=>$score) {
		if((int)$user_id>0 && is_numeric($score) && (int)$score>=1 && (int)$score<=5)$votes[(int)$user_id]=(int)$score;
	}
	// Preserve imported aggregate ratings when individual historical votes are absent.
	$baseline=get_post_meta($novel,'_xin_rating_baseline',true);
	if(!is_array($baseline)) {
		$old_count=(int)get_post_meta($novel,'_xin_rating_count',true);
		$old_value=(float)get_post_meta($novel,'_xin_rating',true);
		$baseline=['count'=>max(0,$old_count-count($votes)),'sum'=>max(0,$old_value*$old_count-array_sum($votes))];
		update_post_meta($novel,'_xin_rating_baseline',$baseline);
	}
	$votes[ get_current_user_id() ] = $value;

	update_post_meta( $novel, '_xin_votes', $votes );
	$count=(int)$baseline['count']+count($votes);
	update_post_meta( $novel, '_xin_rating', round( ((float)$baseline['sum']+array_sum( $votes )) / $count, 2 ) );
	update_post_meta( $novel, '_xin_rating_count', $count );
	if(function_exists('xv_forget_lists')) xv_forget_lists($novel);

	return array(
		'value' => (float) get_post_meta( $novel, '_xin_rating', true ),
		'count' => $count,
		'label' => xv_counted($count,xv_label_ratings($count)),
		'stars' => xv_stars((float)get_post_meta($novel,'_xin_rating',true)),
	);
}

/* -------------------------------------------------------------------------
 * Поддержание счётчиков тайтла
 * ---------------------------------------------------------------------- */

/**
 * Число глав и время последнего обновления пересчитываем при сохранении
 * главы — иначе каталог сортируется по устаревшим данным.
 */
function xv_sync_novel_counters( $post_id, $post ) {
	if ( 'chapter' !== $post->post_type || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$novel = (int) get_post_meta( $post_id, '_xin_novel', true );

	if ( ! $novel ) {
		return;
	}

	// Число глав берём из уже пересобранного индекса, а не отдельным
	// запросом на весь тайтл.
	update_post_meta( $novel, '_xin_chapter_count', count( xv_chapter_index( $novel, false ) ) );

	if ( 'publish' === $post->post_status ) {
		update_post_meta( $novel, '_xin_last_chapter_at', get_post_timestamp( $post_id ) );
	}

	delete_transient( 'xv_site_stats' );
}
add_action( 'save_post', 'xv_sync_novel_counters', 20, 2 );
add_action( 'deleted_post', function ( $post_id, $post ) {
	if ( $post instanceof WP_Post ) {
		xv_sync_novel_counters( $post_id, $post );
	}
}, 20, 2 );

/* -------------------------------------------------------------------------
 * История чтения, серии и достижения
 *
 * Ключи те же, что у XIN-Com: читатель, пришедший с той темы, не теряет
 * ни серию, ни счётчик прочитанного.
 * ---------------------------------------------------------------------- */

const XV_READ_DAY    = '_xin_last_read_day';
const XV_READ_STREAK = '_xin_streak';
const XV_READ_BEST   = '_xin_streak_best';
const XV_READ_COUNT  = '_xin_read_count';
const XV_HISTORY     = '_xin_history';

/**
 * Сегодняшний день по времени сайта, номером. Считать в датах нельзя:
 * «вчера» у читателя и у сервера должно совпадать.
 */
function xv_today() {
	return (int) floor( ( time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) / DAY_IN_SECONDS );
}

/**
 * Отмечает прочитанную главу: счётчик, серия дней и лента истории.
 *
 * Одна глава засчитывается раз в сутки — перечитывание не накручивает
 * ни счётчик, ни серию.
 */
function xv_track_reading( $chapter_id ) {
	$user_id = get_current_user_id();

	if ( ! $user_id || ! $chapter_id ) {
		return;
	}

	$seen = 'xv_read_' . $user_id . '_' . (int) $chapter_id;

	if ( get_transient( $seen ) ) {
		return;
	}

	set_transient( $seen, 1, DAY_IN_SECONDS );

	update_user_meta( $user_id, XV_READ_COUNT, (int) get_user_meta( $user_id, XV_READ_COUNT, true ) + 1 );

	xv_push_history( $user_id, $chapter_id );

	$today = xv_today();
	$last  = (int) get_user_meta( $user_id, XV_READ_DAY, true );

	if ( $last === $today ) {
		return;
	}

	$streak = ( $last && 1 === $today - $last )
		? (int) get_user_meta( $user_id, XV_READ_STREAK, true ) + 1
		: 1;

	update_user_meta( $user_id, XV_READ_DAY, $today );
	update_user_meta( $user_id, XV_READ_STREAK, $streak );

	if ( $streak > (int) get_user_meta( $user_id, XV_READ_BEST, true ) ) {
		update_user_meta( $user_id, XV_READ_BEST, $streak );
	}
}

/**
 * Лента последнего прочитанного. Держим короткой — двадцать записей: это
 * «на чём я остановился», а не архив.
 */
function xv_push_history( $user_id, $chapter_id ) {
	$novel_id = (int) get_post_meta( $chapter_id, '_xin_novel', true );

	if ( ! $novel_id ) {
		return;
	}

	$log = xv_history( $user_id );

	// Тайтл в ленте один: новая глава заменяет прежнюю запись, а не
	// добавляется к ней — иначе лента забивается одним произведением.
	foreach ( $log as $i => $row ) {
		if ( (int) $row['novel'] === $novel_id ) {
			unset( $log[ $i ] );
		}
	}

	array_unshift( $log, array(
		'novel'   => $novel_id,
		'chapter' => (int) $chapter_id,
		'at'      => time(),
	) );

	update_user_meta( $user_id, XV_HISTORY, array_slice( array_values( $log ), 0, 20 ) );
}

function xv_history( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$log = get_user_meta( $user_id, XV_HISTORY, true );

	return is_array( $log ) ? $log : array();
}

function xv_streak( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return 0;
	}

	$last = (int) get_user_meta( $user_id, XV_READ_DAY, true );

	// Серия рвётся, когда пропущен целый день: вчера — ещё серия, позавчера
	// — уже нет.
	if ( ! $last || xv_today() - $last > 1 ) {
		return 0;
	}

	return (int) get_user_meta( $user_id, XV_READ_STREAK, true );
}

function xv_streak_best( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id ? (int) get_user_meta( $user_id, XV_READ_BEST, true ) : 0;
}

function xv_read_count( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return $user_id ? (int) get_user_meta( $user_id, XV_READ_COUNT, true ) : 0;
}

/**
 * Достижения читателя.
 *
 * Не игра с очками, а три понятные отметки: сколько прочитано, сколько дней
 * подряд и сколько тайтлов собрано. Порог берём редкий — знак должен что-то
 * значить.
 */
function xv_badges( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$read   = xv_read_count( $user_id );
	$streak = xv_streak_best( $user_id );
	$shelf  = count( xv_favourites( $user_id ) );

	$badges = array();

	foreach ( array( 10 => __( 'Десять глав', 'xin-v2' ), 100 => __( 'Сто глав', 'xin-v2' ), 1000 => __( 'Тысяча глав', 'xin-v2' ) ) as $need => $label ) {
		$badges[] = array(
			'key'   => 'read-' . $need,
			'label' => $label,
			'icon'  => 'book-open',
			'on'    => $read >= $need,
			'have'  => $read,
			'need'  => $need,
		);
	}

	foreach ( array( 7 => __( 'Неделя подряд', 'xin-v2' ), 30 => __( 'Месяц подряд', 'xin-v2' ) ) as $need => $label ) {
		$badges[] = array(
			'key'   => 'streak-' . $need,
			'label' => $label,
			'icon'  => 'clock',
			'on'    => $streak >= $need,
			'have'  => $streak,
			'need'  => $need,
		);
	}

	$badges[] = array(
		'key'   => 'shelf-10',
		'label' => __( 'Своя полка', 'xin-v2' ),
		'icon'  => 'heart',
		'on'    => $shelf >= 10,
		'have'  => $shelf,
		'need'  => 10,
	);

	return $badges;
}

/**
 * Прочитанное отмечаем на самой странице главы, а не в JS: читатель без
 * скриптов тоже читает, и серия у него рваться не должна.
 */
function xv_track_on_view() {
	if ( is_singular( 'chapter' ) && is_user_logged_in() ) {
		$id = get_queried_object_id();

		if ( xv_chapter_unlocked( $id ) ) {
			xv_track_reading( $id );
		}
	}
}
add_action( 'template_redirect', 'xv_track_on_view', 20 );
