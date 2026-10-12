<?php
/**
 * Читалка.
 *
 * Шапка сайта здесь не выводится: в чтении она только отнимает высоту.
 * Оглавление и настройки открываются из панелей читалки.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$xv_id    = get_the_ID();
	$xv_novel = xv_chapter_novel( $xv_id );
	$xv_near  = xv_adjacent_chapters( $xv_id );
	// Оглавление в сайдбаре — окно вокруг текущей главы: на тайтле в
	// тысячи глав полный список это мегабайт разметки, который никто не
	// пролистает. Остальное подтягивает reader.js по мере прокрутки.
	$xv_toc   = xv_chapter_window( $xv_id );
	$xv_size  = xv_chapter_size( $xv_id );
	$xv_num   = xv_chapter_number( $xv_id );
	$xv_open  = xv_chapter_unlocked( $xv_id );
	$xv_price = xv_chapter_price( $xv_id );
	$xv_until = (int) get_post_meta( $xv_id, '_xin_unlock_at', true );
	?>

	<?php include get_theme_file_path('template-parts/reader-header.php'); ?>

	<div class="xv-reader-progress" data-xv-progress aria-hidden="true"><span></span></div>

	<div class="xv-reader" data-xv-reader>

		<aside class="xv-reader-side" id="xv-reader-toc-panel" hidden><div class="xv-pia-panel-title"><strong><?php esc_html_e('Оглавление', 'xin-v2'); ?></strong><button type="button" class="xv-icon-btn" data-xv-reader-dismiss aria-label="<?php esc_attr_e('Закрыть', 'xin-v2'); ?>"><?php xv_the_icon("close"); ?></button></div>
			<?php if ( $xv_novel ) : ?>
				<div class="xv-reader-side-head">
					<span class="xv-reader-side-cover">
						<?php if ( xv_cover_url( $xv_novel->ID ) ) : ?>
							<img src="<?php echo esc_url( xv_cover_url( $xv_novel->ID ) ); ?>" alt="">
						<?php endif; ?>
					</span>
					<div>
						<a class="xv-reader-side-title" href="<?php echo esc_url( get_permalink( $xv_novel ) ); ?>"><?php echo esc_html( get_the_title( $xv_novel ) ); ?></a>
						<div class="xv-reader-side-count">
							<?php
							printf(
								/* translators: 1: номер текущей главы, 2: всего глав. */
								esc_html__( 'Глава %1$d из %2$d', 'xin-v2' ),
								(int) ( $xv_near['index'] + 1 ),
								(int) $xv_near['total']
							);
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<nav class="xv-reader-toc" aria-label="<?php esc_attr_e( 'Оглавление', 'xin-v2' ); ?>"
				data-xv-toc="<?php echo (int) ( $xv_novel ? $xv_novel->ID : 0 ); ?>"
				data-xv-toc-offset="<?php echo (int) $xv_toc['offset']; ?>"
				data-xv-toc-total="<?php echo (int) $xv_toc['total']; ?>">
				<?php if ( $xv_toc['offset'] > 0 ) : ?>
					<button type="button" class="xv-reader-toc-more" data-xv-toc-up><?php esc_html_e( 'Показать главы выше', 'xin-v2' ); ?></button>
				<?php endif; ?>
				<?php foreach ( $xv_toc['posts'] as $xv_i => $xv_item ) : ?>
					<?php xv_toc_link( $xv_item, $xv_id, $xv_toc['offset'] + $xv_i ); ?>
				<?php endforeach; ?>
				<?php if ( $xv_toc['offset'] + count( $xv_toc['posts'] ) < $xv_toc['total'] ) : ?>
					<button type="button" class="xv-reader-toc-more" data-xv-toc-down><?php esc_html_e( 'Показать главы ниже', 'xin-v2' ); ?></button>
				<?php endif; ?>
			</nav>
		</aside>

		<div class="xv-paper-wrap">
			<article class="xv-paper">
				<?php
				xv_breadcrumbs( array(
					array( 'label' => __( 'Главная', 'xin-v2' ), 'url' => home_url( '/' ) ),
					$xv_novel ? array( 'label' => get_the_title( $xv_novel ), 'url' => get_permalink( $xv_novel ) ) : array(),
					array( 'label' => get_the_title() ),
				) );
				?>

				<?php if ( '' !== $xv_num ) : ?>
					<p class="xv-chapter-kicker"><?php printf( esc_html__( 'Глава %s', 'xin-v2' ), esc_html( $xv_num ) ); ?></p>
				<?php endif; ?>

				<h1 class="xv-chapter-title"><?php the_title(); ?></h1>

				<p class="xv-chapter-meta">
					<?php
					printf(
						/* translators: 1: время чтения со словом «минут», 2: число слов со словом «слов», 3: когда опубликовано. */
						esc_html__( '≈ %1$s · %2$s · %3$s', 'xin-v2' ),
						esc_html( xv_counted( $xv_size['minutes'], xv_label_minutes( $xv_size['minutes'] ) ) ),
						esc_html( xv_counted( $xv_size['words'], xv_label_words( $xv_size['words'] ) ) ),
						esc_html( xv_ago( get_post_timestamp() ) )
					);
					?>
				</p>

				<?php if ( $xv_open ) : ?>
					<div class="xv-chapter-text" data-xv-body>
						<div data-xv-number="<?php echo esc_attr($xv_num); ?>" data-xv-chapter="<?php echo (int) $xv_id; ?>" data-xv-url="<?php echo esc_url( get_permalink() ); ?>"
							data-xv-prev-url="<?php echo esc_url( $xv_near['prev'] ? get_permalink( $xv_near['prev'] ) : '' ); ?>"
							data-xv-prev-title-value="<?php echo esc_attr( $xv_near['prev'] ? get_the_title( $xv_near['prev'] ) : '' ); ?>"
							data-xv-next-url="<?php echo esc_url( $xv_near['next'] ? get_permalink( $xv_near['next'] ) : '' ); ?>"
							data-xv-next-title-value="<?php echo esc_attr( $xv_near['next'] ? get_the_title( $xv_near['next'] ) : '' ); ?>">
							<?php the_content(); ?>
						</div>
					</div>

					<p class="xv-chapter-tail" data-xv-tail></p>

					<p class="xv-chapter-end">— <?php esc_html_e( 'конец главы', 'xin-v2' ); ?> —</p>

					<div class="xv-chapter-foot">
						<?php if ( xv_discussion_open( $xv_id ) ) : ?>
							<a class="xv-btn sm" href="#comments">
								<?php xv_the_icon( 'comment' ); ?>
								<?php echo esc_html( xv_counted( get_comments_number(), xv_label_comments( get_comments_number() ) ) ); ?>
							</a>
						<?php endif; ?>

						<div class="xv-share">
							<span class="xv-share-label"><?php esc_html_e( 'Поделиться', 'xin-v2' ); ?></span>
							<?php
							$xv_link  = rawurlencode( get_permalink() );
							$xv_title = rawurlencode( get_the_title() );
							$xv_links = array(
								'telegram' => 'https://t.me/share/url?url=' . $xv_link . '&text=' . $xv_title,
								'vk'       => 'https://vk.com/share.php?url=' . $xv_link,
							);
							?>
							<?php foreach ( $xv_links as $xv_net => $xv_url ) : ?>
								<a class="xv-icon-btn" href="<?php echo esc_url( $xv_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $xv_net ); ?>">
									<?php xv_the_icon( $xv_net ); ?>
								</a>
							<?php endforeach; ?>
							<button type="button" class="xv-icon-btn" data-xv-copy="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Скопировать ссылку', 'xin-v2' ); ?>">
								<?php xv_the_icon( 'link' ); ?>
							</button>
						</div>
					</div>
				<?php else : ?>
					<div class="xv-locked">
						<div class="xv-locked-icon"><?php xv_the_icon( 'lock' ); ?></div>
						<h2><?php esc_html_e( 'Глава в раннем доступе', 'xin-v2' ); ?></h2>
						<p><?php esc_html_e( 'Она откроется для всех по расписанию. Подписчики читают её уже сейчас.', 'xin-v2' ); ?></p>
						<?php if ( $xv_until > time() ) : ?>
							<p class="xv-locked-when">
								<?php
								printf(
									/* translators: %s: дата открытия главы. */
									esc_html__( 'Откроется %s', 'xin-v2' ),
									esc_html( wp_date( 'j M Y, H:i', $xv_until ) )
								);
								?>
							</p>
						<?php elseif ( $xv_price ) : ?>
							<p class="xv-locked-when">
								<?php
								printf(
									/* translators: %s: цена главы. */
									esc_html__( 'Цена главы — %s', 'xin-v2' ),
									esc_html( $xv_price . ' ' . xv_opt( 'coin_label', '' ) )
								);
								?>
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $xv_open && ( xv_discussion_open( $xv_id ) || get_comments_number() ) ) : ?>
					<div class="xv-chapter-comments"><?php comments_template(); ?></div>
				<?php endif; ?>
			</article>
		</div>

		<div class="xv-reader-tools" data-xv-tools id="xv-reader-settings-panel" hidden><div class="xv-pia-panel-title"><strong><?php esc_html_e('Настройки чтения', 'xin-v2'); ?></strong><button type="button" class="xv-icon-btn" data-xv-reader-dismiss aria-label="<?php esc_attr_e('Закрыть', 'xin-v2'); ?>"><?php xv_the_icon("close"); ?></button></div>

			<div class="xv-reader-tools-group">
				<button type="button" data-xv-font="-" title="<?php esc_attr_e( 'Мельче', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Мельче', 'xin-v2' ); ?>"><span class="xv-tool-icon">A−</span><span class="xv-tool-label"><?php esc_html_e( 'Мельче', 'xin-v2' ); ?></span></button>
				<button type="button" data-xv-font="+" title="<?php esc_attr_e( 'Крупнее', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Крупнее', 'xin-v2' ); ?>"><span class="xv-tool-icon">A+</span><span class="xv-tool-label"><?php esc_html_e( 'Крупнее', 'xin-v2' ); ?></span></button>
				<button type="button" data-xv-width title="<?php esc_attr_e( 'Ширина колонки', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Ширина колонки', 'xin-v2' ); ?>" aria-pressed="false"><span class="xv-tool-icon"><?php xv_the_icon( 'align' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Ширина', 'xin-v2' ); ?></span></button>
			</div>
			<div class="xv-reader-tools-group">
				<button type="button" data-xv-paper title="<?php esc_attr_e( 'Бумага', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Бумага', 'xin-v2' ); ?>" aria-pressed="false"><span class="xv-tool-icon"><?php xv_the_icon( 'sparkles' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Бумага', 'xin-v2' ); ?></span></button>
				<button type="button" data-xv-theme-toggle title="<?php esc_attr_e( 'Светлая / тёмная', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Светлая / тёмная', 'xin-v2' ); ?>" aria-pressed="false"><span class="xv-tool-icon"><?php xv_the_icon( 'moon' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Тема', 'xin-v2' ); ?></span></button>
				<?php if ( xv_opt( 'reader_speech', true ) ) : ?>
					<button type="button" data-xv-speak title="<?php esc_attr_e( 'Читать вслух', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Читать вслух', 'xin-v2' ); ?>" aria-pressed="false"><span class="xv-tool-icon"><?php xv_the_icon( 'volume' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Слушать', 'xin-v2' ); ?></span></button>
				<?php endif; ?>
				<button type="button" data-xv-immersive title="<?php esc_attr_e( 'Скрыть оглавление', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Скрыть оглавление', 'xin-v2' ); ?>" aria-pressed="false"><span class="xv-tool-icon"><?php xv_the_icon( 'expand' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Без меню', 'xin-v2' ); ?></span></button>
				<button type="button" data-xv-top title="<?php esc_attr_e( 'Наверх', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Наверх', 'xin-v2' ); ?>"><span class="xv-tool-icon"><?php xv_the_icon( 'arrow-up' ); ?></span><span class="xv-tool-label"><?php esc_html_e( 'Наверх', 'xin-v2' ); ?></span></button>
			</div>
		</div>
	</div>

	<?php include get_theme_file_path('template-parts/reader-footer.php'); ?>

	<?php
endwhile;

get_footer();
