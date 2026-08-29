<?php
/**
 * Экран «Настройка сайта».
 *
 * Список проверок целиком, с объяснением, зачем каждая нужна: настройка,
 * которую сделали молча, невозможно ни проверить, ни отменить осознанно.
 *
 * @package XI_Studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Выполняет настройку по кнопке.
 *
 * @return void
 */
function xis_setup_post() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-studio' ) );
	}

	check_admin_referer( 'xis_setup' );

	$report = xis_setup_run();

	set_transient( 'xis_setup_result', $report, 60 );

	wp_safe_redirect( add_query_arg( 'page', 'xi-studio-setup', admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_xis_setup', 'xis_setup_post' );

/**
 * Рисует экран.
 *
 * @return void
 */
function xis_setup_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-studio' ) );
	}

	$steps   = xis_setup_steps();
	$result  = get_transient( 'xis_setup_result' );
	$pending = 0;

	delete_transient( 'xis_setup_result' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Настройка сайта под тему', 'xi-studio' ); ?></h1>

		<p class="description" style="max-width:70ch">
			<?php esc_html_e( 'Тема ждёт от установки нескольких вещей, которых в свежем WordPress нет. Без них она не ломается с грохотом — она тихо работает хуже, и замечают это недели спустя. Здесь видно, что не так и что будет сделано.', 'xi-studio' ); ?>
		</p>

		<p class="description" style="max-width:70ch">
			<strong><?php esc_html_e( 'Уже сделанный выбор не переписывается.', 'xi-studio' ); ?></strong>
			<?php esc_html_e( 'Чинится только то, что стоит по умолчанию и мешает теме: если структура ссылок выбрана, пусть даже неудачно, — это ваше решение, и трогать его молча плагин не станет.', 'xi-studio' ); ?>
		</p>

		<?php if ( ! xis_theme_ready() ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Тема XIN-Com не активна. Часть проверок ничего не значит без неё.', 'xi-studio' ); ?></p></div>
		<?php endif; ?>

		<?php if ( is_array( $result ) ) : ?>
			<div class="notice notice-<?php echo empty( $result['failed'] ) ? 'success' : 'warning'; ?>">
				<?php if ( $result['done'] ) : ?>
					<p><strong><?php esc_html_e( 'Сделано:', 'xi-studio' ); ?></strong></p>
					<ul style="margin-left:18px;list-style:disc">
						<?php foreach ( $result['done'] as $line ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $result['failed'] ) : ?>
					<p><strong><?php esc_html_e( 'Не получилось:', 'xi-studio' ); ?></strong></p>
					<ul style="margin-left:18px;list-style:disc">
						<?php foreach ( $result['failed'] as $line ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! $result['done'] && ! $result['failed'] ) : ?>
					<p><?php esc_html_e( 'Менять было нечего — всё уже настроено.', 'xi-studio' ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<table class="widefat striped" style="margin-top:16px">
			<thead>
				<tr>
					<th style="width:44px"></th>
					<th style="width:200px"><?php esc_html_e( 'Что проверяем', 'xi-studio' ); ?></th>
					<th><?php esc_html_e( 'Состояние и зачем это нужно', 'xi-studio' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $steps as $xis_id => $xis_step ) : ?>
					<?php
					$xis_state = call_user_func( $xis_step['check'] );
					$xis_ok    = ! empty( $xis_state['ok'] );
					$xis_fix   = ! $xis_ok && $xis_step['apply'];

					if ( $xis_fix ) {
						++$pending;
					}
					?>
					<tr>
						<td style="font-size:18px;text-align:center">
							<?php if ( $xis_ok ) : ?>
								<span style="color:#146c43" title="<?php esc_attr_e( 'В порядке', 'xi-studio' ); ?>">&#10003;</span>
							<?php elseif ( $xis_fix ) : ?>
								<span style="color:#bb7d00" title="<?php esc_attr_e( 'Будет исправлено', 'xi-studio' ); ?>">&#9679;</span>
							<?php else : ?>
								<span style="color:#8c8f94" title="<?php esc_attr_e( 'Решать вам', 'xi-studio' ); ?>">&#9679;</span>
							<?php endif; ?>
						</td>
						<td><strong><?php echo esc_html( $xis_step['title'] ); ?></strong></td>
						<td>
							<p style="margin:0 0 4px"><?php echo esc_html( $xis_state['detail'] ); ?></p>
							<p class="description" style="margin:0"><?php echo esc_html( $xis_step['why'] ); ?></p>
							<?php if ( ! $xis_ok ) : ?>
								<p style="margin:6px 0 0"><em><?php echo esc_html( $xis_step['plan'] ); ?></em></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:18px">
			<?php wp_nonce_field( 'xis_setup' ); ?>
			<input type="hidden" name="action" value="xis_setup">
			<button type="submit" class="button button-primary" <?php disabled( 0, $pending ); ?>>
				<?php
				echo $pending
					? esc_html( sprintf(
						/* translators: %d — сколько пунктов будет исправлено. */
						__( 'Настроить (%d)', 'xi-studio' ),
						$pending
					) )
					: esc_html__( 'Всё настроено', 'xi-studio' );
				?>
			</button>
		</form>
	</div>
	<?php
}
