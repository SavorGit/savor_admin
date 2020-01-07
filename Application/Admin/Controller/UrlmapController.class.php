<?php
namespace Admin\Controller;
/**
 * @desc URL地址映射管理
 *
 */
class UrlmapController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function urlmaplist(){
        $name = I('name','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $where = array();
        if(!empty($name)){
            $where['name'] = array('like',"%$name%");
        }
        $start  = ($page-1) * $size;
        $m_urlmap  = new \Admin\Model\UrlmapModel();
        $result = $m_urlmap->getDataList('*',$where, 'id desc', $start, $size);
        $hash_ids_key = C('HASH_IDS_KEY');
        $hashids = new \Common\Lib\Hashids($hash_ids_key);
        $short_url = C('SHORT_URL').'/rd/';
        $m_qrscanrecord = new \Admin\Model\QrscanRecordModel();
        foreach ($result['list'] as $k=>$v){
            $encode_id = $hashids->encode($v['id']);
            $result['list'][$k]['qrcode'] = $short_url.$encode_id;
            $result['list'][$k]['qrnum'] = $m_qrscanrecord->where(array('urlmap_id'=>$v['id']))->count();
        }

        $this->assign('datalist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('urlmaplist');
    }

    public function urladd(){
        $this->assign('dinfo',array());
        $this->display('urladd');
    }


    public function urledit(){
        $id = I('id', 0, 'intval');
        $m_urlmap  = new \Admin\Model\UrlmapModel();
        if(IS_GET){
            $dinfo = $m_urlmap->getInfo(array('id'=>$id));
        	$this->assign('dinfo',$dinfo);
        	$this->display('urladd');
        }else{
        	$name = I('post.name','','trim');
        	$link = I('post.link','','trim');

        	if(empty($name)){
        		$this->output('缺少必要参数!', 'urlmap/urladd', 2, 0);
        	}
        	$where = array('link'=>$link);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_url = $m_urlmap->getInfo($where);
        	}else{
                $res_url = $m_urlmap->getInfo($where);
        	}
        	if(!empty($res_url)){
        		$this->output('url不能重复', 'urlmap/urladd', 2, 0);
        	}

        	$data = array('name'=>$name,'link'=>$link);
        	if($id){
        	    $url_id = $id;
        		$condition = array('id'=>$id);
        		$result = $m_urlmap->updateData($condition,$data);
        	}else{
        		$result = $m_urlmap->addData($data);
                $url_id = $result;
        	}

            $hash_ids_key = C('HASH_IDS_KEY');
            $hashids = new \Common\Lib\Hashids($hash_ids_key);
            $short_url = C('SHORT_URL').'/rd/';
            $encode_id = $hashids->encode($url_id);
            $url = $short_url.$encode_id;
            $condition = array('id'=>$url_id);
            $m_urlmap->updateData($condition,array('short_link'=>$url));

        	if($result){
        		$this->output('操作成功', 'urlmap/urlmaplist');
        	}else{
        		$this->output('操作失败', 'urlmap/urladd',2,0);
        	}
        }
    }

    public function scanrecord(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $urlmap_id = I('urlmap_id',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $start  = ($page-1) * $size;
        if(empty($start_time)){
            $start_time = date('Y-m-d',strtotime("-7days"));
        }
        if(empty($end_time)){
            $end_time = date('Y-m-d');
        }
        if($start_time>$end_time){
            $this->output('请选择正确时间段', 'urlmap/scanrecord',2,0);
        }

        $m_urlmap  = new \Admin\Model\UrlmapModel();
        $res = $m_urlmap->getDataList('*','', 'id desc');
        $all_url = array();
        foreach ($res as $v){
            $info = array('id'=>$v['id'],'name'=>$v['name']);
            if($urlmap_id && $v['id']==$urlmap_id){
                $info['is_select'] = 'selected';
            }else{
                $info['is_select'] = '';
            }
            $all_url[$v['id']] = $info;
        }

        $m_qrscanrecord = new \Admin\Model\QrscanRecordModel();
        $where = array();
        if($urlmap_id){
            $where['urlmap_id'] = $urlmap_id;
        }
        $where['add_time'] = array(array('egt',"$start_time 00:00:00"),array('elt',"$end_time 23:59:59"),'and');
        $result = $m_qrscanrecord->getDataList('*',$where,'id desc',$start, $size);
        $datalist = $result['list'];
        if(!empty($datalist)){
            $m_box = new \Admin\Model\BoxModel();
            foreach ($datalist as $k=>$v){
                $area = $hotel_name = $room_name = '';
                if($v['box_mac']){
                    $box_info = $m_box->getHotelInfoByBoxMac($v['box_mac']);
                    $area = $box_info['area_name'];
                    $hotel_name = $box_info['hotel_name'];
                    $room_name = $box_info['room_name'];
                }
                $datalist[$k]['name'] = $all_url[$v['urlmap_id']]['name'];
                $datalist[$k]['area'] = $area;
                $datalist[$k]['hotel_name'] = $hotel_name;
                $datalist[$k]['room_name'] = $room_name;
            }
        }

        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('allurls',$all_url);
        $this->display();
    }


    public function urldel(){
    	$id = I('get.id', 0, 'intval');
        $m_urlmap  = new \Admin\Model\UrlmapModel();
    	$condition = array('id'=>$id);
    	$result = $m_urlmap->delData($condition);
    	if($result){
            $m_qrscanrecord = new \Admin\Model\QrscanRecordModel();
            $m_qrscanrecord->delData(array('urlmap_id'=>$id));
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}