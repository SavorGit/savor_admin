<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Ucpaas;
/**
 * @desc 活动详情页
 *
 */
class ActivitydetailController extends Controller {
    var $vcode_valid_time;
    //var $vcode_max_send_time;
    var $vcode_max_send_num;
    public function __construct() {
        parent::__construct();
        $this->vcode_valid_time = 300;   //手机验证码有效时间为2分钟
        //$this->vcode_max_send_time = 86400;  //手机验证码发送次数保存时长
        $this->vcode_max_send_num = 3;       //手机验证码最多发送3次出现图形验证码
    }
    public function  toothWash(){
        $id = 1;
        $sourceid = I('get.sourceid','0','intval');
        if(!empty($sourceid)){
            $this->assign('sourceid',$sourceid);
        }
        $this->assign('id',$id);
        $this->display('Activity/toothwashdetail');
    }
    /**
     * @desc 提交订单
     */
    public function doapply(){
        $map = array();
        if(IS_POST){
            
            $apply_name =  I('post.apply_name','','trim');   //收货人姓名
            $mobile     =  I('post.mobile','','trim');       //收货人手机号
            $verify_code=  I('post.verify_code','','trim');  //手机验证码
            $address    =  I('post.address','','trim');      //收货地址
            $activity_id = I('post.activity_id','0','intval');  //活动id
            $sourceid   =  I('post.sourceid','0','intval');   //来源
            if(empty($apply_name)){
                $map['status'] = 101;
                $map['extent'] = 110;
                $map['msg']    = '收货人姓名不能为空';
                echo json_encode($map);
                exit;
            }
            if(empty($mobile)){
                $map['status'] = 102;
                $map['extent'] = 110;
                $map['msg']    = '收货人电话不能为空';
                echo json_encode($map);
                exit;
            }
            if(!preg_match('/^1[34578]\d{9}$/', $mobile)){
                $map['status'] = 103;
                $map['extent'] = 100;
                $map['msg'] = '请填写正确手机号';
                echo json_encode($map);
                exit;
            }
            if(empty($verify_code)){
                $map['status'] = 104;
                $map['extent'] = 110;
                $map['msg']    = '手机验证码不能为空';
                echo json_encode($map);
                exit;
            }
            
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(1);
            $cache_key = 'tooth_vcode_'.$mobile;
            $cache_verify_code = $redis->get($cache_key);
            if($verify_code != $cache_verify_code){
                $map['status'] = 105;
                $map['extent'] = 150;
                $map['msg']    = '手机验证码不正确或已过期';
                echo json_encode($map);
                exit;
            }
            $m_activity_config = new \Admin\Model\ActivityConfigModel();
            $activity_info = $m_activity_config->getInfo('id,start_time,end_time,goods_nums',array('id'=>$activity_id,'status'=>1));
            
            if(empty($activity_info)){
                $map['status'] = 201;
                $map['extent'] = 100;
                $map['msg']    = '该活动已下线';
                echo json_encode($map);
                exit;
            }
            $now_time = time();
            $start_time = strtotime($activity_info['start_time']);
            $end_time   = strtotime($activity_info['end_time']) ;
            if($now_time<$start_time){
                $map['status'] = 202;
                $map['extent'] = 100;
                $map['msg'] = '该活动还未开始';
                echo json_encode($map);
                exit;
            }
            if($now_time>$end_time){
                $map['status'] = 203;
                $map['extent'] = 100;
                $map['msg'] = '该活动已结束';
                echo json_encode($map);
                exit;
            }
            $m_activity_data = new \Admin\Model\ActivityDataModel();
            $allData = $m_activity_data->countData(array('activity_id'=>$activity_id));
            if($allData>=$activity_info['goods_nums']){
                $map['status'] = 204;
                $map['extent'] = 100;
                $map['msg'] = '商品已售完';
                echo json_encode($map);
                exit;
            }
            $m_activity_data = new \Admin\Model\ActivityDataModel();
            $info = $m_activity_data->getInfo('id',array('mobile'=>$mobile));
            if(!empty($info)){
                $map['status'] = 204;
                $map['extent'] = 140;
                $map['msg']    ='同一手机号只能下单一次';
                echo json_encode($map);
                exit;
            }
            $data = array();
            $data['receiver'] = $apply_name;
            $data['mobile'] = $mobile;
            $data['address'] = $address;
            $data['activity_id'] = $activity_id;
            
            if(empty($sourceid)){
                $is_wx = checkWxbrowser();
                if($is_wx==0){//app打开
                    $data['sourceid'] = 1;
                }else if($is_wx ==1){//微信分享
                    $data['sourceid'] = 3;
                }
            }
            
            if(!empty($sourceid) && is_numeric($sourceid)){
                $activity_source_arr =  C('ACTIVITY_SOURCE_ARR');
                if(key_exists($sourceid, $activity_source_arr)){
                    $data['sourceid'] = $sourceid;
                }
            }
            $ret = $m_activity_data->addInfo($data);
            if($ret){
                //发送短信
                $info['tel'] = $mobile;
                $param = $activity_info['name'];
                $ret = $this->sendToUcPa($info, $param,2);
                $map['status'] = 1;
                $map['extent'] = 100;
                $map['msg']    = '下单成功';
                $redis->remove($cache_key);
                echo json_encode($map);
                exit;
            }else {
                $map['status'] = 2;
                $map['extent'] = 100;
                $map['msg'] = '下单失败';
                echo json_encode($map);
                exit;
            }
        }else {
            $map['status'] = 301;
            $map['extent'] = 100;
            $map['msg'] = '非法操作';
            echo json_encode($map);
            exit;
        }    
        
    }
    /**
     * @desc 获取手机手机验证码
     */
    public function getMobileCode(){
        $mobile =  I('post.mobile','','trim');
        $activity_id = I('post.activity_id','0','intval');
        $m_account_sms_log =  new \Admin\Model\AccountMsgLogModel();
        $gztime = date('Y-m-d H:i:s',strtotime('-1 Minute'));
       
        $where = array();
        $where['status'] =1;
        $where['type'] = 3;
        $where['msg_type'] =2;
        $where['tel'] = $mobile;
        $where['create_time'] = array('gt',$gztime);
        $isSend = $m_account_sms_log->getOne($where);
        if(!empty($isSend)){
            $map['status'] = 111;
            $map['extent'] = 200;
            $map['msg'] = '一分钟内请勿重复获取验证码';
            echo json_encode($map);
            exit;
        }
        if(empty($activity_id)){
            $map['status'] = 104;
            $map['extent'] = 100;
            $map['msg'] = '活动不存在';
            echo json_encode($map);
            exit;
        }
        $map = array();
        if(empty($mobile)){
            $map['status'] = 101;
            $map['extent'] = 100;
            $map['msg'] = '请填写手机号';
            echo json_encode($map);
            exit;
        }
        if(!preg_match('/^1[34578]\d{9}$/', $mobile)){
                $map['status'] = 103;
                $map['extent'] = 100;
                $map['msg'] = '请填写正确手机号';
                echo json_encode($map);
                exit;
        }
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $activity_info = $m_activity_config->getInfo('id,start_time,end_time,goods_nums',array('id'=>$activity_id,'status'=>1));
        if(empty($activity_info)){
            $map['status'] = 202;
            $map['extent'] = 100;
            $map['msg']    = '该活动不存在';
            echo json_encode($map);
            exit;
        }
        
        if(empty($activity_info)){
            $map['status'] = 201;
            $map['extent'] = 100;
            $map['msg']    = '该活动已下线';
            echo json_encode($map);
            exit;
        }
        $now_time = time();
        $start_time = strtotime($activity_info['start_time']);
        $end_time   = strtotime($activity_info['end_time']) ;
        if($now_time<$start_time){
            $map['status'] = 203;
            $map['extent'] = 100;
            $map['msg'] = '该活动还未开始';
            echo json_encode($map);
            exit;
        }
        if($now_time>$end_time){
            $map['status'] = 204;
            $map['extent'] = 100;
            $map['msg'] = '该活动已结束';
            echo json_encode($map);
            exit;
        }
       
        
        
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $allData = $m_activity_data->countData(array('activity_id'=>$activity_id));
        if($allData>=$activity_info['goods_nums']){
            $map['status'] = 205;
            $map['extent'] = 100;
            $map['msg'] = '商品已售完';
            echo json_encode($map);
            exit;
        }
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $order_info = $m_activity_data->getInfo('id', array('mobile'=>$mobile));
        if(!empty($order_info)){
            $map['status'] = 103;
            $map['extent'] = 210;
            $map['msg'] = '该手机已经下单，无法重新获取验证码';
            echo json_encode($map);
            exit;
        }
        $code_array = array('0','1','2','3','4','5','6','7','8','9');
        $verify_code = array_rand($code_array,4);
        $verify_code = implode('', $verify_code);
        $redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(1);
		
		$vcode_cache_key = 'tooth_vcode_'.$mobile;
		$redis->set($vcode_cache_key, $verify_code,$this->vcode_valid_time);    //手机验证码有效时间为5分钟
		$vcode_num_cache_key = 'tooth_vcode_num';
		$send_nums = session($vcode_num_cache_key);
		
		if($send_nums>=$this->vcode_max_send_num){
		    $map['status'] = 201;
		    $map['extent'] = 150;
		    $map['msg']    ='验证码发送次数已经超过三次';
		    echo json_encode($map);
		    exit;
		}
		//发送短信
		$info['tel'] = $mobile;
		$param = $verify_code.','.$this->vcode_valid_time/60;
		$ret = $this->sendToUcPa($info, $param);
        if($ret){
            $vcode_num = session($vcode_num_cache_key);
            
            $vcode_num = intval($vcode_num) +1;
            session($vcode_num_cache_key,$vcode_num);
            //$redis->set($vcode_num_cache_key, $vcode_num,$this->vcode_max_send_time); //发送短信次数+1
            $map['status'] = 1;
            $map['extent'] = 100;
            $map['msg']  = '验证码发送成功';
            echo json_encode($map);
            exit;
        }else {
            $map['status'] = 301;
            $map['extent'] = 100;
            $map['msg'] = '验证码发送失败';
            echo json_encode($map);
            exit;
        }
    }
    /**
     * @desc 确认图片验证码并发送短信
     */
    public function configcode(){
        $pic_code = I('post.pic_code','','trim');
        $mobile   = I('post.mobile','','trim');
        $activity_id = I('post.activity_id','0','intval');
        
        $m_account_sms_log =  new \Admin\Model\AccountMsgLogModel();
        $gztime = date('Y-m-d H:i:s',strtotime('-1 Minute'));
        $where = array();
        $where['status'] =1;
        $where['type'] = 3;
        $where['msg_type'] =2;
        $where['tel'] = $mobile;
        $where['create_time'] = array('gt',$gztime);
        $isSend = $m_account_sms_log->getOne($where);
        if(!empty($isSend)){
            $map['status'] = 111;
            $map['extent'] = 200;
            $map['msg'] = '一分钟内请勿重复获取验证码';
            echo json_encode($map);
            exit;
        }
        if(empty($activity_id)){
            $map['status'] = 103;
            $map['extent'] = 100;
            $map['msg'] = '参数非法';
            echo json_encode($map);
            exit;
        }
        if(empty($pic_code)){
            $map['status'] = 101;
            $map['extent'] = 100;
            $map['msg']    = '请填写验证码';
            echo json_encode($map);
            exit;
        } 
        if(strlen($pic_code)!=4){
            $map['status'] = 102;
            $map['extent'] = 100;
            $map['msg']  = '验证码长度不正确';
            echo json_encode($map);
            exit;
        }
        
        if(!check_verify($pic_code)){
            $map['status'] = 201;
            $map['extent'] = 100;
            $map['msg'] = '验证码不正确';
            echo json_encode($map);
            exit;
        }
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $activity_info = $m_activity_config->getInfo('id,start_time,end_time,goods_nums',array('id'=>$activity_id,'status'=>1));
        if(empty($activity_info)){
            $map['status'] = 202;
            $map['msg']    = '该活动不存在';
            echo json_encode($map);
            exit;
        }
       
        $now_time = time();
        $start_time = strtotime($activity_info['start_time']);
        $end_time   = strtotime($activity_info['end_time']) ;
        if($now_time<$start_time){
            $map['status'] = 205;
            $map['extent'] = 100;
            $map['msg'] = '该活动还未开始';
            echo json_encode($map);
            exit;
        }
        if($now_time>$end_time){
            $map['status'] = 206;
            $map['extent'] = 100;
            $map['msg'] = '该活动已结束';
            echo json_encode($map);
            exit;
        }
       
        
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $allData = $m_activity_data->countData(array('activity_id'=>$activity_id));
        if($allData>=$activity_info['goods_nums']){
            $map['status'] = 203;
            $map['extent'] = 100;
            $map['msg'] = '商品已售完';
            echo json_encode($map);
            exit;
        }
        
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $order_info = $m_activity_data->getInfo('id', array('mobile'=>$mobile));
        if(!empty($order_info)){
            $map['status'] = 204;
            $map['extent'] = 210;
            $map['msg'] = '该手机已经下单，无法重新获取验证码';
            echo json_encode($map);
            exit;
        }
        
        //发送短信
        $code_array = array('0','1','2','3','4','5','6','7','8','9');
        $verify_code = array_rand($code_array,4);
        $verify_code = implode('', $verify_code);
        $vcode_cache_key = 'tooth_vcode_'.$mobile;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($vcode_cache_key, $verify_code,$this->vcode_valid_time);    //手机验证码有效时间为2分钟
        $info['tel'] = $mobile;
        $param = $verify_code.','.$this->vcode_valid_time/60;
        $ret = $this->sendToUcPa($info, $param); 
        if($ret){
            $map['status'] = 1;
            $map['extent'] = 100;
            $map['msg']  = '验证码发送成功';
            echo json_encode($map);
            exit;
        }else {
            $map['status'] = 2;
            $map['extent'] = 100;
            $map['msg']  = '验证码发送失败';
            echo json_encode($map);
            exit;
        }
        
    }
    private function sendToUcPa($info,$param,$type=1){
        $to = $info['tel'];
        $bool = true;
        $ucconfig = C('SMS_CONFIG');
        $options['accountsid'] = $ucconfig['accountsid'];
        $options['token'] = $ucconfig['token'];
        //确认付款通知
        /* if($type == 2){
            $templateId = $ucconfig['payment_templateid'];
        }else{
            $templateId = $ucconfig['bill_templateid'];
        } */
        if($type==1){
            $templateId = $ucconfig['vcode_templateid'];
        }else if($type==2){
            $templateId = $ucconfig['notice_templateid'];
        }
        
        $ucpass= new Ucpaas($options);
        $appId = $ucconfig['appid'];
        $sjson = $ucpass->templateSMS($appId,$to,$templateId,$param);
    
        $sjson = json_decode($sjson,true);
        $code = $sjson['resp']['respCode'];
        if($code === '000000') {
            $data = array();
            $data['type'] = 3;
            $data['status'] = 1;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            $data['url'] = $param;
            $data['tel'] = $to;
            $data['resp_code'] = $code;
            $data['msg_type'] = 2;
            $m_account_sms_log =  new \Admin\Model\AccountMsgLogModel();
            $m_account_sms_log->addData($data);
            return true;
        }else{
            return false;
        }

    }
    public function isSoldOut(){
        $id = I('post.id','0','intval');
        $id = 1;
        if(empty($id)){
            $map['status'] = 101;
            $map['msg']    = '参数非法';
            echo json_encode($map);
            exit;
        }
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $activity_info = $m_activity_config->getInfo('id,start_time,end_time,goods_nums',array('id'=>$id,'status'=>1));
        if(empty($activity_info)){
            $map['status'] = 102;
            $map['msg']    = '该活动已下线';
            echo json_encode($map);
            exit;
        }
        $now_time = time();
        $start_time = strtotime($activity_info['start_time']);
        $end_time   = strtotime($activity_info['end_time']) ;
        if($now_time<$start_time){
            $map['status'] = 103;
            
            $map['msg'] = '该活动还未开始';
            echo json_encode($map);
            exit;
        }
        if($now_time>$end_time){
            $map['status'] = 104;
            $map['msg'] = '该活动已结束';
            echo json_encode($map);
            exit;
        }
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $allData = $m_activity_data->countData(array('activity_id'=>$id));
        if($allData>=$activity_info['goods_nums']){
            $map['status'] = 103;
            $map['extent'] = 100;
            $map['msg'] = '商品已售完';
            echo json_encode($map);
            exit;
        }
        $map['status'] = 1;
        $map['msg']  ='';
       
        echo json_encode($map);
        exit;
        
    }
    
    //*********以上代码为定制活动洗牙卡**********************//  
    //*********以下代码为活动统一下单代码********************//
    public function index(){
        $id = I('id',0,'intval');
        $sourceid = I('get.sourceid','0','intval');
        if(!empty($sourceid)){
            $this->assign('sourceid',$sourceid);
        }
        
        $this->assign('id',$id);
        $this->display('Activity/activity'.$id);
    }
    /**
     * @desc 获取手机手机验证码
     */
    public function getActMobileCode(){
        $mobile =  I('post.mobile','','trim');             //手机号码
        $activity_id = I('post.activity_id','0','intval'); //活动id
        $goods_id = I('post.goods_id',0,'intval');         //商品id
        $m_account_sms_log =  new \Admin\Model\AccountMsgLogModel();
        $gztime = date('Y-m-d H:i:s',strtotime('-1 Minute'));
       
        //是否重复发送短信
        
        if(empty($activity_id)){
            $map['status'] = 104;
            $map['extent'] = 100;
            $map['msg'] = '活动不存在';
            echo json_encode($map);
            exit;
        }
        $map = array();
        if(empty($mobile)){
            $map['status'] = 101;
            $map['extent'] = 100;
            $map['msg'] = '请填写手机号';
            echo json_encode($map);
            exit;
        }
        if(!preg_match('/^1[34578]\d{9}$/', $mobile)){
                $map['status'] = 103;
                $map['extent'] = 100;
                $map['msg'] = '请填写正确手机号';
                echo json_encode($map);
                exit;
        }
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $activity_info = $m_activity_config->getInfo('id,start_time,end_time,goods_nums,person_order_num',array('id'=>$activity_id,'status'=>1));
        if(empty($activity_info)){
            $map['status'] = 202;
            $map['extent'] = 100;
            $map['msg']    = '该活动不存在';
            echo json_encode($map);
            exit;
        }
        
        $now_time = time();
        $start_time = strtotime($activity_info['start_time']);
        $end_time   = strtotime($activity_info['end_time']) ;
        if($now_time<$start_time){
            $map['status'] = 203;
            $map['extent'] = 100;
            $map['msg'] = '该活动还未开始';
            echo json_encode($map);
            exit;
        }
        if($now_time>$end_time){
            $map['status'] = 204;
            $map['extent'] = 100;
            $map['msg'] = '该活动已结束';
            echo json_encode($map);
            exit;
        }
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $allData = $m_activity_data->countData(array('activity_id'=>$activity_id));
        if($allData>=$activity_info['goods_nums']){
            $map['status'] = 205;
            $map['extent'] = 100;
            $map['msg'] = '商品已售完';
            echo json_encode($map);
            exit;
        }
        if(!empty($goods_id)){
            $m_activity_goods = new \Admin\Model\ActivityGoodsModel();
            $allGoodsData = $m_activity_data->countData(array('activity_id'=>$activity_id,'goods_id'=>$goods_id));
            $goods_info = $m_activity_goods->getOne(array('id'=>$goods_id));
            if($allGoodsData>=$goods_info['goods_nums']){
                $map['status'] = 205;
                $map['extent'] = 100;
                $map['msg'] = '商品已售完';
                echo json_encode($map);
                exit;
            }
            
        }
        $person_order_num = $activity_info['person_order_num'];   //每个人最大购买量
        if(!empty($person_order_num)){
            if($allGoodsData>$person_order_num){
                $map['status'] = 206;
                $map['extent'] = 100;
                $map['msg'] = '已到达最大购买量';
                echo json_encode($map);
                exit;
            }
        }

        $code_array = array('0','1','2','3','4','5','6','7','8','9');
        $verify_code = array_rand($code_array,4);
        $verify_code = implode('', $verify_code);
        $redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(1);
		
		$vcode_cache_key = 'tooth_vcode_'.$mobile;
		$redis->set($vcode_cache_key, $verify_code,$this->vcode_valid_time);    //手机验证码有效时间为5分钟
		$vcode_num_cache_key = 'tooth_vcode_num';
		$send_nums = session($vcode_num_cache_key);
		
		if($send_nums>=$this->vcode_max_send_num){
		    $map['status'] = 201;
		    $map['extent'] = 150;
		    $map['msg']    ='验证码发送次数已经超过三次';
		    echo json_encode($map);
		    exit;
		}
		//发送短信
		$info['tel'] = $mobile;
		$param = $verify_code.','.$this->vcode_valid_time/60;
		$ret = $this->sendToUcPa($info, $param);
        if($ret){
            $vcode_num = session($vcode_num_cache_key);
            
            $vcode_num = intval($vcode_num) +1;
            session($vcode_num_cache_key,$vcode_num);
            //$redis->set($vcode_num_cache_key, $vcode_num,$this->vcode_max_send_time); //发送短信次数+1
            $map['status'] = 1;
            $map['extent'] = 100;
            $map['msg']  = '验证码发送成功';
            echo json_encode($map);
            exit;
        }else {
            $map['status'] = 301;
            $map['extent'] = 100;
            $map['msg'] = '验证码发送失败';
            echo json_encode($map);
            exit;
        }
    }
    
    /**
     * @desc 是否重复发送短信
     */
    private function isRepeatSend($params = array()){
        /* $where = array();
        $where['status'] =1;       //发送成功状态的短信
        $where['type'] = 3;        //短信模版类型
        $where['msg_type'] =2;     //1：营销类短信 2：验证码短信
        $where['tel'] = $mobile;
        $where['create_time'] = array('gt',$gztime); */
        
        $isSend = $m_account_sms_log->getOne($where);
        if(!empty($isSend)){
            $map['status'] = 111;
            $map['extent'] = 200;
            $map['msg'] = '一分钟内请勿重复获取验证码';
            echo json_encode($map);
            exit;
        }
    }
}