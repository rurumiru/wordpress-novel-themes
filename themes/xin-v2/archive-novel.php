<?php
/**
 * Каталог: фильтры слева, сетка обложек справа.
 *
 * Используется и для архива тайтлов, и для страниц жанров и тегов —
 * фильтры в обоих случаях те же.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$xv_is_tax   = is_tax();
$xv_term     = $xv_is_tax ? get_queried_object() : null;
$xv_base     = $xv_is_tax ? get_term_link( $xv_term ) : get_post_type_archive_link( 'novel' );
$xv_sort     = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'new';
$xv_statuses = get_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => true ) );
$xv_genres   = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 16 ) );
$xv_tags     = get_terms( array( 'taxonomy' => 'novel_tag', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 14 ) );
$xv_picked   = array(
	'status' => isset( $_GET['status'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['status'] ) ) : array(),
	'genre'  => isset( $_GET['genre'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['genre'] ) ) : array(),
	'tag'    => isset( $_GET['tag'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['tag'] ) ) : array(),
);
$xv_stats = xv_site_stats();
?>

<div class="xv-section xv-section-wide xv-kakao-catalog">
	<?php get_template_part("template-parts/discovery"); ?><header class="xv-page-head">
		<h1 class="xv-page-title"><?php echo esc_html( $xv_term ? $xv_term->name : __( 'Каталог', 'xin-v2' ) ); ?></h1>
		<p class="xv-page-lede">
			<?php
			if ( $xv_term && $xv_term->description ) {
				echo esc_html( $xv_term->description );
			} else {
				esc_html_e( 'Все тайтлы платформы. Отфильтруйте по жанру, статусу или тегу — и найдите следующую историю.', 'xin-v2' );
			}
			?>
		</p>
		<p class="xv-page-facts">
			<?php
			printf(
				/* translators: 1: число тайтлов, 2: число жанров. */
				esc_html__( '%1$s тайтлов · %2$s жанров', 'xin-v2' ),
				esc_html( number_format_i18n( $xv_stats['novels'] ) ),
				esc_html( number_format_i18n( $xv_stats['genres'] ) )
			);
			?>
		</p>
	</header>

	<nav class="xv-catalog-genres" aria-label="<?php esc_attr_e('Жанры', 'xin-v2'); ?>"><a href="<?php echo esc_url(get_post_type_archive_link("novel")); ?>" class="<?php echo !$xv_is_tax && !$xv_picked["genre"] ? "active" : ""; ?>"><?php esc_html_e('Все', 'xin-v2'); ?></a><?php if($xv_genres && !is_wp_error($xv_genres)) foreach($xv_genres as $genre) : ?><a href="<?php echo esc_url(get_term_link($genre)); ?>" class="<?php echo $xv_term && $xv_term->term_id===$genre->term_id ? "active" : ""; ?>"><?php echo esc_html($genre->name); ?></a><?php endforeach; ?></nav><div class="xv-catalog">
		<?php xv_banner_block( 'catalog', 1 ); ?>
		<details class="xv-catalog-filter-panel"<?php if(!empty($_GET["q"])||!empty($_GET["year"])||array_filter($xv_picked))echo " open"; ?>><summary><?php xv_the_icon("settings"); esc_html_e('Фильтры и поиск', 'xin-v2'); ?></summary><form class="xv-filters" method="get" action="<?php echo esc_url( $xv_base ); ?>">
			<div class="xv-filter-group">
				<h2 class="xv-filter-title"><?php esc_html_e( 'Поиск', 'xin-v2' ); ?></h2>
				<input class="xv-input" type="search" name="q" value="<?php echo esc_attr( isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '' ); ?>" placeholder="<?php esc_attr_e( 'Название или автор…', 'xin-v2' ); ?>" aria-label="<?php esc_attr_e( 'Поиск по каталогу', 'xin-v2' ); ?>">
			</div>

			<?php if ( $xv_statuses && ! is_wp_error( $xv_statuses ) ) : ?>
				<div class="xv-filter-group">
					<h2 class="xv-filter-title"><?php esc_html_e( 'Статус', 'xin-v2' ); ?></h2>
					<div class="xv-filter-list">
						<?php foreach ( $xv_statuses as $xv_item ) : ?>
							<label class="xv-filter-item">
								<input class="xv-checkbox" type="checkbox" name="status[]" value="<?php echo esc_attr( $xv_item->slug ); ?>" <?php checked( in_array( $xv_item->slug, $xv_picked['status'], true ) ); ?>>
								<span><?php echo esc_html( $xv_item->name ); ?></span>
								<span class="xv-filter-count"><?php echo esc_html( (int) $xv_item->count ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="xv-filter-group">
				<h2 class="xv-filter-title"><?php esc_html_e( 'Год выхода', 'xin-v2' ); ?></h2>
				<input class="xv-input" type="text" name="year" inputmode="numeric" value="<?php echo esc_attr( isset( $_GET['year'] ) ? sanitize_text_field( wp_unslash( $_GET['year'] ) ) : '' ); ?>" placeholder="<?php echo esc_attr( wp_date( 'Y' ) ); ?>" aria-label="<?php esc_attr_e( 'Год выхода', 'xin-v2' ); ?>">
			</div>

			<?php if ( $xv_genres && ! is_wp_error( $xv_genres ) && ! is_tax( 'genre' ) ) : ?>
				<div class="xv-filter-group">
					<h2 class="xv-filter-title"><?php esc_html_e( 'Жанры', 'xin-v2' ); ?></h2>
					<div class="xv-filter-list">
						<?php foreach ( $xv_genres as $xv_item ) : ?>
							<label class="xv-filter-item">
								<input class="xv-checkbox" type="checkbox" name="genre[]" value="<?php echo esc_attr( $xv_item->slug ); ?>" <?php checked( in_array( $xv_item->slug, $xv_picked['genre'], true ) ); ?>>
								<span><?php echo esc_html( $xv_item->name ); ?></span>
								<span class="xv-filter-count"><?php echo esc_html( (int) $xv_item->count ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $xv_tags && ! is_wp_error( $xv_tags ) && ! is_tax( 'novel_tag' ) ) : ?>
				<div class="xv-filter-group">
					<h2 class="xv-filter-title"><?php esc_html_e( 'Теги', 'xin-v2' ); ?></h2>
					<div class="xv-filter-list">
						<?php foreach ( $xv_tags as $xv_item ) : ?>
							<label class="xv-filter-item">
								<input class="xv-checkbox" type="checkbox" name="tag[]" value="<?php echo esc_attr( $xv_item->slug ); ?>" <?php checked( in_array( $xv_item->slug, $xv_picked['tag'], true ) ); ?>>
								<span><?php echo esc_html( $xv_item->name ); ?></span>
								<span class="xv-filter-count"><?php echo esc_html( (int) $xv_item->count ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<input type="hidden" name="sort" value="<?php echo esc_attr( $xv_sort ); ?>">

			<div class="xv-filter-actions">
				<button type="submit" class="xv-btn primary sm"><?php esc_html_e( 'Применить', 'xin-v2' ); ?></button>
				<a class="xv-btn sm" href="<?php echo esc_url( $xv_base ); ?>"><?php esc_html_e( 'Сбросить', 'xin-v2' ); ?></a>
			</div>
		</form></details>

		<div>
			<div class="xv-catalog-bar">
				<span class="xv-catalog-count">
					<?php
					printf(
						/* translators: %s: число найденных тайтлов. */
						esc_html__( 'Найдено: %s', 'xin-v2' ),
						esc_html( number_format_i18n( (int) $GLOBALS['wp_query']->found_posts ) )
					);
					?>
				</span>

				<div class="xv-catalog-sort">
					<?php
					$xv_sorts = array(
						'new'     => __( 'Сначала новые', 'xin-v2' ),
						'popular' => __( 'По просмотрам', 'xin-v2' ),
						'rating'  => __( 'По оценке', 'xin-v2' ),
						'updated' => __( 'По обновлению', 'xin-v2' ),
						'title'   => __( 'По названию', 'xin-v2' ),
					);
					?>
					<label class="xv-label-hint" for="xv-sort"><?php esc_html_e( 'Порядок', 'xin-v2' ); ?></label>
					<select class="xv-select" id="xv-sort" data-xv-sort>
						<?php foreach ( $xv_sorts as $xv_key => $xv_label ) : ?>
							<option value="<?php echo esc_url( add_query_arg( 'sort', $xv_key ) ); ?>" <?php selected( $xv_sort, $xv_key ); ?>><?php echo esc_html( $xv_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="xv-catalog-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						xv_novel_card( get_the_ID() );
					endwhile;
					?>
				</div>

				<?php xv_pagination(); ?>
			<?php else : ?>
				<div class="xv-empty">
					<p class="xv-empty-title"><?php esc_html_e( 'Ничего не нашлось', 'xin-v2' ); ?></p>
					<p class="xv-empty-text"><?php esc_html_e( 'Попробуйте снять часть фильтров.', 'xin-v2' ); ?></p>
					<a class="xv-btn" href="<?php echo esc_url( $xv_base ); ?>"><?php esc_html_e( 'Сбросить фильтры', 'xin-v2' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
