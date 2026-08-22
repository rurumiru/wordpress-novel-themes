<?php
/**
 * Экран «Импорт глав»: загрузка архива, замена текста, расписание проекта.
 *
 * Форма только заводит задание — работу делает пакетный обработчик, которого
 * браузер дёргает порциями. Поэтому здесь нет ни одного долгого запроса.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xni_can() {
	return current_user_can( 'edit_others_posts' );
}

function xni_weekdays() {
	return array(
		1 => __( 'Пн', 'xi-novel-import' ),
		2 => __( 'Вт', 'xi-novel-import' ),
		3 => __( 'Ср', 'xi-novel-import' ),
		4 => __( 'Чт', 'xi-novel-import' ),
		5 => __( 'Пт', 'xi-novel-import' ),
		6 => __( 'Сб', 'xi-novel-import' ),
		7 => __( 'Вс', 'xi-novel-import' ),
	);
}

/* -------------------------------------------------------------------------
 * AJAX
 * ---------------------------------------------------------------------- */

/**
 * Принимает архив и заводит задание. Ничего не импортирует.
 */
function xni_ajax_start() {
	check_ajax_referer( 'xni_job' );

	if ( ! xni_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'xi-novel-import' ) ) );
	}

	$type = isset( $_POST['type'] ) && 'fix' === $_POST['type'] ? 'fix' : 'import';

	if ( empty( $_FILES['zip']['name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['zip']['error'] ) {
		wp_send_json_error( array( 'message' => __( 'Архив не загрузился: сервер отверг отправку.', 'xi-novel-import' ) ) );
	}

	if ( ! is_uploaded_file( $_FILES['zip']['tmp_name'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Файл не похож на загруженный — отказываю.', 'xi-novel-import' ) ) );
	}

	$name = sanitize_file_name( $_FILES['zip']['name'] );
	if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
		wp_send_json_error( array( 'message' => __( 'Нужен ZIP-архив.', 'xi-novel-import' ) ) );
	}

	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;
	if ( ! $novel_id || 'novel' !== get_post_type( $novel_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Не выбран проект.', 'xi-novel-import' ) ) );
	}

	$unlock_raw = isset( $_POST['unlock_at'] ) ? sanitize_text_field( wp_unslash( $_POST['unlock_at'] ) ) : '';
	$unlock_at  = $unlock_raw ? (int) get_gmt_from_date( str_replace( 'T', ' ', $unlock_raw ) . ':00', 'U' ) : 0;

	$args = array(
		'novel_id'   => $novel_id,
		'status'     => isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'publish',
		'encoding'   => isset( $_POST['encoding'] ) ? sanitize_text_field( wp_unslash( $_POST['encoding'] ) ) : '',
		'start'      => isset( $_POST['start'] ) ? max( 1, (float) $_POST['start'] ) : 1,
		'skip_dupes' => ! empty( $_POST['skip_dupes'] ),
		'autosort'   => ! empty( $_POST['autosort'] ),
		'queue'      => ! empty( $_POST['queue'] ),
		'price'      => isset( $_POST['price'] ) ? max( 0, (float) $_POST['price'] ) : 0,
		'unlock_at'  => $unlock_at,
		'author_id'  => get_current_user_id(),
	);

	$job = xni_job_start( $_FILES['zip']['tmp_name'], $type, $args );

	if ( is_wp_error( $job ) ) {
		wp_send_json_error( array( 'message' => $job->get_error_message() ) );
	}

	wp_send_json_success( xni_job_public( $job ) );
}
add_action( 'wp_ajax_xni_start', 'xni_ajax_start' );

/**
 * Обрабатывает следующую порцию.
 */
function xni_ajax_step() {
	check_ajax_referer( 'xni_job' );

	if ( ! xni_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'xi-novel-import' ) ) );
	}

	$job = xni_job_step();

	if ( is_wp_error( $job ) ) {
		wp_send_json_error( array( 'message' => $job->get_error_message() ) );
	}

	wp_send_json_success( xni_job_public( $job ) );
}
add_action( 'wp_ajax_xni_step', 'xni_ajax_step' );

function xni_ajax_cancel() {
	check_ajax_referer( 'xni_job' );

	if ( ! xni_can() ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'xi-novel-import' ) ) );
	}

	xni_job_clear();
	wp_send_json_success();
}
add_action( 'wp_ajax_xni_cancel', 'xni_ajax_cancel' );

/**
 * То, что видит браузер: без путей на диске и без содержимого файлов.
 */
function xni_job_public( $job ) {
	return array(
		'type'    => $job['type'],
		'total'   => (int) $job['total'],
		'cursor'  => (int) $job['cursor'],
		'created' => (int) $job['created'],
		'updated' => (int) $job['updated'],
		'skipped' => (int) $job['skipped'],
		'failed'  => (int) $job['failed'],
		'done'    => ! empty( $job['done'] ),
		'log'     => array_slice( $job['log'], -40 ),
		/*
		 * Журнал показывается хвостом, и на архиве в сотню файлов пропуски из
		 * начала уезжали за край: «пропущено 4» было, а какие именно — нет.
		 * Поэтому всё, что не прошло, собирается отдельным списком.
		 */
		'issues'  => array_slice( array_values( array_filter( $job['log'], static function ( $row ) {
			return in_array( $row['state'], array( 'skipped', 'failed' ), true );
		} ) ), 0, 100 ),
	);
}

/* -------------------------------------------------------------------------
 * Расписание
 * ---------------------------------------------------------------------- */

function xni_save_schedule_post() {
	if ( ! xni_can() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-import' ) );
	}
	check_admin_referer( 'xni_schedule' );

	$novel_id = isset( $_POST['novel_id'] ) ? absint( $_POST['novel_id'] ) : 0;

	if ( $novel_id && 'novel' === get_post_type( $novel_id ) ) {
		$times = isset( $_POST['times'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['times'] ) ) ) : array();

		xni_save_schedule( $novel_id, array(
			'enabled' => ! empty( $_POST['enabled'] ),
			'days'    => isset( $_POST['days'] ) ? (array) wp_unslash( $_POST['days'] ) : array(),
			'times'   => array_map( 'trim', $times ),
			'free'    => ! empty( $_POST['free'] ),
			'price'   => isset( $_POST['price'] ) ? (float) $_POST['price'] : 0,
			'unlock'  => isset( $_POST['unlock'] ) ? absint( $_POST['unlock'] ) : 0,
		) );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'xni-import', 'novel' => $novel_id, 'saved' => 1 ), admin_url( 'tools.php' ) ) );
	exit;
}
add_action( 'admin_post_xni_schedule', 'xni_save_schedule_post' );

/* -------------------------------------------------------------------------
 * Экран
 * ---------------------------------------------------------------------- */

function xni_screen() {
	if ( ! xni_can() ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xi-novel-import' ) );
	}

	if ( ! xni_theme_ready() ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Импорт глав', 'xi-novel-import' ) . '</h1>';
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Тема XI Novels не активна: типов записей «Новеллы» и «Главы» не существует.', 'xi-novel-import' ) . '</p></div></div>';
		return;
	}

	$novels = xni_novels();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- выбор проекта.
	$current = isset( $_GET['novel'] ) ? absint( $_GET['novel'] ) : ( $novels ? (int) $novels[0]->ID : 0 );
	$sched   = $current ? xni_schedule( $current ) : xni_schedule( 0 );
	$next    = $current ? xni_next_release( $current ) : null;
	$job     = xni_job_get();
	?>
	<div class="wrap xni">
		<h1><?php esc_html_e( 'Импорт глав', 'xi-novel-import' ); ?></h1>

		<?php if ( ! $novels ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'Сначала заведите хотя бы один проект в разделе «Новеллы».', 'xi-novel-import' ); ?></p></div>
			</div>
			<?php
			return;
		endif;
		?>

		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Расписание сохранено.', 'xi-novel-import' ); ?></p></div>
		<?php endif; ?>

		<form class="xni-pick" method="get" action="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">
			<input type="hidden" name="page" value="xni-import">
			<label for="xni-novel"><strong><?php esc_html_e( 'Проект', 'xi-novel-import' ); ?></strong></label>
			<select name="novel" id="xni-novel" onchange="this.form.submit()">
				<?php foreach ( $novels as $novel ) : ?>
					<option value="<?php echo esc_attr( $novel->ID ); ?>" <?php selected( $current, (int) $novel->ID ); ?>><?php echo esc_html( $novel->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( $next ) : ?>
				<span class="xni-next">
					<?php
					printf(
						/* translators: 1: chapter title, 2: date and time. */
						esc_html__( 'Ближайшая публикация: «%1$s» — %2$s', 'xi-novel-import' ),
						esc_html( $next['title'] ),
						esc_html( wp_date( 'd.m.Y H:i', $next['slot'] ) )
					);
					?>
				</span>
			<?php endif; ?>
		</form>

		<div class="xni-grid">

			<div class="xni-card xni-card--main" id="xni-upload">
				<h2 class="xni-h"><span class="xni-h__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg></span><?php esc_html_e( 'Загрузка архива', 'xi-novel-import' ); ?></h2>
				<p class="description"><?php esc_html_e( 'ZIP с файлами .txt, .md, .html или .docx. Имя файла становится названием главы.', 'xi-novel-import' ); ?></p>

				<div class="xni-drop" data-xni-drop="import">
					<input type="file" accept=".zip" hidden>
					<b><?php esc_html_e( 'Перетащите ZIP сюда', 'xi-novel-import' ); ?></b>
					<span><?php esc_html_e( 'или нажмите, чтобы выбрать', 'xi-novel-import' ); ?></span>
					<em class="xni-picked"></em>
				</div>

				<p class="xni-hint">
					<?php esc_html_e( 'Файлы идут в порядке имён: 1 → 2 → 10, а не 1 → 10 → 2. Между главами ставится разбег в две секунды, чтобы они не слиплись одной меткой времени. Обрабатывается по десять штук за проход — сервер между порциями свободен.', 'xi-novel-import' ); ?>
				</p>

				<?php
				/*
				 * Раньше судьбу глав решали три поля в разных местах: список статуса,
				 * галочка очереди и цена — причём очередь молча перекрывала статус.
				 * Теперь это один выбор из трёх, а что именно получится, пишется
				 * словами внизу карточки и пересчитывается на лету.
				 */
				?>
				<h3><?php esc_html_e( 'Как выйдут главы', 'xi-novel-import' ); ?></h3>
				<div class="xni-modes">
					<label class="xni-mode">
						<input type="radio" name="xni-mode" value="publish" <?php checked( ! $sched['enabled'] ); ?>>
						<span class="xni-mode__body">
							<b><?php esc_html_e( 'Опубликовать сразу', 'xi-novel-import' ); ?></b>
							<small><?php esc_html_e( 'все главы появятся на сайте по окончании загрузки', 'xi-novel-import' ); ?></small>
						</span>
					</label>
					<label class="xni-mode">
						<input type="radio" name="xni-mode" value="draft">
						<span class="xni-mode__body">
							<b><?php esc_html_e( 'Сохранить черновиками', 'xi-novel-import' ); ?></b>
							<small><?php esc_html_e( 'ничего не публикуется, выпустите вручную', 'xi-novel-import' ); ?></small>
						</span>
					</label>
					<label class="xni-mode">
						<input type="radio" name="xni-mode" value="queue" <?php checked( $sched['enabled'] ); ?>>
						<span class="xni-mode__body">
							<b><?php esc_html_e( 'Поставить в очередь', 'xi-novel-import' ); ?></b>
							<small><?php esc_html_e( 'по одной в дни и часы из расписания', 'xi-novel-import' ); ?></small>
						</span>
					</label>
				</div>

				<table class="form-table">
					<tr>
						<th><label for="xni-start"><?php esc_html_e( 'Нумерация с', 'xi-novel-import' ); ?></label></th>
						<td><input type="number" id="xni-start" value="1" min="1" step="1" class="small-text">
							<span class="description"><?php esc_html_e( 'применяется к файлам, в имени которых номера нет', 'xi-novel-import' ); ?></span></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Поведение', 'xi-novel-import' ); ?></th>
						<td>
							<label><input type="checkbox" id="xni-skip" checked> <?php esc_html_e( 'Пропускать дубликаты', 'xi-novel-import' ); ?></label><br>
							<label><input type="checkbox" id="xni-autosort" checked> <?php esc_html_e( 'Пересортировать после загрузки', 'xi-novel-import' ); ?></label>
						</td>
					</tr>
				</table>

				<h3><?php esc_html_e( 'Платный доступ для всей пачки', 'xi-novel-import' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="xni-price"><?php esc_html_e( 'Цена главы', 'xi-novel-import' ); ?></label></th>
						<td>
							<input type="number" id="xni-price" value="0" min="0" step="0.01" class="small-text">
							<p class="description">
								<?php esc_html_e( '0 — глава бесплатная. Больше нуля — глава выходит под ранним доступом, цена сохраняется, и при включённом WooCommerce под неё создаётся товар.', 'xi-novel-import' ); ?>
								<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
									<br><em><?php esc_html_e( 'WooCommerce сейчас выключен — цена сохранится, но купить главу отдельно будет нельзя, доступ пойдёт по PLUS.', 'xi-novel-import' ); ?></em>
								<?php endif; ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><label for="xni-unlock"><?php esc_html_e( 'Открыть всем', 'xi-novel-import' ); ?></label></th>
						<td>
							<input type="datetime-local" id="xni-unlock">
							<p class="description"><?php esc_html_e( 'В этот момент замок снимется сам, а глава встанет в ленту обновлений сегодняшним числом.', 'xi-novel-import' ); ?></p>
						</td>
					</tr>
				</table>

				<?php
				$xni_day_names = xni_weekdays();
				$xni_day_list  = array();
				foreach ( $sched['days'] as $xni_d ) {
					if ( isset( $xni_day_names[ $xni_d ] ) ) {
						$xni_day_list[] = $xni_day_names[ $xni_d ];
					}
				}
				$xni_first = $current ? xni_next_slot( $sched, xni_queue_tail( $current ) ) : 0;
				?>
				<div class="xni-outcome" id="xni-outcome"
					data-days="<?php echo esc_attr( implode( ', ', $xni_day_list ) ); ?>"
					data-times="<?php echo esc_attr( implode( ', ', $sched['times'] ) ); ?>"
					data-first="<?php echo esc_attr( $xni_first ? wp_date( 'd.m.Y H:i', $xni_first ) : '' ); ?>"
					data-free="<?php echo $sched['free'] ? '1' : '0'; ?>">
					<span class="xni-outcome__tag"><?php esc_html_e( 'Что получится', 'xi-novel-import' ); ?></span>
					<p class="xni-outcome__text"></p>
				</div>

				<p class="xni-foot"><button type="button" class="button button-primary button-hero" data-xni-go="import"><?php esc_html_e( 'Загрузить архив', 'xi-novel-import' ); ?></button></p>
			</div>

			<div class="xni-side">

			<div class="xni-card" id="xni-fix">
				<h2 class="xni-h"><span class="xni-h__ico xni-h__ico--fix"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></span><?php esc_html_e( 'Замена текста', 'xi-novel-import' ); ?></h2>
				<p class="description"><?php esc_html_e( 'ZIP с файлами, которые заменят текст уже существующих глав. Сопоставление — по номеру в имени файла, иначе по названию. Дата публикации, статус, ярлык, порядок, цена и замок не меняются.', 'xi-novel-import' ); ?></p>

				<div class="xni-drop" data-xni-drop="fix">
					<input type="file" accept=".zip" hidden>
					<b><?php esc_html_e( 'Перетащите ZIP сюда', 'xi-novel-import' ); ?></b>
					<span><?php esc_html_e( 'только замена текста', 'xi-novel-import' ); ?></span>
					<em class="xni-picked"></em>
				</div>

				<p class="xni-hint"><?php esc_html_e( 'Например, «Глава 12.txt» и «Глава 12 — Тишина.txt» попадут в одну и ту же главу этого проекта. Большие архивы идут теми же порциями по десять.', 'xi-novel-import' ); ?></p>

				<p class="xni-foot"><button type="button" class="button button-secondary button-hero" data-xni-go="fix"><?php esc_html_e( 'Заменить текст', 'xi-novel-import' ); ?></button></p>
			</div>

			<div class="xni-card" id="xni-sched">
				<h2 class="xni-h"><span class="xni-h__ico xni-h__ico--sched"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span><?php esc_html_e( 'Расписание проекта', 'xi-novel-import' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'xni_schedule' ); ?>
					<input type="hidden" name="action" value="xni_schedule">
					<input type="hidden" name="novel_id" value="<?php echo esc_attr( $current ); ?>">

					<p><label><input type="checkbox" name="enabled" value="1" <?php checked( $sched['enabled'] ); ?>> <strong><?php esc_html_e( 'Выпускать главы по расписанию', 'xi-novel-import' ); ?></strong></label></p>

					<p style="margin-bottom:4px"><strong><?php esc_html_e( 'Дни', 'xi-novel-import' ); ?></strong></p>
					<div class="xni-days">
						<?php foreach ( xni_weekdays() as $num => $label ) : ?>
							<label class="xni-day"><input type="checkbox" name="days[]" value="<?php echo esc_attr( $num ); ?>" <?php checked( in_array( $num, $sched['days'], true ) ); ?>><?php echo esc_html( $label ); ?></label>
						<?php endforeach; ?>
					</div>

					<p style="margin-bottom:4px"><label for="xni-times"><strong><?php esc_html_e( 'Время', 'xi-novel-import' ); ?></strong></label></p>
					<p>
						<input type="text" id="xni-times" name="times" value="<?php echo esc_attr( implode( ', ', $sched['times'] ) ); ?>" placeholder="18:00, 21:30" style="width:100%;max-width:280px">
						<span class="description" style="display:block;margin-top:4px"><?php esc_html_e( 'через запятую, по часовому поясу сайта', 'xi-novel-import' ); ?></span>
					</p>

					<p><label><input type="checkbox" name="free" value="1" <?php checked( $sched['free'] ); ?>> <?php esc_html_e( 'Из очереди главы выходят сразу бесплатными', 'xi-novel-import' ); ?></label><br>
						<span class="description"><?php esc_html_e( 'Если снять — глава выйдет под ранним доступом PLUS.', 'xi-novel-import' ); ?></span></p>

					<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить расписание', 'xi-novel-import' ); ?></button></p>
				</form>

				<p class="xni-hint">
					<?php
					$tick = wp_next_scheduled( 'xni_tick' );
					if ( $tick ) {
						printf(
							/* translators: %s: time of the next tick. */
							esc_html__( 'Тик очереди: ближайший в %s. Раз в минуту — при условии, что wp-cron.php дёргает системный cron.', 'xi-novel-import' ),
							esc_html( wp_date( 'H:i:s', $tick ) )
						);
					} else {
						esc_html_e( 'Тик очереди не запланирован — перезайдите на страницу.', 'xi-novel-import' );
					}
					?>
				</p>
			</div>
			</div>
		</div>

		<div class="xni-progress" id="xni-progress" <?php echo $job && empty( $job['done'] ) ? '' : 'hidden'; ?>>
			<h2><?php esc_html_e( 'Обработка', 'xi-novel-import' ); ?></h2>
			<div class="xni-bar"><i></i></div>
			<p class="xni-stat"></p>
			<button type="button" class="button" data-xni-cancel><?php esc_html_e( 'Прервать', 'xi-novel-import' ); ?></button>

			<div class="xni-issues" hidden>
				<h3 class="xni-issues__head"></h3>
				<ul class="xni-log xni-log--issues"></ul>
			</div>

			<ul class="xni-log"></ul>
		</div>
	</div>
	<?php
}
