<?php
namespace Dataexport\Controller;

class HotelController extends BaseController{

    public function hotellist(){
        $area_id = I('get.aid',0,'intval');

        $sql = "select hotel.id as hotel_id,hotel.name as hotel_name,hotel.area_id,area.region_name as area_name,hotel.addr,hotel.county_id,hotel.state from savor_hotel as hotel 
left join savor_area_info as area on hotel.area_id=area.id where hotel.state in(1,2) and hotel.flag=0 ";
        if($area_id>0){
            $sql.=" and hotel.area_id={$area_id}";
        }
        $model = M();
        $res_hotel = $model->query($sql);

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $all_area = array();
        foreach ($area_arr as $v){
            $all_area[$v['id']] = $v['region_name'];
        }
        $m_box = new \Admin\Model\BoxModel();
        $datalist = array();
        foreach ($res_hotel as $v){
            $district = '';
            $county_id = $v['county_id'];
            if(!empty($county_id)){
                $res_county = $m_area->getWhere('*',array('id'=>$county_id),'','');
                if(!empty($res_county)){
                    $district = $res_county[0]['region_name'];
                }
            }
            if($v['state']==1){
                $hotel_state_str = '正常';
            }else{
                $hotel_state_str = '冻结';
            }
            $box_fields = 'count(box.id) as num';
            $bwhere = array('hotel.id'=>$v['hotel_id'],'box.state'=>array('in',array(1,2)),'box.flag'=>0);
            $res_box = $m_box->getBoxByCondition($box_fields,$bwhere);
            $box_num = 0;
            if(!empty($res_box)){
                $box_num = intval($res_box[0]['num']);
            }

            $datalist[] = array('hotel_id'=>$v['hotel_id'],'area_name'=>$all_area[$v['area_id']],'hotel_name'=>$v['hotel_name'],
            'district'=>$district,'addr'=>$v['addr'],'hotel_state_str'=>$hotel_state_str,'box_num'=>$box_num);
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('district','所在区'),
            array('addr','地址'),
            array('hotel_state_str','酒楼状态'),
            array('box_num','版位数'),
        );
        $filename = '酒楼正常和冻结列表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function drinksprice(){
        $file_path = SITE_TP_PATH.'/Public/content/酒楼白酒种类清单-0818.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $m_hotel_drinks = new \Admin\Model\HoteldrinksModel();
        $m_room = new \Admin\Model\RoomModel();
        $m_box = new \Admin\Model\BoxModel();
        $datalist = array();
        for ($row = 3; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $city = $rowData[0][0];
                $hotel_name = $rowData[0][1];
                $hotel_id = $rowData[0][2];

                $rfields = 'count(*) as num';
                $rwhere = array('hotel_id'=>$hotel_id,'state'=>array('in',array(1,2)),'flag'=>0);
                $res_room = $m_room->getInfo($rfields,$rwhere,'id desc','');
                $room_num = intval($res_room[0]['num']);

                $bfields = 'count(box.id) as num';
                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>array('in',array(1,2)),'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition($bfields,$bwhere,'');
                $box_num = intval($res_box[0]['num']);

                $tmp_drinks = array_slice($rowData[0],5);
                if(!empty($tmp_drinks)){
                    $d_num = ceil(count($tmp_drinks) / 2);
                    for ($i=0;$i<$d_num;$i++){
                        $offset = $i*2;
                        $now_drinks = array_slice($tmp_drinks,$offset,2);
                        if(empty($now_drinks[0]) && empty($now_drinks[1])){
                            break;
                        }
                        $name_info = explode('、',$now_drinks[0]);

                        $name = join('',$name_info);
                        $price = $now_drinks[1];
                        $brand = $name_info[0];
                        $series = $name_info[1];
                        $degree = $name_info[2];
                        $capacity = $name_info[3];
                        $dinfo = array('city'=>$city,'hotel_name'=>$hotel_name,'hotel_id'=>$hotel_id,'room_num'=>$room_num,
                            'box_num'=>$box_num,'brand'=>$brand,'series'=>$series,'degree'=>$degree,'capacity'=>$capacity,
                            'price'=>$price,'name'=>$name);
                        $datalist[]=$dinfo;
                    }
                }
            }
        }
        $cell = array(
            array('city','城市'),
            array('hotel_name','酒楼名称'),
            array('hotel_id','酒楼ID'),
            array('room_num','包间数'),
            array('box_num','版位数'),
            array('brand','白酒品牌'),
            array('series','系列名称'),
            array('degree','度数'),
            array('capacity','容量'),
            array('price','价格'),
        );
        $filename = '酒楼酒水单';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function salewine(){
        $static_date = I('date','');
        if(empty($sdate)){
            $static_date = date('Y-m-d',strtotime('-1 day'));
        }else{
            $static_date = date('Y-m-d',strtotime($static_date));
        }
        $start_time = "$static_date 00:00:00";
        $end_time = "$static_date 23:59:59";

        $hotel_ids = array(395,962,964,1056,955,1064,1257,912,898,1250,1284,810,941,720,1110,
            1211,1287,1321,847,970,1240,1271,1289,1033,1049,1062,1107,1124,1031,1029,920);
        $m_basicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $fields = 'hotel_id,hotel_name,static_date,dinner_zxrate as zxrate,wlnum,scancode_num,user_num';
        $where = array('hotel_id'=>array('in',$hotel_ids),'static_date'=>$static_date);
        $res_datas = $m_basicdata->getDataList($fields,$where,'dinner_zxrate asc');
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $datalist = array();
        foreach ($res_datas as $v){
            $order_num = 0;
            $where = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
            $where['a.status'] = array('not in',array(10,11));
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
            $ofields = 'count(a.id) as num';
            $res_orders = $m_order->getOrderinfoList($ofields,$where,'a.id desc');
            if(!empty($res_orders)){
                $order_num = $res_orders[0]['num'];
            }
            $info = array('static_date'=>$v['static_date'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],'zxrate'=>$v['zxrate'],
                'wlnum'=>$v['wlnum'],'scancode_num'=>$v['scancode_num'],'user_num'=>$v['user_num'],'order_num'=>$order_num
            );
            $datalist[]=$info;
        }
        $cell = array(
            array('static_date','日期'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('zxrate','开机率'),
            array('wlnum','屏幕数'),
            array('scancode_num','扫码数'),
            array('user_num','用户数'),
            array('order_num','下单数')
        );
        $filename = '酒楼售酒统计';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }
}