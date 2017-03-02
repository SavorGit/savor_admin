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
        //$where .= " AND state=2 ";
        $category_id = I('category_id',0,'intval');
        if($category_id) $where .=" AND category_id='$category_id'";
        $content_type = I('content_type','','intval');
        if(is_numeric($content_type)){
            switch ($content_type){
                case 0:
                    $where .= " AND type=0";
                    break;
                case 1:
                    $where .=" AND type=1";
                    break;
                case 2:
                    $where .=" AND type=2";
                    break;
                case 3:
                    $where .=" AND type=3 and media_id=0";
                    break;
                case 4:
                    $where .=" AND type=3 and media_id>0"; 
                    break;
            }
            $this->assign('content_type',$content_type);
        }
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
	    $result['list'] = $artModel->changeCatname($result['list']);
	    $catModel = new \Admin\Model\CategoModel();
	    $where = " state=1";
    	$field = 'id,name';
    	$category_list = $catModel->getWhere($where, $field);
    	$this->assign('vcainfo',$category_list);
	    $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('content_type_arr',$this->content_type_arr);
	    $this->assign('category_id',$category_id);
	    $this->assign('timeinfo',$time_info);
        $this->assign('ctype', $type);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('content');
    }

    /*
	 * 修改状态
	 */
    public function operateStatus(){


        $adsid = I('request.adsid','0','intval');
        $artModel = new \Admin\Model\ArticleModel();
        $message = '';
        $flag = I('request.flag');
        if(flag == 2){
            $state = 3;
        } else {
            $state = 2;
        }
        $data = array('state'=>$state);

        $res = $artModel->where("id='$adsid'")->save($data);

        if($res){
            $message = '更新审核状态成功';
        }

        if($message){
            $this->output($message, 'content/getlist',2);
        }else{
            $this->output('更新操作失败', 'content/getlist');
        }


    }


    public function check(){
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
        $where .= " AND state in (0,1,3)";
        $result = $artModel->getList($where,$orders,$start,$size);
        $result['list'] = $artModel->changeCatname($result['list']);
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        $this->assign('ctype', $type);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('contentcheck');
    }

}