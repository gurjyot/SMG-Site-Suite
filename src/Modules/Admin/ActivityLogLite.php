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
        echo '<div class="wrap"><h1>'.esc_html__('Activity Log','smg-site-suite').'</h1><table class="widefat striped"><thead><tr><th>'.esc_html__('Time','smg-site-suite').'</th><th>'.esc_html__('Type','smg-site-suite').'</th><th>'.esc_html__('User','smg-site-suite').'</th><th>'.esc_html__('Activity','smg-site-suite').'</th></tr></thead><tbody>';
        foreach(array_reverse($log) as $row){
            $user=get_userdata((int)($row['user']??0));
            echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i',(int)$row['time'])).'</td><td>'.esc_html((string)$row['type']).'</td><td>'.esc_html($user?$user->user_login:'—').'</td><td>'.esc_html((string)$row['message']).'</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
