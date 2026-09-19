<?php
namespace SMG\SiteSuite\Modules\Media;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class MediaCategories implements ModuleInterface {
    public function register():void{
        add_action('init',[$this,'taxonomy']);
    }

    public function taxonomy():void{
        register_taxonomy('smg_media_category',['attachment'],[
            'labels'=>[
                'name'=>__('Media Categories','smg-site-suite'),
                'singular_name'=>__('Media Category','smg-site-suite'),
            ],
            'public'=>false,
            'show_ui'=>true,
            'show_admin_column'=>true,
            'show_in_rest'=>true,
            'hierarchical'=>true,
            'rewrite'=>false,
        ]);
    }
}
