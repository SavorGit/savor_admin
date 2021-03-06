<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;

class TaskUserModel extends BaseModel{
	
	protected $tableName='integral_task_user';

	public function handle_user_task(){
        $now_date = date('Y-m-d');
        $date_h = date('H');
        if($date_h==17){
            $dinner_type = 1;//午饭
            $sign_begin_time = $now_date." 00:00:00";
            $sign_end_time = $now_date." 14:00:00";
        }elseif($date_h==23){
            $dinner_type = 2;//晚饭
            $sign_begin_time = $now_date." 14:00:01";
            $sign_end_time = $now_date." 21:00:00";
        }else{
            echo "hour $date_h error \r\n";
            exit;
        }

        $begin_time = $now_date." 00:00:00";
        $end_time = $now_date." 23:59:59";
        $where = array();
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        $res_data = $this->getDataList('*',$where,'id asc');
        if(empty($res_data)){
            $now_time = date('Y-m-d H:i:s');
            echo "$now_time user_task empty \r\n";
            exit;
        }

        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $all_config = $m_sysconfig->getAllconfig();
        $integral_boxmac = $all_config['integral_boxmac'];

        $m_task = new \Admin\Model\Integral\TaskModel();
        $m_usersignin = new \Admin\Model\Smallapp\UserSigninModel();
        foreach ($res_data as $v){
            $now_time = date('Y-m-d H:i:s');
            $task_id = $v['task_id'];
            $task_info = $m_task->getInfo(array('id'=>$task_id));
            if(empty($task_info) || $task_info['status']==0 || $task_info['flag']==0){
                echo "task_id:$task_id state error $now_time \r\n";
                continue;
            }
            if($task_info['type']!=1){
                echo "task_id:$task_id type not systemtask $now_time \r\n";
                continue;
            }
            $task_info['integral_boxmac'] = $integral_boxmac;
            $task_info['task_user_id'] = $v['id'];
            $task_content = json_decode($task_info['task_info'],true);
            $openid = $v['openid'];
            $task_type = $task_content['task_content_type'];//1开机 2互动 3活动推广 4邀请食客评价 5打赏补贴
            if(in_array($task_type,array(4,5))){
                switch ($dinner_type){
                    case 1:
                        $begin_time = date("Y-m-d {$task_content['lunch_start_time']}:00");
                        $end_time = date("Y-m-d {$task_content['lunch_end_time']}:00");
                        break;
                    case 2:
                        $begin_time = date("Y-m-d {$task_content['dinner_start_time']}:00");
                        $end_time = date("Y-m-d {$task_content['dinner_end_time']}:00");
                        break;
                    default:
                        $begin_time = '';
                        $end_time = '';
                }
                if(empty($begin_time) && empty($end_time)){
                    echo "task_id:$task_id begin and end time error \r\n";
                    continue;
                }
                $m_staff = new \Admin\Model\Integral\StaffModel();
                $staff_info = $m_staff->getInfo(array('openid'=>$openid,'status'=>1));
                if(empty($staff_info)){
                    echo "task_id:$task_id staff not exist \r\n";
                    continue;
                }
                $fj_bstime = strtotime($begin_time);
                $fj_estime = strtotime($end_time);
                $task_date = date('Ymd');
                $task_times = array('fj_bstime'=>$fj_bstime,'fj_estime'=>$fj_estime,'task_date'=>$task_date);
                switch ($task_type){
                    case 4:
                        $this->task_comment($task_times,$dinner_type,$task_info,$staff_info);
                        break;
                    case 5:
                        $this->task_commentreward($task_times,$dinner_type,$task_info,$staff_info);
                        break;
                }

            }else{
                $where = array('openid'=>$openid);
                $where['add_time'] = array(array('egt',$sign_begin_time),array('elt',$sign_end_time), 'and');
                $res_signin = $m_usersignin->getAll('*',$where,0,1000,'id desc','box_mac');
                if(empty($res_signin)){
                    echo "task_user_id:{$v['id']} $openid not sign $now_time \r\n";
                    continue;
                }
                foreach ($res_signin as $signv){
                    $signinfo = $m_usersignin->checkSigninTime(strtotime($signv['signin_time']));
                    if($signv['signout_time']=='0000-00-00 00:00:00'){
                        if($signinfo['is_signin']){
                            $m_usersignin->updateData(array('id'=>$signv['id']),array('signout_time'=>$signinfo['signout_time']));
                            $signv['signout_time'] = $signinfo['signout_time'];
                        }
                    }
                    if($signv['signout_time']=='0000-00-00 00:00:00'){
                        continue;
                    }
                    $tmp_dinner_type = $signinfo['type'];//1午饭 2晚饭
                    if($tmp_dinner_type==$dinner_type){
                        switch ($tmp_dinner_type){
                            case 1:
                                $fj_begin_hour = $task_content['lunch_start_time'];
                                $fj_end_hour = $task_content['lunch_end_time'];
                                break;
                            case 2:
                                $fj_begin_hour = $task_content['dinner_start_time'];
                                $fj_end_hour = $task_content['dinner_end_time'];
                                break;
                            default:
                                $fj_begin_hour = '';
                                $fj_end_hour = '';
                        }
                        if(empty($fj_begin_hour) && empty($fj_end_hour)){
                            continue;
                        }
                        $fj_begin_time = $now_date." $fj_begin_hour";
                        $fj_end_time = $now_date." $fj_end_hour";
                        $fj_bstime = strtotime($fj_begin_time);
                        $fj_estime = strtotime($fj_end_time);
                        $task_date = date('Ymd');
                        $task_times = array('fj_bstime'=>$fj_bstime,'fj_estime'=>$fj_estime,'task_date'=>$task_date);

                        $task_type = $task_content['task_content_type'];//1开机 2互动 3活动推广
                        switch ($task_type){
                            case 1:
                                $this->task_boot($task_times,$dinner_type,$task_info,$signv);
                                break;
                            case 2:
                                $this->task_interact($task_times,$dinner_type,$task_info,$signv);
                                break;
                            case 3:
                                $this->task_activitypromote($task_times,$dinner_type,$task_info,$signv);
                                break;
                        }
                    }
                }
            }
        }
    }

    private function task_comment($task_times,$dinner_type,$task_info,$staff_info){
        $begin_time = date('Y-m-d H:i:s',$task_times['fj_bstime']);
        $end_time = date('Y-m-d H:i:s',$task_times['fj_estime']);
        $task_date = $task_times['task_date'];

        $where = array('staff_id'=>$staff_info['id']);
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        $m_comment = new \Admin\Model\Smallapp\CommentModel();
        $res_comment = $m_comment->getDataList('*',$where,'id desc');
        if(empty($res_comment)){
            echo "task_user_id:{$task_info['task_user_id']}-task_id:{$task_info['id']} dinner_type $dinner_type no comment\r\n";
            return true;
        }

        $m_box = new \Admin\Model\BoxModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();

        $task_where = array('openid'=>$staff_info['openid'],'task_id'=>$task_info['task_user_id']);
        $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
        $task_where['fj_type'] = $dinner_type;
        $tmp_exist = $m_userintegralrecord->field('id,task_id,fj_type,integral')->where($task_where)->find();
        if(!empty($tmp_exist) && $tmp_exist['integral']>0){
            $integralrecord = json_encode($tmp_exist);
            echo "{$task_info['task_user_id']} had getintegral integralrecord:$integralrecord \r\n";
            return true;
        }

        $now_integral = 0;
        $task_content = json_decode($task_info['task_info'],true);
        $max_daily_integral = $task_content['max_daily_integral'];//每日最多积分上限

        $task_type = $task_content['user_comment']['type'];
        $comment_num = count($res_comment);
        $ap_num = 0;
        switch ($task_type){//1.饭点内评价 2饭点内每评价多少次奖励一次
            case 1:
                if($comment_num>0){
                    $ap_num = 1;
                    $now_integral = $task_info['integral'];
                }
                break;
            case 2:
                if($comment_num>0){
                    $reward_num = $task_content['user_comment']['value'];
                    $ap_num = floor($comment_num/$reward_num);
                    $now_integral = $task_info['integral']*$ap_num;
                }
                break;
        }
        $admin_integral = 0;
        if($now_integral){
            $tmp_where = array('openid'=>$staff_info['openid']);
            $tmp_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_where['task_id'] = $task_info['task_user_id'];
            $tmp_resintegral = $this->field('integral as total_integral')->where($tmp_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);

            $staff_info['box_mac'] = $res_comment[0]['box_mac'];
            $box_info = $m_box->getHotelInfoByBoxMac($staff_info['box_mac']);

            $res_shareprofit = $this->calculate_shareprofit($now_integral,$task_info,$staff_info,$box_info);
            $now_integral = $res_shareprofit['now_integral'];
            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = $max_daily_integral - $tmp_integral;
                echo "task_user_id:{$task_info['task_user_id']}-task_id:{$task_info['id']} gt max_daily_integral $now_integral \r\n";
            }else{
                $admin_integral = $res_shareprofit['admin_integral'];
                echo "task_user_id:{$task_info['task_user_id']}-task_id:{$task_info['id']} dinner_type $dinner_type comment_integral $now_integral \r\n";
            }
        }

        if($admin_integral>0 || $now_integral>0){
            $integralrecord_data = array('openid'=>$staff_info['openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$box_info['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$ap_num,'type'=>7,'integral_time'=>$end_time);
            $integralrecord_id = $m_userintegralrecord->add($integralrecord_data);

            $res_shareprofit['integralrecord_id'] = $integralrecord_id;
            $res_shareprofit['dinner_type'] = $dinner_type;
            $res_shareprofit['fj_estime'] = $task_times['fj_estime'];
            $res_shareprofit['integral_type'] = 6;
            $res_shareprofit['task_id'] = $task_info['id'];
            $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            if(isset($res_shareprofit['middle_openid']) && isset($res_shareprofit['middle_integral'])){
                $res_shareprofit['admin_integral'] = $res_shareprofit['middle_integral'];
                $res_shareprofit['admin_openid'] = $res_shareprofit['middle_openid'];
                $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            }

            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$staff_info['openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$now_integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$staff_info['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }
            //更新任务积分
            $this->where(array('id'=>$task_info['task_user_id']))->setInc('integral',$now_integral);
        }
        echo "{$task_info['task_user_id']} finish \r\n";
        return true;
    }

    private function task_commentreward($task_times,$dinner_type,$task_info,$staff_info){
        $begin_time = date('Y-m-d H:i:s',$task_times['fj_bstime']);
        $end_time = date('Y-m-d H:i:s',$task_times['fj_estime']);
        $task_date = $task_times['task_date'];

        $where = array('staff_id'=>$staff_info['id'],'reward_id'=>0);
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        $m_comment = new \Admin\Model\Smallapp\CommentModel();
        $res_comment = $m_comment->getDataList('*',$where,'id desc');
        if(empty($res_comment)){
            echo "task_user_id:{$task_info['task_user_id']}-task_id:{$task_info['id']} dinner_type $dinner_type no comment\r\n";
            return true;
        }

        $m_box = new \Admin\Model\BoxModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();

        $task_where = array('openid'=>$staff_info['openid'],'task_id'=>$task_info['task_user_id']);
        $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
        $task_where['fj_type'] = $dinner_type;
        $tmp_exist = $m_userintegralrecord->field('id,task_id,fj_type,integral')->where($task_where)->find();
        if(!empty($tmp_exist) && $tmp_exist['integral']>0){
            $integralrecord = json_encode($tmp_exist);
            echo "{$task_info['task_user_id']} had getintegral integralrecord:$integralrecord \r\n";
            return true;
        }

        $now_integral = 0;
        $task_content = json_decode($task_info['task_info'],true);
        $max_daily_integral = $task_content['max_daily_integral'];//每日最多积分上限

        $task_type = $task_content['user_reward']['type'];
        $comment_num = count($res_comment);
        $ap_num = 0;
        switch ($task_type){//1.饭点内评价无打赏奖励 2饭点内每评价多少次 无打赏奖励一次
            case 1:
                if($comment_num>0){
                    $ap_num = 1;
                    $now_integral = $task_info['integral'];
                }
                break;
            case 2:
                if($comment_num>0){
                    $reward_num = $task_content['user_reward']['value'];
                    $ap_num = floor($comment_num/$reward_num);
                    $now_integral = $task_info['integral']*$ap_num;
                }
                break;
        }
        $admin_integral = 0;
        if($now_integral){
            $tmp_where = array('openid'=>$staff_info['openid']);
            $tmp_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_where['task_id'] = $task_info['task_user_id'];
            $tmp_resintegral = $this->field('integral as total_integral')->where($tmp_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);

            $staff_info['box_mac'] = $res_comment[0]['box_mac'];
            $box_info = $m_box->getHotelInfoByBoxMac($staff_info['box_mac']);

            $res_shareprofit = $this->calculate_shareprofit($now_integral,$task_info,$staff_info,$box_info);
            $now_integral = $res_shareprofit['now_integral'];
            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = $max_daily_integral - $tmp_integral;
                echo "task_user_id:{$task_info['task_user_id']}-task_id:{$task_info['id']} gt max_daily_integral $now_integral \r\n";
            }else{
                $admin_integral = $res_shareprofit['admin_integral'];
            }
        }

        if($admin_integral>0 || $now_integral>0){
            $integralrecord_data = array('openid'=>$staff_info['openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$box_info['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$ap_num,'type'=>8,'integral_time'=>$end_time);
            $integralrecord_id = $m_userintegralrecord->add($integralrecord_data);

            $res_shareprofit['integralrecord_id'] = $integralrecord_id;
            $res_shareprofit['dinner_type'] = $dinner_type;
            $res_shareprofit['fj_estime'] = $task_times['fj_estime'];
            $res_shareprofit['integral_type'] = 6;
            $res_shareprofit['task_id'] = $task_info['id'];
            $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            if(isset($res_shareprofit['middle_openid']) && isset($res_shareprofit['middle_integral'])){
                $res_shareprofit['admin_integral'] = $res_shareprofit['middle_integral'];
                $res_shareprofit['admin_openid'] = $res_shareprofit['middle_openid'];
                $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            }

            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$staff_info['openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$now_integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$staff_info['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }
            //更新任务积分
            $this->where(array('id'=>$task_info['task_user_id']))->setInc('integral',$now_integral);
        }
        echo "{$task_info['task_user_id']} finish \r\n";
        return true;
    }

    private function task_activitypromote($task_times,$dinner_type,$task_info,$signv){
        $fj_bstime = $task_times['fj_bstime'];
        $fj_estime = $task_times['fj_estime'];
        $task_date = $task_times['task_date'];

        $redis = \Common\Lib\SavorRedis::getInstance();
        $redis->select(14);
        $key_integral = C('SAPP_SALE_ACTIVITY_PROMOTE');
        $key_opintegral = $key_integral.date('Ymd').':'.$signv['openid'];
        $res_cache = $redis->get($key_opintegral);
        echo "{$task_info['task_user_id']} cache $res_cache \r\n";
        if(empty($res_cache)){
            echo "{$task_info['task_user_id']} finish \r\n";
            return true;
        }

        $res_cache = json_decode($res_cache,true);

        $m_box = new \Admin\Model\BoxModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();

        $task_where = array('openid'=>$signv['openid'],'task_id'=>$task_info['task_user_id']);
        $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
        $task_where['fj_type'] = $dinner_type;
        $tmp_exist = $m_userintegralrecord->field('id,task_id,fj_type,integral')->where($task_where)->find();
        if(!empty($tmp_exist) && $tmp_exist['integral']>0){
            $integralrecord = json_encode($tmp_exist);
            echo "{$task_info['task_user_id']} had getintegral integralrecord:$integralrecord \r\n";
            return true;
        }

        $now_integral = 0;
        $task_content = json_decode($task_info['task_info'],true);
        $max_daily_integral = $task_content['max_daily_integral'];//每日最多积分上限

        $task_type = $task_content['user_promote']['type'];
        $ap_num = 0;
        switch ($task_type){//1.饭点内点击"循环播放" 2饭点内每点播活动多少次奖励一次
            case 1:
                if(isset($res_cache[$task_type])){
                    $cache_activitypromote = json_encode($res_cache[$task_type]);
                    echo "{$task_info['task_user_id']} type:1 cache_activitypromote:$cache_activitypromote \r\n";
                    foreach ($res_cache[$task_type] as $apv){
                        $apv_time = strtotime($apv['date']);
                        if($apv_time>=$fj_bstime && $apv_time<=$fj_estime){
                            $now_integral = $task_info['integral'];
                            $ap_num = 1;
                            break;
                        }
                    }
                }
                break;
            case 2:
                if(isset($res_cache[$task_type])){
                    $cache_activitypromote = json_encode($res_cache[$task_type]);
                    echo "{$task_info['task_user_id']} type:2 cache_activitypromote:$cache_activitypromote \r\n";
                    $reward_num = $task_content['user_promote']['value'];
                    foreach ($res_cache[$task_type] as $apv){
                        $apv_time = strtotime($apv['date']);
                        if($apv_time>=$fj_bstime && $apv_time<=$fj_estime){
                            $ap_num++;
                        }
                    }
                    if($ap_num>=$reward_num){
                        $now_integral = $task_info['integral'];
                    }
                }
                break;
        }
        $admin_integral = 0;
        if($now_integral){
            $tmp_where = array('openid'=>$signv['openid']);
            $tmp_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_where['task_id'] = $task_info['task_user_id'];
            $tmp_resintegral = $this->field('integral as total_integral')->where($tmp_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);
            $box_info = $m_box->getHotelInfoByBoxMac($signv['box_mac']);

            $res_shareprofit = $this->calculate_shareprofit($now_integral,$task_info,$signv,$box_info);
            $now_integral = $res_shareprofit['now_integral'];
            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = 0;
            }else{
                $admin_integral = $res_shareprofit['admin_integral'];
            }
        }

        if($admin_integral || $now_integral){
            $integralrecord_data = array('openid'=>$signv['openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$signv['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$ap_num,'type'=>6,'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
            $integralrecord_id = $m_userintegralrecord->add($integralrecord_data);

            $res_shareprofit['integralrecord_id'] = $integralrecord_id;
            $res_shareprofit['dinner_type'] = $dinner_type;
            $res_shareprofit['fj_estime'] = $fj_estime;
            $res_shareprofit['integral_type'] = 6;
            $res_shareprofit['task_id'] = $task_info['id'];
            $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            if(isset($res_shareprofit['middle_openid']) && isset($res_shareprofit['middle_integral'])){
                $res_shareprofit['admin_integral'] = $res_shareprofit['middle_integral'];
                $res_shareprofit['admin_openid'] = $res_shareprofit['middle_openid'];
                $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            }

            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$signv['openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$now_integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$signv['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }
            //更新任务积分
            $this->where(array('id'=>$task_info['task_user_id']))->setInc('integral',$now_integral);
        }
        echo "{$task_info['task_user_id']} finish \r\n";
        return true;
    }

    private function task_interact($task_times,$dinner_type,$task_info,$signv){
        $m_forscreenrecord = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();

        $fj_bstime = $task_times['fj_bstime'];
        $fj_estime = $task_times['fj_estime'];
        $task_date = $task_times['task_date'];

        $hd_begin_time = date('Y-m-d H:i:s',$fj_bstime);
        $hd_end_time = date('Y-m-d H:i:s',$fj_estime);

        $now_integral = 0;
        $task_content = json_decode($task_info['task_info'],true);
        $max_daily_integral = $task_content['max_daily_integral'];//每日最多积分上限
        //排除酒楼员工
        $all_smallapps = C('all_smallapps');
        unset($all_smallapps['5']);
        $all_smallapp_ids = array_keys($all_smallapps);

        $forscreen_openids = array();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $res_staff = $m_staff->field('openid')->where(array())->order('id desc')->group('openid')->select();
        $openids = array();
        foreach ($res_staff as $staffv){
            $openids[]="{$staffv['openid']}";
        }
        if(!empty($openids)){
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $user_where = array('openid'=>array('in',$openids));
            $res_screenuser = $m_user->getWhere('unionId',$user_where,'id desc','','unionId');
            $union_ids = array();
            foreach ($res_screenuser as $suv){
                if(!empty($suv['unionid'])){
                    $union_ids[]="{$suv['unionid']}";
                }
            }
            if(!empty($union_ids)){
                $user_where = array('unionId'=>array('in',$union_ids));
                $user_where['small_app_id'] = array('in',$all_smallapp_ids);
                $res_screenuser = $m_user->getWhere('openid',$user_where,'id desc','','openid');
                foreach ($res_screenuser as $scv){
                    if(!empty($scv['openid'])){
                        $forscreen_openids[]="{$scv['openid']}";
                    }
                }
            }
        }
        //end

        $interact_num = 0;
        switch ($task_content['user_interact']['type']){//1.有效时段内每个互动大于多少次的独立用户 2有效时段内每次互动
            case 1:
                $where = array('a.box_mac'=>$signv['box_mac']);
                $where['a.create_time'] = array(array('EGT',$hd_begin_time),array('ELT',$hd_end_time));
                $where['a.mobile_brand'] = array('neq','devtools');
                $where['a.is_valid'] = 1;
                $where['a.small_app_id'] = array('in',$all_smallapp_ids);
                if(!empty($forscreen_openids)){
                    $where['a.openid'] = array('not in',$forscreen_openids);
                }
                $interact_num = $m_forscreenrecord->countHdintegralUser($where,$task_content['user_interact']['value']);
                $sql_interact = M()->getLastSql();
                echo "{$task_info['task_user_id']} type:1 sql_interact:$sql_interact \r\n";
                $now_integral = $task_info['integral']*$interact_num;
                break;
            case 2:
                $where = array('a.box_mac'=>$signv['box_mac']);
                $where['a.create_time'] = array(array('EGT',$hd_begin_time),array('ELT',$hd_end_time));
                $where['a.mobile_brand'] = array('neq','devtools');
                $where['a.is_valid'] = 1;
                $where['a.small_app_id'] = array('in',$all_smallapp_ids);
                if(!empty($forscreen_openids)){
                    $where['a.openid'] = array('not in',$forscreen_openids);
                }
                $interact_num = $m_forscreenrecord->countHdintegralNum($where);
                $sql_interact = M()->getLastSql();
                echo "{$task_info['task_user_id']} type:1 sql_interact:$sql_interact \r\n";
                $now_integral = $task_info['integral']*$interact_num;
                break;
        }
        $admin_integral = 0;
        if($now_integral){
            $tmp_where = array('openid'=>$signv['openid']);
            $tmp_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_where['task_id'] = $task_info['task_user_id'];

            $tmp_resintegral = $this->field('integral as total_integral')->where($tmp_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);

            $box_info = $m_box->getHotelInfoByBoxMac($signv['box_mac']);
            $res_shareprofit = $this->calculate_shareprofit($now_integral,$task_info,$signv,$box_info);
            $now_integral = $res_shareprofit['now_integral'];

            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = 0;
            }else{
                $admin_integral = $res_shareprofit['admin_integral'];
            }
        }
        if($admin_integral || $now_integral){
            $integralrecord_data = array('openid'=>$signv['openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$signv['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$interact_num,'type'=>2,'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
            $integralrecord_id = $m_userintegralrecord->add($integralrecord_data);

            $res_shareprofit['integralrecord_id'] = $integralrecord_id;
            $res_shareprofit['dinner_type'] = $dinner_type;
            $res_shareprofit['fj_estime'] = $fj_estime;
            $res_shareprofit['integral_type'] = 2;
            $res_shareprofit['task_id'] = $task_info['id'];
            $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            if(isset($res_shareprofit['middle_openid']) && isset($res_shareprofit['middle_integral'])){
                $res_shareprofit['admin_integral'] = $res_shareprofit['middle_integral'];
                $res_shareprofit['admin_openid'] = $res_shareprofit['middle_openid'];
                $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
            }

            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$signv['openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$now_integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$signv['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }
            //更新任务积分
            $this->setInc('integral',$now_integral);
            $this->where(array('id'=>$task_info['task_user_id']))->setInc('integral',$now_integral);
        }
        echo "{$task_info['task_user_id']} finish \r\n";
        return true;
    }

    private function task_boot($task_times,$dinner_type,$task_info,$signv){
        $fj_bstime = $task_times['fj_bstime'];
        $fj_estime = $task_times['fj_estime'];
        $task_date = $task_times['task_date'];

        $tmp_singin_h = date('G',$fj_bstime);
        $tmp_signout_h = date('G',$fj_estime);
        $m_box = new \Admin\Model\BoxModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_hearlog = new \Admin\Model\HeartAllLogModel();
        $res_logdate = $m_hearlog->getOne($signv['box_mac'],2,$task_date);
        $sql_boot = M()->getLastSql();
        echo "{$task_info['task_user_id']} sql_boot:$sql_boot \r\n";
        $online_hour = 0;
        if(!empty($res_logdate)){
            for($i=$tmp_singin_h;$i<$tmp_signout_h;$i++){
                if($res_logdate["hour$i"]>=10){
                    $online_hour+=1;
                }
            }
        }
        $max_online_hour = 6;//最大在线时长
        $online_hour = $online_hour>$max_online_hour?$max_online_hour:$online_hour;
        $now_integral = 0;
        $task_content = json_decode($task_info['task_info'],true);
        switch ($task_content['heart_time']['type']){//1.饭点内开机时长大于多少小时则达标 2饭点内每开机1小时奖励一次
            case 1:
                if($online_hour>$task_content['heart_time']['value']){
                    $now_integral = $task_info['integral'];
                }
                break;
            case 2:
                $reward_num = intval($online_hour/$task_content['heart_time']['value']);
                $now_integral = $task_info['integral']*$reward_num;
                break;
        }
        if($now_integral){
            $box_info = $m_box->getHotelInfoByBoxMac($signv['box_mac']);
            $res_shareprofit = $this->calculate_shareprofit($now_integral,$task_info,$signv,$box_info);
            $now_integral = $res_shareprofit['now_integral'];
            if($res_shareprofit['admin_integral'] || $now_integral){
                $integralrecord_data = array('openid'=>$signv['openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                    'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                    'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                    'box_id'=>$box_info['box_id'],'box_mac'=>$signv['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                    'integral'=>$now_integral,'content'=>$online_hour,'type'=>1,'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
                $integralrecord_id = $m_userintegralrecord->add($integralrecord_data);

                $res_shareprofit['integralrecord_id'] = $integralrecord_id;
                $res_shareprofit['dinner_type'] = $dinner_type;
                $res_shareprofit['fj_estime'] = $fj_estime;
                $res_shareprofit['integral_type'] = 1;
                $res_shareprofit['task_id'] = $task_info['id'];
                $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
                if(isset($res_shareprofit['middle_openid']) && isset($res_shareprofit['middle_integral'])){
                    $res_shareprofit['admin_integral'] = $res_shareprofit['middle_integral'];
                    $res_shareprofit['admin_openid'] = $res_shareprofit['middle_openid'];
                    $this->add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral);
                }

                $res_userintegral = $m_userintegral->getInfo(array('openid'=>$signv['openid']));
                if(!empty($res_userintegral)){
                    $userintegral = $res_userintegral['integral']+$now_integral;
                    $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                }else{
                    $integraldata = array('openid'=>$signv['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                    $m_userintegral->add($integraldata);
                }
                //更新任务积分
                $this->where(array('id'=>$task_info['task_user_id']))->setInc('integral',$now_integral);
            }
        }
        echo "{$task_info['task_user_id']} finish \r\n";
        return true;
    }


    private function calculate_shareprofit($now_integral,$task_info,$signv,$box_info){
	    $redis = new \Common\Lib\SavorRedis();
	    $redis->select(3);
	    $nowdate = date('Ymd');
	    $cache_key = "smallapp:integralboxmac:$nowdate:{$box_info['box_mac']}";
        $res_cache = $redis->get($cache_key);
        $box_integral = 0;
        if(!empty($res_cache)){
            $box_integral = $res_cache;
        }
        if($box_integral>=$task_info['integral_boxmac']){
            echo "{$task_info['task_user_id']} day integral uplimit $box_integral \r\n";

            $now_integral = 0;
            $task_info['is_shareprofit'] = 0;
        }else{
            $now_box_integral = $now_integral+$box_integral;
            if($now_box_integral>$task_info['integral_boxmac']){
                $now_integral = $task_info['integral_boxmac'] - $box_integral;
                $now_integral = $now_integral>0?$now_integral:0;

                echo "{$task_info['task_user_id']} day integral uplimit $now_box_integral-$now_integral \r\n";
            }
            $redis->set($cache_key,$now_box_integral,86400*7);
        }

        $res_data = array('integral'=>$now_integral,'now_integral'=>$now_integral,'admin_integral'=>0,
            'admin_openid'=>'','shareprofit_config'=>'');
        if($task_info['is_shareprofit']){
            $where_staff = array('a.openid'=>$signv['openid'],'m.hotel_id'=>$box_info['hotel_id'],'a.status'=>1,'m.status'=>1);
            $field_staff = 'a.openid,a.level,a.parent_id,m.type';
            $m_staff = new \Admin\Model\Integral\StaffModel();
            $res_staff = $m_staff->getMerchantStaffInfo($field_staff,$where_staff);
            if(!empty($res_staff) && in_array($res_staff['level'],array(2,3))){
                $where_share= array('task_id'=>$task_info['id']);
                $where_share['hotel_id'] = array('in',array(0,$box_info['hotel_id']));
                $m_task_shareprofit = new \Admin\Model\Integral\TaskShareprofitModel();
                $res_share = $m_task_shareprofit->getTaskShareprofit('level1,level2,level3',$where_share,'id desc',0,1);
                if(!empty($res_share)){
                    if($res_staff['level']==2){
                        $res_share = $res_share[0];
                        $res_staffadmin = $m_staff->getInfo(array('id'=>$res_staff['parent_id']));
                        if(!empty($res_staffadmin) && $res_staffadmin['status']==1) {
                            $admin_openid = $res_staffadmin['openid'];
                            $level1 = $res_share['level1'] / 100;
                            $admin_now_integral = round($level1 * $now_integral);
                            $res_data['now_integral'] = $now_integral - $admin_now_integral;
                            $res_data['admin_integral'] = $admin_now_integral;
                            $res_data['admin_openid'] = $admin_openid;
                            $res_data['shareprofit_config'] = $res_share['level1'].'-'.$res_share['level2'].'-'.$res_share['level3'];
                        }
                    }else{
                        //处理三级用户
                        $res_share = $res_share[0];
                        $res_staffadmin = $m_staff->getInfo(array('id'=>$res_staff['parent_id']));
                        if(!empty($res_staffadmin) && $res_staffadmin['status']==1) {
                            $admin2_openid = $res_staffadmin['openid'];
                            $res_staffadmin = $m_staff->getInfo(array('id'=>$res_staffadmin['parent_id']));
                            $admin1_openid = $res_staffadmin['openid'];

                            $level1 = $res_share['level1'] / 100;
                            $admin1_now_integral = round($level1 * $now_integral);
                            $level2 = $res_share['level2'] / 100;
                            $admin2_now_integral = round($level2 * $now_integral);

                            $res_data['now_integral'] = $now_integral - $admin2_now_integral-$admin1_now_integral;
                            $res_data['admin_integral'] = $admin1_now_integral;
                            $res_data['admin_openid'] = $admin1_openid;
                            $res_data['middle_integral'] = $admin2_now_integral;
                            $res_data['middle_openid'] = $admin2_openid;
                            $res_data['shareprofit_config'] = $res_share['level1'].'-'.$res_share['level2'].'-'.$res_share['level3'];
                        }
                    }
                }
            }
        }
        return $res_data;
    }

    private function add_adminintegral($res_shareprofit,$box_info,$m_userintegralrecord,$m_userintegral){
        if(!empty($res_shareprofit['shareprofit_config']) && !empty($res_shareprofit['admin_openid'])){
            $dinner_type = $res_shareprofit['dinner_type'];
            $fj_estime = $res_shareprofit['fj_estime'];
            $integral_type = $res_shareprofit['integral_type'];
            $task_id = $res_shareprofit['task_id'];

            $integralrecord_data = array('openid'=>$res_shareprofit['admin_openid'],'area_id'=>$box_info['area_id'],'task_id'=>$task_id,
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$box_info['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$res_shareprofit['admin_integral'],'content'=>'','type'=>$integral_type,'source'=>4,
                'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
            $m_userintegralrecord->add($integralrecord_data);

            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$res_shareprofit['admin_openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$res_shareprofit['admin_integral'];
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$res_shareprofit['admin_openid'],'integral'=>$res_shareprofit['integral'],'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }

            $data = array('integralrecord_id'=>$res_shareprofit['integralrecord_id'],'original_integral'=>$res_shareprofit['integral'],
                'openid'=>$res_shareprofit['admin_openid'],'shareprofit_integral'=>$res_shareprofit['admin_integral'],
                'shareprofit_config'=>$res_shareprofit['shareprofit_config']
                );
            $m_shareprofitrecord = new \Admin\Model\Integral\ShareprofitrecordModel();
            $m_shareprofitrecord->add($data);

        }
        return true;
    }

}