<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\ModuleInterface;
final class FreeShippingOnly implements ModuleInterface {
    public function register():void{add_filter('woocommerce_package_rates',[$this,'rates'],100,2);}
    public function rates(array $rates,array $package):array{
        $free=[];foreach($rates as $id=>$rate){if($rate instanceof \WC_Shipping_Rate&&$rate->get_method_id()==='free_shipping')$free[$id]=$rate;}
        return $free!==[]?$free:$rates;
    }
}
