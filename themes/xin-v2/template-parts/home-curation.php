<?php
if(!defined('ABSPATH')) exit;
$books=array_slice($args['books']??[],0,3);
if(!$books) return;
?>
<section class="xv-curation xv-section" data-xv-curation aria-label="<?php esc_attr_e('Откройте новую историю','xin-v2'); ?>">
<div class="xv-section-head"><div><span class="xv-curation-eyebrow"><?php esc_html_e('В центре внимания','xin-v2'); ?></span><h2><?php esc_html_e('Откройте новую историю','xin-v2'); ?></h2></div><div class="xv-curation-arrows"><button type="button" data-xv-curation-prev aria-label="<?php esc_attr_e('Предыдущая история','xin-v2'); ?>"><?php xv_the_icon('chevron-left'); ?></button><button type="button" data-xv-curation-next aria-label="<?php esc_attr_e('Следующая история','xin-v2'); ?>"><?php xv_the_icon('chevron-right'); ?></button></div></div>
<div class="xv-curation-track" data-xv-curation-track tabindex="0" aria-label="<?php esc_attr_e('Карусель произведений','xin-v2'); ?>">
<?php foreach($books as $book) : ?><a class="xv-curation-slide" href="<?php echo esc_url(get_permalink($book)); ?>"><img src="<?php echo esc_url(xv_cover_url($book->ID,'xv-cover-lg')); ?>" alt="" loading="lazy"><div class="xv-curation-copy"><span class="xv-curation-genre"><?php echo esc_html(xv_genre_line($book->ID,2)); ?></span><h3><?php echo esc_html(get_the_title($book)); ?></h3><p><?php echo esc_html(wp_trim_words(get_the_excerpt($book),27)); ?></p><span class="xv-curation-cta"><?php esc_html_e('Открыть произведение','xin-v2'); ?><?php xv_the_icon('arrow-right'); ?></span></div></a><?php endforeach; ?>
</div><div class="xv-curation-dots"><?php foreach($books as $i=>$book) : ?><button type="button" data-xv-curation-dot="<?php echo $i; ?>" aria-pressed="<?php echo $i===0?'true':'false'; ?>" aria-label="<?php echo esc_attr(get_the_title($book)); ?>"></button><?php endforeach; ?></div>
</section>
