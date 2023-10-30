<?php
namespace Dataexport\Controller;

class CrmtaskController extends BaseController{

    public function residentdata(){
        $month = I('month',0,'intval');
        $start_time = date('Y-m-01 00:00:00',strtotime($month));
        $end_time = date('Y-m-31 23:59:59',strtotime($month));
        $model = M();
        $sql_city = 'select id,region_name from savor_area_info where is_in_hotel=1 order by id asc';
        $all_citys = array();
        $res_city = $model->query($sql_city);
        foreach ($res_city as $v){
            $all_citys[$v['id']]=$v['region_name'];
        }
        $sql = "select a.residenter_id,a.residenter_name,count(a.id) as release_num,COUNT(DISTINCT a.hotel_id) as hotel_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            where a.add_time>='$start_time' and a.add_time<='$end_time' and a.residenter_id>0
            and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by a.residenter_id";
        $res_data1 = $model->query($sql);
        $all_release_hotel_num = array();
        foreach ($res_data1 as $v){
            $all_release_hotel_num[$v['residenter_id']] = $v;
        }
        $sql_refuse = "select a.residenter_id,a.residenter_name,count(a.id) as refuse_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            where a.add_time>='$start_time' and a.add_time<='$end_time' and a.residenter_id>0
            and a.handle_status=1 and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by a.residenter_id";
        $res_refuse = $model->query($sql_refuse);
        $all_refuse_num = array();
        foreach ($res_refuse as $v){
            $all_refuse_num[$v['residenter_id']]=$v;
        }
        $sql_finish = "select a.residenter_id,a.residenter_name,count(a.id) as finish_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            where a.add_time>='$start_time' and a.add_time<='$end_time' and a.residenter_id>0
            and a.status=3 and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by a.residenter_id";
        $res_finish = $model->query($sql_finish);
        $all_finish_num = array();
        foreach ($res_finish as $v){
            $all_finish_num[$v['residenter_id']]=$v;
        }

        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.manage_city,user.id as residenter_id,user.remark as residenter_name';
        $where = array('a.state'=>1,'user.status'=>1);
        $res_opusers = $m_opuser_role->getAllRole($fields,$where,'a.id desc');
        $datalist = array();
        foreach ($res_opusers as $v) {
            $area_id = $v['manage_city'];
            $release_num=$hotel_num=0;
            if(isset($all_release_hotel_num[$v['residenter_id']])){
                $release_num = $all_release_hotel_num[$v['residenter_id']]['release_num'];
                $hotel_num = $all_release_hotel_num[$v['residenter_id']]['hotel_num'];
            }
            $refuse_num=0;
            if(isset($all_refuse_num[$v['residenter_id']])){
                $refuse_num = $all_refuse_num[$v['residenter_id']]['refuse_num'];
            }
            $finish_num=0;
            if(isset($all_finish_num[$v['residenter_id']])){
                $finish_num = $all_finish_num[$v['residenter_id']]['finish_num'];
            }

            $info = array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'residenter_id'=>$v['residenter_id'],'residenter_name'=>$v['residenter_name'],
                'hotel_num'=>$hotel_num,'release_num'=>$release_num,'finish_num'=>$finish_num,'refuse_num'=>$refuse_num,
            );
            $datalist[]=$info;
        }

        $cell = array(
            array('area_name','城市'),
            array('residenter_name','驻店人'),
            array('hotel_num','餐厅数'),
            array('release_num','发布总任务数'),
            array('refuse_num','拒绝任务数'),
            array('finish_num','完成任务数'),
        );
        $filename = '驻店任务数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function areadata(){
        $month = I('month',0,'intval');
        $start_time = date('Y-m-01 00:00:00',strtotime($month));
        $end_time = date('Y-m-31 23:59:59',strtotime($month));
        $model = M();
        $sql = "select hotel.area_id,area.region_name as area_name,count(a.id) as release_num,COUNT(DISTINCT a.hotel_id) as hotel_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            left join savor_area_info as area on hotel.area_id=area.id
            where a.add_time>='$start_time' and a.add_time<='$end_time'
            and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by hotel.area_id";
        $res_area_data = $model->query($sql);

        $sql_refuse = "select hotel.area_id,area.region_name as area_name,count(a.id) as refuse_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            left join savor_area_info as area on hotel.area_id=area.id
            where a.add_time>='$start_time' and a.add_time<='$end_time' and a.residenter_id>0
            and a.handle_status=1 and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by hotel.area_id";
        $res_refuse = $model->query($sql_refuse);
        $all_refuse_num = array();
        foreach ($res_refuse as $v){
            $all_refuse_num[$v['area_id']]=$v;
        }
        $sql_finish = "select hotel.area_id,area.region_name as area_name,count(a.id) as finish_num from savor_crm_task_record as a left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id
            left join savor_area_info as area on hotel.area_id=area.id
            where a.add_time>='$start_time' and a.add_time<='$end_time'
            and a.status=3 and a.off_state=1 and hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 
            group by hotel.area_id";
        $res_finish = $model->query($sql_finish);
        $all_finish_num = array();
        foreach ($res_finish as $v){
            $all_finish_num[$v['area_id']]=$v;
        }

        $datalist = array();
        foreach ($res_area_data as $v){
            $area_id = $v['area_id'];
            $area_name = $v['area_name'];

            $release_num = $v['release_num'];
            $hotel_num = $v['hotel_num'];
            $refuse_num=0;
            if(isset($all_refuse_num[$area_id])){
                $refuse_num = $all_refuse_num[$area_id]['refuse_num'];
            }
            $finish_num=0;
            if(isset($all_finish_num[$area_id])){
                $finish_num = $all_finish_num[$area_id]['finish_num'];
            }
            $datalist[]=array('area_name'=>$area_name,'hotel_num'=>$hotel_num,'release_num'=>$release_num,
                'refuse_num'=>$refuse_num,'finish_num'=>$finish_num);
        }
        $cell = array(
            array('area_name','城市'),
            array('hotel_num','餐厅数'),
            array('release_num','发布总任务数'),
            array('refuse_num','拒绝任务数'),
            array('finish_num','完成任务数'),
        );
        $filename = '城市任务数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}