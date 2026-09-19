<?php
namespace SMG\SiteSuite;
final class Autoloader {
    public static function register():void{
        spl_autoload_register(static function(string $class):void{
            $prefixes=[
                'SMG\\SiteSuite\\'=>SMG_SITE_SUITE_PATH.'src/',
                'SMG\\WPFoundation\\'=>SMG_SITE_SUITE_PATH.'vendor/smg-wp-foundation/src/',
            ];
            foreach($prefixes as $prefix=>$base){
                if(strncmp($class,$prefix,strlen($prefix))!==0)continue;
                $relative=substr($class,strlen($prefix));
                $file=$base.str_replace('\\','/',$relative).'.php';
                if(is_readable($file))require_once $file;
                return;
            }
        });
    }
}
