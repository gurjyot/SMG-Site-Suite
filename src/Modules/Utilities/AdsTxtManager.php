<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class AdsTxtManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_ads_txt';

    public function register():void{
        add_action('template_redirect',[$this,'serve'],0);
    }

    public function settingsSchema():array{
        return [['key'=>'content','type'=>'textarea','label'=>__('ads.txt content','smg-site-suite'),'description'=>__('Served virtually at /ads.txt.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['content'=>sanitize_textarea_field((string)($input['content']??''))],false);}

    public function serve():void{
        $path=(string)wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);
        if($path!=='/ads.txt')return;
        $content=trim((string)($this->settings()['content']??''));
        if($content==='')return;
        status_header(200);
        nocache_headers();
        header('Content-Type: text/plain; charset=utf-8');
        echo $content."\n";
        exit;
    }
}
