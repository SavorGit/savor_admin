<?php
//系统配置 
$config = array(
    //路由配置
    'URL_MODEL'				=>2,
    'URL_CASE_INSENSITIVE'  => true, //url支持大小写
    'MODULE_DENY_LIST'      => array('Common','Runtime'), // 禁止访问的模块列表
    'MODULE_ALLOW_LIST'     => array('Admin','H5','Smallapp','Dataexport','Integral'), //模块配置
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
    'TMPL_ACTION_ERROR'     => APP_PATH.'Admin/View/Public/prompt.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => APP_PATH.'Admin/View/Public/prompt.html', // 默认成功跳转对应的模板文件

    //日志配置
    'LOG_RECORD'            =>  false,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志
    //缓存目录配置
    'MINIFY_CACHE_PATH'=>APP_PATH.'Runtime/Cache',
    'HTML_FILE_SUFFIX' => '.html',// 默认静态文件后缀
    'HOST_NAME'=>'http://'.$_SERVER['HTTP_HOST'],
    'HTTPS_HOST_NAME'=>'https://'.$_SERVER['HTTP_HOST'],
    'SITE_NAME'=> '寻味后台管理',
    'SHOW_ERROR_MSG' =>  true, //显示错误信息
    'OSS_ADDR_PATH'=>'media/resource/',
	'SECRET_KEY' => 'sw&a-lvd0onr!',//解密接口数据key
	'API_SECRET_KEY' => 'w&-ld0n!',//解密接口数据key
	'SHOW_URL_APP_KEY'=>'258257010', //新浪短链接appkey
	'BAIDU_GEO_KEY'=>'q1pQnjOG28z8xsCaoby2oqLTLaPgelyq',
    'HASH_IDS_KEY'=>'Q1xsCaoby2o',
    'HASH_IDS_KEY_ADMIN'=>'Q1xsCaoby2o',

    'SAPP_CALL_NETY_CMD'=>'call-mini-program',
    'SAPP_SALE'=>'smallappsale:',
    'SAPP_OPS'=>'smallappops:',
    'SAPP_SALE_ACTIVITYGOODS_PROGRAM'=>'smallappsale:activitygoodsprogram',
    'SAPP_SHOP_PROGRAM'=>'smallapp:shopprogram',
    'SAPP_SALE_WELCOME_RESOURCE'=>'smallappsale:welcomeresource',
    'SAPP_SALE_ACTIVITY_PROMOTE'=>'smallappsale:activitypromote:',
    'SAPP_FIND_TOP'=>'smallapp:findtop',
    'SAPP_HOTPLAY_PRONUM'=>'smallapp:hotplaypronum',
    'SAPP_FORSCREENTRACK'=>'smallapp:trackforscreen:',
    'SAPP_PRIZEPOOL'=>'smallapp:prizepool:',
    'SAPP_PRIZEPOOL_MONEYQUEUE'=>'smallapp:prizepool:moneyqueue:',
    'FEAST_TIME'=>array('lunch'=>array('11:30','14:30'),'dinner'=>array('18:00','21:00')),
    'SALEFEAST_TIME'=>array('lunch'=>array('11:00','14:00'),'dinner'=>array('16:45','21:00')),
    'SAPP_CANCEL_FORSCREEN'=>'smallapp:cancelforscreen:',
    'MEAL_TIME'=>array('lunch'=>array('10:00','15:00'),'dinner'=>array('17:00','23:59')),
    'SCAN_QRCODE_TYPES'=>array(1,2,3,5,6,7,8,9,10,11,12,13,15,16,19,20,21,29,30),
//     scan_qrcode_type 1:小码2:大码(节目)3:手机小程序呼码5:大码（新节目）6:极简版7:主干版桌牌码8:小程序二维码9:极简版节目大码
//     10:极简版大码11:极简版呼玛12:大二维码（节目）13:小程序呼二维码 15:大二维码（新节目）16：极简版二维码19:极简版节目大二维码
//     20:极简版大二维码21:极简版呼二维码22购物二维码 23销售二维码 24菜品商家 25单个菜品 26海报分销售卖商品 27 商城商家 28商城商品大屏购买
//     29推广渠道投屏码 30投屏帮助视频 31活动霸王菜


);
if(APP_DEBUG === false){
    $config['TMPL_TRACE_FILE'] = APP_PATH.'Site/View/Public/404.html';   // 页面Trace的模板文件
    $config['TMPL_EXCEPTION_FILE'] = APP_PATH.'Site/View/Public/404.html';// 异常页面的模板文件
}

$config['GOODS_TYPE'] = array(
    '10'=>'热点优选',
    '11'=>'热点商品',
    '20'=>'商家添加',
    '30'=>'积分兑换现金',
//    '31'=>'积分兑换物品',
    '40'=>'秒杀商品',
);

$config['GOODS_SCOPE'] = array(
    '0'=>'全部',
    '1'=>'包间',
    '2'=>'非包间',
);

$config['DISH_STATUS'] = array(
    '1'=>'上架',
    '2'=>'下架',
);
$config['DISH_TYPE'] = array(
    '21'=>'外卖',
    '22'=>'售全国',
    '23'=>'赠送商品',
    '42'=>'团购商品',
    '40'=>'秒杀商品',
    '43'=>'本店有售商品',
    '44'=>'线上团购商品',
);
$config['DISH_FLAG'] = array(
    '1'=>'审核中',
    '2'=>'审核通过',
    '3'=>'审核不通过'
);
$config['DISH_ORDERSTATUS'] = array(
    '1'=>'待处理',
    '2'=>'已完成',
    '3'=>'待发货',
);

$config['ORDER_ALLSTATUS'] = array(
    '1'=>'待处理',
    '2'=>'已完成',
    '10'=>'已下单',
    '11'=>'支付失败',
    '12'=>'支付成功',
    '13'=>'待商家确认',
    '14'=>'待骑手接单',
    '15'=>'待取货',
    '16'=>'配送中',
    '17'=>'已完成',
    '18'=>'商家取消',
    '19'=>'用户取消',
    '51'=>'待处理',
    '52'=>'待发货',
    '53'=>'已派送',
    '61'=>'赠送中',
    '62'=>'已过期',
    '63'=>'获赠',

);

$config['GOODS_STATUS'] = array(
    '1'=>'未审核',
    '2'=>'审核通过',
    '3'=>'审核不通过',
    '4'=>'下架',
    '5'=>'已过期',
);

$config['ORDER_STATUS'] = array(
    '10'=>'已下单',
    '11'=>'支付失败',
    '12'=>'支付成功',
);
$config['EXCHANGE_STATUS'] = array(
    '20'=>'申请兑换',
    '21'=>'兑换成功',
);

$config['INVOICE_STATUS'] = array(
    '1'=>'暂不开票',
    '2'=>'已提申请',
    '3'=>'待开发票',
    '4'=>'已开发票',
);

$config['INVOICE_TYPE'] = array(
    '1'=>'纸质发票',
    '2'=>'电子发票',
);

$config['BUY_TYPE'] = array(
    '1'=>'店内购买',
    '2'=>'京东购买',
);
$config['ORDER_OTYPE'] = array(
    '1'=>'商品订单',
    '2'=>'兑换订单',
);

$config['MERCHANT_TYPE'] = array(
    '1'=>'合作酒楼商家',
    '2'=>'非合作酒楼商家',
);

$config['DEVICE_TYPE'] = array(
    '1'=>'小平台',
    '2'=>'机顶盒',
    '3'=>'android',
    '4'=>'ios',
    '5'=>'餐厅端_android',
    '6'=>'餐厅端_ios',
    '7'=>'运维端_android',
    '8'=>'运维端_ios',
    '9'=>'运维-单机版_android',
    '10'=>'运维-单机版_ios',
    '21'=>'广告机',
);
$config['UPDATE_TYPE'] = array(
    '0'=>'手动更新',
    '1'=>'强制更新',
);
$config['RESOURCE_TYPE'] = array(
    '1'=>'视频',
    '2'=>'图片',
    '3'=>'其他',
    '4'=>'音频',
    '5'=>'字体',
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
    'png'=>2,
    'gif'=>2,
    'jpeg'=>2,
    'bmp'=>2,
    'wma'=>4,
    'mp3'=>4,
    'ttf'=>5,
);
$config['HOTEL_LEVEL'] = array(
    '3'=>'3A',
    '4'=>'4A',
    '5'=>'5A',
    '6'=>'6A',
);
$config['CONTENT_TYPE'] = array(
    '1'=>'图文',
    '3'=>'视频',
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

$config['PWDPRE'] = 'SAVOR@&^2017^2030&*^';
$config['NUMPERPAGE'] = array('50','100','200','500','800','1000','2000');
$config['MANGER_STATUS'] = array(
    '1'=>'启用',
    '2'=>'禁用'
);
$config['MANGER_LEVEL'] = array(
    '0'=>'一级栏目',
    '1'=>'二级栏目',
    '2'=>'三级栏目'
);

$config['MANGER_STATE'] = array(
    '0'=>'未审核',
    '2'=>'审核通过',
    '3'=>'审核不通过',
);
$config['MANGER_KEY'] = array(
    'colum'=>'版本管理节点',
    'cms'=>'程序节点',
    'system'=>'系统节点',
    'send' =>'内容节点',
    'version'=>'版本更新节点',
    'menu' =>'节目节点',
    'ad' =>'广告节点',
    'hotel' =>'酒楼节点',
    'report'=>'报表节点',
    'testreport'=>'测试报表节点',
    'checkaccount'=>'对账系统节点',
    'dailycontent'=>'每日知享节点',
    'newmenu'=>'新节目节点',
    'advdelivery'=>'广告投放节点',
	'option'=>'运维客户端',
    'installoffer'=>'网络设备报价',
    'smallapp'=>'小程序数据统计节点',
    'miniprogram'=>'小程序管理',
    'integral'=>'积分系统',

);
$config['STATE_REASON'] = array(
    '1'=>'正常',
    '2'=>'倒闭',
    '3'=>'装修',
    '4'=>'淘汰',
    '5'=>'放假',
    '6'=>'易主',
    '7'=>'终止合作',
    '8'=>'问题沟通中',
);
$config['hotel_box_type'] = array(
    '1'=>'一代单机版',
    '2'=>'二代网络版',
    '3'=>'二代5G版',
    //U盘更新
    '4'=>'二代单机版',
    '5'=>'三代单机版',
    '6'=>'三代网络版',
    '7'=>'互联网电视机',
);
$config['heart_hotel_box_type'] = array(
    '2'=>'二代网络版',
    '3'=>'二代5G版',
    '6'=>'三代网络版',
    '7'=>'互联网电视机',
);
$config['all_smallapps'] = array(
    '1'=>'普通版',
    '2'=>'极简版',
    '3'=>'极简版',
    '4'=>'餐厅端',
    '5'=>'销售端',
    '11'=>'h5互动游戏'
);
$config['all_forscreen_actions'] = array(
//    '0'=>'图片投屏',
    '2-1'=>'滑动',
    '2-2'=>'视频投屏',
    '3'=>'切片视频投屏',
    '4'=>'图片投屏',
    '5'=>'节目点播',
    '6'=>'广告跳转',
    '7'=>'点击互动游戏',
    '8'=>'重投',
    '9'=>'手机呼大码',
    '11'=>'发现点播图片',
    '12'=>'发现点播视频',
    '13'=>'点播商城商品',
    '14'=>'点播banner商城商品',
//    '15'=>'点播本地生活店铺视频',
    '16'=>'热播内容点播图片',
    '17'=>'热播内容点播视频',
    '21'=>'查看点播视频',
    '22'=>'查看发现视频',
    '30'=>'投屏文件',
    '31'=>'投屏文件图片',
//    '40'=>'投销售端商品',
//    '41'=>'投屏欢迎词',
    '42'=>'用户端投欢迎词',
//    '43'=>'生日聚会欢迎词',
//    '44'=>'分享文件到电视',
//    '45'=>'分享名片到电视',
//    '32'=>'商务宴请投屏文件图片',
//    '46'=>'商务宴请图片投屏',
//    '47'=>'商务宴请视频投屏',
//    '48'=>'生日聚会图片投屏',
//    '49'=>'生日聚会视频投屏',
//    '50'=>'助力好友',
    '51'=>'扫码抢霸王餐',
    '52'=>'评论',
    '53'=>'点击banner抢霸王餐',
    '54'=>'扫码抽奖',
//    '55'=>'首页致欢迎词',
    '56'=>'生日点播',
    '57'=>'星座点播',
    '58'=>'销售端酒品广告',
    '101'=>'h5互动游戏',
    '120'=>'发红包',
    '121'=>'扫码抢红包'
);
$config['COMMENT_SATISFACTION'] = array(
    '1'=>'很糟糕',
    '2'=>'一般般',
    '3'=>'太赞了',
);

$config['SAPP_QRCODE_TYPE_ARR'] = array(
    array('id'=>'1','name'=>'主干版小码'),
    array('id'=>'2','name'=>'节目大码'),
    array('id'=>'3','name'=>'呼码'),
    array('id'=>'5','name'=>'新节目大码'),
    array('id'=>'6','name'=>'极简版小码'),
    array('id'=>'7','name'=>'主干版桌牌码'),
    array('id'=>'8','name'=>'主干版二维码'),
    array('id'=>'9','name'=>'极简版节目大码'),
    array('id'=>'10','name'=>'极简版大码'),
    array('id'=>'11','name'=>"极简版呼码"),
    array('id'=>'12','name'=>'主干版节目大二维码'),
    array('id'=>'13','name'=>'主干版呼二维码'),
    array('id'=>'15','name'=>'新节目大二维码'),
    array('id'=>'16','name'=>'极简版二维码'),
    array('id'=>'19','name'=>'极简版新节目大二维码'),
    array('id'=>'20','name'=>'极简版节目大二维码'),
    array('id'=>'21','name'=>'极简版呼二维码'),
    array('id'=>'33','name'=>'主干版公众号二维码')
);
$config['PUBLIC_PLAY_PROMPTLY']=array(
    3=>'连续播放3次(每次间隔5分钟)',
    5=>'连续播放5次(每次间隔5分钟)',
);
$config['HOTEL_KM'] = array(
    10001=>array('id'=>10001,'name'=>'1km','km'=>'1'),
    10003=>array('id'=>10003,'name'=>'3km','km'=>'3'),
    10005=>array('id'=>10005,'name'=>'5km','km'=>'5'),
    10010=>array('id'=>10010,'name'=>'10km','km'=>'10'),
    10020=>array('id'=>10020,'name'=>'20km','km'=>'20'),
    10050=>array('id'=>10050,'name'=>'50km','km'=>'50'),
);
$config['all_spotstatus'] = array(
    '1'=>'现场',
    '2'=>'非现场',
);
$config['source_type'] = array(
    '1'=>'官网',
    '2'=>'客户端二维码',
    '3'=>'客户端分享',
    '4'=>'节目',
    '5'=>'服务员扫码',
);
$config['hot_play_types'] = array(
    '1'=>'用户内容',
//    '2'=>'广告',
//    '3'=>'节目',
);
$config['fee_type'] = array(
    '1'=>'开机费',
    '2'=>'APP推广',
);
$config['SAPP_ADSPOSITION'] = array(
    '1'=>'首页顶部',
    '2'=>'互动页顶部',
    '3'=>'互动页中部',
    '4'=>'本地生活中部'
);

$config['NOTICE_STATAE'] = array(
    '1'=>'',
    '2'=>'发送失败(酒楼不存在)',
    '3'=>'发送失败(酒楼费用已经下发)',
    '4'=>'发送失败(对账单联系人电话为空)',
    '5'=>'发送失败(酒楼下发金额为负值)',
    '6'=>'发送失败(EXCEL表中已经存在酒楼)',
    '7'=>'发送失败(酒楼下发金额为空值)',
);
$config['CHECK_STATAE'] = array(
    '0'=>'未读',
    '1'=>'已读',
    '2'=>'已确认',
    '3'=>'已付款',
    '99'=>'未读',
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
$config['SMS_CONFIG'] = array(
    'accountsid'=>'6a929755afeded257916ca68518ec1c3',
    'token'     =>'66edd50a46c882a7f4231186c44416d8',
    'appid'     =>'a982fdb55a2441899f2eaa64640477c0',
    'bill_templateid'=>'76285',
    'payment_templateid'=>'78145',
    'vcode_templateid'=>'107496',
    //'notice_templateid'=>'107928',
    'notice_templateid'=>'146776',
);
$config['ACTIVITY_SOURCE_ARR'] = array(
    '1'=>'App',
    '2'=>'App推送',  
    '3'=>'微信客户端',
    '4'=>'微信公众号',
);
$config['SMALL_WARN'] = array(
    '1'=>'未处理',
    '2'=>'已处理',
);

$config['SP_GR_STATE'] = array(
    '0'=>'未发布',
    '1'=>'已发布',
    '2'=>'已删除',
);

$config['WX_DYH_CONFIG'] = array(
    'appid'=>'wxb19f976865ae9404',
    'appsecret'=>'977d15e1ce3c342c123ae6f30bcfeb48',
);

$config['WX_FWH_CONFIG'] = array(
    'appid'=>'wx7036d73746ff1a14',
    'appsecret'=>'8b658fc90d7105d5cf66cb2193edb7d4',
    'key_ticket'=>'savor_wx_xiaorefu_jsticket',
    'key_token'=>'savor_wx_xiaorefu_token',
);
$config['WX_MP_CONFIG'] = array(
    'cache_key'=>'wxmp',
    'appid'=>'wxcb1e088545260931',
    'appsecret'=>'9f1ebb78d1dc7afe73dcb22a135cfcf9'
);

$config['SMALLAPP_CONFIG'] = array(
    'cache_key'=>'smallapp_token',
    'appid'=>'wxfdf0346934bb672f',
    'appsecret'=>'b9b93aef8d6609722596e35385ff05c5'
);

$config['SMALLAPP_SALE_CONFIG'] = array(
    'cache_key'=>'smallapp_sale_token',
    'appid'=>'wxfc48bdfa3fcaf358',
    'appsecret'=>'8fe57f640a23cc3ecfb3d5f8fff70144'
);

$config['XIAO_REDIAN_DING'] = array(
    'appid'=>'wxb19f976865ae9404',
    'appsecret'=>'977d15e1ce3c342c123ae6f30bcfeb48',
    'key_ticket'=>'savor_wx_xiaore_jsticket',
    'key_token'=>'savor_wx_xiaore_token',
);

$config['ZHI_XIANG_CONFIG'] = array(
    'appid'=>'wx75025eb1e60df2cf',
    'appsecret'=>'32427ebb0caae2d9e76747fed56e2071',
    'key_ticket'=>'savor_wx_zhixiang_jsticket',
    'key_token'=>'savor_wx_zhixiang_token',
    'cardapi_ticket'=>'savor_wx_zhixiang_cardapiticket',
    'token'=>'savor',
);

$config['UMENT_API_CONFIG'] = array(
     'API_URL'=>'http://msg.umeng.com/api/send',
     'opclient'=>array(
         'AppKey'=>'59acb7f0f29d98425d000cfa',
         'App_Master_Secret'=>'75h0agzaqlibje6t2rtph4uuuocjyfse',
         'ios_AppKey'=>'59b1260a734be41803000022',
         'ios_App_Master_Secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
     ),
);

$config['SMALLAPP_CONFIG'] = array(
    'cache_key'=>'smallapp_token',
    'appid'=>'wxfdf0346934bb672f',
    'appsecret'=>'b9b93aef8d6609722596e35385ff05c5'
);

$config['UMENBAI_API_CONFIG'] = array(
    'API_URL'=>'http://msg.umeng.com/api/send',
    'opclient'=>array(
        'android_appkey'=>'59acb7f0f29d98425d000cfa',
        'android_master_secret'=>'75h0agzaqlibje6t2rtph4uuuocjyfse',
        'ios_appkey'=>'59b1260a734be41803000022',
        'ios_master_secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
    ),
    'boxclient'=>array(
        'android_appkey'=>'58576b54677baa3b41000809',
        'android_master_secret'=>'v6fr959wpmczeayq34utymxcm7fizufu',
        //'ios_appkey'=>'59b1260a734be41803000022',
        //'ios_master_secret' =>'wgyklqy5uu8dacj9yartpic9xmpkezs4',
    ),
);
$config['WXAPPIDS'] = array(
    'wx13e41a437b8a1d2e'=>'京东爆款',
    'wxf96ad76f27597d65'=>'故宫书店',
    'wx91d27dbf599dff74'=>'京东购物',
    'wx52af38651932e8d3'=>'赖茅',
);
//推送通知的后续行为必填值
$config['AFTER_APP'] = array(
    0=>"go_app",
    1=>"go_url",
    2=>"go_activity",
    3=>"go_custom",
);
$config['REDPACKET_SENDTYPES'] = array(
    '1'=>'立即发送',
    '2'=>'单次定时',
    '3'=>'多次定时'
);
$config['TASK_CATEGORY'] = array(
    'meal'=>'互动饭局数',
    'interact'=>'互动数',
    'comment'=>'点评数',
    'lottery'=>'邀请抽奖'
);
$config['REDPACKET_OPERATIONERID'] = 42996;
$config['REDPACKET_SENDERS'] = array(
    '0'=>array('id'=>0,'nickName'=>'随机'),
    '1'=>array('id'=>1,'nickName'=>'夏日⊕樱花'),
    '2'=>array('id'=>2,'nickName'=>'夏至*初晴'),
    '3'=>array('id'=>3,'nickName'=>'墨Se天空'),
    '4'=>array('id'=>4,'nickName'=>'静待花开'),
    '5'=>array('id'=>5,'nickName'=>'风掠幽蓝'),
    '6'=>array('id'=>6,'nickName'=>'曙光女神'),
    '7'=>array('id'=>7,'nickName'=>'青丝缠雪'),
    '8'=>array('id'=>8,'nickName'=>'鹿港小镇'),
    '9'=>array('id'=>9,'nickName'=>'何必远方'),
    '10'=>array('id'=>10,'nickName'=>'人生百味'),
    '11'=>array('id'=>11,'nickName'=>'落花入盏'),
    '12'=>array('id'=>12,'nickName'=>'凉城听暖'),
    '13'=>array('id'=>13,'nickName'=>'澄成诚程'),
    '14'=>array('id'=>14,'nickName'=>'海上明月共潮生'),
    '15'=>array('id'=>15,'nickName'=>'清风入梦'),
    '16'=>array('id'=>16,'nickName'=>'南风草木香'),
    '17'=>array('id'=>17,'nickName'=>'雾时之森'),
    '18'=>array('id'=>18,'nickName'=>'烟云似雪'),
    '19'=>array('id'=>19,'nickName'=>'水清天蓝'),
    '20'=>array('id'=>20,'nickName'=>'岛是海的寂寞'),
    '21'=>array('id'=>21,'nickName'=>'冰橙♀柠檬'),
    '22'=>array('id'=>22,'nickName'=>'与花如笺'),
    '23'=>array('id'=>23,'nickName'=>'九月茉莉'),
    '24'=>array('id'=>24,'nickName'=>'暗香疏影'),
    '25'=>array('id'=>25,'nickName'=>'清风耳畔拂'),
    '26'=>array('id'=>26,'nickName'=>'清风徐来'),
    '27'=>array('id'=>27,'nickName'=>'葱郁风光'),
    '28'=>array('id'=>28,'nickName'=>'花落肩头'),
    '29'=>array('id'=>29,'nickName'=>'秋叶静美'),
    '30'=>array('id'=>30,'nickName'=>'未若柳絮'),
);
$config['REDPACKET_SCOPE'] = array(
    '1'=>'全网餐厅电视',
    '2'=>'当前餐厅所有电视',
    '3'=>'当前包间电视',
    '4'=>'区域餐厅电视',
//    '5'=>'运营-所选餐厅单个包间单个红包',
);

$config['ADV_VIDEO'] = array(
    'name' => array(
        '0' => '酒楼宣传片',
        '1' => '酒楼片源',
    ),
    'num' => '6',
);
$config['ADVE_OCCU'] = array(
    'name' => '广告位',
    'num' => '50',
);
$config['RTBADVE_OCCU'] = array(
    'name' => 'RTB广告位',
    'num' => '18',
);
$config['GOODSADVE_OCCU'] = array(
    'name' => '商品广告位',
    'num' => '18',
);
$config['POLY_SCREEN_OCCU']= array(
    'name'=>'聚屏广告位',
    'num' => '50',
);
$config['ACTIVITY_GOODS_OCCU']= array(
    'name'=>'活动商品广告位',
    'num' => '10',
);
$config['SELECTCONTENT_GOODS_OCCU']= array(
    'name'=>'精选内容广告位',
    'num' => '20',
);
$config['LIFE_OCCU']= array(
    'name'=>'本地生活广告位',
    'num' => '24',
);
$config['STORESALE_OCCU']= array(
    'name'=>'本店有售广告位',
    'num' => '10',
);
$config['POLY_SCREEN_MEDIA_LIST'] = array(
    '1'=>'百度聚屏',
    '2'=>'钛镁聚屏',
    '3'=>'奥凌聚屏',
    '4'=>'众盟聚屏',
    '5'=>'京东聚屏',
    '6'=>'易售聚屏',
);
$config['TOU_STATE'] = array(
    '0'=>'全部',
    '1'=>'未到投放时间',
    '3'=>'投放完毕',
    '4'=>'不可投放',
    '2'=>'投放中',
);
$config['USER_GRP_CONFIG'] = array(
    '0'=>'无',
    '1'=>'运维组',
);

$config['OPTION_USER_ROLE_ARR']  = ARRAY(
     '1'=>'发布者', 
     '2'=>'指派者',
     '3'=>'执行者',
     '4'=>'查看',
     '5'=>'外包',
     '6'=>'巡检员',
);
$config['OPTION_USER_SKILL_ARR'] = array(
    '1'=>'信息检测',
    '8'=>'网络改造',
    '2'=>'安装验收',
    '4'=>'维修',
);
$config['HOTEL_DAMAGE_CONFIG'] = array(
    '1'=>'电源适配器',
    '2'=>'SD卡',
    '3'=>'HDMI线',
    '4'=>'信号源错误',
    '5'=>'5G路由器',
    '6'=>'遥控器',
    '7'=>'红外遥控头',
    '8'=>'机顶盒',
    '9'=>'小平台',
    '10'=>'酒楼WIFI',
    '11'=>'酒楼电视机',
	'12'=>'未开机',
    '13'=>'其它',
);
$config['PUB_ADS_HOTEL_ERROR'] = array(
    '1'=>'剩余广告位不足',
    '2'=>'酒楼冻结',
    '3'=>'酒楼报损',
    '4'=>'包间冻结',
    '5'=>'包间报损',
    '6'=>'机顶盒冻结',
    '7'=>'机顶盒报损',
    '8'=>'包间机顶盒为空',
);
$config['ROOM_TYPE'] = array(
    1=>'包间',
    2=>'大厅',
    3=>'等候区'
);
$config['HEART_LOG_SAVE_DAYS'] = 30;
$config['CONFIG_VOLUME'] = array(
    'system_ad_volume'=>'广告音量',
    'system_pro_screen_volume'=>'投屏音量',
    'system_demand_video_volume'=>'点播音量',
    'system_tv_volume'=>'电视音量'
);
$config['PROGRAM_ADS_CACHE_PRE'] = 'program_ads_';
$config['PROGRAM_PRO_CACHE_PRE'] = 'program_pro_';
$config['PROGRAM_ADV_CACHE_PRE'] = 'program_adv_';
$config['SMALL_ROOM_LIST']       = 'small_room_list_';
$config['SMALL_HOTEL_INFO']      = 'small_hotel_info_';
$config['SYSTEM_CONFIG']         = 'system_config';
$config['SMALL_BOX_LIST']        = 'small_box_list_';
$config['SMALL_TV_LIST']         = 'small_tv_list_';
$config['SMALL_PROGRAM_LIST_KEY'] = 'small_program_list_';
$config['SAPP_SCRREN']           = 'smallapp:forscreen';
$config['SAPP_PLAY_GAME']        = 'smallapp:playgame';
$config['SAPP_UPRES_FORSCREEN']  = 'smallapp:upresouce';
$config['SAPP_UPDOWN_FORSCREEN'] = 'smallapp:boxupdown:';
$config['SAPP_WANT_GAME']        = 'smallapp:wantgame';
$config['SAPP_SUNCODE_LOG']      = 'smallapp:suncodelog:';
$config['SAPP_SCRREN_SHARE']     = 'smallapp:public:forscreen:';
$config['SAPP_HISTORY_SCREEN']   = 'smallapp:history:forscreen:';
$config['SAPP_FORSCREEN_NUMS']   = 'smallapp:interact:nums:';
$config['SAPP_PAGEVIEW_LOG']     ='smallap:pageview:log:';
$config['VM_HOTEL_LIST']         ='vsmall_hotel_list';
$config['SAPP_BIRTHDAYDEMAND']   = 'smallapp:birthdaydemand';
$config['SAPP_HOTPLAYDEMAND']   = 'smallapp:hotplaydemand';
$config['SMALLAPP_FORSCREEN_ADS']   = 'smallapp:forscreen:ads:';
$config['SMALLAPP_MARQUEE_ADS']   = 'smallapp:marquee:ads:';
$config['SMALLAPP_LIFE_ADS']   = 'smallapp:life:ads:';
$config['SMALLAPP_STORESALE_ADS']   = 'smallapp:storesale:ads:';
$config['SAPP_BOX_FORSCREEN_NET']='smallapp:net:forscreen:';
$config['SAPP_REDPACKET']='smallapp:redpacket:';
$config['SAPP_PUBLIC_AUDITNUM']='smallapp:public:auditnum:';
$config['BOX_TPMEDIA']  = 'box:tpmedia:';
$config['SAPP_SELECTCONTENT_CONTENT']='smallapp:selectcontent:content';
$config['SAPP_SELECTCONTENT_PUSH']='smallapp:selectcontent:wxpush';
$config['SAPP_SIMPLE_UPLOAD_RESOUCE'] = 'smallapp:simple:upload:';
$config['SAPP_SIMPLE_UPLOAD_PLAYTIME'] = 'smallapp:simple:uploadplaytime:';
$config['SAPP_OPTIMIZE_PROGRAM']='smallapp:optimize:program';
$config['SMALLAPP_HOTEL_RELATION']='smallapp:hotelrelation:';
$config['BOX_LANHOTEL_DOWNLOAD']='lanhotel:download:';
$config['BOX_LANHOTEL_DOWNLOADQUEUE']='lanhotel:queuedownload:';
$config['BOX_LANHOTEL_DOWNLOAD_FAIL']='lanhotel:faildownload:';

//新虚拟小平台接口缓存key
$config['VSMALL_HOTELLIST'] = "vsmall:hotellist";
$config['VSMALL_PRO']   = "vsmall:pro:";
$config['VSMALL_ADV']   = "vsmall:adv:";
$config['VSMALL_ADS']   = "vsmall:ads:";
$config['VSMALL_POLY']  = "vsmall:poly:";
$config['VSMALL_VOD']   = "vsmall:vod:";
$config['VSMALL_APK']   = "vsmall:apk:";

//统计缓存key
$config['STATS_CACHE_PRE'] = "statistics:";

//财务后台缓存key
$config['FINANCE_HOTELSTOCK']   = "finance:hotelstock";
$config['FINANCE_GOODSSTOCK']   = "finance:goodsstock";

$config['UPD_STR'] = array(
    1=>array(
        'ename'=>'get_channel',
        'cname'=>'导出电视节目列表',
    ),
    2=>array(
        'ename'=>'update_logo',
        'cname'=>'上传酒楼LOGO',
    ),
    3=>array(
        'ename'=>'set_channel',
        'cname'=>'更新电视节目列表',
    ),
    4=>array(
        'ename'=>'get_log',
        'cname'=>'导出开机率日志',
    ),
    5=>array(
        'ename'=>'get_loged',
        'cname'=>'导出备份开机率日志',
    ),
    6=>array(
        'ename'=>'update_media',
        'cname'=>'更新广告视频',
    ),
    7=>array(
        'ename'=>'update_apk',
        'cname'=>'更新客户端APK',
    ),
);
$config['CONFIG_VOLUME_VAL'] = array(
    'system_ad_volume'=>60,
    'system_pro_screen_volume'=>100,
    'system_demand_video_volume'=>90,
    'system_tv_volume'=>100,
    'system_tv_volume'=>100,
    'system_switch_time'=>30,
);

$config['ADV_MACH'] = array(
    '0'=>'否',
    '1'=>'是',
);
$config['SELECT_ADV_MACH'] = array(
    '0'=>'全部',
    '1'=>'有',
    '2'=>'无',
);
$config['STATISTICS_TYPE'] = array(
    'ads'=>'广告',
    'pro'=>'节目',
    'rtbads'=>'B类广告',
);
$config['HOTEL_STANDALONE_CONFIG'] = array(
    '1'=>'机顶盒坏',
    '2'=>'信号源错误',
    '3'=>'盒子配件故障',
    '4'=>'酒楼配件故障',
    '5'=>'电视机坏',
    '6'=>'盒子系统时间错误',
    '7'=>'线乱',
    '8'=>'天线被拔',
    '9'=>'天线坏',
    '10'=>'无包间',
    '11'=>'无电视',
    '12'=>'无机顶盒',
    '13'=>'无酒楼',
    '14'=>'酒楼装修中',
    '15'=>'死机',
    '16'=>'其它',
);
$config['HEART_LOSS_HOURS'] = 48;
//发送邮件配置
$config['MAIL_ADDRESS'] = 'xxx@xxx.com'; // 邮箱地址
$config['MAIL_SMTP'] = 'smtp.xxx.com'; // 邮箱SMTP服务
$config['MAIL_LOGINNAME'] = 'xx@xx.com'; // 邮箱登录帐号
$config['MAIL_PASSWORD'] = 'mailpassword'; // 邮箱密码
$config['MAIL_CHARSET'] = 'UTF-8';//编码
$config['MAIL_AUTH'] = true;//邮箱认证
$config['MAIL_HTML'] = true;//true HTML格式 false TXT格式

$config['ALIYUN_SMS_CONFIG'] = array(
    'send_invoice_addr_templateid'=>'SMS_176935152',
    'activity_goods_send_salemanager'=>'SMS_176527162',
    'merchant_login_invite_code'=>'SMS_183767419',
    'public_audit_templateid'=>'SMS_216374893',
    'public_audit_templateid'=>'SMS_216374893',
    'send_tasklottery_user_templateid'=>'SMS_227740798',
    'send_tasklottery_sponsor_templateid'=>'SMS_227745754',
    'send_tasklottery_bootnum_templateid'=>'SMS_227737155',
);

$config['CHANNEL_MERCHANT'] = array(
    '1'=>'内购网'
);
$config['GROUP_RATE'] = array(
    '100'=>'1.0',
    '110'=>'1.1',
    '120'=>'1.2',
);
$config['WELCOME_STATUS'] = array(
    '1'=>'正在播放',
    '2'=>'定时播放',
    '3'=>'已结束',
    '4'=>'已删除',
);
$config['TAGS_CATEGORY'] = array(
    '1'=>'评论',
    '2'=>'下单备注',
    '3'=>'菜品评论',
);
$config['ACTIVITY_STATUS']=array(
    '0'=>'待开始',
    '1'=>'进行中',
    '2'=>'已结束',
    '3'=>'已取消',
);
$config['PUBLIC_AUDIT_STATUS']=array(
    '0'=>'删除',
    '1'=>'待审核',
    '2'=>'审核通过',
    '3'=>'审核不通过',
);
$config['PUBLIC_AUDIT_MOBILE']=array(
    13810910309
);
$config['PUBLIC_PLAY_FREQUENCY']=array(
    1=>array('00'),
    2=>array('00','30'),
    3=>array('00','20','40'),
    4=>array('00','15','30','45'),
    6=>array('00','10','20','30','40','50'),
    12=>array('00','05','10','15','20','25','30','35','40','45','50','55')
);
$config['SAMPLE_HOTEL'] = array(
    236=>array(632,1064,955,427,1043,1039,1085,1077,
        418,1097,1023,981,1038,962,1050,1046,1051,1037,1044,
        714,404,925,530,395,945,421,861,1114,1091,719,
        964,717,713,1052,420,912,
        1090,419,898,428,1042,
        1033,1056,1048
    ),
);

$config['COLLECT_FORSCREEN_OPENIDS'] = array(
//    'ofYZG4zmrApmvRSfzeA_mN-pHv2E'=>'郑伟','ofYZG42whtWOvSELbvxvnXHbzty8'=>'黄勇',
    'ofYZG49N0yz-cCTTgfPPEoL1F7l4'=>'鲍强强','ofYZG4xt_03ADzTTtf4QIrA1lt_c'=>'甘顺山','ofYZG43prBncpYjkYq-XaIWRlj6o'=>'吴琳',
    'ofYZG4-TBnXlWMTGx6afsUrjzXgk'=>'李智','ofYZG4ySsM6GN8bF9bw6iWlS9a44'=>'王习宗',
    'ofYZG4-geGG-WO3drWsAZetCghSc'=>'何永锐','ofYZG43zZMAYXbuOiQxIqGfz25aM'=>'玉洁','ofYZG43DyszPj-qwvP5ZutMCGC_c'=>'欧懿',
    'ofYZG4zTOtj9RCaLmDXI0qfY-I34'=>'熊静怡','ofYZG45GWNg7k9CLVHoRdUqQVPJ4'=>'黎晓欣','ofYZG47NzXqDD0lumUkq-it6_mXY'=>'王伟明',
    );

$config['ACTIVITY_KINGMEAL'] = array(
//    202009031300=>array(
//        array('hotel_id'=>7,'dish'=>'鲍汁香扣花胶筒1份','start_time'=>'2020-09-03 13:00:00','end_time'=>'2020-09-03 13:25:00',
//            'lottery_time'=>'2020-09-03 13:30:00','dish_img'=>'lottery/activity/bzxkhjt.jpg'),
//    ),

    202009031200=>array(
        array('hotel_id'=>427,'dish'=>'鲍汁香扣花胶筒1份','start_time'=>'2020-09-03 12:00:00','end_time'=>'2020-09-03 12:25:00',
            'lottery_time'=>'2020-09-03 12:30:00','dish_img'=>'lottery/activity/bzxkhjt.jpg'),
    ),

    202009031830=>array(
        array('hotel_id'=>1077,'dish'=>'开胃四小蝶1份','start_time'=>'2020-09-03 18:30:00','end_time'=>'2020-09-03 18:55:00',
            'lottery_time'=>'2020-09-03 19:00:00','dish_img'=>'lottery/activity/kwsxd.jpg'),
    ),
    202009031910=>array(
        array('hotel_id'=>1077,'dish'=>'椒盐鲜蘑1份','start_time'=>'2020-09-03 19:10:00','end_time'=>'2020-09-03 19:35:00',
        'lottery_time'=>'2020-09-03 19:40:00','dish_img'=>'lottery/activity/jyxm.jpg'),
    ),

    202009031900=>array(
        array('hotel_id'=>1056,'dish'=>'煎藕饼1份','start_time'=>'2020-09-03 19:00:00','end_time'=>'2020-09-03 19:25:00',
            'lottery_time'=>'2020-09-03 19:30:00','dish_img'=>'lottery/activity/joub.jpg'),
    ),

    202009031940=>array(
        array('hotel_id'=>1056,'dish'=>'剁椒蒸鱼咀1份','start_time'=>'2020-09-03 19:40:00','end_time'=>'2020-09-03 20:05:00',
            'lottery_time'=>'2020-09-03 20:10:00','dish_img'=>'lottery/activity/djzyz.jpg'),
    ),
);

$config['INTEGRAL_TYPES'] = array(
    1=>'开机',
    2=>'互动',
    3=>'销售',
    4=>'兑换',
    5=>'退回',
    6=>'活动促销',
    7=>'评价奖励',
    8=>'评价补贴',
    9=>'分配',
    10=>'领取品鉴酒活动任务',
    11=>'完成品鉴酒活动任务',
    12=>'领取抽奖活动任务',
    13=>'完成抽奖活动任务',
);
$config['GOODS_WINE_TYPES'] = array(
//    '1'=>'预定酒水',
    '2'=>'主推酒水',
    '3'=>'随机酒水',
);
$config ['FORSCREEN_RECOURCE_CATE'] = array('1'=>'节目','2'=>'热播内容节目','3'=>'点播商品视频','4'=>'点播banner商品视频','5'=>'点播生日歌');
$config['FORSCREEN_CONTENT_RECOURCE_CATE'] = array('1'=>'热播内容节目','2'=>'热播用户内容','3'=>'节目','4'=>'商城商品','5'=>'banner商品','6'=>'生日歌','7'=>'星座视频');
$config['BONUS_OPERATION_INFO'] = array(
    'nickName'=>'热点酒水超市',
    'avatarUrl'=>'http://oss.littlehotspot.com/media/resource/btCfRRhHkn.jpg',
    'popout_img'=>'WeChat/resource/popout-bonus.png'
);
$config['INVITATION_HOTEL_CONFIG'] = array(
    'media_id'=>31130,
    'bg_img'=>'media/resource/kJxFWZJEDG.jpeg',
    'theme_color'=>'rgb(193,147,166)',
    'theme_contrast_color'=>'rgb(255, 255, 255)',
    'pain_color'=>'rgb(16, 16, 16)',
    'weak_color'=>'rgb(153, 153, 153)',
    'is_open_sellplatform'=>1
);
$config['RD_TEST_HOTEL'] =
    array (

    );

return $config;