<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class OrderAmountRules implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_order_amount_rules';
    public function register():void{
        add_action('woocommerce_check_cart_items',[$this,'validateClassic']);add_action('woocommerce_checkout_process',[$this,'validateClassic']);
        add_action('woocommerce_store_api_cart_errors',[$this,'validateStoreApi']);
    }
    public function settingsSchema():array{return [
        ['key'=>'minimum','type'=>'number','label'=>__('Minimum order amount','smg-site-suite'),'default'=>0],
        ['key'=>'maximum','type'=>'number','label'=>__('Maximum order amount','smg-site-suite'),'default'=>0,'description'=>__('Use 0 for no maximum.','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,['minimum'=>0,'maximum'=>0]);return is_array($v)?$v:['minimum'=>0,'maximum'=>0];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['minimum'=>max(0,(float)($input['minimum']??0)),'maximum'=>max(0,(float)($input['maximum']??0))],false);}
    private function violation():array{
        if(!WC()->cart)return [];$total=(float)WC()->cart->get_displayed_subtotal();$s=$this->settings();$min=(float)($s['minimum']??0);$max=(float)($s['maximum']??0);
        if($min>0&&$total<$min)return ['minimum',sprintf(__('A minimum order amount of %s is required.','smg-site-suite'),wc_price($min))];
        if($max>0&&$total>$max)return ['maximum',sprintf(__('The maximum order amount is %s.','smg-site-suite'),wc_price($max))];
        return [];
    }
    public function validateClassic():void{$v=$this->violation();if($v)wc_add_notice(wp_strip_all_tags($v[1]),'error');}
    public function validateStoreApi(\WP_Error $errors):void{$v=$this->violation();if($v)$errors->add('smg_site_suite_'.$v[0],wp_strip_all_tags($v[1]));}
}
