<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class OrderItemSummaryColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_edit-shop_order_columns',[$this,'columns'],26);
        add_action('manage_shop_order_posts_custom_column',[$this,'legacy'],26,2);
        add_filter('manage_woocommerce_page_wc-orders_columns',[$this,'columns'],26);
        add_action('manage_woocommerce_page_wc-orders_custom_column',[$this,'hpos'],26,2);
    }

    public function columns(array $columns):array{$columns['smg_order_items']=__('Items','smg-site-suite');return $columns;}
    public function legacy(string $column,int $postId):void{if($column==='smg_order_items')$this->output(wc_get_order($postId));}
    public function hpos(string $column,$order):void{if($column==='smg_order_items')$this->output($order instanceof \WC_Order?$order:null);}

    private function output(?\WC_Order $order):void{
        if(!$order){echo '—';return;}
        $parts=[];$count=0;
        foreach($order->get_items('line_item') as $item){
            $parts[]=sprintf('%s × %d',$item->get_name(),(int)$item->get_quantity());
            $count++;if($count>=3)break;
        }
        $total=count($order->get_items('line_item'));
        $text=implode(', ',$parts);
        if($total>3)$text.=' +'.($total-3);
        echo $text!==''?esc_html($text):'—';
    }
}
