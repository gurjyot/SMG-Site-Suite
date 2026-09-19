<?php
namespace SMG\WPFoundation\Modules;
final class DependencyChecker {
    public function check(ModuleDefinition $module):array{
        $deps=$module->dependencies();$missing=[];
        foreach((array)($deps['classes']??[]) as $v){if(!class_exists((string)$v))$missing[]='class:'.(string)$v;}
        foreach((array)($deps['functions']??[]) as $v){if(!function_exists((string)$v))$missing[]='function:'.(string)$v;}
        foreach((array)($deps['constants']??[]) as $v){if(!defined((string)$v))$missing[]='constant:'.(string)$v;}
        foreach((array)($deps['plugins']??[]) as $v){if(!$this->pluginActive((string)$v))$missing[]='plugin:'.(string)$v;}
        return ['available'=>$missing===[],'missing'=>$missing];
    }
    private function pluginActive(string $plugin):bool{if(in_array($plugin,(array)get_option('active_plugins',[]),true))return true;return is_multisite()&&isset(((array)get_site_option('active_sitewide_plugins',[]))[$plugin]);}
}
