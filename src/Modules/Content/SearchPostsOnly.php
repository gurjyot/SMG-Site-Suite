<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class SearchPostsOnly implements ModuleInterface {
    public function register():void{add_action('pre_get_posts',[$this,'filter']);}
    public function filter(\WP_Query $query):void{
        if(is_admin()||!$query->is_main_query()||!$query->is_search())return;
        $query->set('post_type','post');
    }
}
