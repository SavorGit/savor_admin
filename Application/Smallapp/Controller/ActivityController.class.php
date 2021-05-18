<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 活动
 *
 */
class ActivityController extends BaseController {

    public function activitylist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',99,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array();
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'activity/activitylist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        if($status!=99){
            $where['a.status'] = $status;
        }
        if($hotel_name){
            $where['hotel.name'] = array('like',"%{$hotel_name}%");
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.*,hotel.name as hotel_name';
        $orderby = 'a.id desc';
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res_list = $m_activity->getList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $all_status = C('ACTIVITY_STATUS');
        if(!empty($data_list)){
            $oss_host = 'http://'.C('OSS_HOST_NEW');
            $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
            foreach ($data_list as $k=>$v){
                $data_list[$k]['image_url'] = $oss_host.'/'.$v['image_url'];
                $data_list[$k]['status_str'] = $all_status[$v['status']];
                if($v['type']==3){
                    $data_list[$k]['status_str'] = '';
                }
                $nums = 0;
                if($v['type']==3 || in_array($v['status'],array(1,2))){
                    $where = array('activity_id'=>$v['id']);
                    $res_num = $m_activityapply->getAll('count(id) as num',$where,0,1,'','');
                    if(!empty($res_num)){
                        $nums = intval($res_num[0]['num']);
                    }
                }
                $data_list[$k]['nums'] = $nums;
            }
        }


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

    public function detail(){
        $activity_id = I('id',0,'intval');
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $fields = 'a.*,user.nickName,user.avatarUrl';
        $where = array('activity_id'=>$activity_id);
        $res = $m_activityapply->getList($fields,$where,'a.id desc');
        $all_mac = array();
        foreach ($res as $k=>$v){
            if(!in_array($v['box_mac'],$all_mac)){
                $all_mac[]=$v['box_mac'];
            }
        }
        $m_box = new \Admin\Model\BoxModel();
        $where = array('box.mac'=>array('in',$all_mac));
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $fields = 'box.mac,box.name';
        $res_box = $m_box->getBoxByCondition($fields,$where,'');
        $boxs = array();
        foreach ($res_box as $v){
            $boxs[$v['mac']] = $v['name'];
        }
        $all_status = array('1'=>'未开奖','2'=>'已中奖','3'=>'未中奖','4'=>'已中奖未完成','5'=>'已中奖已完成待领取');
        foreach ($res as $k=>$v){
            $res[$k]['box_name'] = $boxs[$v['box_mac']];
            $res[$k]['status_str'] = $all_status[$v['status']];
        }

        $this->assign('datalist',$res);
        $this->display();
    }


}