<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_icon_path( $name ) {
	$paths = array(
		'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'menu'          => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
		'sun'           => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
		'moon'          => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
		'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
		'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
		'chevron-up'    => '<path d="m18 15-6-6-6 6"/>',
		'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
		'book'          => '<path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
		'book-open'     => '<path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
		'heart'         => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
		'eye'           => '<path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/>',
		'star'          => '<path d="M11.5 2.9a.55.55 0 0 1 1 0l2.35 4.76 5.26.77c.45.06.63.62.3.94l-3.8 3.7.9 5.23c.08.45-.4.79-.8.58L12 16.42l-4.7 2.47c-.4.2-.88-.13-.8-.58l.9-5.23-3.8-3.7c-.33-.32-.15-.88.3-.94l5.26-.77Z"/>',
		'clock'         => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'flame'         => '<path d="M12 3q4 4 4 7a4 4 0 0 1-8 0c0-1 .5-2 1-2.5C8.5 9 8 10.5 8 12a4 4 0 0 0 8 0"/><path d="M12 22a7 7 0 0 0 7-7c0-4-3-7-7-12-4 5-7 8-7 12a7 7 0 0 0 7 7Z"/>',
		'crown'         => '<path d="M11.56 3.69a.5.5 0 0 1 .88 0l2.7 4.87 4.68-2.4a.5.5 0 0 1 .72.56L18.5 16h-13L3.46 6.72a.5.5 0 0 1 .72-.56l4.68 2.4Z"/><path d="M5.5 19h13"/>',
		'trophy'        => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6m12 5h1.5a2.5 2.5 0 0 0 0-5H18M6 3h12v6a6 6 0 0 1-12 0Z"/><path d="M12 15v4m-4 2h8"/>',
		'compass'       => '<circle cx="12" cy="12" r="10"/><path d="m16 8-2 6-6 2 2-6Z"/>',
		'newspaper'     => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9h4"/><path d="M10 6h8M10 10h8M10 14h5"/>',
		'layers'        => '<path d="m12 2 9 5-9 5-9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>',
		'pen'           => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
		'sparkles'      => '<path d="m12 3 1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9Z"/><path d="M18 15.5 19 18l2.5 1L19 20l-1 2.5L17 20l-2.5-1L17 18Z"/>',
		'lock'          => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		'comment'       => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
		'user'          => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'home'          => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
		'library'       => '<path d="M4 3h3v18H4zm5 0h3v18H9z"/><path d="m15.5 3.7 2.9-.8 3.1 17.4-2.9.8z"/>',
		'list'          => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
		'bookmark'      => '<path d="M19 21 12 16 5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
		'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1"/>',
		'type'          => '<path d="M4 7V5h16v2M9 19h6M12 5v14"/>',
		'minus'         => '<path d="M5 12h14"/>',
		'plus'          => '<path d="M12 5v14M5 12h14"/>',
		'arrow-up'      => '<path d="m12 19V5M5 12l7-7 7 7"/>',
		'arrow-right'   => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'calendar'      => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'tag'           => '<path d="M12.6 2.6A2 2 0 0 0 11.2 2H4a2 2 0 0 0-2 2v7.2c0 .5.2 1 .6 1.4l8.8 8.8a2 2 0 0 0 2.8 0l7.2-7.2a2 2 0 0 0 0-2.8Z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
		'check'         => '<path d="m20 6-11 11-5-5"/>',
		'filter'        => '<path d="M3 5h18l-7 8v6l-4 2v-8Z"/>',
		'play'          => '<path d="m6 3 14 9-14 9Z"/>',
		'share'         => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
		'trending'      => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
		'download'      => '<path d="M12 3v12m-5-5 5 5 5-5"/><path d="M5 21h14"/>',
		'telegram'      => '<path d="M21.5 3.5 2.8 10.6c-.8.3-.8 1.4 0 1.7l4.6 1.5 1.8 5.4c.2.7 1.1.9 1.6.3l2.4-2.6 4.6 3.4c.6.4 1.4.1 1.6-.6l3-14.4c.2-.8-.6-1.5-1.4-1.2Z"/><path d="m7.4 13.8 10-7-7.6 8.5"/>',
		'discord'       => '<path d="M8 4.5C6 5 4.5 5.8 4 6.4 2.7 9 2 12.4 2.2 16c1.6 1.3 3.2 2 4.8 2.4l1-1.6"/><path d="M16 4.5c2 .5 3.5 1.3 4 1.9 1.3 2.7 2 6.1 1.8 9.7-1.6 1.3-3.2 2-4.8 2.4l-1-1.6"/><path d="M7.5 16.5c3 1.3 6 1.3 9 0"/><circle cx="9" cy="12" r="1.3"/><circle cx="15" cy="12" r="1.3"/>',
		'vk'            => '<path d="M3 7h3c.4 3.8 2 6.3 3.4 6.9V7h3v4.4c1.4-.2 2.9-2 3.4-4.4H19c-.4 2.6-1.7 4.4-2.9 5.2 1.2.6 2.7 2.2 3.4 4.8h-3.2c-.5-1.7-1.8-3-3.4-3.2V17h-.4C6.9 17 3.6 13 3 7Z"/>',
		'youtube'       => '<rect width="20" height="14" x="2" y="5" rx="4"/><path d="m10 9 5 3-5 3Z"/>',
		'rss'           => '<path d="M4 11a9 9 0 0 1 9 9M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1.5"/>',
		'gift'          => '<rect width="18" height="12" x="3" y="9" rx="1"/><path d="M12 9v12M3 13h18"/><path d="M12 9c-2.5 0-5-1-5-3s2.5-2 5 3c2.5-5 5-5 5-3s-2.5 3-5 3Z"/>',
		'award'         => '<circle cx="12" cy="9" r="6"/><path d="m8.2 14-1.4 7 5.2-3 5.2 3-1.4-7"/>',
		'users'         => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M17 4.5a4 4 0 0 1 0 7.5M18 21h4a5.5 5.5 0 0 0-4-5.3"/>',
	);

	return isset( $paths[ $name ] ) ? $paths[ $name ] : '';
}

function xin_icon( $name, $class = '', $filled = false ) {
	$path = xin_icon_path( $name );
	if ( ! $path ) {
		return '';
	}
	return sprintf(
		'<svg class="xin-icon %1$s" viewBox="0 0 24 24" fill="%2$s" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
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
