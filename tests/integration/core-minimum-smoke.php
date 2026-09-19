<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}

$failures=[];
$assert=static function(bool $condition,string $message)use(&$failures):void{
    if(!$condition)$failures[]=$message;
};

$registry=\SMG\SiteSuite\RegistryFactory::make();
$assert(count($registry->all())>=135,'Registry is incomplete on minimum WordPress.');

$state=new \SMG\WPFoundation\Modules\ModuleStateStore('smg_site_suite_active_modules');
$manager=new \SMG\WPFoundation\Modules\ModuleManager($registry,$state,new \SMG\WPFoundation\Modules\DependencyChecker());

$original=$state->active();
try{
    $manager->activate('reading-time');
    $assert($state->isActive('reading-time'),'Core-only module activation failed on minimum WordPress.');
    $manager->deactivate('reading-time');
    $assert(!$state->isActive('reading-time'),'Core-only module deactivation failed on minimum WordPress.');

    $woo=$manager->status('woocommerce-wishlist');
    $assert($woo['known']===true,'Woo module is missing from registry.');
    $assert($woo['available']===false,'Woo module unexpectedly available without WooCommerce.');
    $assert(in_array('plugin:woocommerce/woocommerce.php',(array)$woo['missing'],true),'Woo dependency failure was not reported.');

    $reading=$manager->settingsModule('reading-time');
    $reading->saveSettings(['words_per_minute'=>250,'label'=>'{{minutes}} minute read']);
    $settings=$reading->settings();
    $assert((int)($settings['words_per_minute']??0)===250,'Settings contract failed on minimum WordPress.');
}finally{
    $state->setActive($original);
    delete_option('smg_site_suite_reading_time');
}

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite minimum WordPress runtime smoke passed.\n";
