<?php
/**
 * Настройка WordPress под тему.
 *
 * Тема ждёт от установки нескольких вещей, которых в свежем WordPress нет:
 * человеческих постоянных ссылок, отдельной страницы блога, назначенного меню.
 * Без них она не ломается с грохотом — она тихо работает хуже: адреса глав
 * сваливаются в `?p=123`, ссылка «Блог» пропадает из шапки, меню подставляется
 * запасное. Такие поломки замечают недели спустя, поэтому здесь они собраны в
 * список, который видно целиком.
 *
 * Главное правило: **осознанный выбор не переписывается**. Плагин чинит только
 * то, что стоит по умолчанию и явно мешает. Если структура ссылок выбрана —
 * пусть даже неудачно, — это чужое решение, и трогать его молча нельзя.
 *
 * @package XI_Studio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Все проверки установки.
 *
 * Каждая умеет рассказать о себе три вещи: в порядке ли она, что не так и что
 * будет сделано. Шаг без `apply` — совет: его нельзя выполнить за пользователя,
 * потому что правильного ответа не знает никто, кроме него.
 *
 * @return array<string, array>
 */
function xis_setup_steps() {
	return array(

		'permalinks' => array(
			'title' => __( 'Постоянные ссылки', 'xi-studio' ),
			'why'   => __( 'Тема строит адреса тайтлов и глав сама: /novels/тайтл/ и /comics/тайтл/глава/. При ссылках «по умолчанию» переписывание адресов в WordPress выключено целиком, и всё это превращается в ?p=123.', 'xi-studio' ),
			'check' => static function () {
				$structure = get_option( 'permalink_structure' );

				return array(
					'ok'     => (bool) $structure,
					'detail' => $structure
						? sprintf( /* translators: %s — структура ссылок. */ __( 'Сейчас: %s', 'xi-studio' ), $structure )
						: __( 'Сейчас: простые ссылки вида ?p=123', 'xi-studio' ),
				);
			},
			'plan'  => __( 'Поставлю /%postname%/ и пересоберу правила адресов.', 'xi-studio' ),
			'apply' => static function () {
				global $wp_rewrite;

				/*
				 * `set_permalink_structure()` сравнивает новое значение с тем, что
				 * лежит у объекта в памяти, а не с базой, и при совпадении молча
				 * ничего не делает. Если структуру меняли мимо него — например
				 * другим плагином в этом же запросе, — объект помнит устаревшее, и
				 * настройка тихо не применяется. `init()` заставляет перечитать.
				 */
				$wp_rewrite->init();
				$wp_rewrite->set_permalink_structure( '/%postname%/' );
				xis_setup_request_flush();

				return __( 'Структура ссылок: /%postname%/', 'xi-studio' );
			},
		),

		'pages' => array(
			'title' => __( 'Служебные страницы', 'xi-studio' ),
			'why'   => __( 'Кабинет автора, библиотека, рейтинг, уголок читателя, PLUS и остальные экраны темы — это страницы WordPress с назначенными шаблонами. Пока страницы нет, ссылка на неё ведёт в 404.', 'xi-studio' ),
			'check' => static function () {
				$missing = xis_setup_missing_pages();

				return array(
					'ok'     => ! $missing,
					'detail' => $missing
						? sprintf( /* translators: %s — список слагов. */ __( 'Не хватает: %s', 'xi-studio' ), implode( ', ', $missing ) )
						: __( 'Все страницы на месте.', 'xi-studio' ),
				);
			},
			'plan'  => __( 'Создам недостающие страницы и назначу им шаблоны темы.', 'xi-studio' ),
			'apply' => static function () {
				if ( ! function_exists( 'xin_create_pages' ) ) {
					return new WP_Error( 'xis_theme', __( 'Тема не активна: создавать страницы нечем.', 'xi-studio' ) );
				}

				/*
				 * Создаёт страницы сама тема — у неё же лежит и карта шаблонов.
				 * Копия карты здесь разошлась бы с темой при первом же новом
				 * экране, причём молча.
				 */
				$made = xin_create_pages();

				return sprintf( /* translators: %d — сколько страниц создано. */ __( 'Создано страниц: %d', 'xi-studio' ), (int) $made );
			},
		),

		'front' => array(
			'title' => __( 'Главная и блог', 'xi-studio' ),
			'why'   => __( 'Пока WordPress показывает на главной ленту записей, у блога нет собственной страницы — и ссылка «Блог» не появляется ни в шапке, ни в подвале: тема выводит её только тогда, когда страница блога задана.', 'xi-studio' ),
			'check' => static function () {
				$ok = 'page' === get_option( 'show_on_front' ) && get_option( 'page_for_posts' );

				return array(
					'ok'     => (bool) $ok,
					'detail' => $ok
						? sprintf( /* translators: %s — заголовок страницы блога. */ __( 'Блог: «%s»', 'xi-studio' ), get_the_title( (int) get_option( 'page_for_posts' ) ) )
						: __( 'На главной лента записей, отдельной страницы блога нет.', 'xi-studio' ),
				);
			},
			'plan'  => __( 'Заведу страницы «Главная» и «Блог» и назначу их в настройках чтения.', 'xi-studio' ),
			'apply' => static function () {
				$front = xis_setup_page( 'home', __( 'Главная', 'xi-studio' ) );
				$blog  = xis_setup_page( 'blog', __( 'Блог', 'xi-studio' ) );

				if ( is_wp_error( $front ) ) {
					return $front;
				}

				if ( is_wp_error( $blog ) ) {
					return $blog;
				}

				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $front );
				update_option( 'page_for_posts', $blog );

				return __( 'Главная и блог назначены.', 'xi-studio' );
			},
		),

		'menu' => array(
			'title' => __( 'Главное меню', 'xi-studio' ),
			'why'   => __( 'Без назначенного меню тема рисует запасной набор ссылок. Он рабочий, но им нельзя управлять: ни порядок поменять, ни свой пункт добавить.', 'xi-studio' ),
			'check' => static function () {
				$ok = has_nav_menu( 'primary' );

				return array(
					'ok'     => $ok,
					'detail' => $ok
						? __( 'Меню назначено.', 'xi-studio' )
						: __( 'Место «Главное меню» пустует, показывается запасной набор ссылок.', 'xi-studio' ),
				);
			},
			'plan'  => __( 'Соберу меню из каталога, обновлений, рейтинга и уголка читателя и поставлю его на место главного.', 'xi-studio' ),
			'apply' => 'xis_setup_build_menu',
		),

		'timezone' => array(
			'title' => __( 'Часовой пояс', 'xi-studio' ),
			'why'   => __( 'От него зависят «сегодня» и «вчера» в ленте обновлений, «9 часов назад» у глав и день недели в расписании комиксов. При нулевом смещении всё это считается по Гринвичу и расходится с тем, что видит читатель.', 'xi-studio' ),
			'check' => static function () {
				$zone = get_option( 'timezone_string' );
				$off  = (float) get_option( 'gmt_offset' );
				$ok   = $zone || 0.0 !== $off;

				return array(
					'ok'     => $ok,
					'detail' => $ok
						? sprintf( /* translators: %s — часовой пояс сайта. */ __( 'Сейчас: %s', 'xi-studio' ), $zone ? $zone : sprintf( 'UTC%+g', $off ) )
						: __( 'Стоит UTC. Если площадка не в Лондоне, даты будут врать.', 'xi-studio' ),
				);
			},
			/*
			 * Единственный шаг без `apply`: угадывать часовой пояс за
			 * пользователя — значит с ненулевой вероятностью молча сдвинуть все
			 * даты на сайте. Пусть лучше выберет сам.
			 */
			'plan'  => __( 'Выберите пояс сами в «Настройки → Общие»: подставить его за вас — значит угадывать.', 'xi-studio' ),
			'apply' => null,
		),
	);
}

/**
 * Каких служебных страниц не хватает.
 *
 * @return string[] Слаги.
 */
function xis_setup_missing_pages() {
	$slugs = array( 'account', 'dashboard', 'manage', 'library', 'ranking', 'hub', 'become-author', 'plus', 'help', 'rules', 'contacts' );

	return array_values( array_filter(
		$slugs,
		static function ( $slug ) {
			return ! get_page_by_path( $slug );
		}
	) );
}

/**
 * Находит или создаёт страницу.
 *
 * @param string $slug  Слаг.
 * @param string $title Заголовок.
 * @return int|WP_Error
 */
function xis_setup_page( $slug, $title ) {
	$page = get_page_by_path( $slug );

	if ( $page ) {
		return (int) $page->ID;
	}

	$id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
	), true );

	return is_wp_error( $id ) ? $id : (int) $id;
}

/**
 * Собирает главное меню.
 *
 * @return string|WP_Error
 */
function xis_setup_build_menu() {
	$name = __( 'Главное меню', 'xi-studio' );
	$menu = wp_get_nav_menu_object( $name );

	if ( ! $menu ) {
		$id = wp_create_nav_menu( $name );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$menu = wp_get_nav_menu_object( $id );
	}

	/*
	 * Пункты собираются архивами и страницами, а не готовыми адресами: адрес,
	 * записанный в пункт меню, переживает переезд на другой домен только до
	 * первого клика.
	 */
	$items = array(
		array( 'type' => 'post_type_archive', 'object' => 'novel', 'title' => __( 'Каталог', 'xi-studio' ) ),
		array( 'type' => 'post_type_archive', 'object' => 'chapter', 'title' => __( 'Обновления', 'xi-studio' ) ),
	);

	foreach ( array( 'ranking' => __( 'Рейтинг', 'xi-studio' ), 'hub' => __( 'Уголок читателя', 'xi-studio' ) ) as $slug => $title ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			$items[] = array(
				'type'      => 'post_type',
				'object'    => 'page',
				'object_id' => (int) $page->ID,
				'title'     => $title,
			);
		}
	}

	/*
	 * Блог попадает в меню только если у него есть своя страница. Запасной набор
	 * ссылок в теме показывает его по тому же условию — собранное меню не должно
	 * оказаться беднее того, что оно заменяет.
	 */
	$blog = (int) get_option( 'page_for_posts' );

	if ( $blog ) {
		$items[] = array(
			'type'      => 'post_type',
			'object'    => 'page',
			'object_id' => $blog,
			'title'     => __( 'Блог', 'xi-studio' ),
		);
	}

	$existing = wp_get_nav_menu_items( $menu->term_id );
	$added    = 0;

	foreach ( $items as $position => $item ) {
		if ( xis_setup_menu_has( $existing, $item ) ) {
			continue;
		}

		wp_update_nav_menu_item( $menu->term_id, 0, array(
			'menu-item-title'     => $item['title'],
			'menu-item-type'      => $item['type'],
			'menu-item-object'    => $item['object'],
			'menu-item-object-id' => isset( $item['object_id'] ) ? $item['object_id'] : 0,
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position + 1,
		) );

		++$added;
	}

	$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu->term_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return sprintf( /* translators: %d — сколько пунктов добавлено. */ __( 'Меню назначено, пунктов добавлено: %d', 'xi-studio' ), $added );
}

/**
 * Есть ли уже такой пункт в меню.
 *
 * @param array $existing Пункты меню.
 * @param array $item     Что ищем.
 * @return bool
 */
function xis_setup_menu_has( $existing, $item ) {
	foreach ( (array) $existing as $menu_item ) {
		if ( $menu_item->type !== $item['type'] || $menu_item->object !== $item['object'] ) {
			continue;
		}

		if ( ! isset( $item['object_id'] ) || (int) $menu_item->object_id === (int) $item['object_id'] ) {
			return true;
		}
	}

	return false;
}

/* -------------------------------------------------------------------------
 * Выполнение
 * ---------------------------------------------------------------------- */

/**
 * Просит пересобрать правила адресов на следующем запросе.
 *
 * Не сейчас: правила регистрируются на `init`, а этот код чаще всего работает
 * позже — пересобирать было бы нечего.
 *
 * @return void
 */
function xis_setup_request_flush() {
	update_option( 'xis_flush_rules', 1, false );
}

/**
 * Пересобирает правила, если просили.
 *
 * @return void
 */
function xis_setup_maybe_flush() {
	if ( ! get_option( 'xis_flush_rules' ) ) {
		return;
	}

	delete_option( 'xis_flush_rules' );
	flush_rewrite_rules( false );
}
add_action( 'init', 'xis_setup_maybe_flush', 99 );

/**
 * Выполняет все шаги, которые не в порядке и поддаются починке.
 *
 * @return array{done: string[], failed: string[]}
 */
function xis_setup_run() {
	$report = array(
		'done'   => array(),
		'failed' => array(),
	);

	foreach ( xis_setup_steps() as $id => $step ) {
		if ( ! $step['apply'] ) {
			continue;
		}

		$state = call_user_func( $step['check'] );

		if ( ! empty( $state['ok'] ) ) {
			continue;
		}

		$result = call_user_func( $step['apply'] );

		if ( is_wp_error( $result ) ) {
			$report['failed'][] = $step['title'] . ': ' . $result->get_error_message();
			continue;
		}

		$report['done'][] = $result;
	}

	return $report;
}

/**
 * Настройка при включении плагина.
 *
 * Молча выполняется только то, что стоит по умолчанию: чужие решения не
 * переписываются, а отчёт показывается сразу после включения — чтобы
 * «автоматически» не означало «неизвестно что».
 *
 * @return void
 */
function xis_setup_on_activate() {
	$report = xis_setup_run();

	update_option( 'xis_setup_report', $report, false );
}
register_activation_hook( XIS_FILE, 'xis_setup_on_activate' );

/**
 * Показывает отчёт один раз.
 *
 * @return void
 */
function xis_setup_notice() {
	$report = get_option( 'xis_setup_report' );

	if ( ! is_array( $report ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	delete_option( 'xis_setup_report' );

	if ( ! $report['done'] && ! $report['failed'] ) {
		return;
	}

	echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'XI Studio настроила сайт под тему:', 'xi-studio' ) . '</strong></p><ul style="margin-left:18px;list-style:disc">';

	foreach ( $report['done'] as $line ) {
		echo '<li>' . esc_html( $line ) . '</li>';
	}

	foreach ( $report['failed'] as $line ) {
		echo '<li style="color:#b32d2e">' . esc_html( $line ) . '</li>';
	}

	echo '</ul></div>';
}
add_action( 'admin_notices', 'xis_setup_notice' );
