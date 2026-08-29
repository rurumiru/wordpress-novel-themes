<?php

global $wp_query;

$xin_sort   = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'date';
$xin_status = isset( $_GET['status'] ) ? sanitize_title( wp_unslash( $_GET['status'] ) ) : '';
$xin_term   = is_tax() ? get_queried_object() : null;
$xin_base   = $xin_term ? get_term_link( $xin_term ) : get_post_type_archive_link( 'novel' );

$xin_sorts = array(
	'date'    => __( 'Сначала новые', 'xin-com' ),
	'popular' => __( 'По просмотрам', 'xin-com' ),
	'rating'  => __( 'По оценке', 'xin-com' ),
	'updated' => __( 'По обновлению', 'xin-com' ),
	'title'   => __( 'По алфавиту', 'xin-com' ),
);

$xin_statuses = get_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );
$xin_genres   = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 18 ) );
?>

<div class="xin-aurora">
	<div class="xin-wrap">
		<header class="xin-pagehead">
			<?php xin_breadcrumbs(); ?>
			<span class="xin-eyebrow">
				<?php echo $xin_term ? esc_html( get_taxonomy( $xin_term->taxonomy )->labels->singular_name ) : esc_html__( 'библиотека', 'xin-com' ); ?>
			</span>
			<h1><?php echo $xin_term ? esc_html( $xin_term->name ) : esc_html__( 'Каталог новелл', 'xin-com' ); ?></h1>
			<p class="xin-pagehead__sub">
				<?php
				if ( $xin_term && $xin_term->description ) {
					echo esc_html( $xin_term->description );
				} else {
					esc_html_e( 'Ранобэ, веб-новеллы и авторские переводы — весь каталог площадки.', 'xin-com' );
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
				<?php esc_html_e( 'Все', 'xin-com' ); ?>
			</a>
			<?php foreach ( $xin_genres as $xin_genre ) : ?>
				<a class="xin-genre-chip <?php echo ( $xin_term && (int) $xin_term->term_id === (int) $xin_genre->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $xin_genre ) ); ?>">
					<?php echo esc_html( $xin_genre->name ); ?><b><?php echo (int) $xin_genre->count; ?></b>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<form class="xin-filters xin-mt-2" method="get" action="<?php echo esc_url( $xin_base ); ?>">
		<?php
		/*
		 * A GET form throws away whatever query string the action URL carries, so on a
		 * site with plain permalinks — where the archive is /?post_type=novel — picking
		 * a sort order used to land on the front page instead of the filtered catalog.
		 * Carrying the base query back in as hidden fields keeps the target intact.
		 */
		xin_hidden_query_fields( $xin_base, array( 'sort', 'status' ) );
		?>
		<span class="xin-filters__label"><?php xin_the_icon( 'filter' ); ?><?php esc_html_e( 'Фильтр', 'xin-com' ); ?></span>

		<select class="form-select form-select-pill form-select-sm w-auto" name="sort" data-xin-select onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Сортировка', 'xin-com' ); ?>">
			<?php foreach ( $xin_sorts as $xin_key => $xin_label ) : ?>
				<option value="<?php echo esc_attr( $xin_key ); ?>" <?php selected( $xin_sort, $xin_key ); ?>><?php echo esc_html( $xin_label ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php if ( ! is_wp_error( $xin_statuses ) && $xin_statuses && ! is_tax( 'novel_status' ) ) : ?>
			<select class="form-select form-select-pill form-select-sm w-auto" name="status" data-xin-select onchange="this.form.submit()" aria-label="<?php esc_attr_e( 'Статус', 'xin-com' ); ?>">
				<option value=""><?php esc_html_e( 'Любой статус', 'xin-com' ); ?></option>
				<?php foreach ( $xin_statuses as $xin_st ) : ?>
					<option value="<?php echo esc_attr( $xin_st->slug ); ?>" <?php selected( $xin_status, $xin_st->slug ); ?>><?php echo esc_html( $xin_st->name ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>

		<noscript><button type="submit" class="btn btn-sm btn-primary"><?php esc_html_e( 'Применить', 'xin-com' ); ?></button></noscript>

		<span class="xin-filters__count">
			<?php
			printf(
				esc_html( xin_plural( $wp_query->found_posts, __( '%s тайтл', 'xin-com' ), __( '%s тайтла', 'xin-com' ), __( '%s тайтлов', 'xin-com' ) ) ),
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
			<h2><?php esc_html_e( 'Ничего не найдено', 'xin-com' ); ?></h2>
			<p><?php esc_html_e( 'Попробуйте снять фильтры или загляните в другой жанр.', 'xin-com' ); ?></p>
			<a class="btn btn-outline xin-mt-2" href="<?php echo esc_url( get_post_type_archive_link( 'novel' ) ); ?>"><?php esc_html_e( 'Сбросить фильтры', 'xin-com' ); ?></a>
		</div>
	<?php endif; ?>
</div>
