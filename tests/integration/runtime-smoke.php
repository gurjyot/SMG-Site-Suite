<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}
if(!class_exists('WooCommerce')){fwrite(STDERR,"WooCommerce not loaded\n");exit(1);}

$failures=[];
$assert=static function(bool $condition,string $message)use(&$failures):void{
    if(!$condition)$failures[]=$message;
};

$registry=\SMG\SiteSuite\RegistryFactory::make();
$modules=$registry->all();
$assert(count($modules)>=135,'Registry contains fewer than 135 modules.');

$required=[
    'payment-method-column',
    'order-phone-column',
    'woo-order-notes-column',
    'order-item-summary-column',
    'woo-customer-lifetime-orders',
];
$active=(array)get_option('smg_site_suite_active_modules',[]);
foreach($required as $slug)$assert(in_array($slug,$active,true),"Expected active module: {$slug}");

$assert(has_filter('manage_woocommerce_page_wc-orders_columns')!==false,'HPOS order columns filter not registered.');
$assert(has_action('manage_woocommerce_page_wc-orders_custom_column')!==false,'HPOS order custom-column action not registered.');
$assert(has_filter('manage_edit-shop_order_columns')!==false,'Legacy order columns filter not registered.');

$product=new \WC_Product_Simple();
$product->set_name('SMG Integration Product');
$product->set_regular_price('100');
$product->set_status('publish');
$productId=$product->save();
$assert($productId>0,'Could not create WooCommerce product.');

$order=wc_create_order();
if(is_wp_error($order)){
    $failures[]='Could not create WooCommerce order: '.$order->get_error_message();
}else{
    $order->add_product(wc_get_product($productId),2);
    $order->set_billing_email('integration@example.test');
    $order->calculate_totals();
    $orderId=$order->save();
    $reloaded=wc_get_order($orderId);
    $assert($reloaded instanceof \WC_Order,'Could not reload WooCommerce order through CRUD.');
    if($reloaded instanceof \WC_Order){
        $assert((float)$reloaded->get_total()>0,'Reloaded order total is invalid.');
        $assert(count($reloaded->get_items())===1,'Reloaded order item count is invalid.');
    }
}

if(class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)){
    $hpos=\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    if(getenv('SMG_EXPECT_HPOS')==='1')$assert($hpos===true,'HPOS was expected to be enabled but is not.');
}

wp_delete_post($productId,true);

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite WordPress/WooCommerce integration smoke passed.\n";
