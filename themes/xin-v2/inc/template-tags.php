<?php
/**
 * Шаблонные функции: карточки, значки, форматирование чисел и дат.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Бренд и иконки
 * ---------------------------------------------------------------------- */

function xv_brand() {
	$name = get_bloginfo( 'name' );

	// Знак в квадрате — первая буква названия без декоративных символов.
	$clean = trim( preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $name ) );
	$mark  = mb_strtolower( mb_substr( '' !== $clean ? $clean : 'e', 0, 1 ) );

	return array(
		'name' => $name,
		'mark' => $mark,
	);
}

function xv_icon( $name, $class = '', $filled = false ) {
	$set = xv_icon_set();

	if ( ! isset( $set[ $name ] ) ) {
		$brands = xv_brand_icon_set();

		if ( isset( $brands[ $name ] ) ) {
			return sprintf(
				'<svg class="xv-i %s" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%s</svg>',
				esc_attr( $class ),
				$brands[ $name ]
			);
		}

		return '';
	}

	return sprintf(
		'<svg class="xv-i %s" viewBox="0 0 24 24" fill="%s" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		esc_attr( $class ),
		$filled ? 'currentColor' : 'none',
		$set[ $name ]
	);
}

function xv_the_icon( $name, $class = '', $filled = false ) {
	echo xv_icon( $name, $class, $filled ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- собственная разметка SVG.
}

/* -------------------------------------------------------------------------
 * Форматирование
 * ---------------------------------------------------------------------- */

/**
 * Короткая запись больших чисел: 1 240 → 1.2K.
 */
function xv_num( $n ) {
	$n = (int) $n;

	if ( $n < 1000 ) {
		return (string) $n;
	}

	if ( $n < 1000000 ) {
		$v = $n / 1000;

		return ( $v >= 100 ? round( $v ) : round( $v, 1 ) ) . 'K';
	}

	$v = $n / 1000000;

	return ( $v >= 100 ? round( $v ) : round( $v, 1 ) ) . 'M';
}

/**
 * Подписи к числам.
 *
 * Отдельными функциями, а не одной строкой на месте: одно и то же число
 * встречается на витрине, в кабинете и в письме, и склоняться должно
 * одинаково. Перевод получает три формы, а не одну «слов» на все случаи.
 */
function xv_label_days( $n ) {
	return xv_plural( $n, __( 'день подряд', 'xin-v2' ), __( 'дня подряд', 'xin-v2' ), __( 'дней подряд', 'xin-v2' ) );
}

function xv_label_requests( $n ) {
	return xv_plural( $n, __( 'заявка в авторы', 'xin-v2' ), __( 'заявки в авторы', 'xin-v2' ), __( 'заявок в авторы', 'xin-v2' ) );
}

function xv_label_waiting( $n ) {
	return xv_plural( $n, __( 'ждёт публикации', 'xin-v2' ), __( 'ждут публикации', 'xin-v2' ), __( 'ждут публикации', 'xin-v2' ) );
}

function xv_label_held( $n ) {
	return xv_plural( $n, __( 'на проверке', 'xin-v2' ), __( 'на проверке', 'xin-v2' ), __( 'на проверке', 'xin-v2' ) );
}
function xv_label_words( $n ) {
	return xv_plural( $n, __( 'слово', 'xin-v2' ), __( 'слова', 'xin-v2' ), __( 'слов', 'xin-v2' ) );
}

function xv_label_chapters( $n ) {
	return xv_plural( $n, __( 'глава', 'xin-v2' ), __( 'главы', 'xin-v2' ), __( 'глав', 'xin-v2' ) );
}

function xv_label_views( $n ) {
	return xv_plural( $n, __( 'просмотр', 'xin-v2' ), __( 'просмотра', 'xin-v2' ), __( 'просмотров', 'xin-v2' ) );
}

function xv_label_titles( $n ) {
	return xv_plural( $n, __( 'тайтл', 'xin-v2' ), __( 'тайтла', 'xin-v2' ), __( 'тайтлов', 'xin-v2' ) );
}

function xv_label_ratings( $n ) {
	return xv_plural( $n, __( 'оценка', 'xin-v2' ), __( 'оценки', 'xin-v2' ), __( 'оценок', 'xin-v2' ) );
}

function xv_label_comments( $n ) {
	return xv_plural( $n, __( 'комментарий', 'xin-v2' ), __( 'комментария', 'xin-v2' ), __( 'комментариев', 'xin-v2' ) );
}

function xv_label_minutes( $n ) {
	return xv_plural( $n, __( 'минута', 'xin-v2' ), __( 'минуты', 'xin-v2' ), __( 'минут', 'xin-v2' ) );
}

function xv_label_readers( $n ) {
	return xv_plural( $n, __( 'читатель', 'xin-v2' ), __( 'читателя', 'xin-v2' ), __( 'читателей', 'xin-v2' ) );
}

/**
 * Число вместе с подписью: «1 глава», «2 главы», «12 глав».
 */
function xv_counted( $n, $label ) {
	return sprintf( '%s %s', number_format_i18n( $n ), $label );
}
function xv_plural( $n, $one, $few, $many ) {
	if ( function_exists('xv_current_lang') && xv_current_lang() !== 'ru' ) {
		return abs((int)$n) === 1 ? $one : $many;
	}
	$n = abs( (int) $n ) % 100;
	$d = $n % 10;

	if ( $n > 10 && $n < 20 ) {
		return $many;
	}

	if ( $d > 1 && $d < 5 ) {
		return $few;
	}

	if ( 1 === $d ) {
		return $one;
	}

	return $many;
}

/**
 * «3 часа назад» — но для старого просто дата: «12 мая» читается быстрее,
 * чем «4 месяца назад».
 */
function xv_ago( $timestamp ) {
	$timestamp = is_numeric( $timestamp ) ? (int) $timestamp : strtotime( (string) $timestamp );

	if ( ! $timestamp ) {
		return '';
	}

	$diff = time() - $timestamp;

	if ( $diff < MINUTE_IN_SECONDS ) {
		return __( 'только что', 'xin-v2' );
	}

	if ( $diff < HOUR_IN_SECONDS ) {
		$m = (int) floor( $diff / MINUTE_IN_SECONDS );

		return sprintf( '%d %s', $m, xv_plural( $m, __( 'минуту', 'xin-v2' ), __( 'минуты', 'xin-v2' ), __( 'минут', 'xin-v2' ) ) ) . __( ' назад', 'xin-v2' );
	}

	if ( $diff < DAY_IN_SECONDS ) {
		$h = (int) floor( $diff / HOUR_IN_SECONDS );

		return sprintf( '%d %s', $h, xv_plural( $h, __( 'час', 'xin-v2' ), __( 'часа', 'xin-v2' ), __( 'часов', 'xin-v2' ) ) ) . __( ' назад', 'xin-v2' );
	}

	if ( $diff < 30 * DAY_IN_SECONDS ) {
		$d = (int) floor( $diff / DAY_IN_SECONDS );

		return sprintf( '%d %s', $d, xv_plural( $d, __( 'день', 'xin-v2' ), __( 'дня', 'xin-v2' ), __( 'дней', 'xin-v2' ) ) ) . __( ' назад', 'xin-v2' );
	}

	return wp_date( 'j M Y', $timestamp );
}

/* -------------------------------------------------------------------------
 * Данные тайтла
 * ---------------------------------------------------------------------- */

function xv_cover_url( $post_id, $size = 'xv-cover' ) {
	$url = get_the_post_thumbnail_url( $post_id, $size );

	return $url ? $url : '';
}

function xv_cover( $post_id, $size = 'xv-cover', $args = array() ) {
	$args = wp_parse_args( $args, array(
		'class' => '',
		'alt'   => get_the_title( $post_id ),
		'lazy'  => true,
	) );

	$url = xv_cover_url( $post_id, $size );

	ob_start();
	?>
	<span class="xv-cover <?php echo esc_attr( $args['class'] ); ?>">
		<?php if ( $url ) : ?>
			<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $args['alt'] ); ?>"<?php echo $args['lazy'] ? ' loading="lazy" decoding="async"' : ''; ?>>
		<?php else : ?>
			<span class="xv-cover-blank" aria-hidden="true"></span>
		<?php endif; ?>
	</span>
	<?php
	return trim( ob_get_clean() );
}

function xv_novel_author( $novel_id ) {
	$name = get_post_meta( $novel_id, '_xin_author_name', true );

	if ( $name ) {
		return $name;
	}

	$author = (int) get_post_field( 'post_author', $novel_id );

	return $author ? get_the_author_meta( 'display_name', $author ) : '';
}

function xv_rating( $novel_id ) {
	$value = (float) get_post_meta( $novel_id, '_xin_rating', true );
	$count = (int) get_post_meta( $novel_id, '_xin_rating_count', true );

	return array(
		'value' => $value,
		'count' => $count,
	);
}

function xv_stars( $value, $max = 5 ) {
	$value = max( 0, min( $max, (float) $value ) );
	$full  = (int) floor( $value );

	ob_start();
	echo '<span class="xv-stars" role="img" aria-label="' . esc_attr( sprintf( __( 'Оценка %s из %d', 'xin-v2' ), number_format_i18n( $value, 1 ), $max ) ) . '">';

	for ( $i = 1; $i <= $max; $i++ ) {
		$filled = $i <= $full;
		echo xv_icon( 'star', $filled ? 'is-on' : 'is-off', $filled ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</span>';

	return trim( ob_get_clean() );
}

function xv_novel_status( $novel_id ) {
	$terms = get_the_terms( $novel_id, 'novel_status' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	return $terms[0];
}

/**
 * Цветовая метка жанра. Совпадает с палитрой --tag-*: каждый жанр всегда
 * одного цвета, иначе каталог мельтешит.
 */
function xv_genre_class( $slug ) {
	$map = array(
		'fantasy'    => 'fantasy',
		'fentezi'    => 'fantasy',
		'romance'    => 'romance',
		'romantika'  => 'romance',
		'action'     => 'action',
		'boevik'     => 'action',
		'adventure'  => 'action',
		'scifi'      => 'scifi',
		'sci-fi'     => 'scifi',
		'fantastika' => 'scifi',
		'mystery'    => 'mystery',
		'detective'  => 'mystery',
		'detektiv'   => 'mystery',
		'slice'      => 'slice',
		'slice-of-life' => 'slice',
		'povsednevnost' => 'slice',
		'horror'     => 'horror',
		'uzhasy'     => 'horror',
		'litrpg'     => 'litrpg',
		'litrpg-ru'  => 'litrpg',
	);

	$slug = (string) $slug;

	if ( isset( $map[ $slug ] ) ) {
		return $map[ $slug ];
	}

	// Незнакомый жанр всё равно должен быть стабильного цвета.
	$palette = array( 'fantasy', 'romance', 'action', 'scifi', 'mystery', 'slice', 'horror', 'litrpg' );

	return $palette[ crc32( $slug ) % count( $palette ) ];
}

/**
 * Жанры строкой через точку.
 *
 * В списках цветные метки на каждой строке превращаются в рябь: там, где
 * важны порядок и название, жанры идут спокойным текстом.
 */
function xv_genre_line( $novel_id, $limit = 3 ) {
	$terms = get_the_terms( $novel_id, 'genre' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$names = wp_list_pluck( array_slice( $terms, 0, $limit ), 'name' );

	return implode( ' · ', $names );
}

/**
 * Метки жанров.
 *
 * @param bool $link Ссылками или простым текстом. Внутри карточки-ссылки
 *                   ссылка вложиться не может — браузер рвёт такую разметку,
 *                   поэтому по умолчанию рисуем span.
 */
function xv_genre_chips( $novel_id, $limit = 3, $link = false ) {
	$terms = get_the_terms( $novel_id, 'genre' );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$terms = array_slice( $terms, 0, $limit );
	$out   = '';

	foreach ( $terms as $term ) {
		if ( $link ) {
			$out .= sprintf(
				'<a class="xv-chip %s" href="%s">%s</a>',
				esc_attr( xv_genre_class( $term->slug ) ),
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name )
			);
		} else {
			$out .= sprintf(
				'<span class="xv-chip %s">%s</span>',
				esc_attr( xv_genre_class( $term->slug ) ),
				esc_html( $term->name )
			);
		}
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * Главы
 * ---------------------------------------------------------------------- */

/**
 * Все главы тайтла по порядку. Черновики видит только тот, кто может их
 * править — читателю их в оглавлении быть не должно.
 */
// Порядок глав держит inc/chapters.php: индекс в мете тайтла вместо
// запроса на весь тайтл при каждом обращении.

function xv_chapter_novel( $chapter_id ) {
	$novel = (int) get_post_meta( $chapter_id, '_xin_novel', true );

	return $novel ? get_post( $novel ) : null;
}

function xv_chapter_number( $chapter_id ) {
	$n = get_post_meta( $chapter_id, '_xin_number', true );

	return '' === $n ? '' : ( 0 === (int) ( (float) $n * 10 ) % 10 ? (string) (int) $n : (string) (float) $n );
}

// Замки, цены и команда проекта живут в inc/access.php: там же, где
// проверка покупки, чтобы читалка и выгрузка книги отвечали одинаково.

function xv_chapter_state( $chapter ) {
	$chapter = get_post( $chapter );

	if ( ! $chapter ) {
		return array( 'badges' => array(), 'date' => '', 'locked' => false );
	}

	$badges = array();
	$locked = ! xv_chapter_unlocked( $chapter->ID );

	if ( get_post_meta( $chapter->ID, '_xin_locked', true ) ) {
		$badges[] = array(
			'key'  => 'locked',
			'text' => $locked ? __( 'Ранний доступ', 'xin-v2' ) : __( 'Открыта', 'xin-v2' ),
			'icon' => $locked ? 'lock' : 'check',
		);
	}

	if ( 'future' === $chapter->post_status ) {
		$badges[] = array(
			'key'  => 'scheduled',
			'text' => __( 'Отложена', 'xin-v2' ),
			'icon' => 'clock',
		);

		/* translators: %s: дата и время выхода главы. */
		$date = sprintf( __( 'Выйдет %s', 'xin-v2' ), get_the_date( 'j M Y, H:i', $chapter->ID ) );
	} else {
		if ( 'publish' !== $chapter->post_status ) {
			$status = get_post_status_object( $chapter->post_status );

			$badges[] = array(
				'key'  => 'status',
				'text' => $status ? $status->label : $chapter->post_status,
				'icon' => '',
			);
		}

		$date = xv_ago( get_post_timestamp( $chapter->ID ) );
	}

	return apply_filters( 'xv_chapter_state', array(
		'badges' => $badges,
		'date'   => $date,
		'locked' => $locked,
	), $chapter->ID );
}

/**
 * Объём главы: слова и время чтения.
 */
function xv_chapter_size( $chapter_id ) {
	$content = get_post_field( 'post_content', $chapter_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ), 0, 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ' );
	$minutes = max( 1, (int) round( $words / 180 ) );

	return array(
		'words'   => $words,
		'minutes' => $minutes,
	);
}

/* -------------------------------------------------------------------------
 * Выборки
 * ---------------------------------------------------------------------- */

/**
 * Подборки для главной и виджетов.
 *
 * @param string $type latest|popular|rating|featured|updated|completed.
 */
function xv_get_novels( $type = 'latest', $limit = 12 ) {
	$args = array(
		'post_type'      => 'novel',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'no_found_rows'  => true,
		'ignore_sticky_posts' => true,
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

		case 'featured':
			$args['meta_query'] = array(
				array(
					'key'     => '_xin_featured',
					'value'   => '1',
					'compare' => '=',
				),
			);
			break;

		case 'updated':
			$args['meta_key'] = '_xin_last_chapter_at';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;

		case 'completed':
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'novel_status',
					'field'    => 'slug',
					'terms'    => 'completed',
				),
			);
			break;

		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
	}

	$posts = get_posts( $args );

	// «Избранное» на пустой базе не должно оставлять дыру на главной.
	if ( 'featured' === $type && empty( $posts ) ) {
		return xv_get_novels( 'rating', $limit );
	}

	return $posts;
}

function xv_get_latest_chapters( $limit = 12 ) {
	return get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
}

function xv_site_stats() {
	$cached = get_transient( 'xv_site_stats' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$novels   = wp_count_posts( 'novel' );
	$chapters = wp_count_posts( 'chapter' );
	$genres   = wp_count_terms( array( 'taxonomy' => 'genre', 'hide_empty' => true ) );

	$stats = array(
		'novels'   => (int) $novels->publish,
		'chapters' => (int) $chapters->publish,
		'genres'   => is_wp_error( $genres ) ? 0 : (int) $genres,
		'readers'  => (int) count_users()['total_users'],
	);

	set_transient( 'xv_site_stats', $stats, 10 * MINUTE_IN_SECONDS );

	return $stats;
}

/* -------------------------------------------------------------------------
 * Блоки разметки
 * ---------------------------------------------------------------------- */

function xv_section_head( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'title'     => '',
		'sub'       => '',
		'more'      => '',
		'more_text' => __( 'Все', 'xin-v2' ),
		'level'     => 'h2',
		'id'        => '',
	) );

	if ( '' === $args['title'] ) {
		return;
	}

	$tag = in_array( $args['level'], array( 'h1', 'h2', 'h3' ), true ) ? $args['level'] : 'h2';
	?>
	<div class="xv-section-head">
		<<?php echo esc_html( $tag ); ?><?php echo $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>>
			<?php echo esc_html( $args['title'] ); ?>
			<?php if ( $args['sub'] ) : ?>
				<span class="sub"><?php echo esc_html( $args['sub'] ); ?></span>
			<?php endif; ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php if ( $args['more'] ) : ?>
			<a class="more" href="<?php echo esc_url( $args['more'] ); ?>">
				<?php echo esc_html( $args['more_text'] ); ?> <span aria-hidden="true">&rarr;</span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Карточка тайтла: обложка, название, автор, оценка.
 */
function xv_novel_card( $novel_id, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'size'  => 'xv-cover',
		'meta'  => true,
		'class' => '',
	) );

	$rating = xv_rating( $novel_id );
	$status = xv_novel_status( $novel_id );
	$views  = xv_get_views( $novel_id );
	$count  = (int) get_post_meta( $novel_id, '_xin_chapter_count', true );

	// Мета — одной строкой моноширинным: оценка, объём, просмотры. Ряд
	// цветных меток под каждой обложкой превращал сетку в рябь.
	$meta = array();

	if ( $rating['value'] > 0 ) {
		$meta[] = '★ ' . number_format_i18n( $rating['value'], 1 );
	}

	if ( $count ) {
		/* translators: %s: число глав. */
		$meta[] = sprintf( __( '%s гл.', 'xin-v2' ), xv_num( $count ) );
	}

	if ( $views ) {
		$meta[] = xv_num( $views );
	}
	?>
	<a class="xv-novel-card <?php echo esc_attr( $args['class'] ); ?>" href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>">
		<span class="xv-cover">
			<?php if ( xv_cover_url( $novel_id, $args['size'] ) ) : ?>
				<img src="<?php echo esc_url( xv_cover_url( $novel_id, $args['size'] ) ); ?>" alt="" loading="lazy" decoding="async">
			<?php else : ?>
				<span class="xv-cover-blank" aria-hidden="true"></span>
			<?php endif; ?>
			<?php if ( $status && 'completed' === $status->slug ) : ?>
				<span class="badge-status"><?php echo esc_html( $status->name ); ?></span>
			<?php endif; ?>
		</span>
		<span class="xv-novel-card-body">
			<span class="xv-novel-card-title"><?php echo esc_html( get_the_title( $novel_id ) ); ?></span>
			<span class="xv-novel-card-author"><?php echo esc_html( xv_novel_author( $novel_id ) ); ?></span>
			<?php if ( $args['meta'] && $meta ) : ?>
				<span class="xv-novel-card-meta"><?php foreach ( $meta as $xv_meta_index => $xv_meta_value ) : ?><span class="xv-card-stat<?php echo 0 === $xv_meta_index && $rating['value'] > 0 ? ' xv-card-stat-rating' : ''; ?>"><?php echo esc_html( $xv_meta_value ); ?></span><?php endforeach; ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Строка рейтинга: крупный номер, обложка, название, жанры.
 */
function xv_rank_row( $novel_id, $rank ) {
	$genres = xv_genre_line( $novel_id, 3 );
	?>
	<a class="xv-rank-row" href="<?php echo esc_url( get_permalink( $novel_id ) ); ?>">
		<span class="xv-rank-num<?php echo $rank <= 3 ? ' top' : ''; ?>"><?php echo (int) $rank; ?></span>
		<span class="xv-rank-cover">
			<?php if ( xv_cover_url( $novel_id ) ) : ?>
				<img src="<?php echo esc_url( xv_cover_url( $novel_id ) ); ?>" alt="" loading="lazy" decoding="async">
			<?php endif; ?>
		</span>
		<span class="xv-rank-body">
			<span class="xv-rank-title"><?php echo esc_html( get_the_title( $novel_id ) ); ?></span>
			<span class="xv-rank-author"><?php echo esc_html( xv_novel_author( $novel_id ) ); ?></span>
			<?php if ( $genres ) : ?>
				<span class="xv-rank-genres"><?php echo esc_html( $genres ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Строка обновления: тайтл, номер и название главы, автор, время.
 */
function xv_update_row( $chapter ) {
	$chapter = get_post( $chapter );

	if ( ! $chapter ) {
		return;
	}

	$novel = xv_chapter_novel( $chapter->ID );

	if ( ! $novel ) {
		return;
	}

	$state = xv_chapter_state( $chapter );
	$num   = xv_chapter_number( $chapter->ID );
	?>
	<div class="xv-upd-row">
		<a class="xv-upd-cover" href="<?php echo esc_url( get_permalink( $novel ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( xv_cover_url( $novel->ID ) ) : ?>
				<img src="<?php echo esc_url( xv_cover_url( $novel->ID ) ); ?>" alt="" loading="lazy" decoding="async">
			<?php endif; ?>
		</a>
		<div class="xv-upd-body">
			<a class="xv-upd-novel" href="<?php echo esc_url( get_permalink( $novel ) ); ?>"><?php echo esc_html( get_the_title( $novel ) ); ?></a>
			<a class="xv-upd-chapter" href="<?php echo esc_url( get_permalink( $chapter ) ); ?>">
				<?php if ( $state['locked'] ) : ?>
					<?php xv_the_icon( 'lock', 'xv-upd-lock' ); ?>
				<?php endif; ?>
				<?php if ( '' !== $num ) : ?>
					<span class="xv-upd-num"><?php printf( esc_html__( 'Гл. %s', 'xin-v2' ), esc_html( $num ) ); ?></span>
					<span class="xv-upd-sep">·</span>
				<?php endif; ?>
				<span class="xv-upd-title"><?php echo esc_html( get_the_title( $chapter ) ); ?></span>
			</a>
		</div>
		<div class="xv-upd-author"><?php
			$translator = trim( (string) get_post_meta( $novel->ID, '_xin_translator', true ) );
			echo esc_html( $translator ?: get_the_author_meta( 'display_name', $chapter->post_author ) );
		?></div>
		<time class="xv-upd-time" datetime="<?php echo esc_attr( get_post_time( 'c', true, $chapter ) ); ?>" title="<?php echo esc_attr( get_the_date( 'd.m.Y H:i', $chapter ) ); ?>"><?php echo esc_html( $state['date'] ); ?></time>
	</div>
	<?php
}

/**
 * Строка оглавления на странице тайтла.
 */
function xv_toc_row( $chapter, $index ) {
	$chapter = get_post( $chapter );

	if ( ! $chapter ) {
		return;
	}

	$state = xv_chapter_state( $chapter );
	$size  = xv_chapter_size( $chapter->ID );
	$price = xv_chapter_price( $chapter->ID );
	$num   = xv_chapter_number( $chapter->ID );
	?>
	<a class="xv-toc-row<?php echo $state['locked'] ? ' is-locked' : ''; ?>" href="<?php echo esc_url( get_permalink( $chapter ) ); ?>" data-title="<?php echo esc_attr( mb_strtolower( get_the_title( $chapter ) ) ); ?>">
		<span class="xv-toc-num"><?php echo esc_html( '' !== $num ? $num : (string) $index ); ?></span>
		<span class="xv-toc-title"><?php echo esc_html( get_the_title( $chapter ) ); ?></span>
		<span class="xv-toc-meta">
			<span class="xv-toc-words"><?php printf( esc_html__( '%s сл.', 'xin-v2' ), esc_html( xv_num( $size['words'] ) ) ); ?></span>
			<span class="xv-toc-date"><?php echo esc_html( $state['date'] ); ?></span>
			<?php foreach ( $state['badges'] as $badge ) : ?>
				<span class="xv-toc-badge">
					<?php if ( $badge['icon'] ) : ?>
						<?php xv_the_icon( $badge['icon'] ); ?>
					<?php endif; ?>
					<?php echo esc_html( $badge['text'] ); ?>
				</span>
			<?php endforeach; ?>
			<?php if ( $state['locked'] && $price > 0 ) : ?>
				<span class="xv-toc-price"><?php echo esc_html( $price . ' ' . xv_opt( 'coin_label', '' ) ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

function xv_post_card( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	?>
	<a class="xv-post-card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<span class="xv-post-card-body">
			<span class="xv-post-card-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			<span class="xv-post-card-date"><?php echo esc_html( xv_ago( get_post_timestamp( $post_id ) ) ); ?></span>
		</span>
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<span class="xv-post-card-thumb">
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'thumbnail' ) ); ?>" alt="" loading="lazy" decoding="async">
			</span>
		<?php endif; ?>
	</a>
	<?php
}

function xv_breadcrumbs( $items ) {
	if ( empty( $items ) ) {
		return;
	}
	?>
	<nav class="xv-crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'xin-v2' ); ?>">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php if ( $i > 0 ) : ?>
				<span class="xv-crumbs-sep" aria-hidden="true">&rsaquo;</span>
			<?php endif; ?>
			<?php if ( ! empty( $item['url'] ) ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php else : ?>
				<span aria-current="page"><?php echo esc_html( $item['label'] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<?php
}

function xv_pagination( $query = null ) {
	global $wp_query;

	$query = $query ? $query : $wp_query;

	if ( $query->max_num_pages < 2 ) {
		return;
	}

	$links = paginate_links( array(
		'total'     => $query->max_num_pages,
		'current'   => max( 1, get_query_var( 'paged' ) ),
		'mid_size'  => 1,
		'prev_text' => '&laquo; ' . __( 'Назад', 'xin-v2' ),
		'next_text' => __( 'Вперёд', 'xin-v2' ) . ' &raquo;',
		'type'      => 'array',
	) );

	if ( ! $links ) {
		return;
	}
	?>
	<nav class="xv-pagination" aria-label="<?php esc_attr_e( 'Страницы', 'xin-v2' ); ?>">
		<?php foreach ( $links as $link ) : ?>
			<?php echo wp_kses_post( $link ); ?>
		<?php endforeach; ?>
	</nav>
	<?php
}

function xv_is_author_area() {
	if ( is_admin() ) {
		return false;
	}

	$templates = array( 'template-author.php', 'template-manage.php' );

	foreach ( $templates as $template ) {
		if ( is_page_template( $template ) ) {
			return true;
		}
	}

	return false;
}
