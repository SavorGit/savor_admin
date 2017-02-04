<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class MenuListOpeModel extends BaseModel
{
	protected $tableName='menulist_ope_log';

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}

	//É¾³ýÊý¾Ý
	public function delData($id) {
		$delSql = "DELETE FROM `savor_mb_content` WHERE id = '{$id}'";
		$result = $this -> execute($delSql);
		return  $result;
	}

	public function getList($where, $order='id desc', $start=0,$size=5)
	{


		$list = $this->where($where)
			->order($order)
			->limit($start,$size)
			->select();


		$count = $this->where($where)
			->count();

		$objPage = new Page($count,$size);

		$show = $objPage->admin_page();


		$data = array('list'=>$list,'page'=>$show);


		return $data;

	}//End Function


	public function getImgRes($path, $old_img) {
		$arr = explode('.', $old_img);
		$img_type = $arr[1];
		$pic = date('Y-m-d').'a_pics'.time().'.'.$img_type;
		$new_img = $path.'/'.$pic;
		$old_img = SITE_TP_PATH.$old_img;
		$res = $this->myCopyFunc($old_img, $new_img);
		if ( $res == 1 ) {
			return array('res'=>1,'pic'=>$pic);
		} else {
			return array('res'=>0);
		}
	}

	/**
	 * @param $res  ??????
	 * @param $des  ??????¡À¨º
	 */

	public function myCopyFunc($res, $des) {
		if(file_exists($res)) {
			$r_fp=fopen($res,"r");

			$d_fp=fopen($des,"w+");
			//$fres=fread($r_fp,filesize($res));
			//¡À???¡À???
			$buffer=1024;
			$fres="";
			while(!feof($r_fp)) {
				$fres=fread($r_fp,$buffer);
				fwrite($d_fp,$fres);
			}
			fclose($r_fp);
			fclose($d_fp);
			return 1;
		} else {
			return 0;
		}
	}




}//End Class