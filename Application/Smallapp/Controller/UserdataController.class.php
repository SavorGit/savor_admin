<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序用户投屏数据统计
 *
 */
class UserdataController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $keyword = I('keyword','','trim');

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
        $where = array('a.static_date'=>array(array('EGT',$start_time),array('ELT',$end_time)));
        if(!empty($keyword)){
            $where['a.openid'] = array('like',"%$keyword%");
        }
        $m_userdata = new \Admin\Model\Smallapp\StaticUserdataModel();
        $fields = 'a.openid,a.static_date,sum(a.box_num) as box_num,sum(a.meal_num) as meal_num,
        count(DISTINCT a.hotel_id) as hotel_num,GROUP_CONCAT(DISTINCT hotel_name) as hotel_names,
        user.avatarUrl,user.nickName';
        $order = 'hotel_num desc';
        $groupby = 'a.openid';
        $start  = ($page-1) * $size;
        $countfields = 'count(DISTINCT a.openid) as tp_count';
        $res_data = $m_userdata->getCustomeList($fields,$where,$groupby,$order,$countfields,$start,$size);
        $datalist = $res_data['list'];

        $this->assign('keyword',$keyword);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('datalist', $datalist);
        $this->assign('page',  $res_data['page']);

        $this->display();
    }




}