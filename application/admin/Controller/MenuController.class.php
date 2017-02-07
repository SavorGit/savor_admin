<?php
namespace Admin\Controller;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
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
class MenuController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function hotelconfirm(){
        //var_dump($_POST);
        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $ids = I('ids');
        $data = array();
        $arr = array();
        foreach($ids as $k=>$v){
            $arr = explode('|', $v);
            $data[] = array('hoid'=>$arr[0],'honame'=>$arr[1]);
        }
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $data);
        $this->display('hotelconfirm');
    }

    public function publishMenu(){
        //隐患要把数组都改成checked
       // var_dump($_POST);

        $time = date("Y-m-d H:i:s");
        $menuid = I('post.menuid');
        $menuname = I('post.menuname');
        $hotel_id_arr = I('post.hoid');
        $hotel_name = I('post.honame');
        $hotelModel = new HotelModel;
        $menuHoModel = new MenuHotelModel();
        $menuLogModel = new MenuListLogModel();
        $menuliModel = new MenuListModel();
        $mItemModel = new MenuItemModel();
        $com_arr = array_combine($hotel_id_arr, $hotel_name);
       // var_dump($com_arr);

        $i = 1;
        $data = array();
        $sava = array();
        //根据menuid获取
        foreach ($com_arr as $k=>$v) {
            $data = array(
                'create_time'=>$time,
                'update_time'=>$time,
                'hotel_id'=>$k,'hotel_name'=>$v,
                'menu_id'=>$menuid,
            );
            //插入savor_menu_hotel
            $res = $menuHoModel->add($data);

            $userInfo = session('sysUserInfo');
            //根据session得到用户名
            if ($res) {
                //插入操作日志并同时操作menu_log
                $save['menu_id'] = $menuid;
                $save['hotel_id'] = $k;
                //获得menu_id内容

                $order = I('_order','id');
                $sort = I('_sort','asc');
                $orders = $order.' '.$sort;
                $where = "1=1";
                $field = "ads_name,ads_id,duration";
                $where .= " AND menu_id={$menuid}  ";
                $res = $mItemModel->getWhere($where,$orders, $field);
                $content = json_encode($res);
                $save['menu_content'] = $content;
                $save['operator_id'] = $userInfo['id'];
                $save['operator_name'] = $userInfo['username'];
                $save['insert_time'] = $time;
                $menuLogModel->add($save);
            }
        }

        //获得menuid数组
        $menu_arr = $menuliModel->getAll('id');
       // var_dump($menu_arr);
        $com_arr = array_flip($com_arr);
        foreach ($menu_arr as $k=>$v) {

            $bak_ho_arr = array();
            $sql = "SELECT hotel_id FROM savor_menu_hotel WHERE create_time=
                (SELECT MAX(create_time) FROM savor_menu_hotel WHERE menu_id={$v['id']})";
            $bak_hotel_id_arr = $menuliModel->query($sql);
            foreach ($bak_hotel_id_arr as $bk=>$bv){
                $bak_ho_arr[] = $bv['hotel_id'];
            }

            $dat = array();
            if ($menuid == $v['id']) {
                //获取count
                $count_arr = $menuliModel->field('count')->where(array('id'=>$v['id']))->find();
                $count = $count_arr['count'];
                if ($count == 0) {
                    $dat['count'] = count($com_arr);
                } else {
                    //取差集在最新发布的而不在原来的hotel
                    var_dump($com_arr, $bak_ho_arr);
                    $inter = array_diff($com_arr, $bak_ho_arr);
                    var_dump($inter);
                    var_dump($bak_ho_arr);
                    $in_count = count($inter);
                    $dat['count'] = $count+$in_count;
                }
            } else{
                $inter = array_intersect($bak_ho_arr, $com_arr);
                $in_count = count($inter);
                //获取本身自有的count
                $count_arr = $menuliModel->field('count')->where(array('id'=>$v['id']))->find();
                $count = $count_arr['count'];
                //获取menu_id对应该的hotelid数组
                //hotelid和现在的hotel取交集，count个数减去交集即可
                //update
                $dat = array();
                $dat['count'] = $count-$in_count;
               // var_dump($bak_ho_arr, $com_arr,$count, $in_count, $v['id']);

            }

            if($dat['count'] != 0) {
                $dat['state'] = 1;
            }

            $menuliModel->where(array('id'=>$v['id']))->save($dat);
        }
        $this->output('发布成功了!', 'menu/getlist');


        //$vinfo = $hotelModel->where('id='.$id)->find();

        //获取hotelname

        //插入操作日志并同时操作menu_log

       //  遍历menu_list table , 跟最新发布的对比
        //如果menu_id1到10有最新发布的，则把count-1
        //
        //检验savor_menu_hotel的menu_id是否存在，
        //存在的话，继续
        //不存在则插入
        //插入savor_menu_hotel
        //插入操作日志并同时操作menu_log
        //遍历修改menu_list count数，可以改成crontab


    }

    public function getdetail(){


        $id = I('get.id'.'');
        $menu_name = I('get.name'.'');
        if ( $id ) {
            $mItemModel = new MenuItemModel();
            $order = I('_order','id');
            $sort = I('_sort','asc');
            $orders = $order.' '.$sort;
            $where = "1=1";
            $field = "ads_name,ads_id,duration,sort_num";
            $where .= " AND menu_id={$id}  ";
            $res = $mItemModel->getWhere($where,$orders, $field);
            $this->assign('list', $res);


        } else {
            $this->display('getdetailmenu');
        }
        $this->assign('men_name', $menu_name);
        $this->display('getdetailmenu');
    }

    public function gethotelmanager()
    {
       // var_dump($_POST);
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

        if($name)
        {
            $this->assign('name',$name);
            $where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $hotelModel->getList($where,$orders,$start,$size);

        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        //print_r($result);die;
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');

    }//End Function

    public function selectHotel(){
        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $hotelModel = new HotelModel;
        $areaModel  = new AreaModel;

        $size   = I('numPerPage',30);//显示每页记录数
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

        if($name)
        {
            $this->assign('name',$name);
            $where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $hotelModel->getList($where,$orders,$start,$size);



        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        //print_r($result);die;
        $menu_id = I('menuid');
        $menu_name = I('menuname');

        $this->assign('menu_id', $menu_id);
        $this->assign('menu_name', $menu_name);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('selecthotel');
    }
    /*
     * 获取log日志，并进行对比
     */
    public function getlog() {
        $menu_id = I('id');
        $menu_name = I('menuname');
        $mlOpeModel = new MenuListOpeModel();
        $list = $mlOpeModel->field('menu_content,id,insert_time')->where(array('menu_id'=>$menu_id))->order('id asc')->select();
        $data = array();
        foreach ($list as $lk=>$lv){
            //如果是第一期不比较
            if ($lk == 0) {
                $datp = array();
                $dat = $mlOpeModel->field('menu_content,id,insert_time')->where(array('id'=>$lv['id']))->select();
                $log_arr = json_decode($lv['menu_content'],true);
                foreach ($log_arr as $lav) {
                    $datp[] = '增加'.$lav['ads_name'];
                }
                $data[$dat[0]['insert_time']] = $datp;
               // var_dump($data);

            } else {
                //获取上期数据
                $bak = array();
                $sec = array();
                $dat = $mlOpeModel->field('menu_content,id,insert_time')->where(array('id'=>$lv['id']-1))->select();

                $bak_log_arr = json_decode($dat[0]['menu_content'],true);
                foreach ($bak_log_arr as $bav) {
                    $bak[] = $bav['ads_name'];
                }
                //获取这期数据
                $dat = $mlOpeModel->field('menu_content,id,insert_time')->where(array('id'=>$lv['id']))->select();
                $log_arr = json_decode($lv['menu_content'],true);
                foreach ($log_arr as $lav) {
                    $sec[] = $lav['ads_name'];
                }
               // var_dump($sec,$bak);

                //取新的有旧的没有则加
                $arr_add = array_diff($sec, $bak);
                foreach($arr_add as &$av){
                    $av = '增加'.$av;
                }
                var_dump($arr_add);
                $arr_minus = array_diff($bak, $sec);
                foreach($arr_minus as &$av){
                    $av = '减少'.$av;
                }
                var_dump($arr_minus);

                $data[$lv['insert_time']] = array_merge($arr_add, $arr_minus);


            }

        }
        echo '<hr/><hr/>';
        var_dump($data);
    }

    public function getHotelInfo(){

        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $data = array();
        $mItemModel = new MenuItemModel();
        $sql = "SELECT hotel_id,hotel_name FROM savor_menu_hotel WHERE create_time=
                (SELECT MAX(create_time) FROM savor_menu_hotel WHERE menu_id=$menu_id)";

        $bak_hotel_id_arr = $mItemModel->query($sql);

        foreach ($bak_hotel_id_arr as $bk=>$bv){
            $data[] = array('hoid'=>$bv['hotel_id'],'honame'=>$bv['hotel_name']);
        }
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $data);

        $this->display('gethotelinfo');
    }
    
    public function manager() {
        //实例化redis
//         $redis = SavorRedis::getInstance();
//         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function getlist(){

        $mlModel = new MenuListModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $starttime = I('starttime');
        $endtime = I('endtime');
        if($starttime == ''){
            $starttime = date("Y-m-d H:i", time()-86400);
        }
        if($endtime == ''){
            $endtime = date("Y-m-d H:i");
        }
        $starttime = $starttime.':00';
        $endtime = $endtime.':00';
        $where = "1=1";
        $name = I('titlename');
        //$name = 'xiao';
        if ($starttime > $endtime) {
            $this->display('getlist');
        } else {
            if($name)
            {
                $this->assign('name',$name);
                $where .= "	AND menu_name LIKE '%{$name}%'";
                $where .= "	AND (`create_time`) > '{$starttime}' AND (`create_time`) < '{$endtime}' ";
            }
            $result = $mlModel->getList($where,$orders,$start,$size);



            $this->assign('list', $result['list']);
            $this->assign('page',  $result['page']);

            $this->display('getlist');
        }

    }



    public function doaddmenu(){
        //表单提交即是新增和导入ajax区分以及与修改进行区分
        $id = I('post.id','');

            //添加到menu_list 表
            $mlModel = new MenuListModel();
            $mItemModel = new MenuItemModel();
            $save                = [];
            $userInfo = session('sysUserInfo');
            $save['creator_name'] = $userInfo['username'];
            $save['creator_id'] = $userInfo['id'];
            $save['state']    = 0;
            $save['menu_name'] = I('post.program');
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['create_time'] = date('Y-m-d H:i:s');

            $id_arr = explode (',',substr(I('post.rightid',''),0,-1) );
            $name_arr = explode (',',substr(I('post.rightname',''),0,-1));
            $time_arr = explode (',',substr(I('post.rightime',''),0,-1));
            $co_arr = $id_arr;
            $id_arr = array();
            $dura_arr = array();
            foreach ($co_arr as $cv) {
                $arr = explode('|', $cv);
                $id_arr[] = $arr[0];
                $dura_arr[] = $arr[1];
            }

            if( $id ) {
                //先删除menuid，后插入
                $mItemModel->delData($id);
                $i = 1;
                $data = array();
                $sql = '';
                $value = '';
                $sql = "INSERT INTO `savor_menu_item` (`ads_id`,`ads_name`,`create_time`,`update_time`,`menu_id`,`sort_num`,`duration`) values ";
                foreach($id_arr as $k=>$v) {
                    $data[] = array('ads_id'=>$v,'ads_name'=>$name_arr[$k],
                        'create_time'=>$time_arr[$k],
                        );
                    $i++;
                }
                foreach($id_arr as $k=>$v) {

                    $value .= "('$v','$name_arr[$k]','$time_arr[$k]','{$save['update_time']}','$id','$i','$dura_arr[$k]'),";
                    $i++;
                }
                $sql .= substr($value,0,-1);

                $res = $mItemModel->execute($sql);
                if ($res) {
                    //添加操作日志非针对饭店
                    $type = 2;
                    $this->addlog($data, $id, $type);
                    $this->output('操作成功!', 'menu/getlist',2);
                } else {

                }

            } else {
                //判断名字是否存在
                $count = $mlModel->where(array('menu_name'=>$save['menu_name']))->count();
               if ($count) {
                   $this->output('操作失败名字已经有!', 'menu/addmenu');
               }
                $result = $mlModel->add($save);
                if ( $result ) {
                    $menu_id = $mlModel->getLastInsID();
                    //将内容添加到savor_menu_item表
                    $data = array();
                    $i = 1;
                    foreach($id_arr as $k=>$v) {
                        $data[] = array('ads_id'=>$v,'ads_name'=>$name_arr[$k],
                            'create_time'=>$time_arr[$k],
                            'update_time'=>$save['update_time'],
                            'menu_id'=>$menu_id,'sort_num'=>$i,
                            'duration'=>$dura_arr[$k]);
                        $i++;
                    }
                    $res = $mItemModel->addAll($data);


                    if ($res) {
                        //添加操作日志不在这边加
                        $this->addlog($data, $menu_id);
                        $this->output('操作成功!', 'menu/getlist',2);
                    } else {

                    }
                } else {

                }
            }






    }

    /*
     * 添加操作日志
     */
    public function addlog($data, $id, $type=1) {
        //1是插入2是更新
        $dat = array();
        $userInfo = session('sysUserInfo');
        $save['operator_name'] = $userInfo['username'];
        $save['operator_id'] = $userInfo['id'];
        $save['menu_id'] = $id;
        $save['insert_time'] = date('Y-m-d H:i:s');
        $save['type'] = $type;
        foreach ($data as $k=>$v) {
            $dat[]= array('ads_id'=>$v['ads_id'],'ads_name'=>$v['ads_name'],
                'create_time'=>$v['create_time'],
            );
        }
        $save['menu_content'] = json_encode($dat);
        $mlOpeModel = new MenuListOpeModel();
        $mlOpeModel->add($save);
    }




    /*
     * 添加节目管理
     */
    public function addmenu() {
        //左边表单提交，右边表单提交，导入ajax,id修改
        $userInfo = session('sysUserInfo');
        $menu_name = I('get.name'.'');
        $type = I('type');
        if ( $type == 2 ) {
            $menuid = I('id','0');
            if ($menuid) {
                $mItemModel = new MenuItemModel();
                $order = I('_order','id');
                $sort = I('_sort','asc');
                $orders = $order.' '.$sort;
                $where = "1=1";
                $field = "ads_name,ads_id,duration,sort_num,create_time";
                $where .= " AND menu_id={$menuid}  ";
                $res = $mItemModel->getWhere($where,$orders, $field);
                $this->assign('menuid',$menuid);
                $this->assign('menuname',$menu_name);
                $this->assign('list',$res);

            }
            $this->display('altermenu');
        } else {
            $this->display('addmenu');
        }

        /*
        $prModel = new ProgramModel();


        $prModel->getWhere();
        $artModel = new ArticleModel();
        $userInfo = session('sysUserInfo');
        $uname = $userInfo['username'];
        $this->assign('uname',$uname);


        $acctype = I('get.acttype');

        if ($acctype && $id)
        {
            $vinfo = $artModel->where('id='.$id)->find();
            $this->assign('vinfo',$vinfo);

        } else {

        }
        $where = "state=0";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);

        $this->assign('vcainfo',$vinfo);

        */


    }

    public function get_se_left(){
        $m_type = I('post.m_type','0');
        $starttime = I('post.starttime');
        $endtime = I('post.endtime', date("Y-m-d H:i"));
        if($starttime == ''){
            $starttime = date("Y-m-d H:i", time()-86400);
        }
        if($endtime == ''){
            $endtime = date("Y-m-d H:i");
        }

        $starttime = $starttime.':00';
        $endtime = $endtime.':00';
        $where = "1=1";
        $field = "id,name,media_id,create_time,duration";
        $searchtitle = I('post.searchtitle','');
        if ($starttime > $endtime) {
            $result = array('error'=>0);
            return $result;
        }
        if ($searchtitle) {
            $where .= "	AND name LIKE '%{$searchtitle}%'";
        }
        $adModel = new AdsModel();
        if ($m_type == 0) {
            $where .= "	AND (`create_time`) > '{$starttime}' AND (`create_time`) < '{$endtime}' ";

            $where .= "	AND (`type`) in (1,2) ";
            $result = $adModel->getWhere($where, $field);

            $result[] = array('id'=>0,'name'=>'酒楼宣传片','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'1酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'2酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'3酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'4酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'5酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'6酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);

        } else if($m_type == 3){
            $result[] = array('id'=>0,'name'=>'酒楼宣传片','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'1酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'2酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'3酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'4酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'5酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
            $result[] = array('id'=>0,'name'=>'6酒楼片源','create_time'=>date("Y-m-d H:i:s"),'duration'=>0);
        } else {
            $where .= "	AND (`create_time`) > '{$starttime}' AND (`create_time`) < '{$endtime}' ";
            $where .= "	AND type = '{$m_type}'";
            $result = $adModel->getWhere($where, $field);
        }
        echo json_encode($result);
        die;
    }














}