<?php
/**
 * Адреса глав.
 *
 * Слаг главы собирался из одного названия, а названия глав повторяются: во
 * втором проекте «Тишина» превращалась в `tishina-2`, в третьем — в `tishina-3`.
 * Адрес переставал что-либо говорить и зависел от того, кого завели раньше.
 *
 * Теперь слаг — `chapter-12-tishina`, а сама глава живёт внутри своего проекта:
 * /novels/stantsiya-tishina/chapter-12-tishina/. Название главы обязано быть
 * уникальным только внутри проекта, а не на всей площадке.
 *
 * Старые адреса /read/… остаются рабочими: WordPress помнит прежний слаг, а
 * префикс перехватывается и уводит редиректом на новый адрес.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_CHAPTER_BASE = 'novels';

/**
 * Включена ли новая схема адресов глав.
 *
 * Переезд меняет адрес каждой главы на сайте, поэтому по умолчанию он выключен:
 * боевую площадку нельзя переводить молча, вместе с обычным обновлением темы.
 * Включается константой в wp-config.php или фильтром:
 *
 *     define( 'XIN_NESTED_CHAPTER_URLS', true );
 *
 * После включения зайдите в Настройки → Постоянные ссылки и нажмите
 * «Сохранить», чтобы правила перестроились, и пересоберите слаги действием
 * «Пересобрать слаги» в массовом управлении.
 */
function xin_nested_chapter_urls() {
	$on = defined( 'XIN_NESTED_CHAPTER_URLS' ) && XIN_NESTED_CHAPTER_URLS;

	return (bool) apply_filters( 'xin_nested_chapter_urls', $on );
}

/**
 * Собирает слаг главы: номер плюс название.
 *
 * @param string $title  Название главы.
 * @param float  $number Номер главы.
 * @return string Слаг без проверки уникальности.
 */
function xin_build_chapter_slug( $title, $number ) {
	$parts = array();

	if ( '' !== $number && null !== $number ) {
		// 12 вместо 12.0, но 12.5 остаётся 12-5.
		$num     = rtrim( rtrim( number_format( (float) $number, 2, '.', '' ), '0' ), '.' );
		$parts[] = 'chapter-' . str_replace( '.', '-', $num );
	}

	$name = sanitize_title( $title );
	if ( $name ) {
		$parts[] = $name;
	}

	$slug = implode( '-', $parts );

	return $slug ? $slug : 'chapter';
}

/**
 * Ставит главе правильный слаг при сохранении.
 *
 * Только когда слаг ещё не задан руками: если автор придумал свой адрес, мы его
 * не переписываем.
 *
 * @param array $data    Данные записи для базы.
 * @param array $postarr Исходный массив.
 * @return array
 */
function xin_chapter_slug_on_save( $data, $postarr ) {
	if ( ! xin_nested_chapter_urls() || 'chapter' !== $data['post_type'] || 'auto-draft' === $data['post_status'] ) {
		return $data;
	}

	$id     = isset( $postarr['ID'] ) ? (int) $postarr['ID'] : 0;
	$number = $id ? get_post_meta( $id, '_xin_number', true ) : '';

	if ( '' === $number && isset( $postarr['meta_input']['_xin_number'] ) ) {
		$number = $postarr['meta_input']['_xin_number'];
	}

	$wanted = xin_build_chapter_slug( $data['post_title'], $number );

	// Слаг, который уже совпадает по смыслу, не трогаем: иначе каждое сохранение
	// плодило бы записи о старых адресах.
	$current = isset( $data['post_name'] ) ? $data['post_name'] : '';
	if ( $current && 0 === strpos( $current, $wanted ) ) {
		return $data;
	}

	/*
	 * WordPress считает уникальность до этого фильтра, а мы ставим слаг после —
	 * значит последнее слово за нами, и разводить совпадения внутри проекта тоже
	 * приходится здесь.
	 */
	$novel = isset( $postarr['meta_input']['_xin_novel'] )
		? (int) $postarr['meta_input']['_xin_novel']
		: ( $id ? (int) get_post_meta( $id, '_xin_novel', true ) : 0 );

	$data['post_name'] = xin_free_chapter_slug( $wanted, $id, $novel );

	return $data;
}

/**
 * Свободный слаг внутри проекта.
 *
 * Одинаковые названия в разных проектах остаются одинаковыми — их разводит
 * сегмент проекта в адресе. Совпадение внутри одного проекта получает номер.
 *
 * @param string $slug  Желаемый слаг.
 * @param int    $id    Глава, которую сохраняем (0 у новой).
 * @param int    $novel Проект.
 * @return string
 */
function xin_free_chapter_slug( $slug, $id, $novel ) {
	global $wpdb;

	if ( ! $novel ) {
		return $slug;
	}

	$try = $slug;
	$n   = 1;

	while ( $n < 200 ) {
		$taken = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_xin_novel'
			 WHERE p.post_type = 'chapter' AND p.post_name = %s AND p.ID != %d AND m.meta_value = %s
			 LIMIT 1",
			$try,
			$id,
			(string) $novel
		) );

		if ( ! $taken ) {
			return $try;
		}

		$n++;
		$try = $slug . '-' . $n;
	}

	return $try;
}
add_filter( 'wp_insert_post_data', 'xin_chapter_slug_on_save', 10, 2 );


/* -------------------------------------------------------------------------
 * Адрес
 * ---------------------------------------------------------------------- */

/**
 * Ссылка на главу: внутри её проекта.
 *
 * @param string  $link Ссылка.
 * @param WP_Post $post Запись.
 * @return string
 */
function xin_chapter_permalink( $link, $post ) {
	if ( ! xin_nested_chapter_urls() || ! $post || 'chapter' !== $post->post_type || ! get_option( 'permalink_structure' ) ) {
		return $link;
	}

	$novel = get_post( (int) get_post_meta( $post->ID, '_xin_novel', true ) );

	if ( ! $novel || 'novel' !== $novel->post_type ) {
		return $link;
	}

	return home_url( user_trailingslashit( XIN_CHAPTER_BASE . '/' . $novel->post_name . '/' . $post->post_name ) );
}
add_filter( 'post_type_link', 'xin_chapter_permalink', 10, 2 );

/**
 * Правило разбора: /novels/проект/глава/.
 *
 * Правило проекта из коробки короче и совпало бы первым, поэтому наше идёт в
 * начало списка.
 */
function xin_chapter_rewrite() {
	if ( ! xin_nested_chapter_urls() ) {
		return;
	}

	add_rewrite_rule(
		'^' . XIN_CHAPTER_BASE . '/([^/]+)/([^/]+)/?$',
		'index.php?chapter=$matches[2]&xin_novel_slug=$matches[1]',
		'top'
	);
}
add_action( 'init', 'xin_chapter_rewrite', 20 );

function xin_chapter_query_var( $vars ) {
	$vars[] = 'xin_novel_slug';
	return $vars;
}
add_filter( 'query_vars', 'xin_chapter_query_var' );

/**
 * Достаёт нужную главу, а не первую попавшуюся с таким слагом.
 *
 * Слаг уникален внутри проекта, поэтому «chapter-12-tishina» есть сразу в
 * нескольких: искать только по слагу — значит всегда открывать одну и ту же.
 * Сегмент проекта из адреса решает, чью именно главу показать.
 *
 * @param array $vars Разобранные переменные запроса.
 * @return array
 */
function xin_chapter_resolve( $vars ) {
	if ( ! xin_nested_chapter_urls() || empty( $vars['chapter'] ) || empty( $vars['xin_novel_slug'] ) ) {
		return $vars;
	}

	$novel = get_page_by_path( $vars['xin_novel_slug'], OBJECT, 'novel' );

	if ( ! $novel ) {
		return $vars;
	}

	$found = get_posts( array(
		'post_type'      => 'chapter',
		'name'           => $vars['chapter'],
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) $novel->ID ),
		),
	) );

	if ( ! $found ) {
		return $vars;
	}

	return array( 'p' => (int) $found[0], 'post_type' => 'chapter' );
}
add_filter( 'request', 'xin_chapter_resolve' );

/**
 * Старый адрес /read/слаг/ уводит на новый.
 *
 * Тип записи по-прежнему зарегистрирован с префиксом read, поэтому WordPress
 * доводит запрос до главы сам — остаётся отправить читателя на канонический
 * адрес, чтобы ссылка из закладок или поисковика не осталась второй копией.
 */
function xin_chapter_redirect_old() {
	if ( ! xin_nested_chapter_urls() || is_admin() || ! is_singular( 'chapter' ) ) {
		return;
	}

	$correct = get_permalink();

	if ( ! $correct ) {
		return;
	}

	/*
	 * Сравниваем раскодированные пути. Кириллица в слаге приезжает то как
	 * %d1%82, то как %D1%82 — в зависимости от клиента, — и посимвольное
	 * сравнение отправляло бы часть читателей в лишний редирект.
	 */
	$here = rawurldecode( (string) wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ) );
	$want = rawurldecode( (string) wp_parse_url( $correct, PHP_URL_PATH ) );

	if ( untrailingslashit( $here ) === untrailingslashit( $want ) ) {
		return;
	}

	wp_safe_redirect( $correct, 301 );
	exit;
}
add_action( 'template_redirect', 'xin_chapter_redirect_old', 5 );
