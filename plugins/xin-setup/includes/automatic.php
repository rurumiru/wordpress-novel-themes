<?php
/** Idempotent installation/import setup. No accounts, sample prose or credentials. */
if(!defined('ABSPATH')) exit;
function xs_schedule_setup() {
 update_option('xs_setup_pending',1,false);
 update_option('xs_counter_cursor',0,false);
}
add_action('after_switch_theme','xs_schedule_setup');
add_action('import_end','xs_schedule_setup');
add_action('xni_import_complete','xs_schedule_setup');
add_action('xff_import_complete','xs_schedule_setup');

function xs_automatic_setup() {
 if(!function_exists('get_home_path'))require_once ABSPATH.'wp-admin/includes/file.php';
 if(!current_user_can(xs_cap())) return new WP_Error('xs_forbidden','Administrator permission required.');
 if(!xs_theme()['ours'] || !post_type_exists('novel') || !post_type_exists('chapter')) return new WP_Error('xs_theme','Activate XIN-V2 or XIN-Com first.');
 if(!get_option('xs_setup_snapshot')) {
  $keys=['permalink_structure','show_on_front','page_on_front','page_for_posts','posts_per_page','thumbnail_size_w','thumbnail_size_h','thumbnail_crop','medium_size_w','medium_size_h','default_comment_status','thread_comments','thread_comments_depth'];
  $snapshot=[];foreach($keys as $key)$snapshot[$key]=get_option($key);
  update_option('xs_setup_snapshot',['options'=>$snapshot,'created'=>time()],false);
 }
 $steps=[];$errors=[];
 $pages=xs_create_pages();foreach($pages as $template=>$id)$steps[]='Page: '.$template.' (#'.$id.')';
 // Information routes used by navigation. Do not replace existing editorial text.
 foreach(['help'=>'Помощь','contacts'=>'Контакты'] as $slug=>$title) {
  if(get_page_by_path($slug)) continue;
  $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_title'=>$title],true);
  if(is_wp_error($id)){$errors[]=$id->get_error_message();continue;}
  update_post_meta($id,'_wp_page_template','template-info.php');$steps[]='Page: '.$slug;
 }
 $settings=xs_wanted_settings();
 foreach(['front','reading','thumbnails','taxonomies'] as $key) {
  if(isset($settings[$key]) && !call_user_func($settings[$key]['ok'])) {
   $steps[]=call_user_func($settings[$key]['fix']);
   if(!call_user_func($settings[$key]['ok']))$errors[]='Setting requires attention: '.$key;
  }
 }
 if(get_option('permalink_structure')!=='/%postname%/') {global $wp_rewrite;$wp_rewrite->set_permalink_structure('/%postname%/');$steps[]='Permalinks: /%postname%/';}
 update_option('default_comment_status','open');update_option('thread_comments',1);update_option('thread_comments_depth',3);
 $theme_options=(array)get_option('xv_settings',[]);
 $theme_options['reader_endless']=false;update_option('xv_settings',$theme_options);
 $menus=xs_build_menus();foreach($menus as $menu)$steps[]='Menu: '.$menu;
 // One rewrite flush per completed setup, after CPTs have registered.
 flush_rewrite_rules(true);
 $rules=get_option('rewrite_rules',[]);
 if(!is_array($rules) || !$rules)$errors[]='Rewrite rules could not be generated.';
 if(!isset($rules['novels/?$']) || !isset($rules['updates/?$']))$errors[]='Novel/chapter archive routes require attention.';
 $report=['version'=>XS_VERSION,'time'=>time(),'steps'=>$steps,'errors'=>$errors,'server'=>['apache_rules'=>get_home_path().'.htaccess','nginx'=>'Configure try_files $uri $uri/ /index.php?$args; in the server block.']];
 update_option('xs_setup_report',$report,false);delete_option('xs_setup_pending');update_option('xs_counter_cursor',0,false);update_option('xs_counters_pending',1,false);
 return $report;
}

/** At most 100 titles per admin request, with a persistent cursor. */
function xs_refresh_counter_batch() {
 if(!current_user_can(xs_cap()) || !get_option('xs_counters_pending'))return;
 global $wpdb;$cursor=(int)get_option('xs_counter_cursor',0);
 $ids=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='novel' AND post_status='publish' AND ID>%d ORDER BY ID ASC LIMIT 100",$cursor));
 foreach($ids as $id) {
  $rows=$wpdb->get_results($wpdb->prepare("SELECT p.ID,p.post_date_gmt FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_xin_novel' WHERE p.post_type='chapter' AND p.post_status='publish' AND m.meta_value=%d",$id));
  update_post_meta($id,'_xin_chapter_count',count($rows));$last=0;foreach($rows as $row)$last=max($last,(int)strtotime($row->post_date_gmt.' GMT'));update_post_meta($id,'_xin_last_chapter_at',$last);
  if(function_exists('xv_forget_chapter_index'))xv_forget_chapter_index($id);
  update_option('xs_counter_cursor',(int)$id,false);
 }
 if(count($ids)<100) {delete_option('xs_counters_pending');if(function_exists('xv_forget_lists'))xv_forget_lists();}
}
add_action('admin_init',function(){
 if(wp_doing_ajax() || !current_user_can(xs_cap()))return;
 if(get_option('xs_setup_pending'))xs_automatic_setup();
 xs_refresh_counter_batch();
},30);
add_action('admin_post_xs_quick_setup',function(){
 if(!current_user_can(xs_cap()))wp_die('Forbidden',403);
 check_admin_referer('xs_quick_setup');xs_automatic_setup();xs_refresh_counter_batch();wp_safe_redirect(admin_url('tools.php?page=xin-setup'));exit;
});
// Finish large imports while an administrator keeps a dashboard page open.
add_action('wp_ajax_xs_counter_batch',function(){
 if(!current_user_can(xs_cap()))wp_send_json_error(['message'=>'Forbidden'],403);
 check_ajax_referer('xs_counter_batch');
 xs_refresh_counter_batch();
 wp_send_json_success(['pending'=>(bool)get_option('xs_counters_pending'),'cursor'=>(int)get_option('xs_counter_cursor',0)]);
});
add_action('admin_footer',function(){
 if(!current_user_can(xs_cap()) || !get_option('xs_counters_pending'))return;
 ?>
 <script>
 (function(){
 const nonce=<?php echo wp_json_encode(wp_create_nonce('xs_counter_batch')); ?>;
 async function next(){
  try{
   const body=new URLSearchParams({action:'xs_counter_batch',_ajax_nonce:nonce});
   const response=await fetch(ajaxurl,{method:'POST',credentials:'same-origin',body});
   const result=await response.json();
   if(response.ok && result.success && result.data.pending)setTimeout(next,250);
  }catch(error){console.warn('XIN Setup: counter refresh will resume on the next dashboard page.');}
 }
 next();
 })();
 </script>
 <?php
});
add_action('admin_notices',function(){
 if(!current_user_can(xs_cap()))return;
 $report=get_option('xs_setup_report');if(!$report)return;
 $screen=get_current_screen();if(!$screen || $screen->id!=='tools_page_xin-setup')return;
 echo '<div class="notice notice-'.(!empty($report['errors'])?'warning':'success').'"><p><strong>XIN Setup 3</strong> — '.esc_html(wp_date('d.m.Y H:i',$report['time'])).'. '.esc_html(implode(' · ',$report['steps']?:['Настройки проверены, всё готово.'])).'</p>';
 foreach($report['errors'] as $error)echo '<p>'.esc_html($error).'</p>';
 echo '<p>На Nginx нужен серверный fallback: <code>try_files $uri $uri/ /index.php?$args;</code>. WordPress не может менять конфигурацию Nginx.</p></div>';
});
