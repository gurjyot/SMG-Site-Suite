<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RedirectManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_redirects';

    public function register():void{add_action('template_redirect',[$this,'redirect'],0);}

    public function settingsSchema():array{
        return [['key'=>'rules','type'=>'textarea','label'=>__('Redirect rules','smg-site-suite'),'description'=>__("One per line: /old-path => /new-path\nOnly local paths are allowed.",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['rules'=>sanitize_textarea_field((string)($input['rules']??''))],false);}

    public function redirect():void{
        if(is_admin()||wp_doing_ajax()||wp_doing_cron())return;
        $path=(string)wp_parse_url(home_url(add_query_arg([],$_SERVER['REQUEST_URI']??'/')),PHP_URL_PATH);
        foreach($this->rules() as $from=>$to){
            if(untrailingslashit($path)!==untrailingslashit($from))continue;
            wp_safe_redirect(home_url($to),301);exit;
        }
    }

    private function rules():array{
        $out=[];$raw=(string)($this->settings()['rules']??'');
        foreach(preg_split('/\r\n|\r|\n/',$raw)?:[] as $line){
            if(!str_contains($line,'=>'))continue;
            [$from,$to]=array_map('trim',explode('=>',$line,2));
            if(!str_starts_with($from,'/')||!str_starts_with($to,'/'))continue;
            $out[$from]=$to;
        }
        return $out;
    }
}
