<?php

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text" href="#xin-main"><?php esc_html_e( 'Перейти к содержимому', 'xi-novels' ); ?></a>

<header class="navbar navbar-expand-lg" id="xin-header">
	<div class="container">

		<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php xin_brand(); ?>
		</a>

		<nav class="collapse navbar-collapse d-none d-lg-flex" aria-label="<?php esc_attr_e( 'Основная навигация', 'xi-novels' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'navbar-nav',
					'depth'          => 2,
					'walker'         => new XIN_Nav_Walker(),
				) );
			} else {
				?>
				<?php
				/*
				 * Without a menu assigned to the theme location WordPress marks nothing as
				 * current, so the built-in links never showed which section was open — and a
				 * hovered link was the only one that looked selected. Work it out here.
				 */
				$xin_rating_on  = isset( $_GET['sort'] ) && 'rating' === $_GET['sort'];
				$xin_in_catalog = is_post_type_archive( 'novel' ) || is_tax( array( 'genre', 'novel_status', 'novel_tag' ) );
				$xin_rank_page  = get_page_by_path( 'ranking' );
				$xin_here       = array(
					'catalog' => $xin_in_catalog && ! $xin_rating_on,
					'updates' => is_post_type_archive( 'chapter' ),
					'rating'  => ( $xin_in_catalog && $xin_rating_on ) || ( $xin_rank_page && is_page( $xin_rank_page->ID ) ),
					'blog'    => is_home() || is_singular( 'post' ) || is_category() || is_tag(),
				);
				$xin_rank_url = xin_ranking_link();
				?>
				<ul class="navbar-nav">
					<li class="nav-item"><a class="nav-link<?php echo $xin_here['catalog'] ? ' is-current' : ''; ?>"<?php echo $xin_here['catalog'] ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Каталог', 'xi-novels' ); ?></a></li>
					<li class="nav-item"><a class="nav-link<?php echo $xin_here['updates'] ? ' is-current' : ''; ?>"<?php echo $xin_here['updates'] ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>"><?php esc_html_e( 'Обновления', 'xi-novels' ); ?></a></li>
					<li class="nav-item"><a class="nav-link<?php echo $xin_here['rating'] ? ' is-current' : ''; ?>"<?php echo $xin_here['rating'] ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( $xin_rank_url ); ?>"><?php esc_html_e( 'Рейтинг', 'xi-novels' ); ?></a></li>
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<li class="nav-item"><a class="nav-link<?php echo $xin_here['blog'] ? ' is-current' : ''; ?>"<?php echo $xin_here['blog'] ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Блог', 'xi-novels' ); ?></a></li>
					<?php endif; ?>
				</ul>
				<?php
			}
			?>
		</nav>

		<div class="d-flex align-items-center gap-1 ms-auto">

			<button type="button" class="btn btn-icon" data-bs-toggle="modal" data-bs-target="#xin-search" aria-label="<?php esc_attr_e( 'Поиск', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'search' ); ?>
			</button>

			<button type="button" class="btn btn-icon xin-theme-toggle" data-xin-theme aria-label="<?php esc_attr_e( 'Сменить тему', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'sun', 'xin-i-sun' ); ?>
				<?php xin_the_icon( 'moon', 'xin-i-moon' ); ?>
			</button>

			<?php xin_lang_switcher(); ?>

			<a class="btn btn-icon d-none d-sm-inline-flex" href="<?php echo esc_url( xin_library_url() ); ?>" aria-label="<?php esc_attr_e( 'Моя библиотека', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'bookmark' ); ?>
			</a>

			<?php if ( is_user_logged_in() ) : ?>
				<div class="dropdown">
					<button class="btn btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php esc_attr_e( 'Профиль', 'xi-novels' ); ?>">
						<?php echo get_avatar( get_current_user_id(), 30, '', '', array( 'style' => 'border-radius:999px' ) ); ?>
					</button>
					<ul class="dropdown-menu dropdown-menu-end">
						<li><a class="dropdown-item" href="<?php echo esc_url( xin_dashboard_url() ); ?>"><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></a></li>
						<li><a class="dropdown-item" href="<?php echo esc_url( get_author_posts_url( get_current_user_id() ) ); ?>"><?php esc_html_e( 'Мой профиль', 'xi-novels' ); ?></a></li>
						<li><a class="dropdown-item" href="<?php echo esc_url( xin_library_url() ); ?>"><?php esc_html_e( 'Моя библиотека', 'xi-novels' ); ?></a></li>
						<?php if ( xin_can_moderate() ) : ?>
							<li><a class="dropdown-item" href="<?php echo esc_url( xin_manage_url() ); ?>"><?php esc_html_e( 'Панель управления', 'xi-novels' ); ?></a></li>
						<?php endif; ?>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Выйти', 'xi-novels' ); ?></a></li>
					</ul>
				</div>
			<?php else : ?>
				<a class="btn btn-primary btn-sm d-none d-sm-inline-flex" href="<?php echo esc_url( xin_login_url( xin_current_url() ) ); ?>">
					<?php xin_the_icon( 'user' ); ?><?php esc_html_e( 'Войти', 'xi-novels' ); ?>
				</a>
			<?php endif; ?>

			<button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#xin-menu" aria-controls="xin-menu" aria-label="<?php esc_attr_e( 'Меню', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'menu' ); ?>
			</button>
		</div>
	</div>
</header>

<div class="modal fade" id="xin-search" tabindex="-1" aria-labelledby="xin-search-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title" id="xin-search-title"><?php esc_html_e( 'Что ищем — тайтл, главу или статью?', 'xi-novels' ); ?></h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Закрыть', 'xi-novels' ); ?>"></button>
			</div>
			<div class="modal-body">
				<?php get_search_form(); ?>
				<?php
				$xin_hot = get_terms( array(
					'taxonomy'   => 'genre',
					'hide_empty' => true,
					'number'     => 8,
					'orderby'    => 'count',
					'order'      => 'DESC',
				) );
				?>
				<?php if ( ! is_wp_error( $xin_hot ) && $xin_hot ) : ?>
					<p class="form-text mt-3 mb-2"><?php esc_html_e( 'Популярные жанры', 'xi-novels' ); ?></p>
					<div class="xin-genres">
						<?php foreach ( $xin_hot as $xin_term ) : ?>
							<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_term ) ); ?>"><?php echo esc_html( $xin_term->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="xin-menu" aria-labelledby="xin-menu-title">
	<div class="offcanvas-header">
		<h2 class="offcanvas-title" id="xin-menu-title"><?php bloginfo( 'name' ); ?></h2>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php esc_attr_e( 'Закрыть', 'xi-novels' ); ?>"></button>
	</div>
	<div class="offcanvas-body">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'xin-menu-list',
				'depth'          => 3,
				'walker'         => new XIN_Offcanvas_Walker(),
			) );
		} else {
			?>
			<ul class="xin-menu-list">
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Каталог', 'xi-novels' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'chapter' ) ); ?>"><?php esc_html_e( 'Обновления', 'xi-novels' ); ?></a></li>
			</ul>
			<?php
		}
		?>

		<hr>

		<ul class="xin-menu-list">
			<li><a href="<?php echo esc_url( xin_library_url() ); ?>"><?php esc_html_e( 'Моя библиотека', 'xi-novels' ); ?></a></li>
			<li><a href="<?php echo esc_url( xin_dashboard_url() ); ?>"><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?></a></li>
			<?php if ( is_user_logged_in() ) : ?>
				<li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Выйти', 'xi-novels' ); ?></a></li>
			<?php else : ?>
				<li><a href="<?php echo esc_url( xin_login_url( xin_current_url() ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a></li>
			<?php endif; ?>
		</ul>

		<div class="mt-3">
			<?php get_search_form(); ?>
		</div>
	</div>
</div>

<main class="xin-site-main" id="xin-main">
