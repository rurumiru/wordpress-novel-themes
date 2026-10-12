<?php
/**
 * Читалка комикса.
 *
 * Страницы, а не текст: настройки размера шрифта и бумаги здесь бессмысленны,
 * зато нужны направление чтения и ширина полосы. Поэтому у комикса своя
 * панель, а не урезанная текстовая.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$xv_id    = get_the_ID();
	$xv_novel = xv_chapter_novel( $xv_id );
	$xv_near  = xv_adjacent_chapters( $xv_id );
	$xv_open  = xv_chapter_unlocked( $xv_id );
	$xv_lock  = xv_chapter_lock( $xv_id );
	$xv_pages = $xv_open ? xv_comic_page_sources( $xv_id ) : array();
	$xv_dir   = $xv_novel ? xv_comic_direction( $xv_novel->ID ) : 'vertical';
	$xv_num   = xv_chapter_number( $xv_id );
	?>

	<div class="xv-reader-mobile">
		<a class="xv-icon-btn" href="<?php echo esc_url( $xv_novel ? get_permalink( $xv_novel ) : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'К тайтлу', 'xin-v2' ); ?>">
			<?php xv_the_icon( 'chevron-left' ); ?>
		</a>
		<a class="xv-reader-mobile-title" href="<?php echo esc_url( $xv_novel ? get_permalink( $xv_novel ) : home_url( '/' ) ); ?>">
			<?php echo esc_html( $xv_novel ? get_the_title( $xv_novel ) : get_bloginfo( 'name' ) ); ?>
		</a>
		<a class="xv-icon-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На главную', 'xin-v2' ); ?>">
			<?php xv_the_icon( 'home' ); ?>
		</a>
	</div>

	<div class="xv-comic" data-xv-comic data-xv-dir="<?php echo esc_attr( $xv_dir ); ?>">

		<header class="xv-comic-head">
			<div>
				<?php if ( $xv_novel ) : ?>
					<a class="xv-comic-novel" href="<?php echo esc_url( get_permalink( $xv_novel ) ); ?>"><?php echo esc_html( get_the_title( $xv_novel ) ); ?></a>
				<?php endif; ?>
				<h1 class="xv-comic-title">
					<?php if ( '' !== $xv_num ) : ?>
						<span class="xv-comic-num"><?php printf( esc_html__( 'Глава %s', 'xin-v2' ), esc_html( $xv_num ) ); ?></span>
					<?php endif; ?>
					<?php the_title(); ?>
				</h1>
			</div>

			<div class="xv-comic-head-tools">
				<button type="button" class="xv-btn sm" data-xv-comic-dir title="<?php esc_attr_e( 'Направление чтения', 'xin-v2' ); ?>">
					<?php xv_the_icon( 'align' ); ?><span data-xv-comic-dir-label><?php echo esc_html( xv_comic_directions()[ $xv_dir ] ); ?></span>
				</button>
				<button type="button" class="xv-btn sm" data-xv-comic-fit title="<?php esc_attr_e( 'Ширина полосы', 'xin-v2' ); ?>">
					<?php xv_the_icon( 'expand' ); ?>
				</button>
			</div>
		</header>

		<?php if ( ! $xv_open ) : ?>

			<div class="xv-locked">
				<?php xv_the_icon( 'lock' ); ?>
				<p class="xv-locked-title"><?php esc_html_e( 'Глава в раннем доступе', 'xin-v2' ); ?></p>
				<?php if ( 'schedule' === $xv_lock['kind'] ) : ?>
					<p class="xv-locked-when">
						<?php
						printf(
							/* translators: %s: дата открытия главы. */
							esc_html__( 'Откроется %s', 'xin-v2' ),
							esc_html( wp_date( 'j M Y, H:i', $xv_lock['at'] ) )
						);
						?>
					</p>
				<?php elseif ( 'purchase' === $xv_lock['kind'] && $xv_lock['buy_url'] ) : ?>
					<p class="xv-locked-when"><?php echo esc_html( $xv_lock['money'] ); ?></p>
					<a class="xv-btn primary" href="<?php echo esc_url( $xv_lock['buy_url'] ); ?>"><?php esc_html_e( 'Купить главу', 'xin-v2' ); ?></a>
				<?php endif; ?>
			</div>

		<?php elseif ( ! $xv_pages ) : ?>

			<div class="xv-empty">
				<p class="xv-empty-title"><?php esc_html_e( 'Страниц пока нет', 'xin-v2' ); ?></p>
				<p class="xv-empty-text"><?php esc_html_e( 'Глава заведена, но страницы ещё не загружены.', 'xin-v2' ); ?></p>
			</div>

		<?php else : ?>

			<div class="xv-comic-pages" data-xv-comic-pages>
				<?php foreach ( $xv_pages as $xv_i => $xv_page ) : ?>
					<img
						class="xv-comic-page"
						src="<?php echo esc_url( $xv_page['small'] ); ?>"
						width="<?php echo (int) $xv_page['width']; ?>"
						height="<?php echo (int) $xv_page['height']; ?>"
						alt="<?php echo esc_attr( $xv_page['alt'] ? $xv_page['alt'] : sprintf( __( 'Страница %d', 'xin-v2' ), $xv_i + 1 ) ); ?>"
						<?php echo $xv_i < 2 ? 'fetchpriority="high"' : 'loading="lazy" decoding="async"'; ?>
					>
				<?php endforeach; ?>
			</div>

			<p class="xv-comic-count">
				<?php
				printf(
					/* translators: %s: число страниц вместе со словом. */
					esc_html__( 'Страниц: %s', 'xin-v2' ),
					esc_html( number_format_i18n( count( $xv_pages ) ) )
				);
				?>
			</p>

		<?php endif; ?>

		<nav class="xv-chapter-nav" aria-label="<?php esc_attr_e( 'Соседние главы', 'xin-v2' ); ?>">
			<?php if ( $xv_near['prev'] ) : ?>
				<a data-xv-prev href="<?php echo esc_url( get_permalink( $xv_near['prev'] ) ); ?>">
					<span class="dir">&larr; <?php esc_html_e( 'Предыдущая', 'xin-v2' ); ?></span>
					<span class="name"><?php echo esc_html( get_the_title( $xv_near['prev'] ) ); ?></span>
				</a>
			<?php else : ?>
				<span class="is-off">
					<span class="dir">&larr; <?php esc_html_e( 'Предыдущая', 'xin-v2' ); ?></span>
					<span class="name"><?php esc_html_e( 'Это первая глава', 'xin-v2' ); ?></span>
				</span>
			<?php endif; ?>

			<?php if ( $xv_near['next'] ) : ?>
				<a class="next" data-xv-next href="<?php echo esc_url( get_permalink( $xv_near['next'] ) ); ?>">
					<span class="dir"><?php esc_html_e( 'Следующая', 'xin-v2' ); ?> &rarr;</span>
					<span class="name"><?php echo esc_html( get_the_title( $xv_near['next'] ) ); ?></span>
				</a>
			<?php else : ?>
				<span class="next is-off">
					<span class="dir"><?php esc_html_e( 'Следующая', 'xin-v2' ); ?> &rarr;</span>
					<span class="name"><?php esc_html_e( 'Это последняя глава', 'xin-v2' ); ?></span>
				</span>
			<?php endif; ?>
		</nav>

		<?php if ( $xv_open && ( xv_discussion_open( $xv_id ) || get_comments_number() ) ) : ?>
			<div class="xv-comic-comments"><?php comments_template(); ?></div>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
