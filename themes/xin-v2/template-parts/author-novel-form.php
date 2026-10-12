<?php
/**
 * Форма тайтла в кабинете автора.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xv_id    = isset( $args['novel'] ) ? (int) $args['novel'] : 0;
$xv_post  = $xv_id ? get_post( $xv_id ) : null;
$xv_terms = $xv_id ? wp_get_object_terms( $xv_id, 'genre', array( 'fields' => 'ids' ) ) : array();
$xv_tags  = $xv_id ? wp_get_object_terms( $xv_id, 'novel_tag', array( 'fields' => 'names' ) ) : array();
$xv_stat  = $xv_id ? xv_novel_status( $xv_id ) : null;
?>

<form class="xv-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="xv_save_novel">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xv_id; ?>">
	<?php wp_nonce_field( 'xv_save_novel' ); ?>

	<div class="xv-form-grid">
		<div class="xv-form-main">
			<h2 class="xv-form-title"><?php echo esc_html( $xv_post ? __( 'Настройки тайтла', 'xin-v2' ) : __( 'Новый тайтл', 'xin-v2' ) ); ?></h2>

			<div class="xv-field">
				<label class="xv-label" for="xv-title"><?php esc_html_e( 'Название', 'xin-v2' ); ?></label>
				<input class="xv-input" type="text" id="xv-title" name="title" required value="<?php echo esc_attr( $xv_post ? $xv_post->post_title : '' ); ?>">
			</div>

			<div class="xv-field">
				<label class="xv-label" for="xv-description"><?php esc_html_e( 'Описание', 'xin-v2' ); ?></label>
				<textarea class="xv-textarea" id="xv-description" name="description" rows="9"><?php echo esc_textarea( $xv_post ? $xv_post->post_content : '' ); ?></textarea>
				<p class="xv-help"><?php esc_html_e( 'Первые две-три строки попадают в карточку каталога.', 'xin-v2' ); ?></p>
			</div>

			<div class="xv-field-row">
				<div class="xv-field">
					<label class="xv-label" for="xv-author-name"><?php esc_html_e( 'Автор оригинала', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-author-name" name="author_name" value="<?php echo esc_attr( $xv_id ? get_post_meta( $xv_id, '_xin_author_name', true ) : '' ); ?>">
				</div>
				<div class="xv-field">
					<label class="xv-label" for="xv-translator"><?php esc_html_e( 'Перевод', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-translator" name="translator" value="<?php echo esc_attr( $xv_id ? get_post_meta( $xv_id, '_xin_translator', true ) : '' ); ?>">
				</div>
			</div>

			<div class="xv-field-row">
				<div class="xv-field">
					<label class="xv-label" for="xv-year"><?php esc_html_e( 'Год выхода', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-year" name="year" inputmode="numeric" value="<?php echo esc_attr( $xv_id ? get_post_meta( $xv_id, '_xin_year', true ) : '' ); ?>">
				</div>
				<div class="xv-field">
					<label class="xv-label" for="xv-country"><?php esc_html_e( 'Страна', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-country" name="country" value="<?php echo esc_attr( $xv_id ? get_post_meta( $xv_id, '_xin_country', true ) : '' ); ?>">
				</div>
			</div>

			<div class="xv-field">
				<label class="xv-label" for="xv-tags"><?php esc_html_e( 'Теги', 'xin-v2' ); ?></label>
				<input class="xv-input" type="text" id="xv-tags" name="tags" value="<?php echo esc_attr( implode( ', ', (array) $xv_tags ) ); ?>" placeholder="<?php esc_attr_e( 'через запятую', 'xin-v2' ); ?>">
			</div>
		</div>

		<aside class="xv-form-side">
			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Публикация', 'xin-v2' ); ?></h3>

				<div class="xv-field">
					<label class="xv-label" for="xv-status"><?php esc_html_e( 'Состояние', 'xin-v2' ); ?></label>
					<select class="xv-select" id="xv-status" name="status" data-xv-status>
						<option value="draft" <?php selected( $xv_post ? $xv_post->post_status : 'draft', 'draft' ); ?>><?php esc_html_e( 'Черновик', 'xin-v2' ); ?></option>
						<option value="publish" <?php selected( $xv_post ? $xv_post->post_status : '', 'publish' ); ?>><?php esc_html_e( 'Опубликован', 'xin-v2' ); ?></option>
						<option value="future" <?php selected( $xv_post ? $xv_post->post_status : '', 'future' ); ?>><?php esc_html_e( 'По расписанию', 'xin-v2' ); ?></option>
					</select>
				</div>

				<div class="xv-field" data-xv-when <?php echo ( $xv_post && 'future' === $xv_post->post_status ) ? '' : 'hidden'; ?>>
					<label class="xv-label" for="xv-when"><?php esc_html_e( 'Выйдет', 'xin-v2' ); ?></label>
					<input class="xv-input" type="datetime-local" id="xv-when" name="publish_at" value="<?php echo esc_attr( $xv_post ? get_the_time( 'Y-m-d\TH:i', $xv_post ) : '' ); ?>">
				</div>

				<div class="xv-field">
					<label class="xv-label" for="xv-novel-status"><?php esc_html_e( 'Статус выхода', 'xin-v2' ); ?></label>
					<select class="xv-select" id="xv-novel-status" name="novel_status">
						<option value="0"><?php esc_html_e( '— не указан —', 'xin-v2' ); ?></option>
						<?php
						$xv_statuses = get_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );

						if ( $xv_statuses && ! is_wp_error( $xv_statuses ) ) :
							foreach ( $xv_statuses as $xv_term ) :
								?>
								<option value="<?php echo (int) $xv_term->term_id; ?>" <?php selected( $xv_stat ? $xv_stat->term_id : 0, $xv_term->term_id ); ?>><?php echo esc_html( $xv_term->name ); ?></option>
								<?php
							endforeach;
						endif;
						?>
					</select>
				</div>

				<div class="xv-form-actions">
					<button type="submit" class="xv-btn primary block"><?php esc_html_e( 'Сохранить', 'xin-v2' ); ?></button>
					<?php if ( $xv_post ) : ?>
						<a class="xv-btn block" href="<?php echo esc_url( get_permalink( $xv_post ) ); ?>"><?php esc_html_e( 'Смотреть страницу', 'xin-v2' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Команда проекта', 'xin-v2' ); ?></h3>

				<div class="xv-field">
					<label class="xv-label" for="xv-team"><?php esc_html_e( 'Кто ещё ведёт тайтл', 'xin-v2' ); ?></label>
					<input class="xv-input" type="text" id="xv-team" name="team" value="<?php echo esc_attr( $xv_id ? xv_team_names( $xv_id ) : '' ); ?>" placeholder="<?php esc_attr_e( 'логины через запятую', 'xin-v2' ); ?>">
					<p class="xv-help"><?php esc_html_e( 'Соавторы правят тайтл и его главы и читают ранний доступ. Логин, ник-адрес или почта — через запятую.', 'xin-v2' ); ?></p>
				</div>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Обложка', 'xin-v2' ); ?></h3>
				<?php if ( $xv_id && xv_cover_url( $xv_id ) ) : ?>
					<img class="xv-form-cover" src="<?php echo esc_url( xv_cover_url( $xv_id ) ); ?>" alt="">
				<?php endif; ?>
				<input class="xv-input" type="file" name="cover" accept="image/*">
				<p class="xv-help"><?php esc_html_e( 'Вертикальная, 2:3 — например 600×900.', 'xin-v2' ); ?></p>
			</div>

			<div class="xv-form-card">
				<h3 class="xv-aside-title"><?php esc_html_e( 'Жанры', 'xin-v2' ); ?></h3>
				<div class="xv-filter-list">
					<?php
					$xv_genres = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => false ) );

					if ( $xv_genres && ! is_wp_error( $xv_genres ) ) :
						foreach ( $xv_genres as $xv_term ) :
							?>
							<label class="xv-filter-item">
								<input class="xv-checkbox" type="checkbox" name="genres[]" value="<?php echo (int) $xv_term->term_id; ?>" <?php checked( in_array( $xv_term->term_id, (array) $xv_terms, true ) ); ?>>
								<span><?php echo esc_html( $xv_term->name ); ?></span>
							</label>
							<?php
						endforeach;
					else :
						?>
						<p class="xv-help"><?php esc_html_e( 'Жанров пока нет — их заводит администратор.', 'xin-v2' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</aside>
	</div>
</form>
