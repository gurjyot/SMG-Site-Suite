<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class AutoFeaturedImage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_auto_featured_image';

    public function register():void{add_action('save_post',[$this,'assign'],30,3);}

    public function settingsSchema():array{
        $options=[];
        foreach(get_post_types(['show_ui'=>true],'objects') as $type){
            if(post_type_supports($type->name,'thumbnail'))$options[$type->name]=$type->labels->singular_name;
        }
        return [['key'=>'post_types','type'=>'multiselect','label'=>__('Post types','smg-site-suite'),'options'=>$options,'description'=>__('When a post has no featured image, use its first attached image.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,['post_types'=>['post']]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $types=isset($input['post_types'])&&is_array($input['post_types'])?array_values(array_unique(array_map('sanitize_key',$input['post_types']))):[];
        update_option(self::OPTION,['post_types'=>$types],false);
    }

    public function assign(int $postId,\WP_Post $post,bool $update):void{
        if(wp_is_post_revision($postId)||wp_is_post_autosave($postId)||has_post_thumbnail($postId))return;
        if(!in_array($post->post_type,(array)($this->settings()['post_types']??[]),true))return;

        $images=get_children([
            'post_parent'=>$postId,
            'post_type'=>'attachment',
            'post_mime_type'=>'image',
            'numberposts'=>1,
            'orderby'=>'menu_order ID',
            'order'=>'ASC',
            'fields'=>'ids',
        ]);
        $imageId=is_array($images)&&$images!==[]?(int)reset($images):0;
        if($imageId>0)set_post_thumbnail($postId,$imageId);
    }
}
