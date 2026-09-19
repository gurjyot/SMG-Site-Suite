<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class TemporaryLogin implements ModuleInterface {
    private const META='_smg_site_suite_temp_login';
    private const ACTION='smg_temp_login';

    public function register():void{
        add_action('admin_menu',[$this,'menu'],50);
        add_action('admin_post_smg_site_suite_create_temp_login',[$this,'create']);
        add_action('admin_post_smg_site_suite_revoke_temp_login',[$this,'revoke']);
        add_action('init',[$this,'consume'],1);
        add_action('smg_site_suite_temp_login_cleanup',[$this,'cleanup']);
    }

    public function menu():void{
        add_submenu_page('smg-site-suite',__('Temporary Login','smg-site-suite'),__('Temporary Login','smg-site-suite'),'create_users','smg-site-suite-temp-login',[$this,'render']);
    }

    public function render():void{
        if(!current_user_can('create_users'))return;
        echo '<div class="wrap"><h1>'.esc_html__('Temporary Login','smg-site-suite').'</h1><p>'.esc_html__('Create a temporary administrator login link with an expiry. The link can be used once.','smg-site-suite').'</p>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="smg_site_suite_create_temp_login">';
        wp_nonce_field('smg_site_suite_create_temp_login');
        echo '<table class="form-table"><tr><th><label>'.esc_html__('Email','smg-site-suite').'</label></th><td><input type="email" name="email" required class="regular-text"></td></tr>';
        echo '<tr><th><label>'.esc_html__('Expires in','smg-site-suite').'</label></th><td><select name="hours"><option value="1">1 hour</option><option value="6">6 hours</option><option value="24" selected>24 hours</option><option value="72">72 hours</option></select></td></tr></table>';
        submit_button(__('Create Temporary Login','smg-site-suite'));
        echo '</form>';

        $users=get_users(['meta_key'=>self::META,'number'=>100,'orderby'=>'registered','order'=>'DESC']);
        if($users!==[]){
            echo '<h2>'.esc_html__('Active / Recent Temporary Access','smg-site-suite').'</h2><table class="widefat striped" style="max-width:1000px"><thead><tr><th>'.esc_html__('User','smg-site-suite').'</th><th>'.esc_html__('Email','smg-site-suite').'</th><th>'.esc_html__('Expires','smg-site-suite').'</th><th>'.esc_html__('Type','smg-site-suite').'</th><th>'.esc_html__('Action','smg-site-suite').'</th></tr></thead><tbody>';
            foreach($users as $listedUser){
                $data=get_user_meta($listedUser->ID,self::META,true);if(!is_array($data))continue;
                $expires=(int)($data['expires']??0);
                $type=!empty($data['temporary_user'])?__('Temporary account','smg-site-suite'):__('Existing account link','smg-site-suite');
                $url=wp_nonce_url(admin_url('admin-post.php?action=smg_site_suite_revoke_temp_login&user_id='.$listedUser->ID),'smg_site_suite_revoke_temp_login_'.$listedUser->ID);
                echo '<tr><td>'.esc_html($listedUser->display_name).'</td><td>'.esc_html($listedUser->user_email).'</td><td>'.esc_html($expires>0?wp_date('Y-m-d H:i',$expires):'—').'</td><td>'.esc_html($type).'</td><td><a class="button button-small" href="'.esc_url($url).'">'.esc_html__('Revoke','smg-site-suite').'</a></td></tr>';
            }
            echo '</tbody></table>';
        }

        if(isset($_GET['token'])&&is_string($_GET['token'])){
            $url=add_query_arg([self::ACTION=>'1','token'=>sanitize_text_field(wp_unslash($_GET['token']))],home_url('/'));
            echo '<div class="notice notice-success inline"><p><strong>'.esc_html__('Temporary login link:','smg-site-suite').'</strong></p><input class="large-text" readonly value="'.esc_attr($url).'"></div>';
        }
        echo '</div>';
    }

    public function create():void{
        if(!current_user_can('create_users'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        check_admin_referer('smg_site_suite_create_temp_login');

        $email=sanitize_email((string)($_POST['email']??''));
        $hours=max(1,min(168,absint($_POST['hours']??24)));
        if($email==='')wp_die(esc_html__('Valid email required.','smg-site-suite'));

        $user=get_user_by('email',$email);
        $created=false;
        if(!$user){
            $username=sanitize_user(strstr($email,'@',true)?:'temp',true);
            $base=$username!==''?$username:'temp';$candidate=$base;$n=1;
            while(username_exists($candidate)){$candidate=$base.$n;$n++;}
            $id=wp_create_user($candidate,wp_generate_password(32,true,true),$email);
            if(is_wp_error($id))wp_die(esc_html($id->get_error_message()));
            $user=new \WP_User($id);$user->set_role('administrator');$created=true;
        }

        $token=wp_generate_password(40,false,false);
        update_user_meta($user->ID,self::META,[
            'hash'=>wp_hash_password($token),
            'expires'=>time()+($hours*HOUR_IN_SECONDS),
            'created_by'=>get_current_user_id(),
            'temporary_user'=>$created,
        ]);
        if($created&&!wp_next_scheduled('smg_site_suite_temp_login_cleanup',[$user->ID]))wp_schedule_single_event(time()+($hours*HOUR_IN_SECONDS)+60,'smg_site_suite_temp_login_cleanup',[$user->ID]);

        wp_safe_redirect(add_query_arg(['page'=>'smg-site-suite-temp-login','token'=>rawurlencode($token)],admin_url('admin.php')));
        exit;
    }

    public function revoke():void{
        if(!current_user_can('create_users'))wp_die(esc_html__('Insufficient permissions.','smg-site-suite'));
        $userId=isset($_GET['user_id'])?absint($_GET['user_id']):0;
        if($userId<=0)wp_die(esc_html__('Invalid user.','smg-site-suite'));
        check_admin_referer('smg_site_suite_revoke_temp_login_'.$userId);

        $data=get_user_meta($userId,self::META,true);
        if(is_array($data)&&!empty($data['temporary_user'])){
            require_once ABSPATH.'wp-admin/includes/user.php';
            wp_delete_user($userId);
        }else{
            delete_user_meta($userId,self::META);
        }

        wp_safe_redirect(add_query_arg('page','smg-site-suite-temp-login',admin_url('admin.php')));
        exit;
    }

    public function cleanup(int $userId):void{
        $data=get_user_meta($userId,self::META,true);
        if(!is_array($data)||empty($data['temporary_user']))return;
        if((int)($data['expires']??0)>time())return;
        require_once ABSPATH.'wp-admin/includes/user.php';
        wp_delete_user($userId);
    }

    public function consume():void{
        if(!isset($_GET[self::ACTION],$_GET['token'])||'1'!==sanitize_text_field(wp_unslash($_GET[self::ACTION])))return;
        $token=sanitize_text_field(wp_unslash($_GET['token']));

        $users=get_users(['meta_key'=>self::META,'number'=>100]);
        foreach($users as $user){
            $data=get_user_meta($user->ID,self::META,true);
            if(!is_array($data)||(int)($data['expires']??0)<time())continue;
            if(!wp_check_password($token,(string)($data['hash']??'')))continue;

            if(!empty($data['temporary_user'])){
                $data['hash']='';
                update_user_meta($user->ID,self::META,$data);
            }else{
                delete_user_meta($user->ID,self::META);
            }
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID,false,is_ssl());
            wp_safe_redirect(admin_url());
            exit;
        }

        wp_die(esc_html__('This temporary login link is invalid or expired.','smg-site-suite'),'', ['response'=>403]);
    }
}
