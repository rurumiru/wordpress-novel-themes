<?php
/**
 * Страница комикса.
 *
 * От страницы текстового тайтла отличается оглавлением: у комикса в строке
 * главы важны обложка-превью и число страниц, а не объём текста.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$xv_id       = get_the_ID();
	$xv_chapters = xv_novel_chapters( $xv_id );
	$xv_rating   = xv_rating( $xv_id );
	$xv_views    = xv_get_views( $xv_id );
	$xv_resume   = xv_resume_chapter( $xv_id, $xv_chapters );
	?>

	<div class="xv-section xv-section-wide">
		<?php
		xv_breadcrumbs( array(
			array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ),
			array( 'label' => __( 'Комиксы', 'xin-v2' ), 'url' => xv_format_url( 'comic' ) ),
			array( 'label' => get_the_title() ),
		) );
		?>

		<div class="xv-novel">
			<div class="xv-novel-side">
				<?php xv_cover( $xv_id, 'xv-cover-lg' ); ?>

				<?php if ( $xv_resume['chapter'] ) : ?>
					<a class="xv-btn primary block" href="<?php echo esc_url( get_permalink( $xv_resume['chapter'] ) ); ?>">
						<?php xv_the_icon( 'book-open' ); ?>
						<?php echo esc_html( $xv_resume['label'] ); ?>
					</a>
				<?php endif; ?>

				<?php echo xv_fav_button( $xv_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php if ( function_exists( 'xv_push_render_bell_button' ) ) : ?>
					<?php xv_push_render_bell_button( $xv_id ); ?>
				<?php endif; ?>

				<dl class="xv-novel-facts">
					<div>
						<dt><?php esc_html_e( 'Формат', 'xin-v2' ); ?></dt>
						<dd><?php esc_html_e( 'Комикс', 'xin-v2' ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Чтение', 'xin-v2' ); ?></dt>
						<dd><?php echo esc_html( xv_comic_directions()[ xv_comic_direction( $xv_id ) ] ); ?></dd>
					</div>
					<?php if ( xv_novel_author( $xv_id ) ) : ?>
						<div>
							<dt><?php esc_html_e( 'Автор', 'xin-v2' ); ?></dt>
							<dd><?php echo esc_html( xv_novel_author( $xv_id ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			</div>

			<div class="xv-novel-main">
				<?php xv_genre_chips( $xv_id ); ?>

				<h1 class="xv-novel-title"><?php the_title(); ?></h1>

				<div class="xv-novel-stats">
					<div>
						<span class="xv-novel-stat-value"><?php echo esc_html( number_format_i18n( count( $xv_chapters ) ) ); ?></span>
						<span class="xv-novel-stat-label"><?php echo esc_html( xv_label_chapters( count( $xv_chapters ) ) ); ?></span>
					</div>
					<div>
						<span class="xv-novel-stat-value"><?php echo esc_html( xv_num( $xv_views ) ); ?></span>
						<span class="xv-novel-stat-label"><?php echo esc_html( xv_label_views( $xv_views ) ); ?></span>
					</div>
					<?php if ( $xv_rating['value'] > 0 ) : ?>
						<div>
							<span class="xv-novel-stat-value"><?php echo esc_html( number_format_i18n( $xv_rating['value'], 1 ) ); ?></span>
							<span class="xv-novel-stat-label"><?php echo esc_html( xv_label_ratings( $xv_rating['count'] ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="xv-card xv-card-pad-lg">
						<h2 class="xv-aside-title"><?php esc_html_e( 'Описание', 'xin-v2' ); ?></h2>
						<div class="xv-prose"><?php the_content(); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<section class="xv-toc">
			<?php
			xv_section_head( array(
				'title' => __( 'Главы', 'xin-v2' ),
				'sub'   => sprintf( '%s', xv_counted( count( $xv_chapters ), xv_label_chapters( count( $xv_chapters ) ) ) ),
			) );
			?>

			<?php if ( $xv_chapters ) : ?>
				<div class="xv-comic-toc">
					<?php foreach ( $xv_chapters as $xv_chapter ) : ?>
						<?php
						$xv_state = xv_chapter_state( $xv_chapter );
						$xv_pages = xv_comic_page_count( $xv_chapter->ID );
						$xv_cover = xv_comic_pages( $xv_chapter->ID );
						?>
						<a class="xv-comic-toc-row<?php echo $xv_state['locked'] ? ' is-locked' : ''; ?>" href="<?php echo esc_url( get_permalink( $xv_chapter ) ); ?>">
							<span class="xv-comic-toc-thumb">
								<?php if ( $xv_cover ) : ?>
									<?php echo wp_get_attachment_image( $xv_cover[0], 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								<?php endif; ?>
							</span>
							<span class="xv-comic-toc-body">
								<span class="xv-comic-toc-title">
									<?php if ( '' !== xv_chapter_number( $xv_chapter->ID ) ) : ?>
										<span class="xv-comic-toc-num"><?php echo esc_html( xv_chapter_number( $xv_chapter->ID ) ); ?></span>
									<?php endif; ?>
									<?php echo esc_html( get_the_title( $xv_chapter ) ); ?>
								</span>
								<span class="xv-comic-toc-meta">
									<?php if ( $xv_pages ) : ?>
										<span><?php printf( esc_html__( '%d стр.', 'xin-v2' ), (int) $xv_pages ); ?></span>
									<?php endif; ?>
									<span><?php echo esc_html( $xv_state['date'] ); ?></span>
									<?php if ( $xv_state['locked'] ) : ?>
										<span class="xv-comic-toc-lock"><?php xv_the_icon( 'lock' ); ?></span>
									<?php endif; ?>
								</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="xv-empty-text"><?php esc_html_e( 'Глав пока нет.', 'xin-v2' ); ?></p>
			<?php endif; ?>
		</section>

		<?php if ( xv_discussion_open( $xv_id ) || get_comments_number() ) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
