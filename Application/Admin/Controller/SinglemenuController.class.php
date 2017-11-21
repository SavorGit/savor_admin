<?php
/**
 *@desc u盘日志上报
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class SinglemenuController extends BaseController{
    
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','create_time');
       // $plan_finish_time = I('plan_finish_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        /* $yestoday = date("Y-m-d", strtotime("-1 day"));
        $where = '1=1';
        $where .= " and DATE_FORMAT(`create_time`,'%Y-%m-%d') = '".$yestoday."'";
        $black_list = new \Admin\Model\BlackListModel(); */
		$m_single_menu = new \Admin\Model\SingleMenuModel();
		$where = ' a.flag=0';
		$fields = 'a.*,user.remark as username';
        $list= $m_single_menu->getList($fields,$where,$orders,$start,$size);
        //print_r($list);exit;
		/* $m_box= new \Admin\Model\BoxModel();
        $ind = $start;
		foreach($list['list'] as $key=>$v){
		    //获取酒楼、包间、机顶盒信息
            $ind ++;
            $list['list'][$key]['num']   = $ind;
		    $hotel_info = $m_box->getHotelInfoByBoxMac($v['mac']);
		    $list['list'][$key]['hotel_name'] = $hotel_info['hotel_name'];
		    $list['list'][$key]['box_name']   = $hotel_info['box_name'];
		} */
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
		$this->display('Report/singlemenu');
		
    }   
    public function getfile(){
        $upload = new \Think\Upload();
        $upload->exts = array('xls','xlsx','xlsm','csv');
        $upload->maxSize = 2097152;
        $upload->rootPath = $this->imgup_path();
        $upload->savePath = '';
        $info = $upload->upload();
        //var_dump($info);
    
        if(empty($info['file_data'])){
            $errMsg = $upload->getError();
            $this->output($errMsg, 'importdata', 0,0);
        }
        $path = SITE_TP_PATH.'/Public/uploads/'.$info['file_data']['savepath'].$info['file_data']['savename'];
        vendor("PHPExcel.PHPExcel.IOFactory");
        //echo $path;
        $ret[] = $path;
        echo json_encode($ret);
        die;
    }
    
    public function add(){
        $this->display('Report/addsinglemenu');
    }
    public function doadd(){
        
        $adsModel = new \Admin\Model\AdsModel();
        //$path = $_POST['singlefile'];
        $path = I('post.singlefile');
        $name = I('post.name');
        if  ($path == '') {
            $this->error('上传文件不能为空');
        }
        
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        vendor("PHPExcel.PHPExcel.IOFactory");
        
        if ($type == 'xlsx' || $type == 'xls') {
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
        } elseif ($type == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
            ->setDelimiter(',')
            ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
            ->setEnclosure('"')
            ->setLineEnding("\r\n")
            ->setSheetIndex(0);
            $objPHPExcel = $objReader->load($path);
        } else {
            $this->error('上传文件不能为空');
            //$this->output('文件格式不正确', 'importdata', 0, 0);
        }
        
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        // var_dump($highestRowNum, $highestColumn, $highestColumnNum);
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
        // var_dump($filed);
        
        //开始取出数据并存入数组
        $data = array();
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = $sheet->getCell($cellName)->getValue();
                $row[$filed[$j]] = $cellVal;
            }
            $data [] = $row;
        }
        if(empty($data)){
            $this->error('文件内容不能为空');
        }
        
        
        $userInfo = session('sysUserInfo');
        $loginId = $userInfo['id'];
        $ret = array();
        $ret['name'] = $name;
        $ret['creator_id'] = $loginId;
        $m_single_menu = new \Admin\Model\SingleMenuModel();
        $sigle_menu_id  =$m_single_menu->addInfo($ret);
        if($sigle_menu_id){
            $m_single_menu_item = new \Admin\Model\SingleMenuItemModel();
            foreach($data as $key=>$v){
                $data[$key]['single_menu_id'] = $sigle_menu_id;
            }
            $m_single_menu_item->addInfos($data);    
        }else {
            $this->error('保存失败');
        }
        
        $this->output('单机版节目单上传成功', 'singlemenu/index', 1);
    }
    public function detail(){
        
        
        $id = I('get.id',0,'intval');
        $where = '';
        $orders = ' id asc';
        $m_single_menu_item = new \Admin\Model\SingleMenuItemModel();
        $list =$m_single_menu_item->getlist($where,$orders);
        $this->assign('list',$list);
        $this->display('Report/sinmedetail');
    }
    public function delete(){
        $id = I('get.id',0,'intval');
        $where['id'] = $id;
        $m_single_menu = new \Admin\Model\SingleMenuModel();
        $data['flag'] = 1;
        $ret = $m_single_menu->where($where)->save($data);
        
        if($ret){
            $this->output('删除成功', 'singlemenu/index', 2);
        }else {
            $this->error('删除失败');
        }
    }
}