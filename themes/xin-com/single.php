<?php

get_header();

$xin_has_sidebar = is_active_sidebar( 'sidebar-blog' );

while ( have_posts() ) :
	the_post();
	?>

	<div class="xin-wrap">
		<div class="xin-layout <?php echo $xin_has_sidebar ? 'xin-layout--sidebar' : ''; ?>" style="padding-top:24px">
			<article <?php post_class(); ?>>
				<?php xin_breadcrumbs(); ?>

				<?php
				$xin_cats = get_the_category();
				if ( $xin_cats ) :
					?>
					<div class="xin-flex xin-flex-wrap xin-mb-2">
						<?php foreach ( $xin_cats as $xin_cat ) : ?>
							<a class="xin-badge xin-badge--primary" href="<?php echo esc_url( get_category_link( $xin_cat ) ); ?>"><?php echo esc_html( $xin_cat->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<h1><?php the_title(); ?></h1>

				<div class="xin-single__meta">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 28 ); ?>
					<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
					<span><?php xin_the_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
					<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( get_the_ID() ) ) ); ?></span>
				</div>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="xin-single__hero">
						<?php the_post_thumbnail( 'full' ); ?>
					</figure>
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

				<?php
				$xin_tags = get_the_tags();
				if ( $xin_tags ) :
					?>
					<div class="xin-tags">
						<?php foreach ( $xin_tags as $xin_tag ) : ?>
							<a class="xin-badge" href="<?php echo esc_url( get_tag_link( $xin_tag ) ); ?>"><?php xin_the_icon( 'tag' ); ?><?php echo esc_html( $xin_tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="xin-share">
					<span><?php esc_html_e( 'Поделиться', 'xin-com' ); ?></span>
					<a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?php echo rawurlencode( get_permalink() ); ?>&text=<?php echo rawurlencode( get_the_title() ); ?>">
						<?php xin_the_icon( 'telegram' ); ?>Telegram
					</a>
					<a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://vk.com/share.php?url=<?php echo rawurlencode( get_permalink() ); ?>">
						<?php xin_the_icon( 'vk' ); ?>VK
					</a>
				</div>

				<?php if ( get_the_author_meta( 'description' ) ) : ?>
					<div class="xin-authorbox">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
						<div>
							<h3><?php the_author(); ?></h3>
							<p><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<nav class="xin-postnav">
					<?php
					$xin_prev = get_previous_post();
					$xin_next = get_next_post();
					?>
					<?php if ( $xin_prev ) : ?>
						<a href="<?php echo esc_url( get_permalink( $xin_prev ) ); ?>">
							<small><?php esc_html_e( 'Предыдущая', 'xin-com' ); ?></small>
							<b><?php echo esc_html( get_the_title( $xin_prev ) ); ?></b>
						</a>
					<?php else : ?>
						<span></span>
					<?php endif; ?>
					<?php if ( $xin_next ) : ?>
						<a class="is-next" href="<?php echo esc_url( get_permalink( $xin_next ) ); ?>">
							<small><?php esc_html_e( 'Следующая', 'xin-com' ); ?></small>
							<b><?php echo esc_html( get_the_title( $xin_next ) ); ?></b>
						</a>
					<?php endif; ?>
				</nav>

			</article>

			<?php if ( $xin_has_sidebar ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

get_footer();
