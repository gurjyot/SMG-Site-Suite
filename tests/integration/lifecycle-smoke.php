<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}
if(!class_exists('WooCommerce')){fwrite(STDERR,"WooCommerce not loaded\n");exit(1);}

if(!function_exists('wp_delete_user'))require_once ABSPATH.'wp-admin/includes/user.php';

use SMG\SiteSuite\RegistryFactory;
use SMG\WPFoundation\Modules\DependencyChecker;
use SMG\WPFoundation\Modules\ModuleManager;
use SMG\WPFoundation\Modules\ModuleStateStore;

$failures=[];
$assert=static function(bool $condition,string $message)use(&$failures):void{
    if(!$condition)$failures[]=$message;
};

$admin=get_user_by('login','admin');
$assert($admin instanceof WP_User,'Integration administrator account not found.');
if(!$admin instanceof WP_User){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}
wp_set_current_user($admin->ID);

$registry=RegistryFactory::make();
$state=new ModuleStateStore('smg_site_suite_active_modules');
$deps=new DependencyChecker();
$manager=new ModuleManager($registry,$state,$deps);

$originalActive=$state->active();
$originalPlugins=(array)get_option('active_plugins',[]);
$originalCheckoutPageId=(int)get_option('woocommerce_checkout_page_id',0);
$createdPageIds=[];
$createdUserIds=[];

try{
    // ModuleManager activation/deactivation on a safe, dependency-free module.
    $manager->activate('reading-time');
    $assert($state->isActive('reading-time'),'ModuleManager did not activate reading-time.');
    $manager->deactivate('reading-time');
    $assert(!$state->isActive('reading-time'),'ModuleManager did not deactivate reading-time.');

    // Dependency detection must fail cleanly when WooCommerce is not active.
    $withoutWoo=array_values(array_diff($originalPlugins,['woocommerce/woocommerce.php']));
    update_option('active_plugins',$withoutWoo,false);
    $status=$manager->status('woocommerce-wishlist');
    $assert($status['known']===true,'Wishlist module missing from registry.');
    $assert($status['available']===false,'Woo dependency unexpectedly reported available.');
    $assert(in_array('plugin:woocommerce/woocommerce.php',(array)$status['missing'],true),'Woo dependency failure was not reported.');
    update_option('active_plugins',$originalPlugins,false);

    // Protected Owner activation should protect the current administrator.
    delete_option('smg_site_suite_protected_owners');
    $manager->activate('protected-owner');
    $assert($state->isActive('protected-owner'),'Protected Owner did not activate.');
    $owners=(array)get_option('smg_site_suite_protected_owners',[]);
    $assert(in_array($admin->ID,array_map('absint',$owners),true),'Protected Owner did not store the activating administrator.');

    $secondId=wp_create_user('smg_integration_admin_'.wp_generate_password(6,false,false),wp_generate_password(24,true,true),'smg-integration-'.wp_generate_password(6,false,false).'@example.test');
    if(is_wp_error($secondId)){
        $failures[]='Could not create secondary administrator: '.$secondId->get_error_message();
    }else{
        $createdUserIds[]=(int)$secondId;
        $second=get_user_by('id',$secondId);
        if($second instanceof WP_User){
            $second->set_role('administrator');
            wp_set_current_user((int)$secondId);
            $blocked=false;
            try{
                $manager->deactivate('protected-owner');
            }catch(Throwable $e){
                $blocked=true;
            }
            $assert($blocked,'Unprotected administrator was allowed to deactivate Protected Owner.');
            $assert($state->isActive('protected-owner'),'Protected Owner state changed after blocked deactivation.');
        }else{
            $failures[]='Secondary administrator could not be loaded.';
        }
    }
    wp_set_current_user($admin->ID);
    $manager->deactivate('protected-owner');
    $assert(!$state->isActive('protected-owner'),'Protected Owner did not deactivate for the protected administrator.');

    // Wishlist activation should create or bind a published Wishlist page.
    delete_option('smg_site_suite_wishlist_page_id');
    $existing=get_page_by_path('wishlist');
    $existingId=$existing instanceof WP_Post?(int)$existing->ID:0;
    $manager->activate('woocommerce-wishlist');
    $wishlistPageId=(int)get_option('smg_site_suite_wishlist_page_id',0);
    $assert($wishlistPageId>0,'Wishlist activation did not store a Wishlist page ID.');
    $assert(get_post_status($wishlistPageId)==='publish','Wishlist page is not published.');
    $assert(str_contains((string)get_post_field('post_content',$wishlistPageId),'[smg_wishlist]'),'Wishlist page does not contain the expected shortcode.');
    if($existingId===0&&$wishlistPageId>0)$createdPageIds[]=$wishlistPageId;
    $manager->deactivate('woocommerce-wishlist');

    // FOMO activation/deactivation should manage exactly its scheduled refresh event.
    wp_clear_scheduled_hook('smg_site_suite_fomo_refresh');
    $manager->activate('fomo-sales-notifications');
    $fomoTimestamp=wp_next_scheduled('smg_site_suite_fomo_refresh');
    $assert(is_int($fomoTimestamp)&&$fomoTimestamp>time(),'FOMO activation did not schedule its refresh event.');
    $manager->deactivate('fomo-sales-notifications');
    $assert(wp_next_scheduled('smg_site_suite_fomo_refresh')===false,'FOMO deactivation left a refresh event scheduled.');

    // Content Expiration should schedule and then cancel its tokenized single event.
    $postId=wp_insert_post([
        'post_title'=>'SMG Integration Expiration',
        'post_content'=>'Lifecycle smoke test',
        'post_status'=>'publish',
        'post_type'=>'post',
    ],true);
    if(is_wp_error($postId)){
        $failures[]='Could not create Content Expiration test post: '.$postId->get_error_message();
    }else{
        $createdPageIds[]=(int)$postId;
        $expiration=new \SMG\SiteSuite\Modules\Content\ContentExpiration();
        $expiration->register();
        $post=get_post($postId);
        $future=(new DateTimeImmutable('now',wp_timezone()))->modify('+2 hours')->format('Y-m-d\TH:i');
        $_POST['smg_site_suite_expiration_nonce']=wp_create_nonce('smg_site_suite_expiration_'.$postId);
        $_POST['smg_expiration_date']=$future;
        $_POST['smg_expiration_action']='draft';
        $expiration->save((int)$postId,$post,true);
        $meta=get_post_meta($postId,'_smg_site_suite_content_expiration',true);
        $token=is_array($meta)?(string)($meta['token']??''):'';
        $assert($token!=='','Content Expiration did not persist a token.');
        $scheduled=$token!==''?wp_next_scheduled('smg_site_suite_content_expire',[(int)$postId,$token]):false;
        $assert(is_int($scheduled)&&$scheduled>time(),'Content Expiration did not schedule its event.');

        $_POST['smg_site_suite_expiration_nonce']=wp_create_nonce('smg_site_suite_expiration_'.$postId);
        $_POST['smg_expiration_date']='';
        $_POST['smg_expiration_action']='draft';
        $expiration->save((int)$postId,$post,true);
        $assert(get_post_meta($postId,'_smg_site_suite_content_expiration',true)==='','Content Expiration did not clear metadata when cancelled.');
        if($token!=='')$assert(wp_next_scheduled('smg_site_suite_content_expire',[(int)$postId,$token])===false,'Content Expiration cancellation left the scheduled event behind.');
        unset($_POST['smg_site_suite_expiration_nonce'],$_POST['smg_expiration_date'],$_POST['smg_expiration_action']);
    }


    // Checkout Blocks guard should warn when a classic-only module is active.
    $checkoutPageId=wp_insert_post([
        'post_title'=>'SMG Integration Checkout',
        'post_content'=>'<!-- wp:woocommerce/checkout /-->',
        'post_status'=>'publish',
        'post_type'=>'page',
    ],true);
    if(is_wp_error($checkoutPageId)){
        $failures[]='Could not create Checkout Blocks compatibility page: '.$checkoutPageId->get_error_message();
    }else{
        $createdPageIds[]=(int)$checkoutPageId;
        update_option('woocommerce_checkout_page_id',(int)$checkoutPageId,false);
        $state->activate('checkout-field-controls');
        $guard=new \SMG\SiteSuite\Admin\CompatibilityGuard($registry,$state);
        ob_start();
        $guard->checkoutBlocksNotice();
        $notice=(string)ob_get_clean();
        $assert(str_contains($notice,'Checkout Field Controls'),'Checkout Blocks compatibility guard did not name the active classic-only module.');
        $state->deactivate('checkout-field-controls');
    }

    // Settings contract should round-trip through ModuleManager and sanitize bounds.
    $dashboardPageId=wp_insert_post([
        'post_title'=>'SMG Integration Dashboard',
        'post_content'=>'Dashboard lifecycle smoke test',
        'post_status'=>'publish',
        'post_type'=>'page',
    ],true);
    if(is_wp_error($dashboardPageId)){
        $failures[]='Could not create Custom Dashboard test page: '.$dashboardPageId->get_error_message();
    }else{
        $createdPageIds[]=(int)$dashboardPageId;
        $dashboard=$manager->settingsModule('custom-dashboard-page');
        $dashboard->saveSettings(['page_id'=>$dashboardPageId,'height'=>99999,'replace'=>1]);
        $settings=$dashboard->settings();
        $assert((int)($settings['page_id']??0)===(int)$dashboardPageId,'Custom Dashboard page setting did not round-trip.');
        $assert((int)($settings['height']??0)===4000,'Custom Dashboard height upper bound was not enforced.');
        $assert(($settings['replace']??false)===true,'Custom Dashboard replace setting did not round-trip.');
    }
}finally{
    update_option('active_plugins',$originalPlugins,false);
    wp_set_current_user($admin->ID);
    wp_clear_scheduled_hook('smg_site_suite_fomo_refresh');
    $state->setActive($originalActive);
    delete_option('smg_site_suite_protected_owners');
    delete_option('smg_site_suite_custom_dashboard');
    foreach($createdUserIds as $userId)wp_delete_user($userId);
    foreach(array_unique($createdPageIds) as $postId)wp_delete_post((int)$postId,true);
    delete_option('smg_site_suite_wishlist_page_id');
    if($originalCheckoutPageId>0)update_option('woocommerce_checkout_page_id',$originalCheckoutPageId,false);else delete_option('woocommerce_checkout_page_id');
}

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite lifecycle integration smoke passed.\n";
