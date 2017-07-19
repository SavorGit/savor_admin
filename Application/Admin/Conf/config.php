<?php
//系统配置
$route_rules = array(
    '/^content\/(\d{0,10})$/'=>'Client/showcontent?id=:1',
    '/^special\/(\d{0,10})$/'=>'Client/showcontent?id=:1',
);
$config = array(
    'VAR_PAGE'=>'pageNum',
    'SHOW_PAGE_TRACE'=>false,

    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>$route_rules
);
return $config;
