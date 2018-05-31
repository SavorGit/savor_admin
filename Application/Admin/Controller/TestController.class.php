<?php
namespace Admin\Controller;

use Think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Common\Lib\SavorRedis;


// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class TestController extends Controller {
    
    public function zyt(){
        exit('ok');
        /*$redis = SavorRedis::getInstance();
        $redis->select(12);
        $keys = $redis->keys('program_ads_*');
        $flag =0;
        foreach($keys as $key=>$v){
            $redis->remove($v);
            $flag++;
        }
        echo $flag;
        exit(ok);*/
        
        
        $redis = SavorRedis::getInstance();
        $redis->select(13);
        
        $hotel_id = 7;
        $menuid = 203;
        $procache_key = 'udriverpan_pro_'.$menuid;
        $adscache_key = 'udriverpan_ads_'.$menuid;
        $menuhotelModel = new \Admin\Model\MenuHotelModel();
        $adsModel = new \Admin\Model\AdsModel();
        $pro_arr = $adsModel->getproInfo($menuid);
        $pro_arr = $this->changeadvList($pro_arr,1);
        $ads_arr = $redis->get($adscache_key);
        if($ads_arr) {
            $ads_arr = json_decode($ads_arr, true);
            $ads_arr = $this->changeadvList($ads_arr,2);
        } else {
            $ads_arr = $adsModel->getadsInfo($menuid);
            $redis->set($adscache_key , json_encode($ads_arr), 120);
            $ads_arr = $this->changeadvList($ads_arr,2);
        }
        
        
        
        $adv_arr = $adsModel->getupanadvInfo($hotel_id, $menuid);
        $adv_arr = $this->changupaneadvList($adv_arr,1);
        $result['play_list'] = array_merge($pro_arr,
            $ads_arr,$adv_arr);
        echo json_encode($result);exit;
    }
    private function changeadvList($res,$type){
        if($res){
            foreach ($res as $vk=>$val) {
                if($type==1){
                    $res[$vk]['order'] =  $res[$vk]['sortnum'];
                    unset($res[$vk]['sortnum']);
                }
    
                if(!empty($val['name'])){
                    $ttp = explode('/', $val['name']);
                    $res[$vk]['name'] = $ttp[2];
                }
            }
    
        }
        return $res;
        //如果是空
    }
    private function changupaneadvList($res,$type){
        if($res){
            foreach ($res as $vk=>$val) {
                if($type==1){
                    $res[$vk]['order'] =  $res[$vk]['sortnum'];
                    unset($res[$vk]['sortnum']);
                }
    
                if(!empty($val['name'])){
                    $ttp = explode('/', $val['name']);
                    $res[$vk]['name'] = $ttp[2];
                }else{
                    unset($res[$vk]);
                }
            }
    
        }
        return $res;
        //如果是空
    }
    
    public function testemail(){


        $pat = APP_PATH;
        require APP_PATH.'Common/Lib/PHPMailer/src/Exception.php';
        require APP_PATH.'Common/Lib/PHPMailer/src/PHPMailer.php';
        require APP_PATH.'Common/Lib/PHPMailer/src/SMTP.php';
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
           /* $mail->SMTPDebug = 2;
            $mail->CharSet = "UTF-8";// Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.littlehotspot.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'sysreport@littlehotspot.com';                 // SMTP username
            $mail->Password = 'savor123456';                           // SMTP password
            //$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 25;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('sysreport@littlehotspot.com', 'savor');
            $mail->addAddress('zhang.yingtao@savor.cn', 'zhangyingtao');     // Add a recipient
            // $mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';*/
            $address ="bai.yutao@littlehotspot.com";
            $mail->IsSMTP(); // 使用SMTP方式发送
            $mail->Host = "smtp.littlehotspot.com"; // 您的企业邮局域名
            $mail->SMTPAuth = true; // 启用SMTP验证功能
            $mail->Username = "zhang.yingtao@savor.cn"; // 邮局用户名(请填写完整的email地址)
            $mail->SMTPSecure = 'ssl';
            $mail->Password = "z3583290"; // 邮局密码
            $mail->Port=465;
            $mail->From = "zhang.yingtao@savor.cn"; //邮件发送者email地址
            $mail->FromName = "在线Q聊";
            $mail->AddAddress("$address", "小热点");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
//$mail->AddReplyTo("", "");

//$mail->AddAttachment("./aa.xls"); // 添加附件
            $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->send();
            echo 'Message has been sent';
        }  catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }


    public function testema(){


        $pat = APP_PATH;
        require APP_PATH.'Common/Lib/PHPMailer/src/Exception.php';
        require APP_PATH.'Common/Lib/PHPMailer/src/PHPMailer.php';
        require APP_PATH.'Common/Lib/PHPMailer/src/SMTP.php';
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.163.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'xiaobaig0407@163.com';                 // SMTP username
            $mail->Password = '123456x';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 25;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('xiaobaig0407@163.com', '163');
            $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
           // $mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Attachments
           // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
           // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }


    //http://www.a.com/admin/test/whoop
   //http://admin.littlehotspot.com/content/4305.html?app=inner&preview=1
    //http://admin.rerdian.com:80/test/testweixin?id=4305.html&app=inner&issq=1&code=001YLDEd1UlmWu0qJiGd1RRnEd1YLDEB&state=wxsq001
    public function testweixin(){
        var_dump('http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]);
        $is_wx = checkWxbrowser();
        if($is_wx){
            $issq =  I('issq',0,'intval');
            $url = "http://admin.littlehotspot.com/test/testweixin?id=4305.html&app=inner";
            if(!empty($issq)){
                $url .='&issq=1';
                $id = 4305;
                $this->wxAuthorLog($url, $id);
            }
            echo 'wlerlwer';


        }
    }


    public function wxAuthorLog($url,$contentid){

        //$url = 'http://devp.admin.littlehotspot.com/content/2785.html?app=inner';
        var_dump($url);
        $m_weixin_api = new \Common\Lib\Weixin_api();
        //微信授权登录开始
        $state = I('state','wxsq001','trim') ;
        $code = I('code');
        //$issq = I('issq',1,'intval');
        $iswx = checkWxbrowser();

        if($iswx==1){
            $redirect_url = urlencode($url);

            $host_name = C('CONTENT_HOST');
            $jumpUrl = $host_name.'admin/wxapply/index?scope=1&redirect_url='.$redirect_url;
            if (!$code || $state!='wxsq001') {
                header("Location:".$jumpUrl);
                exit;
            }
            $result = $m_weixin_api->getWxOpenid($code,$url);
            $openid = $result['openid'];
            $wxUserinfo = $m_weixin_api->getWxUserInfo($result['access_token'],$openid);
            $share_url ='http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            var_dump($share_url);
            var_dump($wxUserinfo);
DIE;
            $wxUserinfo['nickname'] = base64_encode($wxUserinfo['nickname']);
        }
    }

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
    public function rtbTag(){
        vendor("PHPExcel.PHPExcel.IOFactory");
        $path = 'D:\wamp\www\savor_admin/Public/uploads/2018-01-09/7.xls';
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($type == 'xlsx' || $type == 'xls') {
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
        } elseif ($type == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
            ->setDelimiter(',')
            ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
            ->setEnclosure('"')
            ->setLineEnding("\r\n")
            ->setSheetIndex(0);
            $objPHPExcel = $objReader->load($path);
        } else {
            $this->error('上传文件不能为空');
            //$this->output('文件格式不正确', 'importdata', 0, 0);
        }
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        // var_dump($highestRowNum, $highestColumn, $highestColumnNum);
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        //echo $highestColumnNum;exit;
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';    
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
        
        $data = array();
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = $sheet->getCell($cellName)->getValue();
                $row[$filed[$j]] = $cellVal;
            }
            $data [] = $row;
        }
        //print_r($data);exit;
        $m_rtbtaglist = new \Admin\Model\RtbTagListModel();
        $m_rtbtaglist->addAll($data);
    }

    public function rtbtagarray(){
        $arr = array ( 0 => array ( 'tagname' => '品牌手表', 'tag_code' => 'I04010101', ), 1 => array ( 'tagname' => '珠宝饰品', 'tag_code' => 'I04010102', ), 2 => array ( 'tagname' => '服饰鞋帽', 'tag_code' => 'I040102', ), 3 => array ( 'tagname' => '女装', 'tag_code' => 'I04010201', ), 4 => array ( 'tagname' => '女鞋', 'tag_code' => 'I04010202', ), 5 => array ( 'tagname' => '男装', 'tag_code' => 'I04010203', ), 6 => array ( 'tagname' => '男鞋', 'tag_code' => 'I04010204', ), 7 => array ( 'tagname' => '运动户外', 'tag_code' => 'I04010205', ), 8 => array ( 'tagname' => '内衣', 'tag_code' => 'I04010206', ), 9 => array ( 'tagname' => '家居服装', 'tag_code' => 'I04010207', ), 10 => array ( 'tagname' => '配饰', 'tag_code' => 'I04010208', ), 11 => array ( 'tagname' => '童装', 'tag_code' => 'I04010209', ), 12 => array ( 'tagname' => '童鞋', 'tag_code' => 'I04010210', ), 13 => array ( 'tagname' => '集合店', 'tag_code' => 'I04010211', ), 14 => array ( 'tagname' => '家居厨具', 'tag_code' => 'I040103', ), 15 => array ( 'tagname' => '床上用品', 'tag_code' => 'I04010301', ), 16 => array ( 'tagname' => '家居装饰', 'tag_code' => 'I04010302', ), 17 => array ( 'tagname' => '卫浴产品', 'tag_code' => 'I04010303', ), 18 => array ( 'tagname' => '装修建材', 'tag_code' => 'I04010304', ), 19 => array ( 'tagname' => '家具', 'tag_code' => 'I04010305', ), 20 => array ( 'tagname' => '厨具', 'tag_code' => 'I04010306', ), 21 => array ( 'tagname' => '餐具', 'tag_code' => 'I04010307', ), 22 => array ( 'tagname' => '茶具杯具', 'tag_code' => 'I04010308', ), 23 => array ( 'tagname' => '洗涤用品', 'tag_code' => 'I04010309', ), 24 => array ( 'tagname' => '运动健康', 'tag_code' => 'I040104', ), 25 => array ( 'tagname' => '户外装备', 'tag_code' => 'I04010401', ), 26 => array ( 'tagname' => '游泳用品', 'tag_code' => 'I04010402', ), 27 => array ( 'tagname' => '健身器材', 'tag_code' => 'I04010403', ), 28 => array ( 'tagname' => '骑行运动', 'tag_code' => 'I04010404', ), 29 => array ( 'tagname' => '垂钓用品', 'tag_code' => 'I04010405', ), 30 => array ( 'tagname' => '舞蹈', 'tag_code' => 'I04010406', ), 31 => array ( 'tagname' => '箱包', 'tag_code' => 'I040105', ), 32 => array ( 'tagname' => '女包', 'tag_code' => 'I04010501', ), 33 => array ( 'tagname' => '男包', 'tag_code' => 'I04010502', ), 34 => array ( 'tagname' => '钱包', 'tag_code' => 'I04010503', ), 35 => array ( 'tagname' => '功能包', 'tag_code' => 'I04010504', ), 36 => array ( 'tagname' => '旅行箱', 'tag_code' => 'I04010505', ), 37 => array ( 'tagname' => '母婴用品', 'tag_code' => 'I040106', ), 38 => array ( 'tagname' => '婴儿服饰', 'tag_code' => 'I04010601', ), 39 => array ( 'tagname' => '孕妈用品', 'tag_code' => 'I04010602', ), 40 => array ( 'tagname' => '婴儿食品', 'tag_code' => 'I04010603', ), 41 => array ( 'tagname' => '婴儿护理', 'tag_code' => 'I04010604', ), 42 => array ( 'tagname' => '童车床', 'tag_code' => 'I04010605', ), 43 => array ( 'tagname' => '婴幼家纺', 'tag_code' => 'I04010606', ), 44 => array ( 'tagname' => '数码', 'tag_code' => 'I040107', ), 45 => array ( 'tagname' => '相机', 'tag_code' => 'I04010701', ), 46 => array ( 'tagname' => '手机', 'tag_code' => 'I04010702', ), 47 => array ( 'tagname' => '数码配件', 'tag_code' => 'I04010703', ), 48 => array ( 'tagname' => '电脑', 'tag_code' => 'I04010704', ), 49 => array ( 'tagname' => '耳机音响', 'tag_code' => 'I04010705', ), 50 => array ( 'tagname' => '文化教育', 'tag_code' => 'I040108', ), 51 => array ( 'tagname' => '办公文具', 'tag_code' => 'I04010801', ), 52 => array ( 'tagname' => '工艺收藏', 'tag_code' => 'I04010802', ), 53 => array ( 'tagname' => '儿童玩具', 'tag_code' => 'I04010803', ), 54 => array ( 'tagname' => '教育培训', 'tag_code' => 'I04010804', ), 55 => array ( 'tagname' => '体育用品', 'tag_code' => 'I04010805', ), 56 => array ( 'tagname' => '乐器04010807', 'tag_code' => 'I04010806', ), 57 => array ( 'tagname' => '乐器行', 'tag_code' => 'I04010808', ), 58 => array ( 'tagname' => '化妆品', 'tag_code' => 'I040109', ), 59 => array ( 'tagname' => '香氛精油', 'tag_code' => 'I04010901', ), 60 => array ( 'tagname' => '彩妆', 'tag_code' => 'I04010902', ), 61 => array ( 'tagname' => '护肤', 'tag_code' => 'I04010903', ), 62 => array ( 'tagname' => '假发', 'tag_code' => 'I04010904', ), 63 => array ( 'tagname' => '餐饮', 'tag_code' => 'I040110', ), 64 => array ( 'tagname' => '快餐简餐', 'tag_code' => 'I04011001', ), 65 => array ( 'tagname' => '日料', 'tag_code' => 'I04011002', ), 66 => array ( 'tagname' => '西餐正餐', 'tag_code' => 'I04011003', ), 67 => array ( 'tagname' => '咖啡水吧', 'tag_code' => 'I04011004', ), 68 => array ( 'tagname' => '零食小吃', 'tag_code' => 'I04011005', ), 69 => array ( 'tagname' => '韩食', 'tag_code' => 'I04011006', ), 70 => array ( 'tagname' => '中餐正餐', 'tag_code' => 'I04011007', ), 71 => array ( 'tagname' => '面包甜点', 'tag_code' => 'I04011008', ), 72 => array ( 'tagname' => '火锅涮锅', 'tag_code' => 'I04011009', ), 73 => array ( 'tagname' => '自助餐', 'tag_code' => 'I04011010', ), 74 => array ( 'tagname' => '茶座', 'tag_code' => 'I04011013', ), 75 => array ( 'tagname' => '家用电器', 'tag_code' => 'I040111', ), 76 => array ( 'tagname' => '小家电', 'tag_code' => 'I04011101', ), 77 => array ( 'tagname' => '厨房电器', 'tag_code' => 'I04011102', ), 78 => array ( 'tagname' => '大家电', 'tag_code' => 'I04011103', ), 79 => array ( 'tagname' => '休闲娱乐', 'tag_code' => 'I040112', ), 80 => array ( 'tagname' => '儿童娱乐', 'tag_code' => 'I04011201', ), 81 => array ( 'tagname' => 'KTV服务', 'tag_code' => 'I04011206', ), 82 => array ( 'tagname' => '桌游娱乐', 'tag_code' => 'I04011207', ), 83 => array ( 'tagname' => '台球室', 'tag_code' => 'I04011212', ), 84 => array ( 'tagname' => '网吧', 'tag_code' => 'I04011214', ), 85 => array ( 'tagname' => '电影院', 'tag_code' => 'I04011215', ), 86 => array ( 'tagname' => '足疗按摩', 'tag_code' => 'I04011216', ), 87 => array ( 'tagname' => '体验/DIY', 'tag_code' => 'I04011217', ), 88 => array ( 'tagname' => '洗浴', 'tag_code' => 'I04011218', ), 89 => array ( 'tagname' => '生活服务', 'tag_code' => 'I040113', ), 90 => array ( 'tagname' => '摄影冲印', 'tag_code' => 'I04011307', ), 91 => array ( 'tagname' => '花店/水果铺', 'tag_code' => 'I04011310', ), 92 => array ( 'tagname' => '宠物服务', 'tag_code' => 'I04011312', ), 93 => array ( 'tagname' => '药店', 'tag_code' => 'I04011315', ), 94 => array ( 'tagname' => '租车服务', 'tag_code' => 'I04011316', ), 95 => array ( 'tagname' => '保健品', 'tag_code' => 'I04011317', ), 96 => array ( 'tagname' => '皮具保养', 'tag_code' => 'I04011320', ), 97 => array ( 'tagname' => '图文快印', 'tag_code' => 'I04011321', ), 98 => array ( 'tagname' => '邮局', 'tag_code' => 'I04011323', ), 99 => array ( 'tagname' => '旅行社', 'tag_code' => 'I04011324', ), 100 => array ( 'tagname' => '个性写真', 'tag_code' => 'I04011325', ), 101 => array ( 'tagname' => '眼镜店', 'tag_code' => 'I04011326', ), 102 => array ( 'tagname' => '快递公司', 'tag_code' => 'I04011327', ), 103 => array ( 'tagname' => '零售卖场', 'tag_code' => 'I040114', ), 104 => array ( 'tagname' => '烟酒行', 'tag_code' => 'I04011401', ), 105 => array ( 'tagname' => '杂货', 'tag_code' => 'I04011402', ), 106 => array ( 'tagname' => '便利店', 'tag_code' => 'I04011403', ), 107 => array ( 'tagname' => '数码卖场', 'tag_code' => 'I04011404', ), 108 => array ( 'tagname' => '零售', 'tag_code' => 'I04011405', ), 109 => array ( 'tagname' => '大型超市', 'tag_code' => 'I04011406', ), 110 => array ( 'tagname' => '茶叶店', 'tag_code' => 'I04011407', ), 111 => array ( 'tagname' => '家电卖场', 'tag_code' => 'I04011408', ), 112 => array ( 'tagname' => '超市', 'tag_code' => 'I04011409', ), 113 => array ( 'tagname' => '购物中心04011411', 'tag_code' => 'I04011410', ), 114 => array ( 'tagname' => '汽车服务', 'tag_code' => 'I040115', ), 115 => array ( 'tagname' => '汽车4S店', 'tag_code' => 'I04011501', ), 116 => array ( 'tagname' => '汽车装饰', 'tag_code' => 'I04011502', ), 117 => array ( 'tagname' => '汽车品牌', 'tag_code' => 'I04011505', ), 118 => array ( 'tagname' => '汽车配件', 'tag_code' => 'I04011506', ), 119 => array ( 'tagname' => '汽车维修', 'tag_code' => 'I04011508', ), 120 => array ( 'tagname' => '医疗', 'tag_code' => 'I040117', ), 121 => array ( 'tagname' => '医疗器械', 'tag_code' => 'I04011701', ), 122 => array ( 'tagname' => '中医馆', 'tag_code' => 'I04011702', ), 123 => array ( 'tagname' => '结婚', 'tag_code' => 'I040118', ), 124 => array ( 'tagname' => '丽人', 'tag_code' => 'I040119', ), 125 => array ( 'tagname' => '美发', 'tag_code' => 'I04011901', ), 126 => array ( 'tagname' => '瘦身纤体', 'tag_code' => 'I04011902', ), 127 => array ( 'tagname' => '美甲', 'tag_code' => 'I04011903', ), 128 => array ( 'tagname' => '金融', 'tag_code' => 'I040120', ), 129 => array ( 'tagname' => '典当行', 'tag_code' => 'I04012001', ), 130 => array ( 'tagname' => '证券公司', 'tag_code' => 'I04012002', ), 131 => array ( 'tagname' => '亲子', 'tag_code' => 'I040121', ), 132 => array ( 'tagname' => '早教', 'tag_code' => 'I04012101', ), 133 => array ( 'tagname' => '亲子教育', 'tag_code' => 'I04012102', ), 134 => array ( 'tagname' => '月子中心', 'tag_code' => 'I04012103', ), 135 => array ( 'tagname' => '亲子游乐', 'tag_code' => 'I04012104', ), 136 => array ( 'tagname' => '培训机构', 'tag_code' => 'I040122', ), 137 => array ( 'tagname' => '考试培训', 'tag_code' => 'I04012201', ), 138 => array ( 'tagname' => '语言培训', 'tag_code' => 'I04012202', ), 139 => array ( 'tagname' => '时尚品牌', 'tag_code' => 'I040201', ), 140 => array ( 'tagname' => '高端品牌', 'tag_code' => 'I040202', ), 141 => array ( 'tagname' => '大众品牌', 'tag_code' => 'I040203', ), 142 => array ( 'tagname' => '奢侈品牌', 'tag_code' => 'I040204', ), );
        $rtbtagModel = new \Admin\Model\RtbTagListModel();
        $data = array();
        foreach($arr as $ak=>$av) {
            $map['pid'] = 2;
            $map['tag_code'] = array('like', '%'.substr($av['tag_code'],0,5).'%');
            $rts = $rtbtagModel->where($map)->field('tag_code,id')->find();
            //var_export($rts);
            $data['pid'] = $rts['id'];
            $data['tagname']  = $av['tagname'];
            $data['tag_code'] = $av['tag_code'];
            //$rtbtagModel->add($data);
        }
    }

    public function testmaintner(){
        //获取所有酒楼
        $hotelModel = new \Admin\Model\HotelModel();
        $map['a.flag'] = 0;
        $field = 'a.id, a.name, a.maintainer ma, b.maintainer_id maid,a.area_id';
        $hotel_info = $hotelModel->getHotelLists($map,'','',$field);
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id uid,user.remark ';
        $map = array();
        $map['state']   = 1;
        $map['role_id']   = 1;
        $user_info = $m_opuser_role->getAllRole($fields,$map,'' );
        $u_arr = array();
        foreach($user_info as $uv) {
            $u_arr[$uv['uid']] = trim($uv['remark']);
        }
        //var_export($u_arr);
        $hext = new \Admin\Model\HotelExtModel();
        $hare_model = new \Admin\Model\AreaModel();
        foreach($hotel_info as $hk=>$hv) {
            //获取酒楼城市
            $ar_info = $hare_model->find($hv['area_id']);
            $hotel_info[$hk]['city'] = $ar_info['region_name'];
            $hid = $hv['id'];
            $main_t = $hv['ma'];
            $main_id = $hv['maid'];
            if($main_id) {
                $hotel_info[$hk]['st'] = '关联成功';
            } else {
                $rel_uid = array_search(trim($main_t), $u_arr);
                if($rel_uid) {
                    //更新数据库
                    $map = array();
                    $save = array();
                    $map['hotel_id'] = $hid;
                    $save['maintainer_id'] = $rel_uid;
                    /*$sql = "update savor_hotel_ext set maintainer_id=$rel_uid where hotel_id=$hid";*/
                    //echo $sql;
                    //echo "<br/><br/>";
                   // $hext->query($sql);
                    $hext->saveData($save, $map);
                    $hotel_info[$hk]['st'] = '关联成功';
                } else {
                    $hotel_info[$hk]['st'] = '关联失败';
                }
            }

        }

        $xlsCell = array(
            array('id', '酒楼ID'),
            array('name', '酒楼名称'),
            array('city', '酒楼城市'),
            array('ma', '合作维护人'),
            array('st','关联状态'),
        );
        ob_clean();
        $xlsName = '酒楼关联合作维护人';
        $filename = 'exphotelmaintain';
        $excel = new \Admin\Controller\ExcelController();
        $excel->exportExcel($xlsName, $xlsCell, $hotel_info,$filename);
    }
    function getTaskNums(){
        $sql ="select a.*,b.remark from savor_opuser_role a
               left join savor_sysuser b on a.user_id=b.id
               where a.state=1 and a.role_id=1";
        $data = M()->query($sql);
        $result = array();
        foreach($data as $key=>$v){
            $sql ="select count(id) as nums from savor_option_task where publish_user_id=".$v['user_id']." and flag=0";
            $ret = M()->query($sql);
            echo  $v['remark'].":".$ret[0]['nums'];
            echo "<br>";
        }
        echo "aaa";exit;
        print_r($result);exit;
        
        
    }
}