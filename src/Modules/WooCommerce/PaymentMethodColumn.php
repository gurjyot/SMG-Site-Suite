<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class PaymentMethodColumn implements ModuleInterface {
    private const COL='smg_payment_method';
    public function register():void{
        add_filter('manage_edit-shop_order_columns',[$this,'columns'],30);add_action('manage_shop_order_posts_custom_column',[$this,'legacyValue'],30,2);
        add_filter('manage_woocommerce_page_wc-orders_columns',[$this,'columns'],30);add_action('manage_woocommerce_page_wc-orders_custom_column',[$this,'hposValue'],30,2);
    }
    public function columns(array $columns):array{$out=[];foreach($columns as $key=>$label){$out[$key]=$label;if($key==='order_status')$out[self::COL]=__('Payment','smg-site-suite');}if(!isset($out[self::COL]))$out[self::COL]=__('Payment','smg-site-suite');return $out;}
    public function legacyValue(string $column,int $postId):void{if($column!==self::COL)return;$order=wc_get_order($postId);echo esc_html($order?$order->get_payment_method_title():'—');}
    public function hposValue(string $column,$order):void{if($column!==self::COL)return;if(!$order instanceof WC_Order)$order=wc_get_order($order);echo esc_html($order?$order->get_payment_method_title():'—');}
}
