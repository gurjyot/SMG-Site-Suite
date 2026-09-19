<?php
namespace SMG\SiteSuite\Modules\Users;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class LoginLogoutRedirects implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_login_logout_redirects';
    public function register():void{add_filter('login_redirect',[$this,'login'],10,3);add_filter('logout_redirect',[$this,'logout'],10,3);}
    public function settingsSchema():array{return [
        ['key'=>'login_url','type'=>'url','label'=>__('Redirect after login','smg-site-suite'),'default'=>''],
        ['key'=>'logout_url','type'=>'url','label'=>__('Redirect after logout','smg-site-suite'),'default'=>'']
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['login_url'=>esc_url_raw((string)($input['login_url']??'')),'logout_url'=>esc_url_raw((string)($input['logout_url']??''))],false);}
    public function login(string $redirect,string $requested,\WP_User|\WP_Error $user):string{
        if(is_wp_error($user))return $redirect;$url=(string)($this->settings()['login_url']??'');return $url!==''?wp_validate_redirect($url,home_url('/')):$redirect;
    }
    public function logout(string $redirect,string $requested,\WP_User $user):string{
        $url=(string)($this->settings()['logout_url']??'');return $url!==''?wp_validate_redirect($url,home_url('/')):$redirect;
    }
}
