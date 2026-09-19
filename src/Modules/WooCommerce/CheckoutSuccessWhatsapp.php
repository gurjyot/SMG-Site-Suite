<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class CheckoutSuccessWhatsapp implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_checkout_success_whatsapp';

    public function register():void{add_action('woocommerce_thankyou',[$this,'output'],30);}

    public function settingsSchema():array{
        return [
            ['key'=>'phone','type'=>'text','label'=>__('WhatsApp number','smg-site-suite'),'description'=>__('Include country code.','smg-site-suite')],
            ['key'=>'button_text','type'=>'text','label'=>__('Button text','smg-site-suite'),'default'=>'Message us on WhatsApp'],
            ['key'=>'message','type'=>'textarea','label'=>__('Message template','smg-site-suite'),'default'=>'Hi, I just placed order #{{order}} for {{total}}.','description'=>__('Placeholders: {{order}}, {{total}}, {{name}}.','smg-site-suite')],
        ];
    }

    public function settings():array{$v=get_option(self::OPTION,[]);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'phone'=>preg_replace('/\D+/','',(string)($input['phone']??'')),
            'button_text'=>sanitize_text_field((string)($input['button_text']??'Message us on WhatsApp')),
            'message'=>sanitize_textarea_field((string)($input['message']??'')),
        ],false);
    }

    public function output(int $orderId):void{
        $s=$this->settings();$phone=(string)($s['phone']??'');if($phone==='')return;
        $order=wc_get_order($orderId);if(!$order)return;
        $message=strtr((string)($s['message']??''),[
            '{{order}}'=>(string)$order->get_order_number(),
            '{{total}}'=>wp_strip_all_tags($order->get_formatted_order_total()),
            '{{name}}'=>trim($order->get_formatted_billing_full_name()),
        ]);
        $url='https://wa.me/'.$phone.'?text='.rawurlencode($message);
        echo '<p class="smgss-checkout-whatsapp"><a class="button" target="_blank" rel="noopener noreferrer" href="'.esc_url($url).'">'.esc_html((string)($s['button_text']??__('Message us on WhatsApp','smg-site-suite'))).'</a></p>';
    }
}
