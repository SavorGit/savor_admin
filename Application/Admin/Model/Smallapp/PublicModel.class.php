<?php
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class PublicModel extends Model{
	protected $tableName='smallapp_public';
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
	    $data = $this->field($fields)->where($where)->order($order)->group($group)->limit($limit)->select();
	    return $data;
	}
	public function getOne($fields,$where,$order){
	    $data =  $this->field($fields)->where($where)->order($order)->find();
	    return $data;
	}
	public function countNum($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_smallapp_user user on a.openid= user.openid','left')
            	     ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    $count = $this->alias('a')
	                  ->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}

	public function cronforscreenPublicnums(){
        $fields = 'id,forscreen_id';
        $where = array('res_nums'=>0);
	    $res_list = $this->field($fields)->where($where)->select();
	    if(!empty($res_list)){
	        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            foreach ($res_list as $v){
                $field = 'count(id) as num';
                $res_forscreen_num = $m_forscreen->getOne($field,array('forscreen_id'=>$v['forscreen_id']));
                if(!empty($res_forscreen_num)){
                    $res_nums = $res_forscreen_num['num'];
                    $res = $this->updateInfo(array('id'=>$v['id']),array('res_nums'=>$res_nums));
                    if($res){
                        echo "id: {$v['id']} ok \r\n";
                    }else{
                        echo "id: {$v['id']} error \r\n";
                    }
                }
            }
        }else{
	        echo "no data \r\n";
        }
    }

    public function handle_widthheight(){
        $start_time = date('Y-m-d 00:00:00',strtotime('-1 day'));
        $end_time = date('Y-m-d 23:59:59',strtotime('-1 day'));
        $where = array('status'=>array('in',array(1,2,3)));
        $where = array();
        $where['create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
	    $res_data = $this->getWhere('*',$where,'id desc','','');
	    if(!empty($res_data)){
            $host_name = C('OSS_HOST_NEW');
	        $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
            foreach ($res_data as $v){
                $res_type = $v['res_type'];//1图片2视频
                $where = array('forscreen_id'=>$v['forscreen_id']);
                $res_pdetail = $m_pubdetail->getWhere('*',$where,'id asc','','');
                foreach ($res_pdetail as $pv){
                    $file_path = $pv['res_url'];
                    if($res_type==1){
                        $url = "http://$host_name/$file_path?x-oss-process=image/info";
                        $res = '';
                        $http_curl = new \Common\Lib\Curl();
                        $http_curl::get($url,$res);
                        if(!empty($res)){
                            $res_img = json_decode($res,true);
                            $width = $res_img['ImageWidth']['value'];
                            $height = $res_img['ImageHeight']['value'];
                            $up_data = array('width'=>$width,'height'=>$height);
                            $m_pubdetail->updateInfo(array('id'=>$pv['id']),$up_data);
                            echo "pubdetail_id: {$pv['id']} image width:$width height:$height ok \r\n";
                        }
                    }else{
                        $url = "http://$host_name/$file_path?x-oss-process=video/snapshot,t_1000,f_jpg,m_fast,ar_auto";
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        $info = curl_exec($curl);
                        curl_close($curl);
                        $file_info = pathinfo($file_path);
                        $newFileName = SITE_TP_PATH.'/Public/content/img/'.$file_info['filename'].'.jpg';
                        $fp2 = @fopen($newFileName, "a");
                        fwrite($fp2, $info);
                        fclose($fp2);
                        $res_imgsize = getimagesize($newFileName);

                        $width = $res_imgsize[0];
                        $height = $res_imgsize[1];
                        $up_data = array('width'=>$width,'height'=>$height);
                        $m_pubdetail->updateInfo(array('id'=>$pv['id']),$up_data);
                        echo "pubdetail_id: {$pv['id']} video width:$width height:$height ok \r\n";
                    }
                }
            }
        }

    }
}