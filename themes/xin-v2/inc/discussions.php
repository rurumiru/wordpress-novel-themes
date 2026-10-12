<?php
/**
 * Обсуждения.
 *
 * Своя ветка целиком: разметка, порядок, отправка и отметки «полезно».
 * Стандартные comment_form() и wp_list_comments() не используются —
 * они тянут за собой чужую разметку и перезагрузку страницы.
 *
 * Хранилище остаётся штатным (таблица комментариев): так работают модерация,
 * антиспам и выгрузка, а UI полностью наш.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Открыто ли обсуждение под записью.
 *
 * Для тайтлов и глав решает переключатель темы: у импортированного контента
 * `comment_status` почти всегда закрыт, и настройка «Комментарии к тайтлам и
 * главам» иначе ничего бы не включала. Для обычных записей и страниц
 * действует штатная настройка самой записи.
 */
function xv_discussion_open( $post_id ) {
	if ( ! xv_opt( 'discussions', true ) ) {
		return false;
	}

	if ( in_array( get_post_type( $post_id ), array( 'novel', 'chapter' ), true ) ) {
		return true;
	}

	return comments_open( $post_id );
}

/**
 * Ветка обсуждения.
 *
 * @param string $order new|old|top.
 */
function xv_comment_tree( $post_id, $order = 'new' ) {
	$comments = get_comments( array(
		'post_id' => (int) $post_id,
		'status'  => 'approve',
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC',
	) );

	$by_parent = array();

	foreach ( $comments as $comment ) {
		$by_parent[ (int) $comment->comment_parent ][] = $comment;
	}

	if ( empty( $by_parent[0] ) ) {
		return array();
	}

	$roots = $by_parent[0];

	if ( 'top' === $order ) {
		usort( $roots, function ( $a, $b ) {
			return xv_comment_likes( $b->comment_ID ) <=> xv_comment_likes( $a->comment_ID );
		} );
	} elseif ( 'new' === $order ) {
		$roots = array_reverse( $roots );
	}

	$tree = array();

	foreach ( $roots as $root ) {
		$tree[] = array(
			'comment'  => $root,
			// Ответы всегда по возрастанию: внутри ветки важен ход разговора,
			// а не свежесть.
			'children' => isset( $by_parent[ (int) $root->comment_ID ] ) ? $by_parent[ (int) $root->comment_ID ] : array(),
		);
	}

	return $tree;
}

function xv_comment_count( $post_id ) {
	return (int) get_comments( array(
		'post_id' => (int) $post_id,
		'status'  => 'approve',
		'count'   => true,
	) );
}

/**
 * Кто отметил реплику.
 *
 * Пустая мета приходит строкой, а не массивом: (array) '' даёт список из
 * одного пустого значения, и счётчик показывал единицу на нетронутых
 * комментариях.
 */
function xv_comment_likers( $comment_id ) {
	$likes = get_comment_meta( $comment_id, '_xin_likes', true );

	return is_array( $likes ) ? array_map( 'intval', $likes ) : array();
}

function xv_comment_likes( $comment_id ) {
	return count( xv_comment_likers( $comment_id ) );
}

function xv_comment_liked( $comment_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return in_array( (int) $user_id, xv_comment_likers( $comment_id ), true );
}

/**
 * Одна реплика.
 */
function xv_comment_card( $comment, $is_reply = false ) {
	$author_id = (int) $comment->user_id;
	$name      = $author_id ? get_the_author_meta( 'display_name', $author_id ) : $comment->comment_author;
	$likes     = xv_comment_likes( $comment->comment_ID );
	$liked     = xv_comment_liked( $comment->comment_ID );
	$own       = $author_id && get_current_user_id() === $author_id;
	$team      = $author_id && user_can($author_id, 'edit_posts');
	$role      = $author_id && $author_id === (int)get_post_field('post_author', $comment->comment_post_ID) ? __('Автор','xin-v2') : ($team ? __('Редактор','xin-v2') : __('Читатель','xin-v2'));
	?>
	<article class="xv-cmt<?php echo $is_reply ? ' is-reply' : ''; ?><?php echo $team ? ' is-team' : ''; ?><?php echo $own ? ' is-own' : ''; ?>" id="comment-<?php echo (int) $comment->comment_ID; ?>" data-xv-cmt="<?php echo (int) $comment->comment_ID; ?>">
		<div class="xv-cmt-avatar">
			<?php echo $author_id ? xv_avatar( $author_id, 34 ) : '<span class="xv-avatar" style="width:34px;height:34px;font-size:14px;">' . esc_html( mb_strtoupper( mb_substr( $name, 0, 1 ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<div class="xv-cmt-body">
			<header class="xv-cmt-head">
				<span class="xv-cmt-name"><?php echo esc_html( $name ); ?></span>
				<span class="xv-cmt-role"><?php echo esc_html($role); ?></span>
				<time class="xv-cmt-time" datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>"><?php echo esc_html( xv_ago( strtotime( $comment->comment_date_gmt . ' GMT' ) ) ); ?></time>
			</header>

			<div class="xv-cmt-text"><?php echo wp_kses_post( wpautop( $comment->comment_content ) ); ?></div>

			<footer class="xv-cmt-actions">
				<button type="button" class="xv-cmt-act<?php echo $liked ? ' is-on' : ''; ?>" data-xv-cmt-like="<?php echo (int) $comment->comment_ID; ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>">
					<?php xv_the_icon( 'heart', '', $liked ); ?>
					<span data-xv-cmt-likes><?php echo (int) $likes; ?></span>
				</button>

				<?php if ( ! $is_reply ) : ?>
					<button type="button" class="xv-cmt-act" data-xv-cmt-reply="<?php echo (int) $comment->comment_ID; ?>">
						<?php esc_html_e( 'Ответить', 'xin-v2' ); ?>
					</button>
				<?php endif; ?>

				<?php if ( $own || current_user_can( 'moderate_comments' ) ) : ?>
					<button type="button" class="xv-cmt-act is-quiet" data-xv-cmt-delete="<?php echo (int) $comment->comment_ID; ?>">
						<?php esc_html_e( 'Удалить', 'xin-v2' ); ?>
					</button>
				<?php endif; ?>
			</footer>
		</div>
	</article>
	<?php
}

/* -------------------------------------------------------------------------
 * REST
 * ---------------------------------------------------------------------- */

function xv_register_comment_routes() {
	register_rest_route( 'xin/v2', '/comment', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'post'   => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			'parent' => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
			'text'   => array( 'required' => true ),
		),
		'callback'            => 'xv_rest_add_comment',
	) );

	register_rest_route( 'xin/v2', '/comment/like', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'comment' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xv_rest_like_comment',
	) );

	register_rest_route( 'xin/v2', '/comment/delete', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'comment' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xv_rest_delete_comment',
	) );
}
add_action( 'rest_api_init', 'xv_register_comment_routes' );

function xv_rest_add_comment( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'post' );
	$parent  = (int) $request->get_param( 'parent' );
	$text    = trim( wp_strip_all_tags( (string) $request->get_param( 'text' ) ) );

	if ( ! xv_discussion_open( $post_id ) ) {
		return new WP_Error( 'xv_closed', __( 'Обсуждение закрыто.', 'xin-v2' ), array( 'status' => 403 ) );
	}

	if ( '' === $text ) {
		return new WP_Error( 'xv_empty', __( 'Пустой комментарий.', 'xin-v2' ), array( 'status' => 400 ) );
	}

	if ( mb_strlen( $text ) > 4000 ) {
		return new WP_Error( 'xv_long', __( 'Слишком длинно — до 4000 знаков.', 'xin-v2' ), array( 'status' => 400 ) );
	}

	// Ответ на ответ схлопываем на уровень выше: ветка глубже двух уровней
	// на телефоне превращается в лесенку из четырёх слов.
	if ( $parent ) {
		$parent_comment = get_comment( $parent );

		if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $post_id ) {
			$parent = 0;
		} elseif ( (int) $parent_comment->comment_parent ) {
			$parent = (int) $parent_comment->comment_parent;
		}
	}

	$user = wp_get_current_user();

	// Через wp_new_comment(), а не wp_insert_comment(): только этот путь
	// проходит wp_allow_comment() — защиту от повторов и флуда, стоп-слова,
	// настройки премодерации и антиспам-плагины. Прямая вставка с
	// comment_approved = 1 публиковала бы всё мимо модерации.
	$id = wp_new_comment(
		array(
			'comment_post_ID'      => $post_id,
			'comment_parent'       => $parent,
			'comment_content'      => $text,
			'comment_type'         => 'comment',
			'user_id'              => $user->ID,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url'   => $user->user_url,
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		$status = 'comment_duplicate' === $id->get_error_code() ? 409 : 429;

		return new WP_Error( 'xv_rejected', $id->get_error_message(), array( 'status' => $status ) );
	}

	if ( ! $id ) {
		return new WP_Error( 'xv_failed', __( 'Не получилось отправить.', 'xin-v2' ), array( 'status' => 500 ) );
	}

	$comment = get_comment( $id );

	// Комментарий на проверке в ленту не попадает: показать его только
	// автору — значит соврать остальным о том, что он уже виден.
	if ( '1' !== (string) $comment->comment_approved ) {
		return array(
			'id'     => (int) $id,
			'parent' => $parent,
			'held'   => true,
			'html'   => '',
			'count'  => xv_comment_count( $post_id ),
		);
	}

	ob_start();
	xv_comment_card( $comment, (bool) $parent );

	return array(
		'id'     => (int) $id,
		'parent' => $parent,
		'held'   => false,
		'html'   => ob_get_clean(),
		'count'  => xv_comment_count( $post_id ),
	);
}

function xv_rest_like_comment( WP_REST_Request $request ) {
	$comment_id = (int) $request->get_param( 'comment' );
	$comment    = get_comment( $comment_id );

	if ( ! $comment ) {
		return new WP_Error( 'xv_no_comment', __( 'Комментарий не найден.', 'xin-v2' ), array( 'status' => 404 ) );
	}

	$likes = xv_comment_likers( $comment_id );
	$user  = get_current_user_id();
	$key   = array_search( $user, $likes, true );

	if ( false !== $key ) {
		unset( $likes[ $key ] );
		$on = false;
	} else {
		$likes[] = $user;
		$on      = true;
	}

	update_comment_meta( $comment_id, '_xin_likes', array_values( $likes ) );

	return array( 'on' => $on, 'likes' => count( $likes ) );
}

function xv_rest_delete_comment( WP_REST_Request $request ) {
	$comment_id = (int) $request->get_param( 'comment' );
	$comment    = get_comment( $comment_id );

	if ( ! $comment ) {
		return new WP_Error( 'xv_no_comment', __( 'Комментарий не найден.', 'xin-v2' ), array( 'status' => 404 ) );
	}

	$own = (int) $comment->user_id && (int) $comment->user_id === get_current_user_id();

	if ( ! $own && ! current_user_can( 'moderate_comments' ) ) {
		return new WP_Error( 'xv_forbidden', __( 'Недостаточно прав.', 'xin-v2' ), array( 'status' => 403 ) );
	}

	wp_trash_comment( $comment_id );

	return array( 'deleted' => true, 'count' => xv_comment_count( (int) $comment->comment_post_ID ) );
}

/**
 * Штатная форма и список ядра нам не нужны — но плагины и виджеты всё ещё
 * могут их вызвать, поэтому просто не подключаем их скрипты.
 */
function xv_drop_core_comment_reply() {
	wp_deregister_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'xv_drop_core_comment_reply', 20 );
