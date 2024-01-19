<?php
namespace Common\Lib;
class U8cloud {

    public function apiquery($api,$method,$params,$trantype=''){
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
        if(!empty($trantype)){
            $header_info[]="trantype: $trantype";
        }
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
        $res_decode = json_decode($res,true);
        if($res_decode['status']!='success'){
            $m_u8log = new \Admin\Model\U8logModel();
            $m_u8log->add(array('host'=>$api_host,'api'=>$api,'method'=>$method,
                'params'=>$params,'res_data'=>$res
            ));
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
    //新增会计科目
    public function addAccsubj($params){
        $api = '/u8cloud/api/uapbd/accsubj/insert';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //编辑会计科目
    public function editAccsubj($params){
        $api = '/u8cloud/api/uapbd/accsubj/update';
        $method = 'post';
        $res = $this->apiquery($api,$method,$params);
        return $res;
    }
    //客商基本档案新增
    public function addCustdoc($params){
        $api = '/u8cloud/api/uapbd/custdoc/insert';
        $method = 'post';
        $trantype = 'code';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
    //客商基本档案修改
    public function editCustdoc($params){
        $api = '/u8cloud/api/uapbd/custdoc/update';
        $method = 'post';
        $trantype = 'code';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
    //客商基本档案分配
    public function assignCustdoc($params){
        $api = '/u8cloud/api/uapbd/custdoc/assign';
        $method = 'post';
        $trantype = '';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
    //凭证新增
    public function addVoucher($params){
        $api = '/u8cloud/api/gl/voucher/insert';
        $method = 'post';
        $trantype = 'code';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
    //凭证删除
    public function delVoucher($params){
        $api = '/u8cloud/api/gl/voucher/delete';
        $method = 'post';
        $trantype = '';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
    //凭证作废
    public function abandonVoucher($params){
        $api = '/u8cloud/api/gl/voucher/abandon';
        $method = 'post';
        $trantype = 'code';
        $res = $this->apiquery($api,$method,$params,$trantype);
        return $res;
    }
}
?>