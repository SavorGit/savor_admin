<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Think\Exception;

class AdverwarnreportController extends BaseController{

	public $path = 'category/img';
	public $oss_host = '';
	public function __construct() {
		parent::__construct();
	}


    /**
     * @desc 查看广告播放异常预警
     */
    public function getlist(){
        $size       = I('numPerPage',50);     //显示每页记录数
        $start      = I('pageNum',1);         //当前页码
        $order      = I('_order',' reportadsPeriod asc, awarn.last_time asc '); //排序字段
        $start = ($start-1)* $size;
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $where =" 1=1 ";
        
        $start_date = I('start_date');        // 开始日期
        $end_date   = I('end_date');          // 结束日期
        $type       = I('type');              //设备类型
        $areaid     = I('areaid');            //城市id
        $hotel_name = I('hotel_name','','trim'); //酒楼名称
        
        if(!empty($start_date) && !empty($end_date)){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        if(!empty($hotel_name)){
            $where .=" and hotel_name like '%{$hotel_name}%'";
            $this->assign('hotel_name',$hotel_name);
        }
        //城市
        $userinfo = session('sysUserInfo');
        $gid = $userinfo['groupid'];

        $usergrp = new \Admin\Model\SysusergroupModel();
        $p_user_arr = $usergrp->getInfo($gid);
        $pcity = $p_user_arr['area_city'];
        $is_city_search = 0;
        if($p_user_arr['id'] == 1 || empty($p_user_arr['area_city'])  ) {
            $is_city_search = 1;
            $this->assign('is_city_search',$is_city_search);
            $this->assign('pusera', $p_user_arr);
        }else {
            $this->assign('is_city_search',$is_city_search);
           // $where .= "	AND area_id in ($pcity)";
        }
        $field = 'awarn.*,sb.mac box_mac,  ( CASE awarn.report_adsPeriod WHEN "" THEN "999999999999999"
	WHEN NULL THEN "999999999999999"
ELSE awarn.report_adsPeriod END ) AS reportadsPeriod ';
        $adWarnModel = new \Admin\Model\AdverWarnModel();
        $result = $adWarnModel->geWarntlist($field,$where,$order,$start,$size);

        //var_export($result['list']);

        array_walk($result['list'], function(&$v, $k){
            //修改时间
            $v['hea'] = '否';
            $v['adp'] = '否';
            $v['vid'] = '否';
            if($v['last_time'] >= 24) {
                $v['hea'] = '是';
                $day = floor($v['last_time']/24);
                $hour = floor($v['last_time']%24);
                $v['last_time'] = $day.'天'.$hour.'小时';
            } else {
                $v['last_time'] = $v['last_time'].'小时';
            }
            if( $v['report_adsperiod'] < $v['new_adsperiod'] ) {
                $v['adp'] = '是';
            }
            if( $v['report_demperiod'] != $v['new_demperiod'] ) {
                $v['vid'] = '是';
            }

        });
        

        $m_area_info = new \Admin\Model\AreaModel();
        $area_arr = $m_area_info->getAllArea();
        $this->assign('area', $area_arr);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('adwarnlist');
    }
}
