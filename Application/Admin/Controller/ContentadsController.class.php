<?php
/**
 *@author hongwei
 * @desc 内容与广告显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Common\Lib\Page;

class ContentadsController extends BaseController{

	public $oss_host = '';
	public function __construct() {
		parent::__construct();
	}

	/**
	 * getads
	 * @return [json] 返回ajax广告数据
	 */
	public function getads_ajax(){
		$searchtitle = I('adsname','');
		$where = "1=1";
		$where .= " AND type in(1,2,8,9)";
		$field = "id,name,media_id,type";
		if ($searchtitle) {
			$where .= "	AND name LIKE '%{$searchtitle}%' ";
		}
		
		$adModel = new \Admin\Model\AdsModel();
		$pub_ads = new \Admin\Model\PubAdsModel();
		$result = $adModel->getWhere($where, $field);
		foreach ($result as $key=>$v){
		    if($v['type']==1){
		        $rt = $pub_ads->where(array('ads_id'=>$v['id'],'state'=>1))->count();
		        
		        if(empty($rt)){
		            //unset($result[$key]);
		        }
		    }
		    unset($result[$key]['type']);
		}
		
		$result = array_merge($result);
		echo json_encode($result);
		die;
	}

	public function getExpStatebak(){
		$adsname = I('post.adsname','');
		$adsname = str_replace(array('amp;'),array(''),$adsname);
		$starttime = I('post.start');
		$endtime = I('post.end');
		$yesday =  date("Y-m-d",strtotime("-1 day"));
		$hidden_adsid = I('post.hadsid','',0);
		if($adsname){
			if(empty($starttime) || empty($endtime)){
				$result = array('code'=>0,'msg'=>'请选择开始时间与结束时间');
				echo json_encode($result);
				die;
			}
			if($starttime <= $endtime) {
				if ( $endtime > $yesday){
					$result = array('code'=>0,'msg'=>'时间筛选范围有误');
				}else{
					if(!$hidden_adsid){
						$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
					}else{
						$adModel = new \Admin\Model\AdsModel();
						$ads_info = $adModel->find($hidden_adsid);
						if ($ads_info['name'] != $adsname) {
							$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
						}else{
							$where = ' 1=1 ';
							$ads_media_id = $ads_info['media_id'];
							$mItemModel = new \Admin\Model\MenuItemModel();
							$field = "distinct(`menu_id`)";
							$where .= " AND ads_id={$hidden_adsid}  ";
							$order = 'menu_id asc';
							$menu_arr = $mItemModel->getWhere($where,$order, $field);
							if($menu_arr){
								$where = "1=1";
								foreach($menu_arr as $ma){
									$menu_id_str .= $ma['menu_id'].',';
								}
								$menu_id_str = substr($menu_id_str,0,-1);
								$where .= " AND menu_id in ( ".$menu_id_str.')';
								$mhotelModel = new \Admin\Model\MenuHotelModel();
								$hotelModel = new \Admin\Model\HotelModel();
								$field = "distinct(`hotel_id`)";
								$order = 'hotel_id asc';
								$hotel_id_arr = $mhotelModel->getWhere($where, $order, $field);
								if($hotel_id_arr){
									$result = array('code'=>1);
								}else{
									$result = array('code'=>0,'msg'=>'该内容没有发布过，请重新选择');
								}

							}else{
								$result = array('code'=>0,'msg'=>'该内容没有发布过，请重新选择');
							}
						}
					}
				}

			}else{
				$result = array('code'=>0,'msg'=>'开始时间必须小于等于结束时间');
			}
		}else{
			$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
		}
		echo json_encode($result);
	}

	public function getExpState(){
		$adsname = I('post.adsname','');
		$starttime = I('post.start');
		$endtime = I('post.end');
		$yesday =  date("Y-m-d",strtotime("-1 day"));
		$hidden_adsid = I('post.hadsid','',0);
		if($adsname){
			if(empty($starttime) || empty($endtime)){
				$result = array('code'=>0,'msg'=>'请选择开始时间与结束时间');
				echo json_encode($result);
				die;
			}
			if($starttime <= $endtime) {
				if ( $endtime > $yesday){
					$result = array('code'=>0,'msg'=>'时间筛选范围有误');
				}else{
					if(!$hidden_adsid){
						$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
					}else{
						$adModel = new \Admin\Model\AdsModel();
						$ads_info = $adModel->find($hidden_adsid);
						if ($ads_info['name'] != $adsname) {
							$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
						}else{
							$result = array('code'=>1);
						}
					}
				}

			}else{
				$result = array('code'=>0,'msg'=>'开始时间必须小于等于结束时间');
			}
		}else{
			$result = array('code'=>0,'msg'=>'请输入后选择内容与广告');
		}
		echo json_encode($result);
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
	 * 所有数据bak
	 * @return [type] [description]
	 */
	public function listAllbak(){
		$starttime = I('adsstarttime','');
		$endtime = I('adsendtime','');
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order',' shlog.last_heart_time ');
		$adsname = I('contentast');
		$hidden_adsid = I('hadsid','',0);
		$where = "1=1";
		//$hidden_adsid = 98;//429
		//$adsname = '刺客信条';
		//$starttime = '2017-08-02';
		//$endtime = '2017-08-08';
		$yesday =  date("Y-m-d",strtotime("-1 day"));
		if ( $adsname ) {
			$this->assign('adsname', $adsname);
			$this->assign('contentast', $adsname);
			$this->assign('hidden_adsid', $hidden_adsid);
			//判断时间
			if(empty($starttime) || empty($endtime)){
				$this->error('请选择开始时间与结束时间');
			}
			if($starttime <= $endtime) {
				if ( $endtime > $yesday){
					$this->error('时间筛选范围有误');
				}
				$this->assign('s_time',$starttime);
				$this->assign('e_time',$endtime);

			}else{
				$this->error('开始时间必须小于等于结束时间');
			}

			$adModel = new \Admin\Model\AdsModel();
			$ads_info = $adModel->find($hidden_adsid);
			if ($ads_info['name'] != $adsname) {
				$this->error('请输入后选择内容与广告');
			}else{
				if(!$hidden_adsid){
					$this->error('请输入后选择内容与广告');
				}
				if(empty($ads_info)){
					$result = $this->emptyData($size);
				}else{
					//判断是否在节目单中发布过
					$ads_media_id = $ads_info['media_id'];
					$mItemModel = new \Admin\Model\MenuItemModel();
					$field = "distinct(`menu_id`)";
					$where .= " AND ads_id={$hidden_adsid}  ";
					$order = 'menu_id asc';
					$menu_arr = $mItemModel->getWhere($where,$order, $field);
					//echo $mItemModel->getLastSql().'<hr/>';
					if($menu_arr){
						//判断是否在酒店发布过
						$where = "1=1";
						//迭代替换，$v当前迭代元素值，$result 上一次迭代产生值
						$menu_id_str = array_reduce($menu_arr,
							function($result, $v){
								return $result.','.$v['menu_id'];
							}
						);
						$menu_id_str = substr($menu_id_str,1);
						$where .= " AND menu_id in ( ".$menu_id_str.')';
						$mhotelModel = new \Admin\Model\MenuHotelModel();
						$hotelModel = new \Admin\Model\HotelModel();
						$field = "distinct(`hotel_id`)";
						$order = 'hotel_id asc';
						$hotel_id_arr = $mhotelModel->getWhere($where, $order, $field);
						//var_dump($hotel_id_arr);
						if($hotel_id_arr){
							//根据hotelid得出box
							$where = '1=1';
							$hotel_id_str =  array_reduce($hotel_id_arr ,
								function($result , $v){
									Return $result.','.$v['hotel_id'];
								}
							);
							$hotel_id_str = substr($hotel_id_str,1);
							$where .= " AND sht.id in ( ".$hotel_id_str.')';
							$field = 'sht.id hotelid,sht.name,room.id
							          rid,room.name rname,box.name box_name, box.mac,sari
							          .region_name cname';
							$box_info = $hotelModel->getBoxMacByHid($field, $where);
							//var_dump($hotelModel->getLastSql());
							//dump($box_info);


							//求出在规定时间内满足的机顶盒
							$field = 'sum(play_count) plc,
							sum(play_time) plt,mac,group_concat(`play_date`) pld';
							$starttime = date("Ymd", strtotime($starttime));
							$endtime = date("Ymd", strtotime($endtime));
							$where = '1=1';
							$mestaModel = new \Admin\Model\MediaStaModel();
							$where .= " AND media_id = ".$ads_media_id;
							$where .= "	AND play_date >= '{$starttime}'";
							$where .= "	AND play_date <= '{$endtime} '";
							$group = 'mac';
							$me_sta_arr = $mestaModel->getWhere($where, $field, $group);
							//二维数组合并
							//var_dump($mestaModel->getLastSql());
							//die;
							$mp = array_column($me_sta_arr, 'mac');
							$me_sta_arr = array_combine($mp, $me_sta_arr);
							//var_dump($mestaModel->getLastSql());
							//dump($box_info);
							//dump($me_sta_arr);
							//获取电视数量
							//进行比较
							$tmp_box_tv = array();
							foreach ($box_info as $bk=>$bv) {
								$map_mac = $bv['mac'];
								//先判断是否存在
								if(array_key_exists($map_mac, $tmp_box_tv)) {
									$tmp_box_tv[$map_mac]['tv_count'] +=1;
									continue;
								}else {
									if(array_key_exists($map_mac, $me_sta_arr)) {
										$mv = $me_sta_arr[$map_mac];
										$mv['pld'] = preg_replace('/(\s)*/','', $mv['pld']);
										$day_arr = explode(',',$mv['pld']);

										$day_arr = array_unique($day_arr);
										$day_str = implode(',', $day_arr);
										$day_len = count($day_arr);
										$tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
										$tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
										$tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
										$tmp_box_tv[$map_mac]['play_count'] = $mv['plc'];
										$tmp_box_tv[$map_mac]['play_time'] = $mv['plt'];
										$tmp_box_tv[$map_mac]['play_days'] = $day_len;
										$tmp_box_tv[$map_mac]['publication'] = $day_str;
										$tmp_box_tv[$map_mac]['tv_count'] = 1;

										$tmp_box_tv[$map_mac]['mac'] = $map_mac;
										$tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
									}else{
										$tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
										$tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
										$tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
										$tmp_box_tv[$map_mac]['play_count'] = '';
										$tmp_box_tv[$map_mac]['play_time'] = '';
										$tmp_box_tv[$map_mac]['play_days'] = '';
										$tmp_box_tv[$map_mac]['publication'] = '';
										$tmp_box_tv[$map_mac]['tv_count'] = 1;
										$tmp_box_tv[$map_mac]['mac'] = $map_mac;
										$tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
									}
									unset($me_sta_arr[$map_mac]);
								}

							}
							$tmp_box_tv = array_values($tmp_box_tv);
							//var_export($tmp_box_tv);
							if($tmp_box_tv){
								$limit = ($start-1)*$size;
								$tmp_box_tvt = array_slice($tmp_box_tv, $limit , $size,true);
								$result['list']  = $tmp_box_tvt;
								$totals=count($tmp_box_tv);
								$objPage = new Page($totals,$size);
								$result['page']  = $objPage->admin_page();
							}else{
								$result = $this->emptyData($size);
							}



						}else{
							$this->error('该内容没有发布过，请重新选择');
						}

					}else{
						$this->error('该内容没有发布过，请重新选择');
					}

				}
			}
		}else{
			if(IS_POST){
				$this->error('请输入后选择内容与广告');
			}else{
				$result = $this->emptyData($size);
			}

		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('showlist');
	}

	public function listAll(){
		$starttime = I('adsstarttime','');
		$endtime = I('adsendtime','');
		$size   = I('numPerPage',50);//显示每页记录数
		$start = I('pageNum',1);
		$adsname = I('contentast','','trim');
		$hidden_adsid = I('hadsid',0,'intval');

		$yesday =  date("Y-m-d",strtotime("-1 day"));
        if(empty($starttime) || empty($endtime)){
            $starttime = date("Y-m-d",strtotime("-2 day"));
            $endtime = $yesday;
        }
        if ($endtime>$yesday || $starttime>$endtime){
            $this->error('时间筛选范围有误');
        }
        $m_ads = new \Admin\Model\AdsModel();
		if(!empty($adsname) && !empty($hidden_adsid)){
            $ads_info = $m_ads->find($hidden_adsid);
            if($ads_info['name'] != $adsname){
                $this->error('请输入后选择内容与广告');
            }
            $hotel_box_type_str = $this->getNetHotelTypeStr();
            //判断是否在节目单中发布过
            $ads_media_id = $ads_info['media_id'];
            $hotelModel = new \Admin\Model\HotelModel();
            $field = "distinct(`id`) hotel_id";
            $order = 'id asc';
            $where = "name not like '%永峰%' ";
            $where .= " and hotel_box_type in ($hotel_box_type_str) ";
            $hotel_id_arr = $hotelModel->getWhereorderData($where,  $field, $order);
            if($hotel_id_arr){
                //根据hotelid得出box
                $where = 'box.state = 1 and box.flag = 0 ';
                $hotel_id_str =  array_reduce($hotel_id_arr ,
                    function($result , $v){
                        Return $result.','.$v['hotel_id'];
                    }
                );
                $hotel_id_str = substr($hotel_id_str,1);
                $where .= " AND sht.id in ( ".$hotel_id_str.')';
                $field = 'sht.id hotelid,sht.name,room.id rid,room.name rname,box.name box_name, box.mac,sari.region_name cname';
                $box_info = $hotelModel->getBoxMacByHid($field, $where);
                //求出在规定时间内满足的机顶盒
                $field = 'sum(play_count) plc,sum(play_time) plt,mac,group_concat(`play_date`) pld';
                $start_time = date("Ymd", strtotime($starttime));
                $end_time = date("Ymd", strtotime($endtime));
                $mestaModel = new \Admin\Model\MediaStaModel();
                $where = " media_id =$ads_media_id AND play_date>='{$start_time}' AND play_date<='{$end_time}'";
                $group = 'mac';
                $me_sta_arr = $mestaModel->getWhere($where, $field, $group);
                //二维数组合并
                $mp = array_column($me_sta_arr, 'mac');
                $me_sta_arr = array_combine($mp, $me_sta_arr);
                //获取电视数量
                //进行比较
                $tmp_box_tv = array();
                foreach ($box_info as $bk=>$bv) {
                    $map_mac = $bv['mac'];
                    //先判断是否存在
                    if(array_key_exists($map_mac, $tmp_box_tv)) {
                        $tmp_box_tv[$map_mac]['tv_count'] +=1;
                        continue;
                    }else {
                        if(array_key_exists($map_mac, $me_sta_arr)) {
                            $mv = $me_sta_arr[$map_mac];
                            $mv['pld'] = preg_replace('/(\s)*/','', $mv['pld']);
                            $day_arr = explode(',',$mv['pld']);

                            $day_arr = array_unique($day_arr);
                            sort($day_arr);
                            $day_str = implode(',', $day_arr);
                            $day_len = count($day_arr);
                            $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                            $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                            $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                            $tmp_box_tv[$map_mac]['play_count'] = $mv['plc'];
                            $tmp_box_tv[$map_mac]['play_time'] = $mv['plt'];
                            $tmp_box_tv[$map_mac]['play_days'] = $day_len;
                            $tmp_box_tv[$map_mac]['publication'] = $day_str;
                            $tmp_box_tv[$map_mac]['tv_count'] = 1;

                            $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                            $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                        }else{
                            $tmp_box_tv[$map_mac]['cityname'] = $bv['cname'];
                            $tmp_box_tv[$map_mac]['rname'] = $bv['rname'];
                            $tmp_box_tv[$map_mac]['hotel_name'] = $bv['name'];
                            $tmp_box_tv[$map_mac]['play_count'] = '';
                            $tmp_box_tv[$map_mac]['play_time'] = '';
                            $tmp_box_tv[$map_mac]['play_days'] = '';
                            $tmp_box_tv[$map_mac]['publication'] = '';
                            $tmp_box_tv[$map_mac]['tv_count'] = 1;
                            $tmp_box_tv[$map_mac]['mac'] = $map_mac;
                            $tmp_box_tv[$map_mac]['box_name'] = $bv['box_name'];
                            $tmp_box_tv[$map_mac]['hotel_id'] = $bv['hotelid'];
                        }
                        unset($me_sta_arr[$map_mac]);
                    }
                }
                $tmp_box_tv = array_reduce($tmp_box_tv, function($result, $item){
                    $result[$item['hotel_id']][] = $item;
                    return $result;
                });
                ksort($tmp_box_tv);
                $tmp_box_tv = array_reduce($tmp_box_tv, function($result, $item){
                    foreach($item as $k=>$vp){
                        $result[$vp['mac']] = $vp;
                    }
                    return $result;
                });
                $tmp_box_tv = array_values($tmp_box_tv);
                $all_play_nums = 0;
                if($tmp_box_tv){
                    $limit = ($start-1)*$size;
                    foreach ($tmp_box_tv as $v){
                        if(!empty($v['play_count'])){
                            $all_play_nums+=intval($v['play_count']);
                        }
                    }
                    $tmp_box_tvt = array_slice($tmp_box_tv, $limit , $size,true);
                    $result['list']  = $tmp_box_tvt;
                    $totals=count($tmp_box_tv);
                    $objPage = new Page($totals,$size);
                    $result['page']  = $objPage->admin_page();
                    $this->assign('all_play_nums',$all_play_nums);
                }else{
                    $result = $this->emptyData($size);
                }
            }
        }else{
            $result = $this->emptyData($size);
        }
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('adsname', $adsname);
        $this->assign('contentast', $adsname);
        $this->assign('hidden_adsid', $hidden_adsid);
        $this->assign('s_time',$starttime);
        $this->assign('e_time',$endtime);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('showlist');
	}

}
