<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 活动
 *
 */
class GoodsstockController extends BaseController {


    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');

        $where = array('type'=>41);
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $fields = '*';
        $orderby = 'id desc';
        $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
        $res_list = $m_dishgoods->getDataList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        if(!empty($data_list)){
            $oss_host = 'http://'.C('OSS_HOST_NEW');
            foreach ($data_list as $k=>$v){
                $data_list[$k]['image_url'] = $oss_host.'/'.$v['cover_imgs'];
                $data_list[$k]['status_str'] = $all_status[$v['status']];
            }
        }

        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addgoods(){
        $id = I('id',0,'intval');
        $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
        $m_media = new \Admin\Model\MediaModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $price = I('post.price','','trim');
            $video_intromedia_id = I('post.media_vid',0,'intval');
            $tv_media_id = I('post.tv_media_id',0,'intval');
            $covermedia_id = I('post.covermedia_id',0,'intval');
            $detailmedia_id = I('post.detailmedia_id',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'price'=>$price,'video_intromedia_id'=>$video_intromedia_id,'tv_media_id'=>$tv_media_id,
                'status'=>$status,'type'=>41);
            if($covermedia_id){
                $cover_info = $m_media->getMediaInfoById($covermedia_id);
                $data['cover_imgs'] = $cover_info['oss_path'];
            }
            if($detailmedia_id){
                $detail_info = $m_media->getMediaInfoById($detailmedia_id);
                $data['detail_imgs'] = $detail_info['oss_path'];
            }
            if($id){
                $vinfo = $m_dishgoods->getInfo(array('id'=>$id));
                if($vinfo['status']!=$data['status']){
                    $m_taskgoods = new \Admin\Model\Integral\TaskHotelModel();
                    $twhere = array('task.goods_id'=>$id,'task.task_type'=>22,'task.status'=>1,'task.flag'=>1);
                    $res_hotelgoods = $m_taskgoods->getHotelTaskGoodsList('a.hotel_id',$twhere,'a.id asc');
                    if(!empty($res_hotelgoods)){
                        $redis = \Common\Lib\SavorRedis::getInstance();
                        $redis->select(14);
                        $goods_program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');
                        foreach ($res_hotelgoods as $v){
                            $program_key = $goods_program_key.":{$v['hotel_id']}";
                            $period = getMillisecond();
                            $period_data = array('period'=>$period);
                            $redis->set($program_key,json_encode($period_data));
                        }
                    }
                }

                $m_dishgoods->updateData(array('id'=>$id),$data);
            }else{
                $m_dishgoods->add($data);
            }
            $this->output('操作成功', "goodsstock/datalist");
        }else{
            $oss_host = get_oss_host();
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_dishgoods->getInfo(array('id'=>$id));
                $res_media = $m_media->getMediaInfoById($vinfo['video_intromedia_id']);
                $vinfo['video_intromedia_name'] = $res_media['name'];
                $res_media = $m_media->getMediaInfoById($vinfo['tv_media_id']);
                $vinfo['tv_oss_addr'] = $res_media['oss_addr'];
                $vinfo['coveross_addr'] = $oss_host.'/'.$vinfo['cover_imgs'];
                $vinfo['detailoss_addr'] = $oss_host.'/'.$vinfo['detail_imgs'];
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addstock(){
        $goods_id = I('goods_id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');
        $id = I('id',0,'intval');
        $m_stock = new \Admin\Model\Smallapp\GoodsstockModel();
        if(IS_POST){
            $amount = I('post.amount',0,'intval');
            $drink_copies = I('post.drink_copies',0,'intval');
            $data = array('goods_id'=>$goods_id,'hotel_id'=>$hotel_id,'amount'=>$amount,'drink_copies'=>$drink_copies);
            if($id){
                $m_stock->updateData(array('id'=>$id),$data);
            }else{
                $res_stock = $m_stock->getInfo(array('goods_id'=>$goods_id,'hotel_id'=>$hotel_id));
                if(!empty($res_stock)){
                    $this->output('请勿重复添加', "goodsstock/datalist",2,0);
                }
                $m_stock->add($data);
            }
            $this->output('操作成功', "goodsstock/stocklist");
        }else{
            $vinfo = array();
            if($id){
                $vinfo = $m_stock->getInfo(array('id'=>$id));
                $goods_id = $vinfo['goods_id'];
                $hotel_id = $vinfo['hotel_id'];
            }
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('a.status'=>1,'hotel.state'=>1,'hotel.flag'=>0);
            $fields = 'hotel.id as hotel_id,hotel.name';
            $merchants = $m_merchant->getMerchants($fields,$where,'a.id desc');
            foreach ($merchants as $k=>$v){
                if($hotel_id == $v['hotel_id']){
                    $merchants[$k]['is_select'] = 'selected';
                }else{
                    $merchants[$k]['is_select'] = '';
                }
            }

            $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
            $res_goods = $m_dishgoods->getInfo(array('id'=>$goods_id));
            $vinfo['goods_id']=$goods_id;
            $vinfo['goods_name'] = $res_goods['name'];

            $this->assign('vinfo',$vinfo);
            $this->assign('merchants',$merchants);
            $this->display();
        }
    }

    public function stocklist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $goods_id = I('goods_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $where = array();
        if($goods_id){
            $where['a.goods_id'] = $goods_id;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.amount,a.drink_copies,a.consume_amount,a.consume_drink_copies,hotel.name as hotel_name,goods.name as goods_name';
        $orderby = 'a.id desc';
        $m_goodsstock = new \Admin\Model\Smallapp\GoodsstockModel();
        $res_list = $m_goodsstock->getStockList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];

        $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
        $all_goods = $m_dishgoods->getDataList('id,name',array('type'=>41,'status'=>1),'id desc');

        $this->assign('all_goods',$all_goods);
        $this->assign('goods_id',$goods_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}