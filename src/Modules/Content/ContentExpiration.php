<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ContentExpiration implements ModuleInterface {
    private const META='_smg_site_suite_content_expiration';
    private const HOOK='smg_site_suite_content_expire';

    public function register():void{
        add_action('post_submitbox_misc_actions',[$this,'controls']);
        add_action('save_post',[$this,'save'],30,3);
        add_action(self::HOOK,[$this,'expire'],10,2);
    }

    public function controls():void{
        global $post;
        if(!$post instanceof \WP_Post||!current_user_can('edit_post',$post->ID))return;
        if($post->post_type==='attachment')return;

        $data=get_post_meta($post->ID,self::META,true);
        $date=is_array($data)?(string)($data['date']??''):'';
        $action=is_array($data)?(string)($data['action']??'draft'):'draft';

        echo '<div class="misc-pub-section"><strong>'.esc_html__('Content Expiration','smg-site-suite').'</strong>';
        echo '<p><input type="datetime-local" name="smg_expiration_date" value="'.esc_attr($date).'"></p>';
        echo '<p><select name="smg_expiration_action">';
        foreach(['draft'=>__('Move to Draft','smg-site-suite'),'private'=>__('Make Private','smg-site-suite'),'trash'=>__('Move to Trash','smg-site-suite')] as $value=>$label){
            echo '<option value="'.esc_attr($value).'" '.selected($action,$value,false).'>'.esc_html($label).'</option>';
        }
        echo '</select></p>';
        wp_nonce_field('smg_site_suite_expiration_'.$post->ID,'smg_site_suite_expiration_nonce');
        echo '</div>';
    }

    public function save(int $postId,\WP_Post $post,bool $update):void{
        if(!isset($_POST['smg_site_suite_expiration_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_expiration_nonce'])),'smg_site_suite_expiration_'.$postId))return;
        if(!current_user_can('edit_post',$postId))return;

        $existing=wp_next_scheduled(self::HOOK,[$postId,(string)get_post_meta($postId,self::META,true)]);
        if($existing)wp_unschedule_event($existing,self::HOOK,[$postId,(string)get_post_meta($postId,self::META,true)]);

        $date=sanitize_text_field((string)($_POST['smg_expiration_date']??''));
        $action=sanitize_key((string)($_POST['smg_expiration_action']??'draft'));
        if(!in_array($action,['draft','private','trash'],true))$action='draft';

        if($date===''){
            delete_post_meta($postId,self::META);
            return;
        }

        $timezone=wp_timezone();
        $parsed=\DateTimeImmutable::createFromFormat('Y-m-d\TH:i',$date,$timezone);
        if(!$parsed||$parsed->getTimestamp()<=time()){
            delete_post_meta($postId,self::META);
            return;
        }

        $token=wp_generate_password(12,false,false);
        update_post_meta($postId,self::META,['date'=>$date,'action'=>$action,'token'=>$token]);
        wp_schedule_single_event($parsed->getTimestamp(),self::HOOK,[$postId,$token]);
    }

    public function expire(int $postId,string $token):void{
        $data=get_post_meta($postId,self::META,true);
        if(!is_array($data)||!hash_equals((string)($data['token']??''),$token))return;

        $action=(string)($data['action']??'draft');
        if($action==='trash')wp_trash_post($postId);
        elseif(in_array($action,['draft','private'],true))wp_update_post(['ID'=>$postId,'post_status'=>$action]);

        delete_post_meta($postId,self::META);
    }
}
