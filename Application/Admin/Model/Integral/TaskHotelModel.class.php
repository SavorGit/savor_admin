<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class TaskHotelModel extends BaseModel{
	
	protected $tableName='integral_task_hotel';
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
        $where = array('task.task_type'=>array('in',array(4,5)));
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

        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $all_config = $m_sysconfig->getAllconfig();
        $integral_boxmac = $all_config['integral_boxmac'];

        foreach ($res_data as $v){
            $task_id = $v['id'];
            $task_info = $v;
            $task_info['integral_boxmac'] = $integral_boxmac;
            $task_content = json_decode($task_info['task_info'],true);
            $task_type = $task_content['task_type'];//1开机 2互动 3活动推广 4邀请食客评价 5打赏补贴

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
            switch ($task_type){
                case 4:
                    $this->task_comment($task_times,$dinner_type,$task_info);
                    break;
                case 5:
                    $this->task_commentreward($task_times,$dinner_type,$task_info);
                    break;
            }


        }
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