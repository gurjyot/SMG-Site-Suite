<?php
namespace SMG\SiteSuite\Modules\Utilities;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class MaintenanceMode implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_maintenance';
    public function register():void{add_action('template_redirect',[$this,'maybeBlock'],0);}
    public function settingsSchema():array{return [
        ['key'=>'message','type'=>'textarea','label'=>__('Maintenance message','smg-site-suite'),'default'=>__('We are performing scheduled maintenance. Please check back shortly.','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['message'=>sanitize_textarea_field((string)($input['message']??''))],false);}
    public function maybeBlock():void{
        if(is_admin()||wp_doing_ajax()||wp_doing_cron()||current_user_can('manage_options'))return;
        if(defined('REST_REQUEST')&&REST_REQUEST)return;
        status_header(503);nocache_headers();header('Retry-After: 3600');
        $message=(string)($this->settings()['message']??__('Maintenance in progress.','smg-site-suite'));
        wp_die(esc_html($message),esc_html__('Maintenance','smg-site-suite'),['response'=>503]);
    }
}
