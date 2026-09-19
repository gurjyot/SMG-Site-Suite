<?php
namespace SMG\SiteSuite\Modules\Users;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class RegistrationDateColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_users_columns',[$this,'column']);
        add_filter('manage_users_custom_column',[$this,'value'],10,3);
        add_filter('manage_users_sortable_columns',[$this,'sortable']);
    }
    public function column(array $columns):array{$columns['smg_registered']=__('Registered','smg-site-suite');return $columns;}
    public function value(string $value,string $column,int $userId):string{
        if($column!=='smg_registered')return $value;
        $user=get_userdata($userId);
        return $user?wp_date(get_option('date_format'),strtotime($user->user_registered)):'—';
    }
    public function sortable(array $columns):array{$columns['smg_registered']='registered';return $columns;}
}
