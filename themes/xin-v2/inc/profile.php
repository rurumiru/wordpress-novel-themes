<?php
/**
 * Профиль автора: поля, аватар и сохранение из кабинета.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Поля профиля. Один список — и форма, и вывод на странице автора, и
 * админский экран пользователя берут его отсюда.
 */
function xv_profile_fields() {
	return array(
		'xv_pen_name' => array(
			'label' => __( 'Псевдоним', 'xin-v2' ),
			'type'  => 'text',
			'hint'  => __( 'Под ним подписаны работы. Пусто — берём имя аккаунта.', 'xin-v2' ),
		),
		'xv_tagline'  => array(
			'label' => __( 'Строка о себе', 'xin-v2' ),
			'type'  => 'text',
			'hint'  => __( 'Одна короткая фраза под именем.', 'xin-v2' ),
		),
		'description'  => array(
			'label' => __( 'О себе', 'xin-v2' ),
			'type'  => 'textarea',
			'hint'  => __( 'Показывается на странице профиля.', 'xin-v2' ),
		),
		'xv_site'     => array(
			'label' => __( 'Сайт', 'xin-v2' ),
			'type'  => 'url',
		),
		'xv_telegram' => array(
			'label' => __( 'Telegram', 'xin-v2' ),
			'type'  => 'text',
			'hint'  => __( 'Имя пользователя без @.', 'xin-v2' ),
		),
		'xv_donate'   => array(
			'label' => __( 'Ссылка на поддержку', 'xin-v2' ),
			'type'  => 'url',
		),
	);
}

function xv_profile_value( $user_id, $key ) {
	if ( 'description' === $key ) {
		return get_the_author_meta( 'description', $user_id );
	}

	return (string) get_user_meta( $user_id, $key, true );
}

/**
 * Имя, под которым автор подписан.
 */
function xv_author_name( $user_id ) {
	$pen = get_user_meta( $user_id, 'xv_pen_name', true );

	if ( $pen ) {
		return $pen;
	}

	$user = get_userdata( $user_id );

	return $user ? $user->display_name : '';
}

/**
 * Свой аватар вместо граватара. Храним вложение, а не URL: файл тогда виден
 * в медиатеке и удаляется штатно.
 */
function xv_avatar_override( $url, $id_or_email ) {
	$user_id = 0;

	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user_id = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		$user_id = $user ? $user->ID : 0;
	}

	if ( ! $user_id ) {
		return $url;
	}

	$attachment = (int) get_user_meta( $user_id, 'xv_avatar_id', true );

	if ( ! $attachment ) {
		return $url;
	}

	$own = wp_get_attachment_image_url( $attachment, 'thumbnail' );

	return $own ? $own : $url;
}
add_filter( 'get_avatar_url', 'xv_avatar_override', 10, 2 );

/* -------------------------------------------------------------------------
 * Сохранение из кабинета
 * ---------------------------------------------------------------------- */

function xv_handle_profile_save() {
	if ( ! is_user_logged_in() ) {
		auth_redirect();
	}

	check_admin_referer( 'xv_save_profile' );

	$user_id = get_current_user_id();

	foreach ( xv_profile_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] );

		if ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'url' === $field['type'] ) {
			$value = esc_url_raw( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( 'description' === $key ) {
			wp_update_user( array( 'ID' => $user_id, 'description' => $value ) );
			continue;
		}

		update_user_meta( $user_id, $key, $value );
	}

	if ( ! empty( $_FILES['avatar']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment = media_handle_upload( 'avatar', 0 );

		if ( ! is_wp_error( $attachment ) ) {
			update_user_meta( $user_id, 'xv_avatar_id', (int) $attachment );
		}
	}

	if ( ! empty( $_POST['drop_avatar'] ) ) {
		delete_user_meta( $user_id, 'xv_avatar_id' );
	}

	xv_back( array( 'view' => 'profile' ), '', 'saved' );
}
add_action( 'admin_post_xv_save_profile', 'xv_handle_profile_save' );

/* -------------------------------------------------------------------------
 * Те же поля в админке
 * ---------------------------------------------------------------------- */

function xv_user_fields( $user ) {
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}
	?>
	<h2><?php esc_html_e( 'Профиль на сайте', 'xin-v2' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php foreach ( xv_profile_fields() as $key => $field ) : ?>
			<?php if ( 'description' === $key ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( xv_profile_value( $user->ID, $key ) ); ?>">
					<?php if ( ! empty( $field['hint'] ) ) : ?>
						<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'xv_user_fields' );
add_action( 'edit_user_profile', 'xv_user_fields' );

function xv_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	foreach ( xv_profile_fields() as $key => $field ) {
		if ( 'description' === $key || ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw   = wp_unslash( $_POST[ $key ] );
		$value = 'url' === $field['type'] ? esc_url_raw( $raw ) : sanitize_text_field( $raw );

		update_user_meta( $user_id, $key, $value );
	}
}
add_action( 'personal_options_update', 'xv_save_user_fields' );
add_action( 'edit_user_profile_update', 'xv_save_user_fields' );
