<?php
/**
 * Глоссарий проекта.
 *
 * У долгого перевода набирается свой словарь: имена, титулы, названия
 * техник. Читатель, зашедший на триста восьмидесятой главе, половины из них
 * не помнит — поэтому термин в тексте подчёркивается, а по нажатию
 * показывает пояснение.
 *
 * Хранилище — `_xin_glossary` в формате XIN-Com: словарь, набранный там,
 * работает здесь без переноса.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XV_GLOSSARY      = '_xin_glossary';
const XV_GLOSSARY_MAX  = 300;

/**
 * Словарь тайтла.
 */
function xv_glossary( $novel_id ) {
	$novel_id = (int) $novel_id;

	if ( ! $novel_id ) {
		return array();
	}

	$raw = get_post_meta( $novel_id, XV_GLOSSARY, true );

	if ( ! $raw ) {
		return array();
	}

	if ( is_string( $raw ) ) {
		$raw = json_decode( $raw, true );
	}

	return xv_glossary_clean( $raw );
}

/**
 * Приводит любой вход к списку правил.
 *
 * Вход бывает трёх видов: список правил, карта «термин => пояснение» и
 * строки из формы. Разбирать это в трёх местах по-разному — верный способ
 * получить три разных поведения.
 */
function xv_glossary_clean( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$rules = array();
	$seen  = array();

	foreach ( $raw as $key => $item ) {
		if ( is_string( $item ) && ! is_numeric( $key ) ) {
			$item = array( 'from' => $key, 'to' => $item );
		}

		if ( ! is_array( $item ) ) {
			continue;
		}

		$from = isset( $item['from'] ) ? trim( wp_strip_all_tags( (string) $item['from'] ) ) : '';

		if ( '' === $from ) {
			continue;
		}

		$fold = mb_strtolower( $from, 'UTF-8' );

		if ( isset( $seen[ $fold ] ) || count( $rules ) >= XV_GLOSSARY_MAX ) {
			continue;
		}

		$seen[ $fold ] = true;

		$rules[] = array(
			'from'  => $from,
			'to'    => isset( $item['to'] ) ? trim( wp_strip_all_tags( (string) $item['to'] ) ) : '',
			'ci'    => ! isset( $item['ci'] ) || ! empty( $item['ci'] ),
			'whole' => ! empty( $item['whole'] ),
			'note'  => isset( $item['note'] ) ? sanitize_text_field( (string) $item['note'] ) : '',
		);
	}

	return $rules;
}

/**
 * Пишет словарь в тайтл.
 *
 * Массивом, а не строкой JSON: update_post_meta() снимает слеши, и
 * экранирование \uXXXX от wp_json_encode() разрушается — кириллица
 * превращалась в «u0441u043b…». Массив WordPress сериализует сам, а
 * чтение всё равно понимает оба вида — словари XIN-Com лежат строкой.
 */
function xv_glossary_save( $novel_id, $raw ) {
	$rules = xv_glossary_clean( $raw );

	if ( $rules ) {
		update_post_meta( $novel_id, XV_GLOSSARY, $rules );
	} else {
		delete_post_meta( $novel_id, XV_GLOSSARY );
	}

	xv_forget_glossary( $novel_id );

	return $rules;
}

/**
 * Строки формы в правила и обратно. Формат — «термин = пояснение», строка на
 * правило, решётка в начале — комментарий.
 */
function xv_glossary_from_lines( $text ) {
	$rules = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );

		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			continue;
		}

		$parts = preg_split( '/\s*(?:=>|=|\|)\s*/', $line, 2 );

		if ( ! isset( $parts[0] ) || '' === trim( $parts[0] ) ) {
			continue;
		}

		$rules[] = array(
			'from' => trim( $parts[0] ),
			'to'   => isset( $parts[1] ) ? trim( $parts[1] ) : '',
		);
	}

	return $rules;
}

function xv_glossary_to_lines( $rules ) {
	$lines = array();

	foreach ( $rules as $rule ) {
		$lines[] = $rule['from'] . ( '' !== $rule['to'] ? ' = ' . $rule['to'] : '' );
	}

	return implode( "\n", $lines );
}

/* -------------------------------------------------------------------------
 * Разметка терминов в тексте
 * ---------------------------------------------------------------------- */

/**
 * Готовый к работе словарь: длинные термины первыми.
 *
 * Порядок решает всё. Если «Небесный меч» стоит после «меч», то в тексте
 * подсветится только второе слово, и подсказка окажется не о том.
 */
function xv_glossary_ready( $novel_id ) {
	$key = 'xv_gloss_' . (int) $novel_id;
	$hit = wp_cache_get( $key, 'xin-v2' );

	if ( is_array( $hit ) ) {
		return $hit;
	}

	$rules = array_values( array_filter( xv_glossary( $novel_id ), function ( $rule ) {
		return '' !== $rule['to'] || '' !== $rule['note'];
	} ) );

	usort( $rules, function ( $a, $b ) {
		return mb_strlen( $b['from'] ) <=> mb_strlen( $a['from'] );
	} );

	wp_cache_set( $key, $rules, 'xin-v2', HOUR_IN_SECONDS );

	return $rules;
}

function xv_forget_glossary( $novel_id ) {
	wp_cache_delete( 'xv_gloss_' . (int) $novel_id, 'xin-v2' );
}

/**
 * Подчёркивает термины в тексте главы.
 *
 * Разбор идёт по кускам между тегами: замена по всей строке ломала бы
 * атрибуты и превращала «alt="меч"» в разметку. Внутри ссылок и заголовков
 * ничего не трогаем — там подсказка мешает.
 */
function xv_glossary_mark( $html, $novel_id ) {
	$rules = xv_glossary_ready( $novel_id );

	if ( ! $rules ) {
		return $html;
	}

	$parts = preg_split( '~(<[^>]+>)~', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$skip  = 0;
	$out   = '';

	foreach ( $parts as $part ) {
		if ( '' === $part ) {
			continue;
		}

		if ( '<' === $part[0] ) {
			if ( preg_match( '~^<\s*(a|h[1-6]|code|pre|script|style)\b~i', $part ) ) {
				$skip++;
			} elseif ( preg_match( '~^<\s*/\s*(a|h[1-6]|code|pre|script|style)\b~i', $part ) && $skip > 0 ) {
				$skip--;
			}

			$out .= $part;
			continue;
		}

		$out .= $skip ? $part : xv_glossary_replace( $part, $rules );
	}

	return $out;
}

/**
 * Каждый термин подсвечивается один раз на абзац: сплошное подчёркивание
 * читать невозможно.
 */
function xv_glossary_replace( $text, $rules ) {
	$used = array();

	foreach ( $rules as $rule ) {
		if ( isset( $used[ $rule['from'] ] ) ) {
			continue;
		}

		$bound = $rule['whole'] ? '\b' : '';
		$flags = 'u' . ( $rule['ci'] ? 'i' : '' );
		$hint  = '' !== $rule['note'] ? $rule['note'] : $rule['to'];

		$text = preg_replace_callback(
			'~' . $bound . preg_quote( $rule['from'], '~' ) . $bound . '~' . $flags,
			function ( $m ) use ( $hint, &$used, $rule ) {
				if ( isset( $used[ $rule['from'] ] ) ) {
					return $m[0];
				}

				$used[ $rule['from'] ] = true;

				return '<span class="xv-term" tabindex="0" data-xv-term="' . esc_attr( $hint ) . '">' . $m[0] . '</span>';
			},
			$text,
			1
		);
	}

	return $text;
}

/**
 * Включаем разметку только в тексте главы: в анонсах и лентах подсказки
 * бесполезны, а разметку тащат.
 */
function xv_glossary_on_content( $html ) {
	$is_chapter_page = is_singular( 'chapter' );
	$is_chapter_rest = defined( 'REST_REQUEST' ) && REST_REQUEST && 'chapter' === get_post_type();

	if ( ( ! $is_chapter_page && ! $is_chapter_rest ) || is_feed() || is_admin() ) {
		return $html;
	}

	if ( ! xv_opt( 'glossary', true ) ) {
		return $html;
	}

	$novel_id = (int) get_post_meta( get_the_ID(), '_xin_novel', true );

	return $novel_id ? xv_glossary_mark( $html, $novel_id ) : $html;
}
add_filter( 'the_content', 'xv_glossary_on_content', 14 );

/**
 * Сбрасываем словарь тайтла при сохранении — иначе правка не видна до
 * истечения кэша.
 */
function xv_glossary_forget_on_save( $post_id, $post ) {
	if ( $post instanceof WP_Post && 'novel' === $post->post_type ) {
		xv_forget_glossary( $post_id );
	}
}
add_action( 'save_post', 'xv_glossary_forget_on_save', 20, 2 );

/* -------------------------------------------------------------------------
 * Правка из кабинета
 * ---------------------------------------------------------------------- */

function xv_handle_glossary_save() {
	xv_guard( 'xv_save_glossary' );

	$novel_id = isset( $_POST['novel_id'] ) ? (int) $_POST['novel_id'] : 0;

	if ( ! $novel_id || ! current_user_can( 'edit_post', $novel_id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'xin-v2' ), 403 );
	}

	$lines = isset( $_POST['glossary'] ) ? sanitize_textarea_field( wp_unslash( $_POST['glossary'] ) ) : '';

	xv_glossary_save( $novel_id, xv_glossary_from_lines( $lines ) );

	xv_back( array( 'work' => $novel_id, 'view' => 'glossary' ), '', 'saved' );
}
add_action( 'admin_post_xv_save_glossary', 'xv_handle_glossary_save' );
