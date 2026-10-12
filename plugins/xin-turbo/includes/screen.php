<?php
/**
 * Страница настроек.
 *
 * Один экран, на котором у каждого переключателя написано, что он делает и
 * чем за это платят. Без «оптимизировать всё одной кнопкой»: такая кнопка
 * однажды ломает сайт, и владелец не знает, какая именно из тридцати
 * настроек виновата.
 *
 * @package XIN_Turbo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xt_menu() {
	add_options_page(
		__( 'XIN Turbo — скорость', 'xin-turbo' ),
		__( 'Скорость', 'xin-turbo' ),
		xt_cap(),
		'xin-turbo',
		'xt_screen'
	);
}
add_action( 'admin_menu', 'xt_menu' );

function xt_screen_assets( $hook ) {
	if ( 'settings_page_xin-turbo' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'xt-screen', XT_URL . 'assets/screen.css', array(), XT_VERSION );
}
add_action( 'admin_enqueue_scripts', 'xt_screen_assets' );

/**
 * Поля: ключ, подпись, пояснение и цена решения.
 */
function xt_fields() {
	return array(
		__( 'Кэш готовых страниц', 'xin-turbo' ) => array(
			'page_cache' => array(
				'label' => __( 'Отдавать гостям готовый HTML', 'xin-turbo' ),
				'note'  => __( 'Страница собирается один раз и дальше отдаётся файлом, минуя WordPress. Вошедшие читатели кэш не получают — у них своё избранное и своя серия чтения.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'cache_life' => array(
				'label' => __( 'Сколько держать страницу, секунд', 'xin-turbo' ),
				'note'  => __( 'Кэш и так сбрасывается при любой правке. Это верхняя граница на случай, если что-то изменилось мимо WordPress.', 'xin-turbo' ),
				'type'  => 'int',
			),
		),

		__( 'Вес страницы', 'xin-turbo' ) => array(
			'trim_head'    => array(
				'label' => __( 'Убрать лишнее из шапки документа', 'xin-turbo' ),
				'note'  => __( 'Версия WordPress, ссылки RSD и Windows Live Writer, ссылки на соседние записи, обнаружение oEmbed. Ничего из этого браузер не показывает.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'drop_emoji'   => array(
				'label' => __( 'Убрать скрипт эмодзи', 'xin-turbo' ),
				'note'  => __( 'Он подменял эмодзи картинками в браузерах, которые их не рисовали. Такие браузеры давно ушли.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'drop_migrate' => array(
				'label' => __( 'Убрать jQuery Migrate', 'xin-turbo' ),
				'note'  => __( 'Слой совместимости для кода, написанного под jQuery 1.x. Если после включения сломался старый плагин — верните обратно.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'drop_blocks'  => array(
				'label' => __( 'Убрать стили блочного редактора с витрины', 'xin-turbo' ),
				'note'  => __( 'Около 100 КБ CSS. Включайте, только если страницы и записи свёрстаны не блоками — иначе они поедут.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'defer_js'     => array(
				'label' => __( 'Откладывать скрипты из подвала', 'xin-turbo' ),
				'note'  => __( 'Браузер рисует страницу, не дожидаясь их. Скрипты из шапки не трогаем — они там намеренно.', 'xin-turbo' ),
				'type'  => 'bool',
			),
		),

		__( 'Картинки', 'xin-turbo' ) => array(
			'lazy_media'   => array(
				'label' => __( 'Грузить картинки по мере прокрутки', 'xin-turbo' ),
				'note'  => __( 'Первая картинка экрана, наоборот, помечается важной — иначе обложка появляется последней.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'size_images'  => array(
				'label' => __( 'Проставлять размеры картинкам в тексте', 'xin-turbo' ),
				'note'  => __( 'Без размеров страница прыгает, когда картинка догрузилась, и читатель теряет строку.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'lazy_iframes' => array(
				'label' => __( 'Откладывать встраивания', 'xin-turbo' ),
				'note'  => __( 'Плеер с чужого сайта загрузится, только когда читатель до него долистает.', 'xin-turbo' ),
				'type'  => 'bool',
			),
		),

		__( 'Административная часть', 'xin-turbo' ) => array(
			'slow_heartbeat' => array(
				'label' => __( 'Придержать heartbeat', 'xin-turbo' ),
				'note'  => __( 'Открытый редактор шлёт запрос каждые 15 секунд. Ставим минуту в редакторе и две на остальных экранах; на витрине выключаем совсем.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'autosave'       => array(
				'label' => __( 'Автосохранение, секунд', 'xin-turbo' ),
				'note'  => __( 'По умолчанию WordPress пишет черновик раз в минуту. Две минуты — всё ещё безопасно, а записей вдвое меньше.', 'xin-turbo' ),
				'type'  => 'int',
			),
			'revisions'      => array(
				'label' => __( 'Сколько ревизий хранить', 'xin-turbo' ),
				'note'  => __( 'У главы, которую правили полгода, их набирается сотня — и все лежат в той же таблице, по которой идёт каталог.', 'xin-turbo' ),
				'type'  => 'int',
			),
			'no_xmlrpc'      => array(
				'label' => __( 'Закрыть XML-RPC', 'xin-turbo' ),
				'note'  => __( 'Сегодня им в основном перебирают пароли. Приложениям хватает REST. Выключите, если пользуетесь Jetpack или публикацией из редактора.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'no_pingback'    => array(
				'label' => __( 'Выключить пингбэки', 'xin-turbo' ),
				'note'  => __( 'Вместе с самопингованием — запросом сайта к самому себе на каждую публикацию.', 'xin-turbo' ),
				'type'  => 'bool',
			),
		),

		__( 'Уборка базы', 'xin-turbo' ) => array(
			'clean_db'   => array(
				'label' => __( 'Убирать по ночам', 'xin-turbo' ),
				'note'  => __( 'Истёкшие переходные значения, лишние ревизии, спам, мета без записи. Порциями, чтобы не положить сайт.', 'xin-turbo' ),
				'type'  => 'bool',
			),
			'keep_trash' => array(
				'label' => __( 'Держать корзину, дней', 'xin-turbo' ),
				'note'  => __( 'После этого срока выброшенное удаляется насовсем.', 'xin-turbo' ),
				'type'  => 'int',
			),
		),
	);
}

function xt_screen() {
	if ( ! current_user_can( xt_cap() ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-turbo' ), 403 );
	}

	$notice = xt_handle_post();
	$fields = xt_fields();
	$cache  = xt_cache_size();
	$db     = xt_db_report();
	$last   = get_option( 'xt_last_clean' );
	?>
	<div class="wrap xt-wrap">
		<h1><?php esc_html_e( 'Скорость', 'xin-turbo' ); ?></h1>
		<p class="xt-lede"><?php esc_html_e( 'Каждый переключатель здесь что-то выключает. Рядом написано что именно и чем за это платят — чтобы через полгода было понятно, почему сайт ведёт себя так.', 'xin-turbo' ); ?></p>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notice['kind'] ); ?> is-dismissible"><p><?php echo esc_html( $notice['text'] ); ?></p></div>
		<?php endif; ?>

		<div class="xt-tiles">
			<div class="xt-tile">
				<span class="xt-tile-value"><?php echo esc_html( number_format_i18n( $cache['files'] ) ); ?></span>
				<span class="xt-tile-label"><?php esc_html_e( 'страниц в кэше', 'xin-turbo' ); ?></span>
				<span class="xt-tile-note"><?php echo esc_html( size_format( $cache['bytes'] ) ); ?></span>
			</div>
			<div class="xt-tile">
				<span class="xt-tile-value"><?php echo esc_html( number_format_i18n( $db['revisions'] ) ); ?></span>
				<span class="xt-tile-label"><?php esc_html_e( 'ревизий', 'xin-turbo' ); ?></span>
			</div>
			<div class="xt-tile">
				<span class="xt-tile-value"><?php echo esc_html( number_format_i18n( $db['transients'] ) ); ?></span>
				<span class="xt-tile-label"><?php esc_html_e( 'истёкших значений', 'xin-turbo' ); ?></span>
			</div>
			<div class="xt-tile">
				<span class="xt-tile-value"><?php echo esc_html( number_format_i18n( $db['orphans'] ) ); ?></span>
				<span class="xt-tile-label"><?php esc_html_e( 'меты без записи', 'xin-turbo' ); ?></span>
			</div>
		</div>

		<div class="xt-state">
			<?php if ( xt_dropin_installed() ) : ?>
				<p class="xt-ok"><?php esc_html_e( 'Перехватчик установлен: страницы для гостей отдаются мимо WordPress.', 'xin-turbo' ); ?></p>
			<?php elseif ( xt_opt( 'page_cache' ) ) : ?>
				<p class="xt-warn"><?php esc_html_e( 'Кэш включён, но перехватчик не установлен: страницы всё равно собираются заново. Нажмите «Установить перехватчик».', 'xin-turbo' ); ?></p>
			<?php else : ?>
				<p class="xt-off"><?php esc_html_e( 'Кэш страниц выключен.', 'xin-turbo' ); ?></p>
			<?php endif; ?>

			<form method="post" class="xt-actions">
				<?php wp_nonce_field( 'xt_actions' ); ?>
				<button class="button" name="xt_do" value="install"><?php esc_html_e( 'Установить перехватчик', 'xin-turbo' ); ?></button>
				<button class="button" name="xt_do" value="remove"><?php esc_html_e( 'Убрать перехватчик', 'xin-turbo' ); ?></button>
				<button class="button" name="xt_do" value="purge"><?php esc_html_e( 'Очистить кэш', 'xin-turbo' ); ?></button>
				<button class="button" name="xt_do" value="clean"><?php esc_html_e( 'Убрать в базе сейчас', 'xin-turbo' ); ?></button>
			</form>

			<?php if ( is_array( $last ) && ! empty( $last['at'] ) ) : ?>
				<p class="xt-last">
					<?php
					printf(
						/* translators: %s: когда была последняя уборка. */
						esc_html__( 'Последняя уборка: %s', 'xin-turbo' ),
						esc_html( wp_date( 'j M Y, H:i', (int) $last['at'] ) )
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<form method="post">
			<?php wp_nonce_field( 'xt_save' ); ?>

			<?php foreach ( $fields as $group => $items ) : ?>
				<section class="xt-group">
					<h2><?php echo esc_html( $group ); ?></h2>

					<?php foreach ( $items as $key => $field ) : ?>
						<div class="xt-field">
							<?php if ( 'bool' === $field['type'] ) : ?>
								<label class="xt-switch">
									<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( (bool) xt_opt( $key ) ); ?>>
									<span class="xt-switch-label"><?php echo esc_html( $field['label'] ); ?></span>
								</label>
							<?php else : ?>
								<label class="xt-number">
									<span class="xt-switch-label"><?php echo esc_html( $field['label'] ); ?></span>
									<input type="number" min="0" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (int) xt_opt( $key ) ); ?>">
								</label>
							<?php endif; ?>

							<p class="xt-note"><?php echo esc_html( $field['note'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>

			<p class="submit">
				<button class="button button-primary" name="xt_do" value="save"><?php esc_html_e( 'Сохранить', 'xin-turbo' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Обработка обеих форм экрана.
 */
function xt_handle_post() {
	if ( empty( $_POST['xt_do'] ) ) {
		return null;
	}

	$do = sanitize_key( wp_unslash( $_POST['xt_do'] ) );

	if ( 'save' === $do ) {
		check_admin_referer( 'xt_save' );

		$values = array();

		foreach ( xt_fields() as $items ) {
			foreach ( $items as $key => $field ) {
				if ( 'bool' === $field['type'] ) {
					$values[ $key ] = ! empty( $_POST[ $key ] );
				} else {
					$values[ $key ] = isset( $_POST[ $key ] ) ? max( 0, (int) $_POST[ $key ] ) : 0;
				}
			}
		}

		xt_set( $values );
		xt_cache_clear();

		return array( 'kind' => 'success', 'text' => __( 'Сохранено. Кэш очищен, чтобы новые правила применились сразу.', 'xin-turbo' ) );
	}

	check_admin_referer( 'xt_actions' );

	switch ( $do ) {
		case 'install':
			$done = xt_install_dropin();

			if ( is_wp_error( $done ) ) {
				return array( 'kind' => 'error', 'text' => $done->get_error_message() );
			}

			xt_set( array( 'page_cache' => true ) );

			return array( 'kind' => 'success', 'text' => __( 'Перехватчик установлен, кэш включён.', 'xin-turbo' ) );

		case 'remove':
			xt_remove_dropin();
			xt_cache_clear();
			xt_set( array( 'page_cache' => false ) );

			return array( 'kind' => 'success', 'text' => __( 'Перехватчик убран, кэш выключен.', 'xin-turbo' ) );

		case 'purge':
			$count = xt_cache_clear();

			return array(
				'kind' => 'success',
				'text' => sprintf(
					/* translators: %s: сколько файлов убрано. */
					__( 'Кэш очищен: убрано файлов — %s.', 'xin-turbo' ),
					number_format_i18n( $count )
				),
			);

		case 'clean':
			$done = xt_db_clean();
			$sum  = array_sum( array_map( 'intval', (array) $done ) );

			return array(
				'kind' => 'success',
				'text' => sprintf(
					/* translators: %s: сколько строк убрано. */
					__( 'Уборка выполнена: строк убрано — %s.', 'xin-turbo' ),
					number_format_i18n( $sum )
				),
			);
	}

	return null;
}

/**
 * Кнопка сброса кэша в верхней панели — туда за ней и тянутся.
 */
function xt_admin_bar( $bar ) {
	if ( ! current_user_can( xt_cap() ) || ! xt_opt( 'page_cache' ) ) {
		return;
	}

	$bar->add_node( array(
		'id'    => 'xt-purge',
		'title' => __( 'Очистить кэш', 'xin-turbo' ),
		'href'  => wp_nonce_url( admin_url( 'options-general.php?page=xin-turbo&xt_purge=1' ), 'xt_purge_bar' ),
	) );
}
add_action( 'admin_bar_menu', 'xt_admin_bar', 100 );

function xt_purge_from_bar() {
	if ( empty( $_GET['xt_purge'] ) || ! current_user_can( xt_cap() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	check_admin_referer( 'xt_purge_bar' );
	xt_cache_clear();

	wp_safe_redirect( admin_url( 'options-general.php?page=xin-turbo' ) );
	exit;
}
add_action( 'admin_init', 'xt_purge_from_bar' );
