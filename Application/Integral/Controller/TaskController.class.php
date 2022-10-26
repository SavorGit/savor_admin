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
        $all_task_types = C('integral_task_type');
        unset($all_task_types['2']);
        $this->integral_task_type = $all_task_types;
        $this->system_task_content = C('system_task_content');
    }
    public function index(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $status = I('status',99,'intval');
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $task_id = I('task_id','');

        $where = array('a.flag'=>1);
        if($status!=99){
            $where['a.status'] = $status;
        }
        if(!empty($task_id)){
            $where['a.id'] = intval($task_id);
        }
        $fields = 'a.id,a.name,a.type,a.task_type,a.create_time,a.update_time,user.remark user_name,euser.remark e_user_name,a.status';
        $m_integral_task = new \Admin\Model\Integral\TaskModel();
        $orders = $order.' '.$sort;
        $start = ($page-1 ) * $size;
        $list = $m_integral_task->getList($fields, $where, $orders, $start, $size);
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        foreach($list['list'] as $key=>$v){
            $count = $m_task_hotel->where(array('task_id'=>$v['id']))->count();
            $list['list'][$key]['hotel_num'] = $count;
        }
        $this->assign('task_id',$task_id);
        $this->assign('status',$status);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $this->assign('integral_task_type',C('integral_task_type'));
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        
        $this->display();
    }

    public function addactivitymoney(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $is_edit = 0;
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $money = I('post.money',0,'intval');
            $cvr = I('post.cvr',0);
            $activity_day = I('post.activity_day',0,'intval');
            $interact_num = I('post.interact_num',0,'intval');
            $comment_num = I('post.comment_num',0,'intval');
            $people_num = I('post.people_num',1,'intval');
            $lottery_num = I('post.lottery_num',0,'intval');
            $task_info = I('post.task_info','');
            if(empty($task_info)){
                $this->output('请勾选任务种类', "task/addactivitymoney",2,0);
            }
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $type = 2;
            $task_type = 21;
            $data = array('name'=>$name,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,'money'=>$money,
                'cvr'=>$cvr,'activity_day'=>$activity_day,'interact_num'=>$interact_num,'comment_num'=>$comment_num,'people_num'=>$people_num,
                'lottery_num'=>$lottery_num,'task_info'=>json_encode($task_info),'start_time'=>$start_time,'end_time'=>$end_time,'status'=>0,'flag'=>1);
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            if(in_array('lottery',$task_info) && $lottery_num==0){
                $this->output('已勾选邀请抽奖，请填写抽奖参与抽奖人数', "task/addactivitymoney",2,0);
            }
            if($id){
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($data['task_info']!=$res_task_info['task_info']){
                        $this->output('任务已下发,请勿修改任务种类', "task/addactivitymoney",2,0);
                    }
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $m_task->add($data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array('task_info'=>'','md5'=>'','people_num'=>1);
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                $vinfo['task_info'] = join(',',json_decode($vinfo['task_info'],true));
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0) {
                    $is_edit = 1;
                }
            }
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addactivitylottery(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $wmedia_id = I('post.wmedia_id',0,'intval');
            $tv_media_id = I('post.tv_media_id',0,'intval');
            $portraitmedia_id = I('post.portraitmedia_id',0,'intval');
            $task_integral = I('post.task_integral',0,'intval');
            $integral = I('post.integral',0,'intval');
            $people_num = I('post.people_num',0,'intval');
            $boot_num = I('post.boot_num',0,'intval');
            $hotel_id = I('post.hotel_id',0,'intval');
            $staff_id = I('post.staff_id',0,'intval');
            $is_test = I('post.is_test',0,'intval');
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $type = 2;
            $task_type = 23;
            $data = array('name'=>$name,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,'task_integral'=>$task_integral,
                'integral'=>$integral,'people_num'=>$people_num,'start_time'=>$start_time,'end_time'=>$end_time,
                'status'=>0,'flag'=>1,'is_test'=>$is_test);
            $m_media = new \Admin\Model\MediaModel();
            if($wmedia_id){
                $res_media = $m_media->getMediaInfoById($wmedia_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            if($portraitmedia_id){
                $res_media = $m_media->getMediaInfoById($portraitmedia_id);
                $data['portrait_image_url'] = $res_media['oss_path'];
            }
            if($tv_media_id){
                $res_media = $m_media->getMediaInfoById($tv_media_id);
                $data['tv_image_url'] = $res_media['oss_path'];
            }

            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
            if($id){
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $hotel_data = array('task_id'=>$id,'hotel_id'=>$hotel_id,'staff_id'=>$staff_id,'boot_num'=>$boot_num);
                    $m_task_hotel->updateData(array('id'=>$res_task_hotel['list'][0]['id']),$hotel_data);
                    /*
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($res_task_info['status']==1 && $res_task_info['flag'==1]){
                        $this->output('任务已下发,请勿修改酒楼', "task/addactivitymoney",2,0);
                    }
                    */
                }else{
                    $hotel_data = array('task_id'=>$id,'hotel_id'=>$hotel_id,'staff_id'=>$staff_id,'boot_num'=>$boot_num);
                    $m_task_hotel->add($hotel_data);
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $task_id = $m_task->add($data);
                $hotel_data = array('task_id'=>$task_id,'hotel_id'=>$hotel_id,'staff_id'=>$staff_id,'boot_num'=>$boot_num);
                $m_task_hotel->add($hotel_data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array('task_info'=>'','people_num'=>3,'is_test'=>0);
            $hotel_id = 0;
            $is_edit = 0;
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                $oss_host = get_oss_host();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                if($vinfo['image_url']){
                    $vinfo['image_url'] = $oss_host.$vinfo['image_url'];
                }
                if($vinfo['portrait_image_url']){
                    $vinfo['portrait_image_url'] = $oss_host.$vinfo['portrait_image_url'];
                }
                if($vinfo['tv_image_url']){
                    $vinfo['tv_image_url'] = $oss_host.$vinfo['tv_image_url'];
                }
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc');
                if(!empty($res_task_hotel)){
                    $hotel_id = $res_task_hotel[0]['hotel_id'];
                    $vinfo['staff_id'] = $res_task_hotel[0]['staff_id'];
                    $vinfo['boot_num'] = $res_task_hotel[0]['boot_num'];
                }
            }
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('a.status'=>1,'hotel.state'=>1,'hotel.flag'=>0);
            $fields = 'hotel.id as hotel_id,hotel.name';
            $merchants = $m_merchant->getMerchants($fields,$where,'a.id desc');
            foreach ($merchants as $k=>$v){
                if($hotel_id == $v['hotel_id']){
                    $merchants[$k]['is_select'] = 'selected';
                }else{
                    $merchants[$k]['is_select'] = '';
                }
            }
            $staff_list = array();
            if($hotel_id){
                $m_staff = new \Admin\Model\Integral\StaffModel();
                $where = array('m.hotel_id'=>$hotel_id,'m.status'=>1,'a.status'=>1);
                $fields = 'a.id,u.nickName as uname';
                $staff_list = $m_staff->getMerchantStaffUserList($fields,$where);
            }
            $this->assign('merchants',$merchants);
            $this->assign('staff_list',$staff_list);
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function prizelist(){
        $task_id = I('task_id',0,'intval');
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_prize = new \Admin\Model\Integral\TaskprizeModel();
        $where = array('task_id'=>$task_id);
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_prize->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            if($v['status']==1){
                $data_list[$k]['statusstr'] = '可用';
            }else{
                $data_list[$k]['statusstr'] = '不可用';
            }
            $data_list[$k]['name'] = $v['level'].'、'.$v['name'];
            $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
        }
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('status',$status);
        $this->assign('task_id',$task_id);
        $this->display();
    }

    public function prizeadd(){
        $id = I('id',0,'intval');
        $task_id = I('task_id',0,'intval');
        $m_prize = new \Admin\Model\Integral\TaskprizeModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $amount = I('post.amount',0,'intval');
            $status = I('post.status',1,'intval');
            $level = I('post.level',1,'intval');

            $data = array('task_id'=>$task_id,'name'=>$name,'amount'=>$amount,'level'=>$level,'status'=>$status);
            if($media_id){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            if($id){
                $m_prize->updateData(array('id'=>$id),$data);
            }else{
                $m_prize->add($data);
            }
            $this->output('操作成功!', 'task/prizelist');
        }else{
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_prize->getInfo(array('id'=>$id));
                $vinfo['oss_addr'] = $oss_host.$vinfo['image_url'];
                $task_id = $vinfo['task_id'];
            }else{
                $vinfo = array('status'=>1);
            }
            $all_levels = array('1'=>'一等奖','2'=>'二等奖','3'=>'三等奖');
            $this->assign('vinfo',$vinfo);
            $this->assign('all_levels',$all_levels);
            $this->assign('task_id',$task_id);
            $this->display();
        }
    }

    public function addactivitygroupbuysale(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $is_edit = 0;
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $goods_id = I('post.goods_id',0,'intval');
            $integral = I('post.integral',0,'intval');
            $money = I('post.money',0,'intval');
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $type = 2;
            $task_type = 24;
            $data = array('name'=>$name,'goods_id'=>$goods_id,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,'integral'=>$integral,
                'money'=>$money,'start_time'=>$start_time,'end_time'=>$end_time,'status'=>0,'flag'=>1);
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            if($id){
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($data['task_info']!=$res_task_info['task_info']){
                        $this->output('任务已下发,请勿修改任务种类', "task/addactivitymoney",2,0);
                    }
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $m_task->add($data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array('task_info'=>'','md5'=>'','people_num'=>3);
            $goods_id = 0;
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0) {
                    $is_edit = 1;
                }
                $goods_id = $vinfo['goods_id'];
            }
            $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
            $all_goods = $m_dishgoods->getDataList('id,name',array('type'=>42,'status'=>1),'id desc');
            foreach ($all_goods as $k=>$v){
                if($v['id']==$goods_id){
                    $all_goods[$k]['is_select'] = 'selected';
                }else{
                    $all_goods[$k]['is_select'] = '';
                }
            }
            $this->assign('all_goods',$all_goods);
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addactivitysale(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $is_edit = 0;
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $goods_id = I('post.goods_id',0,'intval');
            $task_integral = I('post.task_integral',0,'intval');
            $integral = I('post.integral',0,'intval');
            $people_num = I('post.people_num',0,'intval');
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $type = 2;
            $task_type = 22;
            $data = array('name'=>$name,'goods_id'=>$goods_id,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,'task_integral'=>$task_integral,
                'integral'=>$integral,'people_num'=>$people_num,'start_time'=>$start_time,'end_time'=>$end_time,
                'status'=>0,'flag'=>1);
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            if($id){
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($data['task_info']!=$res_task_info['task_info']){
                        $this->output('任务已下发,请勿修改任务种类', "task/addactivitymoney",2,0);
                    }
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $m_task->add($data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array('task_info'=>'','md5'=>'','people_num'=>3);
            $goods_id = 0;
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0) {
                    $is_edit = 1;
                }
                $goods_id = $vinfo['goods_id'];
            }
            $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
            $all_goods = $m_dishgoods->getDataList('id,name',array('type'=>41,'status'=>1),'id desc');
            foreach ($all_goods as $k=>$v){
                if($v['id']==$goods_id){
                    $all_goods[$k]['is_select'] = 'selected';
                }else{
                    $all_goods[$k]['is_select'] = '';
                }
            }
            $this->assign('all_goods',$all_goods);
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addinvitevip(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $is_edit = 0;
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $invite_vip_reward_saler = I('post.invite_vip_reward_saler',0,'intval');
            $buy_reward_saler = I('post.buy_reward_saler',0,'intval');
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $type = 2;
            $task_type = 26;
            $task_info = array('invite_vip_reward_saler'=>$invite_vip_reward_saler,'buy_reward_saler'=>$buy_reward_saler);
            $data = array('name'=>$name,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,
                'start_time'=>$start_time,'end_time'=>$end_time,'task_info'=>json_encode($task_info),'status'=>0,'flag'=>1);
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            if($id){
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($data['task_info']!=$res_task_info['task_info']){
                        $this->output('任务已下发,请勿修改任务信息', "task/addinvitevip",2,0);
                    }
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $m_task->add($data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array();
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0) {
                    $is_edit = 1;
                }
                $task_info = json_decode($vinfo['task_info'],true);
                $vinfo['invite_vip_reward_saler'] = $task_info['invite_vip_reward_saler'];
                $vinfo['buy_reward_saler'] = $task_info['buy_reward_saler'];
            }
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addactivitydemandadv(){
        $id = I('id',0,'intval');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $is_edit = 0;
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $integral = I('post.integral',0,'intval');
            $desc = I('post.desc','','trim');
            $start_time = I('post.start_time','0000-00-00 00:00:00','trim');
            $end_time = I('post.end_time','0000-00-00 00:00:00','trim');

            $lunch_start_time   = I('post.lunch_start_time');
            $lunch_end_time     = I('post.lunch_end_time');
            $dinner_start_time  = I('post.dinner_start_time');
            $dinner_end_time    = I('post.dinner_end_time');
            $max_daily_integral = I('post.max_daily_integral',0,'intval');
            $hotel_max_rate = I('post.hotel_max_rate',0);
            $room_num = I('post.room_num',0,'intval');
            $box_finish_num = I('post.box_finish_num',0,'intval');
            $interval_time = I('post.interval_time',0,'intval');
            $ads_id = I('post.ads_id',0,'intval');
            if($box_finish_num>1){
                if($interval_time==0){
                    $this->output('请设置任务生效间隔时间', "task/addactivitydemandadv",2,0);
                }
            }

            $type = 2;
            $task_type = 25;
            $task_info = array('lunch_start_time'=>$lunch_start_time,'lunch_end_time'=>$lunch_end_time,'dinner_start_time'=>$dinner_start_time,
                'dinner_end_time'=>$dinner_end_time,'max_daily_integral'=>$max_daily_integral,'hotel_max_rate'=>$hotel_max_rate,'room_num'=>$room_num,
                'box_finish_num'=>$box_finish_num,'interval_time'=>$interval_time,'ads_id'=>$ads_id);
            $data = array('name'=>$name,'media_id'=>$media_id,'type'=>$type,'task_type'=>$task_type,'integral'=>$integral,
                'start_time'=>$start_time,'end_time'=>$end_time,'task_info'=>json_encode($task_info),'status'=>0,'flag'=>1);

            $this->chekInfoParam($task_info,$data);

            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            if(!empty($desc)){
                $data['desc'] = $desc;
            }
            if($id){
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0){
                    $res_task_info = $m_task->getInfo(array('id'=>$id));
                    if($data['task_info']!=$res_task_info['task_info']){
                        $this->output('任务已下发,请勿修改任务信息', "task/addactivitydemandadv",2,0);
                    }
                }
                unset($data['uid']);
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['e_uid'] = $userinfo['id'];
                $m_task->updateData(array('id'=>$id),$data);
            }else{
                $m_task->add($data);
            }
            $this->output('添加成功', "task/index");
        }else{
            $vinfo = array('task_info'=>array('lunch_start_time'=>'11:30','lunch_end_time'=>'13:30',
                'dinner_start_time'=>'18:30','dinner_end_time'=>'20:00','hotel_max_rate'=>'1.8')
            );
            $now_ads_id = 0;
            if($id){
                $vinfo = $m_task->getInfo(array('id'=>$id));
                $m_media = new \Admin\Model\MediaModel();
                if($vinfo['media_id']){
                    $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                    $vinfo['oss_addr'] = $res_media['oss_addr'];
                }
                $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
                $res_task_hotel = $m_task_hotel->getDataList('*',array('task_id'=>$id),'id desc',0,1);
                if($res_task_hotel['total']>0) {
                    $is_edit = 1;
                }
                $vinfo['task_info'] = json_decode($vinfo['task_info'],true);
                $now_ads_id = $vinfo['task_info']['ads_id'];
            }
            $m_pub_ads = new \Admin\Model\PubAdsModel();
            $field = 'pads.id as pub_ads_id,pads.create_time,pads.ads_id,ads.name as ads_name';
            $where = array('pads.is_remove'=>0,'pads.state'=>array('neq',2));
            $where['pads.end_date'] = array('egt',date('Y-m-d'));
            $res_ads = $m_pub_ads->getPubAdsList($field, $where,'pads.id desc');
            foreach ($res_ads as $k=>$v){
                $is_select = '';
                if($v['ads_id']==$now_ads_id){
                    $is_select = 'selected';
                }
                $res_ads[$k]['is_select'] = $is_select;
            }

            $this->assign('ads_list',$res_ads);
            $this->assign('is_edit',$is_edit);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function add(){
        if(IS_POST){
            $m_task = new \Admin\Model\Integral\TaskModel();
            $data = [];
            $data['name']     = I('post.name','','trim');
            $data['media_id'] = I('post.media_id',0,'intval');
            $data['type']     = I('post.type',0,'intval');        //任务类型
            $data['desc']     = I('post.desc','','trim');
            $data['start_time'] = I('post.start_time','0000-00-00 00:00:00','trim') ? I('post.start_time') : '0000-00-00 00:00:00';
            $data['end_time'] = I('post.end_time','0000-00-00 00:00:00','trim') ? I('post.end_time') : '0000-00-00 00:00:00';
            $data['is_long_time'] = I('post.is_long_time',0,'intval');
            $data['integral'] = I('post.integral',0,'intval');
            $data['is_shareprofit'] = I('post.is_shareprofit',0,'intval');
            $data['task_type'] = I('post.task_content_type',0,'intval');
            $this->checkMainParam($data);
            
            if($data['type']==1){//系统任务
                $task_content = array();
                $task_content['task_content_type'] = I('post.task_content_type',0,'intval'); //任务内容
                if($task_content['task_content_type']==1){//电视开机
                    $task_content['lunch_start_time']   = I('post.kj_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.kj_lunch_end_time');
                    $task_content['dinner_start_time']   = I('post.kj_dinner_start_time');
                    $task_content['dinner_end_time']     = I('post.kj_dinner_end_time');
                    $task_content['heart_time']['type'] = I('post.heart_time',0,'intval');
                    $task_content['heart_time']['value'] = I('post.heart_time_'.$task_content['heart_time']['type'],0,'intval');
                }elseif($task_content['task_content_type']==2){//电视互动
                    $task_content['lunch_start_time']   = I('post.hd_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.hd_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.hd_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.hd_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.max_daily_integral',0,'intval');
                    $task_content['user_interact']['type'] = I('post.user_interact',0,'intval');
                    $task_content['user_interact']['value'] = I('post.user_interact_'.$task_content['user_interact']['type'],0,'intval');
                }elseif($task_content['task_content_type']==3){//活动推广
                    $task_content['lunch_start_time']   = I('post.activity_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.activity_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.activity_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.activity_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.activity_max_daily_integral',0,'intval');
                    $task_content['user_promote']['type'] = I('post.user_promote',0,'intval');
                    $task_content['user_promote']['value'] = I('post.user_promote_'.$task_content['user_promote']['type'],0,'intval');
                }elseif($task_content['task_content_type']==4){//邀请食客评价
                    $task_content['lunch_start_time']   = I('post.comment_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.comment_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.comment_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.comment_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.comment_max_daily_integral',0,'intval');
                    $task_content['user_comment']['type'] = I('post.comment_promote',0,'intval');
                    $task_content['user_comment']['value'] = I('post.comment_promote_'.$task_content['user_comment']['type'],0,'intval');
                }elseif($task_content['task_content_type']==5){//打赏补贴
                    $task_content['lunch_start_time']   = I('post.reward_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.reward_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.reward_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.reward_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.reward_max_daily_integral',0,'intval');
                    $task_content['user_reward']['type'] = I('post.reward_promote',0,'intval');
                    $task_content['user_reward']['value'] = I('post.reward_promote_'.$task_content['user_reward']['type'],0,'intval');
                }elseif($task_content['task_content_type']==6){//邀请函
                    $task_content['lunch_start_time']   = I('post.invite_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.invite_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.invite_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.invite_dinner_end_time');
                    $task_content['user_reward']['week_num'] = I('post.week_num',0,'intval');
                    $task_content['user_reward']['room_num'] = I('post.room_num',0,'intval');
                }
                $this->chekInfoParam($task_content,$data);
                $data['task_info'] = json_encode($task_content);
            }
            $data['status'] = 0;
            $data['flag']   = 1;
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];

            if($data['is_shareprofit']){
                $shareprofit_level1 = I('post.shareprofit_level1',0,'intval');
                $shareprofit_level2 = I('post.shareprofit_level2',0,'intval');
                if($shareprofit_level1+$shareprofit_level2!=100){
                    $this->output('分润设置不合理', "task/index",2,0);
                }
            }
            $ret = $m_task->addData($data);
            if($ret){
                if($data['is_shareprofit']){
                    $shareprofit_level1 = I('post.shareprofit_level1',0,'intval');
                    $shareprofit_level2 = I('post.shareprofit_level2',0,'intval');
                    $m_taskshareprofit = new \Admin\Model\Integral\TaskShareprofitModel();
                    $add_data = array('task_id'=>$ret,'level1'=>$shareprofit_level1,'level2'=>$shareprofit_level2);
                    $m_taskshareprofit->add($add_data);
                }
                $this->output('添加成功', "task/index");
            }else {
                $this->output('添加失败', "task/index",2,0);
            }
        }else {
            $cinfo = array('user_reward'=>array('hotel_max_rate'=>'1.6'));
            $this->assign('integral_task_type',$this->integral_task_type);
            $this->assign('system_task_content',$this->system_task_content);
            $this->assign('cinfo',$cinfo);
            $this->display();
        }
    }

    public function delete(){
        $id = I('get.id');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $userinfo = session('sysUserInfo');
        $where['id'] = $id;
        $data['flag']= 0 ;
        $data['e_uid'] = $userinfo['id'];
        $ret = $m_task->updateData($where, $data);
        if($ret){
            $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
            $m_task_hotel->delData(array('task_id'=>$id));
            $this->output('删除成功', "task/index",2);
        }else {
            $this->output('删除失败', "task/index",2,0);
        }
    }

    public function changeStatus(){
        $id = I('get.id');
        $status = I('get.status');
        $m_task = new \Admin\Model\Integral\TaskModel();
        $res_task = $m_task->getInfo(array('id'=>$id));
        if($status==1 && $res_task['task_type']==23){
            $m_taskprize = new \Admin\Model\Integral\TaskprizeModel();
            $res_prizes = $m_taskprize->getDataList('id',array('task_id'=>$id),'id desc');
            if(empty($res_prizes)){
                $this->output('请先配置奖项再次领取', "task/index",2,0);
            }
        }
        $where['id'] = $id;
        $data['status'] = $status;
        $data['update_time'] = date('Y-m-d H:i:s');
        $ret = $m_task->updateData($where, $data);
        if($ret){
            if($status==1) $msg = '上线成功';
            else $msg = '下线成功';
            if($res_task['type']==2){
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(14);
                if($res_task['task_type']==21){
                    $cache_key = C('SAPP_SALE').'openmoneytask:'.date('Ymd').':*';
                    $keys_arr = $redis->keys($cache_key);
                    if(!empty($keys_arr)){
                        foreach($keys_arr as $key=>$v){
                            $redis->remove($v);
                        }
                    }
                }
                if($res_task['task_type']==24){
                    $m_taskgoods = new \Admin\Model\Integral\TaskHotelModel();
                    $twhere = array('task.goods_id'=>$res_task['goods_id'],'task.task_type'=>24);
                    $res_hotelgoods = $m_taskgoods->getHotelTaskGoodsList('a.hotel_id',$twhere,'a.id asc');
                    if(!empty($res_hotelgoods)){
                        $goods_program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');
                        foreach ($res_hotelgoods as $v){
                            $program_key = $goods_program_key.":{$v['hotel_id']}";
                            $period = getMillisecond();
                            $period_data = array('period'=>$period);
                            $redis->set($program_key,json_encode($period_data));
                        }
                    }
                }
            }
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
            $data = array();
            $data['name']     = I('post.name','','trim');
            $data['media_id'] = I('post.media_id',0,'intval');
            $data['type']     = I('post.type',0,'intval');        //任务类型
            $data['desc']     = I('post.desc','','trim');
            $data['start_time'] = I('post.start_time','0000-00-00 00:00:00','trim') ? I('post.start_time') : '0000-00-00 00:00:00';
            $data['end_time'] = I('post.end_time','0000-00-00 00:00:00','trim') ? I('post.end_time') : '0000-00-00 00:00:00';
            $data['is_long_time'] = I('post.is_long_time',0,'intval');
            $data['integral'] = I('post.integral',0,'intval');
            $data['is_shareprofit'] = I('post.is_shareprofit',0,'intval');
            $data['task_type'] = I('post.task_content_type',0,'intval');
            $this->checkMainParam($data);
            if($data['type']==1){//系统任务
                $task_content = array();
                $task_content['task_content_type'] = I('post.task_content_type',0,'intval'); //任务内容
            
                if($task_content['task_content_type']==1){//电视开机
                    $task_content['lunch_start_time']   = I('post.kj_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.kj_lunch_end_time');
                    $task_content['dinner_start_time']   = I('post.kj_dinner_start_time');
                    $task_content['dinner_end_time']     = I('post.kj_dinner_end_time');
                    $task_content['heart_time']['type'] = I('post.heart_time',0,'intval');
                    $task_content['heart_time']['value'] = I('post.heart_time_'.$task_content['heart_time']['type'],0,'intval');
                }elseif($task_content['task_content_type']==2){//电视互动
                    $task_content['lunch_start_time']   = I('post.hd_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.hd_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.hd_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.hd_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.max_daily_integral',0,'intval');
                    $task_content['user_interact']['type'] = I('post.user_interact',0,'intval');
                    $task_content['user_interact']['value'] = I('post.user_interact_'.$task_content['user_interact']['type'],0,'intval');
                }elseif($task_content['task_content_type']==3){//活动推广
                    $task_content['lunch_start_time']   = I('post.activity_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.activity_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.activity_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.activity_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.max_daily_integral',0,'intval');
                    $task_content['user_promote']['type'] = I('post.user_promote',0,'intval');
                    $task_content['user_promote']['value'] = I('post.user_promote_'.$task_content['user_promote']['type'],0,'intval');
                }elseif($task_content['task_content_type']==4){//邀请食客评价
                    $task_content['lunch_start_time']   = I('post.comment_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.comment_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.comment_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.comment_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.comment_max_daily_integral',0,'intval');
                    $task_content['user_comment']['type'] = I('post.comment_promote',0,'intval');
                    $task_content['user_comment']['value'] = I('post.comment_promote_'.$task_content['user_comment']['type'],0,'intval');
                }elseif($task_content['task_content_type']==5){//打赏补贴
                    $task_content['lunch_start_time']   = I('post.reward_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.reward_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.reward_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.reward_dinner_end_time');
                    $task_content['max_daily_integral'] = I('post.reward_max_daily_integral',0,'intval');
                    $task_content['user_reward']['type'] = I('post.reward_promote',0,'intval');
                    $task_content['user_reward']['value'] = I('post.reward_promote_'.$task_content['user_reward']['type'],0,'intval');
                }elseif($task_content['task_content_type']==6){//邀请函
                    $task_content['lunch_start_time']   = I('post.invite_lunch_start_time');
                    $task_content['lunch_end_time']     = I('post.invite_lunch_end_time');
                    $task_content['dinner_start_time']  = I('post.invite_dinner_start_time');
                    $task_content['dinner_end_time']    = I('post.invite_dinner_end_time');
                    $task_content['user_reward']['week_num'] = I('post.week_num',0,'intval');
                    $task_content['user_reward']['room_num'] = I('post.room_num',0,'intval');
                    $task_content['user_reward']['hotel_max_rate'] = I('post.hotel_max_rate',0);
                }
                $this->chekInfoParam($task_content);
                $data['task_info'] = json_encode($task_content);
            }
            $data['status'] = 0;
            $data['flag']   = 1;
            $userinfo = session('sysUserInfo');
            $data['e_uid'] = $userinfo['id'];
            $ret = $m_task->updateData(array('id'=>$id), $data);

            if($data['is_shareprofit']){
                $shareprofit_level1 = I('post.shareprofit_level1',0,'intval');
                $shareprofit_level2 = I('post.shareprofit_level2',0,'intval');
                if($shareprofit_level1+$shareprofit_level2!=100){
                    $this->output('分润设置不合理', "task/index",2,0);
                }
                $m_taskshareprofit = new \Admin\Model\Integral\TaskShareprofitModel();
                $res_profit = $m_taskshareprofit->getInfo(array('task_id'=>$id,'hotel_id'=>0));
                if(!empty($res_profit)){
                    $update_data = array('level1'=>$shareprofit_level1,'level2'=>$shareprofit_level2);
                    $m_taskshareprofit->updateData(array('id'=>$res_profit['id']),$update_data);
                }else{
                    $add_data = array('task_id'=>$id,'level1'=>$shareprofit_level1,'level2'=>$shareprofit_level2);
                    $m_taskshareprofit->add($add_data);
                }
            }
            $this->output('编辑成功', "task/index");
        }else{
            $task_info = $m_task->getRow('*',array('id'=>$id));
            if($task_info['is_long_time']){
                $task_info['start_time'] = '';
                $task_info['end_time']  = '';
            }
            $task_content = json_decode($task_info['task_info'],true);
            $m_media = new \Admin\Model\MediaModel();
            $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
            
            $m_info = $m_media->getRow('oss_addr',array('id'=>$task_info['media_id']));
            $task_info['oss_addr'] = $oss_host.$m_info['oss_addr'];

            $m_taskshareprofit = new \Admin\Model\Integral\TaskShareprofitModel();
            $res_profit = $m_taskshareprofit->getInfo(array('task_id'=>$id,'hotel_id'=>0));
            $shareprofit_level1 = $shareprofit_level2 = '';
            if(!empty($res_profit)){
                $shareprofit_level1 = $res_profit['level1'];
                $shareprofit_level2 = $res_profit['level2'];
            }
            $task_info['shareprofit_level1'] = $shareprofit_level1;
            $task_info['shareprofit_level2'] = $shareprofit_level2;
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
            $task_id = I('post.task_id',0,'intval');
            $ids = I('post.ids');
            $userinfo = session('sysUserInfo');
            $uid = $userinfo['id'];
            $create_time = date('Y-m-d H:i:s');
            $m_task = new \Admin\Model\Integral\TaskModel();
            $res_task = $m_task->getInfo(array('id'=>$task_id));
            $task_info = json_decode($res_task['task_info'],true);
            $now_task_type = $res_task['task_type'];

            $m_box = new \Admin\Model\BoxModel();
            $has_task_hids = array();
            $has_task_ids = array();
            $data = array();
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $goods_program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');
            foreach($ids as $key=> $v){
                $t_info = array('task_id'=>$task_id,'hotel_id'=>$v,'uid'=>$uid,'create_time'=>$create_time);
                if($res_task['type']==2 && $res_task['task_type']==21){
                    $b_field = 'count(box.id) as boxnum';
                    $b_where = array('box.state'=>1,'box.flag'=>0,'hotel.id'=>$t_info['hotel_id']);
                    $res_box = $m_box->getBoxByCondition($b_field,$b_where);
                    $box_num = intval($res_box[0]['boxnum']);

                    $activity_day = $res_task['activity_day'];
                    if($v['money']>100){
                        $activity_day = $activity_day - 1;
                    }
                    if(in_array('meal',$task_info)){
                        $meal_num = $box_num * $res_task['cvr'] * 2 * $activity_day;
                        if($res_task['people_num']>1){
                            $meal_num = round($meal_num/$res_task['people_num']);
                        }
                        $t_info['meal_num'] = $meal_num;
                    }
                    if(in_array('interact',$task_info)){
                        $interact_num = $box_num * $res_task['cvr'] * 2 * $activity_day * $res_task['interact_num'];
                        if($res_task['people_num']>1){
                            $interact_num = round($interact_num/$res_task['people_num']);
                        }
                        $t_info['interact_num'] = $interact_num;
                    }
                    if(in_array('comment',$task_info)){
                        $comment_num = $box_num * $res_task['cvr'] * 2 * $activity_day * $res_task['comment_num'];
                        if($res_task['people_num']>1){
                            $comment_num = round($comment_num/$res_task['people_num']);
                        }
                        $t_info['comment_num'] = $comment_num;
                    }
                    if(in_array('lottery',$task_info)){
                        $lottery_num = $res_task['lottery_num'];
                        if($res_task['people_num']>1){
                            $lottery_num = round($lottery_num/$res_task['people_num']);
                        }
                        $t_info['lottery_num'] = $lottery_num;
                    }
                }else{
                    $hwhere = array('hotel_id'=>$v);
                    $res_hoteltask = $m_task_hotel->getDataList('*',$hwhere,'id desc');
                    if(!empty($res_hoteltask)){
                        foreach ($res_hoteltask as $tv){
                            $tid = $tv['task_id'];
                            $res_task = $m_task->getInfo(array('id'=>$tid));
                            $task_type = $res_task['task_type'];
                            if($res_task['type']==1 && $now_task_type==$task_type){
                                $has_task_hids[]=$v;
                                $has_task_ids[$tid]=$tid;
                                break;
                            }
                        }
                    }
                    if($res_task['task_type']==22 || $res_task['task_type']==24){
                        $program_key = $goods_program_key.":{$v}";
                        $period = getMillisecond();
                        $period_data = array('period'=>$period);
                        $redis->set($program_key,json_encode($period_data));
                    }
                }
                $data[] = $t_info;
            }
            if(!empty($has_task_hids)){
                $hid_str = join(',',$has_task_hids);
                $tid_str = join(',',array_values($has_task_ids));
                $message = '如下酒楼ID：'.$hid_str.' 已有相似任务,任务ID为：'.$tid_str;
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                navTab.reloadFlag("integral/selecthotel");
                alertMsg.error("'.$message.'");</script>';
                exit;
            }
            $ret = $m_task_hotel->addAll($data);
            if($ret){
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                navTab.reloadFlag("task/index");
                alertMsg.correct("发布成功！");</script>';
            }else {
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                navTab.reloadFlag("task/index");
                alertMsg.success("发布失败！");</script>';
            }
        }else{
            $area_id_arr = I('include_a');
            $in_task_id  = I('in_task_id',0,'intval');//所选任务包含酒楼
            $task_id = I('task_id',0,'intval');
            $where = [];
            $where['task_id'] = $task_id;
            $count = $m_task_hotel->where($where)->count();
            if(!empty($count)) {
                echo '<script>
                navTab.closeTab("integral/selecthotel");
                alertMsg.error("该任务已选择酒楼！");</script>';
            }

            if($in_task_id){
                $fields = 'hotel.id hotel_id,hotel.name hotel_name,area.region_name,hotel.hotel_box_type';
                $where = [];
                $where['hotel.state'] = 1;
                $where['hotel.flag']  = 0;
                $where['a.task_id']   = $in_task_id;
                if(!empty($area_id_arr)){
                    $where['area.id'] = array('in',$area_id_arr);
                    $this->assign('include_ak',$area_id_arr);
                }
                $order = 'convert(hotel.name using gbk) asc';
                $hotel_list = $m_task_hotel->alias('a')
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
        $where = array('id'=>$task_id,'flag'=>1);
        $userinfo = session('sysUserInfo');
        $uid = $userinfo['id'];
        $fields = "name,media_id,type,task_type,money,cvr,activity_day,interact_num,comment_num,
        desc,start_time,end_time,is_long_time,integral,separate_id,task_info,goods_id,image_url,portrait_image_url,tv_image_url";
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
        $task_id = I('task_id',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $hotel_name = I('hotel_name','','trim');

        $m_task = new \Admin\Model\Integral\TaskModel();
        $res_task = $m_task->getInfo(array('id'=>$task_id));
        $task_type = $res_task['task_type'];
        $where = array('a.task_id'=>$task_id);
        if(!empty($hotel_name)){
            $where = array('task.status'=>1,'task.flag'=>1);
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $fields = 'a.task_id,a.meal_num,a.interact_num,a.comment_num,a.lottery_num,area.region_name,hotel.name hotel_name,hotel.addr,hotel.state,hotel.pinyin';
        $order = 'hotel.pinyin asc';
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        $start = ($page-1) * $size;
        $list = $m_task_hotel->getList($fields, $where, $order, $start, $size);
        $hotel_list = array();
        if(!empty($list['list'])){
            $all_state = C('HOTEL_STATE');
            foreach ($list['list'] as $v){
                $f_char = strtoupper(substr($v['pinyin'],0,1));
                $v['hotel_name'] = $f_char.'-'.$v['hotel_name'];
                $v['state_str'] = $all_state[$v['state']];
                $hotel_list[]=$v;
            }
        }
        $this->assign('hotel_name',$hotel_name);
        $this->assign('task_id',$task_id);
        $this->assign('task_type',$task_type);
        $this->assign('hotel_list',$hotel_list);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('page',$list['page']);
        $this->display();
    }

    private function checkMainParam($data){
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
        }
        if(empty($data['integral'])) $this->error('请输入奖励积分');
    }

    private function chekInfoParam($data,$map){
        if($map['type']==1){
            if(empty($data['task_content_type'])) $this->error('请选择任务内容');
        }
        if(empty($data['lunch_end_time']) ||empty($data['lunch_start_time'])) $this->error('午饭开始时间和午饭结束时间不能为空');
        if(!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/',$data['lunch_start_time'])) $this->error('午饭开始时间格式错误');
        if(!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/',$data['lunch_end_time'])) $this->error('午饭结束时间格式错误');
        if(!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/',$data['dinner_start_time'])) $this->error('晚饭开始时间格式错误');
        if(!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/',$data['dinner_end_time'])) $this->error('晚饭结束时间格式错误');
        if($data['lunch_end_time']<=$data['lunch_start_time']){
            $this->error('午饭结束时间必须大于开始时间');
        }
        if($data['dinner_end_time']<=$data['dinner_start_time']){
            $this->error('晚饭结束时间必须大于开始时间');
        }
        if($data['lunch_end_time']>'17:00') $this->error('午饭结束时间不能大于17点');
        if($data['dinner_end_time']>'23:00') $this->error('晚饭结束时间不能大于23点');

        if($data['task_content_type']==1){
            if(empty($data['heart_time']['type'])) $this->error('请选择开机奖励类型');
            if(empty($data['heart_time']['value'])) $this->error('请输入开机时长');
        }elseif($data['task_content_type']==2){
            if(empty($data['max_daily_integral'])) $this->error('请输入每日积分上限');
        }elseif($data['task_content_type']==3){
            if(empty($data['max_daily_integral'])) $this->error('请输入每日积分上限');
        }elseif($data['task_content_type']==4){
            if(empty($data['user_comment']['type'])) $this->error('请选择评价奖励类型');
            if(empty($data['max_daily_integral'])) $this->error('请输入每日积分上限');
        }elseif($data['task_content_type']==5){
            if(empty($data['user_reward']['type'])) $this->error('请选择打赏奖励类型');
            if(empty($data['max_daily_integral'])) $this->error('请输入每日积分上限');
        }elseif($data['task_content_type']==6){
            if(empty($data['user_reward']['hotel_max_rate'])) $this->error('请输入餐厅单日积分上限比例');
            if(empty($data['user_reward']['week_num'])) $this->error('请输入打开邀请函奖励次数');
            if(empty($data['user_reward']['room_num'])) $this->error('请输入饭点包间奖励次数');
        }
        return true;
    }
}