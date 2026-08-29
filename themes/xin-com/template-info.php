<?php
/**
 * Template Name: Информационная страница
 */

get_header();

$xin_pages = get_posts( array(
	'post_type'      => 'page',
	'posts_per_page' => 12,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
	'meta_query'     => array(
		array(
			'key'     => '_wp_page_template',
			'value'   => 'template-info.php',
			'compare' => '=',
		),
	),
) );

$xin_links = array(
	array( 'icon' => 'compass', 'label' => __( 'Каталог', 'xin-com' ), 'href' => get_post_type_archive_link( 'novel' ) ),
	array( 'icon' => 'clock', 'label' => __( 'Обновления', 'xin-com' ), 'href' => get_post_type_archive_link( 'chapter' ) ),
	array( 'icon' => 'pen', 'label' => __( 'Стать автором', 'xin-com' ), 'href' => xin_page_url( 'become-author' ) ),
	array( 'icon' => 'crown', 'label' => 'PLUS', 'href' => xin_page_url( 'plus' ) ),
);

while ( have_posts() ) :
	the_post();
	?>

	<div class="xin-aurora">
		<div class="xin-wrap">
			<header class="xin-pagehead">
				<?php xin_breadcrumbs(); ?>
				<span class="xin-eyebrow"><?php esc_html_e( 'справка', 'xin-com' ); ?></span>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="xin-pagehead__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>
		</div>
	</div>

	<div class="xin-wrap">
		<div class="xin-layout xin-layout--sidebar xin-mt-2">
			<article class="xin-panel xin-content xin-info">
				<?php the_content(); ?>

				<?php if ( ! get_the_content() ) : ?>
					<p class="xin-muted"><?php esc_html_e( 'Наполните эту страницу в админке: правила площадки, ответы на вопросы, контакты, политика — всё, что читателю нужно знать.', 'xin-com' ); ?></p>
				<?php endif; ?>

				<p class="xin-info__updated xin-muted">
					<?php
					printf(
						esc_html__( 'Обновлено %s', 'xin-com' ),
						esc_html( get_the_modified_date() )
					);
					?>
				</p>
			</article>

			<aside class="xin-sidebar">
				<?php if ( count( $xin_pages ) > 1 ) : ?>
					<div class="widget">
						<h2 class="widget-title"><?php esc_html_e( 'Разделы справки', 'xin-com' ); ?></h2>
						<ul>
							<?php foreach ( $xin_pages as $xin_page ) : ?>
								<li>
									<a href="<?php echo esc_url( get_permalink( $xin_page->ID ) ); ?>" <?php echo get_the_ID() === $xin_page->ID ? 'class="xin-primary"' : ''; ?>>
										<?php echo esc_html( $xin_page->post_title ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="widget">
					<h2 class="widget-title"><?php esc_html_e( 'Куда дальше', 'xin-com' ); ?></h2>
					<div class="xin-quicklinks">
						<?php foreach ( $xin_links as $xin_link ) : ?>
							<a href="<?php echo esc_url( $xin_link['href'] ); ?>">
								<?php xin_the_icon( $xin_link['icon'] ); ?><span><?php echo esc_html( $xin_link['label'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<?php
				$xin_social = xin_social_links();
				if ( $xin_social ) :
					?>
					<div class="widget">
						<h2 class="widget-title"><?php esc_html_e( 'Связаться', 'xin-com' ); ?></h2>
						<div class="xin-footer__social" style="margin-top:0">
							<?php foreach ( $xin_social as $xin_key => $xin_url ) : ?>
								<a href="<?php echo esc_url( $xin_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $xin_key ) ); ?>">
									<?php xin_the_icon( $xin_key ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</aside>
		</div>
	</div>

	<?php
endwhile;

get_footer();
