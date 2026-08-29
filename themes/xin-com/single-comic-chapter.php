<?php
/**
 * Читалка главы комикса: /comics/<тайтл>/<глава>/.
 *
 * С текстовой читалкой у неё общего только каркас, поэтому это отдельный
 * шаблон. Кегль, шрифт, ширина колонки и бумага здесь не значат ничего, зато
 * значат ширина кадра и зазор между страницами; прогресс считается не по
 * прокрутке текста, а по номеру дочитанного кадра. Глоссарий, озвучка и
 * абзацные инструменты не подключаются вовсе — им нечего здесь делать.
 *
 * @package XIN-Com
 */

while ( have_posts() ) :
	the_post();

	$xin_id       = get_the_ID();
	$xin_novel_id = xin_chapter_novel_id( $xin_id );
	$xin_prev     = xin_adjacent_chapter( $xin_id, -1 );
	$xin_next     = xin_adjacent_chapter( $xin_id, 1 );
	$xin_locked   = (bool) get_post_meta( $xin_id, '_xin_locked', true );
	$xin_label    = xin_chapter_label( $xin_id );
	$xin_pages    = xin_comic_pages( $xin_id );
	$xin_sources  = xin_comic_page_sources( $xin_id );
	$xin_urls     = $xin_sources ? $xin_sources[0]['urls'] : array();
	$xin_dir      = $xin_novel_id ? xin_comic_direction( $xin_novel_id ) : 'strip';
	$xin_open     = xin_can_read_chapter( $xin_id );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'xin-reading xin-reading--comic' ); ?>>
<?php wp_body_open(); ?>

<div class="xin-progress" data-xin-progress></div>

<article
	class="xin-cr"
	data-xin-comic
	data-dir="<?php echo esc_attr( $xin_dir ); ?>"
	data-novel-id="<?php echo (int) $xin_novel_id; ?>"
	data-chapter-id="<?php echo (int) $xin_id; ?>"
	data-novel-title="<?php echo esc_attr( $xin_novel_id ? get_the_title( $xin_novel_id ) : '' ); ?>"
	data-chapter-title="<?php the_title_attribute(); ?>"
	data-cover="<?php echo esc_attr( $xin_novel_id ? xin_cover_url( $xin_novel_id, 'xin-cover-sm' ) : '' ); ?>"
	data-pages="<?php echo (int) count( $xin_pages ); ?>"
>

	<header class="xin-cr__bar" data-xin-cr-bar>
		<a class="btn btn-icon" href="<?php echo esc_url( $xin_novel_id ? get_permalink( $xin_novel_id ) : xin_section_home_link( 'comic' ) ); ?>" aria-label="<?php esc_attr_e( 'К странице тайтла', 'xin-com' ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?>
		</a>

		<div class="xin-cr__where">
			<span class="xin-cr__novel"><?php echo esc_html( $xin_novel_id ? get_the_title( $xin_novel_id ) : '' ); ?></span>
			<span class="xin-cr__chapter">
				<?php if ( $xin_label ) : ?>
					<b>#<?php echo esc_html( $xin_label ); ?></b>
				<?php endif; ?>
				<?php the_title(); ?>
			</span>
		</div>

		<div class="xin-cr__tools">
			<span class="xin-cr__counter" data-xin-cr-counter aria-live="polite">
				<?php echo esc_html( count( $xin_pages ) ? '1 / ' . count( $xin_pages ) : '—' ); ?>
			</span>
			<button type="button" class="btn btn-icon" data-xin-cr-toggle="settings" aria-label="<?php esc_attr_e( 'Настройки чтения', 'xin-com' ); ?>">
				<?php xin_the_icon( 'settings' ); ?>
			</button>
		</div>
	</header>

	<div class="xin-cr__panel" data-xin-cr-panel hidden>
		<div class="xin-cr__group">
			<span class="xin-cr__group-label"><?php esc_html_e( 'Ширина кадра', 'xin-com' ); ?></span>
			<div class="xin-cr__choices" role="group">
				<button type="button" data-xin-cr-width="narrow"><?php esc_html_e( 'Узко', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-width="normal"><?php esc_html_e( 'Обычно', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-width="wide"><?php esc_html_e( 'Широко', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-width="full"><?php esc_html_e( 'Во всю', 'xin-com' ); ?></button>
			</div>
		</div>

		<div class="xin-cr__group">
			<span class="xin-cr__group-label"><?php esc_html_e( 'Зазор между страницами', 'xin-com' ); ?></span>
			<div class="xin-cr__choices" role="group">
				<button type="button" data-xin-cr-gap="none"><?php esc_html_e( 'Без зазора', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-gap="small"><?php esc_html_e( 'Небольшой', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-gap="large"><?php esc_html_e( 'Крупный', 'xin-com' ); ?></button>
			</div>
		</div>

		<?php if ( count( $xin_sources ) > 1 ) : ?>
			<div class="xin-cr__group">
				<span class="xin-cr__group-label"><?php esc_html_e( 'Сервер картинок', 'xin-com' ); ?></span>
				<div class="xin-cr__choices" role="group">
					<?php foreach ( $xin_sources as $xin_source ) : ?>
						<button type="button" data-xin-cr-source="<?php echo esc_attr( $xin_source['id'] ); ?>"><?php echo esc_html( $xin_source['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
				<p class="xin-cr__hint"><?php esc_html_e( 'Если страницы грузятся долго или не открываются вовсе, переключитесь на другой сервер: кадры те же, отдаёт их другой домен.', 'xin-com' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="xin-cr__group">
			<span class="xin-cr__group-label"><?php esc_html_e( 'Режим', 'xin-com' ); ?></span>
			<div class="xin-cr__choices" role="group">
				<button type="button" data-xin-cr-mode="strip"><?php esc_html_e( 'Лентой', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-mode="ltr"><?php esc_html_e( 'Постранично', 'xin-com' ); ?></button>
				<button type="button" data-xin-cr-mode="rtl"><?php esc_html_e( 'Постранично, справа налево', 'xin-com' ); ?></button>
			</div>
			<p class="xin-cr__hint"><?php esc_html_e( 'Постранично листается стрелками и кликом по краям кадра.', 'xin-com' ); ?></p>
		</div>
	</div>

	<div class="xin-cr__stage" data-xin-cr-stage>

		<?php if ( ! $xin_open ) : ?>
			<div class="xin-cr__locked">
				<?php xin_the_icon( 'lock' ); ?>
				<h1><?php esc_html_e( 'Глава пока закрыта', 'xin-com' ); ?></h1>
				<p class="xin-muted"><?php esc_html_e( 'Ранний доступ открывается подписчикам PLUS. Так команда перевода получает поддержку раньше остальных.', 'xin-com' ); ?></p>
				<div class="xin-cr__locked-actions">
					<?php if ( is_user_logged_in() ) : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( xin_page_url( 'plus' ) ); ?>"><?php esc_html_e( 'Что даёт PLUS', 'xin-com' ); ?></a>
					<?php else : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( xin_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xin-com' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

		<?php elseif ( ! $xin_pages ) : ?>
			<div class="xin-cr__locked">
				<?php xin_the_icon( 'image' ); ?>
				<h1><?php esc_html_e( 'В главе нет страниц', 'xin-com' ); ?></h1>
				<p class="xin-muted"><?php esc_html_e( 'Загрузите страницы главы — и они появятся здесь.', 'xin-com' ); ?></p>
			</div>

		<?php else : ?>
			<?php xin_track_reading( $xin_id ); ?>

			<?php
			/*
			 * Карта «источник → адреса» уезжает в браузер целиком: переключение
			 * сервера должно менять кадры на месте, без перезагрузки страницы и
			 * без похода на сервер за теми же самыми ссылками.
			 */
			$xin_map = array();
			foreach ( $xin_sources as $xin_source ) {
				$xin_map[ $xin_source['id'] ] = $xin_source['urls'];
			}
			?>
			<script type="application/json" data-xin-cr-sources>
				<?php echo wp_json_encode( $xin_map, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>
			</script>

			<div class="xin-cr__pages" data-xin-cr-pages>
				<?php foreach ( $xin_pages as $xin_i => $xin_page_id ) : ?>
					<?php
					$xin_src = isset( $xin_urls[ $xin_i ] ) ? $xin_urls[ $xin_i ] : '';

					if ( ! $xin_src ) {
						continue;
					}

					$xin_meta = wp_get_attachment_metadata( $xin_page_id );
					$xin_w    = isset( $xin_meta['width'] ) ? (int) $xin_meta['width'] : 800;
					$xin_h    = isset( $xin_meta['height'] ) ? (int) $xin_meta['height'] : 1200;
					/*
					 * Первые два кадра — то, что читатель видит сразу: они грузятся
					 * обычным порядком и с высоким приоритетом. Остальные ленивые,
					 * иначе глава на полсотни страниц забивает канал целиком.
					 */
					$xin_eager = $xin_i < 2;
					?>
					<figure class="xin-cr__page" data-xin-cr-page="<?php echo (int) $xin_i + 1; ?>">
						<img
							src="<?php echo esc_url( $xin_src ); ?>"
							alt="<?php echo esc_attr( sprintf( /* translators: %d — номер страницы. */ __( 'Страница %d', 'xin-com' ), $xin_i + 1 ) ); ?>"
							width="<?php echo (int) $xin_w; ?>"
							height="<?php echo (int) $xin_h; ?>"
							decoding="async"
							<?php echo $xin_eager ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
						>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>

	<nav class="xin-cr__foot">
		<?php if ( $xin_prev ) : ?>
			<a class="btn btn-outline" href="<?php echo esc_url( get_permalink( $xin_prev ) ); ?>">
				<?php xin_the_icon( 'chevron-left' ); ?><?php esc_html_e( 'Предыдущая', 'xin-com' ); ?>
			</a>
		<?php else : ?>
			<span></span>
		<?php endif; ?>

		<a class="btn btn-icon" href="<?php echo esc_url( $xin_novel_id ? get_permalink( $xin_novel_id ) : xin_section_home_link( 'comic' ) ); ?>" aria-label="<?php esc_attr_e( 'Все главы', 'xin-com' ); ?>">
			<?php xin_the_icon( 'list' ); ?>
		</a>

		<?php if ( $xin_next ) : ?>
			<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_next ) ); ?>">
				<?php esc_html_e( 'Следующая', 'xin-com' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		<?php else : ?>
			<span></span>
		<?php endif; ?>
	</nav>

</article>

<?php wp_footer(); ?>
</body>
</html>
<?php
endwhile;
