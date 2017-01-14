<?php
/**
 *@author hongwei
 *
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\HotelModel;
use Admin\Model\AreaModel;
use Admin\Model\CategoModel;

class ReleaseController extends BaseController
{

	public $path = 'category/img';
	public function __construct() {
        parent::__construct();
    }


	/**
	 * 分类列表
	 *
	 * 
	 * @return [type] [description]
	 */
	public function category()
	{


		$catModel = new CategoModel;



		$size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','sort_num');
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        
        $name = I('name');

        if($name)
        {
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $catModel->getList($where,$orders,$start,$size);





   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('cate');


	}//End Function





	/**
	 * 新增分类
	 * 
	 */
	public function addCate()
	{	
		$id = I('get.id');

		$catModel = new CategoModel;

		if($id)
		{
			$vinfo = $catModel->where('id='.$id)->find();

			$this->assign('vinfo',$vinfo);

		}
			
		return $this->display('addCat');

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
	 * @param $res  读取源
	 * @param $des  写入目标
	 */

	public function myCopyFunc($res, $des) {
		if(file_exists($res)) {
			$r_fp=fopen($res,"r");

			$d_fp=fopen($des,"w+");
			//$fres=fread($r_fp,filesize($res));
			//边读边写
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
	 * 保存或者更新分类信息
	 * 
	 * @return [type] [description]
	 */
	public function doAddCat()
	{
		$id                  = I('post.id');
		$save                = [];
		$save['name']        = I('post.cat_name','','trim');
		$save['sort_num']    = I('post.sort','','intval');

		$save['update_time'] = date('Y-m-d H:i:s');

		$old_img = I('post.shwimage','');
        $path = SITE_TP_PATH.'/Public/'.$this->path;
		if ( !(is_dir($path)) ) {
			mkdir ( $path, 0777, true );
		}
		if ( $old_img == '') {

		} else {
			$result = $this->getImgRes($path, $old_img);
			if ($result['res'] == 1) {
				$save['img_url']  = $this->path.'/'.$result['pic'];
			} else {
				$this->output('添加图片失败!', 'release/addCate');
			}
		}
		$catModel = new CategoModel;
		if($id)
		{
			if($catModel->where('id='.$id)->save($save))
			{
				$this->output('操作成功!', 'release/addCate');
			}
			else
			{
				 $this->output('操作失败!', 'release/doAddCat');
			}		
		}
		else
		{	
			
			$save['create_time'] = date('Y-m-d H:i:s');

			if($catModel->add($save))
			{
				$this->output('操作成功!', 'release/addCate');
			}
			else
			{
				 $this->output('操作失败!', 'release/doAddCat');
			}	

		}		


	}//End Function





}//End Class
