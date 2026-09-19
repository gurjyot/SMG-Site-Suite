<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RedirectManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_redirects';
    private const STATS='smg_site_suite_redirect_stats';

    public function register():void{add_action('template_redirect',[$this,'redirect'],0);}

    public function settingsSchema():array{
        return [['key'=>'rules','type'=>'textarea','label'=>__('Redirect rules','smg-site-suite'),'description'=>__("One per line: /old-path => /new-path | 301\nSupported codes: 301, 302, 307, 308. Only local paths are allowed.",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['rules'=>sanitize_textarea_field((string)($input['rules']??''))],false);}

    public function redirect():void{
        if(is_admin()||wp_doing_ajax()||wp_doing_cron())return;
        $path=(string)wp_parse_url(home_url(add_query_arg([],$_SERVER['REQUEST_URI']??'/')),PHP_URL_PATH);
        foreach($this->rules() as $from=>$rule){
            if(untrailingslashit($path)!==untrailingslashit($from))continue;
            $this->recordHit($from);
            wp_safe_redirect(home_url((string)$rule['to']),(int)$rule['code']);exit;
        }
    }

    private function rules():array{
        $out=[];$raw=(string)($this->settings()['rules']??'');
        foreach(preg_split('/\r\n|\r|\n/',$raw)?:[] as $line){
            if(!str_contains($line,'=>'))continue;
            [$from,$target]=array_map('trim',explode('=>',$line,2));
            $parts=array_map('trim',explode('|',$target,2));
            $to=$parts[0]??'';$code=isset($parts[1])?absint($parts[1]):301;
            if(!in_array($code,[301,302,307,308],true))$code=301;
            if(!str_starts_with($from,'/')||!str_starts_with($to,'/'))continue;
            $out[$from]=['to'=>$to,'code'=>$code];
        }
        return $out;
    }

    private function recordHit(string $from):void{
        $stats=get_option(self::STATS,[]);if(!is_array($stats))$stats=[];
        $row=$stats[$from]??['hits'=>0,'last'=>0];
        $row['hits']=(int)($row['hits']??0)+1;$row['last']=time();
        $stats[$from]=$row;
        if(count($stats)>500)$stats=array_slice($stats,-500,null,true);
        update_option(self::STATS,$stats,false);
    }
}
