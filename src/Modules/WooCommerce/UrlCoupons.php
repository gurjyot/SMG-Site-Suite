<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class UrlCoupons implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_url_coupons';

    public function register():void{add_action('wp_loaded',[$this,'apply'],20);}

    public function settingsSchema():array{
        return [
            ['key'=>'parameter','type'=>'text','label'=>__('URL parameter','smg-site-suite'),'default'=>'coupon'],
            ['key'=>'redirect','type'=>'select','label'=>__('Redirect after applying','smg-site-suite'),'default'=>'same','options'=>[
                'same'=>__('Stay on current page','smg-site-suite'),
                'cart'=>__('Cart','smg-site-suite'),
                'checkout'=>__('Checkout','smg-site-suite'),
            ]],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['parameter'=>'coupon','redirect'=>'same']);return is_array($v)?$v:[];}

    public function saveSettings(array $input):void{
        $parameter=sanitize_key((string)($input['parameter']??'coupon'))?:'coupon';
        $redirect=(string)($input['redirect']??'same');
        if(!in_array($redirect,['same','cart','checkout'],true))$redirect='same';
        update_option(self::OPTION,['parameter'=>$parameter,'redirect'=>$redirect],false);
    }

    public function apply():void{
        if(!WC()->cart)return;
        $settings=$this->settings();$parameter=(string)($settings['parameter']??'coupon');
        if(!isset($_GET[$parameter]))return;

        $code=wc_format_coupon_code(sanitize_text_field(wp_unslash($_GET[$parameter])));
        if($code===''||WC()->cart->has_discount($code))return;

        if(!WC()->cart->apply_coupon($code))return;

        if(($settings['redirect']??'same')==='cart'){$url=wc_get_cart_url();}
        elseif(($settings['redirect']??'same')==='checkout'){$url=wc_get_checkout_url();}
        else return;

        wp_safe_redirect($url);exit;
    }
}
