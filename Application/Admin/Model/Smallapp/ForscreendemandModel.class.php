<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreendemandModel extends BaseModel{
    protected $tableName='smallapp_forscreen_demand';
    public function getList($field,$where,$group, $order='id desc', $start=0,$size=5){
       
        $list = $this->field($field)
        ->where($where)
        ->order($order)
        ->limit($start,$size)
        ->group($group)
        ->select();
        $rt = $this->where($where)
                      ->group($group)
                      ->select();
        $count = count($rt);
        $objPage = new Page($count,$size);
        
        $show = $objPage->admin_page();
        
        
        $data = array('list'=>$list,'page'=>$show);
        
        return $data;
        
    }

    public function statDemandData(){
        set_time_limit(0);
        ini_set("memory_limit", "256M");
        $yesterday = date('Y-m-d',strtotime('-1 day'));
        $start_date = $yesterday.' 00:00:00';
        $end_date   = $yesterday.' 23:59:59';

        $sql = "select r.id as forscreen_record_id,r.serial_number,r.forscreen_id,r.resource_id,
                r.resource_size,r.openid,r.area_id,r.area_name,r.hotel_id,r.hotel_name,r.room_id,r.room_name,
                r.box_id,r.box_name,r.box_mac,r.is_4g,r.box_type,r.hotel_box_type,r.hotel_is_4g,r.action,
                r.resource_type,r.imgs,r.forscreen_char,r.duration,r.mobile_brand,r.mobile_model,
                r.create_time, a.name resource_name,a.media_id
                from savor_smallapp_forscreen_record r
                left join savor_ads a on r.resource_id = a.id
                where r.action in(17) and small_app_id =1 and r.create_time>='".$start_date."' and r.create_time<='".$end_date."' and a.id>0 and r.mobile_brand !='devtools'";

        $data_one = M()->query($sql);


        $sql = "select r.id as forscreen_record_id,r.serial_number,r.forscreen_id,r.resource_id,
                r.resource_size,r.openid,r.area_id,r.area_name,r.hotel_id,r.hotel_name,r.room_id,r.room_name,
                r.box_id,r.box_name,r.box_mac,r.is_4g,r.box_type,r.hotel_box_type,r.hotel_is_4g,r.action,
                r.resource_type,r.imgs,r.forscreen_char,r.duration,r.mobile_brand,r.mobile_model,
                r.create_time from savor_smallapp_forscreen_record r
                where r.action=5 and small_app_id =1 and r.create_time>='".$start_date."'and r.create_time<='".$end_date."' and r.mobile_brand !='devtools'";

        $data_two = M()->query($sql);
        foreach($data_two as $key=>$v){
            $img_arr  = json_decode($v['imgs'],true);
            $oss_path = $img_arr[0];
            $sql ="select id as media_id,name,oss_filesize,duration from savor_media where oss_addr='".$oss_path."'";

            $rt = M()->query($sql);
            if(!empty($rt)){
                $data_two[$key]['resource_name'] = $rt[0]['name'];
                $data_two[$key]['resource_size'] = $rt[0]['oss_filesize'];
                $data_two[$key]['duration']      = $rt[0]['duration'];
                $data_two[$key]['media_id']      = $rt[0]['media_id'];
            }else {
                unset($data_two[$key]);
            }


        }
        $sql = "select r.id as forscreen_record_id,r.serial_number,r.forscreen_id,r.resource_id, r.resource_size,
                r.openid,r.area_id,r.area_name,r.hotel_id,r.hotel_name,r.room_id,r.room_name, r.box_id,
                r.box_name,r.box_mac,r.is_4g,r.box_type,r.hotel_box_type,r.hotel_is_4g,r.action, 
                r.resource_type,r.imgs,r.forscreen_char,r.duration,r.mobile_brand,r.mobile_model, 
                r.create_time,dg.parent_id,dg.tv_media_id,m.name resource_name ,pdg.tv_media_id p_tv_media_id,
                pm.name p_resource_name
                from savor_smallapp_forscreen_record r 
                left join savor_smallapp_dishgoods dg on r.resource_id = dg.id 
                left join savor_smallapp_dishgoods pdg on dg.parent_id = pdg.id
                left join savor_media m on dg.tv_media_id= m.id 
                left join savor_media pm on pdg.tv_media_id = pm.id
                where r.action in(13,14) and small_app_id =1 and r.create_time>='".$start_date.
            "' and r.create_time<='".$end_date."' and r.mobile_brand !='devtools'";

        $data_three = M()->query($sql);
        foreach($data_three as $key=>$v){
            if(!empty($v['tv_media_id'])){
                $data_three[$key]['media_id'] = $v['tv_media_id'];
                unset($data_three[$key]['tv_media_id']);
                unset($data_three[$key]['parent_id']);
                unset($data_three[$key]['p_tv_media_id']);
                unset($data_three[$key]['p_resource_name']);
            }else{
                $data_three[$key]['media_id'] = $v['p_tv_media_id'];
                $data_three[$key]['resource_name'] = $v['p_resource_name'];
                unset($data_three[$key]['tv_media_id']);
                unset($data_three[$key]['parent_id']);
                unset($data_three[$key]['p_tv_media_id']);
                unset($data_three[$key]['p_resource_name']);
            }

        }

        //print_r($data_three);exit;

        $data = [];
        /* if(!empty($data_one)){
            $data = array_merge($data_one,$data_two);
        }else {
            $data = array_merge($data_two,$data_one);
        } */
        $data = array_merge($data_one,$data_two,$data_three);
        $meal_time = C('MEAL_TIME');
        $l_s_time = $meal_time['lunch'][0];
        $l_e_time = $meal_time['lunch'][1];
        $d_s_time = $meal_time['dinner'][0];
        $d_e_time = $meal_time['dinner'][1];
        foreach($data as $key=>$v){
            if($data[$key]['action']==13 ){//13点播商品视频
                $data[$key]['resource_cate'] = 3;
            }else if($data[$key]['action']==14){// 14点播banner商品视频
                $data[$key]['resource_cate'] = 4;
            }else if($data[$key]['action']==17){//点播热播节目视频
                $data[$key]['resource_cate'] = 2;
            }else if($data[$key]['action']==5){
                if($v['forscreen_char']==''){
                    $data[$key]['resource_cate'] = 1;
                }else {
                    $data[$key]['resource_cate'] = 5;
                }
            }
            $f_time = substr($v['create_time'], 11,5);
            if($f_time>=$l_s_time && $f_time <=$l_e_time){
                $data[$key]['static_fj'] = 1;
            }else if($f_time>=$d_s_time && $f_time<=$d_e_time){
                $data[$key]['static_fj'] = 2;
            }else {
                $data[$key]['static_fj'] = 0;
            }
            if(empty($v['resource_size'])){
                $data[$key]['resource_size'] = 0;
            }
            if(empty($v['serial_number'])){
                $data[$key]['serial_number'] = $v['openid'];
            }
            if(empty($v['duration'])){
                $data[$key]['duration'] = 0;
            }else {
                $data[$key]['duration'] = intval($v['duration']);
            }
            $imgs = json_decode($v['imgs'],true);
            $data[$key]['oss_addr'] = $imgs[0];
            $data[$key]['create_date'] = date('Ymd',strtotime($v['create_time']));

        }
        $ret = $this->addAll($data);
        echo $start_date."\n";
    }

}