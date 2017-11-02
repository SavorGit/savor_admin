<?php
//defined('IN_PHPCMS') or exit('No permission resources.');
namespace Common\Lib;
use Common\Lib\Curl;
class UmengApi {
    private $umeng_api_config ;
    public function __construct(){
        $this->umeng_api_config = C('UMENT_API_CONFIG');
        
    }
    public function  umeng_api_ios($params = array(),$ext_arr,$client = 'opclient'){
        $data = array();
        $umeng_config      = $this->umeng_api_config;
        
        $data['appkey']    = $umeng_config[$client]['ios_AppKey'];
        $data['timestamp'] = time();
        $data['type']      = $params['type'];
        $data['device_tokens'] = $params['device_tokens'];
        $data['payload']['aps']['alert'] = $params['alert'];
        $data['payload']['aps']['sound'] = $params['sound'];
        $data['production_mode'] =$params['production_mode'];
        foreach($ext_arr as $key=>$v){
            $data['payload'][$key] = $v;
        }
        $sign = $this->genMySinIos($params,$ext_arr,$client);
        
        $curl = new Curl();
        $url = $umeng_config['API_URL'];
        $url .='?sign='.$sign;
        $data = json_encode($data);
        $curl->post($url, $data, $result);
        $result = json_decode($result,true);
        if($result['ret']=='SUCCESS'){
            return true;
        }else {
            return false;
        }
    }

    public function  umeng_api_android_single($params = array(),$ext_arr = array(),$client = 'opclient'){
        $data = array();
        $ument_config = $this->umeng_api_config;
        $data['appkey']    = $ument_config[$client]['AppKey'];

        $data['type']      = $params['type'];
        $data['timestamp'] = time();
        $data['production_mode'] = $params['production_mode'];

        $data['payload']['display_type']   = $params['display_type'];
        $data['payload']['body']['ticker'] = $params['ticker'];
        $data['payload']['body']['title']  = $params['title'];
        $data['payload']['body']['text']   = $params['text'];
        $data['payload']['body']['after_open'] = $params['after_open'] ;
        $data['payload']['extra'] = $ext_arr;
        $data['device_tokens'] = $params['device_tokens'];
        var_dump($data);
        $sign = $this->genMySinSingle($data, $client, $data['timestamp']);


        $curl = new Curl();
        $url = $ument_config['API_URL'];
        $url .='?sign='.$sign;
        $data = json_encode($data);
        $curl->post($url, $data, $result);
        $result = json_decode($result,true);
      var_dump($result);
        if($result['ret']=='SUCCESS'){
            return true;
        }else {
            return false;
        }
    }


    public function  umeng_api_android($params = array(),$ext_arr = array(),$client = 'opclient'){
        $data = array();
        $ument_config = $this->umeng_api_config;
        $data['appkey']    = $ument_config[$client]['AppKey'];
        $data['timestamp'] = strval(time());
        $data['type']      = $params['type'];
        //$data['device_tokens'] = $params['device_tokens'];
        $data['payload']['display_type']   = $params['display_type'];
        $data['payload']['body']['ticker'] = $params['ticker'];
        $data['payload']['body']['title']  = $params['title'];
        $data['payload']['body']['text']   = $params['text'];
        $data['payload']['body']['after_open'] = $params['after_open'] ;
        $data['payload']['extra'] = $ext_arr;
        $data['production_mode'] = $params['production_mode'];
        $sign = $this->genMySin($params,$ext_arr,$client);
        
        $curl = new Curl();
        $url = $ument_config['API_URL'];
        $url .='?sign='.$sign;
        $data = json_encode($data);
        $curl->post($url, $data, $result);
        $result = json_decode($result,true);

        if($result['ret']=='SUCCESS'){
            return true;
        }else {
            return false;
        }
    }

    private function genMySinSingle($params = array(),$client,$atime){

        $umeng_config = $this->umeng_api_config;
        $appkey            = $umeng_config[$client]['AppKey'];
        $app_master_secret = $umeng_config[$client]['App_Master_Secret'];
        $method            = 'POST';
        $url               = $umeng_config['API_URL'];
        echo '<hr/><hr/>';
        var_dump($params);
        $post_body = json_encode($params);

        $sign = md5("POST" . $url . $post_body . $app_master_secret);

        return $sign;
    }

    private function genMySin($params = array(),$ext_arr = array(),$client){
        
        $umeng_config = $this->umeng_api_config;
        $appkey            = $umeng_config[$client]['AppKey'];
        $app_master_secret = $umeng_config[$client]['App_Master_Secret'];
        $timestamp         = time();
        $method            = 'POST';
        $url               = $umeng_config['API_URL'];
        $data = array();
        $data['appkey']  = $appkey;
        $data['timestamp']     = time();
        //$data['device_tokens'] = $params['device_tokens'];
        $data['type'] = $params['type'];
        $data['payload']['display_type'] = $params['display_type'];
        $data['payload']['body']['ticker'] = $params['ticker'];
        $data['payload']['body']['title'] = $params['title'];
        $data['payload']['body']['text'] = $params['text'];
        $data['payload']['body']['after_open'] = $params['after_open'];
        $data['payload']['extra'] = $ext_arr;
        $data['production_mode'] = $params['production_mode'];
        $post_body = json_encode($data);
        
        $sign = md5("POST" . $url . $post_body . $app_master_secret);
        
        return $sign;
    }
    private function genMySinIos($params,$ext_arr,$client){
        $umeng_config = $this->umeng_api_config;
        $appkey            = $umeng_config[$client]['ios_AppKey'];
        $app_master_secret = $umeng_config[$client]['ios_App_Master_Secret'];
        //$timestamp         = time();
        $method            = 'POST';
        $url               = $umeng_config['API_URL'];
        //$post_body = json_encode($data);
        
        $data['appkey']    = $appkey;
        $data['timestamp'] = time();
        $data['type']      = $params['type'];
        $data['device_tokens'] = $params['device_tokens'];
        $data['payload']['aps']['alert'] = $params['alert'];
        $data['payload']['aps']['sound'] = $params['sound'];
        foreach($ext_arr as $key=>$v){
            $data['payload'][$key] = $v;
        }
        $data['production_mode'] = $params['production_mode'];
        $post_body = json_encode($data);
        $sign = md5("POST" . $url . $post_body . $app_master_secret);
        
        return $sign;
    }
}