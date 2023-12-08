<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class MessageModel extends BaseModel{
	protected $tableName='smallapp_message';

    /*
     * $type 类型1赞(喜欢内容),2内容审核,3优质内容,4领取红包,5购买订单,6发货订单
     */
    public function recordMessage($openid,$content_id,$type,$status=0,$hotel_nums=0){
        switch ($type){
            case 1:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    if($status==0){
                        $this->delData(array('id'=>$res_data[0]['id']));
                    }
                }else{
                    if($status){
                        $data = $where;
                        $data['read_status'] = 1;
                        $this->add($data);
                    }
                }
                break;
            case 2:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    $data = $where;
                    $data['audit_status'] = $status;
                    $data['read_status'] = 1;
                    $this->updateData(array('id'=>$res_data[0]['id']),$data);
                }else{
                    $data = $where;
                    $data['audit_status'] = $status;
                    $data['read_status'] = 1;
                    $this->add($data);
                }
                break;
            case 3:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    $data = $where;
                    $data['good_status'] = $status;
                    $data['hotel_num'] = $hotel_nums;
                    $data['read_status'] = 1;
                    $this->updateData(array('id'=>$res_data[0]['id']),$data);
                }else{
                    $data = $where;
                    $data['good_status'] = $status;
                    $data['hotel_num'] = $hotel_nums;
                    $data['read_status'] = 1;
                    $this->add($data);
                }
                break;
            case 4:
            case 5:
            case 6:
                $data = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type,'read_status'=>1);
                $this->add($data);
                break;
        }

        return true;
    }

    public function stockCheckErrorStat(){
        $sql = "select a.ops_staff_id,GROUP_CONCAT( DISTINCT sc.hotel_id) as hotel_ids from savor_smallapp_message as a left join savor_smallapp_stockcheck as sc on a.content_id=sc.id  where a.type=13
            and sc.is_handle_stock_check=0 and sc.stock_check_success_status in (22,23,24) group by a.ops_staff_id";
        $res_data = $this->query($sql);
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(22);
        $cache_key = C('SAPP_OPS').'msgschotels';
        foreach ($res_data as $v){
            if(!empty($v['hotel_ids'])){
                $redis->set($cache_key.":{$v['ops_staff_id']}",$v['hotel_ids'],86000);
                echo "ops_staff_id:{$v['ops_staff_id']} ok \r\n";
            }
        }
    }

    public function opsSaleNotify(){
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_payrecord = new \Admin\Model\FinanceSalePaymentRecordModel();
        $field = 'hotel_id,GROUP_CONCAT(id) as sale_ids,sum(settlement_price) as total_money,max(add_time) as new_time';
        $where = array('ptype'=>array('in','0,2'),'is_expire'=>0,'is_notifymsg_sk'=>0,'settlement_price'=>array('gt',0));
        $res_data = $m_sale->getAllData($field,$where,'','hotel_id');
        $config_qk_day = 3;
        foreach ($res_data as $v){
            $hotel_id = $v['hotel_id'];
            $diff_day = round((time() - strtotime($v['new_time']))/86400);
            if($diff_day>=$config_qk_day){
                $money = $v['total_money'];
                $res_has_pay = $m_payrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>array('in',$v['sale_ids'])));
                if($res_has_pay[0]['all_pay_money']>0){
                    $money = $money-$res_has_pay[0]['all_pay_money'];
                }
                $qk_day = 7-$diff_day;
                $message_data = array('money'=>$money,'sale_ids'=>$v['sale_ids'],'qk_day'=>$qk_day);
                $this->addNotifyMessage($hotel_id,$message_data,14);//14收款(运维端),15超期欠款(运维端)
                $m_sale->updateData(array('id'=>array('in',$v['sale_ids'])),array('is_notifymsg_sk'=>1));
            }
        }
        echo "message_type:14 ok \r\n";

        $field = 'hotel_id,GROUP_CONCAT(id) as sale_ids,sum(settlement_price) as total_money';
        $where = array('ptype'=>array('in','0,2'),'is_expire'=>1,'is_notifymsg_qk'=>0,'settlement_price'=>array('gt',0));
        $res_data = $m_sale->getAllData($field,$where,'','hotel_id');
        foreach ($res_data as $v){
            $hotel_id = $v['hotel_id'];
            $money = $v['total_money'];
            $res_has_pay = $m_payrecord->getAllData('sum(pay_money) as all_pay_money',array('sale_id'=>array('in',$v['sale_ids'])));
            if($res_has_pay[0]['all_pay_money']>0){
                $money = $money-$res_has_pay[0]['all_pay_money'];
            }
            $message_data = array('money'=>$money,'sale_ids'=>$v['sale_ids']);
            $this->addNotifyMessage($hotel_id,$message_data,15);//14收款(运维端),15超期欠款(运维端)
            $m_sale->updateData(array('id'=>array('in',$v['sale_ids'])),array('is_notifymsg_qk'=>1));
        }
        echo "message_type:15 ok \r\n";
    }

    public function opsOhtersNotify(){
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $where = array('a.type'=>7,'a.wo_reason_type'=>1,'a.wo_status'=>array('in','1,2,4'),'a.is_notifymsg'=>0);
        $fileds = 'a.id,a.op_openid,stock.hotel_id,sale.settlement_price as money,sale.residenter_id';
        $res_stock_record = $m_stock_record->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_sale sale on a.id=sale.stock_record_id','left')
            ->where($where)
            ->select();
        foreach ($res_stock_record as $v){
            $message_data = array('staff_openid'=>$v['op_openid'],'content_id'=>$v['id']);
            $row_id = $this->addNotifyMessage($v['hotel_id'],$message_data,16);//16酒水售卖(运维端),17积分到账(运维端),18积分提现(运维端)
            $m_stock_record->updateData(array('id'=>$v['id']),array('is_notifymsg'=>1));
            echo "message_type:16,stock_record_id:{$v['id']},message_id:$row_id ok \r\n";

            $message_data = array('content_id'=>$v['id'],'money'=>$v['money'],'residenter_id'=>$v['residenter_id']);
            $row_id = $this->addNotifyMessage($v['hotel_id'],$message_data,19);//19酒水回款(运维端)
            echo "message_type:19,stock_record_id:{$v['id']},message_id:$row_id ok \r\n";

        }
        echo "message_type:16 ok \r\n";
        $m_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $field = 'id,openid,hotel_id,integral,money,goods_id,type';
        $res_integreal_record = $m_integralrecord->getAllData($field,array('is_notifymsg'=>0));
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        foreach ($res_integreal_record as $v){
            $message_data = array('staff_openid'=>$v['openid'],'content_id'=>$v['id']);
            if($v['type']==4){
                $type=18;
                $res_goods = $m_goods->getInfo(array('id'=>$v['goods_id']));
                $message_data['money'] = intval($res_goods['price']);
            }else{
                $type=17;
                $message_data['integral'] = $v['integral'];
            }
            $this->addNotifyMessage($v['hotel_id'],$message_data,$type);//16酒水售卖(运维端),17积分到账(运维端),18积分提现(运维端)
            $m_integralrecord->updateData(array('id'=>$v['id']),array('is_notifymsg'=>1));
        }
        echo "message_type:17-18 ok \r\n";
    }

    public function addNotifyMessage($hotel_id,$message_data,$type){
        $all_message_data = array();
        $m_hotel = new \Admin\Model\HotelModel();
        $no_ids = array('7');
        $m_opstaff = new \Admin\Model\OpsstaffModel();
        if($type==19){
            if($message_data['residenter_id']>0){
                $res_ops_staf = $m_opstaff->getInfo(array('sysuser_id'=>$message_data['residenter_id'],'status'=>1));
                if(!empty($res_ops_staf)){
                    $minfo = array('ops_staff_id'=>$res_ops_staf['id'],'content_id'=>$message_data['content_id'],
                        'money'=>$message_data['money'],'hotel_id'=>$hotel_id,'type'=>$type,'read_status'=>1);
                    $all_message_data[]=$minfo;
                }else{
                    echo "message_type:19,residenter_id:{$message_data['residenter_id']} status error \r\n";
                }
            }else{
                echo "message_type:19,residenter_id:{$message_data['residenter_id']} error \r\n";
            }
        }else{
            $res_hotel = $m_hotel->getHotelById('hotel.area_id,ext.maintainer_id',array('hotel.id'=>$hotel_id));
            $area_id = $res_hotel['area_id'];
            $owhere = array('area_id'=>$area_id,'hotel_role_type'=>array('in','2,4'),'is_operrator'=>0,'status'=>1);
            if($res_hotel['maintainer_id']>0){
                $res_ops_staf = $m_opstaff->getInfo(array('sysuser_id'=>$res_hotel['maintainer_id'],'status'=>1));
                if(!empty($res_ops_staf)){
                    $no_ids[]=$res_ops_staf['id'];
                    $minfo = array('ops_staff_id'=>$res_ops_staf['id'],'hotel_id'=>$hotel_id,'type'=>$type,'read_status'=>1);
                    if(!empty($message_data['content_id']))     $minfo['content_id'] = $message_data['content_id'];
                    if(!empty($message_data['staff_openid']))   $minfo['staff_openid'] = $message_data['staff_openid'];
                    if(!empty($message_data['money']))          $minfo['money'] = $message_data['money'];
                    if(!empty($message_data['integral']))       $minfo['integral'] = $message_data['integral'];
                    if(!empty($message_data['sale_ids']))       $minfo['sale_ids'] = $message_data['sale_ids'];
                    if(!empty($message_data['qk_day']))         $minfo['qk_day'] = $message_data['qk_day'];
                    $all_message_data[] = $minfo;
                }
            }
            $owhere['id'] = array('not in',$no_ids);
            $res_mdata = $m_opstaff->getDataList('id,openid',$owhere,'id desc');
            foreach ($res_mdata as $v){
                $minfo = array('ops_staff_id'=>$v['id'],'hotel_id'=>$hotel_id,'type'=>$type,'read_status'=>1);
                if(!empty($message_data['content_id']))     $minfo['content_id'] = $message_data['content_id'];
                if(!empty($message_data['staff_openid']))   $minfo['staff_openid'] = $message_data['staff_openid'];
                if(!empty($message_data['money']))          $minfo['money'] = $message_data['money'];
                if(!empty($message_data['integral']))       $minfo['integral'] = $message_data['integral'];
                if(!empty($message_data['sale_ids']))       $minfo['sale_ids'] = $message_data['sale_ids'];
                if(!empty($message_data['qk_day']))         $minfo['qk_day'] = $message_data['qk_day'];
                $all_message_data[] = $minfo;
            }
        }

        $row_id = 0;
        if(!empty($all_message_data)){
            $row_id = $this->addAll($all_message_data);
        }
        return $row_id;
    }
}