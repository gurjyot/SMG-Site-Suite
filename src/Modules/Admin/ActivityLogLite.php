<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ActivityLogLite implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_activity_log';
    private const SETTINGS='smg_site_suite_activity_log_settings';

    public function register():void{
        add_action('wp_login',fn($login,$user)=>$this->record('login','User logged in',['user'=>$user->ID]),10,2);
        add_action('wp_logout',fn()=> $this->record('logout','User logged out',[]));
        add_action('activated_plugin',fn($plugin)=>$this->record('plugin','Plugin activated',['plugin'=>$plugin]));
        add_action('deactivated_plugin',fn($plugin)=>$this->record('plugin','Plugin deactivated',['plugin'=>$plugin]));
        add_action('switch_theme',fn($name)=>$this->record('theme','Theme switched',['theme'=>$name]));
        add_action('save_post',[$this,'postSaved'],20,3);
        add_action('admin_menu',[$this,'menu'],60);
        add_action('admin_post_smg_site_suite_clear_activity_log',[$this,'clear']);
    }

    public function settingsSchema():array{
        return [['key'=>'limit','type'=>'number','label'=>__('Maximum activity entries','smg-site-suite'),'default'=>500]];
    }
    public function settings():array{$v=get_option(self::SETTINGS,['limit'=>500]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::SETTINGS,['limit'=>max(50,min(5000,absint($input['limit']??500)))],false);}

    public function postSaved(int $postId,\WP_Post $post,bool $update):void{
        if(wp_is_post_revision($postId)||wp_is_post_autosave($postId))return;
        $this->record('content',$update?'Content updated':'Content created',['post'=>$postId,'type'=>$post->post_type]);
    }

    private function record(string $type,string $message,array $context):void{
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        $log[]=['time'=>time(),'type'=>$type,'message'=>$message,'user'=>get_current_user_id(),'context'=>$context];
        $limit=(int)($this->settings()['limit']??500);
        if(count($log)>$limit)$log=array_slice($log,-$limit);
        update_option(self::OPTION,$log,false);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Activity Log','smg-site-suite'),__('Activity Log','smg-site-suite'),'manage_options','smg-site-suite-activity-log',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('manage_options'))return;
        $log=get_option(self::OPTION,[]);if(!is_array($log))$log=[];
        $type=isset($_GET['type'])?sanitize_key(wp_unslash($_GET['type'])):'';
        $userId=isset($_GET['user_id'])?absint($_GET['user_id']):0;
        if($type!=='')$log=array_values(array_filter($log,static fn(array $row):bool=>($row['type']??'')===$type));
        if($userId>0)$log=array_values(array_filter($log,static fn(array $row):bool=>(int)($row['user']??0)===$userId));

        echo '<div class="wrap"><h1>'.esc_html__('Activity Log','smg-site-suite').'</h1>';
        echo '<form method="get" style="display:flex;gap:8px;align-items:end;margin:12px 0 16px"><input type="hidden" name="page" value="smg-site-suite-activity-log"><label>'.esc_html__('Type','smg-site-suite').'<br><select name="type"><option value="">'.esc_html__('All','smg-site-suite').'</option>';
        foreach(['login','logout','plugin','theme','content'] as $option)echo '<option value="'.esc_attr($option).'" '.selected($type,$option,false).'>'.esc_html(ucfirst($option)).'</option>';
        echo '</select></label><label>'.esc_html__('User ID','smg-site-suite').'<br><input type="number" min="1" name="user_id" value="'.esc_attr($userId?:'').'"></label><button class="button">'.esc_html__('Filter','smg-site-suite').'</button></form>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" style="margin:0 0 16px"><input type="hidden" name="action" value="smg_site_suite_clear_activity_log">';
        wp_nonce_field('smg_site_suite_clear_activity_log');
        echo '<button class="button">'.esc_html__('Clear Log','smg-site-suite').'</button></form>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('Time','smg-site-suite').'</th><th>'.esc_html__('Type','smg-site-suite').'</th><th>'.esc_html__('User','smg-site-suite').'</th><th>'.esc_html__('Activity','smg-site-suite').'</th></tr></thead><tbody>';
        foreach(array_reverse($log) as $row){
            $user=get_userdata((int)($row['user']??0));
            echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i',(int)$row['time'])).'</td><td>'.esc_html((string)$row['type']).'</td><td>'.esc_html($user?$user->user_login:'—').'</td><td>'.esc_html((string)$row['message']).'</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function clear():void{
        if(!current_user_can('manage_options'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_clear_activity_log');
        delete_option(self::OPTION);
        wp_safe_redirect(add_query_arg('page','smg-site-suite-activity-log',admin_url('admin.php')));
        exit;
    }
}
