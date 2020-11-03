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

    public function getHotelassess($fields="*",$where,$groupby='',$order='',$start=0,$size=5){
        $data = $this->alias('a')
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        return $data;
    }

    public function getCustomeList($fields="*",$where,$groupby='',$order='level asc',$countfields='',$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->alias('a')->field($countfields)
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

    public function assessConfig(){
        $config = array(
            'A'=>array('fault_rate'=>0.05,'zxrate'=>0.45,'fjrate'=>0.06,'fjsalerate'=>0.30),
            'B'=>array('fault_rate'=>0.10,'zxrate'=>0.35,'fjrate'=>0.03,'fjsalerate'=>0.20),
            'C'=>array('fault_rate'=>0.15,'zxrate'=>0.30,'fjrate'=>0.02,'fjsalerate'=>0.15),
        );
        return $config;
    }

    public function getHotelassessResult($data){
        $assess_config = $this->assessConfig();
        $config_hotel = $assess_config[$data['hotel_level']];
        $data['operation_assess'] = 1;
        if($data['fault_rate']>$config_hotel['fault_rate']){
            $data['operation_assess'] = 2;
        }
        $data['channel_assess'] = 1;
        if($data['zxrate']<$config_hotel['zxrate']){
            $data['channel_assess'] = 2;
        }
        $data['data_assess'] = 1;
        if($data['fjrate']<$config_hotel['fjrate']){
            $data['data_assess'] = 2;
        }
        $data['saledata_assess'] = 1;
        if($data['fjsalerate']<$config_hotel['fjsalerate']){
            $data['saledata_assess'] = 2;
        }
        $data['all_assess'] = 1;
        if($data['is_train']==0){
            if($data['operation_assess']==2 || $data['channel_assess']==2){
                $data['all_assess'] = 2;
            }
        }else{
            if($data['operation_assess']==2 || $data['channel_assess']==2 || $data['data_assess']==2 || $data['saledata_assess']==2){
                $data['all_assess'] = 2;
            }
        }

        return $data;
    }


	public function handle_hotelassess(){
        $config = $this->assessConfig();

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

//        $start = '2020-08-24';
//        $end = '2020-09-16';

        $all_dates = $m_statistics->getDates($start,$end);
        $m_box = new \Admin\Model\BoxModel();
        $m_statichoteldata = new \Admin\Model\Smallapp\StaticHoteldataModel();
        $m_statichotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
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
                $res_hoteldata = $m_statichotelbasicdata->getInfo(array('static_date'=>date('Y-m-d',$time_date),'hotel_id'=>$hotel_id));
                $zxnum = $wlnum = $user_zxhdnum = $sale_zxhdnum = $zxhdnum = 0;
                if(!empty($res_hoteldata)){
                    $wlnum = $res_hoteldata['wlnum'];
                    $zxnum = $res_hoteldata['lunch_zxnum'] + $res_hoteldata['dinner_zxnum'];
                    $user_zxhdnum = $res_hoteldata['user_lunch_zxhdnum'] + $res_hoteldata['user_dinner_zxhdnum'];
                    $sale_zxhdnum = $res_hoteldata['sale_lunch_zxhdnum'] + $res_hoteldata['sale_dinner_zxhdnum'];
                    $zxhdnum = $res_hoteldata['lunch_zxhdnum'] + $res_hoteldata['dinner_zxhdnum'];
                }
                $data['zxnum'] = $zxnum;
                $data['wlnum'] = $wlnum;
                $data['user_zxhdnum'] = $user_zxhdnum;
                $data['sale_zxhdnum'] = $sale_zxhdnum;
                $data['zxhdnum'] = $zxhdnum;

                $zxrate = 0;
                if($zxnum && $wlnum){
                    $total_wlnum = $wlnum * 2;
                    $zxrate = sprintf("%.2f",$zxnum/$total_wlnum);
                }
                $data['zxrate'] = $zxrate;
                $data['channel_assess'] = 1;
                if($data['zxrate']<$config[$hv['hotel_level']]['zxrate']){
                    $data['channel_assess'] = 2;
                }
                $fjrate = 0;
                if($user_zxhdnum && $zxhdnum){
                    $fjrate = sprintf("%.2f",$user_zxhdnum/$zxhdnum);
                }
                $data['fjrate'] = $fjrate;
                $data['data_assess'] = 1;
                if($data['fjrate']<$config[$hv['hotel_level']]['fjrate']){
                    $data['data_assess'] = 2;
                }
                $fjsalerate = 0;
                if($sale_zxhdnum && $zxhdnum){
                    $fjsalerate = sprintf("%.2f",$sale_zxhdnum/$zxhdnum);
                }
                $data['fjsalerate'] = $fjsalerate;

                /*
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
                */

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