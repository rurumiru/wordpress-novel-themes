<?php
/**
 * Редактор текста: панель, поле и подвал со статистикой.
 *
 * Аргументы:
 *   name     — имя поля формы;
 *   value    — исходная разметка;
 *   key      — ключ черновика в браузере (пусто — без автосохранения);
 *   label    — подпись над полем;
 *   lite     — короткая панель (для описаний);
 *   glossary — показывать кнопку «словарь проекта».
 *
 * @package XI_Novels
 */

$xin_name     = isset( $args['name'] ) ? $args['name'] : 'content';
$xin_value    = isset( $args['value'] ) ? $args['value'] : '';
$xin_key      = isset( $args['key'] ) ? $args['key'] : '';
$xin_label    = isset( $args['label'] ) ? $args['label'] : __( 'Текст главы', 'xi-novels' );
$xin_lite     = ! empty( $args['lite'] );
$xin_gloss    = ! empty( $args['glossary'] );
$xin_can_file = current_user_can( 'upload_files' );
?>

<div class="xin-field">
	<label class="xin-label"><?php echo esc_html( $xin_label ); ?></label>

	<div class="xin-w<?php echo $xin_lite ? ' xin-w--lite' : ''; ?>" data-xin-writer data-key="<?php echo esc_attr( $xin_key ); ?>">

		<div class="xin-w__restore" data-xin-w-restore hidden>
			<span data-xin-w-restore-note></span>
			<span class="xin-w__restore-actions">
				<button type="button" class="btn btn-primary btn-sm" data-xin-w-restore-yes><?php esc_html_e( 'Восстановить', 'xi-novels' ); ?></button>
				<button type="button" class="btn btn-ghost btn-sm" data-xin-w-restore-no><?php esc_html_e( 'Убрать', 'xi-novels' ); ?></button>
			</span>
		</div>

		<div class="xin-w__bar">
			<select class="xin-w__block" data-xin-w-block aria-label="<?php esc_attr_e( 'Тип абзаца', 'xi-novels' ); ?>">
				<option value="p"><?php esc_html_e( 'Абзац', 'xi-novels' ); ?></option>
				<option value="h2"><?php esc_html_e( 'Заголовок', 'xi-novels' ); ?></option>
				<option value="h3"><?php esc_html_e( 'Подзаголовок', 'xi-novels' ); ?></option>
				<option value="blockquote"><?php esc_html_e( 'Цитата', 'xi-novels' ); ?></option>
			</select>

			<span class="xin-w__sep" aria-hidden="true"></span>

			<button type="button" class="xin-w__btn" data-xin-w-cmd="bold" title="<?php esc_attr_e( 'Жирный', 'xi-novels' ); ?> (Ctrl+B)" aria-label="<?php esc_attr_e( 'Жирный', 'xi-novels' ); ?>"><?php xin_the_icon( 'bold' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="italic" title="<?php esc_attr_e( 'Курсив', 'xi-novels' ); ?> (Ctrl+I)" aria-label="<?php esc_attr_e( 'Курсив', 'xi-novels' ); ?>"><?php xin_the_icon( 'italic' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="strikeThrough" title="<?php esc_attr_e( 'Зачёркнутый', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Зачёркнутый', 'xi-novels' ); ?>"><?php xin_the_icon( 'strike' ); ?></button>

			<span class="xin-w__sep" aria-hidden="true"></span>

			<button type="button" class="xin-w__btn" data-xin-w-block-btn="blockquote" title="<?php esc_attr_e( 'Цитата', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Цитата', 'xi-novels' ); ?>"><?php xin_the_icon( 'quote' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="insertUnorderedList" title="<?php esc_attr_e( 'Список', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Список', 'xi-novels' ); ?>"><?php xin_the_icon( 'list' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="insertOrderedList" title="<?php esc_attr_e( 'Нумерованный список', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Нумерованный список', 'xi-novels' ); ?>"><?php xin_the_icon( 'list-ordered' ); ?></button>

			<?php if ( ! $xin_lite ) : ?>
				<button type="button" class="xin-w__btn" data-xin-w-break title="<?php esc_attr_e( 'Разрыв сцены', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Разрыв сцены', 'xi-novels' ); ?>"><?php xin_the_icon( 'scissors' ); ?></button>
			<?php endif; ?>

			<button type="button" class="xin-w__btn" data-xin-w-link title="<?php esc_attr_e( 'Ссылка', 'xi-novels' ); ?> (Ctrl+K)" aria-label="<?php esc_attr_e( 'Ссылка', 'xi-novels' ); ?>"><?php xin_the_icon( 'link' ); ?></button>

			<?php if ( $xin_can_file ) : ?>
				<button type="button" class="xin-w__btn" data-xin-w-media title="<?php esc_attr_e( 'Картинка', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Картинка', 'xi-novels' ); ?>"><?php xin_the_icon( 'image' ); ?></button>
			<?php endif; ?>

			<span class="xin-w__sep" aria-hidden="true"></span>

			<button type="button" class="xin-w__btn" data-xin-w-cmd="removeFormat" title="<?php esc_attr_e( 'Убрать оформление', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Убрать оформление', 'xi-novels' ); ?>"><?php xin_the_icon( 'eraser' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="undo" title="<?php esc_attr_e( 'Отменить', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Отменить', 'xi-novels' ); ?>"><?php xin_the_icon( 'undo' ); ?></button>
			<button type="button" class="xin-w__btn" data-xin-w-cmd="redo" title="<?php esc_attr_e( 'Вернуть', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Вернуть', 'xi-novels' ); ?>"><?php xin_the_icon( 'redo' ); ?></button>

			<span class="xin-w__grow"></span>

			<?php if ( ! $xin_lite ) : ?>
				<button type="button" class="xin-w__btn" data-xin-w-findbtn title="<?php esc_attr_e( 'Найти и заменить', 'xi-novels' ); ?> (Ctrl+H)" aria-label="<?php esc_attr_e( 'Найти и заменить', 'xi-novels' ); ?>"><?php xin_the_icon( 'search' ); ?></button>
				<button type="button" class="xin-w__btn" data-xin-w-tidy title="<?php esc_attr_e( 'Причесать текст: кавычки, тире, лишние пробелы', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Причесать текст', 'xi-novels' ); ?>"><?php xin_the_icon( 'wand' ); ?></button>
				<?php if ( $xin_gloss ) : ?>
					<button type="button" class="xin-w__btn" data-xin-w-glossary title="<?php esc_attr_e( 'Применить словарь проекта', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Применить словарь проекта', 'xi-novels' ); ?>"><?php xin_the_icon( 'languages' ); ?></button>
				<?php endif; ?>
			<?php endif; ?>

			<button type="button" class="xin-w__btn" data-xin-w-sourcebtn title="<?php esc_attr_e( 'Показать HTML', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Показать HTML', 'xi-novels' ); ?>"><?php xin_the_icon( 'code' ); ?></button>

			<?php if ( ! $xin_lite ) : ?>
				<button type="button" class="xin-w__btn" data-xin-w-focus title="<?php esc_attr_e( 'Режим фокуса', 'xi-novels' ); ?> (Esc)" aria-label="<?php esc_attr_e( 'Режим фокуса', 'xi-novels' ); ?>"><?php xin_the_icon( 'expand' ); ?></button>
			<?php endif; ?>
		</div>

		<div class="xin-w__pop" data-xin-w-linkbar hidden>
			<input type="text" class="xin-w__input" data-xin-w-linkfield placeholder="https://" aria-label="<?php esc_attr_e( 'Адрес ссылки', 'xi-novels' ); ?>">
			<button type="button" class="btn btn-primary btn-sm" data-xin-w-linkok><?php esc_html_e( 'Готово', 'xi-novels' ); ?></button>
			<button type="button" class="btn btn-ghost btn-sm" data-xin-w-unlink><?php xin_the_icon( 'unlink' ); ?><?php esc_html_e( 'Убрать ссылку', 'xi-novels' ); ?></button>
		</div>

		<?php if ( ! $xin_lite ) : ?>
			<div class="xin-w__pop" data-xin-w-find hidden>
				<input type="text" class="xin-w__input" data-xin-w-findfield placeholder="<?php esc_attr_e( 'найти', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Что заменить', 'xi-novels' ); ?>">
				<input type="text" class="xin-w__input" data-xin-w-replacefield placeholder="<?php esc_attr_e( 'заменить на', 'xi-novels' ); ?>" aria-label="<?php esc_attr_e( 'Чем заменить', 'xi-novels' ); ?>">
				<label class="xin-check"><input type="checkbox" data-xin-w-findci checked><?php esc_html_e( 'Любой регистр', 'xi-novels' ); ?></label>
				<label class="xin-check"><input type="checkbox" data-xin-w-findwhole><?php esc_html_e( 'Слово целиком', 'xi-novels' ); ?></label>
				<button type="button" class="btn btn-primary btn-sm" data-xin-w-replacebtn><?php esc_html_e( 'Заменить всё', 'xi-novels' ); ?></button>
				<span class="xin-w__note" data-xin-w-findnote></span>
			</div>
		<?php endif; ?>

		<div class="xin-w__body" data-xin-w-body contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true" aria-label="<?php echo esc_attr( $xin_label ); ?>"><?php echo wp_kses_post( $xin_value ); ?></div>

		<textarea class="xin-w__source" data-xin-w-source hidden spellcheck="false" aria-label="<?php esc_attr_e( 'Показать HTML', 'xi-novels' ); ?>"></textarea>

		<div class="xin-w__foot">
			<span class="xin-w__stats" data-xin-w-stats></span>
			<span class="xin-w__note" data-xin-w-note><?php echo $xin_key ? esc_html__( 'черновик сохраняется в браузере', 'xi-novels' ) : ''; ?></span>
		</div>

		<textarea name="<?php echo esc_attr( $xin_name ); ?>" data-xin-w-input hidden></textarea>
	</div>
</div>
