<?php
header("content-type:text/html;charset=utf-8");
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('APP_DEBUG',True);
define('APP_PATH','./Application/');
set_time_limit(0);
require './ThinkPHP/ThinkPHP.php';