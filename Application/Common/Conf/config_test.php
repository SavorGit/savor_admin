<?php
$DB_MASTER_HOST = '192.168.168.116';
$DB_SLAVE_HOST  = '192.168.168.116';
$db_name = 'cloud';
$db_user = 'phpweb';
$db_pwd = '123456';

//redis缓存配置
$redis['db1']['0']['host'] = '192.168.168.116';
$redis['db1']['0']['port'] = '6380';
$redis['db1']['0']['password'] = '!1QAZ@2WSX';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = '192.168.168.116'; 
$redis['db1']['1']['port'] = '6380';
$redis['db1']['1']['password'] = '!1QAZ@2WSX';
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
    'DB_OSS'=>array(
        'DB_DEPLOY_TYPE' => 1, //数据库主从支持
        'DB_RW_SEPARATE' => true, //读写分离
        'DB_TYPE' => 'mysql',
        'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
        'DB_NAME' => 'oss',
        'DB_USER' => $db_user,
        'DB_PWD' => $db_pwd,
        'DB_PORT' => 3306,
        'DB_CHARSET' => 'UTF8',
        'DB_PREFIX' => 'oss_',
        'DB_DEBUG'  =>  TRUE,
        'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
    ),
    'DB_STATIS'=>array(
        'DB_DEPLOY_TYPE' => 1, //数据库主从支持
        'DB_RW_SEPARATE' => true, //读写分离
        'DB_TYPE' => 'mysql',
        'DB_HOST' => "$DB_MASTER_HOST,$DB_SLAVE_HOST",
        'DB_NAME' => 'statisticses',
        'DB_USER' => $db_user,
        'DB_PWD' => $db_pwd,
        'DB_PORT' => 3306,
        'DB_CHARSET' => 'UTF8',
        'DB_PREFIX' => 'view_',
        'DB_DEBUG'  =>  TRUE,
        'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
    ),
 	'REDIS_CONFIG' => $redis,
    
);

return $config_db;





