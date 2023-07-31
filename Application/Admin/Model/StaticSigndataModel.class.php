<?php
namespace Admin\Model;
use Common\Lib\Page;

class StaticSigndataModel extends BaseModel{
	protected $tableName='static_signdata';

	public function statSignData(){
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

            $sql_in_dates = "select stock.hotel_id,MIN(a.add_time) as hotel_in_time,ext.sale_hotel_in_time from savor_finance_stock_record as a left join 
            savor_finance_stock stock on a.stock_id=stock.id left join savor_hotel_ext as ext on stock.hotel_id=ext.hotel_id
            where a.type=4 and stock.hotel_id>0 group by stock.hotel_id";
            $in_hotel_dates = $this->query($sql_in_dates);
            $m_hotel_ext = new \Admin\Model\HotelExtModel();
            foreach ($in_hotel_dates as $v){
                if($v['sale_hotel_in_time']=='0000-00-00 00:00:00'){
                    $m_hotel_ext->updateData(array('hotel_id'=>$v['hotel_id']),array('sale_hotel_in_time'=>$v['hotel_in_time']));
                }
            }
            sleep(1);

            $m_opuser_role = new \Admin\Model\OpuserroleModel();
            $fields = 'a.manage_city,user.id as signer_id,user.remark as signer_name';
            $where = array('a.state'=>1,'user.status'=>1);
            $res_opusers = $m_opuser_role->getAllRole($fields,$where,'a.id desc');
            $day_30time = 86400*30;
            $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
            $add_data = array();
            foreach ($res_opusers as $v){
                $area_id = $v['manage_city'];
                $nfields = 'count(a.id) as num';
                $nwhere = array('a.is_salehotel'=>1,'a.signer_id'=>$v['signer_id'],'hotel.state'=>1,'hotel.flag'=>0);
                $nwhere['a.sale_start_date'] = array(array('egt',$static_date),array('elt',$static_date));
                $res_num = $m_hotel_ext->getSellwineList($nfields,$nwhere,'');
                $num = intval($res_num[0]['num']);

                $now_time = time();
                $day_30where = array('a.is_salehotel'=>1,'a.signer_id'=>$v['signer_id'],'a.is_sale30day'=>0,'hotel.state'=>1,'hotel.flag'=>0);
                $day_30where['_string'] = "$now_time-UNIX_TIMESTAMP(a.sale_hotel_in_time)>0 and $now_time-UNIX_TIMESTAMP(a.sale_hotel_in_time)<=$day_30time";
                $res_day30hotels = $m_hotel_ext->getSellwineList('a.hotel_id,a.sale_hotel_in_time,a.is_new',$day_30where,'');
                $day30_num = $new_num = $old_num = 0;
                foreach ($res_day30hotels as $dv){
                    $res_sell_num = $m_stock_record->getHotelSellwineNums($dv['sale_hotel_in_time'],$end_time,$dv['hotel_id']);
                    if(!empty($res_sell_num) && $res_sell_num[$dv['hotel_id']]>=3){
                        $m_hotel_ext->updateData(array('hotel_id'=>$dv['hotel_id']),array('is_sale30day'=>1));
                        $day30_num++;
                        if($dv['is_new']==1){
                            $new_num++;
                        }else{
                            $old_num++;
                        }
                    }
                }
                $add_data[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'signer_id'=>$v['signer_id'],'signer_name'=>$v['signer_name'],
                    'num'=>$num,'day30_num'=>$day30_num,'new_num'=>$new_num,'old_num'=>$old_num,'static_date'=>$static_date,'static_month'=>$static_month);
                echo "signer_id:{$v['signer_id']},name:{$v['signer_name']} ok \r\n";
            }
            if(!empty($add_data)){
                $this->addAll($add_data);
            }

            echo "date:$static_date ok \r\n";
        }
    }
}



