<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ProductSkuColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_edit-product_columns',[$this,'columns'],25);
        add_action('manage_product_posts_custom_column',[$this,'value'],25,2);
    }

    public function columns(array $columns):array{$columns['smg_sku']=__('SKU','smg-site-suite');return $columns;}

    public function value(string $column,int $postId):void{
        if($column!=='smg_sku')return;
        $product=wc_get_product($postId);
        $sku=$product?$product->get_sku():'';
        echo $sku!==''?'<code>'.esc_html($sku).'</code>':'—';
    }
}
