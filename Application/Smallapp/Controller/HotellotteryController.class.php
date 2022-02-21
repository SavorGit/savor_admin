<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 餐厅抽奖
 *
 */
class HotellotteryController extends BaseController {

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
            $data_list[$k]['image_url'] = $oss_host.$v['image_url'];
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
                'start_date'=>$start_date,'end_date'=>$end_date,'sysuser_id'=>$userInfo['id'],'status'=>$status);
            if(empty($hour) || empty($minute)){
                $this->output('发送时间不能为空', 'syslottery/syslotteryadd',2,0);
            }
            $data['timing'] = $hour.':'.$minute;
            if(!empty($media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['image_url'] = $res_media['oss_path'];
            }
            if($id){
                $result = $m_hotellottery->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_hotellottery->addData($data);
                $id = $result;
            }
            $this->output('操作成功!', 'hotellottery/datalist');
        }else{
            $vinfo = array('status'=>0,'wait_time'=>30,'type'=>2);
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
        $result = $m_hotellottery->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'hotellottery/datalist',2);
        }else{
            $this->output('操作失败', 'hotellottery/datalist',2,0);
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