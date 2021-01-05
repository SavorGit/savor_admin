<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class StaticBoxdataModel extends BaseModel{

	protected $tableName='smallapp_static_boxdata';

    public function getCustomDataList($fields,$where,$orderby,$groupby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->field($fields)->where($where)->order($orderby)->group($groupby)->limit($start,$size)->select();
            $count = $this->where($where)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->group($groupby)->select();
        }
        return $data;
    }

	public function handle_box_data(){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));

        $all_dates = $m_statistics->getDates($start,$end);

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        foreach ($all_dates as $v){
            $time_date = strtotime($v);
            $static_date = date('Y-m-d',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);
            $fields = 'a.area_id,a.area_name,a.hotel_id,a.hotel_name,a.hotel_box_type,a.box_id,a.box_name,a.box_mac';
            $where = array('a.create_time'=>array(array('EGT',$start_time),array('ELT',$end_time)));
            $where['a.small_app_id'] = array('in',array(1,2,11));
            $where['a.is_valid'] = 1;
            $where['a.mobile_brand'] = array('neq','devtools');
            $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$where,'','a.box_mac');
            foreach ($res_forscreen as $fv){
                $add_data = array('area_id'=>$fv['area_id'],'area_name'=>$fv['area_name'],'hotel_id'=>$fv['hotel_id'],'hotel_name'=>$fv['hotel_name'],
                    'hotel_box_type'=>$fv['hotel_box_type'],'box_id'=>$fv['box_id'],'box_name'=>$fv['box_name'],'box_mac'=>$fv['box_mac']
                );
                $user_lunch_interact_num = $m_smallapp_forscreen_record->getFeastForscreenNumByBox($fv['hotel_id'],$fv['box_mac'],$time_date,1,1);
                $user_dinner_interact_num = $m_smallapp_forscreen_record->getFeastForscreenNumByBox($fv['hotel_id'],$fv['box_mac'],$time_date,2,1);
                if($user_lunch_interact_num || $user_dinner_interact_num){
                    $add_data['static_date'] = $static_date;
                    $add_data['user_lunch_interact_num'] = $user_lunch_interact_num;
                    $add_data['user_dinner_interact_num'] = $user_dinner_interact_num;
                    $this->add($add_data);
                }
            }
            echo "date:$static_date ok \r\n";

        }
    }

}