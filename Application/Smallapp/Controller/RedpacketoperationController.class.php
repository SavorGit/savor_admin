<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 红包运营管理
 *
 */
class RedpacketoperationController extends BaseController {

    public function operationlist(){
        $status = I('status',99,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $all_sendtypes = C('REDPACKET_SENDTYPES');
        $m_redpacketoperation = new \Admin\Model\Smallapp\RedpacketoperationModel();
        $where = array();
        if($status!=99){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_redpacketoperation->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $m_sysuser = new \Admin\Model\UserModel();
        foreach ($data_list as $k=>$v){
            $send_time = '';
            $data_list[$k]['typestr'] = $all_sendtypes[$v['type']];
            if($v['type']==2){
                $send_time = $v['start_date']." {$v['timing']}";
            }elseif($v['type']==3){
                $send_time = $v['start_date'].'至'.$v['end_date']." {$v['timing']}";
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
        $this->assign('status',$status);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function operationadd(){
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $total_fee = I('post.total_fee',0);
            $amount = I('post.amount',0);
            $scope = I('post.scope',1,'intval');
            $type = I('post.type',1,'intval');//1立即发送,2单次定时,3多次定时
            $start_date = I('post.start_date','');
            $end_date = I('post.end_date','');
            $hour = I('post.hour','');
            $minute = I('post.minute','');
            $sender = I('post.sender',0,'intval');
            $status = I('post.status',0,'intval');
            $hotel_id = I('post.regiona1_id',0,'intval');
            $room_id = I('post.regiona2_id',0,'intval');
            $box_id = I('post.regiona3_id',0,'intval');

            $userInfo = session('sysUserInfo');
            $data = array('total_fee'=>$total_fee,'amount'=>$amount,'scope'=>$scope,'type'=>$type,'start_date'=>$start_date,
                'end_date'=>$end_date,'sender'=>$sender,'sysuser_id'=>$userInfo['id'],'status'=>$status);
            if($total_fee<$amount*0.3){
                $this->output('每个红包最小额度为0.3', 'redpacketoperation/operationadd',2,0);
            }
            if($type==2 || $type==3){
                if(empty($start_date)){
                    $this->output('发送日期不能为空', 'redpacketoperation/operationadd',2,0);
                }
                if(empty($hour) || empty($minute)){
                    $this->output('发送时间不能为空', 'redpacketoperation/operationadd',2,0);
                }
                $data['timing'] = $hour.':'.$minute;
            }
            if($type==3){
                if(empty($end_date)){
                    $this->output('结束日期不能为空', 'redpacketoperation/operationadd',2,0);
                }
            }

            if(empty($box_id) && !$id){
                $this->output('请选择优先发送的版位', 'redpacketoperation/operationadd',2,0);
            }
            if($box_id){
                $m_box = new \Admin\Model\BoxModel();
                $res_box = $m_box->find($box_id);
                $data['mac'] = $res_box['mac'];
            }

            $m_redpacketoperation = new \Admin\Model\Smallapp\RedpacketoperationModel();
            if($id){
                $result = $m_redpacketoperation->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_redpacketoperation->addData($data);
                $id = $result;
            }
            if($type==1 && $status==1){
                $crontab_c = A('Admin/Crontab');
                $crontab_c->operationRedpacket($id);
            }
            if($result){
                $this->output('操作成功!', 'redpacketoperation/operationlist');
            }else{
                $this->output('操作失败', 'redpacketoperation/operationadd',2,0);
            }
        }else{
            $all_sendtypes = C('REDPACKET_SENDTYPES');
            $all_senders = C('REDPACKET_SENDERS');
            $all_scopes = C('REDPACKET_SCOPE');
            $vinfo = array('scope'=>1,'type'=>1,'status'=>0);
            $res = $this->handle_publicinfo();
            $hours = $res['hours'];
            $minutes = $res['minutes'];
            $hlist = $res['hotels'];

            $this->assign('hlist', $hlist);
            $this->assign('vinfo',$vinfo);
            $this->assign('scopes',$all_scopes);
            $this->assign('senders',$all_senders);
            $this->assign('sendtypes',$all_sendtypes);
            $this->assign('hours',$hours);
            $this->assign('minutes',$minutes);
            $this->display();
        }
    }

    public function operationedit(){
        $id = I('get.id',0,'intval');
        $m_redpacketoperation = new \Admin\Model\Smallapp\RedpacketoperationModel();
        $vinfo = $m_redpacketoperation->getInfo(array('id'=>$id));
        $vinfo['total_fee'] = intval($vinfo['total_fee']);
        $timing_info = explode(':',$vinfo['timing']);
        $hour = '00';
        $minute = '00';
        if($vinfo['type']!=1){
            $hour = $timing_info[0];
            $minute = $timing_info[1];
        }
        if($vinfo['start_date']=='0000-00-00'){
            $vinfo['start_date'] = '';
        }
        if($vinfo['end_date']=='0000-00-00'){
            $vinfo['end_date'] = '';
        }
        $m_mac = new \Admin\Model\BoxModel();
        $fields = 'box.mac as box_mac,room.name as room_name,hotel.name as hotel_name';
        $where = array('box.mac'=>$vinfo['mac'],'box.state'=>1,'box.flag'=>0);
        $macinfo = $m_mac->getInfoByCondition($fields,$where);

        $all_sendtypes = C('REDPACKET_SENDTYPES');
        $all_senders = C('REDPACKET_SENDERS');
        $all_scopes = C('REDPACKET_SCOPE');

        $res = $this->handle_publicinfo();
        $hours = $res['hours'];
        $minutes = $res['minutes'];
        $hlist = $res['hotels'];

        $this->assign('macinfo',$macinfo);
        $this->assign('hour',$hour);
        $this->assign('minute',$minute);
        $this->assign('hlist', $hlist);
        $this->assign('vinfo',$vinfo);
        $this->assign('scopes',$all_scopes);
        $this->assign('senders',$all_senders);
        $this->assign('sendtypes',$all_sendtypes);
        $this->assign('hours',$hours);
        $this->assign('minutes',$minutes);
        $this->display('operationadd');
    }

    public function operationdel(){
        $id = I('get.id',0,'intval');
        $m_redpacketoperation = new \Admin\Model\Smallapp\RedpacketoperationModel();
        $result = $m_redpacketoperation->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'redpacketoperation/operationlist',2);
        }else{
            $this->output('操作失败', 'redpacketoperation/operationlist',2,0);
        }
    }

    private function handle_publicinfo(){
        $hours = array();
        for($i=0;$i<24;$i++){
            $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
        }
        $minutes = array();
        for($i=0;$i<60;$i++){
            $minutes[]=str_pad($i,2,'0',STR_PAD_LEFT);
        }
        $where = array('flag'=>0,'state'=>1);
        $m_hotel = new \Admin\Model\HotelModel();
        $hlist = $m_hotel->getInfo('id,name',$where);
        $res = array('hours'=>$hours,'minutes'=>$minutes,'hotels'=>$hlist);
        return $res;
    }

}