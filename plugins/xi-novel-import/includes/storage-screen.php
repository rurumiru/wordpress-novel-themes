<?php
/**
 * Настройки хранилища на экране «Импорт глав».
 *
 * Отдельно от `storage.php`: там подпись, запросы и выгрузка, здесь форма,
 * кнопки и разбор POST. Смешивать протокол с версткой — верный способ потом
 * не найти ни того, ни другого.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Сохраняет настройки хранилища.
 *
 * Пустое поле секрета означает «не трогать»: иначе каждое сохранение формы, где
 * секрет по понятным причинам не подставлен, стирало бы рабочий доступ.
 *
 * @return void
 */
function xni_s3_save_post() {
	if ( ! xni_can() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-import' ) );
	}

	check_admin_referer( 'xni_s3' );

	$old    = xni_s3_settings();
	$secret = isset( $_POST['secret'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['secret'] ) ) ) : '';

	$settings = array(
		'enabled'    => empty( $_POST['enabled'] ) ? 0 : 1,
		'endpoint'   => isset( $_POST['endpoint'] ) ? esc_url_raw( trim( wp_unslash( $_POST['endpoint'] ) ) ) : '',
		'region'     => isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : 'us-east-1',
		'bucket'     => isset( $_POST['bucket'] ) ? sanitize_text_field( wp_unslash( $_POST['bucket'] ) ) : '',
		'key'        => isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '',
		'secret'     => '' === $secret ? $old['secret'] : $secret,
		'prefix'     => isset( $_POST['prefix'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['prefix'] ) ), '/ ' ) : 'comics',
		'base_url'   => isset( $_POST['base_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['base_url'] ) ) ) : '',
		'path_style' => empty( $_POST['path_style'] ) ? 0 : 1,
		'keep_local' => empty( $_POST['keep_local'] ) ? 0 : 1,
		'public_acl' => empty( $_POST['public_acl'] ) ? 0 : 1,
		'mirrors'    => isset( $_POST['mirrors'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mirrors'] ) ) : '',
	);

	update_option( XNI_S3_OPTION, $settings );

	wp_safe_redirect( add_query_arg(
		array(
			'page'     => 'xni-import',
			's3_saved' => 1,
		),
		admin_url( 'tools.php' )
	) );
	exit;
}
add_action( 'admin_post_xni_s3', 'xni_s3_save_post' );

/**
 * Проверка связи по кнопке.
 *
 * @return void
 */
function xni_s3_ajax_probe() {
	check_ajax_referer( 'xni_job' );

	if ( ! xni_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'xi-novel-import' ) ) );
	}

	$result = xni_s3_probe();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => __( 'Связь есть: пробная запись, чтение и удаление прошли.', 'xi-novel-import' ) ) );
}
add_action( 'wp_ajax_xni_s3_probe', 'xni_s3_ajax_probe' );

/**
 * Догружает в хранилище то, что уже лежит на диске.
 *
 * Порциями: страницы уходят через память и по сети, и попытка отправить всю
 * площадку за один запрос упёрлась бы в лимит времени.
 *
 * @return void
 */
function xni_s3_ajax_sync() {
	check_ajax_referer( 'xni_job' );

	if ( ! xni_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'xi-novel-import' ) ) );
	}

	if ( ! xni_s3_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Выгрузка выключена: включите её и сохраните настройки.', 'xi-novel-import' ) ) );
	}

	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'posts_per_page' => 40,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_xin_format',
				'value' => 'comic',
			),
		),
	) );

	$done   = 0;
	$failed = 0;
	$errors = array();

	foreach ( $chapters as $chapter_id ) {
		$report  = xni_s3_offload_chapter( (int) $chapter_id );
		$done   += $report['ok'];
		$failed += $report['failed'];
		$errors  = array_merge( $errors, $report['errors'] );

		if ( $done >= 20 ) {
			break;
		}
	}

	wp_send_json_success( array(
		'message' => sprintf(
			/* translators: 1 — сколько страниц выгружено, 2 — сколько не удалось. */
			__( 'Выгружено страниц: %1$d. Не удалось: %2$d.', 'xi-novel-import' ),
			$done,
			$failed
		),
		'errors'  => array_slice( array_values( array_unique( $errors ) ), 0, 5 ),
	) );
}
add_action( 'wp_ajax_xni_s3_sync', 'xni_s3_ajax_sync' );

/**
 * Сколько страниц уже в хранилище и сколько глав комиксов на сайте.
 *
 * @return array{chapters: int, uploaded: int}
 */
function xni_s3_stats() {
	global $wpdb;

	$chapters = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_xin_format' AND pm.meta_value = 'comic' AND p.post_type = 'chapter'"
	);

	$uploaded = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_xni_s3_key'"
	);

	return array(
		'chapters' => $chapters,
		'uploaded' => $uploaded,
	);
}

/**
 * Блок настроек хранилища.
 *
 * @return void
 */
function xni_s3_screen_section() {
	$s      = xni_s3_settings();
	$stats  = xni_s3_stats();
	$locked = defined( 'XNI_S3_KEY' ) || defined( 'XNI_S3_SECRET' );
	$saved  = isset( $_GET['s3_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="xni-card">
		<h2><?php esc_html_e( 'Хранилище S3 для страниц комиксов', 'xi-novel-import' ); ?></h2>

		<p class="description">
			<?php esc_html_e( 'Глава комикса — это десятки картинок. В хранилище они лежат дешевле и раздаются с его домена или с CDN, а не с диска сайта. Подходит любое S3-совместимое: Amazon S3, Yandex Object Storage, Cloudflare R2, MinIO, Selectel.', 'xi-novel-import' ); ?>
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success inline"><p><?php esc_html_e( 'Настройки хранилища сохранены.', 'xi-novel-import' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $locked ) : ?>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Ключ или секрет заданы константами в wp-config.php — они сильнее полей формы. Это правильный способ: в базе доступа тогда не остаётся.', 'xi-novel-import' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'xni_s3' ); ?>
			<input type="hidden" name="action" value="xni_s3">

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Выгрузка', 'xi-novel-import' ); ?></th>
					<td>
						<label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'] ); ?>> <?php esc_html_e( 'Отправлять страницы комиксов в хранилище', 'xi-novel-import' ); ?></label>
						<p class="description"><?php esc_html_e( 'Страница уходит сразу после того, как её привязали к главе. Текстовые главы и остальная медиатека не затрагиваются.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="xni-s3-endpoint"><?php esc_html_e( 'Адрес хранилища', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="url" id="xni-s3-endpoint" name="endpoint" value="<?php echo esc_attr( $s['endpoint'] ); ?>" class="regular-text" placeholder="https://storage.yandexcloud.net">
						<p class="description"><?php esc_html_e( 'Для Amazon S3 — адрес вида https://s3.eu-central-1.amazonaws.com', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="xni-s3-bucket"><?php esc_html_e( 'Бакет', 'xi-novel-import' ); ?></label></th>
					<td><input type="text" id="xni-s3-bucket" name="bucket" value="<?php echo esc_attr( $s['bucket'] ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th><label for="xni-s3-region"><?php esc_html_e( 'Регион', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="text" id="xni-s3-region" name="region" value="<?php echo esc_attr( $s['region'] ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Участвует в подписи: неверный регион даёт отказ SignatureDoesNotMatch. У MinIO обычно us-east-1.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="xni-s3-key"><?php esc_html_e( 'Ключ доступа', 'xi-novel-import' ); ?></label></th>
					<td><input type="text" id="xni-s3-key" name="key" value="<?php echo esc_attr( $s['key'] ); ?>" class="regular-text" autocomplete="off" <?php disabled( defined( 'XNI_S3_KEY' ) ); ?>></td>
				</tr>
				<tr>
					<th><label for="xni-s3-secret"><?php esc_html_e( 'Секретный ключ', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="password" id="xni-s3-secret" name="secret" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo $s['secret'] ? esc_attr__( 'сохранён — оставьте пустым', 'xi-novel-import' ) : ''; ?>" <?php disabled( defined( 'XNI_S3_SECRET' ) ); ?>>
						<p class="description"><?php esc_html_e( 'Пустое поле не стирает сохранённый секрет. Надёжнее задать XNI_S3_KEY и XNI_S3_SECRET в wp-config.php.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="xni-s3-prefix"><?php esc_html_e( 'Папка в бакете', 'xi-novel-import' ); ?></label></th>
					<td><input type="text" id="xni-s3-prefix" name="prefix" value="<?php echo esc_attr( $s['prefix'] ); ?>" class="regular-text" placeholder="comics"></td>
				</tr>
				<tr>
					<th><label for="xni-s3-base"><?php esc_html_e( 'Публичный адрес', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="url" id="xni-s3-base" name="base_url" value="<?php echo esc_attr( $s['base_url'] ); ?>" class="regular-text" placeholder="https://cdn.example.com">
						<p class="description"><?php esc_html_e( 'Домен CDN перед бакетом. Пусто — страницы отдаются прямо с адреса хранилища.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="xni-s3-mirrors"><?php esc_html_e( 'Зеркала раздачи', 'xi-novel-import' ); ?></label></th>
					<td>
						<textarea id="xni-s3-mirrors" name="mirrors" rows="3" class="large-text code" placeholder="Сервер 1|https://cdn1.example.com&#10;Сервер 2|https://cdn2.example.com"><?php echo esc_textarea( $s['mirrors'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'По одному на строку, «Название|адрес». В читалке появится выбор сервера, с которого грузить страницы: хранилище может тормозить или быть недоступно из чьей-то сети, и читатель переключится сам. Зеркало предлагается только тем главам, что выгружены целиком.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Поведение', 'xi-novel-import' ); ?></th>
					<td>
						<label><input type="checkbox" name="path_style" value="1" <?php checked( $s['path_style'] ); ?>> <?php esc_html_e( 'Бакет в пути адреса, а не в поддомене', 'xi-novel-import' ); ?></label>
						<p class="description"><?php esc_html_e( 'Нужно для MinIO и большинства своих хранилищ. Amazon S3 работает и так, и так.', 'xi-novel-import' ); ?></p>
						<label><input type="checkbox" name="public_acl" value="1" <?php checked( $s['public_acl'] ); ?>> <?php esc_html_e( 'Помечать страницы общедоступными при загрузке', 'xi-novel-import' ); ?></label>
						<p class="description"><?php esc_html_e( 'Без этого браузер получит от хранилища 403: запрос картинки со страницы не подписан. Если провайдер отключил ACL на бакете (так делают Cloudflare R2 и новые бакеты Amazon), снимите флаг и откройте доступ политикой бакета.', 'xi-novel-import' ); ?></p>
						<label><input type="checkbox" name="keep_local" value="1" <?php checked( $s['keep_local'] ); ?>> <?php esc_html_e( 'Оставлять копию на диске сайта', 'xi-novel-import' ); ?></label>
						<p class="description"><?php esc_html_e( 'Снимите — освободится место, но единственная копия страницы будет в хранилище. Локальный файл удаляется только после успешной выгрузки.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
			</table>

			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить хранилище', 'xi-novel-import' ); ?></button></p>
		</form>

		<p>
			<button type="button" class="button" data-xni-s3="probe"><?php esc_html_e( 'Проверить связь', 'xi-novel-import' ); ?></button>
			<button type="button" class="button" data-xni-s3="sync"><?php esc_html_e( 'Догрузить существующие', 'xi-novel-import' ); ?></button>
			<span class="xni-s3-result" style="margin-left:8px"></span>
		</p>

		<p class="description">
			<?php
			printf(
				/* translators: 1 — сколько страниц уже в хранилище, 2 — сколько глав комиксов. */
				esc_html__( 'Страниц в хранилище: %1$d. Глав комиксов на сайте: %2$d.', 'xi-novel-import' ),
				(int) $stats['uploaded'],
				(int) $stats['chapters']
			);
			?>
			<?php esc_html_e( '«Догрузить существующие» отправляет по двадцать страниц за нажатие, чтобы не упереться в лимит времени.', 'xi-novel-import' ); ?>
		</p>
	</div>
	<?php
}
