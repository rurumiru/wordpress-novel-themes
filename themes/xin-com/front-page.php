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

$xin_banner = xin_get_banners( 6 );
if ( ! $xin_banner ) {
	$xin_banner = array_slice( xin_banners_from_novels( $xin_hero_pool ), 0, 5 );
}
?>

<?php if ( $xin_banner ) : ?>
	<?php get_template_part( 'template-parts/section', 'banner', array( 'banners' => $xin_banner ) ); ?>
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
				<dd class="xin-stats__label"><?php esc_html_e( 'тайтлов', 'xin-com' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['chapters']; ?>">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'глав', 'xin-com' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['views']; ?>" data-xin-compact="1">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'прочтений', 'xin-com' ); ?></dd>
			</div>
			<div class="xin-stats__item">
				<dt class="xin-stats__num" data-xin-count="<?php echo (int) $xin_stats['readers']; ?>" data-xin-compact="1">0</dt>
				<dd class="xin-stats__label"><?php esc_html_e( 'читателей', 'xin-com' ); ?></dd>
			</div>
		</dl>
	</div>
<?php endif; ?>

<section class="xin-wrap xin-section" data-xin-continue hidden>
	<?php
	xin_section_head( array(
		'eyebrow'  => __( 'вы читали', 'xin-com' ),
		'title'    => __( 'Продолжить чтение', 'xin-com' ),
		'subtitle' => __( 'История хранится в вашем браузере', 'xin-com' ),
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
			'eyebrow'    => __( 'дебюты', 'xin-com' ),
			'title'      => __( 'Новинки', 'xin-com' ),
			'subtitle'   => __( 'Свежие тайтлы на площадке', 'xin-com' ),
			'icon'       => 'sparkles',
			'more_href'  => get_post_type_archive_link( 'novel' ),
			'more_label' => __( 'Весь каталог', 'xin-com' ),
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
			'title'    => __( 'Жанры', 'xin-com' ),
			'subtitle' => __( 'Выберите настроение — остальное подберём', 'xin-com' ),
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
			'eyebrow'    => __( 'только что', 'xin-com' ),
			'title'      => __( 'Последние главы', 'xin-com' ),
			'subtitle'   => __( 'Свежие публикации со всего сайта', 'xin-com' ),
			'icon'       => 'clock',
			'more_href'  => get_post_type_archive_link( 'chapter' ),
			'more_label' => __( 'Все обновления', 'xin-com' ),
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
			'title'     => __( 'Недавно обновлены', 'xin-com' ),
			'subtitle'  => __( 'Тайтлы со свежими главами', 'xin-com' ),
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
			'eyebrow'    => __( 'выбор читателей', 'xin-com' ),
			'title'      => __( 'Самые любимые', 'xin-com' ),
			'subtitle'   => __( 'Топ по оценкам сообщества', 'xin-com' ),
			'icon'       => 'heart',
			'more_href'  => xin_ranking_link(),
			'more_label' => __( 'Весь рейтинг', 'xin-com' ),
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
				<a class="xin-cta" href="<?php echo esc_url( xin_page_url( 'become-author' ) ); ?>">
					<span class="xin-cta__icon"><?php xin_the_icon( 'book-open' ); ?></span>
					<h3><?php esc_html_e( 'Стать автором', 'xin-com' ); ?></h3>
					<p><?php esc_html_e( 'Публикуйте свои новеллы и переводы, ведите главы в удобном редакторе, собирайте аудиторию. Свой формат, свой темп.', 'xin-com' ); ?></p>
					<span class="xin-cta__more"><?php esc_html_e( 'Узнать как', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?></span>
				</a>
			</div>
			<div class="xin-reveal" style="transition-delay:90ms">
				<a class="xin-cta xin-cta--gold" href="<?php echo esc_url( xin_page_url( 'plus' ) ); ?>">
					<span class="xin-cta__icon"><?php xin_the_icon( 'sparkles' ); ?></span>
					<h3><?php esc_html_e( 'Членство PLUS', 'xin-com' ); ?></h3>
					<p><?php esc_html_e( 'Ранний доступ к главам, закрытые релизы и поддержка любимых переводчиков.', 'xin-com' ); ?></p>
					<span class="xin-cta__more"><?php esc_html_e( 'Узнать больше', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?></span>
				</a>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! $xin_popular && ! $xin_latest ) : ?>
	<section class="xin-wrap xin-empty" style="padding-block:96px">
		<?php xin_the_icon( 'book' ); ?>
		<h1><?php printf( esc_html__( 'Добро пожаловать в %s', 'xin-com' ), esc_html( get_bloginfo( 'name' ) ) ); ?></h1>
		<p style="max-width:52ch;margin-inline:auto"><?php esc_html_e( 'Каталог пока пуст. Добавьте первый тайтл — и главная соберётся сама: витрина, рейтинги и лента обновлений появятся автоматически.', 'xin-com' ); ?></p>
		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
			<a class="xin-btn xin-btn--primary xin-btn--lg xin-mt-2" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=novel' ) ); ?>">
				<?php esc_html_e( 'Добавить новеллу', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
