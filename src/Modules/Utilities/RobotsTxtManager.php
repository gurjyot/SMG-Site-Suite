<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RobotsTxtManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_robots_txt';

    public function register():void{add_filter('robots_txt',[$this,'robots'],99,2);}

    public function settingsSchema():array{
        return [['key'=>'content','type'=>'textarea','label'=>__('Custom robots.txt content','smg-site-suite'),'description'=>__('Leave empty to use the WordPress default virtual robots.txt.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['content'=>sanitize_textarea_field((string)($input['content']??''))],false);}

    public function robots(string $output,bool $public):string{
        $content=trim((string)($this->settings()['content']??''));
        return $content!==''?$content."\n":$output;
    }
}
