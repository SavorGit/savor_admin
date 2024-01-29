<?php
namespace Crontab\Controller;
use Think\Controller;
//库龄明细表
class FinanceAccountageSummaryController extends Controller{
    private $days_range_arr = array(
        array('min'=>1,'max'=>7,'name'=>'store_age_1_7','num'=>0),
        array('min'=>8,'max'=>15,'name'=>'store_age_8_15','num'=>0),
        array('min'=>16,'max'=>30,'name'=>'store_age_16_30','num'=>0),
        array('min'=>31,'max'=>60,'name'=>'store_age_31_60','num'=>0),
        array('min'=>61,'max'=>90,'name'=>'store_age_61_90','num'=>0),
        array('min'=>91,'max'=>180,'name'=>'store_age_91_180','num'=>0),
        array('min'=>181,'max'=>360,'name'=>'store_age_181_360','num'=>0),
        array('min'=>361,'max'=>720,'name'=>'store_age_361_720','num'=>0),
        array('min'=>721,'max'=>9999999,'name'=>'store_age_820_9999999','num'=>0),
    );
    public function accountageSummary(){
        $static_date = date('Y-m-d',strtotime('-1 day')) ;
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $all_goods = $m_stock_detail->getAllData('goods_id','','','goods_id');
        $where = [];
        if(!empty($all_goods)){
            $goods_ids = array();
            foreach ($all_goods as $v){
                $goods_ids[]=$v['goods_id'];
            }
            $where['goods.id']=array('in',$goods_ids);
        }
        $fields = 'goods.id as goods_id,goods.name goods_name';
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $res_list = $m_goods->alias('goods')
                            ->field($fields)
                            ->order('goods.id desc')
                            ->where($where)
                            ->select();
        
        
        
        $area_arr = [];
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        
        $m_accountage_summary = new \Admin\Model\FinanceDataAccountageSummaryModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_avg_price = new \Admin\Model\FinanceGoodsAvgpriceModel();
        $m_companystock = new \Admin\Model\FinanceCompanyStockModel();
        $m_company_stock_detail = new \Admin\Model\FinanceCompanyStockDetailModel();
        //$data_list = [];
        $days_range_arr = $this->days_range_arr;
        foreach ($res_list as $key=>$v){
            $goods_id = $v['goods_id'];
            
            $stock_where = array('goods_id'=>$goods_id);
            
            foreach($res_area as $kk=>$vv){
                $stock_where['area_id'] = $vv['id'];
                $res_stock = $m_companystock->getRow('sum(num) as store_num',$stock_where);
                //echo $m_companystock->getLastSql();exit;
                $store_num = intval($res_stock['store_num']);
                
                $info = [];
                $info['goods_id']   = $v['goods_id'];
                $info['goods_name'] = $v['goods_name'];
                $info['area_id']    = $vv['id'];
                $info['area_name']  = $vv['region_name'];
                $info['store_type'] = 1;   //库存类型1:中转仓2:前置仓
                $info['store_id']   = $vv['id'];
                $info['store_name'] = $vv['region_name'];
                $info['store_num']  = $store_num;
                
                
                
                
                if(!empty($store_num)){
                    $map = [];
                    $map['goods_id'] = $goods_id;
                    $map['area_id']  = $vv['id'];
                    $idcode_list = $m_company_stock_detail->field('*')->where($map)
                                           ->order('type asc')->select();
                    $count_num = count($idcode_list);
                    $all_time = 0;
                    $now_time = time();
                    foreach($idcode_list as $ks=>$vs){
                        $diff_time = $now_time - strtotime($vs['add_time']);
                        
                        $all_time += $diff_time;
                        
                        $avg_age = round($diff_time  / 86400 ,2);
                        foreach($days_range_arr as $dk=>$dv){
                            
                            if($avg_age>=$dv['min'] && $avg_age<=$dv['max']){
                                $days_range_arr[$dk]['num'] +=1;
                                break;
                            }       
                        }
                    }
                    $store_avg_age= round(($all_time / $count_num ) / 86400 ,2);
                    
                    $info['store_avg_age'] = $store_avg_age;
                    
                    $res_price = $m_avg_price->getAll('price',array('goods_id'=>$goods_id),0,1,'id desc');
                    $avg_price = $res_price[0]['price'];
                    
                    $total_fee = $avg_price*$store_num;
                    $info['store_total_money'] = $total_fee;
                    $info['sale_num']          = 0;
                    $info['sale_avg_age']      = 0;
                    
                    
                    $info['all_num']           = 0;  // dstatus=1 stock_record.type=1 area_id=  goods_id=  count一下
                    
                    $info['all_avg_age']       = 0;  ///dstatus=1 stock_record.type=1 area_id=  goods_id=  查明细 idcode  add_time 以后  当前时间-add_time   根据idcode 查stock_record表 order by id desc limit 1 
                    //stock_record.type = 1  now_time - add_time   
                    //stock_record.type != 1  第二次查的add_time - 第一次查的add_time
                    
                    //所有的时间差 相加  除以 all_num
                    
                    
                    foreach($days_range_arr as $dk=>$dv){
                        $info[$dv['name']] = $dv['num'];
                    }
                    $info['static_date'] = $static_date;
                    //print_r($info);exit;
                    //$m_accountage_summary->addData($info);
                }else { //没有库存数量  
                    /*$info['store_avg_age']     = 0;
                    $info['store_total_money'] = 0;
                    $info['sale_num']          = 0;
                    $info['sale_avg_age']      = 0;
                    $info['all_num']           = 0;
                    $info['all_avg_age']       = 0;
                    
                    foreach($days_range_arr as $dk=>$dv){
                        $info[$dv['name']] = 0;
                    }*/
                }
                
            }
        }
        //前置仓数据
        $days_range_arr = $this->days_range_arr;
        foreach ($res_list as $key=>$v){
            $goods_id = $v['goods_id'];
            
            $where = array('a.goods_id'=>$goods_id,'stock.hotel_id'=>array('gt',0),'stock.type'=>20);
            $test_hotels = C('TEST_HOTEL');
            $test_hotels[]=0;
            $where['stock.hotel_id'] = array('not in',$test_hotels);
            
            $fileds = 'stock.hotel_id,hotel.name hotel_name,area.id area_id,area.region_name area_name';
            $group = 'stock.hotel_id';
            $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
            $hotel_list = $m_stock_detail->alias('a')
                                       ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                       ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                                       ->join('savor_area_info area on area.id=stock.area_id')
                                       ->field($fileds)
                                       ->where($where)
                                       ->group($group)
                                       ->select();
            
            if(!empty($hotel_list)){
                $rwhere = array('a.goods_id'=>$goods_id);
                foreach($hotel_list as $hk=>$hv){
                    
                    /*$out_num = $unpack_num = $wo_num = $report_num = 0;
                    $price = 0;
                    $rwhere['stock.hotel_id'] = $hv['hotel_id'];
                    $rwhere['stock.type']     = 20;
                    $rwhere['stock.io_type']  = 22;
                    $rwhere['a.dstatus']      = 1;
                    //$rwhere['a.type'] = array('in',array(2,3));
                    $rwhere['a.type'] = 2;
                    $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                    $rgroup = 'a.type';
                    
                    $res_record = $m_stock_record->alias('a')
                                                 ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                 ->field($rfileds)
                                                 ->where($rwhere)
                                                 ->order('a.id desc')
                                                 ->group($rgroup)
                                                 ->select();
                    
                                           
                    
                    $idcode_list = $m_stock_record->alias('a')
                                   ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                   ->field('a.id,a.add_time,a.idcode')
                                   ->where($rwhere)
                                   ->order('a.id desc')
                                   ->select();
                    echo $m_stock_record->getLastSql();exit;    
                    
                    foreach ($res_record as $rv){
                        switch ($rv['type']){
                            case 2:
                                $out_num = abs($rv['total_amount']);
                                $total_fee = abs($rv['total_fee']);
                                $price = intval($total_fee/$out_num);
                                break;
                            case 3:
                                $unpack_num = $rv['total_amount'];
                                break;
                        }
                    }
                    $rwhere['a.type']=7;
                    $rwhere['a.wo_status']= array('in',array(1,2,4));
                    $res_worecord = $m_stock_record->alias('a')
                                                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                    ->field($rfileds)
                                                    ->where($rwhere)
                                                    ->order('a.id desc')
                                                    ->select();
                    
                    //print_r($res_worecord);exit;
                    if(!empty($res_worecord[0]['total_amount'])){
                        $wo_num = $res_worecord[0]['total_amount'];
                    }
                    
                    $sale_list = $m_stock_record->alias('a')
                                                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                ->field('a.id,a.add_time,a.idcode')
                                                ->where($rwhere)
                                                ->order('a.id desc')
                                                ->select();
                    $sale_idcode_arr = [];
                    foreach($sale_list as $sv){
                        $sale_idcode_arr[]=$sv['idcode'];
                    }
                    
                    
                    
                    $rwhere['a.type']=6;
                    unset($rwhere['a.wo_status']);
                    $rwhere['a.status']= array('in',array(1,2));
                    $res_worecord = $m_stock_record->alias('a')
                                                   ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                   ->field($rfileds)
                                                   ->where($rwhere)
                                                   ->order('a.id desc')
                                                   ->select();
                    
                    
                    
                    if(!empty($res_worecord[0]['total_amount'])){
                        $report_num = $res_worecord[0]['total_amount'];
                    }
                    
                    $report_list = $m_stock_record->alias('a')
                                                  ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                  ->field('a.id,a.add_time,a.idcode')
                                                  ->where($rwhere)
                                                  ->order('a.id desc')
                                                  ->select();
                    $report_idcode_arr = [];
                    foreach($report_list as $rv){
                        $report_idcode_arr[] = $rv['idcode'];
                    }*/
                                                  
                    //当前库存数量
                    $now_stock_num = ''; //??//当前前置仓 某商品总数 savor_finance_hotel_stock  goods_id =  hotel_id=
                    
                    //当前库存平均库龄
                    
                    //stock_record type=2 goods_id= hotel_id= dstatus=1
                    
                    //循环查每一个      查明细 idcode 的 add_time    根据idcode 查stock_record表 order by id desc limit 1 
                    //查出来以后看 type=5验收 处理      差值 =  当前时间- 第二个add_time  type!=5 跳过   计数器+1
                    //所有差值加和 除以 计数器 
                    
                    //差值  看是否 在 days_range_arr范围内  然后加1
                    
                    
                    
                    //当前库存金额
                    //计数器 * 移动平均价
                    
                    
                    $map = [];
                    $map['a.type']=7;
                    $map['a.wo_status']= 2;
                    $map['a.goods_id'] = $goods_id;
                    $map['stock.hotel_id'] = $hv['hotel_id'];
                    //只要当月时间 stock_reord 表的add_time
                    
                    $wo_list = $res_worecord = $m_stock_record->alias('a')
                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                    ->field('a.id,a.idcode,a.add_time')
                    ->where($map)
                    ->order('a.id desc')
                    ->select();
                    $wo_nums = 0;
                    foreach($wo_list as $wk=>$wv){
                        
                        //stock_record 表    根据idcode  type=5 order by id desc limit 1 查 add_time   $diff_time = $wv.add_time - add_time
                        // $wo_nums +=1
                        //diff_time  加和 
                    }
                    //已销售数量平均库龄
                    //diff_time  加和  除以 $wo_nums
                    
                    
                    //总库存数量  =   $now_stock_num + $wo_nums
                    
                    
                    //(当前库存平均库龄 总和 + 已销售数量平均库龄总和) 除以 总库存数量
                    $info = [];
                    $info['goods_id']   = $v['goods_id'];
                    $info['goods_name'] = $v['goods_name'];
                    $info['area_id']    = $hv['area_id'];
                    $info['area_name']  = $hv['area_name'];
                    $info['store_type'] = 2;   //库存类型1:中转仓2:前置仓
                    $info['store_id']   = $hv['hotel_id'];
                    $info['store_name'] = $hv['hotel_name'];
                    $info['store_num']  = $now_stock_num;
                    
                    
                   
                }
            }
            
            
            
        }
        
        
        
                                   
    }
}