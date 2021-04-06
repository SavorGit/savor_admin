<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Lib\SavorRedis;
use Behavior\AgentCheckBehavior;
use \Common\Lib\MailAuto;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class ExcelController extends Controller
{

    public function exportExcel($expTitle, $expCellName, $expTableData,$filename,$type=1,$user_path = '/temp/test.xls')
    {
        set_time_limit(9000);
        ini_set("memory_limit", "8018M");
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        if($filename == 'hotel') {
            $tmpname = '酒楼资源总表';
        }else if($filename =='hotelboxlist'){
            $tmpname = '酒楼机顶盒总表';
        }
         else if ($filename == 'boxlostreport') {
            $tmpname = '机顶盒失联表';
        }  else if ($filename == 'screencastreport') {
            $tmpname = '投屏次数点播表';
        }  else if ($filename == 'mobile_interaction_final') {
            $tmpname = 'APP包间首次互动数据';
        }  else if ($filename == 'first_mobile_download') {
            $tmpname = '酒楼首次打开数据';
        }  else if ($filename == 'downloadcount') {
            $tmpname = '下载量报表统计';
        }  else if ($filename == 'appcreen') {
            $tmpname = 'app与大屏互动统计';
        }  else if ($filename == 'hotelscreen') {
            $tmpname = '酒楼大屏统计';
        }else if($filename == "allappdownload"){
            $tmpname = 'App下载统计总表';
        }else if($filename == "hotelbillinfo"){
            $tmpname = '对账单酒楼信息联系表';
        }else if($filename == 'toothwash'){
            $tmpname = '活动订单';
        }else if($filename == 'contentads'){
            $tmpname = '内容与广告统计';
        }else if($filename =='hotelBv'){
            $tmpname = '酒楼信息';
        }else if($filename =='contentlink'){
            $tmpname = '内容链接明细';
        }else if($filename =='expcontentwxauth'){
            $tmpname = '文章微信授权日志';
        }else if ($filename == 'optionerrobox'){
            $tmpname = '运维端异常机顶盒';
        }else if($filename == 'dinnerapp_hall_log') {
            $tmpname = '餐厅端日志上报';
        }else if($filename =='option_sh_task_list'){
            $tmpname='上海发布任务列表';
        }else if($filename == 'bind_invite_hotel_info') {
            $tmpname = '餐厅端绑定酒楼数据';
        }else if($filename == 'box_version_condition') {
            $tmpname = '机顶盒版本情况分布';
        }else if($filename == 'box_lost_version_condition') {
            $tmpname = '失联机顶盒分布';
        }else if($filename == 'adver_warn_report') {
            $tmpname = '广告播放异常预警';
        }else if($filename == 'noheartlog'){
            $tmpname = '无心跳版位';
        }else if($filename == 'exphotelmaintain') {
            $tmpname = '酒楼关联合作维护人';
        }else if($filename == 'option_sh_signle_pic') {
            $tmpname = '上海单机版换画明细';
        }else if($filename=='hhboxlist'){
            $tmpname = '版位列表';
        }else if($filename =='option_task_list'){
            $tmpname = '运维任务明细';
        }else if($filename=='smallapp_forsacreen'){
            $tmpname = '小程序投屏统计';
        }else if($filename=='smallapp_staticnet'){
            $tmpname = '小程序网络监测';
        }else if($filename=='smallapp_hotel_level'){
            $tmpname = '小程序酒楼评级';
        }else if($filename=='smallapp_hotel_level_detail'){
            $tmpname = '小程序酒楼评级数据详情';
        }else if($filename=='smallapp_generalsituationdetail'){
             $tmpname = '小程序概况详细数据';
        }else if($filename=='smallapp_hoteldata'){
             $tmpname = '小程序酒楼数据详细数据';
        }else if($filename=='smallapp_boxdata'){
             $tmpname = '小程序版位数据详细数据';
         }else if($filename=='smallapp_hotelgradedetail'){
             $tmpname = '小程序概况酒楼评级趋势详细数据';
         }else if($filename=='hotel_update_adv_list'){
             $tmpname = '45天内有更新的酒楼宣传片';
         }else if($filename =='smallapp_forsacreen_box'){
             $tmpname = '小程序包间投屏统计';
         }else if($filename =='box_err_elec'){
             $tmpname = "全国异常机顶盒电源情况";
         }else if($filename=='exportNetBox'){
             $tmpname =  "全国网络机顶盒互动数据统计";
         }else if($filename=='smallapp_interactdiff'){
             $tmpname = '小程序互动数据对比统计';
         }else if($filename=='smallapp_boxdiff'){
             $tmpname=$expTitle;
         }else if($filename=='exportSlBoxList'){
		     $tmpname='失联7天的酒楼版位统计';
	     }else if($filename=='exportEmptyWifiBoxList'){
	         $tmpname= '漏填wifi_mac版位详情';
	         
	     }else if($filename=='noLogBoxListInfo'){
	         $tmpname = '未上传日志的版位详情';
	     }else if($filename=='forscreenTimes'){
	         $tmpname = '投屏时长分布';
	     }else if($filename =='easySellAds'){
	         $tmpname = '易售媒体广告模板';
	     }else if($filename =='fourBwtoLc'){
	         $tmpname = '四地版位数据统计';
	     }else if($filename=='sale_task_list'){
             $tmpname = '销售端使用情况';
         }else if($filename=='sale_hotel_use2list'){
             $tmpname = '销售端酒楼使用2天以上';
         }else if($filename=='channel_hotellist'){
             $tmpname = '渠道部需求表';
         }else if($filename=='exportSl14BoxList'){
             $tmpname = '连续14天失联版位';
         }else if($filename=='haveSl7BoxList'){
             $tmpname = "有失联7天的版位";
         }else if($filename=='user_scan_qrcode_detail'){
             $tmpname ="用户扫码日志";
         }else if($filename=='user_link_wifi_err_detail'){
             $tmpname ="用户链接wifi错误日志";
         }else if($filename =='user_wifi_forscreen_detail'){
             $tmpname = '用户极简版投屏日志';
         }else if($filename == 'have_3day_forscreen_boxlist'){
             $tmpname = '超过三天有普通版投屏的版位信息';
         }else if($filename =='4g_box_forscreen'){
             $tmpname = '4G盒子投屏测试';
         }else if($filename =='exportMallOrder'){
             $tmpname ="商场订单汇总";
         }else if($filename =='topSpeedForscreen'){
             $tmpname = '扫极简版码用户链接wifi投屏数据统计';
         }else if($filename =='hotelqfdata'){
             $tmpname = '酒楼扫码投屏统计';
         }else if($filename=='hotelassessdata'){
             $tmpname = '酒楼数据考核';
         }else if($filename =='exportForVideo'){
             $tmpname ='视频投屏数据';
         }else if($filename=='exportRewardmoney'){
             $tmpname = '打赏明细';
         }else if($filename=='boxinteract'){
             $tmpname = '正常互动屏版位明细';
         }


        if($filename == "heartlostinfo"){
            $fileName = $expTitle;
            $acp = 3;
        }else{
            $fileName = $tmpname . date('_YmdHis');//or $xlsTitle
        }

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        //   $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        //$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Hello');

        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $expCellName[$i][1]);

        }
        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        if($filename == 'hotel') {
            $objPHPExcel->getActiveSheet()->getColumnDimension()->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(45);
            $objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            //$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }else if($filename == 'boxlostreport'){
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        }else if($filename == 'hotelbillinfo'){
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        }else if($filename == "heartlostinfo"){
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        }else if($filename == "contentads"){
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        }
        if($type==1){
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
            header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }else {
            ob_start();
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();
            
            return file_put_contents($user_path, $content);
            
        }
        
    }
    
    /**
     *
     * 导出内容与广告相关数据备份
     */
    public function expcontentadsbaks(){

        $starttime = I('starttime','');
        $endtime = I('endtime','');
        $adsname = I('adsname');
        $hidden_adsid = I('hadsid');
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $tmp_box_tv = array();
        $where = "1=1";
        if ( $adsname ) {
            $adModel = new \Admin\Model\AdsModel();
            $ads_info = $adModel->find($hidden_adsid);
            if(empty($ads_info)){
                $tmp_box_tv = array();
            }else{
                //判断是否在节目单中发布过
                $ads_media_id = $ads_info['media_id'];
                $mItemModel = new \Admin\Model\MenuItemModel();
                $field = "distinct(`menu_id`)";
                $where .= " AND ads_id={$hidden_adsid}  ";
                $order = 'menu_id asc';
                $menu_arr = $mItemModel->getWhere($where,$order, $field);
                    //判断是否在酒店发布过
                    $where = "1=1";
                    foreach($menu_arr as $ma){
                        $menu_id_str .= $ma['menu_id'].',';
                    }
                    $menu_id_str = substr($menu_id_str,0,-1);
                    $where .= " AND menu_id in ( ".$menu_id_str.')';
                    $mhotelModel = new \Admin\Model\MenuHotelModel();
                    $hotelModel = new \Admin\Model\HotelModel();
                    $field = "distinct(`hotel_id`)";
                    $order = 'hotel_id asc';
                    $hotel_id_arr = $mhotelModel->getWhere($where, $order, $field);
                    //根据hotelid得出box
                    $where = '1=1';
                    foreach($hotel_id_arr as $ha){
                        $hotel_id_str .= $ha['hotel_id'].',';
                    }
                    $hotel_id_str = substr($hotel_id_str,0,-1);
                    $where .= " AND sht.id in ( ".$hotel_id_str.')';
                    $field = 'sht.id hotelid,sht.name,room.id
                              rid,room.name rname,box.name box_name, box.mac,sari
                              .region_name cname';
                    $box_info = $hotelModel->getBoxMacByHid($field, $where);

                    $field = 'sum(play_count) plc,
                    sum(play_time) plt,mac,group_concat(`play_date`) pld';
                    $starttime = date("Ymd", strtotime($starttime));
                    $endtime = date("Ymd", strtotime($endtime));
                    $where = '1=1';
                    $mestaModel = new \Admin\Model\MediaStaModel();
                    $where .= " AND media_id = ".$ads_media_id;
                    $where .= "	AND play_date >= '{$starttime}'";
                    $where .= "	AND play_date <= '{$endtime} '";
                    $group = 'mac';
                    $me_sta_arr = $mestaModel->getWhere($where, $field, $group);
                    //二维数组合并
                    $mp = array_column($me_sta_arr, 'mac');
                    $me_sta_arr = array_combine($mp, $me_sta_arr);
                    //var_dump($mestaModel->getLastSql());
                    //dump($box_info);
                    //dump($me_sta_arr);
                    //获取电视数量
                    //进行比较
                    foreach ($box_info as $bk=>$bv) {
                        $map_mac = $bv['mac'];
                        //先判断是否存在
                        if(array_key_exists($map_mac, $tmp_box_tv)) {
                            $tmp_box_tv[$map_mac]['tv_count'] +=1;
                            continue;
                        }else {
                            if(array_key_exists($map_mac, $me_sta_arr)) {
                                $mv = $me_sta_arr[$map_mac];
                                $mv['pld'] = preg_replace('/(\s)*/','', $mv['pld']);
                                $day_arr = explode(',',$mv['pld']);
                                $day_arr = array_unique($day_arr);
                                $day_str = implode(',', $day_arr);
                                $day_len = count($day_arr);
                                $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                                $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                                $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                                $tmp_box_tv[$map_mac]['play_count'] = $mv['plc'];
                                $tmp_box_tv[$map_mac]['play_time'] = $mv['plt'];
                                $tmp_box_tv[$map_mac]['play_days'] = $day_len;
                                $tmp_box_tv[$map_mac]['publication'] = $day_str;
                                $tmp_box_tv[$map_mac]['tv_count'] = 1;
                                $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                                $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                            }else{
                                $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                                $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                                $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                                $tmp_box_tv[$map_mac]['play_count'] = '';
                                $tmp_box_tv[$map_mac]['play_time'] = '';
                                $tmp_box_tv[$map_mac]['play_days'] = '';
                                $tmp_box_tv[$map_mac]['publication'] = '';
                                $tmp_box_tv[$map_mac]['tv_count'] = 1;
                                $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                                $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                            }
                            unset($me_sta_arr[$map_mac]);
                        }

                    }
                    $tmp_box_tv = array_values($tmp_box_tv);
                }

            //需要将传过来name与隐藏域进行对比再次确定它传过来的值是正确的
            //判断是否是广告列表中
        }else{
            $tmp_box_tv = array();
        }
        $xlsCell = array(
            array('cityname', '地区'),
            array('hotel_name', '酒楼名称'),
            array('rname', '包间名称'),
            array('box_name','机顶盒名称'),
            array('mac', 'mac'),
            array('tv_count', '电视数量'),
            array('play_count', '播出次数'),
            array('play_time', '播出时长'),
            array('play_days', '播出天数'),
            array('publication', '上刊日期')
        );
        $xlsName = 'contentads';
        $filename = 'contentads';
        $this->exportExcel($xlsName, $xlsCell, $tmp_box_tv,$filename);
    }





    /**
     *
     * 导出内容与广告相关数据
     */
    public function expcontentads(){
        $starttime = I('starttime','');
        $endtime = I('endtime','');
        $adsname = I('adsname');
        $hidden_adsid = I('hadsid');
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $tmp_box_tv = array();
        //$hidden_adsid = 98;//429
        // $adsname = '刺客信条';
        //$starttime = '2017-08-02';
        //$endtime = '2017-08-08';
        //  $hidden_adsid = 98;
        $where = "1=1";
        if ( $adsname ) {
            $adModel = new \Admin\Model\AdsModel();
            $ads_info = $adModel->find($hidden_adsid);
            if(empty($ads_info)){
                $tmp_box_tv = array();
            }else{
                $hotel_box_type_arr = C('heart_hotel_box_type');
                $hotel_box_type_arr = array_keys($hotel_box_type_arr);
                $space = '';
                $hotel_box_type_str = '';
                foreach($hotel_box_type_arr as $key=>$v){
                    $hotel_box_type_str .= $space .$v;
                    $space = ',';
                }
                $ads_media_id = $ads_info['media_id'];
                $mhotelModel = new \Admin\Model\MenuHotelModel();
                $hotelModel = new \Admin\Model\HotelModel();
                $field = "distinct(`id`) hotel_id";
                $order = 'id asc';
                $where .= " and name not like '%永峰%' ";
                $where .= " and hotel_box_type in ($hotel_box_type_str) ";
                $hotel_id_arr = $hotelModel->getWhereorderData($where,  $field, $order);
                //根据hotelid得出box
                $where = '1=1 and box.state = 1 and box.flag = 0 ';
                $hotel_id_str =  array_reduce($hotel_id_arr ,
                    function($result , $v){
                        Return $result.','.$v['hotel_id'];
                    }
                );
                $hotel_id_str = substr($hotel_id_str,1);
                $where .= " AND sht.id in ( ".$hotel_id_str.')';
                $field = 'sht.id hotelid,sht.name,room.id
                              rid,room.name rname,box.name box_name, box.mac,sari
                              .region_name cname';
                $box_info = $hotelModel->getBoxMacByHid($field, $where);

                $field = 'sum(play_count) plc,
                    sum(play_time) plt,mac,group_concat(`play_date`) pld';
                $starttime = date("Ymd", strtotime($starttime));
                $endtime = date("Ymd", strtotime($endtime));
                $where = '1=1';
                $mestaModel = new \Admin\Model\MediaStaModel();
                $where .= " AND media_id = ".$ads_media_id;
                $where .= "	AND play_date >= '{$starttime}'";
                $where .= "	AND play_date <= '{$endtime} '";
                $group = 'mac';
                $me_sta_arr = $mestaModel->getWhere($where, $field, $group);
                //二维数组合并
                $mp = array_column($me_sta_arr, 'mac');
                $me_sta_arr = array_combine($mp, $me_sta_arr);
                //获取电视数量
                //进行比较
                foreach ($box_info as $bk=>$bv) {
                    $map_mac = $bv['mac'];
                    //先判断是否存在
                    if(array_key_exists($map_mac, $tmp_box_tv)) {
                        $tmp_box_tv[$map_mac]['tv_count'] +=1;
                        continue;
                    }else {
                        if(array_key_exists($map_mac, $me_sta_arr)) {
                            $mv = $me_sta_arr[$map_mac];
                            $mv['pld'] = preg_replace('/(\s)*/','', $mv['pld']);
                            $day_arr = explode(',',$mv['pld']);
                            $day_arr = array_unique($day_arr);
                            sort($day_arr);
                            $day_str = implode(',', $day_arr);
                            $day_len = count($day_arr);
                            $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                            $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                            $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                            $tmp_box_tv[$map_mac]['play_count'] = $mv['plc'];
                            $tmp_box_tv[$map_mac]['play_time'] = $mv['plt'];
                            $tmp_box_tv[$map_mac]['play_days'] = $day_len;
                            $tmp_box_tv[$map_mac]['publication'] = $day_str;
                            $tmp_box_tv[$map_mac]['tv_count'] = 1;
                            $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                            $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                        }else{
                            $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                            $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                            $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                            $tmp_box_tv[$map_mac]['play_count'] = '';
                            $tmp_box_tv[$map_mac]['play_time'] = '';
                            $tmp_box_tv[$map_mac]['play_days'] = '';
                            $tmp_box_tv[$map_mac]['publication'] = '';
                            $tmp_box_tv[$map_mac]['tv_count'] = 1;
                            $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                            $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                            $tmp_box_tv[$map_mac]['hotel_id'] = $bv['hotelid'];
                        }
                        unset($me_sta_arr[$map_mac]);
                    }

                }
                $tmp_box_tv = array_reduce($tmp_box_tv, function($result, $item){
                    $result[$item['hotel_id']][] = $item;
                    return $result;
                });
                ksort($tmp_box_tv);
                $tmp_box_tv = array_reduce($tmp_box_tv, function($result, $item){
                    foreach($item as $k=>$vp){
                        $result[$vp['mac']] = $vp;
                    }
                    return $result;
                });
                $tmp_box_tv = array_values($tmp_box_tv);
            }

            //需要将传过来name与隐藏域进行对比再次确定它传过来的值是正确的
            //判断是否是广告列表中
        }else{
            $tmp_box_tv = array();
        }
        $xlsCell = array(
            array('cityname', '地区'),
            array('hotel_name', '酒楼名称'),
            array('rname', '包间名称'),
            array('box_name','机顶盒名称'),
            array('mac', 'mac'),
            array('tv_count', '电视数量'),
            array('play_count', '播出次数'),
            array('play_time', '播出时长'),
            array('play_days', '播出天数'),
            array('publication', '上刊日期')
        );
        $xlsName = 'contentads';
        $filename = 'contentads';
        $this->exportExcel($xlsName, $xlsCell, $tmp_box_tv,$filename);
    }

    /**
     *
     * 导出心跳相关数据
     */

    public function expheartlost(){
        /*
        导出所有涉及酒店而不根据心跳
        然后与心跳表  对比
        导出
        导出所有涉及酒店
        然后与心跳表  对比*/
        $time = time();
        $heartModel = new \Admin\Model\HeartLogModel();
        $areaModel  = new \Admin\Model\AreaModel();
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $type = I('get.type');
        $main_v = I('get.main_v');
        $hbt_v = I('get.hbt_v');
        $name = I('get.name');
        $area_v = I('get.area_v');
        $areainfo = $areaModel->find($area_v);
        $arname = $areainfo['region_name'];
        $where = ' 1=1 and sht.state = 1 and sht.flag = 0 ';
        //小平台
        if($type == 1){
            $field = 'hex.mac_addr mac,h.hotel_box_type, h.name, hex.hotel_id';
            $xlsName = date("Ymd Hi",$time).$arname.' '.' 小平台心跳情况';
        }else{
            $field = 'b.mac, h.id hotel_id, h.name,h.hotel_box_type,h.remark,h.maintainer ';
            $xlsName = date("Ymd Hi",$time).$arname.' 机顶盒心跳情况';
        }
        if ($main_v) {
            $where .= "	AND sht.maintainer LIKE '%{$main_v}%' ";
        }
        if ($hbt_v) {
            $where .= "	AND sht.hotel_box_type = $hbt_v";
        }else{
            $where .= "	AND (sht.hotel_box_type = 2 or sht.hotel_box_type = 3)";
        }
        if ($area_v) {
            $where .= "	AND sht.area_id = $area_v ";
        }
        if($name){
            $where .= "	AND sht.name LIKE '%{$name}%' ";
        }
        $hboxlist = $heartModel->getAllBox($where,$field,$type);
        //file_put_contents(APP_PATH.'/Runtime/Logs/Admin/1527.txt',$heartModel->getLastSql().PHP_EOL,FILE_APPEND);
        if($type == 1){
            //获取机顶盒数
            foreach ($hboxlist as $rk=>$rv) {
                $number = $heartModel->getBoxNum($rv['hotel_id']);
                if($number==0){
                    unset($hboxlist[$rk]);
                }
            }
        }

        if($type == 1){
            $hfield = 'hotel_id,box_mac mac,max(`last_heart_time`) AS lt';
        }else{
            $hfield = 'hotel_id,sb.state bstate,sb.flag  boflag,box_mac mac,max(`last_heart_time`) AS lt';
        }

        $hearList  = $heartModel->getWhereData($hfield,$type);
        //file_put_contents(APP_PATH.'/Runtime/Logs/Admin/1527.txt',$heartModel->getLastSql().PHP_EOL,FILE_APPEND);
        if ($hboxlist) {
            if($type == 1){

                //获取心跳mac地址小平台
                //由于hotel_id不重复所以可以直接使用函数
                //做一个排重
                if ($hearList) {
                    $tmp = array();
                    foreach($hearList as $hk=>$hv){
                        if(in_array($hv['hotel_id'], $tmp)){
                            unset($hearList[$hk]);
                        }else if(empty($hv['mac'])){
                            unset($hearList[$hk]);
                        }
                        else{
                            $tmp[] = $hv['hotel_id'];
                        }
                        continue;
                    }
                    $h_arr = array_column($hearList, 'hotel_id');
                    $hearList = array_combine($h_arr, $hearList);
                    //flag 1:正常24以内2.24以外3.7天以外
                    //$hp = var_export($hearList,true);
                    foreach($hboxlist as $hk =>$hbv){
                        $hid = $hbv['hotel_id'];
                        if(array_key_exists($hid, $hearList)){
                            //计算时长
                            // dump($hid);
                            $l_time = strtotime($hearList[$hid]['lt']);
                            $ftime = $time-$l_time;
                            $hboxlist[$hk]['htime'] = $ftime;
                            //测试进行修改86400
                            if($ftime<86400){
                                $hboxlist[$hk]['flag'] = '1';
                                $hboxlist[$hk]['bflag'] = '0';
                                $hboxlist[$hk]['lost_time'] = '正常';
                                $hboxlist[$hk]['rate'] = '0';
                            }else if($ftime>604800){
                                $hboxlist[$hk]['flag'] = '0';
                                $hboxlist[$hk]['bflag'] = '1';
                                $hboxlist[$hk]['lost_time'] = '七天以上';
                                $hboxlist[$hk]['htime'] = '1893455000';
                                $hboxlist[$hk]['rate'] = '100%';
                            }else{
                                $hboxlist[$hk]['flag'] = '0';
                                $hboxlist[$hk]['bflag'] = '1';
                                $hboxlist[$hk]['lost_time'] = $this->sec2Time($ftime);
                                $hboxlist[$hk]['rate'] = '100%';
                            }
                        }else{

                            $hboxlist[$hk]['flag'] = '0';
                            $hboxlist[$hk]['bflag'] = '1';
                            $hboxlist[$hk]['htime'] = '1893456000';
                            $hboxlist[$hk]['lost_time'] = '七天以上';
                            $hboxlist[$hk]['rate'] = '100%';
                        }
                        $hboxlist[$hk]['total'] = 1;
                    }
                    $order_arr = array();
                    $order_arr_h = array();
                    foreach($hboxlist as $hval) {
                        $order_arr[] = $hval['htime'];
                        $order_arr_h[] = $hval['hotel_id'];

                    }

                    $arp = array();
                    $flag =0;
                    $bflag = 0;
                    $total = 0;
                    foreach($hboxlist as $hkk=>$hval) {
                        $flag += $hval['flag'];
                        $total += $hval['total'];
                        $bflag += $hval['bflag'];
                        foreach($hotel_box_type_arr as $hk=>$hv){
                            if($hk == $hval['hotel_box_type']){
                                $hboxlist[$hkk]['hotel_box_type'] = $hv;
                            }
                        }

                    }
                    $ce_len = count($hboxlist);
                    $arp['name'] = '总计'.$ce_len.'家酒楼';
                    $arp['hotel_box_type'] = '';
                    $arp['flag'] = $flag;
                    $arp['bflag'] = $bflag;
                    $arp['total'] = $total;
                    $arp['rate'] = round($bflag/$total*100).'%';
                    $arp['lost_time'] = '';
                    array_multisort($order_arr,SORT_DESC,$order_arr_h,SORT_ASC, $hboxlist);
                    array_unshift($hboxlist, $arp);
                }else{
                    foreach($hboxlist as $hk =>$hbv){
                        $hboxlist[$hk]['flag'] = '0';
                        $hboxlist[$hk]['bflag'] = '1';
                        $hboxlist[$hk]['htime'] = '1893456000';
                        $hboxlist[$hk]['lost_time'] = '七天以上';
                        $hboxlist[$hk]['rate'] = '100%';
                        $hboxlist[$hk]['total'] = 1;
                    }
                    $arp = array();
                    $flag =0;
                    $bflag = 0;
                    $total = 0;
                    foreach($hboxlist as $hkk=>$hval) {
                        $flag += $hval['flag'];
                        $total += $hval['total'];
                        $bflag += $hval['bflag'];
                        foreach($hotel_box_type_arr as $hk=>$hv){
                            if($hk == $hval['hotel_box_type']){
                                $hboxlist[$hkk]['hotel_box_type'] = $hv;
                            }
                        }

                    }
                    $ce_len = count($hboxlist);
                    $arp['name'] = '总计'.$ce_len.'家酒楼';
                    $arp['hotel_box_type'] = '';
                    $arp['flag'] = $flag;
                    $arp['bflag'] = $bflag;
                    $arp['total'] = $total;
                    $arp['rate'] = round($bflag/$total*100).'%';
                    $arp['lost_time'] = '';
                    array_multisort($order_arr,SORT_DESC,$order_arr_h,SORT_ASC, $hboxlist);
                    array_unshift($hboxlist, $arp);
                }
                $xlsCell = array(
                    array('name', '酒楼名称'),
                    array('hotel_box_type', '小平台类型'),
                    array('flag', '正常'),
                    array('bflag', '异常'),
                    array('total', '总计'),
                    array('rate', '异常率(%)'),
                    array('lost_time', '失联时长'),
                );
            }else{
                //同样做排重
                $new_arr_heart = array();
                $heart_all = array();
                $nsp = array();
                if ($hearList) {
                    $tmp = array();
                   // $hearListpp = var_export($hearList, true);
                   //  file_put_contents(APP_PATH.'/Runtime/Logs/Admin/1527.txt',$hearListpp.PHP_EOL,FILE_APPEND);
                    foreach($hearList as $hk=>$hv){
                        if(in_array($hv['mac'], $tmp)){
                            unset($hearList[$hk]);
                        }else if($hv['bstate'] != 1 || $hv['boflag'] != 0) {
                            unset($hearList[$hk]);
                        } else {
                            $tmp[] = $hv['mac'];
                        }
                        continue;
                    }
                    foreach($hearList as $hv){
                        $new_arr_heart[$hv['hotel_id']][] = $hv;
                    }
                    foreach($hboxlist as $hbv){
                        $heart_all[$hbv['hotel_id']][] = $hbv;
                    }
                    $nsp = array();
                    foreach ($heart_all as $hea=>$hev){
                        $aflag = 0;
                        $bflag = 0;
                        $total = 0;
                        if(array_key_exists($hea, $new_arr_heart)){
                            //再运算
                            $orign = $hev;
                            $comp_arr = $new_arr_heart[$hea];
                            $orign_mac  = array_column($orign, 'mac');
                            $comp_mac = array_column($comp_arr, 'mac');

                            $len = count(array_diff($orign_mac, $comp_mac));
                            $co_ar_len = count($comp_arr);
                            $bflag = $len;
                            //获取心跳中不超过一天的,得到正常值
                            $aflag = $this->filtertime($comp_arr, $time);
                            //心跳
                            $fail_count = $bflag + $co_ar_len - $aflag;
                            $total = count($orign);
                            $nsp[$hea]['flag'] = $aflag;
                            $nsp[$hea]['bflag'] = $fail_count;
                            $nsp[$hea]['total'] = $total;
                            $nsp[$hea]['rate'] = round($fail_count/$total*100);
                        }else{
                            //根本不存在
                            $nsp[$hea]['flag'] = 0;
                            $nsp[$hea]['bflag'] = count($hev);
                            $nsp[$hea]['total'] = $nsp[$hea]['bflag'];
                            $nsp[$hea]['rate'] = '100';
                        }
                        $nsp[$hea]['maintainer'] = $hev[0]['maintainer'];
                        $nsp[$hea]['name'] = $hev[0]['name'];
                        $nsp[$hea]['hotel_box_type'] = $hev[0]['hotel_box_type'];
                        $nsp[$hea]['remark'] = $hev[0]['remark'];
                        $nsp[$hea]['hotelid'] = $hea;
                    }
                    $flag = 0;
                    $bflag = 0;
                    $total = 0;
                    $order_arr = array();
                    $order_arr_h = array();
                    foreach($nsp as $nval) {
                        $flag += $nval['flag'];
                        $total += $nval['total'];
                        $bflag += $nval['bflag'];

                    }
                    foreach($nsp as $hval) {
                        $order_arr[] = $hval['rate'];
                        $order_arr_h[] = $hval['hotelid'];
                    }
                    $ca_len = count($nsp);
                    $arp = array();
                    $arp['name'] = '总计'.$ca_len.'家酒楼';
                    $arp['flag'] = $flag;
                    $arp['bflag'] = $bflag;
                    $arp['total'] = $total;
                    $arp['rate'] = round($bflag/$total*100);
                    $arp['maintainer'] = '';
                    $arp['hotel_box_type'] = '';
                    $arp['remark'] = '';
                    array_multisort($order_arr,SORT_DESC,$order_arr_h,SORT_ASC, $nsp);
                    array_unshift($nsp, $arp);
                    foreach($nsp as $nk=>$nv){
                        foreach($hotel_box_type_arr as $hk=>$hv){
                            if($hk == $nv['hotel_box_type']){
                                $nsp[$nk]['hotel_box_type'] = $hv;
                            }
                        }
                        $nsp[$nk]['rate'] = $nsp[$nk]['rate'] .'%';
                    }
                    $hboxlist = $nsp;
                }else{
                    foreach($hboxlist as $hbv){
                        $heart_all[$hbv['hotel_id']][] = $hbv;
                    }
                    $nsp = array();
                    foreach ($heart_all as $hea=>$hev) {
                        $aflag = 0;
                        $bflag = 0;
                        $total = 0;
                        //根本不存在
                        $nsp[$hea]['flag'] = 0;
                        $nsp[$hea]['bflag'] = count($hev);
                        $nsp[$hea]['total'] = $nsp[$hea]['bflag'];
                        $nsp[$hea]['rate'] = '100';

                        $nsp[$hea]['maintainer'] = $hev[0]['maintainer'];
                        $nsp[$hea]['name'] = $hev[0]['name'];
                        $nsp[$hea]['hotel_box_type'] = $hev[0]['hotel_box_type'];
                        $nsp[$hea]['remark'] = $hev[0]['remark'];
                        $nsp[$hea]['hotelid'] = $hea;
                    }
                    $flag = 0;
                    $bflag = 0;
                    $total = 0;
                    $order_arr = array();
                    $order_arr_h = array();
                    foreach($nsp as $nval) {
                        $flag += $nval['flag'];
                        $total += $nval['total'];
                        $bflag += $nval['bflag'];

                    }
                    foreach($nsp as $hval) {
                        $order_arr[] = $hval['rate'];
                        $order_arr_h[] = $hval['hotelid'];
                    }
                    $ca_len = count($nsp);
                    $arp = array();
                    $arp['name'] = '总计'.$ca_len.'家酒楼';
                    $arp['flag'] = $flag;
                    $arp['bflag'] = $bflag;
                    $arp['total'] = $total;
                    $arp['rate'] = round($bflag/$total*100);
                    $arp['maintainer'] = '';
                    $arp['hotel_box_type'] = '';
                    $arp['remark'] = '';
                    array_multisort($order_arr,SORT_DESC,$order_arr_h,SORT_ASC, $nsp);
                    array_unshift($nsp, $arp);
                    foreach($nsp as $nk=>$nv){
                        foreach($hotel_box_type_arr as $hk=>$hv){
                            if($hk == $nv['hotel_box_type']){
                                $nsp[$nk]['hotel_box_type'] = $hv;
                            }
                        }
                        $nsp[$nk]['rate'] = $nsp[$nk]['rate'] .'%';
                    }
                    $hboxlist = $nsp;
                }
                $xlsCell = array(
                    array('name', '酒楼名称'),
                    array('maintainer', '维护人'),
                    array('hotel_box_type', '机顶盒类型'),
                    array('flag', '正常'),
                    array('bflag', '异常'),
                    array('total', '总计'),
                    array('rate', '异常率(%)'),
                    array('remark', '酒楼备注')
                );
            }
        }else{
            $hboxlist = array();
        }
        if(empty($hboxlist)){
            if($type == 1){
                $xlsCell = array(
                    array('name', '酒楼名称'),
                    array('hotel_box_type', '小平台类型'),
                    array('flag', '正常'),
                    array('bflag', '异常'),
                    array('total', '总计'),
                    array('rate', '异常率(%)'),
                    array('lost_time', '失联时长'),
                );
                $hboxlist[0]['name'] = '总计0家酒楼';
                $hboxlist[0]['flag'] = '';
                $hboxlist[0]['bflag'] = '';
                $hboxlist[0]['total'] = '';
                $hboxlist[0]['rate'] = '';
                $hboxlist[0]['hotel_box_type'] = '';
                $hboxlist[0]['lost_time'] = '';

            }else{
                $xlsCell = array(
                    array('name', '酒楼名称'),
                    array('maintainer', '维护人'),
                    array('hotel_box_type', '机顶盒类型'),
                    array('flag', '正常'),
                    array('bflag', '异常'),
                    array('total', '总计'),
                    array('rate', '异常率(%)'),
                    array('remark', '酒楼备注')
                );
                $hboxlist[0]['name'] = '总计0家酒楼';
                $hboxlist[0]['flag'] = '';
                $hboxlist[0]['bflag'] = '';
                $hboxlist[0]['total'] = '';
                $hboxlist[0]['rate'] = '';
                $hboxlist[0]['maintainer'] = '';
                $hboxlist[0]['hotel_box_type'] = '';
                $hboxlist[0]['remark'] = '';
            }

        }
        foreach($hboxlist as $hkk=>$hv){
            if(strstr ($hv['name'],'永峰') || strstr ($hv['name'],'茶室')){
                //小平台
                if($type == 1) {
                    if ($hv['lost_time'] == '正常'){
                        $hboxlist[0]['flag'] = $hboxlist[0]['flag']- 1;
                    }else{
                        $hboxlist[0]['bflag'] = $hboxlist[0]['bflag']- 1;
                    }
                    $hboxlist[0]['total'] = $hboxlist[0]['total']- 1;
                }else{
                    $hboxlist[0]['bflag'] = $hboxlist[0]['bflag']- $hv['bflag'];
                    $hboxlist[0]['total'] = $hboxlist[0]['total']- $hv['total'];
                    $hboxlist[0]['flag'] = $hboxlist[0]['flag']- $hv['flag'];
                }
                unset($hboxlist[$hkk]);
            }else{
                if($type == 1) {
                }
            }
        }

        $len = count($hboxlist) - 1;
        $hboxlist[0]['name'] = '总计'.$len.'家酒楼';
        $hboxlist[0]['rate'] = round($hboxlist[0]['bflag']/$hboxlist[0]['total']*100).'%';

        $hboxlist = array_values($hboxlist);
        if (count($hboxlist) == 1) {
            $hboxlist[0]['rate'] = '';
            $hboxlist[0]['flag'] = '';
            $hboxlist[0]['bflag'] = '';
            $hboxlist[0]['total'] = '';
        }
        $filename = 'heartlostinfo';
        $this->exportExcel($xlsName, $xlsCell, $hboxlist,$filename);

    }

    public function filtertime($comp_arr, $time){
        $rs =  array_filter($comp_arr, function ($val)
                use($time) {
                            if ( $time-strtotime($val['lt']) < 86400) {
                                    return true;
                            }else{
                                return false;
                            }
                });
        //得到正常值
       $count = count($rs);
        return $count;
    }

    public function sec2Time($time){
            if(is_numeric($time)){
                $value = array(
                    "days" => 0, "hours" => 0,
                    "minutes" => 0, "seconds" => 0,
                );
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            //return (array) $value;
            $t= $value["days"] ."天"." ". $value["hours"] ."小时". $value["minutes"] ."分";
            Return $t;

        }else{
            return (bool) FALSE;
        }
    }
    /**
     *
     * 导出Excel
     */

    function expboxreportinfo(){
        $filename = 'boxlostreport';
        $dtype = I('get.datetype');

            if ( $dtype == 1) {
                $table = 'heart_count_year';
                $time = date("Y",time());
            } else if ($dtype == 2) {
                $table = 'heart_count_month';
                $time = date("Y-m",time());
            } else if ($dtype == 3) {
                $table = 'heart_count_day';
                $time = date("Y-m-d",time()-86400);

            } else if ($dtype == 4) {
                $table = 'heart_count';
                $starttime = I('get.start','');
                $endtime = I('get.end','');
            }
        $boxreModel =  new \Admin\Model\BoxReportModel($table);
        $where = '1=1 ';
        if($dtype == 4) {
            if($starttime){
                $where .= "	AND time >= '{$starttime}'";
            }
            if($endtime){
                $where .= "	AND time <=  '{$endtime}'";
            }
        } else {
                $where .= "	AND time= '{$time}' ";

        }
        $hname = I('get.hname','');
        if($hname){
            $where .= "	AND hotel_name LIKE '%{$hname}%'";
        }
        $orders = '';
        $rea = $boxreModel->getAllList($where,$orders);
        $box_arr = $rea['list'];
        foreach($box_arr as &$val) {
            if($val['type'] == 1) {
                $val['type'] = '小平台';
                continue;
            }else if($val['type'] == 2){
                $val['type'] = '机顶盒';
                continue;
            }
        }
        $xlsName = "boxreport";
        $xlsCell = array(
            array('box_id', '机顶盒ID'),
            array('box_mac', '机顶盒MAC'),
            array('box_name', '机顶盒名称'),
            array('room_id', '包间ID'),
            array('room_name', '包间名称'),
            array('hotel_id', '酒楼ID'),
            array('hotel_name', '酒楼名称'),
            array('area_id', '区域ID'),
            array('area_name', '区域名称'),
            array('count', '计数'),
            array('type', '类型'),
            array('time', '时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);

    }


    /**
     *
     * 导出Excel
     */

    function expfirstmd(){
        $filename = 'first_mobile_download';
        $dtype = I('get.datetype');
        $starttime = I('start','');
        $endtime = I('end','');
        $table = 'first_mobile_download';
        $tuiModel =  new \Admin\Model\TuiRpModel($table);
        $where = '1=1 and time is not null';
        if($starttime){
            $where .= "	AND time >= '{$starttime}'";
        }
        if($endtime){
            $where .= "	AND time <=  '{$endtime}'";
        }
        $hname = I('get.hname','');
        if($hname){
            $where .= "	AND hotel_name LIKE '%{$hname}%'";
        }
        $orders = 'time,hotel_id';
        //$group =  'hotel_id';
        $field = '`download_count` as   dct,hotel_name,time';
        $rea = $tuiModel->getAllList($where, $field, $group, $orders);
        $box_arr = $rea['list'];
        
        $xlsName = "firstmobiledown";
        $xlsCell = array(
            array('hotel_name', '酒店名称'),
            
            array('dct', '首次打开（次）'),
            
            array('time', '时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
    }


    function expint_final(){
        $filename = 'mobile_interaction_final';
        $dtype = I('get.datetype');
        $starttime = I('start','');
        $endtime = I('end','');
        $table = 'first_mobile_interaction_final';
        $tuiModel =  new \Admin\Model\TuiRpModel($table);
        $where = '1=1 ';
        if($starttime){
            $where .= "	AND date_time >= '{$starttime}'";
        }
        if($endtime){
            $where .= "	AND date_time <=  '{$endtime}'";
        }
        $hname = I('get.hname','');
        if($hname){
            $where .= "	AND hotel_name LIKE '%{$hname}%'";
        }
        $orders = ' date_time,hotel_id asc';
        //$group =  'hotel_name,box_name';
        $field = 'count,hotel_name,box_name,date_time';
        $rea = $tuiModel->getAllList($where, $field, $group, $orders);
        $box_arr = $rea['list'];
        $xlsName = "screencastreport";
        $xlsCell = array(
            array('hotel_name', '酒店名称'),
            array('box_name', '机顶盒名称'),
            array('count', '首次连接（次）'),

            array('date_time', '时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);

    }

    function expscreenrep(){
        ini_set ('memory_limit', '512M');

        $filename = 'screencastreport';
        $dtype = I('get.datetype');
        if ( $dtype == 1) {
            $table = 'mobile_statistic_year';
            $time = date("Y",time());
        } else if ($dtype == 2) {
            $table = 'mobile_statistic_month';
            $time = date("Y-m",time());
        } else if ($dtype == 3) {
            $table = 'mobile_statistic_date';
            $time = date("Y-m-d",time()-86400);

        } else if ($dtype == 4) {
            $table = 'mobile_statistic_date_all';
            $starttime = I('start','');
            $endtime = I('end','');
        } else if ($dtype == 5) {
            $table = 'mobile_statistic';
        }
        $screenModel =  new \Admin\Model\ScreenRpModel($table);
        $where = '1=1 ';
        if($dtype == 4) {
            if($starttime){
                $where .= "	AND time >= '{$starttime}'";
            }
            if($endtime){
                $where .= "	AND time <=  '{$endtime}'";
            }

        } else {
            if($dtype!=5) {
                $where .= "	AND time= '{$time}' ";
            }
        }
        $hname = I('get.hname','');
        if($hname){
            $where .= "	AND hotel_name LIKE '%{$hname}%'";
        }
        $orders = '';
        $rea = $screenModel->getAllList($where,$orders);
        foreach ($rea['list'] as &$val) {
            if(empty($val['project_count'])){
                $val['project_count'] = 0;
            }
            if(empty($val['demand_count'])){
                $val['demand_count'] = 0;
            }
            if($dtype == 5) {
                $val['time'] = 0;
            }
        }
        $box_arr = $rea['list'];
        $xlsName = "screencastreport";
        $xlsCell = array(
            array('box_mac', '机顶盒MAC'),
            array('box_name', '机顶盒名称'),
            array('room_name', '包间名称'),
            array('hotel_name', '酒楼名称'),
            array('area_name', '区域名称'),
            array('mobile_id', '手机标识'),
            array('project_count', '投屏次数'),
            array('demand_count', '点播次数'),
            array('time', '时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
    }


    function expdownloadcount(){
        $filename = 'downloadcount';
        $dtype = I('get.datetype');
        $downloadModel =  new \Admin\Model\DownloadRpModel();
        $where = '1=1 ';
        $starttime = I('start','');
        $endtime = I('end','');
        if($starttime){
            $where .= "	AND add_time >= '{$starttime}'";
        }
        if($endtime){
            //$where .= "	AND add_time <=  '{$endtime}'";
            $where .= "	AND add_time <=  '{$endtime} 23:59:59'";
        }
        $soucetype = I('get.sourcetype','');
        if($soucetype){
            $where .= "	AND source_type =   '{$soucetype}'";
        }
        $orders = '';
        $rea = $downloadModel->getAllList($where,$orders);
        $so_type = C('source_type');
        $cltype = array(
            '1'=>'android',
            '2'=>'ios',
        );
        $dowload_device_typ = array(
            '1'=>'android',
            '2'=>'ios',
            '3'=>'pc',
        );

        foreach ($rea['list'] as &$val) {
            foreach($cltype as $k=>$v){
                if($k == $val['clientid']){
                    $val['clientid'] = $v;
                }
            }
            foreach($dowload_device_typ as $k=>$v){
                if($k == $val['dowload_device_id']){
                    $val['dowload_device_id'] = $v;
                }
            }

            foreach($so_type as $k=>$v){
                if($k == $val['source_type']){
                    $val['source_type'] = $v;
                }
            }

        }
        $box_arr = $rea['list'];
        $xlsName = "downloadcountreport";
        $xlsCell = array(
            array('source_type', '来源'),
            array('clientid', '手机客户端'),

            array('deviceid', '设备唯一标识'),

            array('dowload_device_id', '点击下载设备'),

            array('hotelid', '酒楼id'),
            array('waiterid', '服务员id'),
            array('add_time', '添加时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
    }

    function expappscreen(){
        $filename = 'appcreen';
        $downloadModel =  new \Admin\Model\AppscreenRpModel();
        $where = '1=1 ';
        $starttime = I('start','');
        $endtime = I('end','');
        if($starttime){
            $sttime = strtotime($starttime);
            $where .= "	AND substring(`timestamps`,0,-3) >= '{$sttime}'";
        }
        if($endtime){
            $etime = strtotime($endtime);
            $where .= "	AND substring(`timestamps`,0,-3) <=  '{$etime}'";
        }
        $orders = 'timestamps desc';
        $rea = $downloadModel->getAllList($where,$orders);
        foreach($rea['list'] as &$val){
            $val['addtime'] = date("Y-m-d",substr($val['timestamps'],0,-3));
        }
        $box_arr = $rea['list'];
        $xlsName = "appcreenreport";
        $xlsCell = array(
            array('area_name', '区域名称'),
            array('hotel_name', '酒楼名称'),
            array('room_name', '包间名称'),
            array('box_name', '机顶盒名称'),
            array('box_mac', '机顶盒mac'),
            array('mobile_id', '手机唯一标识id'),
            array('vcount', '点播次数'),
            array('vtime', '点播时长'),
            array('pcount', '投屏次数'),
            array('ptime', '投屏时长'),
            array('addtime', '添加时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
    }

    function expdeviceinfo(){
        //设备故障数
        $hotel_box_type = C('hotel_box_type');
        $box_state = C('HOTEL_STATE');
        $box_fl = array (
            '0'=>'正常',
            '1'=>'删除',
        );
        $boxModel = new \Admin\Model\BoxModel();
        foreach($hotel_box_type as $hb=>$hv) {
            $map = array();
            $map['sht.hotel_box_type'] = $hb;
            $asm = array();
            foreach($box_fl as $k=>$v) {
                $map['box.flag'] = $k;
                foreach($box_state as $bk=>$bv) {
                    $map['box.state'] = $bk;
                    $box_count = $boxModel->alias('box')
                        ->join(' join savor_room rom on rom.id= box.room_id')
                        ->join(' join savor_hotel sht on sht.id = rom.hotel_id')
                        ->where($map)->count();
                    echo $hv.' '.'冻结状态'.$bv.' 删除状态'.$v.'  机顶盒'.$box_count.'个'.'<br/>';
                    $asm[] = $box_count;
                }
            }
            echo $hv.'机顶盒'.array_sum($asm).'个'.'<br/>';
        }
        $map['sht.flag'] = 0;
        $map['sht.state'] = 1;
        $map['rom.flag'] = 0;
        $map['rom.state'] = 1;

        //每日开机数
        //算日期间隔
        $now = date("Y-m-d");
        $yes = date("Y-m-d", strtotime("today") - 604800);
        $dat_diff = $this->prDates($yes, $now);
        $heartLogModel = new \Admin\Model\HeartLogModel();
        $btype = array(
            '1'=>'小平台',
            '2'=>'机顶盒',
        );

        foreach($btype as $bt=>$bv) {
            $map = array();
            $map['type'] = $bt;
            $lo_arr = array();
            $box_num = $heartLogModel->where($map)->count();
            echo $bv.'每日开机数'.$box_num.'个'.'<br/>';
        }

        ob_end_clean();
        //导出失陪30天
        //获取所有二代网络5G机顶盒
        $map = array();
        $map['sht.hotel_box_type'] = array('in', array('2','3'));
        $map['box.flag'] = 0;
        $box_id_arr = $boxModel->alias('box')
            ->field('box.id,box.name bname,sht.name hotel_name')
            ->join(' join savor_room rom on rom.id= box.room_id')
            ->join(' join savor_hotel sht on sht.id = rom.hotel_id')
            ->where($map)
            ->select();
        $map = array();
        $map['hear.type'] = 2;
        $box_arr = $heartLogModel->alias('hear')
            ->join(' savor_box box on box.id= hear.box_id')
            ->where($map)->field('hear.box_id')->select();

        $box_arr_hear = array_column($box_arr, 'box_id');
        foreach($box_id_arr as $bk=>$bv) {
            if(in_array($bv['id'], $box_arr_hear)) {
                unset($box_id_arr[$bk]);
                continue;
            }else{
                unset($box_id_arr[$bk]['id']);
            }
            $box_id_arr[$bk]['apk_version'] = '';

        }
        $box_arr = array_values($box_id_arr);

        $filename = 'box_lost_version_condition';
        $xlsName = "boxlostversioncondition";


       /* //报表导出数据
        $map = array();
        $map['hear.type'] = 2;
        $box_arr = $heartLogModel->alias('hear')
        ->join(' savor_box box on box.id= hear.box_id')
        ->where($map)->field('hotel_name,
        apk_version,box.name bname')->select();
        $filename = 'box_version_condition';
        $xlsName = "boxversioncondition";

        */
        $xlsCell = array(
            array('bname', '机顶盒名称'),
            array('hotel_name', '酒楼名称'),
            array('apk_version', '版本号'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
    }


    function prDates($start,$end){
        $dat = array();
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        while ($dt_start<=$dt_end){
            $dat[] = date('Y-m-d',$dt_start);
            $dt_start = strtotime('+1 day',$dt_start);
        }
        return $dat;
    }


    function exphotelscreen(){
        $filename = 'hotelscreen';
        $hscreenModel =  new \Admin\Model\HotelscreenRpModel();
        $where = '1=1 ';
        $starttime = I('start','');
        $endtime = I('end','');
        if($starttime){

            $where .= "	AND (`play_date`) >= '{$starttime}'";
        }
        if($endtime){
            $where .= "	AND (`play_date`) <=  '{$endtime}'";
        }


        $orders = 'id desc';
        $rea = $hscreenModel->getAllList($where,$orders);
        foreach($rea['list'] as &$val){


        }
        $box_arr = $rea['list'];
        $xlsName = "hotelcreenreport";
        $xlsCell = array(
            array('area_name', '区域名称'),
            array('hotel_name', '酒楼名称'),
            array('room_name', '包间名称'),
            array('mac', '机顶盒mac'),
            array('ads_name', '广告名称'),
            array('plc', '播放次数'),
            array('dur', '播放时长'),
            array('play_date', '播放日期'),
        );
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);

    }

    /*
     *餐厅端目前已经绑定酒楼数据
     */
    function exphotelinvitecode() {
        $filename = 'bind_invite_hotel_info';
        $fileds = 'a.code invite_code,a.bind_mobile, a.bind_time,ht.name hname';
        $where = ' a.state=1 and a.flag = 0';

        $orders = 'a.hotel_id desc';
        $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
        $list = $m_hotel_invite_code->getInviteExcel($fileds,$where,$orders);
        $xlsName = "bindhotelinfo";
        $xlsCell = array(
            array('hname', '酒楼名称'),
            array('invite_code', '邀请码'),
            array('bind_mobile', '绑定手机号'),
            array('bind_time', '绑定时间'),
        );
        foreach($list as &$val){
            $val['bind_mobile'] = $val['bind_mobile'].' ';
        }

        $this->exportExcel($xlsName, $xlsCell, $list,$filename);

    }
    /*
         *餐厅端投屏日志
         */
    function expdinnerappLog(){
        $filename = 'dinnerapp_hall_log';
        $hallModel =  new \Admin\Model\DinnerHallLogModel();
        $where = '1=1 ';
        $starttime = '2017-12-18 00:00:00';
        $endtime = date("Y-m-d H:i:s");
        if($starttime){

            $where .= "	AND dhlog.(`create_time`) >= '{$starttime}'";
        }
        if($endtime){
            $where .= "	AND dhlog.(`create_time`) <=  '{$endtime}'";
        }
        $where .= " AND dhlog.hotel_id != 7 ";


        $orders = 'dhlog.id desc';
        $rea = $hallModel->getAllList($where,$orders);
        $touping_config = array (
            '1'=>'特色菜',
            '2'=>'宣传片',
            '3'=>'照片',
            '4'=>'视频',
            '5'=>'欢乐词',
        );
        $cli_arr = array('3'=>'android','4'=>'ios');
        foreach($rea as &$val){
            if($val['screen_result'] == 1) {
                $val['screen_result'] = '成功';
            }
            if($val['screen_result'] == 1) {
                $val['screen_result'] = '失败';
            }
            $sty = $val['screen_type'];
            $val['screen_type'] = array_key_exists($sty,
            $touping_config)?$touping_config[$sty]:'';
            $dty = $val['device_type'];
            //加空格可以防止过长显示不完整或者不识别
            $val['mobile'] = $val['mobile'].' ';
            $val['device_id'] = $val['device_id'].' ';
            $val['device_type'] = $cli_arr[$dty];
            $temp = '';
            if($val['info']) {
                $ainfo = json_decode($val['info'], true);
                if( isset($ainfo['single_play']) ) {
                    $temp .= "单个投屏时间:".$ainfo['single_play']."秒,";
                }
                if( isset($ainfo['loop_time']) ) {
                    $temp .= "总投屏时长:".$ainfo['loop_time']."秒,";
                }
                if( isset($ainfo['loop']) ) {
                    if($ainfo['loop'] == 0) {
                        $temp .= "不循环";
                    }
                    if($ainfo['loop'] == 1) {
                        $temp .= "循环";
                    }

                }
                $val['info'] = $temp;
            }
        }
        $xlsName = "dinnerapphalllog";
        $xlsCell = array(
            array('mobile', '手机号'),
            array('invite_code', '邀请码'),
            array('hotel_name', '酒楼名称'),
            array('room_name', '包间名称'),
            array('wew', '欢迎词'),
            array('wet', '欢迎词模版'),
            array('screen_result', '投屏是否成功'),
            array('screen_type', '投屏功能'),
            array('device_type', '设备类型'),
            array('device_id', '设备唯一标识'),
            array('screen_num', '投屏数量'),
            array('screen_time', '投屏总时长'),
            array('info', '投屏设置'),
            array('create_time', '上报时间'),
        );
        $this->exportExcel($xlsName, $xlsCell, $rea,$filename);

    }


    function hotelinfo()
    {//导出Excel
        $boxModel = new \Admin\Model\BoxModel();
        //获取所有数据
        $box_arr = $boxModel->getBoxExNumNew();
        $htype = C('hotel_box_type');
        foreach($box_arr as $bk=>$bv) {
            $box_arr[$bk]['hotel_box_type'] = $htype[$box_arr[$bk]['hotel_box_type']];
        }
        $filename = 'hotelboxlist';
        $xlsName = "User";
        $xlsCell = array(
            array('id', '酒楼id'),
            array('install_date', '安装日期'),
            array('boxstate', '机顶盒状态'),
            array('mac', '机顶盒mac地址'),
            array('bname', '机顶盒名称'),
            array('rname', '包间名称'),
            array('rtype', '包间类型'),
            array('tbrd', '品牌'),
            array('tsiz', '尺寸'),
            array('tv_source', '电视信号源'),
            array('hname', '酒店名称'),
            array('level', '酒店级别'),
            array('area_id', '酒店区域'),
            array('addr', '酒店地址'),
            array('contractor', '酒店联系人'),
            array('mobile', '手机'),
            array('tel', '固定电话'),
            array('iskey', '重点酒楼'),
            array('maintainer', '合作维护人'),
            array('tech_maintainer', '技术运维人'),
            array('hotel_box_type', '设备类型'),
        );

        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);

    }
    function hotelboxinfo(){
        $boxModel = new \Admin\Model\BoxModel();
        //获取所有数据
        $box_arr = $boxModel->getBoxListDb();
        $htype = C('hotel_box_type');
        foreach($box_arr as $bk=>$bv) {
            $box_arr[$bk]['box_type'] = $htype[$box_arr[$bk]['box_type']];
        }
        $filename = 'hotel';
        $xlsName = "User";
        $xlsCell = array(
            array('id', '酒楼id'),
            array('install_date', '安装日期'),
            array('hsta','酒楼状态'),
            array('boxstate', '机顶盒状态'),
            array('mac', '机顶盒mac地址'),
            array('bname', '机顶盒名称'),
            
            array('hname', '酒店名称'),
            array('area_id', '酒店区域'),
            array('addr', '酒店地址'),
            
            array('maintainer', '合作维护人'),
            array('box_type', '设备类型'),
            array('remark','包间备注'),
            array('tag','机顶盒备注'),
            array('avg_expense','人均消费'),
            array('is_4g','是否4G'),
            array('wifi_name','wifi名称'),
            array('wifi_password','wifi密码')
        );
        
        $this->exportExcel($xlsName, $xlsCell, $box_arr,$filename);
        
    }


    public function testList()
    {
        //实例化redis
        //         $redis = SavorRedis::getInstance();
        //         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function daorudianping()
    {
        vendor("PHPExcel.PHPExcel.IOFactory");
        $filetmpname = APP_PATH . '../public/2.xls';
        $objPHPExcel = \PHPExcel_IOFactory::load($filetmpname);
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();
        //删除不要的表头部分，我的有三行不要的，删除三次
        array_shift($arrExcel);
        // array_shift($arrExcel);
        // array_shift($arrExcel);//现在可以打印下$arrExcel，就是你想要的数组啦
        //  $arrExcel = array_slice($arrExcel,3,5);
        //查询数据库的字段
        $m = M('a2');
        $fieldarr = $m->query("describe savor_a2");
        foreach ($fieldarr as $v) {
            $field[] = $v['field'];
        }
        array_shift($field);
        $field = array(
            0 => 'tel',
            1 => 'username',
        );
        var_dump($field);
        var_dump($arrExcel);
        //var_dump($arrExcel);
        foreach ($arrExcel as $k => $v) {
            if ($k == 1066) {
                break;
            }
            $fields[] = array_combine($field, $v);//将excel的一行数据赋值给表的字段
        }
        // var_dump($fields);

        //批量插入
        if (!$ids = $m->addAll($fields)) {
            //$this->error("没有添加数据");
            echo 'faile';
        } else {
            echo 'succes';
        }
        // $this->success('添加成功');
    }

    public function daoru()
    {
        vendor("PHPExcel.PHPExcel.IOFactory");
        $filetmpname = APP_PATH . '../public/2.xls';
        $objPHPExcel = \PHPExcel_IOFactory::load($filetmpname);
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();
        //删除不要的表头部分，我的有三行不要的，删除三次
        array_shift($arrExcel);
        // array_shift($arrExcel);
        // array_shift($arrExcel);//现在可以打印下$arrExcel，就是你想要的数组啦
        //  $arrExcel = array_slice($arrExcel,3,5);
        //查询数据库的字段
        $m = M('a2');
        $fieldarr = $m->query("describe savor_a2");
        foreach ($fieldarr as $v) {
            $field[] = $v['field'];
        }
        array_shift($field);
        $field = array(
            0 => 'tel',
            1 => 'username',
        );
        var_dump($field);
        var_dump($arrExcel);
        //var_dump($arrExcel);
        foreach ($arrExcel as $k => $v) {
            if ($k == 1066) {
                break;
            }
            $fields[] = array_combine($field, $v);//将excel的一行数据赋值给表的字段
        }
        // var_dump($fields);

        //批量插入
        if (!$ids = $m->addAll($fields)) {
            //$this->error("没有添加数据");
            echo 'faile';
        } else {
            echo 'succes';
        }
        // $this->success('添加成功');
    }
    public function excelAppDownload(){
        $hotel_name = I('hotel_name','','trim');
        $guardian   = I('guardian','','trim');  
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $where ='';
        $where =" and src in('box','mob','rq')";
        if(!empty($hotel_name)){
            $where .=" and hotel_name like '%".$hotel_name."%'";
        } 
        if(!empty($guardian)){
            $where .=" and guardian like '%".$guardian."%'";
        }
        if($start_date){
            $where .=" and date_time>='".$start_date."'";
        }
        if($end_date){
            $where .= " and date_time<='".$end_date."'";
        }
        
        $m_app_download = new \Admin\Model\AppDownloadModel();
        $download_list = $m_app_download->getDownloadHotel($where ,$order='date_time',$sort='desc');
         
        $data = array();
        foreach($download_list as $key=>$v){
             
            $data[$v['hotel_id']][] = $v;
        }
        
        $count = 0;
        $list = array();
        foreach($data as $key=>$val){
            $list[] = $val;
            $count ++;
        }
         
       
        $rts = array();
        $flag = 0;
        foreach($list as $key=>$val){
            $rts[$flag]['hotel_id']   = $val[0]['hotel_id'];
            $rts[$flag]['hotel_name'] = $val[0]['hotel_name'];
            $rts[$flag]['guardian']   = $val[0]['guardian'];
            //$rts[$flag]['end_date_time']  = $val[0]['date_time'];
            $c_count = count($val) -1;
            //$rts[$flag]['start_date_time'] = $val[$c_count]['date_time'];
            $rts[$flag]['quantum'] = $val[$c_count]['date_time']."--".$val[0]['date_time'];;
            $box = $mob = $rq = $arr = array();
             
            foreach($val as $k=>$v){
                 
                if($v['src'] =='box'){
                    $box[]=$v['mobile_id'];
                }else if($v['src']=='mob'){
                    $mob[] = $v['mobile_id'];
                }else if($v['src']=='rq'){
                    $rq[] = $v['mobile_id'];
                }
            }
            $rts[$flag]['box'] = $box;
            $rts[$flag]['mob'] = $mob;
            $rts[$flag]['rq']  = $rq;
        
             
            $arr = array_merge($box,$mob,$rq);
            $arr = array_unique($arr);
            $rts[$flag]['all'] = $arr;
            $rts[$flag]['box_num'] = count($box);
            $rts[$flag]['mob_num'] = count($mob);
            $rts[$flag]['rq_num']  = count($rq);
            $rts[$flag]['all_num'] = count($arr);
            $flag ++;
        }  
        
        $filename = 'allappdownload';
        $xlsName = "allappdownload";
        $xlsCell = array(
            array('quantum', '时段'),
            array('hotel_name', '酒楼名称'),
            array('guardian', '维护人'),
            array('box_num', '首次投屏数量'),
            array('rq_num', '二维码扫描下载'),
            array('mob_num', '首次打开'),
            array('all_num', '去重后总计'),
        );
        $this->exportExcel($xlsName, $xlsCell, $rts,$filename);
        
    }
    public function excelContAndProm(){
        $where =' 1=1';
        
        /* $start_date = I('start_date');
        $end_date   = I('end_date');
        $userid = I('userid');
        $category_id = I('category_id','0','intval');
        $content_name = I('content_name','','trim');
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        if($start_date){
            $this->assign('start_date',$start_date);
            $start_date = date('YmdH',strtotime($start_date));
            $where .= " and date_time >='".$start_date."'";
        }
        if($end_date){
            $this->assign('end_date',$end_date);
            $end_date = date('YmdH',strtotime($end_date));
            $where .= " and date_time <='".$end_date."'";
        }
        $m_sysuser = new \Admin\Model\UserModel();
        if($userid){
            $this->assign('username',$userid);
            $users = $m_sysuser->getUser(" and id=$userid",'id,username,remark');
            $userinfo = $users[0];
            if($userinfo){
                $where .=" and operators='".$userinfo['username']."' or operators='".$userinfo['remark']."'";
            }
        
        }
        if($category_id){
            $this->assign('category_id',$category_id);
            $where .=" and category_id=$category_id";
        } */
        $content_name = I('content_name','','trim');
        if($content_name){
            $this->assign('content_name',$content_name);
            $where .=" and content_name like '%".$content_name."%'";
        }
        
        $m_content_details_final = new \Admin\Model\ContDetFinalModel();
        $list = $m_content_details_final->getAll($where, "s_read_count desc ");
        
        $filename = 'allcontandprom';
        $xlsName = "allcontandprom";
        foreach($list as $key=>$v){
            if($v['common_value']==0){
                $list[$key]['common_value'] = '纯文本';
            }else if($v['common_value']==1){
                $list[$key]['common_value'] = '图文';
            }else if($v['common_value']==2){
                $list[$key]['common_value'] = '图集';
            }else if($v['common_value']==3){
                $list[$key]['common_value'] = '视频';
            }
            if(empty($v['s_read_count'])){
                $list[$key]['s_read_count'] = 0;
            }
            if(!empty($v['s_read_duration'])){
                $tmp = $list[$key]['s_read_duration']/ $list[$key]['s_read_count'];
                $list[$key]['avg_read_duration'] = changeTimeType($tmp);
            }else {
                $list[$key]['avg_read_duration'] = 0;
            }
            if(empty($v['s_read_duration'])){
                $list[$key]['s_read_duration'] = '0秒';
            }else {
                $list[$key]['s_read_duration'] = changeTimeType($v['s_read_duration']);
            }
            if(empty($v['s_demand_count'])){
                $list[$key]['s_demand_count'] = 0;
            }
            if(empty($v['s_share_count'])){
                $list[$key]['s_share_count'] = 0;
            }
            if(empty($v['s_pv_count'])){
                $list[$key]['s_pv_count'] = 0;
            }
            if(empty($v['s_uv_count'])){
                $list[$key]['s_uv_count'] = 0;
            }
            if(empty($v['s_click_count'])){
                $list[$key]['s_click_count'] = 0;
            }
            if(empty($v['s_outline_count'])){
                $list[$key]['s_outline_count'] = 0;
            }
            
            
        }
        $xlsCell = array(
            array('content_name', '文章标题'),
            array('category_name', '分类'),
            array('common_value', '内容类别'),
            array('operators', '编辑'),
            array('create_time', '创建时间'),
            array('s_read_count', '阅读总次数'),
            array('s_read_duration', '阅读总时长'),
            array('avg_read_duration','平均阅读市场'),
            array('s_demand_count', '点播总次数'),
            array('s_share_count', '分享总次数'),
            array('s_pv_count', 'PV'),
            array('s_uv_count', 'UV'),
            array('s_click_count', '点击数'),
            array('s_outline_count', '外链点击数'),
     
        );
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
    }
    public function importBillInfo()
    {
       //http://admin.rerdian.com/admin/excel/importBillInfo
        echo 'weeljrlwejr';
        die;
        vendor("PHPExcel.PHPExcel.IOFactory");
        $filetmpname = APP_PATH . '../Public/guang.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($filetmpname);
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();
        
        //删除不要的表头部分，我的有三行不要的，删除三次
        array_shift($arrExcel);
        // array_shift($arrExcel);
        // array_shift($arrExcel);//现在可以打印下$arrExcel，就是你想要的数组啦
        //  $arrExcel = array_slice($arrExcel,3,5);
        //查询数据库的字段
        /* $m = M('a2');
        $fieldarr = $m->query("describe savor_a2");
        foreach ($fieldarr as $v) {
            $field[] = $v['field'];
        }
        array_shift($field);
        $field = array(
            0 => 'tel',
            1 => 'username',
        );
        var_dump($field);
        var_dump($arrExcel);
        //var_dump($arrExcel); */
        $m_hotel = new \Admin\Model\HotelModel();
        
        foreach ($arrExcel as $k => $v) {
            $data = $where = array();
            $info = $m_hotel->getHotelByIds($v['0']); 

            if(!empty($info) && $v['0']>0){
                $data['bill_per'] = $v['4'];
                $data['bill_tel'] = $v['5'];
                $where['id'] = $v['0'];
                //var_dump($data);
                //var_dump($where);
                $rt = $m_hotel->saveData($data, $where);
                //var_dump($rt);exit;
            }
        }
        echo "ok";
    }

    public function exphotelbillinfo(){
        $statementid = I('statementid',0);
        $statedetailModel = new \Admin\Model\AccountStatementDetailModel();
        $statenoticeModel = new \Admin\Model\AccountStatementNoticeModel();
        $filename = 'hotelbillinfo';
        $xlsName = "billinfo";
        $where = "1=1";
        $where .= " AND sdet.statement_id = ".$statementid;
        $orders = 'sdet.id asc';
        $result = $statedetailModel->getAll($where,$orders, 0,999);
        $notice_state = C('NOTICE_STATAE');
        $check_state = C('CHECK_STATAE');
        foreach ($result['list'] as &$val){

            if($val['state']!=1){
                $dinfo = $statedetailModel->find($val['detailid']);
                $val['name'] = $dinfo['hotel_name'];
                $val['money'] = $dinfo['money'];
                foreach($notice_state as $nh=>$nv){
                    if($nh == $val['state']) {
                        $val['state'] = $nv;
                    }
                }
                $val['check_status'] = '';
            }else{
                $dat['detail_id'] = $val['detailid'];
                $dat['f_type'] = 1;
                $notice_arr = $statenoticeModel->getWhere($dat);
                $nostus = $notice_arr['status'];
                if($nostus == 1){
                    $val['state'] = '发送成功';
                }else {
                    $val['state'] = '发送中';
                }
                foreach($check_state as $ch=>$cv){
                    if($ch == $val['check_status']) {
                        $val['check_status'] = $cv;
                    }
                }
            }


        }

        $xlsCell = array(
            array('hotelid', '酒楼id'),
            array('name', '酒楼名称'),
            array('money', '金额'),
            array('state', '通知状态'),
            array('check_status', '对账状态'),
        );

        $this->exportExcel($xlsName, $xlsCell, $result['list'],$filename);
    }
    public function excelToothwash(){
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        //$infos = $m_activity_data->getInfo('*','',' add_time desc','',2);
        $infos = $m_activity_data->getAllInfo('a.*,b.name as act_name,c.goods_name,c.goods_price','','add_time desc');
        $xlsCell = array(
            array('id', 'id'),
            array('receiver', '收货人'),
            array('mobile', '电话'),
            array('address', '收货地址'),
            array('act_name','活动名称'),
            array('goods_name','商品名称',''),
            array('goods_nums','购买数量'),
            array('goods_price','商品单价'),
            array('add_time', '下单时间'),
            array('sourceid','来源')
        );
        $activity_source_arr = C('ACTIVITY_SOURCE_ARR');
        foreach($infos as $key=>$v){
            $infos[$key]['sourceid'] = $activity_source_arr[$v['sourceid']];
        }
        $xlsName = '活动订单';
        $filename = 'toothwash';
        $this->exportExcel($xlsName, $xlsCell, $infos,$filename);
    }
    public function excelHotelBv(){
        $m_hotel = new \Admin\Model\HotelModel();
        $m_area_info = new \Admin\Model\AreaModel();
        $where =array();
        $where['hotel_box_type'] = 3;
        $where['state'] = 1;
        $where['flag']  = 0;
        
        $info = $m_hotel->getWhereData($where,'id,name,area_id,addr');
        foreach($info as $key=>$v){
            $area_info = $m_area_info->field('region_name')->where('id='.$v['area_id'])->find();
            $info[$key]['region_name'] = $area_info['region_name'];
            $sql ="select count(1) as num from savor_tv as tv
                   left join savor_box as box  on tv.box_id=box.id
                   left join savor_room as room on box.room_id= room.id
                   left join savor_hotel as hotel on room.hotel_id= hotel.id 
                   where hotel.id=".$v['id'] .' and tv.state=1 and tv.flag =0';
            $rets = M()->query($sql);
            $info[$key]['tv_count'] = $rets[0]['num'];
        }
        $xlsCell = array(
            array('id', 'id'),
            array('region_name', '城市'),
            array('name','酒楼名称'),
            array('addr', '酒楼地址'),
            array('tv_count', '电视数量'),
           
        );
        $xlsName = '酒楼信息以及版位数量';
        $filename = 'hotelBv';
        $this->exportExcel($xlsName, $xlsCell, $info,$filename);
    }
    public function excelHotelBox(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        $sql ="select hotel.id as hotel_id,box.id box_id,area.region_name, hotel.name,hotel.addr,box.mac from savor_box box
               left join savor_room room on room.id=box.room_id
               left join  savor_hotel hotel on hotel.id=room.hotel_id
               left join savor_area_info area on area.id=hotel.area_id
               where hotel.state and hotel.flag =0 and box.state=1 and box.flag=0 and hotel.hotel_box_type in($hotel_box_type_str) and hotel.id !=7 and hotel.id!=53 order by hotel.id asc";
        
        $info = M()->query($sql);
        $tmp = array();
        foreach($info as $key=>$v){
            
            $sql ="select count(1) as num from savor_tv as tv
                   left join savor_box as box  on tv.box_id=box.id
                   where box.id=".$v['box_id'] .' and tv.state=1 and tv.flag =0';
            $rets = M()->query($sql);
            $info[$key]['tv_count'] = $rets[0]['num'];
            
        }
        $xlsCell = array(
            
            array('region_name', '城市'),
            array('name','酒楼名称'),
            array('addr', '酒楼地址'),
            array('mac','机顶盒mac'),
            array('tv_count', '电视数量'),
             
        );
        $xlsName = '酒楼信息以及版位数量';
        $filename = 'hotelBv';
        $this->exportExcel($xlsName, $xlsCell, $info,$filename);
        
    }


    public function  expcontentlink() {
        $starttime = I('adsstarttime','');
        $endtime = I('adsendtime','');
        $url = I('url');

        $where = '1=1 ';
        if(empty($starttime) || empty($endtime)){
            echo "<script>alert('请选择开始时间与结束时间');</script>";
           die;
        }
        if($starttime <= $endtime) {
            $stt = strtotime($starttime);
            $ste = strtotime($endtime);
            if($stt == $ste) {
                $ste = $stt+86399;
            } else {
                $ste = $ste+86399;
            }
            $where.=" AND TIMESTAMP/1000>='$stt'";
            $where.=" AND TIMESTAMP/1000<='$ste'";

        }else{
            echo "<script>alert('开始时间必须小于等于结束时间');</script>";
            die;
        }
        if ( $url ) {
            $url = htmlspecialchars_decode('/'.$url);
            $where.=" AND request_url = '$url'";
        }
        $field = '*';
        $clinkModel = new \Admin\Model\ContentLinkModel();
        $result = $clinkModel->fetchDataWhere($where, $order='timestamp desc', $field,2);
        $dat = $result;
        $is_wei = array(
            '0' => '否',
            '1' => '是'
        );
        $is_shou = array(
            '0' => '否',
            '1' => '是'
        );

        foreach($dat as $rk=>$rv) {
            $w = $dat[$rk]['is_wx'];
            $sq = $dat[$rk]['is_sq'];
            $dat[$rk]['is_wx'] = $is_wei[$w];
            $dat[$rk]['is_sq'] = $is_wei[$sq];
            $ctime = substr($dat[$rk]['timestamp'],0 , -3);
            $dat[$rk]['vtime'] = date("Y-m-d H:i:s", $ctime);
        }


        $xlsCell = array(
            array('content_id', '文章id'),
            array('vtime', '访问日期'),
            array('device_type','设备类型'),
            array('is_wx', '是否为微信打开'),
            array('ip','IP'),
            array('net_type', '网络类型'),
            array('is_sq', '是否授权'),
        );
        $xlsName = '内容链接明细';
        $filename = 'contentlink';
        $this->exportExcel($xlsName, $xlsCell, $dat,$filename);

    }
    public function expcontentwxauth(){
        $start_date = I('get.start_date');
        $end_date   = I('get.end_date');
        $contentid   = I('get.contentid');
        
        
        if(!empty($start_date)){
	        $where .= " and a.create_time>='".$start_date." 00:00:00'";
	    }
	    if(!empty($end_date)){
	        $where .=" and a.create_time<='".$end_date." 23:59:59'";
	        
	    }
	    if(!empty($contentid)){
	        $where .=" and a.contentid=$contentid";
	       
	    }
	    $m_content_wx_auth = new \Admin\Model\ContentWxAuthModel();
	    $data = $m_content_wx_auth->getInfo("a.*,b.title ,c.name catname",$where,' a.create_time desc ','',2);
	    
	     foreach($data as $key=>$v){
	         if(!empty($v['nickname'])){
	             $data[$key]['nickname'] = base64_decode(trim($v['nickname']));
	         }
	        //$data[$key]['nickname'] = base64_decode($v['nickname']);
	         switch ($v['sex']){
	            case 0:
	                $data[$key]['sex'] = '';
	            break;
	            case 1:
	                $data[$key]['sex'] = '男';
	            break;
	            case 2:
	                $data[$key]['sex'] = '女';
	            break;
	        } 
	    } 
	 
	    $xlsCell = array(
	        array('id', '日志id'),
	        array('openid', 'openid'),
	        array('nickname','昵称'),
	        array('sex', '性别'),
	        array('country','国家'),
	        array('province', '省份'),
	        array('city', '城市'),
	        array('contentid', '文章id'),
	        array('title', '文章标题'),
	        array('catname', '文章分类'),
	        array('ip_addr', 'IP'),
	        array('long', '经度'),
	        array('lat', '维度'),
	        array('create_time', '访问时间'),
	        
	    );
	    $xlsName = '文章微信授权明细';
	    $filename = 'expcontentwxauth';
	    $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 获取运维端机顶盒为异常状态的数据
     */
    public function reportErroBoxInfo(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $heart_loss_hours   = C('HEART_LOSS_HOURS');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        $m_hotel = new \Admin\Model\HotelModel();
        $now = time();
        $start_time = strtotime('-'.$heart_loss_hours.' hours');
        $where = '';
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) ";
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id,a.name hotel_name,a.addr');
        //print_r($hotel_list);exit;
        $m_box = new \Admin\Model\BoxModel();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_repair_box_user = new \Admin\Model\RepairBoxUserModel();
        $m_repair_detail = new \Admin\Model\RepairDetailModel();
        $result = array();
        $repair_type_arr = C('HOTEL_DAMAGE_CONFIG');
        foreach($hotel_list as $key=>$v){
            $where =" 1 and room.hotel_id=".$v['id'].' and a.state =1 and a.flag =0 and room.state =1 and room.flag =0 ';
            $box_list = $m_box->getListInfo( 'a.id,room.name rname, a.name boxname, a.mac,a.id box_id',$where);
            foreach($box_list as $ks=>$vs){
                $tmp = array();
                $where = '';
                $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
            
                $rets  = $m_heart_log->getHotelHeartBox($where,'max(last_heart_time) ltime', 'box_mac');
                
                if(empty($rets)){
                    $tmp['hotel_name'] = $v['hotel_name'];  //酒楼名称
                    $tmp['addr']       = $v['addr'];        //酒楼地址
                    $tmp['rname']      = $vs['rname'];       //包间名称
                    $tmp['boxname']    = $vs['boxname'];     //盒子名称
                    //$tmp['boxid'] = $vs['id'];
                    $tmp['repair_0']   = '';
                    $tmp['repair_1']   = '';
                    $tmp['repair_2']   = '';
                    $repair_info = $m_repair_box_user->getWhere('id,remark'," hotel_id = ".$v['id']." and mac='".$vs['mac']."' and  flag=0",' create_time desc ','0,3',2);
                    foreach($repair_info as $rk=>$rv){
                        $space = '';
                        $detail_info = $m_repair_detail->getWhere('repair_type',' repair_id='.$rv['id'],'id desc','',2);
                        foreach($detail_info as $dk=>$dv){
                            $tmp["repair_".$rk] .= $space . $repair_type_arr[$dv['repair_type']];
                            $space = '、';
                        }
                    }
                    
                    
                    $result[] = $tmp;
                }else {
                    $ltime = $rets[0]['ltime'];
                    $ltime = strtotime($ltime);
                    if($ltime <= $start_time) {
                        //$unusual_num +=1;
                        //$box_list[$ks]['ustate'] = 0;
                        $tmp['hotel_name'] = $v['hotel_name'];  //酒楼名称
                        $tmp['addr']       = $v['addr'];        //酒楼地址
                        $tmp['rname']      = $vs['rname'];       //包间名称
                        $tmp['boxname']    = $vs['boxname'];     //盒子名称
                        //$tmp['boxid'] = $vs['id'];
                        $tmp['repair_0']   = '';
                        $tmp['repair_1']   = '';
                        $tmp['repair_2']   = '';
                        $repair_info = $m_repair_box_user->getWhere('id,remark'," hotel_id = ".$v['id']." and mac='".$vs['mac']."' and  flag=0",' create_time desc ','0,3',2);
                        foreach($repair_info as $rk=>$rv){
                            $space = '';
                            $detail_info = $m_repair_detail->getWhere('repair_type',' repair_id='.$rv['id'],'id desc','',2);
                            foreach($detail_info as $dk=>$dv){
                                $tmp["repair_".$rk] .= $space . $repair_type_arr[$dv['repair_type']];
                                $space = '、';
                            }
                        }
                        
                        $result[] = $tmp;
                    } 
                }
            }
        }
        $xlsCell = array(
            array('hotel_name', '酒楼名称'),
            array('addr', '酒楼地址'),
            array('rname','包间名称'),
            array('boxname', '盒子名称'),
            array('repair_0','维修记录1'),
            array('repair_1', '维修记录2'),
            array('repair_2', '维修记录3'),
            
            
             
        );
        $xlsName = '运维端异常机顶盒';
        $filename = 'optionerrobox';
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
        
    }
    public function testone(){
        $aa = fopen('./aa.csv', 'w');
        vendor("PHPExcel.PHPExcel");
        
        $PHPReader =new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead('./aa.csv')){
            $PHPReader = new \PHPExcel_Reader_Excel5();
        }
        if(!$PHPReader->canRead('./aa.csv')){
            $PHPReader = new \PHPExcel_Reader_CSV();
        }
        if(!$PHPReader->canRead('./aa.csv')){
            echo '无法识别';
            return false;
        }
        //读取Excel
        $PHPExcel = $PHPReader->load('./aa.csv');
        //读取工作表1
        $currentSheet = $PHPExcel->getSheet();
        
        $currentSheet->setCellValue('B13','11111s');//表头赋值//
        
        $phpWrite = new \PHPExcel_Writer_CSV($PHPExcel);
        
        $phpWrite->save('./aa.csv');
    }


    /*
     * 换画报修明细
     */
    public function exportShSingle(){
        $m_option_task = new \Admin\Model\OptiontaskModel();
        $where = array();
        $area = I('get.area');
        $ctime = urldecode(I('get.ctime'));
        $etime = urldecode(I('get.etime'));
        if($ctime && $etime) {
            $where['a.create_time'] = array( array('gt',$ctime,),array('elt',$etime));
        } else {
            if($ctime) {
                $where['a.create_time'] = array('gt', $ctime);
            }
            if($etime) {
                $where['a.create_time'] = array('elt', $etime);
            }
        }

        if($area) {
            $where['a.task_area'] = $area;
            $where['sro.manage_city'] = $area;
        }
        $where['sro.role_id'] = 5;
        $where['a.task_type'] = 4;
        $where['a.flag']      =0;



        $fields = "a.id,b.name hotel_name,a.hotel_address,
                   a.create_time pub_time,a.hotel_id,a.state
                   ,a.task_area";
        $list = $m_option_task->alias('a')
            ->join('savor_hotel b on a.hotel_id= b.id','left')
            ->join('savor_sysuser sy on a.publish_user_id = sy.id')
            ->join('savor_opuser_role sro on sro.user_id = sy.id')
            ->field($fields)->where($where)->order()->select();
        $model = D();
        $hotelExt = new \Admin\Model\HotelExtModel();
        $hp_c = C('HOTEL_STANDALONE_CONFIG');
        foreach($list as $key=>$val){
            $repair_str = '';
            $space = '';
            $data = $model->query('select b.mac box_mac, a.box_id,b.name box_name,a.repair_type,a.state bste from
                                   savor_option_task_repair a left join savor_box b
                                   on a.box_id = b.id where a.task_id='.$val['id']);
            if(!empty($data)){
                $list[$key]['box_name'] = $data[0]['box_name'];
                $list[$key]['box_mac'] = $data[0]['box_mac'];
                $rp_type = $data[0]['repair_type'];
                $temp = '';
                if($rp_type) {
                    $rp_type = explode(',', $rp_type);
                    foreach($rp_type as $vp) {
                        $temp .= $hp_c[$vp].',';
                    }
                    $temp = substr($temp,0, -1);
                    $list[$key]['rep_type'] = $temp;
                } else {
                    $list[$key]['rep_type'] = '';
                }
                $bstp = $data[0]['bste'];
                switch ($bstp){
                    case '1':
                        $list[$key]['bste'] = '已经解决';
                        break;
                    case '2':
                        $list[$key]['bste'] = '未解决';
                        break;
                    case '0':
                        $list[$key]['bste'] = '';
                        break;
                }
            } else {
                $list[$key]['rep_type'] = '';
                $list[$key]['bste'] = '';
            }
            $map = array();
            $map['hotel_id'] = $val['hotel_id'];
            $hxinfo = $hotelExt->alias('hx')
                               ->join('savor_sysuser sy on hx.maintainer_id = sy.id')
                               ->find();
            $list[$key]['mainta'] = $hxinfo['remark'];

            switch ($val['task_area']){
                case '1':
                    $task_area= '北京';
                    break;
                case '9':
                    $task_area = '上海';
                    break;
                case '236':
                    $task_area = '广州';
                    break;
                case '246':
                    $task_area = '深圳';
                    break;
            }
            $list[$key]['task_area'] = $task_area;
            switch ($val['state']){
                case '1':
                    $list[$key]['state'] = '新任务';
                    break;
                case '2':
                    $list[$key]['state'] = '执行中';
                    break;
                case '3':
                    $list[$key]['state'] = '排队等待';
                    break;
                case '4':
                    $list[$key]['state'] = '已完成';
                    break;
                case '4':
                    $list[$key]['state'] = '拒绝';
                    break;

            }
        }
        $xlsCell = array(
            array('task_area','城市'),
            array('hotel_name','酒楼名称'),
            array('hotel_address','酒楼地址'),
            array('mainta','合作维护人'),
            array('pub_time', '发布时间'),
            array('box_name','版位名称'),
            array('rep_type', '故障现象'),
            array('box_mac','设备编号'),
            array('state','任务状态'),
            array('bste','盒子是否解决'),

        );
        $xlsName = '上海单机版换画明细';
        $filename = 'option_sh_signle_pic';
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
    }
    /**
     * @desc 后台运维端任务统计
     * @author zhang.yingtao
     * @since  2019-10-31
     */
    public function exportOptionTask(){
    
        $where = array();
        $area = I('get.area');
        $ctime = I('get.ctime');
        $etime = I('get.etime');
        $exe_user_id = I('get.exe_user_id');
        $hotel_box_type = I('get.hotel_box_type');
        //$userid= I('user_id');
        $ctime = !empty($ctime) ? $ctime.' 00:00:00' : '';
        $etime = !empty($etime) ? $etime.' 23:59:59' : '';
        if($ctime && $etime) {
            $where['a.create_time'] = array( array('gt',$ctime),array('lt',$etime));
        } else {
            if($ctime) {
                $where['a.create_time'] = array('gt', $ctime);
            }
            if($etime) {
                $where['a.create_time'] = array('lt', $etime);
            }
        }
    
        if($area) {
            $where['a.task_area'] = $area;
        }
        if($exe_user_id){
            $where['a.exe_user_id'] = $exe_user_id;
        }
        if($hotel_box_type){
            $where['b.hotel_box_type'] = array('in',$hotel_box_type);
        }
        //$where['a.state'] = array('in','4');
        //$where['a.task_type'] = array('eq','4');
        $where['a.flag']      =0;
        $fields = "a.id, a.task_area, a.task_emerge, a.task_type,b.name hotel_name,a.hotel_address,
                   a.hotel_linkman,a.hotel_linkman_tel,tv_nums,a.state,a.create_time,a.complete_time,
                   sy.remark pub_username,a.exe_user_id ";
        $m_option_task = new \Admin\Model\OptiontaskModel();
        $list = $m_option_task->alias('a')
        ->join('savor_hotel b on a.hotel_id= b.id','left')
        ->join('savor_sysuser sy on a.publish_user_id = sy.id','left')
    
        ->field($fields)->where($where)->order('a.create_time asc ')->select();
    
        $model = D();
        $m_sys_user = new \Admin\Model\UserModel();
        foreach($list as $key=>$val){
            $repair_str = $rep_str =  '';
            $space = $space_p = '';
            $data = $model->query('select b.mac box_mac, a.box_id,b.name box_name,fault_desc,remark from
                                   savor_option_task_repair a left join savor_box b
                                   on a.box_id = b.id where a.task_id='.$val['id']);
            if(!empty($data)){
                foreach($data as $k=>$v){
                    $repair_str .= $space .'机顶盒mac：'.$v['box_mac'].' 机顶盒id:'.$v['box_id'].' 机顶盒名称:'.$v['box_name'];
                    $repair_str .=' 故障说明:'.$v['fault_desc'];
                    $space = ',';
    
                    $rep_str .= $space_p .'机顶盒mac：'.$v['box_mac'].' 机顶盒id:'.$v['box_id'].' 机顶盒名称:'.$v['box_name'];
                    $rep_str .=' 解决备注:'.$v['remark'];
                    $space_p = ',';
                }
            }
            switch ($val['task_area']){
                case '1':
                    $task_area= '北京';
                    break;
                case '9':
                    $task_area = '上海';
                    break;
                case '236':
                    $task_area = '广州';
                    break;
                case '246':
                    $task_area = '深圳';
                    break;
            }
            $list[$key]['task_area'] = $task_area;
            switch ($val['task_emerge']){
                case '2':
                    $list[$key]['task_emerge'] = '紧急';
                    break;
                case '3':
                    $list[$key]['task_emerge'] = '正常';
                    break;
            }
            switch ($val['task_type']){
                case '1':
                    $list[$key]['task_type'] = '信息检测';
                    break;
                case '8':
                    $list[$key]['task_type'] = '网络改造';
                    break;
                case '2':
                    $list[$key]['task_type'] = '安装验收';
                    break;
                case '4':
                    $list[$key]['task_type'] = '维修';
                    break;
            }
            switch ($val['state']){
                case '1':
                    $list[$key]['state'] = '新任务';
                    break;
                case '2':
                    $list[$key]['state'] = '执行中';
                    break;
                case '3':
                    $list[$key]['state'] = '排队等待';
                    break;
                case '4':
                    $list[$key]['state'] = '已完成';
                    break;
                case '5':
                    $list[$key]['state'] = '拒绝';
                    break;
    
            }
            //执行人
            if(!empty($val['exe_user_id'])){
                $exe_user_id_arr = explode(',',$val['exe_user_id']);
                $exe_user_str = '';
                $space_e = '';
                foreach($exe_user_id_arr as $v){
                    $e_ret = $m_sys_user->field('remark')->where(array('id'=>$v))->find();
                    $exe_user_str .=$space_e.$e_ret['remark'];
                    $space_e = ',';
                }
                $list[$key]['exe_user_str'] = $exe_user_str;
            }
            $list[$key]['repair_info'] = $repair_str;
            $list[$key]['rep_info']    = $rep_str;
        }
        //print_r($list);exit;
        $xlsCell = array(
            array('id', '任务id'),
            array('task_area', '任务城市'),
            array('hotel_name','酒楼名称'),
            array('hotel_address','酒楼地址'),
            array('hotel_linkman','酒楼联系人'),
            array('hotel_linkman_tel','酒楼联系人电话'),
            array('pub_username','下单人'),
            array('exe_user_str','接单人'),
            array('task_emerge','任务紧急程度'),
            array('task_type', '任务类型'),
            array('state', '实际处理情况'),
            array('tv_nums','版位数量'),
            array('create_time','发布时间'),
            array('complete_time','完成时间'),
//             array('repair_info', '维修记录'),
//             array('rep_info','解决办法')
    
        );
        $xlsName = '运维任务列表';
        $filename = 'option_task_list';
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
    
    
    }
    /**
     * @desc 后台运维端任务统计 到处维修任务列表
     * @author zhang.yingtao
     * @since  2018-04-17
     */
    public function exportRepairTask(){
        
        $where = array();
        $area = I('get.area');
        $ctime = I('get.ctime');
        $etime = I('get.etime');
        $exe_user_id = I('get.exe_user_id');
        $hotel_box_type = I('get.hotel_box_type');
        //$userid= I('user_id');
        $ctime = !empty($ctime) ? $ctime.' 00:00:00' : '';
        $etime = !empty($etime) ? $etime.' 23:59:59' : '';
        if($ctime && $etime) {
            $where['a.create_time'] = array( array('gt',$ctime),array('lt',$etime));
        } else {
            if($ctime) {
                $where['a.create_time'] = array('gt', $ctime);
            }
            if($etime) {
                $where['a.create_time'] = array('lt', $etime);
            }
        }
        
        if($area) {
            $where['a.task_area'] = $area;
        }
        if($exe_user_id){
            $where['a.exe_user_id'] = $exe_user_id;
        }
        if($hotel_box_type){
            $where['b.hotel_box_type'] = array('in',$hotel_box_type);
        }
        $where['a.state'] = array('in','4');
        $where['a.task_type'] = array('eq','4');
        $where['a.flag']      =0;
        $fields = "a.id, a.task_area, a.task_emerge, a.task_type,b.name hotel_name,a.hotel_address,
                   a.hotel_linkman,a.hotel_linkman_tel,tv_nums,a.state,a.create_time,a.complete_time";
        $m_option_task = new \Admin\Model\OptiontaskModel();
        $list = $m_option_task->alias('a')
        ->join('savor_hotel b on a.hotel_id= b.id','left')
        ->join('savor_sysuser sy on a.publish_user_id = sy.id')
        ->field($fields)->where($where)->order('a.create_time asc ')->select();
        
        $model = D();
        foreach($list as $key=>$val){
            $repair_str = $rep_str =  '';
            $space = $space_p = '';
            $data = $model->query('select b.mac box_mac, a.box_id,b.name box_name,fault_desc,remark from
                                   savor_option_task_repair a left join savor_box b
                                   on a.box_id = b.id where a.task_id='.$val['id']);
            if(!empty($data)){
                foreach($data as $k=>$v){
                    $repair_str .= $space .'机顶盒mac：'.$v['box_mac'].' 机顶盒id:'.$v['box_id'].' 机顶盒名称:'.$v['box_name'];
                    $repair_str .=' 故障说明:'.$v['fault_desc'];
                    $space = ',';
                    
                    $rep_str .= $space_p .'机顶盒mac：'.$v['box_mac'].' 机顶盒id:'.$v['box_id'].' 机顶盒名称:'.$v['box_name'];
                    $rep_str .=' 解决备注:'.$v['remark'];
                    $space_p = ',';
                }
            }
            switch ($val['task_area']){
                case '1':
                    $task_area= '北京';
                    break;
                case '9':
                    $task_area = '上海';
                    break;
                case '236':
                    $task_area = '广州';
                    break;
                case '246':
                    $task_area = '深圳';
                    break;
            }
            $list[$key]['task_area'] = $task_area;
            switch ($val['task_emerge']){
                case '2':
                    $list[$key]['task_emerge'] = '紧急';
                    break;
                case '3':
                    $list[$key]['task_emerge'] = '正常';
                    break;
            }
            switch ($val['task_type']){
                case '1':
                    $list[$key]['task_type'] = '信息检测';
                    break;
                case '8':
                    $list[$key]['task_type'] = '网络改造';
                    break;
                case '2':
                    $list[$key]['task_type'] = '安装验收';
                    break;
                case '4':
                    $list[$key]['task_type'] = '维修';
                    break;
            }
            switch ($val['state']){
                case '1':
                    $list[$key]['state'] = '新任务';
                    break;
                case '2':
                    $list[$key]['state'] = '执行中';
                    break;
                case '3':
                    $list[$key]['state'] = '排队等待';
                    break;
                case '4':
                    $list[$key]['state'] = '已完成';
                    break;
                case '5':
                    $list[$key]['state'] = '拒绝';
                    break;
        
            }
            $list[$key]['repair_info'] = $repair_str;
            $list[$key]['rep_info']    = $rep_str;
        }
        //print_r($list);exit;
        $xlsCell = array(
            array('id', '任务id'),
            array('create_time','发布时间'),
            array('complete_time','完成时间'),
            array('hotel_name','酒楼名称'),
            array('hotel_address','酒楼地址'),
            array('hotel_linkman','酒楼联系人'),
            array('hotel_linkman_tel','酒楼联系人电话'),
        
            array('task_area', '任务城市'),
            array('task_emerge','任务紧急程度'),
            array('task_type', '任务类型'),
            array('tv_nums','版位数量'),
        
            array('state', '任务状态'),
            array('repair_info', '维修记录'),
            array('rep_info','解决办法')
        
        );
        $xlsName = '运维任务列表';
        $filename = 'option_task_list';
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
        
        
    }


    public function exportShtask(){
        //2018-01-28 00:00:00
        //2018-01-30 23:59:59
        //小明，小风
        $m_option_task = new \Admin\Model\OptiontaskModel();
        $where = array();
        $area = I('get.area');
        $ctime = urldecode(I('get.ctime'));
        $etime = urldecode(I('get.etime'));
        $uerid = I('get.userid');
        $username = urldecode(I('get.username'));

        if($ctime && $etime) {
            $where['a.create_time'] = array( array('gt',$ctime,),array('lt',$etime));
        } else {
            if($ctime) {
                $where['a.create_time'] = array('gt', $ctime);
            }
            if($etime) {
                $where['a.create_time'] = array('lt', $etime);
            }
        }

        if($area) {
            $where['a.task_area'] = $area;
        }
        
        /* $where['a.state'] = array('in','1,2,3,4,5');
        $where['a.task_type'] = array('in','1,2,4,8'); */
        $where['a.state'] = array('in','4');
        $where['a.task_type'] = array('neq','4');
        $where['a.flag']      =0;





        $fields = "a.id, a.task_area, a.task_emerge, a.task_type,b.name hotel_name,a.hotel_address,
                   a.hotel_linkman,a.hotel_linkman_tel,tv_nums,a.state,a.create_time,a.complete_time";
        $list = $m_option_task->alias('a')
                              ->join('savor_hotel b on a.hotel_id= b.id','left')
            ->join('savor_sysuser sy on a.publish_user_id = sy.id')
                              ->field($fields)->where($where)->order('a.hotel_id desc ')->select();
        $model = D();
        foreach($list as $key=>$val){
            $repair_str = '';
            $space = '';
            $data = $model->query('select b.mac box_mac, a.box_id,b.name box_name,fault_desc from 
                                   savor_option_task_repair a left join savor_box b
                                   on a.box_id = b.id where a.task_id='.$val['id']);
            if(!empty($data)){
                foreach($data as $k=>$v){
                    $repair_str .= $space .'机顶盒mac：'.$v['box_mac'].' 机顶盒id:'.$v['box_id'].' 机顶盒名称:'.$v['box_name'];
                    $repair_str .=' 故障说明:'.$v['fault_desc'];
                    $space = ',';
                }
            }
            switch ($val['task_area']){
                case '1':
                    $task_area= '北京';
                    break;
                case '9':
                    $task_area = '上海';
                    break;
                case '236':
                    $task_area = '广州';
                    break;
                case '246':
                    $task_area = '深圳';
                    break;
            }
            $list[$key]['task_area'] = $task_area;
            switch ($val['task_emerge']){
                case '2':
                    $list[$key]['task_emerge'] = '紧急';
                    break;
                case '3':
                    $list[$key]['task_emerge'] = '正常';
                    break;
            }
            switch ($val['task_type']){
                case '1':
                    $list[$key]['task_type'] = '信息检测';
                    break;
                case '8':
                    $list[$key]['task_type'] = '网络改造';
                    break;
                case '2':
                    $list[$key]['task_type'] = '安装验收';
                    break;
                case '4':
                    $list[$key]['task_type'] = '维修';
                    break;
            }
            switch ($val['state']){
                case '1':
                    $list[$key]['state'] = '新任务';
                    break;
                case '2':
                    $list[$key]['state'] = '执行中';
                    break;
                case '3':
                    $list[$key]['state'] = '排队等待';
                    break;
                case '4':
                    $list[$key]['state'] = '已完成';
                    break;
                case '5':
                    $list[$key]['state'] = '拒绝';
                    break;
                    
            }
            $list[$key]['repair_info'] = $repair_str;
        }
        //print_r($list);exit;
        $xlsCell = array(
            array('id', '任务id'),
            array('create_time','发布时间'),
            array('complete_time','完成时间'),
            array('hotel_name','酒楼名称'),
            array('hotel_address','酒楼地址'),
            array('hotel_linkman','酒楼联系人'),
            array('hotel_linkman_tel','酒楼联系人电话'),
            
            array('task_area', '任务城市'),
            array('task_emerge','任务紧急程度'),
            array('task_type', '任务类型'),
            array('tv_nums','版位数量'),
            
            array('state', '任务状态'),
            array('repair_info', '维修记录'),
        
        );
        $xlsName = '上海运维任务列表';
        $filename = 'option_sh_task_list';
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
    }


    public function getho() {
       $arr = array ( 0 => '阿根廷庄园（北京店）-餐厅 ', 1 => '百富怡大酒店 ', 2 => '草菁菁（金融街店） ', 3 => '朝尚食都 ', 4 => '大益膳房 ', 5 => '东海汇渔港 ', 6 => '朵颐河鲜 ', 7 => '福润龙庭 ', 8 => '花家怡园（金融街店） ', 9 => '辉哥火锅（8号公馆店） ', 10 => '辉哥火锅（远洋国际店） ', 11 => '江南赋 ', 12 => '江仙雅居（苏州桥店） ', 13 => '经易大丰合 ', 14 => '郡王府半岛明珠酒家 ', 15 => '浏阳河大酒楼 ', 16 => '美锦酒家（港澳中心店） ', 17 => '权茂北京菜 ', 18 => '山釜餐厅 ', 19 => '山海楼 ', 20 => '石榴花开餐厅 ', 21 => '食说江南(鸭王店) ', 22 => '唐宫海鲜舫（大悦城店） ', 23 => '唐宫海鲜舫（好苑建国店） ', 24 => '唐宫海鲜舫（丽都店） ', 25 => '唐宫海鲜舫（西藏大厦店） ', 26 => '天水雅居（木樨地店） ', 27 => '天水雅居（万豪店） ', 28 => '晚枫亭（石佛营店） ', 29 => '万龙洲大兴店 ', 30 => '万龙洲海鲜大酒楼（安定门店） ', 31 => '王府茶楼中轴路店 ', 32 => '新净雅烹小鲜 ', 33 => '盐府（大望路店） ', 34 => '夜上海（长安大戏院店） ', 35 => '怡和春天（怡和店） ', 36 => '俞翰姥爷的海味人生 ', 37 => '悦府.潮州菜 ', 38 => '悦融-精致中菜(金融街店) ', 39 => '粤悦香海鲜舫 ', 40 => '镇三关重庆老火锅（工体旗舰店） ', 41 => '正院大宅门（西翠路店） ', 42 => '万国城MOMASO餐厅 ', 43 => '瞳海鲜料理(崇文门店) ', 44 => '唐宫海鲜舫（新世纪饭店店）', );
        $hotelModel = new \Admin\Model\HotelModel();
        $where['flag'] = 0;
        $where['state'] = 1;
        $ho = array();
        foreach($arr as $v) {
            $where['name']  = trim($v);
            $hotel_info = $hotelModel->where($where)->find();
            $ho[] = $hotel_info['id'];
        }
        var_export($ho);
        $ho = array ( 0 => '41', 1 => '48', 2 => '16', 3 => '70', 4 => '30', 5 => '76', 6 => '17', 7 => '9', 8 => '12', 9 => '46', 10 => '19', 11 => '38', 12 => '436', 13 => '28', 14 => '13', 15 => '126', 16 => '25', 17 => '10', 18 => '26', 19 => '33', 20 => '511', 21 => '478', 22 => '171', 23 => '32', 24 => '39', 25 => '175', 26 => '52', 27 => '99', 28 => '184', 29 => '185', 30 => '186', 31 => '196', 32 => '34', 33 => '225', 34 => '35', 35 => '232', 36 => '465', 37 => '51', 38 => '468', 39 => '37', 40 => '466', 41 => '238', 42 => '27', 43 => '517', 44 => '177', );

    }


    public function expadverwarnreport(){
        $field = 'awarn.*,sb.mac box_mac,  ( CASE awarn.report_adsPeriod WHEN "" THEN "999999999999999"
	WHEN NULL THEN "999999999999999"
ELSE awarn.report_adsPeriod END ) AS reportadsPeriod ';
        $adWarnModel = new \Admin\Model\AdverWarnModel();
        $where = '1=1';
        $order      = I('_order',' reportadsPeriod asc, awarn.last_time asc '); //排序字段
        $result = $adWarnModel->getData($field,$where,$order);

        array_walk($result, function(&$v, $k){
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
            $v['report_adsperiod'] = $v['report_adsperiod'].' ';
            $v['report_demperiod'] = $v['report_demperiod'].' ';
        });

        $xlsCell = array(
            array('id', '序号'),
            array('box_id','机顶盒ID'),
            array('box_mac','机顶盒MAC'),
            array('maintainer','维护人'),
            array('room_id','包间ID'),

            array('room_name', '包间名称'),
            array('hotel_id','酒楼ID'),
            array('hotel_name', '酒楼名称'),
            array('last_time','心跳距离现在时间'),

            array('report_adsperiod', '广告期号'),
            array('report_demperiod', '点播期号'),
            array('hea', '心跳异常'),
            array('adp', '广告未更'),
            array('vid', '点播未更'),

        );
        $xlsName = '广告播放异常预警';
        $filename = 'adver_warn_report';
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
    }
    /**
     * @desc 导出没有心跳的机顶盒版位信息
     */
    public function exportNoHeart(){
        $heart_loss_hours = C('HEART_LOSS_HOURS');
        $m_hotel = new \Admin\Model\HotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
        $hotel_box_types = getHeartBoXtypeIds(2);
        
        //$where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !='' and b.mac_addr !='000000000000'";
        $where = "  a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_types) ";
    
        //if($city_id) $where .=" and a.area_id=".$city_id;
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id');
    
    
        $normal_box_num = 0;
        $not_normal_box_num = 0;
        //print_r($hotel_list);exit;
        $start_time = date('Y-m-d H:i:s',strtotime('-'.$heart_loss_hours.' hours'));
        $m_black_list = new \Admin\Model\BlackListModel();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
        $data =  array();
        foreach($hotel_list as $key=>$v){
            $flag = 0;
            $where = '';
            $where .=" 1 and room.hotel_id=".$v['id'].' and a.state=1 and a.flag =0 and room.flag=0 and room.state=1';
            $box_list = $m_box->getListInfo( 'a.id, a.mac',$where);
            foreach($box_list as $ks=>$vs){
                $where = '';
                $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
                //$where .="  and last_heart_time>='".$start_time."'";
    
                $b_counts = $m_black_list->countNums(array('box_id'=>$vs['id']));
                $rets  = $m_heart_log->getOnlineHotel($where,'box_id');
                if(empty($rets)){
                    if(empty($b_counts)){
                        $sql =" select hotel.name hotelname,room.name roomname, box.name boxname,box.id
                                from savor_box box
                                left join savor_room room on box.room_id=room.id
                                left join savor_hotel hotel on room.hotel_id=hotel.id
                                where box.id=".$vs['id'];
                        $rt= D()->query($sql);
                        
                        $sql = "select id from savor_heart_all_log where type=2 and box_id=".$vs['id'];
                        $hrt = D()->query($sql);
                        if(empty($hrt)){
                            $tmp['hotel_name'] = $rt[0]['hotelname'];
                            $tmp['room_name']  = $rt[0]['roomname'];
                            $tmp['box_name']   = $rt[0]['boxname'];
                            $tmp['box_id']     = $rt[0]['id'];
                            $data[] = $tmp;
                        }
                        
    
                    }
                }else {
                     
                }
            }
        }
        
        $xlsCell = array(
            
            array('box_id','机顶盒ID'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
        
           
        
        );
        $xlsName = '无心跳的版位';
        $filename = 'noheartlog';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 获取某个广告发布的版位信息
     */
    public function getAdsBoxList(){
        $ads_id = I('ads_id');
        $sql ="SELECT e.name hotel_name,d.name room_name,c.name box_name,c.id box_id ,c.mac
               FROM `savor_pub_ads_box`a 
               left join savor_pub_ads b on a.pub_ads_id=b.id
               left join savor_box c on a.box_id=c.id
               left join savor_room d on c.room_id=d.id
               left join savor_hotel e on d.hotel_id=e.id 
               where b.id=$ads_id group by box_id";
        $data = D()->query($sql);
        $xlsCell = array(
        
            array('box_id','机顶盒ID'),
            array('mac','机顶盒mac'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
        
             
        
        );
        $xlsName = '无心跳的版位';
        $filename = 'noheartlog';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
        
    }
    public function getHhBox(){
        $sql ="select hotel.id hotel_id,hotel.name hotel_name,box.id box_id, box.mac,hotel.area_id,hotel.addr from savor_box box 
               left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id
               where hotel.id in(512,48,45,51,103,23,10,433,20,184,27,225,431,436,472,500,505,515,516,492,586,52,206,25,41,26,13,46,30,
243,196,12,175,126,99,185,34,70,133,9,232,435,461,104,35,16,17,172,509,107,531,468,19,39) and box.flag=0 and box.state=1";
        $data = D()->query($sql);
        foreach($data as $key=>$v){
            $sql = 'select count(id) as num from savor_tv where box_id='.$v['box_id'].' and flag=0 and state=1';
            $ret = D()->query($sql);
            $data[$key]['tv_nums'] = $ret[0]['num'];
            switch ($v['area_id']){
                case 1:
                    $data[$key]['province'] = '北京市';
                    $data[$key]['city'] = '北京市';
                    break;
                case 9:
                    $data[$key]['province'] = '上海市';
                    $data[$key]['city'] = '上海市';
                    break;
                case 236:
                    $data[$key]['province'] = '广东省';
                    $data[$key]['city'] = '广州市';
                    break;
                case 246:
                    $data[$key]['province'] = '广东省';
                    $data[$key]['city'] = '深圳市';
                    break;
            }
            
        }
        $xlsCell = array(
        
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('box_id','机顶盒id'),
            array('mac','机顶盒mac'),
            array('tv_nums','屏幕数量'),
            array('province','省份'),
            array('city','城市'),
            array('addr','详细地址'),
        
             
        
        );
        $xlsName = '花花版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 网络版酒楼盒子开机统计
     */
    public function getNetBoxHeart(){
        $heart_hotel_box_type = C('heart_hotel_box_type');
        $heart_hotel_box_type_arr = array_keys($heart_hotel_box_type);
        foreach($heart_hotel_box_type_arr as $v){
            $heart_hotel_box_type_str .=$space .$v;
            $space = ',';
        }
        
        $area_id = I('get.area_id',0,'intval');
        $where = '';
        if($area_id){
            $where .=" and hotel.area_id=$area_id";
        }
        $sql = "select hotel.id hotel_id ,hotel.name hotel_name,room.name room_name,box.id box_id,box.name box_name,
                box.mac 
                from savor_box box 
                left join savor_room room on room.id=box.room_id
                left join savor_hotel hotel on hotel.id=room.hotel_id
                where box.state=1 and box.flag=0 and hotel.state=1 and box.flag=0 and hotel.hotel_box_type in($heart_hotel_box_type_str)".$where;
        $data = M()->query($sql);
        $redis = new SavorRedis();
        $redis->select(13);
        
        foreach($data as $key=>$v){
            $cache_key =  'heartbeat:2:'.$v['mac'];
            $heart_info = $redis->get($cache_key);
            if($heart_info){
                $heart_info = json_decode($heart_info,true);
                $data[$key]['last_heart_time'] = date('Y-m-d H:i:s',strtotime($heart_info['date']));
            }else {
                $sql = "select last_heart_time from savor_heart_log where box_mac='".$v['mac']."' and type=2";
                $ret = M()->query($sql);
                $ret = $ret[0];
               
                if($ret){
                    $data[$key]['last_heart_time'] = $ret['last_heart_time'];
                }else {
                    $data[$key]['last_heart_time'] ='从未开过机';
                }
            }
            //过去一周平均开机时长
            $sql ="SELECT `hour0`+`hour1`+`hour2`+`hour3`+`hour4`+`hour5`+`hour6`+`hour7`+`hour8`+`hour9`+`hour10`+`hour11`+`hour12`+`hour13`+`hour14`+`hour15`+`hour16`+`hour17`+`hour18`+`hour19`+`hour20`+`hour21`+`hour22`+`hour23`  as all_time
                   FROM `savor_heart_all_log` WHERE `mac` ='".$v['mac']."'
                   and `date` in('20180407','20180406','20180405','20180402','20180401','20180331','20180330')";
            $all_list = M()->query($sql);
            $all_time = 0;
            foreach($all_list as $vs){
                $all_time += $vs['all_time'];
            }
            $all_time *= 5;
            $all_time = $all_time/7;
            $all_time_hour = floor($all_time/60) ? floor($all_time/60) .'小时 ' :'';
            $all_time_minute = $all_time%60 ? ($all_time%60) .'分':'';
            $all_times =  $all_time_hour . $all_time_minute;
            $data[$key]['all_times'] = $all_times;
            
            $sql ="select version_name from savor_device_upgrade a left join
               savor_device_version b on a.version =b.version_code
               where find_in_set(".$v['hotel_id'].",a.hotel_id)
               order by a.create_time desc limit 1";

            $ret = M()->query($sql);
            if($ret){
                $data[$key]['apk'] = $ret[0]['version_name'];
            }else {
                $data[$key]['apk'] = '';
            }
        }
        
        $xlsCell = array(
        
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
            array('mac','mac地址'),
            array('apk','apk版本号'),
            array('last_heart_time','上次心跳上报时间'),
            array('all_times','最近一周日均开机时长'),
        
             
        
        );
        $xlsName = '花花版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function getAvgTastTime(){
        $start_time = I('get.start_time');
        $end_time   = I('get.end_time');
        $area_id    = I('get.area_id',0,'intval');
        
        $where =" 1 ";
        $where .=" and create_time>='".$start_time."'";
        $where .=" and create_time<='".$end_time."'";
        $where .=" and state=4 and task_type=4 and flag=0";
        if(!empty($area_id)){
            $where .=" and task_area=$area_id";
        }
        $sql = ' select create_time,complete_time from savor_option_task where '.$where;
        $data = M()->query($sql);
        $all_times = 0;
        $all_nums = count($data);
        
        foreach($data as $key=>$v){
            $diff_time =  strtotime($v['complete_time']) - strtotime($v['create_time']);
            $all_times += $diff_time;      
        }
        echo $all_nums;exit;
        $avg_time = floor($all_times / $all_nums);
        echo secsToStr($avg_time);
    }
    public function getBoxHearLog(){
        
        $sql ="select * from savor_heart_all_log where date>'20180420' and date<='20180425' and type=2";
        $data = M()->query($sql);
        $xlsCell = array(
        
            array('id','序号'),
            array('area_id','区域ID'),
            array('area_name','区域名称'),
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('room_id','包间id'),
            array('room_name','包间名称'),
            array('box_id','机顶盒id'),
            
            array('mac','机顶盒mac'),
            array('date','日期'),
            array('hour0','hour0'),
            array('hour1','hour1'),
            array('hour2','hour2'),
            array('hour3','hour3'),
            array('hour4','hour4'),
            array('hour5','hour5'),
            
            array('hour6','hour6'),
            array('hour7','hour7'),
            array('hour8','hour8'),
            array('hour9','hour9'),
            array('hour10','hour10'),
            array('hour11','hour11'),
            array('hour12','hour12'),
            array('hour13','hour13'),
            array('hour14','hour14'),
            array('hour15','hour15'),
            array('hour16','hour16'),
            array('hour17','hour17'),
            
            array('hour18','hour18'),
            array('hour19','hour19'),
            array('hour20','hour20'),
            array('hour21','hour21'),
            array('hour22','hour22'),
            array('hour23','hour23'),

        );
        $xlsName = '花花版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
        
    }
    public function getHeartAdsPorid(){
        
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $where =" and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag = 0 
                 and ext.mac_addr!='000000000000' and hotel.hotel_box_type in( ".$hotel_box_type_str.")";
        $sql =" select hotel.remote_id,hotel.id hotel_id,hotel.name hotel_name,hotel.addr,room.name as room_name,box.id box_id,box.name box_name, box.mac  
                from savor_box box 
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                left join savor_hotel_ext ext on hotel.id= ext.hotel_id
                where 1".$where;
 
        $data = M()->query($sql);
        //print_r($data);exit;
        $promenuHoModel = new \Admin\Model\ProgramMenuHotelModel();
        $promenulistModel = new \Admin\Model\ProgramMenuListModel();
        
        $box_id = I('get.box_id',0,'intval');
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        
        
        foreach($data as $key=>$v){
            $condition['hotel_id'] = $v['hotel_id'];
            
            $new_menu_arr = $promenuHoModel->field('menu_id')->where($condition)->order('id desc')->find();
            
            $men_arr = $promenulistModel->field('menu_num')->find($new_menu_arr['menu_id']);
            $data[$key]['menu_num'] = $men_arr['menu_num'];
            $cache_key = C('PROGRAM_ADS_CACHE_PRE').$v['box_id'];
            //echo $cache_key;exit;
            $program_ads_info = $redis->get($cache_key);
            $program_ads_info = json_decode($program_ads_info,true);
            
            if(!empty($program_ads_info)){
                $ads_num = $program_ads_info['menu_num'];
                $data[$key]['ads_menu_num'] = $ads_num;
            }else {
                $data[$key]['ads_menu_num'] = '';
            }
        }
        
        $xlsCell = array(
        
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
            array('mac','机顶盒mac'),
            array('menu_num','节目单期号'),
            array('ads_menu_num','广告期号'),
            
        
            
        
        );
        $xlsName = '花花版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 导出广告到达明细
     */
    public function exportAdsArrDetail(){
        $sql ="SELECT
         area.region_name,
         hotel.id hotel_id,
         hotel.`name` hotel_name,
         suser.`remark` ,
         hotel.hotel_box_type ,
         room.`name` room_name,
         box.`name` box_name,
         box.mac ,
         media.`name` media_name,
         MIN(smm.on_time) AS to_time,
         heart.heart_count 
        FROM
         cloud.savor_pub_ads_box AS pab
        LEFT JOIN cloud.savor_pub_ads pa ON pab.pub_ads_id = pa.id
        LEFT JOIN cloud.savor_ads ads ON pa.ads_id = ads.id
        LEFT JOIN cloud.savor_media AS media ON ads.media_id = media.id
        LEFT JOIN cloud.savor_box AS box ON pab.box_id = box.id
        LEFT JOIN cloud.savor_room AS room ON box.room_id = room.id
        LEFT JOIN cloud.savor_hotel AS hotel ON room.hotel_id = hotel.id
        left join cloud.savor_hotel_ext as hext on hotel.id=hext.hotel_id
        left join cloud.savor_sysuser suser on hext.maintainer_id = suser.id
        LEFT JOIN cloud.savor_area_info AS area ON hotel.area_id = area.id
        LEFT JOIN statisticses.statistics_media_monitor AS smm ON box.mac = smm.box_mac
        AND ads.media_id = smm.media_id
        AND smm.report_date >= '2018-05-01 00:00:00'
        AND smm.report_date <= '2018-05-07 23:59:59'
        LEFT JOIN cloud.view_savor_heart_all_log_20180416_20180423 AS heart ON box.id = heart.box_id
        AND room.id = heart.room_id
        AND hotel.id = heart.hotel_id
        WHERE
         pa.start_date >= '2018-05-01 00:00:00'
        AND pa.start_date <= '2018-05-07 23:59:59'
        AND media.`name` IN (
         '广告5月熙珠宝35秒_新'
        )
        AND pa.state = 1
        AND hotel.hotel_box_type IN (2, 3, 6)
        AND hotel.state = 1
        AND hotel.flag = 0
        AND box.state = 1
        AND box.flag = 0
        GROUP BY
         box.id,
         box.mac,
         ads.media_id
         
         limit 0,2000";  
        $data = M()->query($sql);

        
        $xlsCell = array(
        
            array('region_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('remark','酒楼维护人'),
            array('hotel_box_type','机顶盒类型'),
            array('room_name','包间名称'),
            array('box_name','盒子名称'),
            array('mac','盒子mac'),
            array('media_name','广告名称'),
            array('to_time','到达时间'),
            array('heart_count','心跳次数')
            
        
        );
        $xlsName = '广告到达明细';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 百度聚屏  网络版盒子导表
     */
    public function polyScreenBox(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        $sql ="select box.id box_id, box.mac ,area.region_name ,hotel.name hotel_name,hotel.addr,
               case 
               when hotel.area_id=1 then '北京市'
               when hotel.area_id=9 then '上海市'
               when hotel.area_id=236 then '广东省'
               when hotel.area_id=246 then '广东省'
               end
               as province
               from savor_box box
               left join savor_room room on room.id=box.room_id
               left join savor_hotel hotel on room.hotel_id=hotel.id
               left join savor_area_info area on hotel.area_id=area.id
               where hotel.state=1 and hotel.flag=0 and box.state=1  and box.flag=0 and hotel.hotel_box_type in($hotel_box_type_str) order by hotel.area_id asc,hotel.id asc,box.id desc ";
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            
            $sql ="select tv_size from savor_tv where box_id=".$v['box_id'];
            $tv_info = M()->query($sql);
            $mac = $v['mac'];
            $tmp =  $mac[0].$mac[1].":".$mac[2].$mac[3].":".$mac[4].$mac[5].":".$mac[6].$mac[7].":".$mac[8].$mac[9].":".$mac[10].$mac[11];
            $data[$key]['tv_size'] = $tv_info[0]['tv_size'];
            $data[$key]['mac'] = $tmp;
            $sql ="select count(1) as nums from savor_tv where box_id=".$v['box_id']." and state=1 and flag=0";
            $ret = M()->query($sql);
            $data[$key]['tv_nums'] = $ret[0]['nums'];
        }
        $xlsCell = array(
        
            array('tv_size','尺寸'),
            array('mac','盒子mac'),
            array('tv_nums','屏幕数'),
            array('province','省份'),
            array('region_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            
        
        
        );
        $xlsName = '百度聚品广告版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function getAdsNotArriveDetail(){
        $pub_ads_id =  I('pub_ads_id');
        $media_id   =  I('media_id');
        $sql =" SELECT e.region_name,d.name hotel_name,d.addr,c.name room_name,b.name box_name, b.mac
                FROM cloud.`savor_pub_ads_box` a
                left join cloud.savor_box b on a.box_id = b.id
                left join cloud.savor_room c on b.room_id = c.id
                left join cloud.savor_hotel d on c.hotel_id=d.id
                left join cloud.savor_area_info e on d.area_id=e.id
                WHERE a.pub_ads_id = $pub_ads_id and b.flag=0 and b.state=1 and d.flag=0 and d.state=1  group by box_id ";
        $data = M()->query($sql);
        $result = array();
        $m_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
        foreach($data as $key=>$v){
            $nums  = $m_box_media_arrive->getCount(array('media_id'=>$media_id,'box_mac'=>$v['mac']));
            $tmp = array();
            if(empty($nums)){
                $tmp['region_name'] = $v['region_name'];
                $tmp['hotel_name']  = $v['hotel_name'];
                $tmp['addr']        = $v['addr'];
                $tmp['room_name']   = $v['room_name'];
                $tmp['box_name']    = $v['box_name'];
                $tmp['mac']         = $v['mac'];
                $result[] = $tmp;
            }
        }
        $xlsCell = array(
        
            array('region_name','地区'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
            array('mac','机顶盒mac'),
         
        
        
        
        );
        $xlsName = '广告未到达';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
    }
    /**
     * @desc 广告一个都没到的版位列表
     * @since 2018-06-25
     */
    public function notArriveBox(){
        $report_time = I('report_time');
        
        $m_box_media_arrive_ratio_hiistory = new \Admin\Model\Statisticses\BoxMediaArriveRatioHistroyModel();
        $sql ="SELECT a.hotel_id,b.name hotel_name,c.mac_addr FROM statisticses.`statistics_box_media_arrive_ratio_histroy`  a
               left join cloud.savor_hotel b on a.hotel_id=b.id
               left join cloud.savor_hotel_ext c on b.id=c.hotel_id
               where a.media_id=-10000 and a.arrive_ratio<1.00 and a.statistics_time='2018-06-25 00:00:00'
               and a.hotel_id not in('7,53,791,747,508')
               ORDER BY a.`media_id` ASC ";
        $hotel_list = $m_box_media_arrive_ratio_hiistory->query($sql);
        
        //获取再投的广告列表
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        $yesterday_end_time = date('Y-m-d 23:59:59',strtotime($report_time));
        $yesterday_start_time = date('Y-m-d 00:00:00',strtotime($report_time));
        
        $where['pads.start_date'] = array('lt',$yesterday_end_time);
        $where['pads.end_date']   = array('gt',$yesterday_start_time);
        $where['pads.state']      = array('neq',2);
        $where['pads.id']         = array('not in','115,116,117,118,119,120,121,122');
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);        
        //print_r($pub_ads_list);exit;
        /* foreach($pub_ads_list as $key=>$v){
            $media_str .=$space .$v['media_id'];
            $space = ',';
        }
        */

        $m_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $m_pub_ads_box_history = new \Admin\Model\PubAdsBoxHistoryModel();
        $redis = SavorRedis::getInstance();
        $time = time();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        
        $result = array();
        foreach($hotel_list as $keys=>$val){
            //获取酒楼正常机顶盒列表
            
            $fields = 'b.id box_id,mac box_mac,b.name box_name';
            $where = ' 1 and h.id='.$val['hotel_id'].' and h.state=1 and h.flag=0 and b.state=1 and b.flag=0'; 
            $box_list = $m_box->isHaveMac($fields, $where);
            
            foreach($box_list as $key=>$v){
                $down_count = 0;
                $pub_count = 0;
                foreach($pub_ads_list as $kk=>$vv){
                    
                    $where = array();
                    $where['media_id'] = $vv['media_id'];
                    $where['media_type'] = 'ads';
                    $where['box_mac'] = $v['box_mac'];
                    $where['report_date'] = array('ELT',$report_time.' 23:59:59') ;
                    //print_r($where);exit;
                    $nums = $m_box_media_arrive->getCount($where);
                    if(!empty($nums)){//已下载
                        $down_count ++;
                        break;
                    }else {
                        $where = array();
                        $where['pub_ads_id'] = $vv['pub_ads_id'];
                        $where['box_id']     = $v['box_id'];
                        
                        $nums1 = $m_pub_ads_box->getDataCount($where);
                        //$nums2 = $m_pub_ads_box_history->getDataCount($where);
                        $nums = $nums1;
                        
                        if(empty($nums)){ //未发布
                            
                        }else {//发布未下载
                            
                            $pub_count ++;
                        }
                    }
                }
                
                if($down_count>0) continue;
                if($pub_count>0){
                    $tmp['hotel_name'] = $val['hotel_name'];
                    if($val['mac_addr']=='000000000000'){
                        $tmp['small_mac_type'] = '虚拟';
                    }else {
                        $tmp['small_mac_type'] = '实体';
                    }
                    $tmp['box_name'] = $v['box_name'];
                    //获取机顶盒的心跳时间
                    $redis->select(13);
                    $heart_info = $redis->get('heartbeat:2:'.$v['box_mac']);
                    if($heart_info){
                        $heart_info = json_decode($heart_info,true);
                        $last_heart_time = date('Y-m-d H:i:s',strtotime($heart_info['date']));
                    }else {
                        $last_heart_time = '30天前';
                    }
                    
                    $tmp['last_heart_time'] = $last_heart_time;
                    $result[] = $tmp;
                }
   
                   
                
            } //  $box_list 结束
        }// $hotel_list 结束
        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('small_mac_type','小平台类型'),
            array('hotel_box_type','机顶盒类型'),
            array('box_name','盒子名称'),
            array('last_heart_time','最后一次心跳时间')
        );
        $xlsName = '到达率为0的版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
    }
    /**
     * @desc 获取网络版酒楼的广告到达明细
     */
    public function getMoco(){
        $report_time = I('report_time');
        //获取再投的广告列表
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        
        
        $yesterday_end_time = date('Y-m-d 23:59:59',strtotime($report_time));
        $yesterday_start_time = date('Y-m-d 00:00:00',strtotime($report_time));
        
        $where['pads.start_date'] = array('lt',$yesterday_end_time);
        $where['pads.end_date']   = array('gt',$yesterday_start_time);
        $where['pads.state']      = array('neq',2);
        $where['pads.id']         = array('not in','115,116,117,118,119,120,121,122');
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name,med.oss_addr';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);
        echo $m_pub_ads->getLastSql();exit;
        
        $m_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $m_pub_ads_box_history = new \Admin\Model\PubAdsBoxHistoryModel();
        $redis = SavorRedis::getInstance();
        $time = time();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        
        //获取版位信息
        
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $arrive_date = date('Y-m-d',strtotime($report_time)+86400);
        
        $where = array();
        $where['a.media_id'] = '-10000';
        $where['a.hotel_id'] = array('gt',0);
        $where['statistics_time'] = array(array('EGT',$arrive_date.' 00:00:00'),array('ELT',$arrive_date.' 23:59:59'));
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['box.state'] = 1;
        $where['box.flag']  = 0;
        $where['hotel.id'] = array('not in',array(7,53,791,747,508));
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_type_str);
        
        $m_box = new \Admin\Model\BoxModel();
        $box_list = $m_box->alias('box')
              ->join('cloud.savor_room room  on box.room_id=room.id','left')
              ->join('cloud.savor_hotel hotel on room.hotel_id = hotel.id','left')
              ->join('statisticses.statistics_box_media_arrive_ratio_histroy a on a.hotel_id=hotel.id','left')
              ->field('hotel.name hotel_name,room.name room_name,box.name box_name,box.id box_id,box.mac box_mac')
              ->where($where)
              ->select();
        
        //print_r($box_list);exit;
        foreach($box_list as $key=>$v){
            
            foreach($pub_ads_list as $kk=>$vv){
                
                $where = array();
                $where['media_id'] = $vv['media_id'];
                $where['media_type'] = 'ads';
                $where['box_mac'] = $v['box_mac'];
                $where['report_date'] = array('ELT',$report_time.' 23:59:59') ;
                //print_r($where);exit;
                $nums = $m_box_media_arrive->getCount($where);
                if(!empty($nums)){//已下载
                    $box_list[$key]['ads_list'.$kk] = '已下载';
                    //$ads_list[$kk] = '已下载';
                }else {
                    $where = array();
                    $where['pub_ads_id'] = $vv['pub_ads_id'];
                    $where['box_id']     = $v['box_id'];
                    
                    $nums1 = $m_pub_ads_box->getDataCount($where);
                    $nums2 = $m_pub_ads_box_history->getDataCount($where);
                    $nums = $nums1+$nums2;
                    
                    if(empty($nums)){ //未发布
                        $box_list[$key]['ads_list'.$kk] = '未发布';
                        //$ads_list[$kk] = '未发布';
                    }else {//发布未下载
                        $box_list[$key]['ads_list'.$kk] = '未下载';
                        //$ads_list[$kk] = '未下载';
                    }
                }
            }
            //$box_list[$key]['ads_list'] = $ads_list;
            //获取机顶盒的心跳时间
            $redis->select(13);
            $heart_info = $redis->get('heartbeat:2:'.$v['box_mac']);
            $heart_info = json_decode($heart_info,true);
            if(!empty($heart_info)){
                $d_time = strtotime($heart_info['date']);
                $diff = $time - $d_time;
                if($diff< 3600) {
                    $last_heart_time = floor($diff/60).'分';
                     
                }else if ($diff >= 3600 && $diff <= 86400) {
                    $hour = floor($diff/3600);
                    $min = floor($diff%3600/60);
                    $last_heart_time = $hour.'小时'.$min.'分';
                }else if ($diff > 86400) {
                    $day = floor($diff/86400);
                    $hour = floor($diff%86400/3600);
                    $last_heart_time = $day.'天'.$hour.'小时';
                }
            }else {
                $heart_info = $m_heart_log->getInfo('last_heart_time,apk_version as apk', array('box_id'=>$v['box_id']));
                if(!empty($heart_info)){
                    $d_time = strtotime($heart_info['last_heart_time']);
                    $diff = $time - $d_time;
                    if($diff< 3600) {
                        $last_heart_time = floor($diff/60).'分';
                         
                    }else if ($diff >= 3600 && $diff <= 86400) {
                        $hour = floor($diff/3600);
                        $min = floor($diff%3600/60);
                        $last_heart_time = $hour.'小时'.$min.'分';
                    }else if ($diff > 86400) {
                        $day = floor($diff/86400);
                        $hour = floor($diff%86400/3600);
                        $last_heart_time = $day.'天'.$hour.'小时';
                    }
                }else {
                    $last_heart_time='30天';
                }
                
            }
            
            $box_list[$key]['apk']        = $heart_info['apk'];
            $box_list[$key]['heart_time'] = $last_heart_time.'前';
            
        }
        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
            array('box_mac','机顶盒mac'),
            array('ads_list0','广告7月夜行实录30秒'),
            array('ads_list1','广告7月《邪不压正》电影宣传片30秒'),
            array('heart_time','最后心跳时间'),
            array('apk','机顶盒apk版本')
        );
        $xlsName = '到达率为0的版位';
        $filename = 'hhboxlist';
        $this->exportExcel($xlsName, $xlsCell, $box_list,$filename);
    }

    public function countForscreenByboxpy(){
        $s_date = I('get.s_date');
        $e_date = I('get.e_date');
        $small_app_id = I('small_app_id',0,'intval');
        $is_valid = I('is_valid',1,'intval');

        $cache_key = C('SAPP_SCRREN').':exportbox'.$s_date.$e_date.$small_app_id.$is_valid;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if($res == 1){
                $this->success('数据正在生成中,请稍后点击下载');
//                $this->output('数据正在生成中,请稍后点击下载', 'sappforscreen/index',2);
            }else{
                //下载
                $dir = SITE_TP_PATH;
                $file_name = $res;
                download($dir,$file_name);
            }
        }else{
            $shell = "/opt/anaconda/envs/python36/bin/python  /application_data/web/python/smallapp/countForscreenBybox.py $s_date $e_date $small_app_id $is_valid > /tmp/null &";
            system($shell);
            $redis->set($cache_key,1,7200);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    /**
     * @desc 小程序网络监测
     */
    public function smallappNet(){
        $start_date = I('get.start_date') ? I('get.start_date') : '2018-09-07';
        $end_date   = I('get.end_date')   ? I('get.end_date')   : date('Y-m-d',strtotime('-1 day'));
        $hotel_name = I('get.hotel_name');
        
        $where = array();
        $where['a.static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
        if($hotel_name) $where['hotel.name'] = array('like',"%$hotel_name%");
        $group = "a.hotel_id";
        $orders = "hotel.id asc";
        $fields = "a.id,a.hotel_id,hotel.name hotel_name,area.region_name";
        $m_static_net = new \Admin\Model\Smallapp\StaticNetModel();
        $hotel_list = $m_static_net->getList($fields,$where,$orders,$group);
        foreach($hotel_list as $key=>$v){
            $map = array();
            $map['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $map['hotel_id'] = $v['hotel_id'];
            $fields = 'sum(`box_donw_nums`) box_donw_nums,sum(`res_size`) res_size,
	                   sum(`order_times`) order_times,sum(`avg_down_speed`) avg_down_speed,
	                   sum(`avg_delay_time`) avg_delay_time,max(`max_down_speed`) max_down_speed,
	                   min(`min_down_speed`) min_down_speed,max(`max_delay_times`) max_delay_times,
	                   min(`min_delay_times`) min_delay_times';
            $ret  = $m_static_net->searchList($fields, $map);
            $nums = $m_static_net->countWhere($map);
            $hotel_list[$key]['box_down_nums'] = $ret[0]['box_donw_nums'];              //总下载次数
            $hotel_list[$key]['res_sizev']     = formatBytes($ret[0]['res_size']);      //总资源大小
            $hotel_list[$key]['order_times']   = $ret[0]['order_times'];                //总质量次数
             
            $hotel_list[$key]['avg_down_speed']= formatBytes($ret[0]['avg_down_speed'] / $nums).'/S';
            $hotel_list[$key]['avg_delay_time']= $ret[0]['avg_delay_time'] /$nums;
            $hotel_list[$key]['max_down_speed']= formatBytes($ret[0]['max_down_speed']) .'/S';
            $hotel_list[$key]['min_down_speed']= formatBytes($ret[0]['min_down_speed']) .'/S';
            $hotel_list[$key]['max_delay_times']= $ret[0]['max_delay_times'];
            $hotel_list[$key]['min_delay_times']= $ret[0]['min_delay_times'];
        }
        $xlsCell = array(
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('region_name','城市'),
            array('box_down_nums','总下载次数'),
            array('res_sizev','总资源大小'),
            array('avg_down_speed','平均下载速度'),
            array('max_down_speed','最快下载速度'),
            array('min_down_speed','最慢下载速度'),
            array('order_times','指令次数'),
            array('avg_delay_time','平均指令延时'),
            array('max_delay_times','最高延时'),
            array('min_delay_times','最低延时'),
        );
        $xlsName = '小程序网络监测';
        $filename = 'smallapp_staticnet';
        $this->exportExcel($xlsName, $xlsCell, $hotel_list,$filename);
    }

    public function smallappDetail(){
        set_time_limit(180);
        ini_set("memory_limit","1024M");
        $day = I('get.day',0,'intval');
        $start_date = I('get.start_date','');
        $end_date = I('get.end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);
        $data = array();
//        $hotellevel_c = A('Smallapp/Hotellevel');
        $m_static_hotel = new \Admin\Model\Smallapp\StaticHotelgradeModel();
        foreach ($days as $k=>$v){
            $ratenums = $m_statistics->getRatenum($v,0,0);
            $detail = array('date'=>$v,'fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
            $detail['conversion'] = $m_statistics->getRate($ratenums,1).'%';
            $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
            $detail['screens'] = $m_statistics->getRate($ratenums,3).'%';
            $detail['network'] = $m_statistics->getRate($ratenums,4).'%';
            $fields = 'hotel_id,level';
            $where = array('static_date'=>$v);
            $order = 'id desc';
            $res_hotel = $m_static_hotel->getListnums($fields,$where,$order);
            $detail['hotela'] = $res_hotel['a'];
            $detail['hotelb'] = $res_hotel['b'];
            $detail['hotelc'] = $res_hotel['c'];
            /*
            $hotel_level = $hotellevel_c->getHotellevel($v);
            $detail['hotela'] = $hotel_level['a'];
            $detail['hotelb'] = $hotel_level['b'];
            $detail['hotelc'] = $hotel_level['c'];
            */
            $data[] = $detail;
        }
        $xlsCell = array(
            array('date','日期'),
            array('conversion','转换率'),
            array('transmissibility','传播力'),
            array('screens','屏幕在线率'),
            array('network','网络质量'),
            array('fjnum','互动饭局数'),
            array('zxnum','在线屏幕数'),
            array('hdnum','互动次数'),
            array('hotela','A级酒楼'),
            array('hotelb','B级酒楼'),
            array('hotelc','C级酒楼'),
        );
        $xlsName = '小程序概况详细数据';
        $filename = 'smallapp_generalsituationdetail';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    public function smallappGradedetail(){
        set_time_limit(180);
        ini_set("memory_limit","1024M");
        $level = I('get.level',0,'intval');
        $start_date = I('get.start_date','');
        $end_date = I('get.end_date','');
        $m_static_hotel = new \Admin\Model\Smallapp\StaticHotelgradeModel();
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $days = $m_statistics->getDays(0,$start_date,$end_date);

        $levels = array(1=>'A',2=>'B',3=>'C');
        $data = array();
        foreach ($days as $dv){
            $fields = '*';
            $where = array('static_date'=>$dv);
            if($level){
                $where['level'] = $level;
            }
            $order = 'id asc';
            $res_hotel = $m_static_hotel->getList($fields,$where,$order);

            foreach ($res_hotel as $k=>$v){
                $hotel_id = $v['hotel_id'];
                $resh = $m_hotel->getOne($hotel_id);
                $info = array('date'=>$v['static_date'],'name'=>$resh['name'],'level_str'=>$levels[$v['level']]);
                $ratenums = $m_statistics->getRatenum($v['static_date'],0,9,$hotel_id);
                $info['heart_log_nums'] = $ratenums['xtnum'];
                $info['avg_speed'] = $v['avg_speed'].'kb/s';
                $info['interact_num'] = $v['interact_num'];
                $where = array();
                $where['hotel_id'] = $v['hotel_id'];
                $where['static_date'] = $v['static_date'];
                $fields =" count(id) as nums";
                $ret = $m_statistics->getOne($fields, $where);
                $info['all_position'] = intval($ret['nums']);
                $info['online_screen_num'] = $v['online_screen_num'];
                $info['position_num'] = $v['position_num'];
                $cvr = $v['cvr']*100;
                $info['cvr'] = $cvr.'%';
                $avg_coverage = round($v['avg_coverage'],1);
                $info['avg_coverage'] = $avg_coverage.'%';
                $data[] = $info;
            }
        }
        $xlsCell = array(
            array('date','日期'),
            array('level_str','级别'),
            array('name','名称'),
            array('heart_log_nums','心跳次数'),
            array('avg_speed','平均网速'),
            array('interact_num','互动次数'),
            array('all_position','版位数量'),
            array('online_screen_num','在线屏数'),
            array('position_num','互动版位'),
            array('cvr','转换率'),
            array('avg_coverage','覆盖率'),
        );
        $xlsName = '小程序概况详细数据';
        $filename = 'smallapp_hotelgradedetail';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    public function smallappHoteldata(){
        $hotel_id = I('hotel_id',0,'intval');
        $day = I('day',7,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);

        //详细数据
        $data = array();
        if($hotel_id){
            $hotellevel_c = A('Smallapp/Hotellevel');
            foreach ($days as $k=>$v){
                $ratenums = $m_statistics->getRatenum($v,0,0,$hotel_id);
                $detail = array('date'=>$v,'fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['conversion'] = $m_statistics->getRate($ratenums,1);
                $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
                $detail['screens'] = $m_statistics->getRate($ratenums,3);
                $detail['network'] = $m_statistics->getRate($ratenums,4);
                $hotel_info = $hotellevel_c->getHotellevel($v,$hotel_id,1);
                $detail['level'] = $hotel_info['level'];
                $detail['score'] = $hotel_info['score'];
                $detail['net_score'] = $hotel_info['net_score'];
                $detail['wake_score'] = $hotel_info['wake_score'];
                $detail['hd_score'] = $hotel_info['hd_score'];
                $data[] = $detail;
            }
        }
        $xlsCell = array(
            array('date','日期'),
            array('level','级别'),
            array('score','综合评分'),
            array('net_score','网络评分'),
            array('wake_score','开机评分'),
            array('hd_score','互动评分'),
            array('conversion','转换率'),
            array('transmissibility','传播力'),
            array('screens','屏幕在线率'),
            array('network','网络质量'),
            array('fjnum','互动饭局数'),
            array('zxnum','在线屏幕数'),
            array('hdnum','互动次数'),
        );
        $xlsName = '小程序酒楼数据详细数据';
        $filename = 'smallapp_hoteldata';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }


    public function smallappBoxdata(){
        $box_mac = I('box_mac','','trim');
        $day = I('day',7,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);

        //详细数据
        $data = array();
        if($box_mac){
            $hotellevel_c = A('Smallapp/Hotellevel');
            foreach ($days as $k=>$v){
                $ratenums = $m_statistics->getRatenum($v,0,0,0,$box_mac);
                $detail = array('date'=>$v,'fjnum'=>$ratenums['fjnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
                $detail['screens'] = $m_statistics->getRate($ratenums,3);
                $detail['network'] = $m_statistics->getRate($ratenums,4);
                $nums = $hotellevel_c->getMacscore($v,$box_mac);
                $detail['score'] = $nums['score'];
                $data[] = $detail;
            }
        }
        $xlsCell = array(
            array('date','日期'),
            array('score','评分'),
            array('transmissibility','传播力'),
            array('screens','屏幕在线率'),
            array('network','网络质量'),
            array('fjnum','互动饭局数'),
            array('hdnum','互动次数'),
        );
        $xlsName = '小程序版位数据详细数据';
        $filename = 'smallapp_boxdata';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    public function exportStatic(){
        
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $m_hotel =  new \Admin\Model\HotelModel();
        $sql ="select id from savor_hotel where flag=0 and state=1 and hotel_box_type in($hotel_box_type_str)";
        $where = array();
        $where['flag'] = 0;
        $where['state']= 1;
        $where['hotel_box_type'] = array('in',$hotel_box_type_arr);
        
        $hotel_list = $m_hotel->field('id hotel_id,name hotel_name')->where($where)->select();
        $data = array();
        foreach($hotel_list as $key=>$v){
            //总屏幕数
            $sql ="select count(box.id) as nums from savor_box box
                   left join savor_room room on box.room_id=room.id
                   left join savor_hotel hotel on room.hotel_id=hotel.id
                   where hotel.id=".$v['hotel_id']." and box.flag=0 and box.state=1";
            $tmp = M()->query($sql);
            
            $all_box_nums = $tmp[0]['nums'];  //总屏幕数
            
           
            
            //在线屏幕数
            $sql ="select id from savor_smallapp_statistics where heart_log_nums>0 and static_date=20181117
                   and hotel_id = ".$v['hotel_id']." and static_fj=1 group by box_id";
            
            $ret =  M()->query($sql);
            $sw_nums = count($ret);
            $sql ="select id from savor_smallapp_statistics where heart_log_nums>0 and static_date=20181117
                   and hotel_id = ".$v['hotel_id']." and static_fj=2 group by box_id";
            
            $ret =  M()->query($sql);
            $xw_nums = count($ret);
            
            
            $online_box_num = $sw_nums +$xw_nums;
            
            
            
            //可投屏总数
            $sql ="select id from savor_smallapp_statistics where heart_log_nums>0 and avg_down_speed>200000 and static_date=20181117
                    and hotel_id = ".$v['hotel_id']." and static_fj=1  group by box_id";
            
            $ret =  M()->query($sql);
            $sw_nums = count($ret);
            
            
            $sql ="select id from savor_smallapp_statistics where heart_log_nums>0 and avg_down_speed>200000 and static_date=20181117
                    and hotel_id = ".$v['hotel_id']." and static_fj=2  group by box_id";
            
            $ret =  M()->query($sql);
            $xw_nums = count($ret);
            $forscreen_box_num = $sw_nums + $xw_nums;
            
            
            //互动饭局数
            $sql ="select id from savor_smallapp_statistics where (forscreen_all_nums+game_nums)>0  and static_date=20181117
                    and hotel_id = ".$v['hotel_id']." and static_fj=1 group by box_id";
            
            $ret =  M()->query($sql);
            $sw_nums = count($ret);
            
            $sql ="select id from savor_smallapp_statistics where (forscreen_all_nums+game_nums)>0  and static_date=20181117
                    and hotel_id = ".$v['hotel_id']." and static_fj=2 group by box_id";
            
            $ret =  M()->query($sql);
            $xw_nums = count($ret);
            
            $hdfj_num = $sw_nums + $xw_nums;
            
            
            
            $data[$key]['hotel_id'] = $v['hotel_id'];
            $data[$key]['hotel_name'] = $v['hotel_name'];
            $data[$key]['all_box_nums'] =$all_box_nums;
            $data[$key]['online_box_num'] = $online_box_num;
            $data[$key]['forscreen_box_num'] = $forscreen_box_num;
            $data[$key]['hdfj_num']  = $hdfj_num;
        }
        $xlsCell = array(
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('all_box_nums','屏幕总数'),
            array('online_box_num','在线屏数'),
            array('forscreen_box_num','可投屏总数'),
            array('hdfj_num','互动饭局数'),
            
        );
        $xlsName = '小程序网络监测';
        $filename = 'smallapp_staticnet';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function sappstatic(){
        $yesterday = date('Ymd',strtotime('-1 day'));
        $start_date = I('start_date') ? date('Ymd',strtotime(I('start_date'))) : $yesterday;
        $end_date   = I('end_date') ? date('Ymd',strtotime(I('end_date'))) : $yesterday;
        $area_id    = I('area_id',0,'intval');
        $fj         = I('fj',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');
        
        $where = array();
        $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
        if($area_id){
            $where['area_id'] = $area_id;   
        }
        if($fj){
            $where['static_fj'] = $fj;
        }
        if($maintainer_id){
            $where['maintainer_id'] = $maintainer_id;  
        }
        $m_statics = new \Admin\Model\Smallapp\StatisticsModel();
        $hotel_list = $m_statics->getWhere('hotel_id,hotel_name', $where, '','', 'hotel_id');

        $m_user = new \Admin\Model\UserModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $all_hotel_types = C('heart_hotel_box_type');


        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $conf_list = $m_static_config->getWhere('conf_data,type',array('status'=>1));
        $conf_arr = array();
        foreach($conf_list as $key=>$v){
            $conf_arr[$v['type']] = json_decode($v['conf_data'],true);
        }
        $m_qrcode = new \Admin\Model\Smallapp\QrcodeLogModel();
        $qrcode_types = array(1,2,3,5,6,7,8,9,10,11,12,13,15,16,19,20,21,29,30);
        $datas = array();
        foreach($hotel_list as $key=>$v){
            $res_hotel = $m_hotel->getHotelInfo('a.name as hotel_name,a.hotel_box_type,ext.maintainer_id',array('a.id'=>$v['hotel_id'],'state'=>1,'flag'=>0));
            if(empty($res_hotel)){
                continue;
            }
            $v['hotel_name'] = $res_hotel['hotel_name'];
            $hotel_box_type = $all_hotel_types[$res_hotel['hotel_box_type']];
            $res_user = $m_user->getUserInfo($res_hotel['maintainer_id']);
            $maintainer_name = '';
            if(!empty($res_user)){
                $maintainer_name = $res_user['remark'];
            }

            //扫码数
            $fields = 'count(a.id) as num';
            $where = array('box.state'=>1,'box.flag'=>0,'hotel.id'=>$v['hotel_id']);
            $where['a.type'] = array('in',$qrcode_types);
            $qrstart_time = date("Y-m-d 00:00:00",strtotime($start_date));
            $qrend_time = date("Y-m-d 23:59:59",strtotime($end_date));
            $where['a.create_time'] = array(array('EGT',$qrstart_time),array('ELT',$qrend_time));
            $res_qrcode = $m_qrcode->getScanqrcodeNum($fields,$where,'');
            $qrcode_num = 0;
            if(!empty($res_qrcode)){
                $qrcode_num = intval($res_qrcode[0]['num']);
            }

            //综合评分-心跳分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['heart_log_nums'] = array('GT',0);
            $fields = "sum(`heart_log_nums`) as heart_log_nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_log_nums = intval($ret['heart_log_nums']);
        
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_box_nums = $ret['nums'];
            $avg_heart_log_nums =round( $heart_log_nums / $heart_box_nums);
        
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[3]);
            $multy_heart_score = $conf_arr[1]['heart'] * $score;
        
            //综合评分-网速分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['avg_down_speed'] = array('GT',0);
            $fields = "sum(`avg_down_speed`) as avg_down_speed";
            $ret    = $m_statics->getOne($fields, $where);
            $avg_down_speed = $ret['avg_down_speed'];
        
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $speed_box_nums = $ret['nums'];
            $avg_down_speed = round($avg_down_speed / ($speed_box_nums*1024));
        
            $score = $this->getScore($avg_down_speed, $conf_arr[4]);
        
            $multy_net_score = $conf_arr[1]['net'] * $score;
        
            //综合评分-互动分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['all_interact_nums'] = array('GT',0);
            $fields = "sum(`all_interact_nums`) as all_interact_nums";
            $ret    = $m_statics->getOne($fields, $where);
            $all_interact_nums = intval($ret['all_interact_nums']);
        
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $hd_box_nums = $ret['nums'];
        
            $avg_interact_nums = round($all_interact_nums / $hd_box_nums);
        
            $score = $this->getScore($avg_interact_nums, $conf_arr[5]);
            $multy_hd_score = $conf_arr[1]['hd'] * $score;
        
            //综合评分 - 互动覆盖率分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $all_box_nums = $ret['nums'];
            $cover_rate = round($hd_box_nums / $all_box_nums * 100);
        
            $score = $this->getScore($cover_rate, $conf_arr[6]);
            $multy_cover_score = $conf_arr[1]['cover'] * $score;
        
            $multy_score = $multy_heart_score + $multy_net_score + $multy_hd_score + $multy_cover_score;
            $multy_score = round($multy_score);
            $multy_level = $this->getLevel($multy_score, $conf_arr[2]);   //综合评分
        
            if($multy_level=='A'){
                $mult_level_type = 1;
                
            }else if($multy_level=='B'){
                $mult_level_type = 2;
                
            }else if($multy_level == 'C'){
                $mult_level_type = 3;
                
            }

            //开机评分-心跳分数
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[13]);
            $wake_heart_score = $conf_arr[11]['heart'] * $score;
        
            //开机评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[14]);
            $wake_net_score = $conf_arr[11]['net'] * $score;
        
            //开机评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[15]);
            $wake_hd_score = $conf_arr[11]['hd'] * $score;
        
            //开机评分 - 互动覆盖率分数
            $score = $this->getScore($cover_rate, $conf_arr[16]);
            $wake_cover_score = $conf_arr[11]['cover'] * $score;
        
            $wake_score = $wake_heart_score + $wake_net_score + $wake_hd_score + $wake_cover_score;
            $wake_score = round($wake_score);
            //网络评分-心跳分数
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[23]);
            $net_heart_score = $conf_arr[21]['heart'] * $score;
        
            //网络评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[24]);
            $net_net_score = $conf_arr[21]['net'] * $score;
        
            //网络评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[25]);
            $net_hd_score = $conf_arr[21]['hd'] * $score;
        
            //网络评分 - 互动覆盖率分数
            $score = $this->getScore($cover_rate, $conf_arr[26]);
            $net_cover_score = $conf_arr[21]['cover'] * $score;
        
            $net_score = $net_heart_score + $net_net_score + $net_hd_score + $net_cover_score;
            $net_score = round($net_score);
            //互动评分-心跳分数
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[33]);
            $hd_heart_score = $conf_arr[31]['heart'] * $score;
            //互动评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[34]);
            $hd_net_score = $conf_arr[31]['net'] * $score;
            //互动评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[35]);
            $hd_hd_score = $conf_arr[31]['hd'] * $score;
        
            //互动评分-互动覆盖率分数
            $score = $this->getScore($cover_rate, $conf_arr[36]);
            $hd_cover_score = $conf_arr[31]['cover'] * $score;
            $hd_score = $hd_heart_score + $hd_net_score + $hd_hd_score + $hd_cover_score;
            $hd_score = round($hd_score);

            $dinfo = $v;
            $dinfo['mult_level_type'] = $mult_level_type;
            $dinfo['multy_level']       = $multy_level;
            $dinfo['mylty_score']       = $multy_score;
            $dinfo['net_score']         = $net_score;
            $dinfo['wake_score']        = $wake_score;
            $dinfo['hd_score']          = $hd_score;
            $dinfo['heart_log_nums']    = $heart_log_nums;
            $dinfo['avg_down_speed']    = $avg_down_speed ? $avg_down_speed.'kb/s' : '';
            $dinfo['all_interact_nums'] = $all_interact_nums;
            $dinfo['all_box_nums'] = $all_box_nums;
            $dinfo['hd_box_nums']       = $hd_box_nums;
            $dinfo['qrcode_num']       = $qrcode_num;
            $dinfo['hotel_box_type']       = $hotel_box_type;
            $dinfo['maintainer_name']       = $maintainer_name;
            $datas[]=$dinfo;
        }
        sortArrByOneField($datas,'mylty_score',true);
        $xlsCell = array(
            
            array('hotel_name','酒楼名称'),
            array('multy_level','级别'),
            array('mylty_score','综合评分'),
            array('net_score','网络评分'),
            array('wake_score','开机评分'),
            array('hd_score','互动评分'),
            array('heart_log_nums','心跳次数'),
            array('avg_down_speed','平均网速'),
            array('all_interact_nums','互动次数'),
            array('qrcode_num','扫码数'),
            array('all_box_nums','版位数量'),
            array('hd_box_nums','互动版位'),
            array('hotel_box_type','酒楼机顶盒类型'),
            array('maintainer_name','维护人'),

        );
        $xlsName = '小程序酒楼评级';
        $filename = 'smallapp_hotel_level';
        $this->exportExcel($xlsName, $xlsCell, $datas,$filename);
    }
    public function sappstaticdetail(){
        ini_set("memory_limit","1024M");
        $column_name = array(array('name'=>'id'),array('name'=>'区域id'),array('name'=>'区域名称'),
            array('name'=>'酒楼id'),array('name'=>'酒楼名称'),array('name'=>'盒子类型'),
            array('name'=>'合作维护人id'),array('name'=>'运维负责人'),array('name'=>'包间id'),
            array('name'=>'包间名称'),array('name'=>'机顶盒id'),array('name'=>'机顶盒mac'),
            array('name'=>'包间类型'),array('name'=>'是否为广告机'),array('name'=>'故障次数'),
            array('name'=>'日志上传次数'),array('name'=>'饭点心跳次数'),array('name'=>'心跳次数'),
            array('name'=>'平均下载速度(kb/s)'),array('name'=>'最大下载速度(kb/s)'),array('name'=>'最小下载速度(kb/s)'),
            array('name'=>'扫小码次数'),array('name'=>'扫大码次数'),array('name'=>'扫呼码次数'),
            array('name'=>'呼码次数'),array('name'=>'投照片次数'),array('name'=>'切换图片投屏次数'),
            array('name'=>'投视频次数'),array('name'=>'发现页投照片次数'),array('name'=>'发现页投视频次数'),
            array('name'=>'首页点播次数'),array('name'=>'互动游戏次数'),array('name'=>'点播生日歌次数'),
            array('name'=>'重投次数'),array('name'=>'点播总次数'),array('name'=>'点播成功次数'),
            array('name'=>'投屏总次数'),array('name'=>'投屏成功次数'),array('name'=>'总互动次数'),
            array('name'=>'饭局'),array('name'=>'创建日期'),array('name'=>'统计日期')
        );
        
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $fields_ids = I('fields_ids');
        
        //字段列表
        $table_name = 'savor_smallapp_statistics';
        $columns_arr = M()->query("select COLUMN_NAME from information_schema.COLUMNS where table_name ='".$table_name."'");
        
        $fields = '';
        if(empty($fields_ids)){
            $fields = "a.*,user.remark as maintainer";
            foreach($column_name as $key=>$v){
                $view_column[] = $v['name'];
                $fields_id_arr[] = $key;
            }
        }else {
            $fields_id_arr = explode(',', $fields_ids);
            
            foreach($fields_id_arr as $v){
                $fields .=$space.'a.'.$columns_arr[$v]['column_name'];
                $space = ',';
                $view_column[] = $column_name[$v]['name'];
            }
        }
        
        if(in_array(6, $fields_id_arr)){
            $view_column[] = '维护人';
        
            $fields .=',user.remark as maintainer';
           
        }
        
        $sql ="select $fields from savor_smallapp_statistics a 
               left join savor_sysuser user on  a.maintainer_id=user.id
               where a.static_date>=".$start_date." and a.static_date<=".$end_date;
        
        $list = M()->query($sql);
        foreach($list as $key=>$v){
            if(!empty($v['hotel_box_type'])){
                switch($v['hotel_box_type']){
                    case 2:
                        $list[$key]['hotel_box_type'] = '二代网络';
                        break;
                    case 3:
                        $list[$key]['hotel_box_type'] = '二代5G';
                        break;
                    case 6:
                    $list[$key]['hotel_box_type'] = '三代网络';
                    break;
                }
            }
            if(!empty($v['box_type'])){
                switch ($v['box_type']){
                    case 1:
                        $list[$key]['box_type'] = '包间';
                        break;
                    case 2:
                        $list[$key]['box_type'] = '大厅';
                        break;
                    case 3:
                        $list[$key]['box_type'] = '等候区';
                        break;
                }
            }
        }
        
        foreach($view_column as $k=> $v){
            if($v=='维护人'){
                $xlsCell[] = array('maintainer',$v);
            }else {
                $xlsCell[] = array($columns_arr[$fields_id_arr[$k]]['column_name'],$v);
            }
            
        }
        $xlsName = '小程序酒楼评级数据详情';
        $filename = 'smallapp_hotel_level_detail';
        $this->exportExcel($xlsName, $xlsCell, $list,$filename);
        
    }
    /**
     * @desc 获取45天之内有更新的酒楼宣传片
     */
    public function getUpdateHotelAdv(){
        $time_start = date('Y-m-d 00:00:00',strtotime('-45 days'));
        $sql ="SELECT  hotel.id hotel_id,hotel.name hotel_name,ads.name media_name,
               concat('http://oss.littlehotspot.com/',media.`oss_addr`) oss_addr,
               ads.create_time,ads.update_time 
                FROM `savor_ads` ads
               left join savor_media media on ads.media_id=media.id
               left join savor_hotel hotel on ads.hotel_id=hotel.id
                
               WHERE ads.type=3 and hotel.flag=0 and hotel.state=1 and ads.update_time>'".$time_start."' order by hotel.id asc";
        $data  =M()->query($sql);
        $xlsName = '45天之内有更新的酒楼宣传片';
        $filename = 'hotel_update_adv_list';
        
        $xlsCell = array(
        
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('media_name','资源名称'),
            array('oss_addr','下载地址'),
            array('create_time','资源创建时间'),
            array('update_time','资源更新时间'),
        
        );
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function qgboxdy(){
        $start_time = '2019-02-01 00:00:00';
        $end_time   = '2019-02-17 23:59:59';
        $hotel_box_types = getHeartBoXtypeIds(2);
        $sql = "select box.mac box_mac,box.id box_id,area.region_name,hotel.name hotel_name,hotel.addr,
                room.type room_type,box.name box_name 
                from savor_box box 
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id = hotel.id
                left join savor_area_info area on hotel.area_id = area.id
                where box.state = 1 and box.flag=0 and hotel.state = 1 and hotel.flag = 0
                and hotel.hotel_box_type in($hotel_box_types)";
        $data = M()->query($sql);
        $flag =0;
        foreach($data as $key=>$v){
            $nums_24 =0;
            $nums_1_8=0;
            $sql ="select box_id from savor_heart_all_log where  date=20190216 and mac='".$v['box_mac']."' and type=2
                   and hour0>=1 and hour1>=1 and hour2>=1 and hour3>=1 and hour4>=1 
                   and hour5>=1 and hour6>=1 and hour7>=1 and hour8>=1 and hour9>=1 and hour10>=1 
                   and hour11>=1 and hour12>=1 and hour13>=1 and hour14>=1 and hour15>=1 
                   and hour16>=1 and hour17>=1 and hour18>=1 and hour19>=1 and hour20>=1 
                   and hour21>=1 and hour22>=1 and hour23>=1 ";
            
            $rt = M()->query($sql);
            if(!empty($rt)){
                $nums_24 = 1;
            }else {
                $sql ="select box_id from savor_heart_all_log where  date=20190217 and mac='".$v['box_mac']."' and type=2
                   and hour0>=1 and hour1>=1 and hour2>=1 and hour3>=1 and hour4>=1 
                   and hour5>=1 and hour6>=1 and hour7>=1 and hour8>=1 and hour9>=1 and hour10>=1 
                   and hour11>=1 and hour12>=1 and hour13>=1 and hour14>=1 and hour15>=1 
                   and hour16>=1 and hour17>=1 and hour18>=1 and hour19>=1 and hour20>=1 
                   and hour21>=1 and hour22>=1 and hour23>=1 ";
                $rt = M()->query($sql);
                if(!empty($rt)){
                    $nums_24 = 1;
                }
            }
            
            $sql =" select box_id from savor_heart_all_log where date=20190216 and mac='".$v['box_mac']."' and type=2
                    and hour1>=1 and hour2>=1 and hour3>=1 and hour4>=1 and hour5>=1  
                    and hour6>=1 and hour7>=1
                    ";
            $rt = M()->query($sql);
            if(!empty($rt)){
                $nums_1_8 = 1;
            }else {
                $sql =" select box_id from savor_heart_all_log where date=20190217 and mac='".$v['box_mac']."' and type=2
                        and hour1>=1 and hour2>=1 and hour3>=1 and hour4>=1 and hour5>=1  
                        and hour6>=1 and hour7>=1
                        ";
                $rt = M()->query($sql);
                if(!empty($rt)){
                    $nums_1_8 = 1;
                }
            }
            
            if(empty($nums_24) && empty($nums_1_8)){
                unset($data[$key]);
            }else {
                if($v['room_type']==1){
                    $v['room_type'] ='包间';
                }else if($v['room_type']==2){
                    $v['room_type'] ='大厅';
                }else if($v['room_type']==3){
                    $v['room_type'] ='等候区';
                }
                
                $rts[$flag] = $v;
                $rts[$flag]['nums_24'] = $nums_24;
                $rts[$flag]['nums_1_8']= $nums_1_8;
                $flag ++;
            }
                
            
        }
        //print_r($rts);exit;
        $xlsName = '全国异常电源机顶盒情况';
        $filename = 'box_err_elec';
        
        $xlsCell = array(
        
            array('region_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('room_type','包间类型'),
            array('box_name','机顶盒名称'),
            array('box_mac','机顶盒mac'),
            array('nums_24','24小时开机'),
            array('nums_1_8','夜间开机'),
        );
        $this->exportExcel($xlsName, $xlsCell, $rts,$filename);
        
    }
    //所有网络版位小程序相关
    public function exportNetBox(){
        $box_type_arr = C('hotel_box_type');
        $month_days = date('t',strtotime('2019-01'));
        $page = I('get.page');
        $start = ($page - 1) * 30; 
        //echo $start;exit;
        $hotel_box_types = getHeartBoXtypeIds(2);
        $sql ="select area.region_name,hotel.id hotel_id,hotel.name hotel_name,box.name box_name,
               ext.avg_expense, mac box_mac,box.is_4g,box.box_type from savor_box box
               left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id
               left join savor_area_info area on hotel.area_id=area.id
               left join savor_hotel_ext ext on ext.hotel_id=hotel.id
               where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
               and hotel.hotel_box_type in($hotel_box_types)  order by box.id asc limit $start, 30";
        
        $result = M()->query($sql);
        $start_time = '2019-02-01 00:00:00';
        $end_time   = '2019-02-28 23:59:59';
        
        $start_date = '2019-02-01';
        $end_date   = '2019-02-28';
        
        $heart_start_date = '20190201';
        $heart_end_date   = '20190228';
        $dinner_arr = array(18,19,20);   //晚饭时间
        $lunch_arr  = array(12,13);       //午饭
        foreach($result as $key=>$v){
            $result[$key]['box_type'] = $box_type_arr[$v['box_type']];
            $result[$key]['is_4g']    = $v['is_4g']== 0 ? '否' : '是';
            //独立用户数
            $sql ="select id from savor_smallapp_qrcode_log where box_mac='".$v['box_mac']."' 
                   and  create_time>='".$start_time."' and create_time<='".$end_time."' group by openid";
            
            $rt = M()->query($sql);
            $result[$key]['user_nums'] = count($rt);
            
            //投图次数
            $sql ="select count(id) as nums from savor_smallapp_forscreen_record
                   where  box_mac='".$v['box_mac']."'  
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and (action=4 or (action=2 and resource_type=1) or(action=8 and resource_type=1))";
            
            $rt = M()->query($sql);
            $result[$key]['pics'] = $rt[0]['nums'];
            //投视频
            $sql ="select count(id) as nums from savor_smallapp_forscreen_record
                   where  box_mac='".$v['box_mac']."'  
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and mobile_brand!='devtools' and ( (action=2 and resource_type=2) or(action=8 and resource_type=2))";
            $rt = M()->query($sql);
            $result[$key]['videos'] = $rt[0]['nums'];
            
            //点播次数
            $sql ="select count(id) as nums from savor_smallapp_forscreen_record
                   where  box_mac='".$v['box_mac']."'   
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and action=5 and forscreen_char!='Happy Birthday'";
            $rt = M()->query($sql);
            $result[$key]['demand'] = $rt[0]['nums'];
            
            //生日歌点播
            $sql ="select count(id) as nums from savor_smallapp_forscreen_record
                   where    box_mac='".$v['box_mac']."'
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and  action=5 and forscreen_char='Happy Birthday'";
            $rt = M()->query($sql);
            $result[$key]['happy']  = $rt[0]['nums'];
            
            //发现内容点播
            $sql ="select count(id) as nums from savor_smallapp_forscreen_record
                   where  box_mac='".$v['box_mac']."'
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and ( action=11 or action=12) ";
            $rt = M()->query($sql);
            $result[$key]['find'] = $rt[0]['nums'];
             
            //游戏次数
            $sql ="select count(id) as nums from savor_smallapp_game_interact
                   where box_mac='".$v['box_mac']."' 
                   and create_time>='".$start_time."' and create_time<='".$end_time."' and is_start=1";
            $rt = M()->query($sql);
            $result[$key]['games'] = $rt[0]['nums'];
            
            //互动总次数
            $result[$key]['all_hd_nums'] = $result[$key]['pics']+ $result[$key]['videos'] + $result[$key]['demand'] + $result[$key]['happy'] + $result[$key]['find'] + $result[$key]['games'];
            
            //扫码总数
            /* $sql ="select count(id) as nums from savor_smallapp_qrcode_log where and  box_mac='".$v['box_mac']."' and type!=6
                   and  create_time>='".$start_time."' and create_time<='".$end_time."'";
            $rt = M()->query($sql);
            $result[$key]['qrcode'] = $rt[0]['nums']; */
            
            //轮播大码
            $sql ="select count(id) as nums from savor_smallapp_qrcode_log where   box_mac='".$v['box_mac']."' 
                   and  create_time>='".$start_time."' and create_time<='".$end_time."' and type in(2,5)";
            $rt = M()->query($sql);
            $result[$key]['lunbo'] = $rt[0]['nums'];
            //呼出大码
            $sql ="select count(id) as nums from savor_smallapp_qrcode_log where  box_mac='".$v['box_mac']."' 
                   and  create_time>='".$start_time."' and create_time<='".$end_time."' and type =3";
            $rt = M()->query($sql);
            $result[$key]['huma'] = $rt[0]['nums'];
            //小码
            $sql ="select count(id) as nums from savor_smallapp_qrcode_log where   box_mac='".$v['box_mac']."' 
                   and  create_time>='".$start_time."' and create_time<='".$end_time."' and type in(1,6)";
            $rt = M()->query($sql);
            $result[$key]['xiaoma'] = $rt[0]['nums'];
            
            
            
            //扫码总数
            $result[$key]['qrcode'] = $result[$key]['lunbo'] + $result[$key]['huma'] + $result[$key]['xiaoma'];
            
            
            //网络高峰
            $sql ="select max(max_down_speed) as max_down_speed from savor_smallapp_static_net 
                   where hotel_id=".$v['hotel_id']." and static_date>='".$start_date."' 
                   and static_date<='".$end_date."'";
            $rt = M()->query($sql);
            if($rt[0]['max_down_speed']>0) $result[$key]['max_down_speed'] = round($rt[0]['max_down_speed']/1048576,2);
            else $result[$key]['max_down_speed'] = '';
            //网络低峰
            $sql ="select min(min_down_speed) as min_down_speed from savor_smallapp_static_net
                   where hotel_id=".$v['hotel_id']." and static_date>='".$start_date."'
                   and static_date<='".$end_date."' and min_down_speed>0";
            $rt = M()->query($sql);
            if($rt[0]['min_down_speed']>0) $result[$key]['min_down_speed'] = round($rt[0]['min_down_speed']/1048576,2);
            else $result[$key]['min_down_speed'] = '';
            
            //网络均值
            $sql ="select sum(avg_down_speed) as avg_down_speed from savor_smallapp_static_net
                   where hotel_id=".$v['hotel_id']." and static_date>='".$start_date."'
                   and static_date<='".$end_date."'";
            $net_all = M()->query($sql);
            
            $sql ="select count(id) as net_nums from savor_smallapp_static_net
                   where hotel_id=".$v['hotel_id']." and static_date>='".$start_date."'
                   and static_date<='".$end_date."'";
            $net_nums = M()->query($sql);
            if(!empty($net_nums)){
                $result[$key]['avg_down_speed'] = round(($net_all[0]['avg_down_speed'] / $net_nums[0]['net_nums'])/1048576,2 ,2); 
            }else {
                $result[$key]['avg_down_speed'] = '';
                
            }
            //晚饭在线率
            $all_log_time = 0;
            
            foreach($dinner_arr as $vv){
                $sql ="select sum(hour".$vv.") as hours from savor_heart_all_log where
                       date>=".$heart_start_date." and date<=".$heart_end_date." and mac='".$v['box_mac']."' and type=2";
                $rt = M()->query($sql);
                if(!empty($rt)){
                    $all_log_time +=$rt[0]['hours'];
                    
                }
            }
            /* $sql ="select count(id) as nums from savor_heart_all_log where 
                     date>=".$heart_start_date." and date<=".$heart_end_date." and mac='".$v['box_mac']."'";
            $rt = M()->query($sql);
            $log_days = $rt[0]['nums']; */
            if($all_log_time>0){
                $result[$key]['dinner_online_rate'] = sprintf("%.4f",($all_log_time*5) / ($month_days*180)) ;
            }else {
                $result[$key]['dinner_online_rate'] = 0 ;
            }
            
            //午饭在线率
            $all_log_time = 0;
            
            foreach($lunch_arr as $vv){
                $sql ="select sum(hour".$vv.") as hours from savor_heart_all_log where
                       date>=".$heart_start_date." and date<=".$heart_end_date." and mac='".$v['box_mac']."' and type=2";
                
                $rt = M()->query($sql);
                if(!empty($rt)){
                    $all_log_time +=$rt[0]['hours'];
            
                }
            }
           
            /* $sql ="select count(id) as nums from savor_heart_all_log where
                     date>=".$heart_start_date." and date<=".$heart_end_date." and mac='".$v['box_mac']."'";
            $rt = M()->query($sql);
            $log_days = $rt[0]['nums']; */
            if($all_log_time>0){
                $result[$key]['lunch_online_rate'] = sprintf("%.4f",($all_log_time*5) / ($month_days*120)) ;
                
            }else {
                $result[$key]['lunch_online_rate'] = 0 ;
            }
            
            
        }
        $xlsName = '全国网络机顶盒互动数据统计';
        $filename = 'exportNetBox';
        
        $xlsCell = array(
        
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','mac'),
            array('user_nums','独立用户'),
            array('all_hd_nums','互动总数'),
            array('pics','图片投屏'),
            array('videos','视频投屏'),
            array('demand','视频点播'),
            array('happy','生日歌点播'),
            array('find','发现内容点播'),
            array('games','游戏次数'),
            array('qrcode','扫码总数'),
            array('lunbo','轮播大码次数'),
            array('huma','呼出大码'),
            array('xiaoma','小码次数'),
            array('lunch_online_rate','午市开机率'),
            array('dinner_online_rate','晚市开机率'),
            array('box_type','设备类型'),
            array('is_4g','是否4G'),
            array('avg_down_speed','网络均值(M)'),
            array('max_down_speed','网络高峰(M)'),
            array('min_down_speed','网络低峰(M)'),
            
            array('avg_expense','人均消费'),
        );
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
    }
    public function exportSlBoxList(){
        $hotel_box_types = getHeartBoXtypeIds(2);
		$sql ="select area.region_name,hotel.name hotel_name ,hotel.id hotel_id,user.remark from savor_hotel hotel
			   left join savor_area_info area on hotel.area_id=area.id
			   left join savor_hotel_ext ext on hotel.id = ext.hotel_id
			   left join savor_sysuser user on user.id= ext.maintainer_id
			   where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 and hotel.flag=0 ";
		$hotel_list = M()->query($sql);
		$sl_date = date('Y-m-d H:i:s',strtotime('-7 days')) ;
		foreach($hotel_list  as $key=>$v){
			$sql ="select box.mac box_mac from  savor_box box
                               left join savor_room room on box.room_id=room.id
                               left join savor_hotel hotel on room.hotel_id=hotel.id
                               where hotel.id=".$v['hotel_id']." and box.state=1 and box.flag=0";
                        $box_list = M()->query($sql);
						$zx_box_nums = 0;
                        foreach($box_list as $kk=>$vv ){
							$sql ="select box_mac from savor_heart_log where box_mac='".$vv['box_mac']."' and type=2 and last_heart_time>'".$sl_date."'";
							$tt = M()->query($sql);
							if(!empty($tt)){
								$zx_box_nums++;
							}
                        }
						$all_box_nums = count($box_list);
						$hotel_list[$key]['all_box_nums'] = $all_box_nums;
						$hotel_list[$key]['sl_box_nums']  = $all_box_nums - $zx_box_nums;
		
		}
		$xlsName = '全国网络机顶盒互动数据统计';
        $filename = 'exportSlBoxList';
        
        $xlsCell = array(
        
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('remark','维护人'),
            array('all_box_nums','酒楼版位数'),
            array('sl_box_nums','失联7天上版位数'),
            
        );
        $this->exportExcel($xlsName, $xlsCell, $hotel_list,$filename);
    }
    public function tolicong(){
        $hotel_box_types = getHeartBoXtypeIds(2);
        $sql = "select area.region_name,hotel.name hotel_name ,box.name box_name,user.remark,
                box.mac box_mac from savor_box box
                left join savor_room room on box.room_id= room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                left join savor_area_info area on hotel.area_id= area.id
                left join savor_hotel_ext ext on hotel.id = ext.hotel_id
			    left join savor_sysuser user on user.id= ext.maintainer_id
                where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and hotel.area_id in(246)";
        
        $box_list = M()->query($sql);
        $start_time = '20201221';
        $end_time   = '20201230';
        
        
        $data = [];
        foreach($box_list  as $key=>$v){
            $sql = "select count(id) as num from savor_heart_all_log where mac='".$v['box_mac']."' and type=2 and  date>='".$start_time."' and date<='".$end_time."'";
            //echo $sql;exit;
            $rt = M()->query($sql);
            $num = $rt[0]['num'];
            if(empty($num)){
                $data[] = $v;
            }
            
            
        }
        $xlsName = '深圳网络机顶盒连续10天失联';
        $filename = 'exportSl14BoxList';
        
        $xlsCell = array(
        
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('remark','维护人'),
            array('box_name','版位名称'),
            array('box_mac','机顶盒编号')
        
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202012/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'深圳失联10天.xls';
      
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
       
    }
    public function tolicongLx(){
        $hotel_box_types = getHeartBoXtypeIds(2);
        $sql = "select area.region_name,hotel.name hotel_name ,box.name box_name,user.remark,
                box.mac box_mac from savor_box box
                left join savor_room room on box.room_id= room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                left join savor_area_info area on hotel.area_id= area.id
                left join savor_hotel_ext ext on hotel.id = ext.hotel_id
			    left join savor_sysuser user on user.id= ext.maintainer_id
                where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 ";
        //print_r($sql);exit;
        $box_list = M()->query($sql);
        $date_arr = array(
            array('start_date'=>20200601,'end_date'=>20200614),
            array('start_date'=>20200602,'end_date'=>20200615),
            array('start_date'=>20200603,'end_date'=>20200616),
            array('start_date'=>20200604,'end_date'=>20200617),
            array('start_date'=>20200605,'end_date'=>20200618),
            array('start_date'=>20200606,'end_date'=>20200619),
            array('start_date'=>20200607,'end_date'=>20200620),
            array('start_date'=>20200608,'end_date'=>20200621),
            array('start_date'=>20200609,'end_date'=>20200622),
            array('start_date'=>20200610,'end_date'=>20200623),
            array('start_date'=>20200611,'end_date'=>20200624),
            array('start_date'=>20200612,'end_date'=>20200625),
            array('start_date'=>20200613,'end_date'=>20200626),
            array('start_date'=>20200614,'end_date'=>20200627),
            array('start_date'=>20200615,'end_date'=>20200628),
            array('start_date'=>20200616,'end_date'=>20200629),
            array('start_date'=>20200617,'end_date'=>20200630),
            
        );
        $data = [];
        foreach($box_list as $key=>$v){
            $sl_date_str = '';
            $space = '';
            foreach($date_arr as $kk=>$vv){
                $start_time = $vv['start_date'];
                $end_time   = $vv['end_date'];
                $sql = "select count(id) as num from savor_heart_all_log where mac='".$v['box_mac']."' and type=2 and  date>='".$start_time."' and date<='".$end_time."'";
                $rt = M()->query($sql);
                $num = $rt[0]['num'];
                if(empty($num)){
                    
                    $sl_date_str .= $space . $start_time.'-'.$end_time;
                    $space = ',';
                    break;
                }
                
            }
            if($sl_date_str!=''){
               $v['sl_data_str'] = $sl_date_str; 
               $data [] = $v;
            }
            
        }
        $xlsName = '失联有14天全国网络机顶盒';
        $filename = 'haveSl7BoxList';
        
        $xlsCell = array(
        
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('remark','维护人'),
            array('box_name','版位名称'),
            array('box_mac','机顶盒编号'),
            array('sl_data_str','失联时段')
        
        );
        $xlsName = '失联超过7天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202006/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'失联14天.xls';
        //echo $path;exit;
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
        
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function exportEmptyWifiBoxList(){
        $sql ="select hotel.id,area.region_name ,hotel.name hotel_name ,hotel.addr,
               room.name room_name,box.name box_name,box.mac,
               case 
               when hotel.hotel_box_type=3 then '二代5G'
               when hotel.hotel_box_type=6 then '三代网络'
               end
               as hotel_box_type,user.remark, box.wifi_name,box.wifi_mac,box.wifi_password
               from savor_box box
               left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id
               left join savor_hotel_ext ext on hotel.id=ext.hotel_id
               left join savor_sysuser user  on ext.maintainer_id = user.id
               left join savor_area_info area on hotel.area_id=area.id
               where hotel.hotel_box_type in(3,6) and hotel.flag=0 and hotel.state=1 
               and box.flag=0 and box.state=1 and wifi_mac !='' and hotel.id!=7 limit 4000,1000";
        $data = M()->query($sql);
        
        $xlsName = '漏填wifi_mac版位详情';
        $filename = 'exportEmptyWifiBoxList';
        
        $xlsCell = array(
            array('id','酒楼id'),
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('room_name','包间名称'),
            array('box_name','机顶盒名称'),
            array('mac','机顶盒mac'),
            array('remark','维护人'),
            
            array('hotel_box_type','酒楼机顶盒类型'),
            array('wifi_name','wifi名称'),
            array('wifi_mac','内网mac'),
            array('wifi_password','wifi密码'),
        
        );
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
        
    }
    /**
     * @desc 1、起始时间为每月最后一天（含），北京地区向前连续30天无日志上传的版位明细，
     *         上海、广州向前连续30天无日志上传的版位明细
     */
    public function noLogBoxListInfo(){
        /* $now_day =  date('j');
        $all_day =  date('t');
        $is_last_day = is_numeric(I('is_last_day')) ? I('is_last_day') : 1;
        if($now_day !=$all_day && !empty($is_last_day)){
            exit('不是本月最后一天');  //上线打开
        } */
        
        $end_date = I('end_date');
        if(empty($end_date)) exit('结束日期不能为空');
        $hotel_box_types = getHeartBoXtypeIds(2);
        //获取北京地区网络版机顶盒
        $sql ="select box.mac from savor_box box
               left join savor_room room   on box.room_id=room.id
               left join savor_hotel hotel on hotel.id=room.hotel_id
               left join savor_area_info area on hotel.area_id = area.id
               where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 
               and hotel.flag=0 and box.state=1 and box.flag=0 and hotel.area_id=1";
        $box_list = M()->query($sql);
        //获取北京地区连续30天没日志上传
        $box_log = new \Admin\Model\Oss\BoxLogModel();
        $end_date = $end_date .' 23:59:59';
        $tmp_str = (strtotime($end_date)) - 30*86400;
        $start_date = date('Y-m-d H:i:s',$tmp_str); 
        $fields = 'box_mac';
        $data = array();
        foreach($box_list as $key=>$v){
            $where = array();
            $where['box_mac'] = $v['mac'];
            $where['create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
            
            
            $rt = $box_log->getInfo($fields, $where);
            if(empty($rt)){
                $data[] = $v;
            }
        }
        
        //获取上海、广州网络版机顶盒
        $sql ="select box.mac from savor_box box
               left join savor_room room   on box.room_id=room.id
               left join savor_hotel hotel on hotel.id=room.hotel_id
               left join savor_area_info area on hotel.area_id = area.id
               where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1
               and hotel.flag=0 and box.state=1 and box.flag=0 and hotel.area_id in(9,236)";
        $box_list = M()->query($sql);
        
        $fields = 'box_mac';
        //获取上海、广州连续30天没日志上传
        foreach($box_list as $key=>$v){
            $where = array();
            $where['box_mac'] = $v['mac'];
            $where['create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
        
        
            $rt = $box_log->getInfo($fields, $where);
            if(empty($rt)){
                $data[] = $v;
            }
        }
        
        $xlsName = '北、上、广未上传日志版位详情';
        $filename = 'noLogBoxListInfo';
        
        $xlsCell = array(
            
            array('mac','机顶盒mac'),
            
        
        );
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function forscreenTimes(){
        $sql ="SELECT a.box_res_edown_time,a.res_sup_time FROM savor_smallapp_forscreen_record a 
               LEFT JOIN savor_box box ON a.box_mac=box.mac 
               LEFT JOIN savor_room room ON room.id= box.room_id 
               LEFT JOIN savor_hotel hotel ON room.hotel_id=hotel.id 
               LEFT JOIN savor_area_info AREA ON hotel.area_id=area.id 
               LEFT JOIN savor_smallapp_user USER ON a.openid=user.openid 
               WHERE box.flag = 0 AND box.state = 1 AND a.mobile_brand <> 'devtools' 
               AND a.is_valid = 1 AND ( a.create_time >= '2019-06-07 00:00:00' AND a.create_time <= '2019-06-14 23:59:59' ) 
               AND a.is_exist = 0 AND a.small_app_id = 1 AND a.box_res_edown_time>0 AND A.res_sup_time>0
               AND A.box_res_edown_time>A.res_sup_time
               ORDER BY a.id DESC ";
        $data = M()->query($sql);
        $rts = array();
        foreach($data as $key=>$v){
            $diff = $v['box_res_edown_time'] - $v['res_sup_time'];
            $diff = $diff/1000;
            $rts[$key]['diff'] = sprintf('%.2f',$diff);
        }
        var_export($rts);exit;
        
        //var_dump($rts);exit;
        $xlsName = '6月7日-6月14投屏时长分布';
        $filename = 'forscreenTimes';
        
        $xlsCell = array(
        
            array('diff','总时长'),
        
        
        );
        $this->exportExcel($xlsName, $xlsCell, $rts,$filename);
    }
    //易售媒体广告模板
    public function exportDd(){
        $hotel_box_types = getHeartBoXtypeIds(2);
        $city_id = $_GET['city_id']  ? $_GET['city_id'] :1;
        $sql ="select box.mac box_mac, box.name box_name,tv.tv_size,city.region_name city_name,area.region_name area_name,
               hotel.addr,hotel.gps,hotel.name hotel_name from savor_tv tv
               left join savor_box box on tv.box_id=box.id
               left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id
               left join savor_area_info city on hotel.area_id= city.id
               left join savor_area_info area on hotel.county_id=area.id
               where hotel.area_id=$city_id and  hotel.state=1 and hotel.flag = 0 and box.state=1 and box.flag=0
               and tv.state=1 and tv.flag=0 and hotel.hotel_box_type in($hotel_box_types)";
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            $gps_arr = explode(',',$v['gps']);
            $data[$key]['log'] = $gps_arr[0];
            $data[$key]['lat'] = $gps_arr[1];
            switch ($city_id){
                case '1':
                    $data[$key]['pro_name'] = '北京';
                    break;
                case '9':
                    $data[$key]['pro_name'] = '上海';
                    break;
                case '236':
                    $data[$key]['pro_name'] = '广州';
                    break;
                case '246':
                    $data[$key]['pro_name'] = '广州';
                    break;
            }
        }
        $xlsName = '易售媒体广告模板';
        $filename = 'easySellAds';
        
        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('box_mac','机顶盒mac'),
            array('box_name','机顶盒名称'),
            array('tv_size','电视尺寸'),
            array('pro_name','省份'),
            array('city_name','城市'),
            array('area_name','区县'),
            array('addr','地址'),
            array('log','经度'),
            array('lat','纬度'),
        );
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function sdBwDataToLc(){
        $start_time = I('get.start_time','','trim');
        $end_time   = I('get.end_time','','trim');
        //$start_time = '2019-11-01 18:00:00';
        //$end_time   = '2019-11-01 22:00:00';
        if(empty($start_time) || empty($end_time)){
            exit('请填写开始和结束时间');
        }
        $area_id = I('get.area_id',0,'intval');
        if(empty($area_id)){
            exit('请填写区域id');
        }
        $page  = I('get.page',0,'intval');
        $pagesize = I('get.pagesize',1000,'intval');
        if($page){
            $offset = ($page-1)*$pagesize;
            
            $limit = 'limit '. $offset.','.$pagesize;
        }else {
            $limit = '';
        }
        $heart_date = date('Ymd',strtotime($start_time));
        $hotel_box_types = getHeartBoXtypeIds(2);
        /* echo $start_time."<br>";
        echo $end_time.'<br>';
        echo $heart_date;exit; */
        $sql ="select box.id box_id,box.mac box_mac, area.region_name,hotel.id hotel_id,hotel.name hotel_name,room.name room_name    from savor_box box 
               left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on hotel.id=room.hotel_id
               left join savor_area_info area on hotel.area_id= area.id
               where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
               and hotel.hotel_box_type in($hotel_box_types) and hotel.id and area.id=".$area_id." $limit";
        //echo $sql ;exit;
        $data = M()->query($sql);
        $redis = SavorRedis::getInstance();
        $redis->select(14);
        foreach($data as $key=>$v){
            //是否开机
            $sql ="select (hour18+hour19+hour20+hour21+hour22) as nums from savor_heart_all_log where box_id=".$v['box_id']." and date='".$heart_date."'";
            $h_rt = M()->query($sql);
            if($h_rt[0]['nums']>=12){
                $data[$key]['is_kj'] = '是';
            }else {
                $data[$key]['is_kj'] = '否';
            }
            $sql ="select count(id) as nums from `savor_smallapp_forscreen_record` where box_mac='".$v['box_mac']."' and create_time>'".$start_time."' and create_time<'".$end_time."' and action=40";
            
            $d_rt = M()->query($sql);
            
            if($d_rt[0]['nums']>0){
                $data[$key]['is_db'] = '是';
            }else {
                $data[$key]['is_db'] = '否';
            }
            //是否轮播商品广告
            $l_rt = $redis->get('smallappsale:activitygoods:loopplay:'.$v['hotel_id']);
            if(empty($l_rt)){
                $data[$key]['is_lb'] = '否';
            }else {
                $l_rt = json_decode($l_rt,true);
                if(in_array(127,$l_rt)) $data[$key]['is_lb'] = '是';
                else $data[$key]['is_lb'] = '否';
            }
        }
        $xlsName = '四地版位数据统计';
        $filename = 'fourBwtoLc';
        
        $xlsCell = array(
            array('region_name','地区'),
            
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            
            array('is_kj','是否开机'),
            array('is_db','是否点播商品广告'),
            array('is_lb','是否轮播商品广告'),
            
        );
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    /**
     * @desc 销售端使用情况
     * @author liubin
     * @since  2019-11-01
     */
    public function exportSaleTask(){
        $ctime = I('get.ctime');
        $etime = I('get.etime');
        $ctime = !empty($ctime) ? date('Y-m-d',strtotime($ctime)).' 00:00:00' : '';
        $etime = !empty($etime) ? date('Y-m-d',strtotime($etime)).' 23:59:59' : '';
        if($ctime>$etime){
            echo 'ctime > etime error';
            exit;
        }
        $model = M();

        //累计绑定酒楼
        $sql_saletotal_hotel = "select area_id,count(id) as num from savor_hotel where id in 
(select hotel_id from savor_integral_merchant where type=2 and status=1) and state=1 and flag=0 group by area_id";
        $res_sale_hoteltotal = $model->query($sql_saletotal_hotel);
        $sale_hotel_nums = array();
        foreach ($res_sale_hoteltotal as $v){
            $sale_hotel_nums[$v['area_id']] = $v['num'];
        }

        //新增绑定酒楼
        $sql_newsale_hotel = "select hotel_id from savor_integral_merchant where add_time>='$ctime' and add_time<='$etime' and type=2 and status=1";
        $res_newsale_hotel = $model->query($sql_newsale_hotel);
        $newsale_hotels = array();
        foreach ($res_newsale_hotel as $v){
            $newsale_hotels[]=$v['hotel_id'];
        }

        $new_bind_hotels = array();
        if(!empty($newsale_hotels)){
            $hotel_ids = join(',',$newsale_hotels);
            $sql_saletotal_hotel_last = "select area_id,count(id) as num from savor_hotel where id in ($hotel_ids) and state=1 and flag=0 group by area_id";
            $res_sale_hoteltotal = $model->query($sql_saletotal_hotel_last);
            foreach ($res_sale_hoteltotal as $v){
                $new_bind_hotels[$v['area_id']] = $v['num'];
            }
        }

        //活跃酒楼数
        $cdate = date('Y-m-d',strtotime($ctime));
        $edate = date('Y-m-d',strtotime($etime));
        $m_box = new \Admin\Model\BoxModel();
        $hotel_num_gt3 = array();
        $hotel_num_let3 = array();

        $sql_bindhotel = "select merchant.id as merchant_id,merchant.hotel_id from savor_integral_merchant as merchant left join savor_hotel as hotel on merchant.hotel_id=hotel.id 
where merchant.type=2 and merchant.status=1 and hotel.state=1 and hotel.flag=0 ";
        $res_hotels = $model->query($sql_bindhotel);

        foreach ($res_hotels as $v){
            $hotel_id = $v['hotel_id'];
            $log = "hotel_id|$hotel_id";

            $where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
            $hotel_boxs = $m_box->getBoxByCondition('box.mac',$where,'');
            $boxs = array();
            foreach ($hotel_boxs as $bv){
                $box = $bv['mac'];
                $boxs[] = "'$box'";
            }
            $hotel_boxs_str = join(',',$boxs);

            //登录绑定
            $sql_bind = "select staff.add_time from savor_integral_merchant_staff as staff left join savor_smallapp_user as u on staff.openid=u.openid where staff.merchant_id={$v['merchant_id']} and staff.add_time>='$ctime' and staff.add_time<='$etime' and u.small_app_id=5 order by staff.id desc";
            $res_bind = $model->query($sql_bind);
            $hotel_bind = array();
            foreach ($res_bind as $bindv){
                $bind_date = date('Y-m-d',strtotime($bindv['add_time']));
                $hotel_bind[$bind_date] = 1;
            }
            $log.="|sql_bind|".$sql_bind;


            //签到
            $sql_signin = "select DATE(add_time) add_date,COUNT(DISTINCT openid) as num from savor_smallapp_user_signin where DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and box_mac in($hotel_boxs_str) GROUP BY add_date";
            $hotel_sign = array();
            if(!empty($boxs)){
                $res_signin = $model->query($sql_signin);
                foreach ($res_signin as $v){
                    $hotel_sign[$v['add_date']] = $v['num'];
                }
            }
            $log.="|sql_signin|".$sql_signin;

            //上传我的商品，添加活动商品
            $sql_goods = "select DATE(add_time) add_date,COUNT(DISTINCT openid) as num from savor_smallapp_hotelgoods where hotel_id=$hotel_id and DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and openid!='' group by add_date";
            $res_goods = $model->query($sql_goods);
            $hotel_goods = array();
            foreach ($res_goods as $goodv){
                $hotel_goods[$goodv['add_date']] = $goodv['num'];
            }
            $log.="|sql_goods|".$sql_goods;


            //店内下单
            $sql_order= "select DATE(add_time) add_date,COUNT(id) as num from savor_smallapp_order where DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and box_mac in($hotel_boxs_str) GROUP BY add_date";
            $hotel_order = array();
            if(!empty($boxs)){
                $res_order = $model->query($sql_order);
                foreach ($res_order as $orderv){
                    $hotel_order[$orderv['add_date']] = $orderv['num'];
                }
            }
            $log.="|sql_order|".$sql_order;

            //上电视
            $sql_ontv = "select DATE(create_time) add_date,COUNT(DISTINCT openid) as num from savor_smallapp_forscreen_record where DATE(create_time)>='$cdate' and DATE(create_time)<='$edate' and action=40 and mobile_brand!='devtools' and box_mac in($hotel_boxs_str) group by add_date";
            $hotel_ontv = array();
            if(!empty($boxs)){
                $res_ontv = $model->query($sql_ontv);
                foreach ($res_ontv as $ov){
                    $hotel_ontv[$ov['add_date']] = $ov['num'];
                }
            }

            $log.="|sql_ontv|".$sql_ontv."\r\n";

            //欢迎词
            $sql_welcome = "select DATE(add_time) add_date,COUNT(id) as num from savor_smallapp_welcome where DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and hotel_id=$hotel_id group by add_date";
            $res_welcome = $model->query($sql_welcome);
            $hotel_welcome = array();
            foreach ($res_welcome as $wv){
                $hotel_welcome[$wv['add_date']] = $wv['num'];
            }
            $log.="|sql_welcome|".$sql_welcome."\r\n";

            $all_hotel_action = array_merge($hotel_bind,$hotel_sign,$hotel_goods,$hotel_order,$hotel_ontv,$hotel_welcome);
            $action_num = count($all_hotel_action);
            if($action_num>0){
                if($action_num>3){
                    $hotel_num_gt3[] = $hotel_id;
                }else{
                    $hotel_num_let3[] = $hotel_id;
                }
            }
            $log.="|num|$action_num"."\r\n";
            $log_file_name = APP_PATH.'Runtime/Logs/'.'sale_'.date("Ymd").".log";
            @file_put_contents($log_file_name, $log, FILE_APPEND);
        }

        $log_hotel ="hotelids:let3|".json_encode($hotel_num_let3)."|gt3|$hotel_num_gt3|time|$ctime-$etime"."\r\n";
        $log_file_name = APP_PATH.'Runtime/Logs/'.'sale_'.date("Ymd").".log";
        @file_put_contents($log_file_name, $log_hotel, FILE_APPEND);

        $hotel_let3 = array();
        if(!empty($hotel_num_let3)){
            $hotel_let3_ids = join(',',$hotel_num_let3);
            $sql_hotel_num_let3 = "select area_id,count(id) as num from savor_hotel where id in ($hotel_let3_ids) and state=1 and flag=0 group by area_id";
            $res_let3 = $model->query($sql_hotel_num_let3);
            foreach ($res_let3 as $v){
                $hotel_let3[$v['area_id']] = $v['num'];
            }
        }

        $hotel_gt4 = array();
        if(!empty($hotel_num_gt3)){
            $hotel_gt3_ids = join(',',$hotel_num_gt3);
            $sql_hotel_num_gt3 = "select area_id,count(id) as num from savor_hotel where id in ($hotel_gt3_ids) and state=1 and flag=0 group by area_id";
            $res_gt3 = $model->query($sql_hotel_num_gt3);
            foreach ($res_gt3 as $v){
                $hotel_gt4[$v['area_id']] = $v['num'];
            }
        }
        $hotel_box_types = getHeartBoXtypeIds(2);
        //所有酒楼
        $sql_hotel = "select area_id,count(id) as num from savor_hotel where state=1 and flag=0 and hotel_box_type in($hotel_box_types) group by area_id";
        $res_hotel = $model->query($sql_hotel);
        $datalist = array();
        foreach ($res_hotel as $v){
            $area_id = $v['area_id'];
            $all_hotel_num = $v['num'];
            $sql_area = "select id,region_name from savor_area_info where id=$area_id";
            $res_area = $model->query($sql_area);
            $area_name = $res_area[0]['region_name'];
            $new_bind_hotel_num = isset($new_bind_hotels[$area_id])?$new_bind_hotels[$area_id]:0;//新增绑定酒楼数
            $hotel_let3_num = isset($hotel_let3[$area_id])?$hotel_let3[$area_id]:0;//活跃使用 使用1-3天
            $hotel_gt3_num = isset($hotel_gt4[$area_id])?$hotel_gt4[$area_id]:0;//活跃使用 使用大于3天
            $hotel_let3_names = array();
            if($hotel_let3_num){
//                $sql_hotel_name = "select name from savor_hotel where id in ($hotel_let3_ids) and area_id=$area_id and state=1 and flag=0 and hotel_box_type in(2,3,6)";
                $sql_hotel_name = "select name from savor_hotel where id in ($hotel_let3_ids) and area_id=$area_id and state=1 and flag=0";
                $res_hotel_let3name = $model->query($sql_hotel_name);
                foreach ($res_hotel_let3name as $let3v){
                    $hotel_let3_names[]=$let3v['name'];
                }
            }
            $hotel_gt3_names = array();
            if($hotel_gt3_num){
                $sql_hotel_name = "select name from savor_hotel where id in ($hotel_gt3_ids) and area_id=$area_id and state=1 and flag=0";
                $res_hotel_gt3name = $model->query($sql_hotel_name);
                foreach ($res_hotel_gt3name as $gt3v){
                    $hotel_gt3_names[]=$gt3v['name'];
                }
            }
            $hotel_let3_names = !empty($hotel_let3_names)?join('，',$hotel_let3_names):'';
            $hotel_gt3_names = !empty($hotel_gt3_names)?join('，',$hotel_gt3_names):'';
            $bind_hotel_total_num = isset($sale_hotel_nums[$area_id])?$sale_hotel_nums[$area_id]:0;//累计绑定酒楼数
            $no_bind_hotel_num = $all_hotel_num-$bind_hotel_total_num;
            $datalist[] = array('area_id'=>$area_id,'area_name'=>$area_name,'new_bind_hotel_num'=>$new_bind_hotel_num,
                'hotel_let3_num'=>$hotel_let3_num,'hotel_gt3_num'=>$hotel_gt3_num,'hotel_let3_names'=>$hotel_let3_names,
                'hotel_gt3_names'=>$hotel_gt3_names,'no_bind_hotel_num'=>$no_bind_hotel_num, 'bind_hotel_total_num'=>$bind_hotel_total_num);
        }
        $xlsCell = array(
            array('area_id', '地区id'),
            array('area_name', '地区'),
            array('new_bind_hotel_num','新增绑定酒楼数'),
            array('hotel_let3_num','活跃使用1-3天'),
            array('hotel_gt3_num','活跃使用3天以上'),
            array('hotel_let3_names','活跃使用1-3天酒楼名称'),
            array('hotel_gt3_names','活跃使用3天以上酒楼名称'),
            array('no_bind_hotel_num','未绑定酒楼数'),
            array('bind_hotel_total_num','累计绑定酒楼数'),
        );
        $xlsName = '销售端使用情况';
        $filename = 'sale_task_list';
        $this->exportExcel($xlsName, $xlsCell, $datalist,$filename);
    }


    public function exportSaleHotel(){
        $ctime = I('get.ctime');
        $etime = I('get.etime');
        $ctime = !empty($ctime) ? date('Y-m-d',strtotime($ctime)).' 00:00:00' : '';
        $etime = !empty($etime) ? date('Y-m-d',strtotime($etime)).' 23:59:59' : '';
        if($ctime>$etime){
            echo 'ctime > etime error';
            exit;
        }
        $model = M();

        //活跃酒楼数
        $cdate = date('Y-m-d',strtotime($ctime));
        $edate = date('Y-m-d',strtotime($etime));
        $m_box = new \Admin\Model\BoxModel();
        $hotel_num = array();

        $sql_bindhotel = "select merchant.id as merchant_id,merchant.hotel_id from savor_integral_merchant as merchant left join savor_hotel as hotel on merchant.hotel_id=hotel.id 
where merchant.type=2 and merchant.status=1 and hotel.state=1 and hotel.flag=0 ";
//        $sql_bindhotel = "select id as hotel_id from savor_hotel where id in
//(select hi.hotel_id from savor_hotel_invite_code as hi left join savor_smallapp_user as u on hi.openid=u.openid where hi.type=2 and hi.state=1 and hi.flag=0 and hi.bind_mobile!='' and hi.openid!='' and u.small_app_id=5
//group by hi.hotel_id) and state=1 and flag=0 and hotel_box_type in(2,3,6)";
        $res_hotels = $model->query($sql_bindhotel);
        foreach ($res_hotels as $v){
            $hotel_id = $v['hotel_id'];
            $log = "hotel_id|$hotel_id";

            $where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
            $hotel_boxs = $m_box->getBoxByCondition('box.mac',$where,'');
            $boxs = array();
            foreach ($hotel_boxs as $bv){
                $box = $bv['mac'];
                $boxs[] = "'$box'";
            }
            $hotel_boxs_str = join(',',$boxs);
            //登录绑定
//            $sql_bind = "select hi.bind_time,hi.openid from savor_hotel_invite_code as hi left join savor_smallapp_user as u on hi.openid=u.openid where hi.hotel_id={$v['hotel_id']} and hi.bind_time>='$ctime' and hi.bind_time<='$etime' and hi.type=2 and hi.state=1 and hi.flag=0 and hi.bind_mobile!='' and hi.openid!='' and u.small_app_id=5 order by hi.id desc";
            //登录绑定
            $sql_bind = "select staff.add_time,u.openid from savor_integral_merchant_staff as staff left join savor_smallapp_user as u on staff.openid=u.openid where staff.merchant_id={$v['merchant_id']} and staff.add_time>='$ctime' and staff.add_time<='$etime' and u.small_app_id=5 order by staff.id desc";
            $res_bind = $model->query($sql_bind);

            $hotel_bind = array();
            foreach ($res_bind as $bindv){
                $bind_date = date('Y-m-d',strtotime($bindv['bind_time']));
                $hotel_bind[$bind_date][] = $bindv['openid'];
            }

            //签到
            $hotel_sign = array();
            if(!empty($boxs)){
                $sql_signin = "select DATE(add_time) add_date,openid from savor_smallapp_user_signin where DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and box_mac in($hotel_boxs_str)";
                $res_signin = $model->query($sql_signin);

                foreach ($res_signin as $v){
                    $hotel_sign[$v['add_date']][] = $v['openid'];
                }
                $log.="|sql_signin|".$sql_signin;
            }

            //上传我的商品，添加活动商品
            $sql_goods = "select DATE(add_time) add_date,openid from savor_smallapp_hotelgoods where hotel_id=$hotel_id and DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and openid!=''";
            $res_goods = $model->query($sql_goods);
            $hotel_goods = array();
            foreach ($res_goods as $goodv){
                $hotel_goods[$goodv['add_date']][] = $goodv['openid'];
            }
            $log.="|sql_goods|".$sql_goods;

            //店内下单
            $hotel_order = array();
            if(!empty($boxs)){
                $sql_order= "select DATE(add_time) add_date,openid from savor_smallapp_order where DATE(add_time)>='$cdate' and DATE(add_time)<='$edate' and box_mac in($hotel_boxs_str)";
                $res_order = $model->query($sql_order);
                foreach ($res_order as $orderv){
                    $hotel_order[$orderv['add_date']][] = $orderv['openid'];
                }
                $log.="|sql_order|".$sql_order;
            }

            //上电视
            $hotel_ontv = array();
            if(!empty($boxs)){
                $sql_ontv = "select DATE(create_time) add_date,openid from savor_smallapp_forscreen_record where DATE(create_time)>='$cdate' and DATE(create_time)<='$edate' and action=40 and mobile_brand!='devtools' and box_mac in($hotel_boxs_str)";
                $res_ontv = $model->query($sql_ontv);
                foreach ($res_ontv as $ov){
                    $hotel_ontv[$ov['add_date']][] = $ov['openid'];
                }
                $log.="|sql_ontv|".$sql_ontv."\r\n";
            }

            //欢迎词
            $hotel_welcome = array();
            $sql_welcome = "select DATE(welcome.add_time) add_date,u.openid from savor_smallapp_welcome as welcome left join savor_smallapp_user as u on welcome.user_id=u.id where DATE(welcome.add_time)>='$cdate' and DATE(welcome.add_time)<='$edate' and welcome.hotel_id=$hotel_id";
            $res_welcome = $model->query($sql_welcome);
            foreach ($res_welcome as $wv){
                $hotel_welcome[$wv['add_date']][] = $wv['openid'];
            }
            $log.="|sql_welcome|".$sql_welcome."\r\n";


            $all_hotel_action = array_merge($hotel_bind,$hotel_sign,$hotel_goods,$hotel_order,$hotel_ontv,$hotel_welcome);
            $action_num = count($all_hotel_action);
            if($action_num>0){
                if($action_num>=2){
                    $hotel_user = array();
                    $hotel_date = array_keys($all_hotel_action);
                    foreach ($hotel_date as $hv){
                        if(isset($hotel_bind[$hv])){
                            foreach ($hotel_bind[$hv] as $hvb){
                                $hotel_user[]=$hvb;
                            }
                        }
                        if(isset($hotel_sign[$hv])){
                            foreach ($hotel_sign[$hv] as $hvs){
                                $hotel_user[]=$hvs;
                            }
                        }
                        if(isset($hotel_goods[$hv])){
                            foreach ($hotel_goods[$hv] as $hvg){
                                $hotel_user[]=$hvg;
                            }
                        }
                        if(isset($hotel_order[$hv])){
                            foreach ($hotel_order[$hv] as $hvo){
                                $hotel_user[]=$hvo;
                            }
                        }
                        if(isset($hotel_ontv[$hv])){
                            foreach ($hotel_ontv[$hv] as $hvtv){
                                $hotel_user[]=$hvtv;
                            }
                        }
                        if(isset($hotel_welcome[$hv])){
                            foreach ($hotel_welcome[$hv] as $hwv){
                                $hotel_user[]=$hwv;
                            }
                        }
                    }
                    $hotel_users = array_unique($hotel_user);
                    $hotel_num[$hotel_id] = $hotel_users;
                }
            }
            $log.="|num|$action_num"."\r\n";
            $log_file_name = APP_PATH.'Runtime/Logs/'.'sale_'.date("Ymd").".log";
            @file_put_contents($log_file_name, $log, FILE_APPEND);
        }

        $hotel_ids = join(',',array_keys($hotel_num));
//        $sql_hotels = "select hotel.id as hotel_id,hotel.name as hotel_name,area.id as area_id,area.region_name as area_name,ext.maintainer_id,suser.remark as uname
//from savor_hotel hotel left join savor_hotel_ext ext on hotel.id=ext.hotel_id left join savor_sysuser as suser on ext.maintainer_id=suser.id left join savor_area_info area on hotel.area_id=area.id
//where hotel.id in($hotel_ids) and state=1 and flag=0 and hotel_box_type in(2,3,6)";
        $sql_hotels = "select hotel.id as hotel_id,hotel.name as hotel_name,area.id as area_id,area.region_name as area_name,ext.maintainer_id,suser.remark as uname 
from savor_hotel hotel left join savor_hotel_ext ext on hotel.id=ext.hotel_id left join savor_sysuser as suser on ext.maintainer_id=suser.id left join savor_area_info area on hotel.area_id=area.id
where hotel.id in($hotel_ids) and state=1 and flag=0";
        $res_hotels = $model->query($sql_hotels);

        $datalist = array();
        foreach ($res_hotels as $v){
            $openids  = $hotel_num[$v['hotel_id']];
            $nums = count($openids);
            $datalist[] = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],
                'hotel_name'=>$v['hotel_name'],'uname'=>$v['uname'],'openids'=>$openids,'nums'=>$nums);
        }
        $xlsCell = array(
            array('area_name', '地区'),
            array('hotel_name','酒楼名称'),
            array('nums','使用人数'),
            array('uname','维护人'),
        );
        $xlsName = '销售端酒楼使用2天以上';
        $filename = 'sale_hotel_use2list';
        $this->exportExcel($xlsName, $xlsCell, $datalist,$filename);
    }
    /**
     * 失联版位明细表
     */
    public function lossBoxList(){
        $hotel_box_types = getHeartBoXtypeIds(2);
        $days = I('days');        //失联天数
        $area_id = I('area_id');  //区域
        $where = '';
        if($area_id){
            $where .=" and hotel.area_id=".$area_id;
        }
        $sql ="select hotel.name,area.region_name, box.mac box_mac,box.name box_name from  savor_box box
                               left join savor_room room on box.room_id=room.id
                               left join savor_hotel hotel on room.hotel_id=hotel.id
                               left join savor_area_info area on hotel.area_id= area.id
                               where 1 and box.state=1 and box.flag=0 and hotel.state=1 and hotel.flag=0 and hotel.hotel_box_type in($hotel_box_types)".$where;
        $box_list = M()->query($sql);
        
        $result = [];
        foreach($box_list as $key=>$v){
            $tmp = [];
            $sql ="select * from savor_heart_log where box_mac='".$v['box_mac']."' and type=2";
            
            $heart_info = M()->query($sql);
            if(empty($heart_info)){
                
                $tmp['hotel_name'] = $v['name'];
                $tmp['region_name'] = $v['region_name'];
                $tmp['box_mac']    = $v['box_mac'];
                $tmp['box_name']   = $v['box_name'];
                $result[] = $tmp;
            }else {
                
                $jz_date = date('Y-m-d H:i:s',strtotime("-$days days"));
                $last_heart_time = $heart_info[0]['last_heart_time'];
                
                if($jz_date>$last_heart_time){
                    $tmp['hotel_name'] = $v['name'];
                    $tmp['region_name'] = $v['region_name'];
                    $tmp['box_mac']    = $v['box_mac'];
                    $tmp['box_name']   = $v['box_name'];
                    $result[] = $tmp;
                }
                
            }
            
        }
        $xlsCell = array(
            array('region_name', '地区'),
            array('hotel_name','酒楼名称'),
            array('box_mac','机顶盒mac'),
            array('box_name','版位名称'),
        );
        $xlsName = '深圳地区失联超过10天的版位信息';
        $filename = 'sale_hotel_use2list';
        $this->exportExcel($xlsName, $xlsCell, $result,$filename);
        
    }

    /**
     * 渠道部酒楼需求
     */
    public function gethotel(){
        $sql ="select hotel.id as hotel_id,hotel.name,hotel.contractor,hotel.hotel_box_type,hotel.is_4g,ext.mac_addr,ext.server_location,
food.name as food_name,ext.avg_expense
from savor_hotel as hotel left join savor_hotel_ext as ext on hotel.id=ext.hotel_id left join savor_hotel_food_style as food
on ext.food_style_id=food.id where hotel.state=1 and hotel.flag=0 and hotel.type=1 and hotel.id not in(7,883)";
        $hotel_list = M()->query($sql);

        $hotel_types = C('hotel_box_type');
        $datalist = array();
        foreach($hotel_list as $key=>$v){
            $hotel_id = $v['hotel_id'];
            $sql_boxnum ="select count(box.mac) as box_num from savor_box as box left join savor_room as room on box.room_id=room.id where room.hotel_id=$hotel_id and box.state=1 and box.flag=0 ";
            $res_box = M()->query($sql_boxnum);
            $box_num = 0;
            if(!empty($res_box)){
                $box_num = $res_box[0]['box_num'];
            }
            if(isset($hotel_types[$v['hotel_box_type']])){
                $hotel_type_str = $hotel_types[$v['hotel_box_type']];
            }else{
                $hotel_type_str = '';
            }
            if($v['is_4g']){
                $is_4g_str = '是';
            }else{
                $is_4g_str = '否';
            }
            if(!empty($v['mac_addr'])){
                $v['mac_addr'] = "'{$v['mac_addr']}'";
            }else{
                $v['mac_addr'] = "";
            }

            $v['hotel_type_str'] = $hotel_type_str;
            $v['box_num'] = $box_num;
            $v['is_4g_str'] = $is_4g_str;
            $datalist[]=$v;
        }
        $xlsCell = array(
            array('name', '酒楼名称'),
            array('food_name','菜系'),
            array('avg_expense','人均消费'),
            array('contractor','酒店联系人'),
            array('hotel_type_str','酒楼机顶盒类型'),
            array('is_4g_str','是否4G酒楼'),
            array('mac_addr','小平台MAC地址'),
            array('server_location','小平台存放位置'),
            array('box_num','正常机顶盒数量'),
        );
        $xlsName = '渠道部需求表';
        $filename = 'channel_hotellist';
        $this->exportExcel($xlsName, $xlsCell, $datalist,$filename);

    }
    public function forscreenDetail(){
        $sql ="SELECT hotel.name hotel_name,f.create_time,f.box_mac,room.name room_name FROM `savor_smallapp_forscreen_record` f 
               left join savor_box box on f.box_mac = box.mac 
               left join savor_room room on box.room_id = room.id 
               left join savor_hotel hotel on room.hotel_id= hotel.id 
               where 1 and f.create_time>='2020-05-25 00:00:00' and f.small_app_id = 1 
               and f.mobile_brand !='devtools' and box.flag=0 and box.state=1 
               order by hotel.id asc ,f.create_time asc ";
       $datalist = M()->query($sql); 
       $xlsCell = array(
           array('hotel_name', '酒楼名称'),
           array('create_time','投屏时间'),
           array('room_name','包间名称'),
           array('box_mac','机顶盒mac')
           
       );
       $xlsName = '渠道部需求表';
       $filename = 'channel_hotellist';
       $this->exportExcel($xlsName, $xlsCell, $datalist,$filename);
    }
    public function wifiForsreenDetail(){
        $start_date = I('start_date');
        $type       = I('type',1);   //1:扫码日志  2:wifi链接错误日志 3:wifi投屏日志 
        //$box_mac = "'00226D2FB212','00226D584193','00226D58461F','00226D6555FB','00226D584138','00226D58423B','00226D65554A'";
        $box_mac = I('box_mac');
        if($type==1){//扫码日志
            $sql ="SELECT hotel.name hotel_name,room.name room_name,box.name box_name,log.* FROM `savor_smallapp_qrcode_log` log 
                   left join savor_box box on log.box_mac= box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on room.hotel_id=hotel.id
                   where log.create_time>='".$start_date."' and log.box_mac in(\"$box_mac\") and hotel.flag=0 and hotel.state=1";
            
            $data = M()->query($sql);
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('openid','openid'),
                array('create_time','扫码时间')
                 
            );
            $xlsName = '用户扫码日志';
            $filename = 'user_scan_qrcode_detail';
            
        }else if($type==2){//wifi链接错误日志
            $sql = "SELECT hotel.name hotel_name,room.name room_name,box.name box_name,err.* FROM `savor_smallapp_wifi_err` err
                   left join savor_box box on err.box_mac= box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on room.hotel_id=hotel.id
                   where err.create_time>='".$start_date."' and err.box_mac in(\"$box_mac\") and hotel.flag=0 and hotel.state=1 and box.flag=0 and box.state=1";
            
            $data = M()->query($sql);
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('err_info','错误信息'),
                array('mobile_brand','手机品牌'),
                array('mobile_model','手机型号'),
                array('platform','操作系统'),
                array('system','系统版本'),
                array('version','微信版本'),
                array('create_time','错误时间')
                 
            );
            $xlsName = '用户链接wifi错误日志';
            $filename = 'user_link_wifi_err_detail';
            
        }else if($type==3){//wifi投屏日志
            $sql ="select hotel.name hotel_name,room.name room_name,box.name box_name,log.openid,log.action,log.resource_type,log.create_time,log.box_mac from `savor_smallapp_forscreen_record` log
                   left join savor_box box on log.box_mac=box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on hotel.id= room.hotel_id
                   where log.create_time>='".$start_date."' and log.box_mac in($box_mac) and hotel.flag=0 and hotel.state=1 and log.mobile_brand!='devtools'";
            $data = M()->query($sql);
            foreach($data as $key=>$v){
                if($v['action']==2 && $v['resource_type']==1){
                    $data[$key]['action_str'] = '图片滑动';
                }else if($v['action']==2 && $v['resource_type']==2){
                    $data[$key]['action_str'] = '视频投屏';
                }else if($v['action']==4){
                    $data[$key]['action_str'] = '多图投屏';
                }else if($v['action']==5){
                    $data[$key]['action_str'] = '视频点播';
                }
            }
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('action_str','投屏动作'),
                array('openid','openid'),
                array('create_time','投屏时间')
            );
            $xlsName = '用户极简版投屏日志';
            $filename = 'user_wifi_forscreen_detail';
        }
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function haveWifiForsreenDetail(){
        $start_date = I('start_date')?I('start_date'):'2020-06-03 00:00:00';
        $type       = I('type',1);   //1:扫码日志  2:wifi链接错误日志 3:wifi投屏日志
        //$box_mac = "'00226D2FB212','00226D584193','00226D58461F','00226D6555FB','00226D584138','00226D58423B','00226D65554A'";
        $sql ="SELECT box_mac FROM `savor_smallapp_forscreen_record` WHERE create_time>='".$start_date."' and small_app_id=2 GROUP by box_mac";
        $box_list = M()->query($sql);
        $box_mac = '';
        $space   = '';
        foreach ($box_list as $key=>$v){
            $box_mac .= $space ."'".$v['box_mac']."'";
            $space    = ',';
        }
        if($type==1){//扫码日志
            $sql ="SELECT hotel.name hotel_name,room.name room_name,box.name box_name,log.* FROM `savor_smallapp_qrcode_log` log
                   left join savor_box box on log.box_mac= box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on room.hotel_id=hotel.id
                   where log.create_time>='".$start_date."' and log.box_mac in($box_mac) and hotel.flag=0 and hotel.state=1";
            
            $data = M()->query($sql);
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('openid','openid'),
                array('create_time','扫码时间')
                 
            );
            $xlsName = '用户扫码日志';
            $filename = 'user_scan_qrcode_detail';
    
        }else if($type==2){//wifi链接错误日志
            $sql = "SELECT hotel.name hotel_name,room.name room_name,box.name box_name,err.* FROM `savor_smallapp_wifi_err` err
                   left join savor_box box on err.box_mac= box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on room.hotel_id=hotel.id
                   where err.create_time>='".$start_date."' and err.box_mac in($box_mac) and hotel.flag=0 and hotel.state=1";
    
            $data = M()->query($sql);
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('err_info','错误信息'),
                array('create_time','错误时间')
                 
            );
            $xlsName = '用户链接wifi错误日志';
            $filename = 'user_link_wifi_err_detail';
    
        }else if($type==3){//wifi投屏日志
            $sql ="select hotel.name hotel_name,room.name room_name,box.name box_name,log.openid,log.action,log.resource_type,log.create_time,log.box_mac from `savor_smallapp_forscreen_record` log
                   left join savor_box box on log.box_mac=box.mac
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on hotel.id= room.hotel_id
                   where log.create_time>='".$start_date."' and log.box_mac in($box_mac) and hotel.flag=0 and hotel.state=1 and log.mobile_brand!='devtools'";
            $data = M()->query($sql);
            foreach($data as $key=>$v){
                if($v['action']==2 && $v['resource_type']==1){
                    $data[$key]['action_str'] = '图片滑动';
                }else if($v['action']==2 && $v['resource_type']==2){
                    $data[$key]['action_str'] = '视频投屏';
                }else if($v['action']==4){
                    $data[$key]['action_str'] = '多图投屏';
                }else if($v['action']==5){
                    $data[$key]['action_str'] = '视频点播';
                }
            }
            $xlsCell = array(
                array('hotel_name', '酒楼名称'),
                array('room_name','包间名称'),
                array('box_name','版位名称'),
                array('box_mac','机顶盒mac'),
                array('action_str','投屏动作'),
                array('openid','openid'),
                array('create_time','投屏时间')
            );
            $xlsName = '用户极简版投屏日志';
            $filename = 'user_wifi_forscreen_detail';
        }
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    /**
     * @desc 2020-06-01开始连续超过三天有普通版投屏的版位信息
     */
    public function have3DayForscreenBoxList(){
        $sql ="SELECT DATE_FORMAT(create_time,'%Y%m%d') as f_date,box_mac FROM `savor_smallapp_forscreen_record` WHERE `create_time`>'2020-06-01 00:00:00' and small_app_id=1 and mobile_brand !='devtools' and mobile_brand!='dev4gtools'";
        $data = M()->query($sql);
        $rts = [];
        foreach($data as $key=>$v){
            
            if(!is_array($rts[$v['box_mac']])){
                $rts[$v['box_mac']] = [];
            }
            if(!in_array($v['f_date'],$rts[$v['box_mac']])){
                
                array_push($rts[$v['box_mac']],$v['f_date']);
            }
        }
        $box_mac_str = '';
        $space       = '';
        foreach($rts as $key=>$v){
            if(count($rts[$key])<3){
                unset($rts[$key]);
            }else {
                $box_mac_str.= $space."'".$key."'";
                $space = ',';
            }
        }
        $sql ="    select hotel.name hotel_name,room.name room_name,box.name box_name,box.mac 
                   from savor_box box 
                   left join savor_room room on box.room_id= room.id
                   left join savor_hotel hotel on hotel.id= room.hotel_id
                   where box.mac in($box_mac_str) and hotel.flag=0 and hotel.state=1";
        $datalist = M()->query($sql);
        $xlsCell = array(
            array('hotel_name', '酒楼名称'),
            array('room_name','包间名称'),
            array('box_name','版位名称'),
            array('mac','机顶盒mac'),
        );
        $xlsName = '超过三天有普通版投屏的版位信息';
        $filename = 'have_3day_forscreen_boxlist';
        $this->exportExcel($xlsName, $xlsCell, $datalist,$filename);
        
    }
    /**
     * @desc 超过7天失联的版位信息 每天早上8:30发送邮件
     */
    public function lostBoxList(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $hotel_box_types = getHeartBoXtypeIds(2);
        $sql ="select area.region_name,hotel.name hotel_name ,room.name room_name,user.remark, 
               box.mac box_mac,hlog.last_heart_time ,box.id box_id ,hlog.box_id hlog_box_id,hlog.pro_period
               from savor_box box  
               left join savor_room room on box.room_id= room.id 
               left join savor_hotel hotel on room.hotel_id=hotel.id 
               left join savor_area_info area on hotel.area_id= area.id 
               left join savor_hotel_ext ext on hotel.id = ext.hotel_id 
               left join savor_sysuser user on user.id= ext.maintainer_id 
               left join savor_heart_log hlog on box.mac=hlog.box_mac 
               where hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 and hotel.flag=0 and box.state=1 ";
        $data = M()->query($sql);
        //$datalist = [];
        $promenuHoModel = new \Admin\Model\ProgramMenuHotelModel();
        $promenuListModel = new \Admin\Model\ProgramMenuListModel();
        $pro_hotel_arr = [];
        foreach($data as $key=>$v){
    	    if($v['box_id']==$v['hlog_box_id'] || empty($v['last_heart_time'])){
                if(empty($v['last_heart_time'])){
                    $data[$key]['last_heart_time'] = '';
                    $data[$key]['last_heart_time_str'] = '30天+';
                    $data[$key]['last_pro_update'] = '30天+';
                }else {
                    $heart_time = strtotime($v['last_heart_time']);
                    $lost_time = strtotime('-7 days');
                    $now_time  = time();
                    if($heart_time<=$lost_time){
                        $data[$key]['last_heart_time'] = $v['last_heart_time'];
                        
                        $diff_time = floor(($now_time - $heart_time)/86400);
                        $data[$key]['last_heart_time_str'] = $diff_time.'天';
                        
                        
                        $box_info = $redis->get('savor_box_'.$v['box_id']);
                        $box_info = json_decode($box_info,true);
                        $room_info = $redis->get('savor_room_'.$box_info['room_id']);
                        $room_info = json_decode($room_info,true);
                        if(!isset($pro_hotel_arr[$room_info['hotel_id']])){
                            $fields = 'pl.create_time';
                            $order = 'pl.create_time desc';
                            $limit = ' 1';
                            $pro_arr = $promenuHoModel->getProgramByHotelId($room_info['hotel_id'],$fields,$order,$limit);
                        
                            if(!empty($pro_arr)){
                                $pro_hotel_arr[$room_info['hotel_id']] = $pro_arr[0]['create_time'];
                            }else {
                                $pro_hotel_arr[$room_info['hotel_id']] = '';
                            }
                        }
                        if($pro_hotel_arr[$room_info['hotel_id']] ==''){
                            $data[$key]['last_pro_update'] = '30+天';
                        }else {
                            $map = [];
                            $map['menu_num'] = $v['pro_period'];
                            $pro_info = $promenuListModel->getOne('create_time', $map);
                            $diff_time =  strtotime($pro_hotel_arr[$room_info['hotel_id']]) - strtotime($pro_info['create_time']);
                            $diff_day = floor($diff_time/86400);
                            $data[$key]['last_pro_update'] = $diff_day.'天';
                        }
                        
                    }else {
                        unset($data[$key]);
                    }
                    
                }
                
                //获取最新的节目单
                /*  */
                   
    	    }else {
    	    	unset($data[$key]);
    	    }
        }
        
        sort($data);
        
        $xlsCell = array(
            array('region_name','区域'),
            array('hotel_name','酒楼名称'),
            array('remark','维护人'),
            array('room_name','包间名称'),
            array('box_mac','机顶盒mac'),
            array('last_heart_time','最后一次心跳时间'),
            array('last_heart_time_str','失联天数'),
            array('last_pro_update','节目未更新天数'),
        );
        $xlsName = '失联超过7天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        
        $path  = './Public/box_heart/'.date('Ym').'/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'mail.xls';
        //echo $path;exit;
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
        if($ret){
            $mail_config =  C('SEND_MAIL_CONF');
            $mail_config =  $mail_config['littlehotspot'];
            
            $ma_auto = new MailAuto();
            $mail = new \Mail\PHPMailer();
            $title = '失联超过7天的版位信息-'.date('Y-m-d');
            $body = '失联超过7天的版位信息';

            $mail_config =  C('SEND_MAIL_CONF');
            $mail_config =  $mail_config['littlehotspot'];
            
            $ma_auto = new MailAuto();
            $mail = new \Mail\PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->IsSMTP();
            $mail->Host = $mail_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mail_config['username'];
            $mail->Password = $mail_config['password'];
            $mail->Port=25;
            $mail->From = $mail_config['username'];
            $mail->FromName = $title;
            
            $mail->AddAddress("alex.liu@littlehotspot.com");
            $mail->AddAddress("li.zhi@littlehotspot.com");
            $mail->AddAddress("li.cong@littlehotspot.com");         
            $mail->AddAddress("zheng.wei@littlehotspot.com");
            $mail->AddAddress("xin.lijuan@littlehotspot.com");
            $mail->AddAddress("cao.jie@littlehotspot.com");
            $mail->AddAddress("lv.yulin@littlehotspot.com");
            $mail->AddAddress("ma.feng@littlehotspot.com");
            //$mail->AddAddress("yang.kai@littlehotspot.com");
            $mail->AddAddress("zhang.jing@littlehotspot.com");
            $mail->AddAddress("zhang.yingtao@littlehotspot.com");
            
            
            
            $mail->IsHTML(true);

            $mail->Subject = $title;
            $mail->Body = $body;
            $mail->AddAttachment($path); // 添加附件
            //var_dump($mail);exit;
            if($mail->Send()){
                echo date('Y-m-d').'发送成功';
            }else {
                echo date('Y-m-d').'发送失败';
            }
        }
    }

    /**
     * 超过7天失联的版位信息 每天早上8:30发送邮件
     */
    public function lostBoxListForTwo()
    {
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $hotel_box_types = getHeartBoXtypeIds(2);
        $not_hotel_in = '201,1129,925,791,7,883,845,598,597,504,482,493,53';
        $sql = "select area.region_name,hotel.name hotel_name ,room.name room_name,user.remark,
        box.mac box_mac,hlog.last_heart_time ,box.id box_id ,hlog.box_id hlog_box_id,hlog.pro_period,
        hlog.apk_version
        from savor_box box
        left join savor_room room on box.room_id= room.id
        left join savor_hotel hotel on room.hotel_id=hotel.id
        left join savor_area_info area on hotel.area_id= area.id
        left join savor_hotel_ext ext on hotel.id = ext.hotel_id
        left join savor_sysuser user on user.id= ext.maintainer_id
        left join savor_heart_log hlog on box.mac=hlog.box_mac
        where hotel.id not in($not_hotel_in) and  hotel.hotel_box_type in($hotel_box_types) and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
        $data = M()->query($sql);
        // $datalist = [];
        $promenuHoModel = new \Admin\Model\ProgramMenuHotelModel();
        $promenuListModel = new \Admin\Model\ProgramMenuListModel();
        $pro_hotel_arr = [];
        foreach ($data as $key => $v) {
            
            if (empty($v['last_heart_time'])) {
                $data[$key]['last_heart_time'] = '';
                $data[$key]['last_heart_time_str'] = '30';
                $data[$key]['last_pro_update'] = '否';
            } else {
                $heart_time = strtotime($v['last_heart_time']);
                $now_time = time();
                $data[$key]['last_heart_time'] = $v['last_heart_time'];
                $diff_time = floor(($now_time - $heart_time) / 86400);
                $data[$key]['last_heart_time_str'] = $diff_time ;
        
                $box_info = $redis->get('savor_box_' . $v['box_id']);
                $box_info = json_decode($box_info, true);
                $room_info = $redis->get('savor_room_' . $box_info['room_id']);
                $room_info = json_decode($room_info, true);

                $fields = 'pl.create_time,pl.menu_num';
                $order = 'pl.create_time desc';
                $limit = ' 2';
                $pro_arr = $promenuHoModel->getProgramByHotelId($room_info['hotel_id'], $fields, $order, $limit);

                $tmp_pro_arr = [];
                foreach($pro_arr as $kk=>$vv){
                    $tmp_pro_arr[] = $vv['menu_num'];
                }
                if(in_array($v['pro_period'], $tmp_pro_arr)){
                    $data[$key]['last_pro_update'] =  '是';
                }else {
                    $data[$key]['last_pro_update'] =  '否';
                }
                
                    
                    
                    
                
            }
                
            
        }
        //sort($data);
        
        $xlsCell = array(
            array(
                'region_name',
                '区域'
            ),
            array(
                'hotel_name',
                '酒楼名称'
            ),
            array(
                'remark',
                '维护人'
            ),
            array(
                'room_name',
                '包间名称'
            ),
            array(
                'box_mac',
                '机顶盒mac'
            ),
            array(
                'last_heart_time',
                '最后一次心跳时间'
            ),
            array(
                'last_heart_time_str',
                '失联天数(天)'
            ),
            array(
                'last_pro_update',
                '是否为最新节目单'
            ),
            array(
                'apk_version',
                'apk版本号'
            )
        );
        $xlsName = '网络版位节目单更新状态表';
        $filename = 'user_wifi_forscreen_detail';
        
        $path = './Public/box_heart/' . date('Ym') . '/';
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= date('Ymd') . 'mail.xls';
        // echo $path;exit;
        $ret = $this->exportExcel($xlsName, $xlsCell, $data, $filename, 2, $path);
        if ($ret) {
            $mail_config = C('SEND_MAIL_CONF');
            $mail_config = $mail_config['littlehotspot'];
            
            $ma_auto = new MailAuto();
            $mail = new \Mail\PHPMailer();
            $title = '设备心跳与节目单更新状态表-' . date('Y-m-d');
            $body = '设备心跳与节目单更新状态表';
            
            $mail_config = C('SEND_MAIL_CONF');
            $mail_config = $mail_config['littlehotspot'];
            
            $ma_auto = new MailAuto();
            $mail = new \Mail\PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->IsSMTP();
            $mail->Host = $mail_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mail_config['username'];
            $mail->Password = $mail_config['password'];
            $mail->Port = 25;
            $mail->From = $mail_config['username'];
            $mail->FromName = $title;
            
            $mail->AddAddress("alex.liu@littlehotspot.com");
            $mail->AddAddress("li.zhi@littlehotspot.com");
            $mail->AddAddress("li.cong@littlehotspot.com");
            $mail->AddAddress("zheng.wei@littlehotspot.com");
            $mail->AddAddress("xin.lijuan@littlehotspot.com");
            $mail->AddAddress("cao.jie@littlehotspot.com");
            $mail->AddAddress("lv.yulin@littlehotspot.com");
            $mail->AddAddress("ma.feng@littlehotspot.com");
            $mail->AddAddress("wang.xizong@littlehotspot.com");
            $mail->AddAddress("zhang.lijuan@littlehotspot.com");
            $mail->AddAddress("he.yongrui@littlehotspot.com");
            $mail->AddAddress("zhang.jing@littlehotspot.com");
            $mail->AddAddress("zhang.yingtao@littlehotspot.com");
            
            $mail->IsHTML(true);
            
            $mail->Subject = $title;
            $mail->Body = $body;
            $mail->AddAttachment($path); // 添加附件
                                         // var_dump($mail);exit;
            if ($mail->Send()) {
                echo date('Y-m-d') . '发送成功';
            } else {
                    echo date('Y-m-d').'发送失败';
                    }
                    }
    }

    public function forscreen4gbox(){
        $sql ="select a.id,a.box_mac,a.create_time,hotel.name hotel_name,room.name room_name,box.name box_name from savor_smallapp_forscreen_record as a left join savor_box box on a.box_mac=box.mac left join savor_room room on box.room_id=room.id
left join savor_hotel hotel on room.hotel_id=hotel.id where a.mobile_brand='dev4gtools' and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 order by a.id asc ";
        $res_data = M()->query($sql);

        $data = array();
        $m_track = new \Admin\Model\Smallapp\ForscreenTrackModel();
        foreach($res_data as $key=>$v){
            $dinfo = array('hotel_name'=>$v['hotel_name'],'box_name'=>$v['box_name'],'box_mac'=>$v['box_mac'],'add_time'=>$v['create_time'],
                'size'=>'1.1');
            $res_forscreentrack = $m_track->getInfo(array('forscreen_record_id'=>$v['id']));
            if(!empty($res_forscreentrack)){
                $dinfo['is_success'] = $res_forscreentrack['is_success'];
                if($res_forscreentrack['is_success']){
                    $dinfo['success_str'] = '成功';
                }else{
                    $dinfo['success_str'] = '失败';
                    $netty_position_result = json_decode($res_forscreentrack['netty_position_result'],true);
                    if($netty_position_result['code']!=10000){
                        $dinfo['msg'] = $netty_position_result['msg'];
                    }else{
                        $netty_result = json_decode($res_forscreentrack['netty_result'],true);
                        if($netty_result['code']!=10000){
                            $dinfo['msg'] = $netty_result['msg'];
                        }else{
                            if(!empty($res_forscreentrack['netty_callback_result'])){
                                $callback_result = json_decode($res_forscreentrack['netty_callback_result'],true);
                                if(is_array($callback_result)){
                                    if($callback_result['code']==10000){
                                        $dinfo['msg'] = '盒子未上报';
                                    }elseif(in_array($callback_result['code'],array(10706,10006))){
                                        $dinfo['msg'] = $callback_result['msg'];
                                    }else{
                                        $dinfo['msg'] = $callback_result['code'];
                                    }
                                }else {
                                    if(strpos($res_forscreentrack['netty_callback_result'],"10302")){
                                        $dinfo['msg'] = 'Netty [读]空闲超时';
                                    }else{
                                        $dinfo['msg'] = '盒子未上报';
                                    }
                                }

                            }else{
                                $dinfo['msg'] = '盒子未上报';
                            }
                        }

                    }


                }
                $data[]=$dinfo;
            }

        }
        $xlsCell = array(
            array('hotel_name', '酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','机顶盒mac'),
            array('add_time','投屏时间'),
            array('success_str','状态'),
            array('msg','原因')
        );
        $xlsName = '4G盒子投屏测试';
        $filename = '4g_box_forscreen';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function exportMallOrder(){
        $sql  = "SELECT openid from savor_smallapp_order where otype=5 and status in(51,52,53) and pay_type=10 group by openid";
        $ret1 = M()->query($sql);
        $sql  = "SELECT openid from savor_smallapp_order where otype=6 and status in(12,61,62) and gift_oid=0 and pay_type=10 group by openid";
        $ret2 = M()->query($sql);
        
        $ret = array_merge($ret1,$ret2);
        $openid_arr = [];
        foreach($ret as $key=>$v){
            if(!in_array($v['openid'],$openid_arr)){
                array_push($openid_arr,$v['openid']);
            }
        }
        $data = [];
        foreach($openid_arr as $key=> $v){
            $data[$key]['openid']      = $v;
            $sql ="select nickName from savor_smallapp_user where openid='".$v."' and small_app_id=1";
            $rts = M()->query($sql);
            $data[$key]['nickname'] = $rts[0]['nickname'];
            
            $total_fee = 0;
            $total_num = 0;
            //普通订单
            $sql = "select pay_fee from savor_smallapp_order where otype=5 and status in(51,52,53) and pay_type=10 and openid='".$v."'";
            $p_rts = M()->query($sql);
            foreach($p_rts as $kk=>$vv){
                $total_fee += $vv['pay_fee'];
            }
            
            //买赠订单
            $sql = "select pay_fee from savor_smallapp_order where otype=6 and status in(12,61,62) and gift_oid=0 and pay_type=10 and openid='".$v."'";
            $m_rts = M()->query($sql);
            foreach($m_rts as $kk=>$vv){
                $total_fee += $vv['pay_fee'];
            }
            $common_order_num = count($p_rts);
            $gift_order_num   = count($m_rts);
            $total_num = $common_order_num + $gift_order_num;
            $data[$key]['all_order_num']    = $total_num;         //总订单数
            $data[$key]['common_order_num'] = $common_order_num;  //普通订单
            $data[$key]['gift_order_num']   = $gift_order_num;    //买赠订单
            
            $avg_pay_fee = round($total_fee / $total_num,2);      //客单价
            
            $data[$key]['avg_pay_fee'] = $avg_pay_fee;
            
            
            
        }
        $xlsCell = array(
            array('openid', '微信唯一标识'),
            array('nickname','昵称'),
            array('all_order_num','购买次数'),
            array('common_order_num','普通订单'),
            array('gift_order_num','赠送订单'),
            array('avg_pay_fee','客单价')
        );
        $xlsName = '商城订单数据汇总';
        $filename = 'exportMallOrder';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function exportForscreenDays(){
        $sql =" SELECT hotel.id hotel_id,hotel.name hotel_name FROM savor_smallapp_forscreen_record r 
                LEFT  JOIN savor_box box ON r.box_mac = box.mac
                LEFT JOIN savor_room room ON box.room_id = room.id
                LEFT JOIN savor_hotel hotel ON room.hotel_id= hotel.id
                WHERE r.create_time >'2020-07-06 00:00:00' AND hotel.flag=0 AND hotel.state=1 AND box.flag =0 AND box.state=1 AND box.box_type=6 AND r.small_app_id IN(1,2) and r.mobile_brand!='devtools' GROUP BY hotel.id";
        $hotel_list = M()->query($sql);
        $date_arr = array('2020-07-06','2020-07-07','2020-07-08','2020-07-09','2020-07-10','2020-07-11','2020-07-12','2020-07-13','2020-07-14','2020-07-15');
        
        foreach($hotel_list as $key=>$val){
            $hotel_list[$key]['forscreen_day'] = 0;
            foreach($date_arr as $vv){
                $start_time = $vv.' 00:00:00';
                $end_time   = $vv.' 23:59:59';
                
                $sql ="SELECT hotel.id,r.create_time FROM savor_smallapp_forscreen_record r 
                LEFT  JOIN savor_box box ON r.box_mac = box.mac
                LEFT JOIN savor_room room ON box.room_id = room.id
                LEFT JOIN savor_hotel hotel ON room.hotel_id= hotel.id
                WHERE r.create_time >'".$start_time."' and r.create_time<'".$end_time."' AND hotel.flag=0 
                AND hotel.state=1 AND box.flag =0 AND box.state=1 AND box.box_type=6 
                AND r.small_app_id IN(1,2) and r.mobile_brand!='devtools' and hotel.id=".$val['hotel_id'];
                $ret = M()->query($sql);
                if(!empty($ret)){
                    $hotel_list[$key]['forscreen_day'] +=1;
                }
            }
        }
        $xlsCell = array(
            array('hotel_id', '酒楼id'),
            array('hotel_name','酒楼名称'),
            array('forscreen_day','互动天'),
        );
        $xlsName = '商城订单数据汇总';
        $filename = 'exportMallOrder';
        $this->exportExcel($xlsName, $xlsCell, $hotel_list,$filename);
    }
    /**
     *@desc 扫极简版码用户链接wifi投屏数据统计
     */
    public function topSpeedForscreen(){
        $start_time = I('start_time');
        $sql = "select l.openid from savor_smallapp_qrcode_log l
                left join savor_box box on l.box_mac= box.mac
                left join savor_room room on box.room_id = room.id
                left join savor_hotel hotel on room.hotel_id = hotel.id
                where hotel.flag = 0 and hotel.state=1 and box.flag=0 and box.state=1 
                and box.is_open_simple=1
                and l.create_time>'".$start_time."' group by l.openid";
        
        $qrcode_list = M()->query($sql);
        //print_r($qrcode_list);exit;
        //$data = array();
        foreach($qrcode_list as $key=>$v){
            $sql ="select hotel.name hotel_name,room.name room_name,box.mac box_mac 
                   from savor_smallapp_qrcode_log l
                   left join savor_box box on l.box_mac= box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id = hotel.id
                   where hotel.flag = 0 and hotel.state=1 and box.flag=0 and box.state=1 
                   and  l.create_time>'".$start_time."' and box.is_open_simple=1 and l.openid='".$v['openid']."'";
            $qrcode_data = M()->query($sql);
               
            $qrcode_list[$key]['hotel_name']          = $qrcode_data[0]['hotel_name']; //扫码酒楼
            $qrcode_list[$key]['room_name']           = $qrcode_data[0]['room_name'];  //扫码包间
            $qrcode_list[$key]['box_mac']             = $qrcode_data[0]['box_mac'];    //扫码mac
            $qrcode_list[$key]['scan_qrcode_numbers'] = count($qrcode_data);           //扫码次数
            
            
            //wifi链接报错
            $sql ="select err_info,mobile_brand,mobile_model,platform,`system`,`version` from savor_smallapp_wifi_err
                   where create_time>'".$start_time."' and openid='".$v['openid']."'";
            $ret = M()->query($sql);
            if(empty($ret)){
                $qrcode_list[$key]['erro_info'] = '无';
                $qrcode_list[$key]['mobile_brand'] = '';
                $qrcode_list[$key]['mobile_model'] = '';
                $qrcode_list[$key]['platform'] = '';
                $qrcode_list[$key]['system'] = '';
                $qrcode_list[$key]['version'] = '';
            }else {
                $space = '';
                $e_info_str = '';
                
                foreach($ret as $kk=>$vv){
                    
                    
                    $e_info_str .=$space .$vv['err_info'];
                    $space = ',';
                }
                $qrcode_list[$key]['erro_info'] = $e_info_str;
                $qrcode_list[$key]['mobile_brand'] = $ret[0]['mobile_brand'];
                $qrcode_list[$key]['mobile_model'] = $ret[0]['mobile_model'];
                $qrcode_list[$key]['platform'] = $ret[0]['platform'];
                $qrcode_list[$key]['system'] = $ret[0]['system'];
                $qrcode_list[$key]['version'] = $ret[0]['version'];
            }
            //是否有投屏
            $sql = "select * from savor_smallapp_forscreen_record where openid='".$v['openid']."'
                    and create_time>'".$start_time."' and small_app_id=2";
            $ret = M()->query($sql);
            $foscreen_num = count($ret);
            if($foscreen_num>0){
                $qrcode_list[$key]['froscreen_num'] = $foscreen_num.'条投屏记录';
            }else {
                $qrcode_list[$key]['froscreen_num'] = '无 或机顶盒未上报投屏日志';
            }
        }
        
        $xlsCell = array(
            array('openid', 'openid'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','扫码mac'),
            array('scan_qrcode_numbers','扫码次数'),
            array('erro_info','链接wifi是否有报错'),
            array('mobile_brand','手机品牌'),
            array('mobile_model','手机型号'),
            array('platform','手机系统'),
            array('system','系统版本'),
            array('version','微信版本'),
            array('froscreen_num','是否有投屏记录'),
            
        );
        $xlsName = '扫极简版码用户链接wifi投屏数据统计';
        $filename = 'topSpeedForscreen';
        $this->exportExcel($xlsName, $xlsCell, $qrcode_list,$filename);
    }
    public function isHaveForscreenBoxList(){
        $box_list = file_get_contents('./Public/box_list.txt');
        $box_list = explode("\r\n",$box_list);
        $data = [];
        foreach($box_list as $key=>$v){
            $sql ="select count(id) as num from savor_smallapp_forscreen_record 
                   where box_mac='".$v."' and create_time>'2020-07-01 00:00:00'";
            
            $ret = M()->query($sql);
            $num = $ret[0]['num'];
            $data[$key]['box_mac'] = $v;
            if(empty($num)){
                $data[$key]['is_have'] = '无';
            }else {
                $data[$key]['is_have'] = '有';
            }
        }
        $xlsCell = array(
            array('box_mac', '机顶盒mac'),
            array('is_have','是否有投屏记录')
        
        );
        $xlsName = '扫极简版码用户链接wifi投屏数据统计';
        $filename = 'topSpeedForscreen';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function topForscreenByBox(){
        $start_time = I('start_time');
        $sql = "select l.box_mac from savor_smallapp_qrcode_log l
                left join savor_box box on l.box_mac= box.mac
                left join savor_room room on box.room_id = room.id
                left join savor_hotel hotel on room.hotel_id = hotel.id
                where hotel.flag = 0 and hotel.state=1 and box.flag=0 and box.state=1 
                and box.is_open_simple=1
                and l.create_time>'".$start_time."' group by l.box_mac";
        
        
        $qrcode_list = M()->query($sql);
        foreach($qrcode_list as $key=>$v){
            $sql ="select hotel.name hotel_name,room.name room_name,box.mac box_mac
                   from savor_smallapp_qrcode_log l
                   left join savor_box box on l.box_mac= box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id = hotel.id
                   where hotel.flag = 0 and hotel.state=1 and box.flag=0 and box.state=1
                   and  l.create_time>'".$start_time."' and box.is_open_simple=1 and l.box_mac='".$v['box_mac']."'";
            $qrcode_data = M()->query($sql);
             
            $qrcode_list[$key]['hotel_name']          = $qrcode_data[0]['hotel_name']; //扫码酒楼
            $qrcode_list[$key]['room_name']           = $qrcode_data[0]['room_name'];  //扫码包间
            $qrcode_list[$key]['box_mac']             = $qrcode_data[0]['box_mac'];    //扫码mac
            $qrcode_list[$key]['scan_qrcode_numbers'] = count($qrcode_data);           //扫码次数
        
        
            //wifi链接报错
            $sql ="select err_info,mobile_brand,mobile_model,platform,`system`,`version` from savor_smallapp_wifi_err
                   where create_time>'".$start_time."' and box_mac='".$v['box_mac']."'";
            $ret = M()->query($sql);
            if(empty($ret)){
                $qrcode_list[$key]['erro_info'] = '无';
                
            }else {
                $space = '';
                $e_info_str = '';
        
                foreach($ret as $kk=>$vv){
        
        
                    $e_info_str .=$space .$vv['err_info'];
                    $space = ',';
                }
                $qrcode_list[$key]['erro_info'] = $e_info_str;
                $qrcode_list[$key]['mobile_brand'] = $ret[0]['mobile_brand'];
                $qrcode_list[$key]['mobile_model'] = $ret[0]['mobile_model'];
                $qrcode_list[$key]['platform'] = $ret[0]['platform'];
                $qrcode_list[$key]['system'] = $ret[0]['system'];
                $qrcode_list[$key]['version'] = $ret[0]['version'];
            }
            //是否有投屏
            $sql = "select * from savor_smallapp_forscreen_record where box_mac='".$v['box_mac']."'
                    and create_time>'".$start_time."' and small_app_id=2";
            $ret = M()->query($sql);
            $foscreen_num = count($ret);
            if($foscreen_num>0){
                $qrcode_list[$key]['froscreen_num'] = $foscreen_num.'条投屏记录';
            }else {
                $qrcode_list[$key]['froscreen_num'] = '无 或机顶盒未上报投屏日志';
            }
            //最近两周有没有投屏
            $last_time = date('Y-m-d 00:00:00',strtotime('-2 weeks')) ;
            
            $sql = "select * from savor_smallapp_forscreen_record where box_mac='".$v['box_mac']."'
                    and create_time>'".$last_time."' and small_app_id=2";
            
            $ret = M()->query($sql);
            $history_foscreen_num = count($ret);
            
            if($history_foscreen_num>0){
                $qrcode_list[$key]['history_foscreen_num'] = $history_foscreen_num.'条投屏记录';
            }else {
                $qrcode_list[$key]['history_foscreen_num'] = '无 或机顶盒未上报投屏日志';
            }
        }
        
        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','扫码mac'),
            array('scan_qrcode_numbers','扫码次数'),
            array('erro_info','链接wifi是否有报错'),
            array('mobile_brand','手机品牌'),
            array('mobile_model','手机型号'),
            array('platform','手机系统'),
            array('system','系统版本'),
            array('version','微信版本'),
            array('froscreen_num','是否有投屏记录'),
            array('history_foscreen_num','最近两周是否有投屏记录'),
        
        );
        $xlsName = '扫极简版码用户链接wifi投屏数据统计';
        $filename = 'topSpeedForscreen';
        $this->exportExcel($xlsName, $xlsCell, $qrcode_list,$filename);
    }
    public function exportSmallappTurn(){
        $open_type = I('open_type');
        if($open_type==1){//主干极简都开
            $where = ' and box.is_open_simple=1 and box.is_sapp_forscreen=1';
        }else if($open_type==2){//主干开极简关
            $where = ' and box.is_open_simple=0 and box.is_sapp_forscreen=1';
        }else if($open_type ==3){
            $where = ' and box.is_open_simple=1 and box.is_sapp_forscreen=0';
        }
        $sql =" select hotel.name hotel_name,room.name room_name,box.mac box_mac from 
                savor_box box 
                left join savor_room room on box.room_id = room.id
                left join savor_hotel hotel on room.hotel_id = hotel.id
                where hotel.flag = 0 and hotel.state=1 and box.flag=0 and box.state=1 
                $where";
        //echo $sql;exit;
        $data = M()->query($sql);
        
        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','mac'),
        
        );
        $xlsName = '扫极简版码用户链接wifi投屏数据统计';
        $filename = 'topSpeedForscreen';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    public function sampledata(){
        $sample_hotel_ids = C('SAMPLE_HOTEL');
        $hotel_ids = $sample_hotel_ids[236];

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $m_qrcodelog = new \Admin\Model\Smallapp\QrcodeLogModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();

        $start_date_a = '2020-08-04';
        $end_date_a = '2020-08-09';
        $days_a = $m_statistics->getDates($start_date_a,$end_date_a,2);
        $start_date_b = '2020-08-11';
        $end_date_b = '2020-08-16';
        $days_b = $m_statistics->getDates($start_date_b,$end_date_b,2);

        $data = array();
        foreach ($hotel_ids as $v){
            $hotel_id = intval($v);
            if(!$hotel_id){
                continue;
            }
            $res_hotel = $m_hotel->getOne($hotel_id);
            $hinfo = array('hotel_name'=>$res_hotel['name']);

            $fields = "count(a.id) as num";
            $qrcode_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days_a));
            $qrcode_where['a.type'] = array('in',array(8,12,13,16,29,30));
            $qrcode_where['box.state'] = 1;
            $qrcode_where['box.flag'] = 0;
            $qrcode_where['hotel.id'] = $hotel_id;
            $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where,'');
            $hinfo['qrcode_num_a'] = intval($res_qrcode[0]['num']);

            $forscreen_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days_a));
            $forscreen_where['hotel.id'] = $hotel_id;
            $forscreen_where['a.is_valid'] = 1;
            $forscreen_where['box.state'] = 1;
            $forscreen_where['box.flag'] = 0;
            $forscreen_where['a.mobile_brand'] = array('neq','devtools');
            $forscreen_where['a.small_app_id'] = 1;//1普通版
            $fields = "count(a.id) as fnum";
            $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','');
            $hinfo['fnum_a'] = intval($res_forscreen[0]['fnum']);

            $fields = "count(a.id) as num";
            $qrcode_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days_b));
            $qrcode_where['a.type'] = array('in',array(8,12,13,16,29,30));
            $qrcode_where['box.state'] = 1;
            $qrcode_where['box.flag'] = 0;
            $qrcode_where['hotel.id'] = $hotel_id;
            $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where,'');
            $hinfo['qrcode_num_b'] = intval($res_qrcode[0]['num']);

            $forscreen_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days_b));
            $forscreen_where['hotel.id'] = $hotel_id;
            $forscreen_where['a.is_valid'] = 1;
            $forscreen_where['box.state'] = 1;
            $forscreen_where['box.flag'] = 0;
            $forscreen_where['a.mobile_brand'] = array('neq','devtools');
            $forscreen_where['a.small_app_id'] = 1;//1普通版
            $fields = "count(a.id) as fnum";
            $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','');
            $hinfo['fnum_b'] = intval($res_forscreen[0]['fnum']);

            $data[] = $hinfo;
        }

        $xlsCell = array(
            array('hotel_name','酒楼名称'),
            array('qrcode_num_a','扫码数(时间段A 8.4-8.9)'),
            array('fnum_a','投屏数(时间段A 8.4-8.9)'),
            array('qrcode_num_b','扫码数(时间段B 8.11-8.16)'),
            array('fnum_b','投屏数(时间段B 8.11-8.16)'),
        );
        $xlsName = '酒楼扫码投屏统计';
        $filename = 'hotelqfdata';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    private function getScore($data,$conf_arr){
        $score = 0;
        foreach ($conf_arr as $key=>$v){
            if($data>=$v['min'] && $data<=$v['max']){
                $score =  $v['score'];
                break;
            }
        }
        return $score/100;
    }
    private function getLevel($data,$conf_arr){
        if($data>=$conf_arr['mina'] && $data<=$conf_arr['maxa']){
            $level = 'A';
        }else if($data>=$conf_arr['minb'] && $data<=$conf_arr['maxb']){
            $level = 'B';
        }else if($data>=$conf_arr['minc'] && $data<=$conf_arr['maxc']){
            $level = 'C';
        }
        return $level;
    }


    public function hotelassess(){
        $model = M();
        $sql = "select a.id,a.area_id,a.area_name,a.hotel_id,ext.is_train,a.hotel_name,a.hotel_box_type,a.hotel_level,a.team_name,a.maintainer,a.box_num,a.lostbox_num,a.fault_rate,a.all_assess,
a.operation_assess,a.zxrate,a.channel_assess,a.fjrate,a.data_assess,a.fjsalerate,a.saledata_assess,DATE_FORMAT(a.date,'%Y-%m-%d') as date 
from savor_smallapp_static_hotelassess as a left join savor_hotel_ext as ext on a.hotel_id=ext.hotel_id where a.date>=20200824 and a.date<=20200830";

        $res = $model->query($sql);
        $assess_money = array('A'=>10,'B'=>15,'C'=>5);
        $data = array();
        foreach ($res as $k=>$v){
            $money = $assess_money[$v['hotel_level']];
            if($v['operation_assess']==1){
                $operation_money = $money;
            }else{
                $operation_money = -$money;
            }
            if($v['channel_assess']==1){
                $channel_money = $money;
            }else{
                $channel_money = -$money;
            }
            if($v['is_train']==1){
                if($v['data_assess']==1){
                    $data_money = $money;
                }else{
                    $data_money = -$money;
                }
            }else{
                $data_money = 0;
            }
            if($v['is_train']==1){
                if($v['saledata_assess']==1){
                    $saledata_money = $money;
                }else{
                    $saledata_money = -$money;
                }
            }else{
                $saledata_money = 0;
            }
            $data[]=array('date'=>$v['date'],'team_name'=>$v['team_name'],'hotel_id'=>$v['hotel_id'],'hotel_level'=>$v['hotel_level'],
                'operation_money'=>$operation_money,'channel_money'=>$channel_money,'data_money'=>$data_money,
                'saledata_money'=>$saledata_money);
        }

        $xlsCell = array(
            array('date','日期'),
            array('team_name','组名'),
            array('hotel_id','酒楼id'),
            array('hotel_level','酒楼等级'),
            array('operation_money','运维奖金'),
            array('channel_money','渠道奖金'),
            array('data_money','数据奖金'),
            array('saledata_money','销售端数据奖金'),
        );
        $xlsName = '酒楼数据考核';
        $filename = 'hotelassessdata';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function exportForVideo(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $where = '';
        if(!empty($start_time)){
            $where .=" and create_time>='".$start_time."'";
        }
        if(!empty($end_time)){
            $where .= " and create_time <='".$end_time."'";
        }
        $sql = "select resource_size ,create_time  from savor_smallapp_forscreen_record where action = '2' AND resource_type = '2' and resource_size>0 ".$where;
        $data = M()->query($sql);
        $xlsCell = array(
            array('resource_size','视频资源大小'),
            array('create_time','投屏时间'),
        );
        $xlsName = '视频投屏数据';
        $filename = 'exportForVideo';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }
    public function exportForVaI(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $where = '';
        if(!empty($start_time)){
            $where .=" and create_time>='".$start_time."'";
        }
        if(!empty($end_time)){
            $where .= " and create_time <='".$end_time."'";
        }
        $sql = "select openid  from savor_smallapp_forscreen_record where ((action = '2' AND resource_type = '2') or action=4) and small_app_id=1 and openid!='ofYZG4yZJHaV2h3lJHG5wOB9MzxE' ".$where." group by openid";
        
        $user = M()->query($sql);
        foreach($user as $key=>$v){
            $sql = "select id from savor_smallapp_forscreen_record where (action = '2' AND resource_type = '2') and small_app_id=1 and openid ='".$v['openid']."'".$where.' group by forscreen_id';
            $rt = M()->query($sql);
            $v_num = count($rt);
            $sql = "select id from savor_smallapp_forscreen_record where action = '4'  and small_app_id=1 and openid ='".$v['openid']."'".$where.' group by forscreen_id';
            $rt = M()->query($sql);
            $i_num = count($rt);
            $user[$key]['v_num'] = $v_num;
            $user[$key]['i_num'] = $i_num;
            //echo $sql;exit;
        }
        $xlsCell = array(
            array('openid','openid'),
            array('v_num','投视频次数'),
            array('i_num','投图片次数'),
        );
        $xlsName = '视频投屏数据';
        $filename = 'exportForVideo';
        $this->exportExcel($xlsName, $xlsCell, $user,$filename);
        
    }
    public function exportForVideoUrl(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $where = '';
        if(!empty($start_time)){
            $where .=" and create_time>='".$start_time."'";
        }
        if(!empty($end_time)){
            $where .= " and create_time <='".$end_time."'";
        }
        $sql ="select imgs,create_time  FROM `savor_smallapp_forscreen_record` where action=2 and `resource_type`=2 and small_app_id=1 and imgs!='[\"forscreen/resource/15368043845967.mp4\"]'".$where;
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            $tmp = json_decode($v['imgs'],true);
            
            $data[$key]['video_url'] = 'http://oss.littlehotspot.com/'.$tmp[0];
        }
        $xlsCell = array(
            array('video_url','视频连接'),
            array('create_time','播放日期')
        );
        $xlsName = '视频投屏数据';
        $filename = 'exportForVideo';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
        
    }
    public function exportForscreenLogs(){
        set_time_limit(36000);
        ini_set("memory_limit","8018M");
        $start_time = "2021-02-26 00:00:00";
        $end_time   = "2021-03-05 23:59:59";
        $all_actions = C('all_forscreen_actions');
        $sql ="select log.id,track.id t_id,log.area_name,log.hotel_name,log.box_name,log.box_mac,
                case log.box_type
				when 2 then '二代网络版'
				when 3 then '二代5G'
				when 6 then '三代网络'
                when 7 then   '互联网电视'
                END AS box_type, log.mobile_brand,log.mobile_model,log.action,log.resource_type,
                log.resource_size,log.create_time,log.res_sup_time,log.res_eup_time,log.small_app_id,
                track.is_success,track.total_time
                from savor_smallapp_forscreen_record log
                left join savor_smallapp_forscreen_track track on log.id=track.forscreen_record_id
                where  log.create_time>='".$start_time."' and log.create_time<='".$end_time."' and  log.small_app_id in(1,2) and log.mobile_brand!='devtools'";
        //echo $sql;exit;
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            if(empty($v['t_id']) && $v['small_app_id']==1){
                unset($data[$key]);
                continue;
            }
            
            $nowaction_type = $v['action'];
            if($nowaction_type==2){
                $nowaction_type = $nowaction_type.'-'.$v['resource_type'];
            }
            if($v['small_app_id']==2 && $nowaction_type=='2-1'){
                unset($data[$key]);
                continue;
            }
            
            
            $data[$key]['action_name'] = $all_actions[$nowaction_type];
            
            if(!empty($v['resource_size'])){
                $data[$key]['resource_size'] = formatBytes($v['resource_size']);
            }else {
                $data[$key]['resource_size'] = '';
            }
            //是否成功
            if($v['small_app_id']==2){//如果是极简版
                if(!empty($v['res_sup_time']) && !empty($v['res_eup_time'])){
                    $data[$key]['is_success'] = '成功';
                    $diff_time = ($v['res_eup_time'] - $v['res_sup_time']) /1000;
                    $data[$key]['total_time'] = $diff_time;
                }else {
                    $data[$key]['is_success'] = '失败';
                    $data[$key]['total_time'] = '';
                }
                
            }else if($v['small_app_id']==1){
                if($v['is_success']==1){
                    $data[$key]['is_success'] ='成功';
                }else if($v['is_success']==2){
                    $data[$key]['is_success'] ='打断';
                }else if($v['is_success']==3){
                    $data[$key]['is_success'] ='退出投屏';
                }
            }
            $data[$key]['small_app_id'] = $v['small_app_id']==1?'普通版':'极简版';
        }
        //print_r($data);exit;
        $xlsName = '投屏日志明细';
        $filename = 'exportSl14BoxList';
        $data = array_values($data);
        $xlsCell = array(
        
            array('id','互动id'),
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('box_name','机顶盒名称'),
            array('box_mac','机顶盒编号'),
            array('box_type','设备类型'),
            array('mobile_brand','手机品牌'),
            array('mobile_model','手机型号'),
            array('action_name','投屏动作'),
            array('resource_size','资源大小'),
            array('create_time','投屏时间'),
            array('is_success','是否成功'),
            array('total_time','总计时间'),
            array('small_app_id','小程序版本')
        
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202103/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'投屏明细4.xls';
        
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
    }
    public function exportForscreenLogsByHotel(){
        set_time_limit(36000);
        ini_set("memory_limit","8018M");
        $start_time = "2020-08-01 00:00:00";
        $end_time   = "2021-03-05 23:59:59";
        $sql = "select hotel.id hotel_id,area.region_name area_name,hotel.name hotel_name,hotel.addr,user.remark,
                case hotel.is_4g
				when 1 then '是'
				when 2 then '否'
                END AS is_4g  
                from savor_hotel hotel
                left join savor_area_info area on hotel.area_id=area.id
                left join savor_hotel_ext ext on hotel.id= ext.hotel_id
                left join savor_sysuser user on ext.maintainer_id = user.id
                where hotel.state=1 and hotel.flag=0";
        $hotel_data = M()->query($sql);
        //print_r(hotel_data);
        $data = array();
        $flag = 0;
        foreach($hotel_data as $key=>$v){
            
            
            //互动数量
            $sql ="select count(id) as count from savor_smallapp_forscreen_record 
                   where create_time>='".$start_time."' and create_time<='".$end_time."' and small_app_id in(1,2)
                   and hotel_id=".$v['hotel_id'];
            
            $rt = M()->query($sql);
            //echo 'hotel_id_'.$v['hotel_id'].'_'.$rt[0]['count']."<br />";
            if($rt[0]['count']==0){
                $sql ="select count(box.id) as count from savor_box box
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id = hotel.id
                   where box.state=1 and box.flag=0 and hotel.id=".$v['hotel_id'];
                
                $rt = M()->query($sql);
                //版位数量
                $hotel_data[$key]['box_num'] = $rt[0]['count'];
                $data[$flag]= $hotel_data[$key];
                $flag ++;
            }
            
        }
        $xlsCell = array(
        
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('box_num','版位数量'),
            array('remark','维护人'),
            array('is_4g','是否4g'),
        
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202103/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'互动数为0的酒楼.xls';
        
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
    }
    public function exportForscreenLogsByBox(){
        set_time_limit(36000);
        ini_set("memory_limit","8018M");
        $start_time = "2020-08-01 00:00:00";
        $end_time   = "2021-03-05 23:59:59";
        $sql = "select area.region_name area_name,hotel.name hotel_name,hotel.addr,user.remark,box.name box_name,
                   box.mac,
                    case box.box_type
        				when 2 then '二代网络版'
        				when 3 then '二代5G'
        				when 6 then '三代网络'
                        when 7 then   '互联网电视' 
                        END AS box_type
                   from savor_box box
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id = hotel.id
                   left join savor_area_info area on hotel.area_id=area.id
                   left join savor_hotel_ext ext on hotel.id= ext.hotel_id
                   left join savor_sysuser user on ext.maintainer_id = user.id
                   where box.state=1 and box.flag=0 and hotel.state= 1 and hotel.flag=0";
        $box_data = M()->query($sql);
        $data = array();
        $flag = 0;
        foreach($box_data as $key=>$v){
            $sql ="select count(id) as count from savor_smallapp_forscreen_record 
                   where create_time>='".$start_time."' and create_time<='".$end_time."' and small_app_id in(1,2)
                   and box_mac='".$v['mac']."'";
            $rt = M()->query($sql);
            if($rt[0]['count']==0){
                $data[$flag] = $box_data[$key];
                $flag ++;
            }
            
        }
        $xlsCell = array(
        
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('box_name','机顶盒名称'),
            array('mac','设备mac'),
            array('box_type','设备类型'),
            array('remark','维护人'),
        
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202103/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'互动数为0的版位.xls';
        
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
    }
    public function exportForscreenLogsByTime(){
        set_time_limit(36000);
        ini_set("memory_limit","8018M");
        $start_time = "2020-08-01 00:00:00";
        $end_time   = "2021-03-05 23:59:59";
        $sql ="select box_mac from savor_smallapp_forscreen_record 
               where create_time>='".$start_time."' and create_time<='".$end_time."'
               and action in(4,3) and small_app_id=2 and res_sup_time>0 and res_eup_time>0 group by box_mac";
        
        $box_data = M()->query($sql);
        $data = array();
        $flag = 0; 
        foreach($box_data as $key=>$v){
            $sql = "select area.region_name area_name,hotel.name hotel_name,hotel.addr,user.remark,box.name box_name,
                   box.mac,
                    case box.box_type
        				when 2 then '二代网络版'
        				when 3 then '二代5G'
        				when 6 then '三代网络'
                        when 7 then   '互联网电视' 
                        END AS box_type
                   from savor_box box
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id = hotel.id
                   left join savor_area_info area on hotel.area_id=area.id
                   left join savor_hotel_ext ext on hotel.id= ext.hotel_id
                   left join savor_sysuser user on ext.maintainer_id = user.id
                   where box.state=1 and box.flag=0 and hotel.state= 1 and hotel.flag=0 and box.mac='".$v['box_mac']."'";
            $hotel_info = M()->query($sql);
         
            //$data[$key] = $rt[0];
            $sql ="select sum(`res_sup_time`) as res_sup_time,sum(`res_eup_time`) as res_eup_time from savor_smallapp_forscreen_record 
               where create_time>='".$start_time."' and create_time<='".$end_time."'
               and action in(4,3) and small_app_id=2 and res_sup_time>0 and res_eup_time>0 and box_mac='".$v['box_mac']."'";
            $rt = M()->query($sql);
            
            $res_sup_time = $rt[0]['res_sup_time'];
            $res_eup_time = $rt[0]['res_eup_time'];
            $diff_time = ($res_eup_time - $res_sup_time) / 1000;
            $sql ="select count(id) as count from savor_smallapp_forscreen_record 
               where create_time>='".$start_time."' and create_time<='".$end_time."'
               and action in(4,3) and small_app_id=2 and res_sup_time>0 and res_eup_time>0 and box_mac='".$v['box_mac']."'";
            $rt = M()->query($sql);
            $count = $rt[0]['count'];
            $avg_time = round($diff_time/$count,2);
            if($avg_time>6){
                $hotel_info = $hotel_info[0];
                $hotel_info['avg_time'] = $avg_time;
                $data[$flag] = $hotel_info;
                $flag ++;
            } 
        
        }
        $xlsCell = array(
        
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('box_name','机顶盒名称'),
            array('mac','设备mac'),
            array('box_type','设备类型'),
            array('avg_time','极简平均耗时'),
            array('remark','维护人'),
        
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202103/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'极简版慢test.xls';
        
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
    }
    
    public function exportReward(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $where = '';
        if(!empty($start_time)){
            $start_time = date('Y-m-d 00:00:00',strtotime($start_time));
            $where .=" and add_time>='".$start_time."'";
        }
        if(!empty($end_time)){
            $end_time = date('Y-m-d 23:59:59',strtotime($end_time));
            $where .= " and add_time<='".$end_time."'";
        }
        $sql = "select * from savor_smallapp_reward where hotel_id not in(7,925) and status in(2,3) {$where}";
        $res_data = M()->query($sql);
        $data = array();
        $m_box = new \Admin\Model\BoxModel();
        $m_hotel = new \Admin\Model\HotelModel();

        foreach($res_data as $key=>$v){
            $hotel_id = $v['hotel_id'];
            $box_mac = $v['box_mac'];
            $fields = 'box.name as box_name,hotel.name as hotel_name,area.region_name as area_name';
            $where = array('box.state'=>1,'box.flag'=>0,'box.mac'=>$box_mac,'hotel.id'=>$hotel_id);
            $res_box = $m_box->getDeviceInfoByBoxMac($fields,$where);
            if(!empty($res_box)){
                $b_info = $res_box[0];
            }else{
                $field = 'a.name as hotel_name,area.region_name as area_name';
                $where = array('a.id'=>$hotel_id);
                $b_info = $m_hotel->getHotelInfo($field,$where);
                $b_info['box_name'] = '';
            }

            $info = array('area_name'=>$b_info['area_name'],'hotel_name'=>$b_info['hotel_name'],'box_mac'=>$v['box_mac'],
                'box_name'=>$b_info['box_name'],'money'=>$v['money'],'add_time'=>$v['add_time']);
            $data[]=$info;
        }

        $xlsCell = array(
            array('area_name','地区'),
            array('hotel_name','酒楼名称'),
            array('box_mac','版位MAC'),
            array('box_name','版位名称'),
            array('money','打赏金额'),
            array('add_time','打赏时间'),
        );
        $xlsName = '打赏明细';
        $filename = 'exportRewardmoney';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);
    }

    public function exportBoxinteract(){
        $area_id = I('aid',236,'intval');
        $sql = "select box.mac as box_mac,box.name as box_name,room.name as room_name,hotel.name as hotel_name,hotel.id as hotel_id,area.region_name as area_name,ext.maintainer_id
        from savor_box as box left join savor_room as room on box.room_id=room.id left join savor_hotel as hotel on room.hotel_id=hotel.id left join savor_hotel_ext as ext
        on hotel.id=ext.hotel_id left join savor_area_info as area on hotel.area_id=area.id where hotel.area_id={$area_id} and box.state=1 and box.flag=0 and box.is_interact=1";
        $res_data = M()->query($sql);
        $data = array();
        foreach ($res_data as $v){
            $sql_u = "select * from savor_sysuser where id={$v['maintainer_id']}";
            $res_u = M()->query($sql_u);
            $v['maintainer_name'] = $res_u[0]['remark'];
            $data[]=$v;
        }
        $xlsCell = array(
            array('box_mac','版位MAC'),
            array('box_name','版位名称'),
            array('room_name','包间名称'),
            array('hotel_name','酒楼名称'),
            array('area_name','地区'),
            array('maintainer_name','合作维护人'),
        );
        $xlsName = '正常互动屏版位明细';
        $filename = 'boxinteract';
        $this->exportExcel($xlsName, $xlsCell, $data,$filename);

    }
    public function  epBoxNetLog(){
        set_time_limit(36000);
        ini_set("memory_limit","8018M");
        $sql ="SELECT st.area_name,st.hotel_name,box.name box_name,st.box_mac, 
               case box.box_type 
                when 2 then '二代网络' 
                when 3 then '二代5G' 
                when 6 then '三代网络' 
                when 7 then '互联网电视' end  as box_type, 
               case st.`static_fj` when 1 then '午饭' when 2 then '晚饭' end as  static_fj,
               st.`avg_down_speed`,st.create_time,
               case box.is_4g when 0 then '否' when 1 then '是' end as is_4g
               FROM `savor_smallapp_statistics` st 
               left join savor_box box on st.box_mac = box.mac 
               WHERE st.`create_time`>'2021-03-01 00:00:00' and st.`create_time`<'2021-04-01 00:00:00' and st.avg_down_speed>0 and box.flag=0 and box.state=1 order by st.box_id asc,st.create_time asc ";
        $data = M()->query($sql);
        
        $xlsName = '版位测速明细';
        $filename = 'exportSl14BoxList';
        
        $xlsCell = array(
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','版位mac'),
            array('box_type','版位类型'),
            array('static_fj','饭点'),
            array('avg_down_speed','平均下载速度'),
            array('create_time','测速时间'),
            array('is_4g','是否4G')
            
        );
        $xlsName = '失联超过10天的版位信息';
        $filename = 'user_wifi_forscreen_detail';
        //$this->exportExcel($xlsName, $xlsCell, $data,$filename);
        $path  = '/application_data/web/php/savor_admin/Public/box_heart/202104/';
        if (!is_dir($path)){
            mkdir($path,0777,true);
        }
        $path  .= date('Ymd').'版位测速明细.xls';
      
        $ret = $this->exportExcel($xlsName, $xlsCell, $data,$filename,2,$path);
    }
}
