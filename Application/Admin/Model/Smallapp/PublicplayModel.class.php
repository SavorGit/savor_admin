<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class PublicplayModel extends BaseModel{
	protected $tableName='smallapp_publicplay';

	public function handle_public_play(){
        $now_date = date('Y-m-d H:i:s');
        $where = array('status'=>1);
        $where['start_date'] = array('ELT',$now_date);
        $where['end_date'] = array('EGT',$now_date);
        $res = $this->getDataList('*',$where,'id asc');
        if(empty($res)){
            $now_time = date('Y-m-d H:i:s');
            echo "time:$now_time no data \r\n";
            exit;
        }
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_public_playhotel = new \Admin\Model\Smallapp\PublicplayHotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $all_play_frequency = C('PUBLIC_PLAY_FREQUENCY');
        $all_box_types = C('heart_hotel_box_type');
        $now_hour = date('G');
        $now_minute = date('i');
        foreach ($res as $v){
            if($now_hour>=$v['start_hour'] && $now_hour<=$v['end_hour']){
                if(in_array($now_minute,$all_play_frequency[$v['frequency']])){
                    $publicplay_id = $v['id'];
                    $res_playhotel = $m_public_playhotel->getDataList('*',array('publicplay_id'=>$publicplay_id),'id asc');
                    if(empty($res_playhotel)){
                        echo "ID:{$v['id']} no hotel \r\n";
                        continue;
                    }
                    $res_public = $m_public->getOne('*',array('id'=>$v['public_id']),'');
                    $uwhere = array('openid'=>$res_public['openid']);
                    $res_user = $m_user->getOne('openid,avatarUrl,nickName',$uwhere,'');
                    $netty_message = array('openid'=>$res_public['openid'],'forscreen_id'=>$res_public['forscreen_id'],
                        'avatarUrl'=>$res_user['avatarurl'],'nickName'=>$res_user['nickname']);
                    $res_type = $res_public['res_type'];//1:图片投屏2:视频投屏
                    if($res_type==2){
                        $netty_message['action']=141;
                        $res_forscreen = $m_forscreen_record->getInfo(array('openid'=>$res_public['openid'],'forscreen_id'=>$res_public['forscreen_id']));
                        $img_info = json_decode($res_forscreen['imgs'],true);
                        $file_info = pathinfo($img_info[0]);
                        $netty_message['url'] = $img_info[0];
                        $netty_message['filename'] = $file_info['basename'];
                        $netty_message['video_id'] = $file_info['filename'];
                        $netty_message['resource_size'] = $res_forscreen['resource_size'];
                    }else{
                        $netty_message['action']=142;
                        $fwhere = array('openid'=>$res_public['openid'],'forscreen_id'=>$res_public['forscreen_id']);
                        $res_forscreen = $m_forscreen_record->getDataList('*',$fwhere,'id asc');
                        $img_nums = count($res_forscreen);
                        $img_list = array();
                        foreach ($res_forscreen as $fk=>$fv){
                            $img_info = json_decode($fv['imgs'],true);
                            $file_info = pathinfo($img_info[0]);
                            $img_list[]=array('url'=>$img_info[0],'filename'=>$file_info['basename'],'order'=>$fk+1,
                                'img_id'=>$file_info['filename'],'resource_size'=>$fv['resource_size']);
                        }
                        $netty_message['img_nums']=$img_nums;
                        $netty_message['img_list']=$img_list;
                    }

                    $hotel_ids = array();
                    foreach ($res_playhotel as $hv){
                        $hotel_ids[]=$hv['hotel_id'];
                    }
                    $bwhere = array('box.state'=>1,'box.flag'=>0);
                    $bwhere['box.box_type'] = array('in',array_keys($all_box_types));
                    $bwhere['hotel.id'] = array('in',$hotel_ids);
                    $res_boxs = $m_box->getBoxByCondition('box.mac,hotel.id as hotel_id',$bwhere);
                    foreach ($res_boxs as $bv){
                        $netty_message['headPic'] = base64_encode($netty_message['avatarUrl']);
                        $res_netty_box = $m_netty->pushBox($bv['mac'],json_encode($netty_message));

                        $netty_data = json_encode($res_netty_box);
                        echo "ID:{$v['id']} box:{$bv['mac']}-hotel_id{$bv['hotel_id']} message:".json_encode($netty_message)."netty:$netty_data \r\n";
                    }
                }else{
                    echo "ID:{$v['id']} hour:{$now_hour} not in minute \r\n";
                }
            }else{
                echo "ID:{$v['id']} hour:{$now_hour} not in hour \r\n";
            }
        }

    }
}