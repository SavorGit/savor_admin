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
        $now_time = date('Y-m-d H:i:s');
        echo "cooperatehotel start:$now_time \r\n";

        $start_date = date('Y-m-d',strtotime('-7 day'));
        $end_date = date('Y-m-d',strtotime('-1 day'));
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";

        $sql ="select a.id as hotel_id,a.name as hotel_name,a.addr,area.region_name as area_name,a.county_id,a.business_circle_id,county.region_name as country_name,circle.name as circle_name,
            ext.signer_id,ext.residenter_id,signer.remark as signer_name,residenter.remark as residenter_name,ext.is_salehotel,
            ext.avg_expense,a.contractor,a.mobile,a.tel,ext.department_name,ext.team_name
            from savor_hotel as a left join savor_hotel_ext as ext on a.id=ext.hotel_id 
            left join savor_area_info as area on a.area_id=area.id
            left join savor_area_info as county on a.county_id = county.id 
            left join savor_business_circle as circle on a.business_circle_id = circle.id 
            left join savor_sysuser as signer on ext.signer_id=signer.id
            left join savor_sysuser as residenter on ext.residenter_id=residenter.id
            where a.state=1 and a.flag=0 and a.htype=10 and a.id not in (7,482,504,791,508,844,845,597,201,493,883,53,598,1366,1337,925)
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
        a.goods_id,dg.name as goods_name,a.hotel_price,a.update_time,dg.price';
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
        $mail->AddAttachment($now_file_path); // 添加附件
        if ($mail->Send()) {
            echo date('Y-m-d') . '邮件发送成功'."\n";
        } else {
            echo date('Y-m-d') . '邮件发送失败'."\n";
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();

        $department_datas = array();
        foreach($datalist as $v){
            if(!empty($v['department_name'])){
                $department_datas[$v['department_name']][]=$v;
            }
        }
        $email_map = array('北京一部'=>'li.cong@littlehotspot.com','北京二部'=>'sun.zijia@littlehotspot.com',
            '广州一部'=>'wu.lin@littlehotspot.com','广州二部'=>'xie.binglei@littlehotspot.com',
            '上海一部'=>'cao.jie@littlehotspot.com','佛山一部'=>'xiao.lei@littlehotspot.com'
        );
        foreach ($department_datas as $k=>$v){
            if(isset($email_map[$k])){
                $email = $email_map[$k];
            }else{
                continue;
            }

            $now_filename = $k.$filename;
            $file_path = $this->exportToExcel($cell,$datalist,$now_filename,2);
            $now_file_path = SITE_TP_PATH .$file_path;
            $now_body = $k.$body;

            $mail->Body = $now_body;
            $mail->AddAddress($email);
            $mail->AddAttachment($now_file_path); // 添加附件
            if ($mail->Send()) {
                echo date('Y-m-d') . '邮件发送成功'."\n";
            } else {
                echo date('Y-m-d') . '邮件发送失败'."\n";
            }
            $mail->ClearAddresses();
            $mail->ClearAttachments();
        }


        $now_time = date('Y-m-d H:i:s');
        echo "abnormalpricehotels end:$now_time \r\n";
    }
    
}