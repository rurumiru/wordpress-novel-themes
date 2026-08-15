<?php

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="xin-wrap xin-wrap--narrow">
		<article <?php post_class(); ?> style="padding-top:24px">
			<?php xin_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="xin-single__hero"><?php the_post_thumbnail( 'full' ); ?></figure>
			<?php endif; ?>

			<div class="xin-content">
				<?php
				the_content();
				wp_link_pages( array(
					'before' => '<div class="xin-pagination">',
					'after'  => '</div>',
				) );
				?>
			</div>

		</article>
	</div>
	<?php
endwhile;

get_footer();
