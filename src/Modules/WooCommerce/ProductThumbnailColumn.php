<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ProductThumbnailColumn implements ModuleInterface {
    public function register():void{
        add_filter('manage_edit-product_columns',[$this,'columns'],20);
        add_action('manage_product_posts_custom_column',[$this,'value'],20,2);
    }

    public function columns(array $columns):array{
        if(isset($columns['thumb']))return $columns;
        $out=[];
        foreach($columns as $key=>$label){$out[$key]=$label;if($key==='cb')$out['smg_product_thumb']=__('Image','smg-site-suite');}
        return $out;
    }

    public function value(string $column,int $postId):void{
        if($column!=='smg_product_thumb')return;
        $product=wc_get_product($postId);
        echo $product?wp_kses_post($product->get_image([48,48],['style'=>'width:48px;height:48px;object-fit:cover'])):'—';
    }
}
