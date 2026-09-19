<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class ActivePluginsFirst implements ModuleInterface {
    public function register():void{add_filter('all_plugins',[$this,'sort']);}
    public function sort(array $plugins):array{
        $active=(array)get_option('active_plugins',[]);
        uksort($plugins,static function(string $a,string $b)use($active):int{
            $aa=in_array($a,$active,true);$bb=in_array($b,$active,true);
            if($aa!==$bb)return $aa?-1:1;
            return strcasecmp($plugins[$a]['Name']??$a,$plugins[$b]['Name']??$b);
        });
        return $plugins;
    }
}
