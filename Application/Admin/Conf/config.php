<?php
//系统配置
$config = array(
    'VAR_PAGE'=>'pageNum',
    'SHOW_PAGE_TRACE'=>false,
   
   //数据库配置信息
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => '127.0.0.1', // 服务器地址
	'DB_NAME'   => 'savoradmin', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'root', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PARAMS' =>  array(), // 数据库连接参数
	'DB_PREFIX' => 'savor_', // 数据库表前缀 
	'DB_CHARSET'=> 'utf8', // 字符集
	'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志


	/***********OSSS上传配置**********/
	'OSS_ACCESS_ID'   => 'tnDh4AQqRYbV9mq8',
	'OSS_ACCESS_KEY'  => 'sv8aZCKEJhQ0nwKHj8uEnw3ADwcM24',
	'OSS_ENDPOINT'    => 'oss-cn-hangzhou.aliyuncs.com',  //注意不要在前面加 http:// 
	'OSS_TEST_BUCKET' => '219bucket',                     //资源空间,即桶
	'OSS_SYNC_CALLBACK_URL'=>'http://devp.savorx.cn/admin/notify/syncCallback', //上传异步回调地址
	/***********OSSS上传配置**********/
	

);


return $config;
