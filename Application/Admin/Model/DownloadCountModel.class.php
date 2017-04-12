<?php
/**
 *@author zhang.yingtao
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
class DownloadCountModel extends BaseModel
{
	protected $tableName='download_count';
	public function addInfo($data){
	    return $this->add($data);
	}
}
