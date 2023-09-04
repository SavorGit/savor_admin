<?php
namespace Crontab\Controller;
use Think\Controller;

class OpstaskController extends Controller{

    public function releasetask(){
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_room = new \Admin\Model\RoomModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $m_taskhotel = new \Admin\Model\Integral\TaskHotelModel();
        $m_crm_taskhotel = new \Admin\Model\Crm\TaskHotelModel();
        $redis = new \Common\Lib\SavorRedis();
        $cache_key = C('FINANCE_HOTELSTOCK');
        $m_crmtask = new \Admin\Model\Crm\TaskModel();
        $alltask = $m_crmtask->getAllData('*',array('status'=>1,'end_time'=>array('egt',date('Y-m-d H:i:s'))));

        $sql = "select hotel.id as hotel_id,hotel.name as hotel_name,ext.residenter_id,sysuser.remark as residenter_name from savor_hotel as hotel 
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            left join savor_sysuser as sysuser on ext.residenter_id=sysuser.id 
            where hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 order by hotel.id asc ";
        $model = M();
        $res_hotel = $model->query($sql);
        foreach ($res_hotel as $v){
            $hotel_id = $v['hotel_id'];
            $residenter_id = $v['residenter_id'];
            $residenter_name = $v['residenter_name'];
            if($residenter_id==0){
                $residenter_name = '';
            }
            foreach ($alltask as $task){
                $rfields = 'id,task_id,status,handle_status,audit_handle_status,integral_task_id';
                $rtwhere = array('hotel_id'=>$hotel_id,'residenter_id'=>$residenter_id,'task_id'=>$task['id']);
                $now_residenter_task = $m_crmtask_record->getAll($rfields,$rtwhere,0,1,'id desc');

                $add_data = array('task_id'=>$task['id'],'hotel_id'=>$hotel_id,'residenter_id'=>$residenter_id,'residenter_name'=>$residenter_name);
                $task_type = $task['type'];
                switch ($task_type){
                    case 1:
                        if(!empty($now_residenter_task)){
                            continue;
                        }
                        $mfields = 'count(a.id) as num';
                        $res_staff = $m_staff->getMerchantStaffList($mfields,array('m.hotel_id'=>$hotel_id,'m.status'=>1,'a.level'=>2));
                        $staff_num = intval($res_staff[0]['num']);
                        if($staff_num<$task['sale_manager_num']){
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 2:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $hotel_cache_key = $cache_key.":$hotel_id";
                        $redis->select(9);
                        $res_hotel_cache = $redis->get($hotel_cache_key);
                        $cate_num=$stock_num=0;
                        if(!empty($res_hotel_cache)){
                            $cache_data = json_decode($res_hotel_cache,true);
                            $cate_num = count($cache_data['goods_ids']);
                            foreach ($cache_data as $cv){
                                $stock_num+=$cv['stock_num'];
                            }
                        }
                        if($cate_num<$task['cate_num'] || $stock_num<$task['stock_num']){
                            if($cate_num<$task['cate_num']){
                                $remind_content = "店内酒水库存种类不足{$task['cate_num']}类，请及时为餐厅补货。";
                            }else{
                                $remind_content = "店内酒水库存不足{$task['stock_num']}瓶，请及时为餐厅补货。";
                            }
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 3:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'record.wo_reason_type'=>1,'record.wo_status'=>2);
                        $sfields = 'count(a.id) as num,sum(a.settlement_price) as money';
                        $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
                        if($res_sale[0]['num']>0){
                            $remind_content = "店内售卖了{$res_sale[0]['num']}瓶酒水，尚有{$res_sale[0]['money']}元未回款，请及时处理。";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);

                        }
                        break;
                    case 4:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'a.is_expire'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
                        $sfields = 'sum(a.settlement_price) as money,min(a.add_time) as sale_min_add_time';
                        $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
                        if($res_sale[0]['money']>0){
                            $no_pay_time = (time()-$res_sale[0]['sale_min_add_time'])/86400;
                            $pay_day = round($no_pay_time);
                            $remind_content = "店内有{$res_sale[0]['money']}元超期欠款未收回，最长欠款时间{$pay_day}天，请及时回款。";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 5:
                        if(!empty($now_residenter_task)){
                            continue;
                        }
                        $rwhere = array('hotel.id'=>$hotel_id,'room.state'=>1,'room.flag'=>0);
                        $res_room_num = $m_room->getRoomByCondition('count(room.id) as num',$rwhere);
                        if($res_room_num[0]['num']==0){
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 6:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $task_finish_day = $task['task_finish_day']+1;
                        $task_finish_rate = $task['task_finish_rate'];
                        $start_date = date('Y-m-d',strtotime("-$task_finish_day day"));
                        $end_date = date('Y-m-d',strtotime('-1 day'));
                        $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
                            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
                        $staff_where = array('a.hotel_id'=>$hotel_id);
                        $staff_where['a.static_date'] = array(array('egt',$start_date),array('elt',$end_date), 'and');
                        $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
                        $task_invitation_finish_rate = sprintf("%.2f",$res_task_data[0]['task_invitation_finish_num']/$res_task_data[0]['task_invitation_operate_num']);
                        if($task_invitation_finish_rate<$task_finish_rate){
                            $remind_content = "此店邀请函发送频率较低，请发动经理使用邀请函";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 7:
                        $task_fields = 'task.id';
                        $task_where = array('task.task_type'=>29,'task.status'=>1,'hoteltask.hotel_id'=>$hotel_id);
                        $task_where['task.end_time'] = array('egt',date('Y-m-d H:i:s'));
                        $res_check_task = $m_taskhotel->getHoteltasks($task_fields,$task_where,'');
                        if(!empty($res_check_task)){
                            if($res_check_task[0]['id']==$now_residenter_task[0]['integral_task_id']){
                                continue;
                            }
                            $add_data['integral_task_id'] = $res_check_task[0]['id'];
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 8:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $task_finish_day = $task['task_finish_day']+1;
                        $task_finish_rate = $task['task_finish_rate'];
                        $start_date = date('Y-m-d',strtotime("-$task_finish_day day"));
                        $end_date = date('Y-m-d',strtotime('-1 day'));
                        $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
                            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
                        $staff_where = array('a.hotel_id'=>$hotel_id);
                        $staff_where['a.static_date'] = array(array('egt',$start_date),array('elt',$end_date), 'and');
                        $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
                        $task_demand_finish_rate = sprintf("%.2f",$res_task_data[0]['task_demand_finish_num']/$res_task_data[0]['task_demand_operate_num']);
                        if($task_demand_finish_rate<$task_finish_rate){
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 9:
                        if(!empty($now_residenter_task) && $now_residenter_task[0]['status']!=3){
                            continue;
                        }
                        $res_box = $m_box->getBoxByCondition('box.mac',array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0));
                        if(!empty($res_box)){
                            $boxs = array();
                            $day_time = $task['task_finish_day']*86400;
                            foreach ($res_box as $bv){
                                $redis->select(13);
                                $cache_key  = 'heartbeat:2:'.$bv['mac'];
                                $res_cache = $redis->get($cache_key);
                                if(!empty($res_cache)){
                                    $cache_data = json_decode($res_cache,true);
                                    $report_time = strtotime($cache_data['date']);
                                    $diff_time = time() - $report_time;
                                    if($diff_time>$day_time){
                                        $boxs[]=$bv['mac'];
                                    }
                                }
                            }
                            if(!empty($boxs)){
                                $box_str = join('、',$boxs);
                                $remind_content = "{$box_str}设备已失联超过{$task['task_finish_day']}天，请及时处理";
                                $add_data['remind_content'] = $remind_content;
                                $m_crmtask_record->add($add_data);
                            }
                        }
                        break;
                    case 10:
                        if(!empty($now_residenter_task)){
                            continue;
                        }
                        $remind_content = "此店为将餐厅人员加至企业微信，添加后请上传截图。";
                        $add_data['remind_content'] = $remind_content;
                        $m_crmtask_record->add($add_data);
                        break;
                    case 11:
                        if(!empty($now_residenter_task)){
                            continue;
                        }
                        $res_has_hotel = $m_crm_taskhotel->getInfo(array('task_id'=>$task['id'],'hotel_id'=>$hotel_id));
                        if(!empty($res_has_hotel)){
                            $remind_content = "此任务需要定位或上传照片，请在销售记录中完成任务处理。";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                }
            }
            echo "hotel_id:$hotel_id ok \r\n";
        }
    }

    public function finishtype1(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(1);
        $m_staff = new \Admin\Model\Integral\StaffModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];

            $mfields = 'count(a.id) as num';
            $res_staff = $m_staff->getMerchantStaffList($mfields,array('m.hotel_id'=>$hotel_id,'m.status'=>1,'a.level'=>2));
            $staff_num = intval($res_staff[0]['num']);
            $is_finish = 0;
            if($staff_num>=$v['sale_manager_num']){
                $is_finish = 1;
            }

            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype2(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(2);
        $redis = new \Common\Lib\SavorRedis();
        $cache_key = C('FINANCE_HOTELSTOCK');
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];

            $hotel_cache_key = $cache_key.":$hotel_id";
            $redis->select(9);
            $res_hotel_cache = $redis->get($hotel_cache_key);
            $cate_num=$stock_num=0;
            if(!empty($res_hotel_cache)){
                $cache_data = json_decode($res_hotel_cache,true);
                $cate_num = count($cache_data['goods_ids']);
                foreach ($cache_data as $cv){
                    $stock_num+=$cv['stock_num'];
                }
            }
            $is_finish = 0;
            if($cate_num>=$v['cate_num'] && $stock_num>=$v['stock_num']){
                $is_finish = 1;
            }

            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype3(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(3);
        $m_sale = new \Admin\Model\FinanceSaleModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];
            $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $sfields = 'count(a.id) as num,sum(a.settlement_price) as money';
            $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
            $is_finish = 0;
            if($res_sale[0]['num']>0){
                $remind_content = "店内售卖了{$res_sale[0]['num']}瓶酒水，尚有{$res_sale[0]['money']}元未回款，请及时处理。";
                $m_crmtask_record->updateData(array('id'=>$v['id']),array('remind_content'=>$remind_content));
            }else{
                $is_finish = 1;
            }
            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype4(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(4);
        $m_sale = new \Admin\Model\FinanceSaleModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];
            $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'a.is_expire'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $sfields = 'sum(a.settlement_price) as money,min(a.add_time) as sale_min_add_time';
            $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
            $is_finish = 0;
            if($res_sale[0]['money']>0){
                $no_pay_time = (time()-$res_sale[0]['sale_min_add_time'])/86400;
                $pay_day = round($no_pay_time);
                $remind_content = "店内有{$res_sale[0]['money']}元超期欠款未收回，最长欠款时间{$pay_day}天，请及时回款。";
                $m_crmtask_record->updateData(array('id'=>$v['id']),array('remind_content'=>$remind_content));
            }else{
                $is_finish = 1;
            }

            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype5(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(5);
        $m_room = new \Admin\Model\RoomModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];
            $rwhere = array('hotel.id'=>$hotel_id,'room.state'=>1,'room.flag'=>0);
            $res_room_num = $m_room->getRoomByCondition('count(room.id) as num',$rwhere);
            $is_finish = 0;
            if($res_room_num[0]['num']>0){
                $is_finish = 1;
            }

            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype6(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(6);
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];


            $task_finish_day = $v['task_finish_day']+1;
            $task_finish_rate = $v['task_finish_rate'];
            $start_date = date('Y-m-d',strtotime("-$task_finish_day day"));
            $end_date = date('Y-m-d',strtotime('-1 day'));
            $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
                            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
            $staff_where = array('a.hotel_id'=>$hotel_id);
            $staff_where['a.static_date'] = array(array('egt',$start_date),array('elt',$end_date), 'and');
            $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
            $task_invitation_finish_rate = sprintf("%.2f",$res_task_data[0]['task_invitation_finish_num']/$res_task_data[0]['task_invitation_operate_num']);
            $is_finish = 0;
            if($task_invitation_finish_rate>=$task_finish_rate){
                $is_finish = 1;
            }
            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype7(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(7);
        $m_stockcheck = new \Admin\Model\Smallapp\StockcheckModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];
            $integral_task_id = $v['integral_task_id'];
            $res_stock_check = $m_stockcheck->getInfo(array('hotel_id'=>$hotel_id,'task_id'=>$integral_task_id));
            $is_finish = 0;
            if(!empty($res_stock_check)){
                $is_finish = 1;
            }

            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype8(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(8);
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];

            $task_finish_day = $v['task_finish_day']+1;
            $task_finish_rate = $v['task_finish_rate'];
            $start_date = date('Y-m-d',strtotime("-$task_finish_day day"));
            $end_date = date('Y-m-d',strtotime('-1 day'));
            $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
                            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
            $staff_where = array('a.hotel_id'=>$hotel_id);
            $staff_where['a.static_date'] = array(array('egt',$start_date),array('elt',$end_date), 'and');
            $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
            $task_demand_finish_rate = sprintf("%.2f",$res_task_data[0]['task_demand_finish_num']/$res_task_data[0]['task_demand_operate_num']);
            $is_finish = 0;
            if($task_demand_finish_rate>=$task_finish_rate){
                $is_finish = 1;
            }
            $this->handle_task_record($is_finish,$v,$m_crmtask_record);
        }
    }

    public function finishtype9(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(9);
        $redis = new \Common\Lib\SavorRedis();
        $m_box = new \Admin\Model\BoxModel();
        foreach ($res_task_record as $v){
            $hotel_id = $v['hotel_id'];

            $res_box = $m_box->getBoxByCondition('box.mac',array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0));
            if(!empty($res_box)){
                $boxs = array();
                $day_time = $v['task_finish_day']*86400;
                foreach ($res_box as $bv){
                    $redis->select(13);
                    $cache_key  = 'heartbeat:2:'.$bv['mac'];
                    $res_cache = $redis->get($cache_key);
                    if(!empty($res_cache)){
                        $cache_data = json_decode($res_cache,true);
                        $report_time = strtotime($cache_data['date']);
                        $diff_time = time() - $report_time;
                        if($diff_time>$day_time){
                            $boxs[]=$bv['mac'];
                        }
                    }
                }
                if(!empty($boxs)){
                    $is_finish = 0;
                    $box_str = join('、',$boxs);
                    $remind_content = "{$box_str}设备已失联超过{$v['task_finish_day']}天，请及时处理";
                    $m_crmtask_record->updateData(array('id'=>$v['id']),array('remind_content'=>$remind_content));
                }else{
                    $is_finish = 1;
                }
                $this->handle_task_record($is_finish,$v,$m_crmtask_record);
            }
        }
    }




    private function handle_task_record($is_finish,$info,$m_crmtask_record){
        if($is_finish){
            $updata = array('finish_time'=>date('Y-m-d H:i:s'),'status'=>3,'form_type'=>2,'update_time'=>date('Y-m-d H:i:s'));
            $m_crmtask_record->updateData(array('id'=>$info['id']),$updata);
        }else{
            if($info['is_trigger']==0){
                $notify_day_time = $info['notify_day']*86400;
                $diff_notify_time = time()-$info['add_time'];
                if($diff_notify_time>=$notify_day_time){
                    $updata = array('trigger_time'=>date('Y-m-d H:i:s'),'is_trigger'=>1,'update_time'=>date('Y-m-d H:i:s'));
                    $m_crmtask_record->updateData(array('id'=>$info['id']),$updata);
                }
            }

            $notify_handle_day_time = $info['notify_handle_day']*86400;
            if($info['reset_time']=='0000-00-00 00:00:00'){
                $reset_time = $info['add_time'];
            }else{
                $reset_time = $info['reset_time'];
            }
            $diff_notify_time = time()-$reset_time;
            if($diff_notify_time>=$notify_handle_day_time){
                $updata = array('status'=>0,'handle_status'=>0,'handle_time'=>'0000-00-00 00:00:00','reset_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'));
                $m_crmtask_record->updateData(array('id'=>$info['id']),$updata);
            }
        }
    }

}
