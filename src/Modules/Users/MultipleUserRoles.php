<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\ModuleInterface;
use SMG\SiteSuite\Modules\Admin\ProtectedOwner;

final class MultipleUserRoles implements ModuleInterface {
    public function register():void{
        add_action('show_user_profile',[$this,'fields']);
        add_action('edit_user_profile',[$this,'fields']);
        add_action('personal_options_update',[$this,'save']);
        add_action('edit_user_profile_update',[$this,'save']);
    }

    public function fields(\WP_User $user):void{
        if(!current_user_can('promote_users'))return;
        if(class_exists(ProtectedOwner::class)&&ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedUser($user->ID)&&!ProtectedOwner::isProtectedCurrentUser())return;

        global $wp_roles;
        $roles=(array)$user->roles;

        echo '<h2>'.esc_html__('Additional Roles','smg-site-suite').'</h2>';
        echo '<table class="form-table"><tr><th>'.esc_html__('WordPress roles','smg-site-suite').'</th><td>';
        wp_nonce_field('smg_site_suite_multiple_roles_'.$user->ID,'smg_site_suite_multiple_roles_nonce');

        foreach(($wp_roles?->roles??[]) as $slug=>$definition){
            echo '<label style="display:block;margin-bottom:6px"><input type="checkbox" name="smg_site_suite_roles[]" value="'.esc_attr((string)$slug).'" '.checked(in_array($slug,$roles,true),true,false).'> '.esc_html((string)($definition['name']??$slug)).'</label>';
        }

        echo '<p class="description">'.esc_html__('A user may have more than one role. At least one role must remain selected.','smg-site-suite').'</p></td></tr></table>';
    }

    public function save(int $userId):void{
        if(!current_user_can('promote_users')||!current_user_can('edit_user',$userId))return;
        if(!isset($_POST['smg_site_suite_multiple_roles_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_multiple_roles_nonce'])),'smg_site_suite_multiple_roles_'.$userId))return;
        if(class_exists(ProtectedOwner::class)&&ProtectedOwner::isEnabled()&&ProtectedOwner::isProtectedUser($userId)&&!ProtectedOwner::isProtectedCurrentUser())return;

        $requested=isset($_POST['smg_site_suite_roles'])&&is_array($_POST['smg_site_suite_roles'])?array_values(array_unique(array_map('sanitize_key',wp_unslash($_POST['smg_site_suite_roles'])))):[];
        if($requested===[])return;

        global $wp_roles;
        $valid=array_values(array_intersect($requested,array_keys($wp_roles?->roles??[])));
        if($valid===[])return;

        $user=new \WP_User($userId);
        foreach((array)$user->roles as $role)$user->remove_role($role);
        foreach($valid as $role)$user->add_role($role);
    }
}
