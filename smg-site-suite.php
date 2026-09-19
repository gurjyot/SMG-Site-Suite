<?php
/**
 * Plugin Name: SMG Site Suite
 * Description: A modular suite of lightweight WordPress and WooCommerce enhancements.
 * Version: 0.1.0-dev
 * Author: Singh Media Group
 * Text Domain: smg-site-suite
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * WC tested up to: 11.1.1
 */

if(!defined('ABSPATH')){exit;}

define('SMG_SITE_SUITE_VERSION','0.1.0-dev');
define('SMG_SITE_SUITE_FILE',__FILE__);
define('SMG_SITE_SUITE_PATH',plugin_dir_path(__FILE__));
define('SMG_SITE_SUITE_URL',plugin_dir_url(__FILE__));

add_action('before_woocommerce_init',static function():void{
    if(!class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))return;
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables',SMG_SITE_SUITE_FILE,true);
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks',SMG_SITE_SUITE_FILE,false);
});

require_once SMG_SITE_SUITE_PATH.'includes/Autoloader.php';
SMG\SiteSuite\Autoloader::register();

SMG\SiteSuite\Plugin::instance()->boot();
