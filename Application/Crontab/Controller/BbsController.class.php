<?php
namespace Crontab\Controller;
use Think\Controller;

class BbsController extends Controller{

    public function updatehotlist(){
        $now_time = date('Y-m-d H:i:s');
        echo "updatehotlist start:$now_time \r\n";

        $m_bbscontent = new \Admin\Model\BbsContentModel();
        $res_content = $m_bbscontent->getDataList('*',array('is_hot'=>0),'id asc');
        if(empty($res_content)){
            echo "no data \r\n";
        }
        $category_ids = array();
        foreach ($res_content as $v){
            $hot_num = $v['view_num']*1 + $v['like_num']*2 + $v['comment_num']*5 + $v['collect_num']*5;
            $m_bbscontent->updateData(array('id'=>$v['id']),array('hot_num'=>$hot_num));
            $category_ids[$v['category_id']]=$v['category_id'];
        }
        $hot_start_time = date('Y-m-d 00:00:00');
        $hot_end_time = date('Y-m-d 23:59:59',strtotime("+14 day"));
        foreach ($category_ids as $v){
            $category_id = $v;
            $res_ccontent = $m_bbscontent->getAll('id',array('category_id'=>$category_id,'is_hot'=>0,'hot_num'=>array('gt',0)),0,10,'hot_num desc');
            if(!empty($res_ccontent)){
                $cids = array();
                foreach ($res_ccontent as $cv){
                    $cids[]=$cv['id'];
                }
                $m_bbscontent->updateData(array('id'=>array('in',$cids)),array('is_hot'=>1,'hot_start_time'=>$hot_start_time,'hot_end_time'=>$hot_end_time));
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "updatehotlist end:$now_time \r\n";
    }
}
