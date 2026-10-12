<?php
/** Run against a disposable/local WordPress: php tools/verify-release.php /path/to/wp-load.php */
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8081';
require $argv[1]??dirname(__DIR__,2).'/xi.community/wp-load.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';
$admins=get_users(['role'=>'administrator','number'=>1]);if(!$admins){fwrite(STDERR,'No local admin for setup test');exit(1);}
wp_set_current_user($admins[0]->ID);
$activation=activate_plugin('xin-setup/xin-setup.php');if(is_wp_error($activation)){fwrite(STDERR,$activation->get_error_message());exit(1);}
function check_release($value,$label){if(!$value){fwrite(STDERR,'FAIL '.$label.PHP_EOL);exit(1);}echo 'PASS '.$label.PHP_EOL;}
$first=xs_automatic_setup();check_release(!is_wp_error($first)&&empty($first['errors']),'Automatic setup');
xs_refresh_counter_batch();
$page_count=wp_count_posts('page')->publish;$second=xs_automatic_setup();xs_refresh_counter_batch();
check_release(wp_count_posts('page')->publish===$page_count,'Repeated setup creates no duplicate pages');
check_release(get_option('permalink_structure')==='/%postname%/','Pretty permalinks');
check_release(xs_page_for('template-auth.php') && xs_page_for('template-author.php'),'Authentication and studio pages');
check_release(isset(get_option('rewrite_rules')['novels/?$']) && isset(get_option('rewrite_rules')['updates/?$']),'Archive rewrite routes');
do_action('xni_import_complete');check_release((bool)get_option('xs_setup_pending'),'Importer schedules setup');xs_automatic_setup();xs_refresh_counter_batch();
foreach(['xv_save_novel','xv_save_chapter','xv_delete','xv_save_profile','xv_save_glossary'] as $action)check_release(has_action('admin_post_'.$action),'Frontend action registered: '.$action);
$id=wp_insert_post(['post_type'=>'novel','post_status'=>'draft','post_title'=>'Dev regression fixture'],true);check_release(!is_wp_error($id),'Temporary rating fixture');
try {
 $chapter=wp_insert_post(['post_type'=>'chapter','post_status'=>'draft','post_title'=>'Dev chapter fixture','post_author'=>get_current_user_id()],true);
 xv_chapter_index($id,true);
 update_post_meta($chapter,'_xin_novel',$id);update_post_meta($chapter,'_xin_number','1');
 check_release(in_array($chapter,xv_chapter_index($id,true),true),'Studio index receives metadata added after insertion');
 check_release(!in_array($chapter,xv_chapter_index($id,false),true),'Draft chapter excluded from public index');
 update_post_meta($id,'_xin_rating',4);update_post_meta($id,'_xin_rating_count',100);
 $dev=get_user_by('login','dev-preview');wp_set_current_user($dev?$dev->ID:$admins[0]->ID);
 $request=new WP_REST_Request('POST','/xin/v2/rate');$request->set_param('novel',$id);$request->set_param('value',2);$one=rest_do_request($request);
 check_release($one->get_status()===200 && $one->get_data()['count']===101,'Rating saves with imported aggregate preserved');
 $request->set_param('value',5);$two=rest_do_request($request);
 check_release($two->get_status()===200 && $two->get_data()['count']===101 && $two->get_data()['value']===4.01,'Repeat vote replaces the same user vote');
 $request->set_param('value',6);check_release(rest_do_request($request)->get_status()===400,'Out-of-range rating rejected');
 $request->set_param('value',-3);check_release(rest_do_request($request)->get_status()===400,'Negative rating rejected');
 $request->set_param('value',2.5);check_release(rest_do_request($request)->get_status()===400,'Fractional rating rejected');
 wp_set_current_user(0);$request->set_param('value',5);check_release(rest_do_request($request)->get_status()===401,'Anonymous rating rejected');
} finally {wp_set_current_user($admins[0]->ID);if(isset($chapter))wp_trash_post($chapter);wp_trash_post($id);}
echo 'Local release checks completed.'.PHP_EOL;
