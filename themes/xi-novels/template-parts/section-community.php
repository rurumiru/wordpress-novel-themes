<?php

$xin_authors = get_users( array(
	'number'              => 6,
	'orderby'             => 'post_count',
	'order'               => 'DESC',
	'has_published_posts' => array( 'novel' ),
	'fields'              => array( 'ID', 'display_name' ),
) );

$xin_posts = get_posts( array(
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
) );

if ( ! $xin_authors && ! $xin_posts ) {
	return;
}
?>
<section class="xin-wrap xin-section">
	<div class="xin-grid xin-grid--2 xin-grid--start xin-reveal">

		<?php if ( $xin_authors ) : ?>
			<div class="xin-panel">
				<div class="xin-panel__head">
					<h2><?php xin_the_icon( 'crown' ); ?><?php esc_html_e( 'Топ-авторы', 'xi-novels' ); ?></h2>
					<a class="xin-head__more" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
						<?php esc_html_e( 'Все', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				</div>

				<div class="xin-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:10px">
					<?php foreach ( $xin_authors as $xin_i => $xin_author ) : ?>
						<?php
						$xin_count = count_user_posts( $xin_author->ID, 'novel' );

$xin_medal = $xin_i < 3 ? ' xin-badge--gold' : '';
						?>
						<a class="xin-author-card" href="<?php echo esc_url( get_author_posts_url( $xin_author->ID ) ); ?>">
							<span class="xin-badge<?php echo esc_attr( $xin_medal ); ?> xin-author-card__rank">#<?php echo (int) ( $xin_i + 1 ); ?></span>
							<?php echo xin_avatar( $xin_author->ID, 56, $xin_author->display_name ); ?>
							<span class="xin-author-card__name"><?php echo esc_html( $xin_author->display_name ); ?></span>
							<span class="xin-author-card__count">
								<?php echo (int) $xin_count; ?> <?php echo esc_html( xin_plural( $xin_count, __( 'новелла', 'xi-novels' ), __( 'новеллы', 'xi-novels' ), __( 'новелл', 'xi-novels' ) ) ); ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $xin_posts ) : ?>
			<div class="xin-panel">
				<div class="xin-panel__head">
					<h2><?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Новости и статьи', 'xi-novels' ); ?></h2>
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<a class="xin-head__more" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">
							<?php esc_html_e( 'Все', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="xin-post-list">
					<?php foreach ( $xin_posts as $xin_post ) : ?>
						<?php $xin_thumb = get_the_post_thumbnail_url( $xin_post->ID, 'xin-wide' ); ?>
						<a class="xin-post-row" href="<?php echo esc_url( get_permalink( $xin_post->ID ) ); ?>">
							<span class="xin-post-row__media">
								<?php if ( $xin_thumb ) : ?>
									<img src="<?php echo esc_url( $xin_thumb ); ?>" alt="" loading="lazy">
								<?php endif; ?>
							</span>
							<span>
								<h3><?php echo esc_html( get_the_title( $xin_post->ID ) ); ?></h3>
								<p>
									<?php echo esc_html( get_the_author_meta( 'display_name', $xin_post->post_author ) ); ?>
									· <?php echo esc_html( get_the_date( 'j M', $xin_post->ID ) ); ?>
								</p>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
