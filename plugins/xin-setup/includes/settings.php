<?php
/**
 * Настройки WordPress, от которых зависит, как тема выглядит.
 *
 * Половина «тема сломалась» на свежей установке — это настройки ядра:
 * ссылки по умолчанию в виде `?p=123`, главная со списком записей вместо
 * витрины, комментарии, закрытые для типов записей темы.
 *
 * Каждый пункт умеет сказать, что сейчас, что будет, и почему это важно.
 *
 * @package XIN_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Что проверяем и чем чиним.
 */
function xs_wanted_settings() {
	return array(
		'permalinks' => array(
			'label' => __( 'Человеческие ссылки', 'xin-setup' ),
			'why'   => __( 'Пока стоят ссылки вида ?p=123, у глав не работают свои адреса, а поисковики не понимают структуру сайта.', 'xin-setup' ),
			'now'   => function () {
				$now = get_option( 'permalink_structure' );

				return $now ? $now : __( 'простые (?p=123)', 'xin-setup' );
			},
			'ok'    => function () {
				return (bool) get_option( 'permalink_structure' );
			},
			'fix'   => function () {
				global $wp_rewrite;

				update_option( 'permalink_structure', '/%postname%/' );
				$wp_rewrite->init();
				$wp_rewrite->flush_rules( true );

				return __( 'Ссылки переключены на /название/.', 'xin-setup' );
			},
		),

		'front' => array(
			'label' => __( 'Витрина на главной', 'xin-setup' ),
			'why'   => __( 'Иначе на главной список записей блога, а собранная тема с книгой недели и подборками не показывается вовсе.', 'xin-setup' ),
			'now'   => function () {
				return 'page' === get_option( 'show_on_front' )
					? get_the_title( (int) get_option( 'page_on_front' ) )
					: __( 'последние записи', 'xin-setup' );
			},
			'ok'    => function () {
				return 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' );
			},
			'fix'   => function () {
				$front = get_page_by_path( 'home' );

				if ( ! $front ) {
					$id = wp_insert_post( array(
						'post_type'   => 'page',
						'post_status' => 'publish',
						'post_title'  => __( 'Главная', 'xin-setup' ),
						'post_name'   => 'home',
					) );

					$front = $id && ! is_wp_error( $id ) ? get_post( $id ) : null;
				}

				if ( ! $front ) {
					return __( 'Не удалось завести страницу главной.', 'xin-setup' );
				}

				$blog = get_page_by_path( 'blog' );

				if ( ! $blog ) {
					$id   = wp_insert_post( array(
						'post_type'   => 'page',
						'post_status' => 'publish',
						'post_title'  => __( 'Блог', 'xin-setup' ),
						'post_name'   => 'blog',
					) );
					$blog = $id && ! is_wp_error( $id ) ? get_post( $id ) : null;
				}

				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $front->ID );

				if ( $blog ) {
					update_option( 'page_for_posts', $blog->ID );
				}

				return __( 'Главная — витрина темы, записи блога переехали на свою страницу.', 'xin-setup' );
			},
		),

		'discussion' => array(
			'label' => __( 'Обсуждения под главами', 'xin-setup' ),
			'why'   => __( 'У импортированных глав комментарии почти всегда закрыты — обсуждение под ними не появится, сколько ни включай его в теме.', 'xin-setup' ),
			'now'   => function () {
				global $wpdb;

				$closed = (int) $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->posts}
					  WHERE post_type IN ('novel','chapter')
					    AND post_status = 'publish'
					    AND comment_status <> 'open'"
				);

				return $closed
					? sprintf(
						/* translators: %s: сколько записей с закрытыми обсуждениями. */
						__( 'закрыто у %s записей', 'xin-setup' ),
						number_format_i18n( $closed )
					)
					: __( 'открыты везде', 'xin-setup' );
			},
			'ok'    => function () {
				global $wpdb;

				return 0 === (int) $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->posts}
					  WHERE post_type IN ('novel','chapter')
					    AND post_status = 'publish'
					    AND comment_status <> 'open'"
				);
			},
			'fix'   => function () {
				global $wpdb;

				$rows = (int) $wpdb->query(
					"UPDATE {$wpdb->posts} SET comment_status = 'open'
					  WHERE post_type IN ('novel','chapter') AND post_status = 'publish'"
				);

				update_option( 'default_comment_status', 'open' );

				return sprintf(
					/* translators: %s: сколько записей открыто. */
					__( 'Обсуждения открыты: записей — %s.', 'xin-setup' ),
					number_format_i18n( $rows )
				);
			},
		),

		'moderation' => array(
			'label' => __( 'Порядок в комментариях', 'xin-setup' ),
			'why'   => __( 'Комментарии от незнакомых читателей должны проходить проверку хотя бы первый раз — иначе спам приходит на второй день.', 'xin-setup' ),
			'now'   => function () {
				return get_option( 'comment_previously_approved' )
					? __( 'первый комментарий проверяется', 'xin-setup' )
					: __( 'публикуется всё подряд', 'xin-setup' );
			},
			'ok'    => function () {
				return (bool) get_option( 'comment_previously_approved' );
			},
			'fix'   => function () {
				update_option( 'comment_previously_approved', 1 );
				update_option( 'comment_registration', 1 );
				update_option( 'close_comments_for_old_posts', 0 );

				return __( 'Первый комментарий читателя уходит на проверку, писать могут только вошедшие.', 'xin-setup' );
			},
		),

		'reading' => array(
			'label' => __( 'Длина ленты обновлений', 'xin-setup' ),
			'why'   => __( 'Десять записей на страницу — это лента, по которой листают неделю. Для площадки с ежедневными главами мало.', 'xin-setup' ),
			'now'   => function () {
				return sprintf(
					/* translators: %s: сколько записей на страницу. */
					__( '%s на страницу', 'xin-setup' ),
					number_format_i18n( (int) get_option( 'posts_per_page' ) )
				);
			},
			'ok'    => function () {
				return (int) get_option( 'posts_per_page' ) >= 20;
			},
			'fix'   => function () {
				update_option( 'posts_per_page', 24 );

				return __( 'Двадцать четыре записи на страницу.', 'xin-setup' );
			},
		),

		'thumbnails' => array(
			'label' => __( 'Размеры обложек', 'xin-setup' ),
			'why'   => __( 'Обложка книги вертикальная, 2:3. Квадратная миниатюра WordPress режет её по краям.', 'xin-setup' ),
			'now'   => function () {
				return sprintf( '%d×%d', (int) get_option( 'thumbnail_size_w' ), (int) get_option( 'thumbnail_size_h' ) );
			},
			'ok'    => function () {
				return 200 === (int) get_option( 'thumbnail_size_w' ) && 300 === (int) get_option( 'thumbnail_size_h' );
			},
			'fix'   => function () {
				update_option( 'thumbnail_size_w', 200 );
				update_option( 'thumbnail_size_h', 300 );
				update_option( 'thumbnail_crop', 1 );
				update_option( 'medium_size_w', 400 );
				update_option( 'medium_size_h', 600 );

				return __( 'Миниатюра стала вертикальной, 200×300.', 'xin-setup' );
			},
		),

		'taxonomies' => array(
			'label' => __( 'Жанры и статусы', 'xin-setup' ),
			'why'   => __( 'Без них фильтр каталога пустой, и читателю нечем сузить выбор.', 'xin-setup' ),
			'now'   => function () {
				$genres = wp_count_terms( array( 'taxonomy' => 'genre', 'hide_empty' => false ) );
				$status = wp_count_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );

				return sprintf(
					/* translators: 1: число жанров, 2: число статусов. */
					__( 'жанров — %1$s, статусов — %2$s', 'xin-setup' ),
					number_format_i18n( is_wp_error( $genres ) ? 0 : (int) $genres ),
					number_format_i18n( is_wp_error( $status ) ? 0 : (int) $status )
				);
			},
			'ok'    => function () {
				$status = wp_count_terms( array( 'taxonomy' => 'novel_status', 'hide_empty' => false ) );

				return ! is_wp_error( $status ) && (int) $status >= 3;
			},
			'fix'   => function () {
				$statuses = array(
					'ongoing'   => __( 'Выходит', 'xin-setup' ),
					'completed' => __( 'Завершён', 'xin-setup' ),
					'hiatus'    => __( 'Заморожен', 'xin-setup' ),
					'announced' => __( 'Анонс', 'xin-setup' ),
				);

				$genres = array(
					__( 'Фэнтези', 'xin-setup' ),
					__( 'Боевые искусства', 'xin-setup' ),
					__( 'Романтика', 'xin-setup' ),
					__( 'Драма', 'xin-setup' ),
					__( 'Мистика', 'xin-setup' ),
					__( 'Научная фантастика', 'xin-setup' ),
					__( 'Приключения', 'xin-setup' ),
					__( 'Повседневность', 'xin-setup' ),
				);

				$made = 0;

				foreach ( $statuses as $slug => $name ) {
					if ( ! term_exists( $slug, 'novel_status' ) ) {
						wp_insert_term( $name, 'novel_status', array( 'slug' => $slug ) );
						$made++;
					}
				}

				foreach ( $genres as $name ) {
					if ( ! term_exists( $name, 'genre' ) ) {
						wp_insert_term( $name, 'genre' );
						$made++;
					}
				}

				return sprintf(
					/* translators: %s: сколько заведено. */
					__( 'Заведено значений — %s.', 'xin-setup' ),
					number_format_i18n( $made )
				);
			},
		),
	);
}
