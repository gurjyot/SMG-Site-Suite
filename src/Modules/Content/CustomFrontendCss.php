<?php
namespace SMG\SiteSuite\Modules\Content;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class CustomFrontendCss implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_frontend_css';
    public function register():void{add_action('wp_enqueue_scripts',[$this,'enqueue'],100);}
    public function settingsSchema():array{return [[
        'key'=>'css','type'=>'textarea','label'=>__('Frontend CSS','smg-site-suite'),
        'default'=>'','description'=>__('Loaded only on the public site while this module is active.','smg-site-suite')
    ]];}
    public function settings():array{$v=get_option(self::OPTION,['css'=>'']);return is_array($v)?$v:['css'=>''];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['css'=>$this->sanitize((string)($input['css']??''))],false);}
    public function enqueue():void{
        $css=(string)($this->settings()['css']??'');if(trim($css)==='')return;
        wp_register_style('smg-site-suite-custom-frontend',false,[],SMG_SITE_SUITE_VERSION);
        wp_enqueue_style('smg-site-suite-custom-frontend');
        wp_add_inline_style('smg-site-suite-custom-frontend',$css);
    }
    private function sanitize(string $css):string{
        $css=preg_replace('/<\/?style[^>]*>/i','',$css)??'';
        $css=preg_replace('/expression\s*\(|javascript\s*:/i','',$css)??'';
        return trim($css);
    }
}
