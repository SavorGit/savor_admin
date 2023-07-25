<?php
namespace Dataexport\Controller;

class SalerecordController extends BaseController{
    
    public function datalist(){
        //set_time_limit(360);
        //ini_set("memory_limit","1024M");
        
        
        $start_date = I('start_date',date('Y-m-d',strtotime('-7 days')));
        $end_date = I('end_date',date('Y-m-d',strtotime('-1 day')));
        //echo $end_date;exit;
        $init_start_date = date('Y-m-d',strtotime('-3 months'));
        if($start_date<$init_start_date){
            exit('只支持导出最近三个月数据');
        }
        $start_date .= ' 00:00:00';
        $end_date   .= ' 23:59:59';
        $m_category = new \Admin\Model\CategoryModel();
        $cate_list  = $m_category->getDataList('id,name,type',array('type'=>array('in','9,10')),'id desc');
        
        $cate_arr = [];
        foreach($cate_list as $key=>$v){
            $cate_arr[$v['id']] = $v;
        }
        
        //secsToStr
        
        $where = ' 1 =1';
        $where .=" and a.add_time>='".$start_date."' && a.add_time<='".$end_date."' and a.type=1 and a.status=2";
        
        $sql ="select a.ops_staff_id,area.region_name,user.remark,hotel.id hotel_id,hotel.name hotel_name,
               signin_time,signout_time,visit_type,visit_purpose
               from savor_crm_salerecord a
               left join savor_ops_staff  staff on a.ops_staff_id = staff.id
               left join savor_sysuser    user  on user.id = staff.sysuser_id
               left join savor_hotel      hotel on a.signin_hotel_id = hotel.id
               left join savor_area_info  area  on hotel.area_id = area.id where ".$where;
        
        $data = M()->query($sql);
        
        foreach($data as $key=>$v){
            $visit_purpose_str = trim($v['visit_purpose'],',');
            $tmp  = explode(',',$visit_purpose_str);
            $visit_p_s = '';
            $flag =  '';
            foreach($tmp as $vv){
                $visit_p_s .= $flag. $cate_arr[$vv]['name'];
                $flag = ',';
            }
            $data[$key]['visit_purpose_str'] = $visit_p_s;
            $data[$key]['visite_type_str']   = $cate_arr[$v['visit_type']]['name'];
            $data[$key]['visit_duration']    = secsToStr(strtotime($v['signout_time']) - strtotime($v['signin_time']));
        }
        
        $cell = array(
            array('region_name','城市'),
            array('remark','姓名'),
            
            array('hotel_id','餐厅ID'),
            array('hotel_name','餐厅名称'),
            
            
            array('signin_time','签到时间'),
            array('signout_time','签退时间'),
            array('visit_duration','拜访时长'),
            
            array('visite_type_str','拜访类型'),
            array('visit_purpose_str','拜访目的'),
            
        );
        $filename = '运维端销售记录报表';
        $this->exportToExcel($cell,$data,$filename,1);
        
        
        
    }
}