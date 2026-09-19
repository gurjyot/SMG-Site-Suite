<?php
namespace SMG\SiteSuite\Modules\Utilities;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class SearchVisibilityStatus implements ModuleInterface {
    public function register():void{
        add_action('admin_bar_menu',[$this,'bar'],999);
        add_action('admin_enqueue_scripts',[$this,'adminStyle']);
        add_action('wp_enqueue_scripts',[$this,'frontStyle']);
    }
    public function bar(\WP_Admin_Bar $bar):void{
        if(!current_user_can('edit_posts')||(string)get_option('blog_public')!=='0')return;
        $bar->add_node([
            'id'=>'smg-search-visibility',
            'title'=>'⚠ '.esc_html__('Search Engines Blocked','smg-site-suite'),
            'href'=>admin_url('options-reading.php'),
            'meta'=>['title'=>esc_attr__('WordPress is discouraging search engines from indexing this site.','smg-site-suite')]
        ]);
    }
    public function adminStyle():void{
        if(!is_admin_bar_showing()||!current_user_can('edit_posts')||(string)get_option('blog_public')!=='0')return;
        wp_add_inline_style('wp-admin',$this->css());
    }
    public function frontStyle():void{
        if(!is_admin_bar_showing()||!current_user_can('edit_posts')||(string)get_option('blog_public')!=='0')return;
        wp_add_inline_style('admin-bar',$this->css());
    }
    private function css():string{return '#wpadminbar #wp-admin-bar-smg-search-visibility>.ab-item{background:#b32d2e!important;color:#fff!important;font-weight:600}';}
}
