<?php
$cwd = getcwd();
chdir(getenv('ZS_APPLICATION_BASE_DIR'));
# [Vendor]
$vendor = zend_deployment_library_path('laraveldemovendor')."/vendor";
exec('ln -sf '.$vendor.' vendor');

# [Assets]
$assets = zend_deployment_library_path('laraveldemoassets')."/assets";
exec('ln -sf '.$assets.' public/assets');

chdir($cwd);
