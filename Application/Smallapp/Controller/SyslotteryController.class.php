<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 系统抽奖
 *
 */
class SyslotteryController extends BaseController {

    public $lottery_types = array('1'=>'系统抽奖','2'=>'幸运抽奖','3'=>'幸运抽奖(通用活动)');
    public $lottery_prize_types = array('1'=>'现金','2'=>'实物','3'=>'无奖');

    public function datalist(){
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');

        $m_syslottery = new \Admin\Model\Smallapp\SyslotteryModel();
        $where = array();
        if($status){
            $where['status'] = $status;
        }
        if($type){
            $where['type'] = $type;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_syslottery->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $m_sysuser = new \Admin\Model\UserModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $oss_host = get_oss_host();
        foreach ($data_list as $k=>$v){
            $res_hotel = $m_hotel->getOne($v['hotel_id']);
            $data_list[$k]['hotel_name'] = $res_hotel['name'];
            $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
            $data_list[$k]['type_str'] = $this->lottery_types[$v['type']];
            if($v['type']==1){
                $send_time = $v['start_date'].'至'.$v['end_date']." {$v['timing']}";
            }else{
                $send_time = '';
            }

            $data_list[$k]['send_time'] = $send_time;
            $res_user = $m_sysuser->getUserInfo($v['sysuser_id']);
            $data_list[$k]['username'] = $res_user['remark'];
            if($v['status']==1){
                $data_list[$k]['statusstr'] = '可用';
            }else{
                $data_list[$k]['statusstr'] = '不可用';
            }
        }
        $this->assign('lottery_types',$this->lottery_types);
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function syslotteryadd(){
        $id = I('id',0,'intval');
        $m_syslottery = new \Admin\Model\Smallapp\SyslotteryModel();
        if(IS_POST){
            $prize = I('post.prize','');
            $media_id = I('post.media_id',0,'intval');
            $type = I('post.type',0,'intval');
            $start_date = I('post.start_date','');
            $end_date = I('post.end_date','');
            $hour = I('post.hour','');
            $minute = I('post.minute','');
            $hotel_id = I('post.hotel_id',0,'intval');
            $status = I('post.status',0,'intval');

            $userInfo = session('sysUserInfo');
            $data = array('prize'=>$prize,'hotel_id'=>$hotel_id,'sysuser_id'=>$userInfo['id'],'type'=>$type);
            if($type==1){
                $data['start_date'] = $start_date;
                $data['end_date'] = $end_date;
                if(empty($hour) || empty($minute)){
                    $this->output('发送时间不能为空', 'syslottery/syslotteryadd',2,0);
                }
                $data['timing'] = $hour.':'.$minute;
                if(!empty($media_id)){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_media = $m_media->getMediaInfoById($media_id);
                    $data['image_url'] = $res_media['oss_path'];
                }
            }
            if($id){
                $m_syslottery_prize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
                $res_prize = $m_syslottery_prize->getDataList('count(id) as num',array('syslottery_id'=>$id),'id desc');
                $prize_num = $res_prize[0]['num'];
                if($status==1 && $prize_num<3){
                    $this->output('请先配置至少3个奖品', 'syslottery/syslotteryadd',2,0);
                }
                if($status==1 && $prize_num>=3){
                    $res_prize = $m_syslottery_prize->getDataList('sum(probability) as probability',array('syslottery_id'=>$id),'id desc');
                    $probability = $res_prize[0]['probability'];
                    if($probability!=100){
                        $this->output('请检查配置的奖品概率总和是否为100', 'syslottery/syslotteryadd',2,0);
                    }
                }
                $data['status'] = $status;
                $result = $m_syslottery->updateData(array('id'=>$id),$data);
            }else{
                $data['status'] = 0;
                $result = $m_syslottery->addData($data);
                $id = $result;
            }
            $this->output('操作成功!', 'syslottery/datalist');
        }else{
            $vinfo = array('status'=>0,'type'=>2);
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_syslottery->getInfo(array('id'=>$id));
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

            $this->assign('lottery_types', $this->lottery_types);
            $this->assign('hlist', $hlist);
            $this->assign('areas', $areas);
            $this->assign('vinfo',$vinfo);
            $this->assign('hours',$hours);
            $this->assign('minutes',$minutes);
            $this->display();
        }
    }

    public function prizelist(){
        $syslottery_id = I('syslottery_id',0,'intval');
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_syslottery_prize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
        $where = array('syslottery_id'=>$syslottery_id);
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_syslottery_prize->getDataList('*',$where,$orderby,$start,$size);
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
        }
        $m_syslottery = new \Admin\Model\Smallapp\SyslotteryModel();
        $lottery_info = $m_syslottery->getInfo(array('id'=>$syslottery_id));

        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('status',$status);
        $this->assign('syslottery_id',$syslottery_id);
        $this->assign('lottery_type',$lottery_info['type']);
        $this->display();
    }

    public function prizeadd(){
        $id = I('id',0,'intval');
        $syslottery_id = I('syslottery_id',0,'intval');
        $m_syslottery_prize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
        $m_syslottery = new \Admin\Model\Smallapp\SyslotteryModel();
        $lottery_info = $m_syslottery->getInfo(array('id'=>$syslottery_id));
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $money = I('post.money',0,'intval');
            $probability = I('post.probability',0,'intval');
            $prizepool_prize_id = I('post.prizepool_prize_id',0,'intval');
            $type = I('post.type',0,'intval');
            $status = I('post.status',0,'intval');
            $interact_num = I('post.interact_num',0,'intval');
            $demand_hotplay_num = I('post.demand_hotplay_num',0,'intval');
            $demand_banner_num = I('post.demand_banner_num',0,'intval');
            if($lottery_info['type']==1 && $type==1){
                if(empty($money)){
                    $this->output('请输入中奖金额', "syslottery/prizeadd", 2, 0);
                }
                if(empty($interact_num) && empty($demand_hotplay_num) && empty($demand_banner_num)){
                    $this->output('请输入需要完成的任务次数', "syslottery/prizeadd", 2, 0);
                }
            }

            $data = array('syslottery_id'=>$syslottery_id,'name'=>$name,'money'=>$money,'probability'=>$probability,'prizepool_prize_id'=>$prizepool_prize_id,'type'=>$type,
                'interact_num'=>$interact_num,'demand_hotplay_num'=>$demand_hotplay_num,'demand_banner_num'=>$demand_banner_num,'status'=>$status);
            if($media_id){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            if($prizepool_prize_id){
                $m_prizepool = new \Admin\Model\Smallapp\PrizepoolprizeModel();
                $res_prizepool = $m_prizepool->getInfo(array('id'=>$prizepool_prize_id));
                $data['name'] = $res_prizepool['name'];
                $data['money'] = $res_prizepool['money'];
                $data['image_url'] = $res_prizepool['image_url'];
                $data['type'] = $res_prizepool['type'];
            }

            if($id){
                $m_syslottery_prize->updateData(array('id'=>$id),$data);
            }else{
                $m_syslottery_prize->add($data);
            }
            $this->output('操作成功!', 'syslottery/prizelist');
        }else{
            if($id){
                $oss_host = get_oss_host();
                $vinfo = $m_syslottery_prize->getInfo(array('id'=>$id));
                $vinfo['oss_addr'] = $oss_host.$vinfo['image_url'];
                $syslottery_id = $vinfo['syslottery_id'];
            }else{
                $all_probability = 100;
                $res_lottery = $m_syslottery_prize->getDataList('sum(probability) as probability',array('syslottery_id'=>$syslottery_id),'id desc');
                $now_probability = 0;
                if(!empty($res_lottery)){
                    $now_probability=$res_lottery[0]['probability'];
                }
                $vinfo = array('probability'=>$all_probability-$now_probability,'interact_num'=>0,'demand_hotplay_num'=>0,'demand_banner_num'=>0,'type'=>1);
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
            $this->assign('syslottery_id',$syslottery_id);
            $this->assign('prizepools',$prizepools);
            $this->assign('prize_types',$this->lottery_prize_types);
            $this->display();
        }
    }

    public function syslotterydel(){
        $id = I('get.id',0,'intval');
        $m_syslottery = new \Admin\Model\Smallapp\SyslotteryModel();
        $result = $m_syslottery->delData(array('id'=>$id));
        if($result){
            $m_syslottery_prize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
            $m_syslottery_prize->delData(array('syslottery_id'=>$id));
            $this->output('操作成功!', 'syslottery/datalist',2);
        }else{
            $this->output('操作失败', 'syslottery/datalist',2,0);
        }
    }

    private function handle_publicinfo(){
        $hours = array();
        for($i=0;$i<24;$i++){
            $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
        }
        $minutes = array();
        for($i=0;$i<4;$i++){
            $minutes[]=str_pad($i*15,2,'0',STR_PAD_LEFT);
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
        $res = array('hours'=>$hours,'minutes'=>$minutes,'hotels'=>$hlist,'areas'=>$area_info);
        return $res;
    }

}