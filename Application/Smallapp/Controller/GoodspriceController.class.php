<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

class GoodspriceController extends BaseController {

    public function datalist(){
        $goods_id = I('goods_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');
        $goods_name = I('goods_name','','trim');

        $m_goods_price = new \Admin\Model\Smallapp\GoodsPriceModel();
        $where = array('goods_id'=>$goods_id);
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_goods_price->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_sysuser = new \Admin\Model\UserModel();
            $m_goods_hotel = new \Admin\Model\Smallapp\GoodsPriceHotelModel();
            $all_status = C('TEMPLATE_STATUS');
            $all_types = C('TEMPLATE_TYPES');
            foreach ($res_list['list'] as $v){
                $res_uinfo = $m_sysuser->getUserInfo($v['sysuser_id']);
                $v['sys_username'] = $res_uinfo['remark'];
                $hotel_num = 0;
                if($v['type']==1){
                    $hotel_num = '全部售酒餐厅';
                }else {
                    $all_hotels = $m_goods_hotel->getAllData('COUNT(DISTINCT hotel_id) as hotel_num', array('goods_price_id'=>$v['id']), '', '');
                    if(!empty($all_hotels[0]['hotel_num'])){
                        $hotel_num = $all_hotels[0]['hotel_num'];
                    }
                }
                $v['hotel_num'] = $hotel_num;
                $v['type_str'] = $all_types[$v['type']];
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('gp_name',"商品ID：{$goods_id}，名称：{$goods_name}");
        $this->assign('data',$data_list);
        $this->assign('goods_id',$goods_id);
        $this->assign('goods_name',$goods_name);
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function priceadd(){
        $id = I('id',0,'intval');
        $goods_id = I('goods_id',0,'intval');
        $m_goods_price = new \Admin\Model\Smallapp\GoodsPriceModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $price = I('post.price',0,'intval');
            $line_price = I('post.line_price',0,'intval');
            $type = I('post.type',0,'intval');//1通用政策,2特殊政策
            $area_id = I('post.area_id',0,'intval');
            $status = I('post.status',0,'intval');

            if($type==1){
                if(empty($area_id)){
                    $this->output('请选择城市', 'goodsprice/priceadd',2,0);
                }
                $rwhere = array('type'=>$type,'area_id'=>$area_id,'goods_id'=>$goods_id,'status'=>1);
                if($id){
                    $rwhere['id'] = array('neq',$id);
                }
                $res_policy = $m_goods_price->getInfo($rwhere);
                if(!empty($res_policy)){
                    $this->output('一个城市只能有一个通用政策', 'goodsprice/priceadd',2,0);
                }
            }
            $userInfo = session('sysUserInfo');
            $add_data = array('name'=>$name,'goods_id'=>$goods_id,'price'=>$price,'line_price'=>$line_price,
                'type'=>$type,'area_id'=>$area_id,'status'=>$status,'sysuser_id'=>$userInfo['id']);
            $m_goods_hotel = new \Admin\Model\Smallapp\GoodsPriceHotelModel();
            if($id){
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $dinfo = $m_goods_price->getInfo(array('id'=>$id));
                if($type==1 && $dinfo['type']!=$type){
                    $m_goods_hotel->delData(array('goods_price_id'=>$id));
                }
            }
            if($status==1){
                $all_hotels = $m_goods_hotel->getAllData('COUNT(DISTINCT hotel_id) as hotel_num', array('goods_price_id'=>$id), '', '');
                $hotel_num = intval($all_hotels[0]['hotel_num']);
                if($hotel_num==0){
                    $this->output('状态为执行中，需发布酒楼', 'goodsprice/priceadd',2,0);
                }
            }
            if($id){
                if($status==1){
                    $res_ap_hotels = $m_goods_hotel->getAllData('hotel_id', array('goods_price_id'=>$id), '', '');
                    $all_hotel_ids = array();
                    foreach ($res_ap_hotels as $v){
                        $all_hotel_ids[]=$v['hotel_id'];
                    }
                    if(!empty($all_hotel_ids)){
                        $fields = 'a.hotel_id,hotel.name as hotel_name,gp.id as gp_id,gp.name';
                        $where = array('gp.type'=>$dinfo['type'],'gp.status'=>1);
                        $where['gp.id'] = array('neq',$id);
                        $where['a.hotel_id'] = array('in',$all_hotel_ids);
                        $res_aphotels = $m_goods_hotel->getGoodsPriceHotels($fields,$where,'a.id desc','0,1');
                        if(!empty($res_aphotels[0]['hotel_id'])){
                            $msg = "酒楼:{$res_aphotels[0]['hotel_id']}-$res_aphotels[0]['hotel_name'],已有政策:{$res_aphotels[0]['gp_id']}-{$res_aphotels[0]['name']}";
                            $this->output($msg,'goodsprice/priceadd',2,0);
                        }
                    }
                }
                $m_goods_price->updateData(array('id'=>$id),$add_data);
            }else{
                $goods_price_id = $m_goods_price->add($add_data);
                if($type==1){
                    $m_goods_hotel->add(array('goods_price_id'=>$goods_price_id,'area_id'=>$area_id,'hotel_id'=>0));
                }
            }

            $this->output('操作成功', 'goodsprice/datalist');
        }else{
            $m_area = new \Admin\Model\AreaModel();
            $city_arr = $m_area->getHotelAreaList();
            array_unshift($city_arr,array('id'=>0,'region_name'=>'全部'));
            $dinfo = array('status'=>2);
            if($id){
                $dinfo = $m_goods_price->getInfo(array('id'=>$id));
                $goods_id = $dinfo['goods_id'];
            }
            $this->assign('goods_id',$goods_id);
            $this->assign('dinfo',$dinfo);
            $this->assign('city_arr',$city_arr);
            $this->display();
        }
    }

    public function hoteladd(){
        $goods_price_id = I('goods_price_id',0,'intval');
        $m_goods_price = new \Admin\Model\Smallapp\GoodsPriceModel();
        $dinfo = $m_goods_price->getInfo(array('id'=>$goods_price_id));
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','goodsprice/hoteladd',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            $hotel_ids = array();
            foreach ($hotel_arr as $hv){
                $hotel_id = intval($hv['hotel_id']);
                if($hotel_id>0){
                    $hotel_ids[]=$hotel_id;
                }
            }
            if(empty($hotel_ids)){
                $this->output('请选择酒楼','goodsprice/hoteladd',2,0);
            }
            $m_goods_price_hotel = new \Admin\Model\Smallapp\GoodsPriceHotelModel();
            $fields = 'a.hotel_id,hotel.name as hotel_name,gp.id as gp_id,gp.name';
            $where = array('gp.type'=>$dinfo['type'],'gp.status'=>1);
            $where['gp.id'] = array('neq',$goods_price_id);
            $where['a.hotel_id'] = array('in',$hotel_ids);
            $res_aphotels = $m_goods_price_hotel->getGoodsPriceHotels($fields,$where,'a.id desc','0,1');
            if(!empty($res_aphotels[0]['hotel_id'])){
                $msg = "酒楼:{$res_aphotels[0]['hotel_id']}-$res_aphotels[0]['hotel_name'],已有政策:{$res_aphotels[0]['gp_id']}-{$res_aphotels[0]['name']}";
                $this->output($msg,'goodsprice/datalist',2,0);
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $hotel_data = array();
            foreach ($hotel_ids as $hv){
                $hotel_id = intval($hv);
                $res_price_hotel = $m_goods_price_hotel->getInfo(array('goods_price_id'=>$goods_price_id,'hotel_id'=>$hotel_id));
                if(!empty($res_price_hotel)){
                    continue;
                }
                $res_hotel = $m_hotel->getRow('area_id',array('id'=>$hotel_id));
                $hotel_data[]=array('goods_price_id'=>$goods_price_id,'hotel_id'=>$hotel_id,'area_id'=>$res_hotel['area_id']);
            }
            if(!empty($hotel_data)){
                $m_goods_price_hotel->addAll($hotel_data);
            }
            $this->output('添加成功','goodsprice/datalist');
        }else{
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getHotelAreaList();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->display('hoteladd');
        }
    }

    public function hotellist() {
        $goods_price_id = I('goods_price_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.goods_price_id'=>$goods_price_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_goods_price_hotel = new \Admin\Model\Smallapp\GoodsPriceHotelModel();
        $result = $m_goods_price_hotel->getHotelDatas($fields,$where,'a.hotel_id desc', 'a.hotel_id',$start,$size);
        $datalist = $result['list'];

        $this->assign('goods_price_id',$goods_price_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');
        $m_goods_price_hotel = new \Admin\Model\Smallapp\GoodsPriceHotelModel();
        $result = $m_goods_price_hotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'goodsprice/hotellist',2);
        }else{
            $this->output('操作失败', 'goodsprice/hotellist',2,0);
        }
    }


    public function getSaleHotel() {
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name', '','trim');
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $field = 'hotel.id as hid,hotel.name as hname';
        $result = $m_hotel->getHotelDatas($field,$where,'hotel.pinyin asc');
        $res = array('code'=>1,'msg'=>'','data'=>$result);
        echo json_encode($res);
    }


}