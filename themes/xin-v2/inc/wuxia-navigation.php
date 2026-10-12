<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** Direct library navigation, following the reference site's hierarchy. */
function xv_wuxia_nav_items() {
    $items = array(
        array('label'=>__('Каталог','xin-v2'),'url'=>get_post_type_archive_link('novel'),'icon'=>'book-open','active'=>is_post_type_archive('novel') || is_tax(),'children'=>array()),
        array('label'=>__('Обновления','xin-v2'),'url'=>get_post_type_archive_link('chapter'),'icon'=>'clock','active'=>is_post_type_archive('chapter'),'children'=>array()),
        array('label'=>__('Рейтинг','xin-v2'),'url'=>xv_page_url('template-ranking.php'),'icon'=>'star','active'=>is_page_template('template-ranking.php'),'children'=>array()),
        array('label'=>__('Закладки','xin-v2'),'url'=>xv_page_url('template-library.php'),'icon'=>'bookmark','active'=>is_page_template('template-library.php'),'children'=>array()),
        array('label'=>__('Сообщество','xin-v2'),'url'=>xv_page_url('template-hub.php'),'icon'=>'comment','active'=>is_page_template('template-hub.php'),'children'=>array()),
    );
    $resources = array();
    foreach (array(
        array(__('Блог','xin-v2'),xv_blog_url(),'newspaper'),
        array(__('Студия автора и переводчика','xin-v2'),xv_author_url(),'pen'),
        array(__('Стать автором','xin-v2'),xv_page_url('template-become-author.php'),'user'),
        array(__('Помощь','xin-v2'),home_url('/help/'),'book-open'),
    ) as $row) {
        if ($row[1]) $resources[]=array('label'=>$row[0],'url'=>$row[1],'icon'=>$row[2],'active'=>untrailingslashit(home_url($_SERVER['REQUEST_URI'] ?? '/')) === untrailingslashit($row[1]));
    }
    $items[]=array('label'=>__('Ресурсы','xin-v2'),'url'=>'#','icon'=>'layers','active'=>is_page_template('template-author.php'),'children'=>$resources);
    return array_values(array_filter($items, function($item){return !empty($item['url']);}));
}

function xv_local_dev_available() {
    return PHP_SAPI === 'cli-server'
        && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)
        && in_array(parse_url(home_url('/'), PHP_URL_HOST), ['localhost','127.0.0.1','::1'], true);
}

add_filter('show_admin_bar', function($show) {
    return xv_local_dev_available() && wp_get_current_user()->user_login === 'dev-preview' ? false : $show;
});

function xv_site_logo() {
    ?><svg class="xv-brand-symbol" viewBox="0 0 40 40" fill="none" aria-hidden="true"><rect x="1" y="1" width="38" height="38" rx="12" fill="currentColor"/><path d="M10 12.5c4.2-.6 7.5.5 10 3 2.5-2.5 5.8-3.6 10-3v16c-4.2-.6-7.5.5-10 3-2.5-2.5-5.8-3.6-10-3v-16Z" stroke="var(--xv-logo-ink,#142538)" stroke-width="1.7" stroke-linejoin="round"/><path d="M20 15.5v15M14 18l3 5m0-5-3 5m11-5v5" stroke="var(--xv-logo-ink,#142538)" stroke-width="1.6" stroke-linecap="round"/></svg><span class="xv-brand-wordmark">XI<span>Novels</span></span><?php
}
