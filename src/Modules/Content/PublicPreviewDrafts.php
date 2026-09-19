<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class PublicPreviewDrafts implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_public_previews';
    private const META='_smg_site_suite_public_preview_token';

    public function register():void{
        add_action('post_submitbox_misc_actions',[$this,'controls']);
        add_action('save_post',[$this,'saveToken'],20,3);
        add_filter('posts_results',[$this,'allowPreview'],20,2);
    }

    public function settingsSchema():array{
        return [['key'=>'expires_hours','type'=>'number','label'=>__('Preview link lifetime (hours)','smg-site-suite'),'default'=>72]];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['expires_hours'=>72]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,['expires_hours'=>max(1,min(720,absint($input['expires_hours']??72)))],false);
    }

    public function controls():void{
        global $post;
        if(!$post instanceof \WP_Post||!current_user_can('edit_post',$post->ID))return;
        if(!in_array($post->post_status,['draft','pending','future'],true))return;

        $data=get_post_meta($post->ID,self::META,true);
        $token=is_array($data)?(string)($data['token']??''):'';
        $expires=is_array($data)?(int)($data['expires']??0):0;

        echo '<div class="misc-pub-section"><label><input type="checkbox" name="smg_public_preview" value="1" '.checked($token!==''&&$expires>time(),true,false).'> '.esc_html__('Enable public preview link','smg-site-suite').'</label>';
        if($token!==''&&$expires>time()){
            $url=add_query_arg(['smg_preview'=>$token],get_permalink($post));
            echo '<p><input class="widefat" readonly value="'.esc_attr($url).'"></p>';
        }
        wp_nonce_field('smg_site_suite_public_preview_'.$post->ID,'smg_site_suite_public_preview_nonce');
        echo '</div>';
    }

    public function saveToken(int $postId,\WP_Post $post,bool $update):void{
        if(!isset($_POST['smg_site_suite_public_preview_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_public_preview_nonce'])),'smg_site_suite_public_preview_'.$postId))return;
        if(!current_user_can('edit_post',$postId))return;

        if(empty($_POST['smg_public_preview'])){
            delete_post_meta($postId,self::META);
            return;
        }

        $existing=get_post_meta($postId,self::META,true);
        $token=is_array($existing)&&(string)($existing['token']??'')!==''?(string)$existing['token']:wp_generate_password(32,false,false);
        $hours=(int)($this->settings()['expires_hours']??72);
        update_post_meta($postId,self::META,['token'=>$token,'expires'=>time()+($hours*HOUR_IN_SECONDS)]);
    }

    public function allowPreview(array $posts,\WP_Query $query):array{
        if(is_admin()||empty($_GET['smg_preview'])||count($posts)!==1)return $posts;
        $post=$posts[0]??null;
        if(!$post instanceof \WP_Post||$post->post_status==='publish')return $posts;

        $data=get_post_meta($post->ID,self::META,true);
        $provided=sanitize_text_field(wp_unslash($_GET['smg_preview']));
        if(!is_array($data)||(int)($data['expires']??0)<time()||!hash_equals((string)($data['token']??''),$provided))return [];

        $post->post_status='publish';
        return [$post];
    }
}
