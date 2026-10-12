<?php
/**
 * Публичная страница автора.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_author = get_queried_object();
$xv_id     = $xv_author ? (int) $xv_author->ID : 0;
$xv_name   = xv_author_name( $xv_id );
$xv_novels = get_posts( array(
	'post_type'      => 'novel',
	'post_status'    => 'publish',
	'author'         => $xv_id,
	'posts_per_page' => 24,
	'no_found_rows'  => true,
) );

$xv_chapters = 0;
$xv_views    = 0;

foreach ( $xv_novels as $xv_novel ) {
	$xv_chapters += (int) get_post_meta( $xv_novel->ID, '_xin_chapter_count', true );
	$xv_views    += xv_get_views( $xv_novel->ID );
}

$xv_links = array_filter( array(
	'site'     => get_user_meta( $xv_id, 'xv_site', true ),
	'telegram' => get_user_meta( $xv_id, 'xv_telegram', true ),
	'donate'   => get_user_meta( $xv_id, 'xv_donate', true ),
) );
?>

<div class="xv-section xv-section-wide">
	<header class="xv-profile-head">
		<?php echo xv_avatar( $xv_id, 92 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="xv-profile-main">
			<h1 class="xv-profile-name"><?php echo esc_html( $xv_name ); ?></h1>

			<?php $xv_tagline = get_user_meta( $xv_id, 'xv_tagline', true ); ?>
			<?php if ( $xv_tagline ) : ?>
				<p class="xv-profile-tagline"><?php echo esc_html( $xv_tagline ); ?></p>
			<?php endif; ?>

			<p class="xv-profile-facts">
				<?php
				printf(
					/* translators: 1: тайтлы, 2: главы, 3: просмотры — каждое уже со своим словом. */
					esc_html__( '%1$s · %2$s · %3$s', 'xin-v2' ),
					esc_html( xv_counted( count( $xv_novels ), xv_label_titles( count( $xv_novels ) ) ) ),
					esc_html( xv_counted( $xv_chapters, xv_label_chapters( $xv_chapters ) ) ),
					esc_html( xv_num( $xv_views ) . ' ' . xv_label_views( $xv_views ) )
				);
				?>
			</p>

			<?php if ( $xv_links ) : ?>
				<div class="xv-profile-links">
					<?php if ( ! empty( $xv_links['site'] ) ) : ?>
						<a class="xv-btn sm" href="<?php echo esc_url( $xv_links['site'] ); ?>" rel="nofollow noopener" target="_blank"><?php xv_the_icon( 'link' ); ?><?php esc_html_e( 'Сайт', 'xin-v2' ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $xv_links['telegram'] ) ) : ?>
						<a class="xv-btn sm" href="<?php echo esc_url( 'https://t.me/' . ltrim( $xv_links['telegram'], '@' ) ); ?>" rel="nofollow noopener" target="_blank"><?php xv_the_icon( 'telegram' ); ?>Telegram</a>
					<?php endif; ?>
					<?php if ( ! empty( $xv_links['donate'] ) ) : ?>
						<a class="xv-btn sm" href="<?php echo esc_url( $xv_links['donate'] ); ?>" rel="nofollow noopener" target="_blank"><?php xv_the_icon( 'heart' ); ?><?php esc_html_e( 'Поддержать', 'xin-v2' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( get_current_user_id() === $xv_id && xv_author_url() ) : ?>
			<a class="xv-btn" href="<?php echo esc_url( xv_author_url( array( 'view' => 'profile' ) ) ); ?>">
				<?php xv_the_icon( 'settings' ); ?><?php esc_html_e( 'Настроить профиль', 'xin-v2' ); ?>
			</a>
		<?php endif; ?>
	</header>

	<?php $xv_bio = get_the_author_meta( 'description', $xv_id ); ?>
	<?php if ( $xv_bio ) : ?>
		<div class="xv-profile-bio xv-prose"><?php echo wp_kses_post( wpautop( $xv_bio ) ); ?></div>
	<?php endif; ?>

	<?php if ( $xv_novels ) : ?>
		<section class="xv-profile-works">
			<?php xv_section_head( array( 'title' => __( 'Работы', 'xin-v2' ) ) ); ?>
			<div class="xv-card-row">
				<?php foreach ( $xv_novels as $xv_novel ) : ?>
					<?php xv_novel_card( $xv_novel->ID ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php else : ?>
		<div class="xv-empty">
			<p class="xv-empty-title"><?php esc_html_e( 'Работ пока нет', 'xin-v2' ); ?></p>
			<p class="xv-empty-text"><?php esc_html_e( 'Здесь появятся тайтлы этого автора.', 'xin-v2' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
