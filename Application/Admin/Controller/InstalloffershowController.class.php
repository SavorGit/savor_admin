<?php
/**
 * @desc   活动
 * @author zhang.yingtao
 * @since  2017-10-16
 */
namespace Admin\Controller;

use Think\Controller;

class InstalloffershowController extends Controller{
    
    public function index(){
        $id = I('get.id');
        
        $m_offer_device = new \Admin\Model\OfferResultDetailModel();
        
        //获取设备信息
        $equipList = $m_offer_device->getEquipList($id,1);
        $this->assign('equipList',$equipList);
        //小计设备信息市场总价、我方报价
        $equip_market_total = 0;
        $equip_our_price_total = 0;
        foreach($equipList as $key=>$v){
            $equip_market_total +=$v['nums']*$v['market_price'];
            $equip_our_price_total += $v['our_price'];
        }
        $this->assign('equip_market_total',$equip_market_total);
        $this->assign('equip_our_price_total',$equip_our_price_total);
        //获取安装调试信息
        $installList = $m_offer_device->getOtherList($id, 2);
        $this->assign('installList',$installList);
        //小计设备调试安装市场单价、我方报价
        $install_market_price_total = 0;
        $install_our_price_total = 0;
        foreach($installList as $key=>$v){
            $install_market_price_total += $v['nums']*$v['market_price'];
            $install_our_price_total += $v['our_price'];
        }
        $this->assign('install_market_price_total',$install_market_price_total);
        $this->assign('install_our_price_total',$install_our_price_total);
        //获取硬件报价信息
        $hardwareList = $m_offer_device->getOtherList($id, 3);
        $this->assign('hardwareList',$hardwareList);
        //获取软件报价信息
        $softwareList = $m_offer_device->getOtherList($id, 4);
        $this->assign('softwareList',$softwareList);
        $this->display('Installoffer/show');
    }
}