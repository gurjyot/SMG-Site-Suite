<?php
if(!defined('ABSPATH')){fwrite(STDERR,"WordPress not loaded\n");exit(1);}
if(!defined('SMG_SITE_SUITE_VERSION')){fwrite(STDERR,"SMG Site Suite not loaded from ZIP\n");exit(1);}
if(!is_plugin_active('smg-site-suite/smg-site-suite.php')){fwrite(STDERR,"SMG Site Suite ZIP install is not active\n");exit(1);}
$registry=\SMG\SiteSuite\RegistryFactory::make();
if(count($registry->all())<135){fwrite(STDERR,"ZIP registry is incomplete\n");exit(1);}
echo "SMG Site Suite ZIP install smoke passed.\n";
