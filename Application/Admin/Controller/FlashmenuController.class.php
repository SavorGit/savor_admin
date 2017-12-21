<?php
namespace Admin\Controller;
    // use Common\Lib\SavorRedis;
/**
 * @desc U盘节目单
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\HotelModel;
use Admin\Model\AreaModel;
use Common\Lib\Page;

class FlashMenuController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->hosname = $this->host_name();
    }

    public function getdetail(){
        $id = I('flid');
        $this->assign('_flid',$id);
        $mlModel = new \Admin\Model\SingleDriveListModel();
        $deatil_info = $mlModel->find($id);
        $hotel_arr = json_decode($deatil_info['hotel_id_str'], true);
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $start  = ( $start-1 ) * $size;
        $count = count($hotel_arr);
        $objPage = new Page($count, $size);
        $show = $objPage->admin_page();
        $hotel_arr = array_slice($hotel_arr, $start, $size);
        $hotelModel = new \Admin\Model\HotelModel();
        $field = 'name hotel_name,id hotel_id';
        $map['id']  = array('in', $hotel_arr);
        $hotel_info = $hotelModel->getInfo($field, $map);
        $menuhotelModel = new \Admin\Model\MenuHotelModel();
        foreach($hotel_info as $hk=>$hv) {
            $hid = $hv['hotel_id'];
            $per_arr = $menuhotelModel->getadsPeriod($hid);
            $hotel_info[$hk]['mename'] = $per_arr[0]['menu_name'];
        }
        $this->assign('list', $hotel_info);
        $this->assign('page',  $show);
        $this->display('getflashdetail');
    }


    public function getlist(){
        $key = 'selectudriver_hotel_key';
        session($key,null);
        $mlModel = new \Admin\Model\SingleDriveListModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        /*$userinfo = session('sysUserInfo');
        $area_city = $userinfo['area_city'];
        if($userinfo['groupid'] == 1 || empty($userinfo['area_city']) ){
        }else{
            $where .= " and sysgroup.area_city=$area_city";
        }*/
        $result = $mlModel->getList($where,$orders,$start,$size);
        $ht = str_replace('/', DIRECTORY_SEPARATOR, $this->hosname);
        $web = $ht.DIRECTORY_SEPARATOR.'Public'.DIRECTORY_SEPARATOR.'udriverpath'.DIRECTORY_SEPARATOR;
        array_walk($result['list'], function(&$v, $k)use($web) {
            if($v['state'] == 1) {
                $now_date =  date('Y-m-d',strtotime('-1 day'));
                $update_date = date('Y-m-d',strtotime($v['update_time']));
                if($now_date>$update_date){
                    $v['addr'] = '压缩包已过期';
                }else {
                    $v['addr'] = "<a href='".$web.$v['gendir'].".zip' target='_blank'>点击下载文件</a>";
                }
                
            } else{
                $v['addr'] = '压缩包生成中';
            }

        });
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('getlist');
    }



    public function selectHotel(){


        $areaModel  = new AreaModel;
        $menliModel  = new \Admin\Model\ProgramMenuListModel();
        //城市
        $area_arr = $areaModel->getAllArea();

        $this->assign('area', $area_arr);

        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $hotelModel = new HotelModel;
        $areaModel  = new AreaModel;
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $name = I('name');
        $beg_time = I('starttime','');
        $end_time = I('endtime','');
        $this->assign('sttime',$beg_time);
        $this->assign('sendime',$end_time);
        if($beg_time)   $where.=" AND install_date>='$beg_time'";
        if($end_time)   $where.=" AND install_date<='$end_time'";
        if($name)
        {
            $this->assign('name',$name);
            $where .= "	AND name LIKE '%{$name}%' ";
        }
        $hbt_v = I('hbt_v');
        if ($hbt_v) {
            $this->assign('hbt_k',$hbt_v);
            $where .= "	AND hotel_box_type = $hbt_v";
        }

        //城市
        $area_v = I('area_v');
        if ($area_v) {
            $this->assign('area_k',$area_v);
            if($area_v == 9999){
            }else{
                $where .= "	AND area_id = $area_v";
            }
        }
        //级别
        $level_v = I('level_v');
        if ($level_v) {
            $this->assign('level_k',$level_v);
            $where .= "	AND level = $level_v";
        }
        //状态
        $state_v = I('state_v');
        if ($state_v) {
            $this->assign('state_k',state_v);
            $where .= "	AND state = $state_v";
        }
        //重点
        $key_v = I('key_v');
        if ($key_v) {
            $this->assign('key_k',$key_v);
            $where .= "	AND iskey = $key_v";
        }
        //城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        $is_city_search = 0;
        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $is_city_search = 1;
            $this->assign('is_city_search',$is_city_search);
            $this->assign('pusera', $userinfo);
        }else {
            $this->assign('is_city_search',$is_city_search);
            $where .= "	AND area_id in ($pcity)";
        }
        $where .= " AND flag=0 AND state=1 AND hotel_box_type in (1,4,5)";

        $result = $hotelModel->getList($where,$orders,$start,$size);

        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        $h_box_type = C('hotel_box_type');
        $h_box_type = array(
            '1'=>'一代单机版',
            '4'=>'二代单机版',
            '5'=>'三代单机版',
        );
        $this->assign('h_box_type', $h_box_type);
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('alist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('selecthotel');
    }



    public function hotelconfirm(){

        $key = 'selectudriver_hotel_key';
        $h_id_arr = session($key);
        $h_id_arr = array_keys($h_id_arr);
        session($key,null);
        $where['id'] = array('in', $h_id_arr);
        if($h_id_arr) {
            $hotelModel = new \Admin\Model\HotelModel();
            $field = 'name honame, id hoid';
            $h_info = $hotelModel->getInfo($field, $where);
        }else{
            $h_info = array();
        }
        $this->assign('vinfo', $h_info);
        $this->display('hotelconfirm');
    }


    public function getsessionHotel(){
        $get_hotel_arr = json_decode($_POST['seshot'], true);
        $key = 'selectudriver_hotel_key';
        $h_arr = empty(session($key))?array():session($key);
        foreach($get_hotel_arr as $tp=>$tv) {
            if($tv['type'] == 1) {
                $h_arr[$tv['id']] = 1;
            } else {
                unset($h_arr[$tv['id']]);
            }
        }
        session($key, $h_arr);
    }

    public function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  intval($msec*100);
        return $msectime;
    }


    public function publishMenu(){
        $now_date = date("Y-m-d H:i:s");
        $hotel_id_arr = I('post.pubhotelhotel');
        if($hotel_id_arr == '') {
            $this->error('酒楼选择不可为空');
        }
        $hotel_id_arr = explode(',', $hotel_id_arr);
        $hotel_id_arr = array_unique($hotel_id_arr);
        $hotel_id_str = json_encode($hotel_id_arr);
        $single_list_Model = new \Admin\Model\SingleDriveListModel();
        $userInfo = session('sysUserInfo');
        $sp['creator_id'] = $userInfo['id'];
        $sp['hotel_id_str'] = $hotel_id_str;
        $sp['create_time'] = $now_date;
        $sp['update_time'] = $now_date;
        $msec = $this->msectime();
        $sp['gendir'] = 'udriver_'.time().$msec;
        //var_export($sp);
        $bool = $single_list_Model->addData($sp, 0);
        if($bool) {
            $this->output('发布成功了!', 'flashmenu/getlist');
        } else {
            $this->error('发布失败了!', 'flashmenu/getlist');
        }
    }




















}