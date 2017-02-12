<?php
namespace Admin\Controller;
    // use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
use Admin\Model\CategoModel;
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
        $order = I('_order','create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        $name = I('titlename');

        $beg_time = I('starttime','');
        $end_time = I('endtime','');
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time'";
        if($name)
        {
            $this->assign('name',$name);
            $where .= "	AND title LIKE '%{$name}%'";

        }
        $result = $artModel->getList($where,$orders,$start,$size);
        // foreach($result['list']

        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);

        $this->display('content');


    }


}