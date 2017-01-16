<?php
/**
 *@author hongwei
 *
 *
 *
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
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










	/**
	 * 保存或者更新分类信息
	 *
	 * @return [type] [description]
	 */
	public function doAddCat()
	{


		$catModel = new CategoModel;
		$id                  = I('post.id');
		$save                = [];
		$save['name']        = I('post.cat_name','','trim');
		$save['sort_num']    = I('post.sort','','intval');
		$save['state']    = I('post.state','0','intval');
		$save['update_time'] = date('Y-m-d H:i:s');

		$old_img = I('post.shwimage','');
		$path = SITE_TP_PATH.'/Public/'.$this->path;
		if ( !(is_dir($path)) ) {
			mkdir ( $path, 0777, true );
		}
		if ( $old_img == '') {

		} else {
			$result = $catModel->getImgRes($path, $old_img);
			if ($result['res'] == 1) {
				$save['img_url']  = $this->path.'/'.$result['pic'];
			} else {
				$this->output('添加图片失败!', 'release/addCate');
			}
		}

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
