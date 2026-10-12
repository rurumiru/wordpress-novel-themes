<?php
/**
 * Словарь проекта в кабинете автора.
 *
 * Одно поле, строка на термин. Таблица с кнопками «добавить строку» здесь
 * была бы хуже: переводчик приносит словарь списком и вставляет его целиком.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_novel_id = isset( $args['novel'] ) ? (int) $args['novel'] : 0;
$xv_novel    = $xv_novel_id ? get_post( $xv_novel_id ) : null;

if ( ! $xv_novel || ! current_user_can( 'edit_post', $xv_novel_id ) ) {
	return;
}

$xv_rules = xv_glossary( $xv_novel_id );
?>

<form class="xv-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="xv_save_glossary">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xv_novel_id; ?>">
	<?php wp_nonce_field( 'xv_save_glossary' ); ?>

	<div class="xv-form-grid">
		<div class="xv-form-main">
			<h2 class="xv-form-title">
				<?php esc_html_e( 'Словарь проекта', 'xin-v2' ); ?>
				<span class="xv-form-sub"><?php echo esc_html( $xv_novel->post_title ); ?></span>
			</h2>

			<div class="xv-field">
				<label class="xv-label" for="xv-glossary"><?php esc_html_e( 'Термины', 'xin-v2' ); ?></label>
				<textarea class="xv-textarea xv-editor" id="xv-glossary" name="glossary" rows="20" spellcheck="false"><?php echo esc_textarea( xv_glossary_to_lines( $xv_rules ) ); ?></textarea>
				<p class="xv-help">
					<?php esc_html_e( 'Строка на термин: «термин = пояснение». Строка, начатая решёткой, пропускается.', 'xin-v2' ); ?>
				</p>
			</div>
		</div>

		<aside class="xv-form-side">
			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Как это выглядит', 'xin-v2' ); ?></h3>
				<p class="xv-help">
					<?php esc_html_e( 'В тексте главы термин подчёркивается пунктиром, а пояснение всплывает по наведению. Один термин подсвечивается один раз на абзац — иначе страница превращается в сплошное подчёркивание.', 'xin-v2' ); ?>
				</p>

				<div class="xv-form-actions">
					<button type="submit" class="xv-btn primary block"><?php esc_html_e( 'Сохранить словарь', 'xin-v2' ); ?></button>
					<a class="xv-btn block" href="<?php echo esc_url( xv_author_url( array( 'work' => $xv_novel_id ) ) ); ?>"><?php esc_html_e( 'К списку глав', 'xin-v2' ); ?></a>
				</div>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Сейчас в словаре', 'xin-v2' ); ?></h3>
				<p class="xv-stat-value"><?php echo esc_html( number_format_i18n( count( $xv_rules ) ) ); ?></p>
				<p class="xv-help">
					<?php
					printf(
						/* translators: %d: предел числа терминов. */
						esc_html__( 'Не больше %d терминов на тайтл — дальше подсказки начинают мешать чтению.', 'xin-v2' ),
						(int) XV_GLOSSARY_MAX
					);
					?>
				</p>
			</div>
		</aside>
	</div>
</form>
