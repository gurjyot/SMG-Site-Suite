<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class BuyNow implements ModuleInterface {
    public function register():void{
        add_action('woocommerce_after_add_to_cart_button',[$this,'button'],20);
        add_filter('woocommerce_add_to_cart_redirect',[$this,'redirect']);
    }
    public function button():void{
        global $product;if(!$product instanceof WC_Product||!$product->is_purchasable()||!$product->is_in_stock())return;
        echo '<button type="submit" name="smg_buy_now" value="1" class="button alt smg-buy-now">'.esc_html__('Buy Now','smg-site-suite').'</button>';
    }
    public function redirect(string $url):string{
        if(isset($_REQUEST['smg_buy_now'])&&'1'===sanitize_text_field(wp_unslash($_REQUEST['smg_buy_now'])))return wc_get_checkout_url();
        return $url;
    }
}
