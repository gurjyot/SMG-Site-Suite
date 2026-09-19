<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CouponMaximumDiscount implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_coupon_max_discount';
    private const META='_smg_site_suite_coupon_max_discount';
    private array $used=[];

    public function register():void{
        add_action('add_meta_boxes_shop_coupon',[$this,'metabox']);
        add_action('save_post_shop_coupon',[$this,'saveCoupon']);
        add_action('woocommerce_before_calculate_totals',[$this,'reset'],1);
        add_filter('woocommerce_coupon_get_discount_amount',[$this,'cap'],20,5);
    }

    public function settingsSchema():array{
        return [['key'=>'default','type'=>'number','label'=>__('Default maximum discount for new coupons','smg-site-suite'),'default'=>0,'description'=>__('0 means no default cap.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,['default'=>0]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['default'=>max(0,(float)($input['default']??0))],false);}

    public function metabox():void{
        add_meta_box('smg-site-suite-coupon-max',__('Maximum Discount','smg-site-suite'),[$this,'render'],'shop_coupon','side','default');
    }

    public function render(\WP_Post $post):void{
        wp_nonce_field('smg_site_suite_coupon_max','smg_site_suite_coupon_max_nonce');
        $stored=get_post_meta($post->ID,self::META,true);
        $value=$stored!==''?$stored:(string)($this->settings()['default']??0);
        echo '<p><label for="smg_coupon_max">'.esc_html__('Maximum discount amount','smg-site-suite').'</label></p>';
        echo '<input type="number" min="0" step="0.01" class="widefat" id="smg_coupon_max" name="smg_coupon_max" value="'.esc_attr((string)$value).'">';
        echo '<p class="description">'.esc_html__('Leave 0 for no cap.','smg-site-suite').'</p>';
    }

    public function saveCoupon(int $postId):void{
        if(!isset($_POST['smg_site_suite_coupon_max_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_site_suite_coupon_max_nonce'])),'smg_site_suite_coupon_max'))return;
        if(!current_user_can('edit_post',$postId))return;
        $value=max(0,(float)($_POST['smg_coupon_max']??0));
        update_post_meta($postId,self::META,$value);
    }

    public function reset():void{$this->used=[];}

    public function cap(float $discount,float $discountingAmount,$cartItem,bool $single,\WC_Coupon $coupon):float{
        $max=(float)get_post_meta($coupon->get_id(),self::META,true);
        if($max<=0)return $discount;

        $id=$coupon->get_id();
        $used=(float)($this->used[$id]??0);
        $remaining=max(0,$max-$used);
        $allowed=min($discount,$remaining);
        $this->used[$id]=$used+$allowed;
        return $allowed;
    }
}
