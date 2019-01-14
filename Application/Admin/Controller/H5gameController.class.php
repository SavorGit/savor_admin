<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class H5gameController extends BaseController{
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $game_id     = I('game_id',2,'intval');
	    $create_time = I('create_time');
	    $box_mac     = I('box_mac');
	    $hotel_name  = I('hotel_name');

	    $fields = "a.id,area.region_name,h.name hotel_name,room.name room_name,a.box_mac,
	               a.start_time,a.end_time,a.create_time,a.is_start";
	    $m_game_interact = new \Admin\Model\Smallapp\GameInteractModel(); 
	    $where['a.game_id'] = $game_id;
	    $where['box.state'] = 1;
	    $where['box.flag']  = 0;
	    $where['h.state']   = 1;
	    $where['h.flag']    = 0;
	    if($create_time){
	        $where['a.create_time'] =array(array('egt',$create_time." 00:00:00"),array('elt',$create_time." 23:59:59"));
	    }
	    if($box_mac){
	        $where['a.box_mac']  = $box_mac;
	    }
	    if($hotel_name){
	        $where['h.name'] = array('like',"%$hotel_name%");
	    }
	    
	    $list = $m_game_interact->getList($fields, $where, $orders, $start, $size);

	    $m_game_climbtree = new \Admin\Model\Smallapp\GameClimbtreeModel(); 
	    foreach($list['list'] as $key=>$v){
	        $map = array();
	        $map['activity_id'] = $v['id'];
	        $join_nums = $m_game_climbtree->countNum($map);
	        $list['list'][$key]['join_nums'] = $join_nums;
	        
	    }
	    
	    $this->assign('create_time',$create_time);
	    $this->assign('box_mac',$box_mac);
	    $this->assign('hotel_name',$hotel_name);
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    
	    $this->display('Report/h5gameindex');
    }
    /**
     * @desc 猴子爬树游戏详情
     */
    public function climbtree(){
        $activity_id = I('get.activity_id',0,'intval');
        
        $m_game_climbtree = new \Admin\Model\Smallapp\GameClimbtreeModel();
        $where = array();
        $where['a.activity_id'] = $activity_id;
        $fields = "user.openid,user.avatarUrl,user.nickName,a.rock_nums,a.rock_rate";
        
        $data = $m_game_climbtree->getList($fields, $where);
        
        $this->assign('data',$data);
        $this->display('Report/climbtree');
    }
}