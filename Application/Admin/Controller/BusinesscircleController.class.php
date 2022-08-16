<?php
namespace Admin\Controller;

class BusinesscircleController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function datalist(){
        $name = I('name','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $where = array();
        if(!empty($name)){
            $where['name'] = array('like',"%$name%");
        }
        $start  = ($page-1) * $size;
        $m_circles  = new \Admin\Model\BusinessCircleModel();
        $result = $m_circles->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = array();
        $m_area  = new \Admin\Model\AreaModel();
        foreach ($result['list'] as $v){
            $area = $county = '';
            if($v['area_id']){
                $res_area = $m_area->getWhere('id,region_name',array('id'=>$v['area_id']),'id desc','');
                $area = $res_area[0]['region_name'];
            }
            if($v['county_id']){
                $res_area = $m_area->getWhere('id,region_name',array('id'=>$v['county_id']),'id desc','');
                $county = $res_area[0]['region_name'];
            }
            $v['area'] = $area;
            $v['county'] = $county;
            $datalist[]=$v;
        }

        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }

    public function circleadd(){
        $id = I('id', 0, 'intval');
        $m_circles  = new \Admin\Model\BusinessCircleModel();
        if(IS_GET){
            $m_area  = new \Admin\Model\AreaModel();
            $area = $m_area->getHotelAreaList();
            $dinfo = array('status'=>1);
            $parent_id = 35;
            if($id){
                $dinfo = $m_circles->getInfo(array('id'=>$id));
                $parent_map = array('1'=>35,'9'=>107);
                if(isset($parent_map[$dinfo['area_id']])){
                    $parent_id = $parent_map[$dinfo['area_id']];
                }else{
                    $parent_id = $dinfo['area_id'];
                }
            }
            $county_list = $m_area->getWhere('id,region_name',array('parent_id'=>$parent_id));

            $this->assign('county_list',$county_list);
            $this->assign('area',$area);
        	$this->assign('dinfo',$dinfo);
        	$this->display('circleadd');
        }else{
        	$name = I('post.name','','trim');
        	$area_id = I('post.area_id',0,'intval');
        	$county_id = I('post.county_id',0,'intval');
        	$status = I('post.status',0,'intval');
        	$where = array('name'=>$name);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_names = $m_circles->getInfo($where);
        	}else{
                $res_names = $m_circles->getInfo($where);
        	}
        	if(!empty($res_names)){
        		$this->output('商圈名称不能重复添加', 'businesscircle/circleadd', 2, 0);
        	}
        	$data = array('name'=>$name,'area_id'=>$area_id,'county_id'=>$county_id,'status'=>$status);
        	if($id){
        		$condition = array('id'=>$id);
        		$result = $m_circles->updateData($condition,$data);
        	}else{
        		$result = $m_circles->addData($data);
        	}
            $this->output('操作成功', 'businesscircle/datalist');
        }
    }

    public function circledel(){
    	$id = I('get.id', 0, 'intval');
    	$m_hotel = new \Admin\Model\HotelModel();
        $res_hotel = $m_hotel->getWhereorderData(array('business_circle_id'=>$id), 'id,name','id desc');
        if(!empty($res_hotel)){
            $this->output('当前商圈已使用,不能删除', '',2);
        }

        $m_circles  = new \Admin\Model\BusinessCircleModel();
        $condition = array('id'=>$id);
        $result = $m_circles->delData($condition);
    	if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}