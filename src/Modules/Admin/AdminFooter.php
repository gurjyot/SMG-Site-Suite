<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class AdminFooter implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_admin_footer';
    public function register():void{add_filter('admin_footer_text',[$this,'footer']);add_filter('update_footer',[$this,'version'],99);}
    public function settingsSchema():array{return [
        ['key'=>'text','type'=>'text','label'=>__('Footer text','smg-site-suite'),'default'=>''],
        ['key'=>'hide_version','type'=>'checkbox','label'=>__('Hide WordPress version in footer','smg-site-suite'),'default'=>false]
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['text'=>sanitize_text_field((string)($input['text']??'')),'hide_version'=>!empty($input['hide_version'])],false);}
    public function footer(string $text):string{$s=$this->settings();return ($s['text']??'')!==''?esc_html((string)$s['text']):$text;}
    public function version(string $text):string{$s=$this->settings();return !empty($s['hide_version'])?'':$text;}
}
