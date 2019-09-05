<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
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

            $data = array('type'=>$type,'name'=>$name,'price'=>$price,'rebate_integral'=>$rebate_integral,'jd_url'=>$jd_url,
                'media_id'=>$media_id,'status'=>$status);
        	if($type==40){
        	    if(empty($appid) || empty($buybutton)){
                    $this->output('请输入appid或购买按钮名称', 'goods/goodsadd', 2, 0);
                }
                $data['appid'] = $appid;
        	    $data['buybutton'] = $buybutton;
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
            }else{
        	    $result = $m_goods->add($data);
            }

        	if($result){
        		$this->output('操作成功', 'goods/goodslist');
        	}else{
        		$this->output('操作失败', 'goods/goodslist',2,0);
        	}

        }
    }

}