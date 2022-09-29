<?php
namespace Admin\Controller;

class FinanceqrcodeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array('ctype'=>2,'type'=>1);
        $start  = ($page-1) * $size;
        $m_fqrcode  = new \Admin\Model\FinanceQrcodeContentModel();
        $result = $m_fqrcode->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = array();
        $code_url = 'https://oss.littlehotspot.com/qrcode/goods/template6';
        $big_image_position = 'g_east,x_200,y_40';
        $all_status = array('1'=>'生成中','2'=>'生成完毕');
        foreach ($result['list'] as $v){
            $id_num = $v['id'];
            $url = '';
            if($v['status']==2){
                $big_file_name = "qrcode/goods/$id_num.png";
                $encode_file_name = $this->urlsafe_b64encode($big_file_name);
                $url = $code_url."-$id_num.jpg?x-oss-process=image/watermark,image_$encode_file_name,$big_image_position";
            }
            $v['status_str'] = $all_status[$v['status']];
            $v['url'] = $url;
            $datalist[]=$v;
        }
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }

    public function qrcodeadd(){
        $num = I('num', 0, 'intval');

        $cache_key = 'cronscript:printer:qrcode';
        $redis = \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            $this->output('二维码正在生成中,请稍后刷新列表查看', 'financeqrcode/qrcodeadd', 2, 0);
        }else{
            $sign = '00f68bfc43be12cf851c8d3ed5d594a9';
            $qtype = 16;
            $ctype = 2;
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php h5/printer/qrcodescript/sign/$sign/qtype/$qtype/num/$num/ctype/$ctype > /tmp/null &";
            system($shell);
            $cache_data = array('status'=>1,'stime'=>time());
            $redis->set($cache_key,json_encode($cache_data),3600);
            $this->output('二维码开始生成,请稍后', 'financeqrcode/qrcodeadd');
        }
    }

    private function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
}