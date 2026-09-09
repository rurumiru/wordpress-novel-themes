<?php
/**
 * Страница тайтла.
 *
 * Собрана как книжная, а не как панель: одна колонка по центру, всё нужное
 * идёт сверху вниз в том порядке, в каком читатель решает — брать или нет.
 * Прежняя версия делила экран на текст и боковую колонку, и правая половина
 * заканчивалась раньше левой: под фактами оставалось полполосы пустоты, а
 * рядом с оглавлением висели виджеты «Архивы» и «Рубрики».
 *
 * @package XI_Novels
 */

get_header();

while ( have_posts() ) :
	the_post();

	$xin_id     = get_the_ID();
	$xin_cover  = xin_cover_url( $xin_id, 'xin-cover-lg' );
	/*
	 * Арт для первого экрана: отдельная широкая картинка тайтла, если она
	 * задана, иначе сама обложка — размытая и затемнённая. Без картинки экран
	 * остаётся бумажным, и вёрстка не разъезжается.
	 */
	$xin_art    = xin_background_url( $xin_id, 'xin-banner' );
	$xin_artsrc = $xin_art ? $xin_art : $xin_cover;
	$xin_rating = xin_rating( $xin_id );
	$xin_status = xin_novel_status( $xin_id );
	$xin_genres = get_the_terms( $xin_id, 'genre' );
	$xin_tags   = get_the_terms( $xin_id, 'novel_tag' );
	$xin_adult  = (bool) get_post_meta( $xin_id, '_xin_adult', true );

	$xin_toc      = xin_chapter_page( $xin_id, 'ASC', xin_chapter_page_number() );
	$xin_chapters = $xin_toc['posts'];

	/*
	 * Первая и последняя главы — из полного порядка, а не из показанной
	 * страницы оглавления: иначе на длинном тайтле «последняя» вела бы в
	 * конец текущей страницы.
	 */
	$xin_first  = xin_first_chapter( $xin_id );
	$xin_last   = xin_last_chapter( $xin_id );
	$xin_rhythm = xin_novel_rhythm( $xin_id );

	$xin_original = get_post_meta( $xin_id, '_xin_original_title', true );
	$xin_year     = get_post_meta( $xin_id, '_xin_year', true );
	$xin_transl   = get_post_meta( $xin_id, '_xin_translator', true );
	$xin_source   = get_post_meta( $xin_id, '_xin_source', true );
	$xin_team     = xin_novel_team_users( $xin_id );
	?>

	<article <?php post_class( 'xin-bk' ); ?>>

		<header class="xin-bk__head<?php echo $xin_artsrc ? ' has-art' : ''; ?>">
			<?php if ( $xin_artsrc ) : ?>
				<div class="xin-bk__art" aria-hidden="true">
					<img src="<?php echo esc_url( $xin_artsrc ); ?>" alt="" decoding="async" fetchpriority="low">
				</div>
			<?php endif; ?>

			<div class="xin-wrap xin-bk__headin">

				<div class="xin-bk__cover">
					<?php if ( $xin_cover ) : ?>
						<?php
						/*
						 * Обложка почти всегда и есть LCP этой страницы: явные
						 * размеры против скачка вёрстки, высокий приоритет —
						 * против очереди за иконками.
						 */
						?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>" width="520" height="780" decoding="async" fetchpriority="high">
					<?php else : ?>
						<span class="xin-bk__cover-empty"><?php xin_the_icon( 'book' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="xin-bk__intro">
					<?php xin_breadcrumbs(); ?>

					<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
						<p class="xin-bk__kicker">
							<?php foreach ( $xin_genres as $xin_genre ) : ?>
								<a href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>

					<h1 class="xin-bk__title"><?php the_title(); ?></h1>

					<?php if ( $xin_original ) : ?>
						<p class="xin-bk__orig"><?php echo esc_html( $xin_original ); ?></p>
					<?php endif; ?>

					<p class="xin-bk__by">
						<?php printf( esc_html__( 'Автор: %s', 'xin-com' ), '<b>' . esc_html( xin_novel_author( $xin_id ) ) . '</b>' ); ?>
						<?php if ( $xin_transl ) : ?>
							<span class="xin-bk__dot">·</span>
							<?php printf( esc_html__( 'перевод: %s', 'xin-com' ), '<b>' . esc_html( $xin_transl ) . '</b>' ); ?>
						<?php endif; ?>
					</p>

					<?php
					/*
					 * Одна строка вместо рядов значков и отдельных чисел. Ритм
					 * выхода стоит здесь же: это тот вопрос, ради которого
					 * читатели обычно и лезут в комментарии — выходит ли ещё.
					 */
					?>
					<p class="xin-bk__facts">
						<?php if ( $xin_status ) : ?>
							<span class="xin-bk__state"><?php echo esc_html( $xin_status->name ); ?></span>
						<?php endif; ?>
						<span><b><?php echo (int) $xin_toc['total']; ?></b> <?php echo esc_html( xin_plural( $xin_toc['total'], __( 'глава', 'xin-com' ), __( 'главы', 'xin-com' ), __( 'глав', 'xin-com' ) ) ); ?></span>
						<?php if ( $xin_rating['count'] ) : ?>
							<span><b><?php echo esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ); ?></b> <?php esc_html_e( 'оценка', 'xin-com' ); ?></span>
						<?php endif; ?>
						<span><b><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></b> <?php esc_html_e( 'просмотров', 'xin-com' ); ?></span>
						<?php if ( $xin_rhythm ) : ?>
							<span class="xin-bk__rhythm"><?php echo esc_html( $xin_rhythm ); ?></span>
						<?php endif; ?>
						<?php if ( $xin_adult ) : ?>
							<span class="xin-badge xin-badge--adult">18+</span>
						<?php endif; ?>
					</p>

					<div class="xin-bk__act">
						<?php if ( $xin_first ) : ?>
							<?php
							/*
							 * Главная кнопка знает, начинал ли читатель книгу:
							 * theme.js подменяет адрес и подпись на «Продолжить»,
							 * если тайтл есть в истории чтения браузера.
							 */
							?>
							<a class="btn btn-primary btn-lg" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>"
								data-xin-continue-cta="<?php echo (int) $xin_id; ?>"
								data-start="<?php esc_attr_e( 'Читать с начала', 'xin-com' ); ?>">
								<?php xin_the_icon( 'play' ); ?><span data-xin-cta-label><?php esc_html_e( 'Читать с начала', 'xin-com' ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( $xin_last && $xin_last !== $xin_first ) : ?>
							<a class="btn btn-outline btn-lg" href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>">
								<?php xin_the_icon( 'clock' ); ?><?php esc_html_e( 'Последняя глава', 'xin-com' ); ?>
							</a>
						<?php endif; ?>

						<span class="xin-bk__act-min">
							<?php xin_fav_button( $xin_id, true ); ?>
							<?php xin_push_render_bell_button( $xin_id ); ?>

							<?php if ( xin_can_download() ) : ?>
								<span class="xin-bk__down">
									<button type="button" class="btn btn-outline" data-xin-dl aria-expanded="false">
										<?php xin_the_icon( 'download' ); ?><?php esc_html_e( 'Скачать', 'xin-com' ); ?>
									</button>
									<span class="xin-nv__dlmenu" data-xin-dl-menu hidden>
										<a href="<?php echo esc_url( xin_export_url( $xin_id, 'epub' ) ); ?>" rel="nofollow">EPUB<small><?php esc_html_e( 'для читалок и телефона', 'xin-com' ); ?></small></a>
										<a href="<?php echo esc_url( xin_export_url( $xin_id, 'fb2' ) ); ?>" rel="nofollow">FB2<small><?php esc_html_e( 'для классических программ', 'xin-com' ); ?></small></a>
									</span>
								</span>
							<?php endif; ?>
						</span>
					</div>

				</div>

			</div>
		</header>

		<div class="xin-wrap xin-bk__body">
			<div class="xin-bk__grid" data-xin-tabs>

				<div class="xin-bk__main">

					<?php
					/*
					 * Вкладки вместо простыни: описание, оглавление и обсуждение —
					 * три разных дела, и держать их одно под другим значит гнать
					 * читателя колесом мыши мимо того, что ему не нужно. Ссылки
					 * вида /novels/<тайтл>/#chapters по-прежнему работают: скрипт
					 * открывает нужную вкладку по адресу.
					 */
					$xin_talk_n = function_exists( 'xin_talk_count' ) ? (int) xin_talk_count( $xin_id ) : 0;
					?>
					<nav class="xin-bk__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Разделы тайтла', 'xin-com' ); ?>">
						<?php
						/*
						 * Главы стоят первыми и открыты сразу: за описанием
						 * приходят один раз, за списком глав — каждый раз.
						 */
						?>
						<button type="button" role="tab" class="active" aria-selected="true" data-xin-tab="chapters" id="chapters">
							<?php esc_html_e( 'Главы', 'xin-com' ); ?><b><?php echo (int) $xin_toc['total']; ?></b>
						</button>
						<button type="button" role="tab" aria-selected="false" data-xin-tab="overview" id="overview">
							<?php esc_html_e( 'Обзор', 'xin-com' ); ?>
						</button>
						<button type="button" role="tab" aria-selected="false" data-xin-tab="talk" id="talk">
							<?php esc_html_e( 'Обсуждение', 'xin-com' ); ?><b><?php echo (int) $xin_talk_n; ?></b>
						</button>
					</nav>

					<section class="xin-bk__panel" data-xin-tabpanel="chapters">
						<?php if ( $xin_chapters ) : ?>

							<?php
							/*
							 * У длинной книги свежие главы лежат на последней
							 * странице оглавления, а нужны чаще всего.
							 */
							$xin_recent = $xin_toc['pages'] > 1 ? xin_get_chapters( $xin_id, 'DESC', 3 ) : array();
							?>
							<?php if ( $xin_recent ) : ?>
								<div class="xin-bk__recent">
									<span class="xin-bk__recent-label"><?php esc_html_e( 'Свежие', 'xin-com' ); ?></span>
									<?php foreach ( $xin_recent as $xin_r ) : ?>
										<a href="<?php echo esc_url( get_permalink( $xin_r->ID ) ); ?>">
											<?php $xin_r_label = xin_chapter_label( $xin_r->ID ); ?>
											<?php if ( $xin_r_label ) : ?><i>#<?php echo esc_html( $xin_r_label ); ?></i><?php endif; ?>
											<?php echo esc_html( $xin_r->post_title ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="xin-bk__tools">
								<label class="xin-bk__find">
									<?php xin_the_icon( 'search' ); ?>
									<input type="search" data-xin-chapter-search
										placeholder="<?php echo $xin_toc['pages'] > 1 ? esc_attr__( 'Поиск по этой странице…', 'xin-com' ) : esc_attr__( 'Номер или название главы', 'xin-com' ); ?>"
										aria-label="<?php esc_attr_e( 'Поиск по главам', 'xin-com' ); ?>">
								</label>

								<?php if ( $xin_toc['pages'] > 1 ) : ?>
									<form class="xin-bk__goto" method="get" action="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
										<label for="xin-goto"><?php esc_html_e( 'К главе №', 'xin-com' ); ?></label>
										<input id="xin-goto" type="text" inputmode="decimal" name="goto" placeholder="<?php echo esc_attr( (int) $xin_toc['total'] ); ?>">
										<button type="submit" class="btn btn-outline btn-sm">
											<?php xin_the_icon( 'chevron-right' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Перейти', 'xin-com' ); ?></span>
										</button>
									</form>
								<?php endif; ?>

								<button type="button" class="btn btn-ghost btn-sm xin-bk__sort" data-xin-chapter-sort>
									<?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Сначала первые', 'xin-com' ); ?>
								</button>
							</div>

							<ol class="xin-bk__chapters" data-xin-chapter-list>
								<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
									<?php xin_chapter_row( $xin_chapter ); ?>
								<?php endforeach; ?>
							</ol>

							<p class="xin-bk__empty" data-xin-chapter-empty hidden><?php esc_html_e( 'Ничего не найдено', 'xin-com' ); ?></p>

							<?php if ( $xin_toc['pages'] > 1 ) : ?>
								<?php
								/*
								 * Кнопка, а не постраничная навигация: читателю нужен
								 * длинный список, а не путешествие по страницам. При
								 * этом в разметке лежит обычная ссылка на следующую
								 * страницу — без JS оглавление всё равно листается.
								 */
								$xin_shown = min( $xin_toc['per_page'] * $xin_toc['page'], $xin_toc['total'] );
								?>
								<div class="xin-bk__more-wrap">
									<a class="btn btn-outline xin-bk__more-btn"
										href="<?php echo esc_url( xin_chapter_page_url( $xin_id, $xin_toc['page'] + 1 ) ); ?>"
										data-xin-more
										data-novel="<?php echo (int) $xin_id; ?>"
										data-page="<?php echo (int) $xin_toc['page']; ?>"
										data-pages="<?php echo (int) $xin_toc['pages']; ?>"
										data-per="<?php echo (int) $xin_toc['per_page']; ?>"
										<?php echo $xin_toc['page'] >= $xin_toc['pages'] ? 'hidden' : ''; ?>>
										<?php xin_the_icon( 'chevron-down' ); ?>
										<span data-xin-more-label>
											<?php
											printf(
												/* translators: %d: how many chapters the button loads. */
												esc_html__( 'Показать ещё %d', 'xin-com' ),
												(int) min( $xin_toc['per_page'], $xin_toc['total'] - $xin_shown )
											);
											?>
										</span>
									</a>
									<p class="xin-bk__more-count">
										<span data-xin-more-shown><?php echo (int) $xin_shown; ?></span>
										<?php
										printf(
											/* translators: %d: total chapters. */
											esc_html__( 'из %d', 'xin-com' ),
											(int) $xin_toc['total']
										);
										?>
									</p>
								</div>
							<?php endif; ?>

						<?php else : ?>
							<p class="xin-bk__empty"><?php esc_html_e( 'Главы ещё не опубликованы.', 'xin-com' ); ?></p>
						<?php endif; ?>
					</section>

					<section class="xin-bk__panel" data-xin-tabpanel="overview" hidden>
						<div class="xin-bk__about xin-content"><?php the_content(); ?></div>

						<?php if ( ! is_wp_error( $xin_tags ) && $xin_tags ) : ?>
							<p class="xin-bk__tags">
								<?php foreach ( $xin_tags as $xin_tag ) : ?>
									<a href="<?php echo esc_url( get_term_link( $xin_tag ) ); ?>"><?php echo esc_html( $xin_tag->name ); ?></a>
								<?php endforeach; ?>
							</p>
						<?php endif; ?>
					</section>

					<section class="xin-bk__panel" data-xin-tabpanel="talk" hidden>
						<?php xin_talk_render( $xin_id ); ?>
					</section>

				</div>

				<aside class="xin-bk__aside">

					<?php
					/**
					 * Боковая колонка тайтла.
					 *
					 * Сюда плагин очереди вешает счётчик до следующей главы.
					 * Буфер нужен, чтобы не рисовать пустую панель, когда
					 * плагина нет.
					 *
					 * @param int $xin_id Тайтл.
					 */
					ob_start();
					do_action( 'xin_novel_after_hero', $xin_id );
					$xin_hooked = trim( (string) ob_get_clean() );
					?>
					<?php if ( $xin_hooked ) : ?>
						<section class="xin-bk__card">
							<h2 class="xin-bk__cardhead"><?php xin_the_icon( 'clock' ); ?><?php esc_html_e( 'Ближайший выпуск', 'xin-com' ); ?></h2>
							<?php echo $xin_hooked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</section>
					<?php endif; ?>

					<section class="xin-bk__card">
						<h2 class="xin-bk__cardhead"><?php esc_html_e( 'Оценка', 'xin-com' ); ?></h2>
						<div class="xin-bk__rate" data-xin-rate="<?php echo (int) $xin_id; ?>">
							<span class="xin-nv__stars">
								<?php for ( $xin_s = 1; $xin_s <= 5; $xin_s++ ) : ?>
									<button type="button" data-value="<?php echo (int) $xin_s; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Оценить на %d', 'xin-com' ), $xin_s ) ); ?>">
										<?php xin_the_icon( 'star', $xin_s <= round( $xin_rating['value'] ) ? '' : 'is-off', true ); ?>
									</button>
								<?php endfor; ?>
							</span>
							<span class="xin-bk__rate-num">
								<b data-xin-rate-value><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b>
								<small>(<span data-xin-rate-count><?php echo (int) $xin_rating['count']; ?></span>)</small>
							</span>
						</div>
					</section>

					<section class="xin-bk__card">
						<h2 class="xin-bk__cardhead"><?php esc_html_e( 'О тайтле', 'xin-com' ); ?></h2>
						<dl class="xin-bk__dl">
							<?php if ( $xin_status ) : ?>
								<div><dt><?php esc_html_e( 'Статус', 'xin-com' ); ?></dt><dd><?php echo esc_html( $xin_status->name ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $xin_year ) : ?>
								<div><dt><?php esc_html_e( 'Год', 'xin-com' ); ?></dt><dd><?php echo esc_html( $xin_year ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $xin_transl ) : ?>
								<div><dt><?php esc_html_e( 'Перевод', 'xin-com' ); ?></dt><dd><?php echo esc_html( $xin_transl ); ?></dd></div>
							<?php endif; ?>
							<?php $xin_when = get_option( 'date_format' ); ?>
							<div><dt><?php esc_html_e( 'Добавлен', 'xin-com' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( $xin_when ) ); ?></time></dd></div>
							<div><dt><?php esc_html_e( 'Обновлён', 'xin-com' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date( $xin_when ) ); ?></time></dd></div>
							<?php if ( $xin_source ) : ?>
								<div><dt><?php esc_html_e( 'Источник', 'xin-com' ); ?></dt><dd><a href="<?php echo esc_url( $xin_source ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'открыть', 'xin-com' ); ?></a></dd></div>
							<?php endif; ?>
						</dl>
					</section>

					<section class="xin-bk__card">
						<h2 class="xin-bk__cardhead"><?php esc_html_e( 'Над проектом', 'xin-com' ); ?></h2>
						<div class="xin-bk__team">
							<a class="xin-bk__member" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 34 ); ?>
								<span><b><?php the_author(); ?></b><small><?php esc_html_e( 'ведёт проект', 'xin-com' ); ?></small></span>
							</a>
							<?php foreach ( (array) $xin_team as $xin_member ) : ?>
								<a class="xin-bk__member" href="<?php echo esc_url( get_author_posts_url( $xin_member->ID ) ); ?>">
									<?php echo get_avatar( $xin_member->ID, 34 ); ?>
									<span><b><?php echo esc_html( $xin_member->display_name ); ?></b><small><?php esc_html_e( 'переводчик', 'xin-com' ); ?></small></span>
								</a>
							<?php endforeach; ?>
						</div>
					</section>

					<?php
					if ( ! is_wp_error( $xin_genres ) && $xin_genres ) :
						$xin_related = get_posts( array(
							'post_type'      => 'novel',
							'posts_per_page' => 5,
							'post__not_in'   => array( $xin_id ),
							'no_found_rows'  => true,
							'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
								array(
									'taxonomy' => 'genre',
									'field'    => 'term_id',
									'terms'    => wp_list_pluck( $xin_genres, 'term_id' ),
								),
							),
						) );

						if ( $xin_related ) :
							?>
							<section class="xin-bk__card">
								<h2 class="xin-bk__cardhead"><?php esc_html_e( 'Похожее', 'xin-com' ); ?></h2>
								<div class="xin-bk__related">
									<?php foreach ( $xin_related as $xin_rel ) : ?>
										<?php $xin_rel_cover = xin_cover_url( $xin_rel->ID, 'xin-cover-sm' ); ?>
										<a href="<?php echo esc_url( get_permalink( $xin_rel->ID ) ); ?>">
											<span class="xin-bk__relcover">
												<?php if ( $xin_rel_cover ) : ?>
													<img src="<?php echo esc_url( $xin_rel_cover ); ?>" alt="" loading="lazy">
												<?php endif; ?>
											</span>
											<span class="xin-bk__reltext">
												<b><?php echo esc_html( $xin_rel->post_title ); ?></b>
												<small><?php echo esc_html( xin_num( xin_get_views( $xin_rel->ID ) ) ); ?> <?php esc_html_e( 'просм.', 'xin-com' ); ?></small>
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

			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
