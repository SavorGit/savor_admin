<?php
namespace Admin\Controller;
// use Common\Lib\SavorRedis;
/**
 * 网络节目单-节目列表
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
use Admin\Model\CategoModel;
use Admin\Model\MediaModel;
use Admin\Model\MenuHotelModel;
use Admin\Model\MenuListLogModel;
use Admin\Model\ProgramModel;
use Admin\Model\AdsModel;
use Admin\Model\MenuListModel;
use Admin\Model\MenuItemModel;
use Admin\Model\HotelModel;
use Admin\Model\AreaModel;
use Admin\Model\MenuListOpeModel;
use Common\Lib\SavorRedis;

class ProgrammenuController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getsessionHotel()
    {
        $get_hotel_arr = json_decode($_POST['seshot'], true);
        $key = 'select_programmenuhotel_key';
        $h_arr = empty(session($key)) ? array() : session($key);
        foreach ($get_hotel_arr as $tp => $tv) {
            if ($tv['type'] == 1) {
                $h_arr[$tv['id']] = 1;
            } else {
                unset($h_arr[$tv['id']]);
            }
        }
        session($key, $h_arr);
    }

    public function copynew()
    {
        $menuid = I('get.menuid', 0, 'int');
        $promenuliModel = new \Admin\Model\ProgramMenuListModel();
        $now_date = date('Y-m-d H:i:s');
        $field = 'creator_id, creator_name, menu_name';
        $map['id'] = $menuid;
        $info = $promenuliModel->getOne($field, $map);
        $info['state'] = 0;
        $info['update_time'] = $now_date;
        $info['create_time'] = $now_date;
        $info['count'] = 0;
        $old['menu_name'] = $info['menu_name'];
        
        $menulistModel = new \Admin\Model\MenuListModel();
        // 先添加旧节目单
        // 判断节目单名称是否存在
        $count_arr = $menulistModel->getWhere($old, '*');
        if (! empty($count_arr)) {
            $info['menu_name'] = $info['menu_name'] . '_' . time();
        }
        $res = $menulistModel->add($info);
        if ($res) {
            // 获取节目单信息
            $new_menu_id = $menulistModel->getLastInsID();
            $promItemModel = new \Admin\Model\ProgramMenuItemModel();
            $order = I('_order', 'a.id');
            $sort = I('_sort', 'asc');
            $orders = $order . ' ' . $sort;
            $where = "1=1";
            $field = "a.ads_name,a.ads_id,a.duration,a.sort_num,
            ads.create_time";
            $where .= " AND menu_id=$menuid  ";
            $menu_item_arr = $promItemModel->getAdInfoByAid($where, $orders, $field);
            foreach ($menu_item_arr as $rk => $rv) {
                $menu_item_arr[$rk]['update_time'] = $now_date;
                $menu_item_arr[$rk]['menu_id'] = $new_menu_id;
                if (empty($menu_item_arr[$rk]['create_time'])) {
                    $menu_item_arr[$rk]['create_time'] = $now_date;
                }
            }
            
            $menuItemModel = new \Admin\Model\MenuItemModel();
            $ret = $menuItemModel->addAll($menu_item_arr);
            if ($ret) {
                $this->output('复制到老节目单成功', 'menu/getlist');
            } else {
                $this->error('复制失败了请重新复制');
            }
        } else {
            $this->error('复制失败请重新复制');
        }
    }

    public function hotelconfirm()
    {
        $menu_id = I('menuid');
        $menu_name = I('menuname');
        // 2是新增
        $hoty = I('hopu');
        $ids = I('ids');
        $data = array();
        $arr = array();
        foreach ($ids as $k => $v) {
            $arr = explode('|', $v);
            $data[] = array(
                'hoid' => $arr[0],
                'honame' => $arr[1]
            );
        }
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $data);
        $this->assign('hoty', $hoty);
        $this->display('hotelconfirm');
    }

    public function publishMenu()
    {
        $putime = I('logtime');
        $is_small_app = I('is_small_app', '0', 'intval');
        
        if ($putime == '') {
            $putime = date("Y-m-d H:i:s");
        } else {
            $putime = $putime . ':00';
        }
        $now_date = date("Y-m-d H:i:s");
        if ($now_date > $putime) {
            $this->error('预约发布时间不可小于当前时间');
        }
        $menuid = I('post.menuid');
        $menuname = I('post.menuname');
        $hotel_id_arr = I('post.pubhotelhotel');
        if ($hotel_id_arr == '') {
            $this->error('酒楼选择不可为空');
        }
        
        $hotel_id_arr = explode(',', $hotel_id_arr);
        $hotelModel = new HotelModel();
        $menuHoModel = new \Admin\Model\ProgramMenuHotelModel();
        $menuliModel = new \Admin\Model\ProgramMenuListModel();
        $hotel_name = array();
        foreach ($hotel_id_arr as $hv) {
            $h_name = $hotelModel->find($hv);
            $hotel_name[] = $h_name['name'];
        }
        // savor_newmenu_pub
        $userInfo = session('sysUserInfo');
        $menuHoModel->startTrans();
        $pu_unix = strtotime($putime);
        $com_arr = array_combine($hotel_id_arr, $hotel_name);
        //如果该酒楼在虚拟小平台 通知更新虚拟小平台该酒楼的节目单、宣传片
        
        $tmp_hotel_arr = getVsmallHotelList();
        $redis = new SavorRedis();
        $redis->select(12);
        foreach ($com_arr as $k => $v) {
            
            $data[] = array(
                'hotel_id' => $k,
                'create_time' => $now_date,
                'update_time' => $now_date,
                'pub_time' => $putime,
                'operator_id' => $userInfo['id'],
                'menu_id' => $menuid
            );
            
        }
        // 插入savor_menu_hotel
        $res = $menuHoModel->addAll($data);
        if ($res) {
            // 更新menulist表
            $hotel_count = count($hotel_id_arr);
            if ($hotel_count > 0) {
                $dat['hotel_num'] = $hotel_count;
                $dat['state'] = 1;
                $dat['id'] = $menuid;
                $dat['menu_num'] = $pu_unix . $menuid;
                $dat['is_small_app'] = $is_small_app;
            } else {
                $dat['state'] = 0;
                $dat['hotel_num'] = 0;
                $dat['id'] = $menuid;
                $dat['is_small_app'] = 0;
            }
            $res = $menuliModel->addData($dat, 1);
            if ($res) {
                $menuHoModel->commit();
                $vm_hotel_arr = array();
                
                foreach ($com_arr as $k => $v) {
                    $cache_key = C('PROGRAM_PRO_CACHE_PRE') . $k;
                    $redis->remove($cache_key);
                    $cache_key = C('PROGRAM_ADV_CACHE_PRE') . $k;
                    $redis->remove($cache_key);
                    if(in_array($k, $tmp_hotel_arr)){
                        $vm_hotel_arr[] = $k;
                        //sendTopicMessage($k, 6);  //通知虚拟小平台更新节目单数据
                        //sendTopicMessage($k, 7);  //通知虚拟小平台更新节目单宣传片数据
                    }
                }
                //sendTopicMessage($vm_hotel_arr, 6); //通知虚拟小平台更新节目单数据
                //sendTopicMessage($vm_hotel_arr, 7); //通知虚拟小平台更新节目单宣传片数据
                //新虚拟小平台接口
                $redis->select(10);
                
                /*$v_hotel_list_key = C('VSMALL_HOTELLIST');
                $redis_result = $redis->get($v_hotel_list_key);
                $v_hotel_list = json_decode($redis_result,true);
                $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id*/
                
                
                
                
                
                $v_pro_key = C('VSMALL_PRO');
                $v_adv_key = C('VSMALL_ADV');
                
                
                //新修改
                $ck = $v_pro_key.'*';
                $rts = $redis->keys($ck);
                $v_hotel_arr = [];
                foreach($rts as $k=>$v){
                    $tmp = explode(':',$v);
                    if(!in_array($tmp[2],$v_hotel_arr)){
                
                        $v_hotel_arr[] = $tmp[2];
                    }
                }
                
                
                
                foreach($com_arr as $k=>$v){
                    if(in_array($k, $v_hotel_arr)){
                        $keys_arr = $redis->keys($v_pro_key.$k."*");
                        foreach($keys_arr as $vv){
                            $redis->remove($vv);
                        }
                        $keys_arr = $redis->keys($v_adv_key.$k."*");
                        foreach($keys_arr as $vv){
                            $redis->remove($vv);   
                        }
                    }
                }
                
                $this->output('发布成功了!', 'programmenu/getlist');
            } else {
                $menuHoModel->rollback();
                $this->error('发布失败了!');
            }
        } else {
            $menuHoModel->rollback();
            $this->error('发布失败了!');
        }
    }

    public function selectgoods(){
        $menuid = I('menuid',0,'intval');
        $menuname = I('menuname','');
        $m_programitem = new \Admin\Model\ProgramMenuItemModel();
        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        if(IS_GET){
            $where = array('menu_id'=>$menuid,'type'=>4);
            $order = 'location_id asc';
            $res_item = $m_programitem->getWhere($where,$order,'*');
            if(empty($res_item)){
                $res_item = array();
            }
            $gwhere = array('status'=>1,'type'=>22);
            $gwhere['gtype'] = array('in',array(1,2));
            $gwhere['tv_media_id'] = array('gt',0);
            $goods = $m_goods->getDataList('*',$gwhere,'id desc');

            $this->assign('menu_items',$res_item);
            $this->assign('goods',$goods);
            $this->assign('menuid',$menuid);
            $this->assign('menuname',$menuname);
            $this->display('selectgoods');
        }else{
            $item_ids = I('post.item_id','');
            $ads_ids = I('post.ads_id','');
            $durations = I('post.duration','');
            $is_modify = 0;
            $m_media = new \Admin\Model\MediaModel();
            foreach ($item_ids as $k=>$v){
                $id = intval($v);
                if($id){
                    $duration = $durations[$k];
                    $ads_id = $ads_ids[$k];
                    if($ads_id && $duration==0){
                        $res_goods = $m_goods->getInfo(array('id'=>$ads_id));
                        if(!empty($res_goods['tv_media_id'])){
                            $res_media = $m_media->getMediaInfoById($res_goods['tv_media_id']);
                            $duration = $res_media['duration'];
                        }
                    }
                    if($ads_id && $duration<5){
                        $ads_num = $k+1;
                        $this->output("商品广告位{$ads_num}-请输入大于5秒的播放时长",'programmenu/getlist',2,0);
                    }
                    if($ads_id==0){
                        $duration=0;
                    }
                    $res = $m_programitem->updateData(array('id'=>$id),array('ads_id'=>$ads_id,'duration'=>$duration));
                    if($res){
                        $is_modify = 1;
                    }
                }
            }
            if($is_modify){
                $where = array('menu_id'=>$menuid,'type'=>4);
                $order = 'id desc';
                $res_item = $m_programitem->getWhere($where,$order,'*');
                $goods_ids = array();
                foreach ($res_item as $v){
                    if(!in_array($v['ads_id'],$goods_ids)){
                        $goods_ids[]=$v['ads_id'];
                    }
                }

                $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
                $gwhere = array('id'=>array('in',$goods_ids));
                $res_goods = $m_goods->getDataList('*',$gwhere,'id desc');
                $goods = array();
                foreach ($res_goods as $v){
                    $goods[$v['id']] = array('id'=>$v['id'],'tv_media_id'=>$v['tv_media_id'],'status'=>$v['status']);
                }

                $key = C('SAPP_SHOP_PROGRAM');
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(2);
                $program_goods_key = $key.":$menuid:goods";
                $redis->set($program_goods_key,json_encode($goods),30*86400);

                $program_period_key = $key.":$menuid:period";
                $period = getMillisecond();
                $period_data = array('period'=>$period,'time'=>date('Y-m-d H:i:s'));
                $redis->set($program_period_key,json_encode($period_data),30*86400);
            }
            $this->output('操作成功','programmenu/getlist');

        }

    }

    public function getdetail()
    {
        $id = I('get.id' . '');
        $menu_name = I('get.name' . '');
        if ($id) {
            $mItemModel = new \Admin\Model\ProgramMenuItemModel();
            $order = I('_order', 'id');
            $sort = I('_sort', 'asc');
            $orders = $order . ' ' . $sort;
            $where = "1=1";
            $field = "ads_name,ads_id,duration,sort_num";
            $where .= " AND menu_id={$id}  ";
            $res = $mItemModel->getWhere($where, $orders, $field);
            $this->assign('list', $res);
        } else {
            $this->display('getdetailmenu');
        }
        $this->assign('men_name', $menu_name);
        $this->display('getdetailmenu');
    }

    public function getfile()
    {
        $upload = new \Think\Upload();
        $upload->exts = array(
            'xls',
            'xlsx',
            'xlsm',
            'csv'
        );
        $upload->maxSize = 2097152;
        $upload->rootPath = $this->imgup_path();
        $upload->savePath = '';
        $info = $upload->upload();
        // var_dump($info);
        
        if (empty($info['file_data'])) {
            $errMsg = $upload->getError();
            $this->output($errMsg, 'importdata', 0, 0);
        }
        $path = SITE_TP_PATH . '/Public/uploads/' . $info['file_data']['savepath'] . $info['file_data']['savename'];
        vendor("PHPExcel.PHPExcel.IOFactory");
        // echo $path;
        $ret[] = $path;
        echo json_encode($ret);
        die();
    }

    public function gethotelmanager()
    {
        // //var_dump($_POST);
        $hotelModel = new HotelModel();
        $areaModel = new AreaModel();
        
        $size = I('numPerPage', 50); // 显示每页记录数
        $this->assign('numPerPage', $size);
        $start = I('pageNum', 1);
        $this->assign('pageNum', $start);
        $order = I('_order', 'id');
        $this->assign('_order', $order);
        $sort = I('_sort', 'desc');
        $this->assign('_sort', $sort);
        $orders = $order . ' ' . $sort;
        $start = ($start - 1) * $size;
        
        $where = "1=1";
        
        $name = I('name');
        
        if ($name) {
            $this->assign('name', $name);
            $where .= "	AND name LIKE '%{$name}%'";
        }
        
        $result = $hotelModel->getList($where, $orders, $start, $size);
        
        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        // print_r($result);die;
        $this->assign('list', $result['list']);
        $this->assign('page', $result['page']);
        $this->display('index');
    }
 // End Function
    public function selectHotel()
    {
        $areaModel = new AreaModel();
        $menliModel = new \Admin\Model\ProgramMenuListModel();
        // 城市
        $area_arr = $areaModel->getAllArea();
        
        $this->assign('area', $area_arr);
        
        $menu_id = I('menuid');
        $prHoModel = new \Admin\Model\ProgramMenuHotelModel();
        $nums = $prHoModel->countWhere(array(
            'menu_id' => $menu_id
        ));
        if ($nums > 0) {
            $this->error('该节目单已选择了酒楼，不能重复选择');
        }
        
        $menu_name = I('menuname');
        $hotelModel = new HotelModel();
        $areaModel = new AreaModel();
        $size = I('numPerPage', 50); // 显示每页记录数
        $this->assign('numPerPage', $size);
        $start = I('pageNum', 1);
        $this->assign('pageNum', $start);
        $order = I('_order', 'id');
        $this->assign('_order', $order);
        $sort = I('_sort', 'desc');
        $this->assign('_sort', $sort);
        $orders = $order . ' ' . $sort;
        $start = ($start - 1) * $size;
        $where = "1=1";
        $name = I('name');
        $beg_time = I('starttime', '');
        $end_time = I('endtime', '');
        $this->assign('sttime', $beg_time);
        $this->assign('sendime', $end_time);
        if ($beg_time)
            $where .= " AND install_date>='$beg_time'";
        if ($end_time)
            $where .= " AND install_date<='$end_time'";
        if ($name) {
            $this->assign('name', $name);
            $where .= "	AND name LIKE '%{$name}%' ";
        }
        $hbt_v = I('hbt_v');
        if ($hbt_v) {
            $this->assign('hbt_k', $hbt_v);
            $where .= "	AND hotel_box_type = $hbt_v";
        }
        
        // 城市
        /*
         * $area_v = I('area_v');
         * if ($area_v) {
         * $this->assign('area_k',$area_v);
         * if($area_v == 9999){
         * }else{
         * $where .= " AND area_id = $area_v";
         * }
         * }
         */
        $include_a = I('include_a');
        $area_strs = '';
        $space = '';
        $include_ak = array();
        if (! empty($include_a)) {
            foreach ($include_a as $key => $v) {
                
                $area_strs .= $space . $v;
                $space = ',';
                $include_ak[] = $v;
            }
            if ($area_strs)
                $where .= " AND area_id in($area_strs)";
            $this->assign('include_ak', $include_ak);
        }
        // 级别
        $level_v = I('level_v');
        if ($level_v) {
            $this->assign('level_k', $level_v);
            $where .= "	AND level = $level_v";
        }
        // 状态
        $state_v = I('state_v');
        if ($state_v) {
            $this->assign('state_k', state_v);
            $where .= "	AND state = $state_v";
        }
        // 重点
        $key_v = I('key_v');
        if ($key_v) {
            $this->assign('key_k', $key_v);
            $where .= "	AND iskey = $key_v";
        }
        // 城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        $is_city_search = 0;
        if ($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $pawhere = '1=1';
            $is_city_search = 1;
            $this->assign('is_city_search', $is_city_search);
            $this->assign('pusera', $userinfo);
        } else {
            $this->assign('is_city_search', $is_city_search);
            $where .= "	AND area_id in ($pcity)";
            $pawhere = '1=1 and area_id = ' . $pcity;
        }
        $hotel_box_types = getHeartBoXtypeIds(2);
        $where .= " and hotel_box_type in ($hotel_box_types) and state=1 and flag=0 ";
        
        $pafield = 'DISTINCT smh.menu_id id,
smlist.menu_name';
        $men_arr = $prHoModel->getPrvMenu($pafield, $pawhere);
        
        // 获取包含有该地区酒楼
        $this->assign('include', $men_arr);
        
        // 包含
        $include_v = I('include_v');
        // 获取节目单对应hotelid
        if ($include_v) {
            // 取部分包含节目单
            $bak_ho_arr = array();
            foreach ($include_v as $iv) {
                $sql = "SELECT hotel_id FROM `savor_programmenu_hotel`  WHERE menu_id={$iv}";
                $bak_hotel_id_arr = $menliModel->query($sql);
                foreach ($bak_hotel_id_arr as $bk => $bv) {
                    $bak_ho_arr[] = $bv['hotel_id'];
                }
            }
            $bak_ho_arr = array_unique($bak_ho_arr);
            $bak_ho_str = implode(',', $bak_ho_arr);
            if ($bak_ho_str) {
                $where .= "	AND id  in ($bak_ho_str)";
            } else {
                $where .= "	AND id  in ('')";
            }
            $this->assign('include_k', $include_v);
        } else {
            $exc_v = I('exc_v');
            if ($exc_v) {
                $bak_ho_arr_p = array();
                foreach ($exc_v as $iv) {
                    $sql = "SELECT hotel_id FROM `savor_programmenu_hotel` WHERE menu_id={$iv}";
                    $bak_hotel_id_arr = $menliModel->query($sql);
                    foreach ($bak_hotel_id_arr as $bk => $bv) {
                        $bak_ho_arr_p[] = $bv['hotel_id'];
                    }
                }
                $bak_ho_arr_p = array_unique($bak_ho_arr_p);
                $bak_ho_str = implode(',', $bak_ho_arr_p);
                if ($bak_ho_str) {
                    $where .= "	AND id not in ($bak_ho_str)";
                }
            } else {}
        }
        
        $result = $hotelModel->getList($where, $orders, $start, $size);
        
        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        // print_r($result);die;
        $hotel_box_type = C('hotel_box_type');
        $hotel_box_type = array(
            '2' => '二代网络版',
            '3' => '二代5G版',
            '6' => '三代网络版'
        );
        $this->assign('h_box_type', $hotel_box_type);
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('alist', $result['list']);
        $this->assign('page', $result['page']);
        $this->display('selecthotel');
    }

    public function getHotelInfo()
    {
        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $data = array();
        $mhotelModel = new \Admin\Model\ProgramMenuHotelModel();
        $where = array(
            'nh.menu_id' => $menu_id
        );
        $field = 'nh.pub_time, nh.hotel_id hoid, nho.name honame';
        $data = $mhotelModel->getMenuHotelPub($where, $field);
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $data);
        $this->display('gethotelinfo');
    }

    public function manager()
    {
        // 实例化redis
        // $redis = SavorRedis::getInstance();
        // $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function getlist()
    {
        $mlModel = new \Admin\Model\ProgramMenuListModel();
        $size = I('numPerPage', 50); // 显示每页记录数
        $this->assign('numPerPage', $size);
        $start = I('pageNum', 1);
        $this->assign('pageNum', $start);
        $order = I('_order', 'update_time');
        $this->assign('_order', $order);
        $sort = I('_sort', 'desc');
        $this->assign('_sort', $sort);
        $orders = $order . ' ' . $sort;
        $start = ($start - 1) * $size;
        
        $where = "1=1";
        $name = I('titlename');
        $beg_time = I('starttime', '');
        $end_time = I('end_time', '');
        if ($beg_time)
            $where .= " AND a.create_time>='$beg_time 00:00:00'";
        if ($end_time)
            $where .= " AND a.create_time<='$end_time 23:59:59'";
        if ($name) {
            $this->assign('name', $name);
            $where .= "	AND a.menu_name LIKE '%{$name}%' ";
        }
        $userinfo = session('sysUserInfo');
        $area_city = $userinfo['area_city'];
        if ($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {} else {
            $where .= " and sysgroup.area_city=$area_city";
        }
        $result = $mlModel->getList($where, $orders, $start, $size);
        $datalist = $result['list'];
        if(!empty($datalist)){
            $m_programitem = new \Admin\Model\ProgramMenuItemModel();
            foreach ($datalist as $k=>$v){
                $menu_id = $v['id'];
                $where = array('menu_id'=>$menu_id,'type'=>4);
                $where['ads_id'] = array('gt',0);
                $order = 'id desc';
                $field = 'count(id) as num';
                $res_item = $m_programitem->getAll($field,$where,0,1,$order,'');
                $item_gnum = 0;
                if(!empty($res_item)){
                    $item_gnum = $res_item[0]['num'];
                }
                $datalist[$k]['item_gnum'] = $item_gnum;
            }
        }
        $this->assign('list', $datalist);
        $this->assign('page', $result['page']);
        
        $this->display('getlist');
    }

    public function judgeAdvOuc($name_arr)
    {
        $result = array();
        $result = $this->getAdsOccup($result);
        $adv_arr = array_column($result, 'name');
        $len = count($adv_arr);
        // 判断要有10个
        if (array_diff($adv_arr, $name_arr)) {
            $this->error("广告位必须选择{$len}个");
        }
        // 取广告位数组
        $ad_arr = array_filter($name_arr, function ($result, $item) use($adv_arr)
        {
            if (in_array($result, $adv_arr)) {
                return true;
            } else {
                return false;
            }
        });
        // 判断恰好10个,取广告位数组反转然后比较
        if (count($ad_arr) != $len) {
            $this->error("广告位必须选择{$len}个且不能有重复");
        }
    }

    public function judgegoodsAdvOuc($name_arr)
    {
        $result = array();
        $result = $this->getGoodsadsOccup($result);
        $adv_arr = array_column($result, 'name');
        $len = count($adv_arr);
        // 判断要有10个
        if (array_diff($adv_arr, $name_arr)) {
            $this->error("商品广告位必须选择{$len}个");
        }
        // 取广告位数组
        $ad_arr = array_filter($name_arr, function ($result, $item) use($adv_arr)
        {
            if (in_array($result, $adv_arr)) {
                return true;
            } else {
                return false;
            }
        });
        // 判断恰好10个,取广告位数组反转然后比较
        if (count($ad_arr) != $len) {
            $this->error("商品广告位必须选择{$len}个且不能有重复");
        }
    }

    public function judgeActivityGoodsAdvOuc($name_arr)
    {
        return true;
        $result = array();
        $result = $this->getActivityGoodsOccup($result);
        $adv_arr = array_column($result, 'name');
        $len = count($adv_arr);
        // 判断要有10个
        if (array_diff($adv_arr, $name_arr)) {
            $this->error("活动商品广告位必须选择{$len}个");
        }
        // 取广告位数组
        $ad_arr = array_filter($name_arr, function ($result, $item) use($adv_arr)
        {
            if (in_array($result, $adv_arr)) {
                return true;
            } else {
                return false;
            }
        });
        // 判断恰好10个,取广告位数组反转然后比较
        if (count($ad_arr) != $len) {
            $this->error("活动商品广告位必须选择{$len}个且不能有重复");
        }
    }

    public function judgePolyScreenOuc($name_arr)
    {
        $result = array();
        $result = $this->getPolyScreenOccup($result);
        $adv_arr = array_column($result, 'name');
        $len = count($adv_arr);
        // 判断要有10个
        if (array_diff($adv_arr, $name_arr)) {
            $this->error("聚屏广告位必须选择{$len}个");
        }
        // 取广告位数组
        $ad_arr = array_filter($name_arr, function ($result, $item) use($adv_arr)
        {
            if (in_array($result, $adv_arr)) {
                return true;
            } else {
                return false;
            }
        });
        // 判断恰好10个,取广告位数组反转然后比较
        if (count($ad_arr) != $len) {
            $this->error("商品广告位必须选择{$len}个且不能有重复");
        }
    }

    public function doaddnewMenu()
    {
        // 表单提交即是新增和导入ajax区分以及与修改进行区分
        $now_date = date('Y-m-d H:i:s');
        $id = I('post.id', '');
        // 添加到menu_list 表
        $mlModel = new \Admin\Model\ProgramMenuListModel();
        $mlModel->startTrans();
        $mItemModel = new \Admin\Model\ProgramMenuItemModel();
        $save = array();
        $userInfo = session('sysUserInfo');
        $save['creator_name'] = $userInfo['username'];
        $save['creator_id'] = $userInfo['id'];
        $save['state'] = 0;
        $id_arr = explode(',', substr(I('post.rightid', ''), 0, - 1));
        $dura_arr = explode(',', substr(I('post.rightdur', ''), 0, - 1));
        $name_arr = explode(',', substr(I('post.rightname', ''), 0, - 1));
        $time_arr = explode(',', substr(I('post.rightime', ''), 0, - 1));
        $co_arr = $id_arr;
        // 判断名字是否存在
        $save['update_time'] = $now_date;
        $save['create_time'] = $now_date;
        $save['menu_name'] = I('post.program', '', 'trim');
        $count = $mlModel->where(array('menu_name' => $save['menu_name']))->count();
        if ($count) {
            $this->error('节目单名称已存在!');
        }
        $rightid_arr = I('post.rightid', '');
        if (empty($rightid_arr)) {
            $this->error('节目单列表不能为空!');
        }
        // 判断广告位版位都有10个,
        $this->judgeAdvOuc($name_arr);

        // 判断商品广告位版位都有18个,
        $this->judgegoodsAdvOuc($name_arr);

        // 判断活动商品广告位版位都有10个,
        $this->judgeActivityGoodsAdvOuc($name_arr);
        // 判断聚屏广告位都有50个
        $this->judgePolyScreenOuc($name_arr);
        $result = $mlModel->add($save);
        if ($result) {
            $menu_id = $mlModel->getLastInsID();
            // 将内容添加到savor_menu_item表
            $data = array();
            $i = 1;
            $res = array();
            // 宣传片
            $res_xuan = $this->getAdsAcccounce($res);
            // 获取广告占位符
            $res_adv = $this->getAdsOccup($res);
            // 获取rtb广告占位符
            $rertb_adv = $this->getGoodsadsOccup($res);
            // 获取聚屏广告位占位符
            $poly_adv = $this->getPolyScreenOccup($res);

            // 获取活动商品广告占位符
            $res_activitygoods_adv = $this->getActivityGoodsOccup($res);

            // 获取活动商品广告占位符
            $res_selectcontent_adv = $this->getSelectcontentOccup($res);

            // 取出name列
            $res_adv = array_column($res_adv, 'name');
            $res_xuan = array_column($res_xuan, 'name');
            $rertb_adv = array_column($rertb_adv, 'name');
            $poly_adv = array_column($poly_adv, 'name');
            $activitygoods_adv = array_column($res_activitygoods_adv, 'name');
            $selectcontent_adv = array_column($res_selectcontent_adv, 'name');

            $adv_promote_num_arr = C('ADVE_OCCU');
            $adv_name = $adv_promote_num_arr['name'];
            $rtbadv_promote_num_arr = C('GOODSADVE_OCCU');
            $rtbadv_name = $rtbadv_promote_num_arr['name'];
            
            $polyadv_promote_num_arr = C('POLY_SCREEN_OCCU');
            $polyadv_name = $polyadv_promote_num_arr['name'];

            $actgadv_promote_num_arr = C('ACTIVITY_GOODS_OCCU');
            $activitygoodsadv_name = $actgadv_promote_num_arr['name'];

            $selectcontent_promote_num_arr = C('SELECTCONTENT_GOODS_OCCU');
            $selectcontentadv_name = $selectcontent_promote_num_arr['name'];


            foreach ($id_arr as $k => $v) {
                // 判断type类型 1广告位 2节目 3宣传片 4rtb广告 5聚屏广告位 6活动商品广告位
                $ad_name = $name_arr[$k];
                if (in_array($ad_name, $res_adv)) {
                    $type = 1;//广告位
                    $lo = str_replace($adv_name, "", $ad_name);
                } elseif(in_array($ad_name, $rertb_adv)) {
                    $type = 4;//rtb广告
                    $lo = str_replace($rtbadv_name, "", $ad_name);
                }elseif (in_array($ad_name, $res_xuan)) {
                    $type = 3;//宣传片
                    $lo = 0;
                } elseif (in_array($ad_name, $poly_adv)) {
                    $type = 5;//聚屏广告位
                    $lo = str_replace($polyadv_name, "", $ad_name);
                } elseif (in_array($ad_name, $activitygoods_adv)) {
                    $type = 6;//活动商品广告位
                    $lo = str_replace($activitygoodsadv_name, "", $ad_name);
                } elseif (in_array($ad_name, $selectcontent_adv)) {
                    $type = 7;//精选内容广告位
                    $lo = str_replace($selectcontentadv_name, "", $ad_name);
                } else {
                    $type = 2;//节目
                    $lo = 0;
                }
                $data[] = array(
                    'ads_id' => $v,
                    'ads_name' => $ad_name,
                    'menu_id' => $menu_id,
                    'type' => $type,
                    'sort_num' => $i,
                    'location_id' => $lo,
                    'duration' => $dura_arr[$k]
                );
                $i ++;
            }
            $res = $mItemModel->addAll($data);
            if ($res) {
                $mlModel->commit();
                $this->output('新增成功', 'programmenu/getlist');
            } else {
                $mlModel->rollback();
                $this->error('新增失败');
            }
        } else {
            $mlModel->rollback();
            $this->error('新增失败');
        }
    }

    /*
     * 处理excel数据
     */
    public function analyseExcel()
    {
        $adsModel = new \Admin\Model\AdsModel();
        $path = $_POST['excelpath'];
        if ($path == '') {
            $res = array(
                'error' => 0,
                'message' => array()
            );
            echo json_encode($res);
        }
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        vendor("PHPExcel.PHPExcel.IOFactory");
        
        if ($type == 'xlsx' || $type == 'xls') {
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
        } elseif ($type == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')->setDelimiter(',')
                ->setInputEncoding('GBK')
                -> // 不设置将导致中文列内容返回boolean(false)或乱码
setEnclosure('"')
                ->setLineEnding("\r\n")
                ->setSheetIndex(0);
            $objPHPExcel = $objReader->load($path);
        } else {
            $this->output('文件格式不正确', 'importdata', 0, 0);
        }
        
        $sheet = $objPHPExcel->getSheet(0);
        // 获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        // 只有一列所以写死
        // 取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        $data = array();
        for ($i = 1; $i <= $highestRowNum; $i ++) { // ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j ++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = (string) $sheet->getCell($cellName)->getValue();
                if (! empty($cellVal) && stristr($cellName, 'A')) {
                    $row[] = $cellVal;
                }
            }
            if ($row) {
                $data[] = $row;
            }
        }
        $remove_arr = array();
        $inc_arr = array();
        // 获取宣传片
        $result = array();
        $result = $this->getAdsAcccounce($result);
        // 获取广告占位符
        $result_adsoc = $this->getAdsOccup($result);
        
        // 获取RTB广告占位符
        $result_rtbadsoc = $this->getGoodsadsOccup($result);
        // 取出name列
        $xuan_arr = array_column($result, 'name');
        $adsoc_arr = array_column($result_adsoc, 'name');
        $rtbadsoc_arr = array_column($result_rtbadsoc, 'name');
        $now_date = date("Y-m-d H:i:s");
        foreach ($data as $rk => $rv) {
            if (in_array($rv[0], $xuan_arr)) {
                $inc_arr[] = array(
                    'id' => 0,
                    'name' => $rv[0],
                    'duration' => 0,
                    'create_time' => $now_date
                );
            } elseif (in_array($rv[0], $adsoc_arr)) {
                $inc_arr[] = array(
                    'id' => 0,
                    'name' => $rv[0],
                    'duration' => 0,
                    'create_time' => $now_date,
                    'type' => '33'
                );
            } elseif (in_array($rv[0], $rtbadsoc_arr)) {
                $inc_arr[] = array(
                    'id' => 0,
                    'name' => $rv[0],
                    'duration' => 0,
                    'create_time' => $now_date,
                    'type' => '33'
                );
            } else {
                $res = $adsModel->where(array(
                    'name' => $rv[0]
                ))->find();
                if ($res['type'] == 1) {
                    $remove_arr[] = $rv[0];
                } else {
                    if ($res) {
                        $inc_arr[] = array(
                            'id' => $res['id'],
                            'name' => $res['name'],
                            'duration' => $res['duration'],
                            'create_time' => $res['create_time']
                        );
                    } else {
                        $remove_arr[] = $rv[0];
                    }
                }
            }
        }
        if ($remove_arr) {
            $res = array(
                'error' => 1,
                'nomessage' => $remove_arr,
                'message' => $inc_arr
            );
        } else {
            $res = array(
                'error' => 0,
                'message' => $inc_arr
            );
        }
        // ob_clean();
        echo json_encode($res);
    }

    public function addnewmenu()
    {
        
        // 左边表单提交，右边表单提交，导入ajax,id修改
        $userInfo = session('sysUserInfo');
        $menu_name = I('get.name' . '');
        $type = I('type');
        // 修改节目单
        if ($type == 2) {
            $menuid = I('id', '0');
            if ($menuid) {
                $mItemModel = new \Admin\Model\ProgramMenuItemModel();
                $order = I('_order', 'spi.id');
                $sort = I('_sort', 'asc');
                $orders = $order . ' ' . $sort;
                $where = "1=1";
                $field = "spi.ads_name,spi.ads_id,spi.duration,spi.sort_num,sads.create_time";
                $where .= " AND spi.menu_id={$menuid}  ";
                $res = $mItemModel->getCopyMenuInfo($where, $order, $field);
                
                // 获取广告占位符
                $result_adsoc = $this->getAdsOccup();
                $adsoc_arr = array_column($result_adsoc, 'name');
                $adsoc_arr = array_flip($adsoc_arr);
                array_walk($res, function (&$v, $k) use($adsoc_arr)
                {
                    if (empty($v['create_time'])) {
                        $v['create_time'] = '无';
                    }
                    if (array_key_exists($v['ads_name'], $adsoc_arr)) {
                        $v['type'] = 33;
                    }
                });
                $this->assign('menuid', $menuid);
                // 判断是新增
                $pct = I('pctype', '0');
                $this->assign('list', $res);
                if ($pct == 1) {
                    $this->assign('menuname', '');
                    $this->assign('menuid', '');
                    $this->display('copynewmenu');
                }
            }
        } else {
            $this->display('addnewmenu');
        }
    }

    public function addtest()
    {
        $this->output('操作成功', 'menu/getlist');
    }

    public function getAdsAcccounce($result)
    {
        $adv_promote_num_arr = C('ADV_VIDEO');
        $adv_promote_num = $adv_promote_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 0; $i <= $adv_promote_num; $i ++) {
            if ($i == 0) {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_promote_num_arr['name'][0],
                    'create_time' => $now_date_time,
                    'duration' => 0
                );
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $i . $adv_promote_num_arr['name'][1],
                    'create_time' => $now_date_time,
                    'duration' => 0
                );
            }
        }
        return $result;
    }

    public function getAdsOccup($result, $filter = '')
    {
        $adv_promote_num_arr = C('ADVE_OCCU');
        if ($filter) {
            $filter_arr = explode(',', $filter);
        }
        $adv_promote_num = $adv_promote_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 1; $i <= $adv_promote_num; $i ++) {
            if (in_array($adv_promote_num_arr['name'] . $i, $filter_arr)) {
                continue;
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_promote_num_arr['name'] . $i,
                    'create_time' => $now_date_time,
                    'duration' => 0,
                    'type' => '33'
                );
            }
        }
        return $result;
    }

    public function getGoodsadsOccup($result, $filter = '')
    {
        $adv_promote_num_arr = C('GOODSADVE_OCCU');
        if ($filter) {
            $filter_arr = explode(',', $filter);
        }
        $adv_promote_num = $adv_promote_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 1; $i <= $adv_promote_num; $i ++) {
            if (in_array($adv_promote_num_arr['name'] . $i, $filter_arr)) {
                continue;
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_promote_num_arr['name'] . $i,
                    'create_time' => $now_date_time,
                    'duration' => 0,
                    'type' => '33'
                );
            }
        }
        return $result;
    }

    /**
     * 获取聚屏类广告位
     */
    public function getPolyScreenOccup($result, $filter = '')
    {
        $adv_promote_num_arr = C('POLY_SCREEN_OCCU');
        if ($filter) {
            $filter_arr = explode(',', $filter);
        }
        $adv_promote_num = $adv_promote_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 1; $i <= $adv_promote_num; $i ++) {
            if (in_array($adv_promote_num_arr['name'] . $i, $filter_arr)) {
                continue;
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_promote_num_arr['name'] . $i,
                    'create_time' => $now_date_time,
                    'duration' => 0,
                    'type' => '33'
                );
            }
        }
        return $result;
    }

    /**
     * 获取活动商品广告位
     */
    public function getActivityGoodsOccup($result, $filter = '')
    {
        $adv_activitygoods_num_arr = C('ACTIVITY_GOODS_OCCU');
        if ($filter) {
            $filter_arr = explode(',', $filter);
        }
        $adv_activitygoods_num = $adv_activitygoods_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 1; $i <= $adv_activitygoods_num; $i ++) {
            if (in_array($adv_activitygoods_num_arr['name'] . $i, $filter_arr)) {
                continue;
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_activitygoods_num_arr['name'] . $i,
                    'create_time' => $now_date_time,
                    'duration' => 0,
                    'type' => '33'
                );
            }
        }
        return $result;
    }

    /**
     * 获取精选内容广告位
     */
    public function getSelectcontentOccup($result, $filter = '')
    {
        $adv_selectcontent_num_arr = C('SELECTCONTENT_GOODS_OCCU');
        if ($filter) {
            $filter_arr = explode(',', $filter);
        }
        $adv_selectcontent_num = $adv_selectcontent_num_arr['num'];
        $now_date_time = date("Y-m-d H:i:s");
        for ($i = 1; $i <= $adv_selectcontent_num; $i ++) {
            if (in_array($adv_selectcontent_num_arr['name'] . $i, $filter_arr)) {
                continue;
            } else {
                $result[] = array(
                    'id' => 0,
                    'name' => $adv_selectcontent_num_arr['name'] . $i,
                    'create_time' => $now_date_time,
                    'duration' => 0,
                    'type' => '33'
                );
            }
        }
        return $result;
    }

    public function get_se_left()
    {
        $m_type = I('post.m_type', '0');
        
        $where = "1=1";
        $field = "id,name,media_id,create_time,resource_type,duration";
        $searchtitle = I('post.searchtitle', '');
        $beg_time = I('starttime', '');
        $end_time = I('endtime', '');
        // 广告位
        $adval = I('adval', '');
        
        if ($beg_time)
            $where .= " AND create_time>='$beg_time'";
        if ($end_time) {
            $end_time = date("Y-m-d", strtotime($end_time . "+1 day"));
            $where .= " AND create_time<'$end_time'";
        }
        
        $where .= " AND state=1 ";
        if ($searchtitle) {
            $where .= "	AND name LIKE '%{$searchtitle}%'";
        }
        $adModel = new AdsModel();
        $result = array();
        switch ($m_type){
            case 0:
                $where .= "	AND (`type`) = 2 ";
                $result = $adModel->getWhere($where, $field,'create_time desc');
                // 获取宣传片
                $result = $this->getAdsAcccounce($result);
                // 获取广告占位符
                $result = $this->getAdsOccup($result, $adval);

                $result = $this->getActivityGoodsOccup($result);

                $result = $this->getSelectcontentOccup($result);
                break;
            case 3:
                // 获取宣传片
                $result = $this->getAdsAcccounce($result);
                break;
            case 4:
                // 获取广告占位符
                $result = $this->getAdsOccup($result, $adval);
                break;
            case 5:
                // 获取商品广告占位符
                $result = $this->getGoodsadsOccup($result, $adval);
                break;
            case 6:
                $result = $this->getPolyScreenOccup($result, $adval);
                break;
            case 7:
                $result = $this->getActivityGoodsOccup($result);
                break;
            case 8:
                $result = $this->getSelectcontentOccup($result);
                break;
            default:
                $where .= "	AND type = '{$m_type}'";
                $result = $adModel->getWhere($where, $field);
        }
        echo json_encode($result);
        die();
    }
}