<?php

get_header();

$xin_user  = get_queried_object();
$xin_id    = $xin_user->ID;
$xin_stats = xin_author_stats( $xin_id );
$xin_tab   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'novels';
$xin_self  = get_current_user_id() === (int) $xin_id;
$xin_url   = get_author_posts_url( $xin_id );
$xin_cover = xin_user_cover( $xin_id );
$xin_links = xin_user_links( $xin_id );
$xin_line  = get_user_meta( $xin_id, 'xin_tagline', true );
$xin_don   = get_user_meta( $xin_id, 'xin_donate', true );

$xin_tabs = array(
	'novels'   => __( 'Проекты', 'xi-novels' ),
	'chapters' => __( 'Главы', 'xi-novels' ),
	'posts'    => __( 'Статьи', 'xi-novels' ),
);
?>

<header class="xin-profile<?php echo $xin_cover ? ' has-cover' : ''; ?>">
	<div class="xin-profile__cover" aria-hidden="true">
		<?php if ( $xin_cover ) : ?>
			<img src="<?php echo esc_url( $xin_cover ); ?>" alt="">
		<?php endif; ?>
	</div>

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

			<?php if ( $xin_line ) : ?>
				<p class="xin-profile__tagline"><?php echo esc_html( $xin_line ); ?></p>
			<?php endif; ?>

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

			<?php
			$xin_reading = xin_reading_stats( $xin_id );
			if ( $xin_reading['read'] > 0 || $xin_reading['streak'] > 0 ) :
				?>
				<p class="xin-profile__streak">
					<?php xin_the_icon( 'flame' ); ?>
					<span>
						<?php echo esc_html( xin_streak_note( $xin_id ) ); ?>
						<?php if ( $xin_reading['read'] > 0 ) : ?>
							· <?php echo esc_html( sprintf( xin_plural( $xin_reading['read'], __( '%d глава прочитана', 'xi-novels' ), __( '%d главы прочитано', 'xi-novels' ), __( '%d глав прочитано', 'xi-novels' ) ), $xin_reading['read'] ) ); ?>
						<?php endif; ?>
					</span>
				</p>
			<?php endif; ?>

			<div class="xin-profile__actions">
				<?php if ( $xin_self ) : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
						<?php xin_the_icon( 'pen' ); ?><?php esc_html_e( 'Кабинет автора', 'xi-novels' ); ?>
					</a>
					<a class="btn btn-outline" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">
						<?php xin_the_icon( 'settings' ); ?><?php esc_html_e( 'Настройки профиля', 'xi-novels' ); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-outline" href="<?php echo esc_url( get_author_feed_link( $xin_id ) ); ?>">
						<?php xin_the_icon( 'rss' ); ?><?php esc_html_e( 'Следить за обновлениями', 'xi-novels' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $xin_don ) : ?>
					<a class="btn btn-gold" href="<?php echo esc_url( $xin_don ); ?>" target="_blank" rel="noopener nofollow">
						<?php xin_the_icon( 'gift' ); ?><?php esc_html_e( 'Поддержать', 'xi-novels' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $xin_links ) : ?>
					<span class="xin-profile__links">
						<?php foreach ( $xin_links as $xin_link ) : ?>
							<a class="btn btn-icon" href="<?php echo esc_url( $xin_link['url'] ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php esc_attr_e( 'Ссылка автора', 'xi-novels' ); ?>">
								<?php xin_the_icon( $xin_link['icon'] ); ?>
							</a>
						<?php endforeach; ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<div class="xin-wrap xin-section">

	<?php
	$xin_top = get_posts( array(
		'post_type'      => 'novel',
		'author'         => $xin_id,
		'posts_per_page' => 3,
		'meta_key'       => '_xin_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );
	?>

	<?php if ( count( $xin_top ) > 1 ) : ?>
		<section class="xin-mb-2">
			<?php
			xin_section_head( array(
				'eyebrow' => __( 'визитка', 'xi-novels' ),
				'title'   => __( 'Читают чаще всего', 'xi-novels' ),
				'icon'    => 'flame',
			) );
			?>
			<div class="xin-podium">
				<?php foreach ( $xin_top as $xin_i => $xin_item ) : ?>
					<?php $xin_c = xin_cover_url( $xin_item->ID, 'xin-cover' ); ?>
					<a class="xin-podium__item" href="<?php echo esc_url( get_permalink( $xin_item->ID ) ); ?>">
						<span class="xin-podium__place"><?php echo (int) ( $xin_i + 1 ); ?></span>
						<span class="xin-podium__cover">
							<?php if ( $xin_c ) : ?>
								<img src="<?php echo esc_url( $xin_c ); ?>" alt="" loading="lazy">
							<?php endif; ?>
						</span>
						<span style="min-width:0">
							<span class="xin-podium__title"><?php echo esc_html( $xin_item->post_title ); ?></span>
							<span class="xin-novel__meta">
								<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $xin_item->ID ) ) ); ?></span>
								<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) xin_chapter_count( $xin_item->ID ); ?></span>
							</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<div class="nav nav-pills xin-mb-2">
		<?php foreach ( $xin_tabs as $xin_key => $xin_label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $xin_key, $xin_url ) ); ?>" class="nav-link <?php echo $xin_tab === $xin_key ? 'active' : ''; ?>">
				<?php echo esc_html( $xin_label ); ?>
			</a>
		<?php endforeach; ?>
	</div>

	<?php
	if ( 'chapters' === $xin_tab ) {

		$xin_items = get_posts( array( 'post_type' => 'chapter', 'author' => $xin_id, 'posts_per_page' => 30 ) );

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

		$xin_items = get_posts( array( 'post_type' => 'post', 'author' => $xin_id, 'posts_per_page' => 12 ) );

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

		$xin_items = get_posts( array( 'post_type' => 'novel', 'author' => $xin_id, 'posts_per_page' => 24 ) );

		if ( $xin_items ) {
			echo '<div class="xin-grid xin-grid--6">';
			foreach ( $xin_items as $xin_item ) {
				xin_novel_card( $xin_item->ID );
			}
			echo '</div>';
		} elseif ( $xin_self ) {
			?>
			<div class="xin-empty">
				<?php xin_the_icon( 'pen' ); ?>
				<h2><?php esc_html_e( 'Здесь появятся ваши проекты', 'xi-novels' ); ?></h2>
				<p><?php esc_html_e( 'Заведите первый тайтл — на это уходит пара минут, а страница профиля соберётся сама.', 'xi-novels' ); ?></p>
				<a class="btn btn-primary xin-mt-2" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'new-novel' ) ) ); ?>">
					<?php xin_the_icon( 'plus' ); ?><?php esc_html_e( 'Новый проект', 'xi-novels' ); ?>
				</a>
			</div>
			<?php
		} else {
			echo '<p class="xin-empty-inline">' . esc_html__( 'Проектов пока нет.', 'xi-novels' ) . '</p>';
		}
	}
	?>

	<?php
	$xin_recent = get_posts( array( 'post_type' => 'chapter', 'author' => $xin_id, 'posts_per_page' => 6 ) );
	?>
	<?php if ( 'novels' === $xin_tab && $xin_recent ) : ?>
		<section class="xin-section">
			<?php
			xin_section_head( array(
				'title'      => __( 'Последние главы автора', 'xi-novels' ),
				'icon'       => 'clock',
				'more_href'  => add_query_arg( 'tab', 'chapters', $xin_url ),
				'more_label' => __( 'Все главы', 'xi-novels' ),
			) );
			?>
			<div class="xin-grid xin-grid--3">
				<?php foreach ( $xin_recent as $xin_item ) : ?>
					<?php xin_chapter_card( $xin_item->ID ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php
$xin_awards = xin_achievements( $xin_id );
$xin_open   = array_filter( $xin_awards, static function ( $a ) { return $a['unlocked']; } );
if ( $xin_open ) :
	?>
	<div class="xin-wrap">
		<section class="xin-awards">
			<h2 class="xin-awards__title"><?php esc_html_e( 'Достижения', 'xi-novels' ); ?></h2>
			<div class="xin-awards__grid">
				<?php foreach ( $xin_awards as $xin_award ) : ?>
					<div class="xin-award<?php echo $xin_award['unlocked'] ? ' is-open' : ''; ?>" title="<?php echo esc_attr( $xin_award['note'] ); ?>">
						<?php xin_the_icon( $xin_award['icon'] ); ?>
						<b><?php echo esc_html( $xin_award['title'] ); ?></b>
						<?php if ( ! $xin_award['unlocked'] ) : ?>
							<small><?php echo esc_html( $xin_award['have'] . ' / ' . $xin_award['need'] ); ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php get_footer(); ?>
