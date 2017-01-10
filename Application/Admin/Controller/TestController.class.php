<?php
namespace Admin\Controller;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class TestController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function testList() {
        //实例化redis
//         $redis = SavorRedis::getInstance();
//         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }
    
    public function ueditior(){
        if(IS_POST){
            $res_param = json_encode($_POST);
            $this->output('操作成功!', 'test/testList');
        }else{
            $this->display();
        }
    }
    
    public function echarts(){
        $this->display();
    }
    
    public function demodata(){
        $type = I('type',1);
        $shw_module = I('shw_module',0);
        if($type ==1){
            if($shw_module==0){
                $res = array ( 'date' => array ( 0 => '00:00', 1 => '01:00', 2 => '02:00', 3 => '03:00', 4 => '04:00', 5 => '05:00', 6 => '06:00', 7 => '07:00', 8 => '08:00',
                     9 => '09:00', 10 => '10:00', 11 => '11:00', 12 => '12:00', 13 => '13:00', 14 => '14:00', 
                    15 => '15:00', 16 => '16:00', 17 => '17:00', 18 => '18:00', 19 => '19:00', 20 => '20:00', 21 => '21:00', 22 => '22:00', 23 => '23:00', 24 => '23:59', ), 
                    'pageviews' => array ( 0 => '0', 1 => '0', 2 => '0', 3 => '0', 4 => '1', 5 => '0', 6 => '0', 7 => '0', 8 => '0', 9 => '1', 10 => '3', 11 => '1', 12 => '0', 
                        13 => '0', 14 => '0', 15 => '1', 16 => '0', 17 => '1', 18 => '1', 19 => '1', 20 => '0', 21 => '0', 22 => '4', 23 => '0', 24 => '0', ), 
                    'visitors' => array ( 0 => '0', 1 => '0', 2 => '0', 3 => '0', 4 => '1', 5 => '0', 6 => '0', 7 => '0', 8 => '0', 9 => '1', 10 => '3', 11 => '1', 12 => '0',
                         13 => '0', 14 => '0', 15 => '1', 16 => '0', 17 => '1', 18 => '1', 19 => '1', 20 => '0', 21 => '0', 22 => '1', 23 => '0', 24 => '0', ), );
            }else{
                $res = array (
                      'pvlist' => 
                      array (),
                      'entrancelist' => 
                      array (),
                      'sourcelist' => array(),
                      'visitors' => array (array (0,0)),
                      'pageviews' => 
                      array (array (0 => 0,1 => 0,)),
                    );
            }
        }
        echo json_encode($res);
        exit;
    }
    
    public function locationdata(){
        $res = array ( 0 => array ( 'name' => '北京', 'value' => 9, 'rate' => 64, ), 1 => array ( 'name' => '天津', 'value' => 0, 'rate' => 0, ), 
            2 => array ( 'name' => '上海', 'value' => 0, 'rate' => 0, ), 3 => array ( 'name' => '重庆', 'value' => 0, 'rate' => 0, ), 
            4 => array ( 'name' => '河北', 'value' => 0, 'rate' => 0, ), 
            5 => array ( 'name' => '河南', 'value' => 0, 'rate' => 0, ), 6 => array ( 'name' => '云南', 'value' => 0, 'rate' => 0, ), 
            7 => array ( 'name' => '辽宁', 'value' => 0, 'rate' => 0, ), 8 => array ( 'name' => '黑龙江', 'value' => 0, 'rate' => 0, ), 
            9 => array ( 'name' => '湖南', 'value' => 0, 'rate' => 0, ), 10 => array ( 'name' => '安徽', 'value' => 0, 'rate' => 0, ), 
            11 => array ( 'name' => '山东', 'value' => 0, 'rate' => 0, ), 12 => array ( 'name' => '新疆', 'value' => 0, 'rate' => 0, ), 
            13 => array ( 'name' => '江苏', 'value' => 0, 'rate' => 0, ), 14 => array ( 'name' => '浙江', 'value' => 0, 'rate' => 0, ), 
            15 => array ( 'name' => '江西', 'value' => 0, 'rate' => 0, ), 16 => array ( 'name' => '湖北', 'value' => 0, 'rate' => 0, ), 
            17 => array ( 'name' => '广西', 'value' => 0, 'rate' => 0, ), 18 => array ( 'name' => '甘肃', 'value' => 0, 'rate' => 0, ), 
            19 => array ( 'name' => '山西', 'value' => 0, 'rate' => 0, ), 20 => array ( 'name' => '内蒙古', 'value' => 0, 'rate' => 0, ), 
            21 => array ( 'name' => '陕西', 'value' => 0, 'rate' => 0, ), 22 => array ( 'name' => '吉林', 'value' => 0, 'rate' => 0, ), 
            23 => array ( 'name' => '福建', 'value' => 0, 'rate' => 0, ), 24 => array ( 'name' => '贵州', 'value' => 0, 'rate' => 0, ), 
            25 => array ( 'name' => '广东', 'value' => 0, 'rate' => 0, ), 26 => array ( 'name' => '青海', 'value' => 0, 'rate' => 0, ), 
            27 => array ( 'name' => '西藏', 'value' => 0, 'rate' => 0, ), 28 => array ( 'name' => '四川', 'value' => 0, 'rate' => 0, ), 
            29 => array ( 'name' => '宁夏', 'value' => 0, 'rate' => 0, ), 30 => array ( 'name' => '海南', 'value' => 0, 'rate' => 0, ), 
            31 => array ( 'name' => '台湾', 'value' => 0, 'rate' => 0, ), 32 => array ( 'name' => '香港', 'value' => 0, 'rate' => 0, ), 
            33 => array ( 'name' => '澳门', 'value' => 0, 'rate' => 0, ), 34 => array ( 'name' => '南海诸岛', 'value' => 0, 'rate' => 0, ), );
        echo json_encode($res);
    }
}