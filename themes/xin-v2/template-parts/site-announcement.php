<?php
if (!defined('ABSPATH')) { exit; }
$xv_notices=get_posts(array('posts_per_page'=>1,'ignore_sticky_posts'=>true));
if (!$xv_notices) return;
$xv_announcement=$xv_notices[0];
?>
<div class="xv-world-announcement" data-xv-announcement><div class="xv-world-announcement-inner"><?php xv_the_icon('newspaper'); ?><div><small><?php esc_html_e('Из блога','xin-v2'); ?></small><a href="<?php echo esc_url(get_permalink($xv_announcement)); ?>"><?php echo esc_html(get_the_title($xv_announcement)); ?></a></div><a class="xv-btn" href="<?php echo esc_url(get_permalink($xv_announcement)); ?>"><?php esc_html_e('Читать запись','xin-v2'); ?> →</a><button class="xv-icon-btn" type="button" data-xv-announcement-close aria-label="<?php esc_attr_e('Скрыть объявление','xin-v2'); ?>"><?php xv_the_icon('close'); ?></button></div></div>
