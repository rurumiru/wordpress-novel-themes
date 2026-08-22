<?php
/**
 * Обсуждения — необязательный модуль, по умолчанию выключен.
 *
 * Внутри это комментарии WordPress, но снаружи ничего вордпрессовского:
 * своя разметка, свои поля, ответы на один уровень, спойлеры и отметки
 * «полезно».
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_TALK_LIKES = '_xin_likes';

function xin_discussions_on() {
	return (bool) get_theme_mod( 'xin_discussions', false );
}

function xin_discussions_here() {
	if ( ! xin_discussions_on() ) {
		return false;
	}

	return is_singular( array( 'novel', 'chapter', 'post' ) );
}

function xin_comments_open( $open, $post_id ) {
	if ( ! xin_discussions_on() ) {
		return false;
	}

	return in_array( get_post_type( $post_id ), array( 'novel', 'chapter', 'post' ), true );
}
add_filter( 'comments_open', 'xin_comments_open', 20, 2 );

function xin_comments_array( $comments ) {
	return xin_discussions_on() ? $comments : array();
}
add_filter( 'comments_array', 'xin_comments_array', 20 );

function xin_comments_number( $count ) {
	return xin_discussions_on() ? $count : 0;
}
add_filter( 'get_comments_number', 'xin_comments_number', 20 );

function xin_talk_count( $post_id ) {
	return xin_discussions_on() ? (int) get_comments_number( $post_id ) : 0;
}

function xin_talk_url( $post_id ) {
	return get_permalink( $post_id ) . '#xin-talk';
}

function xin_talk_likes( $comment_id ) {
	return (int) get_comment_meta( $comment_id, XIN_TALK_LIKES, true );
}

function xin_talk_allowed_html() {
	return array(
		'strong'     => array(),
		'em'         => array(),
		'b'          => array(),
		'i'          => array(),
		'br'         => array(),
		'p'          => array(),
		'a'          => array( 'href' => array(), 'class' => array() ),
		'blockquote' => array( 'class' => array() ),
		'ins'        => array(),
		'del'        => array(),
		'span'       => array(
			'class'            => array(),
			'data-xin-spoiler' => array(),
			'tabindex'         => array(),
			'role'             => array(),
		),
	);
}

function xin_talk_format( $text ) {
	$text = preg_replace_callback(
		'/\[quote\](.*?)\[\/quote\]/su',
		static function ( $m ) {
			$inner = $m[1];
			$inner = preg_replace( '/\[ins\](.*?)\[\/ins\]/su', '<ins>$1</ins>', $inner );
			$inner = preg_replace( '/\[del\](.*?)\[\/del\]/su', '<del>$1</del>', $inner );
			$inner = preg_replace(
				'/\[anchor\](paragraph-\d+)\[\/anchor\]/',
				'<a class="xin-talk__anchor" href="#$1">¶</a>',
				$inner
			);
			return '<blockquote class="xin-talk__quote">' . $inner . '</blockquote>';
		},
		$text
	);

	$text = preg_replace( '/\|\|(.+?)\|\|/su', '<span class="xin-talk__spoiler" data-xin-spoiler tabindex="0" role="button">$1</span>', $text );
	$text = preg_replace( '/(?<!\w)\*\*(.+?)\*\*(?!\w)/su', '<strong>$1</strong>', $text );
	$text = preg_replace( '/(?<!\w)_(.+?)_(?!\w)/su', '<em>$1</em>', $text );

	return wp_kses( $text, xin_talk_allowed_html() );
}
add_filter( 'comment_text', 'xin_talk_format', 30 );

function xin_talk_fields( $fields ) {
	unset( $fields['url'] );

	return $fields;
}
add_filter( 'comment_form_default_fields', 'xin_talk_fields' );

function xin_talk_must_log_in( $data ) {
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'Чтобы участвовать в обсуждении, войдите в аккаунт.', 'xi-novels' ), '', array( 'back_link' => true ) );
	}

	return $data;
}
add_filter( 'preprocess_comment', 'xin_talk_must_log_in' );

function xin_talk_rest() {
	register_rest_route( 'xin/v1', '/like', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
		),
		'callback'            => 'xin_talk_like',
	) );
}
add_action( 'rest_api_init', 'xin_talk_rest' );

function xin_talk_like( $request ) {
	$comment_id = absint( $request['id'] );
	$comment    = get_comment( $comment_id );

	if ( ! $comment ) {
		return new WP_Error( 'xin_no_comment', __( 'Комментарий не найден.', 'xi-novels' ), array( 'status' => 404 ) );
	}

	$user_id = get_current_user_id();
	$voters  = get_comment_meta( $comment_id, XIN_TALK_LIKES . '_by', true );
	$voters  = is_array( $voters ) ? $voters : array();

	if ( in_array( $user_id, $voters, true ) ) {
		$voters = array_values( array_diff( $voters, array( $user_id ) ) );
	} else {
		$voters[] = $user_id;
	}

	update_comment_meta( $comment_id, XIN_TALK_LIKES . '_by', $voters );
	update_comment_meta( $comment_id, XIN_TALK_LIKES, count( $voters ) );

	return array(
		'likes' => count( $voters ),
		'mine'  => in_array( $user_id, $voters, true ),
	);
}

function xin_talk_liked( $comment_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$voters  = get_comment_meta( $comment_id, XIN_TALK_LIKES . '_by', true );

	return is_array( $voters ) && in_array( $user_id, $voters, true );
}

function xin_talk_walk( $comments, $depth = 0 ) {
	foreach ( $comments as $comment ) {
		$author_id = (int) $comment->user_id;
		$is_author = $author_id && (int) get_post_field( 'post_author', $comment->comment_post_ID ) === $author_id;
		$likes     = xin_talk_likes( $comment->comment_ID );
		?>
		<li class="xin-talk__item<?php echo $depth ? ' is-reply' : ''; ?>" id="talk-<?php echo (int) $comment->comment_ID; ?>">
			<div class="xin-talk__avatar"><?php echo get_avatar( $comment, 40 ); ?></div>

			<div class="xin-talk__body">
				<div class="xin-talk__head">
					<b><?php echo esc_html( $comment->comment_author ); ?></b>
					<?php if ( $is_author ) : ?>
						<span class="xin-badge xin-badge--primary"><?php esc_html_e( 'автор', 'xi-novels' ); ?></span>
					<?php elseif ( $author_id && user_can( $author_id, 'edit_others_posts' ) ) : ?>
						<span class="xin-badge"><?php esc_html_e( 'команда', 'xi-novels' ); ?></span>
					<?php elseif ( $author_id && xin_user_is_plus( $author_id ) ) : ?>
						<span class="xin-badge xin-badge--gold">PLUS</span>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>"><?php echo esc_html( xin_ago( strtotime( $comment->comment_date_gmt ) ) ); ?></time>
				</div>

				<div class="xin-talk__text"><?php echo wp_kses_post( xin_talk_format( $comment->comment_content ) ); ?></div>

				<div class="xin-talk__tools">
					<button type="button" class="xin-talk__like<?php echo xin_talk_liked( $comment->comment_ID ) ? ' is-on' : ''; ?>" data-xin-like="<?php echo (int) $comment->comment_ID; ?>">
						<?php xin_the_icon( 'heart' ); ?><span><?php echo (int) $likes; ?></span>
					</button>
					<?php if ( ! $depth && is_user_logged_in() ) : ?>
						<button type="button" class="xin-talk__reply" data-xin-reply="<?php echo (int) $comment->comment_ID; ?>" data-name="<?php echo esc_attr( $comment->comment_author ); ?>">
							<?php esc_html_e( 'Ответить', 'xi-novels' ); ?>
						</button>
					<?php endif; ?>
				</div>

				<?php
				$children = $comment->get_children( array( 'format' => 'flat', 'status' => 'approve' ) );
				if ( $children ) :
					?>
					<ul class="xin-talk__list xin-talk__list--nested">
						<?php xin_talk_walk( $children, $depth + 1 ); ?>
					</ul>
				<?php endif; ?>
			</div>
		</li>
		<?php
	}
}

function xin_talk_render( $post_id = 0 ) {
	if ( ! xin_discussions_on() ) {
		return;
	}

	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! comments_open( $post_id ) ) {
		return;
	}

	$comments = get_comments( array(
		'post_id'  => $post_id,
		'status'   => 'approve',
		'parent'   => 0,
		'orderby'  => 'comment_date_gmt',
		'order'    => 'DESC',
		'number'   => 50,
	) );
	$total = (int) get_comments_number( $post_id );
	?>
	<section class="xin-talk" id="xin-talk">
		<div class="xin-talk__head-row">
			<h2><?php esc_html_e( 'Обсуждение', 'xi-novels' ); ?></h2>
			<span><?php echo esc_html( sprintf( xin_plural( $total, __( '%d сообщение', 'xi-novels' ), __( '%d сообщения', 'xi-novels' ), __( '%d сообщений', 'xi-novels' ) ), $total ) ); ?></span>
		</div>

		<?php if ( is_user_logged_in() ) : ?>
			<form class="xin-talk__form" method="post" action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" data-xin-talk-form>
				<div class="xin-talk__reply-to" data-xin-reply-note hidden>
					<span></span>
					<button type="button" data-xin-reply-cancel><?php esc_html_e( 'отменить', 'xi-novels' ); ?></button>
				</div>
				<textarea name="comment" rows="3" required placeholder="<?php esc_attr_e( 'Что скажете? ||так прячется спойлер||', 'xi-novels' ); ?>"></textarea>
				<div class="xin-talk__formfoot">
					<small><?php esc_html_e( '**жирный**, _курсив_, ||спойлер||', 'xi-novels' ); ?></small>
					<button class="btn btn-primary btn-sm" type="submit"><?php esc_html_e( 'Отправить', 'xi-novels' ); ?></button>
				</div>
				<input type="hidden" name="comment_post_ID" value="<?php echo (int) $post_id; ?>">
				<input type="hidden" name="comment_parent" value="0" data-xin-reply-field>
			</form>
		<?php else : ?>
			<p class="xin-talk__guest">
				<?php esc_html_e( 'Обсуждение открыто для тех, кто вошёл в аккаунт.', 'xi-novels' ); ?>
				<a href="<?php echo esc_url( xin_login_url( get_permalink( $post_id ) ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a>
			</p>
		<?php endif; ?>

		<?php if ( $comments ) : ?>
			<ul class="xin-talk__list">
				<?php xin_talk_walk( $comments ); ?>
			</ul>
		<?php else : ?>
			<p class="xin-talk__empty"><?php esc_html_e( 'Пока тихо. Скажите первое слово.', 'xi-novels' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
}
