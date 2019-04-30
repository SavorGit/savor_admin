<?php
/**
 * @desc   小程序用户扫码
 * @author zhang.yingtao
 * @since  2019-4-29
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class QrcodeLogModel extends Model
{
	protected $tableName='smallapp_qrcode_log';
    public function getQrcount($where,$group){
        
        $ret = $this->alias('a')
             ->join('savor_box box on a.box_mac=box.mac','left')
             ->join('savor_room room on box.room_id=room.id','left')
             ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
             ->join('savor_area_info area  on hotel.area_id=area.id','left')
             ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
             ->join('savor_sysuser user on ext.maintainer_id= user.id','left')
             ->join('savor_smallapp_user suser on a.openid=suser.openid','left')
             ->where($where)
             ->field('a.id')
             ->group($group)
             ->select();
        
        $count = count($ret);
        return $count;
    }
}