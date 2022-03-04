<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreeninvalidrecordModel extends BaseModel{
	protected $tableName='smallapp_forscreen_invalidrecord';

    public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)->count();

        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    /*
     * 获取饭局互动过的可互动版位(饭局时间内有互动且版位标识为互动版位的)
     * $time 时间戳
     * $fj_type 0全部饭局 1午饭 2晚饭
     */
    public function getFeastInteractBoxByHotelId($hotel_id,$time,$fj_type=0,$small_id=1){
        $feast_time = C('MEAL_TIME');
        $lunch_start = date("Y-m-d {$feast_time['lunch'][0]}:00",$time);
        $lunch_end = date("Y-m-d {$feast_time['lunch'][1]}:00",$time);
        $dinner_start = date("Y-m-d {$feast_time['dinner'][0]}:00",$time);
        $dinner_end = date("Y-m-d {$feast_time['dinner'][1]}:59",$time);

        $where = array('a.hotel_id'=>$hotel_id);
        $where_lunch = array('a.create_time'=>array(array('EGT',$lunch_start),array('ELT',$lunch_end)));
        $where_dinner = array('a.create_time'=>array(array('EGT',$dinner_start),array('ELT',$dinner_end)));
        switch ($fj_type){
            case 0:
                $where['_complex'] = array(
                    $where_lunch,
                    $where_dinner,
                    '_logic' => 'or'
                );
                break;
            case 1:
                $where['a.create_time'] = $where_lunch['a.create_time'];
                break;
            case 2:
                $where['a.create_time'] = $where_dinner['a.create_time'];
                break;
        }
        $where['a.is_valid'] = 1;
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $where['box.is_interact'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_id==1){
            $where['a.small_app_id'] = array('in',array(1,2,11));
        }elseif($small_id==5){
            $where['a.small_app_id'] = 5;
        }

        $fields = 'a.box_mac';
        $groupby = 'a.box_mac';
        $result = $this->getWhere($fields,$where,'',$groupby);
        return $result;
    }

    public function getWhere($fields,$where,$limit,$group){
        $data = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on box.room_id=room.id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)->limit($limit)->group($group)->select();
        return $data;
    }

}