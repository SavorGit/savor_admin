<?php
/**
 * @author zhang.yingtao
 * @since  20180626
 * @desc   友盟推送日志
 */
namespace Admin\Model;
use Think\Model;
use Common\Lib\UmengNotice;
class PushLogModel extends Model{

	protected $tableName='push_log';

	public function addInfo($data,$type= 1){
	    if($type==1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}

	public function handle_push_rebootbox(){
	    $m_heartlog = new \Admin\Model\HeartAllLogModel();
        $fields = 'box_id,room_id,hotel_id';
        $nowdata = date('Ymd');
        $where = array('date'=>$nowdata,'type'=>2);
        $where['hour2'] = array('gt',0);
        $orderby = 'id desc';
	    $res_hearlog = $m_heartlog->getDataList($fields,$where,$orderby);
        $push_box = array();
	    if(!empty($res_hearlog)){
            $m_box =  new \Admin\Model\BoxModel();
            $shell_command_arr = array('reboot');
            $after_a = C('AFTER_APP');
            $production_mode = C('UMENG_PRODUCTION_MODE');
	        foreach ($res_hearlog as $v){
                $box_id = $v['box_id'];
                $room_id = $v['room_id'];
                $hotel_id = $v['hotel_id'];
                $field = "b.id,b.device_token";
                $where = " b.id=$box_id and r.id=$room_id and h.id=$hotel_id";
                $box_info =  $m_box->isHaveMac($field, $where);
                if(empty($box_info) || empty($box_info[0]['device_token'])){
                    continue;
                }

                $display_type = 'notification';
                $option_name = 'boxclient';
                $after_open = $after_a[3];
                $device_token = $box_info[0]['device_token'];
                $ticker = 'shell推送';
                $title  = 'shell推送';
                $text   = 'shell推送';
                $custom = array();
                $custom['type'] = 3;  //1:RTB  2:4G投屏 3:shell命令推送 4：apk升级
                $custom['action'] = 1; //1:投屏  0:结束投屏
                $custom['data'] = $shell_command_arr;

                $this->uPushData($display_type, 3,'listcast',$option_name, $after_open, $device_token,
                    $ticker,$title,$text,$production_mode,$custom);

                $push_data = array();
                $push_data['hotel_id'] = $hotel_id;
                $push_data['room_id']  = $room_id;
                $push_data['box_id']   = $box_id;
                $push_data['push_info']= json_encode($custom);
                $push_type['push_type']= 3;
                $this->addInfo($push_data);

                $push_box[]=$box_id;
            }
        }
        echo 'push_box_ids:'.json_encode($push_box)."\r\n";
	    echo "pushrebootbox finish \r\n";
    }

    /**
     * @desc 推送客户端数据
     * @param $display_type 必填, 消息类型: notification(通知), message(消息)
     * @param $device_type  客户端类型   3：安卓  4：ios
     * @param $type listcast-列播(要求不超过500个device_token)
     * @param $option_name app客户端  (运维端:optionclient)
     * @param $after_open 点击"通知"的后续行为，默认为打开app
     * @param $device_tokens  设备token
     * @param $production_mode 可选, 正式/测试模式。默认为true
     * @param $custom   当display_type=message时, 必填
     *                  当display_type=notification且after_open=go_custom时, 必填
    用户自定义内容, 可以为字符串或者JSON格式。
     * @param $extra   可选, JSON格式, 用户自定义key-value。只对"通知"
     */
    public function uPushData($display_type,$device_type = "3",$type='listcast',$option_name,$after_open,
                              $device_tokens = '',$ticker,$title,$text,$production_mode = 'false',
                              $custom = array(),$extra,$alert){
        $obj = new UmengNotice();

        $pam['device_tokens'] = $device_tokens;
        $pam['time'] = time();
        $pam['ticker'] = $ticker;
        $pam['title'] = $title;
        $pam['text'] = $text;
        $pam['after_open'] = $after_open;
        $pam['production_mode'] = $production_mode;
        $pam['display_type']    = $display_type;
        if(!empty($custom)){
            $pam['custom'] = json_encode($custom);
        }
        if(!empty($extra)){
            $pam['extra'] = $extra;
        }
        if($device_type==3){
            if(empty($custom)){
                $pam['custom'] = array('type'=>$type);
            }
            $listcast = $obj->umeng_android($type);
            //设置属于哪个app
            $config_parm = $option_name;
            $listcast->setParam($config_parm);
            $listcast->sendAndroidListcast($pam);
        }else if($device_type ==4){
            if(!empty($alert)){
                $pam['alert'] = $alert;
            }
            $pam['badge'] = 0;
            $pam['sound'] = 'chime';
            $listcast = $obj->umeng_ios($type);
            //设置属于哪个app
            $config_parm = $option_name;
            $listcast->setParam($config_parm);
            $listcast->sendIOSListcast($pam);
        }
    }
}