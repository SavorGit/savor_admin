<?php
namespace Admin\Model;
use Common\Lib\Page;

class StaticResidentModel extends BaseModel{
	protected $tableName='static_residentdata';

	public function statResidentData(){
        $static_date = date('Y-m-d',strtotime('-1 day'));
        $static_month = date('Ym',strtotime($static_date));

        $start_time = "$static_date 00:00:00";
        $end_time = "$static_date 23:59:59";
	    $all_citys = array('1'=>'北京','9'=>'上海','236'=>'广州','246'=>'深圳','248'=>'佛山');

        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.manage_city,user.id as residenter_id,user.remark as residenter_name';
        $where = array('a.state'=>1,'user.status'=>1);
        $res_opusers = $m_opuser_role->getAllRole($fields,$where,'a.id desc');
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_sale_payment = new \Admin\Model\FinanceSalePaymentModel();
        $m_hoteldata_history = new \Admin\Model\HotelDataHistoryModel();
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $add_data = array();
        foreach ($res_opusers as $v){
            $area_id = $v['manage_city'];

            $salewhere = array('a.wo_reason_type'=>1,'a.wo_status'=>2,'ext.residenter_id'=>$v['residenter_id']);
            $salewhere['a.update_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_stock_record = $m_stock_record->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel_ext ext on stock.hotel_id=ext.hotel_id','left')
                ->where($salewhere)
                ->select();
            $num = intval($res_stock_record[0]['num']);

            $paywhere = array('ext.residenter_id'=>$v['residenter_id']);
            $paywhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $pfields = 'sum(a.pay_money) as pay_money,a.hotel_id,a.pay_time';
            $res_paymoney = $m_sale_payment->getSalePaymentList($pfields,$paywhere,'a.hotel_id,a.pay_time');
            $sale_money = 0;
            foreach ($res_paymoney as $pv){
                if($pv['pay_time']==$static_date){
                    $sale_money+=$pv['pay_money'];
                }else{
                    $res_hotel_history = $m_hoteldata_history->getInfo(array('static_date'=>$pv['pay_time'],'hotel_id'=>$pv['hotel_id']));
                    if(!empty($res_hotel_history['residenter_id'])){
                        $upwhere = array('static_date'=>$pv['pay_time'],'residenter_id'=>$res_hotel_history['residenter_id']);
                        $updata = array('sale_money'=>"sale_money+{$pv['pay_money']}");
                        $this->updateData($upwhere,$updata);
                    }
                }
            }

            $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
            $staff_where = array('ext.residenter_id'=>$v['residenter_id'],'a.static_date'=>$static_date);
            $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
            $task_demand_finish_rate = sprintf("%.2f",$res_task_data[0]['task_demand_finish_num']/$res_task_data[0]['task_demand_operate_num']);
            $task_invitation_finish_rate = sprintf("%.2f",$res_task_data[0]['task_invitation_finish_num']/$res_task_data[0]['task_invitation_operate_num']);

            $add_data[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'residenter_id'=>$v['residenter_id'],'residenter_name'=>$v['residenter_name'],
                'num'=>$num,'task_demand_finish_rate'=>$task_demand_finish_rate,'task_invitation_finish_rate'=>$task_invitation_finish_rate,'sale_money'=>$sale_money,
                'static_date'=>$static_date,'static_month'=>$static_month);
            echo "residenter_id:{$v['residenter_id']},name:{$v['residenter_name']} ok \r\n";
        }
        if(!empty($add_data)){
            $this->addAll($add_data);
        }
    }
}



