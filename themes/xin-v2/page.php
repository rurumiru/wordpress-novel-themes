<?php
/**
 * Обычная страница.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="xv-section">
		<article class="xv-article">
			<h1 class="xv-article-title"><?php the_title(); ?></h1>
			<div class="xv-prose"><?php the_content(); ?></div>

			<?php if ( xv_discussion_open( get_the_ID() ) || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</article>
	</div>
	<?php
endwhile;

get_footer();
