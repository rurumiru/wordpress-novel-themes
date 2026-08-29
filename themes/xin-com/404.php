<?php

get_header();
?>

<div class="xin-wrap xin-404">
	<div class="xin-404__code">404</div>
	<h1><?php esc_html_e( 'Такой страницы нет', 'xin-com' ); ?></h1>
	<p><?php esc_html_e( 'Возможно, тайтл переехал или ссылка устарела. Попробуйте найти его в каталоге.', 'xin-com' ); ?></p>

	<div class="xin-flex xin-flex-wrap xin-mt-3" style="justify-content:center">
		<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php xin_the_icon( 'home' ); ?><?php esc_html_e( 'На главную', 'xin-com' ); ?></a>
		<a class="btn btn-outline" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php xin_the_icon( 'compass' ); ?><?php esc_html_e( 'В каталог', 'xin-com' ); ?></a>
	</div>

	<div class="xin-mt-3" style="max-width:480px;margin-inline:auto"><?php get_search_form(); ?></div>

	<?php $xin_popular = xin_get_novels( 'popular', 6 ); ?>
	<?php if ( $xin_popular ) : ?>
		<section class="xin-section" style="text-align:left">
			<?php xin_section_head( array( 'title' => __( 'Пока вы здесь — популярное', 'xin-com' ), 'icon' => 'flame' ) ); ?>
			<div class="xin-grid xin-grid--6">
				<?php foreach ( $xin_popular as $xin_id ) : ?>
					<?php xin_novel_card( $xin_id ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
