<?php
namespace Crontab\Controller;
use Think\Controller;

class OpstaskController extends Controller{

    public function releasetask(){
        $now_time = date('Y-m-d H:i:s');
        echo "releasetask start:$now_time \r\n";
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_room = new \Admin\Model\RoomModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $m_taskhotel = new \Admin\Model\Integral\TaskHotelModel();
        $m_crm_taskhotel = new \Admin\Model\Crm\TaskHotelModel();
        $m_sysuser = new \Admin\Model\UserModel();
        $redis = new \Common\Lib\SavorRedis();
        $cache_hotel_stock_key = C('FINANCE_HOTELSTOCK');
        $m_crmtask = new \Admin\Model\Crm\TaskModel();
        $alltask = $m_crmtask->getAllData('*',array('status'=>1,'end_time'=>array('egt',date('Y-m-d H:i:s'))));

        $sql = "select hotel.id as hotel_id,hotel.name as hotel_name,ext.residenter_id,sysuser.remark as residenter_name from savor_hotel as hotel 
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            left join savor_sysuser as sysuser on ext.residenter_id=sysuser.id 
            where hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 order by hotel.id asc ";
        $model = M();
        $res_hotel = $model->query($sql);
        $now_month = date('Ym');
        foreach ($res_hotel as $v){
            $hotel_id = $v['hotel_id'];
            $residenter_id = $v['residenter_id'];
            $residenter_name = $v['residenter_name'];
            if($residenter_id==0){
                $residenter_name = '';
            }else{
                $res_sysuser = $m_sysuser->getUserInfo($residenter_id);
                if($res_sysuser['status']==2){
                    $residenter_id = 0;
                    $residenter_name = '';
                }
            }
            foreach ($alltask as $task){
                $rfields = 'id,task_id,status,handle_status,audit_handle_status,integral_task_id,add_time';
                $rtwhere = array('hotel_id'=>$hotel_id,'residenter_id'=>$residenter_id,'task_id'=>$task['id'],'off_state'=>1);
                $now_residenter_task = $m_crmtask_record->getAll($rfields,$rtwhere,0,1,'id desc');
                $task_month = 0;
                if(!empty($now_residenter_task)){
                    $task_month = date('Ym',strtotime($now_residenter_task[0]['add_time']));
                }

                $add_data = array('task_id'=>$task['id'],'hotel_id'=>$hotel_id,'residenter_id'=>$residenter_id,'residenter_name'=>$residenter_name);
                $task_type = $task['type'];
                switch ($task_type){
                    case 1:
                        if(($task_month>0 && $now_residenter_task[0]['status']==3) || $now_month==$task_month){
                            continue;
                        }
                        $mfields = 'count(a.id) as num';
                        $res_staff = $m_staff->getMerchantStaffList($mfields,array('m.hotel_id'=>$hotel_id,'m.status'=>1,'a.level'=>2));
                        $staff_num = intval($res_staff[0]['num']);
                        if($staff_num<$task['sale_manager_num']){
                            $remind_content = "店内销售端人数少于{$task['sale_manager_num']}人，请尽快开通";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 2:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
                        }

                        $hotel_cache_key = $cache_hotel_stock_key.":$hotel_id";
                        $redis->select(9);
                        $res_hotel_cache = $redis->get($hotel_cache_key);
                        $cate_num=$stock_num=0;
                        if(!empty($res_hotel_cache)){
                            $cache_data = json_decode($res_hotel_cache,true);
                            $cate_num = count($cache_data['goods_ids']);
                            foreach ($cache_data['goods_list'] as $cv){
                                $stock_num+=$cv['stock_num'];
                            }
                        }
                        if($cate_num<$task['cate_num'] || $stock_num<$task['stock_num']){
                            echo "hotel_id:{$hotel_id} $cate_num<{$task['cate_num']} || $stock_num<{$task['stock_num']} \r\n";
                            $remind_content = "店内酒水库存不足，请及时为餐厅补货";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 3:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
                        }
                        $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'record.wo_reason_type'=>1,'record.wo_status'=>2);
                        $sfields = 'count(a.id) as num,sum(a.settlement_price) as money';
                        $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
                        if($res_sale[0]['num']>0){
                            $remind_content = "店内售卖了{$res_sale[0]['num']}瓶酒水，产生{$res_sale[0]['money']}元欠款，请及时处理";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);

                        }
                        break;
                    case 4:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
                        }
                        $swhere = array('a.hotel_id'=>$hotel_id,'a.ptype'=>array('in','0,2'),'a.is_expire'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
                        $sfields = 'sum(a.settlement_price) as money,min(a.add_time) as sale_min_add_time';
                        $res_sale = $m_sale->getSaleStockRecordList($sfields,$swhere,'','');
                        if($res_sale[0]['money']>0){
                            $no_pay_time = (time()-strtotime($res_sale[0]['sale_min_add_time']))/86400;
                            $pay_day = round($no_pay_time);
                            $remind_content = "店内有{$res_sale[0]['money']}元欠款已超期，请及时催收";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 5:
                        if(($task_month>0 && $now_residenter_task[0]['status']==3) || $now_month==$task_month){
                            continue;
                        }

                        $rwhere = array('hotel.id'=>$hotel_id,'room.state'=>1,'room.flag'=>0);
                        $res_room_num = $m_room->getRoomByCondition('count(room.id) as num',$rwhere);
                        if($res_room_num[0]['num']==0){
                            $remind_content = '该店运维端无包间信息，请及时完善';
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 6:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
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
                            $remind_content = "该店邀请函使用频率较低，请发动经理使用邀请函";
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
                            }else{
                                if($now_residenter_task[0]['status']==0 || $now_residenter_task[0]['status']==1){
                                    $m_crmtask_record->updateData(array('id'=>$now_residenter_task[0]['id']),array('status'=>2));
                                }
                            }
                            $add_data['integral_task_id'] = $res_check_task[0]['id'];
                            $remind_content = "餐厅端盘点任务已下发，请及时完成";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 8:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
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
                            $remind_content = "该店点播任务完成率低，请发动经理完成点播";
                            $add_data['remind_content'] = $remind_content;
                            $m_crmtask_record->add($add_data);
                        }
                        break;
                    case 9:
                        if($task_month>0){
                            if($now_month==$task_month && $now_residenter_task[0]['status']!=3){
                                continue;
                            }
                        }
                        $res_box = $m_box->getBoxByCondition('box.mac,box.name',array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0));
                        if(!empty($res_box)){
                            $boxs = array();
                            $day_time = $task['task_finish_day']*86400;
                            foreach ($res_box as $bv){
                                $redis->select(13);
                                $box_cache_key  = 'heartbeat:2:'.$bv['mac'];
                                $res_cache = $redis->get($box_cache_key);
                                if(!empty($res_cache)){
                                    $cache_data = json_decode($res_cache,true);
                                    $report_time = strtotime($cache_data['date']);
                                    $diff_time = time() - $report_time;
                                    if($diff_time>$day_time){
                                        $boxs[]=$bv['name'];
                                    }
                                }else{
                                    $boxs[]=$bv['name'];
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
                        if(($task_month>0 && $now_residenter_task[0]['status']==3) || $now_month==$task_month){
                            continue;
                        }
                        $remind_content = "请用企业微信添加餐厅人员的微信，添加后请上传截图";
                        $add_data['remind_content'] = $remind_content;
                        $m_crmtask_record->add($add_data);
                        break;
                    case 11:
                        if(($task_month>0 && $now_residenter_task[0]['status']==3) || $now_month==$task_month){
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
        }
        $now_time = date('Y-m-d H:i:s');
        echo "releasetask end:$now_time \r\n";
    }

    public function finishtype1(){
        $now_time = date('Y-m-d H:i:s');
        echo "finishtype1 start:$now_time \r\n";
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
        $now_time = date('Y-m-d H:i:s');
        echo "finishtype1 end:$now_time \r\n";
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
                foreach ($cache_data['goods_list'] as $cv){
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
                $remind_content = "店内售卖了{$res_sale[0]['num']}瓶酒水，产生{$res_sale[0]['money']}元欠款，请及时处理";
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
                $remind_content = "店内有{$res_sale[0]['money']}元欠款已超期，请及时催收";
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

            $task_finish_day = $v['task_finish_day'];
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

            $task_finish_day = $v['task_finish_day'];
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

            $res_box = $m_box->getBoxByCondition('box.mac,box.name',array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0));
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
                            $boxs[]=$bv['name'];
                        }
                    }else{
                        $boxs[]=$bv['name'];
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

    public function finishtype10(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(10);
        foreach ($res_task_record as $v){
            if($v['status']==1){
                $is_finish = 0;
                $this->handle_task_record($is_finish,$v,$m_crmtask_record);
            }
        }
    }

    public function finishtype11(){
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $res_task_record = $m_crmtask_record->getHandleTasks(11);
        foreach ($res_task_record as $v){
            if($v['status']==1){
                $is_finish = 0;
                $this->handle_task_record($is_finish,$v,$m_crmtask_record);
            }
        }
    }

    public function uptaskoffstate(){
        $now_time = date('Y-m-d H:i:s');
        echo "uptaskoffstate start:$now_time \r\n";
        $m_sysuser = new \Admin\Model\UserModel();
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $start_time = date('Y-m-01 00:00:00');
        $end_time = date('Y-m-31 23:00:00');
        $where = array('add_time'=>array(array('egt',$start_time),array('elt',$end_time), 'and'),'off_state'=>1);
        $field = 'hotel_id,residenter_id,GROUP_CONCAT(id) as all_ids';
        $res_record = $m_crmtask_record->getAllData($field,$where,'','hotel_id');
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $residenter_id = $v['residenter_id'];
            $res_ext = $m_hotel_ext->getOneData('residenter_id', array('hotel_id'=>$hotel_id));
            if($res_ext['residenter_id']!=$residenter_id){
                $m_crmtask_record->updateData(array('id'=>array('in',$v['all_ids'])),array('off_state'=>2));
                echo "hotel_id:$hotel_id {$res_ext['residenter_id']}!={$residenter_id} \r\n";
            }
            $res_sysuser = $m_sysuser->getUserInfo($residenter_id);
            if($res_sysuser['status']==2){
                $m_crmtask_record->updateData(array('id'=>array('in',$v['all_ids'])),array('off_state'=>2));
                echo "hotel_id:$hotel_id {$residenter_id} not in company\r\n";
            }
        }

        $now_time = date('Y-m-d H:i:s');
        echo "uptaskoffstate end:$now_time \r\n";
    }

    public function triggertask(){
        $now_time = date('Y-m-d H:i:s');
        echo "triggertask start:$now_time \r\n";
        $m_crmtask_record = new \Admin\Model\Crm\TaskRecordModel();
        $where = array('a.status'=>0,'a.off_state'=>1,'a.is_trigger'=>0,'task.status'=>1);
        $fileds = 'a.id,a.task_id,a.hotel_id,a.residenter_id,a.status,a.form_type,a.handle_status,a.audit_handle_status,
        a.is_trigger,a.integral_task_id,a.reset_time,a.add_time,task.notify_day,task.notify_handle_day';
        $res_task = $m_crmtask_record->getTaskRecords($fileds,$where,'a.id asc');
        foreach ($res_task as $v){
            $notify_day_time = $v['notify_day']*86400;
            $diff_notify_time = time()-strtotime($v['add_time']);
            if($diff_notify_time>=$notify_day_time){
                $updata = array('trigger_time'=>date('Y-m-d H:i:s'),'is_trigger'=>1,'update_time'=>date('Y-m-d H:i:s'));
                $m_crmtask_record->updateData(array('id'=>$v['id']),$updata);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "triggertask end:$now_time \r\n";
    }



    private function handle_task_record($is_finish,$info,$m_crmtask_record){
        if($is_finish){
            $updata = array('finish_time'=>date('Y-m-d H:i:s'),'status'=>3,'form_type'=>2,'update_time'=>date('Y-m-d H:i:s'));
            $m_crmtask_record->updateData(array('id'=>$info['id']),$updata);
        }else{
            if($info['is_trigger']==0){
                $notify_day_time = $info['notify_day']*86400;
                $diff_notify_time = time()-strtotime($info['add_time']);
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
            $diff_notify_time = time()-strtotime($reset_time);
            if($diff_notify_time>=$notify_handle_day_time){
                $updata = array('status'=>0,'handle_status'=>0,'handle_time'=>'0000-00-00 00:00:00','reset_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'));
                $m_crmtask_record->updateData(array('id'=>$info['id']),$updata);
            }
        }
    }

}
