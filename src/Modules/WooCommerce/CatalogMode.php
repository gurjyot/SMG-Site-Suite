<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CatalogMode implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_catalog_mode';

    public function register():void{
        add_filter('woocommerce_is_purchasable',[$this,'purchasable'],99,2);
        add_filter('woocommerce_variation_is_purchasable',[$this,'purchasable'],99,2);
        add_action('wp',[$this,'removeButtons']);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'hide_prices','type'=>'checkbox','label'=>__('Hide product prices','smg-site-suite'),'default'=>false],
            ['key'=>'logged_out_only','type'=>'checkbox','label'=>__('Apply only to logged-out visitors','smg-site-suite'),'default'=>false],
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,['hide_prices'=>false,'logged_out_only'=>false]);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'hide_prices'=>!empty($input['hide_prices']),
            'logged_out_only'=>!empty($input['logged_out_only']),
        ],false);
    }

    public function purchasable(bool $purchasable,$product):bool{
        return $this->applies()?false:$purchasable;
    }

    public function removeButtons():void{
        if(!$this->applies())return;
        remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_add_to_cart',10);
        remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',30);
        if(!empty($this->settings()['hide_prices'])){
            remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price',10);
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_price',10);
        }
    }

    private function applies():bool{
        $settings=$this->settings();
        return empty($settings['logged_out_only'])||!is_user_logged_in();
    }
}
