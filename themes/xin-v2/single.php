<?php
/**
 * Запись блога.
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
			<?php
			xv_breadcrumbs( array(
				array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ),
				array( 'label' => __( 'Блог', 'xin-v2' ), 'url' => xv_blog_url() ),
				array( 'label' => get_the_title() ),
			) );
			?>

			<h1 class="xv-article-title"><?php the_title(); ?></h1>
			<p class="xv-article-meta">
				<?php echo esc_html( xv_ago( get_post_timestamp() ) ); ?> · <?php the_author(); ?>
			</p>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="xv-article-hero"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>

			<div class="xv-prose"><?php the_content(); ?></div>

			<?php if ( xv_discussion_open( get_the_ID() ) || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</article>
	</div>
	<?php
endwhile;

get_footer();
