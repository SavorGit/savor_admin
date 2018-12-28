<?php
/**
 * @desc   小程序酒楼评级数据统计
 * @author liubin
 */
namespace Admin\Model\Smallapp;
use Think\Model;
class StaticHotelgradeModel extends Model{

	protected $tableName='smallapp_static_hotelgrade';

    public function getGradenums($date,$level=1){
        $where = array('static_date'=>$date);
        if($level)  $where['level'] = $level;
        $fields = "count(`id`) as num";
        $ret = $this->field($fields)->where($where)->find();
        $nums = !empty($ret['num'])?$ret['num']:0;
        return $nums;
    }

    public function getList($fields,$where,$order){
        $data = $this->field($fields)->where($where)->order($order)->select();
        return $data;
    }

    public function getListnums($fields,$where,$order){
        $res_hotel = $this->getList($fields,$where,$order);
        $a = $b = $c = 0;
        foreach ($res_hotel as $hv){
            if($hv['level']==1){
                $a++;
            }elseif($hv['level']==2){
                $b++;
            }elseif($hv['level']==3){
                $c++;
            }
        }
        $res = array('a'=>$a,'b'=>$b,'c'=>$c);
        return $res;
    }

}