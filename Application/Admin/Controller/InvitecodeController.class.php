<?php
namespace Admin\Controller;
/**
 *@desc 餐厅客户端生成手机邀请码
 * @author      zhang.yingtao
 * @version     1.2
 * @since       20171129
 */
use Admin\Controller\BaseController;

class InvitecodeController extends BaseController {
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
        
        
        $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
        $list = $m_hotel_invite_code->getList($fileds,$where,$orders,$start,$size);
        //print_r($list);exit;
        $this->assign('hotel_id',$hotel_id);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('index');
        
    }
    public function add(){
        
        $hotel_id =  I('get.hotel_id');
        $this->assign('hotel_id',$hotel_id);
        $this->display('add');
    }
    public function doadd(){

        $hotel_id = I('get.hotel_id'); 
        $userinfo = session('sysUserInfo');
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array();
        $where['id'] = $hotel_id;
        $hotel_info = $m_hotel->getInfo('name',$where);
        if(empty($hotel_info)){
            $this->error('该酒楼不存在');
        }
        $hotel_name = $hotel_info[0]['name'];
        $code_charter = '';
       
        $f_hotel_name = mb_substr($hotel_name, 0,1,'utf8');
        $s_hotel_name = mb_substr($hotel_name, 1,1,'utf8');
        //$code_charter = $pi
        
        $code_charter .=getFirstCharter($f_hotel_name);
        $code_charter .=getFirstCharter($s_hotel_name);
        $code_charter  = strtolower($code_charter);

        $st = '';
        $letter=range('a','z');
        $letter =array_flip($letter);
        for($a=1;$a<=2;$a++)
        {
            $num = array_rand($letter,1);
            $st .=$num;
        }

        /*if(empty($code_charter) || strlen($code_charter)!=2){
            $this->error('酒楼首字母错误');
        }*/
        $code_charter = strtolower($st);
        $data = array();
        $flag = 0;
        $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
        while ($flag <20){
            $code_num = generate_code(6);
            $invite_code = $code_charter.$code_num;
            $where = array();
            $where['code'] = $invite_code;
            $nums = $m_hotel_invite_code->countNums($where);
            if(!empty($nums)){
                continue;
            }
            $data[$flag]['code']     = $invite_code; 
            $data[$flag]['hotel_id'] = $hotel_id;
            $data[$flag]['creator_id']      = $userinfo['id'];
            
            $flag ++;
        }
        $ret = $m_hotel_invite_code->addInfo($data,2);
        if($ret){
            $this->output('添加成功', 'invitecode/index', 2);
        }else {
            $this->error('添加失败');
        }    
    }
    public function delete(){
        $id = I('id');
        $this->assign('id',$id);
        $this->display('delete');
    }
    
    public function dodelete(){
        
        $id = I('get.id');
        $where = $data = array();
        if($id){
            $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
            $where['id'] = $id;
            $data['flag'] = 1;
            $ret = $m_hotel_invite_code->where($where)->save($data);
            if($ret){
                $this->output('添加成功', 'invitecode/index', 2);
            }else {
                $this->error('删除失败');
            }
        }else {
            $this->error('参数错误');
        }
    }
}