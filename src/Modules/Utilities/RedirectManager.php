<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RedirectManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_redirects';
    private const STATS='smg_site_suite_redirect_stats';

    public function register():void{
        add_action('template_redirect',[$this,'redirect'],0);
        add_action('admin_menu',[$this,'menu'],45);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Redirect Stats','smg-site-suite'),__('Redirect Stats','smg-site-suite'),'manage_options','smg-site-suite-redirect-stats',[$this,'renderStats']);
    }

    public function settingsSchema():array{
        return [['key'=>'rules','type'=>'textarea','label'=>__('Redirect rules','smg-site-suite'),'description'=>__("One per line: /old-path => /new-path | 301\nSupported codes: 301, 302, 307, 308. Only local paths are allowed.",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['rules'=>sanitize_textarea_field((string)($input['rules']??''))],false);}

    public function redirect():void{
        if(is_admin()||wp_doing_ajax()||wp_doing_cron())return;
        $requestUri=isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '/';
        $path=(string)wp_parse_url(home_url(add_query_arg([],$requestUri)),PHP_URL_PATH);
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

    public function renderStats():void{
        if(!current_user_can('manage_options'))return;
        $stats=get_option(self::STATS,[]);if(!is_array($stats))$stats=[];
        $rules=$this->rules();
        echo '<div class="wrap"><h1>'.esc_html__('Redirect Statistics','smg-site-suite').'</h1>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('From','smg-site-suite').'</th><th>'.esc_html__('To','smg-site-suite').'</th><th>'.esc_html__('Code','smg-site-suite').'</th><th>'.esc_html__('Hits','smg-site-suite').'</th><th>'.esc_html__('Last Used','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($rules as $from=>$rule){
            $row=$stats[$from]??['hits'=>0,'last'=>0];
            echo '<tr><td><code>'.esc_html($from).'</code></td><td><code>'.esc_html((string)$rule['to']).'</code></td><td>'.esc_html((string)$rule['code']).'</td><td>'.esc_html((string)($row['hits']??0)).'</td><td>'.esc_html(!empty($row['last'])?wp_date('Y-m-d H:i',(int)$row['last']):'—').'</td></tr>';
        }
        echo '</tbody></table></div>';
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
