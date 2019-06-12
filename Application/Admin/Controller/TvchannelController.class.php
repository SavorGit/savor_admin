<?php
namespace Admin\Controller;
/**
 * @desc 电视频道
 *
 */
class TvchannelController extends BaseController {

    public function channellist(){
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_tvchannel = new \Admin\Model\TvchannelModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_tvchannel->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];

        $this->assign('type',$type);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function channeladd(){
        $type = I('type',0,'intval');
        if(IS_GET){
            $this->assign('type',$type);
            $this->display('channeladd');
        }else{
            $tvfile = I('post.tvfile','');
            if(empty($tvfile)){
                $this->output('上传文件不能为空', 'tvchannel/channeladd',2,0);
            }
            $file = SITE_TP_PATH.$tvfile;
            if(!file_exists($file)){
                $this->output('文件不存在,请重新上传', 'tvchannel/channeladd',2,0);
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
            $m_channel = new \Admin\Model\TvchannelModel();
            $where = array('hotel_id'=>$hotel_id,'type'=>$type);
            $res_list = $m_channel->getDataList('*',$where,'id desc');
            if(empty($res_list)){
                $this->output('请先上传电视节目', 'tvchannel/channeladd',2,0);
            }
            foreach ($res_list as $v){
                $raw_number = $v['raw_number'];
                $where = array('id'=>$v['id']);
                if(!array_key_exists($raw_number,$file_data)){
                    $res = $m_channel->delData($where);
                }else{
                    $nowinfo = $file_data[$raw_number];
                    $nowdata = array('raw_number'=>$nowinfo[0],'channel_name'=>$nowinfo[1],'play_number'=>$nowinfo[2],'hotel_id'=>$nowinfo[3]);
                    $res = $m_channel->updateData($where,$nowdata);
                }
            }
            if($res){
                $this->output('操作成功!', 'tvchannel/channellist');
            }else{
                $this->output('操作失败!', 'tvchannel/channellist');
            }
        }
    }

    public function channellock(){
        $id = I('get.id',0,'intval');
        $hotel_id = I('get.hotelid',0,'intval');
        $type = I('get.type',0,'intval');
        $lock = I('get.lock',0,'intval');
        if($lock==0){
            $is_lock = 1;
        }else{
            $is_lock = 0;
        }
        if($is_lock){
            $m_channel = new \Admin\Model\TvchannelModel();
            $res_channel = $m_channel->getInfo(array('hotel_id'=>$hotel_id,'type'=>$type,'is_lock'=>1));
            if(!empty($res_channel)){
                $this->output('只能锁定1个节目', 'tvchannel/channellist',2,0);
            }
        }

        $result = $m_channel->updateData(array('id'=>$id),array('is_lock'=>$is_lock));
        if($result){
            $this->output('操作成功!', 'tvchannel/channellist',2);
        }else{
            $this->output('操作失败', 'tvchannel/channellist',2,0);
        }
    }
}