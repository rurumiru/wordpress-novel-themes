<?php

while ( have_posts() ) :
	the_post();

	$xin_id       = get_the_ID();
	$xin_novel_id = xin_chapter_novel_id( $xin_id );
	$xin_prev     = xin_adjacent_chapter( $xin_id, -1 );
	$xin_next     = xin_adjacent_chapter( $xin_id, 1 );
	$xin_locked   = (bool) get_post_meta( $xin_id, '_xin_locked', true );
	$xin_label    = xin_chapter_label( $xin_id );
	$xin_all      = $xin_novel_id ? xin_get_chapters( $xin_novel_id, 'ASC' ) : array();
	$xin_words    = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$xin_gloss    = $xin_novel_id ? xin_glossary_rules( $xin_novel_id ) : array();
	$xin_minutes  = max( 1, (int) round( $xin_words / 180 ) );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'xin-reading' ); ?>>
<?php wp_body_open(); ?>

<div class="xin-progress" data-xin-progress></div>

<article
	class="xin-rd"
	data-xin-reader
	data-paper="default"
	data-novel-id="<?php echo (int) $xin_novel_id; ?>"
	data-chapter-id="<?php echo (int) $xin_id; ?>"
	data-novel-title="<?php echo esc_attr( $xin_novel_id ? get_the_title( $xin_novel_id ) : '' ); ?>"
	data-chapter-title="<?php the_title_attribute(); ?>"
	data-cover="<?php echo esc_attr( $xin_novel_id ? xin_cover_url( $xin_novel_id, 'xin-cover-sm' ) : '' ); ?>"
>

	<div class="xin-rd__hotzone" data-xin-rd-hotzone aria-hidden="true"></div>

	<header class="xin-rd__bar" data-xin-rd-bar>
		<a class="btn btn-icon" href="<?php echo esc_url( $xin_novel_id ? get_permalink( $xin_novel_id ) : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'К странице тайтла', 'xi-novels' ); ?>">
			<?php xin_the_icon( 'chevron-left' ); ?>
		</a>

		<button type="button" class="btn btn-icon" data-xin-rd-toc aria-label="<?php esc_attr_e( 'Оглавление', 'xi-novels' ); ?>">
			<?php xin_the_icon( 'list' ); ?>
		</button>

		<div class="xin-rd__bar-title" data-xin-gl-scope>
			<b><?php the_title(); ?></b>
			<?php if ( $xin_novel_id ) : ?>
				<small><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?></small>
			<?php endif; ?>
		</div>

		<div class="xin-rd__actions">
			<button type="button" class="btn btn-icon" data-xin-jump-bm hidden aria-label="<?php esc_attr_e( 'Jump to bookmark', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'bookmark', '', true ); ?>
			</button>
			<button type="button" class="btn btn-icon" data-xin-gl-open aria-label="<?php esc_attr_e( 'Глоссарий', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'languages' ); ?>
			</button>
			<button type="button" class="btn btn-icon xin-theme-toggle" data-xin-theme aria-label="<?php esc_attr_e( 'Сменить тему', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'sun', 'xin-i-sun' ); ?><?php xin_the_icon( 'moon', 'xin-i-moon' ); ?>
			</button>
			<button type="button" class="btn btn-icon" data-xin-rd-settings aria-label="<?php esc_attr_e( 'Настройки чтения', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'type' ); ?>
			</button>
			<button type="button" class="btn btn-icon" data-xin-rd-full aria-label="<?php esc_attr_e( 'Во весь экран', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'compass' ); ?>
			</button>
		</div>
	</header>

	<main class="xin-rd__main">
		<div class="xin-rd__inner">
			<?php if ( $xin_novel_id ) : ?>
				<a class="xin-rd__eyebrow" data-xin-gl-scope href="<?php echo esc_url( get_permalink( $xin_novel_id ) ); ?>">
					<?php echo esc_html( get_the_title( $xin_novel_id ) ); ?>
				</a>
			<?php endif; ?>

			<h1 class="xin-rd__title" data-xin-gl-scope>
				<?php if ( $xin_label ) : ?>
					<span class="xin-muted"><?php printf( esc_html__( 'Глава %s.', 'xi-novels' ), esc_html( $xin_label ) ); ?></span>
				<?php endif; ?>
				<?php the_title(); ?>
			</h1>

			<div class="xin-rd__meta">
				<span><?php xin_the_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
				<span><?php xin_the_icon( 'clock' ); ?><?php printf( esc_html__( '~%d мин чтения', 'xi-novels' ), (int) $xin_minutes ); ?></span>
				<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?></span>
				<span><?php xin_the_icon( 'user' ); ?><?php the_author_posts_link(); ?></span>
			</div>

			<div class="xin-rd__rule" aria-hidden="true"></div>

			<?php if ( ! xin_can_read_chapter( $xin_id ) ) : ?>
				<?php
				$xin_price = xin_chapter_price( $xin_id );
				$xin_buy   = xin_chapter_buy_url( $xin_id );
				?>
				<div class="xin-locked">
					<?php xin_the_icon( 'lock' ); ?>
					<h2><?php esc_html_e( 'Глава раннего доступа', 'xi-novels' ); ?></h2>

					<?php if ( $xin_buy ) : ?>
						<p class="xin-muted"><?php esc_html_e( 'Её можно открыть подпиской PLUS или разовой покупкой — деньги идут команде проекта.', 'xi-novels' ); ?></p>
						<div class="xin-locked__actions">
							<a class="btn btn-primary btn-sm" href="<?php echo esc_url( $xin_buy ); ?>">
								<?php
								echo $xin_price
									? esc_html( sprintf( __( 'Купить главу — %s', 'xi-novels' ), $xin_price ) )
									: esc_html__( 'Купить главу', 'xi-novels' );
								?>
							</a>
							<a class="btn btn-outline btn-sm" href="<?php echo esc_url( xin_page_url( 'plus' ) ); ?>"><?php esc_html_e( 'Что даёт PLUS', 'xi-novels' ); ?></a>
						</div>
					<?php elseif ( is_user_logged_in() ) : ?>
						<p class="xin-muted"><?php esc_html_e( 'Ранний доступ открывается подписчикам PLUS. Так переводчик получает поддержку раньше остальных.', 'xi-novels' ); ?></p>
						<div class="xin-locked__actions">
							<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_page_url( 'plus' ) ); ?>"><?php esc_html_e( 'Что даёт PLUS', 'xi-novels' ); ?></a>
						</div>
					<?php else : ?>
						<p class="xin-muted"><?php esc_html_e( 'Войдите в аккаунт с подпиской PLUS, чтобы продолжить чтение.', 'xi-novels' ); ?></p>
						<div class="xin-locked__actions">
							<a class="btn btn-primary btn-sm" href="<?php echo esc_url( xin_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Войти', 'xi-novels' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<?php xin_track_reading( $xin_id ); ?>
				<div class="xin-rd__text" data-xin-rd-text data-xin-gl-scope lang="<?php echo esc_attr( xin_current_lang() ); ?>">
					<?php the_content(); ?>
				</div>
				<div id="xin-paragraph-tools" class="xin-pkit" hidden data-xin-ptools data-nosnippet>
					<div class="xin-pkit__rule" aria-hidden="true"></div>
					<div class="xin-pkit__top">
						<span class="xin-pkit__kicker"><?php esc_html_e( 'This paragraph', 'xi-novels' ); ?></span>
						<button type="button" class="xin-pkit__x" data-xin-ptool="close" aria-label="<?php esc_attr_e( 'Close', 'xi-novels' ); ?>">
							<?php xin_the_icon( 'close' ); ?>
						</button>
					</div>
					<div class="xin-pkit__grid">
						<section class="xin-pkit__col">
							<h3><?php esc_html_e( 'Keep', 'xi-novels' ); ?></h3>
							<button type="button" class="xin-pkit__act" data-xin-ptool="bookmark">
								<?php xin_the_icon( 'bookmark' ); ?>
								<span><?php esc_html_e( 'Pin', 'xi-novels' ); ?></span>
							</button>
							<div class="xin-pkit__inks" data-xin-bm-colors>
								<button type="button" data-xin-bm-color="default" title="<?php esc_attr_e( 'Ink', 'xi-novels' ); ?>"><span><?php esc_html_e( 'Ink', 'xi-novels' ); ?></span></button>
								<button type="button" data-xin-bm-color="beta" title="<?php esc_attr_e( 'Jade', 'xi-novels' ); ?>"><span><?php esc_html_e( 'Jade', 'xi-novels' ); ?></span></button>
								<button type="button" data-xin-bm-color="gamma" title="<?php esc_attr_e( 'Gold', 'xi-novels' ); ?>"><span><?php esc_html_e( 'Gold', 'xi-novels' ); ?></span></button>
								<button type="button" data-xin-bm-color="delta" title="<?php esc_attr_e( 'Crimson', 'xi-novels' ); ?>"><span><?php esc_html_e( 'Crimson', 'xi-novels' ); ?></span></button>
							</div>
							<button type="button" class="xin-pkit__act xin-pkit__act--ghost" data-xin-ptool="link">
								<?php xin_the_icon( 'link' ); ?>
								<span><?php esc_html_e( 'Copy link', 'xi-novels' ); ?></span>
							</button>
						</section>
						<section class="xin-pkit__col">
							<h3><?php esc_html_e( 'Respond', 'xi-novels' ); ?></h3>
							<button type="button" class="xin-pkit__act" data-xin-ptool="quote">
								<?php xin_the_icon( 'quote' ); ?>
								<span><?php esc_html_e( 'Quote', 'xi-novels' ); ?></span>
							</button>
							<button type="button" class="xin-pkit__act" data-xin-ptool="suggestion">
								<?php xin_the_icon( 'highlighter' ); ?>
								<span><?php esc_html_e( 'Suggest', 'xi-novels' ); ?></span>
							</button>
						</section>
						<section class="xin-pkit__col xin-pkit__col--listen">
							<h3><?php esc_html_e( 'Listen', 'xi-novels' ); ?></h3>
							<button type="button" class="xin-pkit__act xin-pkit__act--loud" data-xin-ptool="tts">
								<?php xin_the_icon( 'play', '', true ); ?>
								<span><?php esc_html_e( 'Listen', 'xi-novels' ); ?></span>
							</button>
							<button type="button" class="xin-pkit__voice" data-xin-ptool="voices">
								<?php xin_the_icon( 'volume' ); ?>
								<span data-xin-tts-voice-name><?php esc_html_e( 'Choose a voice', 'xi-novels' ); ?></span>
							</button>
						</section>
					</div>
				</div>
				<div class="xin-suggest" data-xin-suggest hidden>
					<div class="xin-suggest__panel" role="dialog" aria-modal="true" aria-labelledby="xin-suggest-title">
						<button type="button" class="xin-suggest__x" data-xin-suggest-close aria-label="<?php esc_attr_e( 'Close', 'xi-novels' ); ?>"><?php xin_the_icon( 'close' ); ?></button>
						<h2 id="xin-suggest-title"><?php esc_html_e( 'Suggestion', 'xi-novels' ); ?></h2>
						<div class="xin-suggest__box" data-xin-suggest-original></div>
						<textarea class="xin-suggest__input" data-xin-suggest-input rows="4"></textarea>
						<div class="xin-suggest__box xin-suggest__diff" data-xin-suggest-diff></div>
						<div class="xin-suggest__actions">
							<button type="button" class="btn btn-outline btn-sm" data-xin-suggest-reset><?php esc_html_e( 'Reset', 'xi-novels' ); ?></button>
							<button type="button" class="btn btn-primary btn-sm" data-xin-suggest-submit><?php esc_html_e( 'Append to comment', 'xi-novels' ); ?></button>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<nav class="xin-rd__nav" data-xin-gl-scope>
				<?php if ( $xin_prev ) : ?>
					<a href="<?php echo esc_url( get_permalink( $xin_prev->ID ) ); ?>" data-xin-prev>
						<?php xin_the_icon( 'chevron-left' ); ?>
						<span style="min-width:0">
							<small><?php esc_html_e( 'Предыдущая', 'xi-novels' ); ?></small>
							<b><?php echo esc_html( $xin_prev->post_title ); ?></b>
						</span>
					</a>
				<?php else : ?>
					<span><?php xin_the_icon( 'chevron-left' ); ?><small><?php esc_html_e( 'Это первая глава', 'xi-novels' ); ?></small></span>
				<?php endif; ?>

				<?php if ( $xin_next ) : ?>
					<a class="is-next" href="<?php echo esc_url( get_permalink( $xin_next->ID ) ); ?>" data-xin-next>
						<span style="min-width:0">
							<small><?php esc_html_e( 'Следующая', 'xi-novels' ); ?></small>
							<b><?php echo esc_html( $xin_next->post_title ); ?></b>
						</span>
						<?php xin_the_icon( 'chevron-right' ); ?>
					</a>
				<?php else : ?>
					<span class="is-next"><small><?php esc_html_e( 'Это последняя глава', 'xi-novels' ); ?></small><?php xin_the_icon( 'chevron-right' ); ?></span>
				<?php endif; ?>
			</nav>

			<p class="xin-center xin-mt-3 xin-muted" style="font-size:12.5px">
				<?php
				printf(
					
					esc_html__( 'Листайте клавишами %1$s и %2$s', 'xi-novels' ),
					'<span class="xin-kbd">←</span>',
					'<span class="xin-kbd">→</span>'
				);
				?>
			</p>

			<?php xin_talk_render( $xin_id ); ?>

		</div>
	</main>

	<div class="xin-tts" data-xin-tts hidden>
		<div class="xin-tts__transport">
			<button type="button" class="xin-tts__btn" data-xin-tts-play aria-label="<?php esc_attr_e( 'Play', 'xi-novels' ); ?>"><?php xin_the_icon( 'play', '', true ); ?></button>
			<button type="button" class="xin-tts__btn" data-xin-tts-pause aria-label="<?php esc_attr_e( 'Pause', 'xi-novels' ); ?>"><?php xin_the_icon( 'pause', '', true ); ?></button>
			<button type="button" class="xin-tts__btn" data-xin-tts-stop aria-label="<?php esc_attr_e( 'Stop', 'xi-novels' ); ?>"><?php xin_the_icon( 'stop', '', true ); ?></button>
			<button type="button" class="xin-tts__btn" data-xin-tts-skip aria-label="<?php esc_attr_e( 'Skip', 'xi-novels' ); ?>"><?php xin_the_icon( 'skip-forward', '', true ); ?></button>
			<div class="xin-tts__now">
				<small><?php esc_html_e( 'Reading', 'xi-novels' ); ?></small>
				<span data-xin-tts-label></span>
			</div>
			<button type="button" class="xin-tts__voices-toggle" data-xin-tts-studio-toggle>
				<?php xin_the_icon( 'volume' ); ?>
				<span data-xin-tts-voice-name><?php esc_html_e( 'Voices', 'xi-novels' ); ?></span>
			</button>
		</div>
		<div class="xin-tts__studio" data-xin-tts-studio hidden>
			<div class="xin-tts__studio-head">
				<label class="xin-tts__search">
					<?php xin_the_icon( 'search' ); ?>
					<input type="search" data-xin-tts-filter placeholder="<?php esc_attr_e( 'Filter voices on this device', 'xi-novels' ); ?>">
				</label>
				<label class="xin-tts__local">
					<input type="checkbox" data-xin-tts-local checked>
					<?php esc_html_e( 'On-device only', 'xi-novels' ); ?>
				</label>
			</div>
			<div class="xin-tts__list" data-xin-tts-list></div>
			<div class="xin-tts__dials">
				<label><?php esc_html_e( 'Speed', 'xi-novels' ); ?> <output data-xin-tts-rate-out>1.0</output>
					<input type="range" min="0.5" max="1.8" step="0.1" value="1" data-xin-tts-rate>
				</label>
				<label><?php esc_html_e( 'Pitch', 'xi-novels' ); ?> <output data-xin-tts-pitch-out>1.0</output>
					<input type="range" min="0.5" max="1.8" step="0.1" value="1" data-xin-tts-pitch>
				</label>
				<label><?php esc_html_e( 'Volume', 'xi-novels' ); ?> <output data-xin-tts-vol-out>100</output>
					<input type="range" min="0" max="100" step="1" value="100" data-xin-tts-vol>
				</label>
				<button type="button" class="xin-pkit__act xin-pkit__act--ghost" data-xin-tts-preview><?php esc_html_e( 'Preview voice', 'xi-novels' ); ?></button>
			</div>
			<p class="xin-tts__hint" data-xin-tts-hint></p>
		</div>
	</div>

	<div class="xin-rd__dock" data-xin-rd-dock>
		<?php if ( $xin_novel_id ) : ?>
			<a class="btn btn-icon" href="<?php echo esc_url( get_permalink( $xin_novel_id ) . '#chapters' ); ?>" aria-label="<?php esc_attr_e( 'Все главы', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'library' ); ?>
			</a>
		<?php endif; ?>
		<span class="xin-rd__dock-bar"><i data-xin-rd-fill style="width:0"></i></span>
		<span class="xin-rd__dock-pct" data-xin-rd-pct>0%</span>
		<?php if ( $xin_next ) : ?>
			<a class="btn btn-primary btn-sm" href="<?php echo esc_url( get_permalink( $xin_next->ID ) ); ?>">
				<?php esc_html_e( 'Дальше', 'xi-novels' ); ?><?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<aside class="xin-rd__toc" data-xin-rd-sheet data-xin-rd-toc-panel aria-label="<?php esc_attr_e( 'Оглавление', 'xi-novels' ); ?>">
		<h3 style="display:flex;justify-content:space-between;align-items:center">
			<?php esc_html_e( 'Оглавление', 'xi-novels' ); ?>
			<button type="button" class="btn btn-icon" data-xin-rd-close><?php xin_the_icon( 'close' ); ?></button>
		</h3>
		<ul data-xin-gl-scope>
			<?php foreach ( $xin_all as $xin_item ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $xin_item->ID ) ); ?>" class="<?php echo (int) $xin_item->ID === (int) $xin_id ? 'is-current' : ''; ?>">
						<span class="xin-chapters__num"><?php echo esc_html( '#' . xin_chapter_label( $xin_item->ID ) ); ?></span>
						<span class="xin-chapters__title"><?php echo esc_html( $xin_item->post_title ); ?></span>
						<?php if ( get_post_meta( $xin_item->ID, '_xin_locked', true ) ) : ?>
							<span class="xin-chapters__lock"><?php xin_the_icon( 'lock' ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</aside>

	<aside class="xin-rd__panel" data-xin-rd-sheet data-xin-rd-panel aria-label="<?php esc_attr_e( 'Настройки чтения', 'xi-novels' ); ?>">
		<h3>
			<?php esc_html_e( 'Как читать', 'xi-novels' ); ?>
			<button type="button" class="btn btn-icon" data-xin-rd-close><?php xin_the_icon( 'close' ); ?></button>
		</h3>

		<div class="xin-rd__group">
			<span><?php esc_html_e( 'Размер текста', 'xi-novels' ); ?></span>
			<div class="xin-rd__stepper">
				<button type="button" class="btn btn-icon" data-xin-size="-1" aria-label="<?php esc_attr_e( 'Мельче', 'xi-novels' ); ?>"><?php xin_the_icon( 'minus' ); ?></button>
				<output data-xin-size-value>19</output>
				<button type="button" class="btn btn-icon" data-xin-size="1" aria-label="<?php esc_attr_e( 'Крупнее', 'xi-novels' ); ?>"><?php xin_the_icon( 'plus' ); ?></button>
			</div>
		</div>

		<div class="xin-rd__group">
			<span><?php esc_html_e( 'Межстрочный интервал', 'xi-novels' ); ?></span>
			<div class="xin-rd__stepper">
				<button type="button" class="btn btn-icon" data-xin-lead="-1" aria-label="<?php esc_attr_e( 'Плотнее', 'xi-novels' ); ?>"><?php xin_the_icon( 'minus' ); ?></button>
				<output data-xin-lead-value>1.9</output>
				<button type="button" class="btn btn-icon" data-xin-lead="1" aria-label="<?php esc_attr_e( 'Свободнее', 'xi-novels' ); ?>"><?php xin_the_icon( 'plus' ); ?></button>
			</div>
		</div>

		<div class="xin-rd__group">
			<span><?php esc_html_e( 'Ширина колонки', 'xi-novels' ); ?></span>
			<div class="xin-rd__choices">
				<button type="button" data-xin-width="620"><?php esc_html_e( 'Узкая', 'xi-novels' ); ?></button>
				<button type="button" data-xin-width="720"><?php esc_html_e( 'Средняя', 'xi-novels' ); ?></button>
				<button type="button" data-xin-width="900"><?php esc_html_e( 'Широкая', 'xi-novels' ); ?></button>
			</div>
		</div>

		<div class="xin-rd__group">
			<span><?php esc_html_e( 'Шрифт', 'xi-novels' ); ?></span>
			<div class="xin-rd__choices">
				<button type="button" data-xin-font="serif"><?php esc_html_e( 'С засечками', 'xi-novels' ); ?></button>
				<button type="button" data-xin-font="sans"><?php esc_html_e( 'Гротеск', 'xi-novels' ); ?></button>
			</div>
		</div>

		<div class="xin-rd__group">
			<span><?php esc_html_e( 'Бумага', 'xi-novels' ); ?></span>
			<div class="xin-rd__choices">
				<button type="button" data-xin-paper="default"><?php esc_html_e( 'Как сайт', 'xi-novels' ); ?></button>
				<button type="button" data-xin-paper="paper"><?php esc_html_e( 'Белая', 'xi-novels' ); ?></button>
				<button type="button" data-xin-paper="sepia"><?php esc_html_e( 'Сепия', 'xi-novels' ); ?></button>
				<button type="button" data-xin-paper="night"><?php esc_html_e( 'Ночь', 'xi-novels' ); ?></button>
			</div>
		</div>

		<p class="xin-muted" style="font-size:12px">
			<?php esc_html_e( 'Настройки сохраняются в этом браузере и применяются ко всем главам сайта.', 'xi-novels' ); ?>
		</p>
	</aside>

	<aside class="xin-rd__panel xin-rd__panel--wide" data-xin-rd-sheet data-xin-gl-panel aria-label="<?php esc_attr_e( 'Глоссарий', 'xi-novels' ); ?>">
		<h3>
			<?php esc_html_e( 'Глоссарий', 'xi-novels' ); ?>
			<button type="button" class="btn btn-icon" data-xin-rd-close><?php xin_the_icon( 'close' ); ?></button>
		</h3>

		<label class="xin-gl__switch">
			<input type="checkbox" data-xin-gl-toggle checked>
			<span><?php esc_html_e( 'Заменять термины при чтении', 'xi-novels' ); ?></span>
		</label>

		<?php if ( $xin_gloss ) : ?>
			<label class="xin-gl__switch">
				<input type="checkbox" data-xin-gl-project checked>
				<span>
					<?php
					printf(
						/* translators: %s: number of rules */
						esc_html__( 'Словарь переводчика (%s)', 'xi-novels' ),
						esc_html( xin_num( count( $xin_gloss ) ) )
					);
					?>
				</span>
			</label>
		<?php endif; ?>

		<form class="xin-gl__form" data-xin-gl-form>
			<div class="xin-gl__pair">
				<input type="text" data-xin-gl-from autocomplete="off" spellcheck="false" required
					placeholder="<?php esc_attr_e( 'как в тексте', 'xi-novels' ); ?>"
					aria-label="<?php esc_attr_e( 'Что заменить', 'xi-novels' ); ?>">
				<?php xin_the_icon( 'arrow-right' ); ?>
				<input type="text" data-xin-gl-to autocomplete="off" spellcheck="false"
					placeholder="<?php esc_attr_e( 'как надо', 'xi-novels' ); ?>"
					aria-label="<?php esc_attr_e( 'Чем заменить', 'xi-novels' ); ?>">
			</div>

			<div class="xin-checks xin-gl__opts">
				<label class="xin-check"><input type="checkbox" data-xin-gl-ci checked><?php esc_html_e( 'Любой регистр', 'xi-novels' ); ?></label>
				<label class="xin-check"><input type="checkbox" data-xin-gl-whole><?php esc_html_e( 'Слово целиком', 'xi-novels' ); ?></label>
				<label class="xin-check"><input type="checkbox" data-xin-gl-all><?php esc_html_e( 'Во всех тайтлах', 'xi-novels' ); ?></label>
			</div>

			<div class="xin-gl__actions">
				<button type="submit" class="btn btn-primary btn-sm" data-xin-gl-submit><?php esc_html_e( 'Добавить', 'xi-novels' ); ?></button>
				<button type="button" class="btn btn-ghost btn-sm" data-xin-gl-cancel hidden><?php esc_html_e( 'Отмена', 'xi-novels' ); ?></button>
			</div>
		</form>

		<div class="xin-gl__list" data-xin-gl-list></div>

		<label class="xin-gl__switch">
			<input type="checkbox" data-xin-gl-mark>
			<span><?php esc_html_e( 'Подсвечивать замены', 'xi-novels' ); ?></span>
		</label>

		<div class="xin-gl__io">
			<button type="button" class="btn btn-outline btn-sm" data-xin-gl-export>
				<?php xin_the_icon( 'download' ); ?><?php esc_html_e( 'Выгрузить файл', 'xi-novels' ); ?>
			</button>
			<button type="button" class="btn btn-outline btn-sm" data-xin-gl-import>
				<?php xin_the_icon( 'upload' ); ?><?php esc_html_e( 'Загрузить файл', 'xi-novels' ); ?>
			</button>
			<input type="file" accept=".json,application/json" hidden data-xin-gl-file>
		</div>

		<p class="xin-muted xin-gl__note" data-xin-gl-note></p>
		<p class="xin-muted" style="font-size:12px">
			<?php esc_html_e( 'Словарь хранится в этом браузере и никуда не отправляется. Файлом его можно перенести на другое устройство или отдать другому читателю.', 'xi-novels' ); ?>
		</p>
	</aside>

	<button type="button" class="xin-gl-pop" data-xin-gl-pop hidden>
		<?php xin_the_icon( 'languages' ); ?><?php esc_html_e( 'В глоссарий', 'xi-novels' ); ?>
	</button>

	<div class="xin-rd__scrim" data-xin-rd-scrim></div>
</article>

<?php wp_footer(); ?>
</body>
</html>
	<?php
endwhile;
