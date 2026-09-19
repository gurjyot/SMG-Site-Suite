<?php
namespace SMG\SiteSuite\Modules\Admin;

use SMG\WPFoundation\Contracts\ActivatableModuleInterface;
use SMG\WPFoundation\Contracts\ModuleInterface;

final class ProtectedOwner implements ModuleInterface, ActivatableModuleInterface {
    private const OPTION='smg_site_suite_protected_owners';

    public function activate():void{
        if(!current_user_can('manage_options'))return;
        $owners=$this->owners();
        $owners[]=get_current_user_id();
        update_option(self::OPTION,array_values(array_unique(array_filter(array_map('absint',$owners)))),false);
    }

    public function register():void{
        add_filter('map_meta_cap',[$this,'protectCapabilities'],20,4);
        add_filter('user_row_actions',[$this,'rowActions'],20,2);
        add_action('admin_menu',[$this,'adminMenu'],1);
        add_action('admin_init',[$this,'protectSiteSuitePages'],1);
        add_action('admin_post_smg_site_suite_save_protected_owners',[$this,'save']);
        add_filter('plugin_action_links_'.plugin_basename(SMG_SITE_SUITE_FILE),[$this,'pluginActions'],99);
        add_filter('pre_update_option_active_plugins',[$this,'protectPluginActivation'],99,2);
    }

    public static function isProtectedCurrentUser():bool{
        if(!is_user_logged_in())return false;
        $owners=get_option(self::OPTION,[]);
        return is_array($owners)&&in_array(get_current_user_id(),array_map('absint',$owners),true);
    }

    public static function isProtectedUser(int $userId):bool{
        $owners=get_option(self::OPTION,[]);
        return is_array($owners)&&in_array($userId,array_map('absint',$owners),true);
    }

    private function owners():array{
        $owners=get_option(self::OPTION,[]);
        return is_array($owners)?array_values(array_unique(array_filter(array_map('absint',$owners)))):[];
    }

    public function protectCapabilities(array $caps,string $cap,int $userId,array $args):array{
        if(!in_array($cap,['delete_user','remove_user','edit_user','promote_user'],true))return $caps;
        $target=isset($args[0])?absint($args[0]):0;
        if($target<=0||!self::isProtectedUser($target))return $caps;
        if(self::isProtectedCurrentUser())return $caps;
        return ['do_not_allow'];
    }

    public function rowActions(array $actions,\WP_User $user):array{
        if(self::isProtectedUser($user->ID)&&!self::isProtectedCurrentUser()){
            unset($actions['edit'],$actions['delete'],$actions['remove'],$actions['resetpassword'],$actions['view']);
        }
        return $actions;
    }

    public function adminMenu():void{
        if(!self::isProtectedCurrentUser()){
            remove_menu_page('smg-site-suite');
            return;
        }
        add_submenu_page(
            'smg-site-suite',
            __('Protected Owners','smg-site-suite'),
            __('Protected Owners','smg-site-suite'),
            'manage_options',
            'smg-site-suite-protected-owners',
            [$this,'render']
        );
    }

    public function protectSiteSuitePages():void{
        if(self::isProtectedCurrentUser())return;
        $page=isset($_GET['page'])?sanitize_key(wp_unslash($_GET['page'])):'';
        if($page!==''&&str_starts_with($page,'smg-site-suite')){
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    public function pluginActions(array $actions):array{
        if(!self::isProtectedCurrentUser())unset($actions['deactivate']);
        return $actions;
    }

    public function protectPluginActivation($new,$old){
        if(self::isProtectedCurrentUser()||!is_admin()||!is_user_logged_in())return $new;
        $action=isset($_REQUEST['action'])?sanitize_key(wp_unslash($_REQUEST['action'])):'';
        if(!in_array($action,['deactivate','deactivate-selected'],true))return $new;
        $plugin=plugin_basename(SMG_SITE_SUITE_FILE);
        $oldList=is_array($old)?$old:[];$newList=is_array($new)?$new:[];
        if(in_array($plugin,$oldList,true)&&!in_array($plugin,$newList,true))$newList[]=$plugin;
        return array_values(array_unique($newList));
    }

    public function render():void{
        if(!self::isProtectedCurrentUser())wp_die(esc_html__('Only a protected owner can manage protected owners.','smg-site-suite'));

        $owners=$this->owners();
        $admins=get_users(['role'=>'administrator','orderby'=>'user_email','order'=>'ASC']);

        echo '<div class="wrap"><h1>'.esc_html__('Protected Owners','smg-site-suite').'</h1>';
        echo '<p>'.esc_html__('Protected owners bypass Site Suite admin-menu restrictions and cannot be edited, demoted, removed, or deleted by ordinary administrators. Keep at least one protected owner.','smg-site-suite').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_save_protected_owners">';
        wp_nonce_field('smg_site_suite_save_protected_owners');
        echo '<table class="widefat striped" style="max-width:850px"><thead><tr><th>'.esc_html__('Protected','smg-site-suite').'</th><th>'.esc_html__('Administrator','smg-site-suite').'</th><th>'.esc_html__('Email','smg-site-suite').'</th></tr></thead><tbody>';
        foreach($admins as $admin){
            $checked=in_array($admin->ID,$owners,true);
            $disabled=$admin->ID===get_current_user_id();
            echo '<tr><td><input type="checkbox" name="owners[]" value="'.esc_attr((string)$admin->ID).'" '.checked($checked,true,false).' '.disabled($disabled,true,false).'>';
            if($disabled)echo '<input type="hidden" name="owners[]" value="'.esc_attr((string)$admin->ID).'">';
            echo '</td><td>'.esc_html($admin->display_name).'</td><td>'.esc_html($admin->user_email).'</td></tr>';
        }
        echo '</tbody></table>';
        submit_button(__('Save Protected Owners','smg-site-suite'));
        echo '</form></div>';
    }

    public function save():void{
        if(!self::isProtectedCurrentUser())wp_die(esc_html__('Only a protected owner can change this setting.','smg-site-suite'));
        check_admin_referer('smg_site_suite_save_protected_owners');

        $owners=isset($_POST['owners'])&&is_array($_POST['owners'])?array_values(array_unique(array_filter(array_map('absint',wp_unslash($_POST['owners']))))):[];
        if(!in_array(get_current_user_id(),$owners,true))$owners[]=get_current_user_id();

        $valid=[];
        foreach($owners as $id){
            $user=get_userdata($id);
            if($user&&in_array('administrator',(array)$user->roles,true))$valid[]=$id;
        }
        update_option(self::OPTION,array_values(array_unique($valid)),false);
        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-protected-owners','updated'=>'1'],admin_url('admin.php')));
        exit;
    }
}
