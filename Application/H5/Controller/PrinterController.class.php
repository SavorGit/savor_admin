<?php
namespace H5\Controller;
use Common\Lib\AliyunMsn;
use Think\Controller;
use Common\Lib\Aliyun;
use Common\Lib\Qrcode;

class PrinterController extends Controller {

    public $cache_key = 'cronscript:printer:qrcode';
    public $oss_host = 'https://oss.littlehotspot.com/';
    public $oss_code_path = 'qrcode/goods/';
    public $num = 100;
    public $qtypes = array(
        16=>array('name'=>'1带6二维码图','num'=>6,'codesize'=>array('big'=>20,'small'=>11),
            'template_img'=>'template6.jpg',
            'big_image_position'=>'g_east,x_200,y_40',
            'image_position'=>array('g_nw,x_70,y_30','g_west,x_70,y_30','g_sw,x_70,y_30',
            'g_nw,x_360,y_30','g_west,x_360,y_30','g_sw,x_360,y_30')
        ),
        14=>array('name'=>'1带4二维码图','num'=>4,'codesize'=>array(),
            'template_img'=>'',
            'image_position'=>array()
        ),
        12=>array('name'=>'1带2二维码图','num'=>2,'codesize'=>array(),
            'template_img'=>'',
            'image_position'=>array()
        )
        );


    public function qrcode(){
        $qtype = I('get.qtype',0,'intval');//类型16 1带6二维码图,14 1带4二维码,12 1带2二维码
        $num = I('get.num',0,'intval');//生成二维码数量
        $sign = I('get.sign');

        if($sign!=$this->get_sign()){
            $this->output('params error');
        }
        if(!isset($this->qtypes[$qtype])){
            $this->output('请输入正确的类型');
        }
        if($num>$this->num){
            $this->output("请输入小于{$this->num}的二维码数量");
        }

        $cache_key = $this->cache_key;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            $res_cache = json_decode($res,true);
            $all_status = array('1'=>'生成数据中','2'=>'生成二维码中','3'=>'生成主图中','4'=>'生成完毕');
            $msg = $all_status[$res_cache['status']];
            if($res_cache['status']==4){
                $diff_time = $res_cache['etime']-$res_cache['stime'];
                $msg = $msg.'，耗时：'.$diff_time.'秒';
                print_r($res_cache['img_urls']);
            }
            $this->output($msg);
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php h5/printer/qrcodescript/sign/$sign/qtype/$qtype/num/$num > /tmp/null &";
            system($shell);
            $cache_data = array('status'=>1,'stime'=>time());
            $redis->set($cache_key,json_encode($cache_data),3600);
            $this->output('开始生成');
        }
    }

    public function qrcodescript(){
        $qtype = I('get.qtype',0,'intval');//类型16 1带6二维码图,14 1带4二维码,12 1带2二维码
        $num = I('get.num',0,'intval');//生成二维码数量
        $sign = I('get.sign');
        if($sign!=$this->get_sign()){
            $this->output('params error');
        }
        if(!isset($this->qtypes[$qtype])){
            $this->output('请输入正确的类型');
        }
        if($num>$this->num){
            $this->output("请输入小于{$this->num}的二维码数量");
        }

        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = 'oss-cn-beijing.aliyuncs.com';
        $bucket = C('OSS_BUCKET');
        $aliyunoss = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
        $aliyunoss->setBucket($bucket);
        $errorCorrectionLevel = 'L';//容错级别

        $qrcode_create_path = SITE_TP_PATH.'/Public/uploads/qrcode/';
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $cache_key = $this->cache_key;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res_cache = $redis->get($cache_key);
        $cache_data = json_decode($res_cache,true);

        for ($i=1;$i<=$num;$i++){
            $pdata = array('status'=>1,'type'=>1);
            $parent_id = $m_qrcode_content->add($pdata);
            $all_sdata = array();
            for($si=1;$si<=$this->qtypes[$qtype]['num'];$si++){
                $sdata = array('parent_id'=>$parent_id,'status'=>1,'type'=>2);
                $all_sdata[]=$sdata;
            }
            $m_qrcode_content->addAll($all_sdata);
        }
        $cache_data['status'] = 2;
        $redis->set($cache_key,json_encode($cache_data),3600);

        $res_datas = $m_qrcode_content->getDataList('*',array('status'=>1),'id asc');
        $qrcode_contents = array();
        $content_ids = array();
        foreach ($res_datas as $v){
            $content_ids[]=$v['id'];
            $content = $v['id'];
            if($v['type']==1){
                $qrcode_size = $this->qtypes[$qtype]['codesize']['big'];
            }else{
                $qrcode_size = $this->qtypes[$qtype]['codesize']['small'];
                $qrcode_contents[$v['parent_id']][]=$v['id'];
            }
            $file_path = $qrcode_create_path."$content.png";//本地文件路径
            $qrcontent = encrypt_data($content);
            Qrcode::png($qrcontent,$file_path,$errorCorrectionLevel, $qrcode_size, 0);

            $file_name = $this->oss_code_path."$content.png";
            $res_upinfo = $aliyunoss->uploadFile($file_name, $file_path);
            if(empty($res_upinfo['info']['url'])){
                $aliyunoss->uploadFile($file_name, $file_path);
            }
        }

        $cache_data['status'] = 3;
        $redis->set($cache_key,json_encode($cache_data),3600);

        $image_position = $this->qtypes[$qtype]['image_position'];
        $big_image_position = $this->qtypes[$qtype]['big_image_position'];
        $all_printer_imgs = array();
        foreach ($qrcode_contents as $k=>$v){
            $p_i = 0;
            $now_images = array();
            foreach ($v as $sv){
                $content = $sv;
                $file_name = $this->oss_code_path."$content.png";
                $encode_file_name = $this->urlsafe_b64encode($file_name);
                $now_position = $image_position[$p_i];
                $imgs = "watermark,image_$encode_file_name,$now_position";
                $p_i++;
                $now_images[]=$imgs;
            }
            $all_imgs = join('/',$now_images);
            $img_url = $this->oss_host.$this->oss_code_path.$this->qtypes[$qtype]['template_img']."?x-oss-process=image/";
            $content = file_get_contents($img_url.$all_imgs);

            $template_img = $this->qtypes[$qtype]['template_img']."-$k.jpg";
            $file_path = $qrcode_create_path.$template_img;//本地文件路径
            file_put_contents($file_path, $content);

            $file_name = $this->oss_code_path.$template_img;
            $res_upinfo = $aliyunoss->uploadFile($file_name, $file_path);
            if(empty($res_upinfo['info']['url'])){
                $aliyunoss->uploadFile($file_name, $file_path);
            }

            $big_file_name = $this->oss_code_path."$k.png";
            $encode_file_name = $this->urlsafe_b64encode($big_file_name);
            $print_img = $this->oss_host.$this->oss_code_path."$template_img?x-oss-process=image/watermark,image_$encode_file_name,$big_image_position";
            $all_printer_imgs[]=$print_img;
        }
        $m_qrcode_content->updateData(array('id'=>array('in',$content_ids)),array('status'=>2));
        $cache_data['status'] = 4;
        $cache_data['etime'] = time();
        $cache_data['img_urls'] = $all_printer_imgs;
        $redis->set($cache_key,json_encode($cache_data),300);

        //处理订阅消息到打印机
        $accessId = C('OSS_ACCESS_ID');
        $accessKey= C('OSS_ACCESS_KEY');
        $endPoint = C('QUEUE_ENDPOINT');
        $topicName = 'TagPrinter-LHS';
        $messageTag = '0015005DA6CD';
        $now_message = array('orientation'=>'PORTRAIT','printWidth'=>100.0,'printHeight'=>60.0,
            'printLeftSide'=>0.0,'printTopSide'=>0.0,'copiesNum'=>1);
        $ali_msn = new AliyunMsn($accessId, $accessKey, $endPoint);
        foreach ($all_printer_imgs as $v){
            $now_message['printFiles'] = array(array('type'=>'HTTPFile','path'=>$v));
            $messageBody = json_encode($now_message);
            $ali_msn->sendTopicMessage($topicName,$messageBody,$messageTag);
        }
    }


    private function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    private function urlsafe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    private function get_sign(){
        $str = 'R161412d';
        $sign = encrypt_data($str);
        return $sign;
    }

    private function output($msg){
        header("Content-type: text/html; charset=utf-8");
        die($msg);
    }
}