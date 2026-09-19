<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\SiteSuite\Modules\Admin\ProtectedOwner;
use SMG\WPFoundation\Contracts\ModuleInterface;

final class LoginAsUser implements ModuleInterface {
    private const COOKIE='smg_site_suite_return_token';
    private const TTL=3600;

    public function register():void{
        add_filter('user_row_actions',[$this,'rowAction'],30,2);
        add_action('admin_post_smg_site_suite_login_as_user',[$this,'loginAs']);
        add_action('admin_post_smg_site_suite_return_owner',[$this,'returnOwner']);
        add_action('admin_bar_menu',[$this,'returnLink'],9999);
    }

    public function rowAction(array $actions,\WP_User $user):array{
        if(!ProtectedOwner::isEnabled()||!ProtectedOwner::isProtectedCurrentUser())return $actions;
        if($user->ID===get_current_user_id()||ProtectedOwner::isProtectedUser($user->ID))return $actions;

        $url=wp_nonce_url(
            admin_url('admin-post.php?action=smg_site_suite_login_as_user&user_id='.$user->ID),
            'smg_site_suite_login_as_user_'.$user->ID
        );
        $actions['smg_login_as']='<a href="'.esc_url($url).'">'.esc_html__('Login as user','smg-site-suite').'</a>';
        return $actions;
    }

    public function loginAs():void{
        if(!ProtectedOwner::isEnabled()||!ProtectedOwner::isProtectedCurrentUser())wp_die(esc_html__('Only a protected owner can use this feature.','smg-site-suite'));
        $targetId=isset($_GET['user_id'])?absint($_GET['user_id']):0;
        if($targetId<=0||$targetId===get_current_user_id()||ProtectedOwner::isProtectedUser($targetId))wp_die(esc_html__('Invalid target user.','smg-site-suite'));
        check_admin_referer('smg_site_suite_login_as_user_'.$targetId);

        $target=get_userdata($targetId);if(!$target)wp_die(esc_html__('User not found.','smg-site-suite'));
        $ownerId=get_current_user_id();
        $token=wp_generate_password(48,false,false);
        $key='smg_return_'.hash('sha256',$token);
        set_transient($key,['owner'=>$ownerId,'target'=>$targetId],self::TTL);

        setcookie(self::COOKIE,$token,[
            'expires'=>time()+self::TTL,
            'path'=>COOKIEPATH?:'/',
            'domain'=>COOKIE_DOMAIN,
            'secure'=>is_ssl(),
            'httponly'=>true,
            'samesite'=>'Lax',
        ]);

        wp_set_current_user($targetId);
        wp_set_auth_cookie($targetId,false,is_ssl());
        do_action('wp_login',$target->user_login,$target);
        wp_safe_redirect(admin_url());
        exit;
    }

    public function returnLink(\WP_Admin_Bar $bar):void{
        $data=$this->returnData();
        if(!$data||get_current_user_id()!==(int)$data['target'])return;
        $url=wp_nonce_url(admin_url('admin-post.php?action=smg_site_suite_return_owner'),'smg_site_suite_return_owner');
        $bar->add_node(['id'=>'smg-return-owner','title'=>__('Return to Protected Owner','smg-site-suite'),'href'=>$url]);
    }

    public function returnOwner():void{
        check_admin_referer('smg_site_suite_return_owner');
        $data=$this->returnData();
        if(!$data||get_current_user_id()!==(int)$data['target'])wp_die(esc_html__('Return session is invalid or expired.','smg-site-suite'));

        $ownerId=(int)$data['owner'];
        if(!ProtectedOwner::isProtectedUser($ownerId))wp_die(esc_html__('Protected owner is no longer valid.','smg-site-suite'));

        $this->clearReturn();
        $owner=get_userdata($ownerId);if(!$owner)wp_die(esc_html__('Protected owner account not found.','smg-site-suite'));
        wp_set_current_user($ownerId);
        wp_set_auth_cookie($ownerId,false,is_ssl());
        do_action('wp_login',$owner->user_login,$owner);
        wp_safe_redirect(admin_url('users.php'));
        exit;
    }

    private function returnData():?array{
        $token=isset($_COOKIE[self::COOKIE])?sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE])):'';
        if($token==='')return null;
        $data=get_transient('smg_return_'.hash('sha256',$token));
        return is_array($data)?$data:null;
    }

    private function clearReturn():void{
        $token=isset($_COOKIE[self::COOKIE])?sanitize_text_field(wp_unslash($_COOKIE[self::COOKIE])):'';
        if($token!=='')delete_transient('smg_return_'.hash('sha256',$token));
        setcookie(self::COOKIE,'',['expires'=>time()-3600,'path'=>COOKIEPATH?:'/','domain'=>COOKIE_DOMAIN,'secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']);
        unset($_COOKIE[self::COOKIE]);
    }
}
