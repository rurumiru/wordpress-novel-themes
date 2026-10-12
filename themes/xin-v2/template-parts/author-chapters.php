<?php
/**
 * Список глав тайтла в кабинете автора.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_novel_id = isset( $args['novel'] ) ? (int) $args['novel'] : 0;
$xv_novel    = $xv_novel_id ? get_post( $xv_novel_id ) : null;

if ( ! $xv_novel ) {
	?>
	<p class="xv-notice is-error"><?php esc_html_e( 'Тайтл не найден.', 'xin-v2' ); ?></p>
	<?php
	return;
}

$xv_chapters = xv_author_chapters( $xv_novel_id );
?>

<div class="xv-work-head">
	<span class="xv-work-cover">
		<?php if ( xv_cover_url( $xv_novel_id ) ) : ?>
			<img src="<?php echo esc_url( xv_cover_url( $xv_novel_id ) ); ?>" alt="">
		<?php endif; ?>
	</span>
	<div>
		<h2 class="xv-work-head-title"><?php echo esc_html( $xv_novel->post_title ); ?></h2>
		<p class="xv-work-head-meta">
			<?php
			printf(
				/* translators: 1: главы, 2: просмотры — каждое уже со своим словом. */
				esc_html__( '%1$s · %2$s', 'xin-v2' ),
				esc_html( xv_counted( count( $xv_chapters ), xv_label_chapters( count( $xv_chapters ) ) ) ),
				esc_html( xv_counted( xv_get_views( $xv_novel_id ), xv_label_views( xv_get_views( $xv_novel_id ) ) ) )
			);
			?>
		</p>
	</div>
	<a class="xv-btn" href="<?php echo esc_url( get_permalink( $xv_novel ) ); ?>"><?php esc_html_e( 'Смотреть страницу', 'xin-v2' ); ?></a>
</div>

<?php if ( $xv_chapters ) : ?>
	<div class="xv-toc">
		<div class="xv-toc-legend">
			<span><?php esc_html_e( '№', 'xin-v2' ); ?></span>
			<span><?php esc_html_e( 'Название', 'xin-v2' ); ?></span>
			<span><?php esc_html_e( 'Действия', 'xin-v2' ); ?></span>
		</div>

		<?php foreach ( $xv_chapters as $xv_i => $xv_chapter ) : ?>
			<?php
			$xv_state  = xv_chapter_state( $xv_chapter );
			$xv_status = get_post_status_object( $xv_chapter->post_status );
			?>
			<div class="xv-toc-row is-static">
				<span class="xv-toc-num"><?php echo esc_html( xv_chapter_number( $xv_chapter->ID ) ?: (string) ( $xv_i + 1 ) ); ?></span>
				<span class="xv-toc-title">
					<a href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel_id, 'part' => $xv_chapter->ID, 'view' => 'chapter' ) ) ); ?>"><?php echo esc_html( $xv_chapter->post_title ); ?></a>
					<?php if ( 'publish' !== $xv_chapter->post_status ) : ?>
						<span class="xv-toc-badge"><?php echo esc_html( $xv_status ? $xv_status->label : $xv_chapter->post_status ); ?></span>
					<?php endif; ?>
					<?php if ( $xv_state['locked'] ) : ?>
						<span class="xv-toc-badge"><?php xv_the_icon( 'lock' ); ?><?php esc_html_e( 'Ранний доступ', 'xin-v2' ); ?></span>
					<?php endif; ?>
				</span>
				<span class="xv-toc-meta">
					<span class="xv-toc-date"><?php echo esc_html( $xv_state['date'] ); ?></span>
					<a class="xv-btn sm" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel_id, 'part' => $xv_chapter->ID, 'view' => 'chapter' ) ) ); ?>"><?php esc_html_e( 'Правка', 'xin-v2' ); ?></a>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Убрать главу в корзину?', 'xin-v2' ) ); ?>');">
						<input type="hidden" name="action" value="xv_delete">
						<input type="hidden" name="id" value="<?php echo (int) $xv_chapter->ID; ?>">
						<?php wp_nonce_field( 'xv_delete' ); ?>
						<button type="submit" class="xv-icon-btn" aria-label="<?php esc_attr_e( 'Удалить', 'xin-v2' ); ?>"><?php xv_the_icon( 'close' ); ?></button>
					</form>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="xv-empty">
		<p class="xv-empty-title"><?php esc_html_e( 'Глав ещё нет', 'xin-v2' ); ?></p>
		<p class="xv-empty-text"><?php esc_html_e( 'Добавьте первую — она сразу появится в ленте обновлений.', 'xin-v2' ); ?></p>
		<a class="xv-btn primary lg" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel_id, 'view' => 'chapter' ) ) ); ?>"><?php esc_html_e( 'Новая глава', 'xin-v2' ); ?></a>
	</div>
<?php endif; ?>
