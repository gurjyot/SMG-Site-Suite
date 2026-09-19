<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;

final class OrderPhoneColumn implements ModuleInterface {
    private const COL='smg_billing_phone';

    public function register():void{
        add_filter('manage_edit-shop_order_columns',[$this,'columns'],31);
        add_action('manage_shop_order_posts_custom_column',[$this,'legacyValue'],31,2);
        add_filter('manage_woocommerce_page_wc-orders_columns',[$this,'columns'],31);
        add_action('manage_woocommerce_page_wc-orders_custom_column',[$this,'hposValue'],31,2);
    }

    public function columns(array $columns):array{
        $out=[];
        foreach($columns as $key=>$label){
            $out[$key]=$label;
            if($key==='billing_address')$out[self::COL]=__('Phone','smg-site-suite');
        }
        if(!isset($out[self::COL]))$out[self::COL]=__('Phone','smg-site-suite');
        return $out;
    }

    public function legacyValue(string $column,int $postId):void{
        if($column!==self::COL)return;
        $order=wc_get_order($postId);
        $this->render($order);
    }

    public function hposValue(string $column,$order):void{
        if($column!==self::COL)return;
        if(!$order instanceof \WC_Order)$order=wc_get_order($order);
        $this->render($order);
    }

    private function render($order):void{
        if(!$order instanceof \WC_Order){echo '—';return;}
        $phone=(string)$order->get_billing_phone();
        if($phone===''){echo '—';return;}
        echo '<a href="'.esc_url('tel:'.preg_replace('/[^0-9+]/','',$phone)).'">'.esc_html($phone).'</a>';
    }
}
