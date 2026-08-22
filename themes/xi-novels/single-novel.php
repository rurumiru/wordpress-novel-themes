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

	<article <?php post_class( 'xin-nv' ); ?>>

		<header class="xin-nv__hero">
			<?php if ( $xin_bg || $xin_cover ) : ?>
				<div class="xin-nv__backdrop" aria-hidden="true">
					<img src="<?php echo esc_url( $xin_bg ? $xin_bg : $xin_cover ); ?>" alt="">
				</div>
			<?php endif; ?>

			<div class="xin-wrap xin-nv__heroin">
				<div class="xin-nv__cover">
					<?php if ( $xin_cover ) : ?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>">
					<?php endif; ?>
				</div>

				<div class="xin-nv__intro">
					<?php xin_breadcrumbs(); ?>

					<h1 class="xin-nv__title"><?php the_title(); ?></h1>

					<?php
					$xin_original = get_post_meta( $xin_id, '_xin_original_title', true );
					if ( $xin_original ) :
						?>
						<p class="xin-nv__orig"><?php echo esc_html( $xin_original ); ?></p>
					<?php endif; ?>

					<p class="xin-nv__by">
						<?php printf( esc_html__( 'Автор: %s', 'xi-novels' ), '<b>' . esc_html( xin_novel_author( $xin_id ) ) . '</b>' ); ?>
					</p>

					<div class="xin-nv__chips">
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

					<p class="xin-nv__numbers">
						<span><b><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b> <?php esc_html_e( 'оценка', 'xi-novels' ); ?></span>
						<i></i>
						<span><b><?php echo (int) count( $xin_chapters ); ?></b> <?php esc_html_e( 'глав', 'xi-novels' ); ?></span>
						<i></i>
						<span><b><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></b> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?></span>
					</p>

					<div class="xin-nv__cta">
						<?php if ( $xin_first ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать с начала', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $xin_last && $xin_last !== $xin_first ) : ?>
							<a class="btn btn-outline" href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>">
								<?php xin_the_icon( 'clock' ); ?><?php esc_html_e( 'Последняя глава', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php xin_fav_button( $xin_id, true ); ?>

						<div class="xin-nv__dl">
							<button type="button" class="btn btn-outline" data-xin-dl aria-expanded="false">
								<?php xin_the_icon( 'download' ); ?><?php esc_html_e( 'Скачать', 'xi-novels' ); ?>
							</button>
							<div class="xin-nv__dlmenu" data-xin-dl-menu hidden>
								<a href="<?php echo esc_url( xin_export_url( $xin_id, 'epub' ) ); ?>" rel="nofollow">EPUB<small><?php esc_html_e( 'для читалок и телефона', 'xi-novels' ); ?></small></a>
								<a href="<?php echo esc_url( xin_export_url( $xin_id, 'fb2' ) ); ?>" rel="nofollow">FB2<small><?php esc_html_e( 'для классических программ', 'xi-novels' ); ?></small></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>

		<div class="xin-wrap xin-nv__grid">

			<div class="xin-nv__main">

				<?php
				/**
				 * Сразу под шапкой тайтла, до описания.
				 *
				 * Сюда плагин очереди вешает таймер до следующей главы. Без плагина
				 * не выводится ничего, и вёрстка не меняется.
				 *
				 * @param int $xin_id Тайтл.
				 */
				do_action( 'xin_novel_after_hero', $xin_id );
				?>

				<section class="xin-nv__sec">
					<div class="xin-nv__sechead">
						<h2><?php esc_html_e( 'Описание', 'xi-novels' ); ?></h2>
					</div>

					<div class="xin-synopsis xin-content is-collapsed" data-xin-synopsis>
						<?php the_content(); ?>
					</div>
					<button
						type="button"
						class="xin-nv__more"
						data-xin-synopsis-toggle
						data-more="<?php esc_attr_e( 'Читать полностью', 'xi-novels' ); ?>"
						data-less="<?php esc_attr_e( 'Свернуть', 'xi-novels' ); ?>"
					><?php esc_html_e( 'Читать полностью', 'xi-novels' ); ?></button>

					<?php if ( ! is_wp_error( $xin_tags ) && $xin_tags ) : ?>
						<div class="xin-nv__tags">
							<?php foreach ( $xin_tags as $xin_tag ) : ?>
								<a href="<?php echo esc_url( get_term_link( $xin_tag ) ); ?>"><?php echo esc_html( $xin_tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>

				<section class="xin-nv__sec" id="chapters">
					<div class="xin-nv__sechead">
						<h2><?php esc_html_e( 'Оглавление', 'xi-novels' ); ?></h2>
						<span class="xin-nv__count">
							<?php
							printf(
								esc_html( xin_plural( count( $xin_chapters ), __( '%d глава', 'xi-novels' ), __( '%d главы', 'xi-novels' ), __( '%d глав', 'xi-novels' ) ) ),
								(int) count( $xin_chapters )
							);
							?>
						</span>
					</div>

					<?php if ( $xin_chapters ) : ?>
						<div class="xin-nv__tools">
							<input type="search" placeholder="<?php esc_attr_e( 'Поиск по главам…', 'xi-novels' ); ?>" data-xin-chapter-search aria-label="<?php esc_attr_e( 'Поиск по главам', 'xi-novels' ); ?>">
							<button type="button" class="xin-nv__sort" data-xin-chapter-sort>
								<?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Порядок', 'xi-novels' ); ?>
							</button>
						</div>

						<ul class="xin-nv__chapters" data-xin-chapter-list>
							<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
								<?php
								$xin_locked = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
								$xin_label  = xin_chapter_label( $xin_chapter->ID );
								?>
								<li data-xin-chapter-item>
									<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
										<span class="xin-nv__num"><?php echo $xin_label ? esc_html( $xin_label ) : '—'; ?></span>
										<span class="xin-nv__name"><?php echo esc_html( $xin_chapter->post_title ); ?></span>
										<?php if ( $xin_locked ) : ?>
											<span class="xin-nv__lock" title="<?php esc_attr_e( 'Ранний доступ PLUS', 'xi-novels' ); ?>"><?php xin_the_icon( 'lock' ); ?></span>
										<?php endif; ?>
										<span class="xin-nv__date"><?php echo esc_html( get_the_date( 'j M Y', $xin_chapter->ID ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<p class="xin-nv__empty" data-xin-chapter-empty hidden><?php esc_html_e( 'Ничего не найдено', 'xi-novels' ); ?></p>
					<?php else : ?>
						<p class="xin-nv__empty"><?php esc_html_e( 'Главы ещё не опубликованы.', 'xi-novels' ); ?></p>
					<?php endif; ?>
				</section>

			</div>

			<aside class="xin-nv__aside">

				<section class="xin-nv__block">
					<h2><?php esc_html_e( 'О тайтле', 'xi-novels' ); ?></h2>
					<dl class="xin-nv__facts">
						<?php if ( $xin_status ) : ?>
							<div><dt><?php esc_html_e( 'Статус', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_status->name ); ?></dd></div>
						<?php endif; ?>
						<?php
						$xin_year   = get_post_meta( $xin_id, '_xin_year', true );
						$xin_transl = get_post_meta( $xin_id, '_xin_translator', true );
						$xin_source = get_post_meta( $xin_id, '_xin_source', true );
						?>
						<?php if ( $xin_year ) : ?>
							<div><dt><?php esc_html_e( 'Год', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_year ); ?></dd></div>
						<?php endif; ?>
						<?php if ( $xin_transl ) : ?>
							<div><dt><?php esc_html_e( 'Перевод', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_transl ); ?></dd></div>
						<?php endif; ?>
						<?php $xin_when = get_option( 'date_format' ) . ', ' . get_option( 'time_format' ); ?>
						<div><dt><?php esc_html_e( 'Добавлен', 'xi-novels' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( $xin_when ) ); ?></time></dd></div>
						<div><dt><?php esc_html_e( 'Обновлён', 'xi-novels' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date( $xin_when ) ); ?></time></dd></div>
						<?php if ( $xin_source ) : ?>
							<div><dt><?php esc_html_e( 'Источник', 'xi-novels' ); ?></dt><dd><a href="<?php echo esc_url( $xin_source ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'открыть', 'xi-novels' ); ?></a></dd></div>
						<?php endif; ?>
					</dl>
				</section>

				<?php $xin_team = xin_novel_team_users( $xin_id ); ?>
				<?php if ( $xin_team ) : ?>
					<section class="xin-nv__block">
						<h2><?php esc_html_e( 'Над проектом работают', 'xi-novels' ); ?></h2>
						<div class="xin-nv__team">
							<a class="xin-nv__member" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
								<span><b><?php the_author(); ?></b><small><?php esc_html_e( 'ведёт проект', 'xi-novels' ); ?></small></span>
							</a>
							<?php foreach ( $xin_team as $xin_member ) : ?>
								<a class="xin-nv__member" href="<?php echo esc_url( get_author_posts_url( $xin_member->ID ) ); ?>">
									<?php echo get_avatar( $xin_member->ID, 32 ); ?>
									<span><b><?php echo esc_html( $xin_member->display_name ); ?></b><small><?php esc_html_e( 'переводчик', 'xi-novels' ); ?></small></span>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<section class="xin-nv__block">
					<h2><?php esc_html_e( 'Ваша оценка', 'xi-novels' ); ?></h2>
					<div class="xin-nv__rate" data-xin-rate="<?php echo (int) $xin_id; ?>">
						<span class="xin-nv__stars">
							<?php for ( $xin_s = 1; $xin_s <= 5; $xin_s++ ) : ?>
								<button type="button" data-value="<?php echo (int) $xin_s; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Оценить на %d', 'xi-novels' ), $xin_s ) ); ?>">
									<?php xin_the_icon( 'star', $xin_s <= round( $xin_rating['value'] ) ? '' : 'is-off', true ); ?>
								</button>
							<?php endfor; ?>
						</span>
						<span class="xin-nv__ratenum">
							<b data-xin-rate-value><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b>
							<small>(<span data-xin-rate-count><?php echo (int) $xin_rating['count']; ?></span>)</small>
						</span>
					</div>
				</section>

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
						<section class="xin-nv__block">
							<h2><?php esc_html_e( 'Похожее', 'xi-novels' ); ?></h2>
							<div class="xin-nv__related">
								<?php foreach ( $xin_related as $xin_rel ) : ?>
									<?php $xin_rel_cover = xin_cover_url( $xin_rel->ID, 'xin-cover-sm' ); ?>
									<a href="<?php echo esc_url( get_permalink( $xin_rel->ID ) ); ?>">
										<span class="xin-nv__relcover">
											<?php if ( $xin_rel_cover ) : ?>
												<img src="<?php echo esc_url( $xin_rel_cover ); ?>" alt="" loading="lazy">
											<?php endif; ?>
										</span>
										<span class="xin-nv__reltext">
											<b><?php echo esc_html( $xin_rel->post_title ); ?></b>
											<small><?php echo esc_html( xin_num( xin_get_views( $xin_rel->ID ) ) ); ?> <?php esc_html_e( 'просм.', 'xi-novels' ); ?></small>
										</span>
									</a>
								<?php endforeach; ?>
							</div>
						</section>
						<?php
					endif;
				endif;
				?>

				<?php if ( is_active_sidebar( 'sidebar-novel' ) ) : ?>
					<?php dynamic_sidebar( 'sidebar-novel' ); ?>
				<?php endif; ?>
			</aside>

			<div class="xin-nv__talk">
				<?php xin_talk_render( $xin_id ); ?>
			</div>

		</div>
	</article>

	<?php
endwhile;

get_footer();
