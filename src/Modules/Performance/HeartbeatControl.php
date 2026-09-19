<?php
namespace SMG\SiteSuite\Modules\Performance;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class HeartbeatControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_heartbeat_control';
    public function register():void{add_filter('heartbeat_settings',[$this,'settingsFilter']);}
    public function settingsSchema():array{return [['key'=>'interval','type'=>'select','label'=>__('Heartbeat interval','smg-site-suite'),'default'=>'60','options'=>['15'=>'15 seconds','30'=>'30 seconds','60'=>'60 seconds','120'=>'120 seconds']]];}
    public function settings():array{$v=get_option(self::OPTION,['interval'=>'60']);return is_array($v)?$v:['interval'=>'60'];}
    public function saveSettings(array $input):void{$value=(string)($input['interval']??'60');if(!in_array($value,['15','30','60','120'],true))$value='60';update_option(self::OPTION,['interval'=>$value],false);}
    public function settingsFilter(array $settings):array{$s=$this->settings();$settings['interval']=(int)($s['interval']??60);return $settings;}
}
