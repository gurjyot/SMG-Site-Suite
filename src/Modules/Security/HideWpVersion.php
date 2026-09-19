<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class HideWpVersion implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','wp_generator');
        add_filter('the_generator','__return_empty_string');
        add_filter('style_loader_src',[$this,'stripVersion'],9999);
        add_filter('script_loader_src',[$this,'stripVersion'],9999);
    }
    public function stripVersion(string $src):string{
        if($src==='')return $src;
        return remove_query_arg('ver',$src);
    }
}
