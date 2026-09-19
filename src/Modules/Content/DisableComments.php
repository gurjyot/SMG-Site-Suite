<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableComments implements ModuleInterface {
    public function register():void{
        add_action('admin_init',[$this,'removeSupport']);add_filter('comments_open','__return_false',20);add_filter('pings_open','__return_false',20);add_filter('comments_array','__return_empty_array',20);
        add_action('admin_menu',static fn()=>remove_menu_page('edit-comments.php'),999);add_action('wp_before_admin_bar_render',[$this,'adminBar']);
    }
    public function removeSupport():void{foreach(get_post_types([], 'names') as $type){remove_post_type_support($type,'comments');remove_post_type_support($type,'trackbacks');}}
    public function adminBar():void{global $wp_admin_bar;if($wp_admin_bar)$wp_admin_bar->remove_node('comments');}
}
