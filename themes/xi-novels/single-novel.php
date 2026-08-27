<?php
/**
 * Страница тайтла.
 *
 * Собрана заново по книжной странице-эталону: полоса шапки с крупной обложкой,
 * под ней одна колонка в 1040 пикселей и две вкладки — «О книге» и
 * «Оглавление». Боковой панели нет: всё, что жило в ней, идёт разделами внутри
 * первой вкладки, поэтому ни справка о тайтле, ни виджеты площадки не пропали.
 *
 * Классы с приставкой `nv-` — свои и не пересекаются со старыми `xin-nv__*`:
 * прежние правила из pages.css к этой разметке просто не применяются, и облик
 * не приходится перебивать более сильными селекторами. Там, где за разметку
 * держится скрипт темы (оценка, поиск по главам, меню скачивания), старые
 * имена и data-атрибуты оставлены рядом со своими.
 *
 * @package XI_Novels
 */

get_header();

while ( have_posts() ) :
	the_post();

	$xin_id       = get_the_ID();
	$xin_cover    = xin_cover_url( $xin_id, 'xin-cover-lg' );
	$xin_rating   = xin_rating( $xin_id );
	$xin_status   = xin_novel_status( $xin_id );
	$xin_chapters = xin_get_chapters( $xin_id, 'ASC' );
	$xin_first    = $xin_chapters ? $xin_chapters[0] : null;
	$xin_last     = $xin_chapters ? end( $xin_chapters ) : null;
	$xin_genres   = get_the_terms( $xin_id, 'genre' );
	$xin_tags     = get_the_terms( $xin_id, 'novel_tag' );
	$xin_adult    = (bool) get_post_meta( $xin_id, '_xin_adult', true );
	$xin_year     = get_post_meta( $xin_id, '_xin_year', true );
	$xin_transl   = get_post_meta( $xin_id, '_xin_translator', true );
	$xin_source   = get_post_meta( $xin_id, '_xin_source', true );
	$xin_original = get_post_meta( $xin_id, '_xin_original_title', true );
	$xin_team     = xin_novel_team_users( $xin_id );
	$xin_when     = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
	?>

	<article <?php post_class( 'nv' ); ?>>

		<header class="nv-top">
			<div class="nv-wrap nv-top__in">

				<div class="nv-cover">
					<?php if ( $xin_cover ) : ?>
						<?php /* Обложка — почти всегда LCP этой страницы: явные размеры против скачка вёрстки, высокий приоритет — против очереди за иконками. */ ?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>" width="520" height="780" decoding="async" fetchpriority="high">
					<?php else : ?>
						<span class="nv-cover__empty"><?php xin_the_icon( 'book' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="nv-intro">
					<?php xin_breadcrumbs(); ?>

					<h1 class="nv-title"><?php the_title(); ?></h1>

					<?php if ( $xin_original ) : ?>
						<p class="nv-orig"><?php echo esc_html( $xin_original ); ?></p>
					<?php endif; ?>

					<p class="nv-author">
						<?php esc_html_e( 'Автор', 'xi-novels' ); ?>
						<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( xin_novel_author( $xin_id ) ); ?></a>
					</p>

					<?php if ( $xin_rating['count'] ) : ?>
						<div class="nv-score">
							<?php echo wp_kses_post( xin_stars( $xin_rating['value'] ) ); ?>
							<b><?php echo esc_html( number_format( $xin_rating['value'], 2, ',', '' ) ); ?></b>
							<span>
								<?php
								printf(
									esc_html( xin_plural( $xin_rating['count'], __( '%d оценка', 'xi-novels' ), __( '%d оценки', 'xi-novels' ), __( '%d оценок', 'xi-novels' ) ) ),
									(int) $xin_rating['count']
								);
								?>
							</span>
						</div>
					<?php endif; ?>

					<ul class="nv-facts">
						<?php if ( $xin_status ) : ?>
							<li><?php xin_the_icon( 'check' ); ?><?php echo esc_html( $xin_status->name ); ?></li>
						<?php endif; ?>
						<li><?php xin_the_icon( 'book-open' ); ?><b><?php echo (int) count( $xin_chapters ); ?></b> <?php echo esc_html( xin_plural( count( $xin_chapters ), __( 'глава', 'xi-novels' ), __( 'главы', 'xi-novels' ), __( 'глав', 'xi-novels' ) ) ); ?></li>
						<li><?php xin_the_icon( 'eye' ); ?><b><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></b> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?></li>
						<?php if ( $xin_year ) : ?>
							<li><?php xin_the_icon( 'clock' ); ?><?php echo esc_html( $xin_year ); ?></li>
						<?php endif; ?>
					</ul>

					<?php if ( ( ! is_wp_error( $xin_genres ) && $xin_genres ) || $xin_adult ) : ?>
						<div class="nv-chips">
							<?php if ( $xin_adult ) : ?>
								<span class="nv-chip nv-chip--adult">18+</span>
							<?php endif; ?>
							<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
								<?php foreach ( $xin_genres as $xin_genre ) : ?>
									<a class="nv-chip" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="nv-actions">
						<?php if ( $xin_first ) : ?>
							<a class="btn btn-primary btn-lg" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php xin_fav_button( $xin_id, true ); ?>
						<?php if ( $xin_last && $xin_last !== $xin_first ) : ?>
							<a class="nv-plain" href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>">
								<?php xin_the_icon( 'clock' ); ?><?php esc_html_e( 'Последняя глава', 'xi-novels' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( xin_can_download() ) : ?>
							<div class="nv-dl xin-nv__dl">
								<button type="button" class="nv-plain" data-xin-dl aria-expanded="false">
									<?php xin_the_icon( 'download' ); ?><?php esc_html_e( 'Скачать', 'xi-novels' ); ?>
								</button>
								<div class="xin-nv__dlmenu" data-xin-dl-menu hidden>
									<a href="<?php echo esc_url( xin_export_url( $xin_id, 'epub' ) ); ?>" rel="nofollow">EPUB<small><?php esc_html_e( 'для читалок и телефона', 'xi-novels' ); ?></small></a>
									<a href="<?php echo esc_url( xin_export_url( $xin_id, 'fb2' ) ); ?>" rel="nofollow">FB2<small><?php esc_html_e( 'для классических программ', 'xi-novels' ); ?></small></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</header>

		<div class="nv-wrap nv-body">

			<?php
			/**
			 * Сразу под шапкой тайтла, до вкладок.
			 *
			 * Сюда плагин очереди вешает таймер до следующей главы. Без плагина
			 * не выводится ничего, и вёрстка не меняется.
			 *
			 * @param int $xin_id Тайтл.
			 */
			do_action( 'xin_novel_after_hero', $xin_id );
			?>

			<nav class="nv-tabs" data-xin-tabs>
				<button type="button" class="nv-tab is-active" data-xin-tab="about" aria-selected="true"><?php esc_html_e( 'О книге', 'xi-novels' ); ?></button>
				<button type="button" class="nv-tab" data-xin-tab="toc" aria-selected="false"><?php esc_html_e( 'Оглавление', 'xi-novels' ); ?></button>
			</nav>

			<section class="nv-panel" data-xin-panel="about">

				<h2 class="nv-h"><?php esc_html_e( 'Аннотация', 'xi-novels' ); ?></h2>
				<div class="nv-synopsis xin-content is-collapsed" data-xin-synopsis>
					<?php the_content(); ?>
				</div>
				<button
					type="button"
					class="nv-more"
					data-xin-synopsis-toggle
					data-more="<?php esc_attr_e( 'Читать полностью', 'xi-novels' ); ?>"
					data-less="<?php esc_attr_e( 'Свернуть', 'xi-novels' ); ?>"
				><?php esc_html_e( 'Читать полностью', 'xi-novels' ); ?></button>

				<?php if ( ! is_wp_error( $xin_tags ) && $xin_tags ) : ?>
					<h2 class="nv-h"><?php esc_html_e( 'Теги', 'xi-novels' ); ?></h2>
					<div class="nv-tags">
						<?php foreach ( $xin_tags as $xin_tag ) : ?>
							<a href="<?php echo esc_url( get_term_link( $xin_tag ) ); ?>"># <?php echo esc_html( $xin_tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<h2 class="nv-h"><?php esc_html_e( 'О тайтле', 'xi-novels' ); ?></h2>
				<dl class="nv-info">
					<?php if ( $xin_status ) : ?>
						<div><dt><?php esc_html_e( 'Статус', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_status->name ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $xin_year ) : ?>
						<div><dt><?php esc_html_e( 'Год', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_year ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $xin_transl ) : ?>
						<div><dt><?php esc_html_e( 'Перевод', 'xi-novels' ); ?></dt><dd><?php echo esc_html( $xin_transl ); ?></dd></div>
					<?php endif; ?>
					<div><dt><?php esc_html_e( 'Добавлен', 'xi-novels' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( $xin_when ) ); ?></time></dd></div>
					<div><dt><?php esc_html_e( 'Обновлён', 'xi-novels' ); ?></dt><dd><time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date( $xin_when ) ); ?></time></dd></div>
					<?php if ( $xin_source ) : ?>
						<div><dt><?php esc_html_e( 'Источник', 'xi-novels' ); ?></dt><dd><a href="<?php echo esc_url( $xin_source ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'открыть', 'xi-novels' ); ?></a></dd></div>
					<?php endif; ?>
				</dl>

				<h2 class="nv-h"><?php esc_html_e( 'Над проектом работают', 'xi-novels' ); ?></h2>
				<div class="nv-team">
					<a class="nv-person" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
						<span><b><?php the_author(); ?></b><small><?php esc_html_e( 'ведёт проект', 'xi-novels' ); ?></small></span>
					</a>
					<?php foreach ( $xin_team as $xin_member ) : ?>
						<a class="nv-person" href="<?php echo esc_url( get_author_posts_url( $xin_member->ID ) ); ?>">
							<?php echo get_avatar( $xin_member->ID, 40 ); ?>
							<span><b><?php echo esc_html( $xin_member->display_name ); ?></b><small><?php esc_html_e( 'переводчик', 'xi-novels' ); ?></small></span>
						</a>
					<?php endforeach; ?>
				</div>

				<h2 class="nv-h"><?php esc_html_e( 'Ваша оценка', 'xi-novels' ); ?></h2>
				<div class="nv-rate xin-nv__rate" data-xin-rate="<?php echo (int) $xin_id; ?>">
					<span class="nv-rate__stars xin-nv__stars">
						<?php for ( $xin_s = 1; $xin_s <= 5; $xin_s++ ) : ?>
							<button type="button" data-value="<?php echo (int) $xin_s; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Оценить на %d', 'xi-novels' ), $xin_s ) ); ?>">
								<?php xin_the_icon( 'star', $xin_s <= round( $xin_rating['value'] ) ? '' : 'is-off', true ); ?>
							</button>
						<?php endfor; ?>
					</span>
					<span class="nv-rate__num xin-nv__ratenum">
						<b data-xin-rate-value><?php echo $xin_rating['count'] ? esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ) : '—'; ?></b>
						<small>(<span data-xin-rate-count><?php echo (int) $xin_rating['count']; ?></span>)</small>
					</span>
				</div>

				<?php
				$xin_related = array();
				if ( ! is_wp_error( $xin_genres ) && $xin_genres ) {
					$xin_related = get_posts( array(
						'post_type'      => 'novel',
						'posts_per_page' => 6,
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
				}
				?>
				<?php if ( $xin_related ) : ?>
					<h2 class="nv-h"><?php esc_html_e( 'Вам также может понравиться', 'xi-novels' ); ?></h2>
					<div class="nv-similar">
						<?php foreach ( $xin_related as $xin_rel ) : ?>
							<?php xin_novel_card( $xin_rel->ID ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'sidebar-novel' ) ) : ?>
					<div class="nv-widgets"><?php dynamic_sidebar( 'sidebar-novel' ); ?></div>
				<?php endif; ?>

			</section>

			<section class="nv-panel" id="chapters" data-xin-panel="toc" hidden>

				<?php if ( $xin_chapters ) : ?>
					<p class="nv-last">
						<?php esc_html_e( 'Последнее обновление', 'xi-novels' ); ?>
						<a href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>"><?php echo esc_html( get_the_title( $xin_last->ID ) ); ?></a>
						<time><?php echo esc_html( xin_ago( get_post_time( 'U', true, $xin_last->ID ) ) ); ?></time>
					</p>

					<div class="nv-tools">
						<input type="search" placeholder="<?php esc_attr_e( 'Поиск по главам…', 'xi-novels' ); ?>" data-xin-chapter-search aria-label="<?php esc_attr_e( 'Поиск по главам', 'xi-novels' ); ?>">
						<button type="button" class="nv-plain" data-xin-chapter-sort>
							<?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Порядок', 'xi-novels' ); ?>
						</button>
					</div>

					<ol class="nv-chapters" data-xin-chapter-list>
						<?php foreach ( $xin_chapters as $xin_i => $xin_chapter ) : ?>
							<?php
							$xin_locked = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
							$xin_label  = xin_chapter_label( $xin_chapter->ID );
							?>
							<li data-xin-chapter-item>
								<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
									<span class="nv-chapters__num"><?php echo esc_html( $xin_label ? $xin_label : $xin_i + 1 ); ?></span>
									<span class="nv-chapters__title"><?php echo esc_html( get_the_title( $xin_chapter->ID ) ); ?></span>
									<?php if ( $xin_locked ) : ?>
										<span class="nv-chapters__lock" title="<?php esc_attr_e( 'Только для подписки', 'xi-novels' ); ?>"><?php xin_the_icon( 'lock' ); ?></span>
									<?php endif; ?>
									<time class="nv-chapters__date"><?php echo esc_html( xin_ago( get_post_time( 'U', true, $xin_chapter->ID ) ) ); ?></time>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<p class="nv-empty"><?php esc_html_e( 'Главы ещё не опубликованы.', 'xi-novels' ); ?></p>
				<?php endif; ?>

			</section>

			<div class="nv-talk">
				<?php xin_talk_render( $xin_id ); ?>
			</div>

		</div>
	</article>

	<?php
endwhile;

get_footer();
