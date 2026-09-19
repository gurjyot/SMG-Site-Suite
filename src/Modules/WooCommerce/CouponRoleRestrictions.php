<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CouponRoleRestrictions implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_coupon_role_restrictions';
    private const META='_smg_site_suite_coupon_roles';

    public function register():void{
        add_action('add_meta_boxes_shop_coupon',[$this,'metabox']);
        add_action('save_post_shop_coupon',[$this,'saveCoupon']);
        add_filter('woocommerce_coupon_is_valid',[$this,'validate'],20,2);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'logic','type'=>'select','label'=>__('Multiple role logic','smg-site-suite'),'default'=>'any','options'=>[
                'any'=>__('User has any selected role','smg-site-suite'),
                'all'=>__('User has all selected roles','smg-site-suite'),
            ]],
            ['key'=>'guests','type'=>'select','label'=>__('Guest behavior','smg-site-suite'),'default'=>'deny','options'=>[
                'deny'=>__('Deny role-restricted coupons','smg-site-suite'),
                'customer'=>__('Treat guests as Customer role','smg-site-suite'),
            ]],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['logic'=>'any','guests'=>'deny']);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $logic=(string)($input['logic']??'any');if(!in_array($logic,['any','all'],true))$logic='any';
        $guests=(string)($input['guests']??'deny');if(!in_array($guests,['deny','customer'],true))$guests='deny';
        update_option(self::OPTION,['logic'=>$logic,'guests'=>$guests],false);
    }

    public function metabox():void{
        add_meta_box('smg-site-suite-coupon-roles',__('Allowed User Roles','smg-site-suite'),[$this,'render'],'shop_coupon','side','default');
    }

    public function render(\WP_Post $post):void{
        wp_nonce_field('smg_site_suite_coupon_roles','smg_site_suite_coupon_roles_nonce');
        $selected=(array)get_post_meta($post->ID,self::META,true);
        global $wp_roles;
        foreach(($wp_roles?->roles??[]) as $slug=>$role){
            echo '<label style="display:block;margin-bottom:5px"><input type="checkbox" name="smg_coupon_roles[]" value="'.esc_attr((string)$slug).'" '.checked(in_array($slug,$selected,true),true,false).'> '.esc_html((string)($role['name']??$slug)).'</label>';
        }
        echo '<p class="description">'.esc_html__('Leave all unchecked for no role restriction.','smg-site-suite').'</p>';
    }

    public function saveCoupon(int $postId):void{
        if(!isset($_POST['smg_site_suite_coupon_roles_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_coupon_roles_nonce'])),'smg_site_suite_coupon_roles'))return;
        if(!current_user_can('edit_post',$postId))return;
        $roles=isset($_POST['smg_coupon_roles'])&&is_array($_POST['smg_coupon_roles'])?array_values(array_unique(array_map('sanitize_key',wp_unslash($_POST['smg_coupon_roles'])))):[];
        update_post_meta($postId,self::META,$roles);
    }

    public function validate(bool $valid,\WC_Coupon $coupon):bool{
        if(!$valid)return false;
        $roles=(array)get_post_meta($coupon->get_id(),self::META,true);
        if($roles===[])return true;

        $settings=$this->settings();
        if(!is_user_logged_in()){
            if(($settings['guests']??'deny')==='customer')return in_array('customer',$roles,true);
            return false;
        }

        $user=wp_get_current_user();
        $userRoles=(array)$user->roles;
        if(($settings['logic']??'any')==='all')return array_diff($roles,$userRoles)===[];
        return array_intersect($roles,$userRoles)!==[];
    }
}
