<?php
namespace SMG\SiteSuite\Modules\Utilities;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class NotFoundTracker implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_404_log';
    private const SETTINGS='smg_site_suite_404_settings';

    public function register():void{
        add_action('template_redirect',[$this,'track'],99);
        add_action('admin_menu',[$this,'menu'],40);
        add_action('admin_post_smg_site_suite_404_redirect',[$this,'createRedirect']);
    }

    public function settingsSchema():array{
        return [['key'=>'limit','type'=>'number','label'=>__('Maximum stored 404 entries','smg-site-suite'),'default'=>200]];
    }

    public function settings():array{$v=get_option(self::SETTINGS,['limit'=>200]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::SETTINGS,['limit'=>max(20,min(2000,absint($input['limit']??200)))],false);}

    public function track():void{
        if(!is_404()||is_admin()||wp_doing_ajax())return;
        $url=esc_url_raw(home_url(wp_unslash($_SERVER['REQUEST_URI']??'/')));
        $referer=isset($_SERVER['HTTP_REFERER'])?esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])):'';
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        $key=md5($url);
        $row=$log[$key]??['url'=>$url,'count'=>0,'last'=>0,'referer'=>''];
        $row['count']=(int)$row['count']+1;$row['last']=time();if($referer!=='')$row['referer']=$referer;
        $log[$key]=$row;
        uasort($log,static fn($a,$b)=>(int)$b['last']<=>(int)$a['last']);
        $limit=(int)($this->settings()['limit']??200);
        if(count($log)>$limit)$log=array_slice($log,0,$limit,true);
        update_option(self::OPTION,$log,false);
    }

    public function createRedirect():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        $from=isset($_POST['from'])?sanitize_text_field(wp_unslash($_POST['from'])):'';
        $to=isset($_POST['to'])?sanitize_text_field(wp_unslash($_POST['to'])):'';
        if(!str_starts_with($from,'/')||!str_starts_with($to,'/'))wp_die(esc_html__('Redirect paths must begin with /.','smg-site-suite'));
        check_admin_referer('smg_site_suite_404_redirect_'.md5($from));

        $option=get_option('smg_site_suite_redirects',[]);
        if(!is_array($option))$option=[];
        $rules=(string)($option['rules']??'');
        $line=$from.' => '.$to;
        $lines=array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',$rules)?:[]));
        $replaced=false;
        foreach($lines as $index=>$existing){
            if(str_starts_with($existing,$from.' =>')){$lines[$index]=$line;$replaced=true;break;}
        }
        if(!$replaced)$lines[]=$line;
        update_option('smg_site_suite_redirects',['rules'=>implode("\n",$lines)],false);

        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-404-log','redirect_added'=>'1'],admin_url('admin.php')));
        exit;
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('404 Log','smg-site-suite'),__('404 Log','smg-site-suite'),'manage_options','smg-site-suite-404-log',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        echo '<div class="wrap"><h1>'.esc_html__('404 Log','smg-site-suite').'</h1><table class="widefat striped"><thead><tr><th>'.esc_html__('URL','smg-site-suite').'</th><th>'.esc_html__('Hits','smg-site-suite').'</th><th>'.esc_html__('Last seen','smg-site-suite').'</th><th>'.esc_html__('Referrer','smg-site-suite').'</th><th>'.esc_html__('Create Redirect','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($log as $row){
            $path=(string)wp_parse_url((string)$row['url'],PHP_URL_PATH);
            echo '<tr><td><code>'.esc_html((string)$row['url']).'</code></td><td>'.esc_html((string)$row['count']).'</td><td>'.esc_html(wp_date('Y-m-d H:i',(int)$row['last'])).'</td><td>'.esc_html((string)$row['referer']).'</td><td>';
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="display:flex;gap:6px;min-width:260px"><input type="hidden" name="action" value="smg_site_suite_404_redirect"><input type="hidden" name="from" value="'.esc_attr($path).'">';
            wp_nonce_field('smg_site_suite_404_redirect_'.md5($path));
            echo '<input type="text" name="to" placeholder="/new-path" required style="width:150px"><button class="button button-small">'.esc_html__('Add','smg-site-suite').'</button></form></td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
