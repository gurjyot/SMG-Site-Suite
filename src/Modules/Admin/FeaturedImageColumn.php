<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class FeaturedImageColumn implements ModuleInterface {
    public function register():void{
        foreach(get_post_types(['show_ui'=>true],'names') as $type){
            if(!post_type_supports($type,'thumbnail'))continue;
            add_filter("manage_{$type}_posts_columns",[$this,'column']);
            add_action("manage_{$type}_posts_custom_column",[$this,'value'],10,2);
        }
    }
    public function column(array $columns):array{
        $out=[];
        foreach($columns as $key=>$label){$out[$key]=$label;if($key==='cb')$out['smg_featured_image']=__('Image','smg-site-suite');}
        return $out;
    }
    public function value(string $column,int $postId):void{
        if($column!=='smg_featured_image')return;
        echo get_the_post_thumbnail($postId,[48,48],['style'=>'width:48px;height:48px;object-fit:cover;border-radius:4px'])?:'—';
    }
}
