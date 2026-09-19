<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CheckoutTextCustomizer implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_checkout_text';

    public function register():void{
        add_filter('woocommerce_order_button_text',[$this,'button']);
        add_filter('woocommerce_checkout_fields',[$this,'fields'],90);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'place_order','type'=>'text','label'=>__('Place order button text','smg-site-suite'),'default'=>''],
            ['key'=>'phone_placeholder','type'=>'text','label'=>__('Phone placeholder','smg-site-suite'),'default'=>''],
            ['key'=>'email_placeholder','type'=>'text','label'=>__('Email placeholder','smg-site-suite'),'default'=>''],
            ['key'=>'order_notes_placeholder','type'=>'text','label'=>__('Order notes placeholder','smg-site-suite'),'default'=>''],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $out=[];foreach(['place_order','phone_placeholder','email_placeholder','order_notes_placeholder'] as $key)$out[$key]=sanitize_text_field((string)($input[$key]??''));
        update_option(self::OPTION,$out,false);
    }

    public function button(string $text):string{
        $custom=(string)($this->settings()['place_order']??'');return $custom!==''?$custom:$text;
    }

    public function fields(array $fields):array{
        $s=$this->settings();
        $map=[
            ['billing','billing_phone','phone_placeholder'],
            ['billing','billing_email','email_placeholder'],
            ['order','order_comments','order_notes_placeholder'],
        ];
        foreach($map as [$group,$field,$key]){
            if(!empty($s[$key])&&isset($fields[$group][$field]))$fields[$group][$field]['placeholder']=$s[$key];
        }
        return $fields;
    }
}
