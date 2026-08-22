<?php
/**
 * Таймер до следующей главы на странице тайтла.
 *
 * Разметку отдаёт сервер полностью посчитанной — если скрипты не выполнятся,
 * читатель всё равно увидит дату и время выхода. JavaScript только превращает
 * её в тикающий обратный отсчёт.
 *
 * @package XI_Novel_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Отрисовывает таймер, если у проекта есть что ждать.
 *
 * @param int $novel_id Проект. По умолчанию текущая запись.
 * @return string HTML или пустая строка.
 */
function xni_countdown( $novel_id = 0 ) {
	$novel_id = $novel_id ? $novel_id : get_the_ID();

	if ( ! $novel_id || 'novel' !== get_post_type( $novel_id ) ) {
		return '';
	}

	$next = xni_next_release( $novel_id );

	if ( ! $next ) {
		return '';
	}

	$left = $next['slot'] - time();

	if ( $left <= 0 ) {
		return '';
	}

	ob_start();
	?>
	<div class="xni-count" data-xni-count="<?php echo esc_attr( $next['slot'] ); ?>">
		<span class="xni-count__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
		</span>
		<span class="xni-count__body">
			<span class="xni-count__label"><?php esc_html_e( 'Следующая глава через', 'xi-novel-import' ); ?></span>
			<time class="xni-count__clock" datetime="<?php echo esc_attr( gmdate( 'c', $next['slot'] ) ); ?>">
				<?php echo esc_html( xni_left_text( $left ) ); ?>
			</time>
			<span class="xni-count__when"><?php echo esc_html( wp_date( 'd.m.Y, H:i', $next['slot'] ) ); ?></span>
		</span>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * «1д : 22ч : 06м : 12с» без скриптов.
 */
function xni_left_text( $left ) {
	$left = max( 0, (int) $left );
	$d    = (int) floor( $left / DAY_IN_SECONDS );
	$h    = (int) floor( ( $left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
	$m    = (int) floor( ( $left % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
	$s    = $left % MINUTE_IN_SECONDS;

	return sprintf(
		/* translators: 1: days, 2: hours, 3: minutes, 4: seconds. */
		__( '%1$dд : %2$02dч : %3$02dм : %4$02dс', 'xi-novel-import' ),
		$d,
		$h,
		$m,
		$s
	);
}

/**
 * Тема зовёт этот хук на странице тайтла.
 */
function xni_countdown_hook( $novel_id ) {
	/*
	 * Разметку собираем здесь же, и всё подставляемое экранировано на месте —
	 * а wp_kses_post() вырезал бы из неё svg вместе с иконкой.
	 */
	echo xni_countdown( $novel_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'xin_novel_after_hero', 'xni_countdown_hook' );

/**
 * Тот же таймер шорткодом — на случай, если его хочется поставить руками.
 */
function xni_countdown_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'novel' => 0 ), $atts, 'xi_countdown' );
	return xni_countdown( absint( $atts['novel'] ) );
}
add_shortcode( 'xi_countdown', 'xni_countdown_shortcode' );

function xni_countdown_assets() {
	if ( ! is_singular( 'novel' ) ) {
		return;
	}

	wp_enqueue_style( 'xni-countdown', XNI_URL . 'assets/countdown.css', array(), XNI_VERSION );
	wp_enqueue_script( 'xni-countdown', XNI_URL . 'assets/countdown.js', array(), XNI_VERSION, true );

	/*
	 * Единицы времени тикающий счётчик рисует сам, поэтому формат ему нужно
	 * отдать уже переведённым — иначе подпись меняется с языком сайта, а «д ч м с»
	 * остаются русскими. Строка та же, что и у серверной отрисовки.
	 */
	wp_localize_script( 'xni-countdown', 'XNIC', array(
		'format' => __( '%1$dд : %2$02dч : %3$02dм : %4$02dс', 'xi-novel-import' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'xni_countdown_assets' );
