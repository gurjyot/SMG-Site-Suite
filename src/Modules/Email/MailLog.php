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
        add_action('admin_post_smg_site_suite_export_mail_log',[$this,'export']);
        add_action('admin_post_smg_site_suite_clear_mail_log',[$this,'clear']);
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
        $status=isset($_GET['status'])?sanitize_key(wp_unslash($_GET['status'])):'';
        $search=isset($_GET['s'])?sanitize_text_field(wp_unslash($_GET['s'])):'';
        if($status!=='')$log=array_values(array_filter($log,static fn(array $row):bool=>($row['status']??'')===$status));
        if($search!=='')$log=array_values(array_filter($log,static function(array $row)use($search):bool{
            return stripos((string)($row['to']??''),$search)!==false||stripos((string)($row['subject']??''),$search)!==false;
        }));

        echo '<div class="wrap"><h1>'.esc_html__('Mail Log','smg-site-suite').'</h1>';
        echo '<form method="get" style="display:flex;gap:8px;align-items:end;margin:12px 0 16px"><input type="hidden" name="page" value="smg-site-suite-mail-log"><label>'.esc_html__('Status','smg-site-suite').'<br><select name="status"><option value="">'.esc_html__('All','smg-site-suite').'</option><option value="sent-attempt" '.selected($status,'sent-attempt',false).'>'.esc_html__('Sent attempt','smg-site-suite').'</option><option value="failed" '.selected($status,'failed',false).'>'.esc_html__('Failed','smg-site-suite').'</option></select></label><label>'.esc_html__('Search','smg-site-suite').'<br><input type="search" name="s" value="'.esc_attr($search).'"></label><button class="button">'.esc_html__('Filter','smg-site-suite').'</button></form>';

        $export=wp_nonce_url(admin_url('admin-post.php?action=smg_site_suite_export_mail_log'),'smg_site_suite_export_mail_log');
        echo '<p><a class="button" href="'.esc_url($export).'">'.esc_html__('Export CSV','smg-site-suite').'</a></p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="margin:0 0 16px"><input type="hidden" name="action" value="smg_site_suite_clear_mail_log">';
        wp_nonce_field('smg_site_suite_clear_mail_log');
        echo '<button class="button">'.esc_html__('Clear Log','smg-site-suite').'</button></form>';

        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Time','smg-site-suite').'</th><th>'.esc_html__('To','smg-site-suite').'</th><th>'.esc_html__('Subject','smg-site-suite').'</th><th>'.esc_html__('Status','smg-site-suite').'</th><th>'.esc_html__('Error','smg-site-suite').'</th></tr></thead><tbody>';
        foreach(array_reverse($log) as $row)echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i',(int)$row['time'])).'</td><td>'.esc_html((string)$row['to']).'</td><td>'.esc_html((string)$row['subject']).'</td><td>'.esc_html((string)$row['status']).'</td><td>'.esc_html((string)($row['error']??'')).'</td></tr>';
        echo '</tbody></table></div>';
    }

    public function export():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_export_mail_log');
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="smg-mail-log-'.gmdate('Y-m-d').'.csv"');
        $out=fopen('php://output','w');
        if($out){
            fputcsv($out,['time','to','subject','status','error']);
            foreach($log as $row)fputcsv($out,[gmdate('c',(int)($row['time']??0)),(string)($row['to']??''),(string)($row['subject']??''),(string)($row['status']??''),(string)($row['error']??'')]);
            fclose($out);
        }
        exit;
    }

    public function clear():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_clear_mail_log');
        delete_option(self::OPTION);
        wp_safe_redirect(add_query_arg('page','smg-site-suite-mail-log',admin_url('admin.php')));
        exit;
    }
}
