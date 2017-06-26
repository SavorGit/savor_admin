<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class ReportController extends BaseController{

	public $path = 'category/img';
	public $oss_host = '';
	public function __construct() {
		parent::__construct();
	}


	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function heart(){

		$heartModel = new \Admin\Model\HeartLogModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','last_heart_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('name');
		$type = I('type');
		if($name){
			$this->assign('name',$name);
			$where .= "	AND hotel_name LIKE '%{$name}%'";
		}

		if($type){
		    $this->assign('type',$type);
			$where .= "	AND type= '{$type}' ";
		}
		$result = $heartModel->getList($where,$orders,$start,$size);
		$time = time();
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
			$d_time = strtotime($val['last_heart_time']);
			$diff = $time - $d_time;
			if($diff< 3600) {
				$val['last_heart_time'] = floor($diff/60).'分';

			}else if ($diff >= 3600 && $diff <= 86400) {
				$hour = floor($diff/3600);
				$min = floor($diff%3600/60);
				$val['last_heart_time'] = $hour.'小时'.$min.'分';
			}else if ($diff > 86400) {
				$day = floor($diff/86400);
				$hour = floor($diff%86400/3600);
				$val['last_heart_time'] = $day.'天'.$hour.'小时';
			}
		}

		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('heartlist');
	}


	/**
	 * 新增分类
	 *
	 */
	public function addCate(){
		$id = I('get.id');
		$catModel = new CategoModel;
		if($id){
			$vinfo = $catModel->find($id);
			$image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
			$vinfo['oss_addr'] = $image_host.$vinfo['img_url'];
			$this->assign('vinfo',$vinfo);
		}
		return $this->display('addCat');
	}


	/*
	 * 修改状态
	 */

	public function changestate(){
		$cid = I('post.cid');
		$save = array();
		$save['state'] = I('post.state');
		$catModel = new CategoModel;
		$res_save = $catModel->where('id='.$cid)->save($save);
		if($res_save){
			echo 1;
		} else {
			echo 0;
		}
	}

	/**
	 * 保存或者更新分类信息
	 * @return [type] [description]
	 */
	public function doAddCat(){
		$catModel = new CategoModel;
		$id                  = I('post.id');
		$save                = [];
		$save['name']        = I('post.cat_name','','trim');
		$save['sort_num']    = I('post.sort','','intval');

		$save['update_time'] = date('Y-m-d H:i:s');
		$mediaid = I('post.media_id');
		$mediaModel = new \Admin\Model\MediaModel();
		//$mediaid = 11;
		$oss_addr = $mediaModel->find($mediaid);
		$oss_addr = $oss_addr['oss_addr'];
		$save['img_url'] = $oss_addr;
		if($id){
			$res_save = $catModel->where('id='.$id)->save($save);
			if($res_save){
				$this->output('操作成功!', 'release/category');
			}else{
				$this->output('操作失败!', 'release/doAddCat');
			}
		}else{
			$save['state']    =  0;
			$save['create_time'] = date('Y-m-d H:i:s');
			//刷新页面，关闭当前
			$res_save = $catModel->add($save);
			if($res_save){
			    $this->output('添加分类成功!', 'release/category');
			}else{
				$this->output('操作失败!', 'release/doAddCat');
			}
		}
	}
    public function contAndProm(){
        $size   = I('numPerPage',50);     //显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);          //当前页码
        $this->assign('pageNum',$start);
        $order = I('_order','s_read_count'); //排序字段
        $this->assign('_order',$order);
        $sort = I('_sort','desc');        //排序类型
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where =' 1=1';
        
       /*  $start_date = I('start_date');
        $end_date   = I('end_date');
        $userid = I('userid');
        $category_id = I('category_id','0','intval');
        $content_name = I('content_name','','trim');
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        if($start_date){
            $this->assign('start_date',$start_date);
            $start_date = date('YmdH',strtotime($start_date));
            $where .= " and date_time >='".$start_date."'";
        }
        if($end_date){
            $this->assign('end_date',$end_date);
            $end_date = date('YmdH',strtotime($end_date));
            $where .= " and date_time <='".$end_date."'";
        }
        $m_sysuser = new \Admin\Model\UserModel();
        if($userid){
            
            $this->assign('userid',$userid);
            $users = $m_sysuser->getUser(" and id=$userid",'id,username,remark');
           
            $userinfo = $users[0];
            if($userinfo){
                $where .=" and operators='".$userinfo['username']."' or operators='".$userinfo['remark']."'";
            }
            
        }
        if($category_id){
            $this->assign('category_id',$category_id);
            $where .=" and category_id=$category_id";
        } */
        $m_sysuser = new \Admin\Model\UserModel();
        $content_name = I('content_name','','trim');
        if($content_name){
            $this->assign('content_name',$content_name);
            $where .=" and content_name like '%".$content_name."%'";
        }
        
        $m_content_details_final = new \Admin\Model\ContDetFinalModel();
        $data = $m_content_details_final->getDataList($where,$orders,$start,$size);
        
        //分类
        $m_category = new \Admin\Model\CategoModel();
        $category_list = $m_category->getWhere('state = 1', 'id,name');
        array_unshift($category_list, array('id'=>'-1','name'=>'热点'),array('id'=>'-2','name'=>'点播'));
        
        //编辑
        
        $user_list = $m_sysuser->getUser(' and groupid=11');
        $this->assign('user_list',$user_list);
        $this->assign('category_list',$category_list);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('contandprom');
    }
}
