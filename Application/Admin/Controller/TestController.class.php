<?php
namespace Admin\Controller;
use Think\Controller;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class TestController extends Controller {

    //http://www.a.com/admin/test/whoop

    public function getShortUrl() {

        $shortlink = array(
            '0'=>C('CONTENT_HOST').'content/4118.html?app=inner&channel=bigscpro&issq=1',
            '1'=>C('CONTENT_HOST').'content/3311.html?app=inner&channel=bigscpro&issq=1',
        );
        var_dump($shortlink);
        foreach($shortlink as $r=>$v) {
            $shortlink[$r] = urlencode($v);
        }
        var_dump($shortlink);
        foreach($shortlink as $v) {
            $shortlina[] = shortUrlAPI(1, $v);
        }
        var_export($shortlina);
    }

    public function whoop() {

        /*try {
            $this->division(10, 0);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }*/
        // 设置Whoops提供的错误和异常处理



        $this->division(10, 0);
    }

    function division($dividend, $divisor) {
        if($divisor == 0) {
            throw new \Exception('Division by zero');
        }
    }




    public function getarsize(){
        exit();
        $arModel = new \Admin\Model\ArticleModel();
        $m_arr = $arModel->field('id,media_id')->where(array('type'=>3))->select();

        //$m_arr = array_slice($m_arr,0,1);
       // var_dump($m_arr);

        $mediaModel = new \Admin\Model\MediaModel();
        try{
            foreach($m_arr as &$v){

                $me_info =  $mediaModel->find($v['media_id']);
                $oss_path = $me_info['oss_addr'];
                $size = $arModel->getOssSize($oss_path);
                $save['size'] = $size;
                //$save['id'] = $v['id'];
                //var_dump($save);
                if($v['id']==1787){
                    $bool = $arModel->where(array('id'=>$v['id']))->save($save);
                    var_dump($bool);
                    var_dump($v['id']);
                    break;
                }


            }
        }catch(Exception $e){
            echo $e->getMessage();
        }

    }

public function exportExcel($expTitle,$expCellName,$expTableData){
    vendor("PHPExcel.PHPExcel.IOFactory");
    vendor("PHPExcel.PHPExcel");
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['account'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
       
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
       // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }  
        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }
/**
     *
     * 导出Excel
     */
    function expUser(){//导出Excel
        $xlsName  = "User";
        $xlsCell  = array(
        array('id','账号序列'),
        array('username','名字'),
        array('tel','电话'),
         
        );
        $xlsModel = M('a1');
    
        $xlsData  = $xlsModel->Field('id,username,tel')->select();
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['sex']=$v['sex']==1?'男':'女';
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);
         
    }

    public function exportExcelbake($expTitle,$expCellName,$expTableData){

        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $filetmpname = APP_PATH.'../public/2.xls';
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }


        ob_end_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_clean();
        $objWriter->save('php://output');
        exit;
    }

    //导出excel
    public function exportExcelBAA($expTitle,$expCellName,$expTableData){
        ob_clean();
        include (__DIR__."/../../../framework/ThinkPHP/Library/Vendor/PHPExcel/PHPExcel.php");
        include (__DIR__."/../../../framework/ThinkPHP/Library/Vendor/PHPExcel/PHPExcel/IOFactory.php");
        $filetmpname = APP_PATH.'../public/2.xls';
        $objPHPExcel = new PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_clean();
        $objWriter->save('php://output');
        exit;
    }

    public function daochu(){
        $ma = M('a1');
        $expTableData = $ma->field('id,username,tel')->select();
        var_dump($expTableData);

        $expTitle = 'ppepr';
        $expCellName = array(
            array('id','账号'),
            array('username','用户名'),
            array('tel','电话'),
        );
        $this->exportExcel($expTitle,$expCellName,$expTableData);
    }

    public function compare(){
        $ma = M('a1');
        $a1_arr = $ma->select();
        $mb = M('a2');
        $a2_arr = $mb->select();
        $a1_a = array();
        $a2_a = array();
        foreach($a1_arr as $ak=>$av){
            $a1_a[] = $av['tel'];
        }
        foreach($a2_arr as $ak=>$av){
            $a2_a[] = $av['tel'];
        }
        $re_a = array_diff($a1_a,$a2_a);
        $map['tel'] = array('in', $re_a);
        $res = $ma->where($map)->select();
        //写入txt文档
        $filetmpname = APP_PATH.'../public/dao.txt';
        $handle=fopen($filetmpname,"a+");
        foreach($res as $rk=>$rv){
            //$uname = $rv['username'];
            $tel = $rv['tel'];
            $str = "$tel\n";
            fwrite($handle, $str);
        }
        fclose($handle);

      //  $expTitle = 'ppepr';
       // $this->exportExcel($expTitle,$expCellName,$expTableData)
    }


    
    public function testList() {
        //实例化redis
//         $redis = SavorRedis::getInstance();
//         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function daorubak(){
        vendor("PHPExcel.PHPExcel.IOFactory");
        $filetmpname = APP_PATH.'../public/2.xls';
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
        foreach($fieldarr as $v){
            $field[] = $v['field'];
        }
        array_shift($field);
        $field = array(
            0=>'tel',
            1=>'username',
        );
        var_dump($field);
        var_dump($arrExcel);
        //var_dump($arrExcel);
        foreach($arrExcel as $k=>$v){
            if($k == 1066){
                break;
            }
            $fields[] = array_combine($field,$v);//将excel的一行数据赋值给表的字段
        }
        // var_dump($fields);

        //批量插入
        if(!$ids = $m->addAll($fields)){
            //$this->error("没有添加数据");
            echo 'faile';
        }else{
            echo 'succes';
        }
        // $this->success('添加成功');
    }

    public function daoru(){
        vendor("PHPExcel.PHPExcel.IOFactory");
        $filetmpname = APP_PATH.'../public/little.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($filetmpname);
        $arrExceld = $objPHPExcel->getSheet(0)->toArray();
        var_export(array_slice($arrExceld,0,3));

        //$arrExceld = array_slice($arrExceld,0,3);
        $tmp = array();
        foreach($arrExceld as $k=>$v){
            $ard = trim($arrExceld[$k][0]);
            if(mb_strlen($ard)<2 || mb_strlen($ard)>15){
                continue;
            }else{
                if(in_array($ard,$tmp)) {
                    unset($arrExceld[$k]);
                    continue;
                }else{
                    $tmp[] = $ard;
                    $arrExcel[$k]['tagname'] = $ard;
                    $arrExcel[$k]['create_time'] = date("Y-m-d H:i:s");
                    $arrExcel[$k]['create_time'] = date("Y-m-d H:i:s");
                    $arrExcel[$k]['update_time'] = date("Y-m-d H:i:s");
                    $arrExcel[$k]['flag'] = 1;
                    $arrExcel[$k]['num'] = 0;
                }

            }

        }

        dump($arrExcel);


        //删除不要的表头部分，我的有三行不要的，删除三次
       // array_shift($arrExcel);
       // array_shift($arrExcel);
       // array_shift($arrExcel);//现在可以打印下$arrExcel，就是你想要的数组啦
      //  $arrExcel = array_slice($arrExcel,3,5);
        //查询数据库的字段
        $m = M('taglist');
        $fieldarr = $m->query("describe savor_taglist");

       // $req = $m->query("truncate savor_taglist");

        foreach($fieldarr as $v){
            $field[] = $v['field'];
        }
        array_shift($field);
        var_dump($field);

        $field = array(
            0=>'tagname',
            1=>'create_time',
            2=>'update_time',
            3=>'flag',
            4=>'num',
        );
        //var_dump($arrExcel);
        /*foreach($arrExcel as $k=>$v){
            if($k == 1066){
                break;
            }
            $fields[] = array_combine($field,$v);//将excel的一行数据赋值给表的字段
        }*/
       // var_dump($fields);

        //批量插入
        if(!$ids = $m->addAll($arrExcel)){
            //$this->error("没有添加数据");
            echo 'faile';
        }else{
            echo 'succes';
        }
       // $this->success('添加成功');
    }
    
    public function ueditior(){
        if(IS_POST){
            $res_param = json_encode($_POST);
            $this->output('操作成功!', 'test/testList');
        }else{
            $this->display();
        }
    }
    
    public function echarts(){
        $this->display();
    }
    
    public function demodata(){
        $type = I('type',1);
        $shw_module = I('shw_module',0);
        if($type ==1){
            if($shw_module==0){
                $res = array ( 'date' => array ( 0 => '00:00', 1 => '01:00', 2 => '02:00', 3 => '03:00', 4 => '04:00', 5 => '05:00', 6 => '06:00', 7 => '07:00', 8 => '08:00',
                     9 => '09:00', 10 => '10:00', 11 => '11:00', 12 => '12:00', 13 => '13:00', 14 => '14:00', 
                    15 => '15:00', 16 => '16:00', 17 => '17:00', 18 => '18:00', 19 => '19:00', 20 => '20:00', 21 => '21:00', 22 => '22:00', 23 => '23:00', 24 => '23:59', ), 
                    'pageviews' => array ( 0 => '0', 1 => '0', 2 => '0', 3 => '0', 4 => '1', 5 => '0', 6 => '0', 7 => '0', 8 => '0', 9 => '1', 10 => '3', 11 => '1', 12 => '0', 
                        13 => '0', 14 => '0', 15 => '1', 16 => '0', 17 => '1', 18 => '1', 19 => '1', 20 => '0', 21 => '0', 22 => '4', 23 => '0', 24 => '0', ), 
                    'visitors' => array ( 0 => '0', 1 => '0', 2 => '0', 3 => '0', 4 => '1', 5 => '0', 6 => '0', 7 => '0', 8 => '0', 9 => '1', 10 => '3', 11 => '1', 12 => '0',
                         13 => '0', 14 => '0', 15 => '1', 16 => '0', 17 => '1', 18 => '1', 19 => '1', 20 => '0', 21 => '0', 22 => '1', 23 => '0', 24 => '0', ), );
            }else{
                $res = array (
                      'pvlist' => 
                      array (),
                      'entrancelist' => 
                      array (),
                      'sourcelist' => array(),
                      'visitors' => array (array (0,0)),
                      'pageviews' => 
                      array (array (0 => 0,1 => 0,)),
                    );
            }
        }
        echo json_encode($res);
        exit;
    }
    
    public function locationdata(){
        $res = array ( 0 => array ( 'name' => '北京', 'value' => 9, 'rate' => 64, ), 1 => array ( 'name' => '天津', 'value' => 0, 'rate' => 0, ), 
            2 => array ( 'name' => '上海', 'value' => 0, 'rate' => 0, ), 3 => array ( 'name' => '重庆', 'value' => 0, 'rate' => 0, ), 
            4 => array ( 'name' => '河北', 'value' => 0, 'rate' => 0, ), 
            5 => array ( 'name' => '河南', 'value' => 0, 'rate' => 0, ), 6 => array ( 'name' => '云南', 'value' => 0, 'rate' => 0, ), 
            7 => array ( 'name' => '辽宁', 'value' => 0, 'rate' => 0, ), 8 => array ( 'name' => '黑龙江', 'value' => 0, 'rate' => 0, ), 
            9 => array ( 'name' => '湖南', 'value' => 0, 'rate' => 0, ), 10 => array ( 'name' => '安徽', 'value' => 0, 'rate' => 0, ), 
            11 => array ( 'name' => '山东', 'value' => 0, 'rate' => 0, ), 12 => array ( 'name' => '新疆', 'value' => 0, 'rate' => 0, ), 
            13 => array ( 'name' => '江苏', 'value' => 0, 'rate' => 0, ), 14 => array ( 'name' => '浙江', 'value' => 0, 'rate' => 0, ), 
            15 => array ( 'name' => '江西', 'value' => 0, 'rate' => 0, ), 16 => array ( 'name' => '湖北', 'value' => 0, 'rate' => 0, ), 
            17 => array ( 'name' => '广西', 'value' => 0, 'rate' => 0, ), 18 => array ( 'name' => '甘肃', 'value' => 0, 'rate' => 0, ), 
            19 => array ( 'name' => '山西', 'value' => 0, 'rate' => 0, ), 20 => array ( 'name' => '内蒙古', 'value' => 0, 'rate' => 0, ), 
            21 => array ( 'name' => '陕西', 'value' => 0, 'rate' => 0, ), 22 => array ( 'name' => '吉林', 'value' => 0, 'rate' => 0, ), 
            23 => array ( 'name' => '福建', 'value' => 0, 'rate' => 0, ), 24 => array ( 'name' => '贵州', 'value' => 0, 'rate' => 0, ), 
            25 => array ( 'name' => '广东', 'value' => 0, 'rate' => 0, ), 26 => array ( 'name' => '青海', 'value' => 0, 'rate' => 0, ), 
            27 => array ( 'name' => '西藏', 'value' => 0, 'rate' => 0, ), 28 => array ( 'name' => '四川', 'value' => 0, 'rate' => 0, ), 
            29 => array ( 'name' => '宁夏', 'value' => 0, 'rate' => 0, ), 30 => array ( 'name' => '海南', 'value' => 0, 'rate' => 0, ), 
            31 => array ( 'name' => '台湾', 'value' => 0, 'rate' => 0, ), 32 => array ( 'name' => '香港', 'value' => 0, 'rate' => 0, ), 
            33 => array ( 'name' => '澳门', 'value' => 0, 'rate' => 0, ), 34 => array ( 'name' => '南海诸岛', 'value' => 0, 'rate' => 0, ), );
        echo json_encode($res);
    }
}