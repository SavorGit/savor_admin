<?php
$DB_MASTER_HOST = 'localhost';
$DB_SLAVE_HOST  = 'localhost';
$db_name = 'savoradmin';
$db_user = 'root';
$db_pwd = 'root';

//redis缓存配置
$redis['db1']['0']['host'] = '';
$redis['db1']['0']['port'] = '';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = ''; 
$redis['db1']['1']['port'] = '';
$redis['db1']['1']['isMaster'] = '0';

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
    'OSS_ENDPOINT'    => 'oss-cn-hangzhou.aliyuncs.com',  //注意不要在前面加 http://
    'OSS_TEST_BUCKET' => '219bucket',                     //资源空间,即桶
    'OSS_SYNC_CALLBACK_URL'=>'http://devp.savorx.cn/admin/notify/syncCallback', //上传异步回调地址
    //end
);
return $config_db;


