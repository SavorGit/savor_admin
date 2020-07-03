<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class WelcomeModel extends BaseModel{
	protected $tableName='smallapp_welcome';

    public function getWelcomeList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.user_id=user.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field('a.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function updateExpiredData(){
        $where = array('status'=>1);
        $where['finish_time'] = array('lt',date('Y-m-d H:i:s'));
        $res_ids = $this->field('id')->where($where)->select();
        if(!empty($res_ids)){
            $ids = array();
            foreach ($res_ids as $v){
                $ids[]=$v['id'];
            }
            $where = array('id'=>array('in',$ids));
            $this->where($where)->save(array('status'=>3));
        }
        return true;
    }

	public function handle_welcome(){
	    $nowtime = date('Y-m-d H:i');
        $where = array('play_type'=>2,'status'=>2);
        $where['finish_time'] = array('egt',date('Y-m-d H:i:s'));
        $res_welcome = $this->getDataList('*',$where,'id asc');
        if(!empty($res_welcome)){
            $m_sys_config = new \Admin\Model\SysConfigModel();
            $sys_info = $m_sys_config->getAllconfig();
            $playtime = $sys_info['welcome_playtime'];
            $playtime = intval($playtime*60);

            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            $m_box = new \Admin\Model\BoxModel();

            foreach ($res_welcome as $v){
                $start_time = date('Y-m-d H:i',strtotime($v['play_date'].' '.$v['timing']));
                if($start_time==$nowtime){

                    $message = $this->welcome_message($v,$playtime);

                    $m_staff = new \Admin\Model\Integral\StaffModel();
                    $m_user = new \Admin\Model\Smallapp\UserModel();
                    $m_welcome_playfail = new \Admin\Model\Smallapp\WelcomePlayrecordModel();
                    if($v['type']==2){
                        $res_box = $m_box->getBoxListByHotelRelation('box.room_id,room.hotel_id,box.mac as box_mac',$v['hotel_id']);

                        echo "boxs:".json_encode($res_box)."\r\n";

                        $all_push_log = array();
                        foreach ($res_box as $bv){
                            $res_staff = $m_staff->getInfo(array('hotel_id'=>$v['hotel_id'],'room_id'=>$bv['room_id']));
                            $message['type'] = 1;
                            $message['waiterName'] = '';
                            $message['waiterIconUrl'] = '';
                            if(!empty($res_staff)){
                                $message['type'] = 2;
                                $where_user = array('openid'=>$res_staff['openid']);
                                $res_user = $m_user->getOne('id as user_id,avatarUrl,nickName',$where_user,'id desc');
                                $message['waiterName'] = $res_user['nickName'];
                                $message['waiterIconUrl'] = $res_user['avatarUrl'];
                            }
                            $push_message = json_encode($message);
                            $res_netty = $m_netty->pushBox($bv['box_mac'],$push_message);

                            $play_data = array('welcome_id'=>$v['id'],'box_mac'=>$bv['box_mac'],'status'=>1,
                                'hotel_id'=>$v['hotel_id'],'type'=>$v['type'],'finish_time'=>$v['finish_time']);
                            $m_welcome_playfail->add($play_data);

                            $all_push_log[] = array('box'=>$bv['box_mac'],'netty_result'=>$res_netty);
                        }
                    }else{
                        $box_where = array('box.flag'=>0,'box.state'=>1,'hotel.flag'=>0,'hotel.state'=>1,'hotel.id'=>$v['hotel_id'],'box.mac'=>$v['box_mac']);
                        $res_box = $m_box->getInfoByCondition('box.room_id,room.hotel_id',$box_where);

                        echo "boxs:".json_encode($res_box)."\r\n";

                        $res_staff = $m_staff->getInfo(array('hotel_id'=>$v['hotel_id'],'room_id'=>$res_box['room_id']));
                        $message['type'] = 1;
                        $message['waiterName'] = '';
                        $message['waiterIconUrl'] = '';
                        if(!empty($res_staff)){
                            $message['type'] = 2;
                            $where_user = array('openid'=>$res_staff['openid']);
                            $res_user = $m_user->getOne('id as user_id,avatarUrl,nickName',$where_user,'id desc');
                            $message['waiterName'] = $res_user['nickName'];
                            $message['waiterIconUrl'] = $res_user['avatarUrl'];
                        }

                        $push_message = json_encode($message);
                        $res_netty = $m_netty->pushBox($v['box_mac'],$push_message);
                        $play_data = array('welcome_id'=>$v['id'],'box_mac'=>$v['box_mac'],'status'=>1,
                            'hotel_id'=>$v['hotel_id'],'type'=>$v['type'],'finish_time'=>$v['finish_time']);
                        $m_welcome_playfail->add($play_data);

                        $all_push_log[] = array('box'=>$v['box_mac'],'netty_result'=>$res_netty);
                    }
                    $this->where(array('id'=>$v['id']))->save(array('status'=>1));

                    $push_log = json_encode($all_push_log);
                    $time = date('Y-m-d H:i:s');
                    echo "date|$time|id|{$v['id']}|push_log|$push_log \r\n";

                }
            }
        }
    }


    public function welcome_message($welcome,$playtime){
        $wordsize_id = $welcome['wordsize_id'];
        $color_id = $welcome['color_id'];
        $backgroundimg_id = $welcome['backgroundimg_id'];
        $music_id = $welcome['music_id'];
        $font_id = $welcome['font_id'];
        $ids = array($wordsize_id,$color_id);
        if($music_id){
            $ids[]=$music_id;
        }
        if($font_id){
            $ids[]=$font_id;
        }
        if($backgroundimg_id){
            $ids[]=$backgroundimg_id;
        }
        $m_media = new \Admin\Model\MediaModel();
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $where = array('id'=>array('in',$ids));
        $res_resource = $m_welcomeresource->getDataList('*',$where,'id asc');
        $resource_info = array();
        foreach ($res_resource as $resv){
            $resource_info[$resv['id']]=$resv;
        }
        $message = array('action'=>130,'id'=>$welcome['id'],'forscreen_char'=>$welcome['content'],'rotation'=>$welcome['rotate'],
            'wordsize'=>$resource_info[$wordsize_id]['tv_wordsize'],'color'=>$resource_info[$color_id]['color'],
            'finish_time'=>$welcome['finish_time']);
        if(isset($resource_info[$backgroundimg_id])){
            $res_media = $m_media->getMediaInfoById($resource_info[$backgroundimg_id]['media_id']);
            $message['img_id'] = intval($backgroundimg_id);
            $message['img_oss_addr'] = $res_media['oss_addr'];
        }else{
            $message['img_id'] = 0;
            $img_oss_addr = $welcome['image'];
            $message['img_oss_addr'] = $img_oss_addr;
        }
        $name_info = pathinfo($message['img_oss_addr']);
        $message['filename'] = $name_info['basename'];
        if(isset($resource_info[$music_id])){
            $res_media = $m_media->getMediaInfoById($resource_info[$music_id]['media_id']);
            $message['music_id'] = intval($music_id);
            $message['music_oss_addr'] = $res_media['oss_addr'];
        }else{
            $message['music_id'] = 0;
            $message['music_oss_addr'] = '';
        }
        if(isset($resource_info[$font_id])){
            $res_media = $m_media->getMediaInfoById($resource_info[$font_id]['media_id']);
            $message['font_id'] = intval($font_id);
            $message['font_oss_addr'] = $res_media['oss_addr'];
        }else{
            $message['font_id'] = 0;
            $message['font_oss_addr'] = '';
        }
        $message['play_times'] = $playtime;
        return $message;
    }
}