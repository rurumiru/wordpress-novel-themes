<?php
/**
 * Два раздела площадки: текстовые тайтлы и комиксы.
 *
 * Формат — это мета `_xin_format` у тайтла, а не отдельный тип записи. Каталог,
 * рейтинги, библиотека, избранное, оценки, роли и студия ключуются на `novel`;
 * заведи второй тип — и каждую из этих вещей пришлось бы писать дважды. По-
 * настоящему различаются только глава и читалка, и расходятся они на уровне
 * шаблона.
 *
 * Цена решения — протечка: запрос без явного формата смешал бы комиксы с
 * новеллами. Поэтому фильтр здесь ровно один, в `pre_get_posts`, и умолчание в
 * нём — «текст». Раздел новелл остаётся тем же, чем был до появления комиксов,
 * даже если про формат забыли.
 *
 * @package XIN-Com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Корень раздела комиксов: /comics/. */
const XIN_COMICS_BASE = 'comics';

/** Сегмент каталога внутри раздела: /comics/catalog/. */
const XIN_COMICS_CATALOG = 'catalog';

/**
 * Разделы площадки.
 *
 * @return array<string, array{label: string, catalog: string, icon: string}>
 */
function xin_formats() {
	return array(
		'text'  => array(
			'label'   => __( 'Новеллы', 'xin-com' ),
			'catalog' => __( 'Каталог новелл', 'xin-com' ),
			'icon'    => 'book',
		),
		'comic' => array(
			'label'   => __( 'Комиксы', 'xin-com' ),
			'catalog' => __( 'Каталог комиксов', 'xin-com' ),
			'icon'    => 'layers',
		),
	);
}

/**
 * Приводит что угодно к одному из двух форматов.
 *
 * @param mixed $value Сырое значение.
 * @return string `text` или `comic`.
 */
function xin_format_key( $value ) {
	return 'comic' === $value ? 'comic' : 'text';
}

/**
 * Формат тайтла.
 *
 * @param int $novel_id Тайтл.
 * @return string
 */
function xin_novel_format( $novel_id ) {
	return xin_format_key( get_post_meta( $novel_id, '_xin_format', true ) );
}

/**
 * @param int $novel_id Тайтл.
 * @return bool
 */
function xin_is_comic( $novel_id ) {
	return 'comic' === xin_novel_format( $novel_id );
}

/**
 * Формат главы — формат её тайтла.
 *
 * Значение зеркалится в мету самой главы (см. `xin_sync_chapter_format()`),
 * иначе ленту обновлений раздела пришлось бы собирать через список ID всех
 * тайтлов формата — на большой площадке это `IN` на тысячи значений.
 *
 * @param int $chapter_id Глава.
 * @return string
 */
function xin_chapter_format( $chapter_id ) {
	$novel_id = xin_chapter_novel_id( $chapter_id );

	return $novel_id ? xin_novel_format( $novel_id ) : 'text';
}

/**
 * Раздел, в котором находится текущий запрос.
 *
 * @return string
 */
function xin_current_section() {
	if ( is_singular( 'novel' ) ) {
		return xin_novel_format( get_queried_object_id() );
	}

	if ( is_singular( 'chapter' ) ) {
		return xin_chapter_format( get_queried_object_id() );
	}

	return xin_format_key( get_query_var( 'xin_format' ) );
}

/**
 * @return bool
 */
function xin_in_comics() {
	return 'comic' === xin_current_section();
}

/**
 * Главная раздела.
 *
 * @param string $format Формат.
 * @return string
 */
function xin_section_home_link( $format = 'text' ) {
	return 'comic' === $format
		? home_url( user_trailingslashit( XIN_COMICS_BASE ) )
		: home_url( '/' );
}

/**
 * Каталог раздела.
 *
 * @param string $format Формат.
 * @return string
 */
function xin_section_catalog_link( $format = 'text' ) {
	return 'comic' === $format
		? home_url( user_trailingslashit( XIN_COMICS_BASE . '/' . XIN_COMICS_CATALOG ) )
		: (string) get_post_type_archive_link( 'novel' );
}

/**
 * Лента обновлений раздела.
 *
 * @param string $format Формат.
 * @return string
 */
function xin_section_updates_link( $format = 'text' ) {
	$base = (string) get_post_type_archive_link( 'chapter' );

	return 'comic' === $format ? add_query_arg( 'format', 'comic', $base ) : $base;
}

/* -------------------------------------------------------------------------
 * Запросы
 * ---------------------------------------------------------------------- */

/**
 * Условие меты для формата.
 *
 * Тайтлы, заведённые до появления раздела комиксов, меты не имеют вовсе —
 * поэтому «текст» — это «не комикс», а не «равно text».
 *
 * @param string $format `text`, `comic` или `any`.
 * @return array
 */
function xin_format_meta_clause( $format ) {
	if ( 'any' === $format ) {
		return array();
	}

	if ( 'comic' === $format ) {
		return array(
			array(
				'key'   => '_xin_format',
				'value' => 'comic',
			),
		);
	}

	return array(
		array(
			'relation' => 'OR',
			array(
				'key'     => '_xin_format',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_xin_format',
				'value'   => 'comic',
				'compare' => '!=',
			),
		),
	);
}

/**
 * Единственная точка, где раздел подмешивается в основной запрос.
 *
 * @param WP_Query $query Запрос.
 * @return void
 */
function xin_format_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_titles   = $query->is_post_type_archive( 'novel' ) || $query->is_tax( array( 'genre', 'novel_tag', 'novel_status' ) );
	$is_chapters = $query->is_post_type_archive( 'chapter' );

	if ( ! $is_titles && ! $is_chapters ) {
		return;
	}

	$format = xin_format_key( $query->get( 'xin_format' ) );
	$clause = xin_format_meta_clause( $format );

	if ( ! $clause ) {
		return;
	}

	$meta_query = (array) $query->get( 'meta_query' );
	$query->set( 'meta_query', array_merge( $meta_query, $clause ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
}
add_action( 'pre_get_posts', 'xin_format_pre_get_posts' );

/**
 * Лента обновлений читает формат из строки запроса: /updates/?format=comic.
 *
 * @param WP_Query $query Запрос.
 * @return void
 */
function xin_format_from_request( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'chapter' ) ) {
		return;
	}

	if ( isset( $_GET['format'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query->set( 'xin_format', xin_format_key( sanitize_key( wp_unslash( $_GET['format'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
add_action( 'pre_get_posts', 'xin_format_from_request', 9 );

/* -------------------------------------------------------------------------
 * Адреса
 * ---------------------------------------------------------------------- */

/**
 * Переменные запроса раздела.
 *
 * @param array $vars Переменные.
 * @return array
 */
function xin_format_query_vars( $vars ) {
	$vars[] = 'xin_format';
	$vars[] = 'xin_view';

	return $vars;
}
add_filter( 'query_vars', 'xin_format_query_vars' );

/**
 * Адреса раздела комиксов.
 *
 * Порядок правил важен и читается сверху вниз: сначала каталог, потом глава из
 * двух сегментов, потом тайтл из одного. Слаг `catalog` тем самым занят —
 * `xin_reserved_comic_slugs()` не даёт завести тайтл с таким именем.
 *
 * @return void
 */
function xin_comics_rewrite() {
	$base = XIN_COMICS_BASE;

	add_rewrite_rule(
		'^' . $base . '/?$',
		'index.php?xin_format=comic&xin_view=home',
		'top'
	);

	add_rewrite_rule(
		'^' . $base . '/' . XIN_COMICS_CATALOG . '/page/([0-9]+)/?$',
		'index.php?post_type=novel&xin_format=comic&paged=$matches[1]',
		'top'
	);

	add_rewrite_rule(
		'^' . $base . '/' . XIN_COMICS_CATALOG . '/?$',
		'index.php?post_type=novel&xin_format=comic',
		'top'
	);

	add_rewrite_rule(
		'^' . $base . '/([^/]+)/([^/]+)/?$',
		'index.php?chapter=$matches[2]&xin_novel_slug=$matches[1]&xin_format=comic',
		'top'
	);

	add_rewrite_rule(
		'^' . $base . '/([^/]+)/?$',
		'index.php?novel=$matches[1]&xin_format=comic',
		'top'
	);
}
add_action( 'init', 'xin_comics_rewrite', 21 );

/**
 * Слаги, занятые служебными адресами раздела.
 *
 * @return string[]
 */
function xin_reserved_comic_slugs() {
	return array( XIN_COMICS_CATALOG );
}

/**
 * Не даёт тайтлу занять служебный слаг раздела.
 *
 * @param string $slug      Предложенный слаг.
 * @param int    $post_id   Запись.
 * @param string $status    Статус.
 * @param string $post_type Тип записи.
 * @return string
 */
function xin_guard_comic_slug( $slug, $post_id, $status, $post_type ) {
	if ( 'novel' === $post_type && in_array( $slug, xin_reserved_comic_slugs(), true ) ) {
		return $slug . '-2';
	}

	return $slug;
}
add_filter( 'wp_unique_post_slug', 'xin_guard_comic_slug', 10, 4 );

/**
 * Комикс живёт по адресу /comics/<слаг>/, а не /novels/<слаг>/.
 *
 * @param string  $url  Адрес.
 * @param WP_Post $post Запись.
 * @return string
 */
function xin_comic_permalink( $url, $post ) {
	if ( ! $post instanceof WP_Post || 'novel' !== $post->post_type || ! xin_is_comic( $post->ID ) ) {
		return $url;
	}

	return home_url( user_trailingslashit( XIN_COMICS_BASE . '/' . $post->post_name ) );
}
add_filter( 'post_type_link', 'xin_comic_permalink', 10, 2 );

/**
 * Глава комикса — на сегмент глубже тайтла.
 *
 * Свой фильтр нужен потому, что вложенные адреса глав в `inc/permalinks.php`
 * включаются отдельной константой и всегда ведут в /novels/. У комикса выбора
 * нет: его тайтл лежит в /comics/, и глава обязана лежать там же.
 *
 * @param string  $url  Адрес.
 * @param WP_Post $post Запись.
 * @return string
 */
function xin_comic_chapter_permalink( $url, $post ) {
	if ( ! $post instanceof WP_Post || 'chapter' !== $post->post_type ) {
		return $url;
	}

	$novel_id = xin_chapter_novel_id( $post->ID );

	if ( ! $novel_id || ! xin_is_comic( $novel_id ) ) {
		return $url;
	}

	$novel = get_post( $novel_id );

	if ( ! $novel ) {
		return $url;
	}

	return home_url( user_trailingslashit( XIN_COMICS_BASE . '/' . $novel->post_name . '/' . $post->post_name ) );
}
add_filter( 'post_type_link', 'xin_comic_chapter_permalink', 20, 2 );

/* -------------------------------------------------------------------------
 * Шаблоны
 * ---------------------------------------------------------------------- */

/**
 * Раздел выбирает шаблон.
 *
 * Формат — это мета, а WordPress по мете шаблон не подбирает, поэтому подбор
 * здесь. Ветвление снаружи, а не внутри `single-novel.php`: страница комикса и
 * страница новеллы расходятся почти целиком, и `if` через весь файл читался бы
 * хуже двух отдельных шаблонов.
 *
 * @param string $template Найденный шаблон.
 * @return string
 */
function xin_format_template( $template ) {
	if ( 'home' === get_query_var( 'xin_view' ) && xin_in_comics() ) {
		$found = locate_template( 'comics-home.php' );

		return $found ? $found : $template;
	}

	if ( is_singular( 'novel' ) && xin_is_comic( get_queried_object_id() ) ) {
		$found = locate_template( 'single-comic.php' );

		return $found ? $found : $template;
	}

	if ( is_singular( 'chapter' ) && 'comic' === xin_chapter_format( get_queried_object_id() ) ) {
		$found = locate_template( 'single-comic-chapter.php' );

		return $found ? $found : $template;
	}

	if ( is_post_type_archive( 'novel' ) && xin_in_comics() ) {
		$found = locate_template( 'archive-comic.php' );

		return $found ? $found : $template;
	}

	return $template;
}
add_filter( 'template_include', 'xin_format_template' );

/**
 * Главная раздела — не «страница не найдена».
 *
 * Правило `/comics/` не ведёт ни к одной записи, поэтому WordPress по умолчанию
 * считает такой запрос неудачным и отдаёт 404 вместе с заголовком.
 *
 * @return void
 */
function xin_comics_home_is_found() {
	global $wp_query;

	if ( 'home' === get_query_var( 'xin_view' ) && xin_in_comics() ) {
		$wp_query->is_404 = false;
		status_header( 200 );
	}
}
add_action( 'template_redirect', 'xin_comics_home_is_found', 1 );

/* -------------------------------------------------------------------------
 * Страницы комикса
 * ---------------------------------------------------------------------- */

/**
 * Страницы главы комикса — вложения по порядку.
 *
 * @param int $chapter_id Глава.
 * @return int[] ID вложений.
 */
function xin_comic_pages( $chapter_id ) {
	$pages = get_post_meta( $chapter_id, '_xin_pages', true );

	if ( ! is_array( $pages ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', $pages ) ) );
}

/**
 * Сколько страниц в главе.
 *
 * @param int $chapter_id Глава.
 * @return int
 */
function xin_comic_page_count( $chapter_id ) {
	return count( xin_comic_pages( $chapter_id ) );
}

/**
 * Направление чтения тайтла: вебтун лентой или манга постранично справа налево.
 *
 * @param int $novel_id Тайтл.
 * @return string `strip`, `ltr` или `rtl`.
 */
function xin_comic_direction( $novel_id ) {
	$value = get_post_meta( $novel_id, '_xin_direction', true );

	return in_array( $value, array( 'strip', 'ltr', 'rtl' ), true ) ? $value : 'strip';
}

/* -------------------------------------------------------------------------
 * Зеркало формата на главах
 * ---------------------------------------------------------------------- */

/**
 * Глава наследует формат тайтла.
 *
 * @param int $chapter_id Глава.
 * @return void
 */
function xin_sync_chapter_format( $chapter_id ) {
	if ( wp_is_post_revision( $chapter_id ) || wp_is_post_autosave( $chapter_id ) ) {
		return;
	}

	$novel_id = xin_chapter_novel_id( $chapter_id );

	if ( ! $novel_id ) {
		return;
	}

	update_post_meta( $chapter_id, '_xin_format', xin_novel_format( $novel_id ) );
}
add_action( 'save_post_chapter', 'xin_sync_chapter_format', 20 );

/**
 * Сменили формат тайтла — переписываем его главам.
 *
 * @param int $novel_id Тайтл.
 * @return void
 */
function xin_sync_novel_chapters_format( $novel_id ) {
	if ( wp_is_post_revision( $novel_id ) || wp_is_post_autosave( $novel_id ) ) {
		return;
	}

	$format = xin_novel_format( $novel_id );

	foreach ( xin_chapter_ids( $novel_id, 'ASC' ) as $chapter_id ) {
		update_post_meta( $chapter_id, '_xin_format', $format );
	}
}
add_action( 'save_post_novel', 'xin_sync_novel_chapters_format', 20 );

/**
 * Переключатель разделов в шапке.
 *
 * Ведёт на главную раздела, а не на каталог: переключение разделов — это смена
 * витрины целиком, и попасть при этом сразу в список тайтлов было бы странно.
 *
 * @return void
 */
function xin_section_switch() {
	$current = xin_current_section();
	?>
	<div class="xin-sections" role="group" aria-label="<?php esc_attr_e( 'Раздел', 'xin-com' ); ?>">
		<?php foreach ( xin_formats() as $key => $format ) : ?>
			<a
				class="xin-sections__item<?php echo $key === $current ? ' is-current' : ''; ?>"
				href="<?php echo esc_url( xin_section_home_link( $key ) ); ?>"
				<?php echo $key === $current ? ' aria-current="page"' : ''; ?>
			>
				<?php xin_the_icon( $format['icon'] ); ?>
				<span><?php echo esc_html( $format['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}
