<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ThankYouMessage implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_thankyou_message';

    public function register():void{add_action('woocommerce_thankyou',[$this,'output'],5);}

    public function settingsSchema():array{
        return [['key'=>'message','type'=>'textarea','label'=>__('Thank-you message','smg-site-suite'),'default'=>__('Thank you for your order. We appreciate your business.','smg-site-suite')]];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['message'=>wp_kses_post((string)($input['message']??''))],false);}

    public function output(int $orderId):void{
        $message=(string)($this->settings()['message']??'');
        if($message!=='')echo '<div class="woocommerce-message smgss-thankyou-message">'.wp_kses_post(wpautop($message)).'</div>';
    }
}
