<?php

global $wp_query;

$xin_sort   = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'date';
$xin_status = isset( $_GET['status'] ) ? sanitize_title( wp_unslash( $_GET['status'] ) ) : '';
$xin_term   = is_tax() ? get_queried_object() : null;
$xin_base   = $xin_term ? get_term_link( $xin_term ) : get_post_type_archive_link( 'novel' );

$xin_sorts = array(
	'date'    => __( 'Сначала новые', 'xi-novels' ),
	'popular' => __( 'По просмотрам', 'xi-novels' ),
	'rating'  => __( 'По оценке', 'xi-novels' ),
	'updated' => __( 'По обновлению', 'xi-novels' ),
	'title'   => __( 'По алфавиту', 'xi-novels' ),
);

$xin_statuses = get_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );
$xin_genres   = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 18 ) );
?>

<div class="xin-aurora">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<span class="xin-eyebrow">
				<?php echo $xin_term ? esc_html( get_taxonomy( $xin_term->taxonomy )->labels->singular_name ) : esc_html__( 'библиотека', 'xi-novels' ); ?>
			</span>
			<h1><?php echo $xin_term ? esc_html( $xin_term->name ) : esc_html__( 'Каталог новелл', 'xi-novels' ); ?></h1>
			<p class="xin-pagehead__sub">
				<?php
				if ( $xin_term && $xin_term->description ) {
					echo esc_html( $xin_term->description );
				} else {
					esc_html_e( 'Ранобэ, веб-новеллы и авторские переводы — весь каталог площадки.', 'xi-novels' );
				}
				?>
			</p>
		</header>
	</div>
</div>

<div class="xin-wrap">

	<?php if ( ! is_wp_error( $xin_genres ) && $xin_genres ) : ?>
		<div class="xin-genres xin-mt-2" id="genres">
			<a class="xin-genre-chip <?php echo is_post_type_archive( 'novel' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>">
				<?php esc_html_e( 'Все', 'xi-novels' ); ?>
			</a>
			<?php foreach ( $xin_genres as $xin_genre ) : ?>
				<a class="xin-genre-chip <?php echo ( $xin_term && (int) $xin_term->term_id === (int) $xin_genre->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>">
					<?php echo esc_html( $xin_genre->name ); ?><b><?php echo (int) $xin_genre->count; ?></b>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form class="xin-filters xin-mt-2" method="get" action="<?php echo esc_url( $xin_base ); ?>">
		<span class="xin-filters__label"><?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Фильтр', 'xi-novels' ); ?></span>

		<select class="form-select form-select-pill form-select-sm w-auto" name="sort" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Сортировка', 'xi-novels' ); ?>">
			<?php foreach ( $xin_sorts as $xin_key => $xin_label ) : ?>
				<option value="<?php echo esc_attr( $xin_key ); ?>" <?php selected( $xin_sort, $xin_key ); ?>><?php echo esc_html( $xin_label ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php if ( ! is_wp_error( $xin_statuses ) && $xin_statuses && ! is_tax( 'novel_status' ) ) : ?>
			<select class="form-select form-select-pill form-select-sm w-auto" name="status" onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Статус', 'xi-novels' ); ?>">
				<option value=""><?php esc_html_e( 'Любой статус', 'xi-novels' ); ?></option>
				<?php foreach ( $xin_statuses as $xin_st ) : ?>
					<option value="<?php echo esc_attr( $xin_st->slug ); ?>" <?php selected( $xin_status, $xin_st->slug ); ?>><?php echo esc_html( $xin_st->name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>

		<noscript><button type="submit" class="btn btn-sm btn-primary"><?php esc_html_e( 'Применить', 'xi-novels' ); ?></button></noscript>

		<span class="xin-filters__count">
			<?php
			printf(
				esc_html( xin_plural( $wp_query->found_posts, __( '%s тайтл', 'xi-novels' ), __( '%s тайтла', 'xi-novels' ), __( '%s тайтлов', 'xi-novels' ) ) ),
				esc_html( number_format_i18n( $wp_query->found_posts ) )
			);
			?>
		</span>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="xin-grid xin-grid--6 xin-mt-2">
			<?php
			while ( have_posts() ) :
				the_post();
				xin_novel_card( get_the_ID() );
			endwhile;
			?>
		</div>
		<?php xin_pagination(); ?>
	<?php else : ?>
		<div class="xin-empty">
			<?php xin_the_icon( 'book' ); ?>
			<h2><?php esc_html_e( 'Ничего не найдено', 'xi-novels' ); ?></h2>
			<p><?php esc_html_e( 'Попробуйте снять фильтры или загляните в другой жанр.', 'xi-novels' ); ?></p>
			<a class="btn btn-outline xin-mt-2" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Сбросить фильтры', 'xi-novels' ); ?></a>
		</div>
	<?php endif; ?>
</div>
