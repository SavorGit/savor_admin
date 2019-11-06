<?php
namespace Integral\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 销售端积分-任务管理
 *
 */
class TaskController extends BaseController {
    protected $integral_task_type;
    protected $system_task_content;
    public function __construct() {
        parent::__construct();
        $this->integral_task_type = C('integral_task_type');  
        $this->system_task_content = C('system_task_content');  
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
        
        $where = [];
        $where['flag'] = 1;
        
        $fields = 'a.id,a.name,a.type,a.create_time,a.update_time,user.remark user_name,a.status';
        $m_integral_task = new \Admin\Model\Integral\TaskModel();
        $list = $m_integral_task->getList($fields, $where, $orders, $start, $size);
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        foreach($list['list'] as $key=>$v){
            $count = $m_task_hotel->where(array('task_id'=>$v['id']))->count();
            $list['list'][$key]['hotel_num'] = $count;
        }
        //print_r($list['list']);exit;
        $this->assign('integral_task_type',$this->integral_task_type);
        
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        
        $this->display();
    }
    public function add(){
        
        if(IS_POST){
            
            
            $m_task = new \Admin\Model\Integral\TaskModel();
            $data = [];
            $data['name']     = I('post.name','','trim');
            $data['media_id'] = I('post.media_id',0,'intval');
            $data['type']     = I('post.type',0,'intval');        //任务类型
            $data['desc']     = I('post.desc','','trim');
            $data['start_time'] = I('post.start_time','','trim');
            $data['end_time'] = I('post.end_time','','trim');
            $data['is_long_time'] = I('post.is_long_time',0,'intval');
            $data['integral'] = I('post.integral',0,'intval');
            //print_r($data);exit;
            
            $this->checkMainParam($data);
            
            if($data['type']==1){//系统任务
                $task_content = [];
                $task_content['task_content_type'] = I('post.task_content_type',0,'intval'); //任务内容
                
                if($task_content['task_content_type']==1){//电视开机
                    $task_content['lunch_start_time']   = I('post.kj_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.kj_lunch_end_time');
                    $task_content['dinner_start_time']   = I('post.kj_dinner_start_time');
                    $task_content['dinner_end_time']     = I('post.kj_dinner_end_time');
                    $task_content['heart_time']['type'] = I('post.heart_time',0,'intval');
                    $task_content['heart_time']['value'] = I('post.heart_time_'.$task_content['heart_time']['type'],0,'intval');
                    
                }else if($task_content['task_content_type']==2){//电视互动
                    $task_content['lunch_start_time']   = I('post.hd_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.hd_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.hd_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.hd_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.max_daily_integral',0,'intval');
                    $task_content['user_interact']['type'] = I('post.user_interact',0,'intval');
                    $task_content['user_interact']['value'] = I('post.user_interact_'.$task_content['user_interact']['type'],0,'intval');
                }
                //print_r($task_content);exit;
                $this->chekInfoParam($task_content);
                //echo "ddd";exit;
                $data['task_info'] = json_encode($task_content);
            }
            $data['status'] = 0;
            $data['flag']   = 1;
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            $ret = $m_task->addData($data);
            
            if($ret){
                $this->output('添加成功', "task/index");
            }else {
                $this->output('添加失败', "task/index",2,0);
            }
            
        }else {
            $this->assign('integral_task_type',$this->integral_task_type);
            $this->assign('system_task_content',$this->system_task_content);
            $this->display();
        }
    }
    public function delete(){
        $id = I('get.id');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $where['id'] = $id;
        $data['flag']= 0 ;
        $ret = $m_task->updateData($where, $data);
        if($ret){
            $this->output('删除成功', "task/index",2);
        }else {
            $this->output('删除失败', "task/index",2,0);
        }
    }
    public function changeStatus(){
        $id = I('get.id');
        $status = I('get.status');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $where['id'] = $id;
        $data['status'] = $status;
        $data['update_time'] = date('Y-m-d H:i:s');
        $ret = $m_task->updateData($where, $data);
        if($ret){
            if($status==1) $msg = '上线成功';
            else $msg = '下线成功';
            $this->output($msg, "task/index",2);
        }else {
            if($status==1) $msg = '上线失败';
            else $msg = '下线失败';
            $this->output($msg, "task/index",2,0);
        }
    }
    public function edit(){
        $id = I('id',0,'intval');
        
        $m_task = new \Admin\Model\Integral\TaskModel();
        if(IS_POST){
            
            $data = [];
            $data['name']     = I('post.name','','trim');
            $data['media_id'] = I('post.media_id',0,'intval');
            $data['type']     = I('post.type',0,'intval');        //任务类型
            $data['desc']     = I('post.desc','','trim');
            $data['start_time'] = I('post.start_time','','trim');
            $data['end_time'] = I('post.end_time','','trim');
            $data['is_long_time'] = I('post.is_long_time',0,'intval');
            $data['integral'] = I('post.integral',0,'intval');
            //print_r($data);exit;
            
            $this->checkMainParam($data);
            if($data['type']==1){//系统任务
                $task_content = [];
                $task_content['task_content_type'] = I('post.task_content_type',0,'intval'); //任务内容
            
                if($task_content['task_content_type']==1){//电视开机
                    $task_content['lunch_start_time']   = I('post.kj_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.kj_lunch_end_time');
                    $task_content['dinner_start_time']   = I('post.kj_dinner_start_time');
                    $task_content['dinner_end_time']     = I('post.kj_dinner_end_time');
                    $task_content['heart_time']['type'] = I('post.heart_time',0,'intval');
                    $task_content['heart_time']['value'] = I('post.heart_time_'.$task_content['heart_time']['type'],0,'intval');
            
                }else if($task_content['task_content_type']==2){//电视互动
                    $task_content['lunch_start_time']   = I('post.hd_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.hd_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.hd_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.hd_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.max_daily_integral',0,'intval');
                    $task_content['user_interact']['type'] = I('post.user_interact',0,'intval');
                    $task_content['user_interact']['value'] = I('post.user_interact_'.$task_content['user_interact']['type'],0,'intval');
                }
                //print_r($task_content);exit;
                $this->chekInfoParam($task_content);
                //echo "ddd";exit;
                $data['task_info'] = json_encode($task_content);
            }
            $data['status'] = 0;
            $data['flag']   = 1;
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            $ret = $m_task->updateData(array('id'=>$id), $data);
            if($ret){
                $this->output('编辑成功', "task/index");
            }else {
                $this->output('编辑失败', "task/index",2,0);
            }
            
        }else{
            
            $where = [];
            $where['id'] = $id;
            $task_info = $m_task->getRow('*',$where);
            $task_content = json_decode($task_info['task_info'],true);
            
            $m_media = new \Admin\Model\MediaModel();
            $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
            
            $m_info = $m_media->getRow('oss_addr',array('id'=>$task_info['media_id']));
            $task_info['oss_addr'] = $oss_host.$m_info['oss_addr'];
            
            
            $this->assign('integral_task_type',$this->integral_task_type);
            $this->assign('system_task_content',$this->system_task_content);
            $this->assign('vinfo',$task_info);
            $this->assign('cinfo',$task_content);
            
            $this->display();
        }
    }
    public function selecthotel(){
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        $is_s = I('is_s');
        if(IS_POST && empty($is_s)){
            $task_id = I('post.task_id');
            
            $ids = I('post.ids');
            $data = [];
            $userinfo = session('sysUserInfo');
            $uid = $userinfo['id'];
            $create_time = date('Y-m-d H:i:s');
            foreach($ids as $key=> $v){
                $data[$key]['task_id'] = $task_id;
                $data[$key]['hotel_id']= $v;
                $data[$key]['uid']     = $uid;
                $data[$key]['create_time'] =$create_time;
            }
            $ret = $m_task_hotel->addAll($data);
            if($ret){
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                navTab.reloadFlag("task/index");
                alertMsg.correct("发布成功！");</script>';
                //$this->output('发布成功了!', 'task/index');
            }else {
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                navTab.reloadFlag("task/index");
                alertMsg.success("发布失败！");</script>';
                //$this->output('发布失败!', 'task/index');
            }
        }else{
            $area_id_arr = I('include_a');
            $in_task_id  = I('in_task_id',0,'intval');//所选任务包含酒楼
            
            $task_id = I('get.task_id',0,'intval');
            
            $where = [];
            $where['task_id'] = $task_id;
            $count = $m_task_hotel->where($where)->count();
            if(!empty($count)) {
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                alertMsg.error("该任务已选择酒楼！");</script>';
            }
            
            
            if($in_task_id){
                $fields = 'hotel.id hotel_id,hotel.name hotel_name,area.region_name,hotel.hotel_box_type,m.mobile';
                $where = [];
                $where['m.status']    = 1;
                $where['hotel.state'] = 1;
                $where['hotel.flag']  = 0;
                $where['a.task_id']   = $in_task_id;
                if(!empty($area_id_arr)){
                    $where['area.id'] = array('in',$area_id_arr);
                    $this->assign('include_ak',$area_id_arr);
                }
                $order = 'convert(hotel.name using gbk) asc';
                $hotel_list = $m_task_hotel->alias('a')
                                           ->join('savor_integral_merchant m on a.hotel_id=m.hotel_id','left')
                                           ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                                           ->join('savor_area_info area on area.id=hotel.area_id','left')
                                           ->field($fields)
                                           ->where($where)
                                           ->order($order)
                                           ->limit(0,50)
                                           ->select();
                $this->assign('in_task_id',$in_task_id);
            }else {
                $fields = 'hotel.id hotel_id,hotel.name hotel_name,area.region_name,hotel.hotel_box_type,a.mobile';
                $where = [];
                $where['a.status']    = 1;
                $where['hotel.state'] = 1;
                $where['hotel.flag']  = 0;
                if(!empty($area_id_arr)){
                    $where['area.id'] = array('in',$area_id_arr);
                    $this->assign('include_ak',$area_id_arr);
                }
                $order = 'convert(hotel.name using gbk) asc';
                //选择酒楼
                $m_merchant = new \Admin\Model\Integral\MerchantModel();
                $hotel_list = $m_merchant->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_area_info area on area.id=hotel.area_id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->select();
            }
            
            
            //城市列表
            $m_area_info = new \Admin\Model\AreaModel();
            $area_list = $m_area_info->getHotelAreaList();
            //机顶盒类型
            $hotel_box_type = C('hotel_box_type');
            //任务列表
            
            $task_list = $m_task_hotel->alias('a')
                                      ->join('savor_integral_task task on a.task_id=task.id','left')
                                      ->field('task.id,task.name')->where(array('flag'=>1))
                                      ->group('task.id')
                                      ->select();
            foreach($task_list as $key=>$v){
                $nums = $m_task_hotel->where(array('task_id'=>$v['id']))->count();
                $task_list[$key]['name'] .='('.$nums.'家酒楼)';
            }
            
            
            $this->assign('hotel_box_type',$hotel_box_type);
            $this->assign('area_list',$area_list);
            $this->assign('task_list',$task_list);
            
            $this->assign('list',$hotel_list);
            $this->assign('task_id',$task_id);
            $this->display();
        }
        
    }
    public function copy(){
        $task_id = I('get.task_id');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        $where = [];
        $where['id'] = $task_id;
        $where['flag']    = 1;
        
        $userinfo = session('sysUserInfo');
        $uid = $userinfo['id'];
        $fields = "name,media_id,type,desc,start_time,end_time,is_long_time,integral,separate_id,task_info";
        $task_info = $m_task->where($where)->getRow($fields,$where);
        if(empty($task_info)) $this->error('该任务不存在');
        
        
        $task_info['name'] = $task_info['name'].'-'.date('YmdHis');
        $task_info['uid']  = $uid;
        
        $ret = $m_task->addData($task_info);
        if($ret){
            $this->output('复制成功', "task/index",2);
        }else {
            $this->output('删除失败', "task/index",2,0);
        }
        /* $where  = [];
        $where['task_id'] = $task_id;
        
        $hotel_nums = $m_task_hotel->where($where)->count();
        if(!empty($hotel_nums)){
            $fields = 'hotel_id';
            $hotel_list = $m_task_hotel->field($fields)->where($where)->select();
            foreach($hotel_list as $key=>$v){
                $hotel_list[$key]['task_id'] = $ret;
                $hotel_list[$key]['uid'] = $uid;
            }
            
            $rts = $m_task_hotel->addAll($hotel_list);
            if($ret && $rts){
                $m_task->commit();
                $this->output('复制成功', "task/index",2);
                
            }else {
                $m_task->rollback();
                $this->error('复制失败');
            }
        }else {
            if($ret){
                $m_task->commit();
                $this->output('复制成功', "task/index",2);
                
            }else {
                $m_task->rollback();
                $this->error('复制失败');
            }
            
        }  */
    }
    public function gethotelinfo(){
        $task_id = I('get.task_id',0,'intval');
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        
        $where = [] ; 
        $where['a.task_id'] = $task_id;
        $fields = 'area.region_name,hotel.name hotel_name,hotel.addr,hotel.state';
        
        $order = 'convert(hotel.name using gbk) asc';
        $hotel_list = $m_task_hotel->alias('a')
                                   ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                                   ->join('savor_area_info area on hotel.area_id = area.id','left')
                                   ->where($where)
                                   ->field($fields)
                                   ->order($order)
                                   ->select();
        $this->assign('hotel_list',$hotel_list);             
        $this->display();
    }
    private function checkMainParam($data){
        //print_r($data);exit;
        if(empty($data['name'])) $this->error('请输入任务名称');
        if(empty($data['media_id'])) $this->error('请上传任务图标');
        if(empty($data['type'])) $this->error('请选择任务类型');
        if(empty($data['is_long_time'])){
            $now_time = date('Y-m-d H:i:s');
            if(empty($data['start_time']) || empty($data['end_time'])){
                $this->error('任务开始、结束时间不能为空');
            }
            if($data['end_time']<=$data['start_time']){
                
                $this->error('任务结束时间不能小于开始时间');
            }
            //if($data['end_time']<$now_time) $this->error('任务结束时间不能小于当前时间');
        }
        if(empty($data['integral'])) $this->error('请输入奖励积分');
    }
    private function chekInfoParam($data){
        if($data['task_content_type']==2){
            if(empty($data['max_daily_integral'])) $this->error('请输入每日积分上限');
        }else if($data['task_content_type']==1){
            if(empty($data['heart_time']['type'])) $this->error('请选择开机奖励类型');
            if(empty($data['heart_time']['value'])) $this->error('请输入开机时长');
        }
    }
}