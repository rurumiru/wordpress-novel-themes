<?php
/**
 * Страница не найдена.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="xv-section">
	<div class="xv-404">
		<p class="xv-404-num">404</p>
		<h1 class="xv-empty-title"><?php esc_html_e( 'Страницы нет', 'xin-v2' ); ?></h1>
		<p class="xv-empty-text"><?php esc_html_e( 'Возможно, тайтл переехал или ссылка устарела.', 'xin-v2' ); ?></p>
		<div class="xv-404-actions">
			<a class="xv-btn primary lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'xin-v2' ); ?></a>
			<a class="xv-btn lg" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'В каталог', 'xin-v2' ); ?></a>
		</div>
	</div>
</div>

<?php get_footer(); ?>
