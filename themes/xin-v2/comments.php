<?php
/**
 * Обсуждение под тайтлом, главой или записью.
 *
 * Разметка своя — см. inc/discussions.php.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

$xv_post_id = get_the_ID();
$xv_open    = xv_discussion_open( $xv_post_id );
$xv_order   = isset( $_GET['c'] ) ? sanitize_key( wp_unslash( $_GET['c'] ) ) : 'new';
$xv_tree    = xv_comment_tree( $xv_post_id, $xv_order );
$xv_total   = xv_comment_count( $xv_post_id );

if ( ! $xv_open && ! $xv_total ) {
	return;
}

$xv_orders = array(
	'new' => __( 'Новые', 'xin-v2' ),
	'old' => __( 'Старые', 'xin-v2' ),
	'top' => __( 'Полезные', 'xin-v2' ),
);
?>

<section class="xv-comments" id="comments" data-xv-comments="<?php echo (int) $xv_post_id; ?>">

	<div class="xv-comments-bar">
		<h2 class="xv-comments-title">
			<?php esc_html_e( 'Обсуждение', 'xin-v2' ); ?>
			<span class="xv-comments-count" data-xv-comments-count><?php echo esc_html( number_format_i18n( $xv_total ) ); ?></span>
		</h2>

		<?php if ( $xv_total > 1 ) : ?>
			<div class="xv-comments-sort">
				<?php foreach ( $xv_orders as $xv_key => $xv_label ) : ?>
					<a class="xv-comments-sort-item <?php echo $xv_order === $xv_key ? 'is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'c', $xv_key ) . '#comments' ); ?>"><?php echo esc_html( $xv_label ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $xv_open ) : ?>
		<?php if ( is_user_logged_in() ) : ?>
			<form class="xv-cmt-form" data-xv-cmt-form>
				<?php echo xv_avatar( get_current_user_id(), 34, 'xv-cmt-form-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="xv-cmt-form-body">
					<div class="xv-cmt-composer-name"><?php echo esc_html(wp_get_current_user()->display_name); ?><span><?php esc_html_e('Ваш комментарий','xin-v2'); ?></span></div>
					<textarea class="xv-textarea" name="text" rows="3" placeholder="<?php esc_attr_e( 'Поделитесь впечатлениями…', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Текст комментария', 'xin-v2' ); ?>" required></textarea>
					<div class="xv-cmt-form-foot">
						<span class="xv-cmt-form-note" data-xv-cmt-note></span>
						<button type="submit" class="xv-btn primary sm"><?php esc_html_e( 'Отправить', 'xin-v2' ); ?></button>
					</div>
				</div>
			</form>
		<?php else : ?>
			<div class="xv-cmt-guest">
				<p><?php esc_html_e( 'Чтобы оставить комментарий, войдите.', 'xin-v2' ); ?></p>
				<div class="xv-cmt-guest-actions">
					<a class="xv-btn primary sm" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
					<?php if ( get_option( 'users_can_register' ) ) : ?>
						<a class="xv-btn sm" href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Регистрация', 'xin-v2' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="xv-cmt-list" data-xv-cmt-list>
		<?php if ( $xv_tree ) : ?>
			<?php foreach ( $xv_tree as $xv_node ) : ?>
				<div class="xv-cmt-thread" data-xv-cmt-thread="<?php echo (int) $xv_node['comment']->comment_ID; ?>">
					<?php xv_comment_card( $xv_node['comment'] ); ?>

					<div class="xv-cmt-children" data-xv-cmt-children>
						<?php foreach ( $xv_node['children'] as $xv_child ) : ?>
							<?php xv_comment_card( $xv_child, true ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="xv-cmt-empty"><?php esc_html_e( 'Тихо. Скажите первое слово.', 'xin-v2' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( ! $xv_open && $xv_total ) : ?>
		<p class="xv-cmt-closed"><?php esc_html_e( 'Обсуждение закрыто.', 'xin-v2' ); ?></p>
	<?php endif; ?>
</section>
