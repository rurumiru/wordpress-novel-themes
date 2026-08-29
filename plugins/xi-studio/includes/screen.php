<?php
/**
 * Разметка экрана студии. Значения контролов подставляет studio.js.
 *
 * @package XI_Studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$xis_fields  = xin_skin_fields();
$xis_groups  = xin_skin_groups();
$xis_values  = xin_skin_values();
$xis_presets = xin_skin_presets();
$xis_pages   = xis_preview_pages();
?>

<div class="xis" data-xis>

	<header class="xis__top">
		<div class="xis__brand">
			<span class="xis__logo" aria-hidden="true"></span>
			<span>
				<b><?php esc_html_e( 'Студия темы', 'xi-studio' ); ?></b>
				<small><?php esc_html_e( 'XIN-Com — облик сайта целиком', 'xi-studio' ); ?></small>
			</span>
		</div>

		<div class="xis__top-actions">
			<span class="xis__state" data-xis-state></span>

			<button type="button" class="xis__btn" data-xis-export>
				<?php esc_html_e( 'Выгрузить', 'xi-studio' ); ?>
			</button>
			<button type="button" class="xis__btn" data-xis-import>
				<?php esc_html_e( 'Загрузить', 'xi-studio' ); ?>
			</button>
			<input type="file" accept=".json,application/json" hidden data-xis-file>

			<button type="button" class="xis__btn" data-xis-reset>
				<?php esc_html_e( 'Сбросить', 'xi-studio' ); ?>
			</button>
			<button type="button" class="xis__btn xis__btn--primary" data-xis-save>
				<?php esc_html_e( 'Сохранить', 'xi-studio' ); ?>
			</button>
		</div>
	</header>

	<div class="xis__body">

		<aside class="xis__rail">
			<nav class="xis__tabs" data-xis-tabs>
				<button type="button" class="xis__tab is-active" data-xis-tab="presets"><?php esc_html_e( 'Наборы', 'xi-studio' ); ?></button>
				<?php foreach ( $xis_groups as $xis_key => $xis_group ) : ?>
					<button type="button" class="xis__tab" data-xis-tab="<?php echo esc_attr( $xis_key ); ?>">
						<?php echo esc_html( $xis_group['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</nav>

			<div class="xis__panel is-active" data-xis-pane="presets">
				<p class="xis__hint"><?php esc_html_e( 'Готовый набор меняет сразу всё. Дальше можно править по мелочи.', 'xi-studio' ); ?></p>

				<?php foreach ( $xis_presets as $xis_pkey => $xis_preset ) : ?>
					<button type="button" class="xis__preset" data-xis-preset="<?php echo esc_attr( $xis_pkey ); ?>">
						<span class="xis__preset-dots" aria-hidden="true">
							<i style="background:<?php echo esc_attr( $xis_preset['values']['xin_primary'] ? $xis_preset['values']['xin_primary'] : '#2b303b' ); ?>"></i>
							<i style="background:hsl(<?php echo (int) $xis_preset['values']['xin_hue']; ?> <?php echo (int) $xis_preset['values']['xin_saturation'] / 4; ?>% 92%)"></i>
							<i style="background:<?php echo esc_attr( $xis_preset['values']['xin_gold'] ? $xis_preset['values']['xin_gold'] : '#5b6272' ); ?>"></i>
						</span>
						<span class="xis__preset-text">
							<b><?php echo esc_html( $xis_preset['label'] ); ?></b>
							<small><?php echo esc_html( $xis_preset['note'] ); ?></small>
						</span>
					</button>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $xis_groups as $xis_key => $xis_group ) : ?>
				<div class="xis__panel" data-xis-pane="<?php echo esc_attr( $xis_key ); ?>">
					<?php
					foreach ( $xis_fields as $xis_name => $xis_field ) :
						if ( $xis_field['group'] !== $xis_key ) {
							continue;
						}
						?>
						<div class="xis__field" data-xis-field="<?php echo esc_attr( $xis_name ); ?>">
							<label class="xis__label" for="<?php echo esc_attr( $xis_name ); ?>">
								<span><?php echo esc_html( $xis_field['label'] ); ?></span>
								<output data-xis-out="<?php echo esc_attr( $xis_name ); ?>"></output>
							</label>

							<?php if ( 'color' === $xis_field['type'] ) : ?>
								<div class="xis__color">
									<input type="color" id="<?php echo esc_attr( $xis_name ); ?>" data-xis-input="<?php echo esc_attr( $xis_name ); ?>"
										value="<?php echo esc_attr( $xis_values[ $xis_name ] ? $xis_values[ $xis_name ] : '#2b303b' ); ?>">
									<input type="text" class="xis__text" data-xis-hex="<?php echo esc_attr( $xis_name ); ?>"
										value="<?php echo esc_attr( $xis_values[ $xis_name ] ); ?>"
										placeholder="<?php esc_attr_e( 'по умолчанию', 'xi-studio' ); ?>" spellcheck="false">
									<button type="button" class="xis__clear" data-xis-clear="<?php echo esc_attr( $xis_name ); ?>" title="<?php esc_attr_e( 'Вернуть цвет темы', 'xi-studio' ); ?>">×</button>
								</div>

							<?php elseif ( 'choice' === $xis_field['type'] ) : ?>
								<div class="xis__choices">
									<?php foreach ( $xis_field['choices'] as $xis_ckey => $xis_label ) : ?>
										<button type="button" class="xis__choice" data-xis-choice="<?php echo esc_attr( $xis_name ); ?>" data-value="<?php echo esc_attr( $xis_ckey ); ?>">
											<?php echo esc_html( $xis_label ); ?>
										</button>
									<?php endforeach; ?>
								</div>

							<?php else : ?>
								<input type="range" id="<?php echo esc_attr( $xis_name ); ?>" data-xis-input="<?php echo esc_attr( $xis_name ); ?>"
									min="<?php echo (int) $xis_field['min']; ?>"
									max="<?php echo (int) $xis_field['max']; ?>"
									step="<?php echo (int) $xis_field['step']; ?>"
									value="<?php echo (int) $xis_values[ $xis_name ]; ?>">
							<?php endif; ?>

							<?php if ( ! empty( $xis_field['hint'] ) ) : ?>
								<p class="xis__hint"><?php echo esc_html( $xis_field['hint'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</aside>

		<main class="xis__stage">
			<div class="xis__stage-bar">
				<div class="xis__seg" role="group" aria-label="<?php esc_attr_e( 'Страница', 'xi-studio' ); ?>">
					<?php foreach ( $xis_pages as $xis_index => $xis_page ) : ?>
						<button type="button" class="xis__seg-btn<?php echo 0 === $xis_index ? ' is-active' : ''; ?>"
							data-xis-page="<?php echo esc_attr( $xis_page['url'] ); ?>">
							<?php echo esc_html( $xis_page['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="xis__seg" role="group" aria-label="<?php esc_attr_e( 'Ширина экрана', 'xi-studio' ); ?>">
					<button type="button" class="xis__seg-btn is-active" data-xis-width="0"><?php esc_html_e( 'Монитор', 'xi-studio' ); ?></button>
					<button type="button" class="xis__seg-btn" data-xis-width="820"><?php esc_html_e( 'Планшет', 'xi-studio' ); ?></button>
					<button type="button" class="xis__seg-btn" data-xis-width="390"><?php esc_html_e( 'Телефон', 'xi-studio' ); ?></button>
				</div>

				<div class="xis__seg" role="group" aria-label="<?php esc_attr_e( 'Схема', 'xi-studio' ); ?>">
					<button type="button" class="xis__seg-btn is-active" data-xis-scheme="light"><?php esc_html_e( 'Светлая', 'xi-studio' ); ?></button>
					<button type="button" class="xis__seg-btn" data-xis-scheme="dark"><?php esc_html_e( 'Тёмная', 'xi-studio' ); ?></button>
				</div>

				<button type="button" class="xis__btn xis__btn--ghost" data-xis-reload><?php esc_html_e( 'Обновить', 'xi-studio' ); ?></button>
			</div>

			<div class="xis__frame" data-xis-frame>
				<iframe title="<?php esc_attr_e( 'Предпросмотр сайта', 'xi-studio' ); ?>" data-xis-preview
					src="<?php echo esc_url( $xis_pages[0]['url'] ); ?>"></iframe>
			</div>
		</main>
	</div>

	<div class="xis__toast" data-xis-toast hidden></div>
</div>
