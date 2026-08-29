<?php

$xin_ids = isset( $args['ids'] ) ? $args['ids'] : array();
if ( ! $xin_ids ) {
	return;
}
?>
<section class="xin-hero xin-aurora" data-xin-hero>
	<div class="xin-hero__grid">

		<div class="xin-hero__text">
			<?php foreach ( $xin_ids as $xin_i => $xin_id ) : ?>
				<?php
				$xin_rating = xin_rating( $xin_id );
				$xin_status = xin_novel_status( $xin_id );
				$xin_terms  = get_the_terms( $xin_id, 'genre' );
				$xin_first  = xin_first_chapter( $xin_id );
				?>
				<div class="xin-hero__slide" data-xin-hero-slide="<?php echo (int) $xin_i; ?>" <?php echo 0 === $xin_i ? '' : 'hidden'; ?>>
					<div class="xin-hero__eyebrow">
						<span class="xin-hero__pulse"><?php xin_the_icon( 'flame' ); ?></span>
						<?php echo esc_html( get_theme_mod( 'xin_hero_eyebrow', __( 'Сейчас в тренде', 'xin-com' ) ) ); ?>
					</div>

					<h1 class="xin-hero__title"><?php echo esc_html( get_the_title( $xin_id ) ); ?></h1>
					<p class="xin-hero__author"><?php printf( esc_html__( 'от %s', 'xin-com' ), esc_html( xin_novel_author( $xin_id ) ) ); ?></p>

					<?php if ( ! is_wp_error( $xin_terms ) && $xin_terms ) : ?>
						<div class="xin-hero__tags">
							<?php foreach ( array_slice( $xin_terms, 0, 3 ) as $xin_term ) : ?>
								<a class="xin-badge xin-badge--primary" href="<?php echo esc_url( get_term_link( $xin_term ) ); ?>"><?php echo esc_html( $xin_term->name ); ?></a>
							<?php endforeach; ?>
							<?php if ( $xin_status ) : ?>
								<span class="xin-badge"><?php echo esc_html( $xin_status->name ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<p class="xin-hero__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $xin_id ) ), 34 ) ); ?></p>

					<div class="xin-hero__meta">
						<?php if ( $xin_rating['count'] ) : ?>
							<span class="xin-gold"><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ); ?></span>
						<?php endif; ?>
						<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) xin_chapter_count( $xin_id ); ?> <?php echo esc_html( xin_plural( xin_chapter_count( $xin_id ), __( 'глава', 'xin-com' ), __( 'главы', 'xin-com' ), __( 'глав', 'xin-com' ) ) ); ?></span>
						<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></span>
					</div>

					<div class="xin-hero__actions">
						<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $xin_first ? get_permalink( $xin_first->ID ) : get_permalink( $xin_id ) ); ?>">
							<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать', 'xin-com' ); ?>
						</a>
						<a class="btn btn-outline btn-lg" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
							<?php esc_html_e( 'Подробнее', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="xin-hero__deck" data-xin-hero-deck>
			<span class="xin-hero__glow" aria-hidden="true"></span>
			<?php foreach ( $xin_ids as $xin_i => $xin_id ) : ?>
				<?php $xin_cover = xin_cover_url( $xin_id, 'xin-cover-lg' ); ?>
				<button
					type="button"
					class="xin-hero__card"
					data-xin-hero-card="<?php echo (int) $xin_i; ?>"
					data-pos="<?php echo 0 === $xin_i ? '0' : ( 1 === $xin_i ? '1' : 'hidden' ); ?>"
					aria-label="<?php echo esc_attr( get_the_title( $xin_id ) ); ?>"
				>
					<?php if ( $xin_cover ) : ?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" <?php echo $xin_i > 0 ? 'loading="lazy"' : ''; ?>>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</section>
