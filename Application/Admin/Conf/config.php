<?php
//系统配置
$route_rules = array(
    '/^content\/([\-|0-9][0-9]{0,10})$/'=>'Client/showcontent?id=:1',
    '/^special\/(\d{0,10})$/'=>'Client/showcontent?id=:1',
    '/^specialgroupshow\/(\d{0,10})$/'=>'specialgroupshow/showsp?id=:1',
    '/^dailycontentshow\/(\d{0,10})$/'=>'Dailycontentshow/showday?id=:1',
    '/^rd\/(\S{0,10})$/'=>'Scanqrcode/ads?id=:1',
);
$config = array(
    'VAR_PAGE'=>'pageNum',
    'SHOW_PAGE_TRACE'=>false,

    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>$route_rules
);
return $config;
