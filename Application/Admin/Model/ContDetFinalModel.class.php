<?php
/**
 * @author zhang.yingtao
 * @desc 内容与推广数据
 */
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class ContDetFinalModel extends Model
{
    protected $tableName='content_details_final';
    public function getDataList($where, $order='id desc', $start=0,$size=5){
        $list = $this->field("*,sum(read_count) as s_read_count,sum(read_duration) as s_read_duration,
                              sum(demand_count) as s_demand_count, sum(share_count) as s_share_count,
                              sum(pv_count) as s_pv_count,sum(uv_count) as s_uv_count,sum(click_count) as s_click_count,
                              sum(outline_count) s_outline_count")
                     ->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->group('content_id')->select();
        
		/* $count = $this->where($where)
					  ->group('content_id')->limit()->count(); */
        $sql ="SELECT content_id FROM `savor_content_details_final` where $where  GROUP BY content_id ";
        
        $ret = $this->query($sql);
        
		$count = count($ret);
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    public function getAllList($where,$order){
        $list = $this->where($where)
        ->order($order)
        ->select();
        return $list;
    }
    public function getAll($where, $order='id desc'){
        $list = $this->field("*,sum(read_count) as s_read_count,sum(read_duration) as s_read_duration,
                              sum(demand_count) as s_demand_count, sum(share_count) as s_share_count,
                              sum(pv_count) as s_pv_count,sum(uv_count) as s_uv_count,sum(click_count) as s_click_count,
                              sum(outline_count) s_outline_count")
                                      ->where($where)
                                      ->order($order)
                                      
                                      ->group('content_id')->select();
        return $list;
    }
}
