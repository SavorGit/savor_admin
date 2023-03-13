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
            'ty'=>array('name'=>'通用奖励','type'=>'ty'),
            'ts'=>array('name'=>'特殊奖励','type'=>'ts'),
        );
        $all_price_list = array(
            array('name'=>'一种','value'=>'1-5,777'),
            array('name'=>'二种','value'=>'6-20,666'),
            array('name'=>'三种','value'=>'21-1000,555'),
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
                    $all_distribution_config[$k]['values'] = $values;
                }
                $price_list = json_decode($dinfo['price_list'],true);
                foreach ($price_list as $k=>$v){
                    $price_info = $v['min'].'-'.$v['max'].','.$v['pirce'];
                    $all_price_list[$k]['value']=$price_info;
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
            $this->assign('all_price_list',$all_price_list);
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
            $notice = I('post.notice','','trim');
            $price = I('post.price',0,'intval');
            $price_list = I('post.price_list','');
            $reward_money = I('post.reward_money',0,'intval');
            $distribution_reward_money = I('post.distribution_reward_money',0,'intval');
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
            $now_shareprofit_config = array();
            foreach ($distribution_config as $k=>$v){
                $shareprofit_percent = array_sum($v);
                if($shareprofit_percent>0){
                    if($shareprofit_percent==100){
                        if($v[0]==0 || $v[1]==0){
                            $tips = $all_distribution_config[$k]['name'].'分销设置一方不能为0';
                            $this->output($tips, 'distributiongoods/goodsadd',2,0);
                        }
                    }else{
                        $tips = $all_distribution_config[$k]['name'].'分销设置加和不等于100';
                        $this->output($tips, 'distributiongoods/goodsadd',2,0);
                    }
                    $now_shareprofit_config[$k]=$v;
                }
            }
            $distribution_config = json_encode($now_shareprofit_config);
            $prices = array();
            if(!empty($price_list)){
                foreach ($price_list as $v){
                    $v_arr = explode(',',$v);
                    $plist_price = intval($v_arr[1]);
                    $nums = explode('-',$v_arr[0]);
                    $min = intval($nums[0]);
                    $max = intval($nums[1]);
                    if(empty($min) || empty($max) || empty($plist_price)){
                        $this->output('价格列表设置错误', 'distributiongoods/goodsadd',2,0);
                    }
                    $prices[]=array('min'=>$min,'max'=>$max,'pirce'=>$plist_price);
                }
            }
            $price_list = json_encode($prices);
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
            $data = array('name'=>$name,'intro'=>$intro,'notice'=>$notice,'price'=>$price,'reward_money'=>$reward_money,'distribution_reward_money'=>$distribution_reward_money,
                'distribution_profit'=>0,'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,'price_list'=>$price_list,
                'distribution_config'=>$distribution_config,'duser_id'=>$duser_id,'type'=>45,'finance_goods_id'=>$finance_goods_id,
                'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
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