<?php
/**
 * Экран настройки.
 *
 * Один список, в котором видно, что готово, а что нет, и рядом с каждым
 * пунктом кнопка, которая делает ровно этот пункт. Кнопка «настроить всё»
 * тоже есть, но она не первая: владелец сайта должен сначала прочитать, что
 * с ним собираются сделать.
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xs_menu() {
	add_management_page(
		__( 'Настройка сайта под тему', 'xin-setup' ),
		__( 'Настройка темы', 'xin-setup' ),
		xs_cap(),
		'xin-setup',
		'xs_screen'
	);
}
add_action( 'admin_menu', 'xs_menu' );

function xs_assets( $hook ) {
	if ( 'tools_page_xin-setup' !== $hook ) {
		return;
	}

	update_option( 'xs_seen', 1 );

	wp_enqueue_style( 'xs-screen', XS_URL . 'assets/screen.css', array(), XS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'xs_assets' );

function xs_screen() {
	if ( ! current_user_can( xs_cap() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-setup' ), 403 );
	}

	$notice = xs_handle();
	$theme  = xs_theme();
	$survey = xs_survey();
	$size   = xs_library_size();
	?>
	<div class="wrap xs-wrap">
		<h1><?php esc_html_e( 'Настройка сайта под тему', 'xin-setup' ); ?></h1>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('xs_quick_setup'); ?><input type="hidden" name="action" value="xs_quick_setup"><p>Автонастройка запускается после активации и импорта. Повторный запуск проверяет страницы, адреса, меню, обложки и счётчики; содержимое книг сохраняется.</p><button class="button button-primary button-hero">Настроить WordPress автоматически</button></form>

		<p class="xs-lede">
			<?php
			printf(
				/* translators: 1: имя темы, 2: число тайтлов, 3: число глав. */
				esc_html__( 'Тема: %1$s. В библиотеке: тайтлов — %2$s, глав — %3$s.', 'xin-setup' ),
				'<strong>' . esc_html( $theme['name'] ) . '</strong>',
				esc_html( number_format_i18n( $size['novels'] ) ),
				esc_html( number_format_i18n( $size['chapters'] ) )
			);
			?>
		</p>

		<?php if ( ! $theme['templates'] ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'У активной темы нет шаблонов страниц, которые этот плагин умеет заводить. Настройки и правку записей он всё равно сделает.', 'xin-setup' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notice['kind'] ); ?>"><p><?php echo esc_html( $notice['text'] ); ?></p></div>
		<?php endif; ?>

		<div class="xs-head">
			<?php if ( $survey['left'] ) : ?>
				<p class="xs-left">
					<?php
					printf(
						/* translators: %s: сколько пунктов не готово. */
						esc_html__( 'Не готово пунктов: %s', 'xin-setup' ),
						'<strong>' . esc_html( number_format_i18n( $survey['left'] ) ) . '</strong>'
					);
					?>
				</p>
			<?php else : ?>
				<p class="xs-done"><?php esc_html_e( 'Всё на месте. Заходить сюда больше незачем.', 'xin-setup' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $survey['settings'] ) : ?>
			<section class="xs-block">
				<h2><?php esc_html_e( 'Настройки WordPress', 'xin-setup' ); ?></h2>

				<?php foreach ( $survey['settings'] as $key => $item ) : ?>
					<div class="xs-row <?php echo $item['ok'] ? 'is-ok' : 'is-todo'; ?>">
						<div class="xs-row-body">
							<h3><?php echo esc_html( $item['label'] ); ?></h3>
							<p class="xs-now">
								<?php
								printf(
									/* translators: %s: как настроено сейчас. */
									esc_html__( 'Сейчас: %s', 'xin-setup' ),
									esc_html( $item['now'] )
								);
								?>
							</p>
							<p class="xs-why"><?php echo esc_html( $item['why'] ); ?></p>
						</div>

						<div class="xs-row-act">
							<?php if ( $item['ok'] ) : ?>
								<span class="xs-tick"><?php esc_html_e( 'готово', 'xin-setup' ); ?></span>
							<?php else : ?>
								<form method="post">
									<?php wp_nonce_field( 'xs_do' ); ?>
									<input type="hidden" name="xs_what" value="setting">
									<input type="hidden" name="xs_key" value="<?php echo esc_attr( $key ); ?>">
									<button class="button button-primary"><?php esc_html_e( 'Настроить', 'xin-setup' ); ?></button>
								</form>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( $survey['pages'] ) : ?>
			<section class="xs-block">
				<h2><?php esc_html_e( 'Страницы разделов', 'xin-setup' ); ?></h2>
				<p class="xs-block-note"><?php esc_html_e( 'Тема умеет показывать эти разделы, но пока под шаблон не заведена страница, раздела на сайте нет.', 'xin-setup' ); ?></p>

				<div class="xs-pages">
					<?php foreach ( $survey['pages'] as $template => $page ) : ?>
						<div class="xs-page <?php echo $page['ok'] ? 'is-ok' : 'is-todo'; ?>">
							<span class="xs-page-name"><?php echo esc_html( $page['label'] ); ?></span>
							<?php if ( $page['ok'] ) : ?>
								<a class="xs-page-link" href="<?php echo esc_url( $page['url'] ); ?>"><?php esc_html_e( 'открыть', 'xin-setup' ); ?></a>
							<?php else : ?>
								<span class="xs-page-miss"><?php esc_html_e( 'нет', 'xin-setup' ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<form method="post" class="xs-block-act">
					<?php wp_nonce_field( 'xs_do' ); ?>
					<input type="hidden" name="xs_what" value="pages">
					<button class="button button-primary"><?php esc_html_e( 'Завести недостающие страницы', 'xin-setup' ); ?></button>
					<button class="button" name="xs_what" value="menus"><?php esc_html_e( 'Собрать меню', 'xin-setup' ); ?></button>
				</form>
			</section>
		<?php endif; ?>

		<section class="xs-block">
			<h2><?php esc_html_e( 'Уже накопленные записи', 'xin-setup' ); ?></h2>
			<p class="xs-block-note"><?php esc_html_e( 'Правки идут порциями: нажмите ещё раз, если после первой осталось. Ничего не удаляется — только дописывается недостающее.', 'xin-setup' ); ?></p>

			<?php foreach ( $survey['content'] as $key => $item ) : ?>
				<div class="xs-row <?php echo $item['ok'] ? 'is-ok' : 'is-todo'; ?>">
					<div class="xs-row-body">
						<h3>
							<?php echo esc_html( $item['label'] ); ?>
							<?php if ( ! $item['ok'] ) : ?>
								<span class="xs-count"><?php echo esc_html( number_format_i18n( $item['count'] ) ); ?></span>
							<?php endif; ?>
						</h3>
						<p class="xs-why"><?php echo esc_html( $item['why'] ); ?></p>
					</div>

					<div class="xs-row-act">
						<?php if ( $item['ok'] ) : ?>
							<span class="xs-tick"><?php esc_html_e( 'нечего править', 'xin-setup' ); ?></span>
						<?php else : ?>
							<form method="post">
								<?php wp_nonce_field( 'xs_do' ); ?>
								<input type="hidden" name="xs_what" value="content">
								<input type="hidden" name="xs_key" value="<?php echo esc_attr( $key ); ?>">
								<button class="button button-primary"><?php esc_html_e( 'Поправить порцию', 'xin-setup' ); ?></button>
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</section>

		<?php if ( $survey['left'] ) : ?>
			<section class="xs-block xs-all">
				<h2><?php esc_html_e( 'Сделать всё сразу', 'xin-setup' ); ?></h2>
				<p class="xs-block-note"><?php esc_html_e( 'Применит всё перечисленное выше одним заходом: настройки, страницы, меню и по одной порции правок в записях. Прочитайте список до того, как нажмёте.', 'xin-setup' ); ?></p>

				<form method="post">
					<?php wp_nonce_field( 'xs_do' ); ?>
					<input type="hidden" name="xs_what" value="all">
					<button class="button button-primary button-hero"><?php esc_html_e( 'Настроить сайт под тему', 'xin-setup' ); ?></button>
				</form>
			</section>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Выполняет то, что нажали.
 */
function xs_handle() {
	if ( empty( $_POST['xs_what'] ) ) {
		return null;
	}

	check_admin_referer( 'xs_do' );

	if ( ! current_user_can( xs_cap() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-setup' ), 403 );
	}

	$what = sanitize_key( wp_unslash( $_POST['xs_what'] ) );
	$key  = isset( $_POST['xs_key'] ) ? sanitize_key( wp_unslash( $_POST['xs_key'] ) ) : '';

	switch ( $what ) {
		case 'setting':
			$all = xs_wanted_settings();

			if ( ! isset( $all[ $key ] ) ) {
				return array( 'kind' => 'error', 'text' => __( 'Непонятная настройка.', 'xin-setup' ) );
			}

			return array( 'kind' => 'success', 'text' => (string) call_user_func( $all[ $key ]['fix'] ) );

		case 'pages':
			$made = xs_create_pages();

			return array(
				'kind' => 'success',
				'text' => $made
					? sprintf(
						/* translators: %s: сколько страниц заведено. */
						__( 'Заведено страниц: %s.', 'xin-setup' ),
						number_format_i18n( count( $made ) )
					)
					: __( 'Все страницы уже были на месте.', 'xin-setup' ),
			);

		case 'menus':
			$made = xs_build_menus();

			return array(
				'kind' => 'success',
				'text' => $made
					? sprintf(
						/* translators: %s: какие меню собраны. */
						__( 'Собрано меню: %s.', 'xin-setup' ),
						implode( ', ', $made )
					)
					: __( 'Меню уже назначены.', 'xin-setup' ),
			);

		case 'content':
			$jobs = xs_content_jobs();

			if ( ! isset( $jobs[ $key ] ) ) {
				return array( 'kind' => 'error', 'text' => __( 'Непонятная правка.', 'xin-setup' ) );
			}

			$done = (int) call_user_func( $jobs[ $key ]['fix'] );

			return array(
				'kind' => 'success',
				'text' => sprintf(
					/* translators: %s: сколько записей поправлено. */
					__( 'Поправлено записей: %s. Если осталось — нажмите ещё раз.', 'xin-setup' ),
					number_format_i18n( $done )
				),
			);

		case 'all':
			$steps = 0;

			foreach ( xs_wanted_settings() as $item ) {
				if ( ! call_user_func( $item['ok'] ) ) {
					call_user_func( $item['fix'] );
					$steps++;
				}
			}

			$steps += count( xs_create_pages() );
			$steps += count( xs_build_menus() );

			foreach ( xs_content_jobs() as $job ) {
				if ( call_user_func( $job['count'] ) ) {
					call_user_func( $job['fix'] );
					$steps++;
				}
			}

			return array(
				'kind' => 'success',
				'text' => sprintf(
					/* translators: %s: сколько шагов выполнено. */
					__( 'Готово. Шагов выполнено: %s. Проверьте список — то, что правится порциями, могло остаться.', 'xin-setup' ),
					number_format_i18n( $steps )
				),
			);
	}

	return null;
}
