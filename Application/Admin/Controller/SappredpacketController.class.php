<?php
/**
 * @author zhang.yingtao
 * @desc   小程序红包数据
 * @since  2019-03-04
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class SappredpacketController extends BaseController {
    private $order_status ;
    private $pay_type;
	public function __construct() {
	    $this->order_status = array('0'=>'未付款','1'=>'付款码到电视','2'=>'付款中','3'=>'付款失败',
            '4'=>'付款成功','5'=>'已抢完','6'=>'未抢完','7'=>'未抢完已退款');
	    $this->pay_type = array('0'=>'未知','10'=>'微信');
		parent::__construct();
	}
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $pagenum = I('pageNum',1);
        $order = I('_order','a.id');
        $sort = I('_sort','desc');
        $orders = $order.' '.$sort;
        $start  = ( $pagenum-1 ) * $size;
        $where = array();
        $where['a.isdel'] = 0;
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['box.state']   = 1;
        $where['box.flag']    = 0;
        $hotel_name = I('hotel_name','','trim');
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
            $this->assign('hotel_name',$hotel_name);
        }
        $box_mac    = I('box_mac','','trim');
        if($box_mac){
            $where['a.mac'] = $box_mac;
            $this->assign('box_mac',$box_mac);
        }
        $openid = I('openid','','trim');
        if($openid){
            $where['a.openid'] = $openid;
            $this->assign('openid',$openid);
        }
        $create_time = I('start_time','','trim');
        $end_time    = I('end_time','','trim');
        
        if($create_time && $end_time){
            $where['a.add_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
            $this->assign('start_time',$create_time);
            $this->assign('end_time',$end_time);
        } else if($create_time && empty($end_time)){
            
            $where['a.add_time'] = array(array('EGT',$create_time.' 00:00:00'));
            $this->assign('start_time',$create_time);
        }else if(empty($create_time) && !empty($end_time)){
            
            $where['a.add_time'] = array(array('ELT',$end_time.' 23:59:59'));
            
            $this->assign('end_time',$end_time);
        }
        
        $m_redpacket = new \Admin\Model\Smallapp\RedpacketModel();
        
        $fields ="user.avatarUrl,user.nickName,a.id,area.region_name,hotel.name hotel_name,room.name room_name,a.mac,a.total_fee,a.pay_fee,a.amount,
                  a.status,a.pay_time,a.pay_type,a.add_time";
        
        $data = $m_redpacket->getList($fields,$where, $orders, $start,$size);
        
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_redpacket_receive = new \Admin\Model\Smallapp\RedpacketReceiveModel();
        foreach($data['list'] as $key=>$v){
            $data['list'][$key]['order_status'] = $this->order_status[$v['status']];
            //扫码抢红包人数
            $map = array();
            $map['resource_id'] = $v['id'];
            $map['action']      = 121; 
            
            $rt = $m_forscreen_record->field('id')->where($map)->group('openid')->select();
            $data['list'][$key]['scan_nums'] = count($rt);
            if($v['status']>=4){
                $data['list'][$key]['pay_type'] = $this->pay_type[$v['pay_type']];
            }else {
                $data['list'][$key]['pay_type'] = '';
            }
            //红包被抢人数
            $data['list'][$key]['grab_nums'] = $m_redpacket_receive->countWhere(array('redpacket_id'=>$v['id']));
        }
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('pageNum',$pagenum);
        $this->assign('numPerPage',$size);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('Report/redpacket');
    }

    public function detail(){
        $id = I('get.id');
        $m_redpacket_receive = new \Admin\Model\Smallapp\RedpacketReceiveModel();
        $fields = 'user.avatarUrl,user.nickName,a.money,a.barrage,a.receive_time,a.add_time';
        $where = array();
        $where['a.redpacket_id'] =  $id;
        $order = 'a.id asc';
        $data = $m_redpacket_receive->getList($fields, $where, $order);
        
        $this->assign('data',$data);
        $this->display('Report/redpacketdetail');
    }
}