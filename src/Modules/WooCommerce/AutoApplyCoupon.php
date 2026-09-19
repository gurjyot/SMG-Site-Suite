<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class AutoApplyCoupon implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_auto_coupon';

    public function register():void{
        add_action('woocommerce_before_cart',[$this,'apply']);
        add_action('woocommerce_before_checkout_form',[$this,'apply'],5);
        add_action('woocommerce_store_api_cart_update_customer_from_request',[$this,'applyStoreApi']);
    }

    public function settingsSchema():array{
        return [['key'=>'coupon','type'=>'text','label'=>__('Coupon code to auto-apply','smg-site-suite'),'default'=>'']];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['coupon'=>wc_format_coupon_code((string)($input['coupon']??''))],false);}

    public function apply():void{
        if(!WC()->cart)return;
        $code=(string)($this->settings()['coupon']??'');
        if($code!==''&&!WC()->cart->has_discount($code))WC()->cart->apply_coupon($code);
    }

    public function applyStoreApi():void{$this->apply();}
}
