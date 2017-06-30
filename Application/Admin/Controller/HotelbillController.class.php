<?php
/**
 * @desc   酒楼对账单
 * @author zhang.yingtao
 * @since  2017-06-20
 */
namespace Admin\Controller;
use Think\Controller;
class HotelbillController extends Controller{
    /**
     * @desc 酒楼对账单
     */
    public function index(){
        //1be79d87c7f8360f
        $id = I('get.id');
        $bill_id = decrypt_data($id);
        
        $this->assign('bill_bot_exist',0);
        if(!is_numeric($bill_id)){
            $this->assign('bill_not_exist',1);
        }else {
            $bill_info = array();
            $m_account_statement_detail = new \Admin\Model\AccountStatementDetailModel();
            $bill_info = $m_account_statement_detail->getBillDetail($bill_id);
            //print_r($bill_info);exit;
            if(empty($bill_info)){
                $this->assign('bill_not_exist',1);
            }
            //if(!empty($bill_info) && $bill_info['check_status']==0){
            if(!empty($bill_info) && $bill_info['check_status']==0){
                
                    $where = $info = array();
                    $where['id'] = $bill_id;
                    $info['check_status'] = 1;
                
                
                $ret = $m_account_statement_detail->saveData($info,$where);
            }
            $bill_type_arr =  C('fee_type');
            $bill_info['cost_type'] = $bill_type_arr[$bill_info['cost_type']];
            $bill_info['receipt_tel'] = explode(',', $bill_info['receipt_tel']);
            //print_r($bill_info);exit;
            
            $this->assign('bill_id',$id);
            $this->assign('bill_info',$bill_info);
        }
        
        $this->display('index');
    }
    /**
     * @desc 发票寄回确认
     */
    public function confirmBill(){
        
        $id = I('post.id');
        $bill_id = decrypt_data($id);
        
        $data = array();
        if(!is_numeric($bill_id)){
            $data['status'] = '2';
            echo json_encode($data);
            exit;
        }
        $m_account_statement_detail = new \Admin\Model\AccountStatementDetailModel();
        $bill_info = $m_account_statement_detail->getOne($bill_id);
        if(empty($bill_info)){
            $data['status'] = 3;
            echo json_encode($data);
            exit;
        }
        $where = $info = array();
        $where['id'] = $bill_id;
        $info['check_status'] = 2;
        $ret = $m_account_statement_detail->saveData($info,$where);
        if($ret){
            $data['status'] = 1;
            echo json_encode($data);
            exit;
        }else {
            $data['status'] = 4;
            echo json_encode($data);
            exit;
        }
    }
  
}