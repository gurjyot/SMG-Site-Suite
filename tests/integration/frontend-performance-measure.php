<?php
if($argc<2){fwrite(STDERR,"Usage: php frontend-performance-measure.php /path/to/wordpress\n");exit(2);}
$root=rtrim((string)$argv[1],'/');
if(!is_file($root.'/wp-load.php')){fwrite(STDERR,"WordPress wp-load.php not found.\n");exit(2);}

$_SERVER['HTTP_HOST']='smg.test';
$_SERVER['SERVER_NAME']='smg.test';
$_SERVER['REQUEST_METHOD']='GET';
$_SERVER['REQUEST_URI']='/';
$_SERVER['SCRIPT_NAME']='/index.php';
$_SERVER['PHP_SELF']='/index.php';

chdir($root);
require $root.'/wp-load.php';

$active=(array)get_option('smg_site_suite_active_modules',[]);
$moduleFiles=[];
foreach(get_included_files() as $file){
    $normalized=str_replace('\\','/',$file);
    if(str_contains($normalized,'/smg-site-suite/src/Modules/'))$moduleFiles[]=$normalized;
}

echo wp_json_encode([
    'queries'=>function_exists('get_num_queries')?get_num_queries():null,
    'included_files'=>count(get_included_files()),
    'module_files'=>count(array_unique($moduleFiles)),
    'peak_memory'=>memory_get_peak_usage(true),
    'active_modules'=>count($active),
    'active_slugs'=>array_values($active),
])."\n";
