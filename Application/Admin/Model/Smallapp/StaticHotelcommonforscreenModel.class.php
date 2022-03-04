<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class StaticHotelcommonforscreenModel extends BaseModel{
	protected $tableName='smallapp_static_hotelcommonforscreen';


	public function handle_hotelcommonforscreen(){
        $all_hotel_types = C('heart_hotel_box_type');
        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,a.hotel_box_type,a.level as hotel_level,
	    a.is_4g,a.is_5g,ext.trainer_id,ext.train_date,ext.maintainer_id,a.tech_maintainer';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $where['a.hotel_box_type'] = array('in',array_keys($all_hotel_types));
        $res_hotel = $m_hotel->getHotels($field,$where);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));
        $all_dates = $m_statistics->getDates($start,$end);
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();

        foreach ($all_dates as $v) {
            $static_date = $v;

            $time_date = strtotime($v);
            $start_time = date('Y-m-d 00:00:00', $time_date);
            $end_time = date('Y-m-d 23:59:59', $time_date);
            foreach ($res_hotel as $hv) {
                $hotel_id = $hv['hotel_id'];

                $forscreen_where = array('a.hotel_id'=>$hotel_id);
                $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $forscreen_where['a.small_app_id'] = array('in',array(1,2,11));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
                $forscreen_where['a.action'] = array('neq',2);
                $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                $fields = "count(a.id) as use_num,a.action";
                $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','a.action');
                $add_data = array();
                if(!empty($res_forscreen)){
                    foreach ($res_forscreen as $fv){
                        $add_data[]=array('hotel_id'=>$hotel_id,'action'=>$fv['action'],'use_num'=>$fv['use_num'],'static_date'=>$static_date);
                    }
                }
                $forscreen_where['a.action'] = 2;
                $fields = "count(a.id) as use_num,a.resource_type";
                $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$forscreen_where,'','a.resource_type');
                if(!empty($res_forscreen)){
                    foreach ($res_forscreen as $fv){
                        if($fv['resource_type']==1){
                            $action = 1;
                        }else{
                            $action = 2;
                        }
                        $info = array('hotel_id'=>$hotel_id,'action'=>$action,'use_num'=>$fv['use_num'],'static_date'=>$static_date);
                        $add_data[]=$info;
                    }
                }
                if(!empty($add_data)){
                    $this->addAll($add_data);
                }
            }
            echo "date:$static_date ok \r\n";
        }

    }

}