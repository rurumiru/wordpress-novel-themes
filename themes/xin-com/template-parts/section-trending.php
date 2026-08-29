<?php

$xin_ids = isset( $args['ids'] ) ? $args['ids'] : array();
if ( ! $xin_ids ) {
	return;
}
?>
<section class="xin-wrap xin-section">
	<?php
	xin_section_head( array(
		'eyebrow'  => __( 'в тренде', 'xin-com' ),
		'title'    => __( 'Читатели не могут оторваться', 'xin-com' ),
		'subtitle' => __( 'Тайтлы, набравшие больше всего внимания', 'xin-com' ),
		'icon'     => 'trending',
	) );
	?>

	<div class="xin-grid xin-grid--3">
		<?php foreach ( $xin_ids as $xin_i => $xin_id ) : ?>
			<?php
			$xin_bg    = xin_background_url( $xin_id );
			$xin_cover = xin_cover_url( $xin_id, 'xin-cover' );
			$xin_terms = get_the_terms( $xin_id, 'genre' );
			$xin_first = xin_first_chapter( $xin_id );
			?>
			<article class="xin-trending xin-reveal" style="transition-delay:<?php echo (int) $xin_i * 70; ?>ms">
				<?php if ( $xin_bg || $xin_cover ) : ?>
					<div class="xin-trending__bg" aria-hidden="true">
						<img src="<?php echo esc_url( $xin_bg ? $xin_bg : $xin_cover ); ?>" alt="" loading="lazy">
					</div>
				<?php endif; ?>

				<div class="xin-trending__body">
					<a class="xin-trending__cover" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php echo esc_attr( get_the_title( $xin_id ) ); ?>" loading="lazy">
						<?php endif; ?>
					</a>

					<div>
						<div class="xin-flex xin-flex-wrap xin-mb-2" style="gap:6px">
							<span class="xin-badge xin-badge--primary">#<?php echo (int) ( $xin_i + 1 ); ?></span>
							<?php if ( ! is_wp_error( $xin_terms ) && $xin_terms ) : ?>
								<span class="xin-badge"><?php echo esc_html( $xin_terms[0]->name ); ?></span>
							<?php endif; ?>
						</div>

						<h3><a href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>"><?php echo esc_html( get_the_title( $xin_id ) ); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $xin_id ) ), 22 ) ); ?></p>

						<div class="xin-novel__meta xin-mt-1">
							<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></span>
							<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) xin_chapter_count( $xin_id ); ?></span>
						</div>

						<div class="xin-mt-2">
							<a class="btn btn-primary btn-sm" href="<?php echo esc_url( $xin_first ? get_permalink( $xin_first->ID ) : get_permalink( $xin_id ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Начать читать', 'xin-com' ); ?>
							</a>
						</div>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
