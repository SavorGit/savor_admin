<?php
namespace Crontab\Controller;
use Think\Controller;
//库龄明细表
class FinanceaccountagedetailController extends Controller{
    
    public function accountageDetail(){
        
        $end_date = date('Y-m-d',strtotime('-1 day'));
        $m_accountage_detail = new \Admin\Model\FinanceDataAccountageDetailModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $where = [];
        //$where['stock.io_type'] = 11;
        $where['a.add_time']     = array(array('elt',$end_date.' 23:59:59'));
        $where['a.dstatus']      = 1;
        
        
        
        $idcode_list = $m_stock_record->alias('a')
                       ->join('savor_finance_stock stock on a.stock_id= stock.id','left')
                       ->field('a.id,a.stock_id,a.stock_detail_id,a.idcode')
                       ->where($where)
                       ->group('a.idcode')
                       ->select();
        
        //$idcode_list = array_slice($idcode_list, 0,500);  //上线去掉***************
        //$idcode_list = array_slice($idcode_list, 0,564);
        //print_r($idcode_list);exit;
        foreach($idcode_list as $key=>$v){
            
            $field = 'stock.id,a.idcode,goods.id goods_id,goods.name goods_name';
            $map = [];
            $map['a.idcode']= $v['idcode'];
            $order = 'a.id desc';
            
            $ret = $m_stock_record->alias('a')
                           ->join('savor_finance_stock stock on a.stock_id= stock.id','left')
                           
                           ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                           
                           ->field($field)
                           ->where($map)
                           ->order($order)
                           ->find();
            
            
            $account_detail_info = [];                
            
            $account_detail_info['goods_id']   = !empty($ret['goods_id']) ? $ret['goods_id'] :0;
            $account_detail_info['goods_name'] = !empty($ret['goods_name']) ? $ret['goods_name'] : '';
            $account_detail_info['idcode']     = !empty($ret['idcode']) ? $ret['idcode'] : '';
            
            
            
            $ifields = 'a.type,a.add_time,area.id area_id,
                      area.region_name area_name,stock.hotel_id,hotel.name hotel_name,stock.io_type';
            $info = $m_stock_record->alias('a')
                                   ->field($ifields)
                                   ->join('savor_finance_stock stock on a.stock_id= stock.id','left')
                                   ->join('savor_area_info area on stock.area_id=area.id','left')
                                   ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                                   ->where(array('a.idcode'=>$v['idcode'],'a.add_time'=>array('elt',$end_date.' 23:59:59')))
                                   ->order('a.id desc')
                                   ->find();
                       
            if($info['type']==6) continue;  //报损的不处理
            $account_detail_info['area_id']    = !empty($info['area_id']) ? $info['area_id'] :0;
            $account_detail_info['area_name']  = !empty($info['area_name']) ? $info['area_name']: '';
            $account_detail_info['io_type']    = $info['io_type'];
                                   
            if(!empty($info['hotel_id'])){
                $account_detail_info['store_id']    = !empty($info['hotel_id']) ? $info['hotel_id'] :0;
                $account_detail_info['store_name']  = !empty($info['hotel_name']) ? $info['hotel_name'] :'';
               
            }else {
                $account_detail_info['store_id']    = !empty($info['area_id']) ? $info['area_id'] : 0;
                $account_detail_info['store_name']  = !empty($info['area_name']) ? $info['area_name'] : 0;
            }
                                   
            $type_arr = [5,6,7];
            $account_detail_info['type'] = $info['type'];
            if( $info['type']==5){//验收 说明在酒楼
                //持续时长
                $duration = time() - strtotime($info['add_time']);
                $duration = round($duration/86400,2);
                
                $store_type = 2;
                $store_check_time = $info['add_time'];
                $account_detail_info['store_type'] = $store_type;
                $account_detail_info['store_check_time'] = $store_check_time;
                $account_detail_info['duration'] = $duration;
                
                
            }else if($info['type']==7){
                $ys_info = $m_stock_record->field('type,add_time')
                                            ->where(array('idcode'=>$v['idcode'],'type'=>5))
                                            ->order('id desc')
                                            ->find();
                $duration = strtotime($info['add_time']) - strtotime($ys_info['add_time']);
                $duration = round($duration/86400,2);
                $store_type = 2;
                $store_check_time = $ys_info['add_time'];
                $store_sale_time  = $info['add_time'];
                $account_detail_info['store_type'] = $store_type;
                $account_detail_info['store_sale_time'] = $store_sale_time;
                $account_detail_info['duration'] = $duration;
                
            }else if(!in_array($info['type'], $type_arr)){
                if($info['type']==1){
                    $duration = time() - strtotime($info['add_time']);
                    $duration = round($duration/86400,2);
                    $store_in_time = $info['add_time'];
                    $store_type = 1;
                    
                }else if($info['type']==3){
                    $record_info = $m_stock_record->field('type,add_time')
                    ->where(array('idcode'=>$v['idcode'],'type'=>1))
                    ->order('id desc')
                    ->find();
                    $duration = time() - strtotime($record_info['add_time']);
                    $duration = round($duration/86400,2);
                    $store_in_time = $info['add_time'];
                    $store_type = 1;
                }else{
                    $record_info = $m_stock_record->field('type,add_time')
                                                    ->where(array('idcode'=>$v['idcode'],'type'=>1))
                                                    ->order('id desc')
                                                    ->find();
                    if(empty($record_info)){
                        
                        $qrcontent = decrypt_data($v['idcode']);
                        $qr_id = intval($qrcontent);
                        $qr_info = $m_qrcode_content->where(array('id'=>$qr_id))
                                         ->field('parent_id')
                                         ->find();
                        $pidcode = encrypt_data($qr_info['parent_id']);
                       
                        $record_info = $m_stock_record->field('type,add_time')
                                                      ->where(array('idcode'=>$pidcode,'type'=>1))
                                                      ->order('id desc')
                                                      ->find();
                        
                    }
                    
                    $duration = time() - strtotime($record_info['add_time']);
                    $duration = round($duration/86400,2);
                    
                    $store_in_time = $record_info['add_time'];
                    $store_type = 2;
                    
                }
                
                
                
                $account_detail_info['store_type']    = $store_type;
                $account_detail_info['store_in_time'] = $store_in_time;
                $account_detail_info['duration']      = $duration;
                
                
            }
            $static_date  = $end_date;
            
            $account_detail_info['static_date'] = $static_date;
            //print_r($account_detail_info);exit;
            $m_accountage_detail->addData($account_detail_info);
        }
        echo date('Y-m-d H:i:s').'OK'."\n";
    }
}