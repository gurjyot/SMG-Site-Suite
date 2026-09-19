<?php
namespace SMG\SiteSuite\Modules\Email;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class MailLog implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_mail_log';
    private const SETTINGS='smg_site_suite_mail_log_settings';

    public function register():void{
        add_filter('wp_mail',[$this,'capture'],999);
        add_action('wp_mail_failed',[$this,'failed']);
        add_action('admin_menu',[$this,'menu'],70);
    }

    public function settingsSchema():array{
        return [['key'=>'limit','type'=>'number','label'=>__('Maximum mail log entries','smg-site-suite'),'default'=>200]];
    }
    public function settings():array{$v=get_option(self::SETTINGS,['limit'=>200]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::SETTINGS,['limit'=>max(20,min(2000,absint($input['limit']??200)))],false);}

    public function capture(array $args):array{
        $this->record([
            'time'=>time(),'to'=>is_array($args['to']??null)?implode(', ',$args['to']):(string)($args['to']??''),
            'subject'=>(string)($args['subject']??''),'status'=>'sent-attempt','error'=>''
        ]);
        return $args;
    }

    public function failed(\WP_Error $error):void{
        $this->record(['time'=>time(),'to'=>'','subject'=>'','status'=>'failed','error'=>$error->get_error_message()]);
    }

    private function record(array $row):void{
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];$log[]=$row;
        $limit=(int)($this->settings()['limit']??200);if(count($log)>$limit)$log=array_slice($log,-$limit);
        update_option(self::OPTION,$log,false);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Mail Log','smg-site-suite'),__('Mail Log','smg-site-suite'),'manage_options','smg-site-suite-mail-log',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        echo '<div class="wrap"><h1>'.esc_html__('Mail Log','smg-site-suite').'</h1><table class="widefat striped"><thead><tr><th>'.esc_html__('Time','smg-site-suite').'</th><th>'.esc_html__('To','smg-site-suite').'</th><th>'.esc_html__('Subject','smg-site-suite').'</th><th>'.esc_html__('Status','smg-site-suite').'</th></tr></thead><tbody>';
        foreach(array_reverse($log) as $row)echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i',(int)$row['time'])).'</td><td>'.esc_html((string)$row['to']).'</td><td>'.esc_html((string)$row['subject']).'</td><td>'.esc_html((string)$row['status']).'</td></tr>';
        echo '</tbody></table></div>';
    }
}
