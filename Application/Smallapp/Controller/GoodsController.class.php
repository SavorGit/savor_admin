<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
use Common\Lib\Curl;

/**
 * @desc 商品管理
 *
 */
class GoodsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function goodslist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
    	$keyword = I('keyword','','trim');
    	$type = I('type',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $status   = I('status',0,'intval');

        $where = array('type'=>20);
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($status){
            $where['status'] = $status;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        $result = $m_goods->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        $goods_types = C('GOODS_TYPE');
        unset($goods_types['10']);

        $goods_status = C('GOODS_STATUS');
        $m_media = new \Admin\Model\MediaModel();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $m_hotels = new \Admin\Model\HotelModel();
        foreach ($datalist as $k=>$v){
            $media_info = $m_media->getMediaInfoById($v['media_id']);
            if($media_info['type']==1){
                $media_typestr = '视频';
            }else{
                $media_typestr = '图片';
            }
            $datalist[$k]['media_typestr'] = $media_typestr;
            $datalist[$k]['typestr'] = $goods_types[$v['type']];
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];

            if($v['type']==20){
                $res_hotelgoods = $m_hotelgoods->getInfo(array('goods_id'=>$v['id']));
                $res_hotel = $m_hotels->getOne($res_hotelgoods['hotel_id']);
                $hotels = $res_hotel['name'];
            }else{
                $fields = "count(DISTINCT hotel_id) as num";
                $res_hotelgoods = $m_hotelgoods->getRow($fields,array('goods_id'=>$v['id'],'openid'=>'','type'=>1),'id desc');
                $hotels = intval($res_hotelgoods['num']);
            }
            $datalist[$k]['hotels'] = $hotels;
        }

        $this->assign('status',$status);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type',$type);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('goods_types',$goods_types);
        $this->display('goodslist');
    }

    public function optimizegoodslist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $status   = I('status',0,'intval');

        $where = array('type'=>10);
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($status){
            $where['status'] = $status;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_category  = new \Admin\Model\Smallapp\GoodsModel();
        $result = $m_category->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        $goods_types = C('GOODS_TYPE');
        $goods_status = C('GOODS_STATUS');
        $m_media = new \Admin\Model\MediaModel();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        foreach ($datalist as $k=>$v){
            $media_info = $m_media->getMediaInfoById($v['media_id']);
            if($media_info['type']==1){
                $media_typestr = '视频';
            }else{
                $media_typestr = '图片';
            }
            $datalist[$k]['media_typestr'] = $media_typestr;
            $datalist[$k]['typestr'] = $goods_types[$v['type']];
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];

            $fields = "count(DISTINCT hotel_id) as num";
            $res_hotelgoods = $m_hotelgoods->getRow($fields,array('goods_id'=>$v['id'],'openid'=>'','type'=>1),'id desc');
            $datalist[$k]['hotels'] = intval($res_hotelgoods['num']);
        }

        $this->assign('status',$status);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('optimizegoodslist');
    }

    public function seckillgoodslist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $status   = I('status',0,'intval');

        $where = array('type'=>40);
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($status){
            $where['status'] = $status;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_category  = new \Admin\Model\Smallapp\GoodsModel();
        $result = $m_category->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        $goods_types = C('GOODS_TYPE');
        $goods_status = C('GOODS_STATUS');
        $m_media = new \Admin\Model\MediaModel();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        foreach ($datalist as $k=>$v){
            $media_info = $m_media->getMediaInfoById($v['media_id']);
            if($media_info['type']==1){
                $media_typestr = '视频';
            }else{
                $media_typestr = '图片';
            }
            $datalist[$k]['media_typestr'] = $media_typestr;
            $datalist[$k]['typestr'] = $goods_types[$v['type']];
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];

            $fields = "count(DISTINCT hotel_id) as num";
            $res_hotelgoods = $m_hotelgoods->getRow($fields,array('goods_id'=>$v['id'],'openid'=>'','type'=>1),'id desc');
            $datalist[$k]['hotels'] = intval($res_hotelgoods['num']);
        }

        $this->assign('status',$status);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('seckillgoodslist');
    }

    public function seckillgoodsadd(){
        $template_html = 'seckillgoodsadd';
        $goods_list_f = 'seckillgoodslist';
        $this->handle_goodsadd($template_html,$goods_list_f);
    }

    public function goodsadd(){
        $template_html = 'goodsadd';
        $goods_list_f = 'goodslist';
        $this->handle_goodsadd($template_html,$goods_list_f);
    }

    public function optimizegoodsadd(){
        $template_html = 'optimizegoodsadd';
        $goods_list_f = 'optimizegoodslist';
        $this->handle_goodsadd($template_html,$goods_list_f);
    }

    public function withdrawgoodslist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('type'=>30);
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        $result = $m_goods->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        $goods_types = C('GOODS_TYPE');
        $goods_status = C('GOODS_STATUS');
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $user_ids = array();
        foreach ($datalist as $k=>$v){
            $user_ids[] = $v['sysuser_id'];
            $datalist[$k]['typestr'] = $goods_types[$v['type']];
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
            if($v['is_audit']){
                $datalist[$k]['is_audit_str'] = '需审核';
            }else{
                $datalist[$k]['is_audit_str'] = '无需审核';
            }
            $fields = "count(DISTINCT hotel_id) as num";
            $res_hotelgoods = $m_hotelgoods->getRow($fields,array('goods_id'=>$v['id'],'openid'=>'','type'=>1),'id desc');
            $datalist[$k]['hotels'] = intval($res_hotelgoods['num']);
        }
        $user_ids = array_unique($user_ids);
        $m_sysuser = new \Admin\Model\UserModel();
        $where = array('id'=>array('in',join(',',$user_ids)));
        $res_user = $m_sysuser->where($where)->order('id desc')->select();
        $user = array();
        foreach ($res_user as $v){
            $user[$v['id']] = $v['remark'];
        }
        foreach ($datalist as $k=>$v){
            $sysuser_id = $v['sysuser_id'];
            $datalist[$k]['creater'] = $user[$sysuser_id];
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('withdrawgoodslist');
    }

    public function withdrawgoodsadd(){
        $id = I('id', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        if(IS_GET){
            $dinfo = array();
            if($id){
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                $dinfo['start_date'] = date('Y-m-d',strtotime($dinfo['start_time']));
                $dinfo['end_date'] = date('Y-m-d',strtotime($dinfo['end_time']));
            }
            $this->assign('vinfo',$dinfo);
            $this->display('withdrawgoodsadd');
        }else{
            $price = I('post.price',0);
            $rebate_integral = I('post.rebate_integral',0,'intval');
            $start_date = I('post.start_date','');
            $end_date = I('post.end_date','');
            $status = I('post.status',1,'intval');
            $is_audit = I('post.is_audit',0,'intval');
            if(empty($price)){
                $this->output('金额不能为空', "goods/withdrawgoodsadd", 2, 0);
            }
            if(intval($price)!=$price){
                $this->output('请输入整数金额', "goods/withdrawgoodsadd", 2, 0);
            }
            $name = intval($price).'元';
            $type = 30;
            $where = array('name'=>$name,'type'=>$type);
            if($id){
                $where['id']= array('neq',$id);
                $res_goods = $m_goods->getInfo($where);
            }else{
                $res_goods = $m_goods->getInfo($where);
            }
            if($res_goods!==null || !empty($res_goods)){
                $this->output('价格不能重复', "goods/withdrawgoodsadd", 2, 0);
            }
            $data = array('type'=>$type,'name'=>$name,'price'=>$price,'rebate_integral'=>$rebate_integral,'is_audit'=>$is_audit,'status'=>$status);
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', "goods/withdrawgoodsadd", 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $data['start_time'] = $start_time;
            $data['end_time'] = $end_time;
            if($id){
                $m_goods->updateData(array('id'=>$id),$data);
                $result = true;
            }else{
                $result = $m_goods->add($data);
            }

            if($result){
                $this->output('操作成功', "goods/withdrawgoodslist");
            }else{
                $this->output('操作失败', "goods/withdrawgoods",2,0);
            }

        }
    }


    private function handle_goodsadd($template_html,$goods_list_f){
        $id = I('id', 0, 'intval');
        $type = I('type',0,'intval');
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        if(IS_GET){
            $detail_img_num = 5;
            $cover_img_num = 3;
            $dinfo = array('media_type'=>1);
            $detailaddr = array();
            $coveraddr = array();
            if($id){
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                $media_info = $m_media->getMediaInfoById($dinfo['media_id']);
                if($dinfo['detail_imgmedia_ids']){
                    $detail_imgmedia_ids = json_decode($dinfo['detail_imgmedia_ids'],true);
                    foreach ($detail_imgmedia_ids as $k=>$v){
                        $imgmedia_info = $m_media->getMediaInfoById($v);
                        $detailaddr[$k] = array('media_id'=>$v,'oss_addr'=>$imgmedia_info['oss_addr']);
                    }
                }
                if($dinfo['cover_imgmedia_ids']){
                    $cover_imgmedia_ids = json_decode($dinfo['cover_imgmedia_ids'],true);
                    foreach ($cover_imgmedia_ids as $k=>$v){
                        $imgmedia_info = $m_media->getMediaInfoById($v);
                        $coveraddr[$k] = array('media_id'=>$v,'oss_addr'=>$imgmedia_info['oss_addr']);
                    }
                }
                if(!empty($dinfo['label'])){
                    $labels = json_decode($dinfo['label'],true);
                    $dinfo['label1'] = $labels[0];
                    $dinfo['label2'] = $labels[1];
                    $dinfo['label3'] = $labels[2];
                }

                $dinfo['oss_addr'] = $media_info['oss_addr'];
                $dinfo['media_type'] = $media_info['type'];
                $dinfo['start_date'] = date('Y-m-d',strtotime($dinfo['start_time']));
                $dinfo['end_date'] = date('Y-m-d',strtotime($dinfo['end_time']));
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
            $goods_types = C('GOODS_TYPE');
            if($type){
                $dinfo['type'] = $type;
                $goods_types = array($type=>$goods_types[$type]);
            }else{
                unset($goods_types[10]);
            }
            $this->assign('goods_types',$goods_types);
            $this->assign('cover_imgs',$cover_imgs);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('vinfo',$dinfo);
            $this->display($template_html);
        }else{
            $type = I('post.type',0,'intval');
            $name = I('post.name','','trim');
            $wx_category = I('post.wx_category','','trim');
            $price = I('post.price',0,'intval');
            $rebate_integral = I('post.rebate_integral',0,'intval');
            $jd_url = I('post.jd_url','','trim');
            $start_date = I('post.start_date','');
            $end_date = I('post.end_date','');
            $media_id = I('post.media_id',0);
            $imgmedia_id = I('post.imgmedia_id',0);
            $status = I('post.status',1,'intval');
            $is_storebuy = I('post.is_storebuy',0,'intval');
            $clicktype = I('post.clicktype',0,'intval');
            $appid = I('post.appid','','trim');
            $buybutton = I('post.buybutton','','trim');
            $detailmedia_id = I('post.detailmedia_id','');
            $intro = I('post.intro','','trim');
            $label = I('post.label','');
            $covermedia_id = I('post.covermedia_id','');
            $show_status = I('post.show_status',0);
            $duration = I('post.duration',0,'intval');

            if($clicktype==1){
                $media_id = I('post.media_vid',0);
            }
            if(empty($name) && $type!=20){
                $this->output('缺少必要参数!', "goods/$template_html", 2, 0);
            }
            if(!$media_id){
                $this->output('请上传相关资源', "goods/$template_html", 2, 0);
            }
            $where = array('name'=>$name,'type'=>$type);
            $tmp_goods = array();
            if($id){
                $tmp_goods = $m_goods->getInfo(array('id'=>$id));
                $where['id']= array('neq',$id);
                $res_goods = $m_goods->getInfo($where);
            }else{
                $res_goods = $m_goods->getInfo($where);
            }
            if(!empty($res_goods) && $type!=20){
                $this->output('名称不能重复', "goods/$template_html", 2, 0);
            }

            $page_url = '';
            $item_id = 0;
            if($appid){
                $m_sysconfig = new \Admin\Model\SysConfigModel();
                $all_config = $m_sysconfig->getAllconfig();
                $jd_config = json_decode($all_config['jd_union_smallapp'],true);

                $url_info = parse_url($jd_url);
                if(isset($url_info['scheme'])){
                    preg_match('/^http:\/\/\item+.jd.com\/(\d+).html$/', $jd_url, $matches);
                    $item_id = intval($matches[1]);
                    if(empty($item_id)){
                        preg_match('/^https:\/\/\item+.jd.com\/(\d+).html$/', $jd_url, $matches);
                        $item_id = intval($matches[1]);
                    }
                    switch ($appid){
                        case 'wx13e41a437b8a1d2e'://京东爆款(京东联盟)
                            if(empty($buybutton)){
                                $this->output('请输入购买按钮', "goods/$template_html", 2, 0);
                            }
                            $params = array(
                                'promotionCodeReq'=>array(
                                    'materialId'=>$jd_url,
                                    'chainType'=>3,
                                )
                            );
                            $res = jd_union_api($params,'jd.union.open.promotion.bysubunionid.get');
                            if($res['code']!=200){
                                $this->output('地址错误', "goods/$template_html", 2, 0);
                            }
                            $click_url = urlencode($res['data']['clickURL']);
                            $page_url = '/pages/proxy/union/union?spreadUrl='.$click_url.'&customerinfo='.$jd_config['customerinfo'];
                            break;
                        case 'wx91d27dbf599dff74'://京东购物
                            if(empty($buybutton)){
                                $this->output('请输入购买按钮', "goods/$template_html", 2, 0);
                            }
                            $page_url = 'pages/item/detail/detail?sku='.$item_id;
                            break;
                    }
                }else{
                    $str_position = strpos($jd_url,'pages/');
                    if($str_position!== false && $str_position!=0){
                        $this->output('地址错误', "goods/$template_html", 2, 0);
                    }
                    $page_url = $jd_url;
                }
            }
            $data = array('type'=>$type,'name'=>$name,'wx_category'=>$wx_category,'price'=>$price,'rebate_integral'=>$rebate_integral,'jd_url'=>$jd_url,
                'item_id'=>$item_id,'page_url'=>$page_url,'media_id'=>$media_id,'imgmedia_id'=>$imgmedia_id,'show_status'=>$show_status,'status'=>$status,'is_storebuy'=>$is_storebuy);
            if($appid){
                $data['appid'] = $appid;
            }
            if($buybutton){
                $data['buybutton'] = $buybutton;
            }
            if($duration){
                $data['duration'] = $duration;
            }
            $data['intro'] = $intro;
            if(!empty($label)){
                $data['label'] = json_encode($label,true);
            }
            if(!empty($covermedia_id)){
                $data['cover_imgmedia_ids'] = json_encode($covermedia_id,true);
            }
            if($type==10){
                $media_vid = I('post.media_vid',0);
                if(empty($media_vid)){
                    $this->output('请传入视频资源', "goods/$template_html", 2, 0);
                }
                if(empty($appid) || empty($buybutton)){
                    $this->output('请输入appid或购买按钮名称', "goods/$template_html", 2, 0);
                }
                if($detailmedia_id){
                    $data['detail_imgmedia_ids'] = json_encode($detailmedia_id);
                }
                if($covermedia_id){
                    $data['cover_imgmedia_ids'] = json_encode($covermedia_id);
                }
            }
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始日期不能大于结束日期', "goods/$template_html", 2, 0);
            }
            $nowdate = date('Ymd');
            if(date('Ymd',$stime)>$nowdate){
                $this->output('开始日期不能大于当前日期', "goods/$template_html", 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $data['start_time'] = $start_time;
            $data['end_time'] = $end_time;
            if($id){
                $m_goods->updateData(array('id'=>$id),$data);
                $result = true;
                $goods_id = $id;
            }else{
                $result = $m_goods->add($data);
                $goods_id = $result;
            }
            switch ($type){
                case 10:
                    $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
                    $m_hotelgoods->HandleGoodsperiod();

                    $redis = \Common\Lib\SavorRedis::getInstance();
                    $redis->select(5);
                    $program_key = C('SAPP_OPTIMIZE_PROGRAM');
                    $period = getMillisecond();
                    $period_data = array('period'=>$period);
                    $redis->set($program_key,json_encode($period_data));

                    $this->wx_importproduct($goods_id);
                    break;
                case 20:
                    $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
                    $m_hotelgoods->HandleGoodsperiod($goods_id);
                    break;
                case 40:
                    $m_urlmap  = new \Admin\Model\UrlmapModel();
                    $where = array('short_link'=>$jd_url);
                    $res_url = $m_urlmap->getInfo($where);
                    if(!empty($res_url)){
                        $m_urlmap->updateData(array('id'=>$res_url['id']),array('goods_id'=>$goods_id));
                    }
                    $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
                    $m_hotelgoods->HandleGoodsperiod($goods_id);
                    break;
            }

            if($result){
                $this->output('操作成功', "goods/$goods_list_f");
            }else{
                $this->output('操作失败', "goods/$goods_list_f",2,0);
            }

        }
    }

    public function hoteladd(){
        $goods_id = I('goods_id',0,'intval');
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        $dinfo = $m_goods->getInfo(array('id'=>$goods_id));
        if($dinfo['type']==20){
            $this->output('商家添加的商品不能选择酒楼','goods/goodslist',2,0);
        }
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','goods/goodslist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','goods/goodslist',2,0);
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
                $this->output('添加成功','goods/goodslist');
            }else {
                $this->output('请勿重复添加到酒楼','goods/goodslist',2,0);
            }

        }else{
            $m_media = new \Admin\Model\MediaModel();
            $media_info = $m_media->getMediaInfoById($dinfo['media_id']);
            $dinfo['oss_addr'] = $media_info['oss_addr'];

            if($dinfo['type']==30 || $dinfo['type']==31){
                $display_html = 'hotelexchangeadd';
            }else{
                $display_html = 'hoteladd';
            }
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->display($display_html);
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

            $this->output('操作成功!', 'goods/hotelgoodslist',2);
        }else{
            $this->output('操作失败', 'goods/hotelgoodslist',2,0);
        }
    }


    private function wx_importproduct($goods_id){
        $app_config = C('SMALLAPP_SALE_CONFIG');
        $access_token = getWxAccessToken($app_config);
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        $res_goods = $m_goods->getInfo(array('id'=>$goods_id));
        $m_media = new \Admin\Model\MediaModel();
        $media_info = $m_media->getMediaInfoById($res_goods['media_id']);
        $image_url = $media_info['oss_addr'];
        if($media_info['type']==1){
            $image_url = $image_url.'?x-oss-process=video/snapshot,t_1000,f_jpg,w_800';
        }
        $status = 2;
        if($res_goods['status']==2){
            $status = 1;
        }
        if(!empty($res_goods['wx_category'])){
            $goods_info = array('item_code'=>$goods_id,'title'=>$res_goods['name'],'category_list'=>explode(',',$res_goods['wx_category']),
                'image_list'=>array($image_url),'src_wxapp_path'=>'pages/mine/pop_detail?goods_id='.$goods_id,
                'sku_list'=>array(array('sku_id'=>$goods_id,'price'=>$res_goods['price']*100,'status'=>$status))
            );
            $params = json_encode(array('product_list'=>array($goods_info)));
            $curl = new Curl();
            $url = 'https://api.weixin.qq.com/mall/importproduct?access_token='.$access_token;
            $result = '';
            $curl::post($url,$params,$result);
            if(empty($result)){
                $this->output('同步到微信好物圈失败,请重新提交', 'goods/goodsadd', 2, 0);
            }
            $result = json_decode($result,true);
            if($result['errcode']!=0){
                $this->output('好物圈类目错误', 'goods/goodsadd', 2, 0);
            }
        }

        return true;
    }

}