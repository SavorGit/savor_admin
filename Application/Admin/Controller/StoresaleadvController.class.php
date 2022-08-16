<?php
namespace Admin\Controller;
use \Common\Lib\SavorRedis;

/**
 *@desc 本店有售商品广告管理
 *
 */

class StoresaleadvController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function advlist(){
        $keywords = I('keywords','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array('storesaleads.state'=>array('in',array(1,2)));
        if (!empty($keywords)) {
            $where['ads.name'] = array('like',"%$keywords%");
        }
        $field = 'storesaleads.id,storesaleads.state,ads.name,ads.duration,ads.resource_type,storesaleads.ads_id,storesaleads.add_time,storesaleads.creator_id';
        $orders = 'storesaleads.id desc';
        $start  = ($page-1) * $size;

        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        $result = $m_storesaleads->getList($field,$where,$orders,$start,$size);
        $m_user = new \Admin\Model\UserModel();
        $datalist = $result['list'];
        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        foreach ($datalist as $k=>$v){
            if($v['resource_type']==1){
                $v['resourcetypestr'] = '视频';
            }elseif($v['resource_type']==2){
                $v['resourcetypestr'] = '图片';
            }else{
                $v['resourcetypestr'] = '';
            }
            $userinfo = $m_user->getUserInfo($v['creator_id']);
            $v['username'] = $userinfo['remark'];
            $where = array('storesale_ads_id'=>$v['id']);
            $hotels = $m_ads_hotel->getDataCount($where);
            $v['hotels'] = $hotels;
            $datalist[$k] = $v;
        }

        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('keywords',$keywords);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('advlist');
    }

    public function adddevilery(){
        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        if(IS_POST){
            $h_b_arr = $_POST['hbarr'];
            $marketid = I('post.marketid',0,'intval');
            $media_vid = I('post.media_vid',0,'intval');
            $start_date = I('post.start_date', '');
            $end_date = I('post.end_date', '');
            $goods_id = I('post.goods_id',0,'intval');
            $is_price = I('post.is_price',0,'intval');
            $cover_img_media_id = I('post.cover_img_media_id',0,'intval');
            $is_sapp_qrcode = I('post.is_sapp_qrcode',0,'intval');

            $now_date = date("Y-m-d H:i:s");
            $now_day = date("Y-m-d");
            if($start_date > $end_date){
                $this->output('投放开始时间必须小于等于结束时间', 'storesaleadv/advlist',2,0);
            }
            if($start_date < $now_day){
                $this->output('投放开始时间必须大于等于今天', 'storesaleadv/advlist',2,0);
            }

            $m_ads = new \Admin\Model\AdsModel();
            if(!empty($marketid)){
                $ads_id = $marketid;
            }elseif(!empty($media_vid)){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getInfo(array('id'=>$media_vid));
                if(empty($res_media['md5']) || empty($res_media['oss_filesize']) || empty($res_media['duration'])){
                    $this->output('请选择资源库其他视频', 'storesaleadv/advlist',2,0);
                }
                $userInfo = session('sysUserInfo');
                $ads_data = array('media_id'=>$media_vid,'name'=>$res_media['name'],'duration'=>$res_media['duration'],'type'=>9,
                    'is_sapp_qrcode'=>$is_sapp_qrcode,'create_time'=>date("Y-m-d H:i:s"),'state'=>1,'creator_name'=>'','creator_id'=>$userInfo['id'],'resource_type'=>$res_media['type']
                );
                $ads_id = $m_ads->add($ads_data);
            }else{
                $ads_id = 0;
            }
            if(empty($ads_id)){
                $this->output('上传广告视频失败请重新上传', 'storesaleadv/advlist',2,0);
            }
            $m_ads->updateData(array('id'=>$ads_id),array('type'=>9,'is_sapp_qrcode'=>$is_sapp_qrcode));

            $userInfo = session('sysUserInfo');
            $hotel_arr = json_decode($h_b_arr, true);
            $start_datetime = date('Y-m-d 00:00:00',strtotime($start_date));
            $end_datetime = date('Y-m-d 23:59:59',strtotime($end_date));
            $save_data = array('ads_id'=>$ads_id,'start_date'=>$start_datetime,'end_date'=>$end_datetime,'add_time'=>$now_date,
                'creator_id'=>$userInfo['id'],'state'=>1,'goods_id'=>$goods_id,'is_price'=>$is_price,'cover_img_media_id'=>$cover_img_media_id
            );
            $sale_ads_id = $m_storesaleads->addData($save_data);
            if(!$sale_ads_id){
                $this->output('添加失败','storesaleadv/advlist',2,0);
            }

            $m_adshotel = new \Admin\Model\StoresaleAdsHotelModel();
            $data_hotel = array();
            $tmp_hb = array();
            foreach ($hotel_arr as $k=>$v) {
                $hotel_id = $v['hotel_id'];
                if(array_key_exists($hotel_id, $tmp_hb)){
                    continue;
                }
                $tmp_hb[$hotel_id] = 1;
                $data_hotel[] = array('hotel_id'=>$hotel_id,'storesale_ads_id'=>$sale_ads_id);
            }
            $res = $m_adshotel->addAll($data_hotel);
            if($res){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
                foreach($hotel_arr as $key=>$v){
                    if(!empty($v['hotel_id'])){
                        $period = getMillisecond();
                        $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                    }
                }
                $this->output('添加成功','storesaleadv/advlist');
            }else {
                $this->output('添加失败','storesaleadv/advlist',2,0);
            }

        }else{
            //城市
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();

            $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
            $where = array('type'=>43,'status'=>1,'flag'=>2);
            $all_goods = $m_goods->getDataList('*',$where, 'id desc');
            foreach ($all_goods as $k=>$v){
                $all_goods[$k]['name'] = $v['name']."({$v['price']})";
            }

            $this->assign('all_goods',$all_goods);
            $this->assign('is_city_search',1);
            $this->assign('areainfo', $area_arr);
            $this->display('adddevilery');
        }
    }

    public function advpreview(){
        $adsid = I('deliveryid','0','intval');
        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        $field = ' storesaleads.*,ads.name adname,ads.duration,med.oss_addr';
        $oss_host = $this->oss_host;
        $vinfo = $m_storesaleads->getAdsInfoByid($field, array('storesaleads.id'=>$adsid));
        $is_price_str = '否';
        if($vinfo['is_price']==1){
            $is_price_str = '是';
        }

        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $res_goods = $m_goods->getInfo(array('id'=>$vinfo['goods_id']));
        $all_types = C('GOODS_WINE_TYPES');
        $vinfo['goods_name'] = $res_goods['name'];
        $vinfo['is_price_str'] = $is_price_str;
        $vinfo['typestr'] = $all_types[$res_goods['wine_type']];
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];
        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));

        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $where = array('storesale_ads_id'=>$adsid);
        $hotel_count = $m_ads_hotel->getDataCount($where);
        $this->assign('hotel_count',$hotel_count);

        $this->assign('vinfo',$vinfo);
        $this->display('advpreviewhotel');
    }

    public function showdetail() {
        $adsid = I('deliveryid','0','intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');

        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $field = 'adshotel.id,hotel.name as hotel_name';
        $where = array('adshotel.storesale_ads_id'=>$adsid);
        $start = ($page-1)*$size;
        $result = $m_ads_hotel->getList($field,$where,'id asc',$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $datalist[$k]['msg'] = '酒楼：'.$v['hotel_name'];
        }

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('deliveryid', $adsid);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->display('showdetail');
    }

    public function hoteldatalist() {
        $adsid = I('deliveryid',0,'intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $keyword = I('keyword','','trim');

        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $field = 'adshotel.id,adshotel.add_time,hotel.id as hotel_id,hotel.name as hotel_name';
        $where = array('adshotel.storesale_ads_id'=>$adsid);
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        $start = ($page-1)*$size;
        $result = $m_ads_hotel->getList($field,$where,'id asc',$start,$size);
        $datalist = $result['list'];

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('deliveryid', $adsid);
        $this->assign('datalist', $datalist);
        $this->assign('keyword', $keyword);
        $this->assign('page',  $result['page']);
        $this->display();
    }

    public function hoteladd(){
        $deliveryid = I('deliveryid',0,'intval');
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','storesaleadv/hoteladd',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','storesaleadv/hoteladd',2,0);
            }
            $m_adshotel = new \Admin\Model\StoresaleAdsHotelModel();
            $data_hotel = array();
            $tmp_hb = array();
            foreach ($hotel_arr as $k=>$v) {
                $hotel_id = $v['hotel_id'];
                if(array_key_exists($hotel_id, $tmp_hb)){
                    continue;
                }
                $tmp_hb[$hotel_id] = 1;
                $data_hotel[] = array('hotel_id'=>$hotel_id,'storesale_ads_id'=>$deliveryid);
            }
            $res = $m_adshotel->addAll($data_hotel);
            if($res){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
                foreach($hotel_arr as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }
                $this->output('添加成功','storesaleadv/advlist');
            }else {
                $this->output('添加失败','storesaleadv/advlist',2,0);
            }
        }else{
            $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
            $field = 'storesaleads.*,ads.name,ads.duration,med.oss_addr';
            $oss_host = $this->oss_host;
            $dinfo = $m_storesaleads->getAdsInfoByid($field, array('storesaleads.id'=>$deliveryid));
            $dinfo['oss_addr'] = $oss_host.$dinfo['oss_addr'];

            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $this->assign('vinfo', $dinfo);
            $this->assign('deliveryid', $deliveryid);
            $this->display('hoteladd');
        }
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotel_id',0,'intval');
        $m_adshotel = new \Admin\Model\StoresaleAdsHotelModel();
        $result = $m_adshotel->delData(array('id'=>$id));
        if($result){
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
            $period = getMillisecond();
            $redis->set($cache_key_pre.$hotel_id,$period,86400*14);

            $this->output('操作成功!', 'storesaleadv/hoteldatalist',2);
        }else{
            $this->output('操作失败', 'storesaleadv/hoteldatalist',2,0);
        }
    }

    public function operateStatus(){
        $adsid = I('get.id',0,'intval');
        $status = I('get.status',3,'intval');
        $where = array('id'=>$adsid);
        $m_storesale = new \Admin\Model\StoresaleAdsModel();
        $m_storesale->updateData($where, array('state'=>$status));
        if($status==1){
            $message = '启用成功';
        }elseif($status==2){
            $message = '禁用成功';
        }else{
            $message = '操作成功';
        }
        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $field = 'hotel_id';
        $where = array('storesale_ads_id'=>$adsid);
        $res_hotel = $m_ads_hotel->getDataList($field,$where,'id asc');
        if(!empty($res_hotel)){
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
            foreach($res_hotel as $key=>$v){
                $period = getMillisecond();
                $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
            }
        }
        $this->output($message, 'storesaleadv/advlist',2);
    }

    public function deleteAds(){
        $adsid = I('get.id','0','intval');
        $where = array('id'=>$adsid);
        $m_storesale = new \Admin\Model\StoresaleAdsModel();
        $ret = $m_storesale->updateData($where, array('state'=>3));
        if($ret){
            $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
            $field = 'hotel_id';
            $where = array('storesale_ads_id'=>$adsid);
            $res_hotel = $m_ads_hotel->getDataList($field,$where,'id asc');
            if(!empty($res_hotel)){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
                foreach($res_hotel as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }
            }
            $this->output('删除成功', 'storesaleadv/advlist', 2);
        }else {
            $this->error('删除失败');
        }
    }

    public function getOcupHotel() {
        $area_id = I('area_id',0);
        $hotel_name = I('hotel_name', '');
        $goods_id = I('goods_id',0,'intval');

        $where = "1=1";
        if ($area_id) {
            $this->assign('area_k',$area_id);
            $where .= "	AND sht.area_id = $area_id";
        }
        if($hotel_name){
            $this->assign('name',$hotel_name);
            $where .= "	AND name LIKE '%{$hotel_name}%'";
        }
        if($goods_id>0){
            $hwhere = array('h.goods_id'=>$goods_id);
            $m_hotelgoods  = new \Admin\Model\Smallapp\HotelGoodsModel();
            $res_goods = $m_hotelgoods->getGoodsList('h.hotel_id',$hwhere,'','','h.hotel_id');
            $hotel_ids = array();
            foreach ($res_goods as $v){
                $hotel_ids[]=$v['hotel_id'];
            }
            if(!empty($hotel_ids)){
                $hotel_id_str = join(',',$hotel_ids);
                $where .= " and sht.id in ({$hotel_id_str}) ";
            }
        }

        //城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];

        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $this->assign('pusera', $userinfo);
        }else {
            $where .= "	AND sht.area_id in ($pcity)";
        }
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $where .= " and sht.hotel_box_type in ({$hotel_box_type_str}) ";
        $field = 'sht.id hid, sht.name hname';
        $hotelModel = new \Admin\Model\HotelModel();
        $orders = 'convert(sht.name using gbk) asc';
        $result = $hotelModel->getHotelidByArea($where, $field, $orders);
        $msg = '';
        $res = array('code'=>1,'msg'=>$msg,'data'=>$result);
        echo json_encode($res);
    }
	/*
	 * 处理excel数据
	 */
	public function analyseExcel(){
		$path = $_POST['excelpath'];
		if  ($path == '') {
			$res = array('error'=>0,'message'=>array());
			echo json_encode($res);
		}
		$type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		vendor("PHPExcel.PHPExcel.IOFactory");
		if ($type == 'xlsx' || $type == 'xls') {
			$objPHPExcel = \PHPExcel_IOFactory::load($path);
		} elseif ($type == 'csv') {
			$objReader = \PHPExcel_IOFactory::createReader('CSV')
				->setDelimiter(',')
				->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
				->setEnclosure('"')
				->setLineEnding("\r\n")
				->setSheetIndex(0);
			$objPHPExcel = $objReader->load($path);
		} else {
			//$this->output('文件格式不正确', 'importdata', 0, 0);
			$res = array('error'=>1,'message'=>'文件格式不正确');
			echo json_encode($res);
			die;
		}

		$sheet = $objPHPExcel->getSheet(0);
		//获取行数与列数,注意列数需要转换
		$highestRowNum = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
		
		if($highestColumnNum != 2){
			$res = array('error'=>1,'message'=>'必须为两列');
			echo json_encode($res);
			die;
		}
		//取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
		$filed = array();
		for ($i = 0; $i < $highestColumnNum; $i++) {
			$cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
			$cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
			$filed[] = $cellVal;
		}
		if($filed[0] != 'id' || $filed[1] != 'name' ) {
			$res = array('error'=>1,'message'=>'第一行对应两列必须为id,name');
			echo json_encode($res);
			die;
		}
		//开始取出数据并存入数组
		$data = array();
		$hotel_str = '';
		$spx = '';
		for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
			$row = array();
			for ($j = 0; $j < $highestColumnNum; $j++) {
				$cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
				$cellVal = (string)$sheet->getCell($cellName)->getValue();
				if($cellVal === 'null'){
					$cellVal = '';
				}
				if($cellVal === '"' ||  $cellVal === "'"){
					$cellVal = '#';
				}
				if($cellVal === 'null'){
					$cellVal = '';
				}
				$row[$filed[$j]] = $cellVal;
			}
			$hotel_str .= $spx. $row['id'];
			$spx = ',';
			$data [] = $row;
		}
		$boxModel = new \Admin\Model\BoxModel();
		$hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
		
		$field = 'sht.id,sht.name';
        $hotelModel = new \Admin\Model\HotelModel();
        $where = " sht.id in(".$hotel_str.") and  sht.flag=0 and sht.state=1 and  sht.hotel_box_type in ({$hotel_box_type_str}) ";
        $data = $hotelModel->getHotelidByArea($where, $field);
		if(empty($data)){
			$res = array('error'=>2,'message'=>'导入酒楼数据异常');
			echo json_encode($res);
			die;
		}
		$box_nums = 0;
		foreach($data as $key=>$v){
			$field = 'count(distinct (b.id)) num';
			$where = 'b.state=1 and b.flag=0 and r.state=1 and
			r.flag=0 and h.state=1 and h.flag=0 ';
			$where .= ' and h.id = '. $v['id'];
			$b_arr = $boxModel->isHaveMac($field, $where);
			$res = empty($b_arr[0]['num'])?0:$b_arr[0]['num'];
			$box_nums += $res;
		}
		$res = array('error'=>0,'message'=>$data,'box_nums'=>$box_nums,'hotel_nums'=>count($data));
		echo json_encode($res);
		die;
	}
}
