<?php
/**
 * Template Name: Моя библиотека
 *
 * Закладки и история чтения хранятся у читателя в браузере, поэтому страница
 * собирается на клиенте. Регистрация для этого не нужна — именно так к
 * площадке и приходят: сначала читают, потом заводят аккаунт.
 *
 * @package XI_Novels
 */

get_header();
?>

<div class="xin-wrap">
	<header class="xin-pagehead">
		<?php xin_breadcrumbs(); ?>
		<h1><?php esc_html_e( 'Моя библиотека', 'xi-novels' ); ?></h1>
		<p class="xin-pagehead__sub"><?php esc_html_e( 'Закладки и история чтения хранятся в этом браузере — аккаунт не нужен.', 'xi-novels' ); ?></p>
	</header>

	<section class="xin-section" data-xin-lib-continue hidden>
		<?php
		xin_section_head( array(
			'eyebrow' => __( 'продолжить', 'xi-novels' ),
			'title'   => __( 'На чём вы остановились', 'xi-novels' ),
			'icon'    => 'clock',
		) );
		?>
		<div class="xin-continue" data-xin-continue-list></div>
	</section>

	<section class="xin-section">
		<?php
		xin_section_head( array(
			'title'    => __( 'Закладки', 'xi-novels' ),
			'subtitle' => __( 'Тайтлы, отмеченные закладкой в каталоге', 'xi-novels' ),
			'icon'     => 'bookmark',
		) );
		?>
		<div class="xin-grid xin-grid--6" data-xin-lib-list></div>

		<div class="xin-empty" data-xin-lib-empty hidden>
			<?php xin_the_icon( 'bookmark' ); ?>
			<h2><?php esc_html_e( 'Пока пусто', 'xi-novels' ); ?></h2>
			<p><?php esc_html_e( 'Нажмите на закладку у любой обложки в каталоге — тайтл появится здесь.', 'xi-novels' ); ?></p>
			<a class="btn btn-primary xin-mt-2" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
				<?php xin_the_icon( 'compass' ); ?><?php esc_html_e( 'В каталог', 'xi-novels' ); ?>
			</a>
		</div>
	</section>

	<?php if ( get_the_content() ) : ?>
		<div class="xin-content xin-section"><?php the_content(); ?></div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
