<?php
namespace SMG\SiteSuite\Modules\Utilities;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class CustomExcerptLength implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_excerpt_length';
    public function register():void{add_filter('excerpt_length',[$this,'length'],999);}
    public function settingsSchema():array{return [[
        'key'=>'words','type'=>'number','label'=>__('Excerpt length in words','smg-site-suite'),
        'default'=>40,'description'=>__('Applies to automatically generated WordPress excerpts.','smg-site-suite')
    ]];}
    public function settings():array{$v=get_option(self::OPTION,['words'=>40]);return is_array($v)?$v:['words'=>40];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['words'=>max(5,min(500,absint($input['words']??40)))],false);}
    public function length(int $length):int{$s=$this->settings();return (int)($s['words']??$length);}
}
