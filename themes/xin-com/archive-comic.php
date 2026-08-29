<?php
/**
 * Каталог комиксов: /comics/catalog/.
 *
 * Сетка постеров вместо списка карточек с описанием: у комикса решение о
 * чтении принимается по обложке, и на экран их должно помещаться заметно
 * больше, чем новелл.
 *
 * @package XIN-Com
 */

get_header();

$xin_sort   = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$xin_base   = xin_section_catalog_link( 'comic' );
$xin_genres = get_terms( array(
	'taxonomy'   => 'genre',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 18,
) );

$xin_sorts = array(
	'date'    => __( 'Сначала новые', 'xin-com' ),
	'popular' => __( 'По просмотрам', 'xin-com' ),
	'rating'  => __( 'По оценке', 'xin-com' ),
	'updated' => __( 'По обновлению', 'xin-com' ),
	'title'   => __( 'По алфавиту', 'xin-com' ),
);
?>

<div class="xin-aurora xin-aurora--comics">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<nav class="xin-crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'xin-com' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'xin-com' ); ?></a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( xin_section_home_link( 'comic' ) ); ?>"><?php esc_html_e( 'Комиксы', 'xin-com' ); ?></a>
			</nav>
			<span class="xin-eyebrow"><?php esc_html_e( 'библиотека', 'xin-com' ); ?></span>
			<h1><?php esc_html_e( 'Каталог комиксов', 'xin-com' ); ?></h1>
			<p class="xin-pagehead__sub"><?php esc_html_e( 'Манхва, маньхуа, манга и веб-комиксы — весь раздел целиком.', 'xin-com' ); ?></p>
		</header>
	</div>
</div>

<div class="xin-wrap">

	<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
		<div class="xin-genres xin-mt-2">
			<a class="xin-genre-chip is-active" href="<?php echo esc_url( $xin_base ); ?>"><?php esc_html_e( 'Все', 'xin-com' ); ?></a>
			<?php foreach ( $xin_genres as $xin_genre ) : ?>
				<a class="xin-genre-chip" href="<?php echo esc_url( add_query_arg( 'genre', $xin_genre->slug, $xin_base ) ); ?>">
					<?php echo esc_html( $xin_genre->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form class="xin-filters xin-mt-2" method="get" action="<?php echo esc_url( $xin_base ); ?>">
		<label class="xin-filters__field">
			<span class="xin-filters__label"><?php esc_html_e( 'Сортировка', 'xin-com' ); ?></span>
			<select name="sort" onchange="this.form.submit()">
				<?php foreach ( $xin_sorts as $xin_key => $xin_label ) : ?>
					<option value="<?php echo esc_attr( $xin_key ); ?>" <?php selected( $xin_sort, $xin_key ); ?>><?php echo esc_html( $xin_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<noscript><button class="btn btn-outline btn-sm" type="submit"><?php esc_html_e( 'Применить', 'xin-com' ); ?></button></noscript>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="xin-cm-grid xin-cm-grid--wide xin-mt-2">
			<?php
			while ( have_posts() ) :
				the_post();
				xin_comic_card( get_the_ID() );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination( array(
			'mid_size'  => 1,
			'prev_text' => esc_html__( 'Назад', 'xin-com' ),
			'next_text' => esc_html__( 'Вперёд', 'xin-com' ),
		) );
		?>
	<?php else : ?>
		<div class="xin-glass xin-cm-empty xin-mt-2">
			<?php xin_the_icon( 'layers' ); ?>
			<h2><?php esc_html_e( 'В каталоге пока пусто', 'xin-com' ); ?></h2>
			<p class="xin-muted"><?php esc_html_e( 'Ни один тайтл ещё не отмечен форматом «Комикс».', 'xin-com' ); ?></p>
		</div>
	<?php endif; ?>

</div>

<?php get_footer(); ?>
