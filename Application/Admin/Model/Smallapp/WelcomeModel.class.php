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

            $m_media = new \Admin\Model\MediaModel();
            $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_welcome as $v){
                $start_time = date('Y-m-d H:i',strtotime($v['play_date'].' '.$v['timing']));
                if($start_time==$nowtime){

                    $wordsize_id = $v['wordsize_id'];
                    $color_id = $v['color_id'];
                    $backgroundimg_id = $v['backgroundimg_id'];
                    $music_id = $v['music_id'];
                    $ids = array($wordsize_id,$color_id);
                    if($music_id){
                        $ids[]=$music_id;
                    }
                    if($backgroundimg_id){
                        $ids[]=$backgroundimg_id;
                    }
                    $where = array('id'=>array('in',$ids));
                    $res_resource = $m_welcomeresource->getDataList('*',$where,'id asc');
                    $resource_info = array();
                    foreach ($res_resource as $resv){
                        $resource_info[$resv['id']]=$resv;
                    }
                    $message = array('action'=>130,'id'=>$v['id'],'forscreen_char'=>$v['content'],'rotation'=>$v['rotate'],
                        'wordsize'=>$resource_info[$wordsize_id]['tv_wordsize'],'color'=>$resource_info[$color_id]['color'],
                        'finish_time'=>$v['finish_time']);
                    if(isset($resource_info[$backgroundimg_id])){
                        $res_media = $m_media->getMediaInfoById($resource_info[$backgroundimg_id]['media_id']);
                        $message['img_id'] = intval($backgroundimg_id);
                        $message['img_oss_addr'] = $res_media['oss_addr'];
                    }else{
                        $message['img_id'] = 0;
                        $img_oss_addr = $v['image'];
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
                    $message['play_times'] = $playtime;
                    $push_message = json_encode($message);
                    $res_netty = $m_netty->pushBox($v['box_mac'],$push_message);
                    if(isset($res_netty['error_code']) && $res_netty['error_code']==90109){
                        $res_netty = $m_netty->pushBox($v['box_mac'],$push_message);
                    }
                    $this->where(array('id'=>$v['id']))->save(array('status'=>1));

                    $netty_result = json_encode($res_netty);
                    $time = date('Y-m-d H:i:s');
                    echo "date|$time|id|{$v['id']}|message|$push_message|netty_result|$netty_result \r\n";

                }
            }
        }
    }
}