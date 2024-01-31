<?php
namespace Crontab\Controller;
use Think\Controller;

class CompanystockController extends Controller{

    public function stockbj(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockbj start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(1);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockbj end:$now_time \r\n";
    }

    public function stocksh(){
        $now_time = date('Y-m-d H:i:s');
        echo "stocksh start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(9);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stocksh end:$now_time \r\n";
    }

    public function stockgz(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockgz start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(236);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockgz end:$now_time \r\n";
    }

    public function stockfs(){
        $now_time = date('Y-m-d H:i:s');
        echo "stockfs start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceCompanyStockModel();
        $m_stock->handle_company_stock(248);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        $now_time = date('Y-m-d H:i:s');
        echo "stockfs end:$now_time \r\n";
    }



    public function archivedata(){
        $now_time = date('Y-m-d H:i:s');
        echo "archivedata start:$now_time \r\n";

        $static_date = date('Y-m-d',strtotime('-1 day'));
        $archivedata_date = date('Y-m-d',strtotime('-2 day'));
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            if($v['id']!=246){
                $area_arr[$v['id']]=$v;
            }
        }
        $m_companystock = new \Admin\Model\FinanceCompanyStockModel();
        $res_companystock = $m_companystock->getAllData('area_id,goods_id,num',array(),'id desc');
        $company_stock = array();
        foreach ($res_companystock as $v){
            $company_stock[$v['area_id'].$v['goods_id']]=$v['num'];
        }

        $fields = 'goods.id as goods_id,goods.name as goods_name,goods.category_id,cate.name as category_name';
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $where = array('goods.brand_id'=>array('neq',11));
        $res_list = $m_goods->getList($fields,$where, 'goods.id asc');
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_avg_price = new \Admin\Model\FinanceGoodsAvgpriceModel();
        $m_companystock_archivedata = new \Admin\Model\FinanceCompanyStockArchivedataModel();
        foreach ($res_list as $v){
            $goods_id = $v['goods_id'];
            $res_price = $m_avg_price->getAll('price',array('goods_id'=>$goods_id),0,1,'id desc');
            $avg_price = $res_price[0]['price'];

            $fields = 'sum(a.total_amount) as total_amount,a.type';
            $swhere = array('a.goods_id'=>$goods_id,'a.type'=>array('in',array(1,2)),'a.dstatus'=>1);
            $swhere['a.add_time'] = array('elt',"$static_date 23:59:59");
            $archivedatas = array();
            foreach ($area_arr as $av){
                $in_num = $out_num = 0;
                $in_total_fee = $out_total_fee = $price = 0;
                $now_area_id = $av['id'];
                $swhere['stock.area_id'] = $now_area_id;
                $res_goods_record = $m_stock_record->getAllStock($fields,$swhere,'a.id desc','a.type');
                foreach ($res_goods_record as $rv){
                    switch ($rv['type']){
                        case 1:
                            $in_num = abs($rv['total_amount']);
                            $in_total_fee = $in_num*$avg_price;
                            break;
                        case 2:
                            $out_num = abs($rv['total_amount']);
                            $out_total_fee = $out_num*$avg_price;
                            break;
                    }
                }
                if($in_num>0){
                    $stock_num = isset($company_stock[$now_area_id.$goods_id])?$company_stock[$now_area_id.$goods_id]:0;
                    $stock_total_fee = $stock_num*$avg_price;

                    $bwhere = array('area_id'=>$now_area_id,'goods_id'=>$goods_id,'static_date'=>$archivedata_date);
                    $res_begin_data = $m_companystock_archivedata->getAll('stock_num,stock_total_fee',$bwhere,0,1,'id desc');
                    if(!empty($res_begin_data[0]['stock_num'])){
                        $begin_num = $res_begin_data[0]['stock_num'];
                        $begin_total_fee = $res_begin_data[0]['stock_total_fee'];
                    }else{
                        $begin_num = $stock_num;
                        $begin_total_fee = $stock_total_fee;
                    }
                    $v['avg_price'] = $avg_price;
                    $v['area_id'] = $now_area_id;
                    $v['area_name'] = $area_arr[$now_area_id]['region_name'];
                    $v['in_num'] = $in_num;
                    $v['in_total_fee'] = $in_total_fee;
                    $v['out_num'] = $out_num;
                    $v['out_total_fee'] = $out_total_fee;
                    $v['stock_num'] = $stock_num;
                    $v['stock_total_fee'] = $stock_total_fee;
                    $v['begin_num'] = $begin_num;
                    $v['begin_total_fee'] = $begin_total_fee;
                    $v['static_date'] = $static_date;
                    $archivedatas[] = $v;
                }
            }
            if(!empty($archivedatas)){
                $m_companystock_archivedata->addAll($archivedatas);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "archivedata end:$now_time \r\n";
    }
}