<?php
/**
 * @AUTHOR: baiyutao.
 * @PROJECT: PhpStorm
 * @FILE: HotelViewStatisticsController.class.php
 * @CREATE ON: 2018/3/1 9:54
 * @Purpose: 酒楼收视统计
 * @Package Name: Admin\Controller（namespace)
 * @VERSION: X.X
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

/**
 * @ Purpose:
 * 酒楼收视统计，
 * @Package Name: Admin\Controller（namespace)
 */
class HotelviewrepController extends BaseController {
    /*
     * class Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /*
     * @Purpose:列表展示
     * @Access:public
     * @Method:getList
     * @Http:post
     * @param numPerPage integer 显示每页记录数
     * @param pageNum integer 第几页
     * @param _order string 排列顺序
     * @param _sort string 排列顺序

     * @return mixed
     *
     */
    public function getList(){
        $starttime = I('adsstarttime','');
        $endtime = I('adsendtime','');
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order',' a.hotel_id desc');
        $where = "1=1";
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $start  = ( $start-1 ) * $size;
        if ( empty($starttime) ) {
            $st_time = $yesday.' 00:00:00 ';
        } else {
            $st_time = $starttime.' 00:00:00 ';
        }
        $where .= ' AND a.view_date >= "'.$st_time.'"';
        if ( empty($endtime) ) {
            $en_time = $yesday.' 23:59:59 ';
        } else {
            $en_time = $endtime.' 23:59:59 ';
        }
        $where .= ' AND a.view_date <= "'.$en_time.'"';
        if($st_time < $en_time) {
            $this->assign('s_time',$starttime);
            $this->assign('e_time',$endtime);

        }else{
            $this->error('开始时间必须小于等于结束时间');
        }

        $htrpModel = new \Admin\Model\HotelViewReportModel();
        $field = 'sum(`online_duration`) duration, sum(view_duration) vdur
            ,sum(`view_times`) vtime,a.hotel_id,a.hotel_name,a.room_type,a.box_name';
        $group = 'a.hotel_id';
        $result = $htrpModel->getList($field, $where, $order,$group, $start, $size);
        //算总数

        $total_result = $htrpModel->getAllData($where, $field);
        $total_adv = round($total_result[0]['vdur']/$total_result[0]['vtime'], 1);
        $this->assign('total_adv', $total_adv);

        foreach($result['list'] as &$rv) {

            foreach($rv as &$s) {
                if(empty($s)) {
                    $s = 0;
                }
            }
        }
        foreach($result['list'] as $rk=>$rv) {
            if($rv['vtime'] == 0) {
                $result['list'][$rk]['adv_vtime'] = 0;
            } else {
                $result['list'][$rk]['adv_vtime'] = round($rv['vdur']/$rv['vtime'], 1);
            }
            if ( $rv['duration'] <= 60) {
                $result['list'][$rk]['duration'] = $rv['duration'].'分';
            } else {
                $hour= floor($rv['duration']/60);
                $min = $rv['duration']%60;
                $result['list'][$rk]['duration'] = $hour.'时'.$min.'分';
            }

        }





        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showlist');
    }



}