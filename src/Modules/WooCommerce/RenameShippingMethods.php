<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RenameShippingMethods implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_shipping_names';

    public function register():void{add_filter('woocommerce_package_rates',[$this,'rename'],99);}

    public function settingsSchema():array{
        return [['key'=>'map','type'=>'textarea','label'=>__('Shipping method names','smg-site-suite'),'description'=>__("One per line: method_id=New Name\nExamples: flat_rate=Standard Shipping or flat_rate:3=Delhi Delivery",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['map'=>sanitize_textarea_field((string)($input['map']??''))],false);}

    public function rename(array $rates):array{
        $map=$this->map();
        foreach($rates as $id=>$rate){
            if(!$rate instanceof \WC_Shipping_Rate)continue;
            $method=$rate->get_method_id();
            $label=$map[$id]??$map[$method]??null;
            if($label!==null)$rate->set_label($label);
        }
        return $rates;
    }

    private function map():array{
        $out=[];$raw=(string)($this->settings()['map']??'');
        foreach(preg_split('/\r\n|\r|\n/',$raw)?:[] as $line){
            if(!str_contains($line,'='))continue;
            [$id,$label]=array_map('trim',explode('=',$line,2));
            $id=preg_replace('/[^a-zA-Z0-9_:\-]/','',$id)??'';
            $label=sanitize_text_field($label);
            if($id!==''&&$label!=='')$out[$id]=$label;
        }
        return $out;
    }
}
