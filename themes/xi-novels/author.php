<?php

get_header();

$xin_user  = get_queried_object();
$xin_id    = $xin_user->ID;
$xin_stats = xin_author_stats( $xin_id );
$xin_tab   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'novels';
$xin_self  = get_current_user_id() === (int) $xin_id;
$xin_url   = get_author_posts_url( $xin_id );

$xin_tabs = array(
	'novels'   => __( 'Проекты', 'xi-novels' ),
	'chapters' => __( 'Главы', 'xi-novels' ),
	'posts'    => __( 'Статьи', 'xi-novels' ),
);
?>

<header class="xin-profile">
	<div class="xin-profile__cover" aria-hidden="true"></div>

	<div class="xin-profile__inner">
		<div class="xin-profile__avatar">
			<?php echo xin_avatar( $xin_id, 108, $xin_user->display_name ); ?>
		</div>

		<div>
			<h1 class="xin-profile__name">
				<?php echo esc_html( $xin_user->display_name ); ?>
				<?php if ( $xin_stats['novels'] > 0 ) : ?>
					<span class="xin-badge xin-badge--primary"><?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'автор', 'xi-novels' ); ?></span>
				<?php endif; ?>
			</h1>

			<p class="xin-muted" style="margin:0">
				<?php
				printf(
					
					esc_html__( 'На площадке с %s', 'xi-novels' ),
					esc_html( date_i18n( 'F Y', strtotime( get_userdata( $xin_id )->user_registered ) ) )
				);
				?>
			</p>

			<?php if ( get_the_author_meta( 'description', $xin_id ) ) : ?>
				<p class="xin-profile__bio"><?php echo esc_html( get_the_author_meta( 'description', $xin_id ) ); ?></p>
			<?php endif; ?>

			<div class="xin-profile__stats">
				<div><b><?php echo esc_html( number_format_i18n( $xin_stats['novels'] ) ); ?></b><span><?php esc_html_e( 'проектов', 'xi-novels' ); ?></span></div>
				<div><b><?php echo esc_html( number_format_i18n( $xin_stats['chapters'] ) ); ?></b><span><?php esc_html_e( 'глав', 'xi-novels' ); ?></span></div>
				<div><b><?php echo esc_html( xin_num( $xin_stats['views'] ) ); ?></b><span><?php esc_html_e( 'прочтений', 'xi-novels' ); ?></span></div>
				<div><b><?php echo esc_html( number_format_i18n( $xin_stats['posts'] ) ); ?></b><span><?php esc_html_e( 'статей', 'xi-novels' ); ?></span></div>
			</div>

			<div class="xin-profile__actions">
				<?php if ( $xin_self ) : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
						<?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?>
					</a>
					<a class="btn btn-outline" href="<?php echo esc_url( xin_library_url() ); ?>">
						<?php xin_the_icon( 'bookmark' ); ?><?php esc_html_e( 'Моя библиотека', 'xi-novels' ); ?>
					</a>
					<a class="btn btn-ghost" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">
						<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Настройки профиля', 'xi-novels' ); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-outline" href="<?php echo esc_url( get_author_feed_link( $xin_id ) ); ?>">
						<?php xin_the_icon( 'rss' ); ?><?php esc_html_e( 'Следить за обновлениями', 'xi-novels' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<div class="xin-wrap xin-section">

	<div class="nav nav-pills xin-mb-2">
		<?php foreach ( $xin_tabs as $xin_key => $xin_label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $xin_key, $xin_url ) ); ?>" class="nav-link <?php echo $xin_tab === $xin_key ? 'active' : ''; ?>">
				<?php echo esc_html( $xin_label ); ?>
			</a>
		<?php endforeach; ?>
	</div>

	<?php
	if ( 'chapters' === $xin_tab ) {
		$xin_items = get_posts( array(
			'post_type'      => 'chapter',
			'author'         => $xin_id,
			'posts_per_page' => 30,
		) );

		if ( $xin_items ) {
			echo '<div class="xin-grid xin-grid--3">';
			foreach ( $xin_items as $xin_item ) {
				xin_chapter_card( $xin_item->ID );
			}
			echo '</div>';
		} else {
			echo '<p class="xin-empty-inline">' . esc_html__( 'Опубликованных глав пока нет.', 'xi-novels' ) . '</p>';
		}
	} elseif ( 'posts' === $xin_tab ) {
		$xin_items = get_posts( array(
			'post_type'      => 'post',
			'author'         => $xin_id,
			'posts_per_page' => 12,
		) );

		if ( $xin_items ) {
			echo '<div class="xin-grid xin-grid--3">';
			foreach ( $xin_items as $xin_item ) {
				xin_post_card( $xin_item->ID );
			}
			echo '</div>';
		} else {
			echo '<p class="xin-empty-inline">' . esc_html__( 'Статей пока нет.', 'xi-novels' ) . '</p>';
		}
	} else {
		$xin_items = get_posts( array(
			'post_type'      => 'novel',
			'author'         => $xin_id,
			'posts_per_page' => 24,
		) );

		if ( $xin_items ) {
			echo '<div class="xin-grid xin-grid--6">';
			foreach ( $xin_items as $xin_item ) {
				xin_novel_card( $xin_item->ID );
			}
			echo '</div>';
		} else {
			echo '<p class="xin-empty-inline">' . esc_html__( 'Проектов пока нет.', 'xi-novels' ) . '</p>';
		}
	}
	?>
</div>

<?php get_footer(); ?>
