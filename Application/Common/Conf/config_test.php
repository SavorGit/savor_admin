<?php
$DB_MASTER_HOST = '192.168.2.145';
$DB_SLAVE_HOST  = '192.168.2.145';
$db_name = 'cloud';
$db_user = 'phpweb';
$db_pwd = '123456';
/*$DB_MASTER_HOST = '192.168.2.127';
$DB_SLAVE_HOST  = '192.168.2.127';
$db_name = 'cloud';
$db_user = 'phpweb';
$db_pwd = '123456';*/
//redis缓存配置
$redis['db1']['0']['host'] = '192.168.2.145';
$redis['db1']['0']['port'] = '6380';
$redis['db1']['0']['password'] = '!1QAZ@2WSX';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = '192.168.2.145'; 
$redis['db1']['1']['port'] = '6380';
$redis['db1']['1']['password'] = '!1QAZ@2WSX';
$redis['db1']['1']['isMaster'] = '0';
/*$redis['db1']['0']['host'] = 'localhost';
$redis['db1']['0']['port'] = '6379';
$redis['db1']['0']['password'] = '';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = 'localhost';
$redis['db1']['1']['port'] = '6379';
$redis['db1']['1']['password'] = '';
$redis['db1']['1']['isMaster'] = '0';*/


$config_db =  array(
	'DB_DEPLOY_TYPE' => 1, //数据库主从支持
    'DB_RW_SEPARATE' => true, //读写分离
    'DB_TYPE' => 'mysql',
    'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
    'DB_NAME' => $db_name,
    'DB_USER' => $db_user,
    'DB_PWD' => $db_pwd,
    'DB_PORT' => 3306,
    'DB_CHARSET' => 'UTF8',
    'DB_PREFIX' => 'savor_',
    'DB_DEBUG'  =>  TRUE,

 	'REDIS_CONFIG' => $redis,
    
    //OSSS上传配置
	'OSS_ACCESS_ID'   => 'tnDh4AQqRYbV9mq8',
	'OSS_ACCESS_KEY'  => 'sv8aZCKEJhQ0nwKHj8uEnw3ADwcM24',
	//'OSS_HOST'    => 'oss-cn-beijing.aliyuncs.com',  //注意不要在前面加 http://
	'OSS_HOST'=>'devp.oss.littlehotspot.com',
    'OSS_BUCKET' => 'redian-development',                     //资源空间,即桶
	'OSS_HOST_NEW'=> 'devp.oss.rerdian.com',
	'OSS_SYNC_CALLBACK_URL'=>'alioss/syncNotify', //上传异步回调地址
    //end
    'CONTENT_HOST'=>'http://devp.admin.littlehotspot.com/',
);

return $config_db;





