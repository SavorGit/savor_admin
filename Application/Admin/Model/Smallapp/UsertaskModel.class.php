<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class UsertaskModel extends BaseModel{
	protected $tableName='smallapp_usertask';

    public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_integral_task_hotel taskhotel on a.task_hotel_id=taskhotel.id','left')
            ->join('savor_hotel hotel on taskhotel.hotel_id=hotel.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_integral_task_hotel taskhotel on a.task_hotel_id=taskhotel.id','left')
            ->join('savor_hotel hotel on taskhotel.hotel_id=hotel.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

	public function handle_usertask(){
        $date_h = date('H');
        if($date_h==17){
            $dinner_type = 'lunch';//午饭
        }elseif($date_h==23){
            $dinner_type = 'dinner';//晚饭
        }else{
            echo "hour $date_h error \r\n";
            exit;
        }
        $time_date = time();
        $all_meal_time = C('MEAL_TIME');
        $start_time = date("Y-m-d {$all_meal_time["$dinner_type"][0]}:00",$time_date);
        $end_time = date("Y-m-d {$all_meal_time["$dinner_type"][1]}:00",$time_date);

	    $where = array('status'=>1);
        $res_usertask = $this->getDataList('*',$where,'id asc');
	    if(empty($res_usertask)){
	        $now_time = date('Y-m-d H:i:s');
	        echo "nowtime $now_time no task \r\n";
	        exit;
        }
	    $m_task = new \Admin\Model\Integral\TaskModel();
	    $m_hoteltask = new \Admin\Model\Integral\TaskHotelModel();
	    $m_staff = new \Admin\Model\Integral\StaffModel();
	    $m_box = new \Admin\Model\BoxModel();
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_usetaskrecord = new \Admin\Model\Smallapp\UsertaskrecordModel();
        $m_activity_apply = new \Admin\Model\Smallapp\ActivityapplyModel();
	    $all_box_types = C('heart_hotel_box_type');
	    foreach ($res_usertask as $v){
	        $task_id = $v['task_id'];
	        $task_hotel_id = $v['task_hotel_id'];
	        $res_task = $m_task->getInfo(array('id'=>$task_id));
	        $now_time = date('Y-m-d H:i:s');
	        if($res_task['end_time']<$now_time){
	            $this->updateData(array('id'=>$v['id']),array('status'=>3));
                "ID:{$v['id']} task_id:{$task_id} has overtime {$res_task['end_time']} \r\n";
                continue;
            }
	        $hotel_task_info = $m_hoteltask->getInfo(array('id'=>$task_hotel_id));
	        $hotel_id = $hotel_task_info['hotel_id'];
	        $res_staff = $m_staff->getInfo(array('openid'=>$v['openid'],'status'=>1));
            if($res_staff['hotel_id']!=$hotel_id || empty($res_staff['room_ids'])){
                echo "ID:{$v['id']} task_id:{$task_id} no room hotel_id:{$res_staff['hotel_id']} room_ids:{$res_staff['room_ids']} \r\n";
                continue;
            }
            $room_ids = explode(',',trim($res_staff['room_ids'],','));
            $bfields = 'hotel.id as hotel_id,hotel.name as hotel_name,room.id as room_id,room.name as room_name,
            box.id as box_id,box.name as box_name,box.mac as box_mac';
            $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
            if(count($room_ids)>1){
                $bwhere['box.room_id'] = array('in',$room_ids);
            }else{
                $bwhere['box.room_id'] = $room_ids[0];
            }
            $bwhere['box.box_type'] = array('in',array_keys($all_box_types));
            echo "ID:{$v['id']} task_id:{$task_id} \r\n";
            $res_box = $m_box->getBoxByCondition($bfields,$bwhere);
            if(!empty($res_box)){
                $task_meal_num = $hotel_task_info['meal_num'];
                $task_interact_num = $hotel_task_info['interact_num'];
                $task_comment_num = $hotel_task_info['comment_num'];
                $task_lottery_num = $hotel_task_info['lottery_num'];

                foreach ($res_box as $bv){
                    $u_task = $this->getInfo(array('id'=>$v['id']));
                    if($u_task['status']==2){
                        echo "ID:{$v['id']} task_id:{$task_id} has finish \r\n";
                        break;
                    }
                    $meal_num = $interact_num = $comment_num = $lottery_num = 0;
                    $forscreen_where = array('a.hotel_id'=>$hotel_id,'a.box_mac'=>$bv['box_mac'],'a.is_valid'=>1);
                    $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                    $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                    $forscreen_where['a.small_app_id'] = array('in',array(1,2));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
                    $task_num = 0;
                    if($task_interact_num>0){
                        $task_num++;
                        //统计互动数
                        $fields = "count(a.id) as interact_num";
                        $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                        if(!empty($res_forscreen)){
                            $interact_num = intval($res_forscreen[0]['interact_num']);
                        }
                    }
                    if($task_comment_num>0){
                        $task_num++;
                        //统计评论数
                        $forscreen_where['a.action'] = 52;
                        $fields = "count(a.id) as interact_num";
                        $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                        if(!empty($res_forscreen)){
                            $comment_num = intval($res_forscreen[0]['interact_num']);
                            $interact_num = $interact_num - $comment_num;
                        }
                    }
                    if($task_meal_num>0){
                        $task_num++;
                        //统计饭局数
                        if($interact_num>0 || $comment_num>0){
                            $meal_num = 1;
                        }
                    }
                    if($task_lottery_num>0){
                        $task_num++;
                        $apply_fields = 'count(a.id) as num';
                        $apply_where = array('activity.hotel_id'=>$hotel_id,'activity.type'=>1,'a.box_mac'=>$bv['box_mac']);
                        $apply_where['a.add_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                        $res_apply = $m_activity_apply->getApplyDatas($apply_fields,$apply_where,'a.id desc','0，1','');
                        if(!empty($res_apply)){
                            $lottery_num = intval($res_apply[0]['num']);
                        }
                    }
                    //计算用户获得现金
                    $meal_money = $interact_money = $comment_money = $lottery_money = 0;
                    $last_money = 0.3*$u_task['money'];
                    if($task_meal_num>0 && $meal_num>0){
                        if($u_task['meal_num']+$meal_num>$task_meal_num){
                            $meal_num = $task_meal_num-$u_task['meal_num'];
                        }
                        $meal_money = $last_money/$task_num/$task_meal_num * $meal_num;
                    }
                    if($task_interact_num>0 && $interact_num>0){
                        if($u_task['interact_num']+$interact_num>$task_interact_num){
                            $interact_num = $task_interact_num-$u_task['interact_num'];
                        }
                        $interact_money = $last_money/$task_num/$task_interact_num * $interact_num;
                    }
                    if($task_comment_num>0 && $comment_num>0){
                        if($u_task['comment_num']+$comment_num>$task_comment_num){
                            $comment_num = $task_comment_num-$u_task['comment_num'];
                        }
                        $comment_money = $last_money/$task_num/$task_comment_num * $comment_num;
                    }
                    if($task_lottery_num>0 && $lottery_num>0){
                        if($u_task['lottery_num']+$lottery_num>$task_lottery_num){
                            $lottery_num = $task_lottery_num-$u_task['lottery_num'];
                        }
                        $lottery_money = $last_money/$task_num/$task_lottery_num * $lottery_num;
                    }

                    if($meal_num>0 || $interact_num>0 || $comment_num>0 || $lottery_money>0){
                        echo "ID:{$v['id']} task_id:{$task_id} box_mac:{$bv['box_mac']} meal_num:$meal_num,interact_num:$interact_num,comment_num:$comment_num,lottery_num:$lottery_num \r\n";

                        $get_money = $u_task['get_money'] + $meal_money + $interact_money + $comment_money + $lottery_money;
                        $get_money = sprintf("%.2f",$get_money);
                        $up_usertask_data = array('meal_num'=>$u_task['meal_num']+$meal_num,
                            'interact_num'=>$u_task['interact_num']+$interact_num,
                            'comment_num'=>$u_task['comment_num']+$comment_num,'lottery_num'=>$u_task['lottery_num']+$lottery_num,
                            'get_money'=>$get_money
                        );

                        $task_num_eq = 0;
                        if($up_usertask_data['meal_num']==$task_meal_num && $up_usertask_data['interact_num']==$task_interact_num
                            && $up_usertask_data['comment_num']==$task_comment_num && $up_usertask_data['lottery_num']==$task_lottery_num){
                            $task_num_eq = 1;
                        }
                        echo "ID:{$v['id']} task_id:{$task_id} money:{$res_task['money']} get_money:$get_money task_num_eq:$task_num_eq \r\n";

                        if($get_money>=$res_task['money'] || $task_num_eq==1){
                            $up_usertask_data['get_money'] = $u_task['money'];
                            $up_usertask_data['finish_time'] = date('Y-m-d H:i:s');
                            $up_usertask_data['status'] = 2;
                        }
                        $this->updateData(array('id'=>$u_task['id']),$up_usertask_data);
                        //end

                        //记录日志
                        $add_record_data = array('openid'=>$v['openid'],'hotel_id'=>$bv['hotel_id'],'hotel_name'=>$bv['hotel_name'],
                            'room_id'=>$bv['room_id'],'room_name'=>$bv['room_name'],'box_id'=>$bv['box_id'],'box_name'=>$bv['box_name'],
                            'box_mac'=>$bv['box_mac'],'task_hotel_id'=>$task_hotel_id,'usertask_id'=>$u_task['id'],
                            'meal_num'=>$meal_num,'interact_num'=>$interact_num,'comment_num'=>$comment_num,'lottery_num'=>$lottery_num,'type'=>1
                        );
                        if($dinner_type=='lunch'){
                            $m_usetaskrecord->add($add_record_data);
                        }else{
                            $urwhere = array('openid'=>$v['openid'],'type'=>1,'hotel_id'=>$hotel_id,'box_mac'=>$bv['box_mac'],'usertask_id'=>$u_task['id']);
                            $urwhere['DATE(add_time)'] = date('Y-m-d',$time_date);
                            $res_ur = $m_usetaskrecord->getInfo(array($urwhere));
                            if(!empty($res_ur)){
                                $add_record_data['meal_num'] = $res_ur['meal_num'] + $add_record_data['meal_num'];
                                $add_record_data['interact_num'] = $res_ur['interact_num'] + $add_record_data['interact_num'];
                                $add_record_data['comment_num'] = $res_ur['comment_num'] + $add_record_data['comment_num'];
                                $add_record_data['lottery_num'] = $res_ur['lottery_num'] + $add_record_data['lottery_num'];
                                $m_usetaskrecord->updateData(array('id'=>$res_ur['id']),$add_record_data);
                            }else{
                                $m_usetaskrecord->add($add_record_data);
                            }
                        }
                    }else{
                        echo "ID:{$v['id']} task_id:{$task_id} box_mac:{$bv['box_mac']} no data \r\n";
                        continue;
                    }

                }
            }
        }
    }

}