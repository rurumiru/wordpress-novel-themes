<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xin_customize_register( $wp_customize ) {
$wp_customize->add_section( 'xin_brand', array(
		'title'    => __( 'XI Novels: бренд и цвета', 'xi-novels' ),
		'priority' => 25,
	) );

	$wp_customize->add_setting( 'xin_default_scheme', array(
		'default'           => 'dark',
		'sanitize_callback' => 'sanitize_key',
	) );
	$wp_customize->add_control( 'xin_default_scheme', array(
		'label'   => __( 'Схема по умолчанию', 'xi-novels' ),
		'section' => 'xin_brand',
		'type'    => 'select',
		'choices' => array(
			'dark'  => __( 'Тёмная', 'xi-novels' ),
			'light' => __( 'Светлая', 'xi-novels' ),
			'auto'  => __( 'Как в системе', 'xi-novels' ),
		),
	) );

	$wp_customize->add_setting( 'xin_primary', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'xin_primary', array(
		'label'       => __( 'Акцентный цвет', 'xi-novels' ),
		'description' => __( 'Пусто — фирменный кримсон.', 'xi-novels' ),
		'section'     => 'xin_brand',
	) ) );

	$wp_customize->add_setting( 'xin_gold', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'xin_gold', array(
		'label'       => __( 'Цвет премиума', 'xi-novels' ),
		'description' => __( 'Коины, PLUS, медали рейтинга.', 'xi-novels' ),
		'section'     => 'xin_brand',
	) ) );

$wp_customize->add_section( 'xin_home', array(
		'title'    => __( 'XI Novels: главная', 'xi-novels' ),
		'priority' => 26,
	) );

	$blocks = array(
		'xin_show_hero'       => __( 'Витрина «Сейчас в тренде»', 'xi-novels' ),
		'xin_show_services'   => __( 'Панель быстрых переходов', 'xi-novels' ),
		'xin_show_stats'      => __( 'Полоса цифр площадки', 'xi-novels' ),
		'xin_show_ranking'    => __( 'Рейтинг с вкладками', 'xi-novels' ),
		'xin_show_new'        => __( 'Новинки', 'xi-novels' ),
		'xin_show_genres'     => __( 'Жанры', 'xi-novels' ),
		'xin_show_trending'   => __( 'Тренд-блок с фоном', 'xi-novels' ),
		'xin_show_chapters'   => __( 'Последние главы', 'xi-novels' ),
		'xin_show_updated'    => __( 'Недавно обновлены', 'xi-novels' ),
		'xin_show_favorites'  => __( 'Самые любимые', 'xi-novels' ),
		'xin_show_authors'    => __( 'Топ-авторы и статьи', 'xi-novels' ),
		'xin_show_cta'        => __( 'Плитки-приглашения', 'xi-novels' ),
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
		'default'           => 420,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'xin_banner_height', array(
		'label'       => __( 'Высота баннера, px', 'xi-novels' ),
		'description' => __( 'Баннеры добавляются в админке: раздел «Баннеры».', 'xi-novels' ),
		'section'     => 'xin_home',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 220, 'max' => 900, 'step' => 10 ),
	) );

	$wp_customize->add_setting( 'xin_hero_eyebrow', array(
		'default'           => __( 'Сейчас в тренде', 'xi-novels' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'xin_hero_eyebrow', array(
		'label'   => __( 'Ярлык витрины', 'xi-novels' ),
		'section' => 'xin_home',
	) );

$wp_customize->add_section( 'xin_footer', array(
		'title'    => __( 'XI Novels: подвал и соцсети', 'xi-novels' ),
		'priority' => 27,
	) );

	$wp_customize->add_setting( 'xin_footer_about', array(
		'default'           => __( 'Платформа для чтения и публикации новелл, ранобэ и переводов. Читайте бесплатно, поддерживайте авторов.', 'xi-novels' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'xin_footer_about', array(
		'label'   => __( 'Текст о проекте', 'xi-novels' ),
		'section' => 'xin_footer',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'xin_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'xin_copyright', array(
		'label'       => __( 'Строка копирайта', 'xi-novels' ),
		'description' => __( 'Пусто — название сайта и год.', 'xi-novels' ),
		'section'     => 'xin_footer',
	) );

	$socials = array(
		'telegram' => 'Telegram',
		'vk'       => 'VK',
		'discord'  => 'Discord',
		'youtube'  => 'YouTube',
	);
	foreach ( $socials as $key => $label ) {
		$wp_customize->add_setting( 'xin_social_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'xin_social_' . $key, array(
			'label'   => $label,
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
	$css     = '';
	$primary = xin_hex_to_hsl( get_theme_mod( 'xin_primary', '' ) );
	$gold    = xin_hex_to_hsl( get_theme_mod( 'xin_gold', '' ) );

	if ( $primary ) {
		$css .= ':root,[data-theme="dark"]{--primary:' . $primary . ';--ring:' . $primary . ';}';
	}
	if ( $gold ) {
		$css .= ':root,[data-theme="dark"]{--gold:' . $gold . ';}';
	}
	return $css;
}

function xin_social_links() {
	$out = array();
	foreach ( array( 'telegram', 'vk', 'discord', 'youtube' ) as $key ) {
		$url = get_theme_mod( 'xin_social_' . $key, '' );
		if ( $url ) {
			$out[ $key ] = $url;
		}
	}
	return $out;
}
