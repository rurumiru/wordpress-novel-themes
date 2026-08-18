<?php

$xin_banners = isset( $args['banners'] ) ? $args['banners'] : array();
if ( ! $xin_banners ) {
	return;
}

$xin_height = (int) get_theme_mod( 'xin_banner_height', 360 );
$xin_height = max( 200, min( 720, $xin_height ) );
?>
<section class="xin-banner" data-xin-banner style="--banner-h:<?php echo (int) $xin_height; ?>px">
	<div class="xin-banner__track" data-xin-banner-track>
		<?php foreach ( $xin_banners as $xin_b ) : ?>
			<article class="xin-banner__slide is-<?php echo esc_attr( $xin_b['align'] ? $xin_b['align'] : 'left' ); ?>">
				<?php if ( $xin_b['mobile'] ) : ?>
					<picture>
						<source media="(max-width: 640px)" srcset="<?php echo esc_url( $xin_b['mobile'] ); ?>">
						<img src="<?php echo esc_url( $xin_b['image'] ); ?>" alt="" loading="lazy">
					</picture>
				<?php else : ?>
					<img src="<?php echo esc_url( $xin_b['image'] ); ?>" alt="" loading="lazy">
				<?php endif; ?>

				<div class="xin-banner__body">
					<?php if ( $xin_b['badge'] ) : ?>
						<div><span class="xin-badge xin-badge--primary"><?php echo esc_html( $xin_b['badge'] ); ?></span></div>
					<?php endif; ?>

					<?php if ( $xin_b['subtitle'] ) : ?>
						<p class="xin-banner__eyebrow"><?php echo esc_html( $xin_b['subtitle'] ); ?></p>
					<?php endif; ?>

					<h2><?php echo esc_html( $xin_b['title'] ); ?></h2>

					<?php if ( $xin_b['text'] ) : ?>
						<p><?php echo esc_html( $xin_b['text'] ); ?></p>
					<?php endif; ?>

					<?php if ( $xin_b['link'] ) : ?>
						<div class="xin-banner__actions">
							<a class="btn btn-primary" href="<?php echo esc_url( $xin_b['link'] ); ?>">
								<?php xin_the_icon( 'book-open' ); ?>
								<?php echo esc_html( $xin_b['cta'] ? $xin_b['cta'] : __( 'Открыть', 'xi-novels' ) ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $xin_banners ) > 1 ) : ?>
		<button type="button" class="xin-banner__arrow xin-banner__arrow--prev" data-xin-banner-prev aria-label="<?php esc_attr_e( 'Назад', 'xi-novels' ); ?>"><?php xin_the_icon( 'chevron-left' ); ?></button>
		<button type="button" class="xin-banner__arrow xin-banner__arrow--next" data-xin-banner-next aria-label="<?php esc_attr_e( 'Вперёд', 'xi-novels' ); ?>"><?php xin_the_icon( 'chevron-right' ); ?></button>
		<div class="xin-banner__dots" data-xin-banner-dots>
			<?php foreach ( $xin_banners as $xin_i => $xin_b ) : ?>
				<button type="button" class="<?php echo 0 === $xin_i ? 'is-active' : ''; ?>" data-index="<?php echo (int) $xin_i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Слайд %d', 'xi-novels' ), $xin_i + 1 ) ); ?>"></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
