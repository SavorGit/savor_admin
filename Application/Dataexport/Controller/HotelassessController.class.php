<?php
namespace Dataexport\Controller;

class HotelassessController extends BaseController{

    public function assess(){
        $s_date = I('s_date');
        $e_date = I('e_date');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($start_time,$end_time,2);
        $where = array('date'=>array('in',$all_dates));
        if($hotel_level){
            $where['hotel_level'] = $hotel_level;
        }
        if($all_assess)         $where['all_assess'] = $all_assess;
        if($operation_assess)   $where['operation_assess'] = $operation_assess;
        if($channel_assess)     $where['channel_assess'] = $channel_assess;
        if($data_assess)        $where['data_assess'] = $data_assess;
        if($saledata_assess)    $where['saledata_assess'] = $saledata_assess;
        $teams = array(
            'Oiyoboy'=>array('吴琳','朱宇杰','欧懿'),
            '勇者队'=>array('曾峰','陈远程','甘顺山'),
            '超凡队'=>array('王习宗','熊静怡','何永锐'),
        );
        $team = $tmember = array();
        foreach ($teams as $k=>$v){
            $is_select = 0;
            if($k==$hotel_team){
                $is_select = 1;
            }
            $team[] = array('name'=>$k,'is_select'=>$is_select);
            foreach ($v as $uv){
                $tmember[$uv]=$k;
            }
        }
        if($hotel_team){
            $where['team_name'] = array('in',$teams[$hotel_team]);
        }
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'date,hotel_level,team_name,avg(all_assess) as all_assess,hotel_name,avg(box_num) as box_num,avg(lostbox_num) as lostbox_num,
        avg(fault_rate) as fault_rate,avg(operation_assess) as operation_assess,avg(zxrate) as zxrate,avg(channel_assess) as channel_assess,
        avg(fjrate) as fjrate,avg(data_assess) as data_assess';
        $groupby = 'hotel_id';
        $order = 'hotel_level asc';
        $result = $m_staticassess->getAll($fields,$where,0,1000,$order,$groupby);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $datalist[$k]['team'] = $tmember[$v['team_name']];
        }
        $all_data = array();
        $cell = array(
            array('date','日期'),
            array('region_name','城市'),
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('all_count','互动总数'),
            array('count0','图片投屏'),
            array('slide_count','滑动'),
            array('video_count','视频投屏'),
            array('count4','多图投屏'),
            array('count5','视频点播'),
            array('count6','广告跳转'),
            array('count7','点击互动游戏'),
            array('count8','重投'),
            array('count9','手机呼大码'),
            array('count11','发现点播图片'),
            array('count12','发现点播视频'),
            array('count21','查看点播视频'),
            array('count22','查看发现视频'),
            array('count30','投屏文件'),
            array('count31','投屏文件图片'),
            array('count101','h5互动游戏'),
            array('count120','发红包'),
            array('count121','扫码抢红包'),
            array('boxnum','版位数量'),
            array('onlinescreen','在线屏数'),
            array('hdnum','投屏互动版位'),
            array('maintainer_name','维护人'),
            array('box_type','设备类型'),
        );
        $filename = '酒楼考核数据';
        $path = $this->exportToExcel($cell,$all_data,$filename);
    }
}