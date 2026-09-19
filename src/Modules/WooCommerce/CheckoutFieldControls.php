<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CheckoutFieldControls implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_checkout_fields';

    public function register():void{
        add_filter('woocommerce_checkout_fields',[$this,'fields'],99);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'hide_company','type'=>'checkbox','label'=>__('Hide company field','smg-site-suite')],
            ['key'=>'hide_address_2','type'=>'checkbox','label'=>__('Hide address line 2','smg-site-suite')],
            ['key'=>'hide_order_notes','type'=>'checkbox','label'=>__('Hide order notes','smg-site-suite')],
            ['key'=>'phone_required','type'=>'checkbox','label'=>__('Require billing phone','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['phone_required'=>true]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'hide_company'=>!empty($input['hide_company']),
            'hide_address_2'=>!empty($input['hide_address_2']),
            'hide_order_notes'=>!empty($input['hide_order_notes']),
            'phone_required'=>!empty($input['phone_required']),
        ],false);
    }

    public function fields(array $fields):array{
        $s=$this->settings();

        if(!empty($s['hide_company'])){
            unset($fields['billing']['billing_company'],$fields['shipping']['shipping_company']);
        }
        if(!empty($s['hide_address_2'])){
            unset($fields['billing']['billing_address_2'],$fields['shipping']['shipping_address_2']);
        }
        if(!empty($s['hide_order_notes']))unset($fields['order']['order_comments']);
        if(isset($fields['billing']['billing_phone']))$fields['billing']['billing_phone']['required']=!empty($s['phone_required']);

        return $fields;
    }
}
