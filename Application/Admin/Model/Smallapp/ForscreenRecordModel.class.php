<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Aliyun;
use Common\Lib\Page;

class ForscreenRecordModel extends BaseModel
{
	protected $tableName='smallapp_forscreen_record';
	
    public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	        
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
	public function getWhere($fields,$where,$order,$limit,$group){
	    $data = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)->where($where)
	                 ->order($order)->limit($limit)
	                 ->group($group)->select();
	    return $data;
	}
	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}
	public function countWhere($where,$group){
	    $nums = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->where($where)
	                 ->group($group)
	                 ->count();
	    return $nums;
	}

	public function countHdintegralUserNum($where){
        $fields = 'count(DISTINCT(a.openid)) as num';
        $res_num = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on box.room_id=room.id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->select();
        $nums = 0;
        if(!empty($res_num)){
            $nums = $res_num[0]['num'];
        }
        return $nums;
    }

    public function countHdintegralNum($where){
        $fields = 'count(a.id) as num';
        $res_num = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on box.room_id=room.id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->select();
        $nums = 0;
        if(!empty($res_num)){
            $nums = $res_num[0]['num'];
        }
        return $nums;
    }

    public function countHdintegralUser($where,$num){
        $fields = 'a.openid,count(a.id) as num';
        $res_num = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on box.room_id=room.id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->group('a.openid')
            ->select();
        $user_num=0;
        foreach ($res_num as $v){
            if($v['num']>=$num){
                $user_num++;
            }
        }
        return $user_num;
    }


	public function getStaticList($fields,$where,$order,$group,$start,$size,$pageNum,$area_id){
	    $list = $this->alias('a')
            	     ->join('savor_box box on a.box_mac=box.mac','left')
            	     ->join('savor_room room on box.room_id=room.id','left')
            	     ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            	     ->join('savor_area_info area on hotel.area_id= area.id','left')
            	     ->field($fields)
            	     ->where($where)
	                 ->group($group)
	                 ->limit($start,$size)
	                 ->select();
	    $hotel_list = $this->alias('a')
                    	   ->join('savor_box box on a.box_mac=box.mac','left')
                    	   ->join('savor_room room on box.room_id=room.id','left')
                    	   ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
                    	   ->join('savor_area_info area on hotel.area_id= area.id','left')
                    	   ->field('hotel.id hotel_id')
                    	   ->where($where)
                    	   ->group($group)
                    	   ->select();
	    $forscreen_hotel_arr = array();
	    foreach($hotel_list as $v){
	        $forscreen_hotel_arr[] = $v['hotel_id'];
	    }
	    
	    $all_count = count($forscreen_hotel_arr);   //投屏互动数据总数
	    $nt_page = ceil($all_count/$size);
	    
	    if($pageNum>=$nt_page){
	        $h_start = ($pageNum-$nt_page);
	        $h_size  = $size - count($list);
	    }
	    $m_hotel = new \Admin\Model\HotelModel();
	    $map = array();
	    if($area_id){
	        $map['a.area_id'] = $area_id;
	    }
	    $map['a.state'] = 1;
	    $map['a.flag']  = 0;
	    if($forscreen_hotel_arr) $map['a.id']    = array('not in',$forscreen_hotel_arr);
	    
	    $heart_hotel_box_type = C('heart_hotel_box_type');
	    
	    $net_box_arr = array_keys($heart_hotel_box_type);
	    $map['a.hotel_box_type'] = array('in',$net_box_arr);
	    
	    $h_list = $m_hotel->alias('a')
	            ->join('savor_area_info area on a.area_id=area.id','left')
	            ->field('a.id hotel_id,area.region_name ,a.name hotel_name,1 as `tstype`')
	            ->where($map)
	            ->limit($h_start,$h_size)
	            ->select();
	    $list = array_merge($list,$h_list);
	    $ret = $m_hotel->alias('a')
	            ->join('savor_area_info area on a.area_id=area.id','left')
	            ->field('a.id hotel_id,area.region_name ,a.name hotel_name')
	            ->where($map)
	            
	            ->select();
	    
	    $count = count($ret);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function getHdCountPerson($where,$group){
	    $ret = $this->alias('a')
	         ->join('savor_box box on a.box_mac=box.mac','left')
             ->join('savor_room room on box.room_id=room.id','left')
             ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
             ->join('savor_area_info area  on hotel.area_id=area.id','left')
             ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
             ->join('savor_sysuser user on ext.maintainer_id= user.id','left')
             ->join('savor_smallapp_user suser on a.openid=suser.openid','left')
             ->field('a.id')
             ->where($where)
             ->group($group)
             
             ->select();
	    $count = count($ret);
	    return $count;
	}

	public function getFileMd5($forscreen_id){
        $res_forscreen = $this->getInfo(array('forscreen_id'=>$forscreen_id));
        $imgs = json_decode($res_forscreen['imgs'],true);
        $oss_addr = $imgs[0];
        $md5_file = $res_forscreen['md5_file'];
        $file_size = 0;
        $is_eq = 0;
        if(!empty($oss_addr)){
            $accessKeyId = C('OSS_ACCESS_ID');
            $accessKeySecret = C('OSS_ACCESS_KEY');
            $endpoint = 'oss-cn-beijing.aliyuncs.com';
            $bucket = C('OSS_BUCKET');
            $aliyunoss = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
            $aliyunoss->setBucket($bucket);

            $res_object = $aliyunoss->getObjectMeta($oss_addr);
            if(isset($res_object['content-length']) && $res_object['content-length']>0 && isset($res_object['oss-request-url'])){
                $tmp_file = explode("$endpoint/",$res_object['oss-request-url']);
                if($tmp_file[1]==$oss_addr){
                    $file_size = $res_object['content-length'];
                }
            }
            if($file_size==$res_forscreen['resource_size']){
                $is_eq = 1;
            }
            if($is_eq==0){
                $oss_filesize = $file_size;
                $range = '0-199';
                $bengin_info = $aliyunoss->getObject($oss_addr,$range);
                $last_range = $oss_filesize-199;
                $last_size = $oss_filesize-1;
                $last_range = $last_size - 199;
                $last_range = $last_range.'-'.$last_size;
                $end_info = $aliyunoss->getObject($oss_addr,$last_range);
                if(!empty($bengin_info) && !empty($end_info)){
                    $file_str = md5($bengin_info).md5($end_info);
                    $fileinfo = strtoupper($file_str);
                    $md5_file = md5($fileinfo);
                }else{
                    $md5_file = '';
                }

            }
        }
        $res = array('db_size'=>$res_forscreen['resource_size'],'oss_size'=>$file_size,'is_eq'=>$is_eq,'md5_file'=>$md5_file);
        return $res;
    }

    public function handel_forscreen_helpvideo($type=0){
        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }
        if(!empty($res_box['result'])){
            $m_box = new \Admin\Model\BoxModel();
            $fields = 'box.mac,hotel.id as hotel_id';
            $where = array('box.state'=>1,'box.flag'=>0);
            $all_hotel_ids = C('SAMPLE_HOTEL');
            $all_hotel_ids = $all_hotel_ids['236'];
            if($type==1){
                $all_hotel_ids = array();
            }
            $all_hotel_ids[]=7;
            $all_hotel_ids[]=883;

            $where['hotel.id'] = array('in',$all_hotel_ids);
            $res_bdata = $m_box->getBoxByCondition($fields,$where,$group='');
            $all_boxs = array();
            foreach ($res_bdata as $v){
                $all_boxs[]=$v['mac'];
            }

            if($type==0){
                $fields = 'box.mac,hotel.id as hotel_id';
                $where = array('box.state'=>1,'box.flag'=>0,'box.is_4g'=>1,'hotel.area_id'=>236);
                $where['hotel.id'] = array('not in',$all_hotel_ids);
                $res_bdata = $m_box->getBoxByCondition($fields,$where,$group='');
                foreach ($res_bdata as $v){
                    $all_boxs[]=$v['mac'];
                }
            }

            $netty_data = array('action'=>134,'resource_type'=>2,'url'=>'media/resource/h8YcE7debZ.mp4','filename'=>"h8YcE7debZ.mp4");
            $message = json_encode($netty_data);
            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if(!in_array($box_mac,$all_boxs)){
                            continue;
                        }
                        $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                        $req_id  = getMillisecond();
                        $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                        $post_data = http_build_query($box_params);
                        $ret = $m_netty->curlPost($push_url,$post_data);
                        $res_push = json_decode($ret,true);
                        if($res_push['code']==10000){
                            echo "box_mac:$box_mac push ok \r\n";
                        }else{
                            echo "box_mac:$box_mac push error $ret  \r\n";
                        }

                    }
                }

            }
        }
    }

    public function handel_forscreenimg($type=0){
        $hour = date('H');
        if(!in_array($hour,array(11,12,13,14,17,18,19,20,21,22))){
            $now_time = date('Y-m-d H:i:s');
            echo "$now_time hour $hour error\r\n";
            exit;
        }
        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }
        if(!empty($res_box['result'])){
            $m_box = new \Admin\Model\BoxModel();
            $fields = 'box.mac,hotel.id as hotel_id';
            $where = array('box.state'=>1,'box.flag'=>0);
//            $all_hotel_ids = C('SAMPLE_HOTEL');
//            $all_hotel_ids = $all_hotel_ids['236'];
//            if($type==1){
//                $all_hotel_ids = array();
//            }
            $all_hotel_ids[]=7;
            $all_hotel_ids[]=883;
            $where['hotel.id'] = array('in',$all_hotel_ids);
            $res_bdata = $m_box->getBoxByCondition($fields,$where,$group='');
            $all_boxs = array();
            foreach ($res_bdata as $v){
                $all_boxs[]=$v['mac'];
            }

            $all_boxs = array();
            if($type==0){
                $fields = 'box.mac,hotel.id as hotel_id';
//                $where = array('box.state'=>1,'box.flag'=>0,'box.is_4g'=>1,'hotel.area_id'=>236);
                $where = array('box.state'=>1,'box.flag'=>0,'hotel.area_id'=>236);
//                $where['hotel.id'] = array('not in',$all_hotel_ids);
                $res_bdata = $m_box->getBoxByCondition($fields,$where,$group='');
                foreach ($res_bdata as $v){
                    $all_boxs[]=$v['mac'];
                }
            }


            $forscreen_number  = rand(1001,5000);
            $netty_data = array('action'=>133,'forscreen_number'=>$forscreen_number,'countdown'=>30);
            $message = json_encode($netty_data);
            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if(!in_array($box_mac,$all_boxs)){
                            continue;
                        }

                        $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                        $req_id  = getMillisecond();
                        $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                        $post_data = http_build_query($box_params);
                        $ret = $m_netty->curlPost($push_url,$post_data);
                        $res_push = json_decode($ret,true);
                        if($res_push['code']==10000){
                            echo "box_mac:$box_mac push ok \r\n";
                        }else{
                            echo "box_mac:$box_mac push error $ret  \r\n";
                        }
                    }
                }
            }
        }
    }
}