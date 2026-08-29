<?php

get_header();

$xin_has_sidebar = is_active_sidebar( 'sidebar-blog' );
?>

<div class="xin-wrap">
	<header class="xin-pagehead">
		<?php xin_breadcrumbs(); ?>
		<h1>
			<?php
			if ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
			} elseif ( is_archive() ) {
				the_archive_title();
			} elseif ( is_search() ) {
				printf( esc_html__( 'Поиск: %s', 'xin-com' ), esc_html( get_search_query() ) );
			} else {
				esc_html_e( 'Блог', 'xin-com' );
			}
			?>
		</h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<div class="xin-pagehead__sub"><?php the_archive_description(); ?></div>
		<?php else : ?>
			<p class="xin-pagehead__sub"><?php esc_html_e( 'Новости площадки, разборы тайтлов и заметки переводчиков.', 'xin-com' ); ?></p>
		<?php endif; ?>
	</header>

	<div class="xin-layout <?php echo $xin_has_sidebar ? 'xin-layout--sidebar' : ''; ?> xin-mt-2">
		<div>
			<?php if ( have_posts() ) : ?>
				<div class="xin-grid <?php echo $xin_has_sidebar ? 'xin-grid--2' : 'xin-grid--3'; ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<div class="xin-reveal"><?php xin_post_card( get_the_ID() ); ?></div>
						<?php
					endwhile;
					?>
				</div>
				<?php xin_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $xin_has_sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
