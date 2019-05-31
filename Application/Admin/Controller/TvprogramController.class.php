<?php
namespace Admin\Controller;
/**
 * @desc 电视节目
 *
 */
class TvprogramController extends BaseController {

    public function programlist(){
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_tvprogram = new \Admin\Model\TvprogramModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_tvprogram->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];

        $this->assign('type',$type);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function programadd(){
        $type = I('type',0,'intval');
        if(IS_GET){
            $this->assign('type',$type);
            $this->display('programadd');
        }else{
            $tvfile = I('post.tvfile','');
            if(empty($tvfile)){
                $this->output('上传文件不能为空', 'tvprogram/programadd',2,0);
            }
            $file = SITE_TP_PATH.$tvfile;
            if(!file_exists($file)){
                $this->output('文件不存在,请重新上传', 'tvprogram/programadd',2,0);
            }
            $file_data = array();
            $handle=fopen($file,"r");
            $hotel_id = 0;
            while($data=fgetcsv($handle,1000,",")){
                $data = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');//防止乱码
                if(!$hotel_id && isset($data[3])){
                    $hotel_id = $data[3];
                }
                $file_data[$data[0]] = $data;
            }
            fclose($file);
            $m_tvprogram = new \Admin\Model\TvprogramModel();
            $where = array('hotel_id'=>$hotel_id,'type'=>$type);
            $res_list = $m_tvprogram->getDataList('*',$where,'id desc');
            if(empty($res_list)){
                $this->output('请先上传电视节目', 'tvprogram/programadd',2,0);
            }
            foreach ($res_list as $v){
                $raw_number = $v['raw_number'];
                $where = array('id'=>$v['id']);
                if(!array_key_exists($raw_number,$file_data)){
                    $res = $m_tvprogram->delData($where);
                }else{
                    $nowinfo = $file_data[$raw_number];
                    $nowdata = array('raw_number'=>$nowinfo[0],'channel_name'=>$nowinfo[1],'play_number'=>$nowinfo[2],'hotel_id'=>$nowinfo[3]);
                    $res = $m_tvprogram->updateData($where,$nowdata);
                }
            }
            if($res){
                $this->output('操作成功!', 'tvprogram/programlist');
            }else{
                $this->output('操作失败!', 'tvprogram/programlist');
            }

        }
    }

    public function programlock(){
        $id = I('get.id',0,'intval');
        $lock = I('get.lock',0,'intval');
        if($lock==0){
            $is_lock = 1;
        }else{
            $is_lock = 0;
        }
        $m_tvprogram = new \Admin\Model\TvprogramModel();
        $result = $m_tvprogram->updateData(array('id'=>$id),array('is_lock'=>$is_lock));
        if($result){
            $this->output('操作成功!', 'tvprogram/programlist',2);
        }else{
            $this->output('操作失败', 'tvprogram/programlist',2,0);
        }
    }
}