<?php
$DB_MASTER_HOST = 'rm-m5e5ujb814xuex9v6.mysql.rds.aliyuncs.com';
$DB_SLAVE_HOST  = 'rm-m5e5ujb814xuex9v6.mysql.rds.aliyuncs.com';
$db_name = 'savor_duo';
$db_user = 'php_admin';
$db_pwd = 'php_admin_api';

//redis缂撳瓨閰嶇疆
$redis['db1']['0']['host'] = '';
$redis['db1']['0']['port'] = '';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = ''; 
$redis['db1']['1']['port'] = '';
$redis['db1']['1']['isMaster'] = '0';

$config_db =  array(
	'DB_DEPLOY_TYPE' => 1, //鏁版嵁搴撲富浠庢敮鎸?
    'DB_RW_SEPARATE' => true, //璇诲啓鍒嗙
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
    
    //OSSS涓婁紶閰嶇疆
	'OSS_ACCESS_ID'   => 'tnDh4AQqRYbV9mq8',
	'OSS_ACCESS_KEY'  => 'sv8aZCKEJhQ0nwKHj8uEnw3ADwcM24',
	'OSS_HOST'    => 'oss-cn-beijing.aliyuncs.com',  //注意不要在前面加 http://
	'OSS_BUCKET' => 'redian-development',                     //资源空间,即桶
	'OSS_SYNC_CALLBACK_URL'=>'alioss/syncNotify', //上传异步回调地址
        //end
);
return $config_db;


