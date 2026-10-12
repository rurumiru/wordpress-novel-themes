<?php
/**
 * Экран диагностики.
 *
 * @package XIN_V2_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xvk_menu() {
	$parent = 'xin-v2';

	// Тема XIN-V2 заводит свой раздел «Платформа» — встраиваемся туда, чтобы
	// не плодить пунктов в меню. Иначе живём под «Инструментами».
	if ( menu_page_url( $parent, false ) ) {
		add_submenu_page(
			$parent,
			__( 'Диагностика', 'xin-v2-kit' ),
			__( 'Диагностика', 'xin-v2-kit' ),
			xvk_cap(),
			'xin-v2-doctor',
			'xvk_screen'
		);

		return;
	}

	add_management_page(
		__( 'Диагностика библиотеки', 'xin-v2-kit' ),
		__( 'Диагностика библиотеки', 'xin-v2-kit' ),
		xvk_cap(),
		'xin-v2-doctor',
		'xvk_screen'
	);
}
add_action( 'admin_menu', 'xvk_menu', 20 );

function xvk_assets( $hook ) {
	if ( ! str_contains( (string) $hook, 'xin-v2-doctor' ) ) {
		return;
	}

	wp_enqueue_style( 'erk-screen', XVK_URL . 'assets/screen.css', array(), XIN_V2_KIT );
}
add_action( 'admin_enqueue_scripts', 'xvk_assets' );

function xvk_screen() {
	if ( ! current_user_can( xvk_cap() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2-kit' ), 403 );
	}

	$done = array();

	if ( isset( $_POST['xvk_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xvk_nonce'] ) ), 'xvk_fix' ) ) {
		$targets = isset( $_POST['fix'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['fix'] ) ) : array();

		if ( ! empty( $_POST['fix_all'] ) ) {
			// «Всё» — это поломки. Необязательное чинится только галочкой.
			$targets = array();

			foreach ( xvk_checks() as $key => $check ) {
				if ( empty( $check['optional'] ) ) {
					$targets[] = $key;
				}
			}
		}

		foreach ( $targets as $key ) {
			$count = xvk_run_fix( $key );

			if ( $count ) {
				$done[ $key ] = $count;
			}
		}
	}

	$report  = xvk_report();
	$total   = 0;
	$fixable = 0;

	foreach ( $report as $row ) {
		if ( $row['optional'] ) {
			continue;
		}

		$total += $row['count'];

		if ( $row['count'] && $row['fixable'] ) {
			$fixable += $row['count'];
		}
	}
	?>
	<div class="wrap erk">
		<h1 class="erk-title"><?php esc_html_e( 'Диагностика библиотеки', 'xin-v2-kit' ); ?></h1>
		<p class="erk-lede"><?php esc_html_e( 'Проверяет то, что ломает витрину незаметно: счётчики, привязку глав, номера и адреса.', 'xin-v2-kit' ); ?></p>

		<?php if ( $done ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					$parts = array();

					foreach ( $done as $key => $count ) {
						$parts[] = sprintf( '%s — %s', esc_html( $report[ $key ]['label'] ), esc_html( number_format_i18n( $count ) ) );
					}

					printf(
						/* translators: %s: перечень исправленного. */
						esc_html__( 'Исправлено: %s', 'xin-v2-kit' ),
						esc_html( implode( '; ', $parts ) )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="erk-summary">
			<?php if ( $total ) : ?>
				<p class="erk-summary-bad">
					<?php
					printf(
						/* translators: %s: число найденных проблем. */
						esc_html__( 'Найдено расхождений: %s', 'xin-v2-kit' ),
						'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
					);
					?>
				</p>
			<?php else : ?>
				<p class="erk-summary-ok"><?php esc_html_e( 'Всё в порядке — расхождений нет.', 'xin-v2-kit' ); ?></p>
			<?php endif; ?>
		</div>

		<form method="post">
			<?php wp_nonce_field( 'xvk_fix', 'xvk_nonce' ); ?>

			<table class="widefat erk-table">
				<thead>
					<tr>
						<th class="erk-col-check"><?php esc_html_e( 'Проверка', 'xin-v2-kit' ); ?></th>
						<th class="erk-col-count"><?php esc_html_e( 'Найдено', 'xin-v2-kit' ); ?></th>
						<th class="erk-col-fix"><?php esc_html_e( 'Починить', 'xin-v2-kit' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report as $key => $row ) : ?>
						<tr class="<?php echo $row['count'] && ! $row['optional'] ? 'erk-row-bad' : 'erk-row-ok'; ?>">
							<td>
								<strong><?php echo esc_html( $row['label'] ); ?></strong>
								<p class="erk-hint"><?php echo esc_html( $row['hint'] ); ?></p>
								<?php if ( $row['note'] && $row['count'] ) : ?>
									<p class="erk-note"><?php echo esc_html( $row['note'] ); ?></p>
								<?php endif; ?>
								<?php if ( $row['count'] && $row['count'] <= 12 && 'rewrites' !== $key && 'pages' !== $key ) : ?>
									<p class="erk-items">
										<?php
										$links = array();

										foreach ( array_slice( $row['items'], 0, 12 ) as $item ) {
											$edit = get_edit_post_link( $item );
											$name = get_the_title( $item );

											$links[] = $edit
												? '<a href="' . esc_url( $edit ) . '">' . esc_html( $name ? $name : '#' . $item ) . '</a>'
												: esc_html( (string) $item );
										}

										echo wp_kses_post( implode( ', ', $links ) );
										?>
									</p>
								<?php endif; ?>
							</td>
							<td class="erk-count">
								<?php echo $row['count'] ? esc_html( number_format_i18n( $row['count'] ) ) : '—'; ?>
							</td>
							<td>
								<?php if ( ! $row['count'] ) : ?>
									<span class="erk-ok">✓</span>
								<?php elseif ( $row['fixable'] ) : ?>
									<label>
										<input type="checkbox" name="fix[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( ! $row['optional'] ); ?>>
										<?php echo esc_html( $row['optional'] ? __( 'по желанию', 'xin-v2-kit' ) : __( 'да', 'xin-v2-kit' ) ); ?>
									</label>
								<?php else : ?>
									<span class="erk-manual"><?php esc_html_e( 'вручную', 'xin-v2-kit' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="erk-actions">
				<button type="submit" class="button button-primary" <?php disabled( 0, $fixable ); ?>><?php esc_html_e( 'Починить отмеченное', 'xin-v2-kit' ); ?></button>
				<button type="submit" name="fix_all" value="1" class="button" <?php disabled( 0, $fixable ); ?>><?php esc_html_e( 'Починить всё', 'xin-v2-kit' ); ?></button>
				<button type="submit" class="button"><?php esc_html_e( 'Проверить заново', 'xin-v2-kit' ); ?></button>
			</p>

			<p class="erk-batch">
				<?php
				printf(
					/* translators: %s: размер порции. */
					esc_html__( 'За один заход чинится до %s записей — на большой библиотеке нажмите ещё раз.', 'xin-v2-kit' ),
					esc_html( number_format_i18n( xvk_batch() ) )
				);
				?>
			</p>
		</form>
	</div>
	<?php
}
