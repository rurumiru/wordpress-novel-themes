<?php
/**
 * Template Name: Рейтинг
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_by = isset( $_GET['by'] ) ? sanitize_key( wp_unslash( $_GET['by'] ) ) : 'popular';

$xv_tabs = array(
	'popular' => __( 'По просмотрам', 'xin-v2' ),
	'rating'  => __( 'По оценке', 'xin-v2' ),
	'updated' => __( 'По обновлению', 'xin-v2' ),
);

if ( ! isset( $xv_tabs[ $xv_by ] ) ) {
	$xv_by = 'popular';
}

$xv_list = xv_get_novels( $xv_by, 30 );
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
		<p class="xv-page-lede"><?php esc_html_e( 'Что читают чаще всего — и что оценили выше всего.', 'xin-v2' ); ?></p>
	</header>

	<div class="xv-tabs">
		<?php foreach ( $xv_tabs as $xv_key => $xv_label ) : ?>
			<a class="xv-tab <?php echo $xv_by === $xv_key ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'by', $xv_key, get_permalink() ) ); ?>"><?php echo esc_html( $xv_label ); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $xv_list ) : ?>
		<div class="xv-ranking">
			<?php foreach ( $xv_list as $xv_i => $xv_novel ) : ?>
				<?php xv_rank_row( $xv_novel->ID, $xv_i + 1 ); ?>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Считать пока нечего', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Рейтинг соберётся, как только появятся тайтлы.', 'xin-v2' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<div class="xv-prose xv-ranking-note"><?php the_content(); ?></div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
