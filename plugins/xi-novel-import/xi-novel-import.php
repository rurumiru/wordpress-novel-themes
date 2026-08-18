<?php
/**
 * Plugin Name: XI Novels — импорт глав
 * Plugin URI: https://github.com/rurumiru/wordpress-novel-themes
 * Description: Массовый импорт глав из .docx, .txt, .md, .html, ZIP-архивов и Google Docs. Работает с типами записей темы XI Novels.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: XI Community
 * Author URI: https://xi.community/
 * License: GPL-2.0-or-later
 * Text Domain: xi-novel-import
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XNI_VERSION', '1.0.0' );
define( 'XNI_DIR', plugin_dir_path( __FILE__ ) );

require_once XNI_DIR . 'includes/parser.php';
require_once XNI_DIR . 'includes/importer.php';

function xni_menu() {
	add_management_page(
		__( 'Импорт глав', 'xi-novel-import' ),
		__( 'Импорт глав', 'xi-novel-import' ),
		'edit_others_posts',
		'xni-import',
		'xni_screen'
	);
}
add_action( 'admin_menu', 'xni_menu' );

function xni_handle() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-import' ) );
	}
	check_admin_referer( 'xni_import' );

	$args = array(
		'novel_id'    => isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0,
		'novel_title' => isset( $_POST['novel_title'] ) ? sanitize_text_field( wp_unslash( $_POST['novel_title'] ) ) : '',
		'status'      => isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'publish',
		'start'       => isset( $_POST['start'] ) ? (float) $_POST['start'] : 0,
		'locked_from' => isset( $_POST['locked_from'] ) ? (float) $_POST['locked_from'] : 0,
	);

	$encoding = isset( $_POST['encoding'] ) ? sanitize_text_field( wp_unslash( $_POST['encoding'] ) ) : '';
	$gdoc     = isset( $_POST['gdoc'] ) ? esc_url_raw( wp_unslash( $_POST['gdoc'] ) ) : '';
	$chapters = array();
	$errors   = array();

	if ( ! empty( $_FILES['files']['name'][0] ) ) {
		$count = count( $_FILES['files']['name'] );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( UPLOAD_ERR_OK !== (int) $_FILES['files']['error'][ $i ] ) {
				$errors[] = sprintf( __( 'Файл «%s» не загрузился: сервер отверг отправку.', 'xi-novel-import' ), sanitize_file_name( $_FILES['files']['name'][ $i ] ) );
				continue;
			}

			$name = sanitize_file_name( $_FILES['files']['name'][ $i ] );
			$tmp  = $_FILES['files']['tmp_name'][ $i ];
			$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

			if ( ! in_array( $ext, xni_supported_ext(), true ) ) {
				$errors[] = sprintf( __( 'Формат .%s не поддерживается.', 'xi-novel-import' ), $ext );
				continue;
			}

			$parsed = 'zip' === $ext ? xni_parse_zip( $tmp, $encoding ) : xni_parse_file( $tmp, $name, $encoding );

			if ( is_wp_error( $parsed ) ) {
				$errors[] = $parsed->get_error_message();
				continue;
			}

			$chapters = array_merge( $chapters, isset( $parsed['content'] ) ? array( $parsed ) : $parsed );
		}
	}

	if ( $gdoc ) {
		$parsed = xni_google_doc( $gdoc );
		if ( is_wp_error( $parsed ) ) {
			$errors[] = $parsed->get_error_message();
		} else {
			$chapters = array_merge( $chapters, $parsed );
		}
	}

	if ( ! $chapters ) {
		$errors[] = __( 'Нечего импортировать: файлы не выбраны или не разобрались.', 'xi-novel-import' );
		set_transient( 'xni_report_' . get_current_user_id(), array( 'errors' => $errors ), 5 * MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'tools.php?page=xni-import&done=0' ) );
		exit;
	}

	$result = xni_import( $chapters, $args );

	if ( is_wp_error( $result ) ) {
		$errors[] = $result->get_error_message();
		set_transient( 'xni_report_' . get_current_user_id(), array( 'errors' => $errors ), 5 * MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'tools.php?page=xni-import&done=0' ) );
		exit;
	}

	$result['errors'] = $errors;
	set_transient( 'xni_report_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );

	wp_safe_redirect( admin_url( 'tools.php?page=xni-import&done=1' ) );
	exit;
}
add_action( 'admin_post_xni_import', 'xni_handle' );

function xni_screen() {
	$report = get_transient( 'xni_report_' . get_current_user_id() );
	if ( $report ) {
		delete_transient( 'xni_report_' . get_current_user_id() );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт глав', 'xi-novel-import' ); ?></h1>

		<?php if ( ! xni_theme_ready() ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Тема XI Novels не активна — импортировать некуда: типов записей «Новеллы» и «Главы» не существует.', 'xi-novel-import' ); ?></p></div>
			</div>
			<?php
			return;
		endif;
		?>

		<?php if ( $report ) : ?>
			<?php if ( ! empty( $report['errors'] ) ) : ?>
				<div class="notice notice-warning">
					<?php foreach ( $report['errors'] as $error ) : ?>
						<p><?php echo esc_html( $error ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $report['created'] ) ) : ?>
				<div class="notice notice-success">
					<p>
						<?php
						printf(
							/* translators: 1: created, 2: updated, 3: title link */
							esc_html__( 'Создано глав: %1$d, обновлено: %2$d. Проект: %3$s', 'xi-novel-import' ),
							(int) $report['created'],
							(int) $report['updated'],
							'<a href="' . esc_url( get_permalink( $report['novel_id'] ) ) . '">' . esc_html( get_the_title( $report['novel_id'] ) ) . '</a>'
						);
						?>
					</p>
				</div>

				<?php if ( ! empty( $report['report'] ) ) : ?>
					<table class="widefat striped" style="max-width:760px;margin-bottom:24px">
						<thead>
							<tr>
								<th style="width:70px"><?php esc_html_e( 'Номер', 'xi-novel-import' ); ?></th>
								<th><?php esc_html_e( 'Глава', 'xi-novel-import' ); ?></th>
								<th style="width:180px"><?php esc_html_e( 'Файл', 'xi-novel-import' ); ?></th>
								<th style="width:110px"><?php esc_html_e( 'Что сделано', 'xi-novel-import' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $report['report'] as $row ) : ?>
								<tr>
									<td><?php echo esc_html( rtrim( rtrim( number_format( $row['number'], 1, '.', '' ), '0' ), '.' ) ); ?></td>
									<td><?php echo esc_html( $row['title'] ); ?></td>
									<td><?php echo esc_html( $row['source'] ); ?></td>
									<td><?php echo $row['new'] ? esc_html__( 'создана', 'xi-novel-import' ) : esc_html__( 'обновлена', 'xi-novel-import' ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'xni_import' ); ?>
			<input type="hidden" name="action" value="xni_import">

			<h2 class="title"><?php esc_html_e( 'Куда', 'xi-novel-import' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="xni-novel"><?php esc_html_e( 'Проект', 'xi-novel-import' ); ?></label></th>
					<td>
						<select name="novel_id" id="xni-novel">
							<option value="0"><?php esc_html_e( '— создать новый —', 'xi-novel-import' ); ?></option>
							<?php foreach ( xni_novels() as $novel ) : ?>
								<option value="<?php echo (int) $novel->ID; ?>"><?php echo esc_html( $novel->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Если выбрано «создать новый» — заполните название ниже.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="xni-title"><?php esc_html_e( 'Название нового проекта', 'xi-novel-import' ); ?></label></th>
					<td><input type="text" id="xni-title" name="novel_title" class="regular-text"></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Откуда', 'xi-novel-import' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="xni-files"><?php esc_html_e( 'Файлы', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="file" id="xni-files" name="files[]" multiple accept=".txt,.md,.html,.htm,.docx,.zip">
						<p class="description">
							<?php esc_html_e( '.docx, .txt, .md, .html или ZIP с ними. Номер и название берутся из имени файла: «001. Десятый.docx», «Глава 12.5 — Экстра.txt».', 'xi-novel-import' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="xni-gdoc"><?php esc_html_e( 'Google Docs', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="url" id="xni-gdoc" name="gdoc" class="regular-text" placeholder="https://docs.google.com/document/d/…">
						<p class="description"><?php esc_html_e( 'Документ должен быть открыт по ссылке или опубликован: Файл → Поделиться → Опубликовать в интернете.', 'xi-novel-import' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Как', 'xi-novel-import' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="xni-start"><?php esc_html_e( 'Начать с номера', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="number" step="0.1" id="xni-start" name="start" class="small-text" placeholder="<?php esc_attr_e( 'авто', 'xi-novel-import' ); ?>">
						<span class="description"><?php esc_html_e( 'Пусто — продолжить нумерацию проекта.', 'xi-novel-import' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="xni-locked"><?php esc_html_e( 'Ранний доступ с номера', 'xi-novel-import' ); ?></label></th>
					<td>
						<input type="number" step="0.1" id="xni-locked" name="locked_from" class="small-text">
						<span class="description"><?php esc_html_e( 'Главы с этого номера получат отметку PLUS.', 'xi-novel-import' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="xni-status"><?php esc_html_e( 'Статус', 'xi-novel-import' ); ?></label></th>
					<td>
						<select name="status" id="xni-status">
							<option value="publish"><?php esc_html_e( 'Опубликовать', 'xi-novel-import' ); ?></option>
							<option value="draft"><?php esc_html_e( 'Черновики', 'xi-novel-import' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="xni-encoding"><?php esc_html_e( 'Кодировка текстовых файлов', 'xi-novel-import' ); ?></label></th>
					<td>
						<select name="encoding" id="xni-encoding">
							<option value=""><?php esc_html_e( 'определить самому', 'xi-novel-import' ); ?></option>
							<option value="UTF-8">UTF-8</option>
							<option value="windows-1251">windows-1251</option>
							<option value="koi8-r">koi8-r</option>
						</select>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Импортировать', 'xi-novel-import' ); ?></button>
			</p>

			<p class="description" style="max-width:640px">
				<?php
				printf(
					/* translators: %s: upload_max_filesize value */
					esc_html__( 'Предел загрузки на этом сервере: %s. Если файлы больше — поднимите upload_max_filesize и post_max_size или загружайте частями.', 'xi-novel-import' ),
					esc_html( ini_get( 'upload_max_filesize' ) )
				);
				?>
			</p>
		</form>
	</div>
	<?php
}
