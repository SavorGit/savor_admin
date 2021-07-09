<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class ForscreenplayController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        if(empty($start_time)){
            $start_time = date('Y-m-d',strtotime('-1 day'));
        }else{
            $start_time = date('Y-m-d',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Y-m-d',strtotime($end_time));
        }
        $m_forscreenplay = new \Admin\Model\Smallapp\StaticForscreenplayModel();
        $fields = 'sum(forscreen_num) as forscreen_num,sum(total_duration) as total_duration,sum(play_duration) as play_duration,
        sum(play_num) as play_num,sum(full_play_num) as full_play_num,type';
        $start = ($page - 1)*$size;
        $time_condition = array(array('EGT',$start_time),array('ELT',$end_time));
        $where = array('static_date'=>$time_condition);
        $res_data = $m_forscreenplay->getForscreenplayDataList($fields,$where,'id desc','type',$start,$size);
        $datalist = $res_data['list'];
        $all_type = array('1'=>'视频','2'=>'图片','3'=>'文件','4'=>'点播图片','5'=>'点播视频');
        foreach ($datalist as $k=>$v){
            if($v['type']==3){
                $play_num = $v['forscreen_num'];
            }else{
                $play_num = $v['play_num'];
            }
            $one_play_duration = round($v['play_duration']/$play_num);
            if(in_array($v['type'],array(1,5))){
                $play_rate = round($v['full_play_num']/$v['forscreen_num'],2) * 100;
                $play_rate = $play_rate.'%';
            }else{
                $play_rate = '无';
            }
            $datalist[$k]['type_str'] = $all_type[$v['type']];
            $datalist[$k]['one_play_duration'] = $one_play_duration;
            $datalist[$k]['play_rate'] = $play_rate;
        }
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('datalist', $datalist);
        $this->assign('page',  $res_data['page']);

        $this->display();
    }




}