<?php
if(!defined('ABSPATH')) exit;
$book=get_the_ID();
$votes=(array)get_post_meta($book,'_xin_votes',true);
$vote=(int)($votes[get_current_user_id()]??0);
?>
<details class="xv-rate-panel" data-xv-rate-panel data-novel="<?php echo (int)$book; ?>" data-saved="<?php esc_attr_e('Оценка сохранена','xin-v2'); ?>" data-error="<?php esc_attr_e('Не удалось сохранить оценку. Попробуйте ещё раз.','xin-v2'); ?>">
<summary><?php xv_the_icon('star'); ?><span><?php esc_html_e('Ваша оценка','xin-v2'); ?><?php if($vote) echo ': '.(int)$vote.'/5'; ?></span></summary>
<?php if(is_user_logged_in()) : ?><div class="xv-rate-buttons" role="group" aria-label="<?php esc_attr_e('Оценить произведение','xin-v2'); ?>"><?php for($i=1;$i<=5;$i++) : ?><button type="button" data-xv-rate-value="<?php echo $i; ?>" aria-pressed="<?php echo $vote===$i?'true':'false'; ?>" aria-label="<?php echo esc_attr(sprintf(__('%d из 5','xin-v2'),$i)); ?>"><?php xv_the_icon('star'); ?><span><?php echo $i; ?></span></button><?php endfor; ?></div><p class="xv-rate-status" role="status" aria-live="polite"></p><?php else : ?><a class="xv-rate-login" href="<?php echo esc_url(xv_login_url(get_permalink())); ?>"><?php esc_html_e('Войдите, чтобы оценить произведение','xin-v2'); ?></a><?php endif; ?>
</details>
