<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class WhatsappEnquiry implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_whatsapp_enquiry';

    public function register():void{
        add_action('woocommerce_after_add_to_cart_button',[$this,'single'],35);
        add_action('woocommerce_after_shop_loop_item',[$this,'loop'],25);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'phone','type'=>'text','label'=>__('WhatsApp number','smg-site-suite'),'description'=>__('Include country code, digits only preferred.','smg-site-suite')],
            ['key'=>'button_text','type'=>'text','label'=>__('Button text','smg-site-suite'),'default'=>__('Enquire on WhatsApp','smg-site-suite')],
            ['key'=>'message','type'=>'textarea','label'=>__('Message template','smg-site-suite'),'default'=>__('Hi, I am interested in {{product}} - {{url}}','smg-site-suite'),'description'=>__('Available placeholders: {{product}}, {{url}}, {{price}}.','smg-site-suite')],
            ['key'=>'show_loop','type'=>'checkbox','label'=>__('Show on shop/category cards','smg-site-suite'),'default'=>false],
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,[]);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'phone'=>preg_replace('/\D+/','',(string)($input['phone']??'')),
            'button_text'=>sanitize_text_field((string)($input['button_text']??__('Enquire on WhatsApp','smg-site-suite'))),
            'message'=>sanitize_textarea_field((string)($input['message']??'')),
            'show_loop'=>!empty($input['show_loop']),
        ],false);
    }

    public function single():void{
        global $product;
        if($product instanceof \WC_Product)$this->button($product);
    }

    public function loop():void{
        if(empty($this->settings()['show_loop']))return;
        global $product;
        if($product instanceof \WC_Product)$this->button($product);
    }

    private function button(\WC_Product $product):void{
        $settings=$this->settings();
        $phone=(string)($settings['phone']??'');
        if($phone==='')return;

        $template=(string)($settings['message']??'Hi, I am interested in {{product}} - {{url}}');
        $message=strtr($template,[
            '{{product}}'=>$product->get_name(),
            '{{url}}'=>$product->get_permalink(),
            '{{price}}'=>wp_strip_all_tags($product->get_price_html()),
        ]);

        $url='https://wa.me/'.$phone.'?text='.rawurlencode($message);
        echo '<a class="button smgss-whatsapp-enquiry" target="_blank" rel="noopener noreferrer" href="'.esc_url($url).'">'.esc_html((string)($settings['button_text']??__('Enquire on WhatsApp','smg-site-suite'))).'</a>';
    }
}
