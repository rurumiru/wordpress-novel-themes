<?php
/**
 * Форма главы в кабинете автора.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_novel_id = isset( $args['novel'] ) ? (int) $args['novel'] : 0;
$xv_id       = isset( $args['chapter'] ) ? (int) $args['chapter'] : 0;
$xv_post     = $xv_id ? get_post( $xv_id ) : null;
$xv_novel    = $xv_novel_id ? get_post( $xv_novel_id ) : null;

if ( $xv_post && ! current_user_can( 'edit_post', $xv_id ) ) {
	$xv_post = null;
	$xv_id   = 0;
}

if ( ! $xv_novel ) {
	?>
	<p class="xv-notice is-error"><?php esc_html_e( 'Сначала выберите тайтл.', 'xin-v2' ); ?></p>
	<?php
	return;
}

$xv_locked = $xv_id ? get_post_meta( $xv_id, '_xin_locked', true ) : '';
$xv_unlock = $xv_id ? (int) get_post_meta( $xv_id, '_xin_unlock_at', true ) : 0;
?>

<form class="xv-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="xv_save_chapter">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xv_novel_id; ?>">
	<input type="hidden" name="chapter_id" value="<?php echo (int) $xv_id; ?>">
	<?php wp_nonce_field( 'xv_save_chapter' ); ?>

	<div class="xv-form-grid">
		<div class="xv-form-main">
			<h2 class="xv-form-title">
				<?php echo esc_html( $xv_post ? __( 'Правка главы', 'xin-v2' ) : __( 'Новая глава', 'xin-v2' ) ); ?>
				<span class="xv-form-sub"><?php echo esc_html( $xv_novel->post_title ); ?></span>
			</h2>

			<div class="xv-field-row">
				<div class="xv-field xv-field-narrow">
					<label class="xv-label" for="xv-number"><?php esc_html_e( 'Номер', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-number" name="number" value="<?php echo esc_attr( $xv_id ? xv_chapter_number( $xv_id ) : '' ); ?>" placeholder="<?php echo esc_attr( count( xv_author_chapters( $xv_novel_id ) ) + 1 ); ?>">
				</div>
				<div class="xv-field">
					<label class="xv-label" for="xv-chapter-title"><?php esc_html_e( 'Название', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-chapter-title" name="title" required value="<?php echo esc_attr( $xv_post ? $xv_post->post_title : '' ); ?>">
				</div>
			</div>

			<div class="xv-field">
				<label class="xv-label" for="xv-content"><?php esc_html_e( 'Текст главы', 'xin-v2' ); ?></label>
				<div class="xv-editor-toolbar"><button type="button" data-xv-editor-tab="edit" aria-pressed="true"><?php esc_html_e( 'Редактор', 'xin-v2' ); ?></button><button type="button" data-xv-editor-tab="preview" aria-pressed="false"><?php esc_html_e( 'Предпросмотр', 'xin-v2' ); ?></button><span><?php esc_html_e( 'Текст → глава', 'xin-v2' ); ?></span></div>
				<textarea class="xv-textarea xv-editor" id="xv-content" name="content" rows="26" data-xv-count><?php echo esc_textarea( $xv_post ? $xv_post->post_content : '' ); ?></textarea>
				<div class="xv-editor-preview" data-xv-editor-preview hidden></div>
				<p class="xv-help">
					<span data-xv-count-out></span>
					<?php esc_html_e( 'Пустая строка разделяет абзацы.', 'xin-v2' ); ?>
				</p>
			</div>
		</div>

		<aside class="xv-form-side">
			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Публикация', 'xin-v2' ); ?></h3>

				<div class="xv-field">
					<label class="xv-label" for="xv-chapter-status"><?php esc_html_e( 'Состояние', 'xin-v2' ); ?></label>
					<select class="xv-select" id="xv-chapter-status" name="status" data-xv-status>
						<option value="draft" <?php selected( $xv_post ? $xv_post->post_status : 'draft', 'draft' ); ?>><?php esc_html_e( 'Черновик', 'xin-v2' ); ?></option>
						<option value="publish" <?php selected( $xv_post ? $xv_post->post_status : '', 'publish' ); ?>><?php esc_html_e( 'Опубликована', 'xin-v2' ); ?></option>
						<option value="future" <?php selected( $xv_post ? $xv_post->post_status : '', 'future' ); ?>><?php esc_html_e( 'По расписанию', 'xin-v2' ); ?></option>
					</select>
				</div>

				<div class="xv-field" data-xv-when <?php echo ( $xv_post && 'future' === $xv_post->post_status ) ? '' : 'hidden'; ?>>
					<label class="xv-label" for="xv-when"><?php esc_html_e( 'Выйдет', 'xin-v2' ); ?></label>
					<input class="xv-input" type="datetime-local" id="xv-when" name="publish_at" value="<?php echo esc_attr( $xv_post ? get_the_time( 'Y-m-d\TH:i', $xv_post ) : '' ); ?>">
					<p class="xv-help"><?php esc_html_e( 'В этот момент глава выйдет сама — заходить на сайт не нужно.', 'xin-v2' ); ?></p>
				</div>

				<div class="xv-form-actions">
					<button type="submit" class="xv-btn primary block"><?php esc_html_e( 'Сохранить', 'xin-v2' ); ?></button>
					<a class="xv-btn block" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel_id ) ) ); ?>"><?php esc_html_e( 'К списку глав', 'xin-v2' ); ?></a>
				</div>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Ранний доступ', 'xin-v2' ); ?></h3>

				<label class="xv-filter-item">
					<input class="xv-checkbox" type="checkbox" name="locked" value="1" <?php checked( $xv_locked, '1' ); ?> data-xv-lock>
					<span><?php esc_html_e( 'Закрыть главу', 'xin-v2' ); ?></span>
				</label>

				<div class="xv-lock-fields" <?php echo $xv_locked ? '' : 'hidden'; ?> data-xv-lock-fields>
					<div class="xv-field">
						<label class="xv-label" for="xv-unlock"><?php esc_html_e( 'Открыть для всех', 'xin-v2' ); ?></label>
						<input class="xv-input" type="datetime-local" id="xv-unlock" name="unlock_at" value="<?php echo esc_attr( $xv_unlock ? wp_date( 'Y-m-d\TH:i', $xv_unlock ) : '' ); ?>">
					</div>
					<div class="xv-field">
						<label class="xv-label" for="xv-price"><?php esc_html_e( 'Цена', 'xin-v2' ); ?></label>
						<input class="xv-input" type="number" min="0" id="xv-price" name="price" value="<?php echo esc_attr( $xv_id ? xv_chapter_price( $xv_id ) : 0 ); ?>">
					</div>
				</div>

				<p class="xv-help"><?php esc_html_e( 'Пока дата не наступила, глава видна только вам и редакторам.', 'xin-v2' ); ?></p>
			</div>
		</aside>
	</div>
</form>
