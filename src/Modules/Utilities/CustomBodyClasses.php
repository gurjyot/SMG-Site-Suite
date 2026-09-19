<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CustomBodyClasses implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_body_classes';

    public function register():void{add_filter('body_class',[$this,'classes']);}

    public function settingsSchema():array{
        return [['key'=>'classes','type'=>'text','label'=>__('Global body classes','smg-site-suite'),'description'=>__('Space-separated CSS classes added to the public body element.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $classes=preg_split('/\s+/',trim((string)($input['classes']??'')))?:[];
        $classes=array_values(array_unique(array_filter(array_map('sanitize_html_class',$classes))));
        update_option(self::OPTION,['classes'=>implode(' ',$classes)],false);
    }

    public function classes(array $classes):array{
        $extra=preg_split('/\s+/',trim((string)($this->settings()['classes']??'')))?:[];
        return array_values(array_unique(array_merge($classes,array_filter($extra))));
    }
}
