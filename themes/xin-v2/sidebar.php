<?php
/**
 * Колонка рядом с записями блога и обычными страницами.
 *
 * Если владелец площадки ничего в неё не положил, колонки нет вовсе —
 * пустая полоса сбоку хуже, чем текст во всю ширину.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'xv-blog' ) ) {
	return;
}
?>

<aside class="xv-sidebar" role="complementary">
	<?php dynamic_sidebar( 'xv-blog' ); ?>
</aside>
