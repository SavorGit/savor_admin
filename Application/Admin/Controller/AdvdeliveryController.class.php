<?php
namespace Admin\Controller;

/**
 *@desc 专题组控制器,对专题组添加或者修改
 * @Package Name: SpecialgroupController
 *
 * @author      白玉涛
 * @version     3.0.1
 * @copyright www.baidu.com
 */
use Admin\Controller\BaseController;
use Common\Lib\Page;
use Common\Lib\SavorRedis;

class AdvdeliveryController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function  doAddAdvBox() {
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $map['state'] = array(array('eq',3),array('eq',0), 'or') ;
        $field = 'type,state';
        $p_data = $pubadsModel->getWhere($map,$field);
        //$p_data = false;
        if($p_data) {
            foreach( $p_data as $pk=>$pv) {
                if($pv['state']== 3 && $pv['type'] == 2) {
                    $this->error('当前有广告正在发布，暂时无法添加，请稍后再试');
                }
                if($pv['state']== 0 && $pv['type'] == 1) {
                    //判断版位是否不足
                    $this->error('当前有广告正在发布，暂时无法添加，请稍后再试');
                }
            }

        }




        $now_date = date("Y-m-d H:i:s");
        $h_b_arr = $_POST['hbarr'];
        $h_b_arr = json_decode($h_b_arr, true);

        $now_date = date("Y-m-d H:i:s");
        $now_day = date("Y-m-d");
        $save['ads_id'] = I('post.marketid','237');
        if ( empty($save['ads_id']) ) {
            $msg = '上传广告视频失败请重新上传';
            $this->error($msg);
        }
        $save['start_date'] = I('post.start_time', '');
        $save['end_date'] = I('post.end_time', '');
        $save['play_times'] = I('post.play_times', '');
        if($save['start_date'] > $save['end_date']) {
            $msg = '投放开始时间必须小于等于结束时间';
            $this->error($msg);
        }
        if($save['start_date'] < $now_day) {
            $msg = '投放开始时间必须大于等于今天';
            $this->error($msg);
        }

        //投放类型1机顶盒2酒店
        $screen_type = I('post.screenadv_type','1');
        $del_hall    = I('post.del_hall');  //是否剔除大厅版位
        $userInfo = session('sysUserInfo');
        $save['create_time'] = $now_date;
        $save['update_time'] = $now_date;
        $save['creator_id'] = $userInfo['id'];
        $save['state'] = 0;
        $save['del_hall'] = $del_hall;
        $oneday_count = 3600 * 24;  //一天有多少秒
        //明天
        $save['end_date'] = date("Y-m-d H:i:s", strtotime($save['end_date']) + $oneday_count-1);
        $save['type'] = 1;
        //插入pub_ads表
        $pubadsModel->startTrans();
        if( $screen_type == 2 ){
            $save['state'] = 3;
            $save['type'] = 2;
        }
        $save['cover_img_media_id'] = I('post.cover_img_media_id',0,'intval');
        $res = $pubadsModel->addData($save, 0);
        //var_export($res);
        $tmp[] = array();
        if($res) {
            if($screen_type == 2) {
                //插入hotel表
                $pub_ads_id = $pubadsModel->getLastInsID();
                $pub_ads_hotelModel = new \Admin\Model\PubAdsHotelModel();
                $datp = array();
                $tmp_hb = array();
                foreach ($h_b_arr as $k=>$v) {
                    if(array_key_exists($v['hotel_id'], $tmp_hb)) {
                        continue;
                    }
                    $tmp_hb[$v['hotel_id']] = 1;
                    $datp[] = array(

                        'hotel_id'=>$v['hotel_id'],
                        'pub_ads_id'=>$pub_ads_id,
                    );
                }
                $res = $pub_ads_hotelModel->addAll($datp);
                if($res) {
                    $pubadsModel->commit();
                    $this->output('添加成功','advdelivery/getlist');
                }else {
                    $pubadsModel->rollback();
                    $this->error('添加失败');
                }
            } else {
                //插入box表
                $pub_ads_id = $pubadsModel->getLastInsID();
                $pubadsBoxModel = new \Admin\Model\PubAdsBoxModel();
                $tmp_hb = array();
                foreach ($h_b_arr as $k=>$v) {
                    if(array_key_exists($v['hotel_id'], $tmp_hb)) {
                        continue;
                    }
                    $tmp_hb[$v['hotel_id']] = 1;
                    foreach($v['box_str'] as $rv) {
                        if(in_array($rv, $tmp)) {
                            continue;
                        } else {
                            $tmp[] = $rv;
                            for($i=0;$i<$save['play_times'];$i++) {
                                $data[] = array(
                                    'create_time'=>$now_date,
                                    'update_time'=>$now_date,
                                    'box_id'=>$rv,
                                    'pub_ads_id'=>$pub_ads_id,
                                );
                            }

                        }
                    }
                }
                $res = $pubadsBoxModel->addAll($data);
                if($res) {
                    $pubadsModel->commit();
                    $this->output('添加成功','advdelivery/getlist');
                }else {
                    $pubadsModel->rollback();
                    $this->error('添加失败');
                }
            }

        } else {
            $pubadsModel->rollback();
            $this->error('添加失败');
        }




    }




    public function getAllBox($hotel_id) {
        $hotel_box_type_str = $this->getNetHotelTypeStr();
        $where = '1=1 and sht.id='.$hotel_id.' and sht.state=1 and
        sht.flag=0
        and sht.hotel_box_type in ('.$hotel_box_type_str.') and room.state=1
        and room.flag=0 and box.flag=0 and box.state=1';
        $hotelModel = new \Admin\Model\HotelModel();
        $field = 'box.id bid,box.name bname';
        $order = ' box.id asc ';
        $box_arr = $hotelModel->getBoxOrderMacByHid($field, $where, $order);
       // $rs = $hotelModel->getLastSql();
       // file_put_contents(LOG_PATH.'baiyutao.log',$rs.PHP_EOL,  FILE_APPEND);
        $box_arr = assoc_unique($box_arr,'bid');
        return $box_arr;
    }

    //1只返回占位数
    //2返回占位数组
    public function getBoxCondition($box_id , $save, $type) {
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $map['_string'] = "('".$save['start_time'] ."' <=
        ads.end_date and '".$save['end_time']."' >= ads.start_date )";
        $map['ads_box.box_id'] = $box_id;
        $map['ads.state'] = array('neq', 2);
        $field = 'ads_box.location_id as lid';
        $p_tiems = $save['play_times'];
        $group = '';
        $ocu_arr = $pubadsModel->getBoxPlayTimes($map, $field, $group);
        $ocu_len = count($ocu_arr);
        $bool = false;
        if ($type == 1) {
            if (empty($ocu_arr)) {
                $bool = true;
            } else {

                $adv_promote_num_arr = C('ADVE_OCCU');
                $adv_promote_num = $adv_promote_num_arr['num'];
                $l_len = $adv_promote_num-$ocu_len-$p_tiems;
                if($l_len>=0) {
                    $bool = true;
                }else {
                    return false;
                }
            }
            return $bool;
        }
    }


        /*
     * @desc 获取全选过的酒楼
     * @method getcheckadsHotel
     * @access public
     * @http GET
     * @param area_id 城市id
     * @param hotel_name 酒楼名称
     * @return void
     */
    public function getcheckadsHotel() {
        $hotel_arr = $_POST['devilerychds'];
        $hotel_arr = json_decode($hotel_arr, true);
        //城市
        //根据hotelid获取版位
        $boxModel = new \Admin\Model\BoxModel();
        $field = 'count(distinct (b.id)) num';
        $where = ' 1=1 and b.state=1 and b.flag=0 and r.state=1 and
        r.flag=0 and h.state=1 and h.flag=0 ';
        if(count($hotel_arr) == 1) {
            $where .= ' and h.id = '. $hotel_arr[0];
        } else {
            $hotel_str = implode(',', $hotel_arr);
            $where .= ' and h.id in ('.$hotel_str.') ';
        }
        $b_arr = $boxModel->isHaveMac($field, $where);
        //var_export($boxModel->getLastSql());
        $res = array('num'=>empty($b_arr[0]['num'])?0:$b_arr[0]['num']);
        echo json_encode($res);
    }

    /*
	 * @desc 获取有效的酒楼
     * @method getOcupHotel
     * @access public
     * @http GET
     * @param area_id 城市id
     * @param hotel_name 酒楼名称
     * @return void
	 */
    public function getOcupHotel() {
        $now_time = time();
        $area_id = I('area_id',0);
        $hotel_name = I('hotel_name', '');
        $where = "1=1";
        if ($area_id) {
            $this->assign('area_k',$area_id);
            $where .= "	AND sht.area_id = $area_id";
        }
        if($hotel_name){
            $this->assign('name',$hotel_name);
            $where .= "	AND name LIKE '%{$hotel_name}%'";
        }
        //城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        
        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $pawhere = '1=1';
            
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
        /*//获取节目单对应最大id还没写且在预约时间内<今天
        $where_pr = ' UNIX_TIMESTAMP(`pub_time`) < '.$now_time;
        $fieldpr="hotel_id";
        $group = 'hotel_id';
        $order= 'pub_time desc';
        $promenuModel = new \Admin\Model\ProgramMenuHotelModel();
        $pr_hotel_arr =  $promenuModel->getWhere($where_pr,
        $order, $fieldpr,$group);
        if ($pr_hotel_arr) {
            $h_arr = array_column($pr_hotel_arr, 'hotel_id');
            $h_str = implode(',', array_unique($h_arr));
            $h_str = 'sht.id in ('.$h_str.')';
        } else {
            $res = array('code'=>1,'data'=>array());
            echo json_encode($res);
            die;
        }*/
        $field = 'sht.id hid, sht.name hname';
        $hotelModel = new \Admin\Model\HotelModel();
        //$where .= ' and '.$h_str;
        $orders = 'convert(sht.name using gbk) asc';
        $result = $hotelModel->getHotelidByArea($where, $field, $orders);
        //var_export($hotelModel->getLastSql());
        $msg = '';
        $res = array('code'=>1,'msg'=>$msg,'data'=>$result);
        echo json_encode($res);
        /*var_dump($hotelModel->getLastSql());
        var_dump($result);*/
    }

    public function getAllHotel() {
        $now_time = time();
        $area_id = I('area_id',0);
        $hotel_name = I('hotel_name', '');
        $where = "sht.state=1 and sht.flag=0 ";
        if ($area_id) {
            $this->assign('area_k',$area_id);
            $where .= "	AND sht.area_id = $area_id";
        }
        if($hotel_name){
            $this->assign('name',$hotel_name);
            $where .= "	AND name LIKE '%{$hotel_name}%'";
        }
        //城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];

        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $pawhere = '1=1';
            $this->assign('pusera', $userinfo);
        }else {
            $where .= "	AND sht.area_id in ($pcity)";
        }

        $field = 'sht.id hid, sht.name hname';
        $hotelModel = new \Admin\Model\HotelModel();
        $orders = 'convert(sht.name using gbk) asc';
        $result = $hotelModel->getHotelidByArea($where, $field, $orders);
        $msg = '';
        $res = array('code'=>1,'msg'=>$msg,'data'=>$result);
        echo json_encode($res);
    }


    /*
	 * @desc 获取当前酒店有效机顶盒
     * @method getValidBoxidByHotel
     * @access public
     * @http GET
     * @param hotel_id 酒店id
     * @param hotel_name 酒楼名称
     * @return void
	 */
    public function getValidBoxidByHotel() {
        $now_date = date("Y-m-d");
        $hotel_id = I('post.hotel_id',0);
        $start_time = I('post.start_time', '');
        $end_time = I('post.end_time', '');
        $play_times = I('post.play_times', '');
        //时间判断预约
        if($hotel_id == 0) {
            $msg = '选择酒店有误';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        if(empty($start_time) || empty($end_time)){
            $msg = '请选择投放时间段';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        if($start_time > $end_time) {
            $msg = '投放开始时间必须小于等于结束时间';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        if($start_time < $now_date) {
            $msg = '投放开始时间必须大于等于今天';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        $dat = array (
            'start_time'=>$start_time,
            'end_time'=>$end_time.' 23:59:59',
            'play_times'=>$play_times,
        );
        $box_arr = $this->getAllBox($hotel_id);

        //file_put_contents(LOG_PATH.'baiyutao.log', json_encode($box_arr).PHP_EOL,  FILE_APPEND);

        if ($box_arr) {
            foreach ($box_arr as $bk=> $bv) {
                //获取是否有效
                $res = $this->getBoxCondition($bv['bid'], $dat,
                    $type=1);
                if( !($res) ) {
                    $box_arr[$bk]['btype'] = 0;
                } else {

                    if($res === 'wrongwrong') {
                        //错误输出找我们
                        echo $bv['bid'];
                        $msg = '选择版位有误请联系';
                        $res = array('code'=>0,'msg'=>$msg);
                        echo json_encode($res);
                        die;
                    }
                    $box_arr[$bk]['btype'] = 1;

                }
            }
            sort($box_arr);
            $res = array('code'=>1,'data'=>$box_arr);
        } else {
            $res = array('code'=>1,'data'=>array());
        }
        //有可能变成非从0开始

        echo json_encode($res);
        die;
    }



    /**
     * @desc 广告投放列表
     * @method getlist
     * @access public
     * @http post
     * @param numPerPage intger 显示每页记录数
     * @param pageNum intger 当前页数
     * @param _order $record 排序
     * @return void|array
     */
    public function getlist(){
        $tou_arr = C('TOU_STATE');
        unset($tou_arr[3]);
        $this->assign('to_ar', $tou_arr);
        $now_date = date("Y-m-d");
        $pubadsModel = new \Admin\Model\PubAdsModel();
        
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order',' pads.create_time ');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1 and pads.state != 2 and pads.is_remove=0";
        $name = I('serachads');
        $tou_state = I('tou_state',0);
        $dap = array(
            'now'=>$now_date,
            'tou_st'=>$tou_state,
        );

        if ($name) {
            $this->assign('adsname', $name);
            $where .= " and ads.name like '%".$name."%' ";
        }
        if($tou_state) {
            if(1 == $tou_state) {
                $where.=" AND pads.start_date >'$now_date' AND sbox.box_id > 0";
            }
            if(2 == $tou_state) {
                $where .= " AND pads.start_date <= '$now_date'
                AND pads.end_date >= '$now_date' AND sbox.box_id > 0";
            }
            if(3 == $tou_state) {
                $where .=" AND pads.end_date < '$now_date' AND sbox.box_id > 0";
            }
            if(4 == $tou_state) {
                $where .=" AND (sbox.box_id IS NULL OR  sbox.box_id = 0) ";
            }
            $this->assign('to_state', $tou_state);
        } else {
            $where .=" AND (( pads.end_date >= '$now_date' AND sbox.box_id > 0) or (pads.end_date >= '$now_date' AND pads.type=2))";
        }
        $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
        
        
        $field = 'm.oss_addr image_cover,ads.name,pads.is_remove,pads.id,pads.ads_id,pads.start_date,pads.end_date, pads.type type,pads.state stap,pads.state estate';
        $group = 'pads.id';
        $result = $pubadsModel->getList($field, $where,$group, $orders,$start,$size);

        array_walk($result['list'], function(&$v, $k)use($dap){
            
            $now_date = strtotime( $dap['now']);
            $v['start_date'] = strtotime( $v['start_date'] );
            $v['end_date'] = strtotime( $v['end_date'] );
            $tou_state = $dap['tou_st'];
            if( 1 == $v['type'] ) {
                $v['pub'] = '按版位发布';
                if( $tou_state == 2) {
                    $v['tp'] = 2;
                    $v['state'] = '投放中';
                }
                if ($tou_state == 1 ) {
                    $v['tp'] = 1;
                    $v['state'] = '未到投放时间';
                }
                if ($tou_state == 3 ) {
                    $v['tp'] = 3;
                    $v['state'] = '投放完毕';
                }
                if($tou_state == 0) {
                    if( $now_date >= $v['start_date'] && $now_date <=$v['end_date']) {

                        $v['tp'] = 2;
                        $v['state'] = '投放中';
                    } else if ($now_date < $v['start_date'] ) {
                        $v['tp'] = 1;
                        $v['state'] = '未到投放时间';
                    } else {
                        $v['tp'] = 3;
                        $v['state'] = '投放完毕';
                    }
                }
                $v['stap'] = '';
            }
            if( 2 == $v['type']) {
                $v['pub'] = '按酒楼发布';
                if($v['stap'] == 3) {
                    $v['stap'] = '版位计算中';
                    $v['state'] = '';
                }elseif($v['stap'] == 0 || $v['stap'] == 1){
                    $v['stap'] = '可投放';
                    if( $tou_state == 2) {
                        $v['tp'] = 2;
                        $v['state'] = '投放中';
                    }
                    if ($tou_state == 1 ) {
                        $v['tp'] = 1;
                        $v['state'] = '未到投放时间';
                    }
                    if ($tou_state == 3 ) {
                        $v['tp'] = 3;
                        $v['state'] = '投放完毕';
                    }
                    if ($tou_state == 4 ) {
                        $v['tp'] = 4;
                        $v['stap'] = '不可投放';
                        $where = '1=1 and pub_ads_id='.$v['id'];
                        $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
                        $count = $pub_ads_box_Model->getDataCount($where);

                        if($count <= 0) {
                            $v['stap'] = '不可投放';
                        }
                    }
                    if($tou_state == 0) {
                        $where = '1=1 and pub_ads_id='.$v['id'];
                        $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
                        $count = $pub_ads_box_Model->getDataCount($where);
                        if($count <= 0) {
                            $v['stap'] = '不可投放';
                        }elseif( $now_date >= $v['start_date'] && $now_date <=$v['end_date']) {

                            $v['tp'] = 2;
                            $v['state'] = '投放中';
                        } else if ($now_date < $v['start_date'] ) {
                            $v['tp'] = 1;
                            $v['state'] = '未到投放时间';
                        }
                    }
                }
            }
            //获取酒楼数
            $pubadshotelModel = new \Admin\Model\PubAdsHotelModel();
            $hotel_list = $pubadshotelModel->getAdsHotelId($v['id']);
            $v['hotel_nums'] = count($hotel_list);
        });
        $retp = $result['list'];
        //判断是否数组分页
        $this->assign('list', $retp);
        $this->assign('page',  $result['page']);
        $this->assign('oss_host',$oss_host);
        $this->display('advdevilerylist');
    }


    public function gethistorylist(){
        $tou_arr = C('TOU_STATE');
        unset($tou_arr[1]);
        unset($tou_arr[2]);
        $this->assign('to_ar', $tou_arr);
        $now_date = date("Y-m-d");
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order',' pads.create_time ');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1 and pads.state != 2 and pads.is_remove=1";
        $name = I('serachads');
        $tou_state = I('tou_state',0);
        if ($name) {
            $this->assign('adsname', $name);
            $where .= " and ads.`name` like '%".$name."%' ";
        }
        if(3 == $tou_state) {
            $where .=" AND pads.end_date < '$now_date' AND sbox.box_id > 0";
        }
        if(4 == $tou_state) {
            $where .=" AND (sbox.box_id IS NULL OR  sbox.box_id = 0) ";
    }
        if($tou_state == 0) {
            $where .=" AND ( ( pads.end_date < '$now_date' AND sbox.box_id > 0 )";
            $where .=" or (sbox.box_id IS NULL OR  sbox.box_id = 0) ) ";
        }
        $field = 'ads.name,pads.id,pads.ads_id,pads.start_date,pads.end_date, pads.type type,pads.state stap';
        $group = 'pads.id';
        $result = $pubadsModel->gethistory($where,$field,$group,  $orders,$start,$size);
        array_walk($result['list'], function(&$v, $k)use($now_date){
            $now_date = strtotime( $now_date);
            $v['start_date'] = strtotime( $v['start_date'] );
            $v['end_date'] = strtotime( $v['end_date'] );
            if( 1 == $v['type'] ) {
                $v['pub'] = '按版位发布';
                $v['state'] = '投放完毕';
                $v['stap'] = '';
            }
            if( 2 == $v['type']) {
                $v['pub'] = '按酒楼发布';
               if($v['stap'] ==  1){
                    $v['stap'] = '可投放';
                    $v['tp'] = 3;
                    $v['state'] = '投放完毕';
                    $where = '1=1 and pub_ads_id='.$v['id'];
                    $pub_ads_boxhi_Model = new \Admin\Model\PubAdsBoxHistoryModel();
                    $count = $pub_ads_boxhi_Model->getDataCount($where);
                    if($count <= 0) {
                        $v['stap'] = '不可投放';
                    }
                }
            }
        });
        $retp = $result['list'];
        $this->assign('list', $retp);
        $this->assign('page',  $result['page']);
        $this->display('advdevileryhitorylist');
    }
    /*
    * @desc 添加广告投放
    * @method adddevilery
    * @access public
    * @http NULL
    * @return void
    */
    public function adddevilery(){
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $map['state'] = array(array('eq',3),array('eq',0), 'or') ;
        $field = 'type,state';
        $pb_data = $pubadsModel->getWhere($map,$field);
        //$bool = false;
        if($pb_data) {
            foreach( $pb_data as $pk=>$pv) {
                if($pv['state']== 3) {
                    echo '<script>$.pdialog.closeCurrent();  alertMsg.error("有版位在计算中");</script>';
                }
            }
        }

        //城市
        $areaModel  = new \Admin\Model\AreaModel();
        $area_arr = $areaModel->getAllArea();
        //城市
        $userinfo = session('sysUserInfo');
        $pcity = $userinfo['area_city'];
        $is_city_search = 0;
        if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
            $pawhere = '1=1';
            $is_city_search = 1;
            $this->assign('is_city_search',$is_city_search);
        }else {
            $this->assign('is_city_search',$is_city_search); 
        }
        
        $this->assign('areainfo', $area_arr);
        $adv_tou_num = C('ADVE_OCCU')['num'];
        for($i=1;$i<=$adv_tou_num;$i++) {
            $touci_arr[$i] = $i.'次';
        }
        $this->assign('touci_arr', $touci_arr);
        $this->display('adddevilery');
    }

    public function showdetail() {
        if(IS_POST) {
            $adsid = I('post.pubhotelid','0','intval');
        } else {
            $adsid = I('deliveryid','0','intval');
        }
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,pads.state state,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        if($vinfo['state'] == 3) {
            //state置为0时就可以不显示发布中
            $this->error('广告正在发布中');
        }
        $this->assign('pubadsid', $adsid);
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $page = $start;
        $start  = ( $start-1 ) * $size;
        $send_state = I('sendadv_state', '0');
        if ($send_state == 0) {
            //获取总条数
            $where = 'adhotel.pub_ads_id='.$adsid.' and  sht.flag=0 and
         box.flag=0 and  room.flag=0 ';
            $field = 'COUNT(DISTINCT box.id) total';
           // $field = 'box.id bid';
            $pub_ads_hotel_Model = new \Admin\Model\PubAdsHotelModel();
            $group = '';
            $total_arr = $pub_ads_hotel_Model->getCurrentBox($field, $where,         $group);


            $count = $total_arr[0]['total'];
            //机顶盒为空的情况
            $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
            $field = 'count(*) ct';
            $where = ' 1=1 and bid = 0 and pub_ads_id='.$adsid;
            $group = '';
            $box_empty_count = $pub_ads_box_error->getWhere($where, $field, $group);
            $count = $box_empty_count[0]['ct'] + $count;
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $where = '1=1 and pub_ads_id='.$adsid;
            $not_normal_arr = $pub_ads_box_error->getList($where, $order,
                $start, $size);
            //var_dump($not_normal_arr);
            $not_normal_total = $not_normal_arr['count'];
            $pub_len = count($not_normal_arr['list']);
            if($pub_len == 0) {
                //获取当失败表为空时成功表所所补充的数据
                $not_page = ceil($not_normal_total/$size);
                $not_num = $not_normal_total%$size;
                if($not_num == 0) {
                    $first_pub_page = $page-$not_page;
                    $limit = ($first_pub_page-1)*$size;
                } else {
                    $first_pub_num = $size-$not_num;
                    $first_pub_page = $page-$not_page;
                    $limit = ($first_pub_page-1)*$size+$first_pub_num;
                }
                $field = "sht.name hname,sht.id hid,room.name rname,room.id rid, box.id bid,box.name bname, 0 error_type ";
                $where = '1=1 and pub_ads_id='.$adsid;
                $order='adbox.id desc';
                $group = 'adbox.box_id';
                $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
                $normal_box_arr = $pub_ads_box_Model->getBoxInfoBySize
                ($field, $where, $order,$group, $limit, $size);
                //var_dump($pub_ads_box_Model->getLastSql());
                $result['list'] = $normal_box_arr['list'];
            } elseif($pub_len<$size) {
                $left = $size - $pub_len;
                //从成功获取剩余数据
                $field = "sht.name hname,sht.id hid,room.name rname,room.id rid, box.id bid,box.name bname, 0 error_type ";
                $where = '1=1 and pub_ads_id='.$adsid;
                $order='adbox.id desc';
                $group = 'adbox.box_id';
                $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
                $normal_box_arr = $pub_ads_box_Model->getBoxInfoBySize
                ($field, $where, $order,$group, 0, $left);
                //var_dump($pub_ads_box_Model->getLastSql());

                $result['list'] = array_merge($not_normal_arr['list'],
                    $normal_box_arr['list']);
            }else{
                $result['list'] = $not_normal_arr['list'];
            }
        } else if ($send_state == 1) {
                //成功
            $field = "sht.name hname,sht.id hid,room.name rname,room.id
            rid, box.id bid,box.name bname, 0 error_type,count(adbox.box_id) boxnum  ";
            $where = '1=1 and pub_ads_id='.$adsid;
            $order='adbox.id desc';
            $group = 'adbox.box_id';
            $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
            $normal_box_arr = $pub_ads_box_Model->getBoxInfoBySize
            ($field, $where, $order,$group, $start, $size);
            //var_dump($pub_ads_box_Model->getLastSql());

            if(empty($normal_box_arr['list'])) {
                $count = 0;
            } else {
                $field = " count(DISTINCT box_id) as bnum";
                $where = '1=1 and pub_ads_id='.$adsid;
                $count = $pub_ads_box_Model->getWhere($where, $field);
                $count = $count[0]['bnum'];
            }

            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            //var_dump($pub_ads_box_Model->getLastSql());
            $result['list'] = $normal_box_arr['list'];
        } else if ($send_state == 2) {
                //失败
            $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
            $where = '1=1 and pub_ads_id='.$adsid;
            $not_normal_arr = $pub_ads_box_error->getList($where, $order,
                $start, $size);
            if(empty($not_normal_arr['list'])) {
                $not_normal_total = 0;
            } else {
                $not_normal_total = $not_normal_arr['count'];
            }

            $objPage = new Page($not_normal_total,$size);
            $show = $objPage->admin_page();
            //var_dump($pub_ads_box_Model->getLastSql());
            $result['list'] = $not_normal_arr['list'];
        }

        $error_state = C('PUB_ADS_HOTEL_ERROR');
        $ind = $start+1;
        foreach($result['list'] as &$rv) {
            $rv['ind'] = $ind;
            if($rv['error_type'] == 0) {
                $rv['error_msg'] = '酒楼：'.$rv['hname'].' 包间：'.$rv['rname'] .' 机顶盒：'.$rv['bname']
                    .'发送成功';
            }else{
                if($rv['error_type'] == 8) {
                    //获取酒楼信息
                    $hotelModel = new \Admin\Model\HotelModel();
                    $hotel_info = $hotelModel->getOne($rv['hid']);
                    $rv['error_msg'] = '酒楼：'.$hotel_info['name'].' '.$error_state[$rv['error_type']];
                } else{
                    $rv['error_msg'] = '酒楼：'.$rv['hname'].' 包间：'.$rv['rname'] .' 机顶盒：'.$rv['bname'].'  '
                        .$error_state[$rv['error_type']];
                }

            }
            $ind++;
        }
        $pub_ads_state = array(
            0=>'全部',
            1=>'成功',
            2=>'失败',
        );
        $result['page'] = $show;
        $this->assign('sendone', $send_state);
        $this->assign('pubhotelstate', $pub_ads_state);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('detaillist');
    }

    public function showadverjiulou(){

        $adsid = I('deliveryid','0','intval');
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,pads.state state,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        if($vinfo['state'] == 3) {
            //state置为0时就可以不显示发布中
            $this->error('广告正在发布中');
        }
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];

        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));
        //获取当前广告发布选择酒楼
        $where = 'adbox.pub_ads_id='.$adsid.' and  sht.flag=0 and
         box.flag=0 and  room.flag=0 ';
        $field = 'sht.id hid,box.id bid';
        $pub_ads_box_Model = new \Admin\Model\PubAdsBoxModel();
        $group = 'adbox.box_id';
        $normal_arr = $pub_ads_box_Model->getCurrentBox($field, $where, $group);
        $normal_hotel_arr = array_column($normal_arr, 'hid');
        $normal_hotel_arr = array_unique($normal_hotel_arr);
        $normal_hotel_num = count($normal_hotel_arr);
        $normal_box_arr = array_column($normal_arr, 'bid');
        $normal_box_arr = array_unique($normal_box_arr);
        $normal_box_num = count($normal_box_arr);
        //求出失败对应版位数
        $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
        $field = 'hid hotel_id, count(distinct bid) boxnum';
        $where = ' 1=1 and bid <>0 and pub_ads_id='.$adsid;
        $group = 'hid';
        $not_normal_arr = $pub_ads_box_error->getWhere($where, $field,
            $group);
        $not_box_arr = array_column($not_normal_arr,'boxnum');
        $not_hotel_arr = array_column($not_normal_arr,'hotel_id');
        $not_hotel_arr = array_unique($not_hotel_arr);
        //求出机顶盒为空的情况
        $field = 'hid';
        $where = ' 1=1 and bid = 0 and pub_ads_id='.$adsid;
        $group = '';
        $box_empty_arr = $pub_ads_box_error->getWhere($where, $field,
            $group);
        $box_empty_arr = array_column($box_empty_arr,'hid');
        $box_empty_arr = array_unique($box_empty_arr);
        $not_hotel_arr = array_merge($not_hotel_arr,$box_empty_arr);
        $hotel_arr = array_merge($not_hotel_arr,$normal_hotel_arr);
        $hotel_arr = array_unique($hotel_arr);
        $hotel_num = count($hotel_arr);
        $not_box_num = array_sum($not_box_arr);
        $box_num = $normal_box_num+$not_box_num;
        $not_hotel_num =  $hotel_num - $normal_hotel_num;
        /*if ($hotel_box_arr) {
            $hotel_num_arr = array_column($hotel_box_arr,'hid');
            //所有酒店
            $hotel_num_arr = array_unique($hotel_num_arr);
            $hotel_num = count($hotel_num_arr);
            $box_num_arr = array_column($hotel_box_arr,'bid');
            //所有机顶盒数
            $box_num_arr = array_unique($box_num_arr);
            $box_num = count($box_num_arr);
            //求出对应版位数
            $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
            $field = 'hid hotel_id, count(distinct bid) boxnum';
            $where = '1=1 and pub_ads_id='.$adsid;
            $group = 'hid';
            $not_normal_arr = $pub_ads_box_error->getWhere($where, $field,
                $group);
            $not_box_arr = array_column($not_normal_arr,'boxnum');
            $not_box_num = array_sum(array_unique($not_box_arr));
            $normal_box_num = $box_num-$not_box_num;
            $rep = array();
            //从box成功表拿到所有机顶盒
            $pub_ads_box = new \Admin\Model\PubAdsBoxModel();
            $pub_ads_box->


            //遍历得到该酒店所有机顶盒
            array_walk($hotel_box_arr, function($nv, $k)use(&$rep) {
                $rep[$nv['hid']][$nv['bid']]  = 1;
                return $rep;
            });











            $rea = array();
            $mp = array_walk($rep, function($rv, $rk)use($not_normal_arr) {
                foreach($not_normal_arr as $nk=>$nv) {
                    if($rk == $nv['hotel_id']) {
                        $len = count($rv);
                        if($len == $nv['boxnum']) {
                             break;
                        } else {
                            $rea[$rk] = 1;
                        }
                    }
                }
            });
            var_dump($rea);
            foreach($rep as $rk=>$rv) {
                foreach($not_normal_arr as $nk=>$nv) {
                    if($rk == $nv['hotel_id']) {
                        $len = count($rv);
                        if($len == $nv['boxnum']) {
                            unset($rep[$rk]);
                        }
                    }
                }
            }
            $normal_hotel_num = count(array_keys($rep));
            $not_hotel_num = $hotel_num - $normal_hotel_num;
        }*/
        $this->assign('hottotal', $hotel_num);
        $this->assign('boxtotal', $box_num);
        $this->assign('nothotnum', $not_hotel_num);
        $this->assign('notboxnum', $not_box_num);
        $this->assign('normal_hotel', $normal_hotel_num);
        $this->assign('normal_box',$normal_box_num);
        $this->assign('vinfo',$vinfo);
        $this->display('showadverjiulou');
    }


    public function showadverhisjiulou(){

        $adsid = I('deliveryid','0','intval');
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,pads.state state,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        if($vinfo['state'] == 3) {
            //state置为0时就可以不显示发布中
            $this->error('广告正在发布中');
        }
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];

        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));
        //获取当前广告发布选择酒楼
        $where = 'adbox.pub_ads_id='.$adsid.' and  sht.flag=0 and
         box.flag=0 and  room.flag=0 ';
        $field = 'sht.id hid,box.id bid';
        $pub_ads_boxhi_Model = new \Admin\Model\PubAdsBoxHistoryModel();
        $group = 'adbox.box_id';
        $normal_arr = $pub_ads_boxhi_Model->getCurrentBox($field, $where, $group);
        $normal_hotel_arr = array_column($normal_arr, 'hid');
        $normal_hotel_arr = array_unique($normal_hotel_arr);
        $normal_hotel_num = count($normal_hotel_arr);
        $normal_box_arr = array_column($normal_arr, 'bid');
        $normal_box_arr = array_unique($normal_box_arr);
        $normal_box_num = count($normal_box_arr);
        //求出失败对应版位数
        $pub_ads_boxhis_error = new \Admin\Model\PubAdsBoxErrorHisModel();
        $field = 'hid hotel_id, count(distinct bid) boxnum';
        $where = ' 1=1 and bid <>0 and pub_ads_id='.$adsid;
        $group = 'hid';
        $not_normal_arr = $pub_ads_boxhis_error->getWhere($where, $field,
            $group);
        $not_box_arr = array_column($not_normal_arr,'boxnum');
        $not_hotel_arr = array_column($not_normal_arr,'hotel_id');
        $not_hotel_arr = array_unique($not_hotel_arr);
        //求出机顶盒为空的情况
        $field = 'hid';
        $where = ' 1=1 and bid = 0 and pub_ads_id='.$adsid;
        $group = '';
        $box_empty_arr = $pub_ads_boxhis_error->getWhere($where, $field,
            $group);
        $box_empty_arr = array_column($box_empty_arr,'hid');
        $box_empty_arr = array_unique($box_empty_arr);
        $not_hotel_arr = array_merge($not_hotel_arr,$box_empty_arr);
        $hotel_arr = array_merge($not_hotel_arr,$normal_hotel_arr);
        $hotel_arr = array_unique($hotel_arr);
        $hotel_num = count($hotel_arr);
        $not_box_num = array_sum($not_box_arr);
        $box_num = $normal_box_num+$not_box_num;
        $not_hotel_num =  $hotel_num - $normal_hotel_num;
        /*if ($hotel_box_arr) {
            $hotel_num_arr = array_column($hotel_box_arr,'hid');
            //所有酒店
            $hotel_num_arr = array_unique($hotel_num_arr);
            $hotel_num = count($hotel_num_arr);
            $box_num_arr = array_column($hotel_box_arr,'bid');
            //所有机顶盒数
            $box_num_arr = array_unique($box_num_arr);
            $box_num = count($box_num_arr);
            //求出对应版位数
            $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
            $field = 'hid hotel_id, count(distinct bid) boxnum';
            $where = '1=1 and pub_ads_id='.$adsid;
            $group = 'hid';
            $not_normal_arr = $pub_ads_box_error->getWhere($where, $field,
                $group);
            $not_box_arr = array_column($not_normal_arr,'boxnum');
            $not_box_num = array_sum(array_unique($not_box_arr));
            $normal_box_num = $box_num-$not_box_num;
            $rep = array();
            //从box成功表拿到所有机顶盒
            $pub_ads_box = new \Admin\Model\PubAdsBoxModel();
            $pub_ads_box->


            //遍历得到该酒店所有机顶盒
            array_walk($hotel_box_arr, function($nv, $k)use(&$rep) {
                $rep[$nv['hid']][$nv['bid']]  = 1;
                return $rep;
            });











            $rea = array();
            $mp = array_walk($rep, function($rv, $rk)use($not_normal_arr) {
                foreach($not_normal_arr as $nk=>$nv) {
                    if($rk == $nv['hotel_id']) {
                        $len = count($rv);
                        if($len == $nv['boxnum']) {
                             break;
                        } else {
                            $rea[$rk] = 1;
                        }
                    }
                }
            });
            var_dump($rea);
            foreach($rep as $rk=>$rv) {
                foreach($not_normal_arr as $nk=>$nv) {
                    if($rk == $nv['hotel_id']) {
                        $len = count($rv);
                        if($len == $nv['boxnum']) {
                            unset($rep[$rk]);
                        }
                    }
                }
            }
            $normal_hotel_num = count(array_keys($rep));
            $not_hotel_num = $hotel_num - $normal_hotel_num;
        }*/
        $this->assign('hottotal', $hotel_num);
        $this->assign('boxtotal', $box_num);
        $this->assign('nothotnum', $not_hotel_num);
        $this->assign('notboxnum', $not_box_num);
        $this->assign('normal_hotel', $normal_hotel_num);
        $this->assign('normal_box',$normal_box_num);
        $this->assign('vinfo',$vinfo);
        $this->display('showadverjiulou');
    }

    public function showhisdelivery() {

        $adsid = I('deliveryid','0','intval');
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];

        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));
        //获取当前广告选择版位
        $pubadshisboxModel = new \Admin\Model\PubAdsBoxHistoryModel();
        $map['adbox.pub_ads_id'] = $adsid;
        $field = 'sht.id hid,sht.name hname,box.name bname,adbox.box_id';
        $group = 'adbox.box_id';
        $hotel_box_arr = $pubadshisboxModel->getCurrentBox($field, $map, $group);
        if ($hotel_box_arr) {
            $hotel_num_arr = array_column($hotel_box_arr,'hid');
            //所有酒店
            $hotel_num = count(array_unique($hotel_num_arr));
            $box_num_arr = array_column($hotel_box_arr,'box_id');
            //所有机顶盒数
            $box_num = count(array_unique($box_num_arr));
        }
        $dap = array();
        $position_arr = $this->array_group_by($hotel_box_arr, 'hid');

        $this->assign('hottotal',$hotel_num);
        $this->assign('boxtotal',$box_num);
        $this->assign('vinfo',$vinfo);
        $this->assign('pos_ar',$position_arr);
        $this->assign('action_url','advert/editAds');
        $this->display('showadver');

    }


    public function showhisdetail() {
        if(IS_POST) {
            $adsid = I('post.pubhotelid','0','intval');
        } else {
            $adsid = I('deliveryid','0','intval');
        }
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,pads.state state,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        if($vinfo['state'] == 3) {
            //state置为0时就可以不显示发布中
            $this->error('广告正在发布中');
        }
        $this->assign('pubadsid', $adsid);
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $page = $start;
        $start  = ( $start-1 ) * $size;
        $send_state = I('sendadv_state', '0');
        if ($send_state == 0) {
            //获取总条数
            $where = 'adhotel.pub_ads_id='.$adsid.' and  sht.flag=0 and
         box.flag=0 and  room.flag=0 ';
            $field = 'COUNT(DISTINCT box.id) total';
            // $field = 'box.id bid';
            $pub_ads_hotel_Model = new \Admin\Model\PubAdsHotelModel();
            $group = '';
            $total_arr = $pub_ads_hotel_Model->getCurrentBox($field, $where,         $group);


            $count = $total_arr[0]['total'];
            //机顶盒为空的情况
            $pub_ads_boxhis_error = new \Admin\Model\PubAdsBoxErrorHisModel();
            $field = 'count(*) ct';
            $where = ' 1=1 and bid = 0 and pub_ads_id='.$adsid;
            $group = '';
            $box_empty_count = $pub_ads_boxhis_error->getWhere($where, $field, $group);
            $count = $box_empty_count[0]['ct'] + $count;
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $where = '1=1 and pub_ads_id='.$adsid;
            $not_normal_arr = $pub_ads_boxhis_error->getList($where, $order,
                $start, $size);
            //var_dump($not_normal_arr);
            $not_normal_total = $not_normal_arr['count'];
            $pub_len = count($not_normal_arr['list']);
            if($pub_len == 0) {
                //获取当失败表为空时成功表所所补充的数据
                $not_page = ceil($not_normal_total/$size);
                $not_num = $not_normal_total%$size;
                if($not_num == 0) {
                    $first_pub_page = $page-$not_page;
                    $limit = ($first_pub_page-1)*$size;
                } else {
                    $first_pub_num = $size-$not_num;
                    $first_pub_page = $page-$not_page;
                    $limit = ($first_pub_page-1)*$size+$first_pub_num;
                }
                $field = "sht.name hname,sht.id hid,room.name rname,room.id rid, box.id bid,box.name bname, 0 error_type ";
                $where = '1=1 and pub_ads_id='.$adsid;
                $order='adbox.id desc';
                $group = 'adbox.box_id';
                $pub_ads_boxhis_Model = new \Admin\Model\PubAdsBoxHistoryModel();
                $normal_box_arr = $pub_ads_boxhis_Model->getBoxInfoBySize
                ($field, $where, $order,$group, $limit, $size);
                //var_dump($pub_ads_box_Model->getLastSql());
                $result['list'] = $normal_box_arr['list'];
            } elseif($pub_len<$size) {
                $left = $size - $pub_len;
                //从成功获取剩余数据
                $field = "sht.name hname,sht.id hid,room.name rname,room.id rid, box.id bid,box.name bname, 0 error_type ";
                $where = '1=1 and pub_ads_id='.$adsid;
                $order='adbox.id desc';
                $group = 'adbox.box_id';
                $pub_ads_boxhis_Model = new \Admin\Model\PubAdsBoxHistoryModel();
                $normal_box_arr = $pub_ads_boxhis_Model->getBoxInfoBySize
                ($field, $where, $order,$group, 0, $left);
                //var_dump($pub_ads_box_Model->getLastSql());

                $result['list'] = array_merge($not_normal_arr['list'],
                    $normal_box_arr['list']);
            }else{
                $result['list'] = $not_normal_arr['list'];
            }
        } else if ($send_state == 1) {
            //成功
            $field = "sht.name hname,sht.id hid,room.name rname,room.id
            rid, box.id bid,box.name bname, 0 error_type,count(adbox.box_id) boxnum  ";
            $where = '1=1 and pub_ads_id='.$adsid;
            $order='adbox.id desc';
            $group = 'adbox.box_id';
            $pub_ads_boxhis_Model = new \Admin\Model\PubAdsBoxHistoryModel();
            $normal_box_arr = $pub_ads_boxhis_Model->getBoxInfoBySize
            ($field, $where, $order,$group, $start, $size);
            //var_dump($pub_ads_box_Model->getLastSql());

            if(empty($normal_box_arr['list'])) {
                $count = 0;
            } else {
                $field = " count(DISTINCT box_id) as bnum";
                $where = '1=1 and pub_ads_id='.$adsid;
                $count = $pub_ads_boxhis_Model->getWhere($where, $field);
                $count = $count[0]['bnum'];
            }

            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            //var_dump($pub_ads_box_Model->getLastSql());
            $result['list'] = $normal_box_arr['list'];
        } else if ($send_state == 2) {
            //失败
            $pub_ads_box_errorhis = new \Admin\Model\PubAdsBoxErrorHisModel();
            $where = '1=1 and pub_ads_id='.$adsid;
            $not_normal_arr = $pub_ads_box_errorhis->getList($where, $order,
                $start, $size);
            if(empty($not_normal_arr['list'])) {
                $not_normal_total = 0;
            } else {
                $not_normal_total = $not_normal_arr['count'];
            }

            $objPage = new Page($not_normal_total,$size);
            $show = $objPage->admin_page();
            //var_dump($pub_ads_box_Model->getLastSql());
            $result['list'] = $not_normal_arr['list'];
        }

        $error_state = C('PUB_ADS_HOTEL_ERROR');
        $ind = $start+1;
        foreach($result['list'] as &$rv) {
            $rv['ind'] = $ind;
            if($rv['error_type'] == 0) {
                $rv['error_msg'] = '酒楼：'.$rv['hname'].' 包间：'.$rv['rname'] .' 机顶盒：'.$rv['bname']
                    .'发送成功';
            }else{
                if($rv['error_type'] == 8) {
                    //获取酒楼信息
                    $hotelModel = new \Admin\Model\HotelModel();
                    $hotel_info = $hotelModel->getOne($rv['hid']);
                    $rv['error_msg'] = '酒楼：'.$hotel_info['name'].' '.$error_state[$rv['error_type']];
                } else{
                    $rv['error_msg'] = '酒楼：'.$rv['hname'].' 包间：'.$rv['rname'] .' 机顶盒：'.$rv['bname'].'  '
                        .$error_state[$rv['error_type']];
                }

            }
            $ind++;
        }
        $pub_ads_state = array(
            0=>'全部',
            1=>'成功',
            2=>'失败',
        );
        $result['page'] = $show;
        $this->assign('sendone', $send_state);
        $this->assign('pubhotelstate', $pub_ads_state);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('detailhistorylist');
    }

    public function showdelivery() {

        $adsid = I('deliveryid','0','intval');
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $field = ' pads.id,pads.start_date,pads.end_date,
        pads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and pads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $pubadsModel->getPubAdsInfoByid($field, $where);
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];

        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));
        //获取当前广告选择版位
        $pubadsboxModel = new \Admin\Model\PubAdsBoxModel();
        $map['adbox.pub_ads_id'] = $adsid;
        $field = 'sht.id hid,sht.name hname,box.name bname,adbox.box_id';
        $group = 'adbox.box_id';
        $hotel_box_arr = $pubadsboxModel->getCurrentBox($field, $map, $group);
        if ($hotel_box_arr) {
            $hotel_num_arr = array_column($hotel_box_arr,'hid');
            //所有酒店
            $hotel_num = count(array_unique($hotel_num_arr));
            $box_num_arr = array_column($hotel_box_arr,'box_id');
            //所有机顶盒数
            $box_num = count(array_unique($box_num_arr));
        }
        $dap = array();
        $position_arr = $this->array_group_by($hotel_box_arr, 'hid');

        $this->assign('hottotal',$hotel_num);
        $this->assign('boxtotal',$box_num);
        $this->assign('vinfo',$vinfo);
        $this->assign('pos_ar',$position_arr);
        $this->assign('action_url','advert/editAds');
        $this->display('showadver');

    }
    /**
     * @desc 删除广告 并且删除对应酒楼的广告缓存
     */
    public function deleteAds(){
        $redis = SavorRedis::getInstance();
        $redis->select(12);
        $cache_key_pre = C('PROGRAM_ADS_CACHE_PRE');
        $pub_ads_id = I('get.id','0','intval');
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        $where = array();
        $where['id'] = $pub_ads_id;
        $where['state'] = array('neq',2);
        $field = 'id,type,state'; 
        $infos = $m_pub_ads->getWhere($where, $field);
        if(empty($infos)){
            $this->error('该广告不存在');
        }
        $info = $infos[0];
        if($info['state'] !=1){
            $this->error('广告版位正在生成中，不能删除，请稍后删除');
        }
        $ret = $m_pub_ads->updateInfo(array('id'=>$pub_ads_id), array('state'=>2));
        if($ret){
            $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
            $m_pub_ads_hotel = new \Admin\Model\PubAdsHotelModel();
            $box_list = $m_pub_ads_box->getBoxArrByPubAdsId($pub_ads_id);
            $hotel_list = $m_pub_ads_hotel->getAdsHotelId($pub_ads_id);
            foreach($box_list as $key=>$v){
                $redis->remove($cache_key_pre.$v['box_id']);
            }
            $redis->select(10);
            $v_hotel_list_key = C('VSMALL_HOTELLIST');
            $redis_result = $redis->get($v_hotel_list_key);
            $v_hotel_list = json_decode($redis_result,true);
            $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
            
            $cache_key = C('VSMALL_ADS');
            foreach ($hotel_list as $key=>$v){
                if(in_array($v['hotel_id'], $v_hotel_arr)){
                
                    $keys_arr = $redis->keys($cache_key.$v['hotel_id']."*");
                    foreach($keys_arr as $vv){
                            $redis->remove($vv);
                    }
                }
            }
            $this->output('删除成功', 'advdelivery/getlist', 2);
        }else {
            $this->error('删除失败');
        }
        
        
    }
    public function selectHotel(){
        //navTab.reloadFlag("task/index");
        $id = I('id',0,'intval');
        $m_pubads = new \Admin\Model\PubAdsModel();
        $m_pubads_hotel = new \Admin\Model\PubAdsHotelModel();
        if(IS_POST){
            //获取该广告之前选择的酒楼id
            $original_hotel_list = $m_pubads_hotel->getAdsHotelId($id);
            //print_r($original_hotel_list);exit;
            $original_ids = array();
            $original_ids = array_column($original_hotel_list, 'hotel_id');
            
            
            $ids = I('ids'); //所选酒楼
            if(empty($ids)){
                echo '<script>
                
                alertMsg.error("请选择酒楼！");</script>';
            }
            $rts = array_diff($original_ids,$ids);
            if(empty($rts)){
                echo '<script>
                navTab.closeTab("advdelivery/selecthotel");
                alertMsg.error("所选酒楼无修改！");</script>';
            }else {
                
                $m_pubads_box = new \Admin\Model\PubAdsBoxModel();
                
                $m_pubads->startTrans();  //事务开始
                
                //1、删除pub_ads_hotel表的数据
                $where = [];
                $where['pub_ads_id'] = $id;
                $hotel_ret = $m_pubads_hotel->delData($where);
                
                //2、删除pub_ads_box表的数据
                $box_ret = $m_pubads_box->delData($where);
                
                //3、插入pub_ads_hotel表新酒楼数据
                $data = [];
                foreach($ids as $key=>$v){
                    $data[$key]['hotel_id'] = $v;
                    $data[$key]['pub_ads_id'] = $id;
                    
                }
                $add_hotel_ret = $m_pubads_hotel->addData($data);
                
                //4、更新pub_ads表的状态以及创建、更新时间
                $data = [];
                $data['state'] = 3;
                $data['create_time'] = date('Y-m-d H:i:s');
                $data['update_time'] = date('Y-m-d H:i:s');
                $ads_ret = $m_pubads->where(array('id'=>$id))->savor($data);
                
                if($hotel_ret && $box_ret && $add_hotel_ret && $ads_ret){
                    
                }else {
                    $m_pubads->rollback();
                }
                //5、删除缓存
            }
        }else{
            
            $where = [];
            $where['id'] = $id;
            $where['state'] = array('neq',2);
            $pubads_info = $m_pubads->field('state,type')->where($where)->find();
            
            if(empty($pubads_info)){
                echo '<script>
                navTab.closeTab("advdelivery/selecthotel");
                alertMsg.error("该广告不存在或已被删除！");</script>';
            }
            if($pubads_info['state']!=1){
                echo '<script>
                navTab.closeTab("advdelivery/selecthotel");
                alertMsg.error("广告计算中不可修改！");</script>';
            }
            if($pubads_info['type']!=2){
                echo '<script>
                navTab.closeTab("advdelivery/selecthotel");
                alertMsg.error("该广告未按酒楼发布！");</script>';
            }
            
            //获取广告所选酒楼
            
            $where = [];
            $where['a.pub_ads_id'] = $id;
            $fields = 'a.hotel_id,hotel.name hotel_name,area.region_name,hotel.hotel_box_type';
            $hotel_list = $m_pubads_hotel->alias('a')
            ->join('savor_hotel  hotel on a.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->where($where)
            ->field($fields)
            ->select();
            $hotel_box_type = C('hotel_box_type');
            $this->assign('hotel_box_type',$hotel_box_type);
            $this->assign('list',$hotel_list);
            $this->assign('id',$id);
            $this->display();
        }
        
    }
    public static  function array_group_by($arr, $key)
    {
        $grouped = [];
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key

        if (func_num_args() > 2) {
            $args = func_get_args();
            //[0]=>hname
            //var_dump(array_slice($args, 2, func_num_args()));

            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));

               // $grouped[$key] = call_user_func_array('array_group_by', $parms);
                $grouped[$key] = call_user_func(array('AdvdeliveryController','array_group_by'), $parms);
            }
        }
        return $grouped;
    }


}
