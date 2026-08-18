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
			<?php echo $xin_id ? esc_html__( 'Правка проекта', 'xi-novels' ) : esc_html__( 'Новый проект', 'xi-novels' ); ?>
		</h2>
		<a class="xin-head__more" href="<?php echo esc_url( xin_dashboard_url() ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?><?php esc_html_e( 'К списку', 'xi-novels' ); ?>
		</a>
	</div>

	<div class="xin-form">

		<div class="xin-field">
			<label for="xin-title"><?php esc_html_e( 'Название', 'xi-novels' ); ?></label>
			<input class="form-control" type="text" id="xin-title" name="title" required value="<?php echo esc_attr( $xin_novel ? $xin_novel->post_title : '' ); ?>" placeholder="<?php esc_attr_e( 'Например: Печать девятого неба', 'xi-novels' ); ?>">
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-author-name"><?php esc_html_e( 'Автор оригинала', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-author-name" name="author_name" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_author_name', true ) : '' ); ?>">
			</div>
			<div class="xin-field">
				<label for="xin-original"><?php esc_html_e( 'Оригинальное название', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-original" name="original_title" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_original_title', true ) : '' ); ?>">
			</div>
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-translator"><?php esc_html_e( 'Перевод / команда', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-translator" name="translator" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_translator', true ) : '' ); ?>">
			</div>

			<div class="xin-field">
				<label for="xin-team"><?php esc_html_e( 'Соавторы и переводчики', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-team" name="team" value="<?php echo esc_attr( $xin_id ? xin_team_names( $xin_id ) : '' ); ?>">
				<p class="xin-field__hint"><?php esc_html_e( 'Логины через запятую. Каждый сможет добавлять и править главы этого проекта.', 'xi-novels' ); ?></p>
			</div>
			<div class="xin-field">
				<label for="xin-year"><?php esc_html_e( 'Год выпуска', 'xi-novels' ); ?></label>
				<input class="form-control" type="number" id="xin-year" name="year" min="1900" max="2200" value="<?php echo esc_attr( $xin_id ? get_post_meta( $xin_id, '_xin_year', true ) : '' ); ?>">
			</div>
		</div>

		<div class="xin-field">
			<label for="xin-synopsis"><?php esc_html_e( 'Краткое описание', 'xi-novels' ); ?></label>
			<textarea class="form-control" id="xin-synopsis" name="synopsis" placeholder="<?php esc_attr_e( 'Одна-две фразы для карточки в каталоге', 'xi-novels' ); ?>"><?php echo esc_textarea( $xin_novel ? $xin_novel->post_excerpt : '' ); ?></textarea>
			<p class="xin-field__hint"><?php esc_html_e( 'Показывается в витрине, поиске и на карточке тайтла.', 'xi-novels' ); ?></p>
		</div>

		<div class="xin-field xin-editor">
			<label for="xin-description"><?php esc_html_e( 'Полное описание', 'xi-novels' ); ?></label>
			<?php
			wp_editor(
				$xin_novel ? $xin_novel->post_content : '',
				'xin-description',
				array(
					'textarea_name' => 'description',
					'media_buttons' => current_user_can( 'upload_files' ),
					'quicktags'     => true,
					'editor_height' => 300,
					'tinymce'       => array(
						'toolbar1' => 'bold,italic,blockquote,bullist,numlist,link,unlink,removeformat,undo,redo',
						'toolbar2' => '',
					),
				)
			);
			?>
			<p class="xin-field__hint"><?php esc_html_e( 'Показывается на странице тайтла под заголовком «Описание».', 'xi-novels' ); ?></p>
		</div>

		<div class="xin-field">
			<label><?php esc_html_e( 'Жанры', 'xi-novels' ); ?></label>
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
				<label for="xin-status-term"><?php esc_html_e( 'Статус выпуска', 'xi-novels' ); ?></label>
				<select class="form-select" id="xin-status-term" name="status_term">
					<?php foreach ( $xin_statuses as $xin_term ) : ?>
						<option value="<?php echo esc_attr( $xin_term->slug ); ?>" <?php selected( in_array( $xin_term->slug, (array) $xin_cur_status, true ) ); ?>><?php echo esc_html( $xin_term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="xin-field">
				<label for="xin-tags"><?php esc_html_e( 'Теги через запятую', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-tags" name="tags" value="<?php echo esc_attr( implode( ', ', (array) $xin_cur_tags ) ); ?>" placeholder="<?php esc_attr_e( 'система, культивация, перерождение', 'xi-novels' ); ?>">
			</div>
		</div>

		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-cover"><?php esc_html_e( 'Обложка (2:3)', 'xi-novels' ); ?></label>
				<input class="form-control" type="file" id="xin-cover" name="cover" accept="image/*">
				<?php if ( $xin_cover ) : ?>
					<p class="xin-field__hint"><?php esc_html_e( 'Сейчас загружена:', 'xi-novels' ); ?></p>
					<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" style="width:76px;border-radius:8px;margin-top:6px">
				<?php endif; ?>
			</div>
			<div class="xin-field">
				<label for="xin-artwork"><?php esc_html_e( 'Широкий арт для витрины', 'xi-novels' ); ?></label>
				<input class="form-control" type="file" id="xin-artwork" name="artwork" accept="image/*">
				<p class="xin-field__hint"><?php esc_html_e( 'Используется в баннере главной и крупных блоках.', 'xi-novels' ); ?></p>
			</div>
		</div>

		<div class="xin-field">
			<div class="xin-checks">
				<label class="xin-check">
					<input class="form-check-input" type="checkbox" name="adult" value="1" <?php checked( $xin_id && get_post_meta( $xin_id, '_xin_adult', true ) ); ?>>
					<?php esc_html_e( 'Материал 18+', 'xi-novels' ); ?>
				</label>
			</div>
		</div>

		<div class="xin-flex xin-flex-wrap">
			<button type="submit" name="status" value="publish" class="btn btn-primary btn-lg">
				<?php xin_the_icon( 'check' ); ?>
				<?php echo current_user_can( 'publish_posts' ) ? esc_html__( 'Сохранить и опубликовать', 'xi-novels' ) : esc_html__( 'Отправить на модерацию', 'xi-novels' ); ?>
			</button>
			<button type="submit" name="status" value="draft" class="btn btn-outline btn-lg">
				<?php esc_html_e( 'В черновики', 'xi-novels' ); ?>
			</button>
			<?php if ( $xin_id ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( wp_nonce_url( xin_dashboard_url( array( 'xin_action' => 'delete', 'id' => $xin_id ) ), 'xin_delete' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Удалить проект вместе со страницей?', 'xi-novels' ) ); ?>')">
					<?php esc_html_e( 'Удалить', 'xi-novels' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</form>
