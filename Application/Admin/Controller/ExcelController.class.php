<?php
namespace Admin\Controller;

use Think\Controller;

// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class ExcelController extends Controller
{

    public function exportExcel($expTitle, $expCellName, $expTableData,$filename)
    {
        set_time_limit(90);
        ini_set("memory_limit", "512M");
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        if($filename == 'hotel') {
            $tmpname = '酒楼资源总表';
        } else if ($filename == 'boxlostreport') {
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
            $tmpname = '洗牙卡订单';
        }else if($filename == 'contentads'){
            $tmpname = '内容与广告统计';
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
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
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


    function hotelinfo()
    {//导出Excel
        $boxModel = new \Admin\Model\BoxModel();
        //获取所有数据
        $box_arr = $boxModel->getExNum();
        $filename = 'hotel';
        $xlsName = "User";
        $xlsCell = array(
            array('id', '酒楼id'),
            array('install_date', '安装日期'),
            array('tsta', '电视状态'),
            array('mac', '机顶盒mac地址'),
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
        $infos = $m_activity_data->getInfo('*','',' add_time desc','',2);
        $xlsCell = array(
            array('id', 'id'),
            array('receiver', '收货人'),
            array('mobile', '电话'),
            array('address', '收货地址'),
            array('add_time', '下单时间'),
            array('sourceid','来源')
        );
        $activity_source_arr = C('ACTIVITY_SOURCE_ARR');
        foreach($infos as $key=>$v){
            $infos[$key]['sourceid'] = $activity_source_arr[$v['sourceid']];
        }
        $xlsName = '洗牙卡订单';
        $filename = 'toothwash';
        $this->exportExcel($xlsName, $xlsCell, $infos,$filename);
    }
}