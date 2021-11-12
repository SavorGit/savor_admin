<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 活动
 *
 */
class ActivityController extends BaseController {

    public function activitylist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',99,'intval');
        $type = I('type',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array();
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'activity/activitylist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        if($status!=99){
            $where['a.status'] = $status;
        }
        if($hotel_name){
            $where['hotel.name'] = array('like',"%{$hotel_name}%");
        }
        $all_activity = array('1'=>'霸王餐抽奖','2'=>'普通抽奖','3'=>'系统抽奖','4'=>'系统霸王餐抽奖','5'=>'聚划算活动','8'=>'销售人员发起抽奖活动');
        if($type){
            $where['a.type'] = $type;
        }else{
            $where['a.type'] = array('in',array_keys($all_activity));
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.*,hotel.name as hotel_name';
        $orderby = 'a.id desc';
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res_list = $m_activity->getList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $all_status = C('ACTIVITY_STATUS');
        if(!empty($data_list)){
            $oss_host = 'http://'.C('OSS_HOST_NEW');
            $now_time = time();
            $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
            foreach ($data_list as $k=>$v){
                if($v['type']==3){
                    $data_list[$k]['status_str'] = '';
                }
                if($v['type']==5){
                    $data_list[$k]['prize'] = "1.{$v['prize']} 2.{$v['attach_prize']}";
                    if($v['status']==1){
                        $expire_time = strtotime($v['add_time']) + 3600;
                        if($now_time>$expire_time){
                            $v['status'] = 2;
                            $m_activity->updateData(array('id'=>$v['id']),array('status'=>2));
                        }
                    }
                }
                $nums = 0;
                if(in_array($v['type'],array(3,5)) || in_array($v['status'],array(1,2))){
                    $where = array('activity_id'=>$v['id']);
                    $res_num = $m_activityapply->getAll('count(id) as num',$where,0,1,'','');
                    if(!empty($res_num)){
                        $nums = intval($res_num[0]['num']);
                    }
                }
                $data_list[$k]['nums'] = $nums;
                $data_list[$k]['image_url'] = $oss_host.'/'.$v['image_url'];
                $data_list[$k]['status_str'] = $all_status[$v['status']];
            }
        }

        $this->assign('type',$type);
        $this->assign('all_activity',$all_activity);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function detail(){
        $activity_id = I('id',0,'intval');
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $fields = 'a.*,user.nickName,user.avatarUrl';
        $where = array('activity_id'=>$activity_id);
        $res = $m_activityapply->getList($fields,$where,'a.id desc');
        $all_mac = array();
        foreach ($res as $k=>$v){
            if(!in_array($v['box_mac'],$all_mac)){
                $all_mac[]=$v['box_mac'];
            }
        }
        $m_box = new \Admin\Model\BoxModel();
        $where = array('box.mac'=>array('in',$all_mac));
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $fields = 'box.mac,box.name';
        $res_box = $m_box->getBoxByCondition($fields,$where,'');
        $boxs = array();
        foreach ($res_box as $v){
            $boxs[$v['mac']] = $v['name'];
        }
        $all_status = array('1'=>'未开奖','2'=>'已中奖','3'=>'未中奖','4'=>'已中奖未完成','5'=>'已中奖已完成待领取');
        $all_prizes = array('1'=>'一等奖','2'=>'二等奖','3'=>'三等奖');
        $m_activityprize = new \Admin\Model\Smallapp\ActivityprizeModel();
        foreach ($res as $k=>$v){
            $prize = '';
            if($v['prize_id']){
                $res_prize = $m_activityprize->getInfo(array('id'=>$v['prize_id']));
                $prize = $res_prize['name'];
                if($res_prize['level']>0){
                    $prize = $all_prizes[$res_prize['level']].':'.$prize;
                }
            }
            $res[$k]['prize'] = $prize;
            $res[$k]['box_name'] = $boxs[$v['box_mac']];
            $res[$k]['status_str'] = $all_status[$v['status']];
        }

        $this->assign('datalist',$res);
        $this->display();
    }

    public function tastwinelist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $type = I('type',0,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array('type'=>array('in',array(6,7)));
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'activity/tastwinelist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $fields = '*';
        $orderby = 'id desc';
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res_list = $m_activity->getDataList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        if(!empty($data_list)){
            $oss_host = 'http://'.C('OSS_HOST_NEW');
            $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
            $m_activityhotel = new \Admin\Model\Smallapp\ActivityhotelModel();
            foreach ($data_list as $k=>$v){
                $hotel_num = $num = 0;
                $where = array('activity_id'=>$v['id']);
                $res_num = $m_activityapply->getAll('count(id) as num',$where,0,1,'','');
                if(!empty($res_num)){
                    $num = intval($res_num[0]['num']);
                }
                $res_hnum = $m_activityhotel->getAll('count(id) as num',$where,0,1,'','');
                if(!empty($res_hnum)){
                    $hotel_num = intval($res_hnum[0]['num']);
                }

                $activity_date = date('Y-m-d',strtotime($v['start_time'])).'-'.date('Y-m-d',strtotime($v['end_time']));
                $data_list[$k]['activity_date'] = $activity_date;
                $data_list[$k]['hotel_num'] = $hotel_num;
                $data_list[$k]['num'] = $num;
                $data_list[$k]['image_url'] = $oss_host.'/'.$v['image_url'];
                $data_list[$k]['status_str'] = $all_status[$v['status']];
            }
        }

        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addtastwine(){
        $id = I('id',0,'intval');
        $name = I('post.name','','trim');
        $prize = I('post.prize','','trim');
        $people_num = I('post.people_num',0,'intval');
        $start_date = I('post.start_date');
        $end_date = I('post.end_date');
        $media_id = I('post.media_id',0,'intval');
        $portraitmedia_id = I('post.portraitmedia_id',0,'intval');
        $status = I('post.status',0,'intval');

        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        if(IS_POST){
            $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
            $end_time = date('Y-m-d 23:59:59',strtotime($end_date));
            $add_data = array('name'=>$name,'prize'=>$prize,'start_time'=>$start_time,'end_time'=>$end_time,
                'people_num'=>$people_num,'status'=>$status,'type'=>6
            );
            $m_media = new \Admin\Model\MediaModel();
            if($media_id){
                $res_media = $m_media->getMediaInfoById($media_id);
                $add_data['image_url'] = $res_media['oss_path'];
            }
            if($portraitmedia_id){
                $res_media = $m_media->getMediaInfoById($portraitmedia_id);
                $add_data['portrait_image_url'] = $res_media['oss_path'];
            }
            if($id){
                $m_activity->updateData(array('id'=>$id),$add_data);
            }else{
                $m_activity->addData($add_data);
            }
            $this->output('操作成功!', 'activity/tastwinelist');
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_activity->getInfo(array('id'=>$id));
                if($vinfo['image_url']){
                    $vinfo['image_url'] = $oss_host.$vinfo['image_url'];
                }
                if($vinfo['portrait_image_url']){
                    $vinfo['portrait_image_url'] = $oss_host.$vinfo['portrait_image_url'];
                }
                $vinfo['start_date'] = date('Y-m-d',strtotime($vinfo['start_time']));
                $vinfo['end_date'] = date('Y-m-d',strtotime($vinfo['end_time']));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addtastwinehotel(){
        $id = I('id',0,'intval');
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','activity/tastwinelist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','activity/tastwinelist',2,0);
            }
            $m_activityhotel = new \Admin\Model\Smallapp\ActivityhotelModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];
                $data = array('hotel_id'=>$hotel_id,'activity_id'=>$id);
                $res = $m_activityhotel->where($data)->find();
                if(empty($res)){
                    $m_activityhotel->add($data);
                }
            }
            $this->output('操作成功!', 'activity/tastwinelist');
        }else{
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $vinfo = $m_activity->getInfo(array('id'=>$id));
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function hotellist() {
        $activity_id = I('activity_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.activity_id'=>$activity_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_activityhotel = new \Admin\Model\Smallapp\ActivityhotelModel();
        $result = $m_activityhotel->getHotelList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('activity_id',$activity_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotellist');
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');
        $m_activityhotel = new \Admin\Model\Smallapp\ActivityhotelModel();
        $result = $m_activityhotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'activity/hotellist',2);
        }else{
            $this->output('操作失败', 'activity/hotellist',2,0);
        }
    }

    public function tastwineuserlist(){
        $activity_id = I('activity_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array('activity.type'=>array('in',array(6,7)));
        if($activity_id){
            $where['a.activity_id'] = $activity_id;
        }
        if(!empty($hotel_name)){
            $where['a.hotel_name'] = array('like',"%$hotel_name%");
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'activity/activitylist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $all_activity = $m_activity->getDataList('id,name,prize',array('type'=>array('in',array(6,7))),'id asc');
        $start  = ($page-1) * $size;
        $fields = 'a.id,activity.name as activity_name,a.hotel_name,a.box_name,a.box_mac,a.openid,a.mobile,user.nickName,user.avatarUrl,a.add_time';
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $result = $m_activityapply->gettastwineList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('all_activity',$all_activity);
        $this->assign('activity_id',$activity_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist', $datalist);
        $this->assign('start_date', $start_date);
        $this->assign('end_date', $end_date);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);

        $this->display();
    }


}