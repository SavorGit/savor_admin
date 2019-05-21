<?php
/**
 *@author hongwei
 * @desc 内容与广告显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Common\Lib\Page;

class ContentlinkController extends BaseController{

	public $oss_host = '';
	public function __construct() {
		parent::__construct();
	}








	public function emptyData($size){
		$result['list'] = array();
		$count = 0;
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$result['page'] = $show;
		return $result;
	}







	/**
	 * 所有数据
	 * @return [type] [description]
	 */
	public function index(){
		$host_name = C('CONTENT_HOST');
		$this->assign('hname',$host_name);
		$starttime = I('adsstarttime','');
		$endtime = I('adsendtime','');
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$start = ($start-1)* $size;
		$order = ' TIMESTAMP desc ';
		$url = I('url');
		$where = "1=1";
		//$hidden_adsid = 98;//429
		//$adsname = '刺客信条';
//		$starttime = '2017-08-01';
//		$endtime = '2017-09-06';
		if (IS_POST) {
			if(empty($starttime) || empty($endtime)){
				$this->error('请选择开始时间与结束时间');
			}
			if($starttime <= $endtime) {
				$stt = strtotime($starttime);
				$ste = strtotime($endtime);
				if($stt == $ste) {
					$ste = $stt+86399;
				} else {
					$ste = $ste+86399;
				}
				$where.=" AND TIMESTAMP/1000>='$stt'";
				$where.=" AND TIMESTAMP/1000<='$ste'";
				$this->assign('s_time',$starttime);
				$this->assign('e_time',$endtime);

			}else{
				$this->error('开始时间必须小于等于结束时间');
			}
			if ( $url ) {
				$this->assign('urld',$url);
				//$cid_arr =  explode('?', $url);
				//$cid_str = $cid_arr[0];
				//preg_match("/.*content\/(.*).html.*/", $cid_str,$mathes);
				/*$contenid = $mathes[1];
				$cid_str_2 = htmlspecialchars_decode($cid_arr[1]);

				parse_str($cid_str_2, $ch_arr);
				if($contenid) {
					$where.=" AND content_id=$contenid ";
				}
				if($ch_arr) {
					$ina_array = array('channel','issq','iswx','app');
					foreach ($ch_arr as $ck=>$cv) {
						if(!in_array($ck, $ina_array)) {
							$this->error('参数不在请求范围内');
						}
						if($ck == 'app') {

						} else {
							if($ck == 'issq') {
								$ck = 'is_sq';
							}elseif($ck == iswx) {
								$ck = 'is_wx';
							}
							$where.=" AND $ck= '".$cv."' ";
						}

					}
				}*/
				$url = htmlspecialchars_decode('/'.$url);
				$where.=" AND request_url = '$url'";

			}
			$field = '*';
			$clinkModel = new \Admin\Model\ContentLinkModel();
			$result = $clinkModel->getList($where, $order, $field,$start,$size);

			$dat = $result['list'];
			$is_wei = array(
				'0' => '否',
				'1' => '是'
			);
			$is_shou = array(
				'0' => '否',
				'1' => '是'
			);
			$ind = $start;
			foreach($dat as $rk=>$rv) {
				$ind ++;
				$w = $dat[$rk]['is_wx'];
				$sq = $dat[$rk]['is_sq'];
				$dat[$rk]['is_wx'] = $is_wei[$w];
				$dat[$rk]['is_sq'] = $is_wei[$sq];
				$dat[$rk]['num'] = $ind;
				$ctime = substr($dat[$rk]['timestamp'],0 , -3);

				$dat[$rk]['vtime'] = date("Y-m-d H:i:s", $ctime);
			}
		}
		$this->assign('list', $dat);
		$this->assign('page',  $result['page']);
		$this->display('showlist');
	}
}
