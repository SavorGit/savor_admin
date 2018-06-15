<?php
/**
 * @desc   机顶盒日志文件
 * @author zhang.yingtao
 * @since  2018-06-14
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class BoxlogfileController extends BaseController{
    /**
     * @desc 网络日志文件
     */
    public function network(){
        $ajaxversion   = I('ajaxversion',0,'intval');
        $size   = I('numPerPage',20);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $where = '1';
        $area_id = I('area_id');
        if($area_id){
            $where .=" and a.area=".$area_id;
        }
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $box_mac        = I('box_mac');
        
        if($start_date && $end_date){
            if($start_date>$end_date){
                $this->error('结束时间不能小于开始时间');
            }
            $where .=" and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'";
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }else if($start_date && empty($end_date)){
            $where .=" and a.create_time>='".$start_date."'";
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where .= " and a.create_time<='".$end_date."'";
            $this->assign('end_date',$end_date);
        }
        if($box_mac){
            $where .=" and a.box_mac='".$box_mac."'";
            $this->assign('box_mac',$box_mac);
        }
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getAllArea();
        
        
        $m_box_log = new \Admin\Model\Oss\BoxLogModel();
        if($box_mac || ($start_date || $end_date)){
            $fields = 'a.bucket_name,a.event_time,a.size,a.oss_key,a.e_tag,a.flag,a.try_count,
                   a.create_time,area.region_name area,a.box_mac';
            $list = $m_box_log->getList($fields, $where,$orders,$start,$size);
        }else {
            $list = array();
        }
        
        
        
        $this->assign('area',$area_list);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('network');
    }
    /**
     * @desc 一代单机日志文件
     */
    public function firstGeneration(){
        $ajaxversion   = I('ajaxversion',0,'intval');
        $size   = I('numPerPage',20);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        
        
        $where = '1';
        $area_id = I('area_id');
        if($area_id){
            $where .=" and a.area=".$area_id;
        }
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $box_mac        = I('box_mac');
        
        if($start_date && $end_date){
            if($start_date>$end_date){
                $this->error('结束时间不能小于开始时间');
            }
            $where .=" and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'";
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }else if($start_date && empty($end_date)){
            $where .=" and a.create_time>='".$start_date."'";
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where .= " and a.create_time<='".$end_date."'";
            $this->assign('end_date',$end_date);
        }
        if($box_mac){
            $where .=" and a.box_mac='".$box_mac."'";
            $this->assign('box_mac',$box_mac);
        }
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getAllArea();
        
        $m_stand_alone_log = new \Admin\Model\Oss\StandAloneLogModel();
        $fields = 'a.bucket_name,a.event_time,a.size,a.oss_key,a.e_tag,a.flag,a.try_count,
                   a.create_time,a.box_mac,a.area';
        $list = $m_stand_alone_log->getList($fields, $where,$orders,$start,$size);
        
        
        $this->assign('area',$area_list);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('fgeneration');
    }
    /**
     * @desc 三代单机日志文件
     */
    public function thirdGeneration(){
        $ajaxversion   = I('ajaxversion',0,'intval');
        $size   = I('numPerPage',20);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $where = '1';
        $area_id = I('area_id');
        if($area_id){
            $where .=" and a.area=".$area_id;
        }
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $box_mac        = I('box_mac');
        
        if($start_date && $end_date){
            if($start_date>$end_date){
                $this->error('结束时间不能小于开始时间');
            }
            $where .=" and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'";
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }else if($start_date && empty($end_date)){
            $where .=" and a.create_time>='".$start_date."'";
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where .= " and a.create_time<='".$end_date."'";
            $this->assign('end_date',$end_date);
        }
        if($box_mac){
            $where .=" and a.box_mac='".$box_mac."'";
            $this->assign('box_mac',$box_mac);
        }
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getAllArea();
        
        $m_stand_alone_log_v3 = new \Admin\Model\Oss\StandAloneLogV3Model();
        $fields = 'a.bucket_name,a.event_time,a.size,a.oss_key,a.e_tag,a.flag,a.try_count,
                   a.create_time,area.region_name area,a.box_mac';
        $list = $m_stand_alone_log_v3->getList($fields, $where,$orders,$start,$size);
        
        
        $this->assign('area',$area_list);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('tgeneration');
    }
}