<?php
/**
 * Облик темы: набор ручек, из которых собирается CSS с токенами.
 *
 * Одно место, где описано, что вообще можно крутить. Отсюда строятся
 * контролы кастомайзера, экран плагина «Студия темы» и сам CSS —
 * генератор один, поэтому предпросмотр не может разойтись с сайтом.
 *
 * @package XI_Novels
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Все настраиваемые параметры облика.
 *
 * @return array
 */
function xin_skin_fields() {
	$fields = array(
		'xin_primary' => array(
			'group'   => 'color',
			'type'    => 'color',
			'default' => '',
			'label'   => __( 'Акцентный цвет', 'xi-novels' ),
			'hint'    => __( 'Пусто — фирменный тёмно-серый.', 'xi-novels' ),
		),
		'xin_gold' => array(
			'group'   => 'color',
			'type'    => 'color',
			'default' => '',
			'label'   => __( 'Цвет премиума', 'xi-novels' ),
			'hint'    => __( 'Коины, PLUS, медали рейтинга.', 'xi-novels' ),
		),
		'xin_hue' => array(
			'group'   => 'color',
			'type'    => 'range',
			'default' => 220,
			'min'     => 0,
			'max'     => 360,
			'step'    => 1,
			'unit'    => '°',
			'label'   => __( 'Оттенок нейтральных', 'xi-novels' ),
			'hint'    => __( 'Фон, рамки и текст строятся из одного тона. 220 — холодный графит.', 'xi-novels' ),
		),
		'xin_saturation' => array(
			'group'   => 'color',
			'type'    => 'range',
			'default' => 100,
			'min'     => 0,
			'max'     => 200,
			'step'    => 5,
			'unit'    => '%',
			'label'   => __( 'Насыщенность нейтральных', 'xi-novels' ),
			'hint'    => __( 'Ноль — чистый серый, 200 — заметно цветная бумага.', 'xi-novels' ),
		),
		'xin_radius' => array(
			'group'   => 'shape',
			'type'    => 'range',
			'default' => 12,
			'min'     => 0,
			'max'     => 22,
			'step'    => 1,
			'unit'    => 'px',
			'label'   => __( 'Скругление', 'xi-novels' ),
			'hint'    => __( 'Карточки, кнопки и поля. Ноль — строгие прямые углы.', 'xi-novels' ),
		),
		'xin_shadow' => array(
			'group'   => 'shape',
			'type'    => 'choice',
			'default' => 'soft',
			'choices' => array(
				'flat' => __( 'Без теней', 'xi-novels' ),
				'soft' => __( 'Мягкие', 'xi-novels' ),
				'deep' => __( 'Глубокие', 'xi-novels' ),
			),
			'label'   => __( 'Тени', 'xi-novels' ),
		),
		'xin_wrap' => array(
			'group'   => 'shape',
			'type'    => 'range',
			'default' => 1160,
			'min'     => 1040,
			'max'     => 1440,
			'step'    => 20,
			'unit'    => 'px',
			'label'   => __( 'Ширина сайта', 'xi-novels' ),
		),
		'xin_font_ui' => array(
			'group'   => 'type',
			'type'    => 'choice',
			'default' => 'inter',
			'choices' => array(
				'inter'   => __( 'Inter / Segoe UI', 'xi-novels' ),
				'system'  => __( 'Системный', 'xi-novels' ),
				'grotesk' => __( 'Гротеск помягче', 'xi-novels' ),
				'serif'   => __( 'С засечками', 'xi-novels' ),
			),
			'label'   => __( 'Шрифт интерфейса', 'xi-novels' ),
			'hint'    => __( 'Только системные стеки — ничего не грузится со стороны.', 'xi-novels' ),
		),
		'xin_font_read' => array(
			'group'   => 'type',
			'type'    => 'choice',
			'default' => 'georgia',
			'choices' => array(
				'georgia' => __( 'Georgia', 'xi-novels' ),
				'book'    => __( 'Книжный', 'xi-novels' ),
				'palatino'=> __( 'Palatino', 'xi-novels' ),
				'sans'    => __( 'Без засечек', 'xi-novels' ),
			),
			'label'   => __( 'Шрифт чтения', 'xi-novels' ),
		),
		'xin_read_size' => array(
			'group'   => 'read',
			'type'    => 'range',
			'default' => 19,
			'min'     => 16,
			'max'     => 24,
			'step'    => 1,
			'unit'    => 'px',
			'label'   => __( 'Размер текста в читалке', 'xi-novels' ),
			'hint'    => __( 'С чего начинает новый читатель. Свои настройки он всё равно может задать сам.', 'xi-novels' ),
		),
		'xin_read_width' => array(
			'group'   => 'read',
			'type'    => 'range',
			'default' => 720,
			'min'     => 560,
			'max'     => 980,
			'step'    => 20,
			'unit'    => 'px',
			'label'   => __( 'Ширина колонки чтения', 'xi-novels' ),
		),
		'xin_read_lead' => array(
			'group'   => 'read',
			'type'    => 'range',
			'default' => 19,
			'min'     => 14,
			'max'     => 24,
			'step'    => 1,
			'unit'    => '/10',
			'label'   => __( 'Межстрочный интервал', 'xi-novels' ),
		),
		'xin_paper' => array(
			'group'   => 'read',
			'type'    => 'choice',
			'default' => 'default',
			'choices' => array(
				'default' => __( 'Как сайт', 'xi-novels' ),
				'paper'   => __( 'Белая', 'xi-novels' ),
				'sepia'   => __( 'Сепия', 'xi-novels' ),
				'night'   => __( 'Ночь', 'xi-novels' ),
			),
			'label'   => __( 'Бумага по умолчанию', 'xi-novels' ),
		),
		'xin_default_scheme' => array(
			'group'   => 'color',
			'type'    => 'choice',
			'default' => 'light',
			'choices' => array(
				'light' => __( 'Светлая', 'xi-novels' ),
				'dark'  => __( 'Тёмная', 'xi-novels' ),
				'auto'  => __( 'Как в системе', 'xi-novels' ),
			),
			'label'   => __( 'Схема по умолчанию', 'xi-novels' ),
		),
	);

	/**
	 * Позволяет дочерней теме или плагину добавить свою ручку.
	 *
	 * @param array $fields Описание параметров.
	 */
	return apply_filters( 'xin_skin_fields', $fields );
}

/**
 * Разделы, на которые бьются ручки.
 *
 * @return array
 */
function xin_skin_groups() {
	return array(
		'color' => array( 'label' => __( 'Цвет', 'xi-novels' ), 'icon' => 'sparkles' ),
		'shape' => array( 'label' => __( 'Форма', 'xi-novels' ), 'icon' => 'layers' ),
		'type'  => array( 'label' => __( 'Шрифты', 'xi-novels' ), 'icon' => 'type' ),
		'read'  => array( 'label' => __( 'Читалка', 'xi-novels' ), 'icon' => 'book-open' ),
	);
}

/**
 * Приводит значение к типу своей ручки.
 *
 * @param string $key   Имя параметра.
 * @param mixed  $value Сырое значение.
 * @return mixed
 */
function xin_skin_sanitize( $key, $value ) {
	$fields = xin_skin_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return null;
	}

	$field = $fields[ $key ];

	switch ( $field['type'] ) {
		case 'color':
			$color = sanitize_hex_color( is_string( $value ) ? $value : '' );
			return $color ? $color : '';

		case 'range':
			$number = (int) $value;
			return max( (int) $field['min'], min( (int) $field['max'], $number ) );

		case 'choice':
			$choice = sanitize_key( is_string( $value ) ? $value : '' );
			return isset( $field['choices'][ $choice ] ) ? $choice : $field['default'];
	}

	return null;
}

/**
 * Текущее значение ручки: из переданного набора, иначе из настроек темы.
 *
 * @param string     $key    Имя параметра.
 * @param array|null $values Набор значений или null.
 * @return mixed
 */
function xin_skin_value( $key, $values = null ) {
	$fields = xin_skin_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return null;
	}

	if ( is_array( $values ) ) {
		if ( ! isset( $values[ $key ] ) ) {
			return $fields[ $key ]['default'];
		}
		$clean = xin_skin_sanitize( $key, $values[ $key ] );
		return null === $clean ? $fields[ $key ]['default'] : $clean;
	}

	return get_theme_mod( $key, $fields[ $key ]['default'] );
}

/**
 * Все значения разом.
 *
 * @param array|null $values Набор значений или null.
 * @return array
 */
function xin_skin_values( $values = null ) {
	$out = array();
	foreach ( xin_skin_fields() as $key => $field ) {
		$out[ $key ] = xin_skin_value( $key, $values );
	}
	return $out;
}

/**
 * Стеки шрифтов. Только то, что уже стоит в системе.
 *
 * @return array
 */
function xin_skin_font_stacks() {
	return array(
		'ui'   => array(
			'inter'   => '"Inter", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif',
			'system'  => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
			'grotesk' => '"Avenir Next", "Trebuchet MS", "Segoe UI", system-ui, sans-serif',
			'serif'   => '"Iowan Old Style", "Palatino Linotype", Georgia, "Times New Roman", serif',
		),
		'read' => array(
			'georgia'  => '"Georgia", "Times New Roman", "Noto Serif", serif',
			'book'     => '"Literata", "Iowan Old Style", "Charter", Georgia, serif',
			'palatino' => '"Palatino Linotype", "Book Antiqua", Palatino, Georgia, serif',
			'sans'     => '"Inter", "Segoe UI", system-ui, -apple-system, Arial, sans-serif',
		),
	);
}

/**
 * Лестница нейтральных тонов: имя токена => [насыщенность, светлота].
 *
 * @param string $scheme light|dark
 * @return array
 */
function xin_skin_neutrals( $scheme ) {
	if ( 'dark' === $scheme ) {
		return array(
			'bg'       => array( 14, 7 ),
			'fg'       => array( 14, 91 ),
			'card'     => array( 13, 11 ),
			'popover'  => array( 13, 14 ),
			'muted'    => array( 12, 16 ),
			'muted-fg' => array( 10, 64 ),
			'accent'   => array( 12, 20 ),
			'border'   => array( 11, 24 ),
			'input'    => array( 11, 28 ),
		);
	}

	return array(
		'bg'       => array( 20, 98.5 ),
		'fg'       => array( 15, 12 ),
		'card'     => array( 0, 100 ),
		'popover'  => array( 0, 100 ),
		'muted'    => array( 14, 95.5 ),
		'muted-fg' => array( 9, 44 ),
		'accent'   => array( 14, 92.5 ),
		'border'   => array( 13, 89 ),
		'input'    => array( 13, 85 ),
	);
}

/**
 * Наборы теней.
 *
 * @param string $scheme light|dark
 * @return array
 */
function xin_skin_shadows( $scheme ) {
	if ( 'dark' === $scheme ) {
		return array(
			'flat' => array( 'none', 'none', '0 8px 24px hsl(220 20% 2% / .45)' ),
			'soft' => array(
				'0 1px 2px hsl(220 20% 2% / .5)',
				'0 6px 18px hsl(220 20% 2% / .55)',
				'0 18px 44px hsl(220 20% 2% / .6)',
			),
			'deep' => array(
				'0 2px 4px hsl(220 20% 2% / .6)',
				'0 10px 28px hsl(220 20% 2% / .7)',
				'0 26px 60px hsl(220 20% 2% / .78)',
			),
		);
	}

	return array(
		'flat' => array( 'none', 'none', '0 6px 20px hsl(220 15% 12% / .08)' ),
		'soft' => array(
			'0 1px 2px hsl(220 15% 12% / .05), 0 1px 3px hsl(220 15% 12% / .06)',
			'0 4px 12px hsl(220 15% 12% / .07), 0 2px 4px hsl(220 15% 12% / .05)',
			'0 12px 30px hsl(220 15% 12% / .10), 0 4px 8px hsl(220 15% 12% / .05)',
		),
		'deep' => array(
			'0 2px 4px hsl(220 15% 12% / .10)',
			'0 8px 24px hsl(220 15% 12% / .14), 0 3px 6px hsl(220 15% 12% / .08)',
			'0 22px 50px hsl(220 15% 12% / .20), 0 8px 16px hsl(220 15% 12% / .10)',
		),
	);
}

/**
 * Собирает CSS из значений. Пустая строка означает «всё по умолчанию».
 *
 * @param array|null $values Набор значений или null (тогда берутся настройки темы).
 * @return string
 */
function xin_skin_css( $values = null ) {
	$fields = xin_skin_fields();
	$value  = xin_skin_values( $values );
	$css    = '';

	$hue = (int) $value['xin_hue'];
	$sat = (int) $value['xin_saturation'];

	if ( 220 !== $hue || 100 !== $sat ) {
		foreach ( array( 'light', 'dark' ) as $scheme ) {
			$vars = '';
			foreach ( xin_skin_neutrals( $scheme ) as $token => $pair ) {
				$tone = round( $pair[0] * $sat / 100, 1 );
				$vars .= sprintf( '--%s:%d %s%% %s%%;', $token, $hue, $tone, $pair[1] );
			}
			$css .= ( 'dark' === $scheme ? '[data-theme="dark"]{' : ':root{' ) . $vars . '}';
		}
	}

	$primary = xin_hex_to_hsl( $value['xin_primary'] );
	if ( $primary ) {
		$css .= ':root,[data-theme="dark"]{--primary:' . $primary . ';--ring:' . $primary . ';}';
	}

	$gold = xin_hex_to_hsl( $value['xin_gold'] );
	if ( $gold ) {
		$css .= ':root,[data-theme="dark"]{--gold:' . $gold . ';}';
	}

	$radius = (int) $value['xin_radius'];
	if ( 12 !== $radius ) {
		$css .= sprintf(
			':root{--radius:%dpx;--radius-sm:%dpx;--radius-lg:%dpx;}',
			$radius,
			(int) round( $radius * 0.66 ),
			(int) round( $radius * 1.34 )
		);
	}

	$wrap = (int) $value['xin_wrap'];
	if ( 1160 !== $wrap ) {
		$css .= sprintf( ':root{--wrap:%dpx;}', $wrap );
	}

	if ( 'soft' !== $value['xin_shadow'] ) {
		foreach ( array( 'light', 'dark' ) as $scheme ) {
			$set = xin_skin_shadows( $scheme );
			$use = isset( $set[ $value['xin_shadow'] ] ) ? $set[ $value['xin_shadow'] ] : $set['soft'];
			$css .= ( 'dark' === $scheme ? '[data-theme="dark"]{' : ':root{' )
				. sprintf( '--shadow-1:%s;--shadow-2:%s;--shadow-3:%s;', $use[0], $use[1], $use[2] )
				. '}';
		}
	}

	$stacks = xin_skin_font_stacks();
	$vars   = '';

	if ( 'inter' !== $value['xin_font_ui'] && isset( $stacks['ui'][ $value['xin_font_ui'] ] ) ) {
		$vars .= '--font:' . $stacks['ui'][ $value['xin_font_ui'] ] . ';';
	}
	if ( 'georgia' !== $value['xin_font_read'] && isset( $stacks['read'][ $value['xin_font_read'] ] ) ) {
		$vars .= '--font-read:' . $stacks['read'][ $value['xin_font_read'] ] . ';';
	}
	if ( $vars ) {
		$css .= ':root{' . $vars . '}';
	}

	$read = sprintf(
		'--read-size:%dpx;--read-width:%dpx;--read-height:%s;',
		(int) $value['xin_read_size'],
		(int) $value['xin_read_width'],
		number_format( (int) $value['xin_read_lead'] / 10, 2, '.', '' )
	);

	if ( 19 !== (int) $value['xin_read_size'] || 720 !== (int) $value['xin_read_width'] || 19 !== (int) $value['xin_read_lead'] ) {
		$css .= '.xin-rd{' . $read . '}';
	}

	unset( $fields );

	return $css;
}

/**
 * Значения, с которых стартует читалка у нового читателя.
 *
 * @return array
 */
function xin_skin_reader_defaults() {
	return array(
		'size'   => (int) xin_skin_value( 'xin_read_size' ),
		'width'  => (int) xin_skin_value( 'xin_read_width' ),
		'height' => round( (int) xin_skin_value( 'xin_read_lead' ) / 10, 2 ),
		'font'   => 'sans' === xin_skin_value( 'xin_font_read' ) ? 'sans' : 'serif',
		'paper'  => xin_skin_value( 'xin_paper' ),
	);
}

/**
 * Готовые наборы — чтобы не собирать облик с нуля.
 *
 * @return array
 */
function xin_skin_presets() {
	$presets = array(
		'graphite' => array(
			'label'  => __( 'Графит', 'xi-novels' ),
			'note'   => __( 'Как в коробке: холодный нейтральный и спокойный акцент.', 'xi-novels' ),
			'values' => array(),
		),
		'paper' => array(
			'label'  => __( 'Бумага', 'xi-novels' ),
			'note'   => __( 'Тёплый белый, засечки, широкая колонка — ближе к книге.', 'xi-novels' ),
			'values' => array(
				'xin_hue'        => 34,
				'xin_saturation' => 60,
				'xin_radius'     => 6,
				'xin_shadow'     => 'flat',
				'xin_font_read'  => 'palatino',
				'xin_read_size'  => 20,
				'xin_read_width' => 760,
				'xin_read_lead'  => 20,
				'xin_paper'      => 'sepia',
			),
		),
		'ink' => array(
			'label'  => __( 'Чернила', 'xi-novels' ),
			'note'   => __( 'Тёмная схема по умолчанию, глубокие тени, ночная бумага.', 'xi-novels' ),
			'values' => array(
				'xin_default_scheme' => 'dark',
				'xin_hue'            => 232,
				'xin_saturation'     => 120,
				'xin_radius'         => 14,
				'xin_shadow'         => 'deep',
				'xin_paper'          => 'night',
			),
		),
		'neon' => array(
			'label'  => __( 'Неон', 'xi-novels' ),
			'note'   => __( 'Живой акцент и крупные скругления — для молодой аудитории.', 'xi-novels' ),
			'values' => array(
				'xin_primary'    => '#7c5cff',
				'xin_gold'       => '#f0a020',
				'xin_hue'        => 258,
				'xin_saturation' => 130,
				'xin_radius'     => 18,
				'xin_shadow'     => 'soft',
				'xin_font_ui'    => 'grotesk',
			),
		),
		'press' => array(
			'label'  => __( 'Газета', 'xi-novels' ),
			'note'   => __( 'Ноль скруглений, ноль теней, узкая колонка. Только текст.', 'xi-novels' ),
			'values' => array(
				'xin_hue'         => 40,
				'xin_saturation'  => 25,
				'xin_radius'      => 0,
				'xin_shadow'      => 'flat',
				'xin_wrap'        => 1080,
				'xin_font_ui'     => 'serif',
				'xin_font_read'   => 'book',
				'xin_read_width'  => 620,
				'xin_read_size'   => 18,
			),
		),
	);

	foreach ( $presets as $key => $preset ) {
		$presets[ $key ]['values'] = xin_skin_values( array_merge(
			wp_list_pluck( xin_skin_fields(), 'default' ),
			$preset['values']
		) );
	}

	return $presets;
}

/**
 * Пишет значения в настройки темы.
 *
 * @param array $values Сырые значения.
 * @return array Записанный набор.
 */
function xin_skin_save( $values ) {
	$clean = xin_skin_values( $values );

	foreach ( $clean as $key => $value ) {
		set_theme_mod( $key, $value );
	}

	return $clean;
}

/**
 * Роут для живого предпросмотра и сохранения из «Студии темы».
 */
function xin_skin_rest() {
	register_rest_route( 'xin/v1', '/skin', array(
		'methods'             => 'POST',
		'permission_callback' => function () {
			return current_user_can( 'edit_theme_options' );
		},
		'callback'            => 'xin_skin_rest_handler',
	) );
}
add_action( 'rest_api_init', 'xin_skin_rest' );

/**
 * @param WP_REST_Request $request Запрос.
 * @return array
 */
function xin_skin_rest_handler( WP_REST_Request $request ) {
	$values = (array) $request->get_param( 'values' );
	$save   = (bool) $request->get_param( 'save' );

	if ( $save ) {
		$values = xin_skin_save( $values );
	}

	return array(
		'css'    => xin_skin_css( $values ),
		'values' => xin_skin_values( $values ),
		'saved'  => $save,
	);
}
