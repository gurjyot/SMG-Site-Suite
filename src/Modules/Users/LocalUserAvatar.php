<?php
namespace SMG\SiteSuite\Modules\Users;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class LocalUserAvatar implements ModuleInterface {
    private const META='_smg_site_suite_avatar_id';

    public function register():void{
        add_action('show_user_profile',[$this,'field']);
        add_action('edit_user_profile',[$this,'field']);
        add_action('personal_options_update',[$this,'save']);
        add_action('edit_user_profile_update',[$this,'save']);
        add_filter('get_avatar_data',[$this,'avatar'],20,2);
        add_action('admin_enqueue_scripts',[$this,'media']);
    }

    public function media(string $hook):void{
        if(in_array($hook,['profile.php','user-edit.php'],true))wp_enqueue_media();
    }

    public function field(\WP_User $user):void{
        if(!current_user_can('edit_user',$user->ID))return;
        $id=(int)get_user_meta($user->ID,self::META,true);
        echo '<h2>'.esc_html__('Local Avatar','smg-site-suite').'</h2><table class="form-table"><tr><th>'.esc_html__('Avatar image','smg-site-suite').'</th><td>';
        if($id>0)echo wp_get_attachment_image($id,'thumbnail',false,['style'=>'display:block;max-width:96px;height:auto;margin-bottom:8px']);
        echo '<input type="number" min="0" name="smg_avatar_id" value="'.esc_attr((string)$id).'" class="small-text"> <span class="description">'.esc_html__('Media attachment ID.','smg-site-suite').'</span>';
        wp_nonce_field('smg_site_suite_avatar_'.$user->ID,'smg_site_suite_avatar_nonce');
        echo '</td></tr></table>';
    }

    public function save(int $userId):void{
        if(!current_user_can('edit_user',$userId))return;
        if(!isset($_POST['smg_site_suite_avatar_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_avatar_nonce'])),'smg_site_suite_avatar_'.$userId))return;
        $id=absint($_POST['smg_avatar_id']??0);
        if($id>0&&!wp_attachment_is_image($id))$id=0;
        if($id>0)update_user_meta($userId,self::META,$id);else delete_user_meta($userId,self::META);
    }

    public function avatar(array $args,$idOrEmail):array{
        $user=null;
        if($idOrEmail instanceof \WP_User)$user=$idOrEmail;
        elseif(is_numeric($idOrEmail))$user=get_user_by('id',(int)$idOrEmail);
        elseif(is_object($idOrEmail)&&isset($idOrEmail->user_id))$user=get_user_by('id',(int)$idOrEmail->user_id);
        elseif(is_string($idOrEmail)&&is_email($idOrEmail))$user=get_user_by('email',$idOrEmail);
        if(!$user)return $args;

        $id=(int)get_user_meta($user->ID,self::META,true);
        $url=$id>0?wp_get_attachment_image_url($id,'thumbnail'):false;
        if($url){$args['url']=$url;$args['found_avatar']=true;}
        return $args;
    }
}
