<?php
namespace SMG\SiteSuite\Modules\Email;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class EmailSenderIdentity implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_email_sender';
    public function register():void{add_filter('wp_mail_from',[$this,'from']);add_filter('wp_mail_from_name',[$this,'name']);}
    public function settingsSchema():array{return [
        ['key'=>'email','type'=>'email','label'=>__('From email','smg-site-suite'),'default'=>''],
        ['key'=>'name','type'=>'text','label'=>__('From name','smg-site-suite'),'default'=>'']
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $email=sanitize_email((string)($input['email']??''));
        $name=sanitize_text_field((string)($input['name']??''));
        update_option(self::OPTION,['email'=>$email,'name'=>$name],false);
    }
    public function from(string $email):string{$s=$this->settings();return !empty($s['email'])?(string)$s['email']:$email;}
    public function name(string $name):string{$s=$this->settings();return !empty($s['name'])?(string)$s['name']:$name;}
}
