<?php
/**
 * @desc    用户追踪
 * @author  zhang.yingtao
 * @since   2017-06-13
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class TestusertraceController extends BaseController{
    var $user_type ;
    /**
     * @desc 追踪用户列表
     */
    public function __construct() {
        parent::__construct();
        $this->user_type = array('1'=>'酒楼人员','2'=>'运营人员','3'=>'普通用户','4'=>'重点用户');
    }
    
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        
        $start  = ( $start-1 ) * $size;
        
        $where =' 1=1';
        $mobile_id = I('mobile_id','','trim');
        if($mobile_id){
            $where .=" and mobile_id like '%{$mobile_id}%'";
            $this->assign('mobile_id',$mobile_id);
        }
        $download_time =  I('download_time');
        if($download_time){
            $where .=" and download_time='".$download_time."'";
            $this->assign('download_time',$download_time);
        }
        $m_personas = new \Admin\Model\TestpersonasModel();
        $result = $m_personas->getList($where,$order,$start,$size);
        $m_user_mark = new \Admin\Model\UserMarkModel();
        foreach($result['list'] as $key=>$v){
            
            $info = $m_user_mark->getInfoByModelid($v['mobile_id']);
            if(!empty($info)){
               
                $user_type = $info['user_type'];
                
                $user_type = $this->user_type[$user_type];
                
                $result['list'][$key]['user_type'] = $user_type;
            }else {
                $result['list'][$key]['user_type'] = "该设备未被标记";
            }
        }
        
        
        
        $this->assign('list',$result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    /**
     * @desc 获取投屏列表明细
     */
    public function detail(){
        
        $mobile_id = I('mobile_id','','trim');
        if(empty($mobile_id)){
            $this->error('手机标识不能未空');
        }
        $m_user_sad_count = new \Admin\Model\UserSadCountModel();
        $field = 'hotel_name,projection_count,demand_count,date_time';
        $where['mobile_id'] = $mobile_id;
        
        $order =" date_time desc";
        $list = $m_user_sad_count->getwhere($field,$where,$order);
        $m_user_mark = new \Admin\Model\UserMarkModel();
        $info = $m_user_mark->getInfoByModelid($mobile_id);
        if(!empty($info)){
            $this->assign('user_type',$info['user_type']);
        }
        $this->assign('mobile_id',$mobile_id);
        $this->assign('list',$list);
        $this->display('detail');
    }
    
    /**
     * @desc 获取阅读列表明细
     */
    public function readdetail(){
        $mobile_id = I('mobile_id','','trim');
        
        if(empty($mobile_id)){
            $this->error('手机标识不能未空');
        }
        $m_user_read_count = new \Admin\Model\UserReadCountModel();
        $field = 'device_model,device_type,read_count,date_time';
        $where['mobile_id'] = $mobile_id;
        $where['read_count'] = array('GT',0);
        $order =" date_time desc";
        $list = $m_user_read_count->getwhere($field,$where,$order);
        $m_user_mark = new \Admin\Model\UserMarkModel();
        $info = $m_user_mark->getInfoByModelid($mobile_id);
        if(!empty($info)){
            $this->assign('user_type',$info['user_type']);
        }
        $this->assign('mobile_id',$mobile_id);
        $this->assign('list',$list);
        $this->display('readdetail');
    }
    /**
     * @desc 
     */
    public function markuser(){
        $mobile_id = I('mobile_id','','trim');
        if(empty($mobile_id)){
            $this->error('手机标识不能为空');
        }
        $user_type = I('user_type','0','intval');
        $m_user_mark = new \Admin\Model\UserMarkModel();
        $info = $m_user_mark->getInfoByModelid($mobile_id);
        if($user_type ==0){
            if(!empty($info)){
                $ret = $m_user_mark->where("mobile_id='".$mobile_id."'")->delete();
            }else {
                $this->error('改用户未被标记过');
            }
        }else {
            $type = $info['user_type'];
            switch ($type){
                case '1':
                    $users = '酒楼人员';
                    break;
                case '2':
                    $users = '运维人员';
                    break;
                case '3':
                    $users = '普通用户';
                    break;
                case '4':
                    $users = '重点用户';
                    break;
            }
            if(!empty($info)){
                $this->error("改用户已经标记为$users,不能再次标记");
            }else {
                $data = array();
                $data['mobile_id'] = $mobile_id;
                $data['user_type'] = $user_type;
                $data['add_date']  = date('Y-m-d H:i:s');
                $ret = $m_user_mark->add($data);
            }
        }
        if($ret){
            $this->output('标记成功', 'usertrace/index');
        }else {
            $this->error('标记失败');
        }
        
    }
}
