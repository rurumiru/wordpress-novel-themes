<?php

$xin_tabs = array(
	'popular' => array( 'label' => __( 'По просмотрам', 'xi-novels' ), 'ids' => isset( $args['popular'] ) ? $args['popular'] : array() ),
	'rated'   => array( 'label' => __( 'По оценке', 'xi-novels' ), 'ids' => isset( $args['rated'] ) ? $args['rated'] : array() ),
	'updated' => array( 'label' => __( 'Свежие', 'xi-novels' ), 'ids' => isset( $args['updated'] ) ? $args['updated'] : array() ),
);
$xin_tabs = array_filter( $xin_tabs, static function ( $tab ) {
	return ! empty( $tab['ids'] );
} );
if ( ! $xin_tabs ) {
	return;
}

$xin_buttons = '<div class="nav nav-pills" role="tablist">';
$xin_first   = true;
foreach ( $xin_tabs as $xin_key => $xin_tab ) {
	$xin_buttons .= sprintf(
		'<button type="button" role="tab" class="nav-link %s" data-xin-tab="%s" aria-selected="%s">%s</button>',
		$xin_first ? 'active' : '',
		esc_attr( $xin_key ),
		$xin_first ? 'true' : 'false',
		esc_html( $xin_tab['label'] )
	);
	$xin_first = false;
}
$xin_buttons .= '</div>';
?>
<section class="xin-wrap xin-section xin-reveal" data-xin-tabs>
	<?php
	xin_section_head( array(
		'eyebrow'  => __( 'рейтинг', 'xi-novels' ),
		'title'    => __( 'Что читают сейчас', 'xi-novels' ),
		'subtitle' => __( 'Десятка лидеров площадки', 'xi-novels' ),
		'icon'     => 'trophy',
		'after'    => $xin_buttons,
	) );
	?>

	<?php $xin_first = true; ?>
	<?php foreach ( $xin_tabs as $xin_key => $xin_tab ) : ?>
		<?php
		$xin_ids  = array_slice( $xin_tab['ids'], 0, 10 );
		$xin_top  = array_slice( $xin_ids, 0, 3 );
		$xin_rest = array_slice( $xin_ids, 3 );

$xin_max = 0;
		foreach ( $xin_top as $xin_id ) {
			$xin_max = max( $xin_max, xin_get_views( $xin_id ) );
		}
		?>
		<div class="xin-tabpanel" data-xin-tabpanel="<?php echo esc_attr( $xin_key ); ?>" role="tabpanel" <?php echo $xin_first ? '' : 'hidden'; ?>>

			<div class="xin-podium">
				<?php foreach ( $xin_top as $xin_i => $xin_id ) : ?>
					<?php
					$xin_cover  = xin_cover_url( $xin_id, 'xin-cover' );
					$xin_rating = xin_rating( $xin_id );
					$xin_views  = xin_get_views( $xin_id );
					$xin_pct    = $xin_max > 0 ? max( 8, (int) round( $xin_views / $xin_max * 100 ) ) : 8;
					?>
					<a class="xin-podium__item" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
						<span class="xin-podium__place"><?php echo (int) ( $xin_i + 1 ); ?></span>
						<span class="xin-podium__cover">
							<?php if ( $xin_cover ) : ?>
								<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" loading="lazy">
							<?php endif; ?>
						</span>
						<span style="min-width:0">
							<span class="xin-podium__title"><?php echo esc_html( get_the_title( $xin_id ) ); ?></span>
							<span class="xin-podium__author"><?php echo esc_html( xin_novel_author( $xin_id ) ); ?></span>
							<span class="xin-novel__meta">
								<?php if ( $xin_rating['count'] ) : ?>
									<span class="is-rating"><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ); ?></span>
								<?php endif; ?>
								<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( $xin_views ) ); ?></span>
								<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) xin_chapter_count( $xin_id ); ?></span>
							</span>
							<span class="xin-podium__bar"><i style="width:<?php echo (int) $xin_pct; ?>%"></i></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>

			<?php if ( $xin_rest ) : ?>
				<div class="xin-ranklist">
					<?php foreach ( $xin_rest as $xin_i => $xin_id ) : ?>
						<?php xin_rank_row( $xin_id, $xin_i + 4 ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php $xin_first = false; ?>
	<?php endforeach; ?>
</section>
