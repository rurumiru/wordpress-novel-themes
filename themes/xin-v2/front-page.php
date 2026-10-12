<?php
/** Library front page: reference layout, using local WordPress content. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$xv_featured = xv_get_novels( 'featured', 5 );
$xv_popular = xv_get_novels( 'popular', 5 );
$xv_fresh = xv_get_novels( 'latest', 5 );
$xv_rated = xv_get_novels( 'rating', 5 );
$xv_updates = xv_get_latest_chapters( 12 );
$xv_posts = get_posts( array( 'posts_per_page'=>4, 'ignore_sticky_posts'=>true ) );
$xv_genres = get_terms( array( 'taxonomy'=>'genre', 'hide_empty'=>true, 'number'=>5, 'orderby'=>'count', 'order'=>'DESC' ) );
$xv_archive = get_post_type_archive_link( 'novel' );
$xv_history = is_user_logged_in() ? array_slice(xv_history(),0,5) : array();
?>
<div class="xv-home xv-world-home"><?php get_template_part("template-parts/discovery"); ?>
<div class="xv-world-feature-grid">
    <section class="xv-home-spotlight" aria-label="<?php esc_attr_e('Выбор редакции','xin-v2'); ?>">
        <div class="xv-home-spotlight-main">
        <?php foreach ($xv_featured as $xv_index=>$xv_novel) : $xv_rating=xv_rating($xv_novel->ID); $xv_status=xv_novel_status($xv_novel->ID); ?>
        <article class="xv-spotlight-item<?php echo !$xv_index?' is-active':''; ?>" data-xv-spotlight="<?php echo (int)$xv_index; ?>"<?php echo $xv_index?' hidden':''; ?>>
            <?php if (xv_cover_url($xv_novel->ID,'xv-cover-lg')) : ?><div class="xv-spotlight-art" aria-hidden="true" style="background-image:url('<?php echo esc_url(xv_cover_url($xv_novel->ID,'xv-cover-lg')); ?>')"></div><?php endif; ?>
            <a class="xv-spotlight-cover" href="<?php echo esc_url(get_permalink($xv_novel)); ?>"><img src="<?php echo esc_url(xv_cover_url($xv_novel->ID,'xv-cover-lg')); ?>" alt="<?php echo esc_attr(get_the_title($xv_novel)); ?>" <?php echo !$xv_index?'fetchpriority="high"':'loading="lazy"'; ?>></a>
            <div class="xv-spotlight-copy"><h1><a href="<?php echo esc_url(get_permalink($xv_novel)); ?>"><?php echo esc_html(get_the_title($xv_novel)); ?></a></h1>
                <?php if ($xv_rating['value']>0) : ?><div class="xv-world-rating"><b><?php echo esc_html(number_format_i18n($xv_rating['value'],1)); ?></b><?php echo xv_stars($xv_rating['value']); ?></div><?php endif; ?>
                <p class="xv-spotlight-status"><?php echo esc_html($xv_status?$xv_status->name:__('Веб-новелла','xin-v2')); ?></p>
                <p class="xv-spotlight-synopsis"><?php echo esc_html(wp_trim_words(get_the_excerpt($xv_novel),37)); ?></p>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if (!$xv_featured) : ?><div class="xv-spotlight-empty"><h1><?php esc_html_e('Каталог историй','xin-v2'); ?></h1><a href="<?php echo esc_url($xv_archive); ?>"><?php esc_html_e('Открыть каталог','xin-v2'); ?></a></div><?php endif; ?>
        </div>
        <?php if (count($xv_featured)>1) : ?><div class="xv-world-carousel-controls"><button type="button" data-xv-carousel-prev aria-label="<?php esc_attr_e('Предыдущая подборка','xin-v2'); ?>"><?php xv_the_icon('chevron-left'); ?></button><div class="xv-spotlight-picker"><?php foreach ($xv_featured as $xv_index=>$xv_novel) : ?><button type="button" class="xv-spotlight-pick<?php echo !$xv_index?' is-active':''; ?>" data-xv-spotlight-pick="<?php echo (int)$xv_index; ?>" aria-label="<?php echo esc_attr(get_the_title($xv_novel)); ?>" aria-pressed="<?php echo !$xv_index?'true':'false'; ?>"></button><?php endforeach; ?></div><button type="button" data-xv-carousel-next aria-label="<?php esc_attr_e('Следующая подборка','xin-v2'); ?>"><?php xv_the_icon('chevron-right'); ?></button></div><?php endif; ?>
    </section>
    <section class="xv-world-news"><div class="xv-section-head"><h2><?php esc_html_e('Объявления','xin-v2'); ?></h2><a href="<?php echo esc_url(xv_blog_url()?:home_url('/')); ?>"><?php esc_html_e('Все записи','xin-v2'); ?></a></div><div class="xv-world-news-list">
        <?php foreach ($xv_posts as $xv_post) : ?><a class="xv-world-news-row" href="<?php echo esc_url(get_permalink($xv_post)); ?>"><strong><?php echo esc_html(get_the_title($xv_post)); ?></strong><span><?php xv_the_icon('comment'); ?><?php echo esc_html(get_comments_number($xv_post)); ?> <time><?php echo esc_html(get_the_date('d.m',$xv_post)); ?></time></span></a><?php endforeach; ?>
        <?php if (!$xv_posts) : ?><p class="xv-world-news-empty"><?php esc_html_e('Объявлений пока нет.','xin-v2'); ?></p><?php endif; ?>
    </div></section>
</div>
<?php if (xv_banners('home',2)) : ?><div class="xv-world-promos"><?php xv_banner_block('home',2); ?></div><?php else : ?>
<div class="xv-world-promos"><a class="xv-world-promo xv-world-promo-read" href="<?php echo esc_url(add_query_arg('sort','rating',$xv_archive)); ?>"><div><small><?php esc_html_e('ВЫБОР ЧИТАТЕЛЕЙ','xin-v2'); ?></small><h2><?php esc_html_e('Истории, которые остаются с вами','xin-v2'); ?></h2><span><?php esc_html_e('Открыть рейтинг','xin-v2'); ?> →</span></div><?php foreach(array_slice($xv_rated,0,3) as $xv_novel) : ?><img src="<?php echo esc_url(xv_cover_url($xv_novel->ID)); ?>" alt="" loading="lazy"><?php endforeach; ?></a><a class="xv-world-promo xv-world-promo-studio" href="<?php echo esc_url(xv_author_url()?:xv_login_url()); ?>"><div><small><?php esc_html_e('АВТОРАМ И ПЕРЕВОДЧИКАМ','xin-v2'); ?></small><h2><?php esc_html_e('Здесь начинается ваша история','xin-v2'); ?></h2><span><?php esc_html_e('Открыть студию','xin-v2'); ?> →</span></div><?php xv_the_icon('pen'); ?></a></div>
<?php endif; ?>
<section class="xv-section xv-section-wide xv-pia-ranking" data-xv-ranking><div class="xv-section-head"><h2><?php esc_html_e('Рейтинг читателей','xin-v2'); ?></h2><a href="<?php echo esc_url(add_query_arg('sort','popular',$xv_archive)); ?>"><?php esc_html_e('Весь рейтинг','xin-v2'); ?> →</a></div>
<div class="xv-pia-rank-tabs" role="tablist" aria-label="<?php esc_attr_e('Порядок рейтинга','xin-v2'); ?>"><button type="button" role="tab" aria-selected="true" aria-controls="xv-rank-popular" id="xv-rank-tab-popular" data-xv-rank-tab="popular"><?php esc_html_e('По просмотрам','xin-v2'); ?></button><button type="button" role="tab" aria-selected="false" aria-controls="xv-rank-rating" id="xv-rank-tab-rating" data-xv-rank-tab="rating"><?php esc_html_e('По оценке','xin-v2'); ?></button></div>
<?php foreach(array('popular'=>$xv_popular,'rating'=>$xv_rated) as $rankKey=>$books) : ?><div class="xv-pia-rank-grid" role="tabpanel" aria-labelledby="xv-rank-tab-<?php echo esc_attr($rankKey); ?>" id="xv-rank-<?php echo esc_attr($rankKey); ?>" data-xv-rank-panel="<?php echo esc_attr($rankKey); ?>"<?php if('rating'===$rankKey)echo ' hidden'; ?>><?php foreach($books as $index=>$book)xv_rank_row($book->ID,$index+1); ?></div><?php endforeach; ?>
</section>
<?php get_template_part('template-parts/home','curation',array('books'=>$xv_featured)); ?>
<?php if ($xv_history) : ?><section class="xv-section xv-section-wide"><div class="xv-section-head"><h2><?php esc_html_e('Продолжить чтение','xin-v2'); ?></h2><a class="xv-text-link" href="<?php echo esc_url(xv_page_url('template-library.php')); ?>"><?php esc_html_e('Закладки','xin-v2'); ?> →</a></div><div class="xv-return-grid"><?php foreach($xv_history as $xv_read) : $xv_novel=get_post((int)$xv_read['novel']); $xv_chapter=get_post((int)$xv_read['chapter']); if(!$xv_novel||!$xv_chapter||'publish'!==$xv_chapter->post_status)continue; ?><a class="xv-return-card" href="<?php echo esc_url(get_permalink($xv_chapter)); ?>"><img class="xv-return-cover" src="<?php echo esc_url(xv_cover_url($xv_novel->ID)); ?>" alt=""><span class="xv-return-copy"><strong><?php echo esc_html(get_the_title($xv_novel)); ?></strong><span><?php echo esc_html(get_the_title($xv_chapter)); ?></span></span></a><?php endforeach; ?></div></section><?php endif; ?>
<?php foreach(array(array(__('Популярные произведения','xin-v2'),$xv_popular,'popular'),array(__('Новые произведения','xin-v2'),$xv_fresh,'latest')) as $xv_shelf) : if(!$xv_shelf[1])continue; ?>
<section class="xv-section xv-section-wide"><div class="xv-section-head"><h2><?php echo esc_html($xv_shelf[0]); ?></h2><a href="<?php echo esc_url(add_query_arg('sort',$xv_shelf[2],$xv_archive)); ?>"><?php esc_html_e('Смотреть все','xin-v2'); ?></a></div><div class="xv-card-row xv-home-cards"><?php foreach($xv_shelf[1] as $xv_novel) xv_novel_card($xv_novel->ID); ?></div></section>
<?php endforeach; ?>
<?php if ($xv_genres&&!is_wp_error($xv_genres)) : ?>
<section class="xv-section xv-section-wide"><div class="xv-section-head"><div><h2><?php esc_html_e('Лучшие истории по жанрам','xin-v2'); ?></h2><p class="xv-world-section-sub"><?php esc_html_e('Найдите следующую историю в любимом жанре','xin-v2'); ?></p></div></div><div class="xv-world-genre-books"><?php foreach($xv_genres as $xv_genre) : $xv_books=get_posts(array('post_type'=>'novel','posts_per_page'=>1,'tax_query'=>array(array('taxonomy'=>'genre','field'=>'term_id','terms'=>$xv_genre->term_id)))); if(!$xv_books)continue; $xv_book=$xv_books[0]; ?><article><a class="xv-world-genre-label" href="<?php echo esc_url(get_term_link($xv_genre)); ?>"><?php echo esc_html($xv_genre->name); ?></a><?php xv_novel_card($xv_book->ID); ?><p><?php echo esc_html(wp_trim_words(get_the_excerpt($xv_book),28)); ?></p></article><?php endforeach; ?></div></section>
<section class="xv-section xv-section-wide"><div class="xv-section-head"><h2><?php esc_html_e('Популярные жанры','xin-v2'); ?></h2></div><div class="xv-genre-grid"><?php foreach($xv_genres as $xv_genre) : ?><a class="xv-genre-cell" href="<?php echo esc_url(get_term_link($xv_genre)); ?>"><span class="xv-genre-name"><?php echo esc_html($xv_genre->name); ?></span><span class="xv-genre-count"><?php echo esc_html($xv_genre->count); ?></span></a><?php endforeach; ?></div></section>
<?php endif; ?>
<?php if($xv_updates) : ?><section class="xv-section xv-section-wide"><div class="xv-section-head"><h2><?php esc_html_e('Последние обновления','xin-v2'); ?></h2><a href="<?php echo esc_url(get_post_type_archive_link('chapter')); ?>"><?php esc_html_e('Смотреть все','xin-v2'); ?></a></div><div class="xv-upd-list"><?php foreach($xv_updates as $xv_chapter)xv_update_row($xv_chapter); ?></div></section><?php endif; ?>
</div>
<?php get_footer(); ?>
