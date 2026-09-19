<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class EstimatedDeliveryMessage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_delivery_message';

    public function register():void{add_action('woocommerce_single_product_summary',[$this,'output'],31);}

    public function settingsSchema():array{
        return [
            ['key'=>'minimum_days','type'=>'number','label'=>__('Minimum delivery days','smg-site-suite'),'default'=>3],
            ['key'=>'maximum_days','type'=>'number','label'=>__('Maximum delivery days','smg-site-suite'),'default'=>7],
            ['key'=>'template','type'=>'text','label'=>__('Message','smg-site-suite'),'default'=>'Estimated delivery: {{min}}–{{max}} business days'],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,['minimum_days'=>3,'maximum_days'=>7,'template'=>'Estimated delivery: {{min}}–{{max}} business days']);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        $min=max(0,min(90,absint($input['minimum_days']??3)));$max=max($min,min(180,absint($input['maximum_days']??7)));
        update_option(self::OPTION,['minimum_days'=>$min,'maximum_days'=>$max,'template'=>sanitize_text_field((string)($input['template']??''))],false);
    }

    public function output():void{
        $s=$this->settings();$template=(string)($s['template']??'');if($template==='')return;
        $message=strtr($template,['{{min}}'=>(string)($s['minimum_days']??3),'{{max}}'=>(string)($s['maximum_days']??7)]);
        echo '<p class="smgss-estimated-delivery">'.esc_html($message).'</p>';
    }
}
