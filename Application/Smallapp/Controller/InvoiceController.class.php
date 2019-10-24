<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
use Common\Lib\MailAuto;

/**
 * @desc 发票管理
 *
 */
class InvoiceController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function invoicelist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
    	$status = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.buy_type'=>1,'a.status'=>12);//10已下单 11支付失败 12支付成功
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        if($status){
            $where['i.status'] = $status;
        }

        $start  = ($page-1) * $size;
        $m_order  = new \Admin\Model\Smallapp\OrderModel();
        $fields = 'a.id,a.goods_id,a.price,a.amount,a.total_fee,i.status as invoice_status,i.type as invoice_type,
        i.contact,i.phone,i.address,i.email,i.id as invoice_id';
        $result = $m_order->getOrderInvoiceList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $all_invoice_status = C('INVOICE_STATUS');
        $all_invoice_type = C('INVOICE_TYPE');
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        foreach ($datalist as $k=>$v){
            $goods_info = $m_goods->getInfo(array('id'=>$v['goods_id']));
            $datalist[$k]['goods_name'] = $goods_info['name'];
            if(!empty($v['invoice_status'])){
                $invoice_status_str = $all_invoice_status[$v['invoice_status']];
            }else{
                $invoice_status_str = '';
            }
            if(!empty($v['invoice_status'])){
                $invoice_type_str = $all_invoice_type[$v['invoice_type']];
            }else{
                $invoice_type_str = '';
            }
            $datalist[$k]['invoice_status_str'] = $invoice_status_str;
            $datalist[$k]['invoice_type_str'] = $invoice_type_str;
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('status',$status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('invoicelist');
    }

    public function editinvoice(){
        $id = I('id',0,'intval');
        $m_orderinvoice = new \Admin\Model\Smallapp\OrderinvoiceModel();
        $res_orderinvoice = $m_orderinvoice->getInfo(array('id'=>$id));
        if(IS_POST){
            $type = I('post.type',1,'intval');//1纸质发票 2电子发票
            $contact = I('post.contact','','trim');
            $phone = I('post.phone','','trim');
            $address = I('post.address','','trim');
            $email = I('post.email','','trim');
            $invoice_addr = I('post.invoice_addr','','trim');
            $status = I('post.status',0,'intval');//1暂不开票 2已提交开具发票申请 3待开发票 4发票已开

            $is_sendmail = 0;
            if($type==1){
                if(empty($address)){
                    $this->output('联系地址不能为空', 'invoice/invoicelist',2,0);
                }
            }else{
                if(empty($email)){
                    $this->output('邮箱不能为空', 'invoice/invoicelist',2,0);
                }
                if($status==4 && empty($invoice_addr)) {
                    $this->output('发票地址不能为空', 'invoice/invoicelist', 2, 0);
                }
                $is_sendmail = 1;
            }
            $data = array('status'=>$status,'type'=>$type,'contact'=>$contact,'phone'=>$phone,'address'=>$address,
                'email'=>$email,'invoice_addr'=>$invoice_addr,'update_time'=>date('Y-m-d H:i:s'));
            $m_orderinvoice->updateData(array('id'=>$id),$data);
            if($is_sendmail){
                $title = '小热点电子发票';
                $body = '您申请的发票已开具成功，可随时下载。发票链接：'.$invoice_addr;

                $mail_config =  C('SEND_MAIL_CONF');
                $mail_config =  $mail_config['littlehotspot'];
                $ma_auto = new MailAuto();
                $mail = new \Mail\PHPMailer();
                $mail->CharSet = "UTF-8";
                $mail->IsSMTP();
                $mail->Host = $mail_config['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $mail_config['username'];
                $mail->Password = $mail_config['password'];
                $mail->Port=25;
                $mail->From = $mail_config['username'];
                $mail->FromName = $title;

                $mail->AddAddress("$email");
                $mail->IsHTML(true);

                $mail->Subject = $title;
                $mail->Body = $body;
                if(!$mail->Send()){
                    $this->output('邮件发送失败,错误原因:'.$mail->ErrorInfo, 'invoice/invoicelist');
                }
            }
            $this->output('操作完成', 'invoice/invoicelist');

        }else{
            $this->assign('vinfo',$res_orderinvoice);
            $this->display('editinvoice');
        }
    }

}