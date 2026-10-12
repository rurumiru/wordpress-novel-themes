<?php
/**
 * Настройки профиля в кабинете.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_user   = wp_get_current_user();
$xv_fields = xv_profile_fields();
$xv_avatar = (int) get_user_meta( $xv_user->ID, 'xv_avatar_id', true );
?>

<form class="xv-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="xv_save_profile">
	<?php wp_nonce_field( 'xv_save_profile' ); ?>

	<div class="xv-form-grid">
		<div class="xv-form-main">
			<h2 class="xv-form-title">
				<?php esc_html_e( 'Профиль', 'xin-v2' ); ?>
				<span class="xv-form-sub"><?php esc_html_e( 'Так вас видят читатели на странице автора', 'xin-v2' ); ?></span>
			</h2>

			<?php foreach ( $xv_fields as $xv_key => $xv_field ) : ?>
				<div class="xv-field">
					<label class="xv-label" for="xv-<?php echo esc_attr( $xv_key ); ?>"><?php echo esc_html( $xv_field['label'] ); ?></label>
					<?php if ( 'textarea' === $xv_field['type'] ) : ?>
						<textarea class="xv-textarea" id="xv-<?php echo esc_attr( $xv_key ); ?>" name="<?php echo esc_attr( $xv_key ); ?>" rows="6"><?php echo esc_textarea( xv_profile_value( $xv_user->ID, $xv_key ) ); ?></textarea>
					<?php else : ?>
						<input class="xv-input" type="<?php echo 'url' === $xv_field['type'] ? 'url' : 'text'; ?>" id="xv-<?php echo esc_attr( $xv_key ); ?>" name="<?php echo esc_attr( $xv_key ); ?>" value="<?php echo esc_attr( xv_profile_value( $xv_user->ID, $xv_key ) ); ?>">
					<?php endif; ?>
					<?php if ( ! empty( $xv_field['hint'] ) ) : ?>
						<p class="xv-help"><?php echo esc_html( $xv_field['hint'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<aside class="xv-form-side">
			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Аватар', 'xin-v2' ); ?></h3>

				<div class="xv-profile-avatar">
					<?php echo xv_avatar( $xv_user->ID, 84 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<input class="xv-input" type="file" name="avatar" accept="image/*">
				<p class="xv-help"><?php esc_html_e( 'Квадратная картинка, от 200×200.', 'xin-v2' ); ?></p>

				<?php if ( $xv_avatar ) : ?>
					<label class="xv-filter-item">
						<input class="xv-checkbox" type="checkbox" name="drop_avatar" value="1">
						<span><?php esc_html_e( 'Убрать аватар', 'xin-v2' ); ?></span>
					</label>
				<?php endif; ?>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Аккаунт', 'xin-v2' ); ?></h3>
				<dl>
					<div class="xv-aside-stat"><dt><?php esc_html_e( 'Логин', 'xin-v2' ); ?></dt><dd><?php echo esc_html( $xv_user->user_login ); ?></dd></div>
					<div class="xv-aside-stat"><dt><?php esc_html_e( 'Почта', 'xin-v2' ); ?></dt><dd><?php echo esc_html( $xv_user->user_email ); ?></dd></div>
				</dl>
				<p class="xv-help">
					<?php
					printf(
						/* translators: %s: ссылка на смену пароля. */
						esc_html__( 'Пароль и почта меняются %s.', 'xin-v2' ),
						'<a href="' . esc_url( admin_url( 'profile.php' ) ) . '">' . esc_html__( 'в настройках аккаунта', 'xin-v2' ) . '</a>'
					);
					?>
				</p>

				<div class="xv-form-actions">
					<button type="submit" class="xv-btn primary block"><?php esc_html_e( 'Сохранить', 'xin-v2' ); ?></button>
					<a class="xv-btn block" href="<?php echo esc_url( get_author_posts_url( $xv_user->ID ) ); ?>"><?php esc_html_e( 'Смотреть профиль', 'xin-v2' ); ?></a>
				</div>
			</div>
		</aside>
	</div>
</form>
