<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Режим доступа к выгрузке. Чужое значение не запирает скачивание: тема
 * откатывается на «всем», как вела себя до появления настройки.
 *
 * @param string $value Что пришло из формы.
 * @return string
 */
function xin_sanitize_download_audience( $value ) {
	$value = sanitize_key( $value );

	return isset( xin_download_audiences()[ $value ] ) ? $value : 'all';
}

/**
 * Список ролей строкой слагов через запятую.
 *
 * @param string $value Что пришло из формы.
 * @return string
 */
function xin_sanitize_download_roles( $value ) {
	$out = array();

	foreach ( preg_split( '/[,\s]+/', (string) $value ) as $role ) {
		$role = sanitize_key( $role );
		if ( $role && ! in_array( $role, $out, true ) ) {
			$out[] = $role;
		}
	}

	return implode( ',', $out );
}

/**
 * Галочки ролей в кастомайзере.
 *
 * Своё поле, потому что штатные контролы умеют одно значение, а роли — это
 * набор. Галочки складываются в скрытое поле той же строкой, что хранит панель
 * управления: обе формы пишут один theme_mod и не расходятся.
 */
function xin_customize_roles_control() {
	if ( class_exists( 'XIN_Customize_Roles_Control' ) || ! class_exists( 'WP_Customize_Control' ) ) {
		return;
	}

	class XIN_Customize_Roles_Control extends WP_Customize_Control {

		public $type = 'xin-roles';

		public function render_content() {
			$picked = array_filter( explode( ',', (string) $this->value() ) );
			?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<div class="xin-roles-control">
				<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', $picked ) ); ?>">
				<?php foreach ( xin_download_role_choices() as $role => $name ) : ?>
					<label style="display:block;margin:.35em 0">
						<input type="checkbox" value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $picked, true ) ); ?>>
						<?php echo esc_html( $name ); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}
add_action( 'customize_register', 'xin_customize_roles_control', 5 );

/**
 * Собирает галочки ролей обратно в строку.
 *
 * Слушаем документ, а не отдельный контрол: так скрипт переживает и повторную
 * отрисовку панели, и появление второго такого поля.
 */
function xin_customize_roles_script() {
	?>
	<script>
	document.addEventListener( 'change', function ( event ) {
		var box = event.target.closest ? event.target.closest( '.xin-roles-control' ) : null;
		if ( ! box || 'checkbox' !== event.target.type ) {
			return;
		}

		var picked = [];
		box.querySelectorAll( 'input[type=checkbox]:checked' ).forEach( function ( input ) {
			picked.push( input.value );
		} );

		var hidden = box.querySelector( 'input[type=hidden]' );
		hidden.value = picked.join( ',' );
		hidden.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', 'xin_customize_roles_script' );

function xin_customize_register( $wp_customize ) {
$wp_customize->add_section( 'xin_brand', array(
		'title'    => __( 'XIN-Com: бренд и цвета', 'xin-com' ),
		'priority' => 25,
	) );

	$wp_customize->add_setting( 'xin_default_lang', array(
		'default'           => 'ru',
		'sanitize_callback' => 'sanitize_key',
	) );
	$xin_lang_choices = array();
	foreach ( xin_languages() as $xin_lang_key => $xin_lang_data ) {
		$xin_lang_choices[ $xin_lang_key ] = $xin_lang_data['name'];
	}

	$wp_customize->add_control( 'xin_default_lang', array(
		'label'       => __( 'Основной язык', 'xin-com' ),
		'description' => __( 'На каком языке сайт открывается у нового посетителя. Переключатель языков в шапке остаётся.', 'xin-com' ),
		'section'     => 'xin_brand',
		'type'        => 'select',
		'choices'     => $xin_lang_choices,
	) );

	$wp_customize->add_section( 'xin_look', array(
		'title'       => __( 'XIN-Com: облик', 'xin-com' ),
		'description' => __( 'То же самое живьём и с предпросмотром — в «Студии темы».', 'xin-com' ),
		'priority'    => 25,
	) );

	foreach ( xin_skin_fields() as $xin_key => $xin_field ) {
		$xin_section = 'color' === $xin_field['group'] ? 'xin_brand' : 'xin_look';

		$wp_customize->add_setting( $xin_key, array(
			'default'           => $xin_field['default'],
			'sanitize_callback' => function ( $value ) use ( $xin_key ) {
				return xin_skin_sanitize( $xin_key, $value );
			},
		) );

		$xin_args = array(
			'label'       => $xin_field['label'],
			'description' => isset( $xin_field['hint'] ) ? $xin_field['hint'] : '',
			'section'     => $xin_section,
		);

		if ( 'color' === $xin_field['type'] ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $xin_key, $xin_args ) );
			continue;
		}

		if ( 'choice' === $xin_field['type'] ) {
			$xin_args['type']    = 'select';
			$xin_args['choices'] = $xin_field['choices'];
		} else {
			$xin_args['type']        = 'number';
			$xin_args['input_attrs'] = array(
				'min'  => $xin_field['min'],
				'max'  => $xin_field['max'],
				'step' => $xin_field['step'],
			);
		}

		$wp_customize->add_control( $xin_key, $xin_args );
	}

$wp_customize->add_section( 'xin_sections', array(
		'title'    => __( 'XIN-Com: разделы', 'xin-com' ),
		'priority' => 25,
	) );

	$wp_customize->add_setting( 'xin_comics', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'xin_comics', array(
		'label'       => __( 'Раздел комиксов', 'xin-com' ),
		'description' => __( 'Модуль выключен по умолчанию. Включите — и в шапке появится переключатель разделов, а сайт получит адреса /comics/ с собственной главной, каталогом и читалкой страниц. У тайтла появится поле «Формат». Выключение ничего не удаляет: тайтлы-комиксы просто перестают быть видимыми, в каталог новелл они не попадают.', 'xin-com' ),
		'section'     => 'xin_sections',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_section( 'xin_accounts', array(
		'title'    => __( 'XIN-Com: аккаунты', 'xin-com' ),
		'priority' => 26,
	) );

	$wp_customize->add_setting( 'xin_open_registration', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'xin_open_registration', array(
		'label'       => __( 'Открытая регистрация', 'xin-com' ),
		'description' => __( 'Форма на странице «Вход и регистрация» принимает новых читателей. Работает независимо от галочки в «Настройки → Общие», которая относится к экрану wp-login.php.', 'xin-com' ),
		'section'     => 'xin_accounts',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'xin_discussions', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'xin_discussions', array(
		'label'       => __( 'Обсуждения под главами и тайтлами', 'xin-com' ),
		'description' => __( 'Модуль выключен по умолчанию. Пишут только вошедшие, ответы на один уровень, поддерживаются спойлеры.', 'xin-com' ),
		'section'     => 'xin_accounts',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'xin_new_user_role', array(
		'default'           => 'author',
		'sanitize_callback' => 'sanitize_key',
	) );
	$wp_customize->add_control( 'xin_new_user_role', array(
		'label'       => __( 'Кем становится новый пользователь', 'xin-com' ),
		'description' => __( 'Автор публикует сам, участник отправляет главы на проверку, читатель только читает.', 'xin-com' ),
		'section'     => 'xin_accounts',
		'type'        => 'select',
		'choices'     => array(
			'author'      => __( 'Автор', 'xin-com' ),
			'contributor' => __( 'Участник', 'xin-com' ),
			'subscriber'  => __( 'Читатель', 'xin-com' ),
		),
	) );

$wp_customize->add_section( 'xin_access', array(
		'title'       => __( 'XIN-Com: доступ к книгам', 'xin-com' ),
		'description' => __( 'То же самое есть в панели управления на сайте, во вкладке «Настройки».', 'xin-com' ),
		'priority'    => 26,
	) );

	$wp_customize->add_setting( 'xin_download_audience', array(
		'default'           => 'all',
		'sanitize_callback' => 'xin_sanitize_download_audience',
	) );
	$wp_customize->add_control( 'xin_download_audience', array(
		'label'       => __( 'Скачивание книг', 'xin-com' ),
		'description' => __( 'Кому показывать кнопку «Скачать» и отдавать файл EPUB или FB2. Главы, закрытые для читателя, в файл не попадают ни при каком выборе.', 'xin-com' ),
		'section'     => 'xin_access',
		'type'        => 'select',
		'choices'     => xin_download_audiences(),
	) );

	$wp_customize->add_setting( 'xin_download_roles', array(
		'default'           => '',
		'sanitize_callback' => 'xin_sanitize_download_roles',
	) );
	$wp_customize->add_control( new XIN_Customize_Roles_Control( $wp_customize, 'xin_download_roles', array(
		'label'       => __( 'Роли со скачиванием', 'xin-com' ),
		'description' => __( 'Учитываются в двух последних режимах. Достаточно одной отмеченной роли; администратор скачивает всегда.', 'xin-com' ),
		'section'     => 'xin_access',
	) ) );

$wp_customize->add_section( 'xin_home', array(
		'title'    => __( 'XIN-Com: главная', 'xin-com' ),
		'priority' => 26,
	) );

	$blocks = array(
		'xin_show_hero'       => __( 'Витрина «Сейчас в тренде»', 'xin-com' ),
		'xin_show_services'   => __( 'Панель быстрых переходов', 'xin-com' ),
		'xin_show_stats'      => __( 'Полоса цифр площадки', 'xin-com' ),
		'xin_show_ranking'    => __( 'Рейтинг с вкладками', 'xin-com' ),
		'xin_show_new'        => __( 'Новинки', 'xin-com' ),
		'xin_show_genres'     => __( 'Жанры', 'xin-com' ),
		'xin_show_trending'   => __( 'Тренд-блок с фоном', 'xin-com' ),
		'xin_show_chapters'   => __( 'Последние главы', 'xin-com' ),
		'xin_show_updated'    => __( 'Недавно обновлены', 'xin-com' ),
		'xin_show_favorites'  => __( 'Самые любимые', 'xin-com' ),
		'xin_show_authors'    => __( 'Топ-авторы и статьи', 'xin-com' ),
		'xin_show_cta'        => __( 'Плитки-приглашения', 'xin-com' ),
	);
	foreach ( $blocks as $key => $label ) {
		$wp_customize->add_setting( $key, array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $label,
			'section' => 'xin_home',
			'type'    => 'checkbox',
		) );
	}

	$wp_customize->add_setting( 'xin_banner_height', array(
		'default'           => 360,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'xin_banner_height', array(
		'label'       => __( 'Высота баннера, px', 'xin-com' ),
		'description' => __( 'Баннеры добавляются в админке: раздел «Баннеры».', 'xin-com' ),
		'section'     => 'xin_home',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 200, 'max' => 720, 'step' => 10 ),
	) );

	$wp_customize->add_setting( 'xin_hero_eyebrow', array(
		'default'           => __( 'Сейчас в тренде', 'xin-com' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'xin_hero_eyebrow', array(
		'label'   => __( 'Ярлык витрины', 'xin-com' ),
		'section' => 'xin_home',
	) );

$wp_customize->add_section( 'xin_footer', array(
		'title'    => __( 'XIN-Com: подвал и соцсети', 'xin-com' ),
		'priority' => 27,
	) );

	$wp_customize->add_setting( 'xin_footer_about', array(
		'default'           => __( 'Платформа для чтения и публикации новелл, ранобэ и переводов. Читайте бесплатно, поддерживайте авторов.', 'xin-com' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'xin_footer_about', array(
		'label'   => __( 'Текст о проекте', 'xin-com' ),
		'section' => 'xin_footer',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'xin_credit', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'xin_credit', array(
		'label'       => __( 'Строка «Работает на XIN-Com»', 'xin-com' ),
		'description' => __( 'Необязательная ссылка на тему в подвале. Лицензия этого не требует — но авторам приятно.', 'xin-com' ),
		'section'     => 'xin_footer',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'xin_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'xin_copyright', array(
		'label'       => __( 'Строка копирайта', 'xin-com' ),
		'description' => __( 'Пусто — название сайта и год.', 'xin-com' ),
		'section'     => 'xin_footer',
	) );

	$socials = array(
		'telegram'      => array( __( 'Telegram: канал', 'xin-com' ), 'https://t.me/licht_re' ),
		'telegram_chat' => array( __( 'Telegram: чат сообщества', 'xin-com' ), 'https://t.me/xicommunity' ),
		'vk'            => array( 'VK', '' ),
		'discord'       => array( 'Discord', '' ),
		'youtube'       => array( 'YouTube', '' ),
	);
	foreach ( $socials as $key => $data ) {
		$wp_customize->add_setting( 'xin_social_' . $key, array(
			'default'           => $data[1],
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'xin_social_' . $key, array(
			'label'   => $data[0],
			'section' => 'xin_footer',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'xin_customize_register' );

function xin_show( $key ) {
	return (bool) get_theme_mod( $key, true );
}

function xin_hex_to_hsl( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '';
	}

	$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
	$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
	$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

	$max = max( $r, $g, $b );
	$min = min( $r, $g, $b );
	$l   = ( $max + $min ) / 2;
	$d   = $max - $min;
	$h   = 0;
	$s   = 0;

	if ( $d > 0 ) {
		$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
		if ( $max === $r ) {
			$h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
		} elseif ( $max === $g ) {
			$h = ( $b - $r ) / $d + 2;
		} else {
			$h = ( $r - $g ) / $d + 4;
		}
		$h /= 6;
	}

	return sprintf( '%d %.1f%% %.1f%%', round( $h * 360 ), $s * 100, $l * 100 );
}

function xin_customizer_css() {
	return xin_skin_css();
}

function xin_social_links() {
	$defaults = array(
		'telegram'      => 'https://t.me/licht_re',
		'telegram_chat' => 'https://t.me/xicommunity',
		'vk'            => '',
		'discord'       => '',
		'youtube'       => '',
	);

	$out = array();
	foreach ( $defaults as $key => $default ) {
		$url = get_theme_mod( 'xin_social_' . $key, $default );
		if ( $url ) {
			$out[ $key ] = $url;
		}
	}

	return $out;
}

function xin_social_meta( $key ) {
	$map = array(
		'telegram'      => array( 'telegram', __( 'Telegram-канал', 'xin-com' ) ),
		'telegram_chat' => array( 'comment', __( 'Чат сообщества', 'xin-com' ) ),
		'vk'            => array( 'vk', 'VK' ),
		'discord'       => array( 'discord', 'Discord' ),
		'youtube'       => array( 'youtube', 'YouTube' ),
	);

	return isset( $map[ $key ] ) ? $map[ $key ] : array( $key, $key );
}
