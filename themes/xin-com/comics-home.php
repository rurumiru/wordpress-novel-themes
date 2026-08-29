<?php
/**
 * Главная раздела комиксов: /comics/.
 *
 * Отдельный шаблон, а не ветка внутри `front-page.php`. У раздела своя логика
 * витрины: обложка комикса — постер, который работает сам по себе, поэтому
 * первый экран здесь строится на картинке, а не на описании, и полки идут
 * плотной сеткой без аннотаций.
 *
 * @package XIN-Com
 */

get_header();

$xin_featured = xin_get_novels( 'featured', 6, 'comic' );
$xin_popular  = xin_get_novels( 'popular', 12, 'comic' );
$xin_latest   = xin_get_novels( 'latest', 12, 'comic' );
$xin_updated  = xin_get_novels( 'updated', 12, 'comic' );
$xin_chapters = xin_get_latest_chapters( 12, 'comic' );
$xin_hero     = array_slice( $xin_featured ? $xin_featured : $xin_popular, 0, 5 );
$xin_catalog  = xin_section_catalog_link( 'comic' );
?>

<?php if ( $xin_hero ) : ?>
	<section class="xin-cm-hero">
		<div class="xin-wrap">
			<div class="xin-cm-hero__grid">
				<?php foreach ( $xin_hero as $xin_i => $xin_id ) : ?>
					<?php
					$xin_cover = xin_cover_url( $xin_id );
					$xin_title = get_the_title( $xin_id );
					$xin_lead  = 0 === $xin_i;
					?>
					<a class="xin-cm-hero__item<?php echo $xin_lead ? ' xin-cm-hero__item--lead' : ''; ?>" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
						<?php if ( $xin_cover ) : ?>
							<img
								src="<?php echo esc_url( $xin_cover ); ?>"
								alt="<?php echo esc_attr( $xin_title ); ?>"
								width="320" height="480"
								<?php echo $xin_lead ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
							>
						<?php endif; ?>
						<span class="xin-cm-hero__shade"></span>
						<span class="xin-cm-hero__body">
							<?php if ( $xin_lead ) : ?>
								<span class="xin-cm-hero__eyebrow"><?php esc_html_e( 'читают сейчас', 'xin-com' ); ?></span>
							<?php endif; ?>
							<span class="xin-cm-hero__title"><?php echo esc_html( $xin_title ); ?></span>
							<?php if ( $xin_lead ) : ?>
								<span class="xin-cm-hero__meta">
									<?php
									$xin_n = (int) xin_chapter_count( $xin_id );
									echo esc_html( sprintf( xin_plural( $xin_n, __( '%d глава', 'xin-com' ), __( '%d главы', 'xin-com' ), __( '%d глав', 'xin-com' ) ), $xin_n ) );
									?>
								</span>
							<?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! $xin_popular && ! $xin_latest ) : ?>
	<div class="xin-wrap xin-section">
		<div class="xin-glass xin-cm-empty">
			<?php xin_the_icon( 'layers' ); ?>
			<h1><?php esc_html_e( 'Комиксы скоро появятся', 'xin-com' ); ?></h1>
			<p class="xin-muted"><?php esc_html_e( 'В разделе пока ни одного тайтла. Отметьте тайтл форматом «Комикс» — и он окажется здесь.', 'xin-com' ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'В раздел новелл', 'xin-com' ); ?></a>
		</div>
	</div>
<?php endif; ?>

<?php if ( $xin_updated ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'eyebrow'    => __( 'свежее', 'xin-com' ),
			'title'      => __( 'Только что обновились', 'xin-com' ),
			'icon'       => 'clock',
			'more_href'  => xin_section_updates_link( 'comic' ),
			'more_label' => __( 'Все обновления', 'xin-com' ),
		) );
		?>
		<div class="xin-cm-grid">
			<?php foreach ( $xin_updated as $xin_id ) : ?>
				<?php xin_comic_card( $xin_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $xin_chapters ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'eyebrow' => __( 'лента', 'xin-com' ),
			'title'   => __( 'Новые главы', 'xin-com' ),
			'icon'    => 'layers',
		) );
		?>
		<div class="xin-cm-feed">
			<?php foreach ( array_slice( $xin_chapters, 0, 8 ) as $xin_chapter_id ) : ?>
				<?php
				$xin_novel_id = xin_chapter_novel_id( $xin_chapter_id );

				if ( ! $xin_novel_id ) {
					continue;
				}

				$xin_cover = xin_cover_url( $xin_novel_id, 'xin-cover-sm' );
				?>
				<a class="xin-cm-feed__row" href="<?php echo esc_url( get_permalink( $xin_chapter_id ) ); ?>">
					<span class="xin-cm-feed__cover">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" width="60" height="90" loading="lazy">
						<?php endif; ?>
					</span>
					<span class="xin-cm-feed__body">
						<span class="xin-cm-feed__novel"><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?></span>
						<span class="xin-cm-feed__chapter">
							<?php $xin_label = xin_chapter_label( $xin_chapter_id ); ?>
							<?php if ( $xin_label ) : ?>
								<b>#<?php echo esc_html( $xin_label ); ?></b>
							<?php endif; ?>
							<?php echo esc_html( get_the_title( $xin_chapter_id ) ); ?>
						</span>
					</span>
					<span class="xin-cm-feed__pages">
						<?php xin_the_icon( 'image' ); ?>
						<?php echo (int) xin_comic_page_count( $xin_chapter_id ); ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $xin_popular ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'eyebrow'    => __( 'выбор читателей', 'xin-com' ),
			'title'      => __( 'Популярные комиксы', 'xin-com' ),
			'icon'       => 'flame',
			'more_href'  => $xin_catalog,
			'more_label' => __( 'Весь каталог', 'xin-com' ),
		) );
		?>
		<div class="xin-cm-grid">
			<?php foreach ( $xin_popular as $xin_rank => $xin_id ) : ?>
				<?php xin_comic_card( $xin_id, array( 'rank' => $xin_rank + 1 ) ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $xin_latest ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'eyebrow'   => __( 'новинки', 'xin-com' ),
			'title'     => __( 'Недавно добавленные', 'xin-com' ),
			'icon'      => 'sparkles',
			'more_href' => $xin_catalog,
		) );
		?>
		<div class="xin-cm-grid">
			<?php foreach ( $xin_latest as $xin_id ) : ?>
				<?php xin_comic_card( $xin_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
