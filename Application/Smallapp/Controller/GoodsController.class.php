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

        $where = array();
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        if($type){
            $where['type'] = $type;
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
        $m_hotel = new \Admin\Model\HotelModel();

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

            $goods_id = $v['id'];
            $subQuery = $m_hotelgoods->field('hotel_id')->where(array('goods_id'=>$goods_id))->group('hotel_id')->buildSql();
            $res = $m_hotel->field('name')->where("id in $subQuery")->select();
            $hotelarr = array();
            foreach ($res as $hv){
                $hotelarr[] = $hv['name'];
            }
            $datalist[$k]['hotels'] = join(',',$hotelarr);
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type',$type);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('goodslist');
    }
    
    public function goodsadd(){
        $id = I('id', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\GoodsModel();
        if(IS_GET){
            $detail_img_num = 5;
        	$dinfo = array('media_type'=>1);
        	$detailaddr = array();
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
        	$this->assign('detail_imgs',$detail_imgs);
        	$this->assign('vinfo',$dinfo);
        	$this->display('goodsadd');
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
        	$status = I('post.status',1,'intval');
            $clicktype = I('post.clicktype',0,'intval');
            $appid = I('post.appid','','trim');
            $buybutton = I('post.buybutton','','trim');
            $detailmedia_id = I('post.detailmedia_id','');

            if($clicktype==1){
                $media_id = I('post.media_vid',0);
            }
        	if(empty($name) && $type!=20){
        		$this->output('缺少必要参数!', 'goods/goodsadd', 2, 0);
        	}
        	if(!$media_id){
        	    $this->output('请上传相关资源', 'goods/goodsadd', 2, 0);
            }
        	$where = array('name'=>$name,'type'=>$type);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_goods = $m_goods->getInfo($where);
        	}else{
                $res_goods = $m_goods->getInfo($where);
        	}
        	if(!empty($res_goods) && $type!=20){
        		$this->output('名称不能重复', 'goods/goodsadd', 2, 0);
        	}

        	$page_url = '';
        	$item_id = 0;
        	if($appid){
                $jd_config = C('JD_UNION_CONFIG');
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
                                $this->output('请输入购买按钮', 'goods/goodsadd', 2, 0);
                            }
                            $params = array(
                                'promotionCodeReq'=>array(
                                    'materialId'=>$jd_url,
                                    'chainType'=>3,
                                )
                            );
                            $res = jd_union_api($params,'jd.union.open.promotion.bysubunionid.get');
                            if($res['code']!=200){
                                $this->output('地址错误', 'goods/goodsadd', 2, 0);
                            }
                            $click_url = urlencode($res['data']['clickURL']);
                            $page_url = '/pages/proxy/union/union?spreadUrl='.$click_url.'&customerinfo='.$jd_config['customerinfo'];
                            break;
                        case 'wx91d27dbf599dff74'://京东购物
                            if(empty($buybutton)){
                                $this->output('请输入购买按钮', 'goods/goodsadd', 2, 0);
                            }
                            $page_url = 'pages/item/detail/detail?sku='.$item_id;
                            break;
                    }
                }else{
                    $str_position = strpos($jd_url,'pages/');
                    if($str_position!== false && $str_position!=0){
                        $this->output('地址错误', 'goods/goodsadd', 2, 0);
                    }
                    $page_url = $jd_url;
                }
            }

            $data = array('type'=>$type,'name'=>$name,'wx_category'=>$wx_category,'price'=>$price,'rebate_integral'=>$rebate_integral,'jd_url'=>$jd_url,
                'item_id'=>$item_id,'page_url'=>$page_url,'media_id'=>$media_id,'status'=>$status);
        	if($appid){
                $data['appid'] = $appid;
            }
            if($buybutton){
                $data['buybutton'] = $buybutton;
            }
        	if($type==40){
                $media_vid = I('post.media_vid',0);
                if(empty($media_vid)){
                    $this->output('请传入视频资源', 'goods/goodsadd', 2, 0);
                }
        	    if(empty($appid) || empty($buybutton)){
                    $this->output('请输入appid或购买按钮名称', 'goods/goodsadd', 2, 0);
                }
        	    if($detailmedia_id){
        	        $data['detail_imgmedia_ids'] = json_encode($detailmedia_id);
                }
            }else{
                $stime = strtotime($start_date);
                $etime = strtotime($end_date);
                if($stime>$etime){
                    $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
                }
                $start_time = date('Y-m-d 00:00:00',$stime);
                $end_time = date('Y-m-d 23:59:59',$etime);
                $data['start_time'] = $start_time;
                $data['end_time'] = $end_time;
            }

        	if($id){
        	    $m_goods->updateData(array('id'=>$id),$data);
                $result = true;
        	    $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        	    $m_hotelgoods->HandleGoodsperiod($id);
        	    $goods_id = $id;
            }else{
        	    $result = $m_goods->add($data);
                $goods_id = $result;
            }

        	if($result){
                if($type==40){
                    $redis = \Common\Lib\SavorRedis::getInstance();
                    $redis->select(5);
                    $program_key = C('SAPP_OPTIMIZE_PROGRAM');
                    $period = getMillisecond();
                    $period_data = array('period'=>$period);
                    $redis->set($program_key,json_encode($period_data));
                }
                if($type==10){
                    $this->wx_importproduct($goods_id);
                }

        		$this->output('操作成功', 'goods/goodslist');
        	}else{
        		$this->output('操作失败', 'goods/goodslist',2,0);
        	}

        }
    }

    private function wx_importproduct($goods_id){
        return true;
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
        $goods_id = 1;
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
        return true;
    }

}