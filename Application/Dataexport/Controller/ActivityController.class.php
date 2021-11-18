<?php
namespace Dataexport\Controller;

class ActivityController extends BaseController{

    public function tastwineuserlist(){
        $activity_id = I('activity_id',0,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array('activity.type'=>array('in',array(6,7)));
        if($activity_id){
            $where['a.activity_id'] = $activity_id;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $fields = 'a.id,activity.name as activity_name,a.hotel_name,a.box_name,a.box_mac,a.openid,a.mobile,user.nickName,user.avatarUrl,a.add_time';
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $result = $m_activityapply->gettastwineList($fields,$where,'a.id desc', 0,100000);
        $datalist = $result['list'];

        $cell = array(
            array('id','ID'),
            array('activity_name','活动名称'),
            array('hotel_name','酒楼名称'),
            array('box_name','包间名称'),
            array('nickname','领取人'),
            array('mobile','手机号码'),
            array('add_time','领取时间'),
        );
        $filename = '品鉴酒参与人数列表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}