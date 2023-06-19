<?php
namespace Common\Lib;
class AliyunOCR{
    public $data;
    public $accessKeyId = '';
    public $accessKeySecret = '';
    public $url = "http://ocr-api.cn-hangzhou.aliyuncs.com/?";

    public function __construct($actionArray){
        date_default_timezone_set("GMT");
        $this->accessKeyId = C('OSS_ACCESS_ID');
        $this->accessKeySecret = C('OSS_ACCESS_KEY');
        $this->data = array(
            'Format' => 'json',
            'Version' => '2021-07-07',
            'AccessKeyId' => $this->accessKeyId,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce'=> uniqid(),
            'Timestamp' => date('Y-m-d\TH:i:s\Z'),
        );
        if(is_array($actionArray)){
            $this->data = array_merge($this->data,$actionArray);
        }
    }

    public function percentEncode($str){
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    public function computeSignature($parameters, $accessKeySecret){
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key)
                . '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    public function result(){
        $this->data['Signature'] = $this->computeSignature($this->data, $this->accessKeySecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . http_build_query($this->data));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        $res = json_decode($res,true);
        //$res = json_encode($res,JSON_UNESCAPED_UNICODE);
        return $res;
    }
}