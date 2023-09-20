<?php
namespace Admin\Controller;
use Think\Controller;

class ExceldataController extends Controller {

    public function hotel(){
        exit;
        $area_id = 248;//1北京,9上海,236广州市,248佛山市,  246深圳市
//        $file_path = SITE_TP_PATH.'/Public/content/处理后-北京餐厅人均100-20230403.xlsx';
//        $file_path = SITE_TP_PATH.'/Public/content/处理后-上海餐厅人均50-20230403.xlsx';
//        $file_path = SITE_TP_PATH.'/Public/content/处理后-广州餐厅人均50-20230403.xlsx';
        $file_path = SITE_TP_PATH.'/Public/content/处理后-佛山餐厅人均30-20230403.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $m_hotel = new \Admin\Model\HotelModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_business_circle = new \Admin\Model\BusinessCircleModel();
        $data = array();
        $hotel_list = array();
        for ($row = 2; $row<=$highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $hotel_name = trim($rowData[0][0]);
                $dp_comment_num = trim($rowData[0][1]);
                $avg_expense = trim($rowData[0][2]);
                $business_circle_name = trim($rowData[0][3]);
                $county_name = trim($rowData[0][4]);
                $addr = trim($rowData[0][5]);

                $sql_circle="select a.id as business_circle_id,a.county_id from savor_business_circle as a left join savor_area_info as area on a.county_id=area.id 
                    where a.area_id={$area_id} and a.name='{$business_circle_name}' and area.region_name='{$county_name}'";
                $res_circle = $m_business_circle->query($sql_circle);
                $hotel_data = array();
                $county_id = $business_circle_id = 0;
                if(!empty($res_circle)){
                    $county_id = $res_circle[0]['county_id'];
                    $business_circle_id = $res_circle[0]['business_circle_id'];
                    $hotel_data = array('county_id'=>$county_id,'business_circle_id'=>$business_circle_id);
                }
                $hotel_ext_data = array('dp_comment_num'=>$dp_comment_num,'avg_expense'=>$avg_expense);
                $hotel_name = str_replace("'",'',$hotel_name);
//                $sql = "select * from savor_hotel where REPLACE(REPLACE(name, '（', '('), '）', ')')='{$hotel_name}' order by id desc";
                $sql = "select * from savor_hotel where name='{$hotel_name}' order by id desc limit 0,1";
                $res_hotel = $m_hotel->query($sql);
                if(!empty($res_hotel)){
//                    $hotel_id = $res_hotel[0]['id'];
//                    $hotel_name = $res_hotel[0]['name'];
//
//                    if(!empty($hotel_data)){
//                        $m_hotel->updateData(array('id'=>$hotel_id),$hotel_data);
//                    }
//                    $m_hotel_ext->updateData(array('hotel_id'=>$hotel_id),$hotel_ext_data);
//
//                    $data[]=$hotel_id;
//                    $hotel_list[]=array('hotel_id'=>$hotel_id,'hotel_data'=>$hotel_data,'hotel_ext_data'=>$hotel_ext_data);
                }else{
                    $hotel_id = 0;
                    $ahdata = array('name'=>$hotel_name,'area_id'=>$area_id,'county_id'=>$county_id,'business_circle_id'=>$business_circle_id,
                        'addr'=>$addr,'state'=>4,'htype'=>20,'no_work_type'=>21);
                    $hotel_id = $m_hotel->add($ahdata);

                    $hotel_ext_data['hotel_id'] = $hotel_id;
                    $m_hotel_ext->add($hotel_ext_data);
//                    $m_hotel_ext->updateData(array('hotel_id'=>$hotel_id),$hotel_ext_data);
                }
                echo "$hotel_id-$hotel_name-$row \r\n";
            }
        }
//        print_r($hotel_list);
//        echo join(',',$data);


    }
	public function importHotel(){
		exit();
		ini_set("memory_limit","8018M");
		//$area_id = 1;//1北京,9上海,236广州市,248佛山市,  246深圳市
		//$not_input_file = SITE_TP_PATH.'/Public/hotel/北京未导入酒楼.txt';
		//$file_path = SITE_TP_PATH.'/Public/hotel/北京酒楼.xlsx';
		
		//$area_id = 9;//1北京,9上海,236广州市,248佛山市,  246深圳市
		//$not_input_file = SITE_TP_PATH.'/Public/hotel/上海未导入酒楼.txt';
		//$file_path = SITE_TP_PATH.'/Public/hotel/上海酒楼.xlsx';
		
		//$area_id = 236;//1北京,9上海,236广州市,248佛山市,  246深圳市
		//$not_input_file = SITE_TP_PATH.'/Public/hotel/广州未导入酒楼.txt';
		//$file_path = SITE_TP_PATH.'/Public/hotel/广州酒楼.xlsx';
		
		$area_id = 248;//1北京,9上海,236广州市,248佛山市,  246深圳市
		$not_input_file = SITE_TP_PATH.'/Public/hotel/佛山未导入酒楼.txt';
		$file_path = SITE_TP_PATH.'/Public/hotel/佛山酒楼.xlsx';
		
		
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $m_hotel = new \Admin\Model\HotelModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_business_circle = new \Admin\Model\BusinessCircleModel();
        $data = array();
        $hotel_list = array();
		//$highestRow = 3;
		
		$handle = @fopen($not_input_file,'w');
		$flag = 0;
		for ($row = 2; $row<=$highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
			
            if(!empty($rowData[0][3])){
                $hotel_name = trim($rowData[0][3]);
                $dp_comment_num = trim($rowData[0][5]);
                $avg_expense = trim($rowData[0][4]);
                $business_circle_name = trim($rowData[0][10]);
                $county_name = trim($rowData[0][2]);
                $addr = trim($rowData[0][9]);
				
				$tel_str = trim($rowData[0][8]);
				$tel = '';
				$mobile = '';
				if(!empty($tel_str)){
					if(strstr($tel_str,'/')){
						$tel_arr = explode('/',$tel_str);
						
						if(strlen($tel_arr[0])==11){
							$mobile = $tel_arr[0];
							$tel    = $tel_arr[1];
						}else {
							$tel    = $tel_arr[0];
							$mobile = $tel_arr[1];
						}
					}else {
						if(strlen($tel_str)==11){//手机
							$mobile = $tel_str;
						}else {
							$tel    = $tel_str;
						}
					}
				}

                $sql_circle="select a.id as business_circle_id,a.county_id from savor_business_circle as a left join savor_area_info as area on a.county_id=area.id 
                    where a.area_id={$area_id} and a.name='{$business_circle_name}' and area.region_name='{$county_name}'";
				
                $res_circle = $m_business_circle->query($sql_circle);
                $hotel_data = array();
                $county_id = $business_circle_id = 0;
                if(!empty($res_circle)){
                    $county_id = $res_circle[0]['county_id'];
                    $business_circle_id = $res_circle[0]['business_circle_id'];
                    $hotel_data = array('county_id'=>$county_id,'business_circle_id'=>$business_circle_id);
                }
                $hotel_ext_data = array('dp_comment_num'=>$dp_comment_num,'avg_expense'=>$avg_expense);
                $hotel_name = str_replace("'",'',$hotel_name);
//                $sql = "select * from savor_hotel where REPLACE(REPLACE(name, '（', '('), '）', ')')='{$hotel_name}' order by id desc";
                $sql = "select * from savor_hotel where name='{$hotel_name}' order by id desc limit 0,1";
                $res_hotel = $m_hotel->query($sql);
				
                if(!empty($res_hotel)){
                    /*$hotel_id = $res_hotel[0]['id'];
                    $hotel_name = $res_hotel[0]['name'];

                    if(!empty($hotel_data)){
                        $m_hotel->updateData(array('id'=>$hotel_id),$hotel_data);
                    }
                    $m_hotel_ext->updateData(array('hotel_id'=>$hotel_id),$hotel_ext_data);

                    $data[]=$hotel_id;
                    $hotel_list[]=array('hotel_id'=>$hotel_id,'hotel_data'=>$hotel_data,'hotel_ext_data'=>$hotel_ext_data);*/
					$content = "$hotel_name-$row \r\n";
					fwrite($handle,$content);
					$flag ++;
                }else{
                    $hotel_id = 0;
                    $ahdata = array('name'=>$hotel_name,'area_id'=>$area_id,'county_id'=>$county_id,'business_circle_id'=>$business_circle_id,
                        'addr'=>$addr, 'mobile'=>$mobile , 'tel'=>$tel,'state'=>4,'htype'=>20,'no_work_type'=>21);
                    
					$hotel_id = $m_hotel->add($ahdata);

                    $hotel_ext_data['hotel_id'] = $hotel_id;
                    $m_hotel_ext->add($hotel_ext_data);
                    //$m_hotel_ext->updateData(array('hotel_id'=>$hotel_id),$hotel_ext_data);
                }
                echo "$hotel_id-$hotel_name-$row \r\n";
				
            }
        }
		fclose($handle);
		echo $flag ;
	}
	function updateCircle(){
	    exit();
	    vendor("PHPExcel.PHPExcel.IOFactory");
	    vendor("PHPExcel.PHPExcel");
	    $file_path = SITE_TP_PATH.'/Public/business_circle/商圈汇总.xlsx';
	    $inputFileType = \PHPExcel_IOFactory::identify($file_path);
	    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
	    $objPHPExcel = $objReader->load($file_path);
	    
	    $sheet = $objPHPExcel->getSheet(0);
	    $highestRow = $sheet->getHighestRow();
	    $highestColumn = $sheet->getHighestColumn();
	    
	    $m_hotel = new \Admin\Model\HotelModel();
	    $m_hotel_ext = new \Admin\Model\HotelExtModel();
	    $m_business_circle = new \Admin\Model\BusinessCircleModel();
	    
	    $data = array();
	    $hotel_list = array();
	    //$highestRow = 3;
	    
	    $flag = 0;
	    for ($row = 2; $row<=$highestRow; $row++){
	        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	        //print_r($rowData);exit;
	        $map = array();
	        $map['area_id'] = $rowData[0][3];
	        $map['name']    = $rowData[0][2];
	        $map['status']  = 1;
	        $info = array();
	        $info['trade_area_type'] = 1;
	        
	        $rts = $m_business_circle->updateData($map,$info);
	        if($rts){
	            $flag ++;
	            echo  "$rowData[0][3] - $rowData[0][2] \r\n";
	        }
	        
	    }
	    echo $flag;exit;
	}
}