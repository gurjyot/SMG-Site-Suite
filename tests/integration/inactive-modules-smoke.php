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

// Do not assert that generic WordPress/WooCommerce hooks are globally empty;
 // core and WooCommerce legitimately use many of the same hook names. The
 // stronger isolation signal is that no Site Suite module implementation file
 // was loaded at all.
echo "SMG Site Suite inactive-module isolation smoke passed.\n";
