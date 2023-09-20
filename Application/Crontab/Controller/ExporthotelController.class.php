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
                array('trade_area_type','是否聚焦商圈'),
                array('is_cooperation','是否签约'),
                array('have_box','是否有屏幕')
            );
            
            //$path .= 'mail.xls';
            $filename .= '-'.$v['region_name'];
            $file_path = $this->exportToExcel($cell,$datalist,$filename,2);
            $file_path_arr[$v['id']] = SITE_TP_PATH .$file_path;
            
        }
        //print_r($file_path_arr);exit;
        $mail_config = C('SEND_MAIL_CONF');
        $mail_config = $mail_config['littlehotspot'];
        
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
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
    
}