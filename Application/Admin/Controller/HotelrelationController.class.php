<?php
namespace Admin\Controller;
class HotelrelationController extends BaseController {
	public function __construct() {
		parent::__construct();
	}

    public function relationlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_hotelrelation = new \Admin\Model\HotelRelationModel();
        $where = array();
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_hotelrelation->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        if(!empty($data_list)){
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_ids_arr = array();
            foreach ($data_list as $k=>$v){
                $hotel_ids_arr[]=$v['hotel_id'];
                $hotel_ids_arr[]=$v['rhotel_id'];
            }
            $hotel_ids = join(',',$hotel_ids_arr);
            $res_hotels = $m_hotel->getHotelByIds($hotel_ids);
            $hotels = array();
            foreach ($res_hotels as $v){
                $hotels[$v['id']] = $v;
            }
            $all_status = array(1=>'正常',2=>'禁用');
            foreach ($data_list as $k=>$v){
                $data_list[$k]['name'] = $hotels[$v['hotel_id']]['name'];
                $data_list[$k]['rname'] = $hotels[$v['rhotel_id']]['name'];
                $data_list[$k]['status_str'] = $all_status[$v['status']];
            }
        }

        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function relationadd(){
        $id = I('id',0,'intval');
        $m_hotelrelation = new \Admin\Model\HotelRelationModel();
        if(IS_POST){
            $hotel_id = I('post.hotel_id',0,'intval');
            $rhotel_id = I('post.rhotel_id',0,'intval');
            $status = I('post.status',0,'intval');
            if($hotel_id==0 || $rhotel_id==0){
                $this->output('请选择对应的酒楼', 'hotelrelation/relationadd',2,0);
            }
            if($hotel_id==$rhotel_id){
                $this->output('关联酒楼不能同为一个酒楼', 'hotelrelation/relationadd',2,0);
            }
            $where = array();
            $where_1 = array('hotel_id'=>array('in',array($hotel_id,$rhotel_id)));
            $where_2 = array('rhotel_id'=>array('in',array($hotel_id,$rhotel_id)));
            $where['_complex'] = array(
                $where_1,
                $where_2,
                '_logic' => 'or'
            );
            if($id){
                $where['id'] = array('neq',$id);
            }
            $res_data = $m_hotelrelation->getInfo($where);
            if(!empty($res_data)){
                $this->output('请勿重复添加', 'hotelrelation/relationadd',2,0);
            }

            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('hotel_id'=>$hotel_id,'rhotel_id'=>$rhotel_id,'status'=>$status,'sysuser_id'=>$sysuser_id);
            if($id){
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $m_hotelrelation->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_hotelrelation->addData($data);
            }
            if($result){
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(2);
                $cache_key = C('SMALLAPP_HOTEL_RELATION');
                if($status==1){
                    $redis->set($cache_key.$hotel_id,$rhotel_id);
                    $redis->set($cache_key.$rhotel_id,$hotel_id);
                }else{
                    $redis->remove($cache_key.$hotel_id);
                    $redis->remove($cache_key.$rhotel_id);
                }

                $this->output('操作成功!', 'hotelrelation/relationlist');
            }else{
                $this->output('操作失败', 'hotelrelation/relationlist',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_hotelrelation->getInfo(array('id'=>$id));
            }
            $where = array('flag'=>0,'state'=>1);
            $m_hotel = new \Admin\Model\HotelModel();
            $hlist = $m_hotel->getInfo('id,name,area_id',$where);
            $this->assign('hotels',$hlist);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function relationdel(){
        $id = I('get.id',0,'intval');
        $m_hotelrelation = new \Admin\Model\HotelRelationModel();
        $relation_info = $m_hotelrelation->getInfo(array('id'=>$id));

        $result = $m_hotelrelation->delData(array('id'=>$id));
        if($result){
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(2);
            $cache_key = C('SMALLAPP_HOTEL_RELATION');
            $redis->remove($cache_key.$relation_info['hotel_id']);
            $redis->remove($cache_key.$relation_info['rhotel_id']);

            $this->output('操作成功!', 'hotelrelation/relationlist',2);
        }else{
            $this->output('操作失败', 'hotelrelation/relationlist',2,0);
        }
    }

}