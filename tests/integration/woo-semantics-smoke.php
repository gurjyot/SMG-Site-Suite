<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}
if(!class_exists('WooCommerce')){fwrite(STDERR,"WooCommerce not loaded\n");exit(1);}

$failures=[];
$assert=static function(bool $condition,string $message)use(&$failures):void{
    if(!$condition)$failures[]=$message;
};

if(!WC()->cart)wc_load_cart();
if(!WC()->cart){
    fwrite(STDERR,"FAIL: WooCommerce cart could not be initialized.\n");
    exit(1);
}
WC()->cart->empty_cart();

$product=new WC_Product_Simple();
$product->set_name('SMG Semantic Product');
$product->set_regular_price('100');
$product->set_status('publish');
$productId=$product->save();

$couponId=0;
$autoCouponId=0;
$variableId=0;

try{
    $assert($productId>0,'Could not create WooCommerce semantic-test product.');

    // Quantity Rules: min/max/step semantics.
    $quantity=new \SMG\SiteSuite\Modules\WooCommerce\QuantityRules();
    $quantity->saveSettings(['minimum'=>2,'maximum'=>5,'step'=>2]);
    wc_clear_notices();
    $assert($quantity->validate(true,$productId,2)===true,'Quantity Rules rejected configured minimum.');
    wc_clear_notices();
    $assert($quantity->validate(true,$productId,3)===false,'Quantity Rules accepted an invalid step quantity.');
    wc_clear_notices();
    $assert($quantity->validate(true,$productId,4)===true,'Quantity Rules rejected a valid stepped quantity.');
    wc_clear_notices();
    $assert($quantity->validate(true,$productId,6)===false,'Quantity Rules accepted a quantity above maximum.');
    wc_clear_notices();

    // Direct Checkout: simple products get checkout add-to-cart URLs; non-simple products remain unchanged.
    $direct=new \SMG\SiteSuite\Modules\WooCommerce\DirectCheckout();
    $direct->saveSettings(['enabled'=>1]);
    $simple=wc_get_product($productId);
    $original='https://example.test/product';
    $directUrl=$direct->url($original,$simple);
    $assert(str_contains($directUrl,'add-to-cart='.$productId),'Direct Checkout did not create a simple-product checkout URL.');

    $variable=new WC_Product_Variable();
    $variable->set_name('SMG Variable Product');
    $variable->set_status('publish');
    $variableId=$variable->save();
    $variableLoaded=wc_get_product($variableId);
    $assert($direct->url($original,$variableLoaded)===$original,'Direct Checkout rewrote a variable-product URL.');

    // Build a deterministic cart for amount/COD checks.
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($productId,1);
    WC()->cart->calculate_totals();

    $amountRules=new \SMG\SiteSuite\Modules\WooCommerce\OrderAmountRules();
    $amountRules->saveSettings(['minimum'=>150,'maximum'=>250]);
    $errors=new WP_Error();
    $amountRules->validateStoreApi($errors);
    $assert($errors->has_errors(),'Order Amount Rules did not reject subtotal below minimum.');
    $assert($errors->get_error_code()==='smg_site_suite_minimum','Order Amount Rules returned the wrong minimum error code.');

    $cod=new \SMG\SiteSuite\Modules\WooCommerce\CodRules();
    $cod->saveSettings(['minimum'=>150,'maximum'=>250]);
    $gateways=$cod->gateways(['cod'=>new stdClass(),'dummy'=>new stdClass()]);
    $assert(!isset($gateways['cod']),'COD Rules did not hide COD below minimum.');
    $assert(isset($gateways['dummy']),'COD Rules removed an unrelated payment gateway.');

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($productId,2);
    WC()->cart->calculate_totals();

    $errors=new WP_Error();
    $amountRules->validateStoreApi($errors);
    $assert(!$errors->has_errors(),'Order Amount Rules rejected an in-range subtotal.');
    $gateways=$cod->gateways(['cod'=>new stdClass()]);
    $assert(isset($gateways['cod']),'COD Rules hid COD for an in-range order total.');

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($productId,3);
    WC()->cart->calculate_totals();

    $errors=new WP_Error();
    $amountRules->validateStoreApi($errors);
    $assert($errors->get_error_code()==='smg_site_suite_maximum','Order Amount Rules did not reject subtotal above maximum.');
    $gateways=$cod->gateways(['cod'=>new stdClass()]);
    $assert(!isset($gateways['cod']),'COD Rules did not hide COD above maximum.');

    // Free Shipping Only: preserve all rates if free shipping is unavailable, otherwise return free only.
    $freeOnly=new \SMG\SiteSuite\Modules\WooCommerce\FreeShippingOnly();
    $flat=new WC_Shipping_Rate('flat_rate:1','Flat',50,[],'flat_rate');
    $free=new WC_Shipping_Rate('free_shipping:1','Free',0,[],'free_shipping');
    $rates=$freeOnly->rates(['flat'=>$flat],[]);
    $assert(isset($rates['flat'])&&count($rates)===1,'Free Shipping Only removed paid rates when no free rate existed.');
    $rates=$freeOnly->rates(['flat'=>$flat,'free'=>$free],[]);
    $assert(isset($rates['free'])&&!isset($rates['flat'])&&count($rates)===1,'Free Shipping Only did not prefer the free rate exclusively.');

    // Coupon maximum discount must cap cumulatively across multiple line discounts.
    $coupon=new WC_Coupon();
    $coupon->set_code('smg-semantic-cap');
    $coupon->set_discount_type('percent');
    $coupon->set_amount(50);
    $couponId=$coupon->save();
    update_post_meta($couponId,'_smg_site_suite_coupon_max_discount',40);

    $cap=new \SMG\SiteSuite\Modules\WooCommerce\CouponMaximumDiscount();
    $cap->reset();
    $first=$cap->cap(30.0,100.0,[],true,$coupon);
    $second=$cap->cap(30.0,100.0,[],true,$coupon);
    $assert(abs($first-30.0)<0.001,'Coupon Maximum Discount altered an amount below the remaining cap.');
    $assert(abs($second-10.0)<0.001,'Coupon Maximum Discount did not enforce the cumulative maximum.');

    // Auto Apply Coupon: normalize code and apply it once to a live cart.
    $autoCoupon=new WC_Coupon();
    $autoCoupon->set_code('SMG AUTO');
    $autoCoupon->set_discount_type('fixed_cart');
    $autoCoupon->set_amount(10);
    $autoCouponId=$autoCoupon->save();

    $auto=new \SMG\SiteSuite\Modules\WooCommerce\AutoApplyCoupon();
    $auto->saveSettings(['coupon'=>'SMG AUTO']);
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($productId,1);
    WC()->cart->calculate_totals();
    $auto->apply();
    $assert(WC()->cart->has_discount('smg-auto'),'Auto Apply Coupon did not apply the configured coupon.');

    // Hook-level evidence for Store API support where explicitly implemented.
    $amountRules->register();
    $auto->register();
    $assert(has_action('woocommerce_store_api_cart_errors')!==false,'Order Amount Rules Store API hook is not registered.');
    $assert(has_action('woocommerce_store_api_cart_update_customer_from_request')!==false,'Auto Apply Coupon Store API hook is not registered.');

    if($autoCouponId>0)wp_delete_post($autoCouponId,true);
    if($variableId>0)wp_delete_post($variableId,true);
}finally{
    WC()->cart->empty_cart();
    wc_clear_notices();
    delete_option('smg_site_suite_quantity_rules');
    delete_option('smg_site_suite_direct_checkout');
    delete_option('smg_site_suite_order_amount_rules');
    delete_option('smg_site_suite_cod_rules');
    delete_option('smg_site_suite_auto_coupon');
    if($couponId>0)wp_delete_post($couponId,true);
    if($productId>0)wp_delete_post($productId,true);
}

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite WooCommerce semantic smoke passed.\n";
