<?php
header("Content-type:text/html;charset=utf-8");
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('SITE_TP_PATH' , __DIR__);
// 定义应用目录
define('APP_PATH', SITE_TP_PATH . '/Application/');
//判断连接配置文件
define('APP_DEBUG',true);
define('APP_STATUS','config_test');

//require_once "xhprof.php";
//XHProf::init("zuches");




require './framework/ThinkPHP/ThinkPHP.php';