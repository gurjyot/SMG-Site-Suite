<?php
namespace SMG\SiteSuite\Admin;

use SMG\WPFoundation\Modules\ModuleRegistry;
use SMG\WPFoundation\Modules\ModuleStateStore;

final class CompatibilityGuard {
    public function __construct(private ModuleRegistry $registry,private ModuleStateStore $state){}

    public function boot():void{
        add_action('admin_notices',[$this,'checkoutBlocksNotice']);
    }

    public function checkoutBlocksNotice():void{
        if(!current_user_can('manage_options')||!class_exists('WooCommerce')||!function_exists('wc_get_page_id'))return;

        $usesBlocks=$this->pageHasBlock('checkout','woocommerce/checkout')||$this->pageHasBlock('cart','woocommerce/cart');
        if(!$usesBlocks)return;

        $incompatible=[];
        foreach($this->state->active() as $slug){
            $definition=$this->registry->get($slug);
            if(!$definition)continue;
            if(!in_array('compat:classic-checkout',$definition->tags(),true))continue;
            $incompatible[]=$definition->name();
        }

        if($incompatible===[])return;

        sort($incompatible,SORT_NATURAL|SORT_FLAG_CASE);
        echo '<div class="notice notice-warning"><p><strong>'.esc_html__('SMG Site Suite: Checkout Blocks compatibility notice','smg-site-suite').'</strong></p>';
        echo '<p>'.esc_html(sprintf(
            __('This store uses WooCommerce Cart/Checkout Blocks. These active modules are currently classic-checkout-only: %s. Their classic checkout behavior may not appear in the block experience.','smg-site-suite'),
            implode(', ',$incompatible)
        )).'</p></div>';
    }

    private function pageHasBlock(string $page,string $block):bool{
        $pageId=wc_get_page_id($page);
        if($pageId<=0)return false;
        $post=get_post($pageId);
        return $post instanceof \WP_Post&&has_block($block,$post);
    }
}
