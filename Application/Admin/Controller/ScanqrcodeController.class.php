<?php
namespace Admin\Controller;

use Think\Controller;
use DeviceDetector\DeviceDetector;

class ScanqrcodeController extends Controller {

    public function ads(){
        $id = I('get.id','');
        $box_mac = I('get.mac','');

        if(empty($id)){
            die('params error');
        }
        $hash_ids_key = C('HASH_IDS_KEY');
        $hashids = new \Common\Lib\Hashids($hash_ids_key);
        $res_decode = $hashids->decode($id);
        $urlmap_id = intval($res_decode[0]);
        $m_urlmap  = new \Admin\Model\UrlmapModel();
        $res_map = $m_urlmap->getInfo(array('id'=>$urlmap_id));
        if(empty($res_map)){
            die('params not exists');
        }
        $jump_url = $res_map['link'];

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $ip = getClientIP();
        $data = array('urlmap_id'=>$urlmap_id,'user_agent'=>$user_agent,'ip'=>$ip);

        require_once APP_PATH.'Common/Lib/DeviceDetector/spyc-0.6.2/Spyc.php';
        require_once APP_PATH.'Common/Lib/DeviceDetector/autoload.php';
        $deviceDetector = new DeviceDetector($user_agent);
        $deviceDetector->parse();
        $res_os = $deviceDetector->getOs();
        if(!empty($res_os)){
            $data['mobile_os'] = $res_os['name'];
        }
        $res_brand = $deviceDetector->getBrandName();
        if(!empty($res_brand)){
            $data['mobile_brand'] = $res_brand;
        }
        $res_model = $deviceDetector->getModel();
        if(!empty($res_model)){
            $data['mobile_model'] = $res_model;
        }
        if(!empty($box_mac)){
            $data['box_mac'] = $box_mac;
        }
        $data['data_id'] = $res_map['goods_id'];
        $m_qrscanrecord = new \Admin\Model\QrscanRecordModel();
        $m_qrscanrecord->add($data);

        header('Location:'.$jump_url);
        exit;
    }
    
}