<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class OrderNotesColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_edit-shop_order_columns',[$this,'columns'],25);
        add_action('manage_shop_order_posts_custom_column',[$this,'legacy'],25,2);
        add_filter('manage_woocommerce_page_wc-orders_columns',[$this,'columns'],25);
        add_action('manage_woocommerce_page_wc-orders_custom_column',[$this,'hpos'],25,2);
    }

    public function columns(array $columns):array{$columns['smg_order_note']=__('Latest Note','smg-site-suite');return $columns;}

    public function legacy(string $column,int $postId):void{
        if($column==='smg_order_note')$this->output(wc_get_order($postId));
    }

    public function hpos(string $column,$order):void{
        if($column==='smg_order_note')$this->output($order instanceof \WC_Order?$order:null);
    }

    private function output(?\WC_Order $order):void{
        if(!$order){echo '—';return;}
        $notes=wc_get_order_notes(['order_id'=>$order->get_id(),'limit'=>1,'orderby'=>'date_created_gmt','order'=>'DESC']);
        $note=$notes[0]??null;
        echo $note?esc_html(wp_trim_words(wp_strip_all_tags((string)$note->content),10,'…')):'—';
    }
}
