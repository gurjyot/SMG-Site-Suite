<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class DashboardWidgets implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_dashboard_widgets';
    public function register():void{add_action('wp_dashboard_setup',[$this,'remove'],999);add_action('welcome_panel',[$this,'welcome'],0);}
    public function settingsSchema():array{return [
        ['key'=>'activity','type'=>'checkbox','label'=>__('Hide Activity','smg-site-suite')],
        ['key'=>'quick_draft','type'=>'checkbox','label'=>__('Hide Quick Draft','smg-site-suite')],
        ['key'=>'events','type'=>'checkbox','label'=>__('Hide WordPress Events and News','smg-site-suite')],
        ['key'=>'welcome','type'=>'checkbox','label'=>__('Hide Welcome panel','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['activity'=>!empty($input['activity']),'quick_draft'=>!empty($input['quick_draft']),'events'=>!empty($input['events']),'welcome'=>!empty($input['welcome'])],false);}
    public function remove():void{$s=$this->settings();if(!empty($s['activity']))remove_meta_box('dashboard_activity','dashboard','normal');if(!empty($s['quick_draft']))remove_meta_box('dashboard_quick_press','dashboard','side');if(!empty($s['events']))remove_meta_box('dashboard_primary','dashboard','side');}
    public function welcome():void{$s=$this->settings();if(!empty($s['welcome']))remove_action('welcome_panel','wp_welcome_panel');}
}
