<?php

get_header();

global $wp_query;
?>

<div class="xin-wrap">
	<header class="xin-pagehead">
		<?php xin_breadcrumbs(); ?>
		<h1><?php printf( esc_html__( 'Поиск: %s', 'xin-com' ), esc_html( get_search_query() ) ); ?></h1>
		<p class="xin-pagehead__sub">
			<?php
			printf(
				esc_html( xin_plural( $wp_query->found_posts, __( 'Найден %s результат', 'xin-com' ), __( 'Найдено %s результата', 'xin-com' ), __( 'Найдено %s результатов', 'xin-com' ) ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</p>
		<div class="xin-mt-2" style="max-width:520px"><?php get_search_form(); ?></div>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php

$xin_novels   = array();
		$xin_chapters = array();
		$xin_posts    = array();

		while ( have_posts() ) :
			the_post();
			$xin_type = get_post_type();
			if ( 'novel' === $xin_type ) {
				$xin_novels[] = get_the_ID();
			} elseif ( 'chapter' === $xin_type ) {
				$xin_chapters[] = get_the_ID();
			} else {
				$xin_posts[] = get_the_ID();
			}
		endwhile;
		?>

		<?php if ( $xin_novels ) : ?>
			<section class="xin-section">
				<?php xin_section_head( array( 'title' => __( 'Тайтлы', 'xin-com' ), 'icon' => 'book' ) ); ?>
				<div class="xin-grid xin-grid--6">
					<?php foreach ( $xin_novels as $xin_id ) : ?>
						<?php xin_novel_card( $xin_id ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $xin_chapters ) : ?>
			<section class="xin-section">
				<?php xin_section_head( array( 'title' => __( 'Главы', 'xin-com' ), 'icon' => 'list' ) ); ?>
				<div class="xin-grid xin-grid--3">
					<?php foreach ( $xin_chapters as $xin_id ) : ?>
						<?php xin_chapter_card( $xin_id ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $xin_posts ) : ?>
			<section class="xin-section">
				<?php xin_section_head( array( 'title' => __( 'Статьи', 'xin-com' ), 'icon' => 'newspaper' ) ); ?>
				<div class="xin-grid xin-grid--3">
					<?php foreach ( $xin_posts as $xin_id ) : ?>
						<?php xin_post_card( $xin_id ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php xin_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
