<?php
/**
 * Template Name: Кабинет автора (старое имя шаблона)
 *
 * Существует только ради переезда с XIN-Com: там страница кабинета была
 * назначена на `template-dashboard.php`, и после смены темы она бы отдала
 * пустую заглушку. Вся работа — в `template-author.php`.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_theme_file_path( 'template-author.php' );
