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
        foreach ($datalist as $k=>$v){
            $hotel_id = $v['hotel_id'];
            $sql_hotel = "select maintainer from savor_smallapp_static_hotelstaffdata where static_date='$static_edate' and hotel_id={$hotel_id} ";
            $res_hotel = $model->query($sql_hotel);
            $datalist[$k]['maintainer'] = $res_hotel[0]['maintainer'];
        }
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

    public function sellwinehotelsaledata(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $where = array('a.is_salehotel'=>1,'hotel.state'=>1,'hotel.flag'=>0);
        $where['hotel.id'] = array('not in',C('TEST_HOTEL'));
        $fields = 'a.hotel_id,hotel.name as hotel_name,hotel.area_id,area.region_name as area_name,su.remark as maintainer,a.sale_start_date,a.sale_end_date';
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $result = $m_hotel_ext->getSellwineList($fields,$where,'hotel.pinyin asc');
        $datalist = array();
        $m_finance_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $in_hotel_dates = $m_finance_stock_record->getSellIndateHotels();
        $sell_hotel_dates = $m_finance_stock_record->getSellDateHotels();
        $sell_nums = $m_finance_stock_record->getHotelSellwineNums($start_time,$end_time);
        foreach ($result as $k=>$v){
            $in_hotel_date = '';
            if(isset($in_hotel_dates[$v['hotel_id']])){
                $in_hotel_date = $in_hotel_dates[$v['hotel_id']];
            }
            $sell_date = '';
            if(isset($sell_hotel_dates[$v['hotel_id']])){
                $sell_date = $sell_hotel_dates[$v['hotel_id']];
            }
            $sell_num = 0;
            if(isset($sell_nums[$v['hotel_id']])){
                $sell_num = $sell_nums[$v['hotel_id']];
            }
            if($v['sale_start_date']=='0000-00-00'){
                $v['sale_start_date'] = '';
            }
            if($v['sale_end_date']=='0000-00-00'){
                $v['sale_end_date'] = '';
            }
            $v['in_hotel_date'] = $in_hotel_date;
            $v['sell_date'] = $sell_date;
            $v['sell_num'] = $sell_num;
            $datalist[]=$v;
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('maintainer','维护人'),
            array('sale_start_date','开启售酒日期'),
            array('in_hotel_date','首次进店时间'),
            array('sell_date','首次销售时间'),
            array('sell_num','销量'),
            array('sale_end_date','撤店日期'),
        );
        $filename = '酒楼销售统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function hotelsaledata(){
        $hotel_id = I('hotel_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $m_hotel = new \Admin\Model\HotelModel();
        $res_hotel = $m_hotel->getOne($hotel_id);

        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $fileds = 'sum(a.total_amount) as total_amount';
        $where = array('stock.hotel_id'=>$hotel_id,'a.type'=>7,'a.wo_reason_type'=>1,'a.wo_status'=>array('in','1,2,4'),
            'a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        $res_worecord = $m_stock_record->getStockRecordList($fileds,$where,'a.id desc','','');
        $sale_num = abs(intval($res_worecord[0]['total_amount']));

        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK').":$hotel_id";
        $res_cache_stock = $redis->get($cache_key);
        $stock_num = 0;
        if(!empty($res_cache_stock)){
            $res_cache_stock = json_decode($res_cache_stock,true);
            foreach ($res_cache_stock['goods_list'] as $v){
                $stock_num+=$v['stock_num'];
            }
        }

        $m_integral_record = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $where = array('hotel_id'=>$hotel_id,'type'=>17,'add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        $res_integral = $m_integral_record->getRow('sum(integral) as total_integral',$where);
        $integral = intval($res_integral['total_integral']);

        $m_sale = new \Admin\Model\FinanceSaleModel();
        $sale_where = array('stock.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        $fileds = 'sum(a.settlement_price) as sale_money';
        $res_sale = $m_sale->getSaleStockRecordList($fileds,$sale_where,'','');
        $sale_money = abs(intval($res_sale[0]['sale_money']));

        $sale_where['a.ptype'] = array('in','0,2');
        $res_sale_qk = $m_sale->getSaleStockRecordList('a.id as sale_id,a.settlement_price,a.ptype,a.is_expire,a.add_time',$sale_where,'','');
        $qk_money = 0;
        $cqqk_money = 0;
        if(!empty($res_sale_qk)){
            $m_sale_payment_record = new \Admin\Model\FinanceSalePaymentRecordModel();
            foreach ($res_sale_qk as $v){
                if($v['ptype']==0){
                    $now_money = $v['settlement_price'];
                }else{
                    $res_had_pay = $m_sale_payment_record->getRow('sum(pay_money) as total_pay_money',array('sale_id'=>$v['sale_id']));
                    $had_pay_money = intval($res_had_pay['total_pay_money']);
                    $now_money = $v['settlement_price']-$had_pay_money;
                }
                $qk_money+=$now_money;
                if($v['is_expire']==1){
                    $cqqk_money+=$now_money;
                }
            }
        }
        $cqqk_money = abs($cqqk_money);
        $data = array('hotel_id'=>$hotel_id,'hotel_name'=>$res_hotel['name'],'sale_num'=>$sale_num,'sale_money'=>$sale_money,
            'stock_num'=>$stock_num,'integral'=>$integral,'qk_money'=>$qk_money,'cqqk_money'=>$cqqk_money);

        $data['company'] = '北京热点投屏科技有限公司';
        $start_time = date('Y.m.d',strtotime($start_time));
        $end_time = date('Y.m.d',strtotime($end_time));
        $data['hotel_name'] = "{$data['hotel_name']}（{$start_time}-{$end_time}）";
        $data['sale_num'] = $data['sale_num'].'瓶';
        $data['sale_money'] = $data['sale_money'].'元';
        $data['integral'] = $data['integral'].'积分';
        $data['stock_num'] = $data['stock_num'].'瓶';
        $data['qk_money'] = $data['qk_money'].'元';
        $data['cqqk_money'] = $data['cqqk_money'].'元';

        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(15);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getRowDimension('1')->setRowHeight(30);
        $objActSheet->getRowDimension('2')->setRowHeight(20);
        $objActSheet->getRowDimension('3')->setRowHeight(20);
        $objActSheet->getRowDimension('4')->setRowHeight(20);
        $objActSheet->getRowDimension('5')->setRowHeight(20);
        $objActSheet->getStyle('A')->getFont()->setSize(14);
        $objActSheet->getStyle('B')->getFont()->setSize(14);
        $objActSheet->getStyle('C')->getFont()->setSize(14);
        $objActSheet->getStyle('D')->getFont()->setSize(14);
        $objActSheet->mergeCells('A1:D1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$data['hotel_name']);
        $objActSheet->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
        $objActSheet->getStyle('A1')->getFont()->setSize(16);
        $objActSheet->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
        $objActSheet->getStyle('A1:D5')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objActSheet->setCellValue('A2', '销售瓶数')
            ->setCellValue('B2', $data['sale_num'])
            ->setCellValue('C2', '销售额')
            ->setCellValue('D2', $data['sale_money'])
            ->setCellValue('A3', '利润')
            ->setCellValue('B3', $data['integral'])
            ->setCellValue('C3', '当前库存瓶数')
            ->setCellValue('D3', $data['stock_num'])
            ->setCellValue('A4', '总欠款')
            ->setCellValue('B4', $data['qk_money'])
            ->setCellValue('C4', '超期欠款')
            ->setCellValue('D4', $data['cqqk_money']);

        $objActSheet->mergeCells('A5:D5');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5',$data['company']);
        $objActSheet->getStyle('A5')->getFont()->setSize(12);
        $objActSheet->getStyle('A5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $fileName = '酒楼销售统计单'.date('YmdHis');
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $fileName . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    //获取根据人均消费获取某个城市的
    
    //++ 是否售酒  是否有 正常机顶盒
    public function getDiffExpenceHotels(){
        $city_id = I('city_id',1);
        $below_expence = I('below_expence',120);
        /*$sql = "select a.id as hotel_id,a.name as hotel_name,a.area_id,area.region_name as area_name,
                a.county_id,a.business_circle_id,ext.dp_comment_num,ext.avg_expense ,
                count.region_name as count_name,circle.name circle_name,
                
                case ext.is_salehotel
				when 0 then '否'
				when 1 then '是'
				END AS is_salehotel
                from savor_hotel as a 
                left join savor_hotel_ext as ext on a.id=ext.hotel_id 
                left join savor_area_info as area on a.area_id=area.id
                left join savor_area_info as count on a.county_id = count.id
                left join savor_business_circle as circle on a.business_circle_id = circle.id
                where 1 and a.state=1 and a.flag=0 and a.area_id in ($city_id) and ext.avg_expense<".$below_expence;*/
        $sql ="select a.id as hotel_id,a.name as hotel_name,a.area_id,area.region_name as area_name, a.county_id,a.business_circle_id,ext.dp_comment_num,ext.avg_expense , count.region_name as count_name,circle.name circle_name, case ext.is_salehotel when 0 then '否' when 1 then '是' END AS is_salehotel 
               from savor_hotel as a 
               left join savor_hotel_ext as ext on a.id=ext.hotel_id 
               left join savor_area_info as area on a.area_id=area.id 
               left join savor_area_info as count on a.county_id = count.id 
               left join savor_business_circle as circle on a.business_circle_id = circle.id 
               where a.id in (
468,28,590,972,848,550,1507,1564,1007,548,1510,1365,668,572,462,156,49,476,1282,563,81,123,94,65,1253,79,1347,1223,1497,686,1766,1796,1723,1307,1805,1604,1352,1652,742,1200,1562,191,177,189,32,827,1731,186,185,187,524,188,1508,1338,163,1224,1654,842,678,666,95,34,1215,571,57,1248,1473,1247,1568,10,1721,1710,1734,167,36,1804,151,147,1437,1362,1732,1686,1375,1658,543,1021,210,113,1548,1764,1561,512,473,1795,26,1241,806,1348,1576,1243,1673,500,625,56,41,896,24,1807,135,1470,180,140,1360,1306,1646,1359,92,838,837,840,835,1231,546,1675,582,680,196,1267,17,1672,214,787,683,216,1491,84,215,1729,1341,786,1194,1722,1447,1216,1709,1015,802,679,12,90,1563,1244,1733,1573,85,238,886,1278,205,1222,1012,555,55,29,199,204,237,23,207,1006,91,825,243,1293,841,1001,431,464,11,88,1291,67,68,1295,1642,1433,1310,1102,129,169,1472,168,175,176,878,39,888,1263,763,98,61,1239,217,1314,105,138,1738,1300,1270,471,1346,1329,174,218,552,436,18,47,1296,833,222,31,212,38,48,980,1345,1676,884,995,52,181,1242,1238,1273,1315,223,232,940,1185,753,1339,1344,1237,1320,1353,1356,116,112,311,268,602,1268,770,1053,1292,921,1335,952,351,447,1274,294,950,994,1283,603,296,783,1308,779,295,781,260,264,1294,766,251,671,776,533,897,252,258,332,255,262,630,782,263,261,256,675,490,677,335,253,606,333,330,628,805,369,674,265,807,607,313,923,328,327,275,621,623,620,624,622,1285,367,1302,1261,1214,1405,1299,489,1201,1613,907,1288,1277,1231,1819,1416,354,312,1412,303,618,1392,871,631,895,376,894,1415,356,304,305,372,445,384,349,293,320,913,1071,284,315,283,599,629,289,853,877,1191,1399,944,342,608,302,1786,366,600,1376,1396,1279,1398,1394,1714,378,688,916,849,616,693,273,1397,1269,613,610,817,1395,1406,383,1438,619,615,612,1393,1051,420,941,1258,459,1176,816,964,847,943,421,423,424,874,912,1094,810,1087,1221,1022,1019,637,1039,1448,920,1124,1062,706,1054,1029,1075,1226,1333,1257,1262,1045,1091,632,711,1233,1023,1026,958,959,975,1208,1125,1109,1050,917,1106,718,716,714,910,713,719,717,1121,416,1177,1077,1225,1063,967,1209,1227,1041,1048,1210,1107,1097,1037,1076,963,934,922,1351,1331,1229,1435,1323,1218,1078,1115,529,1265,1047,1074,1119,636,1259,1099,430,392,639,863,428,968,872,955,404,412,388,1182,1101,1070,898,1276,1046,1187,1056,927,405,393,1286,664,1059,1123,391,936,663,931,1105,1058,1330,1197,1095,701,427,1065,721,1100,1096,1188,939,1088,415,1005,1068,1009,1110,1085,1450,1111,1199,1113,1649,1599,1632,1586,1382,1523,1456,1570,1518,1592,1575,898,1381,1608,1539,1492,1538,1477,1552,1478,1488,1531,1489,1535,1505,1466,1490,1631,1475,1551,1534,1462,1529,1605,1479,1557,1560,1556,1537,1468,1520,1528,1458,1626,1577,1374,1378,1384,1402,1566,1506,1423,1485,1515,1544,1541,1536,1371,1386,1524,1425,1500,1593,1567,1572,1487,1619,1380,1460,1559,1630,1628,1629,1480,1694,1621,1614,1542,1627,1600,1408,1606,1486,1525,1571,1367,1481,1493,1502,1588,1635,1591,1428,1579,1454,1565,1554,1712,1637,1418,1501,1521,1474,1483,1596,1633,1582,1590,1385,1498,1578,1549,1622,1503,1602,1512,1638,1581,1482,1532,1777,1514,1685,1372,1661,1504,1775,1597
) and  a.state=1 and a.flag=0 and a.area_id =".$city_id." and ext.avg_expense  <".$below_expence;
        //echo $sql;exit;
        $datalist  = M()->query($sql);
        foreach($datalist as $key=>$v){
            $sql ="select box.id from savor_box box
                   left join savor_room room on box.room_id=room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id


                    where hotel.id=".$v['hotel_id'].' and box.flag=0 and box.state=1';
            
            $rts = M()->query($sql);
            if(empty($rts)){
                $datalist[$key]['have_box'] = '无';
            }else {
                $datalist[$key]['have_box'] = '有';
            }
        }
        
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('count_name','区域'),
            array('circle_name','商圈'),
            array('dp_comment_num','评论数'),
            array('avg_expense','人均消费'),
            array('is_salehotel','是否售酒餐厅'),
            array('have_box','是否有机顶盒')
        );
        $filename = $datalist[0]['area_name'].'人均消费少于'.$below_expence.'酒楼数据';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}