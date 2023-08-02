<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class AdsModel extends BaseModel{
	protected $tableName='ads';

	public function getadvInfo($hotelid, $menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortNum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}

            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}


	public function getuAdvname($hotelid, $menuid){
		$field = "ads.name adname,sht.name hname,ads.id ads_id,media.oss_addr,
		sht.id hotel_id";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        left join savor_hotel sht on ads.hotel_id = sht.id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}

            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}

	public function getupanadvInfo($hotelid, $menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortNum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}";

		$result = $this->query($sql);
		return $result;

	}
	public function getupanadvInfoNew($hotelid, $menuid){
	    $field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix suffix,
				item.sort_num AS sortNum,
				item.ads_name AS chinese_name";
	    $sql = "select ".$field;
	
	    $sql .= " FROM savor_ads ads
	    LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
	    LEFT JOIN savor_media media on media.id = ads.media_id
	    where ads.type=3
	    and ads.hotel_id={$hotelid}
	    and (item.ads_id is null or item.ads_id=0)
	    and ads.state=1
	    and item.menu_id={$menuid}";
	
	    $result = $this->query($sql);
	    return $result;
	
	}

	public function getadsInfo($menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS `order`,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= "  FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.id = item.ads_id
        LEFT JOIN savor_media media on media.id = ads.media_id
        where
            ads.state=1
            and item.menu_id={$menuid}
            and ads.type = 1
            and media.oss_addr is not null";
		$result = $this->query($sql);
		return $result;

	}
	public function getadsInfoNew($menuid){
	    $field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix suffix,
				item.sort_num AS `order`,
				item.ads_name AS chinese_name";
	    $sql = "select ".$field;
	
	    $sql .= "  FROM savor_ads ads
	    LEFT JOIN savor_menu_item item on ads.id = item.ads_id
	    LEFT JOIN savor_media media on media.id = ads.media_id
	    where
	    ads.state=1
	    and item.menu_id={$menuid}
	    and ads.type = 1
	    and media.oss_addr is not null";
	    $result = $this->query($sql);
	    return $result;
	
	}

	public function  getupanproInfo($menuid){
        $field = "media.id AS vid,
                    media.oss_addr AS name,
                    media.md5 AS md5,
                    'easyMd5' AS md5_type,
                    case ads.type
                    when 1 then 'ads'
                    when 2 then 'pro'
                    when 3 then 'adv' END AS type,
                    media.oss_addr AS oss_path,
                    media.duration AS duration,
                    media.surfix ,
                    item.sort_num AS sortnum,
                    item.ads_name AS chinese_name";
        $sql = "select ".$field;

        $sql .= "  FROM savor_ads ads LEFT JOIN savor_menu_item item
              on ads.id = item.ads_id
            LEFT JOIN savor_media media on media.id = ads.media_id
            where
                ads.state=1
                and item.menu_id=$menuid
                and ads.type = 2";

        $result = $this->query($sql);
        return $result;
    }

	public function getproInfo($menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortnum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= "  FROM savor_ads ads LEFT JOIN savor_menu_item item
          on ads.id = item.ads_id
        LEFT JOIN savor_media media on media.id = ads.media_id
        where
            ads.state=1
            and item.menu_id=$menuid
            and ads.type = 2
            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}
	public function getproInfoNew($menuid){
	    $field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix suffix,
				item.sort_num AS sortnum,
				item.ads_name AS chinese_name";
	    $sql = "select ".$field;
	
	    $sql .= "  FROM savor_ads ads LEFT JOIN savor_menu_item item
	    on ads.id = item.ads_id
	    LEFT JOIN savor_media media on media.id = ads.media_id
	    where
	    ads.state=1
	    and item.menu_id=$menuid
	    and ads.type = 2
	    and media.oss_addr is not null";
	
	    $result = $this->query($sql);
	    return $result;
	
	}

	public function getWhere($where, $field,$order){
		$list = $this->where($where)->field($field)->order($order)->select();
		return $list;
	}

	public function delData($id) {
		$delSql = "DELETE FROM `savor_mb_content` WHERE id = '{$id}'";
		$result = $this -> execute($delSql);
		return  $result;
	}

	public function getList($where, $order='id desc', $start=0,$size=5){
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
	}

    public function generateDir() {
//        set_time_limit(0);
//        ini_set("memory_limit", "1024M");
        //获取需要执行的列表
        $map          = array();
        $pubic_path = dirname(APP_PATH).DIRECTORY_SEPARATOR.'Public/udriverpath';
        $pub_path = $pubic_path.DIRECTORY_SEPARATOR;
        $signle_Model = new \Admin\Model\SingleDriveListModel();
        $map['state'] = 0;
        $field='hotel_id_str, gendir, id, up_cfg';
        $order = ' id asc ';
        $limit = 1;
        $single_list = $signle_Model->getOrderOne($field, $map, $order, $limit);
        $smfileModel = new SimFile();
        $now_date = date("Y-m-d H:i:s");
        $hotelModel = new \Admin\Model\HotelModel();
        $update_config_cfg = C('UPD_STR');
        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = C('OSS_HOST');
        $bucket = C('OSS_BUCKET');
        $pic_err_log = LOG_PATH.'upan_error_'.date("Y-m-d").'log';
        //获取系统默认音量值
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $whereconfig = " config_key in('system_ad_volume','system_pro_screen_volume','system_demand_video_volume','system_tv_volume','system_switch_time')  ";

        $volume_arr = $m_sys_config->getList($whereconfig);
        $vol = array();
        $vol_default = C('CONFIG_VOLUME_VAL');
        if($volume_arr) {
            foreach($volume_arr as $k=>$v) {
                if($v['config_key']=='system_ad_volume'){
                    //广告轮播音量
                    if( $v['config_value'] === '') {
                        $vol['system_ad_volume'] = $vol_default['system_ad_volume'];
                    } else {
                        $vol['system_ad_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_pro_screen_volume'){
                    //投屏音量
                    if( $v['config_value'] === '') {
                        $vol['system_pro_screen_volume'] = $vol_default['system_pro_screen_volume'];
                    } else {
                        $vol['system_pro_screen_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_demand_video_volume'){
                    //点播音量
                    if( $v['config_value'] === '') {
                        $vol['system_demand_video_volume'] = $vol_default['system_demand_video_volume'];
                    } else {
                        $vol['system_demand_video_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_tv_volume'){
                    //电视音量
                    if( $v['config_value'] === '') {
                        $vol['system_tv_volume'] = $vol_default['system_tv_volume'];
                    } else {
                        $vol['system_tv_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_switch_time' ){

                    //电视音量
                    if($v['status'] == 1) {
                        if( empty($v['config_value']) ) {
                            $vol['system_switch_time'] = $vol_default['system_switch_time'];
                        } else {
                            $vol['system_switch_time'] = intval($v['config_value']);
                        }
                    } else {
                        $vol['system_switch_time'] = -8;
                    }
                }
            }
        }

        if ($single_list) {
            foreach ($single_list as $sk=>$sv) {

                $po_th = '';
                $gid = $sv['id'];
                $gendir = $sv['gendir'];
                $upcfg = $sv['up_cfg'];
                if($upcfg) {
                    $upcfg = explode(',', $upcfg);
                } else {
                    $upcfg = array();
                }
                $po_th = $pub_path.$gendir;
                $savor_path = $po_th.DIRECTORY_SEPARATOR.'savor';
                $savor_me = $po_th.DIRECTORY_SEPARATOR.'media';
                $savor_log = $po_th.DIRECTORY_SEPARATOR.'log';
                $delfile = $po_th;
                $delzipfile = $po_th.'.zip';
                $del_res = $smfileModel->remove_dir($delfile, true);
                $del_res = $smfileModel->unlink_file($delzipfile);
                if ( $smfileModel->create_dir($savor_path)
                    && $smfileModel->create_dir($savor_me)
                    && $smfileModel->create_dir($savor_log)
                ) {
                    //创建hotel.json文件
                    $hotel_file = $po_th.DIRECTORY_SEPARATOR.'hotel.json';
                    if ( $smfileModel->create_file($hotel_file, true) ) {
                        //  echo '创建hotel.json成功'.PHP_EOL;
                        //写入hotel.json
                        $hwhere = array();
                        $hwhere['hotel_box_type'] =  array('in', array('1','4','5') );
                        $hwhere['state'] = 1;
                        $hwhere['flag'] = 0;
                        $hotel_arr = $hotelModel->getInfo('id hotel_id, name hotel_name', $hwhere);
                        if ($hotel_arr) {
                            $hotel_info = json_encode($hotel_arr);
                        } else {
                            $hotel_info = '';
                        }
                        $smfileModel->write_file($hotel_file, $hotel_info);
                    }
                    // echo '创建目录'.$savor_path.'成功'.PHP_EOL;

                    $hotel_id_arr = json_decode($sv['hotel_id_str'], true);
                    $adsModel = new \Admin\Model\AdsModel();
                    $menuhotelModel = new \Admin\Model\MenuHotelModel();
                    $xuan_hotel_st = '';
                    $start_time = microtime(true);
                    $oss_host = get_oss_host();
                    $rs_hotel = array();
                    foreach ( $hotel_id_arr as $hav) {
                        $per_arr = $menuhotelModel->getadsPeriod($hav);
                        if($per_arr) {
                            //获取宣传片
                            $menupid = $per_arr[0]['menuid'];
                            $resa = $adsModel->getuAdvname($hav, $menupid);
                            if($resa) {
                                //获取酒楼下宣传片
                                foreach($resa as $ras=>$rks) {
                                    $rs_hotel[$rks['hname']][$rks['ads_id']] = array(
                                        'name'=>$rks['adname'],
                                        'url'=>$oss_host.$rks['oss_addr'],
                                        'hname'=>$rks['hname'],
                                    );
                                }
                            }
                        } else {
                            continue;
                        }
                    }

                    foreach($rs_hotel as $arh=>$ahv) {
                        $xuan_hotel_st .= $arh."\r\n";
                        foreach($ahv as $bk=>$bv) {
                            $xuan_hotel_st .= $bv['name']."\t".$bv['url']."\r\n";
                        }
                    }

                    $adv_file = $po_th.DIRECTORY_SEPARATOR.'hotel.txt';
                    $smfileModel->write_file($adv_file, $xuan_hotel_st);
                    $m_version_upgrade = new \Admin\Model\UpgradeModel();
                    $aliyun = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
                    $aliyun->setBucket($bucket);
                    foreach ($hotel_id_arr as $hid) {
                        $field = 'sdv.oss_addr apurl,sdv.md5 md5 ';
                        $apk_device_type = 2;
                        $apk_info =$m_version_upgrade->getLastOneByDeviceNew($field, $apk_device_type, $hid);
                        //$apk_info = $m_version_upgrade->getLastOneByDevice($field, $apk_device_type, $hid);
                        if($apk_info) {
                            $flag = 0;
                            $apk_filename = $gendir.'.apk';
                            $afilename = $savor_path .DIRECTORY_SEPARATOR. $apk_filename;
                            for($ai=0;$ai<2;$ai++) {
                                $aliyun->getObjectToLocalFile($apk_info['apurl'], $afilename);
                                ob_start();
                                readfile($afilename);
                                $md5file= ob_get_contents();
                                ob_end_clean();
                                $down_md5 = md5($md5file);

                                if($down_md5 == $apk_info['md5']) {
                                    $flag = 1;
                                    break;
                                } else {
                                    $tpic = '创建' . $afilename . '失败' . PHP_EOL;
                                    error_log($tpic, 3, $pic_err_log);
                                    if (file_exists($afilename))
                                    {

                                        unlink($afilename);
                                    }

                                    continue;
                                }
                            }
                            if($flag == 1) {
                                break;
                            }

                        } else {
                            continue;
                        }

                    }




                    $end_time = microtime(true);
                    echo '循环执行时间为：'.($end_time-$start_time).' s';

                    $start_time = microtime(true);
                    foreach ( $hotel_id_arr as $hv) {
                        $hotel_path = $savor_path.DIRECTORY_SEPARATOR.$hv;
                        if ( $smfileModel->create_dir($hotel_path) ) {
                            //echo '创建目录'.$hotel_path.'成功'.PHP_EOL;
                            $adv_path = $hotel_path.DIRECTORY_SEPARATOR.'adv';
                            if ( $smfileModel->create_dir($adv_path) ) {
                                //  echo '创建目录'.$adv_path.'成功'.PHP_EOL;
                                //创建json文件
                                $play_file = $hotel_path.DIRECTORY_SEPARATOR.'play_list.json';
                                if ( $smfileModel->create_file($play_file, true) ) {
                                    $info = '';
                                    // echo '创建JSON文件'.$play_file.'成功'.PHP_EOL;
                                    //获取酒楼对应节目单

                                    $info = $this->getHotelMedia($hv, $gendir, $vol);

                                    if ( !empty($info) ) {

                                        if(!empty($info['logourl'])) {
                                            //写入酒店目录图片
                                            $img_url = $info['logourl'];
                                            $img_filename = $info['logo_name'];
                                            $img_path = $savor_path.DIRECTORY_SEPARATOR.$hv;

                                            $new_img = $img_path.DIRECTORY_SEPARATOR.$img_filename;
                                            $oldmd5 = $info['lomd5'];
                                            for($ai=0;$ai<2;$ai++) {
                                                $aliyun->getObjectToLocalFile($img_url, $new_img);
                                                $md5file = file_get_contents($new_img);
                                                $down_md5 = md5($md5file);
                                                if ($down_md5 == $oldmd5) {
                                                    break;

                                                } else {
                                                    if (file_exists($new_img)) {
                                                        $tpic = '创建' . $gid . '的图片' . $new_img . '失败' . PHP_EOL;
                                                        error_log($tpic, 3, $pic_err_log);

                                                        unlink($new_img);
                                                    }
                                                    continue;
                                                }
                                            }

                                        }
                                        $smfileModel->write_file($play_file, $info['res']);


                                    }

                                    //写入update.cfg
                                    $update_path = $hotel_path.DIRECTORY_SEPARATOR.'update.cfg';
                                    $upd_str = '';

                                    foreach($update_config_cfg as $cfgk=>$cfgv) {

                                        if( in_array($cfgk, $upcfg) ) {
                                            $upd_str .= $cfgv['ename']."\n";
                                        } else {
                                            $upd_str .= '#'.$cfgv['ename']."\n";
                                        }
                                    }

                                    $smfileModel->write_file($update_path, $upd_str);

                                } else {
                                    echo '创建JSON文件'.$play_file.'失败'.PHP_EOL;
                                }
                            } else {
                                echo '创建目录'.$adv_path.'失败'.PHP_EOL;
                            }
                        } else {
                            echo '创建目录'.$hotel_path.'失败'.PHP_EOL;
                        }

                    }

                } else {
                    echo '创建目录'.$savor_path.'失败'.PHP_EOL;
                }
                //ob_clean();

                $zip=new \ZipArchive();
                //var_export($zip);
                $po_th = iconv("utf-8", "GB2312//IGNORE", $po_th);
                $pzip = $po_th.'.zip';
                $zflag = $zip->open($pzip, \ZipArchive::CREATE);
                if ($zflag) {
                    $this->addtoZip($po_th, $zip, $pubic_path);
                    $zip->close(); //关闭处理的zip文件
                    // echo '创建压缩包'.$gendir.'成功'.PHP_EOL;
                    //修改状态值为0
                    $signle_Model->updateInfo(array('id'=>$sv['id']), array('state'=>1,'update_time'=>$now_date));
                    echo '状态值'.$gendir.'修改成功'.PHP_EOL;
                } else {
                    // var_export($zip);
                    echo '创建压缩包失败';
                }
                // $end_time = microtime(true);
                //echo '循环执行时间为：'.($end_time-$start_time).' s';
            }
        } else {
            echo '数据已执行完毕';
        }
    }

    public function addtoZip($path, $zip, $pubic_path) {
        //print_r($path);
        // echo '<hr/>';
        $handler=opendir($path);
        while ( ($filename=readdir($handler))!==false ) {
            if($filename != "." && $filename != ".."){

                $real_filename = $path.DIRECTORY_SEPARATOR.$filename;
                //var_dump($filename);
                // var_dump($real_filename);
                //echo '<hr/><hr/>';
                if(is_dir($real_filename)){
                    if ( count(scandir($real_filename)) ==2 ){
                        //是空目录
                        $rpname = str_replace($pubic_path.DIRECTORY_SEPARATOR, '', $real_filename);
                        $zip->addEmptyDir($rpname);
                    } else {
                        $this->addtoZip($real_filename, $zip, $pubic_path);
                    }

                }else{
                    //将文件加入zip对象
                    $real_filename = iconv("utf-8", "GB2312//IGNORE", $real_filename);
                    $zip->addFile($real_filename);
                    $rpname = str_replace($pubic_path.DIRECTORY_SEPARATOR, '', $real_filename);
                    $zip->renameName($real_filename, $rpname);
                }
            }
        }
        @closedir($path);
    }
}