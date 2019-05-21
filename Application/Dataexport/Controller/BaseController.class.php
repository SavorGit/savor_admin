<?php
namespace Dataexport\Controller;
use Think\Controller;
/**
 * @desc 基础类，所有后台类必须继承此类
 *
 */
class BaseController extends Controller {

    public function __construct(){
        parent::__construct();
       
    }

    /**
     * 导出数据到Excel
     * $cell Excel表头
     * $data 数据
     * $title Excel标题
     * $filename 文件名
     * $type 1输出 2写入文件
     *
     */
    public function exportToExcel($cell,$data,$filename,$type=1){
        set_time_limit(360);
        ini_set("memory_limit","1024M");

        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $fileName = $filename.'_'.date('YmdHis');

        $cellNum = count($cell);
        $dataNum = count($data);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $cell[$i][1]);
        }
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $data[$i][$cell[$j][0]]);
            }
        }
        if($type==1){
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $fileName . '.xls"');
            header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }else{
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $file_path = "/Public/content/$fileName.xls";
            $file_rootpath =  SITE_TP_PATH.$file_path;
            $objWriter->save($file_rootpath);
            return $file_path;
        }
    }
    

    

}