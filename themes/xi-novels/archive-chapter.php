<?php

get_header();

$xin_today     = current_time( 'Y-m-d' );
$xin_yesterday = gmdate( 'Y-m-d', strtotime( $xin_today . ' -1 day' ) );
$xin_current   = '';
$xin_open      = false;
?>

<div class="xin-aurora">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<span class="xin-eyebrow"><?php esc_html_e( 'лента', 'xi-novels' ); ?></span>
			<h1><?php esc_html_e( 'Обновления', 'xi-novels' ); ?></h1>
			<p class="xin-pagehead__sub"><?php esc_html_e( 'Свежие главы со всей площадки — в порядке публикации.', 'xi-novels' ); ?></p>
		</header>
	</div>
</div>

<div class="xin-wrap xin-mt-3">
	<?php if ( have_posts() ) : ?>

		<div class="xin-timeline">
			<?php
			while ( have_posts() ) :
				the_post();

				$xin_id       = get_the_ID();
				$xin_day      = get_the_date( 'Y-m-d' );
				$xin_novel_id = xin_chapter_novel_id( $xin_id );
				$xin_cover    = $xin_novel_id ? xin_cover_url( $xin_novel_id, 'xin-cover-sm' ) : '';
				$xin_label    = xin_chapter_label( $xin_id );

				if ( $xin_day !== $xin_current ) {
					if ( $xin_open ) {
						echo '</div>';
					}
					$xin_current = $xin_day;
					$xin_open    = true;

					if ( $xin_today === $xin_day ) {
						$xin_title = __( 'Сегодня', 'xi-novels' );
					} elseif ( $xin_yesterday === $xin_day ) {
						$xin_title = __( 'Вчера', 'xi-novels' );
					} else {
						$xin_title = get_the_date( 'j F Y' );
					}
					?>
					<div class="xin-timeline__day">
						<div class="xin-timeline__date"><b><?php echo esc_html( $xin_title ); ?></b></div>
					<?php
				}
				?>

				<a class="xin-update-row" href="<?php echo esc_url( get_permalink() ); ?>">
					<span class="xin-update-row__cover">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</span>
					<span style="min-width:0">
						<span class="xin-update-row__novel"><?php echo esc_html( $xin_novel_id ? get_the_title( $xin_novel_id ) : '' ); ?></span>
						<span class="xin-update-row__title">
							<?php if ( $xin_label ) : ?>
								<span class="xin-muted" style="font-family:var(--font-mono);font-size:12.5px">#<?php echo esc_html( $xin_label ); ?></span>
							<?php endif; ?>
							<?php the_title(); ?>
							<?php if ( get_post_meta( $xin_id, '_xin_locked', true ) ) : ?>
								<span class="xin-badge xin-badge--gold"><?php xin_the_icon( 'lock' ); ?>PLUS</span>
							<?php endif; ?>
						</span>
					</span>
					<span class="xin-update-row__time"><?php echo esc_html( get_the_time( 'H:i' ) ); ?></span>
				</a>

				<?php
			endwhile;

			if ( $xin_open ) {
				echo '</div>';
			}
			?>
		</div>

		<?php xin_pagination(); ?>

	<?php else : ?>
		<div class="xin-empty">
			<?php xin_the_icon( 'clock' ); ?>
			<h2><?php esc_html_e( 'Обновлений пока нет', 'xi-novels' ); ?></h2>
			<p><?php esc_html_e( 'Как только выйдет первая глава, она появится здесь.', 'xi-novels' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
