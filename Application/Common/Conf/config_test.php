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
    
    //OSSS上传配置
	'OSS_ACCESS_ID'   => 'LTAITjXOpRHKflOX',
	'OSS_ACCESS_KEY'  => 'Q1t8XSK8q82H3s8jaLq9NqWx7Jsgkt',
	'OSS_HOST'    => 'oss-cn-beijing.aliyuncs.com',  //注意不要在前面加 http://
	//'OSS_HOST'=>'devp.oss.littlehotspot.com',
    'OSS_BUCKET' => 'redian-development',                     //资源空间,即桶
	'OSS_HOST_NEW'=> 'dev-oss.littlehotspot.com',
	'OSS_SYNC_CALLBACK_URL'=>'alioss/syncNotify', //上传异步回调地址
    'QUEUE_ENDPOINT'=>'https://1379506082945137.mns.cn-beijing.aliyuncs.com',
    'TOPIC_NAME'=>'test-topic',

    'UMENG_PRODUCTION_MODE'=>'false',
    'SAVOR_API_URL'=>'dev-mobile.littlehotspot.com',
    //end
    'CONTENT_HOST'=>'http://devp.admin.littlehotspot.com/',
    'SHORT_URL'=>'http://devp.admin.littlehotspot.com',
    'NETTY_BALANCE_URL'=>'https://dev-api-nzb.littlehotspot.com/netty/position',
    'SEND_MAIL_CONF'=>array(
                            'littlehotspot'=>array(
                                'host'=>'smtp.savor.cn',
                                'username'=>'sysreport@littlehotspot.com',
                                'password'=>'savor123456',
                                'port'=>25,
                                'tomail'=>array('xie.meng@littlehotspot.com',
                                                'tang.mimi@littlehotspot.com',
                                                'zhang.yingtao@littlehotspot.com',
                                ),  
                            )
                        )
    
);

return $config_db;





