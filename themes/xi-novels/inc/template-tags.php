<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_brand() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$name  = get_bloginfo( 'name' );
	$parts = explode( ' ', $name, 2 );

	printf(
		'<span class="xin-brand__text">%s%s</span>',
		esc_html( $parts[0] ),
		isset( $parts[1] ) ? ' <b>' . esc_html( $parts[1] ) . '</b>' : ''
	);
}
function xin_icon_path( $name ) {
	$icons = xin_icon_set();
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

function xin_icon( $name, $class = '', $filled = false ) {
	$brands = xin_brand_icon_set();

	if ( isset( $brands[ $name ] ) ) {
		return sprintf(
			'<svg class="xin-icon %1$s" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>',
			esc_attr( $class ),
			$brands[ $name ]
		);
	}

	$path = xin_icon_path( $name );
	if ( ! $path ) {
		return '';
	}

	return sprintf(
		'<svg class="xin-icon %1$s" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $class ),
		$filled ? 'currentColor' : 'none',
		$path
	);
}

function xin_the_icon( $name, $class = '', $filled = false ) {
	echo xin_icon( $name, $class, $filled ); 
}

function xin_num( $n ) {
	$n = (float) $n;
	if ( $n >= 1000000 ) {
		return str_replace( '.0', '', number_format( $n / 1000000, 1, ',', '' ) ) . 'M';
	}
	if ( $n >= 1000 ) {
		return str_replace( ',0', '', number_format( $n / 1000, 1, ',', '' ) ) . 'K';
	}
	return number_format( $n, 0, ',', ' ' );
}

function xin_ago( $timestamp ) {
	$diff = time() - (int) $timestamp;

	if ( $diff < 60 ) {
		return __( 'только что', 'xi-novels' );
	}

	$units = array(
		array( YEAR_IN_SECONDS, __( 'год', 'xi-novels' ), __( 'года', 'xi-novels' ), __( 'лет', 'xi-novels' ) ),
		array( MONTH_IN_SECONDS, __( 'месяц', 'xi-novels' ), __( 'месяца', 'xi-novels' ), __( 'месяцев', 'xi-novels' ) ),
		array( DAY_IN_SECONDS, __( 'день', 'xi-novels' ), __( 'дня', 'xi-novels' ), __( 'дней', 'xi-novels' ) ),
		array( HOUR_IN_SECONDS, __( 'час', 'xi-novels' ), __( 'часа', 'xi-novels' ), __( 'часов', 'xi-novels' ) ),
		array( MINUTE_IN_SECONDS, __( 'минуту', 'xi-novels' ), __( 'минуты', 'xi-novels' ), __( 'минут', 'xi-novels' ) ),
	);

	foreach ( $units as $unit ) {
		if ( $diff >= $unit[0] ) {
			$n = (int) floor( $diff / $unit[0] );
			
			return sprintf( __( '%1$d %2$s назад', 'xi-novels' ), $n, xin_plural( $n, $unit[1], $unit[2], $unit[3] ) );
		}
	}

	return __( 'только что', 'xi-novels' );
}

function xin_plural( $n, $one, $few, $many ) {
	$n10  = $n % 10;
	$n100 = $n % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return $one;
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 10 || $n100 >= 20 ) ) {
		return $few;
	}
	return $many;
}

function xin_chapter_label( $chapter_id ) {
	$num = xin_chapter_number( $chapter_id );
	if ( null === $num ) {
		return '';
	}
	return rtrim( rtrim( number_format( $num, 1, '.', '' ), '0' ), '.' );
}

function xin_rating( $novel_id ) {
	return array(
		'value' => round( (float) get_post_meta( $novel_id, '_xin_rating', true ), 1 ),
		'count' => (int) get_post_meta( $novel_id, '_xin_rating_count', true ),
	);
}

function xin_novel_status( $novel_id ) {
	$terms = get_the_terms( $novel_id, 'novel_status' );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return null;
	}
	return $terms[0];
}

function xin_cover_url( $post_id, $size = 'xin-cover' ) {
	$url = get_the_post_thumbnail_url( $post_id, $size );
	return $url ? $url : '';
}

function xin_background_url( $novel_id, $size = 'xin-banner' ) {
	$id = (int) get_post_meta( $novel_id, '_xin_background', true );
	if ( ! $id ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $id, $size );
	return $src ? $src[0] : '';
}

function xin_novel_author( $novel_id ) {
	$manual = get_post_meta( $novel_id, '_xin_author_name', true );
	if ( $manual ) {
		return $manual;
	}
	return get_the_author_meta( 'display_name', get_post_field( 'post_author', $novel_id ) );
}

function xin_novel_card( $novel_id, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'rank'        => 0,
		'show_author' => true,
		'show_meta'   => true,
		'class'       => '',
	) );

	$cover  = xin_cover_url( $novel_id );
	$adult  = (bool) get_post_meta( $novel_id, '_xin_adult', true );
	$rating = xin_rating( $novel_id );
	$status = xin_novel_status( $novel_id );
	$count  = xin_chapter_count( $novel_id );
	$title  = get_the_title( $novel_id );
	?>
	<article class="xin-novel <?php echo esc_attr( $args['class'] ); ?><?php echo $adult ? ' xin-novel--blur' : ''; ?>">
		<a class="xin-novel__cover<?php echo $cover ? '' : ' xin-novel__cover--empty'; ?>" href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" width="320" height="480">
			<?php else : ?>
				<?php xin_the_icon( 'book' ); ?>
			<?php endif; ?>

			<div class="xin-novel__badges">
				<?php if ( $adult ) : ?>
					<span class="xin-badge xin-badge--adult">18+</span>
				<?php endif; ?>
				<?php if ( $status && 'completed' === $status->slug ) : ?>
					<span class="xin-badge xin-badge--primary"><?php echo esc_html( $status->name ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $args['rank'] ) : ?>
				<span class="xin-novel__rank<?php echo $args['rank'] <= 3 ? ' xin-novel__rank--gold' : ''; ?>"><?php echo (int) $args['rank']; ?></span>
			<?php endif; ?>

			<?php if ( $count ) : ?>
				<span class="xin-novel__chip"><?php xin_the_icon( 'book-open' ); ?><?php echo (int) $count; ?></span>
			<?php endif; ?>
		</a>

		<?php xin_fav_button( $novel_id ); ?>

		<div class="xin-novel__body">
			<h3 class="xin-novel__title"><a href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>"><?php echo esc_html( $title ); ?></a></h3>
			<?php if ( $args['show_author'] ) : ?>
				<div class="xin-novel__author"><?php echo esc_html( xin_novel_author( $novel_id ) ); ?></div>
			<?php endif; ?>
			<?php if ( $args['show_meta'] ) : ?>
				<div class="xin-novel__meta">
					<?php if ( $rating['count'] ) : ?>
						<span class="is-rating"><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $rating['value'], 1, ',', '' ) ); ?></span>
					<?php endif; ?>
					<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $novel_id ) ) ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

function xin_fav_button( $novel_id, $inline = false ) {
	$data = array(
		'id'    => (int) $novel_id,
		'title' => get_the_title( $novel_id ),
		'url'   => get_permalink( $novel_id ),
		'cover' => xin_cover_url( $novel_id, 'xin-cover-sm' ),
	);
	printf(
		'<button type="button" class="xin-fav%s" data-xin-fav="%s" aria-label="%s" title="%s">%s%s</button>',
		$inline ? ' xin-fav--inline' : '',
		esc_attr( wp_json_encode( $data ) ),
		esc_attr__( 'В библиотеку', 'xi-novels' ),
		esc_attr__( 'В библиотеку', 'xi-novels' ),
		xin_icon( 'bookmark' ), 
		$inline ? '<span>' . esc_html__( 'В библиотеку', 'xi-novels' ) . '</span>' : ''
	);
}

function xin_novel_showcase( $novel_id ) {
	$bg    = xin_background_url( $novel_id, 'xin-banner' );
	$cover = xin_cover_url( $novel_id, 'xin-cover' );
	$img   = $bg ? $bg : $cover;
	$count = xin_chapter_count( $novel_id );
	?>
	<a class="xin-showcase" href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>">
		<?php if ( $img ) : ?>
			<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title( $novel_id ) ); ?>" loading="lazy">
		<?php endif; ?>
		<div class="xin-showcase__body">
			<h3><?php echo esc_html( get_the_title( $novel_id ) ); ?></h3>
			<div class="xin-novel__meta">
				<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $novel_id ) ) ); ?></span>
				<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) $count; ?></span>
			</div>
		</div>
	</a>
	<?php
}

function xin_rank_row( $novel_id, $rank ) {
	$cover  = xin_cover_url( $novel_id, 'xin-cover-sm' );
	$rating = xin_rating( $novel_id );
	?>
	<a class="xin-rankrow" href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>">
		<span class="xin-rankrow__num"><?php echo (int) $rank; ?></span>
		<span class="xin-rankrow__cover">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy">
			<?php endif; ?>
		</span>
		<span class="xin-rankrow__body">
			<span class="xin-rankrow__title"><?php echo esc_html( get_the_title( $novel_id ) ); ?></span>
			<span class="xin-rankrow__meta">
				<span><?php xin_the_icon( 'eye' ); ?><?php echo esc_html( xin_num( xin_get_views( $novel_id ) ) ); ?></span>
				<?php if ( $rating['count'] ) : ?>
					<span class="xin-gold"><?php xin_the_icon( 'star', '', true ); ?><?php echo esc_html( number_format( $rating['value'], 1, ',', '' ) ); ?></span>
				<?php endif; ?>
				<span><?php xin_the_icon( 'book-open' ); ?><?php echo (int) xin_chapter_count( $novel_id ); ?></span>
			</span>
		</span>
	</a>
	<?php
}

function xin_chapter_card( $chapter_id ) {
	$novel_id = xin_chapter_novel_id( $chapter_id );
	$cover    = $novel_id ? xin_cover_url( $novel_id, 'xin-cover-sm' ) : '';
	$locked   = (bool) get_post_meta( $chapter_id, '_xin_locked', true );
	$label    = xin_chapter_label( $chapter_id );
	?>
	<a class="xin-chapcard" href="<?php echo esc_url( get_permalink( $chapter_id ) ); ?>">
		<span class="xin-chapcard__cover">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy">
			<?php endif; ?>
		</span>
		<span class="xin-chapcard__body">
			<?php if ( $novel_id ) : ?>
				<span class="xin-chapcard__novel"><?php echo esc_html( get_the_title( $novel_id ) ); ?></span>
			<?php endif; ?>
			<span class="xin-chapcard__title">
				<?php if ( $label ) : ?>
					<span class="xin-chapcard__num"><?php printf( esc_html__( 'Гл. %s', 'xi-novels' ), esc_html( $label ) ); ?></span>
				<?php endif; ?>
				<?php echo esc_html( get_the_title( $chapter_id ) ); ?>
			</span>
			<span class="xin-chapcard__foot">
				<span><?php echo esc_html( xin_ago( get_post_time( 'U', true, $chapter_id ) ) ); ?></span>
				<?php if ( $locked ) : ?>
					<span class="xin-badge xin-badge--gold"><?php xin_the_icon( 'lock' ); ?><?php esc_html_e( 'PLUS', 'xi-novels' ); ?></span>
				<?php endif; ?>
			</span>
		</span>
	</a>
	<?php
}

function xin_post_card( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cats    = get_the_category( $post_id );
	$thumb   = get_the_post_thumbnail_url( $post_id, 'xin-wide' );
	?>
	<article <?php post_class( 'xin-post-card', $post_id ); ?>>
		<a class="xin-post-card__media<?php echo $thumb ? '' : ' xin-post-card__media--empty'; ?>" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy">
			<?php else : ?>
				<?php xin_the_icon( 'pen' ); ?>
			<?php endif; ?>
			<?php if ( $cats ) : ?>
				<span class="xin-post-card__cat xin-badge xin-badge--primary"><?php echo esc_html( $cats[0]->name ); ?></span>
			<?php endif; ?>
		</a>
		<div class="xin-post-card__body">
			<h3 class="xin-post-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="xin-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 20 ) ); ?></p>
			<div class="xin-post-card__foot">
				<?php echo get_avatar( get_post_field( 'post_author', $post_id ), 24 ); ?>
				<span><?php echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ); ?></span>
				<span class="sep">·</span>
				<span><?php echo esc_html( get_the_date( '', $post_id ) ); ?></span>
			</div>
		</div>
	</article>
	<?php
}

function xin_section_head( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'title'      => '',
		'subtitle'   => '',
		'eyebrow'    => '',
		'icon'       => '',
		'more_href'  => '',
		'more_label' => __( 'Ещё', 'xi-novels' ),
		'after'      => '',
	) );
	?>
	<div class="xin-head">
		<div>
			<?php if ( $args['eyebrow'] ) : ?>
				<div class="xin-head__eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></div>
			<?php endif; ?>
			<h2>
				<?php if ( $args['icon'] ) : ?>
					<?php xin_the_icon( $args['icon'] ); ?>
				<?php endif; ?>
				<?php echo esc_html( $args['title'] ); ?>
			</h2>
			<?php if ( $args['subtitle'] ) : ?>
				<p class="xin-head__sub"><?php echo esc_html( $args['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $args['after'] ) : ?>
			<?php echo $args['after']; ?>
		<?php elseif ( $args['more_href'] ) : ?>
			<a class="xin-head__more" href="<?php echo esc_url( $args['more_href'] ); ?>">
				<?php echo esc_html( $args['more_label'] ); ?><?php xin_the_icon( 'chevron-right' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

function xin_pagination( $query = null ) {
	$args = array(
		'mid_size'  => 1,
		'end_size'  => 1,
		'prev_text' => xin_icon( 'chevron-left' ),
		'next_text' => xin_icon( 'chevron-right' ),
		'type'      => 'array',
	);
	if ( $query ) {
		$args['total']   = $query->max_num_pages;
		$args['current'] = max( 1, get_query_var( 'paged' ) );
	}

	$links = paginate_links( $args );
	if ( ! $links ) {
		return;
	}

	echo '<nav class="d-flex justify-content-center mt-5"><ul class="pagination flex-wrap">';
	foreach ( $links as $link ) {
		$active   = false !== strpos( $link, 'current' );
		$disabled = false !== strpos( $link, 'dots' );
		printf(
			'<li class="page-item%1$s">%2$s</li>',
			$active ? ' active' : ( $disabled ? ' disabled' : '' ),
			str_replace( 'page-numbers', 'page-link', $link ) 
		);
	}
	echo '</ul></nav>';
}

function xin_breadcrumbs() {
	$sep  = xin_icon( 'chevron-right' );
	$home = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Главная', 'xi-novels' ) . '</a>';
	$out  = array( $home );

	if ( is_singular( 'chapter' ) ) {
		$novel_id = xin_chapter_novel_id( get_the_ID() );
		$out[]    = '<a href="' . esc_url( get_post_type_archive_link( 'novel' ) ) . '">' . esc_html__( 'Каталог', 'xi-novels' ) . '</a>';
		if ( $novel_id ) {
			$out[] = '<a href="' . esc_url( get_permalink( $novel_id ) ) . '">' . esc_html( get_the_title( $novel_id ) ) . '</a>';
		}
		$out[] = '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'novel' ) ) {
		$out[] = '<a href="' . esc_url( get_post_type_archive_link( 'novel' ) ) . '">' . esc_html__( 'Каталог', 'xi-novels' ) . '</a>';
		$out[] = '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'post' ) ) {
		$blog = get_option( 'page_for_posts' );
		if ( $blog ) {
			$out[] = '<a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a>';
		}
		$out[] = '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$out[] = '<span>' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_post_type_archive( 'novel' ) ) {
		$out[] = '<span>' . esc_html__( 'Каталог', 'xi-novels' ) . '</span>';
	} elseif ( is_search() ) {
		$out[] = '<span>' . esc_html__( 'Поиск', 'xi-novels' ) . '</span>';
	} elseif ( is_page() ) {
		$out[] = '<span>' . esc_html( get_the_title() ) . '</span>';
	}

	if ( count( $out ) < 2 ) {
		return;
	}
	echo '<nav class="xin-crumbs">' . implode( $sep, $out ) . '</nav>'; 
}

function xin_stars( $value, $max = 5 ) {
	$full = (int) round( $value );
	$out  = '<span class="xin-stars">';
	for ( $i = 1; $i <= $max; $i++ ) {
		$out .= xin_icon( 'star', $i <= $full ? '' : 'is-off', true );
	}
	return $out . '</span>';
}

function xin_get_novels( $type = 'latest', $limit = 12 ) {
	$args = array(
		'post_type'              => 'novel',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'ignore_sticky_posts'    => true,
	);

	switch ( $type ) {
		case 'popular':
			$args['meta_key'] = '_xin_views';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'rating':
			$args['meta_key'] = '_xin_rating';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'updated':
			$args['orderby'] = 'modified';
			$args['order']   = 'DESC';
			break;
		case 'featured':
			$args['meta_query'] = array(
				array(
					'key'   => '_xin_featured',
					'value' => '1',
				),
			);
			$args['orderby'] = 'date';
			break;
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
	}

	$q = new WP_Query( $args );
	return $q->posts;
}

function xin_get_latest_chapters( $limit = 12 ) {
	$q = new WP_Query( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	return $q->posts;
}

function xin_site_stats() {
	$cached = get_transient( 'xin_site_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$views = (int) $wpdb->get_var(
		"SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = '_xin_views'"
	);

	$stats = array(
		'novels'   => (int) wp_count_posts( 'novel' )->publish,
		'chapters' => (int) wp_count_posts( 'chapter' )->publish,
		'views'    => $views,
		'readers'  => (int) count_users()['total_users'],
	);

	set_transient( 'xin_site_stats', $stats, 10 * MINUTE_IN_SECONDS );
	return $stats;
}

/**
 * Prints the query arguments of a URL as hidden inputs.
 *
 * A GET form replaces the query string of its action URL with its own fields, so
 * any argument the action already carried is lost on submit. On a site with plain
 * permalinks that argument is what identifies the page — `?post_type=novel`,
 * `?genre=fantasy`, `?page_id=12` — and dropping it sends the visitor to the front
 * page. Re-emitting those arguments as hidden fields keeps the form on its own page.
 *
 * @param string $url    URL whose query arguments should be carried over.
 * @param array  $except Names the form supplies itself and must not duplicate.
 */
function xin_hidden_query_fields( $url, $except = array() ) {
	$query = wp_parse_url( $url, PHP_URL_QUERY );
	if ( ! $query ) {
		return;
	}

	$args = array();
	wp_parse_str( $query, $args );

	foreach ( $args as $key => $value ) {
		if ( in_array( $key, (array) $except, true ) ) {
			continue;
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				printf(
					'<input type="hidden" name="%s" value="%s">',
					esc_attr( $key . '[]' ),
					esc_attr( $item )
				);
			}
			continue;
		}
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $key ), esc_attr( $value ) );
	}
}

/**
 * Как показать состояние главы в списке кабинета.
 *
 * Список показывал «Черновик» и дату создания даже у главы, которая стоит в
 * очереди на публикацию: по такой строке не понять ни что глава ждёт выхода,
 * ни когда он будет. Состояние собирается здесь, а плагин очереди может
 * дописать своё через фильтр — тема при этом от него не зависит.
 *
 * @param int|WP_Post $chapter Глава.
 * @return array badges (list of ['text','class','icon']) и date.
 */
function xin_chapter_state( $chapter ) {
	$chapter = get_post( $chapter );

	if ( ! $chapter ) {
		return array( 'badges' => array(), 'date' => '' );
	}

	$badges = array();

	if ( get_post_meta( $chapter->ID, '_xin_locked', true ) ) {
		$badges[] = array( 'text' => 'PLUS', 'class' => 'xin-badge--gold', 'icon' => 'lock' );
	}

	if ( 'future' === $chapter->post_status ) {
		// Штатное отложенное WordPress: дата публикации уже известна.
		$badges[] = array(
			'text'  => __( 'Отложена', 'xi-novels' ),
			'class' => 'xin-badge--primary',
			'icon'  => 'clock',
		);
		$date = sprintf(
			/* translators: %s: date and time the chapter goes out. */
			__( 'Выйдет %s', 'xi-novels' ),
			get_the_date( 'j M Y, H:i', $chapter->ID )
		);
	} else {
		if ( 'publish' !== $chapter->post_status ) {
			$status = get_post_status_object( $chapter->post_status );
			$badges[] = array(
				'text'  => $status ? $status->label : $chapter->post_status,
				'class' => '',
				'icon'  => '',
			);
		}
		$date = get_the_date( 'j M Y', $chapter->ID );
	}

	/**
	 * Позволяет дополнить состояние главы — например, временем из очереди.
	 *
	 * @param array $state   badges и date.
	 * @param int   $chapter Идентификатор главы.
	 */
	return apply_filters( 'xin_chapter_state', array( 'badges' => $badges, 'date' => $date, 'note' => '' ), $chapter->ID );
}

/**
 * Печатает значки состояния главы.
 *
 * @param array $badges Значки из xin_chapter_state().
 */
function xin_the_chapter_badges( $badges ) {
	foreach ( (array) $badges as $badge ) {
		printf( '<span class="xin-badge %s">', esc_attr( $badge['class'] ) );
		if ( ! empty( $badge['icon'] ) ) {
			xin_the_icon( $badge['icon'] );
		}
		echo esc_html( $badge['text'] ) . '</span>';
	}
}
