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
        $fields = 'hotel_id,hotel_name,static_date,dinner_zxrate as zxrate,wlnum,scancode_num,user_num,heart_num';
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
                'wlnum'=>$v['wlnum'],'scancode_num'=>$v['scancode_num'],'user_num'=>$v['user_num'],'heart_num'=>$v['heart_num'],
                'order_num'=>$order_num
            );
            $datalist[]=$info;
        }
        $cell = array(
            array('static_date','日期'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('heart_num','心跳数'),
            array('zxrate','开机率'),
            array('wlnum','屏幕数'),
            array('scancode_num','扫码数'),
            array('user_num','用户数'),
            array('order_num','下单数')
        );
        $filename = '酒楼售酒统计';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function invitation(){
        $sdate = I('sdate','');
        $edate = I('edate','');
        if(!empty($sdate) && !empty($edate)){
            $static_sdate = date('Y-m-d',strtotime($sdate));
            $static_edate = date('Y-m-d',strtotime($edate));
            $start_time = "$static_sdate 00:00:00";
            $end_time = "$static_edate 23:59:59";
        }else{
            $static_date = date('Y-m-d',strtotime('-1 day'));
            $start_time = "$static_date 00:00:00";
            $end_time = "$static_date 23:59:59";
        }

        $m_invitation = new \Admin\Model\Smallapp\InvitationModel();
        $where = array();
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res_datas = $m_invitation->getDataList('*',$where,'id desc');
        $datalist = array();
        foreach ($res_datas as $v){
            $info = array('openid'=>$v['openid'],'name'=>$v['name'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],
                'room_name'=>$v['room_name'],'box_mac'=>$v['box_mac'],'book_time'=>$v['book_time'],'add_time'=>$v['add_time']
            );
            $datalist[]=$info;
        }
        $cell = array(
            array('openid','用户openid'),
            array('name','预订人'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','机顶盒MAC'),
            array('book_time','预定时间'),
            array('add_time','添加时间'),
        );
        $filename = '邀请函统计';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function invitationqrj(){
        $sdate = '2023-02-14';
        $edate = '2023-02-14';
        if(!empty($sdate) && !empty($edate)){
            $static_sdate = date('Y-m-d',strtotime($sdate));
            $static_edate = date('Y-m-d',strtotime($edate));
            $start_time = "$static_sdate 00:00:00";
            $end_time = "$static_edate 23:59:59";
        }else{
            $static_date = date('Y-m-d',strtotime('-1 day'));
            $start_time = "$static_date 00:00:00";
            $end_time = "$static_date 23:59:59";
        }

        $m_invitation = new \Admin\Model\Smallapp\InvitationModel();
        $m_invitation_user = new \Admin\Model\Smallapp\InvitationUserModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $where = array();
        $where['book_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res_datas = $m_invitation->getDataList('*',$where,'id desc');
        $datalist = array();
        foreach ($res_datas as $v){
            $invitation_id = $v['id'];
            $res_invitation = $m_invitation_user->getInfo(array('invitation_id'=>$invitation_id,'type'=>1));
            $dk_openid = $dk_mobile = $open_time = '';
            $is_sale = 0;
            if(!empty($res_invitation)){
                $dk_openid = $res_invitation['openid'];
                $open_time = $res_invitation['add_time'];
                $res_user = $m_user->getOne('id,unionId,mobile',array('openid'=>$dk_openid,'small_app_id'=>1),'id desc');

                if(!empty($res_user['mobile'])){
                    $dk_mobile = $res_user['mobile'];
                    $res_user = $m_user->getOne('id',array('mobile'=>$res_user['mobile'],'small_app_id'=>5),'id desc');
                    if(!empty($res_user['id'])){
                        $is_sale = 1;
                    }
                }
                if($is_sale==0){
                    if(!empty($res_user['unionId'])){
                        $res_user = $m_user->getOne('id',array('unionId'=>$res_user['unionId'],'small_app_id'=>5),'id desc');
                        if(!empty($res_user['id'])){
                            $is_sale = 1;
                        }
                    }
                }
            }
            if($is_sale==0){
                $info = array('openid'=>$v['openid'],'name'=>$v['name'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],
                    'room_name'=>$v['room_name'],'box_mac'=>$v['box_mac'],'book_time'=>$v['book_time'],'dk_openid'=>$dk_openid,
                    'dk_mobile'=>$dk_mobile,'open_time'=>$open_time,'add_time'=>$v['add_time']
                );
                $datalist[]=$info;
            }
        }
        $cell = array(
            array('openid','用户openid'),
            array('name','预订人'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','机顶盒MAC'),
            array('book_time','预定时间'),
            array('dk_openid','打开用户openid'),
            array('dk_mobile','打开用户手机号'),
            array('open_time','打开时间'),
            array('add_time','添加时间'),
        );
        $filename = '邀请函统计';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function sellmonthmgr(){
        $all_test_hotel = C('TEST_HOTEL');
        $sql = "select a.op_openid,count(a.id) as num,DATE_FORMAT(a.add_time,'%Y-%m') as sell_date,hotel.id as hotel_id,hotel.name as hotel_name,
            area.region_name as area_name from savor_finance_stock_record as a 
            left join savor_finance_stock stock on a.stock_id=stock.id left join savor_hotel hotel on stock.hotel_id=hotel.id
            left join savor_hotel_ext ext on hotel.id=ext.hotel_id left join savor_area_info area on area.id=hotel.area_id
            where a.type=7 and a.wo_reason_type=1 and a.wo_status=2 group by a.op_openid,sell_date";
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $res_data = $m_stock_record->query($sql);
        $datalist = array();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        foreach ($res_data as $v){
            $res_user = $m_user->getWhere('id,mobile,create_time as reg_time',array('openid'=>$v['op_openid'],'status'=>1),'id desc','0,1','');
            $info = array('reg_time'=>$res_user[0]['reg_time'],'openid'=>$v['op_openid'],'mobile'=>$res_user[0]['mobile'],
                'sell_date'=>$v['sell_date'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'area_name'=>$v['area_name'],'sell_num'=>$v['num']
            );
            if(!in_array($v['hotel_id'],$all_test_hotel)){
                $datalist[]=$info;
            }
        }
        $cell = array(
            array('reg_time','注册时间'),
            array('openid','openid'),
            array('mobile','手机号码'),
            array('sell_date','统计时段'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','酒楼城市'),
            array('sell_num','销售数量(瓶数)'),
        );
        $filename = '餐厅经理分月销售统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function hoteltaskdata(){
        $sdate = I('get.sdate');
        $edate = I('get.edate');

        $static_sdate = date('Y-m-d',strtotime($sdate));
        $static_edate = date('Y-m-d',strtotime($edate));

        $sql = "select a.area_name,a.maintainer,a.hotel_id,a.hotel_name,
sum(a.task_invitevip_release_num) as task_invitevip_release_num,sum(a.task_invitevip_get_num) as task_invitevip_get_num,sum(a.task_invitevip_sale_num) as task_invitevip_sale_num,
sum(a.task_demand_release_num) as task_demand_release_num,sum(a.task_demand_get_num) as task_demand_get_num,sum(a.task_demand_operate_num) as task_demand_operate_num,
sum(a.task_demand_finish_num) as task_demand_finish_num,sum(a.task_invitation_release_num) as task_invitation_release_num,sum(a.task_invitation_get_num) as task_invitation_get_num,
sum(a.task_invitation_operate_num) as task_invitation_operate_num,sum(a.task_invitation_finish_num) as task_invitation_finish_num
from savor_smallapp_static_hotelstaffdata as a left join savor_hotel as hotel on a.hotel_id=hotel.id 
where a.static_date>='$static_sdate' and a.static_date<='$static_edate' group by a.hotel_id order by a.area_id asc";
        $model = M();
        $datalist = $model->query($sql);
        $cell = array(
            array('area_name','地区'),
            array('maintainer','维护人'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('task_invitevip_release_num','任务券发布数'),
            array('task_invitevip_get_num','任务券领取数'),
            array('task_invitevip_sale_num','任务券售酒数'),
            array('task_demand_release_num','点播发布数'),
            array('task_demand_get_num','点播领取数'),
            array('task_demand_operate_num','点播应操作数'),
            array('task_demand_finish_num','点播完成数'),
            array('task_invitation_release_num','邀请函发布数'),
            array('task_invitation_get_num','邀请函领取数'),
            array('task_invitation_operate_num','邀请函应操作数'),
            array('task_invitation_finish_num','邀请函完成数'),
        );
        $filename = '餐厅任务完成情况统计';
        $this->exportToExcel($cell,$datalist,$filename,1);

    }

    public function sellwinestat(){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');
        $res_stock_hotel = $redis->get($cache_key);
        $all_stock_hotel = json_decode($res_stock_hotel,true);
        $model = M();
        $sql_goods = "select goods.id,goods.name as goods_name,brand.name as brand_name from savor_finance_goods as goods left join savor_finance_brand as brand on goods.brand_id=brand.id
            where goods.id!=15 order by goods.brand_id asc ";
        $res_goods = $model->query($sql_goods);
        $datalist = array();
        foreach ($res_goods as $v){
            $goods_id = $v['id'];
            $goods_name = $v['goods_name'];
            $brand_name = $v['brand_name'];

            $sql_rk_date = "select a.id,a.stock_id,stock.io_date from savor_finance_stock_record as a left join savor_finance_stock stock on a.stock_id=stock.id where a.goods_id={$goods_id} and a.type=1 and a.dstatus=1 order by a.id asc limit 1 ";
            $res_rk_date = $model->query($sql_rk_date);
            $rk_date = $res_rk_date[0]['io_date'];

            $sql_purchase = "select sum(total_amount) as total_num from savor_finance_stock_record where goods_id={$goods_id} and type=1 and dstatus=1 order by id desc";
            $res_purchase = $model->query($sql_purchase);
            $purchase_num = intval($res_purchase[0]['total_num']);

            $sql_hotel = "select COUNT(DISTINCT stock.hotel_id) as hotel_num,count(a.id) as sell_num from savor_finance_stock_record as a 
            left join savor_finance_stock stock on a.stock_id=stock.id where a.goods_id={$goods_id} and a.type=7 and a.wo_reason_type=1 and a.wo_status=2 order by a.id desc";
            $res_hotel = $model->query($sql_hotel);
            $hotel_num = intval($res_hotel[0]['hotel_num']);
            $sell_num = intval($res_hotel[0]['sell_num']);

            $out_sql = "select sum(total_amount) as total_num from savor_finance_stock_record where goods_id={$goods_id} and type=2 and dstatus=1 order by id desc";
            $res_out = $model->query($out_sql);
            $out_num = 0;
            if(!empty($res_out[0]['total_num'])){
                $out_num = abs($res_out[0]['total_num']);
            }
            $wo_sql = "select sum(total_amount) as total_num from savor_finance_stock_record where goods_id={$goods_id} and type=7 and wo_status in (1,2,4) and dstatus=1 order by id desc";
            $res_wo = $model->query($wo_sql);
            $wo_num = 0;
            if(!empty($res_wo[0]['total_num'])){
                $wo_num = $res_wo[0]['total_num'];
            }
            $report_sql = "select sum(total_amount) as total_num from savor_finance_stock_record where goods_id={$goods_id} and type=6 and status in (1,2) and dstatus=1 order by id desc";
            $res_report = $model->query($report_sql);
            $report_num = 0;
            if(!empty($res_report[0]['total_num'])){
                $report_num = $res_report[0]['total_num'];
            }
            $hotel_stock_num = $out_num+$wo_num+$report_num;

            $stock_sql = "select sum(total_amount) as total_num from savor_finance_stock_record where goods_id={$goods_id} and type in(1,2) and dstatus=1 order by id desc";
            $res_stock = $model->query($stock_sql);
            $stock_num = intval($res_stock[0]['total_num']);

            $now_sell_hotel_num = 0;
            foreach ($all_stock_hotel as $sk=>$sv){
                if(in_array($goods_id,$sv['goods_ids'])){
                    $now_sell_hotel_num++;
                }
            }
            $datalist[]=array('brand'=>$brand_name,'sku'=>$goods_name,'rk_date'=>$rk_date,'purchase_num'=>$purchase_num,
                'now_sell_hotel_num'=>$now_sell_hotel_num,'hotel_num'=>$hotel_num,'sell_num'=>$sell_num,
                'hotel_stock_num'=>$hotel_stock_num,'stock_num'=>$stock_num);
        }
        $cell = array(
            array('brand','品牌'),
            array('sku','SKU'),
            array('rk_date','首批入库时间'),
            array('purchase_num','总采购量'),
            array('now_sell_hotel_num','在销售餐厅数量'),
            array('hotel_num','已经有动销餐厅数量'),
            array('sell_num','已经核销数量'),
            array('hotel_stock_num','酒楼库存'),
            array('stock_num','公司库存'),
        );
        $filename = '小热点白酒销售统计';
        $this->exportToExcel($cell,$datalist,$filename,1);


    }
}