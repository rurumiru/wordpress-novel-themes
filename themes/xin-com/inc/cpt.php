<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_post_supports( $supports ) {
	if ( function_exists( 'xin_discussions_on' ) && xin_discussions_on() ) {
		$supports[] = 'comments';
	}

	return $supports;
}

function xin_register_post_types() {
	register_post_type( 'novel', array(
		'labels'              => array(
			'name'               => __( 'Новеллы', 'xin-com' ),
			'singular_name'      => __( 'Новелла', 'xin-com' ),
			'add_new'            => __( 'Добавить', 'xin-com' ),
			'add_new_item'       => __( 'Добавить новеллу', 'xin-com' ),
			'edit_item'          => __( 'Редактировать новеллу', 'xin-com' ),
			'new_item'           => __( 'Новая новелла', 'xin-com' ),
			'view_item'          => __( 'Смотреть новеллу', 'xin-com' ),
			'search_items'       => __( 'Искать новеллы', 'xin-com' ),
			'not_found'          => __( 'Новелл не найдено', 'xin-com' ),
			'all_items'          => __( 'Все новеллы', 'xin-com' ),
			'menu_name'          => __( 'Новеллы', 'xin-com' ),
			'featured_image'     => __( 'Обложка', 'xin-com' ),
			'set_featured_image' => __( 'Задать обложку', 'xin-com' ),
		),
		'public'              => true,
		'has_archive'         => 'novels',
		'menu_icon'           => 'dashicons-book-alt',
		'menu_position'       => 5,
		'show_in_rest'        => true,
		'supports'            => xin_post_supports( array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'revisions' ) ),
		'taxonomies'          => array( 'genre', 'novel_tag', 'novel_status' ),
		'rewrite'             => array( 'slug' => 'novels', 'with_front' => false ),
		'exclude_from_search' => false,
	) );

	register_post_type( 'chapter', array(
		'labels'          => array(
			'name'          => __( 'Главы', 'xin-com' ),
			'singular_name' => __( 'Глава', 'xin-com' ),
			'add_new_item'  => __( 'Добавить главу', 'xin-com' ),
			'edit_item'     => __( 'Редактировать главу', 'xin-com' ),
			'all_items'     => __( 'Все главы', 'xin-com' ),
			'search_items'  => __( 'Искать главы', 'xin-com' ),
			'not_found'     => __( 'Глав не найдено', 'xin-com' ),
			'menu_name'     => __( 'Главы', 'xin-com' ),
		),
		'public'          => true,
		'has_archive'     => 'updates',
		'menu_icon'       => 'dashicons-media-text',
		'menu_position'   => 6,
		'show_in_rest'    => true,
		'supports'        => xin_post_supports( array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ) ),
		'rewrite'         => array( 'slug' => 'read', 'with_front' => false ),
	) );
}
add_action( 'init', 'xin_register_post_types' );

function xin_register_taxonomies() {
	register_taxonomy( 'genre', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Жанры', 'xin-com' ),
			'singular_name' => __( 'Жанр', 'xin-com' ),
			'add_new_item'  => __( 'Добавить жанр', 'xin-com' ),
			'menu_name'     => __( 'Жанры', 'xin-com' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'genre', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_tag', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Теги тайтлов', 'xin-com' ),
			'singular_name' => __( 'Тег', 'xin-com' ),
			'menu_name'     => __( 'Теги тайтлов', 'xin-com' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'novel-tag', 'with_front' => false ),
	) );

	register_taxonomy( 'novel_status', array( 'novel' ), array(
		'labels'            => array(
			'name'          => __( 'Статусы', 'xin-com' ),
			'singular_name' => __( 'Статус', 'xin-com' ),
			'menu_name'     => __( 'Статусы', 'xin-com' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'status', 'with_front' => false ),
	) );
}
add_action( 'init', 'xin_register_taxonomies' );

function xin_seed_terms() {
	if ( get_option( 'xin_terms_seeded' ) ) {
		return;
	}
	$statuses = array(
		'ongoing'   => __( 'Выходит', 'xin-com' ),
		'completed' => __( 'Завершён', 'xin-com' ),
		'hiatus'    => __( 'Заморожен', 'xin-com' ),
		'announced' => __( 'Анонс', 'xin-com' ),
	);
	foreach ( $statuses as $slug => $name ) {
		if ( ! term_exists( $slug, 'novel_status' ) ) {
			wp_insert_term( $name, 'novel_status', array( 'slug' => $slug ) );
		}
	}
	update_option( 'xin_terms_seeded', 1 );
}
add_action( 'init', 'xin_seed_terms', 20 );

function xin_flush_rewrites() {
	xin_register_post_types();
	xin_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'xin_flush_rewrites' );

function xin_chapter_novel_id( $chapter_id = 0 ) {
	$chapter_id = $chapter_id ? $chapter_id : get_the_ID();
	return (int) get_post_meta( $chapter_id, '_xin_novel', true );
}

function xin_chapter_number( $chapter_id = 0 ) {
	$chapter_id = $chapter_id ? $chapter_id : get_the_ID();
	$num        = get_post_meta( $chapter_id, '_xin_number', true );
	return '' === $num ? null : (float) $num;
}

function xin_get_chapters( $novel_id, $order = 'ASC', $limit = -1 ) {
	if ( ! $novel_id ) {
		return array();
	}

	$key = 'xin_chapters_' . $novel_id . '_v' . xin_chapters_cache_version( $novel_id ) . '_' . $order . '_' . $limit;
	$hit = wp_cache_get( $key, 'xin-com' );
	if ( false !== $hit ) {
		return $hit;
	}

	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_key'       => '_xin_number',
		'orderby'        => array( 'meta_value_num' => $order, 'date' => $order ),
		'meta_query'     => array(
			array(
				'key'   => '_xin_novel',
				'value' => (int) $novel_id,
			),
		),
		// Пагинации у списка глав нет, а SQL_CALC_FOUND_ROWS стоит второго
		// прохода по таблице. Термины главам не назначаются вовсе.
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );

	wp_cache_set( $key, $chapters, 'xin-com', HOUR_IN_SECONDS );
	return $chapters;
}

/**
 * Порядковый список ID глав тайтла.
 *
 * Отдельно от xin_get_chapters(), потому что тому нужны целые записи — вместе
 * с текстом главы. Карточке в каталоге нужно ЧИСЛО глав, соседней главе — ID
 * соседа; поднимать ради этого весь текст тайтла (сотни записей на каждую
 * карточку страницы) — самое дорогое, что тема делала на обычном каталоге.
 *
 * @param int    $novel_id ID тайтла.
 * @param string $order    ASC или DESC.
 * @return int[]
 */
function xin_chapter_ids( $novel_id, $order = 'ASC' ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return array();
	}

	$order = 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
	$key   = 'xin_chapter_ids_' . $novel_id . '_v' . xin_chapters_cache_version( $novel_id ) . '_' . $order;
	$hit   = wp_cache_get( $key, 'xin-com' );
	if ( false !== $hit ) {
		return $hit;
	}

	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_xin_number',
		'orderby'        => array( 'meta_value_num' => $order, 'date' => $order ),
		'meta_query'     => array(
			array(
				'key'   => '_xin_novel',
				'value' => $novel_id,
			),
		),
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	$ids = array_map( 'intval', (array) $ids );
	wp_cache_set( $key, $ids, 'xin-com', HOUR_IN_SECONDS );

	return $ids;
}

/**
 * Ссылка «наугад»: адрес каталога с меткой, которую ловит редирект.
 *
 * Ссылка, а не форма, потому что её кладут и в меню быстрых переходов, и в
 * плитку на главной. Сам выбор делается на сервере — см. xin_random_redirect().
 *
 * @return string
 */
/**
 * Переход к главе по её номеру: /novels/<тайтл>/?goto=137
 *
 * У книги на несколько тысяч глав листать оглавление постранично — работа.
 * Читатель почти всегда знает номер, на котором остановился, и вводит его.
 *
 * @return void
 */
function xin_goto_chapter_redirect() {
	if ( is_admin() || ! isset( $_GET['goto'] ) || ! is_singular( 'novel' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$novel_id = get_queried_object_id();
	$number   = trim( (string) wp_unslash( $_GET['goto'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$number   = str_replace( ',', '.', $number );

	if ( '' === $number || ! is_numeric( $number ) || ! $novel_id ) {
		return;
	}

	$found = get_posts( array(
		'post_type'              => 'chapter',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'AND',
			array( 'key' => '_xin_novel', 'value' => $novel_id ),
			array( 'key' => '_xin_number', 'value' => (float) $number, 'type' => 'DECIMAL(20,4)', 'compare' => '=' ),
		),
	) );

	if ( $found ) {
		wp_safe_redirect( get_permalink( $found[0] ), 302 );
		exit;
	}

	// Такой главы нет — возвращаем к оглавлению, а не к пустому экрану.
	wp_safe_redirect( get_permalink( $novel_id ) . '#chapters', 302 );
	exit;
}
add_action( 'template_redirect', 'xin_goto_chapter_redirect', 4 );

function xin_random_novel_url() {
	$base = xin_comics_enabled() && 'comic' === xin_current_section()
		? xin_section_home_link( 'comic' )
		: get_post_type_archive_link( 'novel' );

	return add_query_arg( 'xin', 'random', $base ? $base : home_url( '/' ) );
}

/**
 * ID случайного тайтла текущего раздела.
 *
 * Намеренно без `ORDER BY RAND()`: он заставляет базу присвоить случайное
 * число каждой строке и отсортировать весь набор — на каталоге в десятки
 * тысяч тайтлов это самый дорогой запрос на сайте. Вместо этого берётся
 * случайное смещение от уже посчитанного числа записей, и из базы уезжает
 * ровно одна строка.
 *
 * @param string $format `text`, `comic` или `any`.
 * @return int
 */
function xin_random_novel_id( $format = '' ) {
	$format = $format ? $format : ( xin_comics_enabled() ? xin_current_section() : 'any' );
	$counts = wp_count_posts( 'novel' );
	$total  = $counts ? (int) $counts->publish : 0;

	if ( $total < 1 ) {
		return 0;
	}

	$args = array(
		'post_type'              => 'novel',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'orderby'                => 'ID',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'offset'                 => wp_rand( 0, $total - 1 ),
	);

	$clause = xin_format_meta_clause( $format );
	if ( $clause ) {
		$args['meta_query'] = $clause; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}

	$ids = get_posts( $args );

	/*
	 * Смещение считалось по всем тайтлам, а раздел мог отфильтровать часть:
	 * тогда выборка приходит пустой. Второй заход без смещения всегда что-то
	 * возвращает и стоит один индексный поиск.
	 */
	if ( ! $ids ) {
		$args['offset'] = 0;
		$ids            = get_posts( $args );
	}

	return $ids ? (int) $ids[0] : 0;
}

/**
 * Ловит `?xin=random` на любой странице и уводит на случайный тайтл.
 *
 * @return void
 */
function xin_random_redirect() {
	if ( is_admin() || ! isset( $_GET['xin'] ) || 'random' !== $_GET['xin'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$id  = xin_random_novel_id();
	$url = $id ? get_permalink( $id ) : get_post_type_archive_link( 'novel' );

	// 302: «случайный» на то и случайный, кэшировать редирект нельзя.
	wp_safe_redirect( $url ? $url : home_url( '/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'xin_random_redirect', 3 );

/**
 * Ключ, под которым у тайтла лежит число опубликованных глав.
 */
const XIN_COUNT_META = '_xin_chapter_count';

/**
 * Ключ, под которым у тайтла лежит дата последней опубликованной главы.
 */
const XIN_FRESH_META = '_xin_last_chapter_at';

/**
 * Сколько у тайтла опубликованных глав.
 *
 * Раньше считалось как `count( xin_chapter_ids() )` — то есть ради одного
 * числа на карточке из базы уезжал весь список глав тайтла. На каталоге из
 * двух десятков карточек это два десятка выборок без предела, и у тайтла на
 * пять тысяч глав каждая из них тянула пять тысяч строк.
 *
 * Теперь число лежит в мете тайтла: WordPress и так поднимает мету всех
 * записей запроса одним заходом, поэтому карточка не стоит ни одного
 * дополнительного обращения к базе. Пересчёт — ленивый: правка главы просто
 * стирает мету, а считает её первый, кто спросит. Так импорт на тысячи глав
 * не превращается в тысячи COUNT(*).
 *
 * @param int $novel_id Тайтл.
 * @return int
 */
function xin_chapter_count( $novel_id ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return 0;
	}

	$stored = get_post_meta( $novel_id, XIN_COUNT_META, true );
	if ( '' !== $stored && null !== $stored ) {
		return (int) $stored;
	}

	return xin_refresh_chapter_count( $novel_id );
}

/**
 * Пересчитывает число глав тайтла и запоминает его.
 *
 * @param int $novel_id Тайтл.
 * @return int
 */
function xin_refresh_chapter_count( $novel_id ) {
	global $wpdb;

	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return 0;
	}

	/*
	 * Число глав и дата последней считаются одним проходом: обе величины
	 * нужны карточке, и обе устаревают в один и тот же момент.
	 */
	$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT COUNT(*) AS total, MAX( p.post_date_gmt ) AS last_at
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = '_xin_novel'
			   AND pm.meta_value = %d
			   AND p.post_type = 'chapter'
			   AND p.post_status = 'publish'",
			$novel_id
		)
	);

	$count = $row ? (int) $row->total : 0;

	update_post_meta( $novel_id, XIN_COUNT_META, $count );
	update_post_meta( $novel_id, XIN_FRESH_META, $row && $row->last_at ? $row->last_at : '' );

	return $count;
}

/**
 * Когда у тайтла вышла последняя глава.
 *
 * @param int $novel_id Тайтл.
 * @return int Метка времени UTC или 0.
 */
function xin_novel_updated_at( $novel_id ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return 0;
	}

	$stored = get_post_meta( $novel_id, XIN_FRESH_META, true );

	if ( '' === $stored || null === $stored ) {
		// Дата считается тем же запросом, что и число глав.
		xin_refresh_chapter_count( $novel_id );
		$stored = get_post_meta( $novel_id, XIN_FRESH_META, true );
	}

	return $stored ? (int) strtotime( $stored . ' UTC' ) : 0;
}

/**
 * Вышла ли у тайтла глава совсем недавно.
 *
 * Нужно карточке: у переводов, которые выходят каждый день, «свежесть» —
 * первое, на что читатель смотрит в каталоге.
 *
 * @param int $novel_id Тайтл.
 * @return bool
 */
function xin_novel_is_fresh( $novel_id ) {
	$at = xin_novel_updated_at( $novel_id );

	if ( ! $at ) {
		return false;
	}

	/**
	 * За сколько часов глава считается свежей.
	 *
	 * @param int $hours Часы.
	 */
	$hours = max( 1, (int) apply_filters( 'xin_fresh_hours', 72 ) );

	return ( time() - $at ) < $hours * HOUR_IN_SECONDS;
}

/**
 * Помечает число глав тайтла как неизвестное.
 *
 * @param int $novel_id Тайтл.
 * @return void
 */
function xin_forget_chapter_count( $novel_id ) {
	$novel_id = (int) $novel_id;
	if ( $novel_id ) {
		delete_post_meta( $novel_id, XIN_COUNT_META );
		delete_post_meta( $novel_id, XIN_FRESH_META );
	}
}

/**
 * Версия кэша глав тайтла.
 *
 * Считается вместе с ключом, поэтому сброс — это увеличение одного числа, а
 * не обход кэша. Ключ живёт вечно: он крошечный, а «протухнуть» ему нечем.
 *
 * @param int $novel_id Тайтл.
 * @return int
 */
function xin_chapters_cache_version( $novel_id ) {
	$key = 'xin_chapters_ver_' . (int) $novel_id;
	$ver = wp_cache_get( $key, 'xin-com' );

	if ( false === $ver ) {
		$ver = 1;
		wp_cache_set( $key, $ver, 'xin-com', 0 );
	}

	return (int) $ver;
}

/**
 * Объявляет кэш глав тайтла устаревшим.
 *
 * @param int $novel_id Тайтл.
 * @return void
 */
function xin_chapters_cache_bust( $novel_id ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return;
	}

	$key = 'xin_chapters_ver_' . $novel_id;
	$ver = wp_cache_get( $key, 'xin-com' );
	wp_cache_set( $key, false === $ver ? 2 : (int) $ver + 1, 'xin-com', 0 );
}

/**
 * Сколько глав показывать в оглавлении за раз.
 *
 * @return int
 */
function xin_chapters_per_page() {
	/**
	 * Размер страницы оглавления.
	 *
	 * @param int $per_page Строк на странице.
	 */
	$per_page = (int) apply_filters( 'xin_chapters_per_page', 30 );

	/*
	 * Предел сверху обязателен. Страница оглавления поднимает записи запросом
	 * `post__in`, а список идентификаторов в SQL не бесконечен: на SQLite
	 * запрос с несколькими тысячами значений просто возвращает пустоту, а на
	 * MySQL превращается в очень дорогой разбор. Проверено на тайтле с
	 * четырьмя тысячами глав: при попытке выдать их одной страницей
	 * оглавление молча оказывалось пустым.
	 */
	return max( 5, min( 500, $per_page ) );
}

/**
 * Страница оглавления: только те главы, что реально нужно показать.
 *
 * Прежде страница тайтла поднимала из базы все главы целиком — вместе с
 * текстом каждой. У тайтла на три тысячи глав это десятки мегабайт в памяти
 * одного запроса: сайт либо упирался в memory_limit, либо отдавал страницу
 * секундами. Порядок глав берётся из списка одних только ID (он кэшируется),
 * а записи поднимаются ровно за одну страницу.
 *
 * @param int    $novel_id Тайтл.
 * @param string $order    ASC или DESC.
 * @param int    $page     Номер страницы, начиная с единицы.
 * @return array posts, total, page, pages, per_page.
 */
function xin_chapter_page( $novel_id, $order = 'ASC', $page = 1 ) {
	$ids      = xin_chapter_ids( $novel_id, $order );
	$total    = count( $ids );
	$per_page = xin_chapters_per_page();
	$pages    = $total ? (int) ceil( $total / $per_page ) : 1;
	$page     = min( max( 1, (int) $page ), $pages );

	$slice = array_slice( $ids, ( $page - 1 ) * $per_page, $per_page );
	$posts = array();

	if ( $slice ) {
		$found = get_posts( array(
			'post_type'              => 'chapter',
			'post_status'            => 'publish',
			'post__in'               => $slice,
			'orderby'                => 'post__in',
			'posts_per_page'         => count( $slice ),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		) );

		$posts = $found ? $found : array();
	}

	return array(
		'posts'    => $posts,
		'total'    => $total,
		'page'     => $page,
		'pages'    => $pages,
		'per_page' => $per_page,
	);
}

/**
 * Номер страницы оглавления из адреса.
 *
 * @return int
 */
/**
 * Окно глав вокруг текущей — для панели оглавления в читалке.
 *
 * Панель показывала весь список: у тайтла на несколько тысяч глав это и
 * записи целиком в памяти, и такой же список узлов в вёрстке. Читателю в
 * читалке нужны соседи, а не вся книга; за полным оглавлением есть страница
 * тайтла.
 *
 * @param int $novel_id   Тайтл.
 * @param int $chapter_id Текущая глава.
 * @param int $around     Сколько глав показать в каждую сторону.
 * @return array posts, before, after, total.
 */
function xin_chapter_window( $novel_id, $chapter_id, $around = 40 ) {
	$ids   = xin_chapter_ids( $novel_id, 'ASC' );
	$total = count( $ids );

	if ( ! $total ) {
		return array( 'posts' => array(), 'before' => 0, 'after' => 0, 'total' => 0 );
	}

	$at = array_search( (int) $chapter_id, $ids, true );
	$at = false === $at ? 0 : (int) $at;

	$around = max( 5, (int) $around );
	$from   = max( 0, $at - $around );
	$slice  = array_slice( $ids, $from, $around * 2 + 1 );

	$posts = get_posts( array(
		'post_type'              => 'chapter',
		'post_status'            => 'publish',
		'post__in'               => $slice,
		'orderby'                => 'post__in',
		'posts_per_page'         => count( $slice ),
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) );

	return array(
		'posts'  => $posts ? $posts : array(),
		'before' => $from,
		'after'  => max( 0, $total - $from - count( $slice ) ),
		'total'  => $total,
	);
}

/**
 * Как часто у тайтла выходят главы — человеческой строкой.
 *
 * Считается по датам последних двадцати глав. Это та величина, ради которой
 * читатель обычно и лезет в комментарии: «а он вообще ещё выходит?». Пустая
 * строка означает «сказать нечего» — например, все главы выложены разом.
 *
 * @param int $novel_id Тайтл.
 * @return string
 */
function xin_novel_rhythm( $novel_id ) {
	global $wpdb;

	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return '';
	}

	$key = 'xin_rhythm_' . $novel_id . '_v' . xin_chapters_cache_version( $novel_id );
	$hit = wp_cache_get( $key, 'xin-com' );
	if ( false !== $hit ) {
		return $hit;
	}

	$dates = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT p.post_date_gmt
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} link ON link.post_id = p.ID AND link.meta_key = '_xin_novel'
			 WHERE link.meta_value = %d AND p.post_type = 'chapter' AND p.post_status = 'publish'
			 ORDER BY p.post_date_gmt DESC
			 LIMIT 20",
			$novel_id
		)
	);

	$out = '';

	if ( count( (array) $dates ) >= 4 ) {
		$newest = strtotime( $dates[0] . ' UTC' );
		$oldest = strtotime( end( $dates ) . ' UTC' );
		$span   = $newest - $oldest;
		$steps  = count( $dates ) - 1;

		if ( $span > 0 && $steps > 0 ) {
			$per_week = ( $steps / ( $span / WEEK_IN_SECONDS ) );

			if ( $per_week >= 0.7 ) {
				$rounded = $per_week >= 1 ? (int) round( $per_week ) : 1;
				$out     = sprintf(
					/* translators: %1$d: chapters, %2$s: declined word for chapter. */
					__( '≈ %1$d %2$s в неделю', 'xin-com' ),
					$rounded,
					xin_plural( $rounded, __( 'глава', 'xin-com' ), __( 'главы', 'xin-com' ), __( 'глав', 'xin-com' ) )
				);
			} else {
				$days = (int) round( ( $span / DAY_IN_SECONDS ) / $steps );

				if ( $days > 0 && $days <= 120 ) {
					$out = sprintf(
						/* translators: %1$d: days, %2$s: declined word for day. */
						__( '≈ глава раз в %1$d %2$s', 'xin-com' ),
						$days,
						xin_plural( $days, __( 'день', 'xin-com' ), __( 'дня', 'xin-com' ), __( 'дней', 'xin-com' ) )
					);
				}
			}
		}
	}

	wp_cache_set( $key, $out, 'xin-com', HOUR_IN_SECONDS );

	return $out;
}

function xin_chapter_page_number() {
	return isset( $_GET['ch'] ) ? max( 1, absint( $_GET['ch'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Адрес страницы оглавления.
 *
 * @param int $novel_id Тайтл.
 * @param int $page     Страница.
 * @return string
 */
function xin_chapter_page_url( $novel_id, $page ) {
	$url = get_permalink( $novel_id );

	if ( $page > 1 ) {
		$url = add_query_arg( 'ch', (int) $page, $url );
	}

	return $url . '#chapters';
}

/**
 * Обходит главы тайтла порциями, не держа их все в памяти.
 *
 * Выгрузка книги и массовая замена по словарю поднимали разом все главы —
 * вместе с текстом. У тайтла на несколько тысяч глав это гарантированный
 * выход за memory_limit. Здесь из базы за раз приезжает одна порция, и перед
 * следующей она выбрасывается из кэша записей.
 *
 * @param int      $novel_id Тайтл.
 * @param callable $each     Функция, получающая WP_Post каждой главы.
 * @param string   $order    ASC или DESC.
 * @param int      $chunk    Размер порции.
 * @return int Сколько глав обошли.
 */
function xin_each_chapter( $novel_id, $each, $order = 'ASC', $chunk = 40 ) {
	$ids   = xin_chapter_ids( $novel_id, $order );
	$total = 0;

	foreach ( array_chunk( $ids, max( 5, (int) $chunk ) ) as $slice ) {
		$posts = get_posts( array(
			'post_type'              => 'chapter',
			'post_status'            => 'publish',
			'post__in'               => $slice,
			'orderby'                => 'post__in',
			'posts_per_page'         => count( $slice ),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		) );

		foreach ( (array) $posts as $post ) {
			call_user_func( $each, $post );
			++$total;
		}

		// Порция отработала — освобождаем память под следующую.
		foreach ( $slice as $id ) {
			clean_post_cache( $id );
		}

		unset( $posts );
	}

	return $total;
}

function xin_first_chapter( $novel_id ) {
	$chapters = xin_get_chapters( $novel_id, 'ASC', 1 );
	return $chapters ? $chapters[0] : null;
}

function xin_last_chapter( $novel_id ) {
	$chapters = xin_get_chapters( $novel_id, 'DESC', 1 );
	return $chapters ? $chapters[0] : null;
}

function xin_adjacent_chapter( $chapter_id, $dir = 1 ) {
	$novel_id = xin_chapter_novel_id( $chapter_id );
	if ( ! $novel_id ) {
		return null;
	}
	// По списку ID, а не по записям целиком: соседняя глава нужна одна, и
	// поднимать ради неё текст всего тайтла на каждый показ главы незачем.
	$ids = xin_chapter_ids( $novel_id, 'ASC' );
	$at  = array_search( (int) $chapter_id, $ids, true );
	if ( false === $at ) {
		return null;
	}

	$target = $at + ( $dir > 0 ? 1 : -1 );

	return isset( $ids[ $target ] ) ? get_post( $ids[ $target ] ) : null;
}

/**
 * Правка главы обнуляет кэш её тайтла — и только его.
 *
 * Прежде здесь стоял сброс всей группы кэша, а без объектного кэша с
 * поддержкой групп — сброс вообще всего кэша сайта. Импорт на пять тысяч
 * глав означал пять тысяч полных сбросов: сайт всё это время перестраивал
 * каждый запрос заново. Теперь трогается ровно один тайтл.
 *
 * @param int $post_id Запись.
 * @return void
 */
function xin_clear_chapter_cache( $post_id ) {
	if ( 'chapter' !== get_post_type( $post_id ) ) {
		return;
	}

	$novel_id = (int) get_post_meta( $post_id, '_xin_novel', true );
	if ( ! $novel_id ) {
		return;
	}

	xin_chapters_cache_bust( $novel_id );
	xin_forget_chapter_count( $novel_id );
}
add_action( 'save_post', 'xin_clear_chapter_cache' );
add_action( 'deleted_post', 'xin_clear_chapter_cache' );

/**
 * Глава сменила тайтл — пересчитать нужно оба.
 *
 * Ловим саму мету: перевесить главу можно и из массового редактора, и из
 * импорта, минуя обычное сохранение записи.
 *
 * @param int    $meta_id  Не используется.
 * @param int    $post_id  Глава.
 * @param string $meta_key Ключ.
 * @param mixed  $value    Новое значение.
 * @return void
 */
function xin_chapter_novel_changed( $meta_id, $post_id, $meta_key, $value ) {
	if ( '_xin_novel' !== $meta_key || 'chapter' !== get_post_type( $post_id ) ) {
		return;
	}

	foreach ( array( (int) $value, (int) get_post_meta( $post_id, '_xin_novel', true ) ) as $novel_id ) {
		if ( $novel_id ) {
			xin_chapters_cache_bust( $novel_id );
			xin_forget_chapter_count( $novel_id );
		}
	}
}
// `update_post_meta` срабатывает до записи, `added_post_meta` — после;
// оба отдают четыре аргумента, в отличие от `add_post_meta`.
add_action( 'update_post_meta', 'xin_chapter_novel_changed', 10, 4 );
add_action( 'added_post_meta', 'xin_chapter_novel_changed', 10, 4 );

/**
 * Публикация, снятие с публикации и корзина меняют число опубликованных глав.
 *
 * @param string  $new Новый статус.
 * @param string  $old Прежний статус.
 * @param WP_Post $post Запись.
 * @return void
 */
function xin_chapter_status_changed( $new, $old, $post ) {
	if ( ! $post instanceof WP_Post || 'chapter' !== $post->post_type || $new === $old ) {
		return;
	}

	xin_clear_chapter_cache( $post->ID );
}
add_action( 'transition_post_status', 'xin_chapter_status_changed', 10, 3 );

if ( ! function_exists( 'wp_cache_flush_group' ) ) {
	function wp_cache_flush_group( $group ) { 
		wp_cache_flush();
	}
}
