<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CartFragmentsControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_cart_fragments';

    public function register():void{add_action('wp_enqueue_scripts',[$this,'control'],100);}

    public function settingsSchema():array{
        return [['key'=>'mode','type'=>'select','label'=>__('Cart fragments behavior','smg-site-suite'),'default'=>'store','options'=>[
            'default'=>__('WooCommerce default','smg-site-suite'),
            'store'=>__('Only on store/cart/checkout/account pages','smg-site-suite'),
            'disabled'=>__('Disable everywhere','smg-site-suite'),
        ]]];
    }

    public function settings():array{$v=get_option(self::OPTION,['mode'=>'store']);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{$mode=(string)($input['mode']??'store');if(!in_array($mode,['default','store','disabled'],true))$mode='store';update_option(self::OPTION,['mode'=>$mode],false);}

    public function control():void{
        $mode=(string)($this->settings()['mode']??'store');
        if($mode==='default')return;
        $store=is_woocommerce()||is_cart()||is_checkout()||is_account_page();
        if($mode==='disabled'||!$store){
            wp_dequeue_script('wc-cart-fragments');
            wp_deregister_script('wc-cart-fragments');
        }
    }
}
