<?php
function __($text,$domain=null){return $text;}
define('SMG_SITE_SUITE_PATH',dirname(__DIR__).'/');
require SMG_SITE_SUITE_PATH.'includes/Autoloader.php';
SMG\SiteSuite\Autoloader::register();

$registry=SMG\SiteSuite\RegistryFactory::make();
$modules=$registry->all();

if(count($modules)!==85)throw new RuntimeException('Expected 85 registered modules, got '.count($modules));
if(count(array_unique(array_keys($modules)))!==count($modules))throw new RuntimeException('Duplicate module slugs detected.');

foreach([
    'disable-comments','duplicate-content','safe-svg-upload','image-size-control',
    'disable-emojis','disable-embeds','disable-feeds','clean-wp-head','disable-dashicons-frontend','hide-wp-version','disable-xml-rpc','disable-application-passwords','disable-file-editing','show-ids','active-plugins-first','featured-image-column','disable-admin-bar-frontend','hide-admin-notices','footer-timezone','search-visibility-status','last-login-column','registration-date-column','disable-author-archives','external-links-new-tab','custom-excerpt-length','custom-frontend-css','custom-admin-css','auto-publish-missed-schedules','disable-big-image-scaling','disable-self-pingbacks','remove-comment-website-field','email-sender-identity','system-summary','maintenance-mode','head-body-footer-code','generic-login-errors','search-posts-only','disable-texturize','remove-recent-comments-css','login-logout-redirects','woocommerce-wishlist','catalog-mode','whatsapp-enquiry','quantity-rules','product-tabs-control','auto-apply-coupon','thank-you-message','rename-payment-methods','rename-shipping-methods','direct-checkout','checkout-field-controls','direct-checkout-links','url-coupons','checkout-text-customizer','woo-asset-control','cart-fragments-control','my-account-redirects','product-price-history','disable-marketplace-suggestions','coupon-role-restrictions','coupon-maximum-discount','custom-order-statuses','security-headers','redirect-manager','404-tracker','admin-menu-organizer','sanitize-upload-filenames','temporary-login','activity-log-lite','smtp-mailer','mail-log','buy-now',
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
