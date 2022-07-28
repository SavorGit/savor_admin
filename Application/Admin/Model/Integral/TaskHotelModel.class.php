<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class TaskHotelModel extends BaseModel{
	
	protected $tableName='integral_task_hotel';

    public function getHotelTaskGoodsList($fields,$where,$order){
        $goods_list = $this->alias('a')
            ->join('savor_integral_task task on a.task_id=task.id','left')
            ->join('savor_smallapp_dishgoods g on task.goods_id=g.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $goods_list;
    }

	public function getHoteltasks($fields,$where,$group){
        $res_data = $this->alias('hoteltask')
            ->join('savor_integral_task task on hoteltask.task_id=task.id ','left')
            ->field($fields)
            ->where($where)
            ->group($group)
            ->select();
        return $res_data;
    }

	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
	                 ->join('savor_sysuser user on a.uid=user.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = count($list);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}

    public function handle_hotel_task(){
        $now_date = date('Y-m-d');
        $date_h = date('H');
        if($date_h==17){
            $dinner_type = 1;//午饭
        }elseif($date_h==23){
            $dinner_type = 2;//晚饭
        }else{
            echo "hour $date_h error \r\n";
            exit;
        }

        $fields = 'hoteltask.hotel_id,task.*';
        $where = array('task.task_type'=>array('in',array(1,2,4,5)));//1开机 2互动 3活动推广(已废弃) 4邀请食客评价 5打赏补贴
        $where['task.status'] = 1;
        $where['task.flag'] = 1;
        $res_data = $this->alias('hoteltask')
            ->join('savor_integral_task task on hoteltask.task_id=task.id ','left')
            ->field($fields)
            ->where($where)
            ->select();
        if(empty($res_data)){
            $now_time = date('Y-m-d H:i:s');
            echo "$now_time hotel_task empty \r\n";
            exit;
        }

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $all_config = $m_sysconfig->getAllconfig();
        $integral_boxmac = $all_config['integral_boxmac'];

        foreach ($res_data as $v){
            $task_id = $v['id'];
            $task_info = $v;
            $task_info['integral_boxmac'] = $integral_boxmac;
            $task_content = json_decode($task_info['task_info'],true);
            $task_type = $task_info['task_type'];

            $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$v['hotel_id'],'status'=>1));
            if(empty($res_merchant) || $res_merchant['is_integral']==1){
                echo "hotel_id:{$v['hotel_id']} task_id:$task_id staff get integral \r\n";
                continue;
            }

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
                echo "hotel_id:{$v['hotel_id']} task_id:$task_id begin and end time error \r\n";
                continue;
            }
            $fj_bstime = strtotime($begin_time);
            $fj_estime = strtotime($end_time);
            $task_date = date('Ymd');
            $task_times = array('fj_bstime'=>$fj_bstime,'fj_estime'=>$fj_estime,'task_date'=>$task_date);
            $all_boxs = $m_box->getBoxByCondition('box.mac',array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0));
            switch ($task_type){
                case 1:
                    $this->task_boot($task_times,$dinner_type,$task_info,$all_boxs);
                    break;
                case 2:
                    $this->task_interact($task_times,$dinner_type,$task_info,$all_boxs);
                    break;
                case 4:
                    $this->task_comment($task_times,$dinner_type,$task_info);
                    break;
                case 5:
                    $this->task_commentreward($task_times,$dinner_type,$task_info);
                    break;
            }


        }
    }

    private function task_boot($task_times,$dinner_type,$task_info,$all_boxs){
        $fj_bstime = $task_times['fj_bstime'];
        $fj_estime = $task_times['fj_estime'];
        $task_date = $task_times['task_date'];

        $tmp_singin_h = date('G',$fj_bstime);
        $tmp_signout_h = date('G',$fj_estime);
        $m_box = new \Admin\Model\BoxModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_hearlog = new \Admin\Model\HeartAllLogModel();

        foreach ($all_boxs as $v){
            $box_mac = $v['mac'];
            $res_logdate = $m_hearlog->getOne($box_mac,2,$task_date);
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
                $box_info = $m_box->getHotelInfoByBoxMac($box_mac);
                $integralrecord_data = array('openid'=>$task_info['hotel_id'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                    'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                    'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                    'box_id'=>$box_info['box_id'],'box_mac'=>$box_mac,'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                    'integral'=>$now_integral,'content'=>$online_hour,'type'=>1,'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
                $m_userintegralrecord->add($integralrecord_data);

                $m_merchant = new \Admin\Model\Integral\MerchantModel();
                $where = array('hotel_id'=>$task_info['hotel_id'],'status'=>1);
                $m_merchant->where($where)->setInc('integral',$now_integral);
            }
        }
        echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} finish \r\n";
        return true;
    }

    private function task_interact($task_times,$dinner_type,$task_info,$all_boxs){
        $m_forscreenrecord = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_box = new \Admin\Model\BoxModel();
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

        foreach ($all_boxs as $v){
            $box_mac = $v['mac'];

            $interact_num = 0;
            switch ($task_content['user_interact']['type']){//1.有效时段内每个互动大于多少次的独立用户 2有效时段内每次互动
                case 1:
                    $where = array('a.box_mac'=>$box_mac);
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
                    $where = array('a.box_mac'=>$box_mac);
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
            if($now_integral){
                $tmp_where = array('openid'=>$task_info['hotel_id']);
                $tmp_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
                $tmp_where['task_id'] = $task_info['id'];
                $tmp_resintegral = $this->field('integral as total_integral')->where($tmp_where)->find();
                $tmp_integral = intval($tmp_resintegral['total_integral']);
                if($tmp_integral+$now_integral>$max_daily_integral){
                    $now_integral = $max_daily_integral - $tmp_integral;
                }
            }
            if($now_integral>0){
                $box_info = $m_box->getHotelInfoByBoxMac($box_mac);
                $integralrecord_data = array('openid'=>$task_info['hotel_id'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                    'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                    'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                    'box_id'=>$box_info['box_id'],'box_mac'=>$box_mac,'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                    'integral'=>$now_integral,'content'=>$interact_num,'type'=>2,'integral_time'=>date('Y-m-d H:i:s',$fj_estime));
                $m_userintegralrecord->add($integralrecord_data);

                $m_merchant = new \Admin\Model\Integral\MerchantModel();
                $where = array('hotel_id'=>$task_info['hotel_id'],'status'=>1);
                $m_merchant->where($where)->setInc('integral',$now_integral);
            }
        }
        echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} finish \r\n";
        return true;
    }

    private function task_comment($task_times,$dinner_type,$task_info){
        $begin_time = date('Y-m-d H:i:s',$task_times['fj_bstime']);
        $end_time = date('Y-m-d H:i:s',$task_times['fj_estime']);

        $where = array('hotel_id'=>$task_info['hotel_id'],'staff_id'=>0);
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        $m_comment = new \Admin\Model\Smallapp\CommentModel();
        $res_comment = $m_comment->getDataList('*',$where,'id desc');
        if(empty($res_comment)){
            echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} dinner_type $dinner_type no comment\r\n";
            return true;
        }

        $task_where = array('openid'=>$task_info['hotel_id'],'task_id'=>$task_info['id']);
        $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
        $task_where['fj_type'] = $dinner_type;
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $tmp_exist = $m_userintegralrecord->field('id,task_id,fj_type,integral')->where($task_where)->find();
        if(!empty($tmp_exist) && $tmp_exist['integral']>0){
            $integralrecord = json_encode($tmp_exist);
            echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} had getintegral integralrecord:$integralrecord \r\n";
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
        if($now_integral){
            $task_where = array('openid'=>$task_info['hotel_id'],'task_id'=>$task_info['id']);
            $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_resintegral = $m_userintegralrecord->field('sum(integral) as total_integral')->where($task_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);
            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = $max_daily_integral - $tmp_integral;
                echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} gt max_daily_integral $now_integral \r\n";
            }
        }

        if($now_integral>0){
            $m_box = new \Admin\Model\BoxModel();
            $box_info = $m_box->getHotelInfoByBoxMac($res_comment[0]['box_mac']);

            $integralrecord_data = array('openid'=>$task_info['hotel_id'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$box_info['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$ap_num,'type'=>7,'integral_time'=>$end_time);
            $m_userintegralrecord->add($integralrecord_data);

            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('hotel_id'=>$task_info['hotel_id'],'status'=>1);
            $m_merchant->where($where)->setInc('integral',$now_integral);
        }
        echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} finish \r\n";
        return true;
    }


    private function task_commentreward($task_times,$dinner_type,$task_info){
        $begin_time = date('Y-m-d H:i:s',$task_times['fj_bstime']);
        $end_time = date('Y-m-d H:i:s',$task_times['fj_estime']);

        $where = array('hotel_id'=>$task_info['hotel_id'],'staff_id'=>0);
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        $m_comment = new \Admin\Model\Smallapp\CommentModel();
        $res_comment = $m_comment->getDataList('*',$where,'id desc');
        if(empty($res_comment)){
            echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} dinner_type $dinner_type no comment\r\n";
            return true;
        }
        $m_reward = new \Admin\Model\Smallapp\RewardModel();
        foreach ($res_comment as $k=>$v){
            if($v['reward_id']>0){
                $res_reward = $m_reward->getInfo(array('id'=>$v['reward_id']));
                if($res_reward['status']==2 || $res_reward['status']==3){
                    unset($res_comment[$k]);
                }
            }
        }

        $task_where = array('openid'=>$task_info['hotel_id'],'task_id'=>$task_info['id']);
        $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
        $task_where['fj_type'] = $dinner_type;
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $tmp_exist = $m_userintegralrecord->field('id,task_id,fj_type,integral')->where($task_where)->find();
        if(!empty($tmp_exist) && $tmp_exist['integral']>0){
            $integralrecord = json_encode($tmp_exist);
            echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} had getintegral integralrecord:$integralrecord \r\n";
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
        if($now_integral){
            $task_where = array('openid'=>$task_info['hotel_id'],'task_id'=>$task_info['id']);
            $task_where["DATE_FORMAT(add_time,'%Y-%m-%d')"]=date('Y-m-d');
            $tmp_resintegral = $m_userintegralrecord->field('sum(integral) as total_integral')->where($task_where)->find();
            $tmp_integral = intval($tmp_resintegral['total_integral']);
            if($tmp_integral+$now_integral>$max_daily_integral){
                $now_integral = $max_daily_integral - $tmp_integral;
                echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} gt max_daily_integral $now_integral \r\n";
            }
        }

        if($now_integral>0){
            $m_box = new \Admin\Model\BoxModel();
            $box_info = $m_box->getHotelInfoByBoxMac($res_comment[0]['box_mac']);

            $integralrecord_data = array('openid'=>$task_info['hotel_id'],'area_id'=>$box_info['area_id'],'task_id'=>$task_info['id'],
                'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                'box_id'=>$box_info['box_id'],'box_mac'=>$box_info['box_mac'],'box_type'=>$box_info['box_type'],'fj_type'=>$dinner_type,
                'integral'=>$now_integral,'content'=>$ap_num,'type'=>8,'integral_time'=>$end_time);
            $m_userintegralrecord->add($integralrecord_data);

            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('hotel_id'=>$task_info['hotel_id'],'status'=>1);
            $m_merchant->where($where)->setInc('integral',$now_integral);
        }
        echo "hotel_id:{$task_info['hotel_id']}-task_id:{$task_info['id']} finish \r\n";
        return true;
    }
}