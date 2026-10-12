<?php
/**
 * Template Name: Уголок читателя
 *
 * Площадка изнутри: что обновилось, о чём спорят, кто читает. Для вошедшего
 * сверху добавляется своё — серия дней, отметки и новое в избранном.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_me    = get_current_user_id();
$xv_pulse = xv_hub_pulse();
?>

<div class="xv-section xv-section-wide">
	<header class="xv-page-head">
		<?php xv_breadcrumbs( array( array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ), array( 'label' => get_the_title() ) ) ); ?>
		<h1 class="xv-page-title"><?php the_title(); ?></h1>
		<p class="xv-page-lede"><?php esc_html_e( 'Что на площадке происходит прямо сейчас.', 'xin-v2' ); ?></p>
	</header>

	<div class="xv-stat-strip">
		<div class="xv-stat-cell">
			<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_pulse['novels'] ) ); ?></span>
			<span class="xv-stat-label"><?php echo esc_html( xv_label_titles( $xv_pulse['novels'] ) ); ?></span>
		</div>
		<div class="xv-stat-cell">
			<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_pulse['chapters'] ) ); ?></span>
			<span class="xv-stat-label"><?php echo esc_html( xv_label_chapters( $xv_pulse['chapters'] ) ); ?></span>
		</div>
		<div class="xv-stat-cell">
			<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_pulse['week'] ) ); ?></span>
			<span class="xv-stat-label"><?php esc_html_e( 'за неделю', 'xin-v2' ); ?></span>
		</div>
		<div class="xv-stat-cell">
			<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( $xv_pulse['readers'] ) ); ?></span>
			<span class="xv-stat-label"><?php echo esc_html( xv_label_readers( $xv_pulse['readers'] ) ); ?></span>
		</div>
	</div>

	<?php if ( $xv_me ) : ?>
		<section class="xv-hub-mine">
			<?php xv_section_head( array( 'title' => __( 'Вы', 'xin-v2' ), 'sub'      => __( 'ваш счёт', 'xin-v2' ) ) ); ?>

			<div class="xv-hub-me">
				<div class="xv-hub-me-figures">
					<div>
						<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( xv_read_count( $xv_me ) ) ); ?></span>
						<span class="xv-stat-label"><?php echo esc_html( xv_label_chapters( xv_read_count( $xv_me ) ) ); ?></span>
					</div>
					<div>
						<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( xv_streak( $xv_me ) ) ); ?></span>
						<span class="xv-stat-label"><?php echo esc_html( xv_label_days( xv_streak( $xv_me ) ) ); ?></span>
					</div>
					<div>
						<span class="xv-stat-value"><?php echo esc_html( number_format_i18n( xv_streak_best( $xv_me ) ) ); ?></span>
						<span class="xv-stat-label"><?php esc_html_e( 'лучшая серия', 'xin-v2' ); ?></span>
					</div>
				</div>

				<ul class="xv-badges">
					<?php foreach ( xv_badges( $xv_me ) as $xv_badge ) : ?>
						<li class="xv-badge <?php echo $xv_badge['on'] ? 'is-on' : ''; ?>">
							<?php xv_the_icon( $xv_badge['icon'] ); ?>
							<span class="xv-badge-label"><?php echo esc_html( $xv_badge['label'] ); ?></span>
							<?php if ( ! $xv_badge['on'] ) : ?>
								<span class="xv-badge-left"><?php echo esc_html( $xv_badge['have'] . ' / ' . $xv_badge['need'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php $xv_shelf_new = xv_hub_shelf_updates( $xv_me ); ?>

			<?php if ( $xv_shelf_new ) : ?>
				<div class="xv-hub-shelf">
					<h3 class="xv-aside-title"><?php esc_html_e( 'Новое в избранном', 'xin-v2' ); ?></h3>
					<div class="xv-updates">
						<?php foreach ( $xv_shelf_new as $xv_chapter ) : ?>
							<?php xv_update_row( $xv_chapter ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</section>
	<?php else : ?>
		<div class="xv-hub-invite">
			<p><?php esc_html_e( 'Войдите — и здесь появится ваша серия чтения, отметки и новое в избранном.', 'xin-v2' ); ?></p>
			<a class="xv-btn primary" href="<?php echo esc_url( xv_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-v2' ); ?></a>
		</div>
	<?php endif; ?>

	<section class="xv-hub-block">
		<?php
		xv_section_head( array(
			'title'    => __( 'Обновилось', 'xin-v2' ),
			'sub'      => __( 'за последние две недели', 'xin-v2' ),
			'more'     => get_post_type_archive_link( 'chapter' ),
			'more_text' => __( 'Все обновления', 'xin-v2' ),
		) );
		?>

		<div class="xv-catalog-grid">
			<?php foreach ( xv_posts_by_ids( xv_hub_trending( 6 ), 'novel' ) as $xv_novel ) : ?>
				<?php xv_novel_card( $xv_novel ); ?>
			<?php endforeach; ?>
		</div>
	</section>

	<div class="xv-hub-cols">
		<section class="xv-hub-block">
			<?php xv_section_head( array( 'title' => __( 'О чём говорят', 'xin-v2' ) ) ); ?>

			<?php $xv_talk = xv_hub_talk( 6 ); ?>

			<?php if ( $xv_talk ) : ?>
				<div class="xv-talk">
					<?php foreach ( $xv_talk as $xv_row ) : ?>
						<?php $xv_comment = get_comment( $xv_row['id'] ); ?>
						<?php if ( ! $xv_comment ) { continue; } ?>
						<a class="xv-talk-row" href="<?php echo esc_url( get_comment_link( $xv_comment ) ); ?>">
							<span class="xv-talk-text"><?php echo esc_html( wp_trim_words( $xv_comment->comment_content, 18, '…' ) ); ?></span>
							<span class="xv-talk-meta">
								<span class="xv-talk-who"><?php echo esc_html( $xv_comment->comment_author ); ?></span>
								<span class="xv-talk-where"><?php echo esc_html( get_the_title( $xv_comment->comment_post_ID ) ); ?></span>
								<?php if ( $xv_row['likes'] ) : ?>
									<span class="xv-talk-likes"><?php xv_the_icon( 'heart' ); ?><?php echo (int) $xv_row['likes']; ?></span>
								<?php endif; ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="xv-empty-text"><?php esc_html_e( 'Обсуждений пока нет.', 'xin-v2' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="xv-hub-block">
			<?php xv_section_head( array( 'title' => __( 'Читают больше всех', 'xin-v2' ) ) ); ?>

			<?php $xv_readers = xv_hub_readers( 8 ); ?>

			<?php if ( $xv_readers ) : ?>
				<ol class="xv-board">
					<?php foreach ( $xv_readers as $xv_i => $xv_reader ) : ?>
						<li class="xv-board-row <?php echo (int) $xv_reader['id'] === $xv_me ? 'is-me' : ''; ?>">
							<span class="xv-board-num"><?php echo (int) ( $xv_i + 1 ); ?></span>
							<?php echo xv_avatar( $xv_reader['id'], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="xv-board-name"><?php echo esc_html( $xv_reader['name'] ); ?></span>
							<span class="xv-board-count"><?php echo esc_html( xv_counted( $xv_reader['read'], xv_label_chapters( $xv_reader['read'] ) ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="xv-empty-text"><?php esc_html_e( 'Счёт откроет первый, кто дочитает главу.', 'xin-v2' ); ?></p>
			<?php endif; ?>
		</section>
	</div>

	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="xv-prose xv-page-note"><?php the_content(); ?></div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
