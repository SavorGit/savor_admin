<?php
$DB_MASTER_HOST = 'rm-2zesat61l9jwsie7so.mysql.rds.aliyuncs.com';
$DB_SLAVE_HOST  = 'rr-2zevja6lfg5718e3ko.mysql.rds.aliyuncs.com';
$db_name = 'cloud';
$db_user = 'php_admin_wirte';
$db_pwd = 'S4Vt2Z8bsXwRKOCt';

//redisç¼“å­˜é…ç½®
$redis['db1']['0']['host'] = '';
$redis['db1']['0']['port'] = '';
$redis['db1']['0']['isMaster'] = '1';
$redis['db1']['1']['host'] = ''; 
$redis['db1']['1']['port'] = '';
$redis['db1']['1']['isMaster'] = '0';

$config_db =  array(
    'DB_DEPLOY_TYPE' => 1, //æ•°æ®åº“ä¸»ä»Žæ”¯æŒ?
    'DB_RW_SEPARATE' => true, //è¯»å†™åˆ†ç¦»
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
    
    //OSSSä¸Šä¼ é…ç½®
    'OSS_ACCESS_ID'   => 'tnDh4AQqRYbV9mq8',
    'OSS_ACCESS_KEY'  => 'sv8aZCKEJhQ0nwKHj8uEnw3ADwcM24',
    'OSS_HOST'    => 'oss-cn-beijing.aliyuncs.com',  //×¢Òâ²»ÒªÔÚÇ°Ãæ¼Ó http://
    'OSS_BUCKET' => 'redian-produce',                     //×ÊÔ´¿Õ¼ä,¼´Í°
    'OSS_SYNC_CALLBACK_URL'=>'alioss/syncNotify', //ÉÏ´«Òì²½»Øµ÷µØÖ·
        //end
);
return $config_db;

