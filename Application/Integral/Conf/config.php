<?php
$config = array(
    'VAR_PAGE'=>'pageNum',
    'SHOW_PAGE_TRACE'=>false,
    'servie_type'=>array('1'=>'基础服务','2'=>'增值服务'),
    'integral_task_type'=>array('1'=>'系统任务','2'=>'活动任务'),
//    'system_task_content'=>array('1'=>'电视开机','2'=>'电视互动','3'=>'活动推广','4'=>'邀请食客评价','5'=>'打赏补贴'),
    'system_task_content'=>array('1'=>'电视开机','2'=>'电视互动','4'=>'邀请食客评价','5'=>'打赏补贴'),
    'service_list'=>array('tv_forscreen'=>'电视投屏','room_signin'=>'包间签到','pro_play'=>'循环播放',
                          'activity_pop'=>'活动促销','hotel_activity'=>'餐厅活动','integral_manage'=>'积分收益',
                          'integral_shop'=>'积分兑换','goods_manage'=>'活动商品管理','staff_manage'=>'员工管理',
                          'task_manage'=>'任务管理'
    ),
);
return $config;