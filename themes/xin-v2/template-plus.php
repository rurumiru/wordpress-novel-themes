<?php
/**
 * Template Name: Поддержка и ранний доступ
 *
 * Страница объясняет, что даёт поддержка, и показывает, что прямо сейчас
 * лежит в раннем доступе. Приёма денег в теме нет и не будет: ссылку на
 * свой способ оплаты владелец площадки задаёт в настройках, а кому она
 * ведёт — его дело.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_url  = xv_opt( 'plus_url', '' );
$xv_note = xv_opt( 'plus_note', '' );
$xv_soon = xv_locked_chapters( 8 );
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
		<?php if ( $xv_note ) : ?>
			<p class="xv-page-lede"><?php echo esc_html( $xv_note ); ?></p>
		<?php else : ?>
			<p class="xv-page-lede"><?php esc_html_e( 'Поддержка держит перевод: чем она ровнее, тем чаще выходят главы.', 'xin-v2' ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="xv-prose xv-plus-text"><?php the_content(); ?></div>
	<?php else : ?>
		<div class="xv-prose xv-plus-text">
			<h2><?php esc_html_e( 'Что даёт поддержка', 'xin-v2' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Ранний доступ: свежие главы открываются раньше срока.', 'xin-v2' ); ?></li>
				<li><?php esc_html_e( 'Ровный график: команда переводит, а не ищет, на что жить.', 'xin-v2' ); ?></li>
				<li><?php esc_html_e( 'Ничего не отбирается: всё, что было бесплатным, таким и остаётся.', 'xin-v2' ); ?></li>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $xv_url ) : ?>
		<p class="xv-empty-actions xv-plus-cta">
			<a class="xv-btn primary lg" href="<?php echo esc_url( $xv_url ); ?>" rel="noopener nofollow" target="_blank"><?php esc_html_e( 'Поддержать', 'xin-v2' ); ?></a>
		</p>
	<?php endif; ?>

	<?php if ( $xv_soon ) : ?>
		<section class="xv-plus-soon">
			<?php
			xv_section_head( array(
				'title' => __( 'Сейчас в раннем доступе', 'xin-v2' ),
				'sub'   => __( 'откроется всем по расписанию', 'xin-v2' ),
			) );
			?>

			<div class="xv-updates">
				<?php foreach ( $xv_soon as $xv_chapter ) : ?>
					<?php xv_update_row( $xv_chapter ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
