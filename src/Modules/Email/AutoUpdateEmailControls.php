<?php
namespace SMG\SiteSuite\Modules\Email;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class AutoUpdateEmailControls implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_update_emails';

    public function register():void{
        add_filter('auto_core_update_send_email',[$this,'core'],10,4);
        add_filter('auto_plugin_update_send_email',[$this,'plugin'],10,2);
        add_filter('auto_theme_update_send_email',[$this,'theme'],10,2);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'core','type'=>'checkbox','label'=>__('Suppress automatic WordPress core update emails','smg-site-suite')],
            ['key'=>'plugins','type'=>'checkbox','label'=>__('Suppress automatic plugin update emails','smg-site-suite')],
            ['key'=>'themes','type'=>'checkbox','label'=>__('Suppress automatic theme update emails','smg-site-suite')],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['core'=>!empty($input['core']),'plugins'=>!empty($input['plugins']),'themes'=>!empty($input['themes'])],false);}

    public function core(bool $send,string $type,$coreUpdate,$result):bool{return !empty($this->settings()['core'])?false:$send;}
    public function plugin(bool $send,$updateResults):bool{return !empty($this->settings()['plugins'])?false:$send;}
    public function theme(bool $send,$updateResults):bool{return !empty($this->settings()['themes'])?false:$send;}
}
