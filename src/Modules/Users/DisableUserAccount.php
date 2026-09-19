<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DisableUserAccount implements ModuleInterface {
    private const META='_smg_site_suite_account_disabled';

    public function register():void{
        add_filter('authenticate',[$this,'block'],50,3);
        add_filter('manage_users_columns',[$this,'column']);
        add_filter('manage_users_custom_column',[$this,'value'],10,3);
        add_action('show_user_profile',[$this,'field']);
        add_action('edit_user_profile',[$this,'field']);
        add_action('personal_options_update',[$this,'save']);
        add_action('edit_user_profile_update',[$this,'save']);
    }

    public function block($user,string $username,string $password){
        if($user instanceof \WP_User && get_user_meta($user->ID,self::META,true)){
            return new \WP_Error('smg_account_disabled',__('This account has been disabled.','smg-site-suite'));
        }
        return $user;
    }

    public function column(array $columns):array{$columns['smg_disabled']=__('Disabled','smg-site-suite');return $columns;}
    public function value(string $value,string $column,int $userId):string{return $column==='smg_disabled'?(get_user_meta($userId,self::META,true)?__('Yes','smg-site-suite'):__('No','smg-site-suite')):$value;}

    public function field(\WP_User $user):void{
        if(!current_user_can('edit_users'))return;
        if(class_exists('SMG\\SiteSuite\\Modules\\Admin\\ProtectedOwner') && \SMG\SiteSuite\Modules\Admin\ProtectedOwner::isProtectedUser($user->ID))return;
        wp_nonce_field('smg_site_suite_disable_user_'.$user->ID,'smg_site_suite_disable_user_nonce');
        echo '<h2>'.esc_html__('Account Access','smg-site-suite').'</h2><table class="form-table"><tr><th>'.esc_html__('Disable account','smg-site-suite').'</th><td><label><input type="checkbox" name="smg_account_disabled" value="1" '.checked((bool)get_user_meta($user->ID,self::META,true),true,false).'> '.esc_html__('Prevent this user from logging in without deleting the account.','smg-site-suite').'</label></td></tr></table>';
    }

    public function save(int $userId):void{
        if(
            !isset($_POST['smg_site_suite_disable_user_nonce'])
            || !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['smg_site_suite_disable_user_nonce'])),
                'smg_site_suite_disable_user_'.$userId
            )
        )return;
        if(!current_user_can('edit_user',$userId))return;
        if(class_exists('SMG\\SiteSuite\\Modules\\Admin\\ProtectedOwner') && \SMG\SiteSuite\Modules\Admin\ProtectedOwner::isProtectedUser($userId))return;
        if(!empty($_POST['smg_account_disabled']))update_user_meta($userId,self::META,1);else delete_user_meta($userId,self::META);
    }
}
