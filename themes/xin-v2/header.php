<?php
if (!defined('ABSPATH')) exit;
$xv_nav=xv_wuxia_nav_items();
$xv_studio=xv_author_url();
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="dark">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>try{document.documentElement.dataset.theme=localStorage.getItem('xv-theme')||'dark'}catch(e){}</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="xv-skip" href="#xv-main"><?php esc_html_e('К содержанию','xin-v2'); ?></a>
<?php if (!(is_singular('chapter') && xv_opt('reader_toolbar',true))) : ?>
<header class="xv-site-header" data-xv-nav>
 <div class="xv-header-main">
  <button type="button" class="xv-header-icon xv-header-menu" data-xv-drawer-open aria-label="<?php esc_attr_e('Меню','xin-v2'); ?>" aria-expanded="false" aria-controls="xv-site-drawer"><?php xv_the_icon('menu'); ?></button>
  <a class="xv-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="XI Novels — <?php esc_attr_e('Главная','xin-v2'); ?>"><?php xv_site_logo(); ?></a>
  <form class="xv-header-search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
   <?php xv_the_icon('search'); ?><input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Найти историю, автора…','xin-v2'); ?>" aria-label="<?php esc_attr_e('Поиск','xin-v2'); ?>" data-xv-search-input><input type="hidden" name="post_type" value="novel"><kbd>/</kbd>
  </form>
  <div class="xv-header-actions">
   <button type="button" class="xv-header-icon xv-header-search-button" data-xv-search-open aria-label="<?php esc_attr_e('Поиск','xin-v2'); ?>"><?php xv_the_icon('search'); ?></button>
   <button type="button" class="xv-header-icon xv-header-theme" data-xv-theme-toggle aria-label="<?php esc_attr_e('Сменить тему','xin-v2'); ?>" aria-pressed="true"><span class="xv-theme-sun"><?php xv_the_icon('sun'); ?></span><span class="xv-theme-moon"><?php xv_the_icon('moon'); ?></span></button>
   <div class="xv-header-languages"><?php xv_lang_switcher(); ?></div>
   <?php if (xv_local_dev_available() && !is_user_logged_in()) : ?><a class="xv-header-dev" href="<?php echo esc_url(add_query_arg('dev','1',xv_login_url($xv_studio))); ?>"><?php esc_html_e('Dev-вход','xin-v2'); ?></a><?php endif; ?>
   <?php if (is_user_logged_in()) : ?>
   <div class="xv-header-account" data-xv-drop>
    <button type="button" class="xv-header-avatar" data-xv-drop-btn aria-expanded="false" aria-controls="xv-account-menu" aria-label="<?php esc_attr_e('Профиль','xin-v2'); ?>"><?php echo xv_avatar(get_current_user_id(),34); ?></button>
    <div class="xv-header-popover" id="xv-account-menu" data-xv-drop-menu>
     <div class="xv-header-account-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></div>
     <a href="<?php echo esc_url(xv_page_url('template-library.php')); ?>"><?php xv_the_icon('bookmark'); ?><?php esc_html_e('Моя библиотека','xin-v2'); ?></a>
     <a href="<?php echo esc_url($xv_studio); ?>"><?php xv_the_icon('pen'); ?><?php esc_html_e('Студия автора и переводчика','xin-v2'); ?></a>
     <a href="<?php echo esc_url(add_query_arg('view','profile',$xv_studio)); ?>"><?php xv_the_icon('user'); ?><?php esc_html_e('Профиль','xin-v2'); ?></a>
     <?php if(current_user_can('manage_options')) : ?><a href="<?php echo esc_url(xv_manage_url()); ?>"><?php xv_the_icon('settings'); ?><?php esc_html_e('Управление','xin-v2'); ?></a><?php endif; ?>
     <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><?php xv_the_icon('close'); ?><?php esc_html_e('Выйти','xin-v2'); ?></a>
    </div>
   </div>
   <?php else : ?><a class="xv-header-login" href="<?php echo esc_url(xv_login_url()); ?>"><?php xv_the_icon('user'); ?><span><?php esc_html_e('Войти','xin-v2'); ?></span></a><?php endif; ?>
  </div>
 </div>
 <nav class="xv-header-navigation" aria-label="<?php esc_attr_e('Главная навигация','xin-v2'); ?>">
  <div class="xv-header-links">
   <?php foreach($xv_nav as $item) : ?>
   <?php if(empty($item['children'])) : ?><a href="<?php echo esc_url($item['url']); ?>" <?php if($item['active']) echo 'aria-current="page"'; ?>><?php xv_the_icon($item['icon']); ?><?php echo esc_html($item['label']); ?></a>
   <?php else : ?>
   <div class="xv-header-more" data-xv-drop>
    <button type="button" data-xv-drop-btn aria-expanded="false" aria-controls="xv-more-menu"><?php echo esc_html($item['label']); ?><?php xv_the_icon('chevron-down'); ?></button>
    <div class="xv-header-popover" id="xv-more-menu" data-xv-drop-menu>
     <?php foreach($item['children'] as $child) : ?><a href="<?php echo esc_url($child['url']); ?>" <?php if($child['active']) echo 'aria-current="page"'; ?>><?php xv_the_icon($child['icon']); ?><span><?php echo esc_html($child['label']); ?></span></a><?php endforeach; ?>
    </div>
   </div>
   <?php endif; endforeach; ?>
  </div>
  <a class="xv-header-create" href="<?php echo esc_url($xv_studio); ?>"><?php xv_the_icon('pen'); ?><?php esc_html_e('Студия','xin-v2'); ?><?php xv_the_icon('arrow-right'); ?></a>
 </nav>
 <div class="xv-search-overlay" data-xv-search-overlay hidden>
  <form class="xv-search-overlay-form" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get"><?php xv_the_icon('search'); ?><input type="search" name="s" placeholder="<?php esc_attr_e('Найти историю, автора…','xin-v2'); ?>" aria-label="<?php esc_attr_e('Поиск','xin-v2'); ?>"><input type="hidden" name="post_type" value="novel"><button type="button" class="xv-header-icon" data-xv-search-close aria-label="<?php esc_attr_e('Закрыть','xin-v2'); ?>"><?php xv_the_icon('close'); ?></button></form>
 </div>
</header>
<div class="xv-drawer-overlay" data-xv-drawer-overlay hidden></div>
<aside class="xv-site-drawer" id="xv-site-drawer" data-xv-drawer hidden aria-label="<?php esc_attr_e('Меню','xin-v2'); ?>">
 <div class="xv-site-drawer-head"><a class="xv-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php xv_site_logo(); ?></a><button type="button" class="xv-header-icon" data-xv-drawer-close aria-label="<?php esc_attr_e('Закрыть','xin-v2'); ?>"><?php xv_the_icon('close'); ?></button></div>
 <nav><?php foreach($xv_nav as $item) : ?><?php if(empty($item['children'])) : ?><a href="<?php echo esc_url($item['url']); ?>"><?php xv_the_icon($item['icon']); ?><?php echo esc_html($item['label']); ?></a><?php else : ?><div class="xv-site-drawer-label"><?php echo esc_html($item['label']); ?></div><?php foreach($item['children'] as $child) : ?><a href="<?php echo esc_url($child['url']); ?>"><?php xv_the_icon($child['icon']); ?><?php echo esc_html($child['label']); ?></a><?php endforeach; endif; endforeach; ?></nav>
 <div class="xv-site-drawer-tools"><?php xv_lang_switcher(); ?><button type="button" class="xv-header-icon" data-xv-theme-toggle aria-label="<?php esc_attr_e('Сменить тему','xin-v2'); ?>"><?php xv_the_icon('sun'); ?></button></div>
 <?php if(xv_local_dev_available() && !is_user_logged_in()) : ?><a class="xv-header-dev" href="<?php echo esc_url(add_query_arg('dev','1',xv_login_url($xv_studio))); ?>"><?php esc_html_e('Dev-вход','xin-v2'); ?></a><?php endif; ?>
</aside>
<?php endif; ?>
<?php if(!is_singular('chapter') && !is_page_template('template-author.php')) get_template_part('template-parts/site','announcement'); ?>
<main id="xv-main" class="xv-main">
