<?php
/**
 * Template Name: Моя библиотека
 *
 * Полка читателя: на чём он остановился, что отложил в избранное и что
 * читал недавно. Всё лежит в его учётной записи, а не в браузере — полка
 * переезжает вместе с ним на телефон и обратно.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_user = get_current_user_id();
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
		<p class="xv-page-lede"><?php esc_html_e( 'Закладки, прогресс и история чтения — в вашей учётной записи, а не в браузере.', 'xin-v2' ); ?></p>
	</header>

	<?php if ( ! $xv_user ) : ?>

		<div class="xv-empty">
			<?php xv_the_icon( 'heart' ); ?>
			<p class="xv-empty-title"><?php esc_html_e( 'Полка заводится вместе с учётной записью', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Войдите — и закладки с прогрессом будут ждать вас на любом устройстве.', 'xin-v2' ); ?></p>
			<p class="xv-empty-actions">
				<a class="xv-btn primary" href="<?php echo esc_url( xv_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
				<a class="xv-btn" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'В каталог', 'xin-v2' ); ?></a>
			</p>
		</div>

	<?php else : ?>

		<?php
		$xv_history = xv_history( $xv_user );
		$xv_shelf   = xv_favourites( $xv_user );
		$xv_reading = array();

		// «Продолжить» собираем из истории: прогресс без времени не скажет,
		// что читалось последним.
		foreach ( $xv_history as $xv_row ) {
			$xv_novel = get_post( (int) $xv_row['novel'] );

			if ( ! $xv_novel || 'publish' !== $xv_novel->post_status ) {
				continue;
			}

			$xv_reading[] = array(
				'novel'   => $xv_novel,
				'chapter' => get_post( (int) $xv_row['chapter'] ),
				'at'      => (int) $xv_row['at'],
			);
		}
		?>

		<div class="xv-stat-strip">
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( xv_read_count( $xv_user ) ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_chapters( xv_read_count( $xv_user ) ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( count( $xv_shelf ) ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_titles( count( $xv_shelf ) ) ); ?></span>
			</div>
			<div class="xv-stat-cell">
				<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( xv_streak( $xv_user ) ) ); ?></span>
				<span class="xv-stat-label"><?php echo esc_html( xv_label_days( xv_streak( $xv_user ) ) ); ?></span>
			</div>
		</div>

		<?php if ( $xv_reading ) : ?>
			<section class="xv-shelf">
				<?php
				xv_section_head( array(
					'title'    => __( 'Продолжить', 'xin-v2' ),
					'sub'      => __( 'на чём вы остановились', 'xin-v2' ),
				) );
				?>

				<div class="xv-continue">
					<?php foreach ( array_slice( $xv_reading, 0, 6 ) as $xv_item ) : ?>
						<?php
						$xv_next = xv_resume_chapter( $xv_item['novel']->ID );
						$xv_go   = $xv_next['chapter'] ? get_permalink( $xv_next['chapter'] ) : get_permalink( $xv_item['novel'] );
						?>
						<a class="xv-continue-card" href="<?php echo esc_url( $xv_go ); ?>">
							<span class="xv-continue-cover">
								<?php if ( xv_cover_url( $xv_item['novel']->ID ) ) : ?>
									<img src="<?php echo esc_url( xv_cover_url( $xv_item['novel']->ID ) ); ?>" alt="" loading="lazy">
								<?php endif; ?>
							</span>
							<span class="xv-continue-body">
								<span class="xv-continue-title"><?php echo esc_html( get_the_title( $xv_item['novel'] ) ); ?></span>
								<span class="xv-continue-note"><?php echo esc_html( $xv_next['label'] ); ?></span>
								<span class="xv-continue-when"><?php echo esc_html( xv_ago( $xv_item['at'] ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="xv-shelf">
			<?php
			xv_section_head( array(
				'title'    => __( 'Избранное', 'xin-v2' ),
				'sub'      => __( 'отложено читать', 'xin-v2' ),
			) );
			?>

			<?php if ( $xv_shelf ) : ?>
				<div class="xv-catalog-grid">
					<?php foreach ( xv_posts_by_ids( $xv_shelf, 'novel' ) as $xv_novel ) : ?>
						<?php xv_novel_card( $xv_novel ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="xv-empty">
					<?php xv_the_icon( 'heart' ); ?>
					<p class="xv-empty-title"><?php esc_html_e( 'Полка пока пуста', 'xin-v2' ); ?></p>
					<p class="xv-empty-text"><?php esc_html_e( 'Нажмите «В избранное» на странице любого тайтла — он появится здесь.', 'xin-v2' ); ?></p>
					<p class="xv-empty-actions">
						<a class="xv-btn primary" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'В каталог', 'xin-v2' ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		</section>

		<?php if ( count( $xv_reading ) > 6 ) : ?>
			<section class="xv-shelf">
				<?php xv_section_head( array( 'title' => __( 'История', 'xin-v2' ) ) ); ?>

				<div class="xv-history">
					<?php foreach ( array_slice( $xv_reading, 6 ) as $xv_item ) : ?>
						<a class="xv-history-row" href="<?php echo esc_url( $xv_item['chapter'] ? get_permalink( $xv_item['chapter'] ) : get_permalink( $xv_item['novel'] ) ); ?>">
							<span class="xv-history-title"><?php echo esc_html( get_the_title( $xv_item['novel'] ) ); ?></span>
							<span class="xv-history-chapter"><?php echo esc_html( $xv_item['chapter'] ? get_the_title( $xv_item['chapter'] ) : '' ); ?></span>
							<span class="xv-history-when"><?php echo esc_html( xv_ago( $xv_item['at'] ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="xv-prose xv-page-note"><?php the_content(); ?></div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
