<?php
namespace SMG\SiteSuite\Modules\Admin;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class LoginBranding implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_login_branding';
    public function register():void{add_action('login_enqueue_scripts',[$this,'styles']);add_filter('login_headerurl',static fn()=>home_url('/'));add_filter('login_headertext',static fn()=>get_bloginfo('name'));}
    public function settingsSchema():array{return [
        ['key'=>'logo_id','type'=>'media_select','label'=>__('Login logo','smg-site-suite'),'description'=>__('Choose a logo from the WordPress Media Library.','smg-site-suite')],
        ['key'=>'background','type'=>'text','label'=>__('Background color','smg-site-suite'),'default'=>'#f0f0f1','description'=>__('CSS color value, e.g. #f5f5f5.','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $existing=$this->settings();
        $logoId=absint($input['logo_id']??0);
        if($logoId>0&&!wp_attachment_is_image($logoId))$logoId=0;
        $background=isset($input['background'])?sanitize_hex_color((string)$input['background']):'';
        update_option(self::OPTION,[
            'logo_id'=>$logoId,
            'logo_url'=>(string)($existing['logo_url']??''),
            'background'=>$background?:'#f0f0f1'
        ],false);
    }
    public function styles():void{
        $s=$this->settings();
        $logoId=(int)($s['logo_id']??0);
        $logo=$logoId>0?(wp_get_attachment_image_url($logoId,'full')?:''):(string)($s['logo_url']??'');
        $bg=$s['background']??'#f0f0f1';
        $css='body.login{background:'.esc_attr($bg).';}';
        if($logo!=='')$css.='body.login h1 a{background-image:url("'.esc_url($logo).'");background-size:contain;width:100%;}';
        wp_register_style('smg-site-suite-login-branding',false,[],SMG_SITE_SUITE_VERSION);wp_enqueue_style('smg-site-suite-login-branding');wp_add_inline_style('smg-site-suite-login-branding',$css);
    }
}
