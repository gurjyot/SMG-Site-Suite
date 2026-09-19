<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}

$active=(array)get_option('smg_site_suite_active_modules',[]);
if($active!==[]){
    fwrite(STDERR,'Expected no active Site Suite modules, got: '.wp_json_encode($active)."\n");
    exit(1);
}

$moduleFiles=[];
foreach(get_included_files() as $file){
    $normalized=str_replace('\\','/',$file);
    if(str_contains($normalized,'/smg-site-suite/src/Modules/'))$moduleFiles[]=$normalized;
}
if($moduleFiles!==[]){
    fwrite(STDERR,"Inactive module implementation files were loaded:\n".implode("\n",$moduleFiles)."\n");
    exit(1);
}

$unexpected=[
    ['filter','manage_woocommerce_page_wc-orders_columns'],
    ['filter','woocommerce_checkout_fields'],
    ['action','woocommerce_thankyou'],
    ['action','template_redirect'],
];
foreach($unexpected as [$type,$hook]){
    $registered=$type==='filter'?has_filter($hook):has_action($hook);
    if($registered!==false){
        fwrite(STDERR,"Unexpected Site Suite representative hook while all modules are inactive: {$hook}\n");
        exit(1);
    }
}

echo "SMG Site Suite inactive-module isolation smoke passed.\n";
