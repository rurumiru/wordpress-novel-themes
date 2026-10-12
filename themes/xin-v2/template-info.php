<?php
/**
 * Template Name: Информационная страница
 *
 * Правила, помощь, контакты. Читается в одну колонку, с оглавлением по
 * заголовкам справа: такие страницы длинные, и без навигации по ним ходят
 * прокруткой наугад.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	// Оглавление собираем из самого текста: отдельное поле рассинхронится в
	// первый же день.
	$xv_body  = apply_filters( 'the_content', get_the_content() );
	$xv_heads = array();

	if ( preg_match_all( '~<h2[^>]*>(.*?)</h2>~is', $xv_body, $xv_found ) ) {
		foreach ( $xv_found[1] as $xv_i => $xv_head ) {
			$xv_text = trim( wp_strip_all_tags( $xv_head ) );

			if ( '' === $xv_text ) {
				continue;
			}

			$xv_slug    = 'p' . ( $xv_i + 1 ) . '-' . sanitize_title( $xv_text );
			$xv_heads[] = array( 'id' => $xv_slug, 'text' => $xv_text );

			$xv_body = preg_replace(
				'~<h2([^>]*)>' . preg_quote( $xv_head, '~' ) . '</h2>~is',
				'<h2 id="' . esc_attr( $xv_slug ) . '"$1>' . $xv_head . '</h2>',
				$xv_body,
				1
			);
		}
	}
	?>

	<div class="xv-section xv-section-wide">
		<header class="xv-page-head">
			<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
			<h1 class="xv-page-title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="xv-page-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<div class="xv-info">
			<article class="xv-prose xv-info-body">
				<?php echo wp_kses_post( $xv_body ); ?>
			</article>

			<?php if ( count( $xv_heads ) > 2 ) : ?>
				<aside class="xv-info-side">
					<nav class="xv-info-toc" aria-label="<?php esc_attr_e( 'На этой странице', 'xin-v2' ); ?>">
						<h2 class="xv-aside-title"><?php esc_html_e( 'На этой странице', 'xin-v2' ); ?></h2>
						<ul>
							<?php foreach ( $xv_heads as $xv_head ) : ?>
								<li><a href="#<?php echo esc_attr( $xv_head['id'] ); ?>"><?php echo esc_html( $xv_head['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>

					<?php
					$xv_siblings = get_posts( array(
						'post_type'      => 'page',
						'posts_per_page' => 8,
						'post__not_in'   => array( get_the_ID() ),
						'meta_key'       => '_wp_page_template',
						'meta_value'     => 'template-info.php',
						'orderby'        => 'menu_order title',
						'order'          => 'ASC',
						'no_found_rows'  => true,
					) );
					?>

					<?php if ( $xv_siblings ) : ?>
						<nav class="xv-info-more">
							<h2 class="xv-aside-title"><?php esc_html_e( 'Рядом', 'xin-v2' ); ?></h2>
							<ul>
								<?php foreach ( $xv_siblings as $xv_page ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $xv_page ) ); ?>"><?php echo esc_html( get_the_title( $xv_page ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

get_footer();
