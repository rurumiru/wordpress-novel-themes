<?php
/**
 * Словарь проекта в кабинете.
 *
 * Список правил «было = стало» одной колонкой: так его удобно вставить
 * целиком из своей таблицы. Правила уезжают читателям вместе с главой,
 * а по кнопке их можно вписать прямо в текст глав.
 *
 * @package XI_Novels
 */

$xin_novel_id = isset( $args['novel_id'] ) ? (int) $args['novel_id'] : 0;

if ( ! $xin_novel_id || ! xin_owns( $xin_novel_id ) ) {
	echo '<p class="xin-empty-inline">' . esc_html__( 'Сначала выберите проект.', 'xi-novels' ) . '</p>';
	return;
}

$xin_rules    = xin_glossary_rules( $xin_novel_id );
$xin_ci       = $xin_rules ? ! empty( $xin_rules[0]['ci'] ) : true;
$xin_whole    = $xin_rules ? ! empty( $xin_rules[0]['whole'] ) : false;
$xin_chapters = xin_get_chapters( $xin_novel_id, 'ASC' );
$xin_hits     = isset( $_GET['hits'] ) ? absint( $_GET['hits'] ) : 0;
$xin_touched  = isset( $_GET['touched'] ) ? absint( $_GET['touched'] ) : 0;
?>

<form class="xin-panel" method="post" action="<?php echo esc_url( xin_dashboard_url() ); ?>">
	<?php wp_nonce_field( 'xin_glossary' ); ?>
	<input type="hidden" name="xin_action" value="save_glossary">
	<input type="hidden" name="novel_id" value="<?php echo (int) $xin_novel_id; ?>">

	<div class="xin-panel__head">
		<h2>
			<?php xin_the_icon( 'languages' ); ?>
			<?php esc_html_e( 'Словарь проекта', 'xi-novels' ); ?>
		</h2>
		<a class="xin-head__more" href="<?php echo esc_url( xin_dashboard_url( array( 'view' => 'chapters', 'project' => $xin_novel_id ) ) ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?>
		</a>
	</div>

	<div class="xin-form">
		<p class="xin-field__hint">
			<?php esc_html_e( 'Имена и термины, которые читатель увидит вместо того, что стоит в тексте. Замена идёт в браузере читателя: сам текст глав остаётся как есть, пока вы не нажмёте «Вписать в главы».', 'xi-novels' ); ?>
		</p>

		<div class="xin-field">
			<label for="xin-glossary"><?php esc_html_e( 'Правила: одно в строке, «было = стало»', 'xi-novels' ); ?></label>
			<textarea class="form-control xin-glossary__area" id="xin-glossary" name="rules" rows="14" spellcheck="false" placeholder="Ye Chen = Е Чэнь&#10;Qi = ци&#10;[TL Note] ="><?php echo esc_textarea( xin_glossary_to_lines( $xin_rules ) ); ?></textarea>
			<p class="xin-field__hint">
				<?php esc_html_e( 'Пустая правая часть убирает термин из текста. Строка, начатая с #, — комментарий.', 'xi-novels' ); ?>
			</p>
		</div>

		<div class="xin-field">
			<div class="xin-checks">
				<label class="xin-check">
					<input type="checkbox" name="ci" value="1" <?php checked( $xin_ci ); ?>>
					<?php esc_html_e( 'Любой регистр', 'xi-novels' ); ?>
				</label>
				<label class="xin-check">
					<input type="checkbox" name="whole" value="1" <?php checked( $xin_whole ); ?>>
					<?php esc_html_e( 'Слово целиком', 'xi-novels' ); ?>
				</label>
			</div>
			<p class="xin-field__hint">
				<?php esc_html_e( 'Настройки действуют на все правила проекта. «Слово целиком» бережёт склонения: «Чэн» не тронет «Чэня».', 'xi-novels' ); ?>
			</p>
		</div>

		<div class="xin-flex xin-flex-wrap">
			<button type="submit" class="btn btn-primary btn-lg">
				<?php xin_the_icon( 'check' ); ?><?php esc_html_e( 'Сохранить словарь', 'xi-novels' ); ?>
			</button>
			<span class="xin-w__note">
				<?php
				printf(
					/* translators: 1: rules, 2: chapters */
					esc_html__( 'Правил: %1$s · глав в проекте: %2$s', 'xi-novels' ),
					esc_html( xin_num( count( $xin_rules ) ) ),
					esc_html( xin_num( count( $xin_chapters ) ) )
				);
				?>
			</span>
		</div>
	</div>
</form>

<?php if ( $xin_rules ) : ?>
	<div class="xin-panel xin-mt-2">
		<div class="xin-panel__head">
			<h2><?php xin_the_icon( 'wand' ); ?><?php esc_html_e( 'Вписать словарь в главы', 'xi-novels' ); ?></h2>
		</div>

		<div class="xin-form">
			<p class="xin-field__hint">
				<?php esc_html_e( 'Это меняет сам текст глав в базе — как будто вы прошли по ним поиском и заменой. Сначала посмотрите, сколько совпадений найдётся.', 'xi-novels' ); ?>
			</p>

			<?php if ( $xin_hits || $xin_touched ) : ?>
				<p class="xin-notice xin-notice--ok" style="margin:0">
					<?php xin_the_icon( 'check' ); ?>
					<span>
						<?php
						printf(
							/* translators: 1: matches, 2: chapters */
							esc_html__( 'Совпадений: %1$s в %2$s главах.', 'xi-novels' ),
							esc_html( xin_num( $xin_hits ) ),
							esc_html( xin_num( $xin_touched ) )
						);
						?>
					</span>
				</p>
			<?php endif; ?>

			<div class="xin-flex xin-flex-wrap">
				<form method="post" action="<?php echo esc_url( xin_dashboard_url() ); ?>">
					<?php wp_nonce_field( 'xin_glossary' ); ?>
					<input type="hidden" name="xin_action" value="apply_glossary">
					<input type="hidden" name="novel_id" value="<?php echo (int) $xin_novel_id; ?>">
					<input type="hidden" name="dry" value="1">
					<button type="submit" class="btn btn-outline">
						<?php xin_the_icon( 'search' ); ?><?php esc_html_e( 'Посчитать совпадения', 'xi-novels' ); ?>
					</button>
				</form>

				<form method="post" action="<?php echo esc_url( xin_dashboard_url() ); ?>" class="xin-flex xin-flex-wrap">
					<?php wp_nonce_field( 'xin_glossary' ); ?>
					<input type="hidden" name="xin_action" value="apply_glossary">
					<input type="hidden" name="novel_id" value="<?php echo (int) $xin_novel_id; ?>">
					<label class="xin-check">
						<input type="checkbox" name="sure" value="1" required>
						<?php esc_html_e( 'Понимаю: текст глав изменится', 'xi-novels' ); ?>
					</label>
					<button type="submit" class="btn btn-gold">
						<?php xin_the_icon( 'wand' ); ?><?php esc_html_e( 'Вписать в главы', 'xi-novels' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>
