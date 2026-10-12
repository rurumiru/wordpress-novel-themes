<?php
/**
 * Общий список записей — блог и всё, для чего нет своего шаблона.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="xv-section">
	<header class="xv-page-head">
		<h1 class="xv-page-title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
			} elseif ( is_archive() ) {
				echo esc_html( get_the_archive_title() );
			} else {
				esc_html_e( 'Записи', 'xin-v2' );
			}
			?>
		</h1>
		<?php if ( is_archive() && get_the_archive_description() ) : ?>
			<p class="xv-page-lede"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
		<?php endif; ?>
	</header>

	<div class="<?php echo is_active_sidebar( 'xv-blog' ) ? 'xv-with-sidebar' : ''; ?>">
	<div>
	<?php if ( have_posts() ) : ?>
		<div class="xv-post-list">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article class="xv-post-item">
					<div>
						<h2 class="xv-post-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="xv-post-item-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<p class="xv-post-item-meta">
							<?php echo esc_html( xv_ago( get_post_timestamp() ) ); ?>
							<?php if ( 'post' === get_post_type() ) : ?>
								· <?php the_author(); ?>
							<?php endif; ?>
						</p>
					</div>
					<?php if ( has_post_thumbnail() ) : ?>
						<span class="xv-post-item-thumb"><?php the_post_thumbnail( 'medium' ); ?></span>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
		</div>

		<?php xv_pagination(); ?>
	<?php else : ?>
		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Здесь пока пусто', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Записей нет — но каталог тайтлов уже ждёт.', 'xin-v2' ); ?></p>
			<a class="xv-btn primary" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'В каталог', 'xin-v2' ); ?></a>
		</div>
	<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>
