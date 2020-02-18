<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 审核酒楼管理
 *
 */
class HotelController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function hotellist() {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $area_id = I('area_id',0,'intval');
        $flag = I('flag',99,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.type'=>2);
        if($flag!=99)   $where['a.flag'] = $flag;
        if($area_id)    $where['area.id']=$area_id;
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'dishorder/orderlist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.name,a.addr,a.contractor,a.mobile,a.tel,a.state,a.create_time,a.flag,a.type,
        area.region_name as area_name,ext.food_style_id,ext.avg_expense,ext.hotel_logoimg,ext.hotel_faceimg,ext.hotel_envimg,
        ext.legal_name,ext.legal_idcard,ext.legal_charter';
        $m_hotel  = new \Admin\Model\HotelModel();
        $result = $m_hotel->getListExt($where,'a.id desc',$start,$size,$fields);
        $datalist = $result['list'];
        $oss_host = get_oss_host();
        $all_flags = array('0'=>'审核通过','1'=>'审核不通过','2'=>'待审核');
        foreach ($datalist as $k=>$v){
            $logoimg = $oss_host.$v['hotel_logoimg'];
            $faceimg = $oss_host.$v['hotel_faceimg'];
            $envimg = $oss_host.$v['hotel_envimg'];
            $idcard_imgs = array();
            $legal_idcard_arr = explode(',',$v['legal_idcard']);
            foreach ($legal_idcard_arr as $iv){
                $idcard_imgs[]=$oss_host.$iv;
            }
            $charter_imgs = array();
            $legal_charter_arr = explode(',',$v['legal_charter']);
            foreach ($legal_charter_arr as $cv){
                $charter_imgs[]=$oss_host.$cv;
            }
            $datalist[$k]['flag_str'] = $all_flags[$v['flag']];
            $datalist[$k]['logoimg'] = $logoimg;
            $datalist[$k]['faceimg'] = $faceimg;
            $datalist[$k]['envimg'] = $envimg;
            $datalist[$k]['idcard_imgs'] = $idcard_imgs;
            $datalist[$k]['charter_imgs'] = $charter_imgs;
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('all_flags',$all_flags);
        $this->assign('flag',$flag);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotellist');
    }

    public function changestatus(){
        $id = I('get.id',0,'intval');
        $flag = I('get.flag',1,'intval');
        $m_hotel  = new \Admin\Model\HotelModel();
        $result = $m_hotel->updateData(array('id'=>$id),array('flag'=>$flag));
        if($result){
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$id));
            $m_hotel = new \Admin\Model\HotelModel();
            $res_hotel = $m_hotel->getOne($id);
            if(empty($res_merchant)){
                if($flag==0){
                    $data = array('hotel_id'=>$id,'service_model_id'=>1,'channel_id'=>1,
                        'rate_groupid'=>100,'cash_rate'=>1,'recharge_rate'=>1,
                        'name'=>$res_hotel['contractor'],'job'=>'','mobile'=>$res_hotel['mobile'],
                        'sysuser_id'=>$sysuser_id,'status'=>1);
                    $code_charter = '';
                    $s_hotel_name = mb_substr($res_hotel['name'], 0,2,'utf8');
                    if(preg_match('/[a-zA-Z]/', $s_hotel_name)){
                        $code_charter = $s_hotel_name;
                    }else {
                        $pin = new \Common\Lib\Pin();
                        $obj_pin = new \Overtrue\Pinyin\Pinyin();
                        $code_charter = $obj_pin->abbr($s_hotel_name);
                        $code_charter  = strtolower($code_charter);
                        if(strlen($code_charter)==1){
                            $code_charter .=$code_charter;
                        }
                    }
                    $code_charter  = strtolower($code_charter);
                    $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
                    $invite_code = '';
                    $flag = 0;
                    while ($flag <20){
                        $code_num = generate_code(6);
                        $invite_code = $code_charter.$code_num;
                        $where = array('code'=>$invite_code);
                        $nums = $m_hotel_invite_code->countNums($where);
                        if(empty($nums)){
                            break;
                        }
                        $flag ++;
                    }
                    $invite_data = array('code'=>$invite_code,'hotel_id'=>$data['hotel_id'],'bind_mobile'=>$data['mobile'],
                        'bind_time'=>date('Y-m-d H:i:s'),'type'=>2,'creator_id'=>$sysuser_id,'state'=>1);
                    $ret = $m_hotel_invite_code->addInfo($invite_data);
                    $res_merchant = false;
                    if($ret){
                        $data['code'] = $invite_code;
                        $res_merchant = $m_merchant->addData($data);
                    }
                    if($res_merchant){
                        //发送短信
                        $sms_config = C('ALIYUN_SMS_CONFIG');
                        $alisms = new \Common\Lib\AliyunSms();
                        $params = array('hotel_name'=>$res_hotel['name'],'code'=>$invite_code);
                        $template_code = $sms_config['merchant_login_invite_code'];
                        $alisms::sendSms($data['mobile'],$params,$template_code);
                        $message = "邀请码已通过短信的方式发送给了“{$res_hotel['name']}“的管理员请提醒其注意查收！";
                        $this->output($message,'hotel/hotellist',2);
                    }
                }else{
                    $m_merchant->updateData(array('id'=>$res_merchant['id']),array('status'=>2));
                }
            }
            $this->output('操作成功!', 'hotel/hotellist',2);
        }else{
            $this->output('操作失败', 'hotel/hotellist',2,0);
        }
    }

}