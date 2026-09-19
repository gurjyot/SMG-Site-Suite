<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class ProductPriceHistory implements ModuleInterface {
    private const META='_smg_site_suite_price_history';

    public function register():void{
        add_action('woocommerce_update_product',[$this,'record'],20);
        add_action('add_meta_boxes_product',[$this,'metabox']);
    }

    public function record(int $productId):void{
        $product=wc_get_product($productId);if(!$product)return;
        $regular=(string)$product->get_regular_price();$sale=(string)$product->get_sale_price();
        $history=get_post_meta($productId,self::META,true);if(!is_array($history))$history=[];
        $latest=end($history);
        if(is_array($latest)&&($latest['regular']??'')===$regular&&($latest['sale']??'')===$sale)return;
        $history[]=['time'=>time(),'regular'=>$regular,'sale'=>$sale];
        if(count($history)>50)$history=array_slice($history,-50);
        update_post_meta($productId,self::META,$history);
    }

    public function metabox():void{
        add_meta_box('smg-site-suite-price-history',__('Price History','smg-site-suite'),[$this,'render'],'product','side','default');
    }

    public function render(\WP_Post $post):void{
        $history=get_post_meta($post->ID,self::META,true);if(!is_array($history)||$history===[]){echo '<p>'.esc_html__('No recorded price changes yet.','smg-site-suite').'</p>';return;}
        echo '<div style="max-height:220px;overflow:auto"><table style="width:100%;font-size:12px"><tbody>';
        foreach(array_reverse(array_slice($history,-10)) as $row){
            echo '<tr><td>'.esc_html(wp_date('Y-m-d H:i',(int)($row['time']??0))).'</td><td>'.esc_html((string)($row['regular']??'—')).'</td><td>'.esc_html((string)($row['sale']??'—')).'</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
