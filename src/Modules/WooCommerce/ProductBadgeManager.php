<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\SettingsModuleInterface;

final class ProductBadgeManager implements SettingsModuleInterface {
    private const OPTION='smg_site_suite_product_badges';

    public function register():void{
        add_action('woocommerce_before_shop_loop_item_title',[$this,'loop'],8);
        add_action('woocommerce_single_product_summary',[$this,'single'],6);
    }

    public function settingsSchema():array{
        return [
            ['key'=>'show_new','type'=>'checkbox','label'=>__('Show New badge','smg-site-suite'),'default'=>true],
            ['key'=>'new_days','type'=>'number','label'=>__('New badge duration (days)','smg-site-suite'),'default'=>30],
            ['key'=>'show_low','type'=>'checkbox','label'=>__('Show Low Stock badge','smg-site-suite'),'default'=>true],
            ['key'=>'low_threshold','type'=>'number','label'=>__('Low stock threshold','smg-site-suite'),'default'=>5],
            ['key'=>'show_out','type'=>'checkbox','label'=>__('Show Out of Stock badge','smg-site-suite'),'default'=>true],
            ['key'=>'new_label','type'=>'text','label'=>__('New label','smg-site-suite'),'default'=>'New'],
            ['key'=>'low_label','type'=>'text','label'=>__('Low stock label','smg-site-suite'),'default'=>'Low Stock'],
            ['key'=>'out_label','type'=>'text','label'=>__('Out of stock label','smg-site-suite'),'default'=>'Out of Stock'],
        ];
    }

    public function settings():array{
        $v=get_option(self::OPTION,['show_new'=>true,'new_days'=>30,'show_low'=>true,'low_threshold'=>5,'show_out'=>true,'new_label'=>'New','low_label'=>'Low Stock','out_label'=>'Out of Stock']);
        return is_array($v)?$v:[];
    }

    public function saveSettings(array $input):void{
        update_option(self::OPTION,[
            'show_new'=>!empty($input['show_new']),
            'new_days'=>max(1,min(365,absint($input['new_days']??30))),
            'show_low'=>!empty($input['show_low']),
            'low_threshold'=>max(0,min(9999,absint($input['low_threshold']??5))),
            'show_out'=>!empty($input['show_out']),
            'new_label'=>sanitize_text_field((string)($input['new_label']??'New')),
            'low_label'=>sanitize_text_field((string)($input['low_label']??'Low Stock')),
            'out_label'=>sanitize_text_field((string)($input['out_label']??'Out of Stock')),
        ],false);
    }

    public function loop():void{global $product;if($product instanceof \WC_Product)$this->render($product);}
    public function single():void{global $product;if($product instanceof \WC_Product)$this->render($product);}

    private function render(\WC_Product $product):void{
        $s=$this->settings();$badges=[];
        if(!$product->is_in_stock()&&!empty($s['show_out']))$badges[]=(string)($s['out_label']??'Out of Stock');
        elseif($product->managing_stock()&&!empty($s['show_low'])){
            $qty=$product->get_stock_quantity();$threshold=(int)($s['low_threshold']??5);
            if($qty!==null&&$threshold>0&&$qty<=$threshold)$badges[]=(string)($s['low_label']??'Low Stock');
        }

        if(!empty($s['show_new'])){
            $created=$product->get_date_created();
            if($created&&$created->getTimestamp()>=time()-((int)($s['new_days']??30)*DAY_IN_SECONDS))$badges[]=(string)($s['new_label']??'New');
        }

        foreach(array_unique(array_filter($badges)) as $badge)echo '<span class="smgss-product-badge">'.esc_html($badge).'</span> ';
    }
}
