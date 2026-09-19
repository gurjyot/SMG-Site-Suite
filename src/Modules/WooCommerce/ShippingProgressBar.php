<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class ShippingProgressBar implements ModuleInterface {
    public function register():void{
        add_action('woocommerce_before_cart_totals',[$this,'render']);
        add_action('woocommerce_review_order_before_order_total',[$this,'renderCheckout']);
    }
    public function render():void{$this->output();}
    public function renderCheckout():void{$this->output();}
    private function output():void{
        if(!WC()->cart||WC()->cart->is_empty())return;$threshold=$this->threshold();if($threshold<=0)return;
        $subtotal=(float)WC()->cart->get_displayed_subtotal();$remaining=max(0,$threshold-$subtotal);$percent=min(100,max(0,($subtotal/$threshold)*100));
        echo '<div class="smg-shipping-progress" style="margin:0 0 16px"><div style="height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden"><span style="display:block;height:100%;width:'.esc_attr((string)$percent).'%;background:currentColor"></span></div><p style="margin:8px 0 0">';
        if($remaining>0)echo wp_kses_post(sprintf(__('Add %s more to qualify for free shipping.','smg-site-suite'),wc_price($remaining)));
        else echo esc_html__('You qualify for free shipping.','smg-site-suite');
        echo '</p></div>';
    }
    private function threshold():float{
        $packages=WC()->shipping()->get_packages();$package=$packages[0]??null;if(!is_array($package))return 0;
        $zone=WC_Shipping_Zones::get_zone_matching_package($package);
        foreach($zone->get_shipping_methods(true) as $method){
            if($method->id!=='free_shipping')continue;$requires=(string)$method->get_option('requires');
            if(!in_array($requires,['min_amount','either','both'],true))continue;
            return max(0,(float)$method->get_option('min_amount',0));
        }
        return 0;
    }
}
