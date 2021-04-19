<?php
namespace Admin\Model;
use Common\Lib\Page;

class MediaStaModel extends BaseModel{
	protected $tableName='medias_sta';

	public function getWhere($where, $field,$group=''){
		$list = $this->where($where)
			->field($field)
			->group($group)
			->select();
		return $list;
	}

	public function getAdvMachine($where, $field,$group=''){
		$list = $this->alias('sms')
			->join('savor_box sbo on sbo.mac=sms.mac')
			->where($where)
			->field($field)
			->group($group)
			->select();
		return $list;
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

	public function handle_proplaynum(){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(14);
        $cache_key = 'box:play:*';
        $res = $redis->keys($cache_key);
        $media_ids = array();
        $pro_time = strtotime(date('Y-01-01 00:00:00'));
        foreach ($res as $v){
            $play_data = $redis->get($v);
            if(!empty($play_data)){
                $data = json_decode($play_data,true);
                $menu_num = $data['menu_num'];
                $menu_time = intval($menu_num/10000);
                if($menu_time>$pro_time){
                    foreach ($data['list'] as $dv){
                        if($dv['type']=='pro'){
                            $media_ids[$dv['media_id']] = $dv['media_id'];
                        }
                    }
                }
            }
        }
        $pro_media_ids = array_values($media_ids);
        $play_date = 20210101;
        $model = M();
        $media_id_nums = array();
        $num_key = C('SAPP_HOTPLAY_PRONUM');
        foreach ($pro_media_ids as $v){
            $sql = "select sum(play_count) as play_num from savor_medias_sta where media_id={$v} and play_date>={$play_date}";
            $res_data = $model->query($sql);
            if(!empty($res_data)){
                $play_num = intval($res_data[0]['play_num']);
                $media_id_nums[$v] = $play_num;
                $now_time = date('Y-m-d H:i:s');
                echo "$now_time media_id:$v num:{$play_num} \r\n";
            }
        }
        $redis->select(5);
        $redis->set($num_key,json_encode($media_id_nums),86400*30);
    }




}