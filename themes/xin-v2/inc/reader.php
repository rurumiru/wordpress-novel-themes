<?php
/**
 * Читалка: то, что ей нужно с сервера.
 *
 * Бесконечная прокрутка, закладки по абзацам и цитаты требуют двух вещей:
 * устойчивого адреса у каждого абзаца и способа получить следующую главу, не
 * перезагружая страницу.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_MARKS = '_xin_marks';

/* -------------------------------------------------------------------------
 * Абзацы
 * ---------------------------------------------------------------------- */

/**
 * Нумерует абзацы главы.
 *
 * Закладка и цитата должны пережить правку соседнего абзаца, поэтому номер
 * считается по порядку внутри главы, а не по содержимому: правка текста не
 * сдвигает ссылку, а вставка нового абзаца сдвигает только то, что ниже.
 */
function xv_number_paragraphs( $html ) {
	/* REST chapter responses pass through `the_content` after setup_postdata(),
	 * but WordPress does not mark a REST request as a singular page. Numbering
	 * them too keeps bookmarks and quote links working after chapters append. */
	$is_chapter_page = is_singular( 'chapter' );
	$is_chapter_rest = defined( 'REST_REQUEST' ) && REST_REQUEST && 'chapter' === get_post_type();

	if ( ( ! $is_chapter_page && ! $is_chapter_rest ) || is_feed() || is_admin() ) {
		return $html;
	}

	$n = 0;

	return preg_replace_callback(
		'~<p\b([^>]*)>~i',
		function ( $m ) use ( &$n ) {
			$n++;
			$attributes = preg_replace( '~\sdata-xv-p=("[^"]*"|\'[^\']*\')~i', '', $m[1] );
			$has_id     = preg_match( '~\sid=("[^"]*"|\'[^\']*\')~i', $attributes );
			$id         = $has_id ? '' : ' id="p' . $n . '"';

			return '<p' . $attributes . $id . ' data-xv-p="' . $n . '">';
		},
		$html
	);
}
add_filter( 'the_content', 'xv_number_paragraphs', 12 );

/* -------------------------------------------------------------------------
 * Закладки и цитаты
 * ---------------------------------------------------------------------- */

/**
 * Отметки читателя внутри глав: `chapter_id => [ [p, note, at], … ]`.
 */
function xv_marks( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$marks = get_user_meta( $user_id, XV_MARKS, true );

	return is_array( $marks ) ? $marks : array();
}

function xv_chapter_marks( $chapter_id, $user_id = 0 ) {
	$all = xv_marks( $user_id );

	return isset( $all[ (int) $chapter_id ] ) ? $all[ (int) $chapter_id ] : array();
}

/**
 * Ставит или снимает отметку. Повторный клик по тому же абзацу снимает её —
 * отдельной кнопки «удалить» в тексте быть не должно.
 */
function xv_toggle_mark( $chapter_id, $paragraph, $note = '' ) {
	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		return null;
	}

	$all     = xv_marks( $user_id );
	$chapter = (int) $chapter_id;
	$p       = (int) $paragraph;
	$list    = isset( $all[ $chapter ] ) ? $all[ $chapter ] : array();
	$on      = true;

	foreach ( $list as $i => $mark ) {
		if ( (int) $mark['p'] === $p ) {
			unset( $list[ $i ] );
			$on = false;
			break;
		}
	}

	if ( $on ) {
		$list[] = array(
			'p'    => $p,
			'note' => mb_substr( (string) $note, 0, 400 ),
			'at'   => time(),
		);
	}

	if ( $list ) {
		$all[ $chapter ] = array_values( $list );
	} else {
		unset( $all[ $chapter ] );
	}

	// Держим полсотни последних глав: закладки — это «куда вернуться», а не
	// архив за пять лет.
	if ( count( $all ) > 50 ) {
		$all = array_slice( $all, -50, null, true );
	}

	update_user_meta( $user_id, XV_MARKS, $all );

	return $on;
}

/* -------------------------------------------------------------------------
 * REST
 * ---------------------------------------------------------------------- */

function xv_register_reader_routes() {
	register_rest_route( 'xin/v2', '/chapter', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xv_rest_chapter',
	) );

	register_rest_route( 'xin/v2', '/view', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'args'                => array(
			'chapter' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => function ( WP_REST_Request $request ) {
			$chapter = (int) $request->get_param( 'chapter' );
			$post    = get_post( $chapter );

			if ( ! $post || 'chapter' !== $post->post_type || 'publish' !== $post->post_status ) {
				return new WP_Error( 'xv_no_chapter', __( 'Главы нет.', 'xin-v2' ), array( 'status' => 404 ) );
			}

			if ( xv_chapter_lock( $chapter )['locked'] ) {
				return new WP_Error( 'xv_locked_chapter', __( 'Глава пока закрыта.', 'xin-v2' ), array( 'status' => 403 ) );
			}

			xv_bump_view( $chapter );

			if ( is_user_logged_in() ) {
				xv_track_reading( $chapter );
			}

			return array( 'recorded' => true );
		},
	) );

	register_rest_route( 'xin/v2', '/mark', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'chapter'   => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'paragraph' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'note'      => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
		),
		'callback'            => function ( WP_REST_Request $request ) {
			$on = xv_toggle_mark(
				$request->get_param( 'chapter' ),
				$request->get_param( 'paragraph' ),
				$request->get_param( 'note' )
			);

			return array( 'on' => (bool) $on );
		},
	) );
}
add_action( 'rest_api_init', 'xv_register_reader_routes' );

/**
 * Следующая глава для бесконечной прокрутки.
 *
 * Отдаёт готовый текст, а не заготовку: разметку главы знает тема, и
 * повторять её в JS означало бы держать два описания одного и того же.
 * Закрытая глава текста не отдаёт — здесь тот же замок, что и на странице.
 */
function xv_rest_chapter( WP_REST_Request $request ) {
	$id   = (int) $request->get_param( 'id' );
	$post = get_post( $id );

	if ( ! $post || 'chapter' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'xv_no_chapter', __( 'Главы нет.', 'xin-v2' ), array( 'status' => 404 ) );
	}

	$lock = xv_chapter_lock( $id );
	$near = xv_adjacent_chapters( $id );
	$size = xv_chapter_size( $id );

	if ( $lock['locked'] ) {
		return array(
			'id'     => $id,
			'title'  => get_the_title( $post ),
			'number' => xv_chapter_number( $id ),
			'url'    => get_permalink( $post ),
			'locked' => true,
			'html'   => '',
			'next'   => $near['next'] ? (int) $near['next']->ID : 0,
		);
	}

	$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );

	$html = apply_filters( 'the_content', $post->post_content );

	wp_reset_postdata();

	return array(
		'id'      => $id,
		'title'   => get_the_title( $post ),
		'number'  => xv_chapter_number( $id ),
		'url'     => get_permalink( $post ),
		'prev'    => $near['prev'] ? (int) $near['prev']->ID : 0,
		'prevUrl' => $near['prev'] ? get_permalink( $near['prev'] ) : '',
		'prevTitle' => $near['prev'] ? get_the_title( $near['prev'] ) : '',
		'locked'  => false,
		'html'    => $html,
		'marks'   => array_column( xv_chapter_marks( $id ), 'p' ),
		'words'   => (int) $size['words'],
		'minutes' => (int) $size['minutes'],
		'next'    => $near['next'] ? (int) $near['next']->ID : 0,
		'nextUrl' => $near['next'] ? get_permalink( $near['next'] ) : '',
		'nextTitle' => $near['next'] ? get_the_title( $near['next'] ) : '',
		'index'   => (int) $near['index'],
		'total'   => (int) $near['total'],
	);
}

/* -------------------------------------------------------------------------
 * Данные для скрипта
 * ---------------------------------------------------------------------- */

/**
 * Всё, что читалке нужно знать о текущей главе.
 */
function xv_reader_data() {
	$id   = get_queried_object_id();
	$near = xv_adjacent_chapters( $id );

	return array(
		'chapter'  => $id,
		'novel'    => (int) get_post_meta( $id, '_xin_novel', true ),
		'prev'     => $near['prev'] ? (int) $near['prev']->ID : 0,
		'next'     => $near['next'] ? (int) $near['next']->ID : 0,
		'index'    => (int) $near['index'],
		'total'    => (int) $near['total'],
		'marks'    => array_column( xv_chapter_marks( $id ), 'p' ),
		'loggedIn' => is_user_logged_in(),
		'speech'   => (bool) xv_opt( 'reader_speech', true ),
		'endless'  => false,
		'i18n'     => array(
			'left'      => __( 'осталось', 'xin-v2' ),
			'min'       => __( 'мин', 'xin-v2' ),
			'loading'   => __( 'Загружаем следующую главу…', 'xin-v2' ),
			'last'      => __( 'Это последняя глава', 'xin-v2' ),
			'locked'    => __( 'Следующая глава в раннем доступе', 'xin-v2' ),
			'failed'    => __( 'Не получилось загрузить. Обновите страницу.', 'xin-v2' ),
			'mark'      => __( 'Закладка', 'xin-v2' ),
			'unmark'    => __( 'Убрать закладку', 'xin-v2' ),
			'copy'      => __( 'Скопировать', 'xin-v2' ),
			'copied'    => __( 'Скопировано', 'xin-v2' ),
			'speakOn'   => __( 'Читать вслух', 'xin-v2' ),
			'speakOff'  => __( 'Остановить чтение', 'xin-v2' ),
			'noVoice'   => __( 'Браузер не умеет читать вслух', 'xin-v2' ),
		),
	);
}
