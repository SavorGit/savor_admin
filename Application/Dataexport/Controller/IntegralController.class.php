<?php
namespace Dataexport\Controller;

class IntegralController extends BaseController{

    public function hotelrecord(){
        $m_integral = new \Admin\Model\Smallapp\UserIntegralModel();
        $sql = "select hotel.id as hotel_id,hotel.name as hotel_name,merchant.id as merchant_id,hotel.area_id from savor_smallapp_user_integral as a left join savor_integral_merchant_staff as staff on a.openid=staff.openid 
left join savor_integral_merchant as merchant on staff.merchant_id=merchant.id left join savor_hotel as hotel on merchant.hotel_id=hotel.id
where merchant.status=1 and hotel.id not in(7,883) group by hotel.id ";
        $res_integral = $m_integral->query($sql);
        $datalist = array();
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $all_area = array();
        foreach ($area_arr as $v){
            $all_area[$v['id']] = $v['region_name'];
        }
        $m_box = new \Admin\Model\BoxModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_exchange = new \Admin\Model\Smallapp\ExchangeModel();
        $datalist = array();
        foreach ($res_integral as $v){
            $box_fields = 'count(box.id) as num';
            $bwhere = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
            $res_box = $m_box->getBoxByCondition($box_fields,$bwhere);
            $box_num = 0;
            if(!empty($res_box)){
                $box_num = intval($res_box[0]['num']);
            }
            $res_staff = $m_staff->getDataList('openid',array('merchant_id'=>$v['merchant_id']),'id desc');
            $openids = array();
            foreach ($res_staff as $sv){
                $openids[]=$sv['openid'];
            }
            $res_now_integral = $m_integral->getDataList('sum(integral) as integral',array('openid'=>array('in',$openids)));
            $now_integral = 0;
            if(!empty($res_now_integral)){
                $now_integral = intval($res_now_integral[0]['integral']);
            }
            $sql_exchange = "select sum(goods.rebate_integral) as integral from savor_smallapp_exchange as a left join savor_smallapp_goods as goods on a.goods_id=goods.id where a.hotel_id={$v['hotel_id']} and a.type=1 and a.status=21";
            $res_exchange = $m_exchange->query($sql_exchange);
            $exchange_integral = 0;
            if(!empty($res_exchange)){
                $exchange_integral = intval($res_exchange[0]['integral']);
            }
            $total_integral = $now_integral+$exchange_integral;


            /*
            $i_fields = 'sum(integral) as integral';
            $i_where = array('openid'=>array('in',$openids));
            $i_where['type'] = array('in',array(1,2,6,7,8));
            $all_integral = $m_integralrecord->getDataList($i_fields,$i_where,'id desc');
            $total_integral = 0;
            if(!empty($all_integral)){
                $total_integral = $all_integral[0]['integral'];
            }
            $i_where['type'] = 4;
            $all_exchange_integral = $m_integralrecord->getDataList($i_fields,$i_where,'id desc');
            $exchange_integral = 0;
            if(!empty($all_exchange_integral)){
                $exchange_integral = abs($all_exchange_integral[0]['integral']);
            }
            */


            if($total_integral>0){
                $datalist[] = array('hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],'area_name'=>$all_area[$v['area_id']],
                    'box_num'=>$box_num,'total_integral'=>$total_integral,'exchange_integral'=>$exchange_integral,
                    'now_integral'=>$now_integral);
            }
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('box_num','设备数量'),
            array('total_integral','累计产生积分'),
            array('exchange_integral','已兑换积分'),
            array('now_integral','未兑换积分'),

        );
        $filename = '积分餐厅名录';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function datalist(){
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $cache_key = 'cronscript:integral:datalist'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if($res == 1){
                $this->success('数据正在生成中,请稍后点击下载');
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php dataexport/integral/datalistscript/start_date/$start_date/end_date/$end_date > /tmp/null &";
            system($shell);
            $redis->set($cache_key,1,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    public function datalistscript(){
        set_time_limit(360);
        ini_set("memory_limit","1024M");
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $where = array();
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
        }else{
            $start_time = date('Y-m-01 00:00:00',strtotime("-1 month"));
            $end_time = date('Y-m-31 23:59:59',strtotime("-1 month"));
        }
        $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');

        $m_integral_record = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $data_list = $m_integral_record->getList('a.*,user.avatarUrl,user.nickName',$where,'a.id desc');
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $integral_types = C('INTEGRAL_TYPES');
        foreach ($data_list as $k=>$v){
            switch ($v['type']){
                case 1:
                    $info = $integral_types[$v['type']].$v['content'].'小时';
                    if($v['fj_type']==1){
                        $info.='--午饭';
                    }elseif($v['fj_type']==2){
                        $info.='--晚饭';
                    }
                    break;
                case 2:
                    $info = $integral_types[$v['type']].$v['content'].'人数';
                    break;
                case 3:
                case 4:
                case 5:
                    $goods_info = $m_goods->getInfo(array('id'=>$v['goods_id']));
                    $info = $integral_types[$v['type']].'商品：'.$goods_info['name'].' 数量：'.$v['content'];
                    break;
                default:
                    $info = $integral_types[$v['type']];
            }
            $data_list[$k]['info'] = $info;
            $status_str = '';
            if($v['type']==4){
                $status_str = '已使用';
            }else{
                if($v['status']==1){
                    $status_str = '可用';
                }elseif($v['status']==2){
                    $status_str = '待核销';
                }
            }
            $data_list[$k]['status_str']  = $status_str;
            $data_list[$k]['type_str']  = $integral_types[$v['type']];
        }
        $cell = array(
            array('id','ID'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','MAC地址'),
            array('openid','用户openid'),
            array('nickname','用户昵称'),
            array('info','积分信息'),
            array('integral','所得积分'),
            array('status_str','积分状态'),
            array('type_str','类型'),
            array('integral_time','积分时间'),

        );
        $filename = '积分明细表';
        $path = $this->exportToExcel($cell,$data_list,$filename,2);
        $cache_key = 'cronscript:integral:datalist'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }

    public function exchangemoney(){
        $money = I('money',500,'intval');
        $start_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
        $end_time = date('Y-m-31 23:59:59',strtotime('-1 month'));

        $sql = "select um.*,user.mobile,user.nickName,user.name,user.idnumber from 
            (select sum(total_fee) as money,openid,hotel_id from savor_smallapp_exchange where status=21 and 
            add_time>='{$start_time}' and add_time<='{$end_time}' group by openid) as um left join savor_smallapp_user as user on um.openid=user.openid 
            where um.money>={$money}";
        $datalist = M()->query($sql);
        $m_hotel = new \Admin\Model\HotelModel();
        foreach ($datalist as $k=>$v){
            $res_hotel = $m_hotel->getOne($v['hotel_id']);
            $datalist[$k]['hotel_name'] = $res_hotel['name'];
            $datalist[$k]['month'] = date('Y-m',strtotime($start_time));
            if(!empty($v['idnumber'])){
                $datalist[$k]['idnumber'] = "'{$v['idnumber']}";
            }
        }
        $cell = array(
            array('money','金额'),
            array('openid','用户openid'),
            array('mobile','手机号码'),
            array('nickname','昵称'),
            array('name','姓名'),
            array('idnumber','身份证号码'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('month','月份'),
        );
        $filename = '兑换金额大于500的用户';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}