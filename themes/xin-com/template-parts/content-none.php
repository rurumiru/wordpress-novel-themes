<?php

?>
<div class="xin-empty">
	<?php xin_the_icon( 'search' ); ?>
	<h2><?php esc_html_e( 'Ничего не нашлось', 'xin-com' ); ?></h2>
	<p><?php esc_html_e( 'Попробуйте другой запрос или загляните в каталог — там больше тысячи страниц текста.', 'xin-com' ); ?></p>
	<div class="xin-mt-2" style="max-width:440px;margin-inline:auto"><?php get_search_form(); ?></div>
	<a class="btn btn-outline xin-mt-2" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
		<?php xin_the_icon( 'compass' ); ?><?php esc_html_e( 'Открыть каталог', 'xin-com' ); ?>
	</a>
</div>
