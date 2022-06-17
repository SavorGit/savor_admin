<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 餐厅抽奖
 *
 */
class HotellotteryController extends BaseController {

    public $lottery_prize_types = array('1'=>'现金','2'=>'实物','3'=>'无奖');
    public $lottery_prize_level = array('1'=>'一等奖','2'=>'二等奖','3'=>'三等奖');

    public function datalist(){
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_hotellottery = new \Admin\Model\Smallapp\HotellotteryModel();
        $where = array();
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_hotellottery->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $m_sysuser = new \Admin\Model\UserModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            $res_hotel = $m_hotel->getOne($v['hotel_id']);
            $data_list[$k]['hotel_name'] = $res_hotel['name'];
            if(!empty($v['image_url'])){
                $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
            }
            $send_time = $v['start_date'].'至'.$v['end_date']." {$v['timing']}";
            $data_list[$k]['send_time'] = $send_time;
            $res_user = $m_sysuser->getUserInfo($v['sysuser_id']);
            $data_list[$k]['username'] = $res_user['remark'];
            if($v['status']==1){
                $data_list[$k]['statusstr'] = '可用';
            }else{
                $data_list[$k]['statusstr'] = '不可用';
            }
        }
        $this->assign('status',$status);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function lotteryadd(){
        $id = I('id',0,'intval');
        $m_hotellottery = new \Admin\Model\Smallapp\HotellotteryModel();
        if(IS_POST){
            $name = I('post.name','');
            $prize = I('post.prize','');
            $people_num = I('post.people_num',0,'intval');
            $media_id = I('post.media_id',0,'intval');
            $start_date = I('post.start_date','');
            $end_date = I('post.end_date','');
            $hour = I('post.hour','');
            $minute = I('post.minute','');
            $wait_time = I('post.wait_time',0,'intval');
            $hotel_id = I('post.hotel_id',0,'intval');
            $status = I('post.status',0,'intval');
            $type = I('post.type',0,'intval');

            $userInfo = session('sysUserInfo');
            $data = array('name'=>$name,'prize'=>$prize,'wait_time'=>$wait_time,'hotel_id'=>$hotel_id,'type'=>$type,
                'people_num'=>$people_num,'start_date'=>$start_date,'end_date'=>$end_date,'sysuser_id'=>$userInfo['id'],'status'=>$status);
            if(empty($hour) || empty($minute)){
                $this->output('发送时间不能为空', 'hotellottery/lotteryadd',2,0);
            }
            $now_date = date('Y-m-d');
            if($now_date>$start_date){
                $this->output('请选择正确的日期', 'hotellottery/lotteryadd',2,0);
            }
            $data['timing'] = $hour.':'.$minute;
            if(!empty($media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            $day_diff = (strtotime($data['end_date'])-strtotime($data['start_date']))/86400;
            $day = $day_diff+1;

            if($id){
                $m_activity = new \Admin\Model\Smallapp\ActivityModel();
                $res_send_activity = $m_activity->getInfo(array('syslottery_id'=>$id,'type'=>10));
                if(!empty($res_send_activity)){
                    $this->output('活动已发起不能重复使用', 'hotellottery/lotteryadd',2,0);
                }
                $m_hotellottery_prize = new \Admin\Model\Smallapp\HotellotteryPrizeModel();
                $fields = '*';
                $res_prize = $m_hotellottery_prize->getDataList($fields,array('hotellottery_id'=>$id,'status'=>1),'id desc');
                $redis = new \Common\Lib\SavorRedis();
                $redis->select(1);
                $key_pool = C('SAPP_PRIZEPOOL');
                if($status==1){
                    $m_prizepool = new \Admin\Model\Smallapp\PrizepoolprizeModel();
                    $total_amount = 0;

                    foreach ($res_prize as $v){
                        $total_amount+=$v['amount'];
                        $now_amount = $v['amount']*$day;

                        $prizepool_prize_id = $v['prizepool_prize_id'];
                        $res_pool = $m_prizepool->getInfo(array('id'=>$prizepool_prize_id));
                        $db_last_num = $res_pool['amount'] - ($res_pool['send_amount']+$now_amount);

                        $lucky_pool_key = $key_pool.$prizepool_prize_id;
                        $res_cachepool = $redis->get($lucky_pool_key);
                        $cache_num = 0;
                        if(!empty($res_cachepool)){
                            $res_cachepool = json_decode($res_cachepool,true);
                            $cache_num = count($res_cachepool);
                        }
                        $cache_last_num = $res_pool['amount'] - ($cache_num+$now_amount);
                        if($db_last_num<0 || $cache_last_num<0){
                            $msg = '';
                            if($day>1){
                                $msg = $day.'天';
                            }
                            $this->output("奖品:{$res_pool['name']},{$msg}奖池数量不够", 'hotellottery/lotteryadd',2,0);
                        }
                    }
                    if($total_amount==0){
                        $this->output('请先设置奖品', 'hotellottery/lotteryadd',2,0);
                    }
                    if($total_amount>9){
                        $this->output('奖品数量不能超过9个', 'hotellottery/lotteryadd',2,0);
                    }

                    foreach ($res_prize as $v){
                        $amount=$v['amount']*$day;
                        $prizepool_prize_id = $v['prizepool_prize_id'];
                        $lucky_pool_key = $key_pool.$prizepool_prize_id;
                        $res_cachepool = $redis->get($lucky_pool_key);
                        $prizepool_data = array();
                        if(!empty($res_cachepool)){
                            $prizepool_data = json_decode($res_cachepool,true);
                        }
                        for ($i=1;$i<=$amount;$i++){
                            $p_key = $hotel_id.$id.$i;
                            $prizepool_data[$p_key] = 2;
                        }
                        $redis->set($lucky_pool_key,json_encode($prizepool_data));
                    }

                }else{
                    foreach ($res_prize as $v){
                        $amount=$v['amount']*$day;
                        $prizepool_prize_id = $v['prizepool_prize_id'];
                        $lucky_pool_key = $key_pool.$prizepool_prize_id;
                        $res_cachepool = $redis->get($lucky_pool_key);
                        $prizepool_data = array();
                        if(!empty($res_cachepool)){
                            $prizepool_data = json_decode($res_cachepool,true);
                        }
                        for ($i=1;$i<=$amount;$i++){
                            $p_key = $hotel_id.$id.$i;
                            unset($prizepool_data[$p_key]);
                        }
                        $redis->set($lucky_pool_key,json_encode($prizepool_data));
                    }

                }
                $m_hotellottery->updateData(array('id'=>$id),$data);
            }else{
                $data['status'] = 2;
                $m_hotellottery->addData($data);
            }
            $this->output('操作成功!', 'hotellottery/datalist');
        }else{
            $vinfo = array('status'=>2,'wait_time'=>30,'type'=>2);
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_hotellottery->getInfo(array('id'=>$id));
                $vinfo['oss_addr'] = $oss_host.$vinfo['image_url'];
                $timing_info = explode(':',$vinfo['timing']);
                $hour = $timing_info[0];
                $minute = $timing_info[1];
                if($vinfo['start_date']=='0000-00-00'){
                    $vinfo['start_date'] = '';
                }
                if($vinfo['end_date']=='0000-00-00'){
                    $vinfo['end_date'] = '';
                }
                $vinfo['hour'] = $hour;
                $vinfo['minute'] = $minute;
            }
            $res = $this->handle_publicinfo();
            $hours = $res['hours'];
            $minutes = $res['minutes'];
            $hlist = $res['hotels'];
            $areas = $res['areas'];
            $wait_minutes = $res['wait_minutes'];

            $this->assign('hlist', $hlist);
            $this->assign('areas', $areas);
            $this->assign('vinfo',$vinfo);
            $this->assign('hours',$hours);
            $this->assign('minutes',$minutes);
            $this->assign('wait_minutes',$wait_minutes);
            $this->display();
        }
    }

    public function lotterydel(){
        $id = I('get.id',0,'intval');
        $m_hotellottery = new \Admin\Model\Smallapp\HotellotteryModel();
        $vinfo = $m_hotellottery->getInfo(array('id'=>$id));
        $day_diff = (strtotime($vinfo['end_date'])-strtotime($vinfo['start_date']))/86400;
        $all_day = $day_diff+1;

        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res_send_activity = $m_activity->getAll('*',array('syslottery_id'=>$id,'type'=>10),0,1,'id desc');
        if(!empty($res_send_activity)){
            $now_end_date = date('Y-m-d',strtotime($res_send_activity[0]['start_time']));
            $now_day_diff = (strtotime($now_end_date)-strtotime($vinfo['start_date']))/86400;
            $day = $all_day-($now_day_diff+1);
        }else{
            $day = $all_day;
        }

        $result = $m_hotellottery->delData(array('id'=>$id));
        if($result){
            if($vinfo['status']==1){
                $m_hotellottery_prize = new \Admin\Model\Smallapp\HotellotteryPrizeModel();
                $fields = '*';
                $res_prize = $m_hotellottery_prize->getDataList($fields,array('hotellottery_id'=>$id,'status'=>1),'id desc');
                if(!empty($res_prize)){
                    $redis = new \Common\Lib\SavorRedis();
                    $redis->select(1);
                    $key_pool = C('SAPP_PRIZEPOOL');
                    foreach ($res_prize as $v){
                        $amount=$v['amount']*$day;
                        $prizepool_prize_id = $v['prizepool_prize_id'];
                        $lucky_pool_key = $key_pool.$prizepool_prize_id;
                        $res_cachepool = $redis->get($lucky_pool_key);
                        $prizepool_data = array();
                        if(!empty($res_cachepool)){
                            $prizepool_data = json_decode($res_cachepool,true);
                        }
                        for ($i=1;$i<=$amount;$i++){
                            $p_key = $vinfo['hotel_id'].$id.$i;
                            unset($prizepool_data[$p_key]);
                        }
                        $redis->set($lucky_pool_key,json_encode($prizepool_data));
                    }
                }

            }
            $this->output('操作成功!', 'hotellottery/datalist',2);
        }else{
            $this->output('操作失败', 'hotellottery/datalist',2,0);
        }
    }

    public function prizelist(){
        $lottery_id = I('lottery_id',0,'intval');
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_prize = new \Admin\Model\Smallapp\HotellotteryPrizeModel();
        $where = array('a.hotellottery_id'=>$lottery_id);
        if($status){
            $where['a.status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $fields = 'a.*,p.name,p.image_url,p.type';
        $res_list = $m_prize->getHotelpoolprizeList($fields,$where,$orderby, $start,$size);
        $data_list = $res_list['list'];
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            if($v['status']==1){
                $data_list[$k]['statusstr'] = '可用';
            }else{
                $data_list[$k]['statusstr'] = '不可用';
            }
            $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
            $data_list[$k]['typestr'] = $this->lottery_prize_types[$v['type']];
            $data_list[$k]['levelstr'] = $this->lottery_prize_level[$v['level']];
        }
        $m_lottery = new \Admin\Model\Smallapp\HotellotteryModel();
        $lottery_info = $m_lottery->getInfo(array('id'=>$lottery_id));

        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('status',$status);
        $this->assign('lottery_id',$lottery_id);
        $this->assign('lottery_type',$lottery_info['type']);
        $this->display();
    }

    public function prizeadd(){
        $id = I('id',0,'intval');
        $lottery_id = I('lottery_id',0,'intval');
        $m_prize = new \Admin\Model\Smallapp\HotellotteryPrizeModel();
        $m_lottery = new \Admin\Model\Smallapp\HotellotteryModel();
        $lottery_info = $m_lottery->getInfo(array('id'=>$lottery_id));
        if(IS_POST){
            $prizepool_prize_id = I('post.prizepool_prize_id',0,'intval');
            $amount = I('post.amount',0,'intval');
            $level = I('post.level',0,'intval');
            $status = I('post.status',0,'intval');
            if($level==1 && $amount>1){
                $this->output('一等奖只能设置一个奖品', 'hotellottery/prizelist',2);
            }
            $day_diff = (strtotime($lottery_info['end_date'])-strtotime($lottery_info['start_date']))/86400;
            $day = $day_diff+1;

            $now_amount = $amount*$day;
            $m_prizepool = new \Admin\Model\Smallapp\PrizepoolprizeModel();
            $res_pool = $m_prizepool->getInfo(array('id'=>$prizepool_prize_id));
            $db_last_num = $res_pool['amount'] - ($res_pool['send_amount']+$now_amount);
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(1);
            $key_pool = C('SAPP_PRIZEPOOL');
            $lucky_pool_key = $key_pool.$prizepool_prize_id;
            $res_cachepool = $redis->get($lucky_pool_key);
            $cache_num = 0;
            if(!empty($res_cachepool)){
                $res_cachepool = json_decode($res_cachepool,true);
                $cache_num = count($res_cachepool);
            }
            $cache_last_num = $res_pool['amount'] - ($cache_num+$now_amount);
            if($db_last_num<0 || $cache_last_num<0){
                $msg = '';
                if($day>1){
                    $msg = $day.'天';
                }
                $this->output("奖品:{$res_pool['name']},{$msg}奖池数量不够", 'hotellottery/prizelist',2);
            }

            $fields = '*';
            $res_prize = $m_prize->getDataList($fields,array('hotellottery_id'=>$lottery_id),'id desc');

            $data = array('hotellottery_id'=>$lottery_id,'prizepool_prize_id'=>$prizepool_prize_id,
                'level'=>$level,'amount'=>$amount,'status'=>$status);
            if($id){
                $condition = array('hotellottery_id'=>$lottery_id,'level'=>$level,'status'=>1);
                $condition['id'] = array('neq',$id);
                $res_pinfo = $m_prize->getInfo($condition);
                if(!empty($res_pinfo)){
                    $this->output('请勿设置相同等级的奖品', 'hotellottery/prizelist',2);
                }
                $m_prize->updateData(array('id'=>$id),$data);
            }else{
                $m_prize->add($data);
            }

            $m_lottery->updateData(array('id'=>$lottery_id),array('status'=>2));
            if(!empty($res_prize)){
                $m_activity = new \Admin\Model\Smallapp\ActivityModel();
                $res_send_activity = $m_activity->getAll('*',array('syslottery_id'=>$lottery_id,'type'=>10),0,1,'id desc');
                if(!empty($res_send_activity)){
                    $now_end_date = date('Y-m-d',strtotime($res_send_activity[0]['start_time']));
                    $now_day_diff = (strtotime($now_end_date)-strtotime($lottery_info['start_date']))/86400;
                    $day = $day-($now_day_diff+1);
                }

                foreach ($res_prize as $v){
                    $amount=$v['amount']*$day;
                    $prizepool_prize_id = $v['prizepool_prize_id'];
                    $lucky_pool_key = $key_pool.$prizepool_prize_id;
                    $res_cachepool = $redis->get($lucky_pool_key);
                    $prizepool_data = array();
                    if(!empty($res_cachepool)){
                        $prizepool_data = json_decode($res_cachepool,true);
                    }
                    for ($i=1;$i<=$amount;$i++){
                        $p_key = $lottery_info['hotel_id'].$lottery_id.$i;
                        unset($prizepool_data[$p_key]);
                    }
                    $redis->set($lucky_pool_key,json_encode($prizepool_data));
                }
            }

            $this->output('操作成功!', 'hotellottery/prizelist');
        }else{
            $vinfo = array();
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_prize->getInfo(array('id'=>$id));
                $vinfo['oss_addr'] = $oss_host.$vinfo['image_url'];
                $lottery_id = $vinfo['hotellottery_id'];
            }
            $m_prizepool = new \Admin\Model\Smallapp\PrizepoolprizeModel();
            $fields = 'a.id,a.name as prize_name,a.type,p.name';
            $where = array('hp.hotel_id'=>$lottery_info['hotel_id'],'p.status'=>1,'a.status'=>1);
            $res_prizelist = $m_prizepool->getHotelpoolprizeList($fields,$where,'p.id asc');
            $prizepools = array();
            foreach ($res_prizelist as $v){
                $name = $v['name'].'-'.$v['prize_name']."({$this->lottery_prize_types[$v['type']]})";
                $prizepools[]=array('prizepool_prize_id'=>$v['id'],'name'=>$name);
            }

            $vinfo['lottery_type'] = $lottery_info['type'];
            $this->assign('vinfo',$vinfo);
            $this->assign('lottery_id',$lottery_id);
            $this->assign('prizepools',$prizepools);
            $this->assign('prize_levels',$this->lottery_prize_level);
            $this->display();
        }
    }

    private function handle_publicinfo(){
        $hours = array();
        for($i=0;$i<24;$i++){
            $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
        }
        $minutes = array();
        for($i=0;$i<6;$i++){
            $minutes[]=str_pad($i*10,2,'0',STR_PAD_LEFT);
        }
        $wait_minutes = array();
        for($i=1;$i<7;$i++){
            $wait_minutes[]=$i*10;
        }
        $m_area = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $area_info = array();
        foreach ($res_area as $v){
            $area_info[$v['id']] = $v;
        }
        $where = array('flag'=>0,'state'=>1);
        $m_hotel = new \Admin\Model\HotelModel();
        $hlist = $m_hotel->getInfo('id,name,area_id',$where);
        foreach ($hlist as $k=>$v){
            if(isset($area_info[$v['area_id']])){
                $hlist[$k]['name'] = $area_info[$v['area_id']]['region_name'].'-'.$v['name'];
            }
        }
        $res = array('hours'=>$hours,'minutes'=>$minutes,'wait_minutes'=>$wait_minutes,'hotels'=>$hlist,'areas'=>$area_info);
        return $res;
    }

}