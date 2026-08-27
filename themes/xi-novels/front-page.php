<?php
/**
 * Главная.
 *
 * Собрана заново по эталону: баннер, витрина с одним крупным тайтлом, доска
 * рейтингов в три колонки, полки новинок и обновлений, лента последних глав и
 * жанры. Порядок и состав блоков по-прежнему слушаются переключателей в
 * настройках темы (`xin_show`), поэтому владелец площадки ничего не теряет.
 *
 * Классы с приставкой `hm-` свои: старые правила главной из pages.css к этой
 * разметке не применяются, и облик не надо перебивать.
 *
 * @package XI_Novels
 */

get_header();

$xin_featured = xin_get_novels( 'featured', 6 );
$xin_popular  = xin_get_novels( 'popular', 12 );
$xin_latest   = xin_get_novels( 'latest', 12 );
$xin_updated  = xin_get_novels( 'updated', 12 );
$xin_rated    = xin_get_novels( 'rating', 10 );
$xin_chapters = xin_get_latest_chapters( 10 );
$xin_stats    = xin_site_stats();

$xin_pool  = $xin_featured ? $xin_featured : $xin_popular;
$xin_star  = $xin_pool ? $xin_pool[0] : 0;
$xin_picks = array_slice( $xin_pool, 1, 4 );

$xin_banner = xin_get_banners( 6 );
if ( ! $xin_banner ) {
	$xin_banner = array_slice( xin_banners_from_novels( $xin_pool ), 0, 5 );
}
?>

<?php if ( $xin_banner ) : ?>
	<?php get_template_part( 'template-parts/section', 'banner', array( 'banners' => $xin_banner ) ); ?>
<?php endif; ?>

<?php if ( $xin_star ) : ?>
	<?php
	$xin_star_cover  = xin_cover_url( $xin_star, 'xin-cover-lg' );
	$xin_star_rating = xin_rating( $xin_star );
	$xin_star_first  = xin_first_chapter( $xin_star );
	$xin_star_genres = get_the_terms( $xin_star, 'genre' );
	?>
	<section class="hm-spot">
		<div class="hm-wrap hm-spot__in">

			<a class="hm-spot__cover" href="<?php echo esc_url( get_permalink( $xin_star ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $xin_star ) ); ?>">
				<?php if ( $xin_star_cover ) : ?>
					<?php /* Первая большая картинка экрана — грузим сразу, ею меряется LCP. */ ?>
					<img src="<?php echo esc_url( $xin_star_cover ); ?>" alt="" width="520" height="780" decoding="async" fetchpriority="high">
				<?php endif; ?>
			</a>

			<div class="hm-spot__body">
				<p class="hm-eyebrow"><?php esc_html_e( 'Читают сейчас', 'xi-novels' ); ?></p>
				<h2 class="hm-spot__title"><a href="<?php echo esc_url( get_permalink( $xin_star ) ); ?>"><?php echo esc_html( get_the_title( $xin_star ) ); ?></a></h2>

				<p class="hm-spot__by"><?php echo esc_html( xin_novel_author( $xin_star ) ); ?></p>

				<?php if ( $xin_star_rating['count'] ) : ?>
					<div class="hm-score">
						<?php echo wp_kses_post( xin_stars( $xin_star_rating['value'] ) ); ?>
						<b><?php echo esc_html( number_format( $xin_star_rating['value'], 2, ',', '' ) ); ?></b>
					</div>
				<?php endif; ?>

				<p class="hm-spot__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $xin_star ) ), 42 ) ); ?></p>

				<?php if ( ! is_wp_error( $xin_star_genres ) && $xin_star_genres ) : ?>
					<div class="hm-chips">
						<?php foreach ( array_slice( $xin_star_genres, 0, 4 ) as $xin_genre ) : ?>
							<a class="hm-chip" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="hm-spot__cta">
					<?php if ( $xin_star_first ) : ?>
						<a class="btn btn-primary btn-lg" href="<?php echo esc_url( get_permalink( $xin_star_first->ID ) ); ?>">
							<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать', 'xi-novels' ); ?>
						</a>
					<?php endif; ?>
					<a class="btn btn-outline btn-lg" href="<?php echo esc_url( get_permalink( $xin_star ) ); ?>"><?php esc_html_e( 'О книге', 'xi-novels' ); ?></a>
				</div>
			</div>

			<?php if ( $xin_picks ) : ?>
				<ul class="hm-picks">
					<?php foreach ( $xin_picks as $xin_i => $xin_pick ) : ?>
						<?php $xin_pick_cover = xin_cover_url( $xin_pick, 'xin-cover-sm' ); ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $xin_pick ) ); ?>">
								<span class="hm-picks__num"><?php echo (int) $xin_i + 2; ?></span>
								<span class="hm-picks__cover">
									<?php if ( $xin_pick_cover ) : ?>
										<img src="<?php echo esc_url( $xin_pick_cover ); ?>" alt="" loading="lazy" width="120" height="180">
									<?php endif; ?>
								</span>
								<span class="hm-picks__body">
									<b><?php echo esc_html( get_the_title( $xin_pick ) ); ?></b>
									<small><?php echo esc_html( xin_num( xin_get_views( $xin_pick ) ) ); ?> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?></small>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_stats' ) && $xin_stats['novels'] > 0 ) : ?>
	<div class="hm-wrap">
		<dl class="hm-stats">
			<div><dt data-xin-count="<?php echo (int) $xin_stats['novels']; ?>">0</dt><dd><?php esc_html_e( 'тайтлов', 'xi-novels' ); ?></dd></div>
			<div><dt data-xin-count="<?php echo (int) $xin_stats['chapters']; ?>">0</dt><dd><?php esc_html_e( 'глав', 'xi-novels' ); ?></dd></div>
			<div><dt data-xin-count="<?php echo (int) $xin_stats['views']; ?>" data-xin-compact="1">0</dt><dd><?php esc_html_e( 'прочтений', 'xi-novels' ); ?></dd></div>
			<div><dt data-xin-count="<?php echo (int) $xin_stats['readers']; ?>" data-xin-compact="1">0</dt><dd><?php esc_html_e( 'читателей', 'xi-novels' ); ?></dd></div>
		</dl>
	</div>
<?php endif; ?>

<section class="hm-wrap hm-section" data-xin-continue hidden>
	<div class="hm-head"><h2><?php esc_html_e( 'Продолжить чтение', 'xi-novels' ); ?></h2></div>
	<div class="xin-continue" data-xin-continue-list></div>
</section>

<?php if ( xin_show( 'xin_show_ranking' ) && $xin_popular ) : ?>
	<?php
	$xin_boards = array(
		array( __( 'Популярное', 'xi-novels' ), array_slice( $xin_popular, 0, 8 ) ),
		array( __( 'По оценке', 'xi-novels' ), array_slice( $xin_rated, 0, 8 ) ),
		array( __( 'Обновлённое', 'xi-novels' ), array_slice( $xin_updated, 0, 8 ) ),
	);
	?>
	<section class="hm-wrap hm-section">
		<div class="hm-head">
			<h2><?php esc_html_e( 'Рейтинги', 'xi-novels' ); ?></h2>
			<a class="hm-more" href="<?php echo esc_url( xin_ranking_link() ); ?>"><?php esc_html_e( 'Весь рейтинг', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?></a>
		</div>

		<div class="hm-boards">
			<?php foreach ( $xin_boards as $xin_board ) : ?>
				<?php if ( ! $xin_board[1] ) { continue; } ?>
				<div class="hm-board">
					<h3 class="hm-board__head"><?php echo esc_html( $xin_board[0] ); ?></h3>
					<ol class="hm-board__list">
						<?php foreach ( $xin_board[1] as $xin_i => $xin_row_id ) : ?>
							<?php
							$xin_row_cover  = xin_cover_url( $xin_row_id, 'xin-cover-sm' );
							$xin_row_rating = xin_rating( $xin_row_id );
							?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $xin_row_id ) ); ?>">
									<span class="hm-board__num is-<?php echo (int) $xin_i < 3 ? 'top' : 'plain'; ?>"><?php echo (int) $xin_i + 1; ?></span>
									<?php if ( $xin_i < 3 && $xin_row_cover ) : ?>
										<span class="hm-board__cover"><img src="<?php echo esc_url( $xin_row_cover ); ?>" alt="" loading="lazy" width="120" height="180"></span>
									<?php endif; ?>
									<span class="hm-board__body">
										<b><?php echo esc_html( get_the_title( $xin_row_id ) ); ?></b>
										<small>
											<?php if ( $xin_row_rating['count'] ) : ?>
												<?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_row_rating['value'], 1, ',', '' ) ); ?> ·
											<?php endif; ?>
											<?php echo esc_html( xin_num( xin_get_views( $xin_row_id ) ) ); ?> <?php esc_html_e( 'просмотров', 'xi-novels' ); ?>
										</small>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_new' ) && $xin_latest ) : ?>
	<section class="hm-wrap hm-section">
		<div class="hm-head">
			<h2><?php esc_html_e( 'Новинки', 'xi-novels' ); ?></h2>
			<a class="hm-more" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Весь каталог', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?></a>
		</div>
		<div class="hm-shelf">
			<?php foreach ( $xin_latest as $xin_card_id ) : ?>
				<?php xin_novel_card( $xin_card_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_chapters' ) && $xin_chapters ) : ?>
	<section class="hm-wrap hm-section">
		<div class="hm-head">
			<h2><?php esc_html_e( 'Последние главы', 'xi-novels' ); ?></h2>
			<a class="hm-more" href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>"><?php esc_html_e( 'Все обновления', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?></a>
		</div>
		<ul class="hm-feed">
			<?php foreach ( $xin_chapters as $xin_ch_id ) : ?>
				<?php
				$xin_ch_novel = xin_chapter_novel_id( $xin_ch_id );
				$xin_ch_cover = $xin_ch_novel ? xin_cover_url( $xin_ch_novel, 'xin-cover-sm' ) : '';
				$xin_ch_label = xin_chapter_label( $xin_ch_id );
				?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $xin_ch_id ) ); ?>">
						<span class="hm-feed__cover">
							<?php if ( $xin_ch_cover ) : ?>
								<img src="<?php echo esc_url( $xin_ch_cover ); ?>" alt="" loading="lazy" width="120" height="180">
							<?php endif; ?>
						</span>
						<span class="hm-feed__body">
							<?php if ( $xin_ch_novel ) : ?>
								<b><?php echo esc_html( get_the_title( $xin_ch_novel ) ); ?></b>
							<?php endif; ?>
							<span>
								<?php if ( $xin_ch_label ) : ?>
									<i><?php printf( esc_html__( 'Гл. %s', 'xi-novels' ), esc_html( $xin_ch_label ) ); ?></i>
								<?php endif; ?>
								<?php echo esc_html( get_the_title( $xin_ch_id ) ); ?>
							</span>
						</span>
						<time><?php echo esc_html( xin_ago( get_post_time( 'U', true, $xin_ch_id ) ) ); ?></time>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_updated' ) && $xin_updated ) : ?>
	<section class="hm-wrap hm-section">
		<div class="hm-head">
			<h2><?php esc_html_e( 'Недавно обновлены', 'xi-novels' ); ?></h2>
		</div>
		<div class="hm-shelf">
			<?php foreach ( array_slice( $xin_updated, 0, 12 ) as $xin_card_id ) : ?>
				<?php xin_novel_card( $xin_card_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$xin_genre_list = get_terms( array(
	'taxonomy'   => 'genre',
	'hide_empty' => true,
	'number'     => 16,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );
?>
<?php if ( xin_show( 'xin_show_genres' ) && ! is_wp_error( $xin_genre_list ) && $xin_genre_list ) : ?>
	<section class="hm-wrap hm-section">
		<div class="hm-head"><h2><?php esc_html_e( 'Жанры', 'xi-novels' ); ?></h2></div>
		<div class="hm-genres">
			<?php foreach ( $xin_genre_list as $xin_genre ) : ?>
				<a href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>">
					<?php echo esc_html( $xin_genre->name ); ?><b><?php echo (int) $xin_genre->count; ?></b>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_cta' ) ) : ?>
	<section class="hm-wrap hm-section">
		<div class="hm-cta">
			<a class="hm-cta__card" href="<?php echo esc_url( xin_page_url( 'become-author' ) ); ?>">
				<span class="hm-cta__icon"><?php xin_the_icon( 'book-open' ); ?></span>
				<b><?php esc_html_e( 'Стать автором', 'xi-novels' ); ?></b>
				<span><?php esc_html_e( 'Публикуйте свои новеллы и переводы, ведите главы в удобном редакторе, собирайте аудиторию.', 'xi-novels' ); ?></span>
			</a>
			<a class="hm-cta__card hm-cta__card--gold" href="<?php echo esc_url( xin_page_url( 'plus' ) ); ?>">
				<span class="hm-cta__icon"><?php xin_the_icon( 'sparkles' ); ?></span>
				<b><?php esc_html_e( 'Членство PLUS', 'xi-novels' ); ?></b>
				<span><?php esc_html_e( 'Ранний доступ к главам, закрытые релизы и поддержка любимых переводчиков.', 'xi-novels' ); ?></span>
			</a>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! $xin_popular && ! $xin_latest ) : ?>
	<section class="hm-wrap hm-empty">
		<?php xin_the_icon( 'book' ); ?>
		<h1><?php printf( esc_html__( 'Добро пожаловать в %s', 'xi-novels' ), esc_html( get_bloginfo( 'name' ) ) ); ?></h1>
		<p><?php esc_html_e( 'Каталог пока пуст. Добавьте первый тайтл — и главная соберётся сама: витрина, рейтинги и лента обновлений появятся автоматически.', 'xi-novels' ); ?></p>
		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
			<a class="btn btn-primary btn-lg" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=novel' ) ); ?>"><?php esc_html_e( 'Добавить новеллу', 'xi-novels' ); ?></a>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
