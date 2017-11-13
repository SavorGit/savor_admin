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
use Common\Lib\Page;


class MenuController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function gethotelLog(){

        //第一次先判断是空的
        $mlistLogModel = new \Admin\Model\MenuListLogModel();
        $menu_id = I('mid');
        $menu_name = I('name');
        $this->assign('menuname', $menu_name);
        //获取最新flag
       // $result = $mlistLogModel->field('max(id) id,hotel_id')->group('hotel_id')->where($arr)->select();
        $m_hotel_arr = $mlistLogModel->field('menu_id,hotel_id_s,insert_time,flag,operator_name')->order('insert_time asc')->select();
        $hotelModel = new \Admin\Model\HotelModel();
       // dump($m_hotel_arr);
        foreach ($m_hotel_arr as $v) {
            $datp = array();
            $hotel_id_arr = explode(',',$v['hotel_id_s']);
            if ($v['menu_id'] == $menu_id) {
                    foreach ($hotel_id_arr as $val){
                        $h_arr = $hotelModel->field('name')->find($val);
                        $hname = $h_arr['name'];
                        $datp[] = '增加'.$hname;
                    }
               // dump($datp);
                //这里再加个判断吗
                $data[$v['insert_time']] = $datp;
            } else {
                $c_hotel_st = $v['flag'];
                $c_hotel_arr = json_decode($c_hotel_st,true);
                //dump($c_hotel_arr);
                //var_dump($menu_id);
                if(array_key_exists($menu_id, $c_hotel_arr)) {
                    $bak_ho = $c_hotel_arr[$menu_id];
                    ;
                    $bak_ho_arr = explode(',',$bak_ho);

                    $in_arr = array_intersect($bak_ho_arr, $hotel_id_arr);
                    //dump($in_arr);
                    foreach ($in_arr as $ik=>$inv) {
                        $h_arr = $hotelModel->field('name')->find($inv);
                        $hname = $h_arr['name'];
                        $datp[] = '减少'.$hname;
                    }
                    //var_dump($datp);
                    $data[$v['insert_time']] = $datp;
                } else {
                    continue;
                }
            }

        }

        foreach($data as $kt=>&$kv){
            $mp[]  = strtotime($kt);
        }
        array_multisort($mp, SORT_DESC, $data);
        $this->assign('vinfo', $data);
        $this->display('opelog');

    }

    public function hotelconfirm(){

        $menu_id = I('menuid');
        $menu_name = I('menuname');


        $key = 'select_hotel_key';
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

        $hoty = I('hopu');
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $h_info);
        $this->assign('hoty', $hoty);
        $this->display('hotelconfirm');
    }

    public function publishMenu(){
        //隐患要把数组都改成checked
        $be_prefix  = C('DB_PREFIX');
        $hoty = I('post.hoty');
        $putime = I('logtime');
        if($putime == '') {
            $putime = date("Y-m-d H:i:s");
        }else{
            $putime = $putime.':00';
        }

        $time = date("Y-m-d H:i:s");
        $timec = date("Y-m-d H:i:s");
        $menuid = I('post.menuid');
        $menuname = I('post.menuname');
        $hotel_id_arr = I('post.hoid');
        $hotelModel = new HotelModel;
        $menuHoModel = new MenuHotelModel();
        $menuLogModel = new MenuListLogModel();
        $menuliModel = new MenuListModel();
        $mItemModel = new MenuItemModel();
        $hotel_name = array();
        foreach($hotel_id_arr as $hv){
            $h_name =$hotelModel->find($hv);
            $hotel_name[] = $h_name['name'];
        }
        $com_arr = array_combine($hotel_id_arr, $hotel_name);

        $i = 1;
        $data = array();
        $sava = array();
        //根据menuid获取
        //如果是新增的
        //var_dump($hoty);

        if ($hoty == 2) {

            $sql = "SELECT MAX(create_time) as time  FROM `savor_menu_hotel` WHERE menu_id=".$menuid;
            $crt = $menuHoModel->query($sql);
            $timec = $crt[0]['time'];
        }
        //获取发布新单前以前所有节目单对应hotelid
        //求出所有menu_id
        $menu_arr = $menuliModel->field('id')->select();
        $arr_ho = array();
        foreach ($menu_arr as $k=>$v) {
            $sql = "SELECT hotel_id FROM `savor_menu_hotel` WHERE menu_id={$v['id']}";
            $arr = $menuHoModel->query($sql);
            foreach ($arr as $bk=>$bv){
                $arr_ho[$v['id']] .= $bv['hotel_id'].',';
            }
            if(empty($arr_ho[$v['id']])){
                $arr_ho[$v['id']] = '';
            }else{
                $arr_ho[$v['id']] = substr($arr_ho[$v['id']],0,-1);
            }
        }
        foreach ($com_arr as $k=>$v) {
            $data = array(
                'create_time'=>$timec,
                'update_time'=>$time,
                'hotel_id'=>$k,'hotel_name'=>$v,
                'menu_id'=>$menuid,
                'pub_time'=>$putime,
            );
            //插入savor_menu_hotel
            $res = $menuHoModel->add($data);

            $userInfo = session('sysUserInfo');
            //根据session得到用户名
        }

        if ($res) {
            //插入操作日志并同时操作menu_log
            $save['menu_id'] = $menuid;
            $save['hotel_id_s'] = implode(',', $hotel_id_arr);
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
            //存储上一次节目单的酒店字符串
            $save['flag'] = json_encode($arr_ho);
            $menuLogModel->add($save);
        }
        //获得menuid数组

        //var_dump($menu_arr);
        $com_arr = array_flip($com_arr);
       // var_dump($com_arr);
        foreach ($menu_arr as $k=>$v) {

            $bak_ho_arr = array();
            $sql = "SELECT hotel_id FROM `savor_menu_hotel` WHERE create_time=
                (SELECT MAX(create_time) FROM `savor_menu_hotel` WHERE menu_id={$v['id']})";


            $bak_hotel_id_arr = $menuHoModel->query($sql);

            foreach ($bak_hotel_id_arr as $bk=>$bv){
                $bak_ho_arr[] = $bv['hotel_id'];
            }


            $dat = array();
            if ($menuid == $v['id']) {
                //获取count
                $count_arr = $menuliModel->field('count')->where(array('id'=>$v['id']))->find();
                $count = $count_arr['count'];

                if ($hoty != 2) {
                    if ($count == 0) {
                        $dat['count'] = count($com_arr);
                    }
                }
                else {
                    //取差集在最新发布的而不在原来的hotel
                    if ($hoty == 2){
                        $dat['count'] = count($bak_ho_arr);
                    } else {
                        //var_dump($com_arr, $bak_ho_arr);
                        $inter = array_diff($com_arr, $bak_ho_arr);
                        //var_dump($inter);
                        //var_dump($bak_ho_arr);
                        $in_count = count($inter);
                        $dat['count'] = $count+$in_count;
                    }

                }
            } else {
                $inter = array_intersect($bak_ho_arr, $com_arr);
                //var_dump($bak_ho_arr);
                //var_dump($com_arr);
                //var_dump($inter);
                //echo '<hr/><hr/>';
                $in_count = count($inter);
                //获取本身自有的count
                $count_arr = $menuliModel->field('count')->where(array('id'=>$v['id']))->find();


                //menu_id
                //删除sav_menu_item遍历id,就是删除次id
                //var_dump($inter,$v['id']);
                if($in_count>0){
                    $map['hotel_id']  = array('in',$inter);
                    $map['menu_id']  = array('in',$v['id']);
                    $menuHoModel->where($map)->delete(); //
                }
                $count = $count_arr['count'];
                //获取menu_id对应该的hotelid数组
                //hotelid和现在的hotel取交集，count个数减去交集即可
                //update
                $dat = array();
                $dat['count'] = $count-$in_count;
                // //var_dump($bak_ho_arr, $com_arr,$count, $in_count, $v['id']);

            }

            if($dat['count'] != 0) {
                $dat['state'] = 1;
            }


            $menuliModel->where(array('id'=>$v['id']))->save($dat);
            //var_dump($menuliModel->getLastSql());
        }

         $this->output('发布成功了!', 'menu/publish');
       // ob_end_clean();
       // $this->redirect("content/getlist");
        //echo "<script>location.href='http://www.baidu.com'</script>";


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

    public function getfile(){
        $upload = new \Think\Upload();
        $upload->exts = array('xls','xlsx','xlsm','csv');
        $upload->maxSize = 2097152;
        $upload->rootPath = $this->imgup_path();
        $upload->savePath = '';
        $info = $upload->upload();
        //var_dump($info);

        if(empty($info['file_data'])){
            $errMsg = $upload->getError();
            $this->output($errMsg, 'importdata', 0,0);
        }
        $path = SITE_TP_PATH.'/Public/uploads/'.$info['file_data']['savepath'].$info['file_data']['savename'];
        vendor("PHPExcel.PHPExcel.IOFactory");
        //echo $path;
        $ret[] = $path;
        echo json_encode($ret);
        die;
        if (!file_exists($path)) {
            $this->output('上传文件失败', 'importdata', 0,0);
        }
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION) );
        if($type=='xlsx' || $type=='xls'){
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
        }elseif($type=='csv'){
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
                ->setDelimiter(',')
                ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                ->setEnclosure('"')
                ->setLineEnding("\r\n")
                ->setSheetIndex(0);
            $objPHPExcel = $objReader->load($path);
        }else{
            $this->output('文件格式不正确', 'importdata', 0,0);
        }
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for($i=0; $i<$highestColumnNum;$i++){
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i).'1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[]= $cellVal;
        }
        //开始取出数据并存入数组
        $data = array();
        for($i=2;$i<=$highestRowNum;$i++){//ignore row 1
            $row = array();
            for($j=0; $j<$highestColumnNum;$j++){
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j).$i;
                $cellVal = $sheet->getCell($cellName)->getValue();
                $row[$filed[$j]] = $cellVal;
            }
            $data []= $row;
        }
        var_dump($data);
    }

    public function gethotelmanager()
    {
        // //var_dump($_POST);
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


        $befo  = C('DB_PREFIX');
        $areaModel  = new AreaModel;
        $menliModel  = new MenuListModel();
        //城市
        $area_arr = $areaModel->getAllArea();

        $this->assign('area', $area_arr);

        $men_arr = $menliModel->select();
        $this->assign('include', $men_arr);

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
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $pawhere = '1=1';
            $this->assign('pusera', $userinfo);
        }else {
            $where .= "	AND area_id in ($pcity)";
            $pawhere = '1=1 and area_id = '.$pcity;
        }
        //包含酒楼
        $menuHoModel = new \Admin\Model\MenuHotelModel();
        $pafield = 'DISTINCT smh.menu_id id,
smlist.menu_name';
        $men_arr = $menuHoModel->getPrvMenu($pafield, $pawhere);
        //获取包含有该地区酒楼
        $this->assign('include', $men_arr);
        //城市
        $area_v = I('area_v');
        if ($area_v) {
            $this->assign('area_k',$area_v);
            if(empty($area_v)){
            
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
        //包含
        $include_v = I('include_v');
        //获取节目单对应hotelid
        if ($include_v) {
            //取部分包含节目单
            $bak_ho_arr = array();
            foreach ($include_v as $iv) {
                $menuliModel = new MenuListModel();

                $sql = "SELECT hotel_id FROM `savor_menu_hotel` WHERE create_time=
                (SELECT MAX(create_time) FROM `savor_menu_hotel` WHERE menu_id={$iv})";
                $bak_hotel_id_arr = $menuliModel->query($sql);
                foreach ($bak_hotel_id_arr as $bk=>$bv){
                    $bak_ho_arr[] = $bv['hotel_id'];
                }
            }
            $bak_ho_arr = array_unique($bak_ho_arr);
            $bak_ho_str = implode(',', $bak_ho_arr);
            if($bak_ho_str){
                $where .= "	AND id  in ($bak_ho_str)";
            }else{
                $where .= "	AND id  in ('')";
            }
            $this->assign('include_k',$include_v);
        } else {
            $exc_v = I('exc_v');
            if ($exc_v) {
                $bak_ho_arr_p = array();
                foreach ($exc_v as $iv) {
                    $menuliModel = new MenuListModel();

                    $sql = "SELECT hotel_id FROM `savor_menu_hotel` WHERE create_time=
                (SELECT MAX(create_time) FROM  `savor_menu_hotel` WHERE menu_id={$iv})";
                    $bak_hotel_id_arr = $menuliModel->query($sql);
                    foreach ($bak_hotel_id_arr as $bk=>$bv){
                        $bak_ho_arr_p[] = $bv['hotel_id'];
                    }
                }
                $bak_ho_arr_p = array_unique($bak_ho_arr_p);
                $bak_ho_str = implode(',', $bak_ho_arr_p);
                if($bak_ho_str){
                    $where .= "	AND id not in ($bak_ho_str)";
                }
            } else {

            }
        }

        $type = I('type');
        //新增酒楼判断
        $addhotel = I('addhotel');
        if($addhotel == 2) {
            $ex_arr = I('inf');
            //从gethotelinfo新增转过来
            $acp = $addhotel;
            $this->assign('hot', $acp);
            $this->assign('addhotel', $acp);
            $str = '';
            foreach($ex_arr as $ek=>$ev){
                $str .= $ev.',';
            }
            $str = substr($str, 0,-1);

            $where .= "	AND id not in ({$str}) ";
            $this->assign('hopu', $addhotel);
            $nup = 1;
            $this->assign('meyi', 1);

        }
        $hot = I('hot');


        if($hot == 2){
            $str = I('infp');
            $where .= "	AND id not in ({$str}) ";
            $this->assign('hopu', $hot);
            $this->assign('hot', $hot);
            $this->assign('addhotel', $hot);

        }



        $result = $hotelModel->getList($where,$orders,$start,$size);
        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        //print_r($result);die;
        $this->assign('ext', $str);
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('alist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('selecthotel');
    }
    /*
     * 获取log日志，并进行对比
     */



    public function getlog() {
        $userInfo = session('sysUserInfo');
        $menu_id = I('id');
        $menu_name = I('name');
        $this->assign('menuname', $menu_name);
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
                $dat[0]['insert_time'] =date("Y-m-d H:i:s",strtotime($dat[0]['insert_time']));
                $data[$dat[0]['insert_time']] = $datp;
                // //var_dump($data);

            } else {
                //获取上期数据
                $bak = array();
                $sec = array();
                $dat = $mlOpeModel->field('menu_content,id,insert_time')->where(array('id'=>$list[$lk-1]['id']))->select();
                $bak_log_arr = json_decode($dat[0]['menu_content'],true);
                foreach ($bak_log_arr as $bav) {
                    $bak[] = $bav['ads_name'];
                }
                //获取这期数据
                $dat = $mlOpeModel->field('menu_content,id,insert_time')->where(array('id'=>$lv['id']))->select();
                $log_arr = json_decode($lv['menu_content'],true);
                //sec为新，bak为旧
                foreach ($log_arr as $lav) {
                    $sec[] = $lav['ads_name'];
                }
                //新的没有旧的有
                $acn = array_diff($bak, $sec);
                $acn = array_unique($acn);
                $sec = array_count_values($sec);
                $bak = array_count_values($bak);
                $arr_add = array();
                if ($sec === $bak) {
                    unset($data[$lv['insert_time']]);
                    continue;
                } else{
                    if($sec == $bak) {
                        $data[$lv['insert_time']] = array(0=>'改变顺序');
                        continue;
                    }
                }
                foreach($acn as $av){
                    $nup = $bak[$av];
                    for($i=0;$i<$nup;$i++){
                        $arr_add[] = '减少'.$av;
                    }
                }

                foreach ($sec as $sk=>$sv) {
                    if(array_key_exists($sk, $bak)) {
                        //比较大小
                        $bnum = $bak[$sk];
                        if ($bnum>$sv) {
                            for($i=0;$i<$bnum-$sv;$i++){
                                $arr_add[] = '减少'.$sk;
                            }
                        } else if ($bnum<$sv) {
                            for($i=0;$i<$sv-$bnum;$i++){
                                $arr_add[] = '增加'.$sk;
                            }
                        } else {
                            continue;
                        }

                    } else {
                        $num = $sv;
                        for($i=0;$i<$num;$i++){
                            $arr_add[] = '增加'.$sk;
                        }
                    }
                }
                $lv['insert_time'] = date("Y-m-d H:i:s",strtotime($lv['insert_time']));
                $data[$lv['insert_time']] = $arr_add;
            }

        }

        $mp = array();
        foreach($data as $kt=>&$kv){
            $mp[]  = strtotime($kt);
        }
        array_multisort($mp, SORT_DESC, $data);
        $this->assign('vinfo', $data);
        $this->display('opelog');
    }
    public function getHotelInfo(){
        $befo  = C('DB_PREFIX');
        $menu_id = I('menuid');
        $menu_name = I('menuname');
        $data = array();
        $mItemModel = new MenuItemModel();
        $sql = "SELECT hotel_id,sht.name hotel_name,pub_time FROM `savor_menu_hotel` smh join `savor_hotel` sht on smh.hotel_id = sht.id WHERE smh.create_time=
                (SELECT MAX(create_time) FROM `savor_menu_hotel` WHERE menu_id=$menu_id)";
        $bak_hotel_id_arr = $mItemModel->query($sql);


        foreach ($bak_hotel_id_arr as $bk=>$bv){
            $data[] = array('hoid'=>$bv['hotel_id'],'honame'=>$bv['hotel_name'],'pub_time'=>$bv['pub_time']);
        }
        $this->assign('menuid', $menu_id);
        $this->assign('menuname', $menu_name);
        $this->assign('vinfo', $data);

        $this->display('gethotelinfo');
    }

    public function getsessionHotel(){
        $get_hotel_arr = json_decode($_POST['seshot'], true);
        $type = $get_hotel_arr[0]['type'];
        $hid = $get_hotel_arr[0]['id'];
        $key = 'select_hotel_key';
        $h_arr = empty(session($key))?array():session($key);
        if($type == 1){
            $h_arr[$hid] = 1;
        } else {
            unset($h_arr[$hid]);
        }
        session($key, $h_arr);
    }

    public function manager() {
        //实例化redis
        //         $redis = SavorRedis::getInstance();
        //         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function getlist(){

        $key = 'select_hotel_key';
        session($key,null);
        $mlModel = new MenuListModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        $name = I('titlename');
        $beg_time = I('starttime','');
        $end_time = I('end_time','');
        if($beg_time)   $where.=" AND create_time>='$beg_time 00:00:00'";
        if($end_time)   $where.=" AND create_time<='$end_time 23:59:59'";
        if($name)
        {
            $this->assign('name',$name);
            $where .= "	AND menu_name LIKE '%{$name}%' ";

        }


        $menuHoModel = new \Admin\Model\MenuHotelModel();
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        if($userinfo['groupid'] == 1 ||
            $userinfo['area_city'] == 9999){
            $pawhere = '1=1';
            $result = $mlModel->getList($where,$orders,$start,$size);
        }else {
            $pawhere = '1=1 and area_id = '.$pcity;
            //包含酒楼
            $pafield = 'DISTINCT smh.menu_id id';
            $men_arr = $menuHoModel->getPrvMenu($pafield, $pawhere);
            if($men_arr){
                $men_id_arr = array_column($men_arr, 'id');
                $menu_id_str = implode(',', $men_id_arr);
                $where .= " and( id in ($menu_id_str) ";
                $where .= " or `count`= 0 )";
                $result = $mlModel->getList($where,$orders,$start,$size);
            }else{
                $where .= " and `count`= 0 ";
                $result = $mlModel->getList($where,$orders,$start,$size);
            }
        }

        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);

        $this->display('getlist');


    }

    public function doaddmen(){
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
        
       

        $id_arr = explode (',',substr(I('post.rightid',''),0,-1) );
        $dura_arr = explode (',',substr(I('post.rightdur',''),0,-1) );
        $name_arr = explode (',',substr(I('post.rightname',''),0,-1));
        $time_arr = explode (',',substr(I('post.rightime',''),0,-1));
        $co_arr = $id_arr;


        if( $id ) {
            //先删除menuid，后插入
            $mItemModel->delData($id);
            //更新menulist
            $save['update_time'] = date("Y-m-d H:i:s");
            
            $mlModel->where(array('id'=>$id))->save($save);
            
            $i = 1;
            $data = array();
            $sql = '';
            $value = '';
            
            if(I('post.rightname')==''){
               $res = true;
                $data = array();
            }else{
                $sql = "INSERT INTO `savor_menu_item` (`ads_id`,`ads_name`,`create_time`,`update_time`,`menu_id`,`sort_num`,`duration`) values ";

                foreach($id_arr as $k=>$v) {

                    $value .= "('$v','$name_arr[$k]','$time_arr[$k]','{$save['update_time']}','$id','$i','$dura_arr[$k]'),";
                    $i++;
                }
                $sql .= substr($value,0,-1);
               
                $res = $mItemModel->execute($sql);
                foreach($id_arr as $k=>$v) {
                    $data[] = array('ads_id'=>$v,'ads_name'=>$name_arr[$k],
                        'create_time'=>$time_arr[$k],
                    );
                    $i++;
                }
            }


            if ($res) {
                //添加操作日志非针对饭店
                $type = 2;
                $this->addlog($data, $id, $type);
               // $this->success('新增成功', '#menu/getlist');
                //$this->display()
                $this->output('修改成功!', 'menu/getlistgetlistgetlist');
            } else {
                $this->error('修改失败');
            }

        } else {
            //判断名字是否存在
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['create_time'] = date('Y-m-d H:i:s');
            $save['menu_name'] = I('post.program','','trim');
            $count = $mlModel->where(array('menu_name'=>$save['menu_name']))->count();
            if ($count) {
                $this->error('节目单名称已存在!');
            }
            $rightid_arr = I('post.rightid','');
            if(empty($rightid_arr)){
                $this->error('节目单列表不能为空!');
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

                   // $this->output('新增成功', 'menu/addmen',2);
                    $this->output('新增成功', 'menu/getlistgetlistgetlist',2);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error('新增失败');
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
     * 处理excel数据
     */
    public function analyseExcel(){
        $adsModel = new \Admin\Model\AdsModel();
        $path = $_POST['excelpath'];
        if  ($path == '') {
            $res = array('error'=>0,'message'=>array());
            echo json_encode($res);
        }
        $adsname = I('post.adsname','');

        if ($adsname) {
            $name_arr = explode (',',substr(I('post.rightname',''),0,-1));
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
            $this->output('文件格式不正确', 'importdata', 0, 0);
        }

        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
       // var_dump($highestRowNum, $highestColumn, $highestColumnNum);
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
       // var_dump($filed);

        //开始取出数据并存入数组
        $data = array();
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = $sheet->getCell($cellName)->getValue();
                $row[$filed[$j]] = $cellVal;
            }
            $data [] = $row;
        }
        //var_dump($data);
        $ex_arr = array();
        $remove_arr = array();
        $inc_arr = array();
        foreach ($data as $rk=>$rv) {
            foreach($rv as $sk=>$sv){
                $ex_arr[] = $sv;
                break;
            }
            $xuan_arr = array('酒楼宣传片','1酒楼片源','2酒楼片源','3酒楼片源','4酒楼片源','5酒楼片源','6酒楼片源');
            if (in_array($sv, $xuan_arr)) {
                $inc_arr[] = array(
                    'id'=>0,
                    'name'=>$sv,
                    'duration'=>0,
                    'create_time'=>date("Y-m-d H:i:s"),
                );
            }else{
                $res = $adsModel->where(array('name'=>$sv))->find();

                if ($res) {
                    //var_dump($res);
                    $inc_arr[] = array(
                        'id'=>$res['id'],
                        'name'=>$res['name'],
                        'duration'=>$res['duration'],
                        'create_time'=>$res['create_time'],
                    );
                } else{
                    $remove_arr[] = $sv;
                }
            }

        }
        $list = '';
        /*foreach ($inc_arr as $ik=>$iv) {
            $list .= ' <div id="'.$iv['id'].'" dur="'.$iv['duration'].'" class="divlist2"><span class="sleft">'.$iv['name'].'</span><span class="sright">'.$iv['create_time'].' </span></div>';
        }*/
        //$list = h($list);

        if ($remove_arr) {
            $res = array('error'=>1,'nomessage'=>$remove_arr,'message'=>$inc_arr);

        } else {
            $res = array('error'=>0,'message'=>$inc_arr);
        }
       // ob_clean();
        echo json_encode($res);
    }



    /*
   * 添加节目管理
   */
    public function addmen()
    {

        if (isset($_POST['excelsub'])) {
            //excel判断
            $path = $_POST['excelpath'];
            $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            vendor("PHPExcel.PHPExcel.IOFactory");

            if ($type == 'xlsx' || $type == 'xls') {
                $objPHPExcel = \PHPExcel_IOFactory::load($path);
                die;
            } elseif ($type == 'csv') {
                $objReader = \PHPExcel_IOFactory::createReader('CSV')
                    ->setDelimiter(',')
                    ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                    ->setEnclosure('"')
                    ->setLineEnding("\r\n")
                    ->setSheetIndex(0);
                $objPHPExcel = $objReader->load($path);
            } else {
                $this->output('文件格式不正确', 'importdata', 0, 0);
            }

            $sheet = $objPHPExcel->getSheet(0);
            //获取行数与列数,注意列数需要转换
            $highestRowNum = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            var_dump($highestRowNum, $highestColumn, $highestColumnNum);
            //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
            $filed = array();
            for ($i = 0; $i < $highestColumnNum; $i++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
                $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
                $filed[] = $cellVal;
            }
            var_dump($filed);

            //开始取出数据并存入数组
            $data = array();
            for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
                $row = array();
                for ($j = 0; $j < $highestColumnNum; $j++) {
                    $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                    $cellVal = $sheet->getCell($cellName)->getValue();
                    $row[$filed[$j]] = $cellVal;
                }
                $data [] = $row;
            }
            var_dump($data);
            // ob_clean();
            // die;
            die;

        }

            //左边表单提交，右边表单提交，导入ajax,id修改
            $userInfo = session('sysUserInfo');
            $menu_name = I('get.name' . '');
            $type = I('type');
            //修改节目单
            if ($type == 2) {
                $menuid = I('id', '0');
                if ($menuid) {
                    $mItemModel = new MenuItemModel();
                    $order = I('_order', 'id');
                    $sort = I('_sort', 'asc');
                    $orders = $order . ' ' . $sort;
                    $where = "1=1";
                    $field = "ads_name,ads_id,duration,sort_num,create_time";
                    $where .= " AND menu_id={$menuid}  ";
                    $res = $mItemModel->getWhere($where, $orders, $field);
                    $this->assign('menuid', $menuid);
                    //判断是新增
                    $pct = I('pctype', '0');
                    $this->assign('list', $res);
                    if($pct == 1){
                        $this->assign('menuname', '');
                        $this->assign('menuid', '');
                        $this->display('copymenuct');
                    }else{
                        $this->assign('menuname', $menu_name);
                        $this->display('altermenuct');
                    }
                }
            } else {
                $this->display('addmenuct');
            }



    }

    public function addtest(){
        $this->output('操作成功','menu/getlist');
    }


    public function get_se_left(){
        $m_type = I('post.m_type','0');

        $where = "1=1";
        $field = "id,name,media_id,create_time,duration";
        $searchtitle = I('post.searchtitle','');
        $beg_time = I('starttime','');
        $end_time = I('endtime','');

        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   {
            $end_time=date("Y-m-d",strtotime($end_time."+1 day"));
            $where.=" AND create_time<'$end_time'";
        }

        $where .= " AND state=1 ";
        if ($searchtitle) {
            $where .= "	AND name LIKE '%{$searchtitle}%'";
        }
        $adModel = new AdsModel();
        if ($m_type == 0) {
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
            $where .= "	AND type = '{$m_type}'";
            $result = $adModel->getWhere($where, $field);
        }
        echo json_encode($result);
        die;
    }


















}