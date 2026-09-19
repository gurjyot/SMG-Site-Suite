<?php
if($argc<2){fwrite(STDERR,"Usage: php package-audit.php build/smg-site-suite.zip\n");exit(2);}

$zipPath=$argv[1];
$zip=new ZipArchive();
if($zip->open($zipPath)!==true){fwrite(STDERR,"Could not open package ZIP.\n");exit(2);}

$failures=[];
$blockedPaths=[
    '#(^|/)\\.git(/|$)#',
    '#(^|/)\\.github(/|$)#',
    '#(^|/)tests?(/|$)#',
    '#(^|/)scripts?(/|$)#',
    '#(^|/)(composer|package)(\\.lock|\\.json)$#',
    '#(^|/)phpcs\\.xml(\\.dist)?$#',
    '#(^|/)phpunit\\.xml(\\.dist)?$#',
    '#(^|/)(reference|references|source-plugins?)(/|$)#i',
];
$secretPatterns=[
    '/(api[_-]?key|secret|token|password)\\s*[=:]\\s*[\\x27"][^\\x27"]{8,}/i',
    '/-----BEGIN (RSA |OPENSSH |EC |DSA )?PRIVATE KEY-----/',
    '#/Users/[^\\s\\x27"]+#',
    '#/home/[^\\s\\x27"]+#',
];

for($i=0;$i<$zip->numFiles;$i++){
    $name=(string)$zip->getNameIndex($i);
    foreach($blockedPaths as $pattern){
        if(preg_match($pattern,$name)){
            $failures[]="Blocked package path: {$name}";
        }
    }

    if(str_ends_with($name,'/'))continue;
    $content=$zip->getFromIndex($i);
    if(!is_string($content))continue;
    foreach($secretPatterns as $pattern){
        if(preg_match($pattern,$content)){
            $failures[]="Sensitive-looking content in: {$name}";
        }
    }
}

$zip->close();

if($failures!==[]){
    foreach(array_unique($failures) as $failure)fwrite(STDERR,"FAIL: {$failure}\n");
    exit(1);
}

echo "SMG Site Suite package audit passed.\n";
