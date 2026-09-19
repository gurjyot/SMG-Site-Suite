<?php
namespace SMG\SiteSuite\Modules\Security;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class HideWpVersion implements ModuleInterface {
    public function register():void{
        remove_action('wp_head','wp_generator');
        add_filter('the_generator',[$this,'emptyGenerator']);

        foreach(['style_loader_src','script_loader_src'] as $hook){
            add_filter($hook,[$this,'removeCoreVersion'],9999);
        }
    }

    public function emptyGenerator():string{
        return '';
    }

    public function removeCoreVersion(string $src):string{
        if($src===''||!str_contains($src,'ver='))return $src;

        $query=(string)wp_parse_url($src,PHP_URL_QUERY);
        parse_str($query,$args);

        if(!isset($args['ver']))return $src;

        $coreVersion=(string)get_bloginfo('version');
        return (string)$args['ver']===$coreVersion?remove_query_arg('ver',$src):$src;
    }
}
