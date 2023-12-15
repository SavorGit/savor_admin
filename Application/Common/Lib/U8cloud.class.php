<?php
namespace Common\Lib;
class U8cloud {

    public function apiquery($api,$method,$params){
        $u8_conf = C('U8_CONFIG');
        $api_host = $u8_conf['api_host'];
        $system = $u8_conf['system'];
        $usercode = $u8_conf['usercode'];
        $password = $u8_conf['password'];

        $header_info = array(
            "Content-Type: application/json",
            "usercode: $usercode",
            "password: $password",
            "system: $system",
        );
        $GLOBALS['HEADERINFO'] = $header_info;
        $api_url = $api_host.$api;
        $res = '';
        $curl = new \Common\Lib\Curl();
        switch ($method){
            case 'get':
                $params_query = '';
                $url = $api_url.'?'.$params_query;
                $curl::get($url,$res,10);
                break;
            case 'post':
                $url = $api_url;
                $params = json_encode($params);
                $curl::post($url,$params,$res);
                break;
        }
        return array('url'=>$api_url,'result'=>$res);
    }
    //部门档案添加
    public function addDepartmentInfo($params){
        $api = '/u8cloud/api/uapbd/bddept/save';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //部门档案修改
    public function editDepartmentInfo($params){
        $api = '/u8cloud/api/uapbd/bddept/update';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    
    //部门人员新增、修改
    public function saveDepartmentUser($params){
        $api = '/u8cloud/api/uapbd/bdpsn/save';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //部门人员删除
    public function delDepartmentUser($params){
        $api = '/u8cloud/api/uapbd/bdpsn/delete';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //新增存货分类
    public function addGoodsBrand($params){
        $api = '/u8cloud/api/uapbd/bdinvcl/save';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //编辑存货分类
    public function editGoodsBrand($params){
        $api = '/u8cloud/api/uapbd/bdinvcl/update';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //新增存货档案
    public function addGoods($params){
        $api = '/u8cloud/api/uapbd/invbasdoc/insert';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //新增存货档案
    public function editGoods($params){
        $api = '/u8cloud/api/uapbd/invbasdoc/update';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
}
?>