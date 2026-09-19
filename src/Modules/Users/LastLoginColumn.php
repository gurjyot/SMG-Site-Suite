<?php
namespace SMG\SiteSuite\Modules\Users;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class LastLoginColumn implements ModuleInterface {
    private const META='_smg_site_suite_last_login';
    public function register():void{
        add_action('wp_login',[$this,'record'],10,2);
        add_filter('manage_users_columns',[$this,'column']);
        add_filter('manage_users_custom_column',[$this,'value'],10,3);
    }
    public function record(string $login,\WP_User $user):void{update_user_meta($user->ID,self::META,time());}
    public function column(array $columns):array{$columns['smg_last_login']=__('Last Login','smg-site-suite');return $columns;}
    public function value(string $value,string $column,int $userId):string{
        if($column!=='smg_last_login')return $value;
        $ts=(int)get_user_meta($userId,self::META,true);
        return $ts>0?sprintf(__('%s ago','smg-site-suite'),human_time_diff($ts,current_time('timestamp'))):__('Never','smg-site-suite');
    }
}
