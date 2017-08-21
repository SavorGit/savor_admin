<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class ArticleModel extends BaseModel
{
	protected $tableName='mb_content';


	public function getRecommend($where, $field, $sor_arr){

		foreach($sor_arr as $kv){
			$set_str .= " AND find_in_set($kv, order_tag)";
		}
		$sql =" select $field from savor_mb_content where $where and order_tag !='' $set_str order by savor_mb_content.create_time desc";
		$result = $this -> query($sql);
		return  $result;
	}


	public function getArtinfoById($where){
		$sql = "  select mc.order_tag,mc.id artid,m.oss_addr as name,mcat.name as category,
               mc.index_img_url,mc.title,mc.duration,mc.img_url as imgurl,mc.content_url
               as contenturl,mc.tx_url as videourl,mc.share_title as shareTitle,
	           mc.share_content as shareContent,mc.type,mc.content,mc.media_id
	           as mediaId,mc.create_time as updatetime,sascource.name as sourceName
	           from  savor_mb_content mc  left join savor_media m on mc.media_id = m.id
	           left  join savor_mb_hot_category as mcat on mc.hot_category_id = mcat.id
	           left join savor_article_source sascource on sascource.id = mc.source_id
	           where 1=1 $where";
		$result = $this->query($sql);
		return $result[0];
	}


	public function getMaxSort($where){
		$sql ="select `id`,`sort_num` from savor_mb_content where sort_num = ( select max(`sort_num`) from savor_mb_content where $where ) ";
		$result =  $this->query($sql);
		return $result[0];
	}



	public function getOneRow($where, $field,$order){
		$list = $this->where($where)
			->order($order)
			->limit(1)
			->field($field)->select();
		if(empty($list)){
			return false;
		}else{
			return $list[0];
		}

	}


	public function updateSortNum($artid_arr, $sort_arr){
		    $id_str ="";
			foreach($artid_arr as $ak=>$av){
				$id_str .=  ' ('.$av.','.$sort_arr[$ak].')'.',';
			}
		    $id_str = substr($id_str,0,-1);
			$sql =" INSERT INTO `savor_mb_content` (`id`,`sort_num`) values $id_str ON DUPLICATE KEY UPDATE sort_num=VALUES(sort_num)";
			return $this->execute($sql);


	}

	public function getWhere($where, $field,$order,$limit){
		$list = $this->where($where)->field($field)->order($order)->limit($limit)->select();
		return $list;
	}

	//ɾ�����
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


	public function getdiaList($where, $order='id desc', $start=0,$size=5)
	{


		$list = $this->where($where)
			->order($order)
			->limit($start,$size)
			->select();


		$count = $this->where($where)
			->count();

		$objPage = new Page($count,$size);

		$show = $objPage->admin_pagedialog();


		$data = array('list'=>$list,'page'=>$show);


		return $data;

	}//End Function

	public function getOssSize($oss_path) {
		if (empty($oss_path)) {
			return '0';
		}

		$accessKeyId = C('OSS_ACCESS_ID');
		$accessKeySecret = C('OSS_ACCESS_KEY');
		$endpoint = C('OSS_HOST');
		$bucket = C('OSS_BUCKET');
		$aliyun = new \Common\Lib\Aliyun($accessKeyId, $accessKeySecret, $endpoint);
		$aliyun->setBucket($bucket);
		$ossClient = $aliyun->getOssClient();
		$info = $ossClient->getObjectMeta($aliyun->getBucket(), $oss_path);
		//var_dump($info);
		if($info){
			
$byt = $this->byteFormat($info['content-length'],'MB');
		}else{
			$byt = '0';
		}

		return $byt;
	}

	public function byteFormat($bytes, $unit = "", $decimals = 2) {
		$units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

		$value = 0;
		if ($bytes > 0) {
			// Generate automatic prefix by bytes
			// If wrong prefix given
			if (!array_key_exists($unit, $units)) {
				$pow = floor(log($bytes)/log(1024));
				$unit = array_search($pow, $units);
			}

			// Calculate byte value by prefix
			$value = ($bytes/pow(1024,floor($units[$unit])));
		}

		// If decimals is not numeric or decimals is less than 0
		// then set default value
		if (!is_numeric($decimals) || $decimals < 0) {
			$decimals = 2;
		}

		// Format output
		return sprintf('%.' . $decimals . 'f ', $value);
	}



	public function getTotalSize($result=[]){
		if(!$result || !is_array($result)){
			return [];
		}
		$arrArtId = [];
		$size = 0;

		foreach($result as &$value) {
			$contentid = $value['content_id'];
			$info = $this->where(array('type'=>3))->find($contentid);
			$size+= $info['size'];

		}
		return $size;

	}
	public function changeIdjName($result=[],$cat_arr){
		if(!$result || !is_array($result)){
			return [];
		}
		$arrArtId = [];
		$index = 1;
		foreach($result as &$value) {
			$contentid = $value['content_id'];
			$info = $this->find($contentid);
			$value['media_id'] = $info['media_id'];
			$value['category_id'] = $info['hot_category_id'];
			$value['operators'] = $info['operators'];
			$value['title'] = $info['title'];
			$value['type'] = $info['type'];
			$value['size'] = $info['size'];
			$value['index'] = $index;
			$index++;
		}

		foreach ($result as &$value){
			foreach ($cat_arr as  $row){
				if($value['category_id'] == $row['id']){
					$value['cat_name'] = $row['name'];
				}
			}
		}

		return $result;
	}

	public function  changeCatname($result){
		$catModel = new HotCategoryModel();
		$cat_arr =  $catModel->field('id,name')->select();
		$oldcatModel = new CategoModel();
		$oldcat_arr =  $oldcatModel->field('id,name')->select();
		foreach ($result as &$value){
			foreach ($cat_arr as  $row){
				if($value['hot_category_id'] == $row['id']){
					$value['cat_name'] = $row['name'];
				}
			}
			foreach ($oldcat_arr as  $row){
				if($value['category_id'] == $row['id']){
					$value['old_cat_name'] = $row['name'];
				}
			}
		}
		return $result;

	}


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
	 * @param $des  ??????����
	 */

	public function myCopyFunc($res, $des) {
		if(file_exists($res)) {
			$r_fp=fopen($res,"r");

			$d_fp=fopen($des,"w+");
			//$fres=fread($r_fp,filesize($res));
			//��???��???
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
    /**
     * @desc 获取某个分类下的文章数量
     */
	public function getCountByCatid($category_id){
	    $result = $this->where(array('category_id'=>$category_id))->count();
	    return $result;
	}
    /**
     * @desc 根据来源统计文章数量
     */
	public function countNumBySourceId($source_id = 0){
	    if($source_id){
	        return $this->where('source_id='.$source_id)->count();
	    }else {
	        return false;
	    }
	}

}//End Class