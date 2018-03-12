<?php
/**
 * @AUTHOR: baiyutao.
 * @PROJECT: PhpStorm
 * @FILE: SingleProgramViewstatistics.class.php
 * @CREATE ON: 2018/3/1 11:42
 * @VERSION: X.X
 * @Purpost:单个节目收视统计
 * @Package Name: Admin\Controller (namespace)
 *
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;


class SingleproreportController extends BaseController {
    public function __construct(){
        parent::__construct();
    }

    /**
     * @Purpose :获取输入模糊查询
     * @Access:public
     * @Methodname:getadsAjax
     * @Http:post
     * @param string $adsname
     * @return [json] 返回ajax广告数据
     */
    public function getadsAjax(){
        $searchtitle = I('adsname','');
        $st_type = I('sta_type', 'pro');
        $where = "1=1";
        $where .= " AND type = '".$st_type."'";
        $field = "media_id id, media_name name,media_id";
        if ($searchtitle) {
            $where .= "	AND media_name LIKE '%{$searchtitle}%'";
        }
        $group = 'media_id';
        $singModel = new \Admin\Model\Statisticses\SingleproReportModel();
        $result = $singModel->getWhere($where, $field, $group);
        echo json_encode($result);
        die;
    }

    public function emptyData($size){
        $result['list'] = array();
        $count = 0;
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $result['page'] = $show;
        return $result;
    }

    /**
     * @Purpose:所有数据
     * @Access:public
     * @Methodname: getList
     * @Http:post
     * @param
     * @return [type] [description]
     */
    public function getList(){

        $starttime = I('adsstarttime','');
        $endtime = I('adsendtime','');
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order',' a.media_id desc');
        $adsname = I('contentast');
        $hidden_adsid = I('hadsid','',0);
        $where = "1=1";
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $today = date("Y-m-d");
        $start  = ( $start-1 ) * $size;
        $sta_ad_type =  I('sta_ad_type','pro');
        $this->assign('sta_ad_k',$sta_ad_type);
        $where .= " AND a.type = '".$sta_ad_type."'";

        if($starttime >= $today || $endtime >= $today) {
            $this->error('时间筛选范围错误');
        }

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
        $singlerepModel = new \Admin\Model\Statisticses\SingleproReportModel();
        $field = 'sum(`view_times`) vtime, sum(view_duration) vdur
            ,a.media_id,a.media_name,a.type,a.duration';
        $group = 'a.media_id';
        if ( $adsname ) {
            $this->assign('adsname', $adsname);
            $this->assign('contentast', $adsname);
            $this->assign('hidden_adsid', $hidden_adsid);
            $map['media_id'] = $hidden_adsid;
            $ads_info = $singlerepModel->getOneData($map);
            if ($ads_info['media_name'] != $adsname) {
                $this->error('请输入后选择内容与广告');
            }else{
                if(!$hidden_adsid){
                    $this->error('请输入后选择内容与广告');
                }
                if(empty($ads_info)){
                    $result = $this->emptyData($size);
                }else{
                    //获取结果
                    $group = '';
                    $order = '';
                    $where .= ' AND a.media_id = '.$hidden_adsid;
                    $result = $singlerepModel->getList($field, $where, $order,$group, $start, $size);
                }
            }
        }else{
            $result = $singlerepModel->getList($field, $where, $order,$group, $start, $size);
        }
        $statis_type = C('STATISTICS_TYPE');
        $this->assign('statics_type', $statis_type);
        //获取内容与广告统计表
        $mestaModel = new \Admin\Model\MediaStaModel();
        $get_s = date("Ymd", strtotime($starttime));
        $get_e = date("Ymd", strtotime($endtime));
        $wherea = '1=1 and sbo.flag=0 and
        sbo.state=1 and sbo.adv_mach=1 ';
        $wherea.= "	AND sms.play_date >= '$get_s'";
        $wherea .= "	AND sms.play_date <= '$get_e'";
        $mefield = 'play_time meplay';

        foreach($result['list'] as &$av) {

            foreach($av as &$sb) {
                if(empty($sb)) {
                    $sb = 0;
                }
            }
        }
        foreach($result['list'] as $rk=>$rv) {
            if($rv['vtime'] == 0) {
                $result['list'][$rk]['adv_vtime'] = 0;
            } else {
                $result['list'][$rk]['adv_vtime'] = round($rv['vdur']/$rv['vtime'], 1);
            }
            $sp = " AND sms.media_id = ".$rv['media_id'];
            $result['list'][$rk]['type'] = $statis_type[$rv['type']];
            $whereb = $wherea.$sp;
            $me_sta_arr = $mestaModel->getAdvMachine($whereb, $mefield);
            if ( empty($me_sta_arr) ) {
                $result['list'][$rk]['ratio'] = 0;
            } else {
                $me_time_arr = array_column($me_sta_arr, 'meplay');
                $me_time = array_sum($me_time_arr);
                $result['list'][$rk]['ratio'] = round($rv['vdur']/$me_time, 1);
            }
            if ( $rv['duration'] <= 60) {
                $result['list'][$rk]['duration'] = $rv['duration'].'秒';
            } else {
                if($rv['duration'] < 3600) {
                    $min = floor($rv['duration']/60);
                    $sec = $rv['duration']%60;
                    $result['list'][$rk]['duration'] = $min.'分'.$sec.'秒';
                } else {
                    $hour= floor($rv['duration']/3600);
                    $seca = $rv['duration']%3600;
                    if($seca<60) {
                        $sec = $seca;
                    } else {
                        $min = floor($rv['duration']/60);
                        $sec = $rv['duration']%60;
                    }
                    $result['list'][$rk]['duration'] = $hour.'时'.$min.'分'.$sec.'秒';
                }

            }

        }

        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showlist');
    }
}