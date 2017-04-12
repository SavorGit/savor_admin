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
        }

        $fileName = $tmpname . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
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
            
            array('dct', '下载量次'),
            
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
            array('count', '下载量次'),

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

    function hotelinfo()
    {//导出Excel
        $boxModel = new \Admin\Model\BoxModel();
        //获取所有数据
        $box_arr = $boxModel->getExNum();
        $filename = 'hotel';
        $xlsName = "User";
        $xlsCell = array(
            array('install_date', '安装日期'),
            array('hsta', '酒店状态'),
            array('rsta', '版位状态即包间状态'),
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

}