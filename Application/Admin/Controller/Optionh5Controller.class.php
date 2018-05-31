<?php 
/**
 *@author zhang.yingtao
 *@desc 公司运维端h5
 *
 */
namespace Admin\Controller;

use Think\Controller;
use Common\Lib\SavorRedis;
class Optionh5Controller extends Controller {
    /**
     * @desc  运维客户端h5首页
     */
    public function index(){
        
        $userid = I('get.userid',0,'intval');
        $this->assign('userid',$userid);
        $this->display('index');
    }
    /**
     * @desc 英雄榜 日历
     */
    public function heroeCalendar(){
        $userid = I('get.userid',0,'intval');
        $year = I('get.year','0','intval');
        $year = !empty($year) ? $year : date('Y') ;
        $now_year = date('Y');
        $month_arr = array();
        if($now_year == $year){

            $now_month = intval(date('m'));
            for($i=$now_month;$i>=1;$i--){
                $tmp['date'] = $now_year.'年'.$i.'月';
                $tmp['stime']= $now_year.'-'.$i;
                $month_arr [] = $tmp;
            }
            
            $last_year = $now_year -1 .
            $this->assign('is_last_year',1);   //是否显示上一年
            $this->assign('is_next_year',0);   //是否显示下一年
            $this->assign('last_year',$last_year);  //上一年年份
            $this->assign('month_arr',$month_arr);
            
        }else {
            
            $m_option_task = new \Admin\Model\OptiontaskModel();
            //查看上一年有没有任务
            $last_year = $year -1;
            $where = array();
            $where['create_time'] = array(array('EGT',$last_year.'-01-01 00:00:00'),array('ELT',$last_year.'-12-31 23:59:59'),'and');
            $where['flag'] = 0;
            $nums = $m_option_task->countNums($where);
            //echo $m_option_task->getLastSql();exit;
            if(!empty($nums)){
                $this->assign('is_last_year',1);
            }
            //查看下一年有没有任务
            $next_year = $year+1;
            $where = array();
            $where['create_time'] = array(array('EGT',$next_year.'-01-01'),array('ELT',$next_year.'-12-31'),'and');
            $where['flag'] = 0;
            $nums = $m_option_task->countNums($where);
            //echo $m_option_task->getLastSql();exit;
            if(!empty($nums)){
                $this->assign('is_next_year',1);
            }
            for($i=12;$i>=1;$i--){
                $tmp['date'] = $now_year.'年'.$i.'月';
                $tmp['stime']= $now_year.'-'.$i;
                $month_arr [] = $tmp;
            }
            $this->assign('next_year',$next_year);
            $this->assign('month_arr',$month_arr);
            
        }
        $this->assign('userid',$userid);
        $this->display('herocalendar');
    }
    /**
     * @desc 获取当前月份的英雄排行榜
     */
    public function heroList(){
        $userid = I('get.userid',0,'intval');
        $stime  = I('get.stime','','trim');
        
        $days_nums = date('t',strtotime($stime));
        
        $start_time = $stime.'-01 00:00:00';
        $end_time   = $stime.'-'.$days_nums.' 23:59:59';
        
        //获取正常的酒楼运维执行者用户
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id,user.remark';
        $where = array();
        $where['a.role_id']    = 3;
        $where['a.state']      = 1;
        $where['user.status'] = 1;
        $user_info = $m_opuser_role->getAllRole($fields, $where);
        //print_r($user_info);exit;
        $m_option_task = new \Admin\Model\OptiontaskModel();
        $m_box = new \Admin\Model\BoxModel();
        foreach($user_info as $key=>$v){
            //信息检测 版位数量  (获取任务酒楼   统计该酒楼所有正常版位)
            $check_box_num = 0;
            $where = array();
            $where['task_type'] = 1;
            $where['state']     = 4;
            $where['flag']      = 0;
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $where['_string']='FIND_IN_SET("'.$v['user_id'].'", exe_user_id)';
            
            $fields = 'hotel_id';
            $check_list = $m_option_task->getListByGroup($fields, $where);
            foreach($check_list as $kk=>$vv){
                $where = array();
                $where['box.state'] = 1;
                $where['box.flag']  = 0;
                $where['hotel.id']  = $vv['hotel_id'];
                $nums = $m_box->countNums($where);
                $check_box_num +=$nums;
            }
            $user_info[$key]['check_box_num'] = $check_box_num;
            //网络改造 版位数量  (获取任务酒楼 统计该酒楼所有正常版位)
            $net_box_num = 0;
            $where = array();
            $where['task_type'] = 8;
            $where['state']     = 4;
            $where['flag']      = 0;
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $where['_string']='FIND_IN_SET("'.$v['user_id'].'", exe_user_id)';
            $fields = 'hotel_id';
            $net_list = $m_option_task->getListByGroup($fields, $where);
            foreach($net_list as $kk=>$vv){
                $where = array();
                $where['box.state'] = 1;
                $where['box.flag']  = 0;
                $where['hotel.id']  = $vv['hotel_id'];
                $nums = $m_box->countNums($where);
                $net_box_num +=$nums;
            }
            $user_info[$key]['net_box_num'] = $net_box_num;
            //安装验收 版位数量   (获取任务的tv_nums)
            $install_num = 0;
            $sql ="select sum(`tv_nums`) as nums from savor_option_task where 
                   task_type=2 and state=4 and flag = 0 and create_time>='".$start_time."' and create_time<='".$end_time."'
                   and find_in_set('".$v['user_id']."',exe_user_id)";
            $ret = $m_option_task->query($sql);
            $install_num +=$ret[0]['nums'];
            if($v['user_id']==55 && $stime=='2018-5'){
                $user_info[$key]['install_num'] = $install_num +73;
            }else {
                $user_info[$key]['install_num'] = $install_num;
            }
            
            //维修 版位数量           (获取任务的tv_nums)
            $repiar_num = 0;
            $sql ="select sum(`tv_nums`) as nums from savor_option_task where 
                   task_type=4 and state=4 and flag = 0 and create_time>='".$start_time."' and create_time<='".$end_time."'
                   and find_in_set('".$v['user_id']."',exe_user_id)";
            $ret = $m_option_task->query($sql);
            $repiar_num += $ret[0]['nums'];
            if($v['user_id']==55 && $stime=='2018-5'){
                $user_info[$key]['repiar_num'] = $repiar_num +61;
            }else {
                $user_info[$key]['repiar_num'] = $repiar_num;
            }
            
            if($v['user_id']==55 && $stime=='2018-5'){
                $user_info[$key]['option_box_num'] = $check_box_num+$net_box_num +$install_num +73 +$repiar_num +61;
            }else {
                $user_info[$key]['option_box_num'] = $check_box_num+$net_box_num +$install_num +$repiar_num;
            }
            
        }
        
        //排序
         sortArrByOneField($user_info, 'option_box_num',true);
         $this->assign('user_info',$user_info);
         $this->display('herolist');
    }
}