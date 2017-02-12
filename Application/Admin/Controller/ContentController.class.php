<?php
namespace Admin\Controller;
/**
 * @desc 内容管理
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
        $order = I('_order','create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $starttime = I('starttime',date("Y-m-d H:i", time()-3600));
        $endtime = I('endtime', date("Y-m-d H:i"));
        $starttime = $starttime.':00';
        $endtime = $endtime.':00';
        $where = "1=1";
        $name = I('titlename');

        if ($starttime > $endtime) {
            $this->display('content');
        } else {
            if($name){
                $this->assign('name',$name);
                $where .= "	AND title LIKE '%{$name}%'";
                $where .= "	AND (`create_time`) > '{$starttime}' AND (`create_time`) < '{$endtime}' ";
            }
            $result = $artModel->getList($where,$orders,$start,$size);
            $this->assign('list', $result['list']);
            $this->assign('page',  $result['page']);
            $this->display('content');
        }
    }
}