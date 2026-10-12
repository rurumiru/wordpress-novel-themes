<?php
/**
 * Диагностика и починка.
 *
 * Каждая проверка умеет две вещи: найти проблемные записи и починить их.
 * Починка идёт порциями — на большой библиотеке один запрос иначе не
 * укладывается в лимит времени.
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Сколько записей чиним за один заход.
 */
function xvk_batch() {
	return (int) apply_filters( 'xvk_batch_size', 200 );
}

/**
 * Список проверок.
 *
 * `find` возвращает массив идентификаторов (или произвольных значений),
 * `fix` принимает этот массив и возвращает число исправленного.
 * Без `fix` проверка только сообщает — не всё можно починить машинально.
 */
function xvk_checks() {
	$checks = array(

		'counters' => array(
			'label' => __( 'Счётчики тайтлов', 'xin-v2-kit' ),
			'hint'  => __( 'Число глав и время последнего обновления разошлись с фактом. По ним сортируется каталог.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_counters',
			'fix'   => 'xvk_fix_counters',
		),

		'orphans' => array(
			'label' => __( 'Главы без тайтла', 'xin-v2-kit' ),
			'hint'  => __( 'Глава не привязана к тайтлу или тайтл удалён. Такая глава висит в ленте обновлений и ведёт в никуда.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_orphans',
			'fix'   => 'xvk_fix_orphans',
			'note'  => __( 'Починка убирает их в черновики: угадать нужный тайтл нельзя, а в ленте им не место.', 'xin-v2-kit' ),
		),

		'numbers' => array(
			'label' => __( 'Главы без номера', 'xin-v2-kit' ),
			'hint'  => __( 'Без номера глава уходит в конец оглавления и ломает переходы «дальше».', 'xin-v2-kit' ),
			'find'  => 'xvk_find_numbers',
			'fix'   => 'xvk_fix_numbers',
			'note'  => __( 'Номера проставляются по дате выхода внутри тайтла.', 'xin-v2-kit' ),
		),

		'duplicates' => array(
			'label' => __( 'Одинаковые номера глав', 'xin-v2-kit' ),
			'hint'  => __( 'В одном тайтле две главы с одним номером — порядок чтения становится случайным.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_duplicates',
			'fix'   => 'xvk_fix_duplicates',
			'note'  => __( 'Тайтлы перенумеровываются подряд по дате выхода.', 'xin-v2-kit' ),
		),

		'stale_locks' => array(
			'label' => __( 'Просроченный ранний доступ', 'xin-v2-kit' ),
			'hint'  => __( 'Дата открытия прошла, а глава всё ещё закрыта.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_stale_locks',
			'fix'   => 'xvk_fix_stale_locks',
		),

		'statuses' => array(
			'label' => __( 'Тайтлы без статуса', 'xin-v2-kit' ),
			'hint'  => __( 'Без статуса тайтл не попадает в фильтр каталога.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_statuses',
			'fix'   => 'xvk_fix_statuses',
			'note'  => __( 'Проставляется «Выходит» — самый частый случай.', 'xin-v2-kit' ),
		),

		'pages' => array(
			'label' => __( 'Страницы шаблонов', 'xin-v2-kit' ),
			'hint'  => __( 'Кабинету автора и рейтингу нужны страницы: без них ссылки в меню пропадают.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_pages',
			'fix'   => 'xvk_fix_pages',
		),

		'rewrites' => array(
			'label' => __( 'Правила ссылок', 'xin-v2-kit' ),
			'hint'  => __( 'Адреса глав вида /read/тайтл/глава/ не зарегистрированы — страницы отдают 404.', 'xin-v2-kit' ),
			'find'  => 'xvk_find_rewrites',
			'fix'   => 'xvk_fix_rewrites',
		),
	);

	return apply_filters( 'xvk_checks', $checks );
}

/* -------------------------------------------------------------------------
 * Поиск
 * ---------------------------------------------------------------------- */

function xvk_all_novels() {
	return get_posts( array(
		'post_type'      => 'novel',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
}

function xvk_all_chapters() {
	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
}

function xvk_find_counters() {
	$bad = array();

	foreach ( xvk_all_novels() as $novel_id ) {
		$ids   = xvk_novel_chapter_ids( $novel_id );
		$count = count( $ids );

		if ( (int) get_post_meta( $novel_id, '_xin_chapter_count', true ) !== $count ) {
			$bad[] = $novel_id;
			continue;
		}

		$last = 0;

		foreach ( $ids as $id ) {
			$time = (int) get_post_timestamp( $id );
			$last = max( $last, $time );
		}

		if ( $last && (int) get_post_meta( $novel_id, '_xin_last_chapter_at', true ) !== $last ) {
			$bad[] = $novel_id;
		}
	}

	return $bad;
}

function xvk_find_orphans() {
	$bad = array();

	foreach ( xvk_all_chapters() as $id ) {
		$novel = (int) get_post_meta( $id, '_xin_novel', true );

		if ( ! $novel || 'novel' !== get_post_type( $novel ) ) {
			$bad[] = $id;
		}
	}

	return $bad;
}

function xvk_find_numbers() {
	$bad = array();

	foreach ( xvk_all_chapters() as $id ) {
		$novel = (int) get_post_meta( $id, '_xin_novel', true );

		if ( ! $novel || 'novel' !== get_post_type( $novel ) ) {
			continue;
		}

		if ( '' === (string) get_post_meta( $id, '_xin_number', true ) ) {
			$bad[] = $id;
		}
	}

	return $bad;
}

function xvk_find_duplicates() {
	$bad = array();

	foreach ( xvk_all_novels() as $novel_id ) {
		$seen = array();

		foreach ( xvk_novel_chapter_ids( $novel_id, array( 'publish', 'draft', 'pending', 'future' ) ) as $id ) {
			$number = (string) get_post_meta( $id, '_xin_number', true );

			if ( '' === $number ) {
				continue;
			}

			if ( isset( $seen[ $number ] ) ) {
				$bad[] = $novel_id;
				break;
			}

			$seen[ $number ] = true;
		}
	}

	return $bad;
}

function xvk_find_stale_locks() {
	$bad = array();

	foreach ( xvk_all_chapters() as $id ) {
		if ( ! get_post_meta( $id, '_xin_locked', true ) ) {
			continue;
		}

		$at = (int) get_post_meta( $id, '_xin_unlock_at', true );

		if ( $at && $at <= time() ) {
			$bad[] = $id;
		}
	}

	return $bad;
}

function xvk_find_statuses() {
	$bad = array();

	foreach ( xvk_all_novels() as $id ) {
		$terms = wp_get_object_terms( $id, 'novel_status', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			$bad[] = $id;
		}
	}

	return $bad;
}

/**
 * Страницы, которых не хватает. Возвращаем шаблоны, а не идентификаторы.
 */
function xvk_required_pages() {
	return array(
		'template-author.php'  => array( 'title' => __( 'Кабинет автора', 'xin-v2-kit' ), 'slug' => 'cabinet' ),
		'template-ranking.php' => array( 'title' => __( 'Рейтинг', 'xin-v2-kit' ), 'slug' => 'rankings' ),
	);
}

function xvk_find_pages() {
	$missing = array();

	foreach ( xvk_required_pages() as $template => $page ) {
		// Шаблон принадлежит теме — если её нет, требовать страницу незачем.
		if ( ! locate_template( $template ) ) {
			continue;
		}

		$found = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
		) );

		if ( ! $found ) {
			$missing[] = $template;
		}
	}

	return $missing;
}

function xvk_find_rewrites() {
	$rules = get_option( 'rewrite_rules' );

	if ( ! is_array( $rules ) ) {
		return array( 'rewrite_rules' );
	}

	foreach ( array_keys( $rules ) as $pattern ) {
		if ( str_starts_with( $pattern, '^read/' ) ) {
			return array();
		}
	}

	return array( 'rewrite_rules' );
}

/* -------------------------------------------------------------------------
 * Починка
 * ---------------------------------------------------------------------- */

function xvk_fix_counters( $ids ) {
	$done = 0;

	foreach ( array_slice( $ids, 0, xvk_batch() ) as $id ) {
		xvk_sync_novel( $id );
		$done++;
	}

	delete_transient( 'xv_site_stats' );

	return $done;
}

function xvk_fix_orphans( $ids ) {
	$done = 0;

	foreach ( array_slice( $ids, 0, xvk_batch() ) as $id ) {
		if ( 'draft' === get_post_status( $id ) ) {
			continue;
		}

		wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		$done++;
	}

	return $done;
}

function xvk_fix_numbers( $ids ) {
	$by_novel = array();

	foreach ( array_slice( $ids, 0, xvk_batch() ) as $id ) {
		$novel = (int) get_post_meta( $id, '_xin_novel', true );

		if ( $novel ) {
			$by_novel[ $novel ][] = $id;
		}
	}

	$done = 0;

	foreach ( $by_novel as $novel => $chapters ) {
		// Внутри тайтла ставим номера по дате: так порядок совпадает с тем,
		// в каком главы выходили.
		usort( $chapters, function ( $a, $b ) {
			return (int) get_post_timestamp( $a ) <=> (int) get_post_timestamp( $b );
		} );

		$next = xvk_next_chapter_number( $novel );

		foreach ( $chapters as $id ) {
			update_post_meta( $id, '_xin_number', (string) $next );
			$next++;
			$done++;
		}

		xvk_sync_novel( $novel );
	}

	return $done;
}

function xvk_fix_duplicates( $novel_ids ) {
	$done = 0;

	foreach ( array_slice( $novel_ids, 0, xvk_batch() ) as $novel_id ) {
		$chapters = xvk_novel_chapter_ids( $novel_id, array( 'publish', 'draft', 'pending', 'future' ) );

		usort( $chapters, function ( $a, $b ) {
			return (int) get_post_timestamp( $a ) <=> (int) get_post_timestamp( $b );
		} );

		$number = 1;

		foreach ( $chapters as $id ) {
			update_post_meta( $id, '_xin_number', (string) $number );
			$number++;
		}

		xvk_sync_novel( $novel_id );
		$done++;
	}

	return $done;
}

function xvk_fix_stale_locks( $ids ) {
	$done = 0;

	foreach ( array_slice( $ids, 0, xvk_batch() ) as $id ) {
		delete_post_meta( $id, '_xin_locked' );
		$done++;
	}

	return $done;
}

function xvk_fix_statuses( $ids ) {
	$term = term_exists( 'ongoing', 'novel_status' );

	if ( ! $term ) {
		$term = wp_insert_term( __( 'Выходит', 'xin-v2-kit' ), 'novel_status', array( 'slug' => 'ongoing' ) );
	}

	if ( is_wp_error( $term ) ) {
		return 0;
	}

	$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
	$done    = 0;

	foreach ( array_slice( $ids, 0, xvk_batch() ) as $id ) {
		wp_set_object_terms( $id, array( $term_id ), 'novel_status' );
		$done++;
	}

	return $done;
}

function xvk_fix_pages( $templates ) {
	$pages = xvk_required_pages();
	$done  = 0;

	foreach ( $templates as $template ) {
		if ( ! isset( $pages[ $template ] ) ) {
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $pages[ $template ]['title'],
			'post_name'   => $pages[ $template ]['slug'],
		) );

		if ( ! is_wp_error( $id ) && $id ) {
			update_post_meta( $id, '_wp_page_template', $template );
			$done++;
		}
	}

	if ( $done ) {
		wp_cache_flush();
	}

	return $done;
}

function xvk_fix_rewrites( $ids ) {
	flush_rewrite_rules();

	return 1;
}

/* -------------------------------------------------------------------------
 * Отчёт
 * ---------------------------------------------------------------------- */

/**
 * Прогон всех проверок.
 */
function xvk_report() {
	$report = array();

	foreach ( xvk_checks() as $key => $check ) {
		$found = call_user_func( $check['find'] );

		$report[ $key ] = array(
			'label'    => $check['label'],
			'hint'     => $check['hint'],
			'note'     => isset( $check['note'] ) ? $check['note'] : '',
			'fixable'  => isset( $check['fix'] ),
			// Необязательная проверка — не поломка, а предложение: в счётчик
			// расхождений не идёт и галочкой заранее не отмечается.
			'optional' => ! empty( $check['optional'] ),
			'items'    => $found,
			'count'    => count( $found ),
		);
	}

	return $report;
}

/**
 * Починить одну проверку.
 *
 * @return int Сколько записей исправлено.
 */
function xvk_run_fix( $key ) {
	$checks = xvk_checks();

	if ( ! isset( $checks[ $key ]['fix'] ) ) {
		return 0;
	}

	$found = call_user_func( $checks[ $key ]['find'] );

	if ( ! $found ) {
		return 0;
	}

	return (int) call_user_func( $checks[ $key ]['fix'], $found );
}
