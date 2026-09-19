<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!class_exists('WooCommerce')){fwrite(STDERR,"WooCommerce not loaded\n");exit(1);}

$failures=[];
$assert=static function(bool $condition,string $message)use(&$failures):void{
    if(!$condition)$failures[]=$message;
};

if(function_exists('wc_load_cart'))wc_load_cart();
if(!WC()->cart){
    fwrite(STDERR,"FAIL: WooCommerce cart could not be initialized.\n");
    exit(1);
}

WC()->cart->empty_cart();

$product=new \WC_Product_Simple();
$product->set_name('SMG Store API Product');
$product->set_regular_price('100');
$product->set_status('publish');
$productId=$product->save();
$assert($productId>0,'Could not create Store API test product.');

if($productId>0){
    $added=WC()->cart->add_to_cart($productId,1);
    $assert((bool)$added,'Could not add Store API test product to cart.');
}

$orderRules=new \SMG\SiteSuite\Modules\WooCommerce\OrderAmountRules();
$orderRules->saveSettings(['minimum'=>150,'maximum'=>0]);
$errors=new \WP_Error();
$orderRules->validateStoreApi($errors);
$assert($errors->has_errors(),'Order Amount Rules did not add a Store API error for a cart below the minimum.');
$assert(in_array('smg_site_suite_minimum',$errors->get_error_codes(),true),'Order Amount Rules used an unexpected Store API error code.');

$orderRules->saveSettings(['minimum'=>50,'maximum'=>0]);
$errorsOk=new \WP_Error();
$orderRules->validateStoreApi($errorsOk);
$assert(!$errorsOk->has_errors(),'Order Amount Rules rejected a valid Store API cart.');

$coupon=new \WC_Coupon();
$couponCode='smg-store-api-'.wp_generate_password(8,false,false);
$coupon->set_code($couponCode);
$coupon->set_discount_type('fixed_cart');
$coupon->set_amount(10);
$couponId=$coupon->save();
$assert($couponId>0,'Could not create Store API coupon.');

$autoCoupon=new \SMG\SiteSuite\Modules\WooCommerce\AutoApplyCoupon();
$autoCoupon->saveSettings(['coupon'=>$couponCode]);
$autoCoupon->applyStoreApi();
$assert(WC()->cart->has_discount($couponCode),'Auto Apply Coupon did not apply its coupon in Store API server context.');

WC()->cart->empty_cart();
if($productId>0)wp_delete_post($productId,true);
if($couponId>0)wp_delete_post($couponId,true);
delete_option('smg_site_suite_order_amount_rules');
delete_option('smg_site_suite_auto_coupon');

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite Store API server-contract smoke passed.\n";
