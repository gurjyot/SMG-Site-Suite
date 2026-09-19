<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class DirectCheckout implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_direct_checkout';

    public function register():void{
        add_filter('woocommerce_add_to_cart_redirect',[$this,'redirect'],20);
        add_filter('woocommerce_product_add_to_cart_url',[$this,'url'],20,2);
    }

    public function settingsSchema():array{
        return [['key'=>'enabled','type'=>'checkbox','label'=>__('Redirect add-to-cart actions directly to checkout','smg-site-suite'),'default'=>true]];
    }

    public function settings():array{$v=get_option(self::OPTION,['enabled'=>true]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['enabled'=>!empty($input['enabled'])],false);}

    public function redirect(string $url):string{return !empty($this->settings()['enabled'])?wc_get_checkout_url():$url;}

    public function url(string $url,\WC_Product $product):string{
        if(empty($this->settings()['enabled'])||!$product->is_type('simple'))return $url;
        return add_query_arg('add-to-cart',$product->get_id(),wc_get_checkout_url());
    }
}
