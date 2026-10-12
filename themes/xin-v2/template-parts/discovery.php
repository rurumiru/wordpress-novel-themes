<?php
/** Compact discovery navigation shared by catalog and home. */
if (!defined('ABSPATH')) exit;
$base=get_post_type_archive_link('novel');
$current=isset($_GET['sort'])?sanitize_key(wp_unslash($_GET['sort'])):'new';
$items=array('popular'=>__('Популярное','xin-v2'),'new'=>__('Новинки','xin-v2'),'updated'=>__('Обновления','xin-v2'),'rating'=>__('Высокая оценка','xin-v2'));
?>
<nav class="xv-discovery-tabs" aria-label="<?php esc_attr_e('Подборки каталога','xin-v2'); ?>">
<?php foreach($items as $key=>$label) : ?><a class="<?php echo is_post_type_archive('novel')&&$current===$key?'active':''; ?>" href="<?php echo esc_url(add_query_arg('sort',$key,$base)); ?>"<?php if(is_post_type_archive('novel')&&$current===$key)echo ' aria-current="page"'; ?>><?php if('popular'===$key)xv_the_icon('sparkles'); ?><?php echo esc_html($label); ?></a><?php endforeach; ?>
<a href="<?php echo esc_url(xv_page_url('template-library.php')?:xv_login_url()); ?>"><?php esc_html_e('Моя библиотека','xin-v2'); ?></a>
</nav>
