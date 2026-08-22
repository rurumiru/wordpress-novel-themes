<?php
/**
 * Template Name: Рейтинг
 *
 * Отдельная доска, а не каталог с другой сортировкой. Три показателя — оценка,
 * просмотры и число глав, — три окна времени и фильтр по жанру. Первая тройка
 * стоит на подиуме, остальные идут строками с местом и полосой относительно
 * лидера.
 *
 * Оценка считается взвешенно: тайтл с одной пятёркой не обгоняет тайтл с сотней
 * голосов и средней 4,8 — подробности в inc/ranking.php.
 *
 * @package XI_Novels
 */

get_header();

$xin_state  = xin_ranking_state();
$xin_metric = $xin_state['metric'];
$xin_board  = xin_ranking_board( $xin_metric, $xin_state['period'], $xin_state['genre'], 50 );
$xin_top    = (float) ( $xin_board ? $xin_board[0]['score'] : 0 );

$xin_units = array(
	'rating'   => __( 'оценка', 'xi-novels' ),
	'views'    => __( 'просмотров', 'xi-novels' ),
	'chapters' => __( 'глав', 'xi-novels' ),
);
$xin_unit = isset( $xin_units[ $xin_metric ] ) ? $xin_units[ $xin_metric ] : '';

$xin_genres = get_terms( array(
	'taxonomy'   => 'genre',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 14,
) );
?>

<div class="xin-aurora">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<span class="xin-eyebrow"><?php esc_html_e( 'доска', 'xi-novels' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p class="xin-pagehead__sub">
				<?php esc_html_e( 'Лучшее на площадке — по оценке читателей, по числу просмотров и по объёму. Пересчитывается автоматически.', 'xi-novels' ); ?>
			</p>
		</header>
	</div>
</div>

<div class="xin-wrap xin-rank">

	<div class="xin-rank__controls">
		<div class="xin-segmented" role="tablist" aria-label="<?php esc_attr_e( 'Показатель', 'xi-novels' ); ?>">
			<?php foreach ( xin_ranking_metrics() as $xin_key => $xin_label ) : ?>
				<a role="tab" aria-selected="<?php echo $xin_metric === $xin_key ? 'true' : 'false'; ?>"
					class="<?php echo $xin_metric === $xin_key ? 'is-active' : ''; ?>"
					href="<?php echo esc_url( xin_ranking_url( array( 'metric' => $xin_key ) ) ); ?>"><?php echo esc_html( $xin_label ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="xin-segmented" role="tablist" aria-label="<?php esc_attr_e( 'Период', 'xi-novels' ); ?>">
			<?php foreach ( xin_ranking_periods() as $xin_key => $xin_period ) : ?>
				<a role="tab" aria-selected="<?php echo $xin_state['period'] === $xin_key ? 'true' : 'false'; ?>"
					class="<?php echo $xin_state['period'] === $xin_key ? 'is-active' : ''; ?>"
					href="<?php echo esc_url( xin_ranking_url( array( 'period' => $xin_key ) ) ); ?>"><?php echo esc_html( $xin_period[0] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
		<div class="xin-genres xin-mt-2">
			<a class="xin-genre-chip <?php echo $xin_state['genre'] ? '' : 'is-active'; ?>" href="<?php echo esc_url( xin_ranking_url( array( 'genre' => '' ) ) ); ?>">
				<?php esc_html_e( 'Все жанры', 'xi-novels' ); ?>
			</a>
			<?php foreach ( $xin_genres as $xin_genre ) : ?>
				<a class="xin-genre-chip <?php echo $xin_state['genre'] === $xin_genre->slug ? 'is-active' : ''; ?>"
					href="<?php echo esc_url( xin_ranking_url( array( 'genre' => $xin_genre->slug ) ) ); ?>"><?php echo esc_html( $xin_genre->name ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $xin_board ) : ?>

		<div class="xin-empty" style="padding-block:70px">
			<h2><?php esc_html_e( 'Здесь пока пусто', 'xi-novels' ); ?></h2>
			<p><?php esc_html_e( 'Ни один тайтл не набрал показателей для этой доски. Попробуйте другой период или снимите фильтр по жанру.', 'xi-novels' ); ?></p>
			<div class="xin-flex xin-flex-wrap xin-mt-2" style="justify-content:center">
				<a class="btn btn-outline" href="<?php echo esc_url( xin_ranking_url( array( 'period' => 'all', 'genre' => '' ) ) ); ?>">
					<?php esc_html_e( 'За всё время, все жанры', 'xi-novels' ); ?>
				</a>
				<a class="btn btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
					<?php esc_html_e( 'В каталог', 'xi-novels' ); ?>
				</a>
			</div>
		</div>

	<?php else : ?>

		<?php $xin_podium = array_slice( $xin_board, 0, 3 ); ?>
		<div class="xin-podium xin-mt-2">
			<?php foreach ( $xin_podium as $xin_i => $xin_row ) : ?>
				<?php
				$xin_id     = $xin_row['id'];
				$xin_cover  = xin_cover_url( $xin_id, 'xin-cover' );
				$xin_share  = $xin_top > 0 ? max( 6, min( 100, round( $xin_row['score'] / $xin_top * 100 ) ) ) : 0;
				?>
				<a class="xin-podium__item" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
					<span class="xin-podium__place"><?php echo (int) ( $xin_i + 1 ); ?></span>
					<span class="xin-podium__cover">
						<?php if ( $xin_cover ) : ?>
							<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</span>
					<span style="min-width:0">
						<span class="xin-podium__title"><?php echo esc_html( get_the_title( $xin_id ) ); ?></span>
						<span class="xin-podium__author"><?php echo esc_html( xin_novel_author( $xin_id ) ); ?></span>
						<span class="xin-rank__value">
							<b><?php echo esc_html( $xin_row['display'] ); ?></b> <?php echo esc_html( $xin_unit ); ?>
							<?php if ( 'rating' === $xin_metric && $xin_row['votes'] ) : ?>
								<small><?php printf( esc_html__( '· голосов: %s', 'xi-novels' ), esc_html( number_format_i18n( $xin_row['votes'] ) ) ); ?></small>
							<?php endif; ?>
						</span>
						<span class="xin-podium__bar"><i style="width:<?php echo (int) $xin_share; ?>%"></i></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php $xin_rest = array_slice( $xin_board, 3 ); ?>
		<?php if ( $xin_rest ) : ?>
			<ol class="xin-rank__list">
				<?php foreach ( $xin_rest as $xin_i => $xin_row ) : ?>
					<?php
					$xin_id    = $xin_row['id'];
					$xin_cover = xin_cover_url( $xin_id, 'xin-cover-sm' );
					$xin_share = $xin_top > 0 ? max( 4, min( 100, round( $xin_row['score'] / $xin_top * 100 ) ) ) : 0;
					?>
					<li>
						<a class="xin-rankrow" href="<?php echo esc_url( get_permalink( $xin_id ) ); ?>">
							<span class="xin-rankrow__num"><?php echo (int) ( $xin_i + 4 ); ?></span>
							<span class="xin-rankrow__cover">
								<?php if ( $xin_cover ) : ?>
									<img src="<?php echo esc_url( $xin_cover ); ?>" alt="" loading="lazy">
								<?php endif; ?>
							</span>
							<span class="xin-rankrow__body">
								<span class="xin-rankrow__title"><?php echo esc_html( get_the_title( $xin_id ) ); ?></span>
								<span class="xin-rankrow__meta">
									<span><?php echo esc_html( xin_novel_author( $xin_id ) ); ?></span>
								</span>
								<span class="xin-rank__bar"><i style="width:<?php echo (int) $xin_share; ?>%"></i></span>
							</span>
							<span class="xin-rank__score">
								<b><?php echo esc_html( $xin_row['display'] ); ?></b>
								<small><?php echo esc_html( $xin_unit ); ?></small>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php if ( 'rating' === $xin_metric ) : ?>
			<p class="xin-rank__note">
				<?php
				printf(
					/* translators: %s: number of votes. */
					esc_html__( 'Оценка взвешенная: пока у тайтла меньше %s голосов, его средняя подтягивается к средней по площадке. Так одна пятёрка не обгоняет сотню честных оценок.', 'xi-novels' ),
					esc_html( number_format_i18n( xin_ranking_weight() ) )
				);
				?>
			</p>
		<?php endif; ?>

	<?php endif; ?>
</div>

<?php
get_footer();
