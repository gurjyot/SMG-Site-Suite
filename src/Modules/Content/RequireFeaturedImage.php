<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RequireFeaturedImage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_require_featured_image';
    private static bool $reverting=false;

    public function register():void{
        add_action('transition_post_status',[$this,'enforce'],20,3);
        add_action('admin_notices',[$this,'notice']);
    }

    public function settingsSchema():array{
        $options=[];
        foreach(get_post_types(['show_ui'=>true],'objects') as $type){
            if(post_type_supports($type->name,'thumbnail'))$options[$type->name]=$type->labels->singular_name;
        }
        return [['key'=>'post_types','type'=>'multiselect','label'=>__('Require featured image for','smg-site-suite'),'options'=>$options]];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['post_types'=>['post']]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        $types=isset($input['post_types'])&&is_array($input['post_types'])?array_values(array_unique(array_map('sanitize_key',$input['post_types']))):[];
        update_option(self::OPTION,['post_types'=>$types],false);
    }

    public function enforce(string $newStatus,string $oldStatus,\WP_Post $post):void{
        if(self::$reverting||$newStatus!=='publish'||$oldStatus==='publish')return;
        if(!in_array($post->post_type,(array)($this->settings()['post_types']??[]),true))return;
        if(has_post_thumbnail($post->ID))return;

        self::$reverting=true;
        wp_update_post(['ID'=>$post->ID,'post_status'=>'draft']);
        self::$reverting=false;

        set_transient('smg_site_suite_featured_image_notice_'.get_current_user_id(),1,60);
    }

    public function notice():void{
        $key='smg_site_suite_featured_image_notice_'.get_current_user_id();
        if(!get_transient($key))return;
        delete_transient($key);
        echo '<div class="notice notice-error"><p>'.esc_html__('Publishing was blocked because a featured image is required.','smg-site-suite').'</p></div>';
    }
}
