<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class MyAccountRedirects implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_account_redirects';

    public function register():void{
        add_filter('woocommerce_login_redirect',[$this,'login'],20,2);
        add_filter('woocommerce_logout_default_redirect_url',[$this,'logout'],20);
        add_filter('woocommerce_registration_redirect',[$this,'registerRedirect'],20);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'login','type'=>'url','label'=>__('After My Account login','smg-site-suite'),'default'=>''],
            ['key'=>'register','type'=>'url','label'=>__('After registration','smg-site-suite'),'default'=>''],
            ['key'=>'logout','type'=>'url','label'=>__('After WooCommerce logout','smg-site-suite'),'default'=>''],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'login'=>esc_url_raw((string)($input['login']??'')),
            'register'=>esc_url_raw((string)($input['register']??'')),
            'logout'=>esc_url_raw((string)($input['logout']??'')),
        ],false);
    }

    public function login(string $redirect,$user):string{return $this->target('login',$redirect);}
    public function registerRedirect(string $redirect):string{return $this->target('register',$redirect);}
    public function logout(string $redirect):string{return $this->target('logout',$redirect);}

    private function target(string $key,string $fallback):string{
        $url=(string)($this->settings()[$key]??'');
        return $url!==''?wp_validate_redirect($url,$fallback):$fallback;
    }
}
