<?php
namespace Admin\Controller;
/**
 *@desc 餐厅客户端推荐菜
 * @author      zhang.yingtao
 * @version     1.2
 * @since       20171130
 */
use Admin\Controller\BaseController;
use Common\Lib\SavorRedis;
class RecfoodController extends BaseController {
    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $hotel_id = I('hotel_id',0,'intval');
        $this->assign('hotel_id',$hotel_id);
        $fileds = 'a.*,b.remark';
        $where = ' a.hotel_id='.$hotel_id.' and flag = 0';
        
        
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $list = $m_hotel_recommend_food->getList($fileds,$where,$orders,$start,$size);
        //print_r($list);exit;
        $this->assign('hotel_id',$hotel_id);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('index');
    }
    public function add(){
        $hote_id = I('hotel_id');
        $this->assign('hotel_id',$hote_id);
        $this->display('add');
        
    }
    public function doadd(){
        $name = I('post.name');
        $media_id = I('post.media_id');
        $select_media_id =  I('post.select_media_id');
        $hotel_id = I('post.hotel_id');
        $userinfo = session('sysUserInfo');
        $data = array();
        $data['name'] = $name;
        //$data['media_id']= $media_id;
        $data['big_media_id'] = $select_media_id;
        $data['hotel_id'] = $hotel_id;
        $data['creator_id']= $userinfo['id'];
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $ret = $m_hotel_recommend_food->addInfo($data);
        if($ret){
            $this->output('添加成功', 'recfood/index', 1);
        }else {
            $this->error('添加失败');
        }
    }
    public function edit(){
        
        $id = I('get.id');
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $where['a.id'] = $id;
        $where['a.flag']= 0;
        $info = $m_hotel_recommend_food->getInfo('a.*,b.oss_addr,c.oss_addr select_oss_addr',$where);
        
        if(empty($info)){
            $this->error('该推荐菜不存在');
        }
        $info['oss_addr'] = $this->oss_host.$info['oss_addr'];
        $info['select_oss_addr'] = $this->oss_host.$info['select_oss_addr'];
        $this->assign('vinfo',$info);
        $this->display('edit');
    }
    public function doedit(){
        $id = I('post.id');
        $where['id'] = $id;
        $where['flag']= 0;
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $info = $m_hotel_recommend_food->getOne('id,state',$where);
        if(empty($info)){
            $this->error('该推荐菜不存在');
        }
        $data = array();
        $data['name'] = I('post.name');
        //$data['media_id'] = I('post.media_id');
        $data['big_media_id'] = I('post.select_media_id');
        $ret = $m_hotel_recommend_food->saveInfo($where, $data);
        if($ret){
            $tmp_hotel_arr = getVsmallHotelList();
            foreach($tmp_hotel_arr as $key=>$v){
                if($info['hotel_id']==$v && $info['state']==1){
                    sendTopicMessage($info['hotel_id'], 12);
                    break;
                }
            }
            $this->output('修改成功', 'recfood/index', 1);
        }else {
            $this->error('修改失败');
        }
    }
    public function editstate(){
        $id = I('get.id');
        $state = I('get.state');
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $where['id'] = $id;
        $where['flag']= 0;
        $info = $m_hotel_recommend_food->getOne('id,state,hotel_id',$where);
        if(empty($info)){
            $this->error('该推荐菜不存在');  
        }
        if($state == $info['state']){
            if($state ==0){
                $this->error('该推荐菜已下线，请勿重复操作');
            }else if($state==1){
                $this->error('该推荐菜已上线，请勿重复操作');
            }
        }
        $data = array();
        $data['state'] = $state;
        $ret = $m_hotel_recommend_food->saveInfo($where, $data);
        if($ret){
            $tmp_hotel_arr = getVsmallHotelList();
            foreach($tmp_hotel_arr as $key=>$v){
                if($info['hotel_id']==$v){
                    sendTopicMessage($info['hotel_id'], 12);
                    break;
                }
            }
            
            $this->output('修改成功', 'recfood/index', 2);
        }else {
            $this->error('修改失败');
        }
        
    }
    public function delete(){
        $id = I('get.id');
        $where['id'] = $id;
        $where['flag']= 0;
        $m_hotel_recommend_food = new \Admin\Model\HotelRecommendFoodModel();
        $info = $m_hotel_recommend_food->getOne('id,hotel_id,state',$where);
        if(empty($info)){
            $this->error('该推荐菜不存在');
        }
        $data = array();
        $data['flag'] = 1;
        $ret = $m_hotel_recommend_food->saveInfo($where, $data);
        if($ret){
            $tmp_hotel_arr = getVsmallHotelList();
            foreach($tmp_hotel_arr as $key=>$v){
                if($info['hotel_id']==$v && $info['state']==1){
                    sendTopicMessage($info['hotel_id'], 12);
                    break;
                }
            }
            $this->output('删除成功', 'recfood/index', 2);
        }else {
            $this->error('删除失败');
        }
    }
}