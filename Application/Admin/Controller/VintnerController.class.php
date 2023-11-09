<?php
namespace Admin\Controller;

class VintnerController extends BaseController {

    public function __construct() {
        parent::__construct();
    }
    
    public function datalist() {
    	$keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $m_vintner  = new \Admin\Model\VintnerModel();
        $result = $m_vintner->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        if(!empty($datalist)){
            $all_status = C('DATA_STATUS');
            foreach ($datalist as $k=>$v){
                $datalist[$k]['status_str'] = $all_status[$v['status']];
            }
        }

        $this->assign('keyword',$keyword);
        $this->assign('datalist',$datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }
    
    public function vintneradd(){
        $id = I('id', 0, 'intval');
        $m_vintner  = new \Admin\Model\VintnerModel();
        if(IS_GET){
        	$dinfo = array('status'=>1);
            $brand_ids = array();
        	if($id){
                $dinfo = $m_vintner->getInfo(array('id'=>$id));
                if(!empty($dinfo['brand_ids'])){
                    $brand_ids = explode(',',$dinfo['brand_ids']);
                }
        	}
        	$m_finance_brand = new \Admin\Model\FinanceBrandModel();
            $brands = $m_finance_brand->getDataList('id,name',array('status'=>1),'id asc');
            foreach ($brands as $k=>$v){
                $is_select = '';
                if(in_array($v['id'],$brand_ids)){
                    $is_select = 'selected';
                }
                $brands[$k]['is_select'] = $is_select;
            }
        	$this->assign('brands',$brands);
        	$this->assign('dinfo',$dinfo);
        	$this->display('vintneradd');
        }else{
        	$name = I('post.name','','trim');
        	$mobile = I('post.mobile','','trim');
        	$brand_ids = I('post.brand_ids');
        	$status = I('post.status',1,'intval');

        	if(empty($brand_ids)){
        		$this->output('请选择品牌', 'vintner/vintneradd', 2, 0);
        	}
        	$rwhere = array('mobile'=>$mobile,'status'=>1);
        	if($id){
        	    $rwhere['id'] = array('neq',$id);
            }
        	$res_info = $m_vintner->getInfo($rwhere);
        	if(!empty($res_info)){
                $this->output('当前手机号码已有可用账号', 'vintner/vintneradd', 2, 0);
            }
        	$now_brand_ids = join(',',$brand_ids);
            $userInfo = session('sysUserInfo');
            $op_sysuser_id = $userInfo['id'];

        	$data = array('name'=>$name,'mobile'=>$mobile,'brand_ids'=>$now_brand_ids,'status'=>$status,'op_sysuser_id'=>$op_sysuser_id);
        	if($id){
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_vintner->updateData(array('id'=>$id), $data);
        	}else{
        		$m_vintner->addData($data);
        	}

            $this->output('操作成功', 'vintner/datalist');
        }
    }

}