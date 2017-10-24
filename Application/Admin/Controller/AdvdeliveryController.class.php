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


class AdvdeliveryController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
        $this->lnum = 10;
    }

    public function  doAddAdvBox() {

        $now_date = date("Y-m-d H:i:s");
        $h_b_arr = $_POST['hbarr'];
        $h_b_arr = json_decode($h_b_arr, true);
        $now_date = date("Y-m-d H:i:s");
        $save['ads_id'] = I('post.marketid','237');
        $save['start_date'] = I('post.start_time', '');
        $save['end_date'] = I('post.end_time', '');
        $save['play_times'] = I('post.play_times', '');
        $userInfo = session('sysUserInfo');
        $save['create_time'] = $now_date;
        $save['update_time'] = $now_date;
        $save['creator_id'] = $userInfo['id'];
        $save['state'] = 0;
        $oneday_count = 3600 * 24;  //一天有多少秒
        //明天
        $save['end_date'] = date("Y-m-d H:i:s", strtotime($save['end_date']) + $oneday_count-1);
        $pubadsModel = new \Admin\Model\PubAdsModel();
        //插入pub_ads表

        $pubadsModel->startTrans();

        $res = $pubadsModel->addData($save, 0);

        $tmp[] = array();
        if($res) {
            //插入box表
            $pub_ads_id = $pubadsModel->getLastInsID();
            $pubadsBoxModel = new \Admin\Model\PubAdsBoxModel();
            foreach ($h_b_arr as $k=>$v) {
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
                $this->output('添加成功','advdelivery/getadvdeliverylist',2);
            }else {
                $pubadsModel->rollback();
                $this->error('添加失败');
            }
        } else {
            $pubadsModel->rollback();
            $this->error('添加失败');
        }




    }

    public function getAllBox($hotel_id) {
        $where = '1=1 and sht.id='.$hotel_id.' and sht.state=1 and
        sht.flag=0
        and sht.hotel_box_type in (2,3) and room.state=1
        and room.flag=0 and box.flag=0 and box.state=1 and
        tv.flag=0 and tv.state=1 ';
        $hotelModel = new \Admin\Model\HotelModel();
        $field = 'box.id bid,box.name bname';
        $order = ' box.id asc ';
        $box_arr = $hotelModel->getBoxOrderMacByHid($field, $where, $order);
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
        //被占用的数组
        $ocu_arr = $pubadsModel->getBoxPlayTimes($map, $field);
        //var_export($ocu_arr);
        $adv_promote_num_arr = C('ADVE_OCCU');
        $adv_promote_num = $adv_promote_num_arr['num'];
        $ocu_len = count($ocu_arr);
        //var_export($ocu_len);
        //取广告位数组
        $lid_arr = array_column($ocu_arr, 'lid');
        //var_export($lid_arr);
        $l_arr = array();
        foreach($lid_arr as $lv) {
            if($lv != 0) {
                $l_arr[] = $lv;
            }
        }
        $lc = array_unique($l_arr);
        $lid_len = count($lc);
       /* var_export($lid_len);
        var_export($ocu_len);*/
        if ($lid_len != count($l_arr)) {
            //脚本写入有误
           return 'wrongworng';
        }
        $bool = false;
        if ($type == 1) {
            if (empty($ocu_arr)) {
                $bool = true;
            } else {
                $l_len = $adv_promote_num-$ocu_len-$p_tiems;
                if($l_len>=0) {
                    $bool = true;
                }else {

                }
            }
            return $bool;
        } else {
            $num_arr = range(1, $adv_promote_num);
            $ad_arr = array_filter($num_arr, function($result, $item)use($lid_arr) {
                if(in_array($result, $lid_arr)) {
                    return false;
                } else {
                    return true;
                }
            });
            return $ad_arr;
        }
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
        //获取节目单对应最大id还没写且在预约时间内<今天
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
        }
        $field = 'sht.id hid, sht.name hname';
        $hotelModel = new \Admin\Model\HotelModel();
        $where .= ' and '.$h_str;
        $orders = 'convert(sht.name using gbk) asc';
        $result = $hotelModel->getHotelidByArea($where, $field, $orders);
        $msg = '';
        $res = array('code'=>1,'msg'=>$msg,'data'=>$result);
        echo json_encode($res);
        /*var_dump($hotelModel->getLastSql());
        var_dump($result);*/
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
            $msg = '投放开始时间必须小于结束时间';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        if($start_time < $now_date) {
            $msg = '投放开始时间必须大于今天';
            $res = array('code'=>0,'msg'=>$msg);
            echo json_encode($res);
            die;
        }
        $dat = array (
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'play_times'=>$play_times,
        );
        $box_arr = $this->getAllBox($hotel_id);

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
        $where = "1=1 and pads.state != 2";
        $name = I('serachads');
        $tou_state = I('tou_state');
        $beg_time = I('starttime','');
        $end_time = I('end_time','');
        $dap = array(
            'now'=>$now_date,
            'tou_st'=>$tou_state,
        );
        if($beg_time > $end_time) {
            $msg = '开始时间必须小于等于结束时间';
            $this->error($msg);
        }
        if ($name) {
            $this->assign('adsname', $name);
            $where .= " and ads.name like '%".$name."%' ";
        }
        if($beg_time) {
            $this->assign('starttime', $beg_time);
            $where.=" AND pads.start_date >='$beg_time'";
        }
        if($end_time) {
            $this->assign('end_time', $end_time);
            $where.=" AND pads.end_date <='$end_time'";
        }
        if($tou_state) {
            $this->assign('to_state', $tou_state);
        }

        $field = 'ads.name,pads.id,pads.start_date,pads.end_date';
        $result = $pubadsModel->getList($field, $where, $orders,$start,$size);
        array_walk($result['list'], function(&$v, $k)use($dap){
            $now_date = strtotime( $dap['now']);
            $v['start_date'] = strtotime( $v['start_date'] );
            $v['end_date'] = strtotime( $v['end_date'] );

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


        });
        if($tou_state != 0) {
            $result['list'] = array_filter($result['list'], function(&$v, $k)use($tou_state){
                if($v['tp'] != $tou_state) {
                    return 0;
                } else {
                    return 1;
                }
            });
            //数组分页
            $len = count($result['list']);
            $objPage = new \Common\Lib\Page($len,$size);
            $show = $objPage->admin_page();
            $result['page'] = $show;
            $retp = array_slice($result['list'], $start, $size);
        } else {
            $retp = $result['list'];
        }


        //判断是否数组分页
        $this->assign('list', $retp);
        $this->assign('page',  $result['page']);
        $this->display('advdevilerylist');
    }


    /*
    * @desc 添加广告投放
    * @method adddevilery
    * @access public
    * @http NULL
    * @return void
    */
    public function adddevilery(){
        //城市
        $areaModel  = new \Admin\Model\AreaModel();
        $area_arr = $areaModel->getAllArea();

        $this->assign('areainfo', $area_arr);

        $this->display('adddevilery');
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
