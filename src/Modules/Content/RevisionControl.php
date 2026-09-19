<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class RevisionControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_revision_control';
    public function register():void{add_filter('wp_revisions_to_keep',[$this,'limit'],10,2);}
    public function settingsSchema():array{return [['key'=>'limit','type'=>'number','label'=>__('Revisions to keep','smg-site-suite'),'default'=>10,'description'=>__('Use 0 to disable revisions.','smg-site-suite')]];}
    public function settings():array{$v=get_option(self::OPTION,['limit'=>10]);return is_array($v)?$v:['limit'=>10];}
    public function saveSettings(array $input):void{$limit=max(0,min(100,absint($input['limit']??10)));update_option(self::OPTION,['limit'=>$limit],false);}
    public function limit(int $num,WP_Post $post):int{$s=$this->settings();return isset($s['limit'])?(int)$s['limit']:$num;}
}
