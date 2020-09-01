<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class StaticHotelassessModel extends BaseModel{
	protected $tableName='smallapp_static_hotelassess';

    public function getData($fields="*",$where,$groupby='',$order='level asc'){
        $data = $this->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->select();
        return $data;
    }

    public function getCustomeList($fields="*",$where,$groupby='',$order='level asc',$countfields='',$start=0,$size=5){
        $list = $this->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->field($countfields)
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

	public function handle_hotelassess(){
        $config = array(
            'A'=>array('fault_rate'=>0.2,'zxrate'=>0.7,'fjrate'=>0.15,'fjsalerate'=>0.8),
            'B'=>array('fault_rate'=>0.3,'zxrate'=>0.6,'fjrate'=>0.08,'fjsalerate'=>0.6),
            'C'=>array('fault_rate'=>0.4,'zxrate'=>0.5,'fjrate'=>0.03,'fjsalerate'=>0.5),
        );

        $redis = new \Common\Lib\SavorRedis();
        $redis->select(1);
        $key = 'smallapp:hotelassess';
        $res_hotels = $redis->get($key);
        if(empty($res_hotels)){
            return true;
        }
        $res_hotels = json_decode($res_hotels,true);
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));
        $all_dates = $m_statistics->getDates($start,$end);
        $m_box = new \Admin\Model\BoxModel();
        $m_statichoteldata = new \Admin\Model\Smallapp\StaticHoteldataModel();
        $m_forscreen = new \Admin\Model\SmallappForscreenRecordModel();
        foreach ($all_dates as $v){
            $time_date = strtotime($v);
            $static_date = date('Ymd',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);

            foreach ($res_hotels as $hv){
                $hotel_id = $hv['hotel_id'];
                $box_fields = 'count(box.id) as num';
                $box_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition($box_fields,$box_where,'');
                $box_num = $res_box[0]['num'];
                $data = $hv;
                $data['date'] = $static_date;
                $data['box_num'] = $box_num;
                $lost_boxnum = 0;
                $fault_rate = 0;
                $sql_lost = "select count(*) as num from savor_heart_log where hotel_id={$hotel_id} and type=2 and TIMESTAMPDIFF(DAY,last_heart_time,now())>3 ";
                $res_lost = $m_statistics->query($sql_lost);
                if($res_lost[0]['num']){
                    $lost_boxnum = $res_lost[0]['num'];
                    $fault_rate = sprintf("%.2f",$res_lost[0]['num']/$box_num);
                }
                $data['lostbox_num'] = $lost_boxnum;
                $data['fault_rate'] = $fault_rate;
                $data['operation_assess'] = 1;
                if($fault_rate>$config[$hv['hotel_level']]['fault_rate']){
                    $data['operation_assess'] = 2;
                }
                $res_hoteldata = $m_statichoteldata->getInfo(array('date'=>$static_date,'hotel_id'=>$hotel_id));
                $data['zxrate'] = $res_hoteldata['zxrate'];
                $data['channel_assess'] = 1;
                if($data['zxrate']<$config[$hv['hotel_level']]['zxrate']){
                    $data['channel_assess'] = 2;
                }
                $data['fjrate'] = $res_hoteldata['fjrate'];
                $data['data_assess'] = 1;
                if($data['fjrate']<$config[$hv['hotel_level']]['fjrate']){
                    $data['data_assess'] = 2;
                }
                $wlnum = $res_hoteldata['wlnum'];
                $res_box = $m_forscreen->getFeastBoxByHotelId($hotel_id,$time_date,0,5);
                $sale_feast_boxnum = count($res_box);
                $fjsalerate = 0;
                if($sale_feast_boxnum){
                    $fjsalerate = sprintf("%.2f",$sale_feast_boxnum/$wlnum);
                }
                $data['fjsalerate'] = $fjsalerate;
                $data['saledata_assess'] = 1;
                if($data['fjsalerate']<$config[$hv['hotel_level']]['fjsalerate']){
                    $data['saledata_assess'] = 2;
                }
                $data['all_assess'] = 1;
                if($data['operation_assess']==2 || $data['channel_assess']==2 || $data['data_assess']==2 || $data['saledata_assess']==2){
                    $data['all_assess'] = 2;
                }
                $this->add($data);
            }
            echo "date:$static_date ok \r\n";
        }

    }

}