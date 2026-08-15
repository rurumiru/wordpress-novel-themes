<?php

$xin_chapter  = isset( $args['chapter'] ) ? $args['chapter'] : null;
$xin_novel_id = isset( $args['novel_id'] ) ? (int) $args['novel_id'] : 0;
$xin_id       = $xin_chapter ? $xin_chapter->ID : 0;

if ( ! $xin_novel_id || ! xin_owns( $xin_novel_id ) ) {
	echo '<p class="xin-empty-inline">' . esc_html__( 'Сначала выберите проект.', 'xi-novels' ) . '</p>';
	return;
}

$xin_last   = xin_last_chapter( $xin_novel_id );
$xin_number = $xin_id ? xin_chapter_label( $xin_id ) : ( $xin_last ? (float) xin_chapter_number( $xin_last->ID ) + 1 : 1 );
?>

<form class="xin-panel" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-xin-chapter-editor="<?php echo (int) $xin_novel_id; ?>">
	<?php wp_nonce_field( 'xin_save_chapter' ); ?>
	<input type="hidden" name="action" value="xin_save_chapter">
	<input type="hidden" name="chapter_id" value="<?php echo (int) $xin_id; ?>">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xin_novel_id; ?>">

	<div class="xin-panel__head">
		<h2>
			<?php xin_the_icon( 'pen' ); ?>
			<?php echo $xin_id ? esc_html__( 'Правка главы', 'xi-novels' ) : esc_html__( 'Новая глава', 'xi-novels' ); ?>
		</h2>
		<a class="xin-head__more" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'chapters', 'project' => $xin_novel_id ) ) ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?>
		</a>
	</div>

	<div class="xin-form">
		<div class="xin-form__row xin-form__row--2">
			<div class="xin-field">
				<label for="xin-ch-title"><?php esc_html_e( 'Название главы', 'xi-novels' ); ?></label>
				<input class="form-control" type="text" id="xin-ch-title" name="title" required value="<?php echo esc_attr( $xin_chapter ? $xin_chapter->post_title : '' ); ?>">
			</div>
			<div class="xin-field">
				<label for="xin-ch-number"><?php esc_html_e( 'Номер', 'xi-novels' ); ?></label>
				<input class="form-control" type="number" step="0.1" id="xin-ch-number" name="number" value="<?php echo esc_attr( $xin_number ); ?>">
				<p class="xin-field__hint"><?php esc_html_e( 'Дробный номер — экстра или интерлюдия: 12.5', 'xi-novels' ); ?></p>
			</div>
		</div>

		<div class="xin-field xin-editor">
			<label for="xin-ch-content"><?php esc_html_e( 'Текст главы', 'xi-novels' ); ?></label>
			<?php

wp_editor(
				$xin_chapter ? $xin_chapter->post_content : '',
				'xin-ch-content',
				array(
					'textarea_name' => 'content',
					'media_buttons' => current_user_can( 'upload_files' ),
					'quicktags'     => true,
					'editor_height' => 520,
					'tinymce'       => array(
						'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,blockquote,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,hr,removeformat,undo,redo,fullscreen',
						'toolbar2' => '',
						'block_formats' => __( 'Абзац=p; Заголовок=h2; Подзаголовок=h3', 'xi-novels' ),
					),
				)
			);
			?>
			<p class="xin-field__hint">
				<span data-xin-wordcount>0</span> <?php esc_html_e( 'слов', 'xi-novels' ); ?> ·
				<span data-xin-autosave-note><?php esc_html_e( 'черновик сохраняется в браузере', 'xi-novels' ); ?></span>
			</p>
		</div>

		<div class="xin-field">
			<div class="xin-checks">
				<label class="xin-check">
					<input class="form-check-input" type="checkbox" name="locked" value="1" <?php checked( $xin_id && get_post_meta( $xin_id, '_xin_locked', true ) ); ?>>
					<?php xin_the_icon( 'lock' ); ?><?php esc_html_e( 'Ранний доступ (PLUS)', 'xi-novels' ); ?>
				</label>
			</div>
		</div>

		<div class="xin-flex xin-flex-wrap">
			<button type="submit" name="status" value="publish" class="btn btn-primary btn-lg">
				<?php xin_the_icon( 'check' ); ?>
				<?php echo current_user_can( 'publish_posts' ) ? esc_html__( 'Опубликовать', 'xi-novels' ) : esc_html__( 'Отправить на модерацию', 'xi-novels' ); ?>
			</button>
			<button type="submit" name="and_new" value="1" class="btn btn-outline btn-lg">
				<?php esc_html_e( 'Опубликовать и начать следующую', 'xi-novels' ); ?>
			</button>
			<button type="submit" name="status" value="draft" class="btn btn-ghost">
				<?php esc_html_e( 'В черновики', 'xi-novels' ); ?>
			</button>
		</div>
	</div>
</form>
