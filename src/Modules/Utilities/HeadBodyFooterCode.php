<?php
namespace SMG\SiteSuite\Modules\Utilities;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class HeadBodyFooterCode implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_injected_code';
    public function register():void{
        add_action('wp_head',fn()=>$this->output('head'),99);
        add_action('wp_body_open',fn()=>$this->output('body'),1);
        add_action('wp_footer',fn()=>$this->output('footer'),99);
    }
    public function settingsSchema():array{return [
        ['key'=>'head','type'=>'textarea','label'=>__('Head code','smg-site-suite'),'default'=>'','description'=>__('Trusted HTML/JS inserted before </head>.','smg-site-suite')],
        ['key'=>'body','type'=>'textarea','label'=>__('Body-open code','smg-site-suite'),'default'=>'','description'=>__('Trusted HTML/JS inserted after <body> where the theme supports wp_body_open.','smg-site-suite')],
        ['key'=>'footer','type'=>'textarea','label'=>__('Footer code','smg-site-suite'),'default'=>'','description'=>__('Trusted HTML/JS inserted before </body>.','smg-site-suite')],
    ];}
    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['head'=>(string)($input['head']??''),'body'=>(string)($input['body']??''),'footer'=>(string)($input['footer']??'')],false);}
    private function output(string $location):void{
        if(is_admin()||is_feed()||is_robots()||is_trackback())return;
        $code=(string)($this->settings()[$location]??'');
        if($code!=='')echo $code."\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted administrator code injection module.
    }
}
