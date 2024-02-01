<?php
namespace Admin\Model;
class FinanceChangepriceRecordModel extends BaseModel{

    protected $tableName='finance_changeprice_record';

    public function changestockprice(){
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $m_purchase_detail = new \Admin\Model\FinancePurchaseDetailModel();
        $m_avgprice = new \Admin\Model\FinanceGoodsAvgpriceModel();
        $where = array('status'=>array('in','0,1'));
        $where["DATE_FORMAT(add_time,'%Y-%m-%d')"] = date('Y-m-d',strtotime('-1 day'));
        $res_data = $this->getDataList('*',$where,'goods_id asc,id asc');
//        $res_data = array(
//            array('id'=>'ts9991','goods_id'=>1,'purchase_detail_id'=>0,'price'=>0),
//            array('id'=>'ts9994','goods_id'=>4,'purchase_detail_id'=>0,'price'=>0)
//        );//处理特殊情况
        foreach ($res_data as $v){
            $this->updateData(array('id'=>$v['id']),array('status'=>1));
            echo "id: {$v['id']}-{$v['goods_id']}-{$v['purchase_detail_id']} start \r\n";

            $goods_id = $v['goods_id'];
            $change_purchase_detail_id = $v['purchase_detail_id'];
            $change_price = $v['price'];

            $sql = "select a.goods_id,sum(a.total_amount) as total_num,a.price,a.stock_id,a.stock_detail_id,stock.purchase_id,detail.purchase_detail_id from savor_finance_stock_record as a 
                left join savor_finance_stock as stock on a.stock_id=stock.id
                left join savor_finance_stock_detail as detail on a.stock_detail_id=detail.id
                where a.goods_id={$goods_id} and a.type=1 and stock.io_type=11 group by a.stock_detail_id";
            $res_data = $m_stock_record->query($sql);
            $goods_data = array();
            foreach ($res_data as $gk=>$gv){
                $stock_detail_id = $gv['stock_detail_id'];

                $in_where = array('goods_id'=>$goods_id,'stock_detail_id'=>$stock_detail_id,'type'=>1);
                $res_inend = $m_stock_record->getDataList('idcode,add_time,amount,total_amount',$in_where,'id desc');
                $idcodes = array();
                foreach ($res_inend as $inv){
                    if($inv['total_amount']==$inv['amount']){
                        $idcodes[]=$inv['idcode'];
                    }else{
                        $idcodes[]=$inv['idcode'];
                        $qrcontent = decrypt_data($inv['idcode']);
                        $qr_id = intval($qrcontent);
                        $res_allqrcode = $m_qrcode_content->getDataList('id',array('parent_id'=>$qr_id),'id asc');
                        foreach ($res_allqrcode as $qrv){
                            $qrcontent = encrypt_data($qrv['id']);
                            $idcodes[]=$qrcontent;
                        }
                    }
                }
                $gv['in_time'] = $res_inend[0]['add_time'];
                $gv['idcodes'] = $idcodes;
                $goods_data[]=$gv;

                //更新savor_finance_stock_detail,savor_finance_stock_record表price
                if($change_purchase_detail_id==$gv['purchase_detail_id']){
                    $m_stock_detail->updateData(array('id'=>$stock_detail_id),array('price'=>$change_price));

                    $res_nowpd = $m_purchase_detail->getInfo(array('id'=>$change_purchase_detail_id));
                    $now_change_price = sprintf("%.2f",$res_nowpd['total_fee']/$res_nowpd['total_amount']);

                    $idcodes_str = join("','",$idcodes);
                    $change_price_sql = "update savor_finance_stock_record set price=$now_change_price*amount,total_fee=$now_change_price*total_amount where idcode in('$idcodes_str') and type!=7";
                    $m_stock_record->execute($change_price_sql);
                }
            }
            sortArrByOneField($goods_data,'in_time',false);

            $now_goods_avgprices = array();
            foreach ($goods_data as $gdk=>$gdv){
                $num = $gdv['total_num'];
                $now_idcodes = $gdv['idcodes'];

                $res_pd = $m_purchase_detail->getInfo(array('id'=>$gdv['purchase_detail_id']));
                $now_price = sprintf("%.2f",$res_pd['total_fee']/$res_pd['total_amount']);
                if($gdk==0){
                    $avg_price = ($num*$now_price)/$num;
                    $stock_num = 0;
                }else{
                    $last_total_num = $goods_data[$gdk-1]['total_num'];
                    $last_stock_idcodes = $goods_data[$gdk-1]['idcodes'];
                    $start_time = $goods_data[$gdk-1]['in_time'];
                    $end_time = $gdv['in_time'];

                    $wo_where = array('goods_id'=>$goods_id,'idcode'=>array('in',$last_stock_idcodes),'type'=>7,'wo_status'=>array('in','1,2,4'));
//                    $wo_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $wo_fields = 'sum(total_amount) as total_num';
                    $res_wo_num = $m_stock_record->getAll($wo_fields,$wo_where,0,1);
                    $wo_num = 0;
                    if(!empty($res_wo_num[0]['total_num'])){
                        $wo_num = abs($res_wo_num[0]['total_num']);
                    }
                    $re_where = array('goods_id'=>$goods_id,'idcode'=>array('in',$last_stock_idcodes),'type'=>6,'status'=>array('in','1,2'));
//                    $re_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $re_fields = 'sum(total_amount) as total_num';
                    $res_re_num = $m_stock_record->getAll($re_fields,$re_where,0,1);
                    $report_num = 0;
                    if(!empty($res_re_num[0]['total_num'])){
                        $report_num = abs($res_re_num[0]['total_num']);
                    }
                    $stock_num  = $last_total_num-$wo_num-$report_num;
                    $stock_num = $stock_num + $now_goods_avgprices[$gdk-1]['stock_num'];
                    $last_avg_price = $now_goods_avgprices[$gdk-1]['avg_price'];

                    $avg_price = ($num*$now_price+$stock_num*$last_avg_price)/($num+$stock_num);
                    $avg_price = sprintf("%.2f",$avg_price);
                }
                $now_goods_avgprices[$gdk] = array('avg_price'=>$avg_price,'purchase_detail_id'=>$gdv['purchase_detail_id'],
                    'stock_detail_id'=>$gdv['stock_detail_id'],'stock_num'=>$stock_num);
                //更新savor_finance_stock_record表中 移动平均价avg_price
                $m_stock_record->updateData(array('idcode'=>array('in',$now_idcodes),'type'=>array('neq',7)),array('avg_price'=>$avg_price));

                foreach ($now_goods_avgprices as $ngav){
                    $avg_data = array('goods_id'=>$goods_id,'stock_detail_id'=>$ngav['stock_detail_id'],
                        'purchase_detail_id'=>$ngav['purchase_detail_id'],'price'=>$ngav['avg_price']);
                    $m_avgprice->add($avg_data);
                }

                $this->updateData(array('id'=>$v['id']),array('status'=>2,'update_time'=>date('Y-m-d H:i:s')));
                echo "id: {$v['id']}-{$v['goods_id']}-{$v['purchase_detail_id']} end \r\n";
            }
        }
    }
}
