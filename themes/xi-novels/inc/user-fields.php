<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_user_fields() {
	return array(
		'xin_tagline'  => array( 'label' => __( 'Короткая подпись', 'xi-novels' ), 'type' => 'text', 'hint' => __( 'Одна строка под именем: «переводчик с корейского», «пишу тёмное фэнтези».', 'xi-novels' ) ),
		'xin_cover'    => array( 'label' => __( 'ID картинки-обложки профиля', 'xi-novels' ), 'type' => 'number', 'hint' => __( 'Необязательно: ID файла из медиатеки. Пусто — рисуется градиент.', 'xi-novels' ) ),
		'xin_telegram' => array( 'label' => 'Telegram', 'type' => 'url', 'hint' => '' ),
		'xin_vk'       => array( 'label' => 'VK', 'type' => 'url', 'hint' => '' ),
		'xin_discord'  => array( 'label' => 'Discord', 'type' => 'url', 'hint' => '' ),
		'xin_site'     => array( 'label' => __( 'Сайт', 'xi-novels' ), 'type' => 'url', 'hint' => '' ),
		'xin_donate'   => array( 'label' => __( 'Ссылка на поддержку', 'xi-novels' ), 'type' => 'url', 'hint' => __( 'Boosty, Patreon, кошелёк — показывается кнопкой в профиле.', 'xi-novels' ) ),
	);
}

function xin_user_profile_fields( $user ) {
	?>
	<h2><?php esc_html_e( 'Профиль на площадке', 'xi-novels' ); ?></h2>
	<table class="form-table" role="presentation">
		<?php foreach ( xin_user_fields() as $key => $field ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input
						type="<?php echo esc_attr( $field['type'] ); ?>"
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>"
						class="regular-text"
					>
					<?php if ( $field['hint'] ) : ?>
						<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'xin_user_profile_fields' );
add_action( 'edit_user_profile', 'xin_user_profile_fields' );

function xin_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	foreach ( xin_user_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] );
		$value = 'url' === $field['type'] ? esc_url_raw( $value ) : sanitize_text_field( $value );
		update_user_meta( $user_id, $key, $value );
	}
}
add_action( 'personal_options_update', 'xin_save_user_fields' );
add_action( 'edit_user_profile_update', 'xin_save_user_fields' );

function xin_user_links( $user_id ) {
	$map = array(
		'xin_telegram' => 'telegram',
		'xin_vk'       => 'vk',
		'xin_discord'  => 'discord',
		'xin_site'     => 'compass',
	);

	$out = array();
	foreach ( $map as $key => $icon ) {
		$url = get_user_meta( $user_id, $key, true );
		if ( $url ) {
			$out[] = array( 'url' => $url, 'icon' => $icon );
		}
	}
	return $out;
}

function xin_user_cover( $user_id ) {
	$id = (int) get_user_meta( $user_id, 'xin_cover', true );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'xin-banner' );
		if ( $src ) {
			return $src;
		}
	}

	$novels = get_posts( array(
		'post_type'      => 'novel',
		'author'         => $user_id,
		'posts_per_page' => 6,
		'fields'         => 'ids',
	) );

	foreach ( $novels as $novel_id ) {
		$art = xin_background_url( $novel_id );
		if ( $art ) {
			return $art;
		}
	}

	return '';
}
