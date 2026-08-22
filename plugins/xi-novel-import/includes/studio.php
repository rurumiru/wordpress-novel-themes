<?php
/**
 * Очередь глазами автора: в кабинете, а не в админке.
 *
 * Список глав показывал у отложенной главы «Черновик» и дату создания — по
 * такой строке не понять ни того, что глава ждёт выхода, ни когда он будет.
 * А расписание проекта жило только в админке, куда автору ходить незачем.
 * Обе точки тема отдаёт хуками, плагин их заполняет.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Дописывает состояние главы, стоящей в очереди.
 *
 * @param array $state   badges и date от темы.
 * @param int   $chapter Глава.
 */
function xni_chapter_state( $state, $chapter ) {
	$slot = (int) get_post_meta( $chapter, XNI_SLOT, true );

	if ( ! $slot ) {
		return $state;
	}

	// Значок «Черновик» здесь только запутывает: глава не забыта, она ждёт срок.
	$state['badges'] = array_values( array_filter( (array) $state['badges'], static function ( $badge ) {
		return 'PLUS' === $badge['text'];
	} ) );

	$free = (bool) get_post_meta( $chapter, XNI_ON_FREE, true );

	// Значок PLUS у главы уже может стоять — второй такой же рядом только шумит.
	// Поэтому платность уходит в текст одного значка, а не в отдельный.
	$has_plus = (bool) $state['badges'];

	$state['badges'][] = array(
		'text'  => ( $free || $has_plus )
			? __( 'В очереди', 'xi-novel-import' )
			: __( 'В очереди, по PLUS', 'xi-novel-import' ),
		'class' => 'xin-badge--primary',
		'icon'  => 'clock',
	);

	$left = $slot - time();

	/* translators: %s: date and time the chapter goes out. */
	$state['date'] = sprintf( __( 'Выйдет %s', 'xi-novel-import' ), wp_date( 'j M Y, H:i', $slot ) );

	/* translators: %s: how long is left, already formatted. */
	$state['note'] = $left > 0 ? sprintf( __( 'через %s', 'xi-novel-import' ), xni_human_left( $left ) ) : '';

	return $state;
}
add_filter( 'xin_chapter_state', 'xni_chapter_state', 10, 2 );

/**
 * «2 дня 3 часа» — коротко, без секунд: в списке они ни к чему.
 */
function xni_human_left( $seconds ) {
	$seconds = max( 0, (int) $seconds );
	$days    = (int) floor( $seconds / DAY_IN_SECONDS );
	$hours   = (int) floor( ( $seconds % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
	$mins    = (int) floor( ( $seconds % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

	if ( $days > 0 ) {
		/* translators: 1: days, 2: hours. */
		return sprintf( __( '%1$d дн. %2$d ч.', 'xi-novel-import' ), $days, $hours );
	}
	if ( $hours > 0 ) {
		/* translators: 1: hours, 2: minutes. */
		return sprintf( __( '%1$d ч. %2$d мин.', 'xi-novel-import' ), $hours, $mins );
	}

	/* translators: %d: minutes. */
	return sprintf( __( '%d мин.', 'xi-novel-import' ), $mins );
}

/* -------------------------------------------------------------------------
 * Расписание в настройках проекта
 * ---------------------------------------------------------------------- */

/**
 * Блок расписания в форме проекта в кабинете автора.
 *
 * @param int $novel_id Проект. У нового ещё 0 — тогда показывать нечего.
 */
function xni_novel_form_extra( $novel_id ) {
	if ( ! $novel_id ) {
		echo '<div class="xin-field"><p class="xin-field__hint">'
			. esc_html__( 'Расписание выхода глав можно будет задать сразу после сохранения проекта.', 'xi-novel-import' )
			. '</p></div>';
		return;
	}

	$sched   = xni_schedule( $novel_id );
	$next    = xni_next_release( $novel_id );
	$queued  = xni_queued_count( $novel_id );
	$days    = xni_weekdays();
	?>
	<div class="xin-field xni-sched-field">
		<label><?php esc_html_e( 'Расписание выхода', 'xi-novel-import' ); ?></label>

		<div class="xin-checks" style="margin-bottom:10px">
			<label class="xin-check">
				<input type="checkbox" name="xni_sched_on" value="1" <?php checked( $sched['enabled'] ); ?>>
				<?php esc_html_e( 'Выпускать главы по расписанию', 'xi-novel-import' ); ?>
			</label>
			<label class="xin-check">
				<input type="checkbox" name="xni_sched_free" value="1" <?php checked( $sched['free'] ); ?>>
				<?php esc_html_e( 'Из очереди выходят бесплатными', 'xi-novel-import' ); ?>
			</label>
		</div>

		<div class="xin-checks" style="margin-bottom:10px">
			<?php foreach ( $days as $xni_num => $xni_label ) : ?>
				<label class="xin-check">
					<input type="checkbox" name="xni_sched_days[]" value="<?php echo esc_attr( $xni_num ); ?>" <?php checked( in_array( $xni_num, $sched['days'], true ) ); ?>>
					<?php echo esc_html( $xni_label ); ?>
				</label>
			<?php endforeach; ?>
		</div>

		<input class="form-control" type="text" name="xni_sched_times"
			value="<?php echo esc_attr( implode( ', ', $sched['times'] ) ); ?>" placeholder="18:00, 21:30">
		<p class="xin-field__hint">
			<?php esc_html_e( 'Время через запятую, по часовому поясу сайта.', 'xi-novel-import' ); ?>
			<?php if ( $queued ) : ?>
				<br><b>
				<?php
				printf(
					/* translators: %s: number of chapters waiting. */
					esc_html__( 'В очереди сейчас: %s', 'xi-novel-import' ),
					esc_html( number_format_i18n( $queued ) )
				);
				?>
				</b>
				<?php if ( $next ) : ?>
					—
					<?php
					printf(
						/* translators: 1: chapter title, 2: date and time. */
						esc_html__( 'ближайшая «%1$s» выйдет %2$s', 'xi-novel-import' ),
						esc_html( $next['title'] ),
						esc_html( wp_date( 'j M Y, H:i', $next['slot'] ) )
					);
					?>
				<?php endif; ?>
			<?php endif; ?>
		</p>
	</div>
	<?php
}
add_action( 'xin_novel_form_extra', 'xni_novel_form_extra' );

/**
 * Сколько глав проекта ждут своего слота.
 */
function xni_queued_count( $novel_id ) {
	$ids = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => array( 'draft', 'pending', 'future' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_xin_novel', 'value' => (string) absint( $novel_id ) ),
			array( 'key' => XNI_SLOT, 'compare' => 'EXISTS' ),
		),
	) );

	return count( $ids );
}

/**
 * Сохраняет расписание, отправленное формой проекта.
 *
 * Права и nonce проверены темой до вызова хука.
 *
 * @param int $novel_id Проект.
 */
function xni_novel_form_save( $novel_id ) {
	if ( ! $novel_id ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- проверено в xin_studio_guard().
	if ( ! isset( $_POST['xni_sched_times'] ) && ! isset( $_POST['xni_sched_on'] ) ) {
		return; // форма пришла не из кабинета — не трогаем расписание
	}

	$times = isset( $_POST['xni_sched_times'] )
		? explode( ',', sanitize_text_field( wp_unslash( $_POST['xni_sched_times'] ) ) )
		: array();

	xni_save_schedule( $novel_id, array(
		'enabled' => ! empty( $_POST['xni_sched_on'] ),
		'free'    => ! empty( $_POST['xni_sched_free'] ),
		'days'    => isset( $_POST['xni_sched_days'] ) ? (array) wp_unslash( $_POST['xni_sched_days'] ) : array(),
		'times'   => array_map( 'trim', $times ),
	) );
	// phpcs:enable
}
add_action( 'xin_novel_form_save', 'xni_novel_form_save' );
