<?php
/**
 * Plugin Name: XI: перенос с Fictioneer
 * Description: Переводит содержимое сайта с темы Fictioneer на XIN-Com: типы записей, связь глав с тайтлами, порядок глав, жанры и метки, статус, возрастную отметку и соавторов. Работает порциями, ведёт журнал и умеет откатиться. Экран появляется в «Инструментах» только тогда, когда на сайте действительно есть данные Fictioneer.
 * Version: 2.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: XI Community
 * License: GPLv2 or later
 * Text Domain: xi-from-fictioneer
 *
 * @package XI_From_Fictioneer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XFF_VERSION', '1.0.0' );

/** Сколько записей обрабатывается за один заход. */
const XFF_BATCH = 40;

/** Куда складывается исходный тип записи, чтобы перенос можно было отменить. */
const XFF_BACK_META = '_xff_was';

/** Куда складывается прежний статус записи, если перенос его менял. */
const XFF_BACK_STATUS = '_xff_was_status';

/** Опция с состоянием переноса. */
const XFF_STATE = 'xff_state';

/**
 * Соответствие типов записей.
 *
 * @return array
 */
function xff_types() {
	return array(
		'fcn_story'   => 'novel',
		'fcn_chapter' => 'chapter',
	);
}

/**
 * Соответствие таксономий.
 *
 * Фандомы, персонажи и предупреждения у нас отдельными таксономиями не живут:
 * всё это метки тайтла, и разводить под каждую свой архив значило бы городить
 * три почти пустых раздела.
 *
 * @return array
 */
function xff_taxonomies() {
	return array(
		'fcn_genre'           => 'genre',
		'fcn_fandom'          => 'novel_tag',
		'fcn_character'       => 'novel_tag',
		'fcn_content_warning' => 'novel_tag',
	);
}

/**
 * Сколько записей Fictioneer лежит в базе.
 *
 * Считаем напрямую: типы записей Fictioneer после смены темы уже не
 * зарегистрированы, и обычные функции WordPress их не видят.
 *
 * @return array тип => количество.
 */
function xff_counts() {
	global $wpdb;

	$out = array();

	foreach ( array_keys( xff_types() ) as $type ) {
		$out[ $type ] = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'trash'",
				$type
			)
		);
	}

	return $out;
}

/**
 * Сколько записей уже перенесено (по метке возврата).
 *
 * @return int
 */
function xff_done_count() {
	global $wpdb;

	return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", XFF_BACK_META )
	);
}

/**
 * Есть ли вообще о чём говорить: данные Fictioneer или след прошлого переноса.
 *
 * @return bool
 */
function xff_relevant() {
	return array_sum( xff_counts() ) > 0 || xff_done_count() > 0;
}

/**
 * Экран в «Инструментах». Появляется только когда есть что переносить.
 *
 * @return void
 */
function xff_menu() {
	if ( ! xff_relevant() ) {
		return;
	}

	add_management_page(
		__( 'Перенос с Fictioneer', 'xi-from-fictioneer' ),
		__( 'Перенос с Fictioneer', 'xi-from-fictioneer' ),
		'manage_options',
		'xff',
		'xff_screen'
	);
}
add_action( 'admin_menu', 'xff_menu' );

/**
 * Экран переноса.
 *
 * @return void
 */
function xff_screen() {
	$counts = xff_counts();
	$done   = xff_done_count();
	$theme  = function_exists( 'xin_chapter_novel_id' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Перенос с Fictioneer', 'xi-from-fictioneer' ); ?></h1>

		<?php if ( ! $theme ) : ?>
			<div class="notice notice-error"><p>
				<?php esc_html_e( 'Тема XIN-Com не активна. Включите её и вернитесь сюда: переносить не во что.', 'xi-from-fictioneer' ); ?>
			</p></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:640px;margin:16px 0">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Произведения Fictioneer', 'xi-from-fictioneer' ); ?></td>
					<td><b><?php echo (int) $counts['fcn_story']; ?></b></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Главы Fictioneer', 'xi-from-fictioneer' ); ?></td>
					<td><b><?php echo (int) $counts['fcn_chapter']; ?></b></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Уже перенесено записей', 'xi-from-fictioneer' ); ?></td>
					<td><b><?php echo (int) $done; ?></b></td>
				</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Что будет сделано', 'xi-from-fictioneer' ); ?></h2>
		<ul style="list-style:disc;padding-left:20px;max-width:760px">
			<li><?php esc_html_e( '«Произведение» становится «Новеллой», «Глава» — «Главой». Записи не копируются, а меняют тип: идентификаторы, тексты, обложки, комментарии, авторы и даты остаются теми же.', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Связь главы с тайтлом переезжает из fictioneer_chapter_story в _xin_novel.', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Порядок глав берётся из списка глав произведения — того самого, что вы сортировали в редакторе, — и записывается номерами 1, 2, 3…', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Жанры становятся жанрами; фандомы, персонажи и предупреждения — метками тайтла.', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Статус произведения переносится в таксономию статусов, возрастная отметка Adult — в отметку 18+, соавторы — в команду проекта, краткое описание — в отрывок, если он пуст.', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Скрытые главы Fictioneer становятся черновиками: в теме XIN-Com такого признака нет, и публиковать их молча было бы неправильно.', 'xi-from-fictioneer' ); ?></li>
			<li><?php esc_html_e( 'Старые адреса /story/… и /chapter/… отвечают перенаправлением на новые — пока плагин включён.', 'xi-from-fictioneer' ); ?></li>
		</ul>

		<p class="description" style="max-width:760px">
			<?php esc_html_e( 'Подборки и рекомендации Fictioneer не переносятся: в теме XIN-Com для них нет своего раздела. Записи остаются в базе нетронутыми — их можно вернуть, снова включив Fictioneer.', 'xi-from-fictioneer' ); ?>
		</p>

		<p style="margin-top:20px">
			<button type="button" class="button button-primary" id="xff-run" <?php disabled( ! $theme || 0 === array_sum( $counts ) ); ?>>
				<?php esc_html_e( 'Перенести', 'xi-from-fictioneer' ); ?>
			</button>
			<?php if ( $done ) : ?>
				<button type="button" class="button" id="xff-undo">
					<?php esc_html_e( 'Отменить перенос', 'xi-from-fictioneer' ); ?>
				</button>
			<?php endif; ?>
		</p>

		<div id="xff-log" style="max-width:760px;margin-top:14px;font-family:monospace;font-size:12px;white-space:pre-wrap"></div>
	</div>

	<script>
	(function () {
		var run = document.getElementById('xff-run');
		var undo = document.getElementById('xff-undo');
		var log = document.getElementById('xff-log');
		var nonce = <?php echo wp_json_encode( wp_create_nonce( 'xff' ) ); ?>;
		var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

		function say(line) { log.textContent += line + '\n'; }

		function step(action) {
			var body = new URLSearchParams();
			body.set('action', action);
			body.set('_wpnonce', nonce);

			return fetch(ajax, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || !res.success) {
						say(<?php echo wp_json_encode( __( 'Ошибка. Обновите страницу и попробуйте ещё раз.', 'xi-from-fictioneer' ) ); ?>);
						return;
					}
					say(res.data.message);
					if (!res.data.done) return step(action);
					say(<?php echo wp_json_encode( __( 'Готово.', 'xi-from-fictioneer' ) ); ?>);
					window.setTimeout(function () { window.location.reload(); }, 1200);
				});
		}

		if (run) {
			run.addEventListener('click', function () {
				run.disabled = true;
				if (undo) undo.disabled = true;
				step('xff_step');
			});
		}

		if (undo) {
			undo.addEventListener('click', function () {
				if (!window.confirm(<?php echo wp_json_encode( __( 'Вернуть записи в типы Fictioneer?', 'xi-from-fictioneer' ) ); ?>)) return;
				undo.disabled = true;
				if (run) run.disabled = true;
				step('xff_undo');
			});
		}
	})();
	</script>
	<?php
}

/**
 * Один заход переноса.
 *
 * @return void
 */
function xff_step() {
	check_ajax_referer( 'xff' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	global $wpdb;

	$state = wp_parse_args( (array) get_option( XFF_STATE, array() ), array( 'stage' => 'posts' ) );

	if ( 'posts' === $state['stage'] ) {
		$moved = xff_move_posts();

		if ( $moved > 0 ) {
			wp_send_json_success( array(
				'done'    => false,
				/* translators: %d: how many posts were converted in this batch. */
				'message' => sprintf( __( 'Переведено записей: %d', 'xi-from-fictioneer' ), $moved ),
			) );
		}

		$state['stage'] = 'order';
		update_option( XFF_STATE, $state, false );

		wp_send_json_success( array( 'done' => false, 'message' => __( 'Записи переведены, расставляю порядок глав…', 'xi-from-fictioneer' ) ) );
	}

	if ( 'order' === $state['stage'] ) {
		$ordered = xff_order_batch();

		if ( $ordered > 0 ) {
			wp_send_json_success( array(
				'done'    => false,
				/* translators: %d: how many titles got their chapter order. */
				'message' => sprintf( __( 'Расставлен порядок глав у тайтлов: %d', 'xi-from-fictioneer' ), $ordered ),
			) );
		}

		$state['stage'] = 'finish';
		update_option( XFF_STATE, $state, false );
	}

	xff_finish();
	delete_option( XFF_STATE );

	wp_send_json_success( array( 'done' => true, 'message' => __( 'Перенос завершён, ссылки обновлены.', 'xi-from-fictioneer' ) ) );
}
add_action( 'wp_ajax_xff_step', 'xff_step' );

/**
 * Переводит порцию записей в наши типы вместе с метой и терминами.
 *
 * @return int Сколько записей обработано.
 */
function xff_move_posts() {
	global $wpdb;

	$types = xff_types();
	$in    = "'" . implode( "','", array_map( 'esc_sql', array_keys( $types ) ) ) . "'";

	// Строка собрана из ключей нашего же массива, пользовательских данных в ней нет.
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT ID, post_type FROM {$wpdb->posts}
			 WHERE post_type IN ({$in}) AND post_status != 'trash'
			 ORDER BY ID ASC LIMIT %d",
			XFF_BATCH
		)
	);

	if ( ! $rows ) {
		return 0;
	}

	foreach ( $rows as $row ) {
		xff_move_one( (int) $row->ID, $row->post_type, $types[ $row->post_type ] );
	}

	return count( $rows );
}

/**
 * Переводит одну запись.
 *
 * @param int    $id   Запись.
 * @param string $from Тип Fictioneer.
 * @param string $to   Наш тип.
 * @return void
 */
function xff_move_one( $id, $from, $to ) {
	global $wpdb;

	$status = get_post_field( 'post_status', $id );

	/*
	 * Скрытая глава Fictioneer не должна от переноса вдруг стать общедоступной:
	 * такого признака у нас нет, поэтому она уходит в черновики.
	 */
	if ( 'fcn_chapter' === $from && get_post_meta( $id, 'fictioneer_chapter_hidden', true ) && 'publish' === $status ) {
		// Запоминаем прежний статус: иначе откат вернёт тип, но глава так и
		// останется черновиком.
		update_post_meta( $id, XFF_BACK_STATUS, $status );
		$status = 'draft';
	}

	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->posts,
		array( 'post_type' => $to, 'post_status' => $status ),
		array( 'ID' => $id )
	);

	/*
	 * Тип меняется запросом мимо wp_update_post(): тот на смену типа записи
	 * не рассчитан и по дороге трогает лишнее. Расплата — кэш записи остаётся
	 * старым, и следующий же get_post_type() отвечает «fcn_chapter». Из-за
	 * этого порядок глав из списка произведения молча не применялся.
	 */
	clean_post_cache( $id );

	update_post_meta( $id, XFF_BACK_META, $from );

	// --- термины -----------------------------------------------------------
	foreach ( xff_taxonomies() as $old_tax => $new_tax ) {
		if ( ! taxonomy_exists( $new_tax ) ) {
			continue;
		}

		$names = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT t.name
				 FROM {$wpdb->term_relationships} tr
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
				 WHERE tr.object_id = %d AND tt.taxonomy = %s",
				$id,
				$old_tax
			)
		);

		if ( $names ) {
			wp_set_object_terms( $id, $names, $new_tax, true );
		}
	}

	// --- мета --------------------------------------------------------------
	if ( 'fcn_chapter' === $from ) {
		$story = (int) get_post_meta( $id, 'fictioneer_chapter_story', true );
		if ( $story ) {
			update_post_meta( $id, '_xin_novel', $story );
		}

		if ( 'Adult' === get_post_meta( $id, 'fictioneer_chapter_rating', true ) ) {
			update_post_meta( $id, '_xin_adult', 1 );
		}

		return;
	}

	// Дальше — только произведения.
	$excerpt = get_post_field( 'post_excerpt', $id );
	$short   = (string) get_post_meta( $id, 'fictioneer_story_short_description', true );

	if ( '' === trim( (string) $excerpt ) && '' !== trim( $short ) ) {
		$wpdb->update( $wpdb->posts, array( 'post_excerpt' => wp_strip_all_tags( $short ) ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_post_cache( $id );
	}

	if ( 'Adult' === get_post_meta( $id, 'fictioneer_story_rating', true ) ) {
		update_post_meta( $id, '_xin_adult', 1 );
	}

	$team = get_post_meta( $id, 'fictioneer_story_co_authors', true );
	if ( is_array( $team ) && $team ) {
		update_post_meta( $id, '_xin_team', array_values( array_map( 'absint', $team ) ) );
	}

	$status_term = xff_status_term( (string) get_post_meta( $id, 'fictioneer_story_status', true ) );
	if ( $status_term && taxonomy_exists( 'novel_status' ) ) {
		wp_set_object_terms( $id, array( $status_term ), 'novel_status', false );
	}
}

/**
 * Статус Fictioneer в слаг нашей таксономии.
 *
 * @param string $status Значение из меты.
 * @return string Слаг или пустая строка.
 */
function xff_status_term( $status ) {
	$map = array(
		'Ongoing'   => 'ongoing',
		'Completed' => 'completed',
		'Oneshot'   => 'completed',
		'Hiatus'    => 'hiatus',
		'Canceled'  => 'canceled',
		'Cancelled' => 'canceled',
	);

	$slug = isset( $map[ $status ] ) ? $map[ $status ] : '';

	if ( ! $slug ) {
		return '';
	}

	/*
	 * «Отменён» в коробке нет: у темы четыре статуса, и отменённый перевод —
	 * не то же самое, что замороженный. Заводим термин, а не подменяем смысл.
	 */
	if ( 'canceled' === $slug && ! term_exists( 'canceled', 'novel_status' ) ) {
		wp_insert_term( __( 'Отменён', 'xi-from-fictioneer' ), 'novel_status', array( 'slug' => 'canceled' ) );
	}

	return $slug;
}

/**
 * Расставляет номера глав по порядку из списка произведения.
 *
 * @return int Сколько тайтлов обработано.
 */
function xff_order_batch() {
	$novels = get_posts( array(
		'post_type'      => 'novel',
		'post_status'    => 'any',
		'posts_per_page' => XFF_BATCH,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'AND',
			array( 'key' => XFF_BACK_META, 'value' => 'fcn_story' ),
			array( 'key' => '_xff_ordered', 'compare' => 'NOT EXISTS' ),
		),
	) );

	if ( ! $novels ) {
		return 0;
	}

	foreach ( $novels as $novel_id ) {
		$list = get_post_meta( $novel_id, 'fictioneer_story_chapters', true );
		$list = is_array( $list ) ? array_values( array_filter( array_map( 'absint', $list ) ) ) : array();

		$number = 0;
		foreach ( $list as $chapter_id ) {
			if ( 'chapter' !== get_post_type( $chapter_id ) ) {
				continue;
			}

			++$number;
			update_post_meta( $chapter_id, '_xin_number', $number );
			update_post_meta( $chapter_id, '_xin_novel', (int) $novel_id );
		}

		/*
		 * Главы, которых не оказалось в списке произведения, всё равно должны
		 * получить номер — иначе они провалятся в конец оглавления без порядка.
		 */
		$orphans = get_posts( array(
			'post_type'      => 'chapter',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array( 'key' => '_xin_novel', 'value' => (int) $novel_id ),
				array( 'key' => '_xin_number', 'compare' => 'NOT EXISTS' ),
			),
		) );

		foreach ( $orphans as $chapter_id ) {
			++$number;
			update_post_meta( $chapter_id, '_xin_number', $number );
		}

		update_post_meta( $novel_id, '_xff_ordered', 1 );

		// Кэши числа глав и даты последней главы должны пересчитаться заново.
		if ( function_exists( 'xin_forget_chapter_count' ) ) {
			xin_forget_chapter_count( $novel_id );
		}
		if ( function_exists( 'xin_chapters_cache_bust' ) ) {
			xin_chapters_cache_bust( $novel_id );
		}
	}

	return count( $novels );
}

/**
 * Завершение: чистим служебное и обновляем правила адресов.
 *
 * @return void
 */
function xff_finish() {
	global $wpdb;

	$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_xff_ordered' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery

	flush_rewrite_rules();
	do_action('xff_import_complete');
}

/**
 * Возвращает порцию записей в типы Fictioneer.
 *
 * Вынесено из обработчика запроса отдельной функцией: так откат можно
 * прогнать и проверить, не притворяясь браузером.
 *
 * @return int Сколько записей возвращено.
 */
function xff_undo_batch() {
	global $wpdb;

	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s ORDER BY post_id ASC LIMIT %d",
			XFF_BACK_META,
			XFF_BATCH
		)
	);

	if ( ! $rows ) {
		return 0;
	}

	foreach ( $rows as $row ) {
		$id     = (int) $row->post_id;
		$fields = array( 'post_type' => $row->meta_value );
		$back   = get_post_meta( $id, XFF_BACK_STATUS, true );

		if ( $back ) {
			$fields['post_status'] = $back;
			delete_post_meta( $id, XFF_BACK_STATUS );
		}

		$wpdb->update( $wpdb->posts, $fields, array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_post_cache( $id );
		delete_post_meta( $id, XFF_BACK_META );
	}

	return count( $rows );
}

/**
 * Откат: записи возвращаются в типы Fictioneer.
 *
 * @return void
 */
function xff_undo() {
	check_ajax_referer( 'xff' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$count = xff_undo_batch();

	if ( ! $count ) {
		flush_rewrite_rules();
		wp_send_json_success( array( 'done' => true, 'message' => __( 'Записи возвращены в типы Fictioneer.', 'xi-from-fictioneer' ) ) );
	}

	wp_send_json_success( array(
		'done'    => false,
		/* translators: %d: how many posts were reverted in this batch. */
		'message' => sprintf( __( 'Возвращено записей: %d', 'xi-from-fictioneer' ), $count ),
	) );
}
add_action( 'wp_ajax_xff_undo', 'xff_undo' );

/**
 * Старые адреса Fictioneer отвечают перенаправлением.
 *
 * Слаги записей при переносе не менялись, поэтому найти запись по адресу
 * несложно. Живёт ровно столько, сколько включён плагин: это мост, а не
 * постоянная часть сайта.
 *
 * @return void
 */
function xff_redirect_old_urls() {
	if ( ! is_404() ) {
		return;
	}

	$path = trim( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
	$bits = explode( '/', $path );

	if ( count( $bits ) < 2 || ! in_array( $bits[0], array( 'story', 'chapter' ), true ) ) {
		return;
	}

	$slug = sanitize_title( end( $bits ) );
	$type = 'story' === $bits[0] ? 'novel' : 'chapter';
	$post = get_page_by_path( $slug, OBJECT, $type );

	if ( $post ) {
		wp_safe_redirect( get_permalink( $post ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'xff_redirect_old_urls', 1 );
