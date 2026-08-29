<?php

$xin_novel = isset( $args['novel'] ) ? $args['novel'] : null;
$xin_id    = $xin_novel ? $xin_novel->ID : 0;

$xin_genres     = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => false ) );
$xin_statuses   = get_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );
$xin_has_genres = $xin_id ? wp_get_object_terms( $xin_id, 'genre', array( 'fields' => 'ids' ) ) : array();
$xin_cur_status = $xin_id ? wp_get_object_terms( $xin_id, 'novel_status', array( 'fields' => 'slugs' ) ) : array();
$xin_cur_tags   = $xin_id ? wp_get_object_terms( $xin_id, 'novel_tag', array( 'fields' => 'names' ) ) : array();
$xin_cover      = $xin_id ? xin_cover_url( $xin_id, 'xin-cover-sm' ) : '';
?>

<form class="xin-panel" method="post" enctype="multipart/form-data" action="<?php echo esc_url( xin_dashboard_url() ); ?>">
	<?php wp_nonce_field( 'xin_save_novel' ); ?>
	<input type="hidden" name="xin_action" value="save_novel">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xin_id; ?>">

	<div class="xin-panel__head">
		<h2>
			<?php xin_the_icon( 'book' ); ?>
			<?php echo $xin_id ? esc_html__( 'Правка проекта', 'xin-com' ) : esc_html__( 'Новый проект', 'xin-com' ); ?>
		</h2>
		<a class="xin-head__more" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?><?php esc_html_e( 'К списку', 'xin-com' ); ?>
		</a>
	</div>

	<div class="xin-form">

		<div class="xin-field">
			<label for="xin-title"><?php esc_html_e( 'Название', 'xin-com' ); ?></label>
			<input class="form-control" type="text" id="xin-title" name="title" required value="<?php echo esc_attr( $xin_novel ? $xin_novel->post_title : '' ); ?>" placeholder="<?php esc_attr_e( 'Например: Печать девятого неба', 'xin-com' ); ?>">
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-author-name"><?php esc_html_e( 'Автор оригинала', 'xin-com' ); ?></label>
				<input class="form-control" type="text" id="xin-author-name" name="author_name" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_author_name', true ) : '' ); ?>">
			</div>
			<div class="xin-field">
				<label for="xin-original"><?php esc_html_e( 'Оригинальное название', 'xin-com' ); ?></label>
				<input class="form-control" type="text" id="xin-original" name="original_title" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_original_title', true ) : '' ); ?>">
			</div>
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-translator"><?php esc_html_e( 'Перевод / команда', 'xin-com' ); ?></label>
				<input class="form-control" type="text" id="xin-translator" name="translator" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_translator', true ) : '' ); ?>">
			</div>

			<div class="xin-field">
				<label for="xin-team"><?php esc_html_e( 'Соавторы и переводчики', 'xin-com' ); ?></label>
				<input class="form-control" type="text" id="xin-team" name="team" value="<?php echo esc_attr( $xin_id ? xin_team_names( $xin_id ) : '' ); ?>">
				<p class="xin-field__hint"><?php esc_html_e( 'Логины через запятую. Каждый сможет добавлять и править главы этого проекта.', 'xin-com' ); ?></p>
			</div>
			<div class="xin-field">
				<label for="xin-year"><?php esc_html_e( 'Год выпуска', 'xin-com' ); ?></label>
				<input class="form-control" type="number" id="xin-year" name="year" min="1900" max="2200" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_year', true ) : '' ); ?>">
			</div>
		</div>

		<div class="xin-field">
			<label for="xin-synopsis"><?php esc_html_e( 'Краткое описание', 'xin-com' ); ?></label>
			<textarea class="form-control" id="xin-synopsis" name="synopsis" placeholder="<?php esc_attr_e( 'Одна-две фразы для карточки в каталоге', 'xin-com' ); ?>"><?php echo esc_textarea( $xin_novel ? $xin_novel->post_excerpt : '' ); ?></textarea>
			<p class="xin-field__hint"><?php esc_html_e( 'Показывается в витрине, поиске и на карточке тайтла.', 'xin-com' ); ?></p>
		</div>

		<?php
		get_template_part( 'template-parts/writer', null, array(
			'name'  => 'description',
			'value' => $xin_novel ? $xin_novel->post_content : '',
			'key'   => '',
			'label' => __( 'Полное описание', 'xin-com' ),
			'lite'  => true,
		) );
		?>

		<div class="xin-field">
			<label><?php esc_html_e( 'Жанры', 'xin-com' ); ?></label>
			<div class="xin-checks">
				<?php foreach ( $xin_genres as $xin_genre ) : ?>
					<label class="xin-check">
						<input type="checkbox" name="genres[]" value="<?php echo (int) $xin_genre->term_id; ?>" <?php checked( in_array( $xin_genre->term_id, (array) $xin_has_genres, true ) ); ?>>
						<?php echo esc_html( $xin_genre->name ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-status-term"><?php esc_html_e( 'Статус выпуска', 'xin-com' ); ?></label>
				<select class="form-select" id="xin-status-term" name="status_term">
					<?php foreach ( $xin_statuses as $xin_term ) : ?>
						<option value="<?php echo esc_attr( $xin_term->slug ); ?>" <?php selected( in_array( $xin_term->slug, (array) $xin_cur_status, true ) ); ?>><?php echo esc_html( $xin_term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="xin-field">
				<label for="xin-tags"><?php esc_html_e( 'Теги через запятую', 'xin-com' ); ?></label>
				<input class="form-control" type="text" id="xin-tags" name="tags" value="<?php echo esc_attr( implode( ', ', (array) $xin_cur_tags ) ); ?>" placeholder="<?php esc_attr_e( 'система, культивация, перерождение', 'xin-com' ); ?>">
			</div>
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-cover"><?php esc_html_e( 'Обложка (2:3)', 'xin-com' ); ?></label>
				<input class="form-control" type="file" id="xin-cover" name="cover" accept="image/*">
				<?php if ( $xin_cover ) : ?>
					<p class="xin-field__hint"><?php esc_html_e( 'Сейчас загружена:', 'xin-com' ); ?></p>
					<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" style="width:76px;border-radius:8px;margin-top:6px">
				<?php endif; ?>
			</div>
			<div class="xin-field">
				<label for="xin-artwork"><?php esc_html_e( 'Широкий арт для витрины', 'xin-com' ); ?></label>
				<input class="form-control" type="file" id="xin-artwork" name="artwork" accept="image/*">
				<p class="xin-field__hint"><?php esc_html_e( 'Используется в баннере главной и крупных блоках.', 'xin-com' ); ?></p>
			</div>
		</div>

		<div class="xin-field">
			<div class="xin-checks">
				<label class="xin-check">
					<input class="form-check-input" type="checkbox" name="adult" value="1" <?php checked( $xin_id && get_post_meta( $xin_id, '_xin_adult', true ) ); ?>>
					<?php esc_html_e( 'Материал 18+', 'xin-com' ); ?>
				</label>
			</div>
		</div>

		<?php
		/**
		 * Дополнительные настройки проекта.
		 *
		 * Сюда плагин очереди вешает расписание выхода: автору незачем ходить за
		 * ним в админку, если весь проект он ведёт отсюда. Без плагина не
		 * выводится ничего.
		 *
		 * @param int $xin_id Проект (0 у нового).
		 */
		do_action( 'xin_novel_form_extra', (int) $xin_id );
		?>

		<div class="xin-flex xin-flex-wrap">
			<button type="submit" name="status" value="publish" class="btn btn-primary btn-lg">
				<?php xin_the_icon( 'check' ); ?>
				<?php echo current_user_can( 'publish_posts' ) ? esc_html__( 'Сохранить и опубликовать', 'xin-com' ) : esc_html__( 'Отправить на модерацию', 'xin-com' ); ?>
			</button>
			<button type="submit" name="status" value="draft" class="btn btn-outline btn-lg">
				<?php esc_html_e( 'В черновики', 'xin-com' ); ?>
			</button>
			<?php if ( $xin_id ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( wp_nonce_url( xin_dashboard_url( array( 'xin_action' => 'delete', 'id' => $xin_id ) ), 'xin_delete' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Удалить проект вместе со страницей?', 'xin-com' ) ); ?>')">
					<?php esc_html_e( 'Удалить', 'xin-com' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</form>
