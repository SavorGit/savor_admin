<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class SellwineactivityController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array();
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'sellwineactivity/datalist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['start_date'] = array('egt',$start_time);
            $where['end_date'] = array('elt',$end_time);
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $fields = '*';
        $orderby = 'id desc';
        $m_activity = new \Admin\Model\Smallapp\SellwineActivityModel();
        $res_list = $m_activity->getDataList($fields,$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        if(!empty($data_list)){
            $m_activityhotel = new \Admin\Model\Smallapp\SellwineActivityHotelModel();
            foreach ($data_list as $k=>$v){
                $hotel_num = 0;
                $where = array('activity_id'=>$v['id'],'status'=>1);
                $res_hnum = $m_activityhotel->getAll('count(id) as num',$where,0,1,'','');
                if(!empty($res_hnum)){
                    $hotel_num = intval($res_hnum[0]['num']);
                }
                $activity_date = date('Y-m-d',strtotime($v['start_date'])).'至'.date('Y-m-d',strtotime($v['end_date']));

                $data_list[$k]['lunch_time'] = $v['lunch_start_time'].'-'.$v['lunch_end_time'];
                $data_list[$k]['dinner_time'] = $v['dinner_start_time'].'-'.$v['dinner_end_time'];
                $data_list[$k]['activity_date'] = $activity_date;
                $data_list[$k]['hotel_num'] = $hotel_num;
                $data_list[$k]['status_str'] = $all_status[$v['status']];
            }
        }

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

    public function addactivity(){
        $id = I('id',0,'intval');
        $m_activity = new \Admin\Model\Smallapp\SellwineActivityModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $tvleftmedia_id = I('post.tvleftmedia_id',0,'intval');
            $start_date = I('post.start_date');
            $end_date = I('post.end_date');
            $lunch_start_time = I('post.lunch_start_time');
            $lunch_end_time = I('post.lunch_end_time');
            $dinner_start_time = I('post.dinner_start_time');
            $dinner_end_time = I('post.dinner_end_time');
            $daily_money_limit = I('post.daily_money_limit',0,'intval');
            $money_limit = I('post.money_limit',0,'intval');
            $interval_time = I('post.interval_time',0,'intval');
            $status = I('post.status',0,'intval');

            $push_times = array();
            $lunch_stime = date("Y-m-d $lunch_start_time");
            $lunch_etime = date("Y-m-d {$lunch_end_time}");
            $dinner_stime = date("Y-m-d {$dinner_start_time}");
            $dinner_etime = date("Y-m-d {$dinner_end_time}");
            if($lunch_stime>=$lunch_etime){
                $this->output('午饭开始时间不能大于结束时间', 'sellwineactivity/addactivity',2,0);
            }
            if($dinner_stime>=$dinner_etime){
                $this->output('晚饭开始时间不能大于结束时间', 'sellwineactivity/addactivity',2,0);
            }
            $user = session('sysUserInfo');
            $sysuser_id = $user['id'];
            $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
            $end_time = date('Y-m-d 23:59:59',strtotime($end_date));
            $add_data = array('name'=>$name,'media_id'=>$media_id,'tvleftmedia_id'=>$tvleftmedia_id,'start_date'=>$start_time,'end_date'=>$end_time,
                'lunch_start_time'=>$lunch_start_time,'lunch_end_time'=>$lunch_end_time,'dinner_start_time'=>$dinner_start_time,'dinner_end_time'=>$dinner_end_time,
                'daily_money_limit'=>$daily_money_limit,'money_limit'=>$money_limit,'sysuser_id'=>$sysuser_id,'status'=>$status,
                'interval_time'=>$interval_time,'push_times'=>$push_times
            );
            if($id){
                $m_activity->updateData(array('id'=>$id),$add_data);
            }else{
                $m_activity->addData($add_data);
            }
            $this->output('操作成功!', 'sellwineactivity/datalist');
        }else{
            $vinfo = array('status'=>2,'lunch_start_time'=>'11:30','lunch_end_time'=>'13:30','dinner_start_time'=>'18:30','dinner_end_time'=>'20:00');
            $interval_minutes = array();
            for($i=1;$i<7;$i++){
                $interval_minutes[]=$i*10;
            }
            if($id){
                $m_media = new \Admin\Model\MediaModel();
                $vinfo = $m_activity->getInfo(array('id'=>$id));
                $image_url = $tvleftimage_url = '';
                if($vinfo['media_id']){
                    $res_meida = $m_media->getMediaInfoById($vinfo['media_id']);
                    $image_url = $res_meida['oss_addr'];
                }
                if($vinfo['tvleftmedia_id']){
                    $res_meida = $m_media->getMediaInfoById($vinfo['media_id']);
                    $tvleftimage_url = $res_meida['oss_addr'];
                }
                $vinfo['image_url'] = $image_url;
                $vinfo['tvleftimage_url'] = $tvleftimage_url;
                $vinfo['start_date'] = date('Y-m-d',strtotime($vinfo['start_date']));
                $vinfo['end_date'] = date('Y-m-d',strtotime($vinfo['end_date']));
                $vinfo['lunch_start_time'] = date('H:i',strtotime($vinfo['lunch_start_time']));
                $vinfo['lunch_end_time'] = date('H:i',strtotime($vinfo['lunch_end_time']));
                $vinfo['dinner_start_time'] = date('H:i',strtotime($vinfo['dinner_start_time']));
                $vinfo['dinner_end_time'] = date('H:i',strtotime($vinfo['dinner_end_time']));
            }
            $vinfo['interval_minutes'] = $interval_minutes;
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function addhotel(){
        $id = I('id',0,'intval');
        $m_activity = new \Admin\Model\Smallapp\SellwineActivityModel();
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','sellwineactivity/datalist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','sellwineactivity/datalist',2,0);
            }
            $m_activityhotel = new \Admin\Model\Smallapp\SellwineActivityHotelModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];
                $has_activity = $m_activityhotel->getInfo(array('hotel_id'=>$hotel_id,'status'=>1));
                if(!empty($has_activity)){
                    $this->output("酒楼ID:$hotel_id,已有活动:{$has_activity['activity_id']}",'sellwineactivity/datalist',2,0);
                }
                $data = array('hotel_id'=>$hotel_id,'activity_id'=>$id,'status'=>1);
                $res = $m_activityhotel->where($data)->find();
                if(empty($res)){
                    $m_activityhotel->add($data);
                }
            }
            $this->output('操作成功!', 'sellwineactivity/datalist');
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

        $where = array('a.activity_id'=>$activity_id,'a.status'=>1);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_activityhotel = new \Admin\Model\Smallapp\SellwineActivityHotelModel();
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
        $m_activityhotel = new \Admin\Model\Smallapp\SellwineActivityHotelModel();
        $user = session('sysUserInfo');
        $sysuser_id = $user['id'];
        $updata = array('status'=>2,'update_time'=>date('Y-m-d H:i:s'),'sysuser_id'=>$sysuser_id);
        $result = $m_activityhotel->updateData(array('id'=>$id),$updata);
        if($result){
            $this->output('操作成功!', 'sellwineactivity/hotellist',2);
        }else{
            $this->output('操作失败', 'sellwineactivity/hotellist',2,0);
        }
    }

    public function goodslist() {
        $activity_id = I('activity_id',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $status = I('status',0,'intval');

        $where = array('activity_id'=>$activity_id);
        if($status){
            $where['status'] = $status;
        }
        $start = ($page-1) * $size;
        $m_activitygoods = new \Admin\Model\Smallapp\SellwineActivityGoodsModel();
        $result = $m_activitygoods->getDataList('*',$where,'id desc',$start,$size);
        $datalist = $result['list'];
        $m_finance_goods = new \Admin\Model\FinanceGoodsModel();
        $all_status = array('1'=>'正常','2'=>'禁用');
        foreach ($datalist as $k=>$v){
            $ginfo = $m_finance_goods->getInfo(array('id'=>$v['finance_goods_id']));
            $datalist[$k]['goods_name'] = $ginfo['name'];
            $datalist[$k]['status_str'] = $all_status[$v['status']];
        }

        $this->assign('activity_id',$activity_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function goodsadd(){
        $id = I('id',0,'intval');
        $activity_id = I('activity_id',0,'intval');
        $m_activitygoods = new \Admin\Model\Smallapp\SellwineActivityGoodsModel();
        if(IS_POST){
            $finance_goods_id = I('post.finance_goods_id',0,'intval');
            $money = I('post.money',0,'intval');
            $status = I('post.status',0,'intval');
            $data = array('activity_id'=>$activity_id,'finance_goods_id'=>$finance_goods_id,'money'=>$money,'status'=>$status);
            $where = array('activity_id'=>$activity_id,'finance_goods_id'=>$finance_goods_id);
            if($id){
                $where['id'] = array('neq',$id);
            }
            $res_data = $m_activitygoods->getInfo($where);
            if(!empty($res_data)){
                $this->output('商品不能重复添加','sellwineactivity/goodsadd',2,0);
            }
            if($id){
                $user = session('sysUserInfo');
                $sysuser_id = $user['id'];
                $data['update_time'] = date('Y-m-d H:i:s');
                $data['sysuser_id'] = $sysuser_id;

                $m_activitygoods->updateData(array('id'=>$id),$data);
            }else{
                $m_activitygoods->add($data);
            }
            $this->output('操作成功!', 'sellwineactivity/goodslist');
        }else{
            $vinfo = array('status'=>1,'goods_id'=>0,'activity_id'=>$activity_id);
            if($id){
                $vinfo = $m_activitygoods->getInfo(array('id'=>$id));
            }
            $m_finance_goods = new \Admin\Model\FinanceGoodsModel();
            $goods = $m_finance_goods->getDataList('id,name',array('status'=>1),'brand_id asc,id asc');
            $this->assign('goods',$goods);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }

    }


}