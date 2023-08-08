<?php
namespace Admin\Model;
use Common\Lib\Page;

class StaticResidentModel extends BaseModel{
	protected $tableName='static_residentdata';

	public function statResidentData(){
        $static_date = date('Y-m-d',strtotime('-1 day'));
        $static_month = date('Ym',strtotime($static_date));

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = $static_date;
        $end = $static_date;
        $all_dates = $m_statistics->getDates($start,$end);
        foreach ($all_dates as $adv){
            $static_date = $adv;
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
            $m_sale_paymentrecord = new \Admin\Model\FinanceSalePaymentRecordModel();

            $m_sale = new \Admin\Model\FinanceSaleModel();
            $m_hoteldata_history = new \Admin\Model\HotelDataHistoryModel();
            $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
            $add_data = array();
            foreach ($res_opusers as $v){
                $area_id = $v['manage_city'];

                $salewhere = array('a.residenter_id'=>$v['residenter_id'],'record.wo_reason_type'=>1,'record.wo_status'=>2);
                $salewhere['record.update_time'] = array(array('egt',$start_time),array('elt',$end_time));
                $res_stock_record = $m_sale->alias('a')
                    ->field('count(a.id) as num')
                    ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                    ->where($salewhere)
                    ->select();
                $num = intval($res_stock_record[0]['num']);

                $paywhere = array('sale.residenter_id'=>$v['residenter_id'],'record.wo_reason_type'=>1,'record.wo_status'=>2);
                $paywhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                $pfields = 'sum(a.pay_money) as pay_money';
                $res_paymoney = $m_sale_paymentrecord->getSalePaymentRecordList($pfields,$paywhere);
                $sale_money = intval($res_paymoney[0]['pay_money']);

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

            echo "date:$static_date ok \r\n";
        }
    }
}



