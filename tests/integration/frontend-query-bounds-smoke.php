<?php
$root=dirname(__DIR__,2);
$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src/Modules'));
$failures=[];

foreach($files as $file){
    if(!$file->isFile()||$file->getExtension()!=='php')continue;
    $path=$file->getPathname();
    $source=(string)file_get_contents($path);
    $frontend=preg_match("/\\['[^\\]]*(frontend|all)[^\\]]*'\\]/",$source)===1
        ||str_contains($source,'wp_enqueue_scripts')
        ||str_contains($source,'add_shortcode')
        ||str_contains($source,'woocommerce_after_')
        ||str_contains($source,'woocommerce_before_')
        ||str_contains($source,'template_redirect');
    if(!$frontend)continue;

    if(preg_match("/['\"](?:limit|posts_per_page|numberposts)['\"]\\s*=>\\s*-1/",$source)){
        $failures[]=str_replace($root.'/','',$path).' uses an unbounded frontend query limit.';
    }
}

if($failures!==[]){
    foreach($failures as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite frontend query bounds smoke passed.\n";
