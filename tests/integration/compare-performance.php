<?php
if($argc<3){fwrite(STDERR,"Usage: php compare-performance.php baseline.json suite.json\n");exit(2);}
$baseline=json_decode(trim((string)file_get_contents($argv[1])),true);
$suite=json_decode(trim((string)file_get_contents($argv[2])),true);
if(!is_array($baseline)||!is_array($suite)){fwrite(STDERR,"Invalid performance JSON\n");exit(2);}

$queryDelta=(int)$suite['queries']-(int)$baseline['queries'];
$fileDelta=(int)$suite['included_files']-(int)$baseline['included_files'];
$memoryDelta=(int)$suite['peak_memory']-(int)$baseline['peak_memory'];

printf("Baseline queries: %d\n",(int)$baseline['queries']);
printf("Site Suite queries: %d (delta %+d)\n",(int)$suite['queries'],$queryDelta);
printf("Baseline included files: %d\n",(int)$baseline['included_files']);
printf("Site Suite included files: %d (delta %+d)\n",(int)$suite['included_files'],$fileDelta);
printf("Peak memory delta: %+d bytes\n",$memoryDelta);

$fail=[];
if($queryDelta>6)$fail[]="Zero-module bootstrap query delta exceeds 6.";
if($fileDelta>25)$fail[]="Zero-module included-file delta exceeds 25.";
if($memoryDelta>8*1024*1024)$fail[]="Zero-module peak-memory delta exceeds 8 MiB.";

if($fail){
    foreach($fail as $message)fwrite(STDERR,"FAIL: {$message}\n");
    exit(1);
}
echo "SMG Site Suite zero-module performance budget passed.\n";
