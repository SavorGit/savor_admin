<?php
/**
 * @desc   活动
 * @author zhang.yingtao
 * @since  2017-10-16
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class InstallofferController extends BaseController{
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $m_offer_result = new \Admin\Model\OfferResultModel();
        $list = $m_offer_result->getList('',$orders,$start,$size);
        
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('index');
    }
    public function add(){
        $m_offer_device_params = new \Admin\Model\OfferDeviceModel();
        $where = array();
        $where['a.device_group'] =2;
        $where['a.state']  = 1;
        $order = 'a.id asc';
        $list = $m_offer_device_params->getDeviceList('a.id,b.cost_price,b.market_price',$where,$order);
        $this->assign('list',$list);
        
        $where = array();
        $where['a.device_group'] =3;
        $where['a.state']  = 1;
        $order = 'a.id asc';
        $info = $m_offer_device_params->getDeviceList('a.id,b.cost_price,b.market_price',$where,$order);
        $this->assign('info',$info);
        $this->display('add');
    }
    public function doadd(){
        //$this->output('修改成功!', 'installoffer/add',2);
        $m_offer_result = new \Admin\Model\OfferResultModel();
        
        $now = date('Y-m-d H:i:s');
        $userInfo = session('sysUserInfo');
        $loginId = $userInfo['id'];
        $hotel_name = I('post.hotel_name');
        $result_arr = array('name'=>"网络设备报价",'hotel_name'=>$hotel_name,'create_time'=>$now,'update_time'=>$now,'creator_id'=>$loginId);
        $ret = $m_offer_result->addInfo($result_arr);
        if($ret){
            
            $info = I('post.info');   //A网络设备
            $m_offer_result_detail = new \Admin\Model\OfferResultDetailModel();
            foreach($info as $key=>$v){
                $data = array();
                $data['result_id'] = $ret;
                $data['device_id'] = $v['device_id'];
                $data['params_id'] = $v['params_id'];
                $data['nums']      = $v['nums'];
                $data['market_price'] = $v['market_price'];
                $data['our_price'] = $v['our_price'];
                $data['type'] = 1;
                $m_offer_result_detail->addInfo($data);
            }
            
            
            //插入数据库
            
            
            $message = I('post.message');    //B材料、安装、匹配、调试
            foreach($message as $key=>$v){
                $data = array();
                $data['result_id'] = $ret;
                $data['device_id'] = 0;
                $data['params_id'] = 0;
                $data['nums'] = $v['nums'];
                $data['market_price'] = $v['market_price'];
                $data['our_price'] = $v['our_price'];
                $data['type'] = 2; 
                $m_offer_result_detail->addInfo($data);
            }
            //插入数据库
            
            $data = array();
            $hardware = I('post.hardware');  //D硬件费用
            foreach($hardware as $key=>$v){
                $data = array();
                $data['result_id'] = $ret;
                $data['device_id'] = 0;
                $data['params_id'] = 0;
                $data['nums'] = $v['nums'];
                $data['market_price'] = $v['market_price'];
                $data['our_price'] = $v['our_price'];
                $data['type'] = 3; 
                $m_offer_result_detail->addInfo($data);
            }
            //插入数据库
            
            
            $data = array();
            $software = I('post.softwart');  //E软件费用
            foreach($software as $key=>$v){
                $data = array();
                $data['result_id'] = $ret;
                $data['device_id'] = 0;
                $data['params_id'] = 0;
                $data['nums'] = $v['nums'];
                $data['market_price'] = $v['market_price'];
                $data['our_price'] = $v['our_price'];
                $data['type'] = 4; 
                $m_offer_result_detail->addInfo($data);
            }
            //插入数据库
            
            
        }
        $this->output('添加成功!', 'installoffer/add',2);
    }
    public function getAllDevice(){
        $m_offer_device = new \Admin\Model\OfferDeviceModel();
        $list = $m_offer_device->getAllDevice($device_group=1);
        echo json_encode($list);
        exit;
    }
    public function getStandard(){
        $device_id = I('get.id',0,'intval');
        $m_offer_device_params = new \Admin\Model\OfferDeviceParamsModel(); 
        $list = $m_offer_device_params->getStandardList($device_id);
        echo json_encode($list);
        exit;
    }
    public function getParams(){
        $params_id = I('get.id',0,'intval');
        $m_offer_device_params = new \Admin\Model\OfferDeviceParamsModel(); 
        $list = $m_offer_device_params->getStandardInfo($params_id);
        echo json_encode($list);
        exit;
    }
    
}