<?php
/**
 *导表更新酒楼数据
 */
namespace Admin\Controller;


class UpdatehotelController extends BaseController{
    
    public function index(){
        //echo "dddd";exit;
        $this->display('index');
    }
    public function updateopuser(){
        if(IS_POST){
            $upload = new \Think\Upload();
            $upload->exts = array('xls','xlsx','csv');
            $upload->maxSize = 2097152;
            $upload->rootPath = $this->imgup_path();
            $upload->savePath = '';
            $upload->saveName = time().mt_rand();
            $info = $upload->upload();
            if(!$info){
                $errMsg = $upload->getError();
                $this->output($errMsg, 'Wxworkgroup/updateHotelGroup', 2,0);
            }else {
                $file_name = $info['fileup']['savepath'].$info['fileup']['savename'];
                //echo $file_name;exit;
                $file_path = SITE_TP_PATH.'/Public/uploads/'.$file_name;
                //echo $file_path;exit;
                
                
                vendor("PHPExcel.PHPExcel.IOFactory");
                vendor("PHPExcel.PHPExcel");
                $inputFileType = \PHPExcel_IOFactory::identify($file_path);
                $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file_path);
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $flag = 0;
                $m_hotel_ext = new \Admin\Model\HotelExtModel();
				$m_opsstaff = new \Admin\Model\OpsstaffModel();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                    //print_r($rowData);exit;
                    $fields = 'user.id main_id,user.remark';
					$mps = [];
					//$mps['status'] = 1;
					$mps['is_operrator'] = 1;
					$mps['user.remark']  = $rowData[0][3];
					
					$opuser_arr = $m_opsstaff->alias('a')
							   ->join('savor_sysuser user on a.sysuser_id=user.id','left')
							   ->field($fields)
							   ->where($mps)
							   ->find();
					//print_r($opuser_arr);exit;
					if(!empty($opuser_arr)){
						
						$where = [];
						$data = [];
						$where['hotel_id'] = $rowData[0][1];
						$data['responsible_maintainer_id'] = $opuser_arr['main_id'] ;
						
						//print_r($data);
						//print_r($where);exit;
						
						$ret = $m_hotel_ext->saveData($data, $where);
						if($ret){
							$flag ++;
						}
					}
					
					
                    
                }
                $message = '成功更新'.$flag.'家餐厅';
                $this->output($message, 'updatehotel/index');
            }
        }else{
            $this->display();
        }
    }
    
}