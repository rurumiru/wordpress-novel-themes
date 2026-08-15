<?php

get_header();

$xin_featured  = xin_get_novels( 'featured', 6 );
$xin_popular   = xin_get_novels( 'popular', 12 );
$xin_latest    = xin_get_novels( 'latest', 12 );
$xin_updated   = xin_get_novels( 'updated', 12 );
$xin_rated     = xin_get_novels( 'rating', 10 );
$xin_chapters  = xin_get_latest_chapters( 12 );
$xin_stats     = xin_site_stats();
$xin_hero_pool = $xin_featured ? $xin_featured : $xin_popular;
$xin_hero      = array_slice( $xin_hero_pool, 0, 5 );

$xin_banner = array();
foreach ( $xin_hero_pool as $xin_id ) {
	if ( xin_background_url( $xin_id ) ) {
		$xin_banner[] = $xin_id;
	}
}
$xin_banner = array_slice( $xin_banner, 0, 5 );
?>

<?php if ( $xin_banner ) : ?>
	<section class="xin-banner" data-xin-banner>
		<div class="xin-banner__track" data-xin-banner-track>
			<?php foreach ( $xin_banner as $xin_id ) : ?>
				<?php
				$xin_status = xin_novel_status( $xin_id );
				$xin_terms  = get_the_terms( $xin_id, 'genre' );
				?>
				<article class="xin-banner__slide">
					<img src="<?php echo esc_url( xin_background_url( $xin_id ) ); ?>" alt="" loading="lazy">
					<div class="xin-banner__body">
						<?php if ( $xin_status ) : ?>
							<div><span class="xin-badge xin-badge--primary"><?php echo esc_html( $xin_status->name ); ?></span></div>
						<?php endif; ?>
						<h2><?php echo esc_html( get_the_title( $xin_id ) ); ?></h2>
						<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $xin_id ) ), 26 ) ); ?></p>
						<div class="xin-flex xin-flex-wrap">
							<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
								<?php xin_the_icon( 'book-open' ); ?><?php esc_html_e( 'Открыть тайтл', 'xi-novels' ); ?>
							</a>
							<?php if ( ! is_wp_error( $xin_terms ) && $xin_terms ) : ?>
								<span class="xin-badge xin-badge--solid"><?php echo esc_html( $xin_terms[0]->name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $xin_banner ) > 1 ) : ?>
			<button type="button" class="xin-banner__arrow xin-banner__arrow--prev" data-xin-banner-prev aria-label="<?php esc_attr_e( 'Назад', 'xi-novels' ); ?>"><?php xin_the_icon( 'chevron-left' ); ?></button>
			<button type="button" class="xin-banner__arrow xin-banner__arrow--next" data-xin-banner-next aria-label="<?php esc_attr_e( 'Вперёд', 'xi-novels' ); ?>"><?php xin_the_icon( 'chevron-right' ); ?></button>
			<div class="xin-banner__dots" data-xin-banner-dots>
				<?php foreach ( $xin_banner as $xin_i => $xin_id ) : ?>
					<button type="button" class="<?php echo 0 === $xin_i ? 'is-active' : ''; ?>" data-index="<?php echo (int) $xin_i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Слайд %d', 'xi-novels' ), $xin_i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_services' ) ) : ?>
	<div class="xin-wrap">
		<?php get_template_part( 'template-parts/section', 'services' ); ?>
	</div>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_hero' ) && $xin_hero ) : ?>
	<?php get_template_part( 'template-parts/section', 'hero', array( 'ids' => $xin_hero ) ); ?>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_stats' ) && $xin_stats['novels'] > 0 ) : ?>
	<div class="xin-wrap xin-reveal">
		<dl class="xin-glass xin-stats">
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['novels']; ?>">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'тайтлов', 'xi-novels' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['chapters']; ?>">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'глав', 'xi-novels' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['views']; ?>" data-xin-compact="1">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'прочтений', 'xi-novels' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['readers']; ?>" data-xin-compact="1">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'читателей', 'xi-novels' ); ?></dd>
			</div>
		</dl>
	</div>
<?php endif; ?>

<section class="xin-wrap xin-section" data-xin-continue hidden>
	<?php
	xin_section_head( array(
		'eyebrow'  => __( 'вы читали', 'xi-novels' ),
		'title'    => __( 'Продолжить чтение', 'xi-novels' ),
		'subtitle' => __( 'История хранится в вашем браузере', 'xi-novels' ),
		'icon'     => 'clock',
	) );
	?>
	<div class="xin-continue" data-xin-continue-list></div>
</section>

<?php if ( xin_show( 'xin_show_ranking' ) && $xin_popular ) : ?>
	<?php
	get_template_part( 'template-parts/section', 'ranking', array(
		'popular' => array_slice( $xin_popular, 0, 10 ),
		'rated'   => $xin_rated,
		'updated' => array_slice( $xin_updated, 0, 10 ),
	) );
	?>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_new' ) && $xin_latest ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'eyebrow'    => __( 'дебюты', 'xi-novels' ),
			'title'      => __( 'Новинки', 'xi-novels' ),
			'subtitle'   => __( 'Свежие тайтлы на площадке', 'xi-novels' ),
			'icon'       => 'sparkles',
			'more_href'  => get_post_type_archive_link( 'novel' ),
			'more_label' => __( 'Весь каталог', 'xi-novels' ),
		) );
		?>
		<div class="xin-rail">
			<?php foreach ( $xin_latest as $xin_id ) : ?>
				<?php xin_novel_card( $xin_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
$xin_genres = get_terms( array(
	'taxonomy'   => 'genre',
	'hide_empty' => true,
	'number'     => 14,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );
?>
<?php if ( xin_show( 'xin_show_genres' ) && ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'title'    => __( 'Жанры', 'xi-novels' ),
			'subtitle' => __( 'Выберите настроение — остальное подберём', 'xi-novels' ),
			'icon'     => 'compass',
		) );
		?>
		<div class="xin-genres">
			<?php foreach ( $xin_genres as $xin_genre ) : ?>
				<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>">
					<?php echo esc_html( $xin_genre->name ); ?><b><?php echo (int) $xin_genre->count; ?></b>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_trending' ) && $xin_popular ) : ?>
	<?php get_template_part( 'template-parts/section', 'trending', array( 'ids' => array_slice( $xin_popular, 0, 3 ) ) ); ?>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_chapters' ) && $xin_chapters ) : ?>
	<section class="xin-wrap xin-section">
		<?php
		xin_section_head( array(
			'eyebrow'    => __( 'только что', 'xi-novels' ),
			'title'      => __( 'Последние главы', 'xi-novels' ),
			'subtitle'   => __( 'Свежие публикации со всего сайта', 'xi-novels' ),
			'icon'       => 'clock',
			'more_href'  => get_post_type_archive_link( 'chapter' ),
			'more_label' => __( 'Все обновления', 'xi-novels' ),
		) );
		?>
		<div class="xin-grid xin-grid--3">
			<?php foreach ( $xin_chapters as $xin_i => $xin_id ) : ?>
				<div class="xin-reveal" style="transition-delay:<?php echo (int) min( $xin_i, 6 ) * 45; ?>ms">
					<?php xin_chapter_card( $xin_id ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_updated' ) && $xin_updated ) : ?>
	<section class="xin-wrap xin-section xin-reveal">
		<?php
		xin_section_head( array(
			'title'     => __( 'Недавно обновлены', 'xi-novels' ),
			'subtitle'  => __( 'Тайтлы со свежими главами', 'xi-novels' ),
			'more_href' => get_post_type_archive_link( 'chapter' ),
		) );
		?>
		<div class="xin-grid xin-grid--6">
			<?php foreach ( array_slice( $xin_updated, 0, 12 ) as $xin_id ) : ?>
				<?php xin_novel_card( $xin_id ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_favorites' ) && $xin_rated ) : ?>
	<section class="xin-wrap xin-section">
		<?php
		xin_section_head( array(
			'eyebrow'    => __( 'выбор читателей', 'xi-novels' ),
			'title'      => __( 'Самые любимые', 'xi-novels' ),
			'subtitle'   => __( 'Топ по оценкам сообщества', 'xi-novels' ),
			'icon'       => 'heart',
			'more_href'  => add_query_arg( 'sort', 'rating', get_post_type_archive_link( 'novel' ) ),
			'more_label' => __( 'Весь рейтинг', 'xi-novels' ),
		) );
		?>
		<div class="xin-grid xin-grid--6">
			<?php foreach ( array_slice( $xin_rated, 0, 6 ) as $xin_i => $xin_id ) : ?>
				<div class="xin-reveal" style="transition-delay:<?php echo (int) min( $xin_i, 6 ) * 45; ?>ms">
					<?php xin_novel_showcase( $xin_id ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_authors' ) ) : ?>
	<?php get_template_part( 'template-parts/section', 'community' ); ?>
<?php endif; ?>

<?php if ( xin_show( 'xin_show_cta' ) ) : ?>
	<section class="xin-wrap xin-section">
		<div class="xin-grid xin-grid--2">
			<div class="xin-reveal">
				<a class="xin-cta" href="<?php echo esc_url( wp_registration_url() ); ?>">
					<span class="xin-cta__icon"><?php xin_the_icon( 'book-open' ); ?></span>
					<h3><?php esc_html_e( 'Стать автором', 'xi-novels' ); ?></h3>
					<p><?php esc_html_e( 'Публикуйте свои новеллы и переводы, ведите главы в удобном редакторе, собирайте аудиторию. Свой формат, свой темп.', 'xi-novels' ); ?></p>
					<span class="xin-cta__more"><?php esc_html_e( 'Узнать как', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?></span>
				</a>
			</div>
			<div class="xin-reveal" style="transition-delay:90ms">
				<a class="xin-cta xin-cta--gold" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
					<span class="xin-cta__icon"><?php xin_the_icon( 'sparkles' ); ?></span>
					<h3><?php esc_html_e( 'Членство PLUS', 'xi-novels' ); ?></h3>
					<p><?php esc_html_e( 'Ранний доступ к главам, закрытые релизы и поддержка любимых переводчиков.', 'xi-novels' ); ?></p>
					<span class="xin-cta__more"><?php esc_html_e( 'Узнать больше', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?></span>
				</a>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! $xin_popular && ! $xin_latest ) : ?>
	<section class="xin-wrap xin-empty" style="padding-block:96px">
		<?php xin_the_icon( 'book' ); ?>
		<h1><?php printf( esc_html__( 'Добро пожаловать в %s', 'xi-novels' ), esc_html( get_bloginfo( 'name' ) ) ); ?></h1>
		<p style="max-width:52ch;margin-inline:auto"><?php esc_html_e( 'Каталог пока пуст. Добавьте первый тайтл — и главная соберётся сама: витрина, рейтинги и лента обновлений появятся автоматически.', 'xi-novels' ); ?></p>
		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
			<a class="xin-btn xin-btn--primary xin-btn--lg xin-mt-2" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=novel' ) ); ?>">
				<?php esc_html_e( 'Добавить новеллу', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
