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
        $start = ($pagenum-1)* $size;
        
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        
        $where = array();
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $is_wx_auth = I('is_wx_auth',-1,'intval');
        $gender     = I('gender',-1,'intval');
        //var_dump($is_wx_auth);
        $small_app_id = I('small_app_id');
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('开始时间不能大于结束时间');
            }
            $where['create_time'] = array(array('EGT',$start_date." 00:00:00"),array('ELT',$end_date." 23:59:59"));
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }else if(!empty($start_date) && empty($end_date)){
            $where['create_time']= array('EGT',$start_date." 00:00:00");
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where['create_time'] = array('ELT',$end_date." 23:59:59");
            $this->assign('end_date',$end_date);
        }
        if($is_wx_auth>=0){
            if($is_wx_auth==2){
                $where['is_wx_auth'] = array('in','2,3');
            }else {
                $where['is_wx_auth'] = $is_wx_auth;
            }   
        }
        $this->assign('is_wx_auth',$is_wx_auth);
        if(!empty($small_app_id)){
            $where['small_app_id'] = $small_app_id;
            $this->assign('small_app_id',$small_app_id);
        }
        if($gender>=0){
            $where['gender'] = $gender;
            
        }
        $this->assign('gender',$gender);
        
        $limit ="limit $start,$size";
            
        $m_user = new \Admin\Model\Smallapp\UserModel();
        
        $fields = "*";
        
        $where['status'] =1;
        $data = $m_user->getWhere($fields, $where, $orders, $limit);
        $count = $m_user->where($where)->count();
        $objPage = new Page($count,$size);
        $page = $objPage->admin_page();
        //echo $m_user->getLastSql();
        //小程序类型
        $small_app_id_arr = array(array('id'=>1,'name'=>'普通版'),
                                  array('id'=>2,'name'=>'老极简版'),
                                  array('id'=>3,'name'=>'极简版'),
                                  array('id'=>4,'name'=>'餐厅版'),
        );
        $this->assign('small_app_id_arr',$small_app_id_arr);
        $this->assign('page',$page);
        
        $this->assign('data',$data);
        $this->display();
        
    }
}