<?php
/**
 * Подвал сайта.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_brand = xv_brand();
?>
</main>

<footer class="xv-footer">
	<div class="xv-footer-inner">
		<div class="xv-footer-brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xv-logo">
				<?php xv_site_logo(); ?>
			</a>
			<p class="xv-footer-note">
				<?php
				$xv_note = xv_opt( 'footer_note' );
				echo esc_html( $xv_note ? $xv_note : get_bloginfo( 'description' ) );
				?>
			</p>
		</div>

		<div class="xv-footer-col">
			<h4><?php esc_html_e( 'Разделы', 'xin-v2' ); ?></h4>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'items_wrap'     => '<ul>%3$s</ul>',
				) );
				?>
			<?php else : ?>
				<ul>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Каталог', 'xin-v2' ); ?></a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>"><?php esc_html_e( 'Обновления', 'xin-v2' ); ?></a></li>
					<?php $xv_rank = xv_page_url( 'template-ranking.php' ); ?>
					<?php if ( $xv_rank ) : ?>
						<li><a href="<?php echo esc_url( $xv_rank ); ?>"><?php esc_html_e( 'Рейтинг', 'xin-v2' ); ?></a></li>
					<?php endif; ?>
					<?php $xv_blog = xv_blog_url(); ?>
					<?php if ( $xv_blog ) : ?>
						<li><a href="<?php echo esc_url( $xv_blog ); ?>"><?php esc_html_e( 'Блог', 'xin-v2' ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="xv-footer-col">
			<h4><?php esc_html_e( 'Информация', 'xin-v2' ); ?></h4>
			<?php if ( has_nav_menu( 'footer_info' ) ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer_info',
					'container'      => false,
					'depth'          => 1,
					'items_wrap'     => '<ul>%3$s</ul>',
				) );
				?>
			<?php else : ?>
				<ul>
					<?php
					$xv_pages = array_filter(array_map('get_page_by_path', array('help', 'rules', 'contacts', 'become-author')));
					foreach ( $xv_pages as $xv_page ) :
						?>
						<li><a href="<?php echo esc_url( get_permalink( $xv_page ) ); ?>"><?php echo esc_html( $xv_page->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<div class="xv-footer-bottom">
		<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $xv_brand['name'] ); ?></span>
		<?php $xv_stats = xv_site_stats(); ?>
		<span class="xv-footer-stats">
			<?php
			printf(
				/* translators: 1: число тайтлов, 2: число глав. */
				esc_html__( '%1$s тайтлов · %2$s глав', 'xin-v2' ),
				esc_html( number_format_i18n( $xv_stats['novels'] ) ),
				esc_html( number_format_i18n( $xv_stats['chapters'] ) )
			);
			?>
		</span>
	</div>
</footer>

<?php if ( ! is_singular( 'chapter' ) ) : ?>
	<?php
	// Нижняя панель — только на телефоне. В читалке её нет: там своя
	// панель управления, две сразу перекрывают текст.
	$xv_tabs = array(
		array( 'url' => home_url( '/' ), 'icon' => 'home', 'label' => __( 'Главная', 'xin-v2' ), 'on' => is_front_page() ),
		array( 'url' => get_post_type_archive_link( 'novel' ), 'icon' => 'compass', 'label' => __( 'Каталог', 'xin-v2' ), 'on' => is_post_type_archive( 'novel' ) || is_tax() ),
		array( 'url' => get_post_type_archive_link( 'chapter' ), 'icon' => 'clock', 'label' => __( 'Новое', 'xin-v2' ), 'on' => is_post_type_archive( 'chapter' ) ),
	);

	if ( is_user_logged_in() ) {
		$xv_cabinet = xv_author_url();

		if ( $xv_cabinet && xv_can_author() ) {
			$xv_tabs[] = array( 'url' => $xv_cabinet, 'icon' => 'pen', 'label' => __( 'Работы', 'xin-v2' ), 'on' => is_page_template( 'template-author.php' ) );
		}

		$xv_tabs[] = array( 'url' => get_author_posts_url( get_current_user_id() ), 'icon' => 'user', 'label' => __( 'Профиль', 'xin-v2' ), 'on' => is_author() );
	} else {
		$xv_tabs[] = array( 'url' => wp_login_url(), 'icon' => 'user', 'label' => __( 'Войти', 'xin-v2' ), 'on' => false );
	}
	?>
	<nav class="xv-tabbar" aria-label="<?php esc_attr_e( 'Разделы', 'xin-v2' ); ?>">
		<?php foreach ( $xv_tabs as $xv_tab ) : ?>
			<a class="xv-tabbar-item<?php echo $xv_tab['on'] ? ' is-on' : ''; ?>" href="<?php echo esc_url( $xv_tab['url'] ); ?>"<?php echo $xv_tab['on'] ? ' aria-current="page"' : ''; ?>>
				<?php xv_the_icon( $xv_tab['icon'] ); ?>
				<span><?php echo esc_html( $xv_tab['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
