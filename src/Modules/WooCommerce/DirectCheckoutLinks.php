<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class DirectCheckoutLinks implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_direct_checkout_links';

    public function register():void{
        add_action('wp_loaded',[$this,'process'],5);
        add_action('add_meta_boxes_product',[$this,'metabox']);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'max_quantity','type'=>'number','label'=>__('Maximum quantity in generated links','smg-site-suite'),'default'=>20],
            ['key'=>'clear_cart','type'=>'checkbox','label'=>__('Clear the existing cart before adding the linked product','smg-site-suite'),'default'=>true],
        ];
    }

    public function settings():array{
        $value=get_option(self::OPTION,['max_quantity'=>20,'clear_cart'=>true]);
        return is_array($value)?$value:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'max_quantity'=>max(1,min(9999,absint($input['max_quantity']??20))),
            'clear_cart'=>!empty($input['clear_cart']),
        ],false);
    }

    public function process():void{
        if(!isset($_GET['smg_direct_checkout'])||'1'!==sanitize_text_field(wp_unslash($_GET['smg_direct_checkout'])))return;
        if(!WC()->cart)return;

        $productId=isset($_GET['product_id'])?absint($_GET['product_id']):0;
        $product=wc_get_product($productId);
        if(!$product||!$product->is_purchasable()||!$product->is_in_stock())return;

        $settings=$this->settings();
        $limit=(int)($settings['max_quantity']??20);
        $productMax=(int)$product->get_max_purchase_quantity();
        if($productMax>0)$limit=min($limit,$productMax);
        $quantity=max(1,min($limit,absint($_GET['quantity']??1)));

        if(!empty($settings['clear_cart']))WC()->cart->empty_cart();
        if(!WC()->cart->add_to_cart($productId,$quantity))return;

        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    public function metabox():void{
        add_meta_box(
            'smg-site-suite-direct-checkout-link',
            __('Direct Checkout Link','smg-site-suite'),
            [$this,'renderMetabox'],
            'product',
            'side',
            'default'
        );
    }

    public function renderMetabox(\WP_Post $post):void{
        $product=wc_get_product($post->ID);
        if(!$product||!$product->is_purchasable()){
            echo '<p>'.esc_html__('Available for purchasable products.','smg-site-suite').'</p>';return;
        }
        $url=add_query_arg([
            'smg_direct_checkout'=>'1',
            'product_id'=>$product->get_id(),
            'quantity'=>'1',
        ],home_url('/'));
        echo '<input type="text" class="widefat" readonly value="'.esc_attr($url).'">';
        echo '<p class="description">'.esc_html__('Append &quantity=N for campaign links.','smg-site-suite').'</p>';
    }
}
