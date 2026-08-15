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

		<div class="xin-rd__bar-title">
			<b><?php the_title(); ?></b>
			<?php if ( $xin_novel_id ) : ?>
				<small><?php echo esc_html( get_the_title( $xin_novel_id ) ); ?></small>
			<?php endif; ?>
		</div>

		<div class="xin-rd__actions">
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
				<a class="xin-rd__eyebrow" href="<?php echo esc_url( get_permalink( $xin_novel_id ) ); ?>">
					<?php echo esc_html( get_the_title( $xin_novel_id ) ); ?>
				</a>
			<?php endif; ?>

			<h1 class="xin-rd__title">
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

			<?php if ( $xin_locked && ! is_user_logged_in() ) : ?>
				<div class="xin-locked">
					<?php xin_the_icon( 'lock' ); ?>
					<h2><?php esc_html_e( 'Глава для подписчиков PLUS', 'xi-novels' ); ?></h2>
					<p class="xin-muted"><?php esc_html_e( 'Войдите в аккаунт, чтобы продолжить чтение и поддержать переводчика.', 'xi-novels' ); ?></p>
					<a class="btn btn-gold xin-mt-2" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
						<?php xin_the_icon( 'crown' ); ?><?php esc_html_e( 'Войти', 'xi-novels' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="xin-rd__text" data-xin-rd-text>
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<nav class="xin-rd__nav">
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

		</div>
	</main>

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

	<aside class="xin-rd__toc" data-xin-rd-toc-panel aria-label="<?php esc_attr_e( 'Оглавление', 'xi-novels' ); ?>">
		<h3 style="display:flex;justify-content:space-between;align-items:center">
			<?php esc_html_e( 'Оглавление', 'xi-novels' ); ?>
			<button type="button" class="btn btn-icon" data-xin-rd-close><?php xin_the_icon( 'close' ); ?></button>
		</h3>
		<ul>
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

	<aside class="xin-rd__panel" data-xin-rd-panel aria-label="<?php esc_attr_e( 'Настройки чтения', 'xi-novels' ); ?>">
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

	<div class="xin-rd__scrim" data-xin-rd-scrim></div>
</article>

<?php wp_footer(); ?>
</body>
</html>
	<?php
endwhile;
