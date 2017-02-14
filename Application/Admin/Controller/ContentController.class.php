<?php
namespace Admin\Controller;
/**
 * @desc 功能测试类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
class ContentController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function getlist(){
        $artModel = new ArticleModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        $name = I('titlename');
        $type = I('type',10,'intval');//10为全部

        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time 23:59:59'";
        if($name){
            $this->assign('name',$name);
            $where .= "	AND title LIKE '%{$name}%'";
        }
        if($type!=10){
            $where .= "	AND type='$type'";
        }
        $result = $artModel->getList($where,$orders,$start,$size);
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        $this->assign('ctype', $type);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('content');
    }

}