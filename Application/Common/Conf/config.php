<?php
//系统配置
$config = array(
	//路由配置
	'URL_MODEL'				=>2,
	'URL_CASE_INSENSITIVE'  => true, //url支持大小写
	'MODULE_DENY_LIST'      => array('Common','Runtime'), // 禁止访问的模块列表
	'MODULE_ALLOW_LIST'     => array('Admin'), //模块配置
	'DEFAULT_MODULE'        => 'Admin',
	//session cookie配置
	'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
	'SESSION_OPTIONS'       =>  array(), // session 配置数组 支持type name id path expire domain 等参数
	'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
	'SESSION_PREFIX'        =>  'savor_', // session 前缀
	//数据库配置
	'DB_FIELDS_CACHE' 		=> true,
    'DATA_CACHE_TABLE'      =>'savor_datacache',
    //报错页面配置
    'TMPL_ACTION_ERROR'     => 'Public:prompt', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => 'Public:prompt', // 默认成功跳转对应的模板文件
    
	//日志配置
	'LOG_RECORD'            =>  false,   // 默认不记录日志
	'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
	'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
	'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志

    'HTML_FILE_SUFFIX' => '.html',// 默认静态文件后缀
    'HOST_NAME'=>'http://'.$_SERVER['HTTP_HOST'],
    'SITE_NAME'=> '寻味后台管理',
    'SHOW_ERROR_MSG' =>  true, //显示错误信息
    'OSS_ADDR_PATH'=>'media/resource/',

);


if(APP_DEBUG === false){
    $config['TMPL_TRACE_FILE'] = APP_PATH.'Site/View/Public/404.html';   // 页面Trace的模板文件
    $config['TMPL_EXCEPTION_FILE'] = APP_PATH.'Site/View/Public/404.html';// 异常页面的模板文件
}
$config['PWDPRE'] = 'SAVOR@&^2017^2030&*^';
$config['NUMPERPAGE'] = array('50','100','200','500');
$config['MANGER_STATUS'] = array(
    '1'=>'启用',
    '2'=>'禁用'
);
$config['MANGER_LEVEL'] = array(
    '0'=>'一级栏目',
    '1'=>'二级栏目'
);

$config['MANGER_STATE'] = array(
    '2'=>'审核通过',
    '3'=>'审核不通过'
);

$config['RESOURCE_TYPE'] = array(
    '1'=>'视频',
    '2'=>'图片',
    '3'=>'其他',
);
$config['ADS_TYPE'] = array(
    '1'=>'广告',
    '2'=>'节目',
    '3'=>'宣传片',
);
$config['RESOURCE_TYPEINFO'] = array(
    'mp4'=>1,
    'mov'=>1,
    'jpg'=>2,
    'gif'=>2,
    'png'=>2,
    'bmp'=>2,
);
$config['HOTEL_LEVEL'] = array(
    '3'=>'3A',
    '4'=>'4A',
    '5'=>'5A',
    '6'=>'6A',
);

$config['HOTEL_STATE'] = array(
    '1'=>'正常',
    '2'=>'冻结',
    '3'=>'报损',
);

$config['HOTEL_KEY'] = array(
    '1'=>'重点',
    '2'=>'非重点',
);

$config['MANGER_KEY'] = array(
    'colum'=>'栏目节点',
    'cms'=>'程序节点',
    'system'=>'系统节点',
    'send' =>'内容节点',
    'version'=>'版本节点',
    'menu' =>'节目节点',
    'ad' =>'广告节点',
    'hotel' =>'酒楼节点',
);
$config['MOBILE_TYPE'] = array(
    '1' => array('id'=>1, 't'=>'Iphone 4', 'w'=>'320', 'h'=>'480'),
    '2' => array('id'=>2, 't'=>'Iphone 5', 'w'=>'320', 'h'=>'568'),
    '3' => array('id'=>3, 't'=>'Iphone 6', 'w'=>'375', 'h'=>'667'),
    '4' => array('id'=>4, 't'=>'Iphone 6 Plus', 'w'=>'414', 'h'=>'736'),
    '5' => array('id'=>5, 't'=>'Ipad Mini', 'w'=>'768', 'h'=>'1024'),
    '6' => array('id'=>6, 't'=>'Ipad', 'w'=>'768', 'h'=>'1024'),
    '7' => array('id'=>7, 't'=>'Galaxy S5', 'w'=>'360', 'h'=>'640'),
    '8' => array('id'=>8, 't'=>'Nexus 5X', 'w'=>'411', 'h'=>'731'),
    '9' => array('id'=>9, 't'=>'Nexus 6P', 'w'=>'435', 'h'=>'773'),
    '10' => array('id'=>10, 't'=>'Laptop MDPI', 'w'=>'1280', 'h'=>'800'),
    '11' => array('id'=>11, 't'=>'Laptop HiDPI', 'w'=>'1440', 'h'=>'900'),
);
return $config;
