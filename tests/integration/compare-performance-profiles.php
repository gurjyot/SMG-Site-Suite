<?php
if($argc<4){fwrite(STDERR,"Usage: php compare-performance-profiles.php zero.json ten.json fifty.json\n");exit(2);}
$load=static function(string $path):array{
    $data=json_decode(trim((string)file_get_contents($path)),true);
    if(!is_array($data))throw new RuntimeException("Invalid performance JSON: {$path}");
    return $data;
};
try{
    $zero=$load($argv[1]);$ten=$load($argv[2]);$fifty=$load($argv[3]);
}catch(Throwable $e){
    fwrite(STDERR,$e->getMessage()."\n");exit(2);
}

$fail=[];
foreach([[0,$zero],[10,$ten],[50,$fifty]] as [$expected,$profile]){
    if((int)($profile['active_modules']??-1)!==$expected)$fail[]="Profile {$expected} did not load exactly {$expected} active modules.";
    if($expected>0&&(int)($profile['module_files']??0)<$expected)$fail[]="Profile {$expected} loaded fewer module implementation files than active modules.";
}
if((int)$ten['module_files']<(int)$zero['module_files'])$fail[]='10-module profile loaded fewer module files than zero-module profile.';
if((int)$fifty['module_files']<(int)$ten['module_files'])$fail[]='50-module profile loaded fewer module files than 10-module profile.';

$delta=static fn(array $a,array $b,string $key):(int)=>((int)$b[$key])-((int)$a[$key]);
printf("Zero: queries=%d files=%d module_files=%d peak_memory=%d\n",$zero['queries'],$zero['included_files'],$zero['module_files'],$zero['peak_memory']);
printf("10 modules: queries=%d (%+d) files=%d (%+d) module_files=%d peak_memory=%d (%+d)\n",
    $ten['queries'],$delta($zero,$ten,'queries'),$ten['included_files'],$delta($zero,$ten,'included_files'),$ten['module_files'],$ten['peak_memory'],$delta($zero,$ten,'peak_memory'));
printf("50 modules: queries=%d (%+d) files=%d (%+d) module_files=%d peak_memory=%d (%+d)\n",
    $fifty['queries'],$delta($zero,$fifty,'queries'),$fifty['included_files'],$delta($zero,$fifty,'included_files'),$fifty['module_files'],$fifty['peak_memory'],$delta($zero,$fifty,'peak_memory'));

if($delta($zero,$ten,'queries')>30)$fail[]='10-module frontend profile adds more than 30 bootstrap queries.';
if($delta($zero,$fifty,'queries')>120)$fail[]='50-module frontend profile adds more than 120 bootstrap queries.';
if($delta($zero,$ten,'peak_memory')>16*1024*1024)$fail[]='10-module frontend profile adds more than 16 MiB peak memory.';
if($delta($zero,$fifty,'peak_memory')>40*1024*1024)$fail[]='50-module frontend profile adds more than 40 MiB peak memory.';

if($fail!==[]){
    foreach($fail as $message)fwrite(STDERR,"FAIL: {$message}\n");
    exit(1);
}
echo "SMG Site Suite frontend performance profiles passed.\n";
