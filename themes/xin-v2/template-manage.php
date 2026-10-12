<?php
/**
 * Template Name: Панель управления
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
	</header>

	<?php if ( ! xv_can_moderate() && ! xv_can_manage() ) : ?>

		<div class="xv-empty">
			<?php xv_the_icon( 'lock' ); ?>
			<p class="xv-empty-title"><?php esc_html_e( 'Страница для модераторов', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Если вы модератор — войдите под своей учётной записью.', 'xin-v2' ); ?></p>
			<p class="xv-empty-actions">
				<a class="xv-btn primary" href="<?php echo esc_url( xv_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
			</p>
		</div>

	<?php else : ?>

		<?php
		xv_manage_notice();
		$xv_stats = xv_manage_stats();
		?>

		<div class="xv-stat-strip">
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['hold'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_held( $xv_stats['hold'] ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['asks'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_requests( $xv_stats['asks'] ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['pending'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_waiting( $xv_stats['pending'] ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_stats['users'] ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_readers( $xv_stats['users'] ) ); ?></span>
			</div>
		</div>

		<?php if ( xv_can_moderate() ) : ?>
			<section class="xv-manage-block">
				<?php xv_section_head( array( 'title' => __( 'Комментарии на проверке', 'xin-v2' ) ) ); ?>

				<?php $xv_queue = xv_manage_queue(); ?>

				<?php if ( $xv_queue ) : ?>
					<div class="xv-queue">
						<?php foreach ( $xv_queue as $xv_comment ) : ?>
							<article class="xv-queue-row">
								<header class="xv-queue-head">
									<span class="xv-queue-who"><?php echo esc_html( $xv_comment->comment_author ); ?></span>
									<a class="xv-queue-where" href="<?php echo esc_url( get_permalink( $xv_comment->comment_post_ID ) ); ?>"><?php echo esc_html( get_the_title( $xv_comment->comment_post_ID ) ); ?></a>
									<time class="xv-queue-when"><?php echo esc_html( xv_ago( strtotime( $xv_comment->comment_date_gmt . ' GMT' ) ) ); ?></time>
								</header>

								<p class="xv-queue-text"><?php echo esc_html( wp_trim_words( $xv_comment->comment_content, 60, '…' ) ); ?></p>

								<div class="xv-queue-actions">
									<?php foreach ( array( 'approve' => __( 'Одобрить', 'xin-v2' ), 'spam' => __( 'Спам', 'xin-v2' ), 'trash' => __( 'В корзину', 'xin-v2' ) ) as $xv_do => $xv_label ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<input type="hidden" name="action" value="xv_moderate">
											<input type="hidden" name="comment" value="<?php echo (int) $xv_comment->comment_ID; ?>">
											<input type="hidden" name="do" value="<?php echo esc_attr( $xv_do ); ?>">
											<?php wp_nonce_field( 'xv_moderate' ); ?>
											<button type="submit" class="xv-btn sm <?php echo 'approve' === $xv_do ? 'primary' : ''; ?>"><?php echo esc_html( $xv_label ); ?></button>
										</form>
									<?php endforeach; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="xv-empty-text"><?php esc_html_e( 'Очередь пуста.', 'xin-v2' ); ?></p>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( xv_can_manage() ) : ?>

			<section class="xv-manage-block">
				<?php xv_section_head( array( 'title' => __( 'Заявки в авторы', 'xin-v2' ) ) ); ?>

				<?php $xv_asks = xv_author_requests(); ?>

				<?php if ( $xv_asks ) : ?>
					<div class="xv-queue">
						<?php foreach ( $xv_asks as $xv_ask ) : ?>
							<article class="xv-queue-row">
								<header class="xv-queue-head">
									<span class="xv-queue-who"><?php echo esc_html( $xv_ask['name'] ); ?></span>
									<time class="xv-queue-when"><?php echo esc_html( xv_ago( $xv_ask['at'] ) ); ?></time>
								</header>

								<?php if ( $xv_ask['about'] ) : ?>
									<p class="xv-queue-text"><?php echo esc_html( $xv_ask['about'] ); ?></p>
								<?php endif; ?>

								<div class="xv-queue-actions">
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="xv_role">
										<input type="hidden" name="user" value="<?php echo (int) $xv_ask['id']; ?>">
										<input type="hidden" name="role" value="author">
										<?php wp_nonce_field( 'xv_role' ); ?>
										<button type="submit" class="xv-btn sm primary"><?php esc_html_e( 'Сделать автором', 'xin-v2' ); ?></button>
									</form>

									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="xv_ask_drop">
										<input type="hidden" name="user" value="<?php echo (int) $xv_ask['id']; ?>">
										<?php wp_nonce_field( 'xv_ask_drop' ); ?>
										<button type="submit" class="xv-btn sm"><?php esc_html_e( 'Отклонить', 'xin-v2' ); ?></button>
									</form>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="xv-empty-text"><?php esc_html_e( 'Заявок нет.', 'xin-v2' ); ?></p>
				<?php endif; ?>
			</section>

			<section class="xv-manage-block">
				<?php
				xv_section_head( array(
					'title' => __( 'Роли', 'xin-v2' ),
					'sub'   => __( 'последние пришедшие', 'xin-v2' ),
				) );
				?>

				<div class="xv-roles">
					<?php
					$xv_users = get_users( array(
						'number'  => 25,
						'orderby' => 'registered',
						'order'   => 'DESC',
					) );
					?>

					<?php foreach ( $xv_users as $xv_user ) : ?>
						<?php $xv_role = $xv_user->roles ? $xv_user->roles[0] : ''; ?>
						<div class="xv-role-row">
							<?php echo xv_avatar( $xv_user->ID, 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="xv-role-name"><?php echo esc_html( $xv_user->display_name ); ?></span>

							<?php if ( user_can( $xv_user, 'manage_options' ) || get_current_user_id() === $xv_user->ID ) : ?>
								<span class="xv-role-fixed"><?php echo esc_html( xv_role_label( $xv_role ) ); ?></span>
							<?php else : ?>
								<form class="xv-role-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="xv_role">
									<input type="hidden" name="user" value="<?php echo (int) $xv_user->ID; ?>">
									<?php wp_nonce_field( 'xv_role' ); ?>
									<select class="xv-select sm" name="role">
										<?php foreach ( xv_manage_roles() as $xv_key => $xv_label ) : ?>
											<option value="<?php echo esc_attr( $xv_key ); ?>" <?php selected( $xv_role, $xv_key ); ?>><?php echo esc_html( $xv_label ); ?></option>
										<?php endforeach; ?>
									</select>
									<button type="submit" class="xv-btn sm"><?php esc_html_e( 'Сменить', 'xin-v2' ); ?></button>
								</form>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="xv-prose xv-page-note"><?php the_content(); ?></div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
