<?php
namespace SMG\SiteSuite\Modules\WooCommerce;

use SMG\WPFoundation\Contracts\ModuleInterface;

final class DisableMarketplaceSuggestions implements ModuleInterface {
    public function register():void{
        add_filter('woocommerce_allow_marketplace_suggestions','__return_false',999);
    }
}
