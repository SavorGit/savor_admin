<?php
namespace Integral\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc ���۶˻���-�����б�
 *
 */
class ServiceController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $size   = I('numPerPage',50);//��ʾÿҳ��¼��
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start  = ( $start-1 ) * $size;
        $m_service = new \Admin\Model\Integral\IntegralServiceModel();
        
        $fields = 'a.id,a.name service_name,user.remark user_name,a.create_time,a.update_time';
        $where = [];
        $where['status'] = 1;
        
        $list = $m_service->getList($fields,$where,$orders,$start,$size);
        
    }
}