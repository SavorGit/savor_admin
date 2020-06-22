<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class WelcomePlayrecordModel extends BaseModel{
	protected $tableName='smallapp_welcome_playrecord';

	public function handle_welcomefail(){
        $where = array('status'=>1);
        $where['finish_time'] = array('egt',date('Y-m-d H:i:s'));
        $res_welcomefail = $this->getDataList('*',$where,'id asc');
        if(!empty($res_welcomefail)){
            $m_sys_config = new \Admin\Model\SysConfigModel();
            $sys_info = $m_sys_config->getAllconfig();
            $playtime = $sys_info['welcome_playtime'];
            $playtime = intval($playtime*60);

            $m_welcome = new \Admin\Model\Smallapp\WelcomeModel();
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            $m_staff = new \Admin\Model\Integral\StaffModel();
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $m_box = new \Admin\Model\BoxModel();

            $diff_time = 30*60;
            foreach ($res_welcomefail as $v){
                $now_time = time();
                $add_time = strtotime($v['add_time']);
                $welcome_id = $v['welcome_id'];
                $hotel_id = $v['hotel_id'];
                if($add_time+$diff_time<$now_time){
                    echo "welcome_id:|$welcome_id|box_mac|{$v['box_mac']}|expire \r\n";
                    continue;
                }
                $welcome_where = array('hotel_id'=>$hotel_id,'type'=>$v['type']);
                $res_welcome = $m_welcome->getAll('*',$welcome_where,0,1,'id desc');
                if(!empty($res_welcome) && $res_welcome[0]['id']==$welcome_id){
                    $message = $m_welcome->welcome_message($res_welcome[0],$playtime);
                    $message['type'] = 1;
                    $message['waiterName'] = '';
                    $message['waiterIconUrl'] = '';

                    $box_where = array('box.flag'=>0,'box.state'=>1,'hotel.flag'=>0,'hotel.state'=>1,'hotel.id'=>$v['hotel_id'],'box.mac'=>$v['box_mac']);
                    $res_box = $m_box->getInfoByCondition('box.room_id,room.hotel_id',$box_where);
                    $res_staff = $m_staff->getInfo(array('hotel_id'=>$res_box['hotel_id'],'room_id'=>$res_box['room_id']));
                    if(!empty($res_staff)){
                        $message['type'] = 2;
                        $where_user = array('openid'=>$res_staff['openid']);
                        $res_user = $m_user->getOne('id as user_id,avatarUrl,nickName',$where_user,'id desc');
                        $message['waiterName'] = $res_user['nickName'];
                        $message['waiterIconUrl'] = $res_user['avatarUrl'];
                    }
                    $push_message = json_encode($message);
                    $res_netty = $m_netty->pushBox($v['box_mac'],$push_message);
                    if($res_netty['code'] ==10000){
//                        $this->updateData(array('id'=>$v['id']),array('status'=>2));
                        echo "welcome_id:|$welcome_id|box_mac|{$v['box_mac']}|push ok|result|".json_encode($res_netty)."\r\n";
                    }else{
                        echo "welcome_id:|$welcome_id|box_mac|{$v['box_mac']}|push fail|result|".json_encode($res_netty)."\r\n";
                    }
                }else{
                    $this->updateData(array('id'=>$v['id']),array('status'=>3));
                    echo "welcome_id:|$welcome_id|box_mac|{$v['box_mac']}|replace|result|newwelcome_id:{$res_welcome[0]['id']} \r\n";
                }
            }
        }


    }
}