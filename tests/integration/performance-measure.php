<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
$payload=[
    'queries'=>function_exists('get_num_queries')?get_num_queries():null,
    'included_files'=>count(get_included_files()),
    'peak_memory'=>memory_get_peak_usage(true),
    'site_suite_active'=>defined('SMG_SITE_SUITE_VERSION'),
];
echo wp_json_encode($payload)."\n";
