<?php
namespace SMG\SiteSuite\Modules\Content;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class DefaultFeaturedImage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_default_featured_image';

    public function register():void{
        add_filter('post_thumbnail_id',[$this,'thumbnail'],20,2);
    }

    public function settingsSchema():array{
        $options=[];
        foreach(get_post_types(['show_ui'=>true],'objects') as $type){
            if(post_type_supports($type->name,'thumbnail'))$options[$type->name]=$type->labels->singular_name;
        }
        return [
            ['key'=>'image_id','type'=>'media_select','label'=>__('Default image','smg-site-suite')],
            ['key'=>'post_types','type'=>'multiselect','label'=>__('Post types','smg-site-suite'),'options'=>$options],
        ];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['image_id'=>0,'post_types'=>['post']]);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        $imageId=absint($input['image_id']??0);
        if($imageId>0&&!wp_attachment_is_image($imageId))$imageId=0;
        $types=isset($input['post_types'])&&is_array($input['post_types'])?array_values(array_unique(array_map('sanitize_key',$input['post_types']))):[];
        update_option(self::OPTION,['image_id'=>$imageId,'post_types'=>$types],false);
    }

    public function thumbnail($thumbnailId,$post){
        if((int)$thumbnailId>0)return $thumbnailId;
        $post=get_post($post);
        if(!$post)return $thumbnailId;

        $settings=$this->settings();
        if(!in_array($post->post_type,(array)($settings['post_types']??[]),true))return $thumbnailId;

        $imageId=(int)($settings['image_id']??0);
        return $imageId>0?$imageId:$thumbnailId;
    }
}
