<?php
function __($text,$domain=null){return $text;}
define('SMG_SITE_SUITE_PATH',dirname(__DIR__).'/');
require SMG_SITE_SUITE_PATH.'includes/Autoloader.php';
SMG\SiteSuite\Autoloader::register();

$registry=SMG\SiteSuite\RegistryFactory::make();
$modules=$registry->all();

if(count($modules)!==15)throw new RuntimeException('Expected 15 registered modules, got '.count($modules));
if(count(array_unique(array_keys($modules)))!==count($modules))throw new RuntimeException('Duplicate module slugs detected.');

foreach(['disable-comments','safe-svg-upload','buy-now','shipping-progress','payment-method-column','cod-rules'] as $required){
    if(!isset($modules[$required]))throw new RuntimeException('Required module missing: '.$required);
}

foreach($modules as $slug=>$module){
    if($module->slug()!==$slug)throw new RuntimeException('Registry key mismatch for '.$slug);
    if($module->category()==='woocommerce'){
        $plugins=$module->dependencies()['plugins']??[];
        if(!in_array('woocommerce/woocommerce.php',$plugins,true))throw new RuntimeException('WooCommerce dependency missing for '.$slug);
    }
}

echo "site-suite-registry-ok\n";
