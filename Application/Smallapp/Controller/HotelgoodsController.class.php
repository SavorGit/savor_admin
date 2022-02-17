<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 酒楼商品管理
 *
 */
class HotelgoodsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function goodslist() {
        $status   = I('status',0,'intval');
        $type   = I('type',0,'intval');
        $flag   = I('flag',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $goods_types = C('DISH_TYPE');
        unset($goods_types[21],$goods_types[22],$goods_types[23],$goods_types[42]);
        if($type){
            $where['type'] = $type;
        }else{
            $where['type'] = array('in',array(40,43));
        }

        if($status)     $where['status'] = $status;
        if($flag)       $where['flag'] = $flag;
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $fields = '*';
        $result = $m_goods->getDataList($fields,$where, 'id desc', $start, $size);
        $datalist = $result['list'];

        $goods_status = C('DISH_STATUS');
        $goods_flag = C('DISH_FLAG');
        $oss_host = get_oss_host();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        foreach ($datalist as $k=>$v){
            $cover_imgsinfo = explode(',',$v['cover_imgs']);
            $image = '';
            if(!empty($cover_imgsinfo)){
                $image = $oss_host.$cover_imgsinfo[0];
            }
            if(isset($goods_flag[$v['flag']])){
                $flagstr = $goods_flag[$v['flag']];
            }else{
                $flagstr = '';
            }
            $is_seckill_str = '否';
            if($v['is_seckill']==1){
                $is_seckill_str = '是';
            }

            $fields = "count(DISTINCT hotel_id) as num";
            $res_hotelgoods = $m_hotelgoods->getRow($fields,array('goods_id'=>$v['id'],'openid'=>'','type'=>1),'id desc');
            $hotels = intval($res_hotelgoods['num']);
            $datalist[$k]['hotels'] = $hotels;
            $datalist[$k]['typestr']=$goods_types[$v['type']];
            $datalist[$k]['flagstr'] = $flagstr;
            $datalist[$k]['image'] = $image;
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
            $datalist[$k]['is_seckill_str'] = $is_seckill_str;
        }
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('flag',$flag);
        $this->assign('keyword',$keyword);
        $this->assign('goods_types', $goods_types);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function goodsadd(){
        $id = I('id', 0, 'intval');
        $type = I('type', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        if(IS_GET){
            $detail_img_num = $cover_img_num = 6;
            $roll_num = 3;
            $detailaddr = $coveraddr = $now_roll_content = array();
            $is_seckill = 0;
            if($type==40){
                $is_seckill = 1;
            }
            $dinfo = array('type'=>$type,'amount'=>1,'tvmedia_type'=>0,'is_seckill'=>$is_seckill,'sort'=>1);
            $goods_types = C('DISH_TYPE');
            if($id){
                $m_media = new \Admin\Model\MediaModel();
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                $type = $dinfo['type'];
                $tv_oss_addr = $model_oss_addr = $poster_oss_addr = '';

                if(!empty($dinfo['tv_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['tv_media_id']);
                    $tv_oss_addr = $res_media['oss_addr'];
                    $dinfo['tvmedia_type'] = $res_media['type'];
                }else{
                    $dinfo['tvmedia_type'] = 1;
                }
                $dinfo['tv_oss_addr'] = $tv_oss_addr;
                if(!empty($dinfo['model_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['model_media_id']);
                    $model_oss_addr = $res_media['oss_addr'];
                }
                $dinfo['model_oss_addr'] = $model_oss_addr;
                if(!empty($dinfo['poster_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['poster_media_id']);
                    $model_oss_addr = $res_media['oss_addr'];
                }
                $dinfo['poster_oss_addr'] = $model_oss_addr;

                if($dinfo['amount']==0){
                    $dinfo['amount'] = 1;
                }
                $oss_host = get_oss_host();
                if($dinfo['detail_imgs']){
                    $detail_imgs = explode(',',$dinfo['detail_imgs']);
                    foreach ($detail_imgs as $k=>$v){
                        if(!empty($v)){
                            $detailaddr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                        }
                    }
                }
                if($dinfo['cover_imgs']){
                    $cover_imgs = explode(',',$dinfo['cover_imgs']);
                    foreach ($cover_imgs as $k=>$v){
                        if(!empty($v)){
                            $coveraddr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                        }
                    }
                }
                if($dinfo['roll_content']){
                    $d_roll_content = json_decode($dinfo['roll_content'],true);
                    foreach ($d_roll_content as $k=>$v){
                        if(!empty($v)){
                            $now_roll_content[$k+1] = $v;
                        }
                    }
                }
                if($dinfo['start_time']=='0000-00-00 00:00:00'){
                    $dinfo['start_time'] = '';
                }
                if($dinfo['end_time']=='0000-00-00 00:00:00'){
                    $dinfo['end_time'] = '';
                }
            }
            foreach ($goods_types as $k=>$v){
                if($k!=$type){
                    unset($goods_types[$k]);
                }
            }
            $detail_imgs = array();
            for($i=1;$i<=$detail_img_num;$i++){
                $img_info = array('id'=>$i,'imgid'=>'detail_id'.$i,'media_id'=>0);
                if(isset($detailaddr[$i])){
                    $img_info['media_id'] = $detailaddr[$i]['media_id'];
                    $img_info['oss_addr'] = $detailaddr[$i]['oss_addr'];
                }
                $detail_imgs[] = $img_info;
            }
            $cover_imgs = array();
            for($i=1;$i<=$cover_img_num;$i++){
                $img_info = array('id'=>$i,'imgid'=>'cover_id'.$i,'media_id'=>0);
                if(isset($coveraddr[$i])){
                    $img_info['media_id'] = $coveraddr[$i]['media_id'];
                    $img_info['oss_addr'] = $coveraddr[$i]['oss_addr'];
                }
                $cover_imgs[] = $img_info;
            }
            $roll_contents = array();
            for($i=1;$i<=$roll_num;$i++){
                $info = array('id'=>$i,'content'=>'');
                if(isset($now_roll_content[$i])){
                    $info['content'] = $now_roll_content[$i];
                }
                $roll_contents[] = $info;
            }
            $this->assign('roll_contents',$roll_contents);
            $this->assign('cover_imgs',$cover_imgs);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('goods_types',$goods_types);
            $this->assign('vinfo',$dinfo);
            $this->display('goodsadd');
        }else{
            $name = I('post.name','','trim');
            $covermedia_id = I('post.covermedia_id','');
            $detailmedia_id = I('post.detailmedia_id','');
            $video_intromedia_id = I('post.media_vid',0,'intval');
            $intro = I('post.intro','');
            $notice = I('post.notice','','trim');
            $price = I('post.price',0);
            $amount = I('post.amount',0,'intval');
            $line_price = I('post.line_price',0);
            $is_seckill = I('post.is_seckill',0);
            $type = I('post.type',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');
            $tv_media_id = I('post.tv_media_id',0,'intval');
            $tv_media_vid = I('post.tv_media_vid',0,'intval');
            $model_media_id = I('post.model_media_id',0,'intval');
            $postermedia_id = I('post.postermedia_id',0,'intval');
            $start_time = I('post.start_time','');
            $end_time = I('post.end_time','');
            $roll_content = I('post.roll_content','');
            if($tv_media_vid>0){
                $tv_media_id = $tv_media_vid;
            }elseif($tv_media_id>0){
                $tv_media_id = $tv_media_id;
            }else{
                $tv_media_id = 0;
            }

            if($line_price && $line_price<$price){
                $this->output('划线价必须大于零售价', "hotelgoods/goodsadd", 2, 0);
            }
            if(!$price){
                $this->output('建议零售价不能为空', "hotelgoods/goodsadd", 2, 0);
            }
            $where = array('name'=>$name);
            if($id){
                $where['id']= array('neq',$id);
                $res_goods = $m_goods->getInfo($where);
            }else{
                $res_goods = $m_goods->getInfo($where);
            }
            if(!empty($res_goods)){
                $this->output('名称不能重复', "hotelgoods/goodsadd", 2, 0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            if(empty($price))   $price = 0;
            if(empty($supply_price))   $supply_price = 0;
            if(empty($line_price))   $line_price = 0;
            $data = array('name'=>$name,'video_intromedia_id'=>$video_intromedia_id,'intro'=>$intro,'notice'=>$notice,'price'=>$price,
                'distribution_profit'=>0,'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,'is_seckill'=>$is_seckill,
                'poster_media_id'=>$postermedia_id,'tv_media_id'=>$tv_media_id,'model_media_id'=>$model_media_id,'type'=>$type,
                'sort'=>$sort,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
            $data['status'] = $status;
            if(!empty($start_time)){
                $data['start_time'] = $start_time;
            }
            if(!empty($end_time)){
                $data['end_time'] = $end_time;
            }
            if(!empty($roll_content)){
                $now_roll_content = array();
                foreach ($roll_content as $v){
                    $rv = trim($v);
                    if(!empty($rv)){
                        $now_roll_content[]=$rv;
                    }
                }
                $data['roll_content'] = json_encode($now_roll_content);
            }else{
                $data['roll_content'] = '';
            }

            if($status==1){
                $flag = 2;
            }else{
                $flag = 3;
            }
            $data['flag'] = $flag;
            $m_media = new \Admin\Model\MediaModel();
            $cover_imgs = array();
            if(!empty($covermedia_id)){
                foreach ($covermedia_id as $v){
                    if(!empty($v)){
                        if(is_numeric($v)){
                            $res_m = $m_media->getMediaInfoById($v);
                            $img = $res_m['oss_path'];
                        }else{
                            $img = $v;
                        }
                        $cover_imgs[]=$img;
                    }
                }
            }
            if(!empty($cover_imgs)){
                $data['cover_imgs'] = join(',',$cover_imgs);
            }else{
                $data['cover_imgs'] = '';
            }
            $detail_imgs = array();
            if(!empty($detailmedia_id)){
                foreach ($detailmedia_id as $v){
                    if(!empty($v)){
                        if(is_numeric($v)){
                            $res_m = $m_media->getMediaInfoById($v);
                            $img = $res_m['oss_path'];
                        }else{
                            $img = $v;
                        }
                        $detail_imgs[]=$img;
                    }
                }
            }
            if(!empty($detail_imgs)){
                $data['detail_imgs'] = join(',',$detail_imgs);
            }else{
                $data['detail_imgs'] = '';
            }
            if($id){
                $m_goods->updateData(array('id'=>$id),$data);
                $result = true;
            }else{
                $result = $m_goods->add($data);
            }
            if($result){
                $this->output('操作成功','hotelgoods/goodslist');
            }else{
                $this->output('操作失败', "hotelgoods/goodsadd",2,0);
            }
        }
    }

    public function hoteladd(){
        $goods_id = I('goods_id',0,'intval');
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','hotelgoods/goodslist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','hotelgoods/goodslist',2,0);
            }
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $goods_program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');
            $is_succ = false;
            $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];
                $where = array('hotel_id'=>$hotel_id,'goods_id'=>$goods_id,'openid'=>'','type'=>1);
                $res = $m_hotelgoods->where($where)->find();
                if(empty($res)){
                    $is_succ = true;
                    $m_hotelgoods->add($where);

                    $program_key = $goods_program_key.":$hotel_id";
                    $period = getMillisecond();
                    $period_data = array('period'=>$period);
                    $redis->set($program_key,json_encode($period_data));
                }
            }
            if($is_succ){
                $this->output('添加成功','hotelgoods/goodslist');
            }else {
                $this->output('请勿重复添加到酒楼','hotelgoods/goodslist',2,0);
            }

        }else{
            $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();;
            $dinfo = $m_goods->getInfo(array('id'=>$goods_id));
            $m_media = new \Admin\Model\MediaModel();
            $media_info = $m_media->getMediaInfoById($dinfo['media_id']);
            $dinfo['oss_addr'] = $media_info['oss_addr'];
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->display('hoteladd');
        }
    }

    public function hotelgoodslist() {
        $goods_id = I('goods_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.goods_id'=>$goods_id,'a.type'=>1,'a.openid'=>'');
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $result = $m_hotelgoods->getHotelgoodsList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('goods_id',$goods_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotelgoodslist');
    }

    public function hotelgoodsdel(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotel_id',0,'intval');
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $result = $m_hotelgoods->delData(array('id'=>$id));
        if($result){
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $goods_program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');
            $program_key = $goods_program_key.":$hotel_id";
            $period = getMillisecond();
            $period_data = array('period'=>$period);
            $redis->set($program_key,json_encode($period_data));

            $this->output('操作成功!', 'hotelgoods/hotelgoodslist',2);
        }else{
            $this->output('操作失败', 'hotelgoods/hotelgoodslist',2,0);
        }
    }

    public function changestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');
        if($status==1){
            $flag = 2;
        }else{
            $flag = 3;
        }
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $result = $m_goods->updateData(array('id'=>$id),array('status'=>$status,'flag'=>$flag));
        if($result){
            if($id>0){
                $upwhere = array('parent_id'=>$id);
                $upwhere['status'] = array('in',array(1,2));

                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $data = array('status'=>$status,'flag'=>$flag,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
                $m_goods->updateData($upwhere,$data);
            }
            $this->output('操作成功!', 'dishgoods/goodslist',2);
        }else{
            $this->output('操作失败', 'dishgoods/goodslist',2,0);
        }
    }
}