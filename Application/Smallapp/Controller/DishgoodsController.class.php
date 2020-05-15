<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 菜品管理
 *
 */
class DishgoodsController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function goodslist() {
        $area_id = I('area_id',0,'intval');
        $status   = I('status',0,'intval');
        $type   = I('type',0,'intval');
        $flag   = I('flag',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.gtype'=>array('in',array(1,2)));
        if($status)     $where['a.status'] = $status;
        if($type)       $where['a.type'] = $type;
        if($flag)       $where['a.flag'] = $flag;
        if($area_id)    $where['area.id']=$area_id;
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $fields = 'a.id,a.name,a.cover_imgs,a.intro,a.price,a.is_top,a.status,a.flag,a.gtype,a.add_time,a.type,
        user.nickName as staff_name,user.avatarUrl as staff_url,hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_goods->getDishList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $goods_status = C('DISH_STATUS');
        $goods_flag = C('DISH_FLAG');
        $goods_types = C('DISH_TYPE');
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
            if($v['is_localsale']){
                $datalist[$k]['localstr']='是';
            }else{
                $datalist[$k]['localstr']='否';
            }
            $datalist[$k]['typestr']=$goods_types[$v['type']];

            if($v['gtype']==1){
                $datalist[$k]['gtypestr']='单品';
            }else{
                $datalist[$k]['gtypestr']='多型号';
                $res_price = $m_goods->getDataList('*',array('parent_id'=>$v['id']),'id asc',0,1);
                $datalist[$k]['price']=$res_price['list'][0]['price'];
            }
            $datalist[$k]['flagstr'] = $flagstr;
            $datalist[$k]['image'] = $image;
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('flag',$flag);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('goodslist');
    }

    public function goodsadd(){
        $id = I('id', 0, 'intval');
        $type = I('type', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $m_goodsactivity = new \Admin\Model\Smallapp\GoodsActivityModel();
        if(IS_GET){
            $detail_img_num = $cover_img_num = 6;
            $merchant_id = $category_id = $gift_goods_id = 0;
            $detailaddr = $coveraddr = array();
            if($type==0){
                $type = 22;
            }
            $dinfo = array('type'=>$type,'amount'=>1,'gtype'=>1);

            $goods_types = C('DISH_TYPE');
            if($id){
                $m_media = new \Admin\Model\MediaModel();
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                $type = $dinfo['type'];
                if($type==22){
                    $res_goods_activity = $m_goodsactivity->getInfo(array('goods_id'=>$id));
                    if(!empty($res_goods_activity)){
                        $gift_goods_id = $res_goods_activity['gift_goods_id'];
                    }
                }

                $poster_oss_addr = '';
                if(!empty($dinfo['poster_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['poster_media_id']);
                    $poster_oss_addr = $res_media['oss_addr'];
                }
                $dinfo['poster_oss_addr'] = $poster_oss_addr;
                if($dinfo['amount']==0){
                    $dinfo['amount'] = 1;
                }
                $merchant_id = $dinfo['merchant_id'];
                $category_id = $dinfo['category_id'];
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
            foreach ($goods_types as $k=>$v){
                if($k!=$type){
                    unset($goods_types[$k]);
                }
            }

            $res_agoods = $m_goods->getDataList('*',array('type'=>23,'status'=>1));
            $activity_goods = array();
            foreach ($res_agoods as $k=>$v){
                $info = array('id'=>$v['id'],'name'=>$v['name'],'is_select'=>'');
                if($v['id']==$gift_goods_id){
                    $info['is_select'] = 'selected';
                }
                $activity_goods[]=$info;
            }

            $m_category = new \Admin\Model\CategoryModel();
            $categorys = $m_category->getCategory($category_id,1,7);

            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('a.status'=>1,'hotel.state'=>1,'hotel.flag'=>0);
            $fields = 'a.id,a.is_takeout,hotel.name';
            $merchants = $m_merchant->getMerchants($fields,$where,'a.id desc');
            foreach ($merchants as $k=>$v){
                if($merchant_id && $v['id']==$merchant_id){
                    $merchants[$k]['is_select'] = 'selected';
                }else{
                    $merchants[$k]['is_select'] = '';
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
            $this->assign('cover_imgs',$cover_imgs);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('merchants',$merchants);
            $this->assign('goods_types',$goods_types);
            $this->assign('categorys',$categorys);
            $this->assign('activity_goods',$activity_goods);
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
            $supply_price = I('post.supply_price',0);
            $line_price = I('post.line_price',0);
            $distribution_profit = I('post.distribution_profit',0);
            $merchant_id = I('post.merchant_id',0,'intval');
            $type = I('post.type',0,'intval');
            $gtype = I('post.gtype',0,'intval');
            $category_id = I('post.category_id',0,'intval');
            $status = I('post.status',0,'intval');
            $is_localsale = I('post.is_localsale',0,'intval');
            $flag = I('post.flag',0,'intval');
            $postermedia_id = I('post.postermedia_id',0,'intval');
            $is_recommend = I('post.is_recommend',0,'intval');
            $gift_goods_id = I('post.gift_goods_id',0,'intval');

            if($type==22 || $type==23){
                if($price<$supply_price){
                    $this->output('零售价必须大于供货价', "dishgoods/goodsadd", 2, 0);
                }
                if($line_price && $line_price<$price){
                    $this->output('划线价必须大于零售价', "dishgoods/goodsadd", 2, 0);
                }
            }
            if(empty($distribution_profit)){
                $distribution_profit = 0;
            }

            if($gtype==1 && !$price){
                $this->output('建议零售价不能为空', "dishgoods/goodsadd", 2, 0);
            }
            if(!$merchant_id){
                $this->output('请先选择商家', "dishgoods/goodsadd", 2, 0);
            }

            $where = array('name'=>$name,'merchant_id'=>$merchant_id);
            if($id){
                $where['id']= array('neq',$id);
                $res_goods = $m_goods->getInfo($where);
            }else{
                $res_goods = $m_goods->getInfo($where);
            }
            if(!empty($res_goods)){
                $this->output('名称不能重复', "dishgoods/goodsadd", 2, 0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            if(empty($price))   $price = 0;
            if(empty($supply_price))   $supply_price = 0;
            if(empty($line_price))   $line_price = 0;
            $data = array('name'=>$name,'video_intromedia_id'=>$video_intromedia_id,'intro'=>$intro,'notice'=>$notice,'price'=>$price,
                'distribution_profit'=>$distribution_profit,'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,
                'merchant_id'=>$merchant_id,'poster_media_id'=>$postermedia_id,'type'=>$type,'gtype'=>$gtype,'category_id'=>$category_id,
                'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'),'is_recommend'=>$is_recommend);
            if($type==22){
                if($flag==2){
                    $status = 1;
                }else{
                    $status = 2;
                }
                if($amount==0){
                    $status = 2;
                }
            }
            $data['status'] = $status;
            $data['flag'] = $flag;
            $data['is_localsale'] = $is_localsale;
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
                $goods_id = $id;
            }else{
                $result = $m_goods->add($data);
                $goods_id = $result;
            }
            if($type==22){
                if($gtype==2){
                    $res_allgoods = $m_goods->getDataList('*',array('parent_id'=>$goods_id,'status'=>1),'id desc',0,1);
                    if($res_allgoods['total']<=0){
                        $m_goods->updateData(array('id'=>$goods_id),array('status'=>2,'flag'=>3));
                    }
                }
                $res_goods_activity = $m_goodsactivity->getInfo(array('goods_id'=>$goods_id));
                if(!empty($res_goods_activity)){
                    if($res_goods_activity['gift_goods_id']!=$gift_goods_id){
                        $gift_goods = array('gift_goods_id'=>$gift_goods_id,'goods_id'=>$goods_id);
                        $m_goodsactivity->updateData(array('id'=>$res_goods_activity['id']),$gift_goods);
                    }
                }else{
                    $gift_add = array('gift_goods_id'=>$gift_goods_id,'goods_id'=>$goods_id);
                    $m_goodsactivity->addData($gift_add);
                }
            }
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $res_merchant = $m_merchant->getInfo(array('id'=>$merchant_id));

            if($res_merchant['is_takeout']==0){
                $m_merchant->updateData(array('id'=>$res_merchant['id']),array('is_takeout'=>1));
            }
            if($result){
                $this->output('操作成功', "dishgoods/goodslist");
            }else{
                $this->output('操作失败', "dishgoods/goodsadd",2,0);
            }
        }
    }

    public function addmodel(){
        $goods_id = I('goods_id',0,'intval');
        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $ginfo = $m_goods->getInfo(array('id'=>$goods_id));
        if(IS_GET){
            $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
            $where = array('category_id'=>$ginfo['category_id'],'status'=>1);
            $result = $m_goodsspecification->getDataList('*',$where,'sort desc,id asc');
            $specifications = array();
            foreach ($result as $v){
                $specifications[]=array('id'=>$v['id'],'name'=>$v['name'],'sort'=>$v['sort'],'is_select'=>'');
            }
            $specifications[0]['is_select'] = 'selected';

            $this->assign('goods_id',$goods_id);
            $this->assign('specifications',$specifications);
            $this->display();
        }else{
            $names = I('post.names');
            $attr_ids = I('post.attr_ids');
            $specification_id = I('post.specification_id',0,'intval');

            $m_specificationattr = new \Admin\Model\Smallapp\GoodsspecificationattrModel();
            foreach ($names as $k=>$v){
                if(!empty($v)){
                    if(isset($attr_ids[$k]) && $attr_ids[$k]>0){
                        $m_specificationattr->updateData(array('id'=>$attr_ids[$k]),array('name'=>$v));
                    }else{
                        $add_data = array('goods_id'=>$goods_id,'name'=>$v,'specification_id'=>$specification_id);
                        $m_specificationattr->add($add_data);
                    }
                }else{
                    if(isset($attr_ids[$k]) && $attr_ids[$k]>0){
                        $m_specificationattr->updateData(array('id'=>$attr_ids[$k]),array('name'=>$v));
                    }
                }
            }

            $where = array('goods_id'=>$goods_id);
            $res_attr = $m_specificationattr->getDataList('*',$where,'id asc');
            $all_attrs = array();
            foreach ($res_attr as $v){
                if($v['id'] && $v['name']){
                    $all_attrs[$v['specification_id']][$v['id']] = $v['name'];
                }
            }
            $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
            $where = array('category_id' => $ginfo['category_id'], 'status' => 1);
            $result = $m_goodsspecification->getDataList('*', $where, 'sort desc,id asc');
            $all_models = array();
            foreach ($result as $v) {
                $specification_id = $v['id'];
                if(isset($all_attrs[$specification_id])){
                    $all_models[] = $all_attrs[$specification_id];
                }
            }
            $arr1 = array();
            $result = array_shift($all_models);
            while($arr2 = array_shift($all_models)){
                $arr1 = $result;
                $result = array();
                foreach($arr1 as $k=>$v){
                    foreach($arr2 as $k2=>$v2){
                        $result[$k.'_'.$k2] = $v.'_'.$v2;
                    }
                }
            }
            if(count($result)>100){
                $this->output('规格组合超过100,请修改规格', 'dishgoods/modelgoods',2,0);
            }

            $this->output('操作成功!', 'dishgoods/modelgoods');
        }
    }

    public function modelgoods(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $goods_id = I('goods_id',0,'intval');

        $start = ($page-1) * $size;
        $where = array('a.parent_id'=>$goods_id);
        $where['a.status'] = array('in',array(1,2));
        $fields = 'a.id,a.name,a.attr_name,a.model_media_id,a.price,a.supply_price,a.line_price,a.amount,a.status,a.flag,a.gtype,a.add_time';
        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $result = $m_goods->getDishList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];
        if(!empty($datalist)){
            $goods_status = C('DISH_STATUS');
            $m_media = new \Admin\Model\MediaModel();
            foreach ($datalist as $k=>$v){
                $res_media = $m_media->getMediaInfoById($v['model_media_id']);
                $datalist[$k]['model_img'] = $res_media['oss_addr'];
                $datalist[$k]['statusstr'] = $goods_status[$v['status']];
            }
        }
        $ginfo = $m_goods->getInfo(array('id'=>$goods_id));

        $this->assign('goods_id',$goods_id);
        $this->assign('ginfo',$ginfo);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function modelgoodsadd(){
        $goods_id = I('goods_id',0,'intval');
        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $ginfo = $m_goods->getInfo(array('id'=>$goods_id));

        $m_specificationattr = new \Admin\Model\Smallapp\GoodsspecificationattrModel();
        $where = array('goods_id'=>$goods_id);
        $res_attr = $m_specificationattr->getDataList('*',$where,'id asc');
        if(empty($res_attr)){
            $this->output('请先录入型号信息', 'dishgoods/modelgoods',2,0);
        }
        $all_attrs = array();
        foreach ($res_attr as $v){
            if($v['id'] && $v['name']){
                $all_attrs[$v['specification_id']][$v['id']] = $v['name'];
            }
        }
        if(IS_GET) {
            $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
            $res_goods = $m_dishgoods->getDataList('id,name,attr_name,attr_ids',array('parent_id'=>$goods_id),'id desc');
            $mode_attrs = array();
            foreach ($res_goods as $v){
                $mode_attrs[]=$v['attr_ids'];
            }
            $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
            $where = array('category_id' => $ginfo['category_id'], 'status' => 1);
            $result = $m_goodsspecification->getDataList('*', $where, 'sort desc,id asc');

            $all_models = array();
            foreach ($result as $v) {
                $specification_id = $v['id'];
                if(isset($all_attrs[$specification_id])){
                    $all_models[] = $all_attrs[$specification_id];
                }
            }

            $arr1 = array();
            $result = array_shift($all_models);
            while($arr2 = array_shift($all_models)){
                $arr1 = $result;
                $result = array();
                foreach($arr1 as $k=>$v){
                    foreach($arr2 as $k2=>$v2){
                        $result[$k.'_'.$k2] = $v.'_'.$v2;
                    }
                }
            }
            if(count($result)>100){
                $this->output('规格组合超过100,请修改规格', 'dishgoods/modelgoods',2,0);
            }

            $specifications = array();
            if(!empty($result)){
                $i = 0;
                foreach ($result as $k=>$v){
                    if(!in_array($k,$mode_attrs)){
                        $specifications[]=array('model_key'=>$k,'name'=>$v,'media_id'=>"model{$i}media_id",'number'=>$i+1);
                        $i++;
                    }
                }
            }

            $this->assign('specifications',$specifications);
            $this->assign('goods_id',$goods_id);
            $this->display('modelgoodsadd');
        }else{
            $names = I('names');
            $model_ids = I('model_ids');
            $price = I('price');
            $supply_price = I('supply_price');
            $line_price = I('line_price');
            $amount = I('amount');
            $distribution_profits = I('distribution_profit');

            $m_goodsattr = new \Admin\Model\Smallapp\GoodsAttrModel();
            foreach ($names as $k=>$v){
                $model_name = $v;
                $model_media_id = I("model{$k}media_id",0,'intval');
                $now_price = $price[$k];
                $now_supply_price = $supply_price[$k];
                $now_line_price = $line_price[$k];
                $now_amount = $amount[$k];
                $attr_ids = $model_ids[$k];
                $distribution_profit = $distribution_profits[$k];
                if(empty($distribution_profit)){
                    $distribution_profit = 0;
                }

                if($now_price && $now_supply_price && $now_line_price && $now_amount){
                    if($now_price<$now_supply_price){
                        $this->output('零售价必须大于供货价', "dishgoods/modelgoodsadd", 2, 0);
                    }
                    if($now_line_price && $now_line_price<$now_price){
                        $this->output('划线价必须大于零售价', "dishgoods/modelgoodsadd", 2, 0);
                    }
                    $add_data = array('attr_name'=>$model_name,'attr_ids'=>$attr_ids,'price'=>$now_price,'supply_price'=>$now_supply_price,
                        'line_price'=>$now_line_price,'distribution_profit'=>$distribution_profit,'amount'=>$now_amount,'model_media_id'=>$model_media_id,'parent_id'=>$goods_id,
                        'merchant_id'=>$ginfo['merchant_id'],'type'=>22,'gtype'=>3,'status'=>1,'flag'=>2);
                    $tmp_goods_id = $m_goods->add($add_data);

                    $attr_ids_arr = explode('_',$add_data['attr_ids']);
                    foreach ($attr_ids_arr as $v){
                        if(!empty($v)){
                            $attr_data = array('goods_id'=>$tmp_goods_id,'attr_id'=>$v,'status'=>1);
                            $m_goodsattr->add($attr_data);
                        }
                    }
                }
            }

            $this->output('录入完成', 'dishgoods/modelgoods');
        }
    }

    public function modelgoodsedit(){
        $id = I('id', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $m_goodsactivity = new \Admin\Model\Smallapp\GoodsActivityModel();
        if(IS_GET){
            $vinfo = $m_goods->getInfo(array('id'=>$id));
            $m_media = new \Admin\Model\MediaModel();
            $res_media = $m_media->getMediaInfoById($vinfo['model_media_id']);
            $vinfo['model_img'] = $res_media['oss_addr'];
            $gift_goods_id = 0;
            $res_goods_activity = $m_goodsactivity->getInfo(array('goods_id'=>$id));
            if(!empty($res_goods_activity)){
                $gift_goods_id = $res_goods_activity['gift_goods_id'];
            }
            $res_agoods = $m_goods->getDataList('*',array('type'=>23,'status'=>1));
            $activity_goods = array();
            foreach ($res_agoods as $k=>$v){
                $info = array('id'=>$v['id'],'name'=>$v['name'],'is_select'=>'');
                if($v['id']==$gift_goods_id){
                    $info['is_select'] = 'selected';
                }
                $activity_goods[]=$info;
            }

            $this->assign('activity_goods',$activity_goods);
            $this->assign('vinfo',$vinfo);
            $this->display('modelgoodsedit');
        }else{
            $model_media_id = I('post.model_media_id',0,'intval');
            $gift_goods_id = I('post.gift_goods_id',0,'intval');
            $price = I('post.price',0);
            $amount = I('post.amount',0,'intval');
            $supply_price = I('post.supply_price',0);
            $line_price = I('post.line_price',0);
            $flag = I('post.flag',0,'intval');
            $distribution_profit = I('post.distribution_profit',0);
            if(empty($distribution_profit)){
                $distribution_profit = 0;
            }

            if($price<$supply_price){
                $this->output('零售价必须大于供货价', "dishgoods/modelgoodsedit", 2, 0);
            }
            if($line_price && $line_price<$price){
                $this->output('划线价必须大于零售价', "dishgoods/modelgoodsedit", 2, 0);
            }

            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('price'=>$price,'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,'distribution_profit'=>$distribution_profit,
                'model_media_id'=>$model_media_id,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
            if($flag==2){
                $status = 1;
            }else{
                $status = 2;
            }
            if($amount==0){
                $status = 2;
            }
            $data['status'] = $status;
            $m_goods->updateData(array('id'=>$id),$data);

            $res_goods_activity = $m_goodsactivity->getInfo(array('goods_id'=>$id));
            if(!empty($res_goods_activity)){
                if($res_goods_activity['gift_goods_id']!=$gift_goods_id){
                    $gift_goods = array('gift_goods_id'=>$gift_goods_id,'goods_id'=>$id);
                    $m_goodsactivity->updateData(array('id'=>$res_goods_activity['id']),$gift_goods);
                }
            }else{
                $gift_add = array('gift_goods_id'=>$gift_goods_id,'goods_id'=>$id);
                $m_goodsactivity->addData($gift_add);
            }

            $this->output('录入完成', 'dishgoods/modelgoods');
        }

    }

    public function modelgoodsdel(){
        $id = I('get.id',0,'intval');

        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];
        $data = array('status'=>3,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
        $result = $m_goods->updateData(array('id'=>$id),$data);
        if($result){
            $m_goods_attr = new \Admin\Model\Smallapp\GoodsAttrModel();
            $m_goods_attr->updateData(array('goods_id'=>$id),array('status'=>2));
            
            $this->output('删除成功!', 'dishgoods/modelgoods',2);
        }else{
            $this->output('删除失败', 'dishgoods/modelgoods',2,0);
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
                $m_goods->updateData(array('parent_id'=>$id),array('status'=>$status,'flag'=>$flag));
            }
            $this->output('操作成功!', 'dishgoods/goodslist',2);
        }else{
            $this->output('操作失败', 'dishgoods/goodslist',2,0);
        }
    }


    public function getspecificationinfo(){
        $specification_id = I('specification_id',0,'intval');
        $goods_id = I('goods_id',0,'intval');

        $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
        $res_spec = $m_goodsspecification->getInfo(array('id'=>$specification_id));

        $m_specificationattr = new \Admin\Model\Smallapp\GoodsspecificationattrModel();
        $where = array('goods_id'=>$goods_id,'specification_id'=>$specification_id);
        $res_attr = $m_specificationattr->getDataList('*',$where,'id asc');

        $model_names = array();
        for($i=1;$i<=10;$i++){
            $attr_num = $i-1;
            $value = '';
            $attr_id = 0;
            if(isset($res_attr[$attr_num]) && !empty($res_attr[$attr_num]['id'])){
                $value = $res_attr[$attr_num]['name'];
                $attr_id = $res_attr[$attr_num]['id'];
            }
            $info = array('id'=>$i,'name'=>$res_spec['name'].$i,'content'=>$value,'attr_id'=>$attr_id);
            $model_names[] = $info;
        }
        $this->ajaxReturn($model_names);
    }


}
