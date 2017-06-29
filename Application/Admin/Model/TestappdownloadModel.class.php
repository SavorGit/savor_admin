<?php
/**
 *@author zhang.yingtao
 * @desc app下载统计
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
class TestappdownloadModel extends BaseModel
{
	protected $trueTableName='devp_savor_app_download';
	public function getDownloadHotel($where,$order,$sort){
	    $sql ="select * from devp_savor_app_download where 1 $where order by $order $sort";
	    $result = $this->query($sql);
	    return $result;
	}
	
}