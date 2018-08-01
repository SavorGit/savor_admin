<?php
/**
 *@desc u盘日志上报
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class BoxshellController extends BaseController{
    var $password ;
    public function __construct(){
        parent::__construct();
        $this->password = '3c886a5e9cdb3b747f9e33507c62b62b';
    }
    /**
     * @desc 首页
     */
    public function index(){
        $hotel_box_type_str = $this->getNetHotelTypeStr();
        $m_hotel = new \Admin\Model\HotelModel();
        
        $fields = 'id hotel_id , name hotel_name';
        $where  = array();
        $where['flag'] = 0;
        $where['state'] = 1;
        $where['hotel_box_type'] = array('in',"$hotel_box_type_str");
        $hotel_list = $m_hotel->getInfo($fields,$where);
        foreach($hotel_list as $key=>$v){
            $firstCharter = getFirstCharter(cut_str($v['hotel_name'], 1));
            $hotel_arr[$firstCharter][] = $v;
        }
        ksort($hotel_arr);
        $this->assign('hotel_list',$hotel_arr);
        $this->display('index');
    }
    /**
     * @desc 推送
     */
    public function pushData(){
        $hotel_id = I('hotel_id', 0,'intval');
        $room_id  = I('room_id', 0,'intval');
        $box_id   = I('box_id', 0,'intval');
        $password = I('password');
        if(empty($hotel_id)){
            $this->error('请选择一家酒楼');
        }
        if(empty($room_id)){
            $this->error('请选择一个包间');
        }
        if(empty($box_id)){
            $this->error('请选择一个机顶盒');
        }
        if(empty($password)){
            $this->error('请输入密码');
        }
        if(md5($password)!==$this->password){
            $this->error('密码输入错误');
        }
        $shell_command = I('shell_command');
        $shell_command_arr = explode("\n", $shell_command);
        foreach($shell_command_arr as $key=>$v){
            if(empty(trim($v))){
                unset($shell_command_arr[$key]);
            }else {
                $shell_command_arr[$key] = str_replace("\r", '', $v);
            }
        }
        if(empty($shell_command_arr)){
            $this->error('请输入shell命令');
        }
        $m_box =  new \Admin\Model\BoxModel();
        $field = "b.id,b.device_token";
        $where = " b.id=$box_id and r.id=$room_id and h.id=$hotel_id";
        
        $box_info =  $m_box->isHaveMac($field, $where);
        if(empty($box_info)){
            $this->error('该机顶盒不存在,请重新选择');
        }
        $box_info = $box_info[0];
        if(empty($box_info['device_token'])){
            $this->error('该机顶盒的device_token为空,不可以发shell推送');
        }
        $display_type = 'notification';
        $option_name = 'boxclient';
        $after_a = C('AFTER_APP');
        $after_open = $after_a[3];
        $device_token = $box_info['device_token'];
        $ticker = 'shell推送';
        $title  = 'shell推送';
        $text   = 'shell推送';
        $production_mode = C('UMENG_PRODUCTION_MODE');
        $custom = array();
        $custom['type'] = 3;  //1:RTB  2:4G投屏 3:shell命令推送  4：apk升级
        $custom['action'] = 1; //1:投屏  0:结束投屏
        $custom['data'] = $shell_command_arr;
        
        
        $this->uPushData($display_type, 3,'listcast',$option_name, $after_open, $device_token,
                         $ticker,$title,$text,$production_mode,$custom);
        
        
        $push_data = array();
        $push_data['hotel_id'] = $hotel_id;
        $push_data['room_id']  = $room_id;
        $push_data['box_id']   = $box_id;
        $push_data['push_info']= json_encode($custom);
        $push_type['push_type']= 3;
        $m_push_log = new \Admin\Model\PushLogModel();
        $m_push_log->addInfo($push_data);
        $this->output('推送成功', 'boxshell/index', 2);
    }
    
    
    
    /**
     * @desc 获取酒楼的包间信息
     */
    public function getRoomList(){
        $hotel_id = I('hotel_id','0','intval');
        $m_room = new \Admin\Model\RoomModel();
        $fields = 'id room_id,name room_name';
        $where = array();
        $where['hotel_id'] = $hotel_id;
        $where['flag'] = 0;
        
        $room_list = $m_room->getInfo($fields,$where);
        echo json_encode($room_list);
        exit;
    }
    /**
     * @desc 获取酒楼的机顶盒信息
     */
    public function getBoxList(){
        $room_id = I('room_id','0','intval');
        $m_box = new \Admin\Model\BoxModel();
        $fields = 'id box_id,name box_name,mac';
        $where = array();
        $where['room_id'] = $room_id;
        $where['flag'] = 0;
        $where['state'] = 1;
        $box_list = $m_box->getInfo($fields,$where);
        echo json_encode($box_list);
        exit;
    }
    
    /**
     * @desc 推送机顶盒APK升级
     */
    public function apkIndex(){
        $hotel_box_type_str = $this->getNetHotelTypeStr();
        $m_hotel = new \Admin\Model\HotelModel();
        
        $fields = 'id hotel_id , name hotel_name';
        $where  = array();
        $where['flag'] = 0;
        $where['state'] = 1;
        $where['hotel_box_type'] = array('in',"$hotel_box_type_str");
        $hotel_list = $m_hotel->getInfo($fields,$where);
        foreach($hotel_list as $key=>$v){
            
            $firstCharter = getFirstCharter(cut_str($v['hotel_name'], 1));
            $hotel_arr[$firstCharter][] = $v;
        }
        ksort($hotel_arr);
        $this->assign('hotel_list',$hotel_arr);
        $this->display('apkindex');
    }
    /**
     * @desc 获取当前酒楼的apk最新版本
     */
    public function getBoxApkVersion(){
        $hotel_id = I('hotel_id',0,'intval');
        $box_id   = I('box_id',0,'intval');
        $m_box = new \Admin\Model\BoxModel();
        $box_info = $m_box->field('adv_mach')->where('id='.$box_id)->find();
        if(empty($box_info['adv_mach'])){//非广告机
            $m_upgrade = new \Admin\Model\UpgradeModel();
            
            $field = 'sdv.version_code version_name';
            $device_type = 2;
            $data = $m_upgrade->getLastOneByDeviceNew($field, $device_type, $hotel_id);
            echo json_encode($data);
        }else {
            $m_device_version = new \Admin\Model\VersionModel();
            $data = $m_device_version->field('version_code version_name')->where('device_type=21')->order('id desc')->find();
            if(empty($data)){
                $data = array();
            }
            echo json_encode($data);
        }   
    }
    /**
     * @desc 推送apk升级数据到机顶盒
     */
    public function pushApkData(){
        $hotel_id = I('hotel_id', 0,'intval');
        $room_id  = I('room_id', 0,'intval');
        $box_id   = I('box_id', 0,'intval');
        $password = I('password');
        
        
        if(empty($hotel_id)){
            $this->error('请选择一家酒楼');
        }
        if(empty($room_id)){
            $this->error('请选择一个包间');
        }
        if(empty($box_id)){
            $this->error('请选择一个机顶盒');
        }
        if(md5($password) != $this->password){
            $this->error('密码输入错误');
        }
        $m_box =  new \Admin\Model\BoxModel();
        $field = "b.id,b.device_token,b.adv_mach";
        $where = " b.id=$box_id and r.id=$room_id and h.id=$hotel_id";
        
        $box_info =  $m_box->isHaveMac($field, $where);
        if(empty($box_info)){
            $this->error('该机顶盒不存在,请重新选择');
        }
        $box_info = $box_info[0];
        if(empty($box_info['device_token'])){
            $this->error('该机顶盒的device_token为空,不可以发shell推送');
        }
        if(empty($box_info['adv_mach'])){//非广告机
            //获取当前机顶盒的最新apk
            $m_upgrade = new \Admin\Model\UpgradeModel();
            
            $field = 'sdv.oss_addr,md5';
            $device_type = 2;
            $data = $m_upgrade->getLastOneByDeviceNew($field, $device_type, $hotel_id);
            
        }else {//广告机
            $m_device_version = new \Admin\Model\VersionModel();
            $data = $m_device_version->field('oss_addr,md5')->where('device_type=21')->order('id desc')->find();
            
        }
        $apk_url = 'http://'.C('OSS_HOST_NEW').'/'.$data['oss_addr'];
        $apk_md5 = $data['md5'];
        
        
        $display_type = 'notification';
        $option_name = 'boxclient';
        $after_a = C('AFTER_APP');
        $after_open = $after_a[3];
        $device_token = $box_info['device_token'];
        $ticker = 'apk升级推送';
        $title  = 'apk升级推送';
        $text   = 'apk升级推送';
        $production_mode = C('UMENG_PRODUCTION_MODE');
        $custom = array();
        $custom['type'] = 4;  //1:RTB  2:4G投屏 3:shell命令推送  4：apk升级
        $custom['action'] = 1; //1:投屏  0:结束投屏
        $custom['data'] = array('apkUrl'=>$apk_url,'apkMd5'=>$apk_md5);
        $this->uPushData($display_type, 3,'listcast',$option_name, $after_open, $device_token,
            $ticker,$title,$text,$production_mode,$custom);
        
        
        $push_data = array();
        $push_data['hotel_id'] = $hotel_id;
        $push_data['room_id']  = $room_id;
        $push_data['box_id']   = $box_id;
        $push_data['push_info']= json_encode($custom);
        $push_type['push_type']= 4;
        $m_push_log = new \Admin\Model\PushLogModel();
        $m_push_log->addInfo($push_data);
        $this->output('推送成功', 'boxshell/apkindex', 2);
    }
}