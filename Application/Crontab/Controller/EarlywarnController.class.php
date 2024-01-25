<?php
namespace Crontab\Controller;
use Think\Controller;

class EarlywarnController extends Controller{

    public $finance_user_mobile = array('18813191797'=>'庞梦迎','13910825534'=>'赵翠燕');

    public function purchasecheck(){
        $now_time = date('Y-m-d H:i:s');
        echo "purchasecheck start:$now_time \r\n";

        $not_goods_id = array(10,39);
        $error_money = 1000;
        $fields = 'p.id,p.name as pname,p.serial_number,a.goods_id,goods.name as goods_name,a.price,unit.name as unit_name';
        $where = array('a.status'=>1,'goods.brand_id'=>array('neq',11),
            'unit.convert_type'=>array('gt',1),'a.price'=>array('lt',$error_money));
        if(!empty($not_goods_id)){
            $where['goods.id'] = array('not in',$not_goods_id);
        }
        $m_purchase_detail = new \Admin\Model\FinancePurchaseDetailModel();
        $res_details = $m_purchase_detail->alias('a')
            ->join('savor_finance_purchase as p on a.purchase_id=p.id','left')
            ->join('savor_finance_goods as goods on a.goods_id=goods.id','left')
            ->join('savor_finance_unit as unit on a.unit_id=unit.id','left')
            ->field($fields)
            ->where($where)
            ->order('a.id asc')
            ->select();
        if(!empty($res_details)){
            $ids = array();
            foreach ($res_details as $v){
                $ids[]=$v['id'];
            }
            $ids_str = join(',',$ids);
            $emsms = new \Common\Lib\EmayMessage();
            $content = '热点财务后台:采购管理->采购订单管理,ID:'.$ids_str.',单价有异常,请仔细核对!';
            foreach ($this->finance_user_mobile as $k=>$v){
                $emsms->sendSMS($content,$k);
            }
            echo "content:$content \r\n";
        }
        $now_time = date('Y-m-d H:i:s');
        echo "purchasecheck end:$now_time \r\n";
    }
}