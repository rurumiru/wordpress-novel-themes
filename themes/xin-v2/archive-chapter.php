<?php
/**
 * Лента обновлений — все свежие главы по всем тайтлам.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="xv-section xv-section-wide xv-updates-page">
	<header class="xv-page-head">
		<h1 class="xv-page-title"><?php esc_html_e( 'Обновления', 'xin-v2' ); ?></h1>
		<p class="xv-page-lede"><?php esc_html_e( 'Свежие главы по всем тайтлам — в порядке выхода.', 'xin-v2' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="xv-upd-list">
			<?php
			while ( have_posts() ) :
				the_post();
				xv_update_row( get_the_ID() );
			endwhile;
			?>
		</div>

		<?php xv_pagination(); ?>
	<?php else : ?>
		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Обновлений пока нет', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Как только выйдет первая глава, она появится здесь.', 'xin-v2' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
