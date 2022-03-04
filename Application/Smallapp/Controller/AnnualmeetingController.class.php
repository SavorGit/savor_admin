<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class AnnualmeetingController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $type = I('type',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array();
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'annualmeeting/datalist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.start_time'] = array('egt',$start_time);
            $where['a.end_time'] = array('elt',$end_time);
        }
        if($status){
            $where['a.status'] = $status;
        }
        if($type){
            $where['a.type'] = $type;
        }
        if(!empty($hotel_name)){
            $where['m.hotel_name'] = array('like',"%$hotel_name%");
        }

        $all_types = array('1'=>'企业宣传片','2'=>'祝福视频');
        $all_status = array('1'=>'待下载','3'=>'下载完成');
        $start = ($pageNum-1)*$size;
        $fields = 'a.*,m.hotel_name,m.room_name,m.box_mac';
        $orderby = 'a.id desc';
        $m_activity = new \Admin\Model\Smallapp\AnnualmeetingVideoModel();
        $res_list = $m_activity->getList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        if(!empty($data_list)){
            $oss_host = 'http://'.C('OSS_HOST_NEW');
            foreach ($data_list as $k=>$v){
                $oss_addr = $oss_host.'/'.$v['oss_addr'];
                $data_list[$k]['oss_addr'] = $oss_addr;
                $data_list[$k]['video_img'] = $oss_addr.'?x-oss-process=video/snapshot,t_3000,f_jpg,w_450,m_fast';
                $data_list[$k]['status_str'] = $all_status[$v['status']];
                $data_list[$k]['type_str'] = $all_types[$v['type']];
            }
        }

        $this->assign('type',$type);
        $this->assign('all_types',$all_types);
        $this->assign('all_status',$all_status);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}