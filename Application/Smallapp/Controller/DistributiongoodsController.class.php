<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class DistributiongoodsController extends BaseController {
    
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

        $where = array('type'=>45);
        if($status)     $where['status'] = $status;
        if($flag)       $where['flag'] = $flag;
        if(!empty($keyword)){
            $where['name'] = array('like',"%$keyword%");
        }
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_GOODSSTOCK');
        $res_cache = $redis->get($cache_key);
        $finance_goods = array();
        if(!empty($res_cache)){
            $finance_goods = json_decode($res_cache,true);
        }

        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $result = $m_goods->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];

        $goods_status = C('DISH_STATUS');
        $goods_flag = C('DISH_FLAG');
        $oss_host = get_oss_host();
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
            $datalist[$k]['flagstr'] = $flagstr;
            $datalist[$k]['image'] = $image;
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
            $datalist[$k]['finance_goods_name'] = $finance_goods[$v['finance_goods_id']]['name'];
        }
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('flag',$flag);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function goodsadd(){
        $id = I('id', 0, 'intval');
        $type = I('type', 0, 'intval');

        $all_distribution_config = array(
            array('name'=>'阶梯价一','min'=>1,'max'=>5,'price'=>777,
                'reward_money'=>'','distribution_reward_money'=>'','ty'=>array(),'ts'=>array()
            ),
            array('name'=>'阶梯价二','min'=>6,'max'=>20,'price'=>666,
                'reward_money'=>'','distribution_reward_money'=>'','ty'=>array(),'ts'=>array()
            ),
            array('name'=>'阶梯价三','min'=>21,'max'=>99999,'price'=>555,
                'reward_money'=>'','distribution_reward_money'=>'','ty'=>array(),'ts'=>array()
            ),
        );
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        if(IS_GET){
            $detail_img_num = $cover_img_num = 6;
            $detailaddr = $coveraddr = array();
            $dinfo = array('type'=>$type,'amount'=>1);
            if($id){
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                $dinfo['price'] = intval($dinfo['price']);
                $distribution_config = json_decode($dinfo['distribution_config'],true);
                foreach ($all_distribution_config as $k=>$v){
                    $values = array();
                    if(isset($distribution_config[$k])){
                        $values = $distribution_config[$k];
                    }
                    $values['name'] = $v['name'];
                    $all_distribution_config[$k] = $values;
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
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(9);

            $cache_key = C('FINANCE_GOODSSTOCK');
            $res_cache = $redis->get($cache_key);
            $finance_goods = array();
            if(!empty($res_cache)){
                $finance_goods = json_decode($res_cache,true);
            }
            $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
            $duser_list = $m_duser->getDataList('id,name',array('level'=>1,'status'=>1),'id asc');

            $this->assign('duser_list',$duser_list);
            $this->assign('all_distribution_config',$all_distribution_config);
            $this->assign('finance_goods',$finance_goods);
            $this->assign('cover_imgs',$cover_imgs);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('vinfo',$dinfo);
            $this->display('goodsadd');
        }else{
            $name = I('post.name','','trim');
            $covermedia_id = I('post.covermedia_id','');
            $detailmedia_id = I('post.detailmedia_id','');
            $intro = I('post.intro','');
            $desc = I('post.desc','');
            $desc2 = I('post.desc2','');
            $price = I('post.price',0,'intval');
            $amount = I('post.amount',0,'intval');
            $line_price = I('post.line_price',0);
            $status = I('post.status',0,'intval');
            $finance_goods_id = I('post.finance_goods_id',0,'intval');
            $distribution_config = I('post.distribution_config','');
            $duser_id = I('post.duser_id',0,'intval');

            if($line_price && $line_price<$price){
                $this->output('划线价必须大于零售价', "distributiongoods/goodsadd", 2, 0);
            }
            if(!$price){
                $this->output('建议零售价不能为空', "distributiongoods/goodsadd", 2, 0);
            }
            if(empty($desc) || empty($desc2)){
                $this->output('请输入奖励规则介绍信息', "distributiongoods/goodsadd", 2, 0);
            }
            foreach ($distribution_config as $k=>$v){
                if($v['price']>$price){
                    $this->output('请设置正确的阶梯价', "distributiongoods/goodsadd", 2, 0);
                }
                if($k=0){
                    if($v['price']!=$price){
                        $this->output('请设置正确的阶梯价一', "distributiongoods/goodsadd", 2, 0);
                    }
                }
            }
            $distribution_config = json_encode($distribution_config);
            $where = array('name'=>$name,'status'=>1,'type'=>45);
            if($id){
                $where['id']= array('neq',$id);
            }
            $res_goods = $m_goods->getInfo($where);
            if(!empty($res_goods)){
                $this->output('名称不能重复', "distributiongoods/goodsadd", 2, 0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            if(empty($price))   $price = 0;
            if(empty($supply_price))   $supply_price = 0;
            if(empty($line_price))   $line_price = 0;
            $data = array('name'=>$name,'intro'=>$intro,'desc'=>$desc,'desc2'=>$desc2,'price'=>$price,
                'distribution_profit'=>0,'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,
                'distribution_config'=>$distribution_config,'duser_id'=>$duser_id,'type'=>45,'finance_goods_id'=>$finance_goods_id,
                'merchant_id'=>92,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
            $data['status'] = $status;
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
                $this->output('操作成功','distributiongoods/goodslist');
            }else{
                $this->output('操作失败', "distributiongoods/goodsadd",2,0);
            }
        }
    }
}