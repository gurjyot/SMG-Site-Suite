<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ProductTabsControl implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_product_tabs';

    public function register():void{
        add_filter('woocommerce_product_tabs',[$this,'tabs'],99);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'hide_description','type'=>'checkbox','label'=>__('Hide Description tab','smg-site-suite')],
            ['key'=>'hide_additional','type'=>'checkbox','label'=>__('Hide Additional Information tab','smg-site-suite')],
            ['key'=>'hide_reviews','type'=>'checkbox','label'=>__('Hide Reviews tab','smg-site-suite')],
            ['key'=>'description_title','type'=>'text','label'=>__('Rename Description tab','smg-site-suite'),'default'=>''],
            ['key'=>'additional_title','type'=>'text','label'=>__('Rename Additional Information tab','smg-site-suite'),'default'=>''],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'hide_description'=>!empty($input['hide_description']),
            'hide_additional'=>!empty($input['hide_additional']),
            'hide_reviews'=>!empty($input['hide_reviews']),
            'description_title'=>sanitize_text_field((string)($input['description_title']??'')),
            'additional_title'=>sanitize_text_field((string)($input['additional_title']??'')),
        ],false);
    }

    public function tabs(array $tabs):array{
        $s=$this->settings();
        if(!empty($s['hide_description']))unset($tabs['description']);
        elseif(!empty($s['description_title'])&&isset($tabs['description']))$tabs['description']['title']=$s['description_title'];

        if(!empty($s['hide_additional']))unset($tabs['additional_information']);
        elseif(!empty($s['additional_title'])&&isset($tabs['additional_information']))$tabs['additional_information']['title']=$s['additional_title'];

        if(!empty($s['hide_reviews']))unset($tabs['reviews']);
        return $tabs;
    }
}
