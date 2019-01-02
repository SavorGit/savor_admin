<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 排除盒子
 *
 */
class PositionexcludeController extends BaseController {

    public function positionlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $start = ($pageNum-1)*$size;

        $area_id = I('area_id',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $m_boxexclude = new \Admin\Model\Smallapp\BoxexcludeModel();
        $where = array();
        if($area_id){
            $where['area_id'] = $area_id;
        }
        if($maintainer_id){
            $where['maintainer_id'] = $maintainer_id;
        }
        $order = 'id desc';
        $res_list = $m_boxexclude->getList('*',$where,$order,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_box = new \Admin\Model\BoxModel();
            foreach ($res_list['list'] as $v){
                $binfo = $m_box->getHotelInfoByBoxMac($v['box_mac']);
                $v['hotel_name'] = $binfo['hotel_name'];
                $v['room_name'] = $binfo['room_name'];
                $data_list[] = $v;
            }
        }

        //地区
        $m_area_info = new \Admin\Model\AreaModel();
        $area_list = $m_area_info->getAllArea();

        //合作维护人
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id main_id,user.remark ';
        $map['state']   = 1;
        $map['role_id']   = 1;
        $user_list = $m_opuser_role->getAllRole($fields,$map,'' );

        $this->assign('area_id',$area_id);
        $this->assign('maintainer_id',$maintainer_id);
        $this->assign('areas',$area_list);
        $this->assign('maintainers',$user_list);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->display();
    }

    public function positionadd(){
        if(IS_POST){
            $mac_addr = I('post.mac_addr','','trim');
            $exclude_reason = I('post.exclude_reason','','trim');
            $m_box = new \Admin\Model\BoxModel();
            $macbox_info = $m_box->getHotelInfoByBoxMac($mac_addr);
            if(empty($macbox_info) || empty($macbox_info['hotel_id']) || empty($macbox_info['room_id'])){
                $this->output('机顶盒信息有误', 'boxexclude/addexclude',2,0);
            }
            $m_ebox = new \Admin\Model\Smallapp\BoxexcludeModel();
            $res = $m_ebox->getOne('id',array('box_mac'=>$mac_addr));
            if(!empty($res)){
                $this->output('版位MAC已添加,请勿重复添加', 'boxexclude/addexclude',2,0);
            }

            $data = array('area_id'=>$macbox_info['area_id'],'hotel_id'=>$macbox_info['hotel_id'],'box_mac'=>$mac_addr,
                'exclude_reason'=>$exclude_reason,'maintainer_id'=>'');
            $m_boxexclude = new \Admin\Model\Smallapp\BoxexcludeModel();
            $result = $m_boxexclude->addInfo($data);
            if($result){
                $this->output('操作成功!', 'boxexclude/excludelist');

            }else{
                $this->output('操作失败', 'boxexclude/addexclude',2,0);
            }

        }else{
            $this->display();
        }
    }



    public function positiondel(){
        $this->output('更新成功!', 'positionexclude/positionlist',2);
//        $this->output('删除成功', 'positionexclude/positionlist');

    }
}