<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class EmptyCartButton implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_empty_cart_button';

    public function register():void{
        add_action('woocommerce_cart_actions',[$this,'button']);
        add_action('wp_loaded',[$this,'handle'],20);
    }

    public function settingsSchema():array{
        return [['key'=>'label','type'=>'text','label'=>__('Button label','smg-site-suite'),'default'=>'Empty Cart']];
    }

    public function settings():array{$v=get_option(self::OPTION,['label'=>'Empty Cart']);return is_array($v)?$v:[];}
    public function saveSettings(array $input):void{update_option(self::OPTION,['label'=>sanitize_text_field((string)($input['label']??'Empty Cart'))],false);}

    public function button():void{
        echo '<button type="submit" class="button" name="smg_empty_cart" value="1">'.esc_html((string)($this->settings()['label']??__('Empty Cart','smg-site-suite'))).'</button>';
        wp_nonce_field('smg_site_suite_empty_cart','smg_empty_cart_nonce');
    }

    public function handle():void{
        if(empty($_POST['smg_empty_cart'])||empty($_POST['smg_empty_cart_nonce']))return;
        if(!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smg_empty_cart_nonce'])),'smg_site_suite_empty_cart'))return;
        if(WC()->cart)WC()->cart->empty_cart();
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
