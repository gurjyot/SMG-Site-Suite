<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class DisableReviews implements ModuleInterface {
    public function register():void{add_filter('woocommerce_product_tabs',[$this,'tabs'],98);add_filter('comments_open',[$this,'commentsOpen'],20,2);}
    public function tabs(array $tabs):array{unset($tabs['reviews']);return $tabs;}
    public function commentsOpen(bool $open,int $postId):bool{return get_post_type($postId)==='product'?false:$open;}
}
