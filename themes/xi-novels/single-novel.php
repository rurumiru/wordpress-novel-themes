<?php

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
	$xin_last     = $xin_chapters ? end( $xin_chapters ) : null;
	$xin_genres   = get_the_terms( $xin_id, 'genre' );
	$xin_tags     = get_the_terms( $xin_id, 'novel_tag' );
	$xin_adult    = (bool) get_post_meta( $xin_id, '_xin_adult', true );
	?>

	<article <?php post_class(); ?>>

		<header class="xin-novel-hero">
			<?php if ( $xin_bg || $xin_cover ) : ?>
				<div class="xin-novel-hero__bg" aria-hidden="true">
					<img src="<?php echo esc_url( $xin_bg ? $xin_bg : $xin_cover ); ?>" alt="">
				</div>
			<?php endif; ?>

			<div class="xin-novel-hero__inner">
				<div>
					<div class="xin-novel-hero__cover">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>">
						<?php endif; ?>
					</div>
				</div>

				<div>
					<?php xin_breadcrumbs(); ?>

					<div class="xin-novel-hero__tags">
						<?php if ( $xin_status ) : ?>
							<span class="xin-badge xin-badge--primary"><?php echo esc_html( $xin_status->name ); ?></span>
						<?php endif; ?>
						<?php if ( $xin_adult ) : ?>
							<span class="xin-badge xin-badge--adult">18+</span>
						<?php endif; ?>
						<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
							<?php foreach ( $xin_genres as $xin_genre ) : ?>
								<a class="xin-badge" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<h1 class="xin-novel-hero__title"><?php the_title(); ?></h1>

					<?php
					$xin_original = get_post_meta( $xin_id, '_xin_original_title', true );
					if ( $xin_original ) :
						?>
						<p class="xin-novel-hero__orig"><?php echo esc_html( $xin_original ); ?></p>
					<?php endif; ?>

					<p class="xin-muted">
						<?php printf( esc_html__( 'Автор: %s', 'xi-novels' ), '<b>' . esc_html( xin_novel_author( $xin_id ) ) . '</b>' ); ?>
					</p>

					<dl class="xin-statbar">
						<div>
							<b class="is-gold"><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b>
							<span><?php esc_html_e( 'оценка', 'xi-novels' ); ?></span>
						</div>
						<div>
							<b><?php echo (int) count( $xin_chapters ); ?></b>
							<span><?php esc_html_e( 'глав', 'xi-novels' ); ?></span>
						</div>
						<div>
							<b><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></b>
							<span><?php esc_html_e( 'просмотров', 'xi-novels' ); ?></span>
						</div>
					</dl>

					<div class="xin-novel-hero__actions">
						<?php if ( $xin_first ) : ?>
							<a class="btn btn-primary btn-lg" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать с начала', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $xin_last && $xin_last !== $xin_first ) : ?>
							<a class="btn btn-outline btn-lg" href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>">
								<?php xin_the_icon( 'clock' ); ?><?php esc_html_e( 'Последняя глава', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php xin_fav_button( $xin_id, true ); ?>
					</div>
				</div>
			</div>
		</header>

		<div class="xin-wrap">
			<div class="xin-novel-body">

				<div>
					<div class="xin-panel">
						<div class="xin-panel__head">
							<h2><?php xin_the_icon( 'book' ); ?><?php esc_html_e( 'Описание', 'xi-novels' ); ?></h2>
						</div>
						<div class="xin-synopsis xin-content is-collapsed" data-xin-synopsis>
							<?php the_content(); ?>
						</div>
						<button
							type="button"
							class="xin-btn xin-btn--ghost xin-btn--sm xin-synopsis-toggle"
							data-xin-synopsis-toggle
							data-more="<?php esc_attr_e( 'Читать полностью', 'xi-novels' ); ?>"
							data-less="<?php esc_attr_e( 'Свернуть', 'xi-novels' ); ?>"
						><?php esc_html_e( 'Читать полностью', 'xi-novels' ); ?></button>

						<?php if ( ! is_wp_error( $xin_tags ) && $xin_tags ) : ?>
							<div class="xin-tags" style="margin-bottom:0">
								<?php foreach ( $xin_tags as $xin_tag ) : ?>
									<a class="xin-badge" href="<?php echo esc_url( get_term_link( $xin_tag ) ); ?>"><?php xin_the_icon( 'tag' ); ?><?php echo esc_html( $xin_tag->name ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="xin-panel" id="chapters">
						<div class="xin-panel__head">
							<h2><?php xin_the_icon( 'list' ); ?><?php esc_html_e( 'Оглавление', 'xi-novels' ); ?></h2>
							<span class="xin-muted" style="font-size:13px">
								<?php
								printf(
									esc_html( xin_plural( count( $xin_chapters ), __( '%d глава', 'xi-novels' ), __( '%d главы', 'xi-novels' ), __( '%d глав', 'xi-novels' ) ) ),
									(int) count( $xin_chapters )
								);
								?>
							</span>
						</div>

						<?php if ( $xin_chapters ) : ?>
							<div class="xin-chaptertools">
								<input class="form-control form-control-pill" type="search" placeholder="<?php esc_attr_e( 'Поиск по главам…', 'xi-novels' ); ?>" data-xin-chapter-search aria-label="<?php esc_attr_e( 'Поиск по главам', 'xi-novels' ); ?>">
								<button type="button" class="btn btn-outline btn-sm" data-xin-chapter-sort>
									<?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Порядок', 'xi-novels' ); ?>
								</button>
							</div>

							<ul class="xin-chapters" data-xin-chapter-list>
								<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
									<?php
									$xin_locked = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
									$xin_label  = xin_chapter_label( $xin_chapter->ID );
									?>
									<li data-xin-chapter-item>
										<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
											<span class="xin-chapters__num"><?php echo $xin_label ? esc_html( '#' . $xin_label ) : '—'; ?></span>
											<span class="xin-chapters__title"><?php echo esc_html( $xin_chapter->post_title ); ?></span>
											<?php if ( $xin_locked ) : ?>
												<span class="xin-chapters__lock" title="<?php esc_attr_e( 'Платная глава', 'xi-novels' ); ?>"><?php xin_the_icon( 'lock' ); ?></span>
											<?php endif; ?>
											<span class="xin-chapters__date"><?php echo esc_html( get_the_date( 'j M Y', $xin_chapter->ID ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
							<p class="xin-chapters__empty" data-xin-chapter-empty hidden><?php esc_html_e( 'Ничего не найдено', 'xi-novels' ); ?></p>
						<?php else : ?>
							<p class="xin-chapters__empty"><?php esc_html_e( 'Главы ещё не опубликованы.', 'xi-novels' ); ?></p>
						<?php endif; ?>
					</div>

				</div>

				<aside class="xin-sidebar">
					<div class="widget">
						<h2 class="widget-title"><?php esc_html_e( 'О тайтле', 'xi-novels' ); ?></h2>
						<ul class="xin-infolist">
							<?php if ( $xin_status ) : ?>
								<li><span><?php esc_html_e( 'Статус', 'xi-novels' ); ?></span><b><?php echo esc_html( $xin_status->name ); ?></b></li>
							<?php endif; ?>
							<?php
							$xin_year   = get_post_meta( $xin_id, '_xin_year', true );
							$xin_transl = get_post_meta( $xin_id, '_xin_translator', true );
							$xin_source = get_post_meta( $xin_id, '_xin_source', true );
							?>
							<?php if ( $xin_year ) : ?>
								<li><span><?php esc_html_e( 'Год', 'xi-novels' ); ?></span><b><?php echo esc_html( $xin_year ); ?></b></li>
							<?php endif; ?>
							<?php if ( $xin_transl ) : ?>
								<li><span><?php esc_html_e( 'Перевод', 'xi-novels' ); ?></span><b><?php echo esc_html( $xin_transl ); ?></b></li>
							<?php endif; ?>
							<li><span><?php esc_html_e( 'Добавлен', 'xi-novels' ); ?></span><b><?php echo esc_html( get_the_date() ); ?></b></li>
							<li><span><?php esc_html_e( 'Обновлён', 'xi-novels' ); ?></span><b><?php echo esc_html( get_the_modified_date() ); ?></b></li>
							<?php if ( $xin_source ) : ?>
								<li><span><?php esc_html_e( 'Источник', 'xi-novels' ); ?></span><b><a href="<?php echo esc_url( $xin_source ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'открыть', 'xi-novels' ); ?></a></b></li>
							<?php endif; ?>
						</ul>
					</div>

					<div class="widget">
						<h2 class="widget-title"><?php esc_html_e( 'Ваша оценка', 'xi-novels' ); ?></h2>
						<div class="xin-rating" data-xin-rate="<?php echo (int) $xin_id; ?>">
							<span class="xin-stars">
								<?php for ( $xin_s = 1; $xin_s <= 5; $xin_s++ ) : ?>
									<button type="button" class="btn btn-icon" data-value="<?php echo (int) $xin_s; ?>" style="width:26px;height:26px" aria-label="<?php echo esc_attr( sprintf( __( 'Оценить на %d', 'xi-novels' ), $xin_s ) ); ?>">
										<?php xin_the_icon( 'star', $xin_s <= round( $xin_rating['value'] ) ? '' : 'is-off', true ); ?>
									</button>
								<?php endfor; ?>
							</span>
							<span>
								<b data-xin-rate-value><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b>
								<small class="xin-muted">(<span data-xin-rate-count><?php echo (int) $xin_rating['count']; ?></span>)</small>
							</span>
						</div>
					</div>

					<?php

if ( ! is_wp_error( $xin_genres ) && $xin_genres ) :
						$xin_related = get_posts( array(
							'post_type'      => 'novel',
							'posts_per_page' => 4,
							'post__not_in'   => array( $xin_id ),
							'tax_query'      => array(
								array(
									'taxonomy' => 'genre',
									'field'    => 'term_id',
									'terms'    => wp_list_pluck( $xin_genres, 'term_id' ),
								),
							),
						) );
						if ( $xin_related ) :
							?>
							<div class="widget">
								<h2 class="widget-title"><?php esc_html_e( 'Похожее', 'xi-novels' ); ?></h2>
								<div class="xin-widget-novels">
									<?php foreach ( $xin_related as $xin_rel ) : ?>
										<?php $xin_rel_cover = xin_cover_url( $xin_rel->ID, 'xin-cover-sm' ); ?>
										<a class="xin-widget-novel" href="<?php echo esc_url( get_permalink( $xin_rel->ID ) ); ?>">
											<span class="xin-widget-novel__cover">
												<?php if ( $xin_rel_cover ) : ?>
													<img src="<?php echo esc_url( $xin_rel_cover ); ?>" alt="" loading="lazy">
												<?php endif; ?>
											</span>
											<span>
												<h4><?php echo esc_html( $xin_rel->post_title ); ?></h4>
												<small><?php echo esc_html( xin_num( xin_get_views( $xin_rel->ID ) ) ); ?> <?php esc_html_e( 'просм.', 'xi-novels' ); ?></small>
											</span>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
							<?php
						endif;
					endif;
					?>

					<?php if ( is_active_sidebar( 'sidebar-novel' ) ) : ?>
						<?php dynamic_sidebar( 'sidebar-novel' ); ?>
					<?php endif; ?>
				</aside>

			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
