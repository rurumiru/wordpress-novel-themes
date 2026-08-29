<?php
/**
 * Страница тайтла-комикса: /comics/<слаг>/.
 *
 * От страницы новеллы отличается тем, что здесь измеряется в страницах, а не в
 * словах: ни времени чтения, ни выгрузки в EPUB и FB2 — для ленты картинок обе
 * величины бессмысленны. Список глав идёт превьюшкой первой страницы, потому
 * что по номеру главы комикс не узнают, а по кадру узнают.
 *
 * @package XIN-Com
 */

get_header();

while ( have_posts() ) :
	the_post();

	$xin_id       = get_the_ID();
	$xin_cover    = xin_cover_url( $xin_id, 'xin-cover-lg' );
	$xin_bg       = xin_background_url( $xin_id );
	$xin_rating   = xin_rating( $xin_id );
	$xin_status   = xin_novel_status( $xin_id );
	$xin_chapters = xin_get_chapters( $xin_id, 'ASC' );
	$xin_first    = $xin_chapters ? $xin_chapters[0] : null;
	$xin_genres   = get_the_terms( $xin_id, 'genre' );
	$xin_adult    = (bool) get_post_meta( $xin_id, '_xin_adult', true );
	$xin_dir      = xin_comic_direction( $xin_id );
	$xin_dirs     = array(
		'strip' => __( 'лентой вниз', 'xin-com' ),
		'ltr'   => __( 'постранично, слева направо', 'xin-com' ),
		'rtl'   => __( 'постранично, справа налево', 'xin-com' ),
	);

	$xin_pages = 0;
	foreach ( $xin_chapters as $xin_chapter ) {
		$xin_pages += xin_comic_page_count( $xin_chapter->ID );
	}
	?>

	<article <?php post_class( 'xin-cm-title' ); ?>>

		<header class="xin-cm-title__hero">
			<?php if ( $xin_bg || $xin_cover ) : ?>
				<div class="xin-cm-title__backdrop" aria-hidden="true">
					<img src="<?php echo esc_url( $xin_bg ? $xin_bg : $xin_cover ); ?>" alt="" width="1920" height="640" decoding="async" fetchpriority="low">
				</div>
			<?php endif; ?>

			<div class="xin-wrap xin-cm-title__heroin">
				<div class="xin-cm-title__cover">
					<?php if ( $xin_cover ) : ?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>" width="520" height="780" decoding="async" fetchpriority="high">
					<?php endif; ?>
				</div>

				<div class="xin-cm-title__intro">
					<nav class="xin-crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'xin-com' ); ?>">
						<a href="<?php echo esc_url( xin_section_home_link( 'comic' ) ); ?>"><?php esc_html_e( 'Комиксы', 'xin-com' ); ?></a>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( xin_section_catalog_link( 'comic' ) ); ?>"><?php esc_html_e( 'Каталог', 'xin-com' ); ?></a>
					</nav>

					<h1 class="xin-cm-title__name"><?php the_title(); ?></h1>

					<div class="xin-cm-title__author"><?php echo esc_html( xin_novel_author( $xin_id ) ); ?></div>

					<div class="xin-cm-title__facts">
						<?php if ( $xin_rating['count'] ) : ?>
							<span class="is-rating">
								<?php xin_the_icon( 'star', '', true ); ?>
								<?php echo esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ); ?>
							</span>
						<?php endif; ?>
						<span><?php xin_the_icon( 'layers' ); ?><?php echo esc_html( sprintf( xin_plural( count( $xin_chapters ), __( '%d глава', 'xin-com' ), __( '%d главы', 'xin-com' ), __( '%d глав', 'xin-com' ) ), count( $xin_chapters ) ) ); ?></span>
						<span><?php xin_the_icon( 'image' ); ?><?php echo esc_html( sprintf( xin_plural( $xin_pages, __( '%d страница', 'xin-com' ), __( '%d страницы', 'xin-com' ), __( '%d страниц', 'xin-com' ) ), $xin_pages ) ); ?></span>
						<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></span>
						<?php if ( $xin_status ) : ?>
							<span class="xin-badge"><?php echo esc_html( $xin_status->name ); ?></span>
						<?php endif; ?>
						<?php if ( $xin_adult ) : ?>
							<span class="xin-badge xin-badge--adult">18+</span>
						<?php endif; ?>
					</div>

					<?php if ( $xin_genres && ! is_wp_error( $xin_genres ) ) : ?>
						<div class="xin-cm-title__genres">
							<?php foreach ( $xin_genres as $xin_genre ) : ?>
								<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="xin-cm-title__actions">
						<?php if ( $xin_first ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Начать читать', 'xin-com' ); ?>
							</a>
						<?php endif; ?>
						<?php xin_fav_button( $xin_id, true ); ?>
					</div>

					<p class="xin-cm-title__reading">
						<?php xin_the_icon( 'compass' ); ?>
						<?php
						printf(
							/* translators: %s — способ чтения: лентой вниз, постранично и так далее. */
							esc_html__( 'Читается %s', 'xin-com' ),
							esc_html( $xin_dirs[ $xin_dir ] )
						);
						?>
					</p>
				</div>
			</div>
		</header>

		<div class="xin-wrap xin-cm-title__body">

			<?php if ( get_the_content() ) : ?>
				<section class="xin-cm-title__about">
					<?php
					xin_section_head( array(
						'title' => __( 'О тайтле', 'xin-com' ),
						'icon'  => 'book-open',
					) );
					?>
					<div class="xin-prose"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>

			<section class="xin-cm-title__chapters">
				<?php
				xin_section_head( array(
					'title'    => __( 'Главы', 'xin-com' ),
					'icon'     => 'layers',
					'subtitle' => $xin_chapters ? '' : __( 'Глав пока нет', 'xin-com' ),
				) );
				?>

				<?php if ( $xin_chapters ) : ?>
					<ol class="xin-cm-chapters">
						<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
							<?php
							$xin_pages_in = xin_comic_pages( $xin_chapter->ID );
							$xin_thumb    = $xin_pages_in ? wp_get_attachment_image_url( $xin_pages_in[0], 'thumbnail' ) : '';
							$xin_locked   = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
							?>
							<li class="xin-cm-chapters__item<?php echo $xin_locked ? ' is-locked' : ''; ?>">
								<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
									<span class="xin-cm-chapters__thumb">
										<?php if ( $xin_thumb ) : ?>
											<img src="<?php echo esc_url( $xin_thumb ); ?>" alt="" width="72" height="72" loading="lazy">
										<?php else : ?>
											<?php xin_the_icon( 'image' ); ?>
										<?php endif; ?>
									</span>
									<span class="xin-cm-chapters__text">
										<span class="xin-cm-chapters__label">
										<?php $xin_label = xin_chapter_label( $xin_chapter->ID ); ?>
										<?php if ( $xin_label ) : ?>
											<b class="xin-cm-chapters__num">#<?php echo esc_html( $xin_label ); ?></b>
										<?php endif; ?>
										<?php echo esc_html( $xin_chapter->post_title ); ?>
									</span>
										<span class="xin-cm-chapters__meta">
											<?php echo esc_html( sprintf( xin_plural( count( $xin_pages_in ), __( '%d страница', 'xin-com' ), __( '%d страницы', 'xin-com' ), __( '%d страниц', 'xin-com' ) ), count( $xin_pages_in ) ) ); ?>
											<span aria-hidden="true">·</span>
											<?php echo esc_html( xin_ago( get_the_time( 'U', $xin_chapter->ID ) ) ); ?>
										</span>
									</span>
									<?php if ( $xin_locked ) : ?>
										<span class="xin-cm-chapters__lock"><?php xin_the_icon( 'lock' ); ?></span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</section>

		</div>

	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
