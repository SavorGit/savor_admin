<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-设备概况
 *
 */
class DeviceoverviewController extends BaseController {

    public function index(){
        $city = I('get.city',0,'intval');

        $this->display();
    }

}