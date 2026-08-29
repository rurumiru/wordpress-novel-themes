<?php
/**
 * Главная раздела комиксов: /comics/.
 *
 * Витрина построена вокруг того, чем комикс отличается от текста: его выбирают
 * глазами и читают по расписанию. Отсюда афиша во весь экран вместо плитки
 * равнозначных обложек, неделя выходов отдельным блоком и лента, собранная по
 * тайтлам, а не по главам, — иначе один тайтл, выложивший вечером три главы,
 * занимает собой всю ленту.
 *
 * @package XIN-Com
 */

get_header();

$xin_featured = xin_get_novels( 'featured', 8, 'comic' );
$xin_popular  = xin_get_novels( 'popular', 10, 'comic' );
$xin_latest   = xin_get_novels( 'latest', 12, 'comic' );
$xin_billing  = array_slice( $xin_featured ? $xin_featured : $xin_popular, 0, 5 );
$xin_updates  = xin_comics_updates( 6, 3 );
$xin_schedule = xin_comics_schedule();
$xin_genres   = xin_comics_genres();
$xin_catalog  = xin_section_catalog_link( 'comic' );
$xin_today    = (int) current_time( 'N' );

$xin_days = array(
	1 => __( 'Понедельник', 'xin-com' ),
	2 => __( 'Вторник', 'xin-com' ),
	3 => __( 'Среда', 'xin-com' ),
	4 => __( 'Четверг', 'xin-com' ),
	5 => __( 'Пятница', 'xin-com' ),
	6 => __( 'Суббота', 'xin-com' ),
	7 => __( 'Воскресенье', 'xin-com' ),
);

$xin_days_short = array(
	1 => __( 'Пн', 'xin-com' ),
	2 => __( 'Вт', 'xin-com' ),
	3 => __( 'Ср', 'xin-com' ),
	4 => __( 'Чт', 'xin-com' ),
	5 => __( 'Пт', 'xin-com' ),
	6 => __( 'Сб', 'xin-com' ),
	7 => __( 'Вс', 'xin-com' ),
);
?>

<?php if ( ! $xin_popular && ! $xin_latest ) : ?>

	<div class="xin-wrap xin-section">
		<div class="xin-glass xin-cm-empty">
			<?php xin_the_icon( 'layers' ); ?>
			<h1><?php esc_html_e( 'Комиксы скоро появятся', 'xin-com' ); ?></h1>
			<p class="xin-muted"><?php esc_html_e( 'В разделе пока ни одного тайтла. Отметьте тайтл форматом «Комикс» — и он окажется здесь.', 'xin-com' ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'В раздел новелл', 'xin-com' ); ?></a>
		</div>
	</div>

<?php else : ?>

	<?php if ( $xin_billing ) : ?>
		<?php
		$xin_lead      = $xin_billing[0];
		$xin_lead_bg   = xin_background_url( $xin_lead );
		$xin_lead_art  = $xin_lead_bg ? $xin_lead_bg : xin_cover_url( $xin_lead );
		$xin_lead_rate = xin_rating( $xin_lead );
		$xin_lead_gen  = get_the_terms( $xin_lead, 'genre' );
		$xin_lead_ch   = xin_get_chapters( $xin_lead, 'ASC', 1 );
		$xin_lead_cov  = xin_cover_url( $xin_lead, 'xin-cover-lg' );
		?>
		<section class="xin-bill">
			<div class="xin-bill__art" aria-hidden="true">
				<?php if ( $xin_lead_art ) : ?>
					<img src="<?php echo esc_url( $xin_lead_art ); ?>" alt="" width="1600" height="900" fetchpriority="high" decoding="async">
				<?php endif; ?>
			</div>

			<div class="xin-wrap xin-bill__in">
				<a class="xin-bill__poster" href="<?php echo esc_url( get_permalink( $xin_lead ) ); ?>" tabindex="-1" aria-hidden="true">
					<?php if ( $xin_lead_cov ) : ?>
						<img src="<?php echo esc_url( $xin_lead_cov ); ?>" alt="" width="400" height="600" fetchpriority="high" decoding="async">
					<?php endif; ?>
				</a>

				<div class="xin-bill__body">
					<span class="xin-bill__kicker">
						<?php xin_the_icon( 'flame' ); ?>
						<?php esc_html_e( 'выбор редакции', 'xin-com' ); ?>
					</span>

					<h1 class="xin-bill__title">
						<a href="<?php echo esc_url( get_permalink( $xin_lead ) ); ?>"><?php echo esc_html( get_the_title( $xin_lead ) ); ?></a>
					</h1>

					<div class="xin-bill__facts">
						<?php if ( $xin_lead_rate['count'] ) : ?>
							<b><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_lead_rate['value'], 1, ',', '' ) ); ?></b>
						<?php endif; ?>
						<span><?php echo esc_html( xin_novel_author( $xin_lead ) ); ?></span>
						<?php if ( $xin_lead_gen && ! is_wp_error( $xin_lead_gen ) ) : ?>
							<span><?php echo esc_html( implode( ' · ', wp_list_pluck( array_slice( $xin_lead_gen, 0, 3 ), 'name' ) ) ); ?></span>
						<?php endif; ?>
					</div>

					<p class="xin-bill__lead"><?php echo esc_html( wp_trim_words( get_the_excerpt( $xin_lead ), 28 ) ); ?></p>

					<div class="xin-bill__actions">
						<?php if ( $xin_lead_ch ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( $xin_lead_ch[0]->ID ) ); ?>">
								<?php xin_the_icon( 'play' ); ?><?php esc_html_e( 'Читать', 'xin-com' ); ?>
							</a>
						<?php endif; ?>
						<a class="btn btn-outline xin-bill__more" href="<?php echo esc_url( get_permalink( $xin_lead ) ); ?>"><?php esc_html_e( 'Подробнее', 'xin-com' ); ?></a>
					</div>
				</div>

				<?php if ( count( $xin_billing ) > 1 ) : ?>
					<div class="xin-bill__rail">
						<span class="xin-bill__rail-label"><?php esc_html_e( 'Смотрите также', 'xin-com' ); ?></span>
						<?php foreach ( array_slice( $xin_billing, 1 ) as $xin_id ) : ?>
							<?php $xin_thumb = xin_cover_url( $xin_id, 'xin-cover-sm' ); ?>
							<a class="xin-bill__rail-item" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
								<span class="xin-bill__rail-cover">
									<?php if ( $xin_thumb ) : ?>
										<img src="<?php echo esc_url( $xin_thumb ); ?>" alt="" width="48" height="72" loading="lazy">
									<?php endif; ?>
								</span>
								<span class="xin-bill__rail-name"><?php echo esc_html( get_the_title( $xin_id ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( array_filter( $xin_schedule ) ) : ?>
		<section class="xin-wrap xin-section xin-reveal">
			<?php
			xin_section_head( array(
				'eyebrow'  => __( 'неделя', 'xin-com' ),
				'title'    => __( 'Когда что выходит', 'xin-com' ),
				'icon'     => 'calendar',
				'subtitle' => __( 'День берётся из дат последних глав, поэтому расписание не расходится с выходами.', 'xin-com' ),
			) );
			?>

			<div class="xin-week" data-xin-tabs>
				<div class="xin-week__days" role="tablist">
					<?php foreach ( $xin_days_short as $xin_n => $xin_short ) : ?>
						<button
							type="button"
							class="xin-week__day<?php echo $xin_n === $xin_today ? ' active' : ''; ?><?php echo $xin_schedule[ $xin_n ] ? '' : ' is-empty'; ?>"
							role="tab"
							aria-selected="<?php echo $xin_n === $xin_today ? 'true' : 'false'; ?>"
							data-xin-tab="day-<?php echo (int) $xin_n; ?>"
						>
							<span class="xin-week__short"><?php echo esc_html( $xin_short ); ?></span>
							<span class="xin-week__count"><?php echo (int) count( $xin_schedule[ $xin_n ] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<?php foreach ( $xin_days as $xin_n => $xin_name ) : ?>
					<div class="xin-week__panel" data-xin-tabpanel="day-<?php echo (int) $xin_n; ?>" <?php echo $xin_n === $xin_today ? '' : 'hidden'; ?>>
						<?php if ( $xin_schedule[ $xin_n ] ) : ?>
							<div class="xin-cm-grid">
								<?php foreach ( $xin_schedule[ $xin_n ] as $xin_id ) : ?>
									<?php xin_comic_card( $xin_id ); ?>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="xin-week__none">
								<?php
								printf(
									/* translators: %s — день недели в нижнем регистре. */
									esc_html__( 'В этот день ничего не выходило: %s свободен.', 'xin-com' ),
									esc_html( function_exists( 'mb_strtolower' ) ? mb_strtolower( $xin_name ) : $xin_name )
								);
								?>
							</p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $xin_updates ) : ?>
		<section class="xin-wrap xin-section xin-reveal">
			<?php
			xin_section_head( array(
				'eyebrow'    => __( 'лента', 'xin-com' ),
				'title'      => __( 'Свежие главы', 'xin-com' ),
				'icon'       => 'clock',
				'more_href'  => xin_section_updates_link( 'comic' ),
				'more_label' => __( 'Все обновления', 'xin-com' ),
			) );
			?>

			<div class="xin-upd">
				<?php foreach ( $xin_updates as $xin_group ) : ?>
					<?php
					$xin_id    = $xin_group['novel'];
					$xin_cover = xin_cover_url( $xin_id, 'xin-cover-sm' );
					?>
					<article class="xin-upd__card">
						<a class="xin-upd__cover" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
							<?php if ( $xin_cover ) : ?>
								<img src="<?php echo esc_url( $xin_cover ); ?>" alt="<?php echo esc_attr( get_the_title( $xin_id ) ); ?>" width="80" height="120" loading="lazy">
							<?php endif; ?>
						</a>

						<div class="xin-upd__body">
							<h3 class="xin-upd__title"><a href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>"><?php echo esc_html( get_the_title( $xin_id ) ); ?></a></h3>

							<ul class="xin-upd__list">
								<?php foreach ( $xin_group['chapters'] as $xin_chapter ) : ?>
									<?php
									$xin_label  = xin_chapter_label( $xin_chapter->ID );
									$xin_locked = (bool) get_post_meta( $xin_chapter->ID, '_xin_locked', true );
									?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $xin_chapter->ID ) ); ?>">
											<?php if ( $xin_label ) : ?>
												<b>#<?php echo esc_html( $xin_label ); ?></b>
											<?php endif; ?>
											<span class="xin-upd__name"><?php echo esc_html( $xin_chapter->post_title ); ?></span>
											<?php if ( $xin_locked ) : ?>
												<?php xin_the_icon( 'lock' ); ?>
											<?php endif; ?>
											<time><?php echo esc_html( xin_ago( get_the_time( 'U', $xin_chapter->ID ) ) ); ?></time>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $xin_popular ) : ?>
		<section class="xin-wrap xin-section xin-reveal">
			<?php
			xin_section_head( array(
				'eyebrow'    => __( 'выбор читателей', 'xin-com' ),
				'title'      => __( 'Топ раздела', 'xin-com' ),
				'icon'       => 'trophy',
				'more_href'  => add_query_arg( 'sort', 'popular', $xin_catalog ),
				'more_label' => __( 'Весь рейтинг', 'xin-com' ),
			) );
			?>

			<ol class="xin-top">
				<?php foreach ( $xin_popular as $xin_rank => $xin_id ) : ?>
					<?php
					$xin_cover = xin_cover_url( $xin_id );
					$xin_rate  = xin_rating( $xin_id );
					?>
					<li class="xin-top__item">
						<a href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
							<span class="xin-top__num" aria-hidden="true"><?php echo (int) $xin_rank + 1; ?></span>
							<span class="xin-top__cover">
								<?php if ( $xin_cover ) : ?>
									<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" width="160" height="240" loading="lazy">
								<?php endif; ?>
							</span>
							<span class="xin-top__meta">
								<span class="xin-top__name"><?php echo esc_html( get_the_title( $xin_id ) ); ?></span>
								<span class="xin-top__facts">
									<?php if ( $xin_rate['count'] ) : ?>
										<?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $xin_rate['value'], 1, ',', '' ) ); ?>
										<span aria-hidden="true">·</span>
									<?php endif; ?>
									<?php echo esc_html( xin_num( xin_get_views( $xin_id ) ) ); ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( $xin_latest ) : ?>
		<section class="xin-wrap xin-section xin-reveal">
			<?php
			xin_section_head( array(
				'eyebrow'    => __( 'новинки', 'xin-com' ),
				'title'      => __( 'Недавно добавленные', 'xin-com' ),
				'icon'       => 'sparkles',
				'more_href'  => $xin_catalog,
				'more_label' => __( 'Весь каталог', 'xin-com' ),
			) );
			?>
			<div class="xin-cm-grid">
				<?php foreach ( $xin_latest as $xin_id ) : ?>
					<?php xin_comic_card( $xin_id ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $xin_genres ) : ?>
		<section class="xin-wrap xin-section xin-reveal">
			<?php
			xin_section_head( array(
				'eyebrow' => __( 'по вкусу', 'xin-com' ),
				'title'   => __( 'Жанры раздела', 'xin-com' ),
				'icon'    => 'tag',
			) );
			?>
			<div class="xin-genres">
				<?php foreach ( $xin_genres as $xin_genre ) : ?>
					<a class="xin-genre-chip" href="<?php echo esc_url( get_term_link( $xin_genre['term'] ) ); ?>">
						<?php echo esc_html( $xin_genre['term']->name ); ?><b><?php echo (int) $xin_genre['count']; ?></b>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

<?php endif; ?>

<?php get_footer(); ?>
