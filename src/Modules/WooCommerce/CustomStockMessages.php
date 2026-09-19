<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CustomStockMessages implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_stock_messages';

    public function register():void{add_filter('woocommerce_get_availability_text',[$this,'text'],20,2);}

    public function settingsSchema():array{
        return [
            ['key'=>'in_stock','type'=>'text','label'=>__('In-stock message','smg-site-suite'),'default'=>'In stock'],
            ['key'=>'low_stock','type'=>'text','label'=>__('Low-stock message','smg-site-suite'),'default'=>'Only {{quantity}} left'],
            ['key'=>'out_of_stock','type'=>'text','label'=>__('Out-of-stock message','smg-site-suite'),'default'=>'Out of stock'],
            ['key'=>'low_threshold','type'=>'number','label'=>__('Low-stock threshold','smg-site-suite'),'default'=>5],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['in_stock'=>'In stock','low_stock'=>'Only {{quantity}} left','out_of_stock'=>'Out of stock','low_threshold'=>5]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'in_stock'=>sanitize_text_field((string)($input['in_stock']??'')),
            'low_stock'=>sanitize_text_field((string)($input['low_stock']??'')),
            'out_of_stock'=>sanitize_text_field((string)($input['out_of_stock']??'')),
            'low_threshold'=>max(0,min(9999,absint($input['low_threshold']??5))),
        ],false);
    }

    public function text(string $text,\WC_Product $product):string{
        $s=$this->settings();
        if(!$product->is_in_stock())return (string)($s['out_of_stock']??$text);
        $qty=$product->get_stock_quantity();
        $threshold=(int)($s['low_threshold']??5);
        if($product->managing_stock()&&$qty!==null&&$threshold>0&&$qty<=$threshold)return str_replace('{{quantity}}',(string)$qty,(string)($s['low_stock']??$text));
        return (string)($s['in_stock']??$text);
    }
}
