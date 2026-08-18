<?php

$xin_socials = xin_social_links();
?>
</main><footer class="xin-footer">
	<div class="xin-wrap">
		<div class="xin-footer__top">

			<div class="xin-footer__about">
				<a class="navbar-brand xin-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-bottom:12px">
					<?php xin_brand(); ?>
				</a>
				<p><?php echo wp_kses_post( get_theme_mod( 'xin_footer_about', __( 'Платформа для чтения и публикации новелл, ранобэ и переводов. Читайте бесплатно, поддерживайте авторов.', 'xi-novels' ) ) ); ?></p>

				<?php if ( $xin_socials ) : ?>
					<div class="xin-footer__social">
						<?php foreach ( $xin_socials as $xin_key => $xin_url ) : ?>
							<?php $xin_meta = xin_social_meta( $xin_key ); ?>
							<a href="<?php echo esc_url( $xin_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $xin_meta[1] ); ?>" aria-label="<?php echo esc_attr( $xin_meta[1] ); ?>">
								<?php xin_the_icon( $xin_meta[0] ); ?>
							</a>
						<?php endforeach; ?>
						<a href="<?php echo esc_url( get_feed_link() ); ?>" aria-label="RSS"><?php xin_the_icon( 'rss' ); ?></a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( is_active_sidebar( 'footer-widgets' ) ) : ?>
				<?php dynamic_sidebar( 'footer-widgets' ); ?>
			<?php else : ?>

				<div>
					<h4><?php esc_html_e( 'Читателю', 'xi-novels' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Каталог тайтлов', 'xi-novels' ); ?></a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>"><?php esc_html_e( 'Последние главы', 'xi-novels' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/?s=&post_type=novel' ) ); ?>"><?php esc_html_e( 'Поиск по каталогу', 'xi-novels' ); ?></a></li>
					</ul>
				</div>

				<div>
					<h4><?php esc_html_e( 'Жанры', 'xi-novels' ); ?></h4>
					<ul>
						<?php
						$xin_genres = get_terms( array(
							'taxonomy'   => 'genre',
							'hide_empty' => false,
							'number'     => 5,
							'orderby'    => 'count',
							'order'      => 'DESC',
						) );
						if ( ! is_wp_error( $xin_genres ) && $xin_genres ) :
							foreach ( $xin_genres as $xin_genre ) :
								?>
								<li><a href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a></li>
								<?php
							endforeach;
						else :
							?>
							<li class="xin-muted"><?php esc_html_e( 'Жанры пока не заданы', 'xi-novels' ); ?></li>
						<?php endif; ?>
					</ul>
				</div>

				<div>
					<h4><?php esc_html_e( 'Проект', 'xi-novels' ); ?></h4>
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
						) );
					} else {
						?>
						<ul>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'xi-novels' ); ?></a></li>
							<?php if ( get_option( 'page_for_posts' ) ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Блог', 'xi-novels' ); ?></a></li>
							<?php endif; ?>
							<li><a href="<?php echo esc_url( xin_login_url() ); ?>"><?php esc_html_e( 'Вход для авторов', 'xi-novels' ); ?></a></li>
						</ul>
						<?php
					}
					?>
				</div>

			<?php endif; ?>
		</div>

		<div class="xin-footer__bottom">
			<div>
				<?php
				$xin_copy = get_theme_mod( 'xin_copyright', '' );
				if ( $xin_copy ) {
					echo wp_kses_post( $xin_copy );
				} else {
					printf(
						
						esc_html__( '© %1$s %2$s. Все права на тексты принадлежат их авторам.', 'xi-novels' ),
						esc_html( gmdate( 'Y' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
				}
				?>
			</div>
			<?php
			if ( has_nav_menu( 'legal' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'legal',
					'container'      => false,
					'depth'          => 1,
					'menu_class'     => 'xin-flex xin-flex-wrap',
				) );
			}
			?>
		</div>
	</div>

	<?php if ( get_theme_mod( 'xin_credit', false ) ) : ?>
		<div class="xin-footer__credit">
			<a href="https://github.com/rurumiru/wordpress-novel-themes" target="_blank" rel="noopener">
				<?php esc_html_e( 'Работает на теме XI Novels', 'xi-novels' ); ?>
			</a>
		</div>
	<?php endif; ?>
</footer>

<nav class="xin-bottomnav" aria-label="<?php esc_attr_e( 'Быстрая навигация', 'xi-novels' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo is_front_page() ? 'is-active' : ''; ?>">
		<?php xin_the_icon( 'home' ); ?><span><?php esc_html_e( 'Главная', 'xi-novels' ); ?></span>
	</a>
	<a href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>" class="<?php echo is_post_type_archive( 'novel' ) ? 'is-active' : ''; ?>">
		<?php xin_the_icon( 'compass' ); ?><span><?php esc_html_e( 'Каталог', 'xi-novels' ); ?></span>
	</a>
	<a href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>" class="<?php echo is_post_type_archive( 'chapter' ) ? 'is-active' : ''; ?>">
		<?php xin_the_icon( 'clock' ); ?><span><?php esc_html_e( 'Новое', 'xi-novels' ); ?></span>
	</a>
	<a href="<?php echo esc_url( xin_library_url() ); ?>">
		<?php xin_the_icon( 'bookmark' ); ?><span><?php esc_html_e( 'Моё', 'xi-novels' ); ?></span>
	</a>
</nav>

<button type="button" class="xin-totop" data-xin-totop aria-label="<?php esc_attr_e( 'Наверх', 'xi-novels' ); ?>">
	<?php xin_the_icon( 'chevron-up' ); ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
