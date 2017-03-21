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
        $box_arr = session('boxlostreport');
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