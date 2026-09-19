<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class CouponUsageColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_edit-shop_coupon_columns',[$this,'columns'],25);
        add_action('manage_shop_coupon_posts_custom_column',[$this,'value'],25,2);
    }

    public function columns(array $columns):array{
        $columns['smg_coupon_usage']=__('Usage','smg-site-suite');
        $columns['smg_coupon_expiry']=__('Expiry','smg-site-suite');
        return $columns;
    }

    public function value(string $column,int $postId):void{
        $coupon=new \WC_Coupon($postId);
        if($column==='smg_coupon_usage'){
            $used=(int)$coupon->get_usage_count();$limit=(int)$coupon->get_usage_limit();
            echo esc_html($limit>0?$used.' / '.$limit:(string)$used);
        }
        if($column==='smg_coupon_expiry'){
            $expiry=$coupon->get_date_expires();
            echo $expiry?esc_html(wp_date(get_option('date_format'),$expiry->getTimestamp())):'—';
        }
    }
}
