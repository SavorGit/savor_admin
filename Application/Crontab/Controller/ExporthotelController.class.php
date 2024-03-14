<?php
namespace Crontab\Controller;
use Dataexport\Controller\BaseController;
use \Common\Lib\MailAuto;
class ExporthotelController extends BaseController{
    public function exportHotelInfos(){
        //$area_id = I('get.id',0,'intval');
        $sql = 'select id,region_name from savor_area_info where is_in_hotel = 1 and id !=246';
        $ret = M()->query($sql);
        $filename = '酒楼资源总表';
        $file_path_arr = [];
        foreach($ret as $key=>$v){
            $filename = '酒楼资源总表';
            $sql = "select a.state, a.id as hotel_id,a.name as hotel_name,area.region_name as area_name,
            circle.name circle_name,
            user1.remark as signer_name,
            user2.remark as maintainer_name,
            case a.htype
            when 10 then '是'
            when 20 then '否'
            END AS is_cooperation,
            case circle.trade_area_type
            when 2 then '否'
            when 1 then '是'
			ELSE '否'
            END AS trade_area_type
            from savor_hotel as a
            left join savor_hotel_ext as ext on a.id=ext.hotel_id
            left join savor_area_info as area on a.area_id=area.id
            
            left join savor_sysuser user1 on ext.signer_id = user1.id
            left join savor_sysuser user2 on ext.maintainer_id = user2.id
            left join savor_business_circle as circle on a.business_circle_id = circle.id
            where 1 and a.state in(1,4) and a.flag=0 and a.area_id= ".$v['id'];
            
            $datalist  = M()->query($sql);
            foreach($datalist as $kk=>$vv){
                
                if($vv['state']==1){
                    $sql ="select box.id from savor_box box
                   left join savor_room room on box.room_id=room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                        
                        
                where hotel.id=".$vv['hotel_id'].' and box.flag=0 and box.state=1';
                    
                    $rts = M()->query($sql);
                    if(empty($rts)){
                        $datalist[$kk]['have_box'] = '无';
                    }else {
                        $datalist[$kk]['have_box'] = '有';
                    }
                }else if($vv['state']==4){
                    $datalist[$kk]['have_box'] = '无';
                }
                
            }
            $cell = array(
                array('hotel_id','酒楼ID'),
                array('hotel_name','酒楼名称'),
                array('area_name','城市'),
                array('signer_name','签约人'),
                array('maintainer_name','维护人'),
                
                array('circle_name','商圈'),
                array('trade_area_type','是否核心商圈'),
                array('is_cooperation','是否签约'),
                array('have_box','是否有屏幕')
            );

            $filename .= '-'.$v['region_name'];
            $file_path = $this->exportToExcel($cell,$datalist,$filename,2);
            $file_path_arr[$v['id']] = SITE_TP_PATH .$file_path;
            
        }
        $title = '酒楼资源总表-' . date('Y-m-d');
        $body = '导出酒楼资源总表，详情见附件';
        $mail_config = C('SEND_MAIL_CONF');
        $mail_config = $mail_config['littlehotspot'];
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->IsSMTP();
        $mail->Host = $mail_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config['username'];
        $mail->Password = $mail_config['password'];
        $mail->Port = 465;
        $mail->From = $mail_config['username'];
        $mail->FromName = $title;
        $mail->IsHTML(true);
        
        $mail->Subject = $title;
        $mail->Body = $body;
		//北京
		$mail->AddAddress("liu.jingli@littlehotspot.com");
		$mail->AddAttachment($file_path_arr[1]); // 添加附件
		if ($mail->Send()) {
		    echo date('Y-m-d') . '北京发送成功'."\n";
		} else {
		    echo date('Y-m-d') . '北京发送失败'."\n";
		}
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		
		//上海
        $mail->AddAddress("cao.jie@littlehotspot.com");
        $mail->AddAttachment($file_path_arr[9]); // 添加附件
        if ($mail->Send()) {
            echo date('Y-m-d') . '上海发送成功'."\n";
        } else {
            echo date('Y-m-d') . '上海发送失败'."\n";
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();
        
		//广州
        $mail->AddAddress("wu.lin@littlehotspot.com");
        $mail->AddAddress("mi.le@littlehotspot.com");
		$mail->AddAttachment($file_path_arr[236]); // 添加附件
		if ($mail->Send()) {
		    echo date('Y-m-d') . '广州发送成功'."\n";
		} else {
		    echo date('Y-m-d') . '广州发送失败'."\n";
		}
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		
		//佛山
		$mail->AddAddress("li.cong@littlehotspot.com");
		$mail->AddAttachment($file_path_arr[248]); // 添加附件
		if ($mail->Send()) {
		    echo date('Y-m-d') . '佛山发送成功'."\n";
		} else {
		    echo date('Y-m-d') . '佛山发送失败'."\n";
		}
		
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		
		$mail->AddAddress("alex.liu@littlehotspot.com");
		$mail->AddAddress("xie.yunhang@littlehotspot.com");
		$mail->AddAddress("hu.shunhua@littlehotspot.com");
		$mail->AddAddress("jiang.gongjing@littlehotspot.com");
		$mail->AddAddress("zheng.wei@littlehotspot.com");
		$mail->AddAddress("zhang.yingtao@littlehotspot.com");
        foreach($file_path_arr as $ks=>$vs){
            $mail->AddAttachment($vs); // 添加附件
        }
        if ($mail->Send()) {
            echo date('Y-m-d') . '全国发送成功'."\n";
        } else {
            echo date('Y-m-d') . '全国发送失败'."\n";
        }
    }

    public function cooperatehotel(){
        echo '暂停使用';
        exit;
        $now_time = date('Y-m-d H:i:s');
        echo "cooperatehotel start:$now_time \r\n";

        $start_date = date('Y-m-d',strtotime('-7 day'));
        $end_date = date('Y-m-d',strtotime('-1 day'));
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";

        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $sql ="select a.id as hotel_id,a.name as hotel_name,a.addr,area.region_name as area_name,a.county_id,a.business_circle_id,county.region_name as country_name,circle.name as circle_name,
            ext.signer_id,ext.residenter_id,signer.remark as signer_name,residenter.remark as residenter_name,ext.is_salehotel,
            ext.avg_expense,a.contractor,a.mobile,a.tel,ext.department_name,ext.team_name
            from savor_hotel as a left join savor_hotel_ext as ext on a.id=ext.hotel_id 
            left join savor_area_info as area on a.area_id=area.id
            left join savor_area_info as county on a.county_id = county.id 
            left join savor_business_circle as circle on a.business_circle_id = circle.id 
            left join savor_sysuser as signer on ext.signer_id=signer.id
            left join savor_sysuser as residenter on ext.residenter_id=residenter.id
            where a.state=1 and a.flag=0 and a.htype=10 and a.id not in ($test_hotel_ids)
            order by a.area_id asc";
        $result = M()->query($sql);
        $m_room = new \Admin\Model\RoomModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_salerecord = new \Admin\Model\Crm\SalerecordModel();
        $m_opsstaff = new \Admin\Model\OpsstaffModel();
        $m_contract = new \Admin\Model\FinanceContractHotelModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');
        $datalist = array();
        foreach ($result as $v){
            $hotel_id = $v['hotel_id'];
            $circle_name = !empty($v['circle_name'])?$v['circle_name']:'';
            $res_room = $m_room->getRoomByCondition('count(room.id) as num',array('hotel.id'=>$v['hotel_id'],'room.state'=>1,'room.flag'=>0));
            $room_num = intval($res_room[0]['num']);

            $res_box = $m_box->getBoxByCondition('count(box.id) as num',array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0));
            $all_box_num = intval($res_box[0]['num']);

            $res_box = $m_box->getBoxByCondition('count(box.id) as num',array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0,'box.box_type'=>7));
            $tv_num = intval($res_box[0]['num']);
            $box_num = $all_box_num-$tv_num;

            $salewhere = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['record.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $sale_num = intval($res_stock_record[0]['num']);

            $visitwhere = array('signin_hotel_id'=>$v['hotel_id'],'type'=>1,'status'=>2);
            $visitwhere['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_visit = $m_salerecord->getDataList('count(id) as num',$visitwhere);
            $visit_num = intval($res_visit[0]['num']);

            $visitwhere['visit_purpose'] = array('like',"%,182,%");//驻店
            $visit_field = 'count(id) as num,sum(UNIX_TIMESTAMP(signout_time)-UNIX_TIMESTAMP(signin_time)) as vtime';
            $res_zdvisit = $m_salerecord->getDataList($visit_field,$visitwhere);
            $zdvisit_num = intval($res_zdvisit[0]['num']);
            $zdvisit_time = round($res_zdvisit[0]['vtime']/3600,2);

            $visitwhere['visit_purpose'] = array('like',"%,183,%");//巡店
            $res_xdvisit = $m_salerecord->getDataList($visit_field,$visitwhere);
            $xdvisit_num = intval($res_xdvisit[0]['num']);
            $xdvisit_time = round($res_xdvisit[0]['vtime']/3600,2);

            $res_merchant = $m_merchant->getMerchants('a.id,a.is_shareprofit',array('a.hotel_id'=>$hotel_id,'a.status'=>1),'');
            $is_shareprofit_str = '';
            if(!empty($res_merchant[0]['id'])){
                $is_shareprofit = $res_merchant[0]['is_shareprofit'];
                if($is_shareprofit==1){
                    $is_shareprofit_str = '是';
                }else{
                    $is_shareprofit_str = '否';
                }
            }
            $is_salehotel_str = $v['is_salehotel']==1?'是':'否';
            $residenter_mobile='';
            if($v['residenter_id']){
                $res_staff = $m_opsstaff->getInfo(array('sysuser_id'=>$v['residenter_id']));
                $residenter_mobile = $res_staff['mobile'];
            }
            $mobile_arr = array();
            if(!empty($v['mobile']))    $mobile_arr[]=$v['mobile'];
            if(!empty($v['tel']))       $mobile_arr[]=$v['tel'];
            $hotel_mobile = !empty($mobile_arr)?join('/',$mobile_arr):'';

            $cwhere = array('a.hotel_id'=>$hotel_id,'contract.type'=>20,'contract.ctype'=>21);
            $res_contract = $m_contract->getContract('contract.company_name',$cwhere,'a.id desc');
            $company_name = '';
            if(!empty($res_contract[0]['company_name'])){
                $company_name = $res_contract[0]['company_name'];
            }

            $sku_num = 0;
            $stock_num = 0;
            $res_cache_stock = $redis->get($cache_key.":$hotel_id");
            if(!empty($res_cache_stock)){
                $cache_stock = json_decode($res_cache_stock,true);
                $sku_num = count($cache_stock['goods_ids']);
                if($sku_num>0){
                    foreach ($cache_stock['goods_list'] as $gv){
                        $stock_num+=$gv['stock_num'];
                    }
                }
            }
            echo "hotel_id:$hotel_id ok \r\n";

            $datalist[]=array('hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],'area_name'=>$v['area_name'],'country_name'=>$v['country_name'],
                'circle_name'=>$circle_name,'room_num'=>$room_num,'box_num'=>$box_num,'sale_num'=>$sale_num,'visit_num'=>$visit_num,
                'signer_name'=>$v['signer_name'],'residenter_name'=>$v['residenter_name'],'sku_num'=>$sku_num,'stock_num'=>$stock_num,
                'is_shareprofit_str'=>$is_shareprofit_str,'is_salehotel_str'=>$is_salehotel_str,'addr'=>$v['addr'],'residenter_mobile'=>$residenter_mobile,
                'tv_num'=>$tv_num,'contractor'=>$v['contractor'],'avg_expense'=>$v['avg_expense'],'mobile'=>$hotel_mobile,'company_name'=>$company_name,
                'zdvisit_num'=>$zdvisit_num,'zdvisit_time'=>$zdvisit_time,'xdvisit_num'=>$xdvisit_num,'xdvisit_time'=>$xdvisit_time
            );
        }

        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('department_name','部门'),
            array('team_name','小组'),
            array('area_name','城市'),
            array('country_name','区域'),
            array('circle_name','商圈'),
            array('room_num','包间数'),
            array('box_num','机顶盒数'),
            array('tv_num','电视机数'),
            array('sale_num','上周销量'),
            array('visit_num','上周拜访次数'),
            array('zdvisit_num','上周驻店次数'),
            array('zdvisit_time','上周驻店总时长(小时)'),
            array('xdvisit_num','上周巡店次数'),
            array('xdvisit_time','上周巡店总时长(小时)'),
            array('signer_name','签约人'),
            array('residenter_name','驻店人'),
            array('residenter_mobile','驻店人联系电话'),
            array('sku_num','sku数'),
            array('stock_num','库存数'),
            array('is_shareprofit_str','是否分润'),
            array('is_salehotel_str','是否是售酒餐厅'),
            array('addr','地址'),
            array('company_name','签约主体名称'),
            array('avg_expense','人均消费'),
            array('contractor','酒楼联系人'),
            array('mobile','酒楼联系电话'),
        );

        $filename = '合作酒楼数据表';
        $file_path = $this->exportToExcel($cell,$datalist,$filename,2);
        $now_file_path = SITE_TP_PATH .$file_path;

        $title = $filename.'-' . $start_date.'至'.$end_date;
        $body = '导出合作酒楼数据表，详情见附件';
        $mail_config = C('SEND_MAIL_CONF');
        $mail_config = $mail_config['littlehotspot'];
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->IsSMTP();
        $mail->Host = $mail_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config['username'];
        $mail->Password = $mail_config['password'];
        $mail->Port = 465;
        $mail->From = $mail_config['username'];
        $mail->FromName = $title;
        $mail->IsHTML(true);

        $mail->Subject = $title;
        $mail->Body = $body;

        $mail->AddAddress("alex.liu@littlehotspot.com");
        $mail->AddAddress("ma.feng@littlehotspot.com");
        $mail->AddAddress("hu.shunhua@littlehotspot.com");
        $mail->AddAddress("liu.bin@littlehotspot.com");

        $mail->AddAttachment($now_file_path); // 添加附件
        if ($mail->Send()) {
            echo date('Y-m-d') . '邮件发送成功'."\n";
        } else {
            echo date('Y-m-d') . '邮件发送失败'."\n";
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $now_time = date('Y-m-d H:i:s');
        echo "cooperatehotel end:$now_time \r\n";
    }


    public function abnormalpricehotels(){
        $now_time = date('Y-m-d H:i:s');
        echo "abnormalpricehotels start:$now_time \r\n";

        $start_date = date('Y-m-d',strtotime('-7 day'));
        $end_date = date('Y-m-d',strtotime('-1 day'));
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";

        $where = array('dg.status'=>1,'dg.type'=>43,'a.hotel_price'=>array('gt',0),
            'a.update_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        $where['h.id'] = array('not in',C('TEST_HOTEL'));
        $fields = 'a.hotel_id,h.name as hotel_name,area.region_name as area_name,ext.department_name,ext.team_name,
        ext.bdm_name,a.goods_id,dg.name as goods_name,a.hotel_price,a.update_time,dg.price';
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $datalist = $m_hotelgoods->getHotelgoodsList($fields,$where,'a.hotel_id desc');

        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('department_name','部门'),
            array('team_name','小组'),
            array('area_name','城市'),
            array('goods_id','酒水ID'),
            array('goods_name','酒水名称'),
            array('hotel_price','酒水价格'),
            array('price','公司售价'),
        );

        $filename = '未按照公司定价售酒酒楼明细';
        $file_path = $this->exportToExcel($cell,$datalist,$filename,2);
        $now_file_path = SITE_TP_PATH .$file_path;

        $title = $filename.'-' . $start_date.'至'.$end_date;
        $body = '导出未按照公司定价售酒酒楼明细，详情见附件';
        $mail_config = C('SEND_MAIL_CONF');
        $mail_config = $mail_config['littlehotspot'];
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->IsSMTP();
        $mail->Host = $mail_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config['username'];
        $mail->Password = $mail_config['password'];
        $mail->Port = 465;
        $mail->From = $mail_config['username'];
        $mail->FromName = $title;
        $mail->IsHTML(true);

        $mail->Subject = $title;
        $mail->Body = $body;
        $mail->AddAddress("liu.bin@littlehotspot.com");
        $mail->AddAddress("jiang.gongjing@littlehotspot.com");
        $mail->AddAttachment($now_file_path); // 添加附件
        if($mail->Send()) {
            echo "email: liu.bin@littlehotspot.com send ok \r\n";
        }else{
            echo "email: liu.bin@littlehotspot.com send ok \r\n";
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $bdm_datas = array();
        foreach($datalist as $v){
            if(!empty($v['bdm_name'])){
                $bdm_datas[$v['bdm_name']][]=$v;
            }
        }
        $email_map = C('BDM_NAME');
        foreach ($bdm_datas as $k=>$v){
            if(isset($email_map[$k])){
                $email = $email_map[$k];
            }else{
                continue;
            }

            $now_filename = $k.$filename;
            $datalist = $v;
            $file_path = $this->exportToExcel($cell,$datalist,$now_filename,2);
            $now_file_path = SITE_TP_PATH .$file_path;
            $now_body = $k.$body;

            $mail->Body = $now_body;
            $mail->AddAddress($email);
            $mail->AddAttachment($now_file_path); // 添加附件
            if ($mail->Send()) {
                echo "email: $email,name:$k send ok \r\n";
            } else {
                echo "email: $email,name:$k send fail \r\n";
            }
            $mail->ClearAddresses();
            $mail->ClearAttachments();
        }
        $now_time = date('Y-m-d H:i:s');
        echo "abnormalpricehotels end:$now_time \r\n";
    }

    public function sellhoteldata(){
        $now_time = date('Y-m-d H:i:s');
        echo "sellhoteldata start:$now_time \r\n";

        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $start_time = "$startOfMonth 00:00:00";
        $end_time = "$endOfMonth 23:59:59";
        $month_1_stime = date('Y-m-01 00:00:00',strtotime('-1 month'));
        $month_1_etime = date('Y-m-t 23:59:59',strtotime('-1 month'));
        $month_2_stime = date('Y-m-01 00:00:00',strtotime('-2 month'));
        $month_2_etime = date('Y-m-t 23:59:59',strtotime('-2 month'));

        $weeks = getWeeksMonth(date('Y'),date('m'));

        $m_room = new \Admin\Model\RoomModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_salerecord = new \Admin\Model\Crm\SalerecordModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');

        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $sql ="select a.id as hotel_id,a.name as hotel_name,area.region_name as area_name,a.business_circle_id,circle.name as circle_name,
            ext.residenter_id,residenter.remark as residenter_name,ext.department_name,ext.team_name,ext.bdm_name
            from savor_hotel as a left join savor_hotel_ext as ext on a.id=ext.hotel_id 
            left join savor_area_info as area on a.area_id=area.id
            left join savor_business_circle as circle on a.business_circle_id = circle.id
            left join savor_sysuser as residenter on ext.residenter_id=residenter.id
            where a.state=1 and a.flag=0 and ext.is_salehotel=1 and a.id not in ($test_hotel_ids) 
            order by a.area_id asc";
        $result = M()->query($sql);
        $datalist = array();
        foreach ($result as $v){
            $hotel_id = $v['hotel_id'];
            $res_room = $m_room->getRoomByCondition('count(room.id) as num',array('hotel.id'=>$v['hotel_id'],'room.state'=>1,'room.flag'=>0));
            $v['room_num'] = intval($res_room[0]['num']);

            $res_merchant = $m_merchant->getMerchants('a.id,a.name,a.mobile,a.is_shareprofit',array('a.hotel_id'=>$hotel_id,'a.status'=>1),'');
            $is_shareprofit_str = '';
            $dz_name = $dz_mobile = '';
            $sale_people_num = 0;
            if(!empty($res_merchant[0]['id'])){
                $dz_name = $res_merchant[0]['name'];
                $dz_mobile = $res_merchant[0]['mobile'];
                $is_shareprofit = $res_merchant[0]['is_shareprofit'];
                if($is_shareprofit==1){
                    $is_shareprofit_str = '是';
                }else{
                    $is_shareprofit_str = '否';
                }
                $res_staff_num = $m_staff->getRow('count(*) as num',array('merchant_id'=>$res_merchant[0]['id'],'status'=>1));
                $sale_people_num = intval($res_staff_num['num']);
            }
            $v['dz_name'] = $dz_name;
            $v['dz_mobile'] = $dz_mobile;
            $v['is_shareprofit_str'] = $is_shareprofit_str;
            $v['sale_people_num'] = $sale_people_num;

            $salewhere = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['record.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $v['sale_num'] = intval($res_stock_record[0]['num']);
            $salewhere['record.recycle_status'] = 2;
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $v['open_sale_num'] = intval($res_stock_record[0]['num']);

            $visitwhere = array('signin_hotel_id'=>$hotel_id,'type'=>1,'status'=>2);
            $visitwhere['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $visitwhere['visit_purpose'] = array('like',"%,182,%");//驻店
            $visit_field = 'count(id) as num,sum(UNIX_TIMESTAMP(signout_time)-UNIX_TIMESTAMP(signin_time)) as vtime';
            $res_zdvisit = $m_salerecord->getDataList($visit_field,$visitwhere);
            $v['zdvisit_num'] = intval($res_zdvisit[0]['num']);

            $visitwhere['visit_purpose'] = array('like',"%,183,%");//巡店
            $res_xdvisit = $m_salerecord->getDataList($visit_field,$visitwhere);
            $v['xdvisit_num'] = intval($res_xdvisit[0]['num']);

            $salewhere = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['record.add_time'] = array(array('egt',$month_2_stime),array('elt',$month_2_etime));
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $v['month_2_sale_num'] = intval($res_stock_record[0]['num']);
            $salewhere['record.add_time'] = array(array('egt',$month_1_stime),array('elt',$month_1_etime));
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $v['month_1_sale_num'] = intval($res_stock_record[0]['num']);

            foreach ($weeks as $wk=>$wday){
                $w_stime = "{$wday['start']} 00:00:00";
                $w_etime = "{$wday['end']} 23:59:59";
                $salewhere['record.add_time'] = array(array('egt',$w_stime),array('elt',$w_etime));
                $res_week_stock_record = $m_sale->alias('a')
                    ->field('count(a.id) as num')
                    ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                    ->where($salewhere)
                    ->select();
                $v["sale_week{$wk}_num"] = intval($res_week_stock_record[0]['num']);
            }
            $hgwhere = array('dg.status'=>1,'dg.type'=>43,'a.hotel_price'=>array('gt',0),'h.id'=>$hotel_id);
            $res_hgoods = $m_hotelgoods->getHotelgoodsList('count(a.id) as num',$hgwhere,'a.id desc');
            $v['is_parity'] = $res_hgoods[0]['num']>0?'否':'是';

            $qksale_where = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $qksale_where['a.ptype'] = array('in','0,2');
            $res_sale_qk = $m_sale->alias('a')
                ->field('sum(a.settlement_price-a.pay_money) as money')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($qksale_where)
                ->select();
            $v['qk_money'] = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;

            $qksale_where['a.is_expire'] = 1;
            $res_sale_qk = $m_sale->alias('a')
                ->field('sum(a.settlement_price-a.pay_money) as money')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($qksale_where)
                ->select();
            $v['cqqk_money'] = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;

            $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
            $staff_where = array('a.hotel_id'=>$hotel_id,'a.static_date'=>array(array('egt',$startOfMonth),array('elt',$endOfMonth)));
            $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
            $v['task_demand_finish_rate'] = sprintf("%.2f",$res_task_data[0]['task_demand_finish_num']/$res_task_data[0]['task_demand_operate_num']);
            $v['task_invitation_finish_rate'] = sprintf("%.2f",$res_task_data[0]['task_invitation_finish_num']/$res_task_data[0]['task_invitation_operate_num']);

            $sku_num = 0;
            $stock_num = 0;
            $res_cache_stock = $redis->get($cache_key.":$hotel_id");
            if(!empty($res_cache_stock)){
                $cache_stock = json_decode($res_cache_stock,true);
                $sku_num = count($cache_stock['goods_ids']);
                foreach ($cache_stock['goods_list'] as $csg){
                    $stock_num+=$csg['stock_num'];
                }
            }
            $v['sku_num'] = $sku_num;
            $v['stock_num'] = $stock_num;
            $datalist[]=$v;
        }

        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('circle_name','商圈'),
            array('room_num','包间数'),
            array('dz_name','店长'),
            array('dz_mobile','店长手机'),
            array('sale_num','本月销量'),
            array('open_sale_num','本月开瓶核销量'),
            array('zdvisit_num','驻店次数'),
            array('xdvisit_num','巡店次数'),
            array('month_2_sale_num','前月销量'),
            array('month_1_sale_num','上月销量'),
        );
        foreach ($weeks as $wk=>$wday){
            $cell[]=array("sale_week{$wk}_num","{$wk}周销量");
        }
        $cell[]=array('department_name','部门');
        $cell[]=array('bdm_name','BDM');
        $cell[]=array('team_name','BD');
        $cell[]=array('residenter_name','AC');
        $cell[]=array('is_parity','是否平价');
        $cell[]=array('is_shareprofit_str','是否分润');
        $cell[]=array('qk_money','总欠款');
        $cell[]=array('cqqk_money','超期欠款');
        $cell[]=array('sale_people_num','销售端人数');
        $cell[]=array('sku_num','sku数');
        $cell[]=array('stock_num','库存数');
        $cell[]=array('task_demand_finish_rate','点播任务完成率');
        $cell[]=array('task_invitation_finish_rate','邀请函任务完成率');


        $filename = '渠道人员工作表';
        $file_path = $this->exportToExcel($cell,$datalist,$filename,2);
        $now_file_path = SITE_TP_PATH .$file_path;
        $now_month = date('Y-m');
        $title = $now_month.'月份'.$filename;
        $body = '导出'.$now_month.'月份渠道人员工作表明细，详情见附件';
        $mail_config = C('SEND_MAIL_CONF');
        $mail_config = $mail_config['littlehotspot'];
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->IsSMTP();
        $mail->Host = $mail_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config['username'];
        $mail->Password = $mail_config['password'];
        $mail->Port = 465;
        $mail->From = $mail_config['username'];
        $mail->FromName = $title;
        $mail->IsHTML(true);

        $mail->Subject = $title;
        $mail->Body = $body;
        $mail->AddAddress("liu.bin@littlehotspot.com");
        $mail->AddAddress("alex.liu@littlehotspot.com");
        $mail->AddAddress("xie.yunhang@littlehotspot.com");
        $mail->AddAddress("hu.shunhua@littlehotspot.com");
        $mail->AddAddress("jiang.gongjing@littlehotspot.com");
        $mail->AddAddress("ma.feng@littlehotspot.com");
        $mail->AddAddress("he.yongrui@littlehotspot.com");
        $mail->AddAddress("pang.mengying@littlehotspot.com");
        $mail->AddAddress("zhao.cuiyan@littlehotspot.com");
        $mail->AddAddress("zhang.lijuan@littlehotspot.com");
        $mail->AddAttachment($now_file_path); // 添加附件
        if ($mail->Send()) {
            echo "email: 10num send ok \r\n";
        } else {
            echo "email: 10num send fail \r\n";
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $now_time = date('Y-m-d H:i:s');
        echo "email: 9num send,$now_time \r\n";

        $bdm_datas = array();
        $bd_datas = array();
        foreach($datalist as $v){
            if(!empty($v['bdm_name'])){
                $bdm_datas[$v['bdm_name']][]=$v;
            }
            if(!empty($v['team_name'])){
                $bd_datas[$v['team_name']][]=$v;
            }
        }

        $now_time = date('Y-m-d H:i:s');
        echo "bdm: send,$now_time \r\n";

        $email_map = C('BDM_NAME');
        foreach ($bdm_datas as $k=>$v){
            if(isset($email_map[$k])){
                $email = $email_map[$k];
            }else{
                continue;
            }
            $now_filename = $k.$filename;
            $datalist = $v;
            $file_path = $this->exportToExcel($cell,$datalist,$now_filename,2);
            $now_file_path = SITE_TP_PATH .$file_path;
            $now_body = $k.$body;

            $mail->Body = $now_body;
            $mail->AddAddress($email);
            $mail->AddAttachment($now_file_path); // 添加附件
            if ($mail->Send()) {
                echo "email: $email,name:$k send ok \r\n";
            } else {
                echo "email: $email,name:$k send fail \r\n";
            }
            $mail->ClearAddresses();
            $mail->ClearAttachments();
        }

        $now_time = date('Y-m-d H:i:s');
        echo "bd(team_name): send,$now_time \r\n";

        $email_map = C('TEAM_NAME');
        foreach ($bd_datas as $k=>$v){
            if(isset($email_map[$k])){
                $email = $email_map[$k];
            }else{
                continue;
            }
            $now_filename = $k.$filename;
            $datalist = $v;
            $file_path = $this->exportToExcel($cell,$datalist,$now_filename,2);
            $now_file_path = SITE_TP_PATH .$file_path;
            $now_body = $k.$body;

            $mail->Body = $now_body;
            $mail->AddAddress($email);
            $mail->AddAttachment($now_file_path); // 添加附件
            if ($mail->Send()) {
                echo "email: $email,name:$k send ok \r\n";
            } else {
                echo "email: $email,name:$k send fail \r\n";
            }
            $mail->ClearAddresses();
            $mail->ClearAttachments();
        }

        $now_time = date('Y-m-d H:i:s');
        echo "sellhoteldata end:$now_time \r\n";
    }


    
}