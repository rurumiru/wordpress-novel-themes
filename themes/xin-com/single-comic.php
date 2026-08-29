<?php
/**
 * Страница тайтла-комикса: /comics/<слаг>/.
 *
 * Собрана вокруг решения «читать или нет», и решение это принимают по обложке
 * и по тому, живой ли тайтл. Поэтому первый экран — арт во всю ширину с одной
 * кнопкой, а под ним вкладки: описание, главы, похожее. Всё, чем комикс не
 * измеряется, убрано — ни времени чтения, ни слов, ни выгрузки в EPUB и FB2:
 * для ленты картинок это пустые числа.
 *
 * Список глав, сортировка, поиск и сворачивание описания работают на тех же
 * механизмах, что и страница новеллы, — те же data-атрибуты, тот же theme.js.
 *
 * @package XIN-Com
 */

get_header();

while ( have_posts() ) :
	the_post();

	$xin_id       = get_the_ID();
	$xin_cover    = xin_cover_url( $xin_id, 'xin-cover-lg' );
	$xin_bg       = xin_background_url( $xin_id );
	$xin_art      = $xin_bg ? $xin_bg : $xin_cover;
	$xin_rating   = xin_rating( $xin_id );
	$xin_status   = xin_novel_status( $xin_id );
	$xin_chapters = xin_get_chapters( $xin_id, 'ASC' );
	$xin_first    = $xin_chapters ? $xin_chapters[0] : null;
	$xin_last     = $xin_chapters ? end( $xin_chapters ) : null;
	$xin_genres   = get_the_terms( $xin_id, 'genre' );
	$xin_tags     = get_the_terms( $xin_id, 'novel_tag' );
	$xin_adult    = (bool) get_post_meta( $xin_id, '_xin_adult', true );
	$xin_related  = xin_comics_related( $xin_id, 6 );
	$xin_dir      = xin_comic_direction( $xin_id );

	$xin_dirs = array(
		'strip' => __( 'лентой вниз', 'xin-com' ),
		'ltr'   => __( 'постранично, слева направо', 'xin-com' ),
		'rtl'   => __( 'постранично, справа налево', 'xin-com' ),
	);

	$xin_pages = 0;
	foreach ( $xin_chapters as $xin_chapter ) {
		$xin_pages += xin_comic_page_count( $xin_chapter->ID );
	}

	$xin_details = array_filter( array(
		__( 'Оригинальное название', 'xin-com' ) => get_post_meta( $xin_id, '_xin_original_title', true ),
		__( 'Автор', 'xin-com' )                 => xin_novel_author( $xin_id ),
		__( 'Перевод', 'xin-com' )               => get_post_meta( $xin_id, '_xin_translator', true ),
		__( 'Год', 'xin-com' )                   => get_post_meta( $xin_id, '_xin_year', true ),
		__( 'Статус', 'xin-com' )                => $xin_status ? $xin_status->name : '',
		__( 'Чтение', 'xin-com' )                => $xin_dirs[ $xin_dir ],
	) );
	?>

	<article <?php post_class( 'xin-ct' ); ?>>

		<header class="xin-ct__top">
			<div class="xin-ct__art" aria-hidden="true">
				<?php if ( $xin_art ) : ?>
					<img src="<?php echo esc_url( $xin_art ); ?>" alt="" width="1600" height="900" decoding="async" fetchpriority="low">
				<?php endif; ?>
			</div>

			<div class="xin-wrap xin-ct__topin">
				<div class="xin-ct__cover<?php echo $xin_adult ? ' is-adult' : ''; ?>">
					<?php if ( $xin_cover ) : ?>
						<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php the_title_attribute(); ?>" width="480" height="720" decoding="async" fetchpriority="high">
					<?php endif; ?>
					<?php if ( $xin_adult ) : ?>
						<span class="xin-badge xin-badge--adult">18+</span>
					<?php endif; ?>
				</div>

				<div class="xin-ct__head">
					<nav class="xin-crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'xin-com' ); ?>">
						<a href="<?php echo esc_url( xin_section_home_link( 'comic' ) ); ?>"><?php esc_html_e( 'Комиксы', 'xin-com' ); ?></a>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( xin_section_catalog_link( 'comic' ) ); ?>"><?php esc_html_e( 'Каталог', 'xin-com' ); ?></a>
					</nav>

					<h1 class="xin-ct__name"><?php the_title(); ?></h1>

					<?php if ( ! empty( $xin_details[ __( 'Оригинальное название', 'xin-com' ) ] ) ) : ?>
						<p class="xin-ct__original"><?php echo esc_html( $xin_details[ __( 'Оригинальное название', 'xin-com' ) ] ); ?></p>
					<?php endif; ?>

					<dl class="xin-ct__stats">
						<?php if ( $xin_rating['count'] ) : ?>
							<div>
								<dt><?php esc_html_e( 'оценка', 'xin-com' ); ?></dt>
								<dd class="is-rating"><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_rating['value'], 1, ',', '' ) ); ?></dd>
							</div>
						<?php endif; ?>
						<div>
							<dt><?php esc_html_e( 'глав', 'xin-com' ); ?></dt>
							<dd><?php echo (int) count( $xin_chapters ); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'страниц', 'xin-com' ); ?></dt>
							<dd><?php echo (int) $xin_pages; ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'просмотров', 'xin-com' ); ?></dt>
							<dd><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></dd>
						</div>
					</dl>

					<?php if ( $xin_genres && ! is_wp_error( $xin_genres ) ) : ?>
						<div class="xin-ct__genres">
							<?php if ( $xin_status ) : ?>
								<span class="xin-badge"><?php echo esc_html( $xin_status->name ); ?></span>
							<?php endif; ?>
							<?php foreach ( $xin_genres as $xin_genre ) : ?>
								<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="xin-ct__actions">
						<?php if ( $xin_first ) : ?>
							<a class="btn btn-primary btn-lg" href="<?php echo esc_url( get_permalink( $xin_first->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать с первой', 'xin-com' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $xin_last && $xin_last !== $xin_first ) : ?>
							<a class="btn btn-outline" href="<?php echo esc_url( get_permalink( $xin_last->ID ) ); ?>">
								<?php esc_html_e( 'Последняя глава', 'xin-com' ); ?>
							</a>
						<?php endif; ?>
						<?php xin_fav_button( $xin_id, true ); ?>
					</div>
				</div>
			</div>
		</header>

		<div class="xin-wrap xin-ct__body" data-xin-tabs>

			<div class="xin-ct__tabs" role="tablist">
				<button type="button" class="xin-ct__tab active" role="tab" aria-selected="true" data-xin-tab="about"><?php esc_html_e( 'О тайтле', 'xin-com' ); ?></button>
				<button type="button" class="xin-ct__tab" role="tab" aria-selected="false" data-xin-tab="chapters">
					<?php esc_html_e( 'Главы', 'xin-com' ); ?><b><?php echo (int) count( $xin_chapters ); ?></b>
				</button>
				<?php if ( $xin_related ) : ?>
					<button type="button" class="xin-ct__tab" role="tab" aria-selected="false" data-xin-tab="related"><?php esc_html_e( 'Похожее', 'xin-com' ); ?></button>
				<?php endif; ?>
			</div>

			<section class="xin-ct__panel" data-xin-tabpanel="about">
				<?php if ( get_the_content() ) : ?>
					<div class="xin-prose xin-ct__synopsis is-collapsed" data-xin-synopsis><?php the_content(); ?></div>
					<button type="button" class="xin-ct__more" data-xin-synopsis-toggle><?php esc_html_e( 'Читать полностью', 'xin-com' ); ?></button>
				<?php else : ?>
					<p class="xin-muted"><?php esc_html_e( 'Описание пока не заполнено.', 'xin-com' ); ?></p>
				<?php endif; ?>

				<?php if ( $xin_details ) : ?>
					<dl class="xin-ct__details">
						<?php foreach ( $xin_details as $xin_label => $xin_value ) : ?>
							<div>
								<dt><?php echo esc_html( $xin_label ); ?></dt>
								<dd><?php echo esc_html( $xin_value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( $xin_tags && ! is_wp_error( $xin_tags ) ) : ?>
					<div class="xin-ct__tags">
						<?php foreach ( $xin_tags as $xin_tag ) : ?>
							<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_tag ) ); ?>">#<?php echo esc_html( $xin_tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>

			<section class="xin-ct__panel" data-xin-tabpanel="chapters" hidden>
				<?php if ( $xin_chapters ) : ?>
					<div class="xin-ct__toolbar">
						<label class="xin-ct__search">
							<?php xin_the_icon( 'search' ); ?>
							<input type="search" data-xin-chapter-search placeholder="<?php esc_attr_e( 'Найти главу', 'xin-com' ); ?>" aria-label="<?php esc_attr_e( 'Найти главу', 'xin-com' ); ?>">
						</label>
						<button type="button" class="btn btn-outline btn-sm" data-xin-chapter-sort>
							<?php xin_the_icon( 'align' ); ?><?php esc_html_e( 'Порядок', 'xin-com' ); ?>
						</button>
					</div>

					<ol class="xin-ct__chapters" data-xin-chapter-list>
						<?php foreach ( $xin_chapters as $xin_chapter ) : ?>
							<?php
							$xin_pages_in = xin_comic_pages( $xin_chapter->ID );
							$xin_thumb    = $xin_pages_in ? wp_get_attachment_image_url( $xin_pages_in[0], 'thumbnail' ) : '';
							$xin_locked   = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
							$xin_label    = xin_chapter_label( $xin_chapter->ID );
							?>
							<li data-xin-chapter-item>
								<a class="xin-ct__chapter<?php echo $xin_locked ? ' is-locked' : ''; ?>" href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
									<span class="xin-ct__thumb">
										<?php if ( $xin_thumb ) : ?>
											<img src="<?php echo esc_url( $xin_thumb ); ?>" alt="" width="64" height="64" loading="lazy">
										<?php else : ?>
											<?php xin_the_icon( 'image' ); ?>
										<?php endif; ?>
									</span>

									<span class="xin-ct__chapter-text">
										<span class="xin-ct__chapter-name">
											<?php if ( $xin_label ) : ?>
												<b>#<?php echo esc_html( $xin_label ); ?></b>
											<?php endif; ?>
											<?php echo esc_html( $xin_chapter->post_title ); ?>
										</span>
										<span class="xin-ct__chapter-meta">
											<?php
											echo esc_html( sprintf(
												xin_plural( count( $xin_pages_in ), __( '%d страница', 'xin-com' ), __( '%d страницы', 'xin-com' ), __( '%d страниц', 'xin-com' ) ),
												count( $xin_pages_in )
											) );
											?>
											<span aria-hidden="true">·</span>
											<?php echo esc_html( xin_ago( get_the_time( 'U', $xin_chapter->ID ) ) ); ?>
										</span>
									</span>

									<?php if ( $xin_locked ) : ?>
										<span class="xin-ct__lock" title="<?php esc_attr_e( 'Ранний доступ PLUS', 'xin-com' ); ?>"><?php xin_the_icon( 'lock' ); ?></span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>

					<p class="xin-ct__none" data-xin-chapter-empty hidden><?php esc_html_e( 'Ни одна глава не подошла под запрос.', 'xin-com' ); ?></p>
				<?php else : ?>
					<p class="xin-muted"><?php esc_html_e( 'Глав пока нет.', 'xin-com' ); ?></p>
				<?php endif; ?>
			</section>

			<?php if ( $xin_related ) : ?>
				<section class="xin-ct__panel" data-xin-tabpanel="related" hidden>
					<div class="xin-cm-grid">
						<?php foreach ( $xin_related as $xin_rel ) : ?>
							<?php xin_comic_card( $xin_rel ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

		</div>

	</article>

<?php endwhile; ?>

<?php get_footer(); ?>
