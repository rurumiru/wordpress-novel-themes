<?php
/**
 * Результаты поиска. Тайтлы показываем карточками, остальное — списком:
 * обложка помогает узнать книгу быстрее, чем заголовок.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_novels = array();
$xv_rest   = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		if ( 'novel' === get_post_type() ) {
			$xv_novels[] = get_the_ID();
		} else {
			$xv_rest[] = get_the_ID();
		}
	}
}
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<h1 class="xv-page-title"><?php esc_html_e( 'Поиск', 'xin-v2' ); ?></h1>
		<p class="xv-page-lede">
			<?php
			printf(
				/* translators: 1: поисковый запрос, 2: число результатов. */
				esc_html__( 'По запросу «%1$s» — %2$s результатов.', 'xin-v2' ),
				esc_html( get_search_query() ),
				esc_html( number_format_i18n( (int) $GLOBALS['wp_query']->found_posts ) )
			);
			?>
		</p>
	</header>

	<?php if ( $xv_novels ) : ?>
		<section class="xv-search-block">
			<?php xv_section_head( array( 'title' => __( 'Тайтлы', 'xin-v2' ) ) ); ?>
			<div class="xv-catalog-grid">
				<?php foreach ( $xv_novels as $xv_id ) : ?>
					<?php xv_novel_card( $xv_id ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $xv_rest ) : ?>
		<section class="xv-search-block">
			<?php xv_section_head( array( 'title' => __( 'Остальное', 'xin-v2' ) ) ); ?>
			<div class="xv-post-list">
				<?php foreach ( $xv_rest as $xv_id ) : ?>
					<article class="xv-post-item">
						<div>
							<h3 class="xv-post-item-title"><a href="<?php echo esc_url( get_permalink( $xv_id ) ); ?>"><?php echo esc_html( get_the_title( $xv_id ) ); ?></a></h3>
							<p class="xv-post-item-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $xv_id ), 24 ) ); ?></p>
							<p class="xv-post-item-meta">
								<?php
								$xv_type = get_post_type_object( get_post_type( $xv_id ) );
								echo esc_html( $xv_type ? $xv_type->labels->singular_name : '' );
								?>
								· <?php echo esc_html( xv_ago( get_post_timestamp( $xv_id ) ) ); ?>
							</p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! $xv_novels && ! $xv_rest ) : ?>
		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Ничего не нашлось', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Попробуйте другое написание — или загляните в каталог.', 'xin-v2' ); ?></p>
			<a class="xv-btn primary" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'В каталог', 'xin-v2' ); ?></a>
		</div>
	<?php endif; ?>

	<?php xv_pagination(); ?>
</div>

<?php get_footer(); ?>
