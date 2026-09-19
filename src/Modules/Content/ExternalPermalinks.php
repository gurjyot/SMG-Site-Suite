<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ExternalPermalinks implements ModuleInterface {
    private const META='_smg_site_suite_external_permalink';

    public function register():void{
        add_action('post_submitbox_misc_actions',[$this,'field']);
        add_action('save_post',[$this,'save'],20);
        add_filter('post_link',[$this,'link'],20,3);
        add_filter('page_link',[$this,'pageLink'],20,3);
        add_filter('post_type_link',[$this,'typeLink'],20,4);
        add_action('template_redirect',[$this,'redirect'],0);
    }

    public function field():void{
        global $post;
        if(!$post instanceof \WP_Post||!current_user_can('edit_post',$post->ID))return;
        $value=(string)get_post_meta($post->ID,self::META,true);
        echo '<div class="misc-pub-section"><label for="smg_external_permalink">'.esc_html__('External permalink','smg-site-suite').'</label>';
        echo '<input class="widefat" id="smg_external_permalink" type="url" name="smg_external_permalink" value="'.esc_attr($value).'" placeholder="https://example.com/">';
        wp_nonce_field('smg_site_suite_external_permalink_'.$post->ID,'smg_site_suite_external_permalink_nonce');
        echo '</div>';
    }

    public function save(int $postId):void{
        if(!isset($_POST['smg_site_suite_external_permalink_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_external_permalink_nonce'])),'smg_site_suite_external_permalink_'.$postId))return;
        if(!current_user_can('edit_post',$postId))return;
        $url=isset($_POST['smg_external_permalink'])
            ? esc_url_raw(wp_unslash($_POST['smg_external_permalink']))
            : '';
        if($url==='')delete_post_meta($postId,self::META);else update_post_meta($postId,self::META,$url);
    }

    public function link(string $url,\WP_Post $post,bool $leavename=false):string{return $this->external($post->ID,$url);}
    public function pageLink(string $url,int $postId,bool $sample=false):string{return $this->external($postId,$url);}
    public function typeLink(string $url,\WP_Post $post,bool $leavename=false,bool $sample=false):string{return $this->external($post->ID,$url);}

    public function redirect():void{
        if(is_admin()||!is_singular())return;
        $postId=get_queried_object_id();
        $url=(string)get_post_meta($postId,self::META,true);
        if($url!==''){wp_redirect($url,302,'SMG Site Suite');exit;}
    }

    private function external(int $postId,string $fallback):string{
        $url=(string)get_post_meta($postId,self::META,true);
        return $url!==''?$url:$fallback;
    }
}
