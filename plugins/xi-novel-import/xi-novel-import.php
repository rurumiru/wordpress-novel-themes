<?php
/**
 * Plugin Name: XI Novels — импорт глав
 * Plugin URI: https://github.com/rurumiru/wordpress-novel-themes
 * Description: Массовый импорт глав из .docx, .txt, .md, .html, ZIP-архивов и Google Docs — порциями, без нагрузки на сервер. Замена текста в существующих главах, очередь публикации по расписанию, авторазблокировка платных глав и таймер до следующего выпуска.
 * Version: 1.1.0
 * Requires PHP: 7.4
 * Author: XI Community
 * Author URI: https://xi.community/
 * License: GPL-2.0-or-later
 * Text Domain: xi-novel-import
 * Domain Path: /languages
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XNI_VERSION', '1.1.0' );
define( 'XNI_URL', plugin_dir_url( __FILE__ ) );
define( 'XNI_DIR', plugin_dir_path( __FILE__ ) );

require_once XNI_DIR . 'includes/parser.php';
require_once XNI_DIR . 'includes/importer.php';
require_once XNI_DIR . 'includes/schedule.php';
require_once XNI_DIR . 'includes/batch.php';
require_once XNI_DIR . 'includes/fix.php';
require_once XNI_DIR . 'includes/screen.php';
require_once XNI_DIR . 'includes/countdown.php';
require_once XNI_DIR . 'includes/studio.php';

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

function xni_assets( $hook ) {
	if ( 'tools_page_xni-import' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'xni', XNI_URL . 'assets/import.css', array(), XNI_VERSION );
	wp_enqueue_script( 'xni', XNI_URL . 'assets/import.js', array(), XNI_VERSION, true );

	wp_localize_script( 'xni', 'XNI', array(
		'ajax'       => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'xni_job' ),
		'needZip'    => __( 'Нужен ZIP-архив.', 'xi-novel-import' ),
		'pickFirst'  => __( 'Сначала выберите архив.', 'xi-novel-import' ),
		'uploading'  => __( 'Загружаю архив…', 'xi-novel-import' ),
		'finished'   => __( 'Готово.', 'xi-novel-import' ),
		'cancelled'  => __( 'Прервано.', 'xi-novel-import' ),
		'failed'     => __( 'Не получилось — попробуйте ещё раз.', 'xi-novel-import' ),
		'issues'     => __( 'Не прошло: %s — разберитесь с этими файлами', 'xi-novel-import' ),
		// Фразы для строки «что получится»: собираются в браузере по мере того,
		// как меняются переключатели, поэтому каждая переводится отдельно.
		'outPublish' => __( 'Главы появятся на сайте сразу после загрузки.', 'xi-novel-import' ),
		'outDraft'   => __( 'Главы лягут черновиками: на сайте их не будет, пока вы не опубликуете их сами.', 'xi-novel-import' ),
		'outQueue'   => __( 'Главы встанут в очередь. Первая выйдет %1$s, дальше по одной: %2$s в %3$s.', 'xi-novel-import' ),
		'outNoSched' => __( 'Главы встанут в очередь, но расписание не задано — заполните дни и время справа.', 'xi-novel-import' ),
		'outFree'    => __( 'Открыты всем.', 'xi-novel-import' ),
		'outPaid'    => __( 'Под ранним доступом PLUS, цена %s.', 'xi-novel-import' ),
		'outLocked'  => __( 'Под ранним доступом PLUS, без цены.', 'xi-novel-import' ),
		'outUnlock'  => __( 'Замок снимется %s и глава встанет в ленту сегодняшним числом.', 'xi-novel-import' ),
		'confirmFix' => __( 'Заменить текст глав этого проекта содержимым архива? Даты, статусы и цены не изменятся.', 'xi-novel-import' ),
		/* translators: 1: done, 2: total, 3: created, 4: updated, 5: skipped, 6: failed. */
		'progress'   => __( '%1$s из %2$s · создано %3$s · обновлено %4$s · пропущено %5$s · ошибок %6$s', 'xi-novel-import' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'xni_assets' );

function xni_load_textdomain() {
	load_plugin_textdomain( 'xi-novel-import', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'xni_load_textdomain' );

register_deactivation_hook( __FILE__, 'xni_deactivate_cron' );
