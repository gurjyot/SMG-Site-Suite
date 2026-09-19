<?php
function __($text,$domain=null){return $text;}
define('SMG_SITE_SUITE_PATH',dirname(__DIR__).'/');
require SMG_SITE_SUITE_PATH.'includes/Autoloader.php';
SMG\SiteSuite\Autoloader::register();

$registry=SMG\SiteSuite\RegistryFactory::make();
$modules=$registry->all();

if(count($modules)!==41)throw new RuntimeException('Expected 41 registered modules, got '.count($modules));
if(count(array_unique(array_keys($modules)))!==count($modules))throw new RuntimeException('Duplicate module slugs detected.');

foreach([
    'disable-comments','duplicate-content','safe-svg-upload','image-size-control',
    'disable-emojis','disable-embeds','disable-feeds','clean-wp-head','disable-dashicons-frontend','hide-wp-version','disable-xml-rpc','disable-application-passwords','disable-file-editing','show-ids','active-plugins-first','featured-image-column','disable-admin-bar-frontend','hide-admin-notices','footer-timezone','search-visibility-status','last-login-column','registration-date-column','disable-author-archives','external-links-new-tab','custom-excerpt-length','custom-frontend-css','custom-admin-css','buy-now',
    'shipping-progress','payment-method-column','order-phone-column','cod-rules','fomo-sales-notifications'
] as $required){
    if(!isset($modules[$required]))throw new RuntimeException('Required module missing: '.$required);
}

foreach($modules as $slug=>$module){
    if($module->slug()!==$slug)throw new RuntimeException('Registry key mismatch for '.$slug);
    if(!class_exists($module->className()))throw new RuntimeException('Registered module class does not exist for '.$slug.': '.$module->className());

    if($module->category()==='woocommerce'){
        $plugins=$module->dependencies()['plugins']??[];
        if(!in_array('woocommerce/woocommerce.php',$plugins,true))throw new RuntimeException('WooCommerce dependency missing for '.$slug);
    }
}

echo "site-suite-registry-ok\n";
