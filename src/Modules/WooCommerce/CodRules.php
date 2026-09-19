<?php
namespace SMG\SiteSuite\Modules\WooCommerce;
use SMG\WPFoundation\Contracts\SettingsModuleInterface;
final class CodRules implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_cod_rules';
    public function register():void{add_filter('woocommerce_available_payment_gateways',[$this,'gateways']);}
    public function settingsSchema():array{return [
        ['key'=>'minimum','type'=>'number','label'=>__('Minimum amount for COD','smg-site-suite'),'default'=>0],
        ['key'=>'maximum','type'=>'number','label'=>__('Maximum amount for COD','smg-site-suite'),'default'=>0,'description'=>__('Use 0 for no maximum.','smg-site-suite')]
    ];}
    public function settings():array{$v=get_option(self::OPTION,['minimum'=>0,'maximum'=>0]);return is_array($v)?$v:['minimum'=>0,'maximum'=>0];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['minimum'=>max(0,(float)($input['minimum']??0)),'maximum'=>max(0,(float)($input['maximum']??0))],false);}
    public function gateways(array $gateways):array{
        if(!isset($gateways['cod'])||!WC()->cart)return $gateways;$total=(float)WC()->cart->get_total('edit');$s=$this->settings();$min=(float)($s['minimum']??0);$max=(float)($s['maximum']??0);
        if(($min>0&&$total<$min)||($max>0&&$total>$max))unset($gateways['cod']);return $gateways;
    }
}
