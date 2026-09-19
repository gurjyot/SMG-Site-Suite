<?php
/**
 * Plugin Name: SMG Site Suite
 * Description: A modular suite of lightweight WordPress and WooCommerce enhancements.
 * Version: 0.1.0-dev
 * Author: Singh Media Group
 * Text Domain: smg-site-suite
 * Requires at least: 6.5
 * Requires PHP: 8.0
 */

if(!defined('ABSPATH')){exit;}

define('SMG_SITE_SUITE_VERSION','0.1.0-dev');
define('SMG_SITE_SUITE_FILE',__FILE__);
define('SMG_SITE_SUITE_PATH',plugin_dir_path(__FILE__));
define('SMG_SITE_SUITE_URL',plugin_dir_url(__FILE__));

require_once SMG_SITE_SUITE_PATH.'includes/Autoloader.php';
SMG\SiteSuite\Autoloader::register();

SMG\SiteSuite\Plugin::instance()->boot();
