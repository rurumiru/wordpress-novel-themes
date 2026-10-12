<?php
/**
 * Кастомайзер: то, что владелец сайта меняет с предпросмотром.
 *
 * Полный список настроек живёт на своей странице в админке — здесь только
 * то, что имеет смысл видеть сразу.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xv_customize( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'xv_look', array(
		'title'    => __( 'XIN-V2 — витрина', 'xin-v2' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'xv_settings[tagline]', array(
		'type'              => 'option',
		'default'           => xv_defaults()['tagline'],
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'xv_settings[tagline]', array(
		'label'       => __( 'Подзаголовок', 'xin-v2' ),
		'description' => __( 'Короткая строка о проекте.', 'xin-v2' ),
		'section'     => 'xv_look',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'xv_settings[footer_note]', array(
		'type'              => 'option',
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'xv_settings[footer_note]', array(
		'label'   => __( 'Строка в подвале', 'xin-v2' ),
		'section' => 'xv_look',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'xv_settings[hero_limit]', array(
		'type'              => 'option',
		'default'           => 5,
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( 'xv_settings[hero_limit]', array(
		'label'       => __( 'Книг в подборке недели', 'xin-v2' ),
		'section'     => 'xv_look',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 10 ),
	) );

	$wp_customize->add_setting( 'xv_settings[reader_toolbar]', array(
		'type'              => 'option',
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'xv_settings[reader_toolbar]', array(
		'label'       => __( 'Прятать шапку при чтении', 'xin-v2' ),
		'description' => __( 'В читалке остаётся только оглавление и тулбар.', 'xin-v2' ),
		'section'     => 'xv_look',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'xv_settings[discussions]', array(
		'type'              => 'option',
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'xv_settings[discussions]', array(
		'label'   => __( 'Комментарии к тайтлам и главам', 'xin-v2' ),
		'section' => 'xv_look',
		'type'    => 'checkbox',
	) );

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
add_action( 'customize_register', 'xv_customize' );
