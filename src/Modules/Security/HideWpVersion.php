<?php
namespace SMG\SiteSuite\Modules\Security;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class HideWpVersion implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','wp_generator');
        add_filter('the_generator','__return_empty_string');
        add_filter('style_loader_src',[$this,'stripCoreVersion'],9999);
        add_filter('script_loader_src',[$this,'stripCoreVersion'],9999);
    }
    public function stripCoreVersion(string $src):string{
        if($src===''||!str_contains($src,'ver='))return $src;
        $version=(string)get_bloginfo('version');
        $query=(string)wp_parse_url($src,PHP_URL_QUERY);
        parse_str($query,$args);
        return isset($args['ver'])&&(string)$args['ver']===$version?remove_query_arg('ver',$src):$src;
    }
}
