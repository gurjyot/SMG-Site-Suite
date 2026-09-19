<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class WooAssetControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_woo_assets';

    public function register():void{add_action('wp_enqueue_scripts',[$this,'cleanup'],99);}

    public function settingsSchema():array{
        return [
            ['key'=>'disable_styles','type'=>'checkbox','label'=>__('Unload core WooCommerce styles on non-store pages','smg-site-suite'),'default'=>true],
            ['key'=>'disable_scripts','type'=>'checkbox','label'=>__('Unload selected WooCommerce frontend scripts on non-store pages','smg-site-suite'),'default'=>false],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['disable_styles'=>true,'disable_scripts'=>false]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['disable_styles'=>!empty($input['disable_styles']),'disable_scripts'=>!empty($input['disable_scripts'])],false);}

    public function cleanup():void{
        if($this->isStoreContext())return;
        $s=$this->settings();
        if(!empty($s['disable_styles'])){
            foreach(['woocommerce-general','woocommerce-layout','woocommerce-smallscreen'] as $handle)wp_dequeue_style($handle);
        }
        if(!empty($s['disable_scripts'])){
            foreach(['wc-add-to-cart','woocommerce','wc-cart-fragments'] as $handle)wp_dequeue_script($handle);
        }
    }

    private function isStoreContext():bool{
        return is_woocommerce()||is_cart()||is_checkout()||is_account_page();
    }
}
