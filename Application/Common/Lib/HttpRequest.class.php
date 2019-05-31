<?php
namespace Common\Lib;
class HttpRequest{

    private $_fp = null;
    private $_errno = '';
    private $_errstr = '';
    private $_formdata = array();
    private $_filedata = array();
    private $_config = array(
        'url'=>'',
        'port'=>80,
        'timeout'=>10,
        'ip'=>''
    );
    
    public function __construct($config){
        $this->setConfig($config);
    }
    
    public function setRequestData($formdata=array(),$filedata=array()){
        if(!empty($formdata))   $this->_formdata = $formdata;
        if(!empty($filedata))   $this->_filedata = $filedata;
    }

    public function send($type='multipart'){
        $type = strtolower($type);
        if(!in_array($type, array('get','post','multipart'))){
            return false;
        }
        if($this->connect()){
            switch($type){
                case 'get':
                    $out = $this->sendGet();
                    break;
                case 'post':
                    $out = $this->sendPost();
                    break;
                case 'multipart':
                    $out = $this->sendMultipart();
                    break;
            }
            if(!$out){
                return false;
            }
            
            fputs($this->_fp, $out);
            $response = '';
            while($row = fread($this->_fp, 4096)){
                $response .= $row;
            }
            $this->disconnect();
            $pos = strpos($response, "\r\n\r\n");
            $response = substr($response, $pos+4);
            return $response;
        }else{
            return false;
        }
    }

    private function setConfig($config){
        if(empty($config) || empty($config['url']))   return false;
        $urlinfo = parse_url($config['url']);
        $config['host'] = $urlinfo['host'];
        if(empty($config['port']))   $config['port'] = $this->_config['port'];
        if(empty($config['timeout']))   $config['timeout'] = $this->_config['timeout'];
        $this->_config = $config;
        if(empty($this->_config['ip'])) $this->_config['ip'] = $this->_config['host'];
    }
    
    private function connect(){
        $this->_fp = fsockopen($this->_config['ip'], $this->_config['port'], $this->_errno, $this->_errstr, $this->_config['timeout']);
        if(!$this->_fp){
            return false;
        }
        return true;
    }

    private function disconnect(){
        if($this->_fp!=null){
            fclose($this->_fp);
            $this->_fp = null;
        }
    }
    
    private function sendGet(){
        if(!$this->_formdata){
            return false;
        }
        $url = $this->_config['url'].'?'.http_build_query($this->_formdata);
        $out = "GET ".$url." http/1.1\r\n";
        $out .= "host: ".$this->_config['host']."\r\n";
        $out .= "connection: close\r\n\r\n";
        return $out;
    }

    private function sendPost(){
        if(!$this->_formdata && !$this->_filedata){
            return false;
        }
        $data = $this->_formdata? $this->_formdata : array();
        if($this->_filedata){
            foreach($this->_filedata as $filedata){
                if(file_exists($filedata['file'])){
                    $data[$filedata['name']] = file_get_contents($filedata['file']);
                }
            }
        }
        if(!$data){
            return false;
        }
        $data = http_build_query($data);
        $out = "POST ".$this->_config['url']." http/1.1\r\n";
        $out .= "host: ".$this->_config['host']."\r\n";
        $out .= "content-type: application/x-www-form-urlencoded\r\n";
        $out .= "content-length: ".strlen($data)."\r\n";
        $out .= "connection: close\r\n\r\n";
        $out .= $data;
        return $out;
    }

    private function sendMultipart(){
        if(!$this->_formdata && !$this->_filedata){
            return false;
        }
        srand((double)microtime()*1000000);
        $boundary = '---------------------------'.substr(md5(rand(0,32000)),0,10);
        $data = '--'.$boundary."\r\n";
        $formdata = '';
        foreach($this->_formdata as $key=>$val){
            $formdata .= "content-disposition: form-data; name=\"".$key."\"\r\n";
            $formdata .= "content-type: text/plain\r\n\r\n";
            if(is_array($val)){
                $formdata .= json_encode($val)."\r\n";
            }else{
                $formdata .= rawurlencode($val)."\r\n";
            }
            $formdata .= '--'.$boundary."\r\n";
        }
        $filedata = '';
        foreach($this->_filedata as $val){
            if(file_exists($val['file'])){
                $filedata .= "content-disposition: form-data; name=\"".$val['name']."\"; filename=\"".basename($val['file'])."\"\r\n";
                $filedata .= "content-type: ".mime_content_type($val['file'])."\r\n\r\n";
                $filedata .= implode('', file($val['file']))."\r\n";
                $filedata .= '--'.$boundary."\r\n";
            }
        }
        
        if(!$formdata && !$filedata){
            return false;
        }
        $data .= $formdata.$filedata."--\r\n\r\n";
        $out = "POST ".$this->_config['url']." http/1.1\r\n";
        $out .= "host: ".$this->_config['host']."\r\n";
        $out .= "content-type: multipart/form-data; boundary=".$boundary."\r\n";
        $out .= "content-length: ".strlen($data)."\r\n";
        $out .= "connection: close\r\n\r\n";
        $out .= $data;
        return $out;
    }

}

?>