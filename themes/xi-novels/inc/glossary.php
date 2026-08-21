<?php
/**
 * Словарь проекта: правила, которые ведёт переводчик.
 *
 * Читателю они приходят вместе с главой и работают как его собственные —
 * с той разницей, что своё правило всегда старше. Правила можно применить
 * и к самому тексту глав: тогда замена уходит в базу.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XIN_GLOSSARY_META  = '_xin_glossary';
const XIN_GLOSSARY_LIMIT = 300;

/**
 * Правила проекта.
 *
 * @param int $novel_id ID тайтла.
 * @return array
 */
function xin_glossary_rules( $novel_id ) {
	$novel_id = (int) $novel_id;
	if ( ! $novel_id ) {
		return array();
	}

	$raw = get_post_meta( $novel_id, XIN_GLOSSARY_META, true );
	if ( ! $raw ) {
		return array();
	}

	if ( is_string( $raw ) ) {
		$raw = json_decode( $raw, true );
	}

	return xin_glossary_clean( $raw );
}

/**
 * Приводит любой вход — массив, карту «термин => замена» или строки — к списку правил.
 *
 * @param mixed $raw Сырые данные.
 * @return array
 */
function xin_glossary_clean( $raw ) {
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

		$fold = function_exists( 'mb_strtolower' ) ? mb_strtolower( $from, 'UTF-8' ) : strtolower( $from );
		if ( isset( $seen[ $fold ] ) || count( $rules ) >= XIN_GLOSSARY_LIMIT ) {
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
 * @param int   $novel_id ID тайтла.
 * @param mixed $raw      Сырые правила.
 * @return array Записанный список.
 */
function xin_glossary_save( $novel_id, $raw ) {
	$rules = xin_glossary_clean( $raw );

	if ( $rules ) {
		update_post_meta( $novel_id, XIN_GLOSSARY_META, wp_json_encode( $rules ) );
	} else {
		delete_post_meta( $novel_id, XIN_GLOSSARY_META );
	}

	return $rules;
}

/**
 * Готовит правила к работе: длинные вперёд, выключенные прочь.
 *
 * @param array $rules Правила.
 * @return array
 */
function xin_glossary_order( $rules ) {
	$rules = array_values( array_filter( $rules, function ( $rule ) {
		return ! empty( $rule['from'] );
	} ) );

	usort( $rules, function ( $a, $b ) {
		return xin_glossary_len( $b['from'] ) - xin_glossary_len( $a['from'] );
	} );

	return $rules;
}

/**
 * Длина в символах, а не в байтах.
 *
 * @param string $value Строка.
 * @return int
 */
function xin_glossary_len( $value ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

/**
 * Переносит регистр найденного куска на замену.
 *
 * @param array  $rule    Правило.
 * @param string $matched Что нашлось в тексте.
 * @return string
 */
function xin_glossary_recase( $rule, $matched ) {
	$to = $rule['to'];

	if ( empty( $rule['ci'] ) || '' === $to || $matched === $rule['from'] ) {
		return $to;
	}

	$upper = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $matched, 'UTF-8' ) : strtoupper( $matched );
	$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $matched, 'UTF-8' ) : strtolower( $matched );

	if ( xin_glossary_len( $matched ) > 1 && $matched === $upper && $matched !== $lower ) {
		return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $to, 'UTF-8' ) : strtoupper( $to );
	}

	$first = function_exists( 'mb_substr' ) ? mb_substr( $matched, 0, 1, 'UTF-8' ) : substr( $matched, 0, 1 );
	$flat  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $first, 'UTF-8' ) : strtolower( $first );

	if ( $first !== $flat ) {
		$head = function_exists( 'mb_substr' ) ? mb_substr( $to, 0, 1, 'UTF-8' ) : substr( $to, 0, 1 );
		$rest = function_exists( 'mb_substr' ) ? mb_substr( $to, 1, null, 'UTF-8' ) : substr( $to, 1 );
		$head = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $head, 'UTF-8' ) : strtoupper( $head );

		return $head . $rest;
	}

	return $to;
}

/**
 * Замена в обычном тексте. Возвращает текст и число замен.
 *
 * @param string $text  Текст.
 * @param array  $rules Правила.
 * @return array {text, count}
 */
function xin_glossary_replace_text( $text, $rules ) {
	$rules = xin_glossary_order( $rules );

	if ( ! $rules || '' === $text ) {
		return array( 'text' => $text, 'count' => 0 );
	}

	$pattern = '/' . implode( '|', array_map( function ( $rule ) {
		return preg_quote( $rule['from'], '/' );
	}, $rules ) ) . '/iu';

	$count = 0;

	$out = preg_replace_callback(
		$pattern,
		function ( $m ) use ( $rules, &$count, $text ) {
			$found  = $m[0][0];
			$offset = $m[0][1];

			foreach ( $rules as $rule ) {
				$length = strlen( $rule['from'] );
				$chunk  = substr( $text, $offset, $length );

				if ( '' === $chunk || strlen( $chunk ) !== $length ) {
					continue;
				}

				if ( ! empty( $rule['ci'] ) ) {
					$same = 0 === strcasecmp( $chunk, $rule['from'] )
						|| ( function_exists( 'mb_strtolower' )
							&& mb_strtolower( $chunk, 'UTF-8' ) === mb_strtolower( $rule['from'], 'UTF-8' ) );
				} else {
					$same = $chunk === $rule['from'];
				}

				if ( ! $same ) {
					continue;
				}

				if ( ! empty( $rule['whole'] ) ) {
					$before = substr( $text, 0, $offset );
					$after  = substr( $text, $offset + $length );

					if ( preg_match( '/[\p{L}\p{N}_]$/u', $before ) || preg_match( '/^[\p{L}\p{N}_]/u', $after ) ) {
						continue;
					}
				}

				$count++;

				return xin_glossary_recase( $rule, $chunk );
			}

			return $found;
		},
		$text,
		-1,
		$ignored,
		PREG_OFFSET_CAPTURE
	);

	unset( $ignored );

	return array( 'text' => null === $out ? $text : $out, 'count' => $count );
}

/**
 * Замена в разметке: теги и атрибуты не трогаются.
 *
 * @param string $html  Разметка.
 * @param array  $rules Правила.
 * @return array {html, count}
 */
function xin_glossary_replace_html( $html, $rules ) {
	if ( '' === trim( (string) $html ) ) {
		return array( 'html' => $html, 'count' => 0 );
	}

	$parts = preg_split( '/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$count = 0;
	$out   = '';
	$skip  = 0;

	foreach ( $parts as $part ) {
		if ( '' === $part ) {
			continue;
		}

		if ( '<' === $part[0] ) {
			if ( preg_match( '/^<\s*(script|style|code|pre)\b/i', $part ) ) {
				$skip++;
			} elseif ( preg_match( '/^<\s*\/\s*(script|style|code|pre)\s*>/i', $part ) && $skip > 0 ) {
				$skip--;
			}
			$out .= $part;
			continue;
		}

		if ( $skip > 0 ) {
			$out .= $part;
			continue;
		}

		$result = xin_glossary_replace_text( $part, $rules );
		$count += $result['count'];
		$out   .= $result['text'];
	}

	return array( 'html' => $out, 'count' => $count );
}

/**
 * Прогоняет словарь по всем главам тайтла.
 *
 * @param int   $novel_id ID тайтла.
 * @param array $rules    Правила.
 * @param bool  $dry      true — только посчитать, ничего не записывая.
 * @return array {chapters, hits, touched}
 */
function xin_glossary_bulk( $novel_id, $rules, $dry = true ) {
	$chapters = xin_get_chapters( $novel_id, 'ASC' );
	$hits     = 0;
	$touched  = 0;

	foreach ( $chapters as $chapter ) {
		$result = xin_glossary_replace_html( $chapter->post_content, $rules );

		if ( ! $result['count'] ) {
			continue;
		}

		$hits += $result['count'];
		$touched++;

		if ( ! $dry ) {
			wp_update_post( array(
				'ID'           => $chapter->ID,
				'post_content' => $result['html'],
			) );
		}
	}

	if ( ! $dry && $touched ) {
		wp_cache_flush();
	}

	return array(
		'chapters' => count( $chapters ),
		'hits'     => $hits,
		'touched'  => $touched,
	);
}

/**
 * Словарь тайтла в виде, готовом для JS.
 *
 * @param int $novel_id ID тайтла.
 * @return array
 */
function xin_glossary_for_js( $novel_id ) {
	$out = array();

	foreach ( xin_glossary_rules( $novel_id ) as $rule ) {
		$out[] = array(
			'from'  => $rule['from'],
			'to'    => $rule['to'],
			'ci'    => (bool) $rule['ci'],
			'whole' => (bool) $rule['whole'],
		);
	}

	return $out;
}

/**
 * Разбирает textarea вида «было = стало» в правила.
 *
 * @param string $text Текст из формы.
 * @return array
 */
function xin_glossary_parse_lines( $text ) {
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

/**
 * Правила обратно в текст для textarea.
 *
 * @param array $rules Правила.
 * @return string
 */
function xin_glossary_to_lines( $rules ) {
	$lines = array();

	foreach ( $rules as $rule ) {
		$lines[] = $rule['from'] . ' = ' . $rule['to'];
	}

	return implode( "\n", $lines );
}
