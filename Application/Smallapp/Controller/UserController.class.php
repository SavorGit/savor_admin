<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-授权用户
 *
 */
class UserController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $size       = I('numPerPage',50);       //显示每页记录数
        $pagenum      = I('pageNum',1);          //当前页码
        $pagenum      = $pagenum ? $pagenum :1;
        $order      = I('_order','id');         //排序字段
        $sort       = I('_sort','desc');        //排序类型
        $orders     = $order.' '.$sort;
        $start_date = I('start_date','','trim');
        $end_date   = I('end_date','','trim');
        $subscribe_start_date = I('subscribe_start_date','','trim');
        $subscribe_end_date = I('subscribe_end_date','','trim');
        $is_wx_auth = I('is_wx_auth',-1,'intval');
        $gender     = I('gender',-1,'intval');
        $small_app_id = I('small_app_id');
        $is_subscribe = I('is_subscribe',-1,'intval');
        $nickname = I('nickname','','trim');
        $openid = I('openid','','trim');

        $where = array();
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('注册开始时间不能大于结束时间');
            }
            $where['create_time'] = array(array('EGT',$start_date." 00:00:00"),array('ELT',$end_date." 23:59:59"));
        }else if(!empty($start_date) && empty($end_date)){
            $where['create_time']= array('EGT',$start_date." 00:00:00");
        }else if(empty($start_date) && !empty($end_date)){
            $where['create_time'] = array('ELT',$end_date." 23:59:59");
        }
        if($subscribe_start_date && $subscribe_end_date){
            if($subscribe_end_date<$subscribe_start_date){
                $this->error('关注开始时间不能大于结束时间');
            }
            $where['subscribe_time'] = array(array('EGT',$subscribe_start_date." 00:00:00"),array('ELT',$subscribe_end_date." 23:59:59"));
        }
        if($is_wx_auth>=0){
            if($is_wx_auth==2){
                $where['is_wx_auth'] = array('in','2,3');
            }else {
                $where['is_wx_auth'] = $is_wx_auth;
            }   
        }
        if($small_app_id){
            if($small_app_id == 2){
                $where['small_app_id'] = array('in',array(2,3));
            }else{
                $where['small_app_id'] = $small_app_id;
            }
        }
        if($gender>=0){
            $where['gender'] = $gender;
        }
        if($is_subscribe>=0){
            $where['is_subscribe'] = $is_subscribe;
        }
        if(!empty($nickname)){
            $where['nickName'] = array('like',"%$nickname%");
        }
        if(!empty($openid)){
            $where['openid'] = $openid;
        }
        $start = ($pagenum-1)* $size;
        $limit ="$start,$size";
        
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $where['status'] =1;
        $data = $m_user->getWhere('*', $where, $orders, $limit);
        $small_app_id_arr = C('all_smallapps');
        foreach ($data as $k=>$v){
            if($v['subscribe_time']=='0000-00-00 00:00:00'){
                $data[$k]['subscribe_time'] = '';
            }
            $data[$k]['small_app_id_str'] = $small_app_id_arr[$v['small_app_id']];
        }
        unset($small_app_id_arr[3],$small_app_id_arr[11]);

        $count = $m_user->where($where)->count();
        $objPage = new Page($count,$size);
        $page = $objPage->admin_page();

        $this->assign('openid',$openid);
        $this->assign('nickname',$nickname);
        $this->assign('is_subscribe',$is_subscribe);
        $this->assign('is_wx_auth',$is_wx_auth);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('gender',$gender);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('subscribe_start_date',$subscribe_start_date);
        $this->assign('subscribe_end_date',$subscribe_end_date);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pagenum);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $this->assign('small_app_id_arr',$small_app_id_arr);
        $this->assign('page',$page);
        $this->assign('datalist',$data);
        $this->display();
        
    }
}