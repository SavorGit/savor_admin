<?php
namespace Admin\Controller;

class StoresalesortController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $start  = ($page-1) * $size;
        $where = array();
        $m_storesalesort= new \Admin\Model\StoresaleSortModel();
        $result = $m_storesalesort->getDataList('*',$where,'id desc',$start,$size);
        $datalist = $result['list'];
        if(!empty($datalist)){
            $all_status = C('DATA_STATUS');
            $m_sort_hotel = new \Admin\Model\StoresaleSortHotelModel();
            foreach ($datalist as $k=>$v){
                $v['status_str'] = $all_status[$v['status']];
                $hotels = $m_sort_hotel->getDataCount(array('storesale_sort_id'=>$v['id']));
                $v['hotels'] = $hotels;
                $datalist[$k] = $v;
            }
        }

        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function addsort(){
        $id = I('id',0,'intval');
        $m_salesort = new \Admin\Model\StoresaleSortModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $item_ids = I('post.item_ids');
            $goods_ids = I('post.goods_ids');
            $status = I('post.status',1,'intval');

            $all_goods = array();
            $content = array();
            foreach ($item_ids as $k=>$v){
                $now_goods_id = $goods_ids[$k];
                if(!empty($now_goods_id)){
                    $content[$v]=$now_goods_id;
                    if(in_array($now_goods_id,$all_goods)){
                        $this->output('排序商品不能重复选择','storesalesort/addsort',2,0);
                    }else{
                        $all_goods[]=$now_goods_id;
                    }
                }
            }
            if(empty($content)){
                $this->output('请选择需要排序的商品','storesalesort/addsort',2,0);
            }
            $userInfo = session('sysUserInfo');
            $sysuser_id = $userInfo['id'];
            $add_data = array('name'=>$name,'content'=>json_encode($content),'status'=>$status,'sysuser_id'=>$sysuser_id);
            if($id){
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $m_salesort->updateData(array('id'=>$id),$add_data);
            }else{
                $m_salesort->add($add_data);
            }
            $this->output('添加成功','storesalesort/datalist');
        }else{
            $content = array();
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_salesort->getInfo(array('id'=>$id));
                $content = json_decode($vinfo['content'],true);
            }
            $m_finance_goods = new \Admin\Model\FinanceGoodsModel();
            $goods = $m_finance_goods->getDataList('id,name',array('status'=>1),'id desc');

            $goods_num = count($goods);
            $all_sort = array();
            for ($i=1;$i<=$goods_num;$i++){
                $name = '顺序'.$i;
                $goods_id = 0;
                if(isset($content[$i])){
                    $goods_id = $content[$i];
                }
                $all_sort[]=array('id'=>$i,'name'=>$name,'goods_id'=>$goods_id);
            }
            $this->assign('vinfo',$vinfo);
            $this->assign('goods',$goods);
            $this->assign('all_sort',$all_sort);
            $this->display();
        }
    }

    public function delsort(){
        $id = I('get.id',0,'intval');
        $m_salesort = new \Admin\Model\StoresaleSortModel();
        $result = $m_salesort->delData(array('id'=>$id));
        if($result){
            $m_sorthotel = new \Admin\Model\StoresaleSortHotelModel();
            $m_sorthotel->delData(array('storesale_sort_id'=>$id));

            $this->output('操作成功!', 'storesalesort/datalist',2);
        }else{
            $this->output('操作失败', 'storesalesort/datalist',2,0);
        }
    }

    public function hoteldatalist() {
        $sortid = I('sortid',0,'intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $keyword = I('keyword','','trim');

        $m_sort_hotel = new \Admin\Model\StoresaleSortHotelModel();
        $field = 'sorthotel.id,sorthotel.add_time,hotel.id as hotel_id,hotel.name as hotel_name';
        $where = array('sorthotel.storesale_sort_id'=>$sortid);
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        $start = ($page-1)*$size;
        $result = $m_sort_hotel->getList($field,$where,'id asc',$start,$size);
        $datalist = $result['list'];

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('sortid', $sortid);
        $this->assign('datalist', $datalist);
        $this->assign('keyword', $keyword);
        $this->assign('page',  $result['page']);
        $this->display();
    }

    public function hoteladd(){
        $sortid = I('sortid',0,'intval');
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','storesalesort/hoteladd',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','storesalesort/hoteladd',2,0);
            }
            $m_sort_hotel = new \Admin\Model\StoresaleSortHotelModel();
            $data_hotel = array();
            $tmp_hb = array();
            foreach ($hotel_arr as $k=>$v) {
                $hotel_id = $v['hotel_id'];
                if(array_key_exists($hotel_id, $tmp_hb)){
                    continue;
                }
                $tmp_hb[$hotel_id] = 1;
                $data_hotel[] = array('hotel_id'=>$hotel_id,'storesale_sort_id'=>$sortid);
            }
            $res = $m_sort_hotel->addAll($data_hotel);
            if($res){
                $this->output('添加成功','storesalesort/datalist');
            }else {
                $this->output('添加失败','storesalesort/datalist',2,0);
            }
        }else{
            $m_storesalesort = new \Admin\Model\StoresaleSortModel();
            $dinfo = $m_storesalesort->getInfo(array('id'=>$sortid));

            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->assign('sortid', $sortid);
            $this->display('hoteladd');
        }
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotel_id',0,'intval');
        $m_sorthotel = new \Admin\Model\StoresaleSortHotelModel();
        $result = $m_sorthotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'storesalesort/hoteldatalist',2);
        }else{
            $this->output('操作失败', 'storesalesort/hoteldatalist',2,0);
        }
    }

}
