<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class BoxincomeModel extends BaseModel{
	protected $tableName='smallapp_boxincome';

	public function handle_boxincome(){
        $m_hoteltask = new \Admin\Model\Integral\TaskHotelModel();
        $fields = 'hoteltask.hotel_id';
        $where = array('task.task_type'=>21,'task.status'=>1,'task.flag'=>1);
        $res_hotel = $m_hoteltask->getHoteltasks($fields,$where,'hoteltask.hotel_id');
        if(empty($res_hotel)){
            echo "no hotel has money \r\n";
            exit;
        }
        $time_date = strtotime("-1 day");
        $all_meal_time = C('MEAL_TIME');
        $all_box_types = C('heart_hotel_box_type');
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_box = new \Admin\Model\BoxModel();
        foreach ($res_hotel as $v){
            $hotel_id = $v['hotel_id'];
            $sfields = 'a.room_ids';
            $swhere = array('m.hotel_id'=>$hotel_id,'m.status'=>1,
                'a.status'=>1,'a.hotel_id'=>$hotel_id);
            $swhere['a.room_ids'] = array('neq','');
            $res_staff = $m_staff->getMerchantStaffList($sfields,$swhere);
            $room_ids = array();
            if(!empty($res_staff)){
                foreach ($res_staff as $sv){
                    $tmp_room_ids = explode(',',trim($sv['room_ids'],','));
                    foreach ($tmp_room_ids as $rid){
                        $room_ids[]=$rid;
                    }
                }
            }
            $bfields = 'hotel.id as hotel_id,hotel.name as hotel_name,room.id as room_id,room.name as room_name,
            box.id as box_id,box.name as box_name,box.mac as box_mac';
            $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
            if(!empty($room_ids)){
                $bwhere['box.room_id'] = array('not in',$room_ids);
            }
            $bwhere['box.box_type'] = array('in',array_keys($all_box_types));
            $res_box = $m_box->getBoxByCondition($bfields,$bwhere);
            if(!empty($res_box)){
                $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
                foreach ($res_box as $bv){
                    $dinner_type = 'lunch';
                    $lunch_meal_num = $lunch_interact_num = $lunch_comment_num = 0;
                    $start_time = date("Y-m-d {$all_meal_time["$dinner_type"][0]}:00",$time_date);
                    $end_time = date("Y-m-d {$all_meal_time["$dinner_type"][1]}:00",$time_date);
                    $forscreen_where = array('a.hotel_id'=>$hotel_id,'a.box_mac'=>$bv['box_mac'],'a.is_valid'=>1);
                    $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                    $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                    $forscreen_where['a.small_app_id'] = array('in',array(1,2));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
                    //统计互动数
                    $fields = "count(a.id) as interact_num";
                    $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                    if(!empty($res_forscreen)){
                        $lunch_interact_num = intval($res_forscreen[0]['interact_num']);
                    }

                    //统计评论数
                    $forscreen_where['a.action'] = 52;
                    $fields = "count(a.id) as interact_num";
                    $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                    if(!empty($res_forscreen)){
                        $lunch_comment_num = intval($res_forscreen[0]['interact_num']);
                        $lunch_interact_num = $lunch_interact_num - $lunch_comment_num;
                    }

                    //统计饭局数
                    if($lunch_interact_num>0 || $lunch_comment_num>0){
                        $lunch_meal_num = 1;
                    }

                    $dinner_type = 'dinner';
                    $dinner_meal_num = $dinner_interact_num = $dinner_comment_num = 0;
                    $start_time = date("Y-m-d {$all_meal_time["$dinner_type"][0]}:00",$time_date);
                    $end_time = date("Y-m-d {$all_meal_time["$dinner_type"][1]}:00",$time_date);
                    $forscreen_where = array('a.hotel_id'=>$hotel_id,'a.box_mac'=>$bv['box_mac'],'a.is_valid'=>1);
                    $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                    $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                    $forscreen_where['a.small_app_id'] = array('in',array(1,2));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏

                    //统计互动数
                    $fields = "count(a.id) as interact_num";
                    $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                    if(!empty($res_forscreen)){
                        $dinner_interact_num = intval($res_forscreen[0]['interact_num']);
                    }

                    //统计评论数
                    $forscreen_where['a.action'] = 52;
                    $fields = "count(a.id) as interact_num";
                    $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','');
                    if(!empty($res_forscreen)){
                        $dinner_comment_num = intval($res_forscreen[0]['interact_num']);
                        $dinner_interact_num = $dinner_interact_num - $dinner_comment_num;
                    }

                    //统计饭局数
                    if($dinner_interact_num>0 || $dinner_comment_num>0){
                        $dinner_meal_num = 1;
                    }

                    $meal_num = $lunch_meal_num + $dinner_meal_num;
                    $interact_num = $lunch_interact_num + $dinner_interact_num;
                    $comment_num = $lunch_comment_num + $dinner_comment_num;
                    if($meal_num>0 || $interact_num>0 || $comment_num>0){
                        echo "hotel_id:{$bv['hotel_id']}  box_mac:{$bv['box_mac']} meal_num:$meal_num,interact_num:$interact_num,comment_num:$comment_num \r\n";
                        $static_date = date('Y-m-d',$time_date);
                        $add_data = array('hotel_id'=>$bv['hotel_id'],'hotel_name'=>$bv['hotel_name'],'room_id'=>$bv['room_id'],
                            'room_name'=>$bv['room_name'],'box_id'=>$bv['box_id'],'box_name'=>$bv['box_name'],'box_mac'=>$bv['box_mac'],
                            'meal_num'=>$meal_num,'interact_num'=>$interact_num,'comment_num'=>$comment_num,'static_date'=>$static_date
                        );
                        $this->add($add_data);
                    }else{
                        echo "hotel_id:{$bv['hotel_id']}  box_mac:{$bv['box_mac']} no tasknum \r\n";
                    }

                }
            }

        }


    }

}