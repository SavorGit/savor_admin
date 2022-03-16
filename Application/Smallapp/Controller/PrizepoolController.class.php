<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class PrizepoolController extends BaseController {

    public $pool_status = array('1'=>'正常','2'=>'禁用');

    public function __construct() {
        parent::__construct();
    }

    public function datalist() {
        $status   = I('status',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status)     $where['status'] = $status;
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $m_prizepool  = new \Admin\Model\Smallapp\PrizepoolModel();
        $fields = '*';
        $result = $m_prizepool->getDataList($fields,$where, 'id desc', $start, $size);
        $datalist = $result['list'];

        $m_hotelpool = new \Admin\Model\Smallapp\HotelPrizepoolModel();
        foreach ($datalist as $k=>$v){
            $fields = "count(DISTINCT hotel_id) as num";
            $res_hotelgoods = $m_hotelpool->getRow($fields,array('prizepool_id'=>$v['id']),'id desc');
            $hotels = intval($res_hotelgoods['num']);
            $datalist[$k]['hotels'] = $hotels;
            $datalist[$k]['statusstr'] = $this->pool_status[$v['status']];
        }
        $this->assign('status',$status);
        $this->assign('keyword',$keyword);
        $this->assign('pool_status', $this->pool_status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function pooladd(){
        $id = I('id', 0, 'intval');
        $m_prizepool  = new \Admin\Model\Smallapp\PrizepoolModel();
        if(IS_GET){
            $dinfo = array('status'=>2);
            if($id){
                $dinfo = $m_prizepool->getInfo(array('id'=>$id));
            }
            $this->assign('pool_status', $this->pool_status);
            $this->assign('vinfo',$dinfo);
            $this->display('pooladd');
        }else{
            $name = I('post.name','','trim');
            $status = I('post.status',0,'intval');
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('name'=>$name,'status'=>$status,'sysuser_id'=>$sysuser_id,'add_time'=>date('Y-m-d H:i:s'));
            if($id){
                $m_prizepoolprize  = new \Admin\Model\Smallapp\PrizepoolprizeModel();
                $res_prize = $m_prizepoolprize->getDataList('count(id) as num',array('prizepool_id'=>$id,'status'=>1),'id desc');
                $prize_num = $res_prize[0]['num'];
                if($status==1 && $prize_num<3){
                    $this->output('请先配置至少3个奖品', 'syslottery/syslotteryadd',2,0);
                }

                $m_prizepool->updateData(array('id'=>$id),$data);
                $result = true;
            }else{
                $result = $m_prizepool->add($data);
            }
            if($result){
                $this->output('操作成功','prizepool/datalist');
            }else{
                $this->output('操作失败', "prizepool/pooladd",2,0);
            }
        }
    }


    public function prizelist(){
        $prizepool_id = I('prizepool_id',0,'intval');
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_prizepoolprize  = new \Admin\Model\Smallapp\PrizepoolprizeModel();
        $where = array('prizepool_id'=>$prizepool_id);
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_prizepoolprize->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            $data_list[$k]['statusstr'] = $this->pool_status[$v['status']];
            if(!empty($v['image_url'])){
                $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
            }
        }

        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('status',$status);
        $this->assign('pool_status', $this->pool_status);
        $this->assign('prizepool_id',$prizepool_id);
        $this->display();
    }

    public function prizeadd(){
        $id = I('id',0,'intval');
        $prizepool_id = I('prizepool_id',0,'intval');
        $m_prizepoolprize  = new \Admin\Model\Smallapp\PrizepoolprizeModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $money = I('post.money',0,'intval');
            $amount = I('post.amount',0,'intval');
            $type = I('post.type',0,'intval');
            $status = I('post.status',0,'intval');

            if($type==1 && empty($money)){
                $this->output('请输入中奖金额', "prizepool/prizeadd", 2, 0);
            }

            $data = array('prizepool_id'=>$prizepool_id,'name'=>$name,'money'=>$money,'type'=>$type,
                'amount'=>$amount,'status'=>$status);
            if($media_id){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            if($id){
                $m_prizepoolprize->updateData(array('id'=>$id),$data);
            }else{
                $m_prizepoolprize->add($data);
            }
            $this->output('操作成功!', 'prizepool/prizelist');
        }else{
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_prizepoolprize->getInfo(array('id'=>$id));
                $vinfo['oss_addr'] = $oss_host.$vinfo['image_url'];
                $prizepool_id = $vinfo['prizepool_id'];
            }else{
                $vinfo = array('type'=>1);
            }
            $this->assign('vinfo',$vinfo);
            $this->assign('prizepool_id',$prizepool_id);
            $this->display();
        }
    }

    public function hoteladd(){
        $prizepool_id = I('prizepool_id',0,'intval');
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','prizepool/datalist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','prizepool/datalist',2,0);
            }
            $is_succ = false;
            $m_hotelpool = new \Admin\Model\Smallapp\HotelPrizepoolModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];
                $where = array('hotel_id'=>$hotel_id,'prizepool_id'=>$prizepool_id);
                $res = $m_hotelpool->where($where)->find();
                if(empty($res)){
                    $is_succ = true;
                    $m_hotelpool->add($where);
                }
            }
            if($is_succ){
                $this->output('添加成功','prizepool/datalist');
            }else {
                $this->output('请勿重复添加到酒楼','prizepool/datalist',2,0);
            }

        }else{
            $m_prizepool  = new \Admin\Model\Smallapp\PrizepoolModel();
            $dinfo = $m_prizepool->getInfo(array('id'=>$prizepool_id));

            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->assign('prizepool_id', $prizepool_id);
            $this->display('hoteladd');
        }
    }

    public function hotelpools() {
        $prizepool_id = I('prizepool_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.prizepool_id'=>$prizepool_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_hotelpools = new \Admin\Model\Smallapp\HotelPrizepoolModel();
        $result = $m_hotelpools->getHotelprizeList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('prizepool_id',$prizepool_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotelpools');
    }

    public function hotelpoolsdel(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotel_id',0,'intval');
        $m_hotelpools = new \Admin\Model\Smallapp\HotelPrizepoolModel();
        $result = $m_hotelpools->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'prizepool/hotelpools',2);
        }else{
            $this->output('操作失败', 'prizepool/hotelpools',2,0);
        }
    }

}