<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"Site Suite not loaded\n");exit(1);}

$target=(int)(getenv('SMG_PROFILE_SIZE')?:0);
if(!in_array($target,[0,10,50],true)){
    fwrite(STDERR,"SMG_PROFILE_SIZE must be 0, 10, or 50.\n");
    exit(2);
}

$registry=\SMG\SiteSuite\RegistryFactory::make();
$deps=new \SMG\WPFoundation\Modules\DependencyChecker();
$eligible=[];

foreach($registry->all() as $definition){
    $contexts=$definition->contexts();
    if(!in_array('all',$contexts,true)&&!in_array('frontend',$contexts,true))continue;
    if(in_array($definition->risk(),['high','destructive'],true))continue;
    if(!$deps->check($definition)['available'])continue;
    $eligible[]=$definition->slug();
}

sort($eligible,SORT_STRING);
if(count($eligible)<$target){
    fwrite(STDERR,sprintf("Only %d eligible frontend-safe modules are available; %d requested.\n",count($eligible),$target));
    exit(1);
}

$selected=array_slice($eligible,0,$target);
update_option('smg_site_suite_active_modules',$selected,false);

echo wp_json_encode([
    'target'=>$target,
    'eligible'=>count($eligible),
    'selected'=>$selected,
])."\n";
