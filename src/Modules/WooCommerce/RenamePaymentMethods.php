<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class RenamePaymentMethods implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_payment_names';

    public function register():void{add_filter('woocommerce_available_payment_gateways',[$this,'rename'],99);}

    public function settingsSchema():array{
        return [['key'=>'map','type'=>'textarea','label'=>__('Payment method names','smg-site-suite'),'description'=>__("One per line: gateway_id=New Name\nExample: cod=Cash on Delivery",'smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['map'=>sanitize_textarea_field((string)($input['map']??''))],false);}

    public function rename(array $gateways):array{
        foreach($this->map() as $id=>$label){
            if(isset($gateways[$id]))$gateways[$id]->title=$label;
        }
        return $gateways;
    }

    private function map():array{
        $out=[];$raw=(string)($this->settings()['map']??'');
        foreach(preg_split('/\r\n|\r|\n/',$raw)?:[] as $line){
            if(!str_contains($line,'='))continue;
            [$id,$label]=array_map('trim',explode('=',$line,2));
            $id=sanitize_key($id);$label=sanitize_text_field($label);
            if($id!==''&&$label!=='')$out[$id]=$label;
        }
        return $out;
    }
}
